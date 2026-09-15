/**
 * Module: Creaciones Management (Panel del Artesano — Server-Driven · Subfase 4.3)
 * Single Responsibility: rejilla reactiva del inventario contra
 * `GET /api/creaciones/index.php`, mutaciones autenticadas (crear/editar con
 * multipart, stock in-situ, toggle encargo, baja/restauración lógica) y ficha
 * técnica modal.
 *
 * Seguridad DOM (H-004): el marcado artesanal vive constante en la vista
 * (`<template id="creacionCardTemplate">`); los datos del servidor se inyectan
 * exclusivamente con `textContent`/DOM APIs. Cero interpolación en `innerHTML`
 * (solo contadores numéricos exentos).
 * Moneda (R-06): la UI trabaja en pesos, el wire viaja en centavos enteros.
 */

import { getToken, getUser, isAuthenticated, clearSession } from './auth.js';
import { pesosToCents } from './currency.js';
import { setIconText } from './dom-safe.js';

const INDEX_URL = '/api/creaciones/index.php';
const ARTISANS_URL = '/api/creaciones/artesanos.php';
const DETALLE_URL = '/api/creaciones/detalle.php';
const CREAR_URL = '/api/creaciones/crear.php';
const ACTUALIZAR_URL = '/api/creaciones/actualizar.php';
const ELIMINAR_URL = '/api/creaciones/eliminar.php';
const RESTAURAR_URL = '/api/creaciones/restaurar.php';
const AJUSTAR_URL = '/api/creaciones/ajustar-stock.php';
const TOGGLE_URL = '/api/creaciones/toggle-encargo.php';
const PAGE_LIMIT = 12;
const GENERIC_FALLBACK = 'assets/svg/piezas/ovillo-generico.svg';

const state = {
  search: '',
  category: 'all',
  stock: 'all',
  artisan: 'all',
  sort: 'recientes',
  page: 1,
  seq: 0,
};

let grid = null;
let template = null;
const itemCache = new Map();

function authHeaders(extra = {}) {
  const headers = { ...extra };
  const token = getToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;
  return headers;
}

async function parseJsonBody(res) {
  const contentType = res.headers.get('content-type') || '';
  if (!contentType.includes('application/json')) return null;
  try {
    return await res.json();
  } catch {
    return null;
  }
}

function debounce(fn, wait = 250) {
  let timer = null;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), wait);
  };
}

function buildQuery() {
  const q = new URLSearchParams();
  if (String(state.search).trim() !== '') q.set('busqueda', state.search.trim());
  if (state.category !== 'all') q.set('categoria', state.category);
  if (state.stock !== 'all') q.set('estado_stock', state.stock);
  if (state.artisan !== 'all') q.set('artesano_id', String(state.artisan));
  q.set('orden', state.sort);
  q.set('pagina', String(state.page));
  q.set('limite', String(PAGE_LIMIT));
  return q;
}

function showLoading(show) {
  const el = document.getElementById('creacionesLoadingState');
  if (el) el.style.display = show ? '' : 'none';
}

function showEmptyState(show) {
  const empty = document.getElementById('emptyCreacionesState');
  if (!empty) return;
  empty.classList.toggle('d-none', !show);
  if (grid) grid.classList.toggle('d-none', show);
}

function updateCountBadge(shownOnPage, totalItems) {
  const badge = document.getElementById('creacionesCountBadge');
  if (badge) setIconText(badge, 'bi bi-box2-heart me-1', `${shownOnPage} de ${totalItems} piezas`);
  const showing = document.getElementById('creacionesShowingCount');
  const total = document.getElementById('creacionesTotalCount');
  if (showing) showing.textContent = String(shownOnPage);
  if (total) total.textContent = String(totalItems);
}

