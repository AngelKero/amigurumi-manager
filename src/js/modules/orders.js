/**
 * Module: Orders (Gestión de Pedidos, Inspección, Filtros y Encargos Manuales)
 * Algodón Nórdico Design System
 * Responsabilidad: Control del ciclo de vida de pedidos mediante Cards responsivas 3x/2x,
 * filtrado reactivo textil, métricas dinámicas y registro de nuevos encargos.
 */

import { formatPesos } from './currency.js';

export function initOrders() {
  let currentCancelOrderId = null;
  let nextOrderId = 3;

  function getPaymentBadge(estadoPago) {
    if (estadoPago === 'Liquidado') {
      return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.72rem;"><i class="bi bi-check-all me-1"></i>Liquidado</span>';
    } else if (estadoPago === 'Anticipo 50%') {
      return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.72rem;"><i class="bi bi-coin me-1"></i>Anticipo 50%</span>';
    } else {
      return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace" style="font-size: 0.72rem;"><i class="bi bi-clock-history me-1"></i>Pendiente</span>';
    }
  }

  // 1. Modales de Cancelación con Salvaguarda de Restitución de Stock [QW-2]
  const cancelModalIdSpan = document.getElementById('cancelModalOrderId') || document.getElementById('cancelOrderIdSpan');
  const cancelStockUnitsSpan = document.getElementById('cancelStockRestitutionUnits') || document.getElementById('cancelStockUnitsSpan');
  const cancelProductNameSpan = document.getElementById('cancelStockRestitutionProduct') || document.getElementById('cancelProductNameSpan');
  const btnConfirmCancel = document.getElementById('btnConfirmarCancelacionPedido');

  function bindCancelButtons() {
    document.querySelectorAll('.btn-cancel-order, .btn-trigger-cancel-order').forEach(btn => {
      btn.onclick = (e) => {
        e.preventDefault();
        currentCancelOrderId = btn.getAttribute('data-order-id') || '#1';
        const qty = btn.getAttribute('data-qty') || '1';
        const product = btn.getAttribute('data-product') || 'Dragón Ignis';

        if (cancelModalIdSpan) cancelModalIdSpan.textContent = currentCancelOrderId;
        if (cancelStockUnitsSpan) cancelStockUnitsSpan.textContent = `+${qty} unidad(es)`;
        if (cancelProductNameSpan) cancelProductNameSpan.textContent = product;
      };
    });
  }
  bindCancelButtons();

  if (btnConfirmCancel) {
    btnConfirmCancel.addEventListener('click', () => {
      if (currentCancelOrderId) {
        updateOrderStatus(currentCancelOrderId, 'Cancelado');
        const modalEl = document.getElementById('modalCancelarPedido');
        if (modalEl && window.bootstrap) {
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();
        }
      }
    });
  }

  // 2. Modal de Inspección Técnica
  function bindInspectButtons() {
    const inspectButtons = document.querySelectorAll('.btn-inspect-order');
    const inspectId = document.getElementById('inspectOrderId');
    const inspectCliente = document.getElementById('inspectCliente');
    const inspectContacto = document.getElementById('inspectContacto');
    const inspectEstadoPago = document.getElementById('inspectEstadoPago');
    const inspectProducto = document.getElementById('inspectProducto');
    const inspectCantidad = document.getElementById('inspectCantidad');
    const inspectTotal = document.getElementById('inspectTotal');
    const inspectFecha = document.getElementById('inspectFecha');
    const inspectNotas = document.getElementById('inspectNotas');

    inspectButtons.forEach(btn => {
      btn.onclick = () => {
        if (inspectId) inspectId.textContent = btn.getAttribute('data-order-id') || '#1';
        if (inspectCliente) inspectCliente.textContent = btn.getAttribute('data-cliente') || 'Mariana Gómez';
        if (inspectContacto) {
          const tel = btn.getAttribute('data-contacto') || '+52 55 4892 1039';
          inspectContacto.innerHTML = `<i class="bi bi-whatsapp text-success me-1"></i>${tel}`;
        }
        if (inspectEstadoPago) {
          const ep = btn.getAttribute('data-estado-pago') || 'Pendiente';
          inspectEstadoPago.innerHTML = getPaymentBadge(ep);
        }
        if (inspectProducto) inspectProducto.textContent = btn.getAttribute('data-product') || 'Dragón Ignis';
        if (inspectCantidad) inspectCantidad.textContent = `${btn.getAttribute('data-qty') || 1} u.`;
        if (inspectTotal) inspectTotal.textContent = btn.getAttribute('data-total') || '$450.00 MXN';
        if (inspectFecha) inspectFecha.textContent = btn.getAttribute('data-fecha') || '2026-09-24';
        if (inspectNotas) inspectNotas.textContent = btn.getAttribute('data-notes') || 'Sin notas especiales.';
      };
    });
  }
  bindInspectButtons();

  // 3. Configuración de Badges de Estado
  function getBadgeConfig(status) {
    switch (status) {
      case 'Pendiente':
        return {
          className: 'badge badge-order-pendiente px-2.5 py-1.5 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-hourglass-split me-1"></i>Pendiente'
        };
      case 'En Proceso':
        return {
          className: 'badge badge-order-proceso px-2.5 py-1.5 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-gear-wide-connected me-1"></i>En Proceso'
        };
      case 'Entregado':
        return {
          className: 'badge badge-order-entregado px-2.5 py-1.5 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-check2 text-success me-1"></i>Entregado'
        };
      case 'Cancelado':
        return {
          className: 'badge badge-order-cancelado px-2.5 py-1.5 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-x-circle me-1"></i>Cancelado'
        };
      default:
        return {
          className: 'badge bg-secondary px-2.5 py-1.5 rounded-pill font-monospace order-status-badge',
          html: status
        };
    }
  }

  function updateOrderStatus(orderId, targetStatus) {
    const orderCards = document.querySelectorAll(`#ordersGrid .order-card-col[data-order-id="${orderId}"]`);
    const config = getBadgeConfig(targetStatus);

    orderCards.forEach(card => {
      card.setAttribute('data-status', targetStatus);
      const badge = card.querySelector('.order-status-badge');
      if (badge) {
        badge.className = config.className;
        badge.innerHTML = config.html;
      }
    });

    recalculateKPIs();
    applyCurrentFilters();
  }

  function bindStatusChangeButtons() {
    document.querySelectorAll('.btn-change-order-status').forEach(btn => {
      btn.onclick = (e) => {
        e.preventDefault();
        const orderId = btn.getAttribute('data-order-id');
        const targetStatus = btn.getAttribute('data-target-status');
        if (orderId && targetStatus) {
          updateOrderStatus(orderId, targetStatus);
        }
      };
    });
  }
  bindStatusChangeButtons();

  // 4. Filtros Textiles por Estado y Búsqueda Reactiva
  let activeStatusFilter = 'all';
  const filterTabs = document.querySelectorAll('.filter-order-btn');
  const searchInput = document.getElementById('searchOrdersInput');

  filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      filterTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      activeStatusFilter = tab.getAttribute('data-status') || 'all';
      applyCurrentFilters();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      applyCurrentFilters();
    });
  }

  function applyCurrentFilters() {
    const term = (searchInput ? searchInput.value.toLowerCase().trim() : '');
    const orderCards = document.querySelectorAll('#ordersGrid .order-card-col');

    let visibleCount = 0;
    orderCards.forEach(card => {
      const status = card.getAttribute('data-status') || '';
      const searchText = (card.getAttribute('data-search') || '').toLowerCase();
      const matchesStatus = (activeStatusFilter === 'all' || status === activeStatusFilter);
      const matchesSearch = (!term || searchText.includes(term));

      if (matchesStatus && matchesSearch) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    const emptyGrid = document.getElementById('emptyOrdersGrid');
    if (emptyGrid) {
      emptyGrid.classList.toggle('d-none', visibleCount > 0);
    }
  }

  // 5. Recalcular Métricas y KPIs de Pedidos
  function recalculateKPIs() {
    const orderCards = document.querySelectorAll('#ordersGrid .order-card-col');
    let totalAll = 0;
    let pendingCount = 0;
    let processCount = 0;
    let deliveredCount = 0;
    let cancelledCount = 0;
    let totalRevenue = 0;

    orderCards.forEach(card => {
      totalAll++;
      const status = card.getAttribute('data-status');
      const price = parseFloat(card.getAttribute('data-price')) || 0;

      if (status === 'Pendiente') {
        pendingCount++;
        totalRevenue += price;
      } else if (status === 'En Proceso') {
        processCount++;
        totalRevenue += price;
      } else if (status === 'Entregado') {
        deliveredCount++;
        totalRevenue += price;
      } else if (status === 'Cancelado') {
        cancelledCount++;
      }
    });

    const kpiTotal = document.getElementById('kpiOrdersTotal');
    const kpiPendientes = document.getElementById('kpiOrdersPendientes');
    const kpiProceso = document.getElementById('kpiOrdersProceso');
    const kpiIngresos = document.getElementById('kpiOrdersIngresos');

    if (kpiTotal) kpiTotal.textContent = `${totalAll} órdenes`;
    if (kpiPendientes) kpiPendientes.textContent = `${pendingCount} pedidos`;
    if (kpiProceso) kpiProceso.textContent = `${processCount} activos`;
    if (kpiIngresos) kpiIngresos.textContent = formatPesos(totalRevenue);

    // Actualizar contadores en píldoras textiles de filtro
    const cAll = document.getElementById('countFilterAll');
    const cPendiente = document.getElementById('countFilterPendiente');
    const cProceso = document.getElementById('countFilterProceso');
    const cEntregado = document.getElementById('countFilterEntregado');
    const cCancelado = document.getElementById('countFilterCancelado');

    if (cAll) cAll.textContent = totalAll;
    if (cPendiente) cPendiente.textContent = pendingCount;
    if (cProceso) cProceso.textContent = processCount;
    if (cEntregado) cEntregado.textContent = deliveredCount;
    if (cCancelado) cCancelado.textContent = cancelledCount;
  }

  // 6. Modal Nuevo Encargo Manual
  const formNuevoPedido = document.getElementById('formNuevoPedido');
  const selectAmigurumi = document.getElementById('manualAmigurumiSelect');
  const inputCantidad = document.getElementById('manualCantidad');
  const displayTotal = document.getElementById('manualTotalDisplay');

  function updateManualTotal() {
    if (!selectAmigurumi || !inputCantidad || !displayTotal) return;
    const selectedOption = selectAmigurumi.options[selectAmigurumi.selectedIndex];
    const unitPrice = parseFloat(selectedOption?.getAttribute('data-price')) || 450;
    const qty = parseInt(inputCantidad.value, 10) || 1;
    const total = unitPrice * qty;
    displayTotal.textContent = formatPesos(total);
  }

  if (selectAmigurumi) selectAmigurumi.addEventListener('change', updateManualTotal);
  if (inputCantidad) inputCantidad.addEventListener('input', updateManualTotal);

  if (formNuevoPedido) {
    formNuevoPedido.addEventListener('submit', (e) => {
      e.preventDefault();

      const selectedOption = selectAmigurumi.options[selectAmigurumi.selectedIndex];
      const selectedVal = selectedOption.value;
      const productName = selectedOption.getAttribute('data-name') || 'Dragón Ignis';
      const unitPrice = parseFloat(selectedOption.getAttribute('data-price')) || 450;
      const clienteNombre = document.getElementById('manualClienteNombre').value.trim();
      const clienteContacto = document.getElementById('manualClienteContacto').value.trim();
      const estadoPago = document.getElementById('manualEstadoPago')?.value || 'Pendiente';
      const qty = parseInt(inputCantidad.value, 10) || 1;
      const fechaEntrega = document.getElementById('manualFechaEntrega').value;
      const notas = (document.getElementById('manualNotas')?.value || '').trim() || 'Encargo registrado manualmente por el artesano.';
      const totalOrder = unitPrice * qty;
      const formattedTotal = formatPesos(totalOrder);

      const newIdString = `#${nextOrderId++}`;
      const searchData = `${newIdString.replace('#', '')} ${clienteNombre} ${clienteContacto} ${productName}`.toLowerCase();
      const waDigits = clienteContacto.replace(/[^0-9]/g, '');

      // Determinar thumbnail SVG según la selección
      let svgThumbHtml = '';
      if (selectedVal === '1') {
        svgThumbHtml = '<img src="assets/svg/amigurumis/dragon-ignis.svg" width="54" height="54" alt="Dragón Ignis" style="max-width:100%; max-height:100%; object-fit:contain;">';
      } else if (selectedVal === '2') {
        svgThumbHtml = '<img src="assets/svg/amigurumis/mini-suculenta.svg" width="54" height="54" alt="Mini Suculenta" style="max-width:100%; max-height:100%; object-fit:contain;">';
      } else if (selectedVal === '3') {
        svgThumbHtml = '<img src="assets/svg/amigurumis/ajolote-pastel.svg" width="54" height="54" alt="Ajolote Pastel" style="max-width:100%; max-height:100%; object-fit:contain;">';
      } else {
        svgThumbHtml = '<img src="assets/svg/branding/isologo-medallon-garantia.svg" width="54" height="54" alt="Encargo Especial" style="max-width:100%; max-height:100%; object-fit:contain;">';
      }

      // Inyectar Card en el Grid Responsivo #ordersGrid
      const ordersGrid = document.getElementById('ordersGrid');
      if (ordersGrid) {
        const newCol = document.createElement('div');
        newCol.className = 'col order-card-col';
        newCol.id = `orderCol_${newIdString.replace('#', '')}`;
        newCol.setAttribute('data-order-id', newIdString);
        newCol.setAttribute('data-status', 'Pendiente');
        newCol.setAttribute('data-price', totalOrder);
        newCol.setAttribute('data-search', searchData);

        newCol.innerHTML = `
          <div class="card card-admin-pedido card-stitched h-100">
            <!-- Encabezado Superior de la Card -->
            <div class="card-order-header d-flex justify-content-between align-items-center">
              <span class="order-id-badge">${newIdString}</span>
              <span class="order-delivery-chip" title="Fecha pactada de entrega">
                <i class="bi bi-calendar3 text-primary"></i>
                <span class="font-monospace text-dark">${fechaEntrega}</span>
              </span>
            </div>

            <!-- Cuerpo de la Card -->
            <div class="card-order-body">
              <!-- Franja de Producto -->
              <div class="order-product-strip">
                <div class="order-product-thumb-frame">
                  ${svgThumbHtml}
                </div>
                <div class="order-product-info">
                  <h5 class="order-product-title text-truncate" title="${productName}">
                    ${productName}
                  </h5>
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-textile-tag" style="font-size: 0.68rem; padding: 0.15rem 0.45rem;">
                      Encargo Taller
                    </span>
                    <span class="order-qty-tag">
                      ${qty} ${qty > 1 ? 'unidades' : 'unidad'}
                    </span>
                  </div>
                  <small class="text-muted font-monospace" style="font-size: 0.73rem;">
                    <i class="bi bi-magic me-1"></i>Confección Artesanal
                  </small>
                </div>
              </div>

              <!-- Caja de Datos del Cliente con Acceso a WhatsApp -->
              <div class="order-client-box">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="order-client-label">Cliente / Destinatario</div>
                    <div class="order-client-name">${clienteNombre}</div>
                  </div>
                  <a href="https://wa.me/${waDigits}" target="_blank" class="btn-wa-pill" title="Contactar por WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp</span>
                  </a>
                </div>
                <div class="small text-muted font-monospace mt-1" style="font-size: 0.74rem;">
                  <i class="bi bi-telephone me-1"></i>${clienteContacto}
                </div>
              </div>

              <!-- Franja Financiera -->
              <div class="order-financial-strip">
                <div>
                  <span class="text-muted small d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em;">Total Acordado</span>
                  <span class="order-price-amount">${formattedTotal}</span>
                </div>
                <div>
                  ${getPaymentBadge(estadoPago)}
                </div>
              </div>

              <!-- Notas Especiales -->
              <div class="order-notes-preview text-truncate" title="${notas}">
                <i class="bi bi-chat-quote me-1 text-warning"></i>"${notas}"
              </div>
            </div>

            <!-- Footer: Estado y Acciones -->
            <div class="card-order-footer">
              <div>
                <span class="badge badge-order-pendiente px-2.5 py-1.5 rounded-pill font-monospace order-status-badge">
                  <i class="bi bi-hourglass-split me-1"></i>Pendiente
                </span>
              </div>

              <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-craft-outline btn-inspect-order"
                        data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
                        data-order-id="${newIdString}"
                        data-cliente="${clienteNombre}"
                        data-contacto="${clienteContacto}"
                        data-estado-pago="${estadoPago}"
                        data-product="${productName}"
                        data-qty="${qty}"
                        data-total="${formattedTotal}"
                        data-fecha="${fechaEntrega}"
                        data-notes="${notas}"
                        title="Ver ficha técnica y notas completas">
                  <i class="bi bi-eye"></i>
                </button>

                <div class="btn-group btn-group-sm">
                  <button class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar fase de confección">
                    Estado
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: var(--craft-radius-sm);">
                    <li>
                      <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="${newIdString}" data-target-status="Pendiente">
                        <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
                      </button>
                    </li>
                    <li>
                      <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="${newIdString}" data-target-status="En Proceso">
                        <i class="bi bi-gear-wide-connected text-primary me-2"></i>En Confección (Proceso)
                      </button>
                    </li>
                    <li>
                      <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="${newIdString}" data-target-status="Entregado">
                        <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
                      </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <a class="dropdown-item py-2 text-danger btn-trigger-cancel-order" href="#"
                         data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
                         data-order-id="${newIdString}"
                         data-qty="${qty}"
                         data-product="${productName}">
                        <i class="bi bi-x-circle me-2"></i>Cancelar (Restaura Stock)
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        `;

        ordersGrid.insertBefore(newCol, ordersGrid.firstChild);
      }

      // Re-bind listeners para los nuevos elementos insertados
      bindCancelButtons();
      bindInspectButtons();
      bindStatusChangeButtons();
      recalculateKPIs();
      applyCurrentFilters();

      formNuevoPedido.reset();
      updateManualTotal();

      const modalEl = document.getElementById('modalNuevoPedido');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
      }
    });
  }

  // Inicializar cálculo inicial
  recalculateKPIs();
}
