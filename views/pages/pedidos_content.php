<?php
/**
 * Page Content: Dashboard de Gestión de Pedidos y Encargos
 * Algodón Nórdico Design System
 *
 * Panel server-driven (Subfase 4.4 · Feature 007): la rejilla #ordersGrid se
 * puebla desde `GET /api/pedidos/index.php` vía src/js/modules/orders.js.
 * La tarjeta vive en el <template id="pedidoCardTemplate"> (marcado constante);
 * los datos se inyectan con DOM APIs / textContent (H-004). Los KPIs y conteos
 * llegan del agregado `?resumen=1` (exactos, sin tope).
 */
?>

<!-- CABECERA PRINCIPAL DEL MÓDULO DE PEDIDOS (ALGODÓN NÓRDICO) -->
<section class="artisan-module-header card-stitched mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag mb-2">
        <?= svg('branding/isologo-sello-taller', ['width' => 20, 'height' => 20]) ?>
        <span>Gestión de Pedidos &amp; Encargos</span>
      </div>
      <h1 class="h2 fw-bold font-theme-display text-dark mb-1">Control de Pedidos y Encargos</h1>
      <p class="text-muted small mb-0" style="max-width: 650px;">
        Seguimiento de encargos coordinados con clientes, control de entregas, anticipos y comunicación directa vía WhatsApp.
      </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-craft-primary btn-craft-stitched d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoPedido" id="btnAbrirModalNuevoPedido">
        <i class="bi bi-journal-plus"></i>
        <span>Registrar Pedido</span>
      </button>
      <a href="creaciones.php" class="btn btn-craft-outline btn-craft-outline-stitched d-inline-flex align-items-center gap-2">
        <i class="bi bi-box2-heart"></i>
        <span>Inventario</span>
      </a>
      <a href="index.php" class="btn btn-craft-outline btn-craft-outline-stitched d-inline-flex align-items-center gap-2">
        <i class="bi bi-shop"></i>
        <span>Ver Catálogo</span>
      </a>
    </div>
  </div>
</section>

<!-- TARJETAS DE MÉTRICAS KPI (TIPOGRAFÍA NÓRDICA MEJORADA) -->
<section class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Total Pedidos</span>
        <i class="bi bi-journal-text card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-dark" id="kpiOrdersTotal">0</div>
      <div class="card-kpi-desc">Registros en el sistema</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Pendientes</span>
        <i class="bi bi-hourglass-split card-kpi-icon" style="color: var(--craft-accent-gold);"></i>
      </div>
      <div class="card-kpi-value kpi-val-gold" id="kpiOrdersPendientes">0</div>
      <div class="card-kpi-desc">Esperando confección</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">En Confección</span>
        <i class="bi bi-gear-wide-connected card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-primary" id="kpiOrdersProceso">0</div>
      <div class="card-kpi-desc">En el telar / crochet</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Ingresos Activos</span>
        <i class="bi bi-cash-coin card-kpi-icon" style="color: var(--craft-secondary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-secondary text-nowrap" id="kpiOrdersIngresos">$0.00</div>
      <div class="card-kpi-desc">Monto en pedidos vigentes</div>
    </div>
  </div>
</section>

<!-- TOOLBAR DE FILTROS Y BÚSQUEDA TEXTIL -->
<section class="card border-0 shadow-sm mb-4 card-stitched" style="border-radius: var(--craft-radius);">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      
      <!-- Píldoras de Filtro por Estado (Textile Tabs) -->
      <div class="filter-tabs-craft" role="tablist" aria-label="Filtro por Estado" id="orderStatusFilters">
        <button type="button" class="btn-filter-order-tab active filter-order-btn" data-status="all">
          <i class="bi bi-collection me-1"></i>Todos (<span id="countFilterAll">0</span>)
        </button>
        <button type="button" class="btn-filter-order-tab filter-order-btn" data-status="Pendiente">
          <i class="bi bi-hourglass-split me-1 text-warning"></i>Pendientes (<span id="countFilterPendiente">0</span>)
        </button>
        <button type="button" class="btn-filter-order-tab filter-order-btn" data-status="En Proceso">
          <i class="bi bi-gear-wide-connected me-1 text-primary"></i>En Proceso (<span id="countFilterProceso">0</span>)
        </button>
        <button type="button" class="btn-filter-order-tab filter-order-btn" data-status="Entregado">
          <i class="bi bi-check2 me-1 text-success"></i>Entregados (<span id="countFilterEntregado">0</span>)
        </button>
        <button type="button" class="btn-filter-order-tab filter-order-btn" data-status="Cancelado">
          <i class="bi bi-x-circle me-1 text-danger"></i>Cancelados (<span id="countFilterCancelado">0</span>)
        </button>
      </div>

      <!-- Buscador Rápido -->
      <div class="col-12 col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-white text-muted border-end-0" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" class="form-control border-start-0" id="searchOrdersInput" placeholder="Buscar por cliente, pieza o folio..." style="border-top-right-radius: var(--craft-radius-pill); border-bottom-right-radius: var(--craft-radius-pill);">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- CUADRÍCULA DE PEDIDOS (poblada vía API · Subfase 4.4) -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 g-xl-4 mb-4" id="ordersGrid" data-total="0" aria-live="polite">
  <div class="col-12" id="ordersLoadingState">
    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
      <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
      <span class="small">Recopilando encargos…</span>
    </div>
  </div>
