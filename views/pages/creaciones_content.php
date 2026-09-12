<?php
/**
 * Page Content: Gestión e Inventario de Creaciones (Algodón Nórdico)
 * Comprehensive administrative CRUD table with financial metrics and in-situ stock control.
 */

// Mock items aligning with database/seed.sql (sourced from CreacionRepository in Phase 4)
$creacionesList = [
  [
    'id' => 1,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Creador)',
    'nombre' => 'Dragón Ignis',
    'categoria' => 'Amigurumis & Figuras',
    'material' => '100% Algodón Mercerizado',
    'dimensiones' => '18.5 cm (Alto)',
    'precio' => 450.00,
    'precio_centavos' => 45000,
    'costo_materiales' => 120.00,
    'costo_materiales_centavos' => 12000,
    'cantidad_stock' => 4,
    'horas_tejido' => 6.5,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Amigurumi de dragón fantástico tejido a crochet con escamas en relieve, alas articuladas y relleno sintético hipoalergénico de alta densidad.',
    'imagen_url' => 'uploads/dragon_ignis.jpg',
    'svg_slug' => 'creaciones/dragon-ignis',
    'pedidos_asociados' => 1 // Constraint fk_pedidos_creacion prevents deletion
  ],
  [
    'id' => 2,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Creador)',
    'nombre' => 'Mini Suculenta en Maceta',
    'categoria' => 'Hogar & Decoración',
    'material' => 'Algodón Rústico y Lana Acrílica',
    'dimensiones' => '10.0 cm x 8.0 cm',
    'precio' => 180.00,
    'precio_centavos' => 18000,
    'costo_materiales' => 45.00,
    'costo_materiales_centavos' => 4500,
    'cantidad_stock' => 12,
    'horas_tejido' => 2.0,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Pequeña maceta tejida con suculenta en relieve botánico. No requiere riego, ideal para escritorios, repisas y espacios de trabajo.',
    'imagen_url' => 'uploads/suculenta.jpg',
    'svg_slug' => 'creaciones/mini-suculenta',
    'pedidos_asociados' => 0
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
    'costo_materiales' => 85.00,
    'costo_materiales_centavos' => 8500,
    'cantidad_stock' => 0,
    'horas_tejido' => 4.5,
    'es_sobre_encargo' => 1,
    'descripcion' => 'Tierno ajolote mexicano con textura aterciopelada ultra suave, branquias externas en color frambuesa y ojos de seguridad kawaii. Se elabora exclusivamente bajo encargo.',
    'imagen_url' => 'uploads/ajolote.jpg',
    'svg_slug' => 'creaciones/ajolote-pastel',
    'pedidos_asociados' => 1
  ],
  [
    'id' => 4,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Creador)',
    'nombre' => 'Cardigan Granny Squares',
    'categoria' => 'Prendas & Ropa',
    'material' => 'Lana Merino y Algodón Soft',
    'dimensiones' => 'Talla M (95 x 58 cm)',
    'precio' => 980.00,
    'precio_centavos' => 98000,
    'costo_materiales' => 280.00,
    'costo_materiales_centavos' => 28000,
    'cantidad_stock' => 2,
    'horas_tejido' => 18.0,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Cardigan bohemio tejido a mano con cuadros de la abuela (granny squares) florales en paleta nórdica y botones de madera rústica.',
    'imagen_url' => 'uploads/cardigan_granny.jpg',
    'svg_slug' => 'creaciones/cardigan-granny',
    'pedidos_asociados' => 0
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
    'costo_materiales' => 95.00,
    'costo_materiales_centavos' => 9500,
    'cantidad_stock' => 6,
    'horas_tejido' => 4.5,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Bolsa estilo tote bag resistente tejida con punto espiga tupido, base ovalada reforzada y asas dobles ergonómicas.',
    'imagen_url' => 'uploads/tote_bag.jpg',
    'svg_slug' => 'creaciones/tote-bag',
    'pedidos_asociados' => 0
  ]
];

