/**
 * Module: Orders (Panel de Pedidos Server-Driven — Subfase 4.4)
 * Single Responsibility: rejilla reactiva de pedidos contra
 * `GET /api/pedidos/index.php` (Bearer + scoping por rol), alta manual,
 * cambio de estado y cancelación idempotente con restitución visible.
 *
 * Seguridad DOM (H-004): marcado constante en
 * `<template id="pedidoCardTemplate">`; datos solo con `textContent`/DOM APIs.
 * Cero `innerHTML` con datos. WhatsApp: solo enlaces del servidor.
 * Moneda (R-06): centavos enteros en el wire, pesos solo para mostrar.
 */

import { getToken, getUser, isAuthenticated, clearSession } from './auth.js';
import { formatPesos, pesosToCents } from './currency.js';
import { setIconText } from './dom-safe.js';

const INDEX_URL = '/api/creaciones/mias.php';
const PEDIDOS_URL = '/api/pedidos/index.php';
const CREAR_URL = '/api/pedidos/crear.php';
const ESTADO_URL = '/api/pedidos/cambiar-estado.php';
const CANCELAR_URL = '/api/pedidos/cancelar.php';
const PAGE_LIMIT = 20;
const GENERIC_FALLBACK = 'assets/svg/piezas/ovillo-generico.svg';

const state = {
  search: '',
  status: 'all',
  pago: 'all',
  page: 1,
  seq: 0,
};

let grid = null;
let template = null;
let kpiSeq = 0;
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
  if (state.status !== 'all') q.set('estado_pedido', state.status);
  if (state.pago !== 'all') q.set('estado_pago', state.pago);
  q.set('pagina', String(state.page));
  q.set('limite', String(PAGE_LIMIT));
  return q;
}

function showLoading(show) {
  const el = document.getElementById('ordersLoadingState');
  if (el) el.style.display = show ? '' : 'none';
}

function showEmptyState(show) {
  const empty = document.getElementById('emptyOrdersGrid');
  if (!empty) {
    if (grid) grid.classList.toggle('d-none', show);
    return;
  }
  empty.classList.toggle('d-none', !show);
  if (grid) grid.classList.toggle('d-none', show);
}