function formatMoneyFromCents(cents) {
  const pesos = Number(cents) / 100;
  return `$${pesos.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

async function fetchKpis() {
  const kpiModelos = document.getElementById('kpiCreacionesModelos');
  const kpiStock = document.getElementById('kpiCreacionesStock');
  const kpiValor = document.getElementById('kpiCreacionesValor');
  const kpiCostos = document.getElementById('kpiCreacionesCostos');
  if (!kpiModelos && !kpiStock && !kpiValor && !kpiCostos) return;

  try {
    const first = await fetch(`${INDEX_URL}?${buildQueryWithoutPage(1)}`);
    const firstJson = await first.json();
    if (!firstJson || firstJson.exito !== true) return;
    const pag = firstJson.paginacion || {};
    const totalItems = Number(pag.total_items) || 0;
    const totalPages = Math.min(Number(pag.total_paginas) || 1, 20);

    let items = Array.isArray(firstJson.datos) ? [...firstJson.datos] : [];
    for (let p = 2; p <= totalPages; p++) {
      const res = await fetch(`${INDEX_URL}?${buildQueryWithoutPage(p)}`);
      const json = await res.json();
      if (json && json.exito === true && Array.isArray(json.datos)) {
        items = items.concat(json.datos);
      }
    }

    let totalStock = 0;
    let totalValor = 0;
    let totalCostos = 0;
    items.forEach((item) => {
      const stock = Number(item.cantidad_stock) || 0;
      totalStock += stock;
      totalValor += stock * (Number(item.precio_centavos) || 0);
      totalCostos += stock * (Number(item.costo_materiales_centavos) || 0);
    });

    if (kpiModelos) kpiModelos.textContent = String(totalItems);
    if (kpiStock) kpiStock.textContent = String(totalStock);
    if (kpiValor) kpiValor.textContent = formatMoneyFromCents(totalValor);
    if (kpiCostos) kpiCostos.textContent = formatMoneyFromCents(totalCostos);
  } catch {
    /* Los KPIs conservan su último valor ante un fallo de red. */
  }
}

function buildQueryWithoutPage(page) {
  const q = buildQuery();
  q.set('pagina', String(page));
  q.set('limite', '48');
  return q;
}

function setStockBadge(badge, stock, onDemand) {
  if (!badge) return;
  badge.replaceChildren();
  const icon = document.createElement('i');
  badge.appendChild(icon);
  if (onDemand) {
    badge.className = 'badge bg-light text-muted font-monospace border';
    icon.className = '';
    badge.appendChild(document.createTextNode('Bajo Encargo'));
  } else if (stock === 0) {
    badge.className = 'badge-stock-alert';
    icon.className = '';
    badge.appendChild(document.createTextNode('Agotado'));
  } else if (stock <= 3) {
    badge.className = 'badge-stock-critical';
    icon.className = '';
    badge.appendChild(document.createTextNode('Stock Crítico'));
  } else {
    badge.className = 'badge badge-stock-in';
    icon.className = '';
    badge.appendChild(document.createTextNode('En Existencia'));
  }
}

function setEncargoToggle(button, onDemand) {
  if (!button) return;
  button.replaceChildren();
  const icon = document.createElement('i');
  button.appendChild(icon);
  if (onDemand) {
    button.className = 'btn-toggle-encargo badge badge-textile-tag text-primary border-primary border-0 bg-transparent p-1';
    icon.className = 'bi bi-magic me-1';
    button.appendChild(document.createTextNode('Bajo Encargo'));
    button.title = 'Click para cambiar a Entrega Inmediata';
  } else {
    button.className = 'btn-toggle-encargo badge bg-light text-dark border font-monospace border-0 p-1';
    icon.className = 'bi bi-lightning-charge-fill text-warning me-1';
    button.appendChild(document.createTextNode('Inmediata'));
    button.title = 'Click para cambiar a Bajo Encargo Exclusivo';
  }
}

function imageSources(item) {
  const sources = [];
  if (item.imagen_url) sources.push(String(item.imagen_url));
  if (item.imagen_fallback_svg) sources.push(String(item.imagen_fallback_svg));
  sources.push(GENERIC_FALLBACK);
  return sources;
}

function renderCard(item) {
  const node = template.content.cloneNode(true);
  const part = (name) => node.querySelector(`[data-part="${name}"]`);
  const id = String(item.id);

  itemCache.set(id, item);

  part('idBadge').textContent = `#${id}`;

  const stock = Number(item.cantidad_stock) || 0;
  const onDemand = Number(item.es_sobre_encargo) === 1;
  const toggleBtn = part('encargoToggle');
  setEncargoToggle(toggleBtn, onDemand);
  toggleBtn.setAttribute('data-id', id);

  const img = part('photo');
  const sources = imageSources(item);
  img.setAttribute('src', sources[0]);
  img.setAttribute('alt', String(item.nombre || 'Pieza artesanal'));
  let attempt = 0;
  img.addEventListener('error', () => {
    attempt += 1;
    if (attempt < sources.length) img.setAttribute('src', sources[attempt]);
    else img.classList.add('d-none');
  });

  part('nombre').textContent = String(item.nombre || '');
  part('nombre').setAttribute('title', String(item.nombre || ''));
  part('categoria').textContent = String(item.categoria || '');
  part('dimensiones').textContent = String(item.dimensiones || '');
  part('material').textContent = String(item.material || '');

  part('precio').textContent = String(item.precio_formateado || formatMoneyFromCents(item.precio_centavos || 0));
  part('costo').textContent = `Costo: ${String(item.costo_materiales_formateado || formatMoneyFromCents(item.costo_materiales_centavos || 0))}`;

  const margen = Number(item.ganancia_bruta_centavos) || 0;
  setIconText(part('margen'), 'bi bi-graph-up-arrow', ` +${formatMoneyFromCents(margen)} (${Number(item.margen_bruto_porcentaje) || 0}%)`);
  part('retorno').textContent = String(item.retorno_por_hora_formateado || '');

  const decBtn = part('stockDec');
  const incBtn = part('stockInc');
  decBtn.setAttribute('data-id', id);
  incBtn.setAttribute('data-id', id);
  part('stockVal').textContent = String(stock);
  part('stockVal').id = `stockVal_${id}`;
  setStockBadge(part('stockBadge'), stock, onDemand);
  part('stockBadge').id = `stockBadgeContainer_${id}`;

  const artisan = (item.artesano && item.artesano.username) || item.artesano_username || '';
  part('authorInitial').textContent = String(artisan).charAt(0).toUpperCase();
  part('authorName').textContent = `@${artisan}`;

  const inspectBtn = part('inspectBtn');
  inspectBtn.setAttribute('data-id', id);
  part('editLink').setAttribute('href', `formulario.php?id=${encodeURIComponent(id)}`);

  const deleteBtn = part('deleteBtn');
  deleteBtn.setAttribute('data-id', id);
  deleteBtn.setAttribute('data-name', String(item.nombre || ''));
  const restoreBtn = part('restoreBtn');
  restoreBtn.setAttribute('data-id', id);
  restoreBtn.setAttribute('data-name', String(item.nombre || ''));

  const col = node.querySelector('[data-part="cardCol"]');
  if (col) {
    col.id = `creacionCardCol_${id}`;
    col.setAttribute('data-id', id);
  }

  return node;
}

