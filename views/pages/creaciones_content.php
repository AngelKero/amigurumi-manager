<?php
/**
 * Page Content: Gestión e Inventario de Creaciones (Algodón Nórdico)
 *
 * Panel server-driven (Subfase 4.3 · Feature 006): la rejilla #creacionesGrid se
 * puebla desde `GET /api/creaciones/index.php` vía src/js/modules/creaciones.js.
 * La tarjeta administrativa vive en el <template id="creacionCardTemplate">
 * (marcado constante); los datos se inyectan con DOM APIs / textContent (H-004).
 * Los KPIs se calculan contra el total real del servidor (fetchKpis).
 */
?>

<!-- CABECERA PRINCIPAL DEL MÓDULO DE GESTIÓN -->
<section class="artisan-module-header card-stitched mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag mb-2">
        <?= svg('branding/isologo-sello-taller', ['width' => 20, 'height' => 20]) ?>
        <span>Inventario &amp; Catálogo del Creador</span>
      </div>
      <h1 class="h2 fw-bold font-theme-display text-dark mb-1">Inventario y Creaciones en Crochet</h1>
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
      <div class="card-kpi-value kpi-val-dark" id="kpiCreacionesModelos">0</div>
      <div class="card-kpi-desc">Diseños en catálogo activo</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Unidades en Almacén</span>
        <i class="bi bi-box-seam card-kpi-icon" style="color: var(--craft-secondary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-secondary" id="kpiCreacionesStock">0</div>
      <div class="card-kpi-desc">Piezas físicas disponibles</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Valor del Inventario</span>
        <i class="bi bi-cash-coin card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-dark" id="kpiCreacionesValor">$0.00</div>
      <div class="card-kpi-desc">Precio venta total acumulado</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Inversión en Insumos</span>
        <i class="bi bi-piggy-bank card-kpi-icon" style="color: var(--craft-accent-gold);"></i>
      </div>
      <div class="card-kpi-value kpi-val-gold" id="kpiCreacionesCostos">$0.00</div>
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
          <i class="bi bi-box2-heart me-1"></i>0 de 0 piezas
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
      <div class="col-12 col-sm-6 col-md-4">
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
      <div class="col-12 col-sm-6 col-md-4">
        <label for="filterStockStatusSelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Disponibilidad</label>
        <select id="filterStockStatusSelect" class="form-select select-craft-pill">
          <option value="all" selected>Todo el inventario</option>
          <option value="en_stock">En Existencia (> 0)</option>
          <option value="bajo_encargo">Bajo Encargo</option>
          <option value="agotados">Agotados (0 u.)</option>
        </select>
      </div>

      <!-- Filtro por Artesano -->
      <div class="col-12 col-sm-6 col-md-4">
        <label for="filterArtisanSelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Artesano Autor</label>
        <select id="filterArtisanSelect" class="form-select select-craft-pill">
          <option value="all" selected>Todos los artesanos</option>
        </select>
      </div>

      <!-- Ordenación -->
      <div class="col-12 col-sm-6 col-md-4">
        <label for="sortCreacionesSelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Ordenar por</label>
        <select id="sortCreacionesSelect" class="form-select select-craft-pill">
          <option value="recientes" selected>Más recientes</option>
          <option value="precio_desc">Precio: Mayor a Menor</option>
          <option value="precio_asc">Precio: Menor a Mayor</option>
          <option value="nombre_asc">Nombre: A &rarr; Z</option>
          <option value="nombre_desc">Nombre: Z &rarr; A</option>
          <option value="stock_desc">Stock: Mayor a Menor</option>
        </select>
      </div>

      <!-- Estado en catálogo (papelera) -->
      <div class="col-12 col-sm-6 col-md-4">
        <label for="filterEstadoSelect" class="form-label small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Estado</label>
        <select id="filterEstadoSelect" class="form-select select-craft-pill">
          <option value="activas" selected>Activas en catálogo</option>
          <option value="inactivas">Inactivas (papelera)</option>
          <option value="todas">Todas</option>
        </select>
      </div>
    </div>
  </div>
</section>

<!-- CUADRÍCULA ADMINISTRATIVA (poblada vía API · Subfase 4.3) -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-xl-4 mb-4" id="creacionesGrid" data-total="0" aria-live="polite">
  <div class="col-12" id="creacionesLoadingState">
    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
      <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
      <span class="small">Tejiendo tu inventario…</span>
    </div>
  </div>
</div>

