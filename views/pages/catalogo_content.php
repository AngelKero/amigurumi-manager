<?php
/**
 * Page Content: Catálogo e Inventario Textil
 * Algodón Nórdico Design System
 *
 * Catálogo reactivo (Subfase 4.2 · Feature 005): la rejilla #productCardGrid se
 * puebla desde `GET /api/creaciones/index.php` vía src/js/modules/catalog.js.
 * La tarjeta artesanal vive en el <template id="catalogCardTemplate"> (marcado
 * constante); los datos se inyectan con DOM APIs / textContent (H-004).
 */
?>

<!-- SECCIÓN HERO: Algodón Nórdico Cloud Banner con Pespunte y Marco Acolchado -->
<section class="hero-cloud hero-cloud-stitched p-4 p-md-5 mb-4 position-relative">
  <div class="hero-cloud-seam"></div>
  <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
    
    <!-- Columna Texto y Acciones -->
    <div class="col-lg-6 text-center text-lg-start">
      <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag fs-6 mb-3">
        <?= svg('branding/isotipo-hebra-nordica', ['width' => 20, 'height' => 20]) ?>
        <span>Colección Textil en Crochet</span>
        <span class="badge bg-white text-muted font-monospace border ms-1" style="border-radius: var(--craft-radius-pill); font-size: 0.72rem;">Stock en Vivo</span>
      </div>
      
      <h1 class="display-5 fw-extrabold mb-3" style="color: var(--craft-text-main);">
        Creaciones en Crochet con Alma y Ternura
      </h1>
      
      <p class="lead mb-4" style="color: var(--craft-text-muted); font-size: 1.1rem;">
        Plataforma abierta y catálogo colectivo de creadores textiles en crochet. Explora prendas, amigurumis, accesorios y piezas únicas, consulta disponibilidad en tiempo real y contacta directamente con cada artesano para encargos a tu medida.
      </p>
      
      <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
        <a href="#productCardGrid" class="btn btn-craft-primary btn-craft-stitched btn-lg d-inline-flex align-items-center gap-2">
          <i class="bi bi-bag-heart"></i>
          <span>Explorar Catálogo</span>
        </a>
        <button class="btn btn-craft-outline btn-craft-outline-stitched btn-lg d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#checkoutModal">
          <i class="bi bi-magic"></i>
          <span>Encargar al Artesano</span>
        </button>
      </div>

      <!-- Micro-indicadores de Calidad de la Plataforma en Chips Hilvanados -->
      <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2 mt-4 pt-3 border-top" style="border-color: rgba(228, 232, 237, 0.7) !important;">
        <div class="trust-chip-stitched d-inline-flex align-items-center gap-1">
          <?= svg('branding/isologo-medallon-garantia', ['width' => 20, 'height' => 20]) ?>
          <span>Creadores Textiles Independientes</span>
        </div>
        <div class="trust-chip-stitched d-inline-flex align-items-center gap-1">
          <?= svg('branding/isotipo-ovillo-corazon', ['width' => 16, 'height' => 16]) ?>
          <span>Trato Directo con el Artesano</span>
        </div>
        <div class="trust-chip-stitched d-inline-flex align-items-center gap-1">
          <?= svg('branding/isologo-sello-taller', ['width' => 20, 'height' => 20]) ?>
          <span>Plataforma Confiable &amp; Verificada</span>
        </div>
      </div>
    </div>

    <!-- Columna Fotografía Artesanal con Marco Pespunte Acolchado -->
    <div class="col-lg-6 text-center">
      <div class="hero-photo-stitched-frame mx-auto">
        <div class="position-relative overflow-hidden" style="border-radius: calc(var(--craft-radius) - 4px);">
          <img src="assets/img/hero_crochet.jpg" 
               id="heroCraftedImg"
               alt="Colección Artesanal de Creaciones en Crochet" 
               class="img-fluid hero-crafted-img"
               width="560"
               height="380"
               fetchpriority="high"
               decoding="async">
          
          <!-- Fallback SVG Ilustrado Mejorado -->
          <div class="d-none bg-white p-4 text-center" id="heroFallback">
            <?= svg('creaciones/dragon-ignis', ['class' => 'img-fluid', 'style' => 'max-height: 280px;']) ?>
          </div>

          <!-- Micro-Badge de Autoría Flotante -->
          <div class="position-absolute bottom-0 start-0 m-3 p-2 px-3 rounded-pill shadow-sm border small fw-bold font-monospace d-inline-flex align-items-center gap-1" style="color: var(--craft-primary); font-size: 0.78rem; z-index: 4; backdrop-filter: blur(8px); background: rgba(255, 255, 255, 0.94) !important;">
            <?= svg('branding/isotipo-ovillo-corazon', ['width' => 18, 'height' => 18]) ?>
            <span>Comunidad de Creadores • Catálogo Abierto</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ESTACIÓN DE FILTROS Y BÚSQUEDA DINÁMICA -->