// Calculation of initial KPI metrics
$kpiTotalModelos = count($creacionesList);
$kpiUnidadesStock = 0;
$kpiValorInventario = 0;
$kpiCostoInsumos = 0;
$kpiAgotados = 0;

foreach ($creacionesList as $item) {
  $kpiUnidadesStock += $item['cantidad_stock'];
  $kpiValorInventario += ($item['cantidad_stock'] * $item['precio']);
  $kpiCostoInsumos += ($item['cantidad_stock'] * $item['costo_materiales']);
  if ($item['cantidad_stock'] === 0) {
    $kpiAgotados++;
  }
}
$kpiMargenPromedio = $kpiValorInventario > 0 ? (($kpiValorInventario - $kpiCostoInsumos) / $kpiValorInventario) * 100 : 0;
?>

<!-- CABECERA PRINCIPAL DEL MÓDULO DE GESTIÓN -->
<section class="artisan-module-header card-stitched mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag mb-2">
        <?= svg('branding/isologo-sello-taller', ['width' => 20, 'height' => 20]) ?>
        <span>Inventario &amp; Catálogo del Creador</span>
      </div>
      <h2 class="fw-bold font-theme-display text-dark mb-1">Inventario y Creaciones en Crochet</h2>
      <p class="text-muted small mb-0" style="max-width: 650px;">
        Administración de piezas, costos de insumos, horas de tejido, existencias físicas y modalidades de encargo en la plataforma.
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="formulario.php" class="btn btn-craft-primary btn-craft-stitched d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-circle-fill"></i>
        <span>Nueva Creación</span>
      </a>
      <a href="index.php" class="btn btn-craft-outline btn-craft-outline-stitched d-inline-flex align-items-center gap-2">
        <i class="bi bi-shop"></i>
        <span>Ver Catálogo</span>
      </a>
    </div>
  </div>
</section>

<!-- TARJETAS DE MÉTRICAS KPI DEL INVENTARIO (TIPOGRAFÍA NÓRDICA MEJORADA) -->
<section class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Modelos Registrados</span>
        <i class="bi bi-collection card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-dark" id="kpiCreacionesModelos"><?= $kpiTotalModelos ?></div>
      <div class="card-kpi-desc">Diseños en catálogo activo</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Unidades en Almacén</span>
        <i class="bi bi-box-seam card-kpi-icon" style="color: var(--craft-secondary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-secondary" id="kpiCreacionesStock"><?= $kpiUnidadesStock ?></div>
      <div class="card-kpi-desc">Piezas físicas disponibles</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Valor del Inventario</span>
        <i class="bi bi-cash-coin card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-dark" id="kpiCreacionesValor">$<?= number_format($kpiValorInventario, 2) ?></div>
      <div class="card-kpi-desc">Precio venta total acumulado</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Inversión en Insumos</span>
        <i class="bi bi-piggy-bank card-kpi-icon" style="color: var(--craft-accent-gold);"></i>
      </div>
      <div class="card-kpi-value kpi-val-gold" id="kpiCreacionesCostos">$<?= number_format($kpiCostoInsumos, 2) ?></div>
      <div class="card-kpi-desc">Capital inmovilizado en lanas</div>
    </div>
  </div>
</section>

