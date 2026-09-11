<?php
/**
 * Page Content: Catálogo e Inventario Textil
 * Algodón Nórdico Design System
 */

// Mock items for showcase / catalog rendering (will be sourced from Repository in Phase 4)
$catalogItems = [
  [
    'id' => 1,
    'nombre' => 'Dragón Ignis',
    'categoria' => 'Fantasía',
    'material' => 'Algodón Mercerizado',
    'tamano_cm' => 18.5,
    'precio' => 450.00,
    'cantidad_stock' => 4,
    'descripcion' => 'Dragón mítico con escamas en relieve tejidas con hilo de algodón mercerizado y relleno antialérgico.',
    'svg_illustration' => '<svg viewBox="0 0 400 300" class="card-product-img" xmlns="http://www.w3.org/2000/svg"><rect width="400" height="300" fill="#f5ede7"/><circle cx="200" cy="150" r="85" fill="#c25e3e"/><path d="M140 100 Q160 50 180 90" stroke="#a84d30" stroke-width="12" fill="none" stroke-linecap="round"/><path d="M260 100 Q240 50 220 90" stroke="#a84d30" stroke-width="12" fill="none" stroke-linecap="round"/><circle cx="175" cy="140" r="10" fill="#2d2621"/><circle cx="225" cy="140" r="10" fill="#2d2621"/><circle cx="178" cy="138" r="3" fill="#ffffff"/><circle cx="228" cy="138" r="3" fill="#ffffff"/><path d="M185 165 Q200 180 215 165" stroke="#ffffff" stroke-width="4" fill="none" stroke-linecap="round"/><text x="200" y="270" text-anchor="middle" font-family="\'Plus Jakarta Sans\', sans-serif" font-weight="700" fill="#8c3f25" font-size="15">🧶 Tejido a Mano &bull; 100% Algodón</text></svg>'
  ],
  [
    'id' => 2,
    'nombre' => 'Mini Suculenta Maceta',
    'categoria' => 'Plantas / Botánica',
    'material' => 'Algodón Rústico',
    'tamano_cm' => 10.0,
    'precio' => 180.00,
    'cantidad_stock' => 12,
    'descripcion' => 'Suculenta de escritorio que no requiere riego, tejida con algodón rústico en maceta color terracota.',
    'svg_illustration' => '<svg viewBox="0 0 400 300" class="card-product-img" xmlns="http://www.w3.org/2000/svg"><rect width="400" height="300" fill="#edf4ef"/><path d="M150 180 L160 250 L240 250 L250 180 Z" fill="#bfa085"/><ellipse cx="200" cy="150" rx="45" ry="30" fill="#5a7d66"/><ellipse cx="170" cy="140" rx="30" ry="20" fill="#6d947b"/><ellipse cx="230" cy="140" rx="30" ry="20" fill="#6d947b"/><circle cx="200" cy="120" r="22" fill="#7fa88e"/><text x="200" y="280" text-anchor="middle" font-family="\'Plus Jakarta Sans\', sans-serif" font-weight="700" fill="#486552" font-size="15">🌿 Colección Botánica</text></svg>'
  ],
  [
    'id' => 3,
    'nombre' => 'Ajolote Rosado Pastel',
    'categoria' => 'Animales / Fauna',
    'material' => 'Hilo Chenille Soft',
    'tamano_cm' => 14.0,
    'precio' => 320.00,
    'cantidad_stock' => 0,
    'descripcion' => 'Ajolote mexicano extra suave confeccionado en hilo chenille velvet. Disponible bajo pedido por encargo.',
    'svg_illustration' => '<svg viewBox="0 0 400 300" class="card-product-img" xmlns="http://www.w3.org/2000/svg"><rect width="400" height="300" fill="#faeff2"/><ellipse cx="200" cy="150" rx="80" ry="60" fill="#e8a2b5"/><path d="M120 140 Q90 120 125 105" stroke="#d4708c" stroke-width="8" fill="none" stroke-linecap="round"/><path d="M115 155 Q80 150 115 135" stroke="#d4708c" stroke-width="8" fill="none" stroke-linecap="round"/><path d="M280 140 Q310 120 275 105" stroke="#d4708c" stroke-width="8" fill="none" stroke-linecap="round"/><path d="M285 155 Q320 150 285 135" stroke="#d4708c" stroke-width="8" fill="none" stroke-linecap="round"/><circle cx="170" cy="145" r="8" fill="#2d2621"/><circle cx="230" cy="145" r="8" fill="#2d2621"/><path d="M185 165 Q200 175 215 165" stroke="#2d2621" stroke-width="3" fill="none" stroke-linecap="round"/><text x="200" y="270" text-anchor="middle" font-family="\'Plus Jakarta Sans\', sans-serif" font-weight="700" fill="#a64964" font-size="15">✨ Hilo Chenille Aterciopelado</text></svg>'
  ]
];
?>