<section class="card border-0 shadow-sm mb-4 card-filter-station card-stitched" style="border-radius: var(--craft-radius);">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <div>
        <h5 class="fw-bold mb-0 text-dark font-theme-display fs-4">
          <i class="bi bi-sliders me-2 text-primary"></i>Explorador de Creaciones
        </h5>
        <span class="text-muted small d-none d-md-inline">Filtra por categoría, material, presupuesto o creador independiente</span>
      </div>
      <span class="badge bg-light text-muted font-monospace border px-3 py-2" id="filterResultsCount" style="border-radius: var(--craft-radius-pill);">
        <i class="bi bi-grid-fill text-primary me-1"></i><span id="filterResultsCountText">0 piezas visibles</span>
      </span>
    </div>

    <!-- Chips de Categoría Interactivos con Pespunte -->
    <div class="d-flex flex-wrap gap-2 mb-3" id="textileCategoryChips" role="group" aria-label="Filtro rápido de categorías">
      <button type="button" class="btn-chip-textile active" data-category="all">
        <i class="bi bi-sparkles me-1"></i>Todas las Colecciones
      </button>
      <button type="button" class="btn-chip-textile" data-category="Amigurumis & Figuras">
        <i class="bi bi-balloon-heart me-1"></i>Amigurumis & Figuras
      </button>
      <button type="button" class="btn-chip-textile" data-category="Prendas & Ropa">
        <i class="bi bi-person-hearts me-1"></i>Prendas & Ropa
      </button>
      <button type="button" class="btn-chip-textile" data-category="Bolsos & Accesorios">
        <i class="bi bi-handbag me-1"></i>Bolsos & Accesorios
      </button>
      <button type="button" class="btn-chip-textile" data-category="Hogar & Decoración">
        <i class="bi bi-flower1 me-1"></i>Hogar & Decoración
      </button>
      <button type="button" class="btn-chip-textile" data-category="Bebé & Infantil">
        <i class="bi bi-stars me-1"></i>Bebé & Infantil
      </button>
    </div>

    <!-- Fila 1 de Controles: Búsqueda, Categoría, Stock, Ordenación y Reset -->
    <div class="row g-2 align-items-center mb-2">
      <!-- Buscador por texto -->
      <div class="col-12 col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0 text-muted" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" id="filterSearch" class="form-control input-craft-pill border-start-0" placeholder="Buscar por nombre, material o hilaza..." style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
        </div>
      </div>

      <!-- Filtro por Categoría (Dropdown en Sincronía) -->
      <div class="col-6 col-md-2">
        <select id="filterCategory" class="form-select select-craft-pill">
          <option value="all" selected>Categoría: Todas</option>
          <option value="Amigurumis & Figuras">Amigurumis & Figuras</option>
          <option value="Prendas & Ropa">Prendas & Ropa</option>
          <option value="Bolsos & Accesorios">Bolsos & Accesorios</option>
          <option value="Hogar & Decoración">Hogar & Decoración</option>
          <option value="Bebé & Infantil">Bebé & Infantil</option>
        </select>
      </div>

      <!-- Filtro por Stock -->
      <div class="col-6 col-md-2">
        <select id="filterStock" class="form-select select-craft-pill">
          <option value="all" selected>Stock: Todos</option>
          <option value="in">En Stock (> 0)</option>
          <option value="on-demand">Bajo Encargo</option>
          <option value="out">Agotados (0)</option>
        </select>
      </div>

      <!-- Dropdown de Ordenación -->
      <div class="col-8 col-md-3">
        <div class="input-group">
          <span class="input-group-text bg-white text-muted small" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);"><i class="bi bi-sort-down"></i></span>
          <select id="filterSort" class="form-select select-craft-pill" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
            <option value="recent" selected>Más recientes</option>
            <option value="price-asc">Precio: menor a mayor</option>
            <option value="price-desc">Precio: mayor a menor</option>
            <option value="name-asc">Nombre: A → Z</option>
            <option value="name-desc">Nombre: Z → A</option>
            <option value="stock-desc">Mayor existencia</option>
          </select>
        </div>
      </div>

      <!-- Botón Limpiar Filtros -->
      <div class="col-4 col-md-1 d-grid">
        <button type="button" id="btnClearFilters" class="btn btn-craft-outline btn-craft-outline-stitched" title="Reiniciar Filtros">
          <i class="bi bi-arrow-counterclockwise"></i>
        </button>
      </div>
    </div>

    <!-- Controles de Filtros Avanzados (2da Fila) -->
    <div class="row g-3 align-items-center mt-2 pt-2 border-top">
      <!-- Filtro Rango de Precio -->
      <div class="col-12 col-md-6">
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small fw-bold text-nowrap"><i class="bi bi-currency-dollar text-primary me-1"></i>Precio:</span>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light text-muted border-end-0">Min $</span>
            <input type="number" id="filterPriceMin" class="form-control border-start-0" placeholder="Mínimo en pesos" min="0" step="10">
          </div>
          <span class="text-muted small">&ndash;</span>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light text-muted border-end-0">Max $</span>
            <input type="number" id="filterPriceMax" class="form-control border-start-0" placeholder="Máximo en pesos" min="0" step="10">
          </div>
        </div>
      </div>

      <!-- Filtro por Artesano Titular -->
      <div class="col-12 col-md-6">
        <div class="d-flex align-items-center gap-2 justify-content-md-end">
          <span class="text-muted small fw-bold text-nowrap"><i class="bi bi-person-badge text-primary me-1"></i>Artesano:</span>
          <select id="filterArtisan" class="form-select form-select-sm select-craft-pill" style="max-width: 260px;">
            <option value="all" selected>Todos los Artesanos</option>
          </select>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- REJILLA RESPONSIVA DE PRODUCTOS (col-12, col-md-6, col-lg-4) — poblada vía API -->
