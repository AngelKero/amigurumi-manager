/**
 * Module: Checkout (Compra Pública contra /api/pedidos/solicitar.php — Subfase 4.4)
 * Single Responsibility: abrir el modal con datos reales de la pieza (creacion_id
 * propagado), stepper acotado a existencias y confirmación con precio congelado
 * por el servidor + enlace WhatsApp pre-formateado.
 *
 * Seguridad DOM (H-004): datos solo con `textContent`/DOM APIs. Cero `alert()`.
 * Moneda (R-06): el modal estima en pesos; el total oficial lo congela el servidor.
 */

import { formatPesos } from './currency.js';
import { setIconText } from './dom-safe.js';

const SOLICITAR_URL = '/api/pedidos/solicitar.php';
const ENCARGO_MAX = 1000;

function readPieceFromButton(btn) {
  return {
    id: btn.getAttribute('data-id') || '',
    nombre: btn.getAttribute('data-nombre') || btn.getAttribute('data-name') || 'Pieza artesanal',
    precioCents: Number(btn.getAttribute('data-precio-cents') || btn.getAttribute('data-price') || 0),
    stock: Number(btn.getAttribute('data-stock') || 0),
    onDemand: (btn.getAttribute('data-on-demand') || '0') === '1',
  };
}

function maxForPiece(piece) {
  if (piece.onDemand) return ENCARGO_MAX;
  return Math.max(0, piece.stock);
}

