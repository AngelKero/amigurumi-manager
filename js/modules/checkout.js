/**
 * Module: Checkout (Stepper de Cantidad y Modal de Compra Pública)
 * Single Responsibility: Control de cantidad acotado a existencias físicas y cómputo de totales.
 */

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
    const qty = parseInt(inputQty.value) || 1;
    const unitPrice = parseFloat(unitPriceHidden.value) || 450;
    const maxStock = parseInt(availableStockHidden ? availableStockHidden.value : 4) || 4;

    // Habilitar o deshabilitar según límites [CR-2]
    if (btnDec) btnDec.disabled = qty <= 1;
    if (btnInc) btnInc.disabled = qty >= maxStock;

    const total = qty * unitPrice;
    displayTotal.textContent = `$${total.toFixed(2)} MXN`;
  }

  if (btnDec && btnInc && inputQty) {
    btnDec.addEventListener('click', () => {
      let current = parseInt(inputQty.value) || 1;
      if (current > 1) {
        inputQty.value = current - 1;
        updateTotal();
      }
    });

    btnInc.addEventListener('click', () => {
      const maxStock = parseInt(availableStockHidden ? availableStockHidden.value : 4) || 4;
      let current = parseInt(inputQty.value) || 1;
      if (current < maxStock) {
        inputQty.value = current + 1;
        updateTotal();
      }
    });

    inputQty.addEventListener('change', updateTotal);
    updateTotal();
  }

  // Soporte para botones de compra directa en catálogo
  const buyButtons = document.querySelectorAll('.btn-buy-product');
  buyButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const card = btn.closest('.card-product-item');
      if (!card) return;

      const name = card.getAttribute('data-name') || 'Amigurumi';
      const price = parseFloat(card.getAttribute('data-price') || '0');
      const stock = parseInt(card.getAttribute('data-stock') || '0');

      const modalTitle = document.getElementById('checkoutModalTitle');
      const modalProductName = document.getElementById('modalProductName');
      const modalUnitPriceDisplay = document.getElementById('modalUnitPriceDisplay');
      const modalStockBadge = document.getElementById('modalStockBadge');
      const checkoutStockNote = document.getElementById('checkoutStockNote');

      if (modalTitle) modalTitle.innerHTML = `<i class="bi bi-bag-heart me-2 text-primary"></i>Solicitud de Pedido: ${name}`;
      if (modalProductName) modalProductName.textContent = name;
      if (modalUnitPriceDisplay) modalUnitPriceDisplay.textContent = `Precio Unitario: $${price.toFixed(2)} MXN`;
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

      updateTotal();
    });
  });
}
