/**
 * Module: Catalog (Catálogo Reactivo Server-Driven — Subfase 4.2)
 * Algodón Nórdico Design System
 *
 * Responsabilidad única: consultar `GET /api/creaciones/index.php` con los filtros
 * del Explorador de Creaciones, re-renderizar la rejilla `#productCardGrid` y la
 * estación de paginación sin recargar la página.
 *
 * Seguridad DOM (H-004): todo el marcado artesanal vive constante en el
 * `<template id="catalogCardTemplate">` de la vista; los datos del servidor se
 * inyectan exclusivamente con `textContent`/DOM APIs. Cero interpolación en
 * `innerHTML` (solo marcado constante y contadores numéricos exentos).
 */

import { pesosToCents } from './currency.js';
import { isAuthenticated } from './auth.js';

const CATALOG_URL = '/api/creaciones/index.php';
const ARTISANS_URL = '/api/creaciones/artesanos.php';
const PAGE_LIMIT = 12;
const GENERIC_FALLBACK = 'assets/svg/piezas/ovillo-generico.svg';

let grid = null;
let template = null;

const state = {
  search: '',
  category: 'all',
  stock: 'all',
  priceMin: '',
  priceMax: '',
  artisan: 'all',
  sort: 'recent',
  page: 1,
  seq: 0,
};

function stockToEstadoStock(value) {
  if (value === 'in' || value === 'in_stock') return 'en_stock';
  if (value === 'on-demand' || value === 'bajo_encargo') return 'bajo_encargo';
  if (value === 'out' || value === 'out_stock') return 'agotados';
  return value;
}

function sortToOrden(value) {
  const map = {
    'price-asc': 'precio_asc',
    'price-desc': 'precio_desc',
    'name-asc': 'nombre_asc',
    'name-desc': 'nombre_desc',
    'stock-desc': 'stock_desc',
  };
  return map[value] || 'recientes';
}

function buildQuery() {
  const q = new URLSearchParams();
  if (String(state.search).trim() !== '') q.set('busqueda', state.search.trim());
  if (state.category !== 'all') q.set('categoria', state.category);
  if (state.stock !== 'all') q.set('estado_stock', stockToEstadoStock(state.stock));
  if (state.priceMin !== '' && !Number.isNaN(Number(state.priceMin))) {
    q.set('precio_min', String(pesosToCents(state.priceMin)));
  }
  if (state.priceMax !== '' && !Number.isNaN(Number(state.priceMax))) {
    q.set('precio_max', String(pesosToCents(state.priceMax)));
  }
  if (state.artisan !== 'all') q.set('artesano_id', String(state.artisan));
  q.set('orden', sortToOrden(state.sort));
  q.set('pagina', String(state.page));
  q.set('limite', String(PAGE_LIMIT));
  return q;
}

function debounce(fn, wait = 250) {
  let timer = null;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), wait);
  };
}

function showLoading(show) {
  const el = document.getElementById('catalogLoadingState');
  if (el) el.style.display = show ? '' : 'none';
}

function showEmptyState(show, opts = {}) {
  const empty = document.getElementById('emptyCatalogState');
  if (!empty) return;
  if (!show) {
    empty.classList.add('d-none');
    return;
  }
  const title = document.getElementById('emptyCatalogTitle');
  const message = document.getElementById('emptyCatalogMessage');
  if (title) title.textContent = opts.title || 'No se encontraron piezas artesanales';
  if (message) {
    message.textContent = opts.message || 'No hay ninguna creación en el catálogo que coincida con tu búsqueda o filtros actuales. Prueba a limpiar los filtros o buscar con otro término.';
  }
  empty.classList.remove('d-none');
}

function updateCounters(shownOnPage, totalItems) {
  const counted = document.getElementById('filterResultsCountText');
  if (counted) {
    counted.textContent = `${totalItems} ${totalItems === 1 ? 'pieza visible' : 'piezas visibles'}`;
  }
  const showing = document.getElementById('paginationShowingCount');
  const totalCount = document.getElementById('paginationTotalCount');
  if (showing) showing.textContent = String(shownOnPage);
  if (totalCount) totalCount.textContent = String(totalItems);
}