<section class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5" id="productCardGrid" data-total="0" aria-live="polite">
  <!-- Indicador de carga artesanal con skeleton placeholders hasta el primer render -->
  <div class="col-12" id="catalogLoadingState">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 w-100 m-0">
      <?php for ($i = 0; $i < 3; $i++): ?>
        <div class="col p-2">
          <div class="skeleton-card h-100 p-3">
            <div class="skeleton skeleton-img mb-3"></div>
            <div class="skeleton skeleton-text skeleton-text-title"></div>
            <div class="skeleton skeleton-text w-50 mb-3"></div>
            <div class="skeleton skeleton-btn w-100 mt-auto"></div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
    <div class="d-flex flex-column align-items-center justify-content-center py-4 text-muted">
      <div class="spinner-border spinner-border-sm text-primary mb-2" role="status" aria-hidden="true"></div>
      <span class="small font-monospace">Tejiendo el catálogo…</span>
    </div>
  </div>
</section>

<!-- TEMPLATE DE TARJETA ARTESANAL (marcado constante · datos vía DOM APIs, H-004) -->
<template id="catalogCardTemplate">
  <article class="col product-grid-item">
    <div class="card card-product card-stitched h-100">
      <a href="#" class="card-product-img-wrapper text-decoration-none" data-part="detailLink">
        <div class="card-product-badge-float">
          <span class="badge shadow-sm" data-part="stockBadge"></span>
        </div>
        <img class="card-product-img" data-part="productImg" alt="" style="object-fit: cover;" width="400" height="300" loading="lazy" decoding="async">
      </a>

      <div class="card-body d-flex flex-column p-4" style="position: relative; z-index: 2;">
        <div class="card-product-meta">
          <span class="badge-textile-tag d-inline-flex align-items-center gap-1">
            <i class="bi bi-tag-fill me-1"></i><span data-part="category"></span>
          </span>
          <span class="card-product-dimension" title="Dimensiones">
            <i class="bi bi-rulers me-1"></i><span data-part="dimensions"></span>
          </span>
        </div>

        <h5 class="card-title fw-bold mb-2">
          <a href="#" class="text-decoration-none text-dark hover-primary" data-part="titleLink"></a>
        </h5>

        <p class="card-text text-muted small flex-grow-1 mb-2" data-part="description"></p>

        <div class="divider-stitched mb-3"></div>

        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fs-4 fw-bold text-dark" data-part="price"></span>
            <small class="text-muted d-block" style="font-size: 0.75rem;">MXN / Unidad</small>
          </div>
          <button type="button" class="btn btn-craft-primary btn-craft-stitched btn-sm btn-buy-product" data-bs-toggle="modal" data-bs-target="#checkoutModal" data-part="actionButton">
            <i class="bi bi-cart-plus me-1"></i><span data-part="actionLabel"></span>
          </button>
        </div>

        <!-- Acciones Contextuales del Artesano (visibles con sesión activa) -->
        <div class="artisan-card-actions d-none pt-2 mt-2 border-top d-flex justify-content-between align-items-center">
          <small class="text-muted font-monospace" style="font-size: 0.7rem;" data-part="artisanMeta"></small>
          <div class="btn-group btn-group-sm">
            <a href="#" class="btn btn-outline-secondary btn-sm py-0 px-2" data-part="editLink" title="Editar creación">
              <i class="bi bi-pencil-square"></i>
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-card-delete" data-bs-toggle="modal" data-bs-target="#modalEliminarCreacion" data-part="deleteButton" title="Eliminar del catálogo">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </article>
</template>