function renderCards(items) {
  grid.replaceChildren();
  itemCache.clear();
  items.forEach((item) => grid.appendChild(renderCard(item)));
}

function pageWindow(current, total) {
  if (total <= 7) {
    const arr = [];
    for (let i = 1; i <= total; i++) arr.push(i);
    return arr;
  }
  const numbers = [...new Set([1, current - 1, current, current + 1, total])]
    .filter((n) => n >= 1 && n <= total)
    .sort((a, b) => a - b);
  const result = [];
  let prev = 0;
  numbers.forEach((n) => {
    if (n - prev > 1) result.push(null);
    result.push(n);
    prev = n;
  });
  return result;
}

function makeNavItem(kind, targetPage, enabled) {
  const li = document.createElement('li');
  li.className = 'page-item' + (enabled ? '' : ' disabled');
  const a = document.createElement('a');
  a.className = 'page-link';
  a.setAttribute('role', 'button');
  a.setAttribute('aria-label', kind === 'prev' ? 'Anterior' : 'Siguiente');
  const i = document.createElement('i');
  i.className = kind === 'prev' ? 'bi bi-chevron-left' : 'bi bi-chevron-right';
  a.appendChild(i);
  if (enabled) {
    a.href = '#';
    a.addEventListener('click', (e) => {
      e.preventDefault();
      state.page = targetPage;
      fetchPage();
    });
  } else {
    a.setAttribute('aria-disabled', 'true');
    a.tabIndex = -1;
  }
  li.appendChild(a);
  return li;
}

function makeNumberItem(page) {
  const li = document.createElement('li');
  const isCurrent = page === state.page;
  li.className = 'page-item' + (isCurrent ? ' active' : '');
  const a = document.createElement('a');
  a.className = 'page-link';
  a.textContent = String(page);
  if (isCurrent) {
    a.setAttribute('aria-current', 'page');
  } else {
    a.href = '#';
    a.addEventListener('click', (e) => {
      e.preventDefault();
      state.page = page;
      fetchPage();
    });
  }
  li.appendChild(a);
  return li;
}

function renderPagination(pag) {
  const current = Number(pag.pagina_actual) || 1;
  const totalPages = Number(pag.total_paginas) || 1;
  const nav = document.getElementById('creacionesPaginationNav');
  const ul = nav && nav.querySelector('ul');
  if (!ul) return;
  ul.replaceChildren();
  ul.appendChild(makeNavItem('prev', current - 1, Boolean(pag.tiene_anterior)));
  pageWindow(current, totalPages).forEach((p) => {
    if (p === null) {
      const li = document.createElement('li');
      li.className = 'page-item disabled';
      const span = document.createElement('span');
      span.className = 'page-link';
      span.textContent = '…';
      li.appendChild(span);
      ul.appendChild(li);
    } else {
      ul.appendChild(makeNumberItem(p));
    }
  });
  ul.appendChild(makeNavItem('next', current + 1, Boolean(pag.tiene_siguiente)));
}

