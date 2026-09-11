<?php
/**
 * Page Content: Catálogo e Inventario Textil
 * Algodón Nórdico Design System
 */

// Mock items for showcase / catalog rendering (will be sourced from Repository in Phase 4)
$catalogItems = [
  [
    'id' => 1,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Taller Principal)',
    'nombre' => 'Dragón Ignis',
    'categoria' => 'Amigurumis & Figuras',
    'material' => '100% Algodón Mercerizado',
    'dimensiones' => '18.5 cm (Alto)',
    'precio' => 450.00,
    'precio_centavos' => 45000,
    'costo_materiales' => 12000,
    'cantidad_stock' => 4,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Dragón mítico con escamas en relieve tejidas con hilo de algodón mercerizado y relleno antialérgico.',
    'svg_illustration' => svg('dragon-ignis', ['class' => 'card-product-img'])
  ],
  [
    'id' => 2,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Taller Principal)',
    'nombre' => 'Mini Suculenta en Maceta',
    'categoria' => 'Hogar & Decoración',
    'material' => 'Algodón Rústico y Lana Acrílica',
    'dimensiones' => '10.0 cm x 8.0 cm',
    'precio' => 180.00,
    'precio_centavos' => 18000,
    'costo_materiales' => 4500,
    'cantidad_stock' => 12,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Suculenta de escritorio que no requiere riego, tejida con algodón rústico en maceta color terracota.',
    'svg_illustration' => svg('mini-suculenta', ['class' => 'card-product-img'])
  ],
  [
    'id' => 3,
    'artesano_id' => 2,
    'artesano_username' => 'artesana_ana',
    'artesano_nombre' => 'Ana Diseñadora',
    'nombre' => 'Ajolote Rosado Pastel',
    'categoria' => 'Amigurumis & Figuras',
    'material' => 'Hilo Chenille Terciopelo',
    'dimensiones' => '14.0 x 10.0 cm',
    'precio' => 320.00,
    'precio_centavos' => 32000,
    'costo_materiales' => 8500,
    'cantidad_stock' => 0,
    'es_sobre_encargo' => 1,
    'descripcion' => 'Ajolote mexicano extra suave confeccionado en hilo chenille velvet. Se elabora exclusivamente bajo encargo.',
    'svg_illustration' => svg('ajolote-pastel', ['class' => 'card-product-img'])
  ],
  [
    'id' => 4,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Taller Principal)',
    'nombre' => 'Cardigan Granny Squares',
    'categoria' => 'Prendas & Ropa',
    'material' => 'Lana Merino y Algodón Soft',
    'dimensiones' => 'Talla M (95 x 58 cm)',
    'precio' => 980.00,
    'precio_centavos' => 98000,
    'costo_materiales' => 28000,
    'cantidad_stock' => 2,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Cardigan bohemio tejido a mano con cuadros de la abuela (granny squares) florales y botones de madera rústica.',
    'svg_illustration' => svg('cardigan-granny', ['class' => 'card-product-img'])
  ],
  [
    'id' => 5,
    'artesano_id' => 2,
    'artesano_username' => 'artesana_ana',
    'artesano_nombre' => 'Ana Diseñadora',
    'nombre' => 'Tote Bag Boho Trapillo',
    'categoria' => 'Bolsos & Accesorios',
    'material' => 'Trapillo de Algodón Reciclado',
    'dimensiones' => '35 x 30 cm (Asas: 25 cm)',
    'precio' => 380.00,
    'precio_centavos' => 38000,
    'costo_materiales' => 9500,
    'cantidad_stock' => 6,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Bolsa estilo tote bag resistente tejida con punto espiga tupido, base ovalada reforzada y asas dobles ergonómicas.',
    'svg_illustration' => svg('tote-bag', ['class' => 'card-product-img'])
  ]
];
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
        Prendas, amigurumis, bolsos y piezas de decoración tejidas a mano punto a punto con hilazas suaves y fibras naturales. Monitorea inventarios reales o encarga confecciones a tu medida.
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
        <div class="trust-chip-stitched d-inline-flex align-items-center gap-1">
          <?= svg('branding/isologo-medallon-garantia', ['width' => 20, 'height' => 20]) ?>
          <span>100% Hecho a Mano</span>
        </div>
        <div class="trust-chip-stitched d-inline-flex align-items-center gap-1">
          <?= svg('branding/isotipo-ovillo-corazon', ['width' => 16, 'height' => 16]) ?>
          <span>Envío Seguro Acolchado</span>
        </div>
        <div class="trust-chip-stitched d-inline-flex align-items-center gap-1">
          <?= svg('branding/isologo-sello-taller', ['width' => 20, 'height' => 20]) ?>
          <span>Garantía Taller Oficial</span>
        </div>
      </div>
    </div>

    <!-- Columna Fotografía Artesanal con Marco Pespunte Acolchado -->
    <div class="col-lg-6 text-center">
      <div class="hero-photo-stitched-frame mx-auto">
        <div class="position-relative overflow-hidden" style="border-radius: calc(var(--craft-radius) - 4px);">
          <img src="assets/img/hero_amigurumi.jpg" 
               alt="Colección Artesanal de Amigurumis" 
               class="img-fluid hero-crafted-img" 
               onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
          
          <!-- Fallback SVG Ilustrado Mejorado -->
          <div class="d-none bg-white p-4 text-center">
            <?= svg('amigurumis/dragon-ignis', ['class' => 'img-fluid', 'style' => 'max-height: 280px;']) ?>
          </div>

          <!-- Micro-Badge de Autoría Flotante -->
          <div class="position-absolute bottom-0 start-0 m-3 p-2 px-3 rounded-pill shadow-sm border small fw-bold font-monospace d-inline-flex align-items-center gap-1" style="color: var(--craft-primary); font-size: 0.78rem; z-index: 4; backdrop-filter: blur(8px); background: rgba(255, 255, 255, 0.94) !important;">
            <?= svg('branding/isotipo-ovillo-corazon', ['width' => 18, 'height' => 18]) ?>
            <span>Taller de Creaciones • Colección Artesanal</span>
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
        <span class="text-muted small d-none d-md-inline">Filtra por temática, material, presupuesto o artesano</span>
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
          <input type="text" id="filterSearch" class="form-control input-craft-pill border-start-0" placeholder="Buscar por nombre, hilo o material..." style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
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

    <!-- Fila 2 de Controles: Filtros Avanzados (Presupuesto Min/Max & Artesano Autor) [2.2.D] -->
    <div class="row g-2 align-items-center pt-2 border-top" style="border-color: rgba(228, 232, 237, 0.7) !important;">
      <!-- Rango de Presupuesto -->
      <div class="col-12 col-md-6">
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small fw-bold text-nowrap"><i class="bi bi-cash-coin text-primary me-1"></i>Presupuesto:</span>
          <div class="input-group input-group-sm" style="max-width: 120px;">
            <span class="input-group-text bg-white text-muted">$</span>
            <input type="number" id="filterPriceMin" class="form-control" placeholder="Mín" min="0" max="10000" step="50">
          </div>
          <span class="text-muted small">—</span>
          <div class="input-group input-group-sm" style="max-width: 120px;">
            <span class="input-group-text bg-white text-muted">$</span>
            <input type="number" id="filterPriceMax" class="form-control" placeholder="Máx" min="0" max="10000" step="50">
          </div>
          <small class="text-muted font-monospace d-none d-sm-inline" style="font-size: 0.72rem;">MXN</small>
        </div>
      </div>

      <!-- Filtro por Artesano Titular -->
      <div class="col-12 col-md-6">
        <div class="d-flex align-items-center gap-2 justify-content-md-end">
          <span class="text-muted small fw-bold text-nowrap"><i class="bi bi-person-badge text-primary me-1"></i>Artesano:</span>
          <select id="filterArtisan" class="form-select form-select-sm select-craft-pill" style="max-width: 260px;">
            <option value="all" selected>Todos los Artesanos</option>
            <option value="admin">@admin (Taller Principal)</option>
            <option value="artesana_ana">@artesana_ana (Diseñadora)</option>
          </select>
        </div>
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
    <?= svg('empty-basket', ['width' => 120, 'height' => 110, 'class' => 'mx-auto mb-2']) ?>
  </div>
  <h4 class="fw-bold text-dark mb-2 font-theme-display">No se encontraron piezas artesanales</h4>
  <p class="text-muted small mx-auto mb-4" style="max-width: 460px;">
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
      <span>Mostrando <strong id="paginationShowingCount"><?= count($catalogItems) ?></strong> de <strong id="paginationTotalCount"><?= count($catalogItems) ?></strong> creaciones artesanales</span>
    </div>
    <span class="badge bg-light text-muted font-monospace border px-3 py-2 d-none d-md-inline" style="border-radius: var(--craft-radius-pill); font-size: 0.75rem;">
      <i class="bi bi-clock-history me-1 text-primary"></i>Colección Textil 2026
    </span>
  </div>

  <nav aria-label="Navegación de catálogo">
    <ul class="pagination-craft">
      <li class="page-item disabled">
        <a class="page-link" href="#" aria-label="Anterior" title="Página anterior">
          <i class="bi bi-chevron-left"></i>
        </a>
      </li>
      <li class="page-item active" aria-current="page">
        <a class="page-link" href="#">1</a>
      </li>
      <li class="page-item disabled">
        <a class="page-link" href="#" aria-label="Siguiente" title="Página siguiente">
          <i class="bi bi-chevron-right"></i>
        </a>
      </li>
    </ul>
  </nav>
</section>
