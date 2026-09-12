<?php
/**
 * Page Content: Dashboard de Gestión de Pedidos y Encargos
 * Algodón Nórdico Design System
 * Responsabilidad: Administración y seguimiento de ciclo de vida de pedidos mediante cards responsivas 3x.
 */

// Conjunto inicial de pedidos alineado con la base de datos
$mockOrders = [
  [
    'id' => 1,
    'codigo' => '#1',
    'cliente_nombre' => 'Mariana Gómez',
    'cliente_contacto' => '+52 55 4892 1039',
    'cliente_wa' => '525548921039',
    'producto_nombre' => 'Dragón Ignis',
    'producto_categoria' => 'Amigurumis & Figuras',
    'producto_dimensiones' => '18.5 cm (Alto)',
    'svg_slug' => 'piezas/dragon-ignis',
    'cantidad' => 1,
    'total' => 450.00,
    'estado_pago' => 'Anticipo 50%',
    'estado' => 'En Proceso',
    'fecha_entrega' => '2026-09-24',
    'notas' => "Empaque para regalo con listón verde bosque y dedicatoria para Sofía.",
    'search' => '1 mariana gomez mariana.g@example.com dragon ignis amigurumis figuras'
  ],
  [
    'id' => 2,
    'codigo' => '#2',
    'cliente_nombre' => 'Carlos Mendoza',
    'cliente_contacto' => '+52 55 9301 8472',
    'cliente_wa' => '525593018472',
    'producto_nombre' => 'Ajolote Rosado Pastel',
    'producto_categoria' => 'Amigurumis & Figuras',
    'producto_dimensiones' => '14.0 x 10.0 cm',
    'svg_slug' => 'piezas/ajolote-pastel',
    'cantidad' => 2,
    'total' => 640.00,
    'estado_pago' => 'Pendiente',
    'estado' => 'Pendiente',
    'fecha_entrega' => '2026-09-30',
    'notas' => 'Cliente solicita que ambos ajolotes lleven un tono ligeramente más pastel en las branquias.',
    'search' => '2 carlos mendoza carlos.m@example.com ajolote rosado pastel amigurumis figuras'
  ]
];

// Cálculo inicial de métricas KPI
$kpiTotal = count($mockOrders);
$kpiPendientes = 0;
$kpiProceso = 0;
$kpiIngresos = 0;

foreach ($mockOrders as $ord) {
  if ($ord['estado'] === 'Pendiente') {
    $kpiPendientes++;
    $kpiIngresos += $ord['total'];
  } elseif ($ord['estado'] === 'En Proceso') {
    $kpiProceso++;
    $kpiIngresos += $ord['total'];
  } elseif ($ord['estado'] === 'Entregado') {
    $kpiIngresos += $ord['total'];
  }
}
?>