function setStockBadge(badge, stock, onDemand) {
  badge.replaceChildren();
  const icon = document.createElement('i');
  badge.appendChild(icon);
  if (onDemand) {
    badge.className = 'badge badge-textile-tag shadow-sm d-inline-flex align-items-center gap-1';
    badge.style.cssText = 'background-color: var(--craft-primary-subtle); color: var(--craft-primary); border: 1.5px dashed var(--craft-primary);';
    icon.className = 'bi bi-magic me-1';
    badge.appendChild(document.createTextNode('Bajo Encargo'));
  } else if (stock <= 0) {
    badge.className = 'badge badge-stock-out shadow-sm d-inline-flex align-items-center gap-1';
    icon.className = 'bi bi-dash-circle me-1';
    badge.appendChild(document.createTextNode('Agotado (0 disp.)'));
  } else {
    badge.className = 'badge badge-stock-in shadow-sm d-inline-flex align-items-center gap-1';
    icon.className = 'bi bi-check-circle-fill me-1';
    badge.appendChild(document.createTextNode(`En Stock (${stock} u.)`));
  }
}

function configureAction(button, label, onDemand, stock) {
  const icon = button.querySelector('i');
  if (onDemand) {
    button.classList.add('btn-craft-primary', 'btn-craft-stitched');
    button.classList.remove('btn-outline-secondary');
    button.disabled = false;
    button.setAttribute('data-bs-toggle', 'modal');
    button.setAttribute('data-bs-target', '#checkoutModal');
    button.title = 'Solicitar bajo encargo personalizado';
    label.textContent = 'Encargar';
    if (icon) icon.className = 'bi bi-magic me-1';
  } else if (stock <= 0) {
    button.classList.remove('btn-craft-primary', 'btn-craft-stitched');
    button.classList.add('btn-outline-secondary');
    button.disabled = true;
    button.removeAttribute('data-bs-toggle');
    button.removeAttribute('data-bs-target');
    button.title = 'Sin stock físico inmediato disponible';
    label.textContent = 'Agotado';
    if (icon) icon.className = 'bi bi-slash-circle me-1';
  } else {
    button.classList.add('btn-craft-primary', 'btn-craft-stitched');
    button.classList.remove('btn-outline-secondary');
    button.disabled = false;
    button.setAttribute('data-bs-toggle', 'modal');
    button.setAttribute('data-bs-target', '#checkoutModal');
    button.title = 'Comprar / reservar unidad';
    label.textContent = 'Comprar';
    if (icon) icon.className = 'bi bi-cart-plus me-1';
  }
}

function renderCard(item) {
  const node = template.content.cloneNode(true);
  const part = (name) => node.querySelector(`[data-part="${name}"]`);

  const detailUrl = `detalle.php?id=${encodeURIComponent(String(item.id))}`;
  const detailLink = part('detailLink');
  detailLink.setAttribute('href', detailUrl);

  const stock = Number(item.cantidad_stock) || 0;
  const onDemand = Number(item.es_sobre_encargo) === 1;
  setStockBadge(part('stockBadge'), stock, onDemand);

  const img = part('productImg');
  const sources = [];
  if (item.imagen_url) sources.push(String(item.imagen_url));
  if (item.imagen_fallback_svg) sources.push(String(item.imagen_fallback_svg));
  sources.push(GENERIC_FALLBACK);
  img.setAttribute('src', sources[0]);
  img.setAttribute('alt', String(item.nombre || 'Pieza artesanal'));
  let attempt = 0;
  img.addEventListener('error', () => {
    attempt += 1;
    if (attempt < sources.length) {
      img.setAttribute('src', sources[attempt]);
    } else {
      img.classList.add('d-none');
    }
  });

  part('category').textContent = String(item.categoria || '');
  part('dimensions').textContent = String(item.dimensiones || 'Estándar');

  const titleLink = part('titleLink');
  titleLink.setAttribute('href', detailUrl);
  titleLink.textContent = String(item.nombre || '');

  part('description').textContent = String(item.descripcion || '');

  part('price').textContent = String(item.precio_formateado || `$${Number(item.precio_centavos || 0) / 100}`);

  configureAction(part('actionButton'), part('actionLabel'), onDemand, stock);

  const artisan = (item.artesano && item.artesano.username) || item.artesano_username || '';
  part('artisanMeta').textContent = `ID: #${String(item.id)} • @${artisan}`;

  const editLink = part('editLink');
  editLink.setAttribute('href', `formulario.php?id=${encodeURIComponent(String(item.id))}`);

  const deleteButton = part('deleteButton');
  deleteButton.setAttribute('data-id', String(item.id));
  deleteButton.setAttribute('data-name', String(item.nombre || ''));

  return node;
}