</div>

<!-- TEMPLATE DE TARJETA DE PEDIDO (marcado constante · datos vía DOM APIs, H-004) -->
<template id="pedidoCardTemplate">
  <div class="col order-card-col" data-part="cardCol">
    <div class="card card-admin-pedido card-stitched h-100">
      <div class="card-order-header d-flex justify-content-between align-items-center">
        <span class="order-id-badge" data-part="codigo"></span>
        <span class="order-delivery-chip" title="Fecha pactada de entrega">
          <i class="bi bi-calendar3 text-primary"></i>
          <span class="font-monospace text-dark" data-part="fecha"></span>
        </span>
      </div>

      <div class="order-card-photo-frame">
        <img class="order-card-photo-img" data-part="photo" alt="" style="object-fit: cover;">
      </div>

      <div class="card-order-body">
        <div class="mb-3">
          <h5 class="order-product-title text-truncate" data-part="nombre"></h5>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge badge-textile-tag" style="font-size: 0.7rem; padding: 0.18rem 0.5rem;" data-part="categoria"></span>
            <span class="order-qty-tag" data-part="qty"></span>
            <span class="card-product-dimension" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;">
              <i class="bi bi-rulers me-1"></i><span data-part="dimensiones"></span>
            </span>
          </div>
        </div>

        <div class="order-client-box">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="order-client-label">Cliente / Destinatario</div>
              <div class="order-client-name" data-part="clienteNombre"></div>
            </div>
            <a href="#" target="_blank" rel="noopener" class="btn-wa-pill" data-part="waLink" title="Contactar por WhatsApp">
              <i class="bi bi-whatsapp"></i>
              <span>WhatsApp</span>
            </a>
          </div>
          <div class="small text-muted font-monospace mt-1" style="font-size: 0.74rem;">
            <i class="bi bi-telephone me-1"></i><span data-part="clienteContacto"></span>
          </div>
        </div>

        <div class="order-financial-strip">
          <div>
            <span class="text-muted small d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em;">Total Acordado</span>
            <span class="order-price-amount" data-part="precioTotal"></span>
            <small class="text-muted font-monospace" style="font-size: 0.72rem;">MXN</small>
          </div>
          <div data-part="pagoBadge"></div>
        </div>

        <div class="order-notes-preview d-none" data-part="notasBox">
          <i class="bi bi-chat-quote me-1 text-warning"></i><span data-part="notas"></span>
        </div>
      </div>

      <div class="card-order-footer">
        <div data-part="estadoBadge"></div>
        <div class="d-flex align-items-center gap-1">
          <button type="button" class="btn btn-sm btn-craft-outline btn-inspect-order" data-part="inspectBtn" title="Ver ficha y notas completas">
            <i class="bi bi-eye"></i>
          </button>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar fase de confección">
              Estado
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: var(--craft-radius-sm);">
              <li><button type="button" class="dropdown-item py-2 btn-change-order-status" data-target-status="Pendiente"><i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente</button></li>
              <li><button type="button" class="dropdown-item py-2 btn-change-order-status" data-target-status="En Proceso"><i class="bi bi-gear-wide-connected text-primary me-2"></i>En Confección (Proceso)</button></li>
              <li><button type="button" class="dropdown-item py-2 btn-change-order-status" data-target-status="Entregado"><i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado</button></li>
              <li><hr class="dropdown-divider"></li>
              <li><button type="button" class="dropdown-item py-2 text-danger btn-trigger-cancel-order" data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"><i class="bi bi-x-circle me-2"></i>Cancelar (Restaura Stock)</button></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- ESTACIÓN DE PAGINACIÓN DE PEDIDOS -->
<section class="pagination-craft-station card-stitched d-flex flex-wrap justify-content-between align-items-center gap-3 my-4">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="pagination-results-chip">
      <i class="bi bi-journal-text" style="color: var(--craft-primary);"></i>
      <span>Mostrando <strong id="pedidosShowingFrom">0</strong>–<strong id="pedidosShowingTo">0</strong> de <strong id="pedidosTotalCount">0</strong> encargos</span>
    </div>
  </div>
  <nav aria-label="Navegación de pedidos" id="pedidosPaginationNav">
    <ul class="pagination-craft"></ul>
  </nav>
</section>

<!-- Estado Vacío Cuando Ningún Pedido Coincide con la Búsqueda o Filtros -->
<div id="emptyOrdersGrid" class="p-5 text-center bg-white card-stitched rounded-4 border d-none mb-5" style="border-radius: var(--craft-radius);">
  <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
  <h5 class="fw-bold font-theme-display text-dark">No se encontraron pedidos</h5>
  <p class="text-muted small mb-0">No hay encargos que coincidan con los filtros o el término de búsqueda ingresado.</p>
</div>
