/**
 * Module: Checkout (Stepper de Cantidad y Modal de Compra Pública)
 * Single Responsibility: Control de cantidad acotado a existencias físicas y cómputo de totales.
 */

import { formatPesos, parseCurrency } from './currency.js';

export function initCheckout() {
  const checkoutModal = document.getElementById('checkoutModal');
  if (!checkoutModal) return;

  const btnDec = document.getElementById('btnCheckoutDec');
  const btnInc = document.getElementById('btnCheckoutInc');
  const inputQty = document.getElementById('inputCheckoutQty');
  const displayTotal = document.getElementById('checkoutTotalDisplay');
  const unitPriceHidden = document.getElementById('checkoutUnitPrice');
  const availableStockHidden = document.getElementById('checkoutStockMax');

  function updateTotal() {
    if (!inputQty || !unitPriceHidden || !displayTotal) return;
    const qty = parseInt(inputQty.value, 10) || 1;
    const unitPrice = parseFloat(unitPriceHidden.value) || 450;
    const maxStock = parseInt(availableStockHidden ? availableStockHidden.value : 4, 10) || 4;

    // Habilitar o deshabilitar según límites [CR-2]
    if (btnDec) btnDec.disabled = qty <= 1;
    if (btnInc) btnInc.disabled = qty >= maxStock;

    const total = qty * unitPrice;
    displayTotal.textContent = formatPesos(total);
  }

  if (btnDec && btnInc && inputQty) {
    btnDec.addEventListener('click', () => {
      let current = parseInt(inputQty.value, 10) || 1;
      if (current > 1) {
        inputQty.value = current - 1;
        updateTotal();
      }
    });

    btnInc.addEventListener('click', () => {
      const maxStock = parseInt(availableStockHidden ? availableStockHidden.value : 4, 10) || 4;
      let current = parseInt(inputQty.value, 10) || 1;
      if (current < maxStock) {
        inputQty.value = current + 1;
        updateTotal();
      }
    });

    inputQty.addEventListener('change', updateTotal);
    updateTotal();
  }

  // Soporte para botones de compra directa en catálogo y detalle
  const buyButtons = document.querySelectorAll('.btn-buy-product, #btnComprarDetalle, [data-bs-target="#checkoutModal"]');
  buyButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.product-grid-item, .card-product-item');
      
      const modalTitle = document.getElementById('checkoutModalTitle');
      const modalProductName = document.getElementById('modalProductName');
      const modalUnitPriceDisplay = document.getElementById('modalUnitPriceDisplay');
      const modalStockBadge = document.getElementById('modalStockBadge');
      const checkoutStockNote = document.getElementById('checkoutStockNote');

      if (card) {
        // Obtenido de tarjeta de catálogo
        const name = card.getAttribute('data-name') || 'Amigurumi';
        const price = parseFloat(card.getAttribute('data-price') || '0');
        const stock = parseInt(card.getAttribute('data-stock') || '0', 10);

        if (modalTitle) modalTitle.innerHTML = `<i class="bi bi-bag-heart me-2 text-primary"></i>Solicitud de Pedido: ${name}`;
        if (modalProductName) modalProductName.textContent = name;
        if (modalUnitPriceDisplay) modalUnitPriceDisplay.textContent = `Precio Unitario: ${formatPesos(price)}`;
        if (unitPriceHidden) unitPriceHidden.value = price;
        if (availableStockHidden) availableStockHidden.value = stock;

        if (inputQty) inputQty.value = 1;

        if (modalStockBadge) {
          if (stock > 0) {
            modalStockBadge.className = 'badge badge-stock-in';
            modalStockBadge.textContent = `Stock: ${stock} disp.`;
          } else {
            modalStockBadge.className = 'badge badge-stock-out';
            modalStockBadge.textContent = 'Agotado (Bajo encargo)';
          }
        }

        if (checkoutStockNote) {
          checkoutStockNote.textContent = stock > 0 ? `Máx: ${stock} unidades en stock` : 'Confección bajo encargo especial';
        }
      } else {
        // Obtenido de la vista de detalle
        const detailTitle = document.getElementById('detalleTitle');
        const detailPrice = document.getElementById('detallePriceDisplay');
        const detailStock = document.getElementById('detalleStockBadge');

        if (detailTitle && modalProductName) {
          const name = detailTitle.textContent.trim();
          modalProductName.textContent = name;
          if (modalTitle) modalTitle.innerHTML = `<i class="bi bi-bag-heart me-2 text-primary"></i>Solicitud de Pedido: ${name}`;
        }
        if (detailPrice && unitPriceHidden) {
          const price = parseCurrency(detailPrice.textContent) || 450;
          unitPriceHidden.value = price;
          if (modalUnitPriceDisplay) modalUnitPriceDisplay.textContent = `Precio Unitario: ${formatPesos(price)}`;
        }
        if (detailStock && availableStockHidden) {
          const isOut = detailStock.classList.contains('badge-stock-out');
          const stock = isOut ? 0 : 4;
          availableStockHidden.value = isOut ? 10 : stock;
          if (modalStockBadge) {
            modalStockBadge.className = isOut ? 'badge badge-stock-out' : 'badge badge-stock-in';
            modalStockBadge.textContent = isOut ? 'Agotado (Bajo encargo)' : `Stock: ${stock} disp.`;
          }
          if (checkoutStockNote) {
            checkoutStockNote.textContent = isOut ? 'Confección bajo encargo especial' : `Máx: ${stock} unidades en stock`;
          }
        }
      }

      updateTotal();
    });
  });
}