<!-- ESTACIÓN DE BÚSQUEDA Y FILTRADO ADMINISTRATIVO (2 NIVELES ESPACIOSOS) -->
<section class="card border-0 shadow-sm mb-4 card-stitched" style="border-radius: var(--craft-radius); background-color: #FFFFFF;">
  <div class="card-body p-3 p-md-4">
    <!-- Nivel 1: Búsqueda amplia, contador dinámico y reseteo -->
    <div class="row g-3 align-items-center mb-3">
      <div class="col-12 col-md-7 col-lg-8">
        <div class="input-group">
          <span class="input-group-text bg-white text-muted border-end-0" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" id="searchCreacionInput" class="form-control border-start-0" placeholder="Buscar por nombre, material o técnica..." style="border-top-right-radius: var(--craft-radius-pill); border-bottom-right-radius: var(--craft-radius-pill);">
        </div>
      </div>
      <div class="col-12 col-md-5 col-lg-4 d-flex justify-content-md-end align-items-center gap-2">
        <span class="badge badge-textile-tag" id="creacionesCountBadge">
          <i class="bi bi-box2-heart me-1"></i><?= count($creacionesList) ?> piezas
        </span>
        <button type="button" id="btnResetCreacionFilters" class="btn btn-craft-outline btn-craft-outline-stitched btn-sm d-inline-flex align-items-center gap-1">
          <i class="bi bi-arrow-counterclockwise"></i>
          <span>Restablecer</span>
        </button>
      </div>
    </div>

    <!-- Separador Pespunte Hilvanado -->
    <hr class="divider-stitched my-3">

    <!-- Nivel 2: 4 selectores bien distribuidos con espacio completo -->
    <div class="row g-2 g-md-3">
      <!-- Filtro por Categoría -->
      <div class="col-12 col-sm-6 col-md-3">
        <label for="filterCategorySelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Categoría</label>
        <select id="filterCategorySelect" class="form-select select-craft-pill">
          <option value="all" selected>Todas las categorías</option>
          <option value="Amigurumis & Figuras">Amigurumis & Figuras</option>
          <option value="Prendas & Ropa">Prendas & Ropa</option>
          <option value="Bolsos & Accesorios">Bolsos & Accesorios</option>
          <option value="Hogar & Decoración">Hogar & Decoración</option>
          <option value="Bebé & Infantil">Bebé & Infantil</option>
        </select>
      </div>

      <!-- Filtro por Estado de Stock -->
      <div class="col-12 col-sm-6 col-md-3">
        <label for="filterStockStatusSelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Disponibilidad</label>
        <select id="filterStockStatusSelect" class="form-select select-craft-pill">
          <option value="all" selected>Todo el inventario</option>
          <option value="in-stock">En Existencia (> 0)</option>
          <option value="low-stock">Stock Crítico (≤ 3)</option>
          <option value="out-of-stock">Agotados (0 u.)</option>
          <option value="on-demand">Bajo Encargo</option>
        </select>
      </div>

      <!-- Filtro por Artesano -->
      <div class="col-12 col-sm-6 col-md-3">
        <label for="filterArtisanSelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Artesano Autor</label>
        <select id="filterArtisanSelect" class="form-select select-craft-pill">
          <option value="all" selected>Todos los artesanos</option>
          <option value="admin">@admin (Artesano Registrado)</option>
          <option value="artesana_ana">@artesana_ana</option>
        </select>
      </div>

      <!-- Ordenación -->
      <div class="col-12 col-sm-6 col-md-3">
        <label for="sortCreacionesSelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Ordenar por</label>
        <select id="sortCreacionesSelect" class="form-select select-craft-pill">
          <option value="name-asc" selected>Nombre: A &rarr; Z</option>
          <option value="price-desc">Precio: Mayor a Menor</option>
          <option value="price-asc">Precio: Menor a Mayor</option>
          <option value="stock-desc">Stock: Mayor a Menor</option>
          <option value="stock-asc">Stock: Menor a Mayor</option>
          <option value="rate-desc">Rentabilidad: $/hr</option>
        </select>
      </div>
    </div>
  </div>
</section>