<!-- TEMPLATE DE TARJETA ADMINISTRATIVA (marcado constante · datos vía DOM APIs, H-004) -->
<template id="creacionCardTemplate">
  <div class="col" data-part="cardCol">
    <div class="card card-admin-creacion card-stitched h-100 border-0 shadow-sm">
      <div class="card-admin-top p-3 pb-0">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="badge bg-light text-muted font-monospace border small" data-part="idBadge"></span>
          <button type="button" class="btn-toggle-encargo" data-part="encargoToggle" title="Cambiar modalidad"></button>
        </div>
        <div class="admin-card-photo-frame mb-3">
          <img class="admin-card-photo-img" data-part="photo" alt="" style="object-fit: cover;">
        </div>
      </div>

      <div class="card-body p-3 pt-0 d-flex flex-column">
        <div class="mb-2">
          <h5 class="fw-bold font-theme-display text-dark mb-1 text-truncate" data-part="nombre"></h5>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="badge badge-textile-tag" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;" data-part="categoria"></span>
            <span class="card-product-dimension" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;">
              <i class="bi bi-rulers me-1"></i><span data-part="dimensiones"></span>
            </span>
          </div>
          <small class="text-muted d-block text-truncate mt-1" style="font-size: 0.74rem;">
            <i class="bi bi-palette2 me-1"></i><span data-part="material"></span>
          </small>
        </div>

        <div class="admin-card-economics p-2 rounded mb-3">
          <div class="d-flex justify-content-between align-items-baseline mb-1">
            <span class="fw-bold font-monospace text-dark fs-6" data-part="precio"></span>
            <small class="text-muted font-monospace" style="font-size: 0.72rem;" data-part="costo"></small>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-1">
            <span class="badge-profit-pill" style="font-size: 0.72rem; padding: 0.18rem 0.45rem;" data-part="margen"></span>
            <span class="badge-hourly-rate" style="font-size: 0.7rem; padding: 0.18rem 0.45rem;" data-part="retorno"></span>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 mt-auto pt-2 border-top">
          <div class="quick-stock-control">
            <button type="button" class="quick-stock-btn btn-stock-dec" data-part="stockDec" title="Disminuir 1 unidad">-</button>
            <span class="quick-stock-val" data-part="stockVal"></span>
            <button type="button" class="quick-stock-btn btn-stock-inc" data-part="stockInc" title="Aumentar 1 unidad">+</button>
          </div>
          <div data-part="stockBadge"></div>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1">
          <div class="d-flex align-items-center gap-1 text-truncate" style="max-width: 120px;">
            <div class="avatar-artisan-initials" style="width: 24px; height: 24px; font-size: 0.7rem;" data-part="authorInitial"></div>
            <small class="text-dark fw-bold text-truncate" style="font-size: 0.74rem;" data-part="authorName"></small>
          </div>
          <div class="d-inline-flex gap-1">
            <button type="button" class="artisan-action-btn btn-inspect-creacion" data-part="inspectBtn" title="Ver Ficha Técnica">
              <i class="bi bi-eye"></i>
            </button>
            <a href="#" class="artisan-action-btn" data-part="editLink" title="Editar Creación">
              <i class="bi bi-pencil-square text-primary"></i>
            </a>
            <button type="button" class="artisan-action-btn btn-card-restore" data-bs-toggle="modal" data-bs-target="#modalRestaurarCreacion" data-part="restoreBtn" title="Restaurar pieza dada de baja">
              <i class="bi bi-arrow-counterclockwise text-success"></i>
            </button>
            <button type="button" class="artisan-action-btn btn-delete btn-card-delete"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEliminarCreacion"
                    data-part="deleteBtn"
                    title="Eliminar creación">
              <i class="bi bi-trash text-danger"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- ESTACIÓN DE PAGINACIÓN DEL INVENTARIO -->
<section class="pagination-craft-station card-stitched d-flex flex-wrap justify-content-between align-items-center gap-3 my-4">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="pagination-results-chip">
      <i class="bi bi-collection-fill" style="color: var(--craft-primary);"></i>
      <span>Mostrando <strong id="creacionesShowingFrom">0</strong>–<strong id="creacionesShowingTo">0</strong> de <strong id="creacionesTotalCount">0</strong> piezas del inventario</span>
    </div>
  </div>
  <nav aria-label="Navegación del inventario" id="creacionesPaginationNav">
    <ul class="pagination-craft"></ul>
  </nav>
</section>

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
