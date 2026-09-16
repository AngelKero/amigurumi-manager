/**
 * Module: Detail (Ficha Técnica Hidratada + Guardia de Stock — Subfase 4.4)
 * Single Responsibility: hidratar `detalle.php?id=` desde
 * `GET /api/creaciones/detalle.php` y reflejar el estado real de inventario.
 *
 * Seguridad DOM (H-004): datos del servidor solo con `textContent`/DOM APIs.
 * Cero interpolación en `innerHTML`.
 */

import { isAuthenticated } from './auth.js';
import { setIconText } from './dom-safe.js';

const DETALLE_URL = '/api/creaciones/detalle.php';
const GENERIC_FALLBACK = 'assets/svg/piezas/ovillo-generico.svg';

// Última pieza hidratada + cadena de fuentes activas (misma que catalog.js:
// imagen_url -> imagen_fallback_svg -> ovillo-generico.svg). H-004: solo
// textContent / setAttribute, cero innerHTML con datos del servidor.
let lastItem = null;
let detailImageSources = [GENERIC_FALLBACK];
let detailImageIndex = 0;

function currentSourceKind() {
  const src = String(detailImageSources[detailImageIndex] || '');
  if (src === GENERIC_FALLBACK) return 'generic';
  if (src.startsWith('assets/svg/')) return 'thematic';
  return 'photo';
}

function updateSourceBadges() {
  const kind = currentSourceKind();
  const label = kind === 'photo'
    ? 'Fotografía real'
    : (kind === 'thematic' ? 'Ilustración temática' : 'Ilustración genérica');
  const mainLabel = document.getElementById('detailImageSourceLabel');
  if (mainLabel) mainLabel.textContent = label;
  const mainBadge = document.getElementById('detailImageSourceBadge');
  if (mainBadge) mainBadge.classList.remove('d-none');
  const lightLabel = document.getElementById('detailLightboxSourceLabel');
  if (lightLabel) lightLabel.textContent = label;
}

function syncLightboxFromMain() {
  const mainImg = document.getElementById('detailMainImage');
  const lightImg = document.getElementById('detailLightboxImage');
  if (!mainImg || !lightImg) return;
  const resolved = mainImg.currentSrc || mainImg.getAttribute('src') || GENERIC_FALLBACK;
  lightImg.setAttribute('src', String(resolved));
  lightImg.setAttribute('alt', String(mainImg.getAttribute('alt') || 'Vista ampliada de la creación artesanal'));
}

function openDetailLightbox() {
  const modalEl = document.getElementById('detailLightboxModal');
  if (!modalEl) return;
  syncLightboxFromMain();
  updateSourceBadges();
  if (window.bootstrap && window.bootstrap.Modal) {
    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
  } else {
    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    modalEl.removeAttribute('aria-hidden');
  }
}

function setDetailImage(item) {
  const mainImg = document.getElementById('detailMainImage');
  if (!mainImg) return;
  const sources = [];
  const rawUrl = item.imagen_url != null ? String(item.imagen_url).trim() : '';
  const rawFallback = item.imagen_fallback_svg != null ? String(item.imagen_fallback_svg).trim() : '';
  if (rawUrl !== '') sources.push(rawUrl);
  if (rawFallback !== '') sources.push(rawFallback);
  sources.push(GENERIC_FALLBACK);
  detailImageSources = sources;
  detailImageIndex = 0;

  const nombre = String(item.nombre || 'Pieza artesanal');
  mainImg.classList.remove('d-none');
  mainImg.setAttribute('alt', `Fotografía de ${nombre}`);
  mainImg.onerror = () => {
    detailImageIndex += 1;
    if (detailImageIndex < detailImageSources.length) {
      mainImg.setAttribute('src', detailImageSources[detailImageIndex]);
    } else {
      mainImg.onerror = null;
      mainImg.classList.add('d-none');
    }
    updateSourceBadges();
  };
  mainImg.setAttribute('src', detailImageSources[0]);

  const material = String(item.material || '');
  const caption = document.getElementById('detailImageCaption');
  if (caption) {
    caption.textContent = material !== ''
      ? `${nombre} · ${material}`
      : `${nombre} · Toca la fotografía para verla en grande`;
  }
  const legacyCaption = document.getElementById('detailSvgCaption');
  if (legacyCaption) legacyCaption.textContent = nombre;
  const zoomBtn = document.getElementById('detailImageZoomBtn');
  if (zoomBtn) zoomBtn.setAttribute('aria-label', `Ampliar fotografía de ${nombre}`);

  const lightTitle = document.getElementById('detailLightboxTitleText');
  if (lightTitle) lightTitle.textContent = nombre;
  const lightCaption = document.getElementById('detailLightboxCaption');
  if (lightCaption) {
    lightCaption.textContent = material !== '' ? `${nombre} · ${material}` : nombre;
  }

  // Contacto público con el artesano (010-3): server-side, oculto si no hay número
  const waBtn = document.getElementById('detailArtisanWhatsappBtn');
  const waHint = document.getElementById('detailArtisanWhatsappHint');
  const waLink = typeof item.enlace_whatsapp_artesano === 'string' && item.enlace_whatsapp_artesano.trim() !== ''
    ? item.enlace_whatsapp_artesano.trim()
    : '';
  if (waBtn) {
    if (waLink !== '') {
      waBtn.setAttribute('href', waLink);
      waBtn.classList.remove('d-none');
      waBtn.classList.add('d-flex');
    } else {
      waBtn.classList.add('d-none');
      waBtn.classList.remove('d-flex');
    }
  }
  if (waHint) waHint.classList.toggle('d-none', waLink === '');

  updateSourceBadges();
}

function setStockState(stock, onDemand) {
  const stockBadge = document.getElementById('detalleStockBadge');
  const btnCheckout = document.getElementById('btnDetalleCheckout') || document.getElementById('btnComprarDetalle');
  const inStockContainer = document.getElementById('actionInStockContainer');
  const outOfStockNotice = document.getElementById('detalleOutOfStockNotice') || document.getElementById('actionOutOfStockContainer');
  const modalStockBadge = document.getElementById('modalStockBadge');
  const checkoutStockMax = document.getElementById('checkoutStockMax');
  const checkoutStockNote = document.getElementById('checkoutStockNote');

  if (stockBadge) {
    stockBadge.replaceChildren();
    const icon = document.createElement('i');
    stockBadge.appendChild(icon);
    if (onDemand) {
      stockBadge.className = 'badge badge-textile-tag fs-6';
      icon.className = 'bi bi-magic me-1';
      stockBadge.appendChild(document.createTextNode('Bajo Encargo'));
    } else if (stock === 0) {
      stockBadge.className = 'badge badge-stock-out fs-6';
      icon.className = 'bi bi-dash-circle me-1';
      stockBadge.appendChild(document.createTextNode('Agotado (0 disp.)'));
    } else {
      stockBadge.className = 'badge badge-stock-in fs-6';
      icon.className = 'bi bi-check-circle-fill me-1';
      stockBadge.appendChild(document.createTextNode(`En Stock: ${stock} unidades`));
    }
  }

  if (btnCheckout) {
    btnCheckout.replaceChildren();
    const icon = document.createElement('i');
    btnCheckout.appendChild(icon);
    if (stock === 0 && !onDemand) {
      btnCheckout.disabled = true;
      btnCheckout.setAttribute('aria-disabled', 'true');
      icon.className = 'bi bi-slash-circle me-2';
      btnCheckout.appendChild(document.createTextNode(' Agotado para Entrega Inmediata'));
    } else {
      btnCheckout.disabled = false;
      btnCheckout.removeAttribute('aria-disabled');
      icon.className = 'bi bi-cart-plus me-2';
      btnCheckout.appendChild(document.createTextNode(' Adquirir Creación'));
    }
  }

  if (inStockContainer && inStockContainer !== (btnCheckout && btnCheckout.parentElement)) {
    inStockContainer.classList.toggle('d-none', stock === 0 && !onDemand);
  }
  if (outOfStockNotice) outOfStockNotice.classList.toggle('d-none', !(stock === 0 && !onDemand));

  if (modalStockBadge) {
    modalStockBadge.className = onDemand || stock > 0 ? 'badge badge-stock-in' : 'badge badge-stock-out';
    modalStockBadge.textContent = onDemand ? 'Bajo Encargo' : (stock === 0 ? 'Agotado (Bajo encargo)' : `Stock: ${stock} disp.`);
  }
  if (checkoutStockMax) checkoutStockMax.value = String(onDemand ? 1000 : stock);
  if (checkoutStockNote) {
    checkoutStockNote.textContent = onDemand || stock === 0
      ? 'Confección bajo encargo especial'
      : `Máx: ${stock} unidades en stock`;
  }

  const inputQty = document.getElementById('inputCheckoutQty');
  if (inputQty) {
    inputQty.dispatchEvent(new Event('change'));
  }
}

function fillDetail(item) {
  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  lastItem = item;
  set('detalleTitle', String(item.nombre || ''));
  set('breadcrumbCurrentItem', String(item.nombre || 'Detalle'));
  set('detallePriceDisplay', String(item.precio_formateado || ''));
  set('detalleDescription', String(item.descripcion || ''));
  set('detalleTamano', String(item.dimensiones || ''));
  set('detalleMaterial', String(item.material || ''));
  set('detalleCategoryBadge', '');
  setIconText(document.getElementById('detalleCategoryBadge'), 'bi bi-tag me-1', String(item.categoria || ''));

  const horas = Number(item.horas_tejido) || 0;
  set('detalleHoras', `${horas} horas de tejido`);

  set('metricCostDisplay', String(item.costo_materiales_formateado || ''));
  set('metricGainDisplay', String(item.ganancia_bruta_formateada || ''));
  const margenEl = document.getElementById('metricMarginDisplay');
  if (margenEl) margenEl.textContent = `${Number(item.margen_bruto_porcentaje) || 0}%`;
  set('metricHourlyDisplay', String(item.retorno_por_hora_formateado || ''));

  const stock = Number(item.cantidad_stock) || 0;
  const onDemand = Number(item.es_sobre_encargo) === 1;
  setStockState(stock, onDemand);
  setDetailImage(item);

  const buyBtn = document.getElementById('btnDetalleCheckout') || document.getElementById('btnComprarDetalle');
  if (buyBtn) {
    buyBtn.setAttribute('data-id', String(item.id));
    buyBtn.setAttribute('data-nombre', String(item.nombre || ''));
    buyBtn.setAttribute('data-precio-cents', String(item.precio_centavos || 0));
    buyBtn.setAttribute('data-stock', String(stock));
    buyBtn.setAttribute('data-on-demand', onDemand ? '1' : '0');
  }
}

export function initDetail() {
  if (!document.getElementById('detalleTitle')) return;

  const urlParams = new URLSearchParams(window.location.search);
  const idParam = urlParams.get('id');

  const notFoundAlert = document.getElementById('detalleNotFoundAlert');
  const mainContent = document.getElementById('detalleMainContent');

  function showNotFound() {
    if (notFoundAlert) notFoundAlert.classList.remove('d-none');
    if (mainContent) mainContent.classList.add('d-none');
  }

  if (!idParam || Number.isNaN(Number(idParam))) {
    showNotFound();
    return;
  }

  fetch(`${DETALLE_URL}?id=${encodeURIComponent(String(idParam))}`)
    .then((res) => {
      if (res.status === 404) {
        showNotFound();
        return null;
      }
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    })
    .then((json) => {
      if (!json) return;
      if (json.exito === true && json.datos) {
        fillDetail(json.datos);
      } else {
        showNotFound();
      }
    })
    .catch(() => {
      showNotFound();
    });

  // Lightbox artesanal: abrir la foto en grande (foto real o fallback temático)
  // El modal debe vivir como hijo directo de <body>: dentro de <main>
  // (position:relative + z-index:1) quedaría bajo el backdrop (1050) y la
  // página se bloquearía. Se reparenta por si el marcado viniera anidado.
  const lightboxModal = document.getElementById('detailLightboxModal');
  if (lightboxModal && lightboxModal.parentElement !== document.body) {
    document.body.appendChild(lightboxModal);
  }
  const zoomBtn = document.getElementById('detailImageZoomBtn');
  if (zoomBtn) zoomBtn.addEventListener('click', openDetailLightbox);
  const openBtn = document.getElementById('detailOpenLightboxBtn');
  if (openBtn) openBtn.addEventListener('click', openDetailLightbox);
  if (lightboxModal) {
    lightboxModal.addEventListener('show.bs.modal', () => {
      syncLightboxFromMain();
      updateSourceBadges();
    });
  }

  // Compatibilidad: si quedara alguna miniatura heredada, abre el lightbox
  const thumbnails = document.querySelectorAll('.card-thumb-item, .thumb-textile-item');
  thumbnails.forEach((thumb) => {
    thumb.addEventListener('click', () => {
      thumbnails.forEach((t) => t.classList.remove('active', 'border-primary', 'shadow-sm', 'bg-white'));
      thumb.classList.add('active');
      openDetailLightbox();
    });
  });

  // Barra de autoría y métricas privadas si hay sesión activa
  if (isAuthenticated()) {
    const artisanToolbar = document.getElementById('artisanDetailToolbar');
    const privateMetricsCard = document.getElementById('artisanPrivateMetricsCard');
    if (artisanToolbar) artisanToolbar.classList.remove('d-none');
    if (privateMetricsCard) privateMetricsCard.classList.remove('d-none');
  }

  // Poblar nombre en modal de eliminación
  const btnEliminar = document.getElementById('btnEliminarCreacion');
  if (btnEliminar) {
    btnEliminar.addEventListener('click', () => {
      const titleEl = document.getElementById('detalleTitle');
      const deleteNameSpan = document.getElementById('deleteCreacionName') || document.getElementById('deleteAmigurumiName');
      if (titleEl && deleteNameSpan) {
        deleteNameSpan.textContent = titleEl.textContent.trim();
      }
    });
  }
}