async function fetchPage() {
  if (!grid || !template) return;
  const seq = ++state.seq;
  if (grid.childElementCount === 0) showLoading(true);

  try {
    const res = await fetch(`${INDEX_URL}?${buildQuery()}`);
    if (res.status === 401) {
      clearSession();
      showLoading(false);
      return;
    }
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    if (!json || json.exito !== true) {
      throw new Error((json && json.error && json.error.mensaje) || 'Error del servidor.');
    }
    if (seq !== state.seq) return;

    const pag = json.paginacion || {};
    const totalPages = Number(pag.total_paginas) || 1;
    if (state.page > totalPages) {
      state.page = 1;
      return fetchPage();
    }

    const items = Array.isArray(json.datos) ? json.datos : [];
    renderCards(items);
    renderPagination(pag);
    updateCountBadge(items.length, pag.total_items ?? 0);
    grid.dataset.total = String(pag.total_items ?? 0);
    showLoading(false);
    showEmptyState(items.length === 0);
    fetchKpis();
  } catch {
    if (seq !== state.seq) return;
    showLoading(false);
    showEmptyState(true);
  }
}

async function loadArtisans() {
  const select = document.getElementById('filterArtisanSelect');
  if (!select) return;
  try {
    const res = await fetch(ARTISANS_URL);
    if (!res.ok) return;
    const json = await res.json();
    const artisans = json && json.exito === true && Array.isArray(json.datos) ? json.datos : [];
    artisans.forEach((artisan) => {
      const opt = document.createElement('option');
      opt.value = String(artisan.id);
      opt.textContent = `@${artisan.username} (${artisan.total_creaciones})`;
      select.appendChild(opt);
    });
  } catch {
    /* El dropdown conserva "Todos los artesanos". */
  }
}

function resetFilters(searchInput, filterCategory, filterStock, filterArtisan, sortSelect) {
  state.search = '';
  state.category = 'all';
  state.stock = 'all';
  state.artisan = 'all';
  state.sort = 'recientes';
  state.page = 1;

  if (searchInput) searchInput.value = '';
  if (filterCategory) filterCategory.value = 'all';
  if (filterStock) filterStock.value = 'all';
  if (filterArtisan) filterArtisan.value = 'all';
  if (sortSelect) sortSelect.value = 'recientes';

  fetchPage();
}

async function postJson(url, payload) {
  const res = await fetch(url, {
    method: 'POST',
    headers: authHeaders({ 'Content-Type': 'application/json' }),
    body: JSON.stringify(payload),
  });
  const json = await parseJsonBody(res);
  return { status: res.status, json };
}

function refreshCard(id) {
  state.page = 1;
  return fetchPage();
}

async function adjustStock(id, newStock) {
  const clamped = Math.max(0, Math.min(10000, newStock));
  const { status, json } = await postJson(AJUSTAR_URL, { id: Number(id), cantidad_stock: clamped });
  if (status === 401) {
    clearSession();
    return;
  }
  if (status === 200 && json && json.exito) {
    await refreshCard(id);
  }
}

async function toggleEncargo(id, newState) {
  const { status, json } = await postJson(TOGGLE_URL, { id: Number(id), es_sobre_encargo: newState ? 1 : 0 });
  if (status === 401) {
    clearSession();
    return;
  }
  if (status === 200 && json && json.exito) {
    await refreshCard(id);
  }
}

