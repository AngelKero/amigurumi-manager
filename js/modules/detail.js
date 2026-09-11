/**
 * Module: Detail (Ficha Técnica, Miniaturas Interactivas y Guardia de Stock)
 * Single Responsibility: Interacción de la vista de detalle del amigurumi y simulación de inventario.
 */

export function initDetail() {
  const stockBadge = document.getElementById('detalleStockBadge');
  const btnCheckout = document.getElementById('btnDetalleCheckout');
  const outOfStockNotice = document.getElementById('detalleOutOfStockNotice');
  const modalStockBadge = document.getElementById('modalStockBadge');
  const checkoutStockMax = document.getElementById('checkoutStockMax');
  const checkoutStockNote = document.getElementById('checkoutStockNote');
  const btnSimIn = document.getElementById('btnSimulateStockIn');
  const btnSimOut = document.getElementById('btnSimulateStockOut');

  if (!stockBadge || !btnCheckout) return;

  function setStockState(stock) {
    if (stock === 0) {
      // Out-of-Stock Guard [CR-1]
      stockBadge.className = 'badge badge-stock-out fs-6';
      stockBadge.innerHTML = '<i class="bi bi-dash-circle me-1"></i>Agotado (0 disp.)';
      
      btnCheckout.disabled = true;
      btnCheckout.setAttribute('aria-disabled', 'true');
      btnCheckout.innerHTML = '<i class="bi bi-slash-circle me-2"></i> Agotado para Entrega Inmediata';

      if (outOfStockNotice) outOfStockNotice.classList.remove('d-none');
      if (modalStockBadge) {
        modalStockBadge.className = 'badge badge-stock-out';
        modalStockBadge.textContent = 'Agotado (Bajo encargo)';
      }
      if (checkoutStockMax) checkoutStockMax.value = 10;
      if (checkoutStockNote) checkoutStockNote.textContent = 'Confección bajo encargo especial';
    } else {
      // In-Stock State
      stockBadge.className = 'badge badge-stock-in fs-6';
      stockBadge.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>En Stock: ${stock} unidades`;
      
      btnCheckout.disabled = false;
      btnCheckout.removeAttribute('aria-disabled');
      btnCheckout.innerHTML = '<i class="bi bi-bag-heart me-2"></i> Encargar / Comprar Ahora';

      if (outOfStockNotice) outOfStockNotice.classList.add('d-none');
      if (modalStockBadge) {
        modalStockBadge.className = 'badge badge-stock-in';
        modalStockBadge.textContent = `Stock: ${stock} disp.`;
      }
      if (checkoutStockMax) checkoutStockMax.value = stock;
      if (checkoutStockNote) checkoutStockNote.textContent = `Máx: ${stock} unidades en stock`;
    }

    const inputQty = document.getElementById('inputCheckoutQty');
    if (inputQty) {
      inputQty.dispatchEvent(new Event('change'));
    }
  }

  // Comprobar parámetros de URL: ?id=3, ?stock=0, o ?id=9999
  const urlParams = new URLSearchParams(window.location.search);
  const stockParam = urlParams.get('stock');
  const idParam = urlParams.get('id');

  const notFoundAlert = document.getElementById('detalleNotFoundAlert');
  const mainContent = document.getElementById('detalleMainContent');

  if (idParam === '9999' || (idParam && parseInt(idParam) > 10)) {
    if (notFoundAlert && mainContent) {
      notFoundAlert.classList.remove('d-none');
      mainContent.classList.add('d-none');
    }
    return;
  }

  if (stockParam === '0' || idParam === '3') {
    setStockState(0);
  }

  if (btnSimIn) btnSimIn.addEventListener('click', () => setStockState(4));
  if (btnSimOut) btnSimOut.addEventListener('click', () => setStockState(0));

  // Miniaturas textiles interactivas
  const thumbnails = document.querySelectorAll('.card-thumb-item');
  const svgCaption = document.getElementById('detailSvgCaption');
  const captionMap = {
    frontal: '🧶 Edición Especial Fantasía • Dragón Ignis (Vista Frontal)',
    escamas: '🔍 Detalle Escamas Tridimensionales en Crochet Artesanal',
    perfil: '🐉 Vista de Perfil y Cola con Escamas Modeladas'
  };

  thumbnails.forEach(thumb => {
    thumb.addEventListener('click', () => {
      thumbnails.forEach(t => t.classList.remove('active', 'border-primary', 'shadow-sm', 'bg-white'));
      thumb.classList.add('active');

      const viewType = thumb.getAttribute('data-view');
      if (svgCaption && captionMap[viewType]) {
        svgCaption.textContent = captionMap[viewType];
      }
    });
  });
}