function formatMoneyFromCents(cents) {
  const pesos = Number(cents) / 100;
  return `$${pesos.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

async function fetchSummary() {
  const totalEl = document.getElementById('kpiOrdersTotal');
  const pendEl = document.getElementById('kpiOrdersPendientes');
  const procEl = document.getElementById('kpiOrdersProceso');
  const ingEl = document.getElementById('kpiOrdersIngresos');
  if (!totalEl && !pendEl && !procEl && !ingEl) return;
  const seq = ++kpiSeq;

  try {
    const res = await fetch(`${PEDIDOS_URL}?resumen=1`, { headers: authHeaders() });
    if (res.status === 401) {
      clearSession();
      return;
    }
    if (!res.ok) return;
    const json = await res.json();
    if (seq !== kpiSeq) return;
    if (!json || json.exito !== true || !json.datos) return;

    const s = json.datos;
    if (totalEl) totalEl.textContent = String(Number(s.total) || 0);
    if (pendEl) pendEl.textContent = String(Number(s.pendientes) || 0);
    if (procEl) procEl.textContent = String(Number(s.proceso) || 0);
    if (ingEl) ingEl.textContent = formatMoneyFromCents(Number(s.ingresos_centavos) || 0);

    const counts = {
      countFilterAll: Number(s.total) || 0,
      countFilterPendiente: Number(s.pendientes) || 0,
      countFilterProceso: Number(s.proceso) || 0,
      countFilterEntregado: Number(s.entregados) || 0,
      countFilterCancelado: Number(s.cancelados) || 0,
    };
    Object.entries(counts).forEach(([id, value]) => {
      const el = document.getElementById(id);
      if (el) el.textContent = String(value);
    });
  } catch {
    /* KPIs y conteos conservan su último valor ante un fallo de red. */
  }
}

function paymentBadge(container, estadoPago) {
  if (!container) return;
  container.replaceChildren();
  const badge = document.createElement('span');
  const icon = document.createElement('i');
  badge.appendChild(icon);
  if (estadoPago === 'Liquidado') {
    badge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace';
    icon.className = 'bi bi-check-all me-1';
    badge.appendChild(document.createTextNode('Liquidado'));
  } else if (estadoPago === 'Anticipo 50%') {
    badge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace';
    icon.className = 'bi bi-coin me-1';
    badge.appendChild(document.createTextNode('Anticipo 50%'));
  } else {
    badge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace';
    icon.className = 'bi bi-clock-history me-1';
    badge.appendChild(document.createTextNode('Pendiente'));
  }
  badge.style.fontSize = '0.72rem';
  container.appendChild(badge);
}

function statusBadge(container, estado) {
  if (!container) return;
  container.replaceChildren();
  const badge = document.createElement('span');
  const icon = document.createElement('i');
  badge.appendChild(icon);
  const base = 'px-2.5 py-1.5 rounded-pill font-monospace order-status-badge ';
  if (estado === 'En Proceso') {
    badge.className = `badge badge-order-proceso ${base}`;
    icon.className = 'bi bi-gear-wide-connected me-1';
  } else if (estado === 'Pendiente') {
    badge.className = `badge badge-order-pendiente ${base}`;
    icon.className = 'bi bi-hourglass-split me-1';
  } else if (estado === 'Entregado') {
    badge.className = `badge badge-order-entregado ${base}`;
    icon.className = 'bi bi-check2 text-success me-1';
  } else {
    badge.className = `badge badge-order-cancelado ${base}`;
    icon.className = 'bi bi-x-circle me-1';
  }
  badge.appendChild(document.createTextNode(estado));
  container.appendChild(badge);
}

function orderImageSources(item) {
  const sources = [];
  const url = item.creacion && item.creacion.imagen_url ? String(item.creacion.imagen_url) : '';
  if (url) sources.push(url);
  sources.push(GENERIC_FALLBACK);
  return sources;
}

function renderCard(item) {
  const node = template.content.cloneNode(true);
  const part = (name) => node.querySelector(`[data-part="${name}"]`);
  const id = String(item.id);
  const cantidad = Number(item.cantidad) || 0;
  const creacion = item.creacion || {};
  const estado = String(item.estado_pedido || 'Pendiente');
  const isCancelled = estado === 'Cancelado';

  itemCache.set(id, item);

  part('codigo').textContent = `#${id}`;
  part('fecha').textContent = item.fecha_entrega ? String(item.fecha_entrega) : 'Sin fecha';

  const img = part('photo');
  const sources = orderImageSources(item);
  img.setAttribute('src', sources[0]);
  img.setAttribute('alt', String(creacion.nombre || 'Pieza del pedido'));
  img.setAttribute('loading', 'lazy');
  img.setAttribute('decoding', 'async');
  img.setAttribute('width', '120');
  img.setAttribute('height', '90');
  let attempt = 0;
  img.addEventListener('error', () => {
    attempt += 1;
    if (attempt < sources.length) img.setAttribute('src', sources[attempt]);
    else img.classList.add('d-none');
  });

  part('nombre').textContent = String(creacion.nombre || '');
  part('nombre').setAttribute('title', String(creacion.nombre || ''));
  const catEl = part('categoria');
  if (catEl) catEl.style.display = 'none';
  const dimEl = part('dimensiones');
  if (dimEl && dimEl.parentElement) dimEl.parentElement.style.display = 'none';
  const qtyEl = part('qty');
  qtyEl.replaceChildren();
  const qtyIcon = document.createElement('i');
  qtyIcon.className = 'bi bi-box-seam me-1';
  qtyEl.appendChild(qtyIcon);
  qtyEl.appendChild(document.createTextNode(`${cantidad} ${cantidad === 1 ? 'unidad' : 'unidades'}`));

  part('clienteNombre').textContent = String(item.cliente_nombre || '');
  const waLink = part('waLink');
  // Panel del artesano: contactar al COMPRADOR (fallback al enlace legado).
  const buyerWa = (typeof item.enlace_whatsapp_comprador === 'string' && item.enlace_whatsapp_comprador.trim() !== '')
    ? item.enlace_whatsapp_comprador.trim()
    : (typeof item.enlace_whatsapp === 'string' ? item.enlace_whatsapp : '');
  if (buyerWa.trim() !== '') {
    waLink.setAttribute('href', String(buyerWa));
    waLink.classList.remove('d-none');
  } else {
    waLink.classList.add('d-none');
  }
  part('clienteContacto').textContent = String(item.cliente_contacto || '');
  part('precioTotal').textContent = String(item.precio_final_formateado || formatMoneyFromCents(item.precio_final || 0));

  paymentBadge(part('pagoBadge'), String(item.estado_pago || 'Pendiente'));

  const notas = String(item.notas || '').trim();
  const notasBox = part('notasBox');
  if (notas !== '') {
    notasBox.classList.remove('d-none');
    part('notas').textContent = `“${notas}”`;
    notasBox.setAttribute('title', notas);
  } else {
    notasBox.classList.add('d-none');
  }

  statusBadge(part('estadoBadge'), estado);

  const inspectBtn = part('inspectBtn');
  inspectBtn.setAttribute('data-id', id);

  node.querySelectorAll('.btn-change-order-status').forEach((btn) => {
    btn.setAttribute('data-id', id);
    if (isCancelled) btn.classList.add('disabled');
  });
  const cancelBtn = node.querySelector('.btn-trigger-cancel-order');
  if (cancelBtn) {
    cancelBtn.setAttribute('data-id', id);
    cancelBtn.setAttribute('data-qty', String(cantidad));
    cancelBtn.setAttribute('data-product', String(creacion.nombre || ''));
    if (isCancelled) cancelBtn.classList.add('d-none');
  }

  const col = node.querySelector('[data-part="cardCol"]');
  if (col) {
    col.id = `orderCol_${id}`;
    col.setAttribute('data-id', id);
    col.setAttribute('data-estado', estado);
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
  const totalItems = Number(pag.total_items) || 0;
  const limit = Number(pag.limite) || PAGE_LIMIT;

  const fromEl = document.getElementById('pedidosShowingFrom');
  const toEl = document.getElementById('pedidosShowingTo');
  const totalEl = document.getElementById('pedidosTotalCount');
  if (fromEl) fromEl.textContent = String(totalItems === 0 ? 0 : (current - 1) * limit + 1);
  if (toEl) toEl.textContent = String(Math.min(current * limit, totalItems));
  if (totalEl) totalEl.textContent = String(totalItems);

  const nav = document.getElementById('pedidosPaginationNav');
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
    const res = await fetch(`${PEDIDOS_URL}?${buildQuery()}`, { headers: authHeaders() });
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
    showLoading(false);
    showEmptyState(items.length === 0);
    fetchSummary();
  } catch {
    if (seq !== state.seq) return;
    showLoading(false);
    showEmptyState(true);
  }
}