<!-- CUADRÍCULA ADMINISTRATIVA DE CARDS 3X -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-xl-4 mb-4" id="creacionesGrid">
  <?php foreach ($creacionesList as $item): 
    $margenNeto = $item['precio'] - $item['costo_materiales'];
    $margenPct = $item['precio'] > 0 ? ($margenNeto / $item['precio']) * 100 : 0;
    $retornoHora = $item['horas_tejido'] > 0 ? $margenNeto / $item['horas_tejido'] : 0;
    $isOutOfStock = ($item['cantidad_stock'] === 0 && empty($item['es_sobre_encargo']));
    $isLowStock = ($item['cantidad_stock'] > 0 && $item['cantidad_stock'] <= 3);
  ?>
  <div class="col"
       id="creacionCardCol_<?= $item['id'] ?>"
       data-id="<?= $item['id'] ?>"
       data-nombre="<?= htmlspecialchars($item['nombre']) ?>"
       data-categoria="<?= htmlspecialchars($item['categoria']) ?>"
       data-material="<?= htmlspecialchars($item['material']) ?>"
       data-dimensiones="<?= htmlspecialchars($item['dimensiones']) ?>"
       data-precio="<?= $item['precio'] ?>"
       data-costo="<?= $item['costo_materiales'] ?>"
       data-stock="<?= $item['cantidad_stock'] ?>"
       data-horas="<?= $item['horas_tejido'] ?>"
       data-artisan="<?= htmlspecialchars($item['artesano_username']) ?>"
       data-on-demand="<?= $item['es_sobre_encargo'] ?>"
       data-descripcion="<?= htmlspecialchars($item['descripcion']) ?>"
       data-pedidos="<?= $item['pedidos_asociados'] ?>">

    <div class="card card-admin-creacion card-stitched h-100 border-0 shadow-sm">
      <!-- Encabezado Superior de la Card: Foto Mat, ID y Badge Modalidad -->
      <div class="card-admin-top p-3 pb-0">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="badge bg-light text-muted font-monospace border small">
            #<?= $item['id'] ?>
          </span>
          <!-- Modalidad Toggle Button -->
          <?php if ($item['es_sobre_encargo'] == 1): ?>
            <button type="button" class="btn-toggle-encargo badge badge-textile-tag text-primary border-primary border-0 bg-transparent p-1" style="font-size: 0.73rem;" title="Click para cambiar a Entrega Inmediata">
              <i class="bi bi-magic me-1"></i>Bajo Encargo
            </button>
          <?php else: ?>
            <button type="button" class="btn-toggle-encargo badge bg-light text-dark border font-monospace border-0 p-1" style="border-radius: var(--craft-radius-pill); font-size: 0.72rem;" title="Click para cambiar a Bajo Encargo">
              <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Inmediata
            </button>
          <?php endif; ?>
        </div>

        <!-- Marco Fotográfico Acolchado Pespunteado -->
        <div class="admin-card-photo-frame mb-3">
          <?= svg($item['svg_slug'], ['class' => 'admin-card-photo-img']) ?>
        </div>
      </div>

      <!-- Cuerpo de la Card -->
      <div class="card-body p-3 pt-0 d-flex flex-column">
        <!-- Título y Categoría -->
        <div class="mb-2">
          <h5 class="fw-bold font-theme-display text-dark mb-1 text-truncate" title="<?= htmlspecialchars($item['nombre']) ?>">
            <?= htmlspecialchars($item['nombre']) ?>
          </h5>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="badge badge-textile-tag" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">
              <?= htmlspecialchars($item['categoria']) ?>
            </span>
            <span class="card-product-dimension" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;" title="Dimensiones: <?= htmlspecialchars($item['dimensiones']) ?>">
              <i class="bi bi-rulers me-1"></i><span><?= htmlspecialchars($item['dimensiones']) ?></span>
            </span>
          </div>
          <small class="text-muted d-block text-truncate mt-1" style="font-size: 0.74rem;" title="<?= htmlspecialchars($item['material']) ?>">
            <i class="bi bi-palette2 me-1"></i><?= htmlspecialchars($item['material']) ?>
          </small>
        </div>

        <!-- Desglose Económico del Creador -->
        <div class="admin-card-economics p-2 rounded mb-3">
          <div class="d-flex justify-content-between align-items-baseline mb-1">
            <span class="fw-bold font-monospace text-dark fs-6">$<?= number_format($item['precio'], 2) ?></span>
            <small class="text-muted font-monospace" style="font-size: 0.72rem;">Costo: $<?= number_format($item['costo_materiales'], 2) ?></small>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-1">
            <span class="badge-profit-pill" style="font-size: 0.72rem; padding: 0.18rem 0.45rem;">
              <i class="bi bi-graph-up-arrow"></i> +$<?= number_format($margenNeto, 2) ?> (<?= round($margenPct) ?>%)
            </span>
            <span class="badge-hourly-rate" style="font-size: 0.7rem; padding: 0.18rem 0.45rem;">
              <i class="bi bi-clock-history"></i> $<?= number_format($retornoHora, 2) ?>/hr
            </span>
          </div>
        </div>

        <!-- Control de Stock In-Situ y Estado -->
        <div class="d-flex justify-content-between align-items-center mb-3 mt-auto pt-2 border-top">
          <div class="quick-stock-control">
            <button type="button" class="quick-stock-btn btn-stock-dec" data-id="<?= $item['id'] ?>" title="Disminuir 1 unidad">-</button>
            <span class="quick-stock-val" id="stockVal_<?= $item['id'] ?>"><?= $item['cantidad_stock'] ?></span>
            <button type="button" class="quick-stock-btn btn-stock-inc" data-id="<?= $item['id'] ?>" title="Aumentar 1 unidad">+</button>
          </div>

          <div id="stockBadgeContainer_<?= $item['id'] ?>">
            <?php if ($item['es_sobre_encargo'] == 1): ?>
              <span class="badge bg-light text-muted font-monospace border" style="font-size: 0.68rem;">Bajo Encargo</span>
            <?php elseif ($item['cantidad_stock'] === 0): ?>
              <span class="badge-stock-alert" style="font-size: 0.68rem;">Agotado</span>
            <?php elseif ($isLowStock): ?>
              <span class="badge-stock-critical" style="font-size: 0.68rem;">Stock Crítico</span>
            <?php else: ?>
              <span class="badge badge-stock-in" style="font-size: 0.7rem;">En Existencia</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Pie de Card: Autor y Botones de Acción CRUD -->
        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1">
          <!-- Autor -->
          <div class="d-flex align-items-center gap-1 text-truncate" style="max-width: 120px;" title="@<?= htmlspecialchars($item['artesano_username']) ?>">
            <div class="user-avatar-circle" style="width: 24px; height: 24px; font-size: 0.7rem;">
              <?= strtoupper(substr($item['artesano_username'], 0, 1)) ?>
            </div>
            <small class="text-dark fw-bold text-truncate" style="font-size: 0.74rem;">@<?= htmlspecialchars($item['artesano_username']) ?></small>
          </div>

          <!-- Acciones CRUD -->
          <div class="d-inline-flex gap-1">
            <button type="button" class="artisan-action-btn btn-inspect-creacion" data-id="<?= $item['id'] ?>" title="Ver Ficha Técnica">
              <i class="bi bi-eye"></i>
            </button>
            <a href="formulario.php?id=<?= $item['id'] ?>" class="artisan-action-btn" title="Editar Creación">
              <i class="bi bi-pencil-square text-primary"></i>
            </a>
            <button type="button" class="artisan-action-btn btn-delete btn-card-delete" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalEliminarCreacion" 
                    data-id="<?= $item['id'] ?>" 
                    data-name="<?= htmlspecialchars($item['nombre']) ?>"
                    title="<?= $item['pedidos_asociados'] > 0 ? 'Restringido: Tiene pedidos asociados (ON DELETE RESTRICT)' : 'Eliminar creación' ?>">
              <i class="bi bi-trash text-danger"></i>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Estado Vacío cuando no hay coincidencias de filtros -->
<div id="emptyCreacionesState" class="d-none text-center py-5 p-4 card border-0 shadow-sm card-stitched" style="border-radius: var(--craft-radius); background: #FFFFFF;">
  <div class="mb-3">
    <?= svg('empty-basket', ['width' => 100, 'height' => 90, 'class' => 'mx-auto mb-2']) ?>
  </div>
  <h5 class="fw-bold font-theme-display text-dark mb-1">No se encontraron piezas en el almacén</h5>
  <p class="text-muted small mx-auto mb-3" style="max-width: 420px;">
    Ninguna creación coincide con los criterios de búsqueda o filtros seleccionados. Prueba a restablecer los filtros para ver todo el inventario.
  </p>
  <button type="button" class="btn btn-craft-outline btn-craft-outline-stitched btn-sm px-3" id="btnResetEmptyCreaciones">
    <i class="bi bi-arrow-counterclockwise me-1"></i>Restablecer Filtros
  </button>
</div>
