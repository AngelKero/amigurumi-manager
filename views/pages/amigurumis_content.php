<?php
/**
 * Page Content: Gestión e Inventario de Amigurumis (Algodón Nórdico)
 * Comprehensive administrative CRUD table with financial metrics and in-situ stock control.
 */

// Mock items aligning with database/seed.sql (sourced from AmigurumiRepository in Phase 4)
$amigurumisList = [
  [
    'id' => 1,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Taller Principal)',
    'nombre' => 'Dragón Ignis',
    'categoria' => 'Fantasía',
    'material' => '100% Algodón Mercerizado',
    'tamano_cm' => 18.5,
    'precio' => 450.00,
    'precio_centavos' => 45000,
    'costo_materiales' => 120.00,
    'costo_materiales_centavos' => 12000,
    'cantidad_stock' => 4,
    'horas_tejido' => 6.5,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Amigurumi de dragón fantástico tejido a crochet con escamas en relieve, alas articuladas y relleno sintético hipoalergénico de alta densidad.',
    'imagen_url' => 'uploads/dragon_ignis.jpg',
    'svg_slug' => 'amigurumis/dragon-ignis',
    'pedidos_asociados' => 1 // Constraint fk_pedidos_amigurumi prevents deletion
  ],
  [
    'id' => 2,
    'artesano_id' => 1,
    'artesano_username' => 'admin',
    'artesano_nombre' => 'Admin (Taller Principal)',
    'nombre' => 'Mini Suculenta en Maceta',
    'categoria' => 'Plantas / Botánica',
    'material' => 'Algodón Rústico y Lana Acrílica',
    'tamano_cm' => 10.0,
    'precio' => 180.00,
    'precio_centavos' => 18000,
    'costo_materiales' => 45.00,
    'costo_materiales_centavos' => 4500,
    'cantidad_stock' => 12,
    'horas_tejido' => 2.0,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Pequeña maceta tejida con suculenta en relieve botánico. No requiere riego, ideal para escritorios, repisas y espacios de trabajo.',
    'imagen_url' => 'uploads/suculenta.jpg',
    'svg_slug' => 'amigurumis/mini-suculenta',
    'pedidos_asociados' => 0
  ],
  [
    'id' => 3,
    'artesano_id' => 2,
    'artesano_username' => 'artesana_ana',
    'artesano_nombre' => 'Ana Diseñadora',
    'nombre' => 'Ajolote Rosado Pastel',
    'categoria' => 'Animales / Fauna',
    'material' => 'Hilo Chenille Terciopelo',
    'tamano_cm' => 14.0,
    'precio' => 320.00,
    'precio_centavos' => 32000,
    'costo_materiales' => 85.00,
    'costo_materiales_centavos' => 8500,
    'cantidad_stock' => 0,
    'horas_tejido' => 4.5,
    'es_sobre_encargo' => 1,
    'descripcion' => 'Tierno ajolote mexicano con textura aterciopelada ultra suave, branquias externas en color frambuesa y ojos de seguridad kawaii. Se elabora exclusivamente bajo encargo.',
    'imagen_url' => 'uploads/ajolote.jpg',
    'svg_slug' => 'amigurumis/ajolote-pastel',
    'pedidos_asociados' => 1
  ]
];

// Calculation of initial KPI metrics
$kpiTotalModelos = count($amigurumisList);
$kpiUnidadesStock = 0;
$kpiValorInventario = 0;
$kpiCostoInsumos = 0;
$kpiAgotados = 0;