<!-- ESTADO VACÍO (Visible cuando ningún producto coincide con los filtros) -->
<div id="emptyCatalogState" class="d-none text-center py-5 my-4 p-4 card border-0 shadow-sm card-stitched" style="border-radius: var(--craft-radius); background-color: #ffffff;">
  <div class="mb-3">
    <?= svg('empty-basket', ['width' => 120, 'height' => 110, 'class' => 'mx-auto mb-2']) ?>
  </div>
  <h4 class="fw-bold text-dark mb-2 font-theme-display" id="emptyCatalogTitle">No se encontraron piezas artesanales</h4>
  <p class="text-muted small mx-auto mb-4" id="emptyCatalogMessage" style="max-width: 460px;">
    No hay ninguna creación en el catálogo que coincida con tu búsqueda o filtros actuales. Prueba a limpiar los filtros o buscar con otro término.
  </p>
  <div>
    <button type="button" class="btn btn-craft-primary btn-craft-stitched btn-sm px-4" id="btnResetFiltersEmpty">
      <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer Filtros
    </button>
  </div>
</div>

<!-- ESTACIÓN DE PAGINACIÓN TEXTIL ARTESANAL -->
<section class="pagination-craft-station card-stitched d-flex flex-wrap justify-content-between align-items-center gap-3 my-4">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="pagination-results-chip">
      <i class="bi bi-collection-fill" style="color: var(--craft-primary);"></i>
      <span>Mostrando <strong id="paginationShowingCount">0</strong> de <strong id="paginationTotalCount">0</strong> creaciones artesanales</span>
    </div>
    <span class="badge bg-light text-muted font-monospace border px-3 py-2 d-none d-md-inline" style="border-radius: var(--craft-radius-pill); font-size: 0.75rem;">
      <i class="bi bi-clock-history me-1 text-primary"></i>Colección Textil 2026
    </span>
  </div>

  <nav aria-label="Navegación de catálogo" id="paginationNav">
    <ul class="pagination-craft"></ul>
  </nav>
</section>
