/**
 * Module: Orders (Gestión de Pedidos, Inspección, Filtros y Encargos Manuales)
 * Algodón Nórdico Design System
 * Responsabilidad: Control del ciclo de vida de pedidos, filtrado reactivo, KPIs dinámicos y registro de encargos.
 */

export function initOrders() {
  let currentCancelOrderId = null;
  let nextOrderId = 3;

  // 1. Modales de Inspección y Cancelación
  const cancelButtons = document.querySelectorAll('.btn-cancel-order, .btn-trigger-cancel-order');
  const cancelModalIdSpan = document.getElementById('cancelModalOrderId') || document.getElementById('cancelOrderIdSpan');
  const cancelStockUnitsSpan = document.getElementById('cancelStockRestitutionUnits') || document.getElementById('cancelStockUnitsSpan');
  const cancelProductNameSpan = document.getElementById('cancelStockRestitutionProduct') || document.getElementById('cancelProductNameSpan');
  const btnConfirmCancel = document.getElementById('btnConfirmarCancelacionPedido');

  function bindCancelButtons() {
    document.querySelectorAll('.btn-cancel-order, .btn-trigger-cancel-order').forEach(btn => {
      btn.onclick = () => {
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
    const inspectProducto = document.getElementById('inspectProducto');
    const inspectCantidad = document.getElementById('inspectCantidad');
    const inspectTotal = document.getElementById('inspectTotal');
    const inspectFecha = document.getElementById('inspectFecha');
    const inspectNotas = document.getElementById('inspectNotas');

    inspectButtons.forEach(btn => {
      btn.onclick = () => {
        if (inspectId) inspectId.textContent = btn.getAttribute('data-order-id') || '#1';
        if (inspectCliente) inspectCliente.textContent = btn.getAttribute('data-cliente') || 'Mariana Gómez';
        if (inspectProducto) inspectProducto.textContent = btn.getAttribute('data-product') || 'Dragón Ignis';
        if (inspectCantidad) inspectCantidad.textContent = `${btn.getAttribute('data-qty') || 1} u.`;
        if (inspectTotal) inspectTotal.textContent = btn.getAttribute('data-total') || '$450.00 MXN';
        if (inspectFecha) inspectFecha.textContent = btn.getAttribute('data-fecha') || '2026-09-24';
        if (inspectNotas) inspectNotas.textContent = btn.getAttribute('data-notes') || 'Sin notas especiales.';
      };
    });
  }
  bindInspectButtons();

  // 3. Cambio de Estado Interactivo
  function getBadgeConfig(status) {
    switch (status) {
      case 'Pendiente':
        return {
          className: 'badge badge-order-pendiente px-3 py-2 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-hourglass-split me-1"></i>Pendiente'
        };
      case 'En Proceso':
        return {
          className: 'badge badge-order-proceso px-3 py-2 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-gear-wide-connected me-1"></i>En Proceso'
        };
      case 'Entregado':
        return {
          className: 'badge badge-order-entregado px-3 py-2 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-check2 text-success me-1"></i>Entregado'
        };
      case 'Cancelado':
        return {
          className: 'badge badge-order-cancelado px-3 py-2 rounded-pill font-monospace order-status-badge',
          html: '<i class="bi bi-x-circle me-1"></i>Cancelado'
        };
      default:
        return {
          className: 'badge bg-secondary px-3 py-2 rounded-pill font-monospace order-status-badge',
          html: status
        };
    }
  }

  function updateOrderStatus(orderId, targetStatus) {
    const desktopRows = document.querySelectorAll(`#ordersTableBody tr[data-order-id="${orderId}"]`);
    const mobileCards = document.querySelectorAll(`#mobileOrdersContainer .order-card-mobile[data-order-id="${orderId}"]`);
    const config = getBadgeConfig(targetStatus);

    desktopRows.forEach(row => {
      row.setAttribute('data-status', targetStatus);
      const badge = row.querySelector('.order-status-badge');
      if (badge) {
        badge.className = config.className;
        badge.innerHTML = config.html;
      }
    });

    mobileCards.forEach(card => {
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

  // 4. Filtros por Píldora de Estado y Buscador
  let activeStatusFilter = 'all';
  const filterPills = document.querySelectorAll('.filter-order-btn');
  const searchInput = document.getElementById('searchOrdersInput');

  filterPills.forEach(pill => {
    pill.addEventListener('click', () => {
      filterPills.forEach(p => {
        p.classList.remove('btn-dark', 'active');
        p.classList.add('btn-outline-secondary');
      });
      pill.classList.remove('btn-outline-secondary');
      pill.classList.add('btn-dark', 'active');
      activeStatusFilter = pill.getAttribute('data-status') || 'all';
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
    const desktopRows = document.querySelectorAll('#ordersTableBody tr:not(#emptyOrdersDesktopRow)');
    const mobileCards = document.querySelectorAll('#mobileOrdersContainer .order-card-mobile');

    let visibleDesktop = 0;
    desktopRows.forEach(row => {
      const status = row.getAttribute('data-status') || '';
      const searchText = (row.getAttribute('data-search') || '').toLowerCase();
      const matchesStatus = (activeStatusFilter === 'all' || status === activeStatusFilter);
      const matchesSearch = (!term || searchText.includes(term));

      if (matchesStatus && matchesSearch) {
        row.style.display = '';
        visibleDesktop++;
      } else {
        row.style.display = 'none';
      }
    });

    let visibleMobile = 0;
    mobileCards.forEach(card => {
      const status = card.getAttribute('data-status') || '';
      const searchText = (card.getAttribute('data-search') || '').toLowerCase();
      const matchesStatus = (activeStatusFilter === 'all' || status === activeStatusFilter);
      const matchesSearch = (!term || searchText.includes(term));

      if (matchesStatus && matchesSearch) {
        card.style.display = '';
        visibleMobile++;
      } else {
        card.style.display = 'none';
      }
    });

    const emptyDesktopRow = document.getElementById('emptyOrdersDesktopRow');
    if (emptyDesktopRow) {
      emptyDesktopRow.classList.toggle('d-none', visibleDesktop > 0);
    }

    const emptyMobile = document.getElementById('emptyOrdersMobile');
    if (emptyMobile) {
      emptyMobile.classList.toggle('d-none', visibleMobile > 0);
    }
  }

  // 5. Recalcular Métricas y KPIs de Pedidos
  function recalculateKPIs() {
    const desktopRows = document.querySelectorAll('#ordersTableBody tr:not(#emptyOrdersDesktopRow)');
    let totalAll = 0;
    let totalActive = 0;
    let pendingCount = 0;
    let processCount = 0;
    let deliveredCount = 0;
    let cancelledCount = 0;
    let totalRevenue = 0;

    desktopRows.forEach(row => {
      totalAll++;
      const status = row.getAttribute('data-status');
      const price = parseFloat(row.getAttribute('data-price')) || 0;

      if (status === 'Pendiente') {
        pendingCount++;
        totalActive++;
        totalRevenue += price;
      } else if (status === 'En Proceso') {
        processCount++;
        totalActive++;
        totalRevenue += price;
      } else if (status === 'Entregado') {
        deliveredCount++;
        totalActive++;
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
    if (kpiIngresos) kpiIngresos.textContent = `$${totalRevenue.toLocaleString('es-MX', { minimumFractionDigits: 2 })} MXN`;

    // Actualizar contadores en píldoras de filtro
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
    displayTotal.textContent = `$${total.toLocaleString('es-MX', { minimumFractionDigits: 2 })} MXN`;
  }

  if (selectAmigurumi) selectAmigurumi.addEventListener('change', updateManualTotal);
  if (inputCantidad) inputCantidad.addEventListener('input', updateManualTotal);

  if (formNuevoPedido) {
    formNuevoPedido.addEventListener('submit', (e) => {
      e.preventDefault();

      const selectedOption = selectAmigurumi.options[selectAmigurumi.selectedIndex];
      const productName = selectedOption.getAttribute('data-name') || 'Dragón Ignis';
      const unitPrice = parseFloat(selectedOption.getAttribute('data-price')) || 450;
      const clienteNombre = document.getElementById('manualClienteNombre').value.trim();
      const clienteContacto = document.getElementById('manualClienteContacto').value.trim();
      const qty = parseInt(inputCantidad.value, 10) || 1;
      const fechaEntrega = document.getElementById('manualFechaEntrega').value;
      const notas = document.getElementById('manualNotas').value.trim() || 'Encargo registrado manualmente por el artesano.';
      const totalOrder = unitPrice * qty;
      const formattedTotal = `$${totalOrder.toFixed(2)} MXN`;

      const newIdString = `#${nextOrderId++}`;
      const searchData = `${newIdString.replace('#', '')} ${clienteNombre} ${clienteContacto} ${productName}`.toLowerCase();

      // Añadir fila a Desktop
      const tableBody = document.getElementById('ordersTableBody');
      if (tableBody) {
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-order-id', newIdString);
        newRow.setAttribute('data-status', 'Pendiente');
        newRow.setAttribute('data-price', totalOrder);
        newRow.setAttribute('data-search', searchData);
        newRow.innerHTML = `
          <td class="fw-bold font-monospace text-primary px-3">${newIdString}</td>
          <td>
            <div class="fw-bold text-dark order-client-name">${clienteNombre}</div>
            <div class="small text-muted font-monospace"><i class="bi bi-whatsapp text-success me-1"></i>${clienteContacto}</div>
          </td>
          <td>
            <div class="fw-semibold text-dark order-product-name">${productName}</div>
            <div class="small text-muted">Encargo Directo &bull; Artesanal</div>
          </td>
          <td class="text-center fw-bold">${qty}</td>
          <td>
            <span class="fw-bold text-dark font-monospace">$${totalOrder.toFixed(2)}</span>
            <small class="text-muted d-block" style="font-size: 0.72rem;">MXN</small>
          </td>
          <td>
            <span class="badge badge-order-pendiente px-3 py-2 rounded-pill font-monospace order-status-badge">
              <i class="bi bi-hourglass-split me-1"></i>Pendiente
            </span>
          </td>
          <td>
            <span class="font-monospace small text-dark"><i class="bi bi-calendar3 me-1"></i>${fechaEntrega}</span>
          </td>
          <td class="text-end pe-3">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-inspect-order" 
                      data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
                      data-order-id="${newIdString}"
                      data-cliente="${clienteNombre}"
                      data-product="${productName}"
                      data-qty="${qty}"
                      data-total="${formattedTotal}"
                      data-fecha="${fechaEntrega}"
                      data-notes="${notas}"
                      title="Ver notas y detalles completos">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Estado
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="${newIdString}" data-target-status="Pendiente">
                    <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="${newIdString}" data-target-status="En Proceso">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>Iniciar Confección (En Proceso)
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
                    <i class="bi bi-x-circle me-2"></i>Cancelar Pedido (Restaura Stock)
                  </a>
                </li>
              </ul>
            </div>
          </td>
        `;
        tableBody.insertBefore(newRow, tableBody.firstChild);
      }

      // Añadir tarjeta a Móvil
      const mobileContainer = document.getElementById('mobileOrdersContainer');
      if (mobileContainer) {
        const newCard = document.createElement('div');
        newCard.className = 'order-card-mobile card-stitched mb-3';
        newCard.setAttribute('data-order-id', newIdString);
        newCard.setAttribute('data-status', 'Pendiente');
        newCard.setAttribute('data-price', totalOrder);
        newCard.setAttribute('data-search', searchData);
        newCard.innerHTML = `
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold font-monospace text-primary fs-5">${newIdString}</span>
            <span class="badge badge-order-pendiente px-3 py-2 rounded-pill font-monospace order-status-badge">
              <i class="bi bi-hourglass-split me-1"></i>Pendiente
            </span>
          </div>
          <div class="mb-2">
            <h6 class="fw-bold mb-0 text-dark order-product-name">${productName}</h6>
            <small class="text-muted">Cliente: <strong class="order-client-name">${clienteNombre}</strong></small>
            <div class="small text-muted font-monospace"><i class="bi bi-whatsapp text-success me-1"></i>${clienteContacto}</div>
          </div>
          <div class="row g-2 py-2 my-2 border-top border-bottom bg-light rounded px-2">
            <div class="col-6">
              <small class="text-muted d-block" style="font-size: 0.72rem;">Cantidad:</small>
              <strong class="font-monospace">${qty} unidad(es)</strong>
            </div>
            <div class="col-6">
              <small class="text-muted d-block" style="font-size: 0.72rem;">Total:</small>
              <strong class="text-dark font-monospace fs-6">${formattedTotal}</strong>
            </div>
            <div class="col-12">
              <small class="text-muted d-block" style="font-size: 0.75rem;"><i class="bi bi-calendar3 me-1"></i>Compromiso: <strong class="font-monospace text-dark">${fechaEntrega}</strong></small>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm btn-inspect-order" 
                    data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
                    data-order-id="${newIdString}"
                    data-cliente="${clienteNombre}"
                    data-product="${productName}"
                    data-qty="${qty}"
                    data-total="${formattedTotal}"
                    data-fecha="${fechaEntrega}"
                    data-notes="${notas}">
              <i class="bi bi-eye me-1"></i> Notas
            </button>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Estado
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="${newIdString}" data-target-status="Pendiente">
                    <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="${newIdString}" data-target-status="En Proceso">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>Iniciar Confección
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
        `;
        mobileContainer.insertBefore(newCard, mobileContainer.firstChild);
      }

      // Re-vincular botones dinámicos
      bindCancelButtons();
      bindInspectButtons();
      bindStatusChangeButtons();

      // Recalcular y cerrar modal
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