<!-- SECCIÓN HERO: Algodón Nórdico Cloud Banner con Pespunte y Marco Acolchado -->
<section class="hero-cloud hero-cloud-stitched p-4 p-md-5 mb-4 position-relative">
  <div class="hero-cloud-seam"></div>
  <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
    
    <!-- Columna Texto y Acciones -->
    <div class="col-lg-7 text-center text-lg-start">
      <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag fs-6 mb-3">
        <span class="text-primary"><i class="bi bi-stars"></i></span>
        <span>Colección Textil Algodón Nórdico</span>
        <span class="badge bg-white text-muted font-monospace border ms-1" style="border-radius: var(--craft-radius-pill); font-size: 0.72rem;">Stock en Vivo</span>
      </div>
      
      <h1 class="display-5 fw-extrabold mb-3" style="color: var(--craft-text-main);">
        Creaciones Amigurumi con Alma y Ternura
      </h1>
      
      <p class="lead mb-4" style="color: var(--craft-text-muted); font-size: 1.1rem;">
        Piezas artesanales tejidas a mano punto a punto con lanas nórdicas y algodón mercerizado hipoalergénico. Monitorea inventarios reales o encarga piezas exclusivas a tu medida.
      </p>
      
      <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
        <a href="#productCardGrid" class="btn btn-craft-primary btn-craft-stitched btn-lg d-inline-flex align-items-center gap-2">
          <i class="bi bi-bag-heart"></i>
          <span>Explorar Catálogo</span>
        </a>
        <button class="btn btn-craft-outline btn-craft-outline-stitched btn-lg d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#checkoutModal">
          <i class="bi bi-magic"></i>
          <span>Encargar Personalizado</span>
        </button>
      </div>

      <!-- Micro-indicadores de Confianza en Chips Textiles Hilvanados -->
      <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2 mt-4 pt-3 border-top" style="border-color: rgba(228, 232, 237, 0.7) !important;">
        <div class="trust-chip-stitched">
          <i class="bi bi-patch-check-fill fs-6" style="color: var(--craft-secondary);"></i>
          <span>100% Hecho a Mano</span>
        </div>
        <div class="trust-chip-stitched">
          <i class="bi bi-box2-heart-fill fs-6" style="color: var(--craft-primary);"></i>
          <span>Envío Seguro Acolchado</span>
        </div>
        <div class="trust-chip-stitched">
          <i class="bi bi-shield-check fs-6 text-warning"></i>
          <span>Garantía de Autor Taller TT-001</span>
        </div>
      </div>
    </div>

    <!-- Columna Fotografía Artesanal con Marco Pespunte Acolchado -->
    <div class="col-lg-5 text-center">
      <div class="hero-photo-stitched-frame mx-auto">
        <div class="position-relative overflow-hidden" style="border-radius: calc(var(--craft-radius) - 6px);">
          <img src="uploads/dragon.jpg" 
               alt="Dragón Ignis Amigurumi Artesanal" 
               class="img-fluid hero-crafted-img" 
               onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
          
          <!-- Fallback SVG Ilustrado -->
          <div class="d-none bg-white p-4">
            <svg viewBox="0 0 320 280" class="img-fluid" xmlns="http://www.w3.org/2000/svg">
              <rect width="320" height="280" fill="#fdfbf9" rx="16"/>
              <circle cx="160" cy="140" r="80" fill="#8e5b74" opacity="0.85"/>
              <circle cx="135" cy="130" r="8" fill="#ffffff"/>
              <circle cx="185" cy="130" r="8" fill="#ffffff"/>
              <path d="M145 155 Q160 170 175 155" stroke="#ffffff" stroke-width="4" fill="none" stroke-linecap="round"/>
              <text x="160" y="245" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif" font-weight="700" fill="#8e5b74" font-size="14">✨ Taller Textil Nórdico</text>
            </svg>
          </div>
          <div class="hero-floating-cloud-badge">
            <i class="bi bi-heart-fill text-danger me-1"></i> Favorito del Taller
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
        <h5 class="fw-bold mb-0 text-dark">
          <i class="bi bi-sliders me-2 text-primary"></i>Explorador de Creaciones
        </h5>
        <span class="text-muted small d-none d-md-inline">Filtra por temática, material o nivel de inventario</span>
      </div>
      <span class="badge bg-light text-muted font-monospace border px-3 py-2" id="filterResultsCount" style="border-radius: var(--craft-radius-pill);">
        <i class="bi bi-grid-fill text-primary me-1"></i><?= count($catalogItems) ?> piezas visibles
      </span>
    </div>

    <!-- Chips de Categoría Interactivos con Pespunte -->
    <div class="d-flex flex-wrap gap-2 mb-3" id="textileCategoryChips" role="group" aria-label="Filtro rápido de categorías">
      <button type="button" class="btn-chip-textile active" data-category="all">
        <i class="bi bi-sparkles me-1"></i>Todas las Colecciones
      </button>
      <button type="button" class="btn-chip-textile" data-category="Fantasía">
        <i class="bi bi-tag-fill me-1"></i>Fantasía
      </button>
      <button type="button" class="btn-chip-textile" data-category="Plantas / Botánica">
        <i class="bi bi-flower1 me-1"></i>Plantas / Botánica
      </button>
      <button type="button" class="btn-chip-textile" data-category="Animales / Fauna">
        <i class="bi bi-balloon-heart me-1"></i>Animales / Fauna
      </button>
    </div>

    <!-- Fila de Controles de Entrada Redondeados -->
    <div class="row g-2 align-items-center">
      <!-- Buscador por texto -->
      <div class="col-12 col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0 text-muted" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" id="filterSearch" class="form-control input-craft-pill border-start-0" placeholder="Buscar por nombre, hilo o material..." style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
        </div>
      </div>

      <!-- Filtro por Categoría (Dropdown en Sincronía) -->
      <div class="col-6 col-md-2">
        <select id="filterCategory" class="form-select select-craft-pill">
          <option value="all" selected>Categoría: Todas</option>
          <option value="Fantasía">Fantasía</option>
          <option value="Plantas / Botánica">Plantas / Botánica</option>
          <option value="Animales / Fauna">Animales / Fauna</option>
        </select>
      </div>

      <!-- Filtro por Stock -->
      <div class="col-6 col-md-2">
        <select id="filterStock" class="form-select select-craft-pill">
          <option value="all" selected>Stock: Todos</option>
          <option value="in">En Stock (> 0)</option>
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
  </div>