function fillInspectModal(item) {
  const id = String(item.id);
  const stock = Number(item.cantidad_stock) || 0;
  const onDemand = Number(item.es_sobre_encargo) === 1;
  const precio = Number(item.precio_centavos) || 0;
  const costo = Number(item.costo_materiales_centavos) || 0;
  const margen = Number(item.ganancia_bruta_centavos) || (precio - costo);
  const horas = Number(item.horas_tejido) || 0;
  const artisan = (item.artesano && item.artesano.username) || item.artesano_username || '';

  const set = (elementId, value) => {
    const el = document.getElementById(elementId);
    if (el) el.textContent = value;
  };

  set('inspectCreacionId', `#${id}`);
  set('inspectCreacionTitle', String(item.nombre || ''));
  set('inspectCategoryBadge', String(item.categoria || ''));
  set('inspectTamano', String(item.dimensiones || ''));
  set('inspectPrecio', String(item.precio_formateado || formatMoneyFromCents(precio)));
  set('inspectCosto', String(item.costo_materiales_formateado || formatMoneyFromCents(costo)));
  set('inspectMargen', formatMoneyFromCents(margen));
  set('inspectHoras', `${horas} hrs`);
  set('inspectHourlyRate', '');
  setIconText(
    document.getElementById('inspectHourlyRate'),
    'bi bi-clock-history',
    ` Retorno: ${String(item.retorno_por_hora_formateado || '$0.00 MXN/h')}`
  );
  set('inspectMaterial', String(item.material || ''));
  set('inspectDescripcion', String(item.descripcion || ''));
  set('inspectArtisanAuthor', `Autoría: @${artisan} (Creador Registrado)`);

  const pedidos = Number(item.pedidos_asociados) || 0;
  const pedidosEl = document.getElementById('inspectPedidosInfo');
  if (pedidosEl) {
    pedidosEl.replaceChildren();
    const badge = document.createElement('span');
    if (pedidos > 0) {
      badge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning';
      setIconText(badge, 'bi bi-shield-lock-fill me-1', ` ${pedidos} encargo(s) activo(s) • ON DELETE RESTRICT`);
    } else {
      badge.className = 'badge bg-light text-muted border';
      setIconText(badge, 'bi bi-check2-circle me-1', ' Sin encargos vinculados');
    }
    pedidosEl.appendChild(badge);
  }

  const stockBadge = document.getElementById('inspectStockBadge');
  if (stockBadge) {
    if (stock === 0 && !onDemand) {
      stockBadge.className = 'badge badge-stock-alert';
      stockBadge.textContent = 'Agotado (0 disp.)';
    } else {
      stockBadge.className = 'badge badge-stock-in';
      stockBadge.textContent = `En Stock: ${stock} u.`;
    }
  }

  const onDemandBadge = document.getElementById('inspectOnDemandBadge');
  if (onDemandBadge) {
    if (onDemand) {
      onDemandBadge.className = 'badge badge-textile-tag';
      onDemandBadge.textContent = 'Bajo Encargo';
    } else {
      onDemandBadge.className = 'badge bg-light text-dark font-monospace border';
      onDemandBadge.textContent = 'Entrega Inmediata';
    }
  }

  const imgContainer = document.getElementById('inspectCreacionImageContainer');
  if (imgContainer) {
    imgContainer.replaceChildren();
    const img = document.createElement('img');
    img.className = 'img-fluid';
    img.style.maxHeight = '180px';
    const sources = imageSources(item);
    let attempt = 0;
    img.setAttribute('src', sources[0]);
    img.setAttribute('alt', String(item.nombre || 'Pieza artesanal'));
    img.addEventListener('error', () => {
      attempt += 1;
      if (attempt < sources.length) img.setAttribute('src', sources[attempt]);
      else img.classList.add('d-none');
    });
    imgContainer.appendChild(img);
  }

  const btnEdit = document.getElementById('inspectBtnEdit');
  if (btnEdit) btnEdit.setAttribute('href', `formulario.php?id=${encodeURIComponent(id)}`);
  const btnDetail = document.getElementById('inspectBtnViewDetail');
  if (btnDetail) btnDetail.setAttribute('href', `detalle.php?id=${encodeURIComponent(id)}`);
}

function openInspectModal(item) {
  fillInspectModal(item);
  const modalEl = document.getElementById('modalInspectCreacion');
  if (modalEl && window.bootstrap) {
    const modal = new window.bootstrap.Modal(modalEl);
    modal.show();
  }
}

function hideModalById(modalId) {
  const modalEl = document.getElementById(modalId);
  if (modalEl && window.bootstrap) {
    const instance = window.bootstrap.Modal.getInstance(modalEl);
    if (instance) instance.hide();
  }
}

function bindDeleteConfirm() {
  const confirmBtn = document.getElementById('btnConfirmDeleteCreacion');
  if (!confirmBtn || confirmBtn.dataset.bound === '1') return;
  confirmBtn.dataset.bound = '1';
  confirmBtn.addEventListener('click', async () => {
    const id = confirmBtn.getAttribute('data-id');
    if (!id) return;
    confirmBtn.disabled = true;
    try {
      const { status, json } = await postJson(ELIMINAR_URL, { id: Number(id) });
      if (status === 401) clearSession();
      if ((status === 200 && json && json.exito) || status === 409) {
        hideModalById('modalEliminarCreacion');
        await fetchPage();
      }
    } finally {
      confirmBtn.disabled = false;
    }
  });
}

function bindRestoreConfirm() {
  const confirmBtn = document.getElementById('btnConfirmRestoreCreacion');
  if (!confirmBtn || confirmBtn.dataset.bound === '1') return;
  confirmBtn.dataset.bound = '1';
  confirmBtn.addEventListener('click', async () => {
    const id = confirmBtn.getAttribute('data-id');
    if (!id) return;
    confirmBtn.disabled = true;
    try {
      const { status, json } = await postJson(RESTAURAR_URL, { id: Number(id) });
      if (status === 401) clearSession();
      if ((status === 200 && json && json.exito) || status === 409) {
        hideModalById('modalRestaurarCreacion');
        await fetchPage();
      }
    } finally {
      confirmBtn.disabled = false;
    }
  });
}