function resetFilters(searchInput) {
  state.search = '';
  state.status = 'all';
  state.pago = 'all';
  state.page = 1;
  if (searchInput) searchInput.value = '';
  document.querySelectorAll('.filter-order-btn').forEach((btn) => {
    btn.classList.toggle('active', btn.getAttribute('data-status') === 'all');
  });
  fetchPage();
}

async function postJson(url, payload) {
  const res = await fetch(url, {
    method: 'POST',
    headers: authHeaders({ 'Content-Type': 'application/json' }),
    body: JSON.stringify(payload),
  });
  return { status: res.status, json: await parseJsonBody(res) };
}

function hideModalById(modalId) {
  const modalEl = document.getElementById(modalId);
  if (modalEl && window.bootstrap) {
    const instance = window.bootstrap.Modal.getInstance(modalEl);
    if (instance) instance.hide();
  }
}

function fillInspectModal(item) {
  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };
  set('inspectOrderId', `#${item.id}`);
  set('inspectCliente', String(item.cliente_nombre || ''));
  set('inspectContacto', String(item.cliente_contacto || ''));
  paymentBadge(document.getElementById('inspectEstadoPago'), String(item.estado_pago || 'Pendiente'));
  set('inspectProducto', String((item.creacion && item.creacion.nombre) || ''));
  set('inspectCantidad', `${Number(item.cantidad) || 0} u.`);
  set('inspectTotal', String(item.precio_final_formateado || ''));
  set('inspectFecha', item.fecha_entrega ? String(item.fecha_entrega) : 'Sin fecha');
  set('inspectNotas', String(item.notas || '').trim() !== '' ? String(item.notas) : 'Sin notas especiales.');
}