<!-- CABECERA PRINCIPAL DEL MÓDULO DE PEDIDOS (ALGODÓN NÓRDICO) -->
<section class="artisan-module-header card-stitched mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag mb-2">
        <?= svg('branding/isologo-sello-taller', ['width' => 20, 'height' => 20]) ?>
        <span>Taller de Confección &amp; Encargos</span>
      </div>
      <h2 class="fw-bold font-theme-display text-dark mb-1">Control de Pedidos y Encargos</h2>
      <p class="text-muted small mb-0" style="max-width: 650px;">
        Gestión integral de ciclo de vida de pedidos, fechas de entrega programadas, anticipos y restitución de inventario.
      </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-craft-primary btn-craft-stitched d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoPedido" id="btnAbrirModalNuevoPedido">
        <i class="bi bi-journal-plus"></i>
        <span>Nuevo Encargo Manual</span>
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
      <div class="card-kpi-value kpi-val-dark" id="kpiOrdersTotal"><?= $kpiTotal ?></div>
      <div class="card-kpi-desc">Registros en el sistema</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Pendientes</span>
        <i class="bi bi-hourglass-split card-kpi-icon" style="color: var(--craft-accent-gold);"></i>
      </div>
      <div class="card-kpi-value kpi-val-gold" id="kpiOrdersPendientes"><?= $kpiPendientes ?></div>
      <div class="card-kpi-desc">Esperando confección</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">En Confección</span>
        <i class="bi bi-gear-wide-connected card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-primary" id="kpiOrdersProceso"><?= $kpiProceso ?></div>
      <div class="card-kpi-desc">En el telar / crochet</div>
    </div>
  </div>

  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Ingresos Activos</span>
        <i class="bi bi-cash-coin card-kpi-icon" style="color: var(--craft-secondary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-secondary text-nowrap" id="kpiOrdersIngresos">$<?= number_format($kpiIngresos, 2) ?></div>
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
          <i class="bi bi-collection me-1"></i>Todos (<span id="countFilterAll"><?= $kpiTotal ?></span>)
        </button>
        <button type="button" class="btn-filter-order-tab filter-order-btn" data-status="Pendiente">
          <i class="bi bi-hourglass-split me-1 text-warning"></i>Pendientes (<span id="countFilterPendiente"><?= $kpiPendientes ?></span>)
        </button>
        <button type="button" class="btn-filter-order-tab filter-order-btn" data-status="En Proceso">
          <i class="bi bi-gear-wide-connected me-1 text-primary"></i>En Proceso (<span id="countFilterProceso"><?= $kpiProceso ?></span>)
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
          <input type="text" class="form-control border-start-0" id="searchOrdersInput" placeholder="Filtrar por cliente, producto o ID..." style="border-top-right-radius: var(--craft-radius-pill); border-bottom-right-radius: var(--craft-radius-pill);">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- CUADRÍCULA RESPONSIVA DE TARJETAS DE PEDIDOS (ESTILO CREACIONES 3X / 2X) -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 g-xl-4 mb-4" id="ordersGrid">
  <?php foreach ($mockOrders as $pedido): ?>
    <div class="col order-card-col"
         id="orderCol_<?= $pedido['id'] ?>"
         data-order-id="<?= $pedido['codigo'] ?>"
         data-status="<?= $pedido['estado'] ?>"
         data-price="<?= $pedido['total'] ?>"
         data-search="<?= htmlspecialchars(strtolower($pedido['search'])) ?>">
      
      <div class="card card-admin-pedido card-stitched h-100">
        
        <!-- Encabezado Superior de la Card: ID y Fecha Límite de Entrega -->
        <div class="card-order-header d-flex justify-content-between align-items-center">
          <span class="order-id-badge"><?= $pedido['codigo'] ?></span>
          <span class="order-delivery-chip" title="Fecha pactada de entrega">
            <i class="bi bi-calendar3 text-primary"></i>
            <span class="font-monospace text-dark"><?= $pedido['fecha_entrega'] ?></span>
          </span>
        </div>

        <!-- Marco Fotográfico Acolchado Pespunteado (Centrado) -->
        <div class="order-card-photo-frame">
          <?= svg($pedido['svg_slug']) ?>
        </div>

        <!-- Cuerpo de la Card -->
        <div class="card-order-body">
          
          <!-- Título y Metadata de la Creación (Con flex-wrap holgado) -->
          <div class="mb-3">
            <h5 class="order-product-title text-truncate" title="<?= htmlspecialchars($pedido['producto_nombre']) ?>">
              <?= htmlspecialchars($pedido['producto_nombre']) ?>
            </h5>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span class="badge badge-textile-tag" style="font-size: 0.7rem; padding: 0.18rem 0.5rem;">
                <?= htmlspecialchars($pedido['producto_categoria']) ?>
              </span>
              <span class="order-qty-tag">
                <i class="bi bi-box-seam me-1"></i><?= $pedido['cantidad'] ?> <?= $pedido['cantidad'] > 1 ? 'unidades' : 'unidad' ?>
              </span>
              <small class="text-muted font-monospace" style="font-size: 0.74rem;">
                <i class="bi bi-rulers me-1"></i><?= htmlspecialchars($pedido['producto_dimensiones'] ?? $pedido['producto_tamano']) ?>
              </small>
            </div>
          </div>

          <!-- Caja de Datos del Cliente con Acceso Inmediato a WhatsApp -->
          <div class="order-client-box">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="order-client-label">Cliente / Destinatario</div>
                <div class="order-client-name"><?= htmlspecialchars($pedido['cliente_nombre']) ?></div>
              </div>
              <a href="https://wa.me/<?= $pedido['cliente_wa'] ?>" target="_blank" class="btn-wa-pill" title="Contactar por WhatsApp">
                <i class="bi bi-whatsapp"></i>
                <span>WhatsApp</span>
              </a>
            </div>
            <div class="small text-muted font-monospace mt-1" style="font-size: 0.74rem;">
              <i class="bi bi-telephone me-1"></i><?= $pedido['cliente_contacto'] ?>
            </div>
          </div>

          <!-- Franja Financiera: Precio Total y Badge Tri-Estado de Pago -->
          <div class="order-financial-strip">
            <div>
              <span class="text-muted small d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em;">Total Acordado</span>
              <span class="order-price-amount">$<?= number_format($pedido['total'], 2) ?></span>
              <small class="text-muted font-monospace" style="font-size: 0.72rem;">MXN</small>
            </div>
            <div>
              <?php if ($pedido['estado_pago'] === 'Liquidado'): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.72rem;">
                  <i class="bi bi-check-all me-1"></i>Liquidado
                </span>
              <?php elseif ($pedido['estado_pago'] === 'Anticipo 50%'): ?>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.72rem;">
                  <i class="bi bi-coin me-1"></i>Anticipo 50%
                </span>
              <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace" style="font-size: 0.72rem;">
                  <i class="bi bi-clock-history me-1"></i>Pendiente
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Notas y Especificaciones Especiales del Cliente -->
          <?php if (!empty($pedido['notas'])): ?>
            <div class="order-notes-preview" title="<?= htmlspecialchars($pedido['notas']) ?>">
              <i class="bi bi-chat-quote me-1 text-warning"></i>"<?= htmlspecialchars($pedido['notas']) ?>"
            </div>
          <?php endif; ?>

        </div>

        <!-- Footer: Badge de Estado y Botonera de Acciones -->
        <div class="card-order-footer">
          <div>
            <?php if ($pedido['estado'] === 'En Proceso'): ?>
              <span class="badge badge-order-proceso px-2.5 py-1.5 rounded-pill font-monospace order-status-badge">
                <i class="bi bi-gear-wide-connected me-1"></i>En Proceso
              </span>
            <?php elseif ($pedido['estado'] === 'Pendiente'): ?>
              <span class="badge badge-order-pendiente px-2.5 py-1.5 rounded-pill font-monospace order-status-badge">
                <i class="bi bi-hourglass-split me-1"></i>Pendiente
              </span>
            <?php elseif ($pedido['estado'] === 'Entregado'): ?>
              <span class="badge badge-order-entregado px-2.5 py-1.5 rounded-pill font-monospace order-status-badge">
                <i class="bi bi-check2 text-success me-1"></i>Entregado
              </span>
            <?php else: ?>
              <span class="badge badge-order-cancelado px-2.5 py-1.5 rounded-pill font-monospace order-status-badge">
                <i class="bi bi-x-circle me-1"></i>Cancelado
              </span>
            <?php endif; ?>
          </div>

          <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-craft-outline btn-inspect-order"
                    data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
                    data-order-id="<?= $pedido['codigo'] ?>"
                    data-cliente="<?= htmlspecialchars($pedido['cliente_nombre']) ?>"
                    data-contacto="<?= htmlspecialchars($pedido['cliente_contacto']) ?>"
                    data-estado-pago="<?= $pedido['estado_pago'] ?>"
                    data-product="<?= htmlspecialchars($pedido['producto_nombre']) ?>"
                    data-qty="<?= $pedido['cantidad'] ?>"
                    data-total="$<?= number_format($pedido['total'], 2) ?> MXN"
                    data-fecha="<?= $pedido['fecha_entrega'] ?>"
                    data-notes="<?= htmlspecialchars($pedido['notas']) ?>"
                    title="Ver ficha técnica y notas completas">
              <i class="bi bi-eye"></i>
            </button>

            <div class="btn-group btn-group-sm">
              <button class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar fase de confección">
                Estado
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: var(--craft-radius-sm);">
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="<?= $pedido['codigo'] ?>" data-target-status="Pendiente">
                    <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="<?= $pedido['codigo'] ?>" data-target-status="En Proceso">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>En Confección (Proceso)
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="<?= $pedido['codigo'] ?>" data-target-status="Entregado">
                    <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
                  </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item py-2 text-danger btn-trigger-cancel-order" href="#"
                     data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
                     data-order-id="<?= $pedido['codigo'] ?>"
                     data-qty="<?= $pedido['cantidad'] ?>"
                     data-product="<?= htmlspecialchars($pedido['producto_nombre']) ?>">
                    <i class="bi bi-x-circle me-2"></i>Cancelar (Restaura Stock)
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Estado Vacío Cuando Ningún Pedido Coincide con la Búsqueda o Filtros -->
<div id="emptyOrdersGrid" class="p-5 text-center bg-white card-stitched rounded-4 border d-none mb-5" style="border-radius: var(--craft-radius);">
  <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
  <h5 class="fw-bold font-theme-display text-dark">No se encontraron pedidos</h5>
  <p class="text-muted small mb-0">No hay encargos que coincidan con los filtros o el término de búsqueda ingresado.</p>
</div>