export function initCreaciones() {
  grid = document.getElementById('creacionesGrid');
  template = document.getElementById('creacionCardTemplate');
  if (!grid || !template) return;

  const searchInput = document.getElementById('searchCreacionInput');
  const filterCategory = document.getElementById('filterCategorySelect');
  const filterStock = document.getElementById('filterStockStatusSelect');
  const filterArtisan = document.getElementById('filterArtisanSelect');
  const sortSelect = document.getElementById('sortCreacionesSelect');
  const btnReset = document.getElementById('btnResetCreacionFilters');
  const btnResetEmpty = document.getElementById('btnResetEmptyCreaciones');

  const debouncedSearch = debounce(() => {
    state.search = searchInput ? searchInput.value.trim() : '';
    state.page = 1;
    fetchPage();
  }, 250);

  if (searchInput) searchInput.addEventListener('input', debouncedSearch);
  if (filterCategory) {
    filterCategory.addEventListener('change', () => {
      state.category = filterCategory.value;
      state.page = 1;
      fetchPage();
    });
  }
  if (filterStock) {
    filterStock.addEventListener('change', () => {
      state.stock = filterStock.value;
      state.page = 1;
      fetchPage();
    });
  }
  if (filterArtisan) {
    filterArtisan.addEventListener('change', () => {
      state.artisan = filterArtisan.value;
      state.page = 1;
      fetchPage();
    });
  }
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      state.sort = sortSelect.value;
      state.page = 1;
      fetchPage();
    });
  }

  const doReset = () => resetFilters(searchInput, filterCategory, filterStock, filterArtisan, sortSelect);
  if (btnReset) btnReset.addEventListener('click', doReset);
  if (btnResetEmpty) btnResetEmpty.addEventListener('click', doReset);

  grid.addEventListener('click', async (e) => {
    const incBtn = e.target.closest('.btn-stock-inc');
    const decBtn = e.target.closest('.btn-stock-dec');
    if (incBtn || decBtn) {
      const id = (incBtn || decBtn).getAttribute('data-id');
      const item = itemCache.get(String(id));
      if (!item) return;
      const current = Number(item.cantidad_stock) || 0;
      const next = incBtn ? current + 1 : current - 1;
      (incBtn || decBtn).disabled = true;
      try {
        await adjustStock(id, next);
      } finally {
        (incBtn || decBtn).disabled = false;
      }
      return;
    }

    const toggleBtn = e.target.closest('.btn-toggle-encargo');
    if (toggleBtn) {
      const id = toggleBtn.getAttribute('data-id');
      const item = itemCache.get(String(id));
      if (!item) return;
      const current = Number(item.es_sobre_encargo) === 1;
      toggleBtn.disabled = true;
      try {
        await toggleEncargo(id, !current);
      } finally {
        toggleBtn.disabled = false;
      }
      return;
    }

    const deleteBtn = e.target.closest('.btn-card-delete');
    if (deleteBtn) {
      const nameSpan = document.getElementById('deleteCreacionName');
      if (nameSpan) nameSpan.textContent = deleteBtn.getAttribute('data-name') || 'esta pieza';
      const confirmBtn = document.getElementById('btnConfirmDeleteCreacion');
      if (confirmBtn) confirmBtn.setAttribute('data-id', deleteBtn.getAttribute('data-id') || '');
    }

    const restoreBtn = e.target.closest('.btn-card-restore');
    if (restoreBtn) {
      const nameSpan = document.getElementById('restoreCreacionName');
      if (nameSpan) nameSpan.textContent = restoreBtn.getAttribute('data-name') || 'esta pieza';
      const confirmBtn = document.getElementById('btnConfirmRestoreCreacion');
      if (confirmBtn) confirmBtn.setAttribute('data-id', restoreBtn.getAttribute('data-id') || '');
    }

    const inspectBtn = e.target.closest('.btn-inspect-creacion');
    if (inspectBtn) {
      const item = itemCache.get(String(inspectBtn.getAttribute('data-id')));
      if (item) openInspectModal(item);
    }
  });

  bindDeleteConfirm();
  bindRestoreConfirm();
  loadArtisans();
  fetchPage();
}

// Alias de compatibilidad
export const initAmigurumis = initCreaciones;

// =============================================================================
// Formulario de alta/edición (formulario.php)
// =============================================================================

const LIMITS = {
  nombre: { min: 2, max: 100, label: 'Nombre' },
  categoria: { min: 2, max: 50, label: 'Categoría' },
  material: { min: 3, max: 80, label: 'Material' },
  dimensiones: { min: 2, max: 100, label: 'Dimensiones' },
  descripcion: { min: 0, max: 2000, label: 'Descripción' },
};