function renderCards(items) {
  grid.replaceChildren();
  items.forEach((item) => grid.appendChild(renderCard(item)));

  const session = isAuthenticated();
  grid.querySelectorAll('.artisan-card-actions').forEach((el) => {
    el.classList.toggle('d-none', !session);
  });
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
  a.title = kind === 'prev' ? 'Página anterior' : 'Página siguiente';
  const i = document.createElement('i');
  i.className = kind === 'prev' ? 'bi bi-chevron-left' : 'bi bi-chevron-right';
  a.appendChild(i);
  if (enabled) {
    a.href = '#';
    a.addEventListener('click', (e) => {
      e.preventDefault();
      state.page = targetPage;
      fetchCatalog();
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
      fetchCatalog();
    });
  }
  li.appendChild(a);
  return li;
}

function renderPagination(pag) {
  const current = Number(pag.pagina_actual) || 1;
  const totalPages = Number(pag.total_paginas) || 1;

  const nav = document.getElementById('paginationNav');
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

async function fetchCatalog() {
  const seq = ++state.seq;
  if (grid.childElementCount === 0) showLoading(true);

  try {
    const res = await fetch(`${CATALOG_URL}?${buildQuery()}`);
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
      return fetchCatalog();
    }

    const items = Array.isArray(json.datos) ? json.datos : [];
    renderCards(items);
    renderPagination(pag);
    updateCounters(items.length, pag.total_items ?? 0);
    grid.dataset.total = String(pag.total_items ?? 0);
    showLoading(false);
    showEmptyState(Array.isArray(json.datos) && json.datos.length === 0);
  } catch (err) {
    if (seq !== state.seq) return;
    showLoading(false);
    showEmptyState(true, {
      title: 'No fue posible cargar el catálogo',
      message: err && err.message
        ? `Ocurrió un error al consultar el catálogo: ${err.message}. Prueba a restablecer los filtros o recargar la página.`
        : 'Ocurrió un error inesperado. Recarga la página e inténtalo de nuevo.',
    });
  }
}

async function loadArtisans() {
  const select = document.getElementById('filterArtisan');
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
  } catch (e) {
    /* El dropdown permanece con la opción "Todos los Artesanos". */
  }
}

function syncChipsFromCategory(category) {
  document.querySelectorAll('.btn-chip-textile').forEach((chip) => {
    chip.classList.toggle('active', chip.getAttribute('data-category') === category);
  });
}

function resetFilters(searchInput, filterCategory, filterStock, filterSort, filterPriceMin, filterPriceMax, filterArtisan) {
  state.search = '';
  state.category = 'all';
  state.stock = 'all';
  state.priceMin = '';
  state.priceMax = '';
  state.artisan = 'all';
  state.sort = 'recent';
  state.page = 1;

  if (searchInput) searchInput.value = '';
  if (filterCategory) filterCategory.value = 'all';
  if (filterStock) filterStock.value = 'all';
  if (filterSort) filterSort.value = 'recent';
  if (filterPriceMin) filterPriceMin.value = '';
  if (filterPriceMax) filterPriceMax.value = '';
  if (filterArtisan) filterArtisan.value = 'all';

  syncChipsFromCategory('all');
  fetchCatalog();
}