</section>

<!-- REJILLA RESPONSIVA DE PRODUCTOS (col-12, col-md-6, col-lg-4) -->
<section class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5" id="productCardGrid">
  <?php foreach ($catalogItems as $item): ?>
    <?php require __DIR__ . '/../components/product_card.php'; ?>
  <?php endforeach; ?>
</section>

<!-- ESTADO VACÍO (Visible cuando ningún producto coincide con los filtros) -->
<div id="emptyCatalogState" class="d-none text-center py-5 my-4 p-4 card border-0 shadow-sm card-stitched" style="border-radius: var(--craft-radius); background-color: #ffffff;">
  <div class="mb-3">
    <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-primary mx-auto" style="opacity: 0.85;">
      <circle cx="50" cy="50" r="38" stroke="currentColor" stroke-width="3" stroke-dasharray="6 6" fill="var(--craft-primary-subtle)"/>
      <path d="M30 45 Q50 30 70 45 Q50 60 30 45" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
      <path d="M35 55 Q50 70 65 55" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
      <circle cx="50" cy="50" r="4" fill="currentColor"/>
    </svg>
  </div>
  <h4 class="fw-bold text-dark mb-2">No se encontraron piezas artesanales</h4>
  <p class="text-muted small mx-auto mb-4" style="max-width: 460px;">
    No hay ninguna creación en el catálogo que coincida con tu búsqueda o filtros actuales. Prueba a limpiar los filtros o buscar con otro término.
  </p>
  <div>
    <button type="button" class="btn btn-craft-primary btn-craft-stitched btn-sm px-4" id="btnResetFiltersEmpty">
      <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer Filtros
    </button>
  </div>
</div>

<!-- PAGINACIÓN / RESUMEN DE RESULTADOS -->
<section class="d-flex flex-wrap justify-content-between align-items-center py-3 border-top">
  <span class="text-muted small">
    Mostrando <strong><?= count($catalogItems) ?></strong> de <strong><?= count($catalogItems) ?></strong> piezas artesanales en el catálogo
  </span>
  <nav aria-label="Navegación de catálogo">
    <ul class="pagination pagination-sm mb-0">
      <li class="page-item disabled"><a class="page-link" href="#">«</a></li>
      <li class="page-item active"><a class="page-link bg-dark border-dark" href="#">1</a></li>
      <li class="page-item disabled"><a class="page-link" href="#">»</a></li>
    </ul>
  </nav>
</section>