const IMAGE_MIME = ['image/jpeg', 'image/png', 'image/webp'];
const IMAGE_MAX_BYTES = 5 * 1024 * 1024;

function readFormValues() {
  const val = (id) => {
    const el = document.getElementById(id);
    return el ? el.value : '';
  };
  const encargoEl = document.getElementById('inputEsSobreEncargo');
  const artesanoEl = document.getElementById('inputArtesanoId');
  const imagenEl = document.getElementById('inputImagen');
  return {
    nombre: val('inputNombre').trim(),
    categoria: val('inputCategoria').trim(),
    material: val('inputMaterial').trim(),
    dimensiones: val('inputDimensiones').trim(),
    precio: val('inputPrecio').trim(),
    costo: val('inputCosto').trim(),
    horas: val('inputHoras').trim(),
    stock: val('inputStock').trim(),
    descripcion: val('inputDescripcion').trim(),
    esSobreEncargo: !!(encargoEl && encargoEl.checked),
    artesanoId: artesanoEl && artesanoEl.value ? artesanoEl.value : '',
    imagenFile: imagenEl && imagenEl.files && imagenEl.files[0] ? imagenEl.files[0] : null,
  };
}

function validateFormValues(v) {
  const errors = [];
  Object.entries(LIMITS).forEach(([key, rule]) => {
    const len = [...(v[key] || '')].length;
    if (key === 'descripcion') {
      if (len > rule.max) errors.push(`${rule.label}: máximo ${rule.max} caracteres.`);
      return;
    }
    if (len < rule.min || len > rule.max) {
      errors.push(`${rule.label}: entre ${rule.min} y ${rule.max} caracteres.`);
    }
  });

  const precioCents = pesosToCents(v.precio);
  if (v.precio === '' || Number.isNaN(Number(v.precio)) || precioCents < 1 || precioCents > 9999999) {
    errors.push('Precio: monto positivo en pesos (máximo $99,999.99 MXN).');
  }
  const costoCents = v.costo === '' ? 0 : pesosToCents(v.costo);
  if (v.costo !== '' && (Number.isNaN(Number(v.costo)) || costoCents < 0 || costoCents > 9999999)) {
    errors.push('Costo: monto no negativo en pesos (máximo $99,999.99 MXN).');
  }
  const stock = Number(v.stock);
  if (v.stock === '' || !Number.isInteger(stock) || stock < 0 || stock > 10000) {
    errors.push('Stock: entero entre 0 y 10,000 unidades.');
  }
  const horas = v.horas === '' ? 0 : Number(v.horas);
  if (v.horas !== '' && (Number.isNaN(horas) || horas < 0 || horas > 500)) {
    errors.push('Horas: valor entre 0.0 y 500.0.');
  }

  if (v.imagenFile) {
    if (v.imagenFile.type && !IMAGE_MIME.includes(v.imagenFile.type)) {
      errors.push('Fotografía: formato no admitido (usa JPG, PNG o WEBP).');
    }
    if (typeof v.imagenFile.size === 'number' && v.imagenFile.size > IMAGE_MAX_BYTES) {
      errors.push('Fotografía: supera el máximo de 5MB.');
    }
  }
  return { errors, precioCents, costoCents };
}

function showFormFeedback(kind, messages) {
  const box = document.getElementById('formFeedback');
  if (!box) return;
  box.replaceChildren();
  const list = Array.isArray(messages) ? messages : [messages];
  if (list.length === 0 || (list.length === 1 && !list[0])) {
    box.className = 'alert d-none';
    return;
  }
  box.className = kind === 'success' ? 'alert alert-success' : 'alert alert-danger';
  list.forEach((message) => {
    const div = document.createElement('div');
    div.textContent = String(message);
    box.appendChild(div);
  });
  box.classList.remove('d-none');
}

function fillFormFromItem(item) {
  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.value = value;
  };
  set('inputNombre', String(item.nombre || ''));
  set('inputCategoria', String(item.categoria || 'Amigurumis & Figuras'));
  set('inputMaterial', String(item.material || ''));
  set('inputDimensiones', String(item.dimensiones || ''));
  set('inputPrecio', String((Number(item.precio_centavos) || 0) / 100));
  set('inputCosto', String((Number(item.costo_materiales_centavos) || 0) / 100));
  set('inputHoras', String(item.horas_tejido ?? ''));
  set('inputStock', String(item.cantidad_stock ?? ''));
  set('inputDescripcion', String(item.descripcion || ''));
  const encargoEl = document.getElementById('inputEsSobreEncargo');
  if (encargoEl) encargoEl.checked = Number(item.es_sobre_encargo) === 1;

  if (item.imagen_url) {
    const container = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('imagePreview');
    const dropzone = document.getElementById('uploadDropzone');
    if (container && preview) {
      preview.setAttribute('src', String(item.imagen_url));
      container.classList.remove('d-none');
      if (dropzone) dropzone.classList.add('d-none');
    }
  }

  const title = document.getElementById('formTitle');
  if (title && item.nombre) title.textContent = `Modificar: ${String(item.nombre)}`;
}

async function loadArtisanOptions() {
  const field = document.getElementById('artesanoField');
  const select = document.getElementById('inputArtesanoId');
  if (!field || !select) return;
  const user = getUser();
  if (!user || user.rol !== 'admin') return;
  try {
    const res = await fetch(ARTISANS_URL);
    if (!res.ok) return;
    const json = await res.json();
    const artisans = json && json.exito === true && Array.isArray(json.datos) ? json.datos : [];
    artisans.forEach((artisan) => {
      const opt = document.createElement('option');
      opt.value = String(artisan.id);
      opt.textContent = `@${artisan.username} (ID ${artisan.id})`;
      select.appendChild(opt);
    });
    field.classList.remove('d-none');
  } catch {
    /* El admin publica como sí mismo si falla la carga. */
  }
}

export function initFormularioCreacion() {
  const form = document.getElementById('creacionForm');
  if (!form) return;
  if (form.dataset.bound === '1') return;
  form.dataset.bound = '1';

  const editId = Number(form.getAttribute('data-edit-id') || document.getElementById('creacionId')?.value || 0);
  const isEditing = Number.isInteger(editId) && editId > 0;
  const submitBtn = form.querySelector('button[type="submit"]');

  loadArtisanOptions();

  if (isEditing) {
    fetch(`${DETALLE_URL}?id=${encodeURIComponent(String(editId))}`)
      .then((res) => {
        if (res.status === 401) {
          clearSession();
          window.location.replace('index.php');
          return null;
        }
        return res.json();
      })
      .then((json) => {
        if (json && json.exito === true && json.datos) {
          fillFormFromItem(json.datos);
        } else {
          showFormFeedback('error', 'No se pudo cargar la pieza para edición (quizá fue dada de baja).');
        }
      })
      .catch(() => {
        showFormFeedback('error', 'No se pudo cargar la pieza para edición. Revisa tu conexión.');
      });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const v = readFormValues();
    const { errors, precioCents, costoCents } = validateFormValues(v);
    if (errors.length > 0) {
      showFormFeedback('error', errors);
      return;
    }
    showFormFeedback('success', []);
    if (submitBtn) submitBtn.disabled = true;

    try {
      const fd = new FormData();
      fd.append('nombre', v.nombre);
      fd.append('categoria', v.categoria);
      fd.append('material', v.material);
      fd.append('dimensiones', v.dimensiones);
      fd.append('precio', String(precioCents));
      fd.append('costo_materiales', String(costoCents));
      fd.append('cantidad_stock', String(Number(v.stock)));
      fd.append('horas_tejido', v.horas === '' ? '0' : String(Number(v.horas)));
      fd.append('descripcion', v.descripcion);
      fd.append('es_sobre_encargo', v.esSobreEncargo ? '1' : '0');
      const user = getUser();
      if (user && user.rol === 'admin' && v.artesanoId !== '') {
        fd.append('artesano_id', v.artesanoId);
      }
      if (v.imagenFile) fd.append('imagen', v.imagenFile);
      if (isEditing) fd.append('id', String(editId));

      const res = await fetch(isEditing ? ACTUALIZAR_URL : CREAR_URL, {
        method: 'POST',
        headers: authHeaders(),
        body: fd,
      });
      const json = await parseJsonBody(res);

      if ((res.status === 201 || res.status === 200) && json && json.exito) {
        window.location.href = 'creaciones.php';
        return;
      }
      if (res.status === 401) {
        clearSession();
        window.location.replace('index.php');
        return;
      }
      if (res.status === 403) {
        showFormFeedback('error', 'No tienes permiso sobre esta pieza (solo su artesano autor o un admin puede modificarla).');
        return;
      }
      if (res.status === 422) {
        const detail = json && json.error
          ? ([json.error.mensaje].concat(json.error.detalles || []))
          : ['Revisa los datos del formulario.'];
        showFormFeedback('error', detail);
        return;
      }
      showFormFeedback('error', (json && json.error && json.error.mensaje) || `Error inesperado (HTTP ${res.status}).`);
    } catch {
      showFormFeedback('error', 'No se pudo contactar al servidor. Revisa tu conexión.');
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
}