function openInspectModal(item) {
  fillInspectModal(item);
  const modalEl = document.getElementById('modalInspeccionarPedido');
  if (modalEl && window.bootstrap) {
    new window.bootstrap.Modal(modalEl).show();
  }
}

function bindCancelConfirm() {
  const confirmBtn = document.getElementById('btnConfirmarCancelacionPedido');
  if (!confirmBtn || confirmBtn.dataset.bound === '1') return;
  confirmBtn.dataset.bound = '1';
  confirmBtn.addEventListener('click', async () => {
    const id = confirmBtn.getAttribute('data-id');
    if (!id) return;
    confirmBtn.disabled = true;
    try {
      const { status, json } = await postJson(CANCELAR_URL, { id: Number(id) });
      if (status === 401) clearSession();
      if ((status === 200 && json && json.exito) || status === 409) {
        hideModalById('modalCancelarPedido');
        await fetchPage();
      }
    } finally {
      confirmBtn.disabled = false;
    }
  });
}

async function loadCreacionOptions() {
  const select = document.getElementById('manualCreacionSelect');
  if (!select) return;
  try {
    const res = await fetch(`${INDEX_URL}?estado=activas&limite=48&orden=recientes`, { headers: authHeaders() });
    if (!res.ok) return;
    const json = await res.json();
    const items = json && json.exito === true && Array.isArray(json.datos) ? json.datos : [];
    select.replaceChildren();
    items.forEach((item) => {
      const opt = document.createElement('option');
      opt.value = String(item.id);
      const stock = Number(item.cantidad_stock) || 0;
      const onDemand = Number(item.es_sobre_encargo) === 1;
      opt.setAttribute('data-precio-cents', String(item.precio_centavos || 0));
      opt.setAttribute('data-stock', String(stock));
      opt.setAttribute('data-on-demand', onDemand ? '1' : '0');
      opt.textContent = `${item.nombre} — ${item.precio_formateado} (${onDemand ? 'Bajo encargo' : `Stock: ${stock}`})`;
      select.appendChild(opt);
    });
    updateManualTotal();
  } catch {
    /* El select conserva su estado inicial. */
  }
}

function updateManualTotal() {
  const select = document.getElementById('manualCreacionSelect');
  const qtyEl = document.getElementById('manualCantidad');
  const totalEl = document.getElementById('manualTotalDisplay');
  if (!select || !qtyEl || !totalEl) return;
  const opt = select.selectedOptions && select.selectedOptions[0];
  const cents = opt ? Number(opt.getAttribute('data-precio-cents')) || 0 : 0;
  const qty = Math.max(1, parseInt(qtyEl.value, 10) || 1);
  totalEl.textContent = formatPesos((cents * qty) / 100);
}

function showManualAlert(messages) {
  const box = document.getElementById('nuevoPedidoAlert');
  if (!box) return;
  box.replaceChildren();
  const list = Array.isArray(messages) ? messages : [messages];
  if (list.length === 0 || (list.length === 1 && !list[0])) {
    box.classList.add('d-none');
    return;
  }
  list.forEach((message) => {
    const div = document.createElement('div');
    div.textContent = String(message);
    box.appendChild(div);
  });
  box.classList.remove('d-none');
}