export function initCatalog() {
  grid = document.getElementById('productCardGrid');
  template = document.getElementById('catalogCardTemplate');

  const heroImg = document.getElementById('heroCraftedImg');
  if (heroImg) {
    heroImg.addEventListener('error', () => {
      heroImg.style.display = 'none';
      const fallback = document.getElementById('heroFallback');
      if (fallback) fallback.classList.remove('d-none');
    });
  }

  if (!grid || !template) return;

  const searchInput = document.getElementById('filterSearch');
  const filterCategory = document.getElementById('filterCategory');
  const filterStock = document.getElementById('filterStock');
  const filterSort = document.getElementById('filterSort');
  const filterPriceMin = document.getElementById('filterPriceMin');
  const filterPriceMax = document.getElementById('filterPriceMax');
  const filterArtisan = document.getElementById('filterArtisan');
  const btnClearFilters = document.getElementById('btnClearFilters');
  const btnResetEmpty = document.getElementById('btnResetFiltersEmpty');
  const chips = document.querySelectorAll('.btn-chip-textile');

  const debouncedSearch = debounce(() => {
    state.search = searchInput ? searchInput.value.trim() : '';
    state.page = 1;
    fetchCatalog();
  }, 250);

  const debouncedPrice = debounce(() => {
    state.priceMin = filterPriceMin ? filterPriceMin.value : '';
    state.priceMax = filterPriceMax ? filterPriceMax.value : '';
    state.page = 1;
    fetchCatalog();
  }, 250);

  if (searchInput) searchInput.addEventListener('input', debouncedSearch);

  if (filterCategory) {
    filterCategory.addEventListener('change', () => {
      state.category = filterCategory.value;
      state.page = 1;
      syncChipsFromCategory(state.category);
      fetchCatalog();
    });
  }

  chips.forEach((chip) => {
    chip.addEventListener('click', () => {
      const category = chip.getAttribute('data-category') || 'all';
      if (filterCategory) filterCategory.value = category;
      state.category = category;
      state.page = 1;
      syncChipsFromCategory(category);
      fetchCatalog();
    });
  });

  if (filterStock) {
    filterStock.addEventListener('change', () => {
      state.stock = filterStock.value;
      state.page = 1;
      fetchCatalog();
    });
  }

  if (filterSort) {
    filterSort.addEventListener('change', () => {
      state.sort = filterSort.value;
      state.page = 1;
      fetchCatalog();
    });
  }

  if (filterPriceMin) filterPriceMin.addEventListener('input', debouncedPrice);
  if (filterPriceMax) filterPriceMax.addEventListener('input', debouncedPrice);

  if (filterArtisan) {
    filterArtisan.addEventListener('change', () => {
      state.artisan = filterArtisan.value;
      state.page = 1;
      fetchCatalog();
    });
  }

  if (btnClearFilters) {
    btnClearFilters.addEventListener('click', () => resetFilters(searchInput, filterCategory, filterStock, filterSort, filterPriceMin, filterPriceMax, filterArtisan));
  }
  if (btnResetEmpty) {
    btnResetEmpty.addEventListener('click', () => resetFilters(searchInput, filterCategory, filterStock, filterSort, filterPriceMin, filterPriceMax, filterArtisan));
  }

  // Delegación del modal de eliminación (datos servidos por data-*)
  grid.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-card-delete');
    if (btn) {
      const name = btn.getAttribute('data-name') || 'esta pieza';
      const nameSpan = document.getElementById('deleteCreacionName') || document.getElementById('deleteAmigurumiName');
      if (nameSpan) nameSpan.textContent = name;
    }
  });

  loadArtisans();
  fetchCatalog();
}