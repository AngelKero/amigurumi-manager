<?php
/**
 * Page Content: Detalle y Ficha Técnica de la Creación
 * Algodón Nórdico Design System
 */
?>
<div id="detalleMainContent">
  <!-- MIGA DE PAN TEXTIL (BREADCRUMB RIBBON) -->
  <nav aria-label="breadcrumb" class="mb-4">
    <div class="breadcrumb-craft-ribbon">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-heart me-1"></i>Inicio</a></li>
        <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-grid me-1"></i>Catálogo</a></li>
        <li class="breadcrumb-item active" aria-current="page">
          <i class="bi bi-tag-fill me-1 small"></i><span id="breadcrumbCurrentItem">Dragón Ignis</span>
        </li>
      </ol>
    </div>
  </nav>

  <!-- SPLIT DE 2 COLUMNAS (col-12 col-lg-6 / col-12 col-lg-6) -->
  <div class="row g-4 g-lg-5 mb-5">

    <!-- COLUMNA IZQUIERDA: GALERÍA DE IMÁGENES Y ATRIBUCIÓN ARTESANAL -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm p-3 p-md-4 mb-3 bg-white card-stitched" style="border-radius: var(--craft-radius);">
        
        <!-- Imagen Principal con Marco Acolchado Paspartú -->
        <div class="product-photo-stitched-frame mb-3">
          <div class="card-product-img-wrapper rounded shadow-sm" style="aspect-ratio: 1 / 1; max-height: 480px;">
            <svg viewBox="0 0 500 500" class="card-product-img" xmlns="http://www.w3.org/2000/svg" id="detailMainProductSvg">
              <rect width="500" height="500" fill="#f5ede7"/>
              <circle cx="250" cy="250" r="140" fill="#c25e3e"/>
              <path d="M170 170 Q200 90 230 150" stroke="#a84d30" stroke-width="18" fill="none" stroke-linecap="round"/>
              <path d="M330 170 Q300 90 270 150" stroke="#a84d30" stroke-width="18" fill="none" stroke-linecap="round"/>
              <circle cx="210" cy="230" r="16" fill="#2d2621"/>
              <circle cx="290" cy="230" r="16" fill="#2d2621"/>
              <circle cx="215" cy="226" r="5" fill="#ffffff"/>
              <circle cx="295" cy="226" r="5" fill="#ffffff"/>
              <path d="M225 275 Q250 295 275 275" stroke="#ffffff" stroke-width="6" fill="none" stroke-linecap="round"/>
              <!-- Detalles de escamas en relieve -->
              <circle cx="250" cy="180" r="10" fill="#a84d30"/>
              <circle cx="230" cy="200" r="8" fill="#a84d30"/>
              <circle cx="270" cy="200" r="8" fill="#a84d30"/>
              <text x="250" y="440" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif" font-weight="700" fill="#8c3f25" font-size="18" id="detailSvgCaption">🧶 Edición Especial Fantasía &bull; Dragón Ignis</text>
            </svg>
          </div>
        </div>

        <!-- Miniaturas Textiles Interactivas con Pespunte -->
        <div class="row g-2 mb-3" id="detailThumbnailsContainer">
          <div class="col-4">
            <button type="button" class="w-100 thumb-textile-item active card-thumb-item" data-view="frontal">
              <i class="bi bi-eye"></i>
              <span>Frontal</span>
            </button>
          </div>
          <div class="col-4">
            <button type="button" class="w-100 thumb-textile-item card-thumb-item" data-view="escamas">
              <i class="bi bi-zoom-in"></i>
              <span>Escamas</span>
            </button>
          </div>
          <div class="col-4">
            <button type="button" class="w-100 thumb-textile-item card-thumb-item" data-view="perfil">
              <i class="bi bi-camera"></i>
              <span>Perfil / Cola</span>
            </button>
          </div>
        </div>

        <!-- Sello Oficial del Taller Artesanal (Algodón Nórdico) -->
        <div class="artisan-workshop-seal-card">
          <div class="seal-avatar">
            <i class="bi bi-patch-check-fill"></i>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
              <strong class="text-dark">@admin (Artesano Titular)</strong>
              <span class="seal-badge-tag"><i class="bi bi-award-fill"></i> Taller Verificado</span>
            </div>
            <span class="text-muted small d-block">Confección y autoría responsable registrada en el Micro-ERP</span>
            <small class="text-muted font-monospace" style="font-size: 0.72rem;">Código de Taller Textil: #TT-001-NORDIC</small>
          </div>
        </div>

      </div>
    </div>

    <!-- COLUMNA DERECHA: ESPECIFICACIONES Y ACCIONES DE COMPRA -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm p-4 p-lg-5 bg-white h-100 d-flex flex-column justify-content-between card-stitched" style="border-radius: var(--craft-radius);">
        <div>

          <!-- Badges de Categoría y Stock (QW-1 WCAG AA Dark Spruce Contrast) -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge badge-textile-tag fs-6" id="detalleCategoryBadge">
              <i class="bi bi-tag me-1"></i>Fantasía
            </span>
            <span class="badge badge-stock-in fs-6" id="detalleStockBadge">
              <i class="bi bi-check-circle-fill me-1"></i>En Stock: 4 unidades
            </span>
          </div>

          <!-- Título de la Pieza -->
          <h1 class="display-6 fw-bold text-dark mb-2" id="detalleTitle">Dragón Ignis</h1>
          
          <div class="d-flex align-items-baseline gap-2 mb-3">
            <span class="display-5 fw-extrabold text-dark" id="detallePriceDisplay">$450.00</span>
            <span class="text-muted fs-6">MXN</span>
            <span class="badge bg-light text-muted font-monospace border ms-2" style="border-radius: var(--craft-radius-pill);">IVA incluido</span>
          </div>

          <!-- Párrafo Descriptivo con Recuadro Artesanal -->
          <div class="story-quote-craft mb-4">
            <p class="mb-0 text-muted" id="detalleDescription">
              Inspirado en leyendas nórdicas de fuego sereno, cada escama de Ignis es tejida pacientemente a mano con doble hebra para otorgar textura tridimensional al tacto. Incluye armazón interno flexible no deformable y fibra hipoalergénica lavable.
            </p>
          </div>

          <!-- TABLA DE ESPECIFICACIONES TÉCNICAS -->
          <div class="table-responsive mb-4">
            <table class="table table-craft-specs align-middle mb-0">
              <tbody>
                <tr>
                  <td class="text-muted small" style="width: 45%;">
                    <i class="bi bi-rulers spec-icon"></i> Altura / Envergadura:
                  </td>
                  <td class="fw-bold text-dark font-monospace" id="detalleTamano">18.5 cm</td>
                </tr>
                <tr>
                  <td class="text-muted small">
                    <i class="bi bi-palette spec-icon"></i> Material Textil:
                  </td>
                  <td class="fw-bold text-dark" id="detalleMaterial">100% Algodón Mercerizado</td>
                </tr>
                <tr>
                  <td class="text-muted small">
                    <i class="bi bi-clock-history spec-icon"></i> Tiempo de Confección:
                  </td>
                  <td class="fw-bold text-dark font-monospace" id="detalleHoras">6.5 horas de tejido</td>
                </tr>
                <tr>
                  <td class="text-muted small">
                    <i class="bi bi-shield-check spec-icon"></i> Relleno & Seguridad:
                  </td>
                  <td class="fw-bold text-dark">Fibra Siliconada &bull; Ojos de Seguridad</td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>

        <!-- SECCIÓN DE ACCIONES DE COMPRA (Condicionales por Stock) -->
        <div class="pt-3 border-top">
          <!-- CASO 1: En Stock (Stock > 0) -->
          <div id="actionInStockContainer" class="d-flex gap-3 align-items-center">
            <button class="btn btn-craft-primary btn-craft-stitched btn-lg flex-grow-1" data-bs-toggle="modal" data-bs-target="#checkoutModal" id="btnComprarDetalle">
              <i class="bi bi-cart-plus me-2"></i> Adquirir Creación
            </button>
            <button class="btn btn-craft-outline btn-craft-outline-stitched btn-lg px-3" title="Guardar en Favoritos">
              <i class="bi bi-heart"></i>
            </button>
          </div>

          <!-- CASO 2: Agotado (Stock === 0) [CR-1] -->
          <div id="actionOutOfStockContainer" class="d-none alert alert-warning border-0 p-3" style="border-radius: var(--craft-radius-sm); background-color: var(--craft-primary-subtle);">
            <div class="d-flex align-items-center gap-2 mb-2 fw-bold text-dark">
              <i class="bi bi-clock-history text-primary fs-5"></i>
              <span>Agotado para Entrega Inmediata</span>
            </div>
            <p class="small text-muted mb-2">
              No hay piezas terminadas en inventario físico actualmente. Sin embargo, nuestro taller artesanal puede tejerla especialmente para ti bajo encargo programado (5 a 7 días hábiles de confección).
            </p>
            <button class="btn btn-craft-outline btn-sm" data-bs-toggle="modal" data-bs-target="#checkoutModal">
              <i class="bi bi-magic me-1"></i> Solicitar Encargo Especial
            </button>
          </div>

          <!-- Selector Rápido de Estado para Inspección / Auditoría en Vivo -->
          <div class="text-center mt-3 pt-3 border-top">
            <span class="text-muted small d-block mb-1 font-monospace" style="font-size: 0.72rem;">Prueba de Auditoría UI/UX (Inventario en Vivo):</span>
            <div class="toolbar-stock-simulator" role="group" aria-label="Simulador de Stock">
              <button type="button" class="btn-sim-chip btn-sim-in" id="btnSimulateStockIn">
                <i class="bi bi-check-circle-fill me-1"></i>En Stock (4 u.)
              </button>
              <button type="button" class="btn-sim-chip btn-sim-out" id="btnSimulateStockOut">
                <i class="bi bi-slash-circle me-1"></i>Agotado (0 u.)
              </button>
            </div>
          </div>

          <!-- Enlace Secundario Volver al Catálogo -->
          <div class="text-center mt-3">
            <a href="index.php" class="text-muted small text-decoration-none d-inline-flex align-items-center gap-1 hover-primary">
              <i class="bi bi-arrow-left"></i>
              <span>Volver al Catálogo Completo</span>
            </a>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