export function initCheckout() {
  const checkoutModal = document.getElementById('checkoutModal');
  if (!checkoutModal) return;

  const form = document.getElementById('publicCheckoutForm');
  if (!form || form.dataset.bound === '1') return;
  form.dataset.bound = '1';

  const modalTitle = document.getElementById('checkoutModalTitle');
  const btnDec = document.getElementById('btnCheckoutDec');
  const btnInc = document.getElementById('btnCheckoutInc');
  const inputQty = document.getElementById('inputCheckoutQty');
  const displayTotal = document.getElementById('checkoutTotalDisplay');
  const unitPriceHidden = document.getElementById('checkoutUnitPrice');
  const stockMaxHidden = document.getElementById('checkoutStockMax');
  const encargoHidden = document.getElementById('checkoutEsSobreEncargo');
  const creacionIdHidden = document.getElementById('checkoutCreacionId');
  const feedback = document.getElementById('checkoutFeedback');
  const formFields = document.getElementById('checkoutFormFields');
  const success = document.getElementById('checkoutSuccess');
  const submitBtn = document.getElementById('btnConfirmCheckout');

  function showFeedback(messages) {
    if (!feedback) return;
    feedback.replaceChildren();
    const list = Array.isArray(messages) ? messages : [messages];
    if (list.length === 0 || (list.length === 1 && !list[0])) {
      feedback.classList.add('d-none');
      return;
    }
    list.forEach((message) => {
      const div = document.createElement('div');
      div.textContent = String(message);
      feedback.appendChild(div);
    });
    feedback.classList.remove('d-none');
  }

  function currentMax() {
    if (encargoHidden && encargoHidden.value === '1') return ENCARGO_MAX;
    return Math.max(0, parseInt(stockMaxHidden ? stockMaxHidden.value : '0', 10) || 0);
  }

  function updateTotal() {
    if (!inputQty || !unitPriceHidden || !displayTotal) return;
    const qty = Math.max(1, parseInt(inputQty.value, 10) || 1);
    const unitPesos = Number(unitPriceHidden.value) || 0;
    const max = currentMax();
    if (btnDec) btnDec.disabled = qty <= 1;
    if (btnInc) btnInc.disabled = qty >= max;
    displayTotal.textContent = formatPesos(qty * unitPesos);
  }

  function openWithPiece(piece) {
    if (creacionIdHidden) creacionIdHidden.value = piece.id;
    if (unitPriceHidden) unitPriceHidden.value = String(piece.precioCents / 100);
    if (stockMaxHidden) stockMaxHidden.value = String(maxForPiece(piece));
    if (encargoHidden) encargoHidden.value = piece.onDemand ? '1' : '0';
    if (inputQty) inputQty.value = '1';
    showFeedback([]);

    if (formFields) formFields.classList.remove('d-none');
    if (success) success.classList.add('d-none');

    setIconText(modalTitle, 'bi bi-bag-heart me-2 text-primary', `Solicitud de Pedido: ${piece.nombre}`);
    const modalProductName = document.getElementById('modalProductName');
    if (modalProductName) modalProductName.textContent = piece.nombre;
    const modalUnitPriceDisplay = document.getElementById('modalUnitPriceDisplay');
    if (modalUnitPriceDisplay) {
      modalUnitPriceDisplay.textContent = `Precio Unitario: ${formatPesos(piece.precioCents / 100)}`;
    }

    const modalStockBadge = document.getElementById('modalStockBadge');
    if (modalStockBadge) {
      modalStockBadge.replaceChildren();
      const icon = document.createElement('i');
      modalStockBadge.appendChild(icon);
      if (piece.onDemand) {
        modalStockBadge.className = 'badge badge-textile-tag';
        icon.className = 'bi bi-magic me-1';
        modalStockBadge.appendChild(document.createTextNode('Bajo Encargo'));
      } else if (piece.stock > 0) {
        modalStockBadge.className = 'badge badge-stock-in';
        icon.className = 'bi bi-check-circle-fill me-1';
        modalStockBadge.appendChild(document.createTextNode(`Stock: ${piece.stock} disp.`));
      } else {
        modalStockBadge.className = 'badge badge-stock-out';
        icon.className = 'bi bi-dash-circle me-1';
        modalStockBadge.appendChild(document.createTextNode('Agotado (Bajo encargo)'));
      }
    }

    const checkoutStockNote = document.getElementById('checkoutStockNote');
    if (checkoutStockNote) {
      checkoutStockNote.textContent = piece.onDemand || piece.stock === 0
        ? 'Confección bajo encargo especial'
        : `Máx: ${piece.stock} unidades en stock`;
    }

    updateTotal();
  }

  // Apertura desde catálogo (data-* reales) o detalle (data-* propagados por detail.js).
  // Las tarjetas del catálogo se renderizan de forma asíncrona vía fetch
  // DESPUÉS de este init, así que el binding directo por querySelectorAll
  // nunca las alcanzaba (el modal abría vacío en /index.php). Delegación en
  // documento + show.bs.modal con relatedTarget: cubre botones estáticos,
  // dinámicos y aperturas programáticas de Bootstrap.
  const BUY_SELECTOR = '.btn-buy-product, #btnComprarDetalle, #btnDetalleCheckout';
  document.addEventListener('click', (e) => {
    const target = e.target instanceof Element ? e.target.closest(BUY_SELECTOR) : null;
    if (!target || target.disabled) return;
    openWithPiece(readPieceFromButton(target));
  });
  if (checkoutModal) {
    checkoutModal.addEventListener('show.bs.modal', (event) => {
      const related = event && event.relatedTarget;
      const btn = related instanceof Element ? related.closest(BUY_SELECTOR) : null;
      if (btn && !btn.disabled) openWithPiece(readPieceFromButton(btn));
    });
  }

  if (btnDec && btnInc && inputQty) {
    btnDec.addEventListener('click', () => {
      const current = parseInt(inputQty.value, 10) || 1;
      if (current > 1) {
        inputQty.value = String(current - 1);
        updateTotal();
      }
    });
    btnInc.addEventListener('click', () => {
      const current = parseInt(inputQty.value, 10) || 1;
      if (current < currentMax()) {
        inputQty.value = String(current + 1);
        updateTotal();
      }
    });
    inputQty.addEventListener('change', updateTotal);
  }

  function showSuccess(datos) {
    if (formFields) formFields.classList.add('d-none');
    if (success) success.classList.remove('d-none');
    const folio = document.getElementById('checkoutSuccessFolio');
    if (folio) folio.textContent = `#${datos.id}`;
    const message = document.getElementById('checkoutSuccessMessage');
    if (message) message.textContent = String(datos.mensaje || '');
    const total = document.getElementById('checkoutSuccessTotal');
    if (total) total.textContent = String(datos.precio_final_formateado || '');
    const waBtn = document.getElementById('checkoutWhatsAppBtn');
    if (waBtn) {
      if (datos.enlace_whatsapp) {
        waBtn.setAttribute('href', String(datos.enlace_whatsapp));
        waBtn.classList.remove('d-none');
        waBtn.classList.add('d-inline-flex');
      } else {
        waBtn.classList.add('d-none');
        waBtn.classList.remove('d-inline-flex');
      }
    }
    const waHint = document.getElementById('checkoutWhatsAppHint');
    if (waHint) {
      waHint.textContent = datos.enlace_whatsapp
        ? 'Toca el botón para coordinar la entrega directamente con el artesano.'
        : 'El artesano aún no registra WhatsApp; te contactará con los datos que dejaste.';
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const creacionId = Number(creacionIdHidden ? creacionIdHidden.value : 0);
    const nombreEl = document.getElementById('clienteNombre');
    const contactoEl = document.getElementById('clienteContacto');
    const notasEl = document.getElementById('notasPedido');
    const nombre = nombreEl ? nombreEl.value.trim() : '';
    const contacto = contactoEl ? contactoEl.value.trim() : '';
    const notas = notasEl ? notasEl.value.trim() : '';
    const cantidad = Math.max(1, parseInt(inputQty ? inputQty.value : '1', 10) || 1);

    const errors = [];
    if (!creacionId) errors.push('No se identificó la pieza (reabre el modal desde el catálogo).');
    if ([...nombre].length < 2 || [...nombre].length > 100) errors.push('Nombre: entre 2 y 100 caracteres.');
    if ([...contacto].length < 3 || [...contacto].length > 50) errors.push('Contacto: entre 3 y 50 caracteres.');
    if (cantidad < 1 || cantidad > 1000) errors.push('Cantidad: entre 1 y 1,000 unidades.');
    if ([...notas].length > 1000) errors.push('Notas: máximo 1,000 caracteres.');
    if (errors.length > 0) {
      showFeedback(errors);
      return;
    }
    showFeedback([]);
    if (submitBtn) submitBtn.disabled = true;

    try {
      const res = await fetch(SOLICITAR_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          creacion_id: creacionId,
          cantidad,
          cliente_nombre: nombre,
          cliente_contacto: contacto,
          notas: notas !== '' ? notas : null,
        }),
      });
      const contentType = res.headers.get('content-type') || '';
      const json = contentType.includes('application/json') ? await res.json().catch(() => null) : null;

      if (res.status === 201 && json && json.exito) {
        showSuccess(json.datos || {});
        return;
      }
      if (res.status === 422 || res.status === 404 || res.status === 409) {
        const detail = json && json.error
          ? ([json.error.mensaje].concat(json.error.detalles || []))
          : [`No se pudo registrar (HTTP ${res.status}).`];
        showFeedback(detail);
        return;
      }
      showFeedback((json && json.error && json.error.mensaje) || `Error inesperado (HTTP ${res.status}).`);
    } catch {
      showFeedback('No se pudo contactar al servidor. Revisa tu conexión.');
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
}