function bindManualForm() {
  const form = document.getElementById('formNuevoPedido');
  if (!form || form.dataset.bound === '1') return;
  form.dataset.bound = '1';

  const select = document.getElementById('manualCreacionSelect');
  const qtyEl = document.getElementById('manualCantidad');
  if (select) select.addEventListener('change', updateManualTotal);
  if (qtyEl) qtyEl.addEventListener('input', updateManualTotal);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const nombreEl = document.getElementById('manualClienteNombre');
    const contactoEl = document.getElementById('manualClienteContacto');
    const fechaEl = document.getElementById('manualFechaEntrega');
    const pagoEl = document.getElementById('manualEstadoPago');
    const pedidoEl = document.getElementById('manualEstadoPedido');
    const notasEl = document.getElementById('manualNotas');

    const payload = {
      creacion_id: Number(select ? select.value : 0),
      cliente_nombre: nombreEl ? nombreEl.value.trim() : '',
      cliente_contacto: contactoEl ? contactoEl.value.trim() : '',
      cantidad: qtyEl ? Math.max(1, parseInt(qtyEl.value, 10) || 1) : 1,
      fecha_entrega: fechaEl && fechaEl.value !== '' ? fechaEl.value : null,
      estado_pedido: pedidoEl ? pedidoEl.value : 'Pendiente',
      estado_pago: pagoEl ? pagoEl.value : 'Pendiente',
      notas: notasEl && notasEl.value.trim() !== '' ? notasEl.value.trim() : null,
    };

    const errors = [];
    if (!(payload.creacion_id > 0)) errors.push('Elige una pieza del catálogo.');
    if ([...payload.cliente_nombre].length < 2 || [...payload.cliente_nombre].length > 100) {
      errors.push('Cliente: entre 2 y 100 caracteres.');
    }
    if ([...payload.cliente_contacto].length > 50) errors.push('Contacto: máximo 50 caracteres.');
    if (payload.fecha_entrega !== null && !/^\d{4}-\d{2}-\d{2}$/.test(payload.fecha_entrega)) {
      errors.push('Fecha: formato YYYY-MM-DD.');
    }
    if (errors.length > 0) {
      showManualAlert(errors);
      return;
    }
    showManualAlert([]);

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;
    try {
      const { status, json } = await postJson(CREAR_URL, payload);
      if (status === 401) {
        clearSession();
        return;
      }
      if (status === 201 && json && json.exito) {
        hideModalById('modalNuevoPedido');
        form.reset();
        await fetchPage();
        return;
      }
      if (status === 403) {
        showManualAlert('No tienes permiso sobre esa pieza (solo su artesano autor o un admin).');
        return;
      }
      const detail = json && json.error
        ? ([json.error.mensaje].concat(json.error.detalles || []))
        : [`No se pudo registrar (HTTP ${status}).`];
      showManualAlert(detail);
    } catch {
      showManualAlert('No se pudo contactar al servidor. Revisa tu conexión.');
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
}

export function initOrders() {
  grid = document.getElementById('ordersGrid');
  template = document.getElementById('pedidoCardTemplate');
  if (!grid || !template) return;

  const searchInput = document.getElementById('searchOrdersInput');
  const debouncedSearch = debounce(() => {
    state.search = searchInput ? searchInput.value.trim() : '';
    state.page = 1;
    fetchPage();
  }, 250);
  if (searchInput) searchInput.addEventListener('input', debouncedSearch);

  document.querySelectorAll('.filter-order-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-order-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      state.status = btn.getAttribute('data-status') || 'all';
      state.page = 1;
      fetchPage();
    });
  });

  grid.addEventListener('click', async (e) => {
    const statusBtn = e.target.closest('.btn-change-order-status');
    if (statusBtn && !statusBtn.classList.contains('disabled')) {
      const id = statusBtn.getAttribute('data-id');
      const target = statusBtn.getAttribute('data-target-status');
      if (!id || !target) return;
      statusBtn.disabled = true;
      try {
        const { status, json } = await postJson(ESTADO_URL, { id: Number(id), estado_pedido: target });
        if (status === 401) clearSession();
        if ((status === 200 && json && json.exito) || status === 409) {
          await fetchPage();
        }
      } finally {
        statusBtn.disabled = false;
      }
      return;
    }

    const cancelBtn = e.target.closest('.btn-trigger-cancel-order');
    if (cancelBtn) {
      const id = cancelBtn.getAttribute('data-id') || '';
      const qty = cancelBtn.getAttribute('data-qty') || '1';
      const product = cancelBtn.getAttribute('data-product') || 'esta pieza';
      const idSpan = document.getElementById('cancelModalOrderId');
      const unitsSpan = document.getElementById('cancelStockRestitutionUnits');
      const productSpan = document.getElementById('cancelStockRestitutionProduct');
      if (idSpan) idSpan.textContent = `#${id}`;
      if (unitsSpan) unitsSpan.textContent = `+${qty} unidad(es)`;
      if (productSpan) productSpan.textContent = product;
      const confirmBtn = document.getElementById('btnConfirmarCancelacionPedido');
      if (confirmBtn) confirmBtn.setAttribute('data-id', id);
    }

    const inspectBtn = e.target.closest('.btn-inspect-order');
    if (inspectBtn) {
      const item = itemCache.get(String(inspectBtn.getAttribute('data-id')));
      if (item) openInspectModal(item);
    }
  });

  bindCancelConfirm();
  bindManualForm();
  loadCreacionOptions();
  fetchPage();
}