foreach ($amigurumisList as $item) {
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
        <i class="bi bi-box2-heart-fill text-primary"></i>
        <span>Almacén & Catálogo del Taller</span>
      </div>
      <h2 class="fw-bold font-theme-display text-dark mb-1">Inventario y Creaciones de Amigurumis</h2>
      <p class="text-muted small mb-0" style="max-width: 650px;">
        Control administrativo de piezas, costos de insumos, márgenes de labor, existencias físicas y modalidades de confección.
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

<!-- TARJETAS DE MÉTRICAS KPI DEL INVENTARIO -->
<section class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase d-flex justify-content-between align-items-center">
        <span>Modelos Registrados</span>
        <i class="bi bi-collection text-primary"></i>
      </div>
      <div class="fs-2 fw-extrabold text-dark font-monospace" id="kpiAmigurumiModelos"><?= $kpiTotalModelos ?></div>
      <div class="text-muted small">Diseños en catálogo activo</div>
    </div>
  </div>

  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase d-flex justify-content-between align-items-center">
        <span>Unidades en Almacén</span>
        <i class="bi bi-box-seam text-success"></i>
      </div>
      <div class="fs-2 fw-extrabold text-success font-monospace" id="kpiAmigurumiStock"><?= $kpiUnidadesStock ?></div>
      <div class="text-muted small">Piezas físicas disponibles</div>
    </div>
  </div>

  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase d-flex justify-content-between align-items-center">
        <span>Valor del Inventario</span>
        <i class="bi bi-cash-coin text-primary"></i>
      </div>
      <div class="fs-2 fw-extrabold text-dark font-monospace" id="kpiAmigurumiValor">$<?= number_format($kpiValorInventario, 2) ?></div>
      <div class="text-muted small">Precio venta total acumulado</div>
    </div>
  </div>

  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase d-flex justify-content-between align-items-center">
        <span>Inversión en Insumos</span>
        <i class="bi bi-piggy-bank text-warning"></i>
      </div>
      <div class="fs-2 fw-extrabold text-warning-emphasis font-monospace" id="kpiAmigurumiCostos">$<?= number_format($kpiCostoInsumos, 2) ?></div>
      <div class="text-muted small">Capital inmovilizado en lanas</div>
    </div>
  </div>
</section>

<!-- ESTACIÓN DE BÚSQUEDA Y FILTRADO ADMINISTRATIVO -->
<section class="card border-0 shadow-sm mb-4 card-stitched" style="border-radius: var(--craft-radius); background-color: #FFFFFF;">
  <div class="card-body p-3 p-md-4">
    <div class="row g-2 align-items-center">
      <!-- Búsqueda en Vivo -->
      <div class="col-12 col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-white text-muted border-end-0" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" id="searchAmigurumiInput" class="form-control border-start-0" placeholder="Buscar amigurumi o material..." style="border-top-right-radius: var(--craft-radius-pill); border-bottom-right-radius: var(--craft-radius-pill);">
        </div>
      </div>

      <!-- Filtro por Categoría -->
      <div class="col-6 col-md-2">
        <select id="filterCategorySelect" class="form-select select-craft-pill">
          <option value="all" selected>Categoría: Todas</option>
          <option value="Fantasía">Fantasía</option>
          <option value="Plantas / Botánica">Plantas / Botánica</option>
          <option value="Animales / Fauna">Animales / Fauna</option>
        </select>
      </div>

      <!-- Filtro por Estado de Stock -->
      <div class="col-6 col-md-2">
        <select id="filterStockStatusSelect" class="form-select select-craft-pill">
          <option value="all" selected>Stock: Todos</option>
          <option value="in-stock">En Stock (> 0)</option>
          <option value="low-stock">Stock Bajo (≤ 3)</option>
          <option value="out-of-stock">Agotados (0)</option>
          <option value="on-demand">Bajo Encargo</option>
        </select>
      </div>

      <!-- Filtro por Artesano -->
      <div class="col-6 col-md-2">
        <select id="filterArtisanSelect" class="form-select select-craft-pill">
          <option value="all" selected>Artesano: Todos</option>
          <option value="admin">@admin (Taller Principal)</option>
          <option value="artesana_ana">@artesana_ana</option>
        </select>
      </div>

      <!-- Ordenación -->
      <div class="col-4 col-md-1">
        <select id="sortAmigurumisSelect" class="form-select select-craft-pill" title="Ordenar lista">
          <option value="name-asc" selected>A-Z</option>
          <option value="price-desc">Precio: Mayor</option>
          <option value="price-asc">Precio: Menor</option>
          <option value="stock-desc">Stock: Mayor</option>
          <option value="stock-asc">Stock: Menor</option>
          <option value="rate-desc">Rentabilidad $/hr</option>
        </select>
      </div>

      <!-- Botón Restablecer Filtros -->
      <div class="col-2 col-md-1 d-grid">
        <button type="button" id="btnResetAmigurumiFilters" class="btn btn-craft-outline btn-craft-outline-stitched" title="Restablecer filtros">
          <i class="bi bi-arrow-counterclockwise"></i>
        </button>
      </div>
    </div>
  </div>
</section>

<!-- TABLA ADMINISTRATIVA DE AMIGURUMIS -->
<div class="card border-0 shadow-sm card-stitched overflow-hidden mb-4" style="border-radius: var(--craft-radius); background: #FFFFFF;">
  <div class="table-responsive table-responsive-craft">
    <table class="table table-artisan-amigurumis align-middle mb-0" id="amigurumisTable">
      <thead>
        <tr>
          <th style="width: 70px;">Pieza</th>
          <th>Nombre & Clasificación</th>
          <th>Economía del Taller</th>
          <th class="text-center" style="width: 170px;">Stock en Almacén</th>
          <th class="text-center">Modalidad</th>
          <th>Artesano</th>
          <th class="text-end" style="width: 140px;">Acciones</th>
        </tr>
      </thead>
      <tbody id="amigurumisTableBody">
        <?php foreach ($amigurumisList as $item): 
          $margenNeto = $item['precio'] - $item['costo_materiales'];
          $margenPct = $item['precio'] > 0 ? ($margenNeto / $item['precio']) * 100 : 0;
          $retornoHora = $item['horas_tejido'] > 0 ? $margenNeto / $item['horas_tejido'] : 0;
          $isOutOfStock = ($item['cantidad_stock'] === 0 && empty($item['es_sobre_encargo']));
          $isLowStock = ($item['cantidad_stock'] > 0 && $item['cantidad_stock'] <= 3);
        ?>
        <tr id="amigurumiRow_<?= $item['id'] ?>"
            data-id="<?= $item['id'] ?>"
            data-nombre="<?= htmlspecialchars($item['nombre']) ?>"
            data-categoria="<?= htmlspecialchars($item['categoria']) ?>"
            data-material="<?= htmlspecialchars($item['material']) ?>"
            data-tamano="<?= $item['tamano_cm'] ?>"
            data-precio="<?= $item['precio'] ?>"
            data-costo="<?= $item['costo_materiales'] ?>"
            data-stock="<?= $item['cantidad_stock'] ?>"
            data-horas="<?= $item['horas_tejido'] ?>"
            data-artisan="<?= htmlspecialchars($item['artesano_username']) ?>"
            data-on-demand="<?= $item['es_sobre_encargo'] ?>"
            data-descripcion="<?= htmlspecialchars($item['descripcion']) ?>"
            data-pedidos="<?= $item['pedidos_asociados'] ?>">
          
          <!-- Miniatura & ID -->
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="font-monospace fw-bold text-muted small">#<?= $item['id'] ?></span>
              <div class="amigurumi-thumb-frame">
                <?= svg($item['svg_slug'], ['class' => 'amigurumi-thumb-img']) ?>
              </div>
            </div>
          </td>

          <!-- Nombre & Clasificación -->
          <td>
            <div class="d-flex align-items-baseline gap-1">
              <strong class="text-dark font-theme-display fs-6 text-truncate" style="max-width: 220px; display: inline-block;">
                <?= htmlspecialchars($item['nombre']) ?>
              </strong>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1">
              <span class="badge badge-textile-tag" style="font-size: 0.72rem; padding: 0.15rem 0.5rem;">
                <?= htmlspecialchars($item['categoria']) ?>
              </span>
              <small class="text-muted font-monospace" style="font-size: 0.75rem;">
                <i class="bi bi-rulers me-1"></i><?= $item['tamano_cm'] ?> cm
              </small>
            </div>
            <small class="text-muted d-block text-truncate mt-1" style="max-width: 240px; font-size: 0.75rem;">
              <i class="bi bi-palette2 me-1"></i><?= htmlspecialchars($item['material']) ?>
            </small>
          </td>

          <!-- Economía del Taller (Precio, Costo, Margen, Retorno) -->
          <td>
            <div class="d-flex align-items-baseline gap-2">
              <span class="fw-bold font-monospace text-dark fs-6">$<?= number_format($item['precio'], 2) ?></span>
              <small class="text-muted font-monospace" style="font-size: 0.75rem;">(Costo: $<?= number_format($item['costo_materiales'], 2) ?>)</small>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1">
              <span class="badge-profit-pill">
                <i class="bi bi-graph-up-arrow"></i> +$<?= number_format($margenNeto, 2) ?> (<?= round($margenPct) ?>%)
              </span>
              <span class="badge-hourly-rate">
                <i class="bi bi-clock-history"></i> $<?= number_format($retornoHora, 2) ?>/hr
              </span>
            </div>
          </td>

          <!-- Stock Físico con Ajuste Rápido In-Situ -->
          <td class="text-center">
            <div class="quick-stock-control mb-1">
              <button type="button" class="quick-stock-btn btn-stock-dec" data-id="<?= $item['id'] ?>" title="Disminuir 1 unidad">-</button>
              <span class="quick-stock-val" id="stockVal_<?= $item['id'] ?>"><?= $item['cantidad_stock'] ?></span>
              <button type="button" class="quick-stock-btn btn-stock-inc" data-id="<?= $item['id'] ?>" title="Aumentar 1 unidad">+</button>
            </div>
            
            <div id="stockBadgeContainer_<?= $item['id'] ?>">
              <?php if ($item['es_sobre_encargo'] == 1): ?>
                <span class="badge bg-light text-muted font-monospace border" style="font-size: 0.7rem;">Bajo Encargo</span>
              <?php elseif ($item['cantidad_stock'] === 0): ?>
                <span class="badge-stock-alert">Agotado</span>
              <?php elseif ($isLowStock): ?>
                <span class="badge-stock-critical">Stock Crítico</span>
              <?php else: ?>
                <span class="badge badge-stock-in" style="font-size: 0.72rem;">En Existencia</span>
              <?php endif; ?>
            </div>
          </td>

          <!-- Modalidad de Confección -->
          <td class="text-center">
            <?php if ($item['es_sobre_encargo'] == 1): ?>
              <button type="button" class="btn-toggle-encargo badge badge-textile-tag text-primary border-primary border-0 bg-transparent p-1" style="font-size: 0.75rem;" title="Click para cambiar a Entrega Inmediata (Con stock)">
                <i class="bi bi-magic me-1"></i>Bajo Encargo (5-7 d)
              </button>
            <?php else: ?>
              <button type="button" class="btn-toggle-encargo badge bg-light text-dark border font-monospace border-0 p-1" style="border-radius: var(--craft-radius-pill); font-size: 0.72rem;" title="Click para cambiar a Bajo Encargo Exclusivo">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Inmediata
              </button>
            <?php endif; ?>
          </td>

          <!-- Artesano Responsable -->
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="user-avatar-circle" style="width: 30px; height: 30px; font-size: 0.75rem;">
                <?= strtoupper(substr($item['artesano_username'], 0, 1)) ?>
              </div>
              <div>
                <strong class="d-block text-dark small">@<?= htmlspecialchars($item['artesano_username']) ?></strong>
                <small class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($item['artesano_nombre']) ?></small>
              </div>
            </div>
          </td>

          <!-- Acciones CRUD -->
          <td class="text-end">
            <div class="d-inline-flex gap-1">
              <button type="button" class="artisan-action-btn btn-inspect-amigurumi" data-id="<?= $item['id'] ?>" title="Ver Ficha Técnica">
                <i class="bi bi-eye"></i>
              </button>
              <a href="formulario.php?id=<?= $item['id'] ?>" class="artisan-action-btn" title="Editar Amigurumi">
                <i class="bi bi-pencil-square text-primary"></i>
              </a>
              <button type="button" class="artisan-action-btn btn-delete btn-card-delete" 
                      data-bs-toggle="modal" 
                      data-bs-target="#modalEliminarAmigurumi" 
                      data-id="<?= $item['id'] ?>" 
                      data-name="<?= htmlspecialchars($item['nombre']) ?>"
                      title="<?= $item['pedidos_asociados'] > 0 ? 'Restringido: Tiene pedidos asociados (ON DELETE RESTRICT)' : 'Eliminar amigurumi' ?>">
                <i class="bi bi-trash text-danger"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Estado Vacío cuando no hay coincidencias de filtros -->
  <div id="emptyAmigurumisState" class="d-none text-center py-5 p-4">
    <div class="mb-3">
      <?= svg('empty-basket', ['width' => 100, 'height' => 90, 'class' => 'mx-auto mb-2']) ?>
    </div>
    <h5 class="fw-bold font-theme-display text-dark mb-1">No se encontraron piezas en el almacén</h5>
    <p class="text-muted small mx-auto mb-3" style="max-width: 420px;">
      Ningún amigurumi coincide con los criterios de búsqueda o filtros seleccionados. Prueba a restablecer los filtros para ver todo el inventario.
    </p>
    <button type="button" class="btn btn-craft-outline btn-craft-outline-stitched btn-sm px-3" id="btnResetEmptyAmigurumis">
      <i class="bi bi-arrow-counterclockwise me-1"></i>Restablecer Filtros
    </button>
  </div>
</div>
