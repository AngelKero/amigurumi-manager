/**
 * Module: Orders (Gestión de Pedidos, Inspección y Restitución de Stock)
 * Single Responsibility: Población dinámica de modales de cancelación e inspección de pedidos.
 */

export function initOrders() {
  // Modal de Cancelación y Restitución Física de Stock [QW-2]
  const cancelButtons = document.querySelectorAll('.btn-cancel-order, .btn-trigger-cancel-order');
  const cancelModalIdSpan = document.getElementById('cancelModalOrderId') || document.getElementById('cancelOrderIdSpan');
  const cancelStockUnitsSpan = document.getElementById('cancelStockRestitutionUnits') || document.getElementById('cancelStockUnitsSpan');
  const cancelProductNameSpan = document.getElementById('cancelStockRestitutionProduct') || document.getElementById('cancelProductNameSpan');

  cancelButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const orderId = btn.getAttribute('data-order-id') || '#1';
      const qty = btn.getAttribute('data-qty') || '1';
      const product = btn.getAttribute('data-product') || 'Dragón Ignis';

      if (cancelModalIdSpan) cancelModalIdSpan.textContent = orderId;
      if (cancelStockUnitsSpan) cancelStockUnitsSpan.textContent = `+${qty} unidad(es)`;
      if (cancelProductNameSpan) cancelProductNameSpan.textContent = product;
    });
  });

  // Modal de Inspección Técnica de Encargos
  const inspectButtons = document.querySelectorAll('.btn-inspect-order');
  const inspectId = document.getElementById('inspectOrderId');
  const inspectCliente = document.getElementById('inspectCliente');
  const inspectProducto = document.getElementById('inspectProducto');
  const inspectCantidad = document.getElementById('inspectCantidad');
  const inspectTotal = document.getElementById('inspectTotal');
  const inspectFecha = document.getElementById('inspectFecha');
  const inspectNotas = document.getElementById('inspectNotas');

  inspectButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      if (inspectId) inspectId.textContent = btn.getAttribute('data-order-id') || '#1';
      if (inspectCliente) inspectCliente.textContent = btn.getAttribute('data-cliente') || 'Mariana Gómez';
      if (inspectProducto) inspectProducto.textContent = btn.getAttribute('data-product') || 'Dragón Ignis';
      if (inspectCantidad) inspectCantidad.textContent = `${btn.getAttribute('data-qty') || 1} u.`;
      if (inspectTotal) inspectTotal.textContent = btn.getAttribute('data-total') || '$450.00 MXN';
      if (inspectFecha) inspectFecha.textContent = btn.getAttribute('data-fecha') || '2026-09-24';
      if (inspectNotas) inspectNotas.textContent = btn.getAttribute('data-notes') || 'Sin notas especiales.';
    });
  });
}
