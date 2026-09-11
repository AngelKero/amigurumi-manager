<?php
/**
 * Page Content: Dashboard de Gestión de Pedidos y Encargos
 * Algodón Nórdico Design System
 */
?>
<!-- CABECERA DEL PANEL DEL ARTESANO -->
<section class="artisan-panel-banner p-3 p-md-4 mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="bg-white p-1 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
        <?= svg('tools/cinta-metrica', ['width' => 32, 'height' => 32]) ?>
      </div>
      <div>
        <h4 class="mb-0 fw-bold">Panel de Administración del Artesano</h4>
        <small class="text-white-50">Control integral de encargos, fechas de entrega y ciclo de vida de pedidos</small>
      </div>
    </div>
    <!-- BOTONES DE ACCIÓN RÁPIDA DENTRO DEL PANEL -->
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-sm btn-panel-action active" data-bs-toggle="modal" data-bs-target="#modalNuevoPedido" id="btnAbrirModalNuevoPedido">
        <i class="bi bi-journal-plus me-1"></i>Nuevo Encargo Manual
      </button>
      <a href="formulario.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Amigurumi
      </a>
      <a href="index.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-arrow-left me-1"></i>Ver Catálogo
      </a>
    </div>
  </div>
</section>

<!-- TARJETAS DE MÉTRICAS KPI -->
<section class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase">Total Pedidos</div>
      <div class="fs-2 fw-extrabold text-dark font-monospace" id="kpiOrdersTotal">2</div>
      <div class="text-muted small">Registros históricos</div>
    </div>
  </div>
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase">Pendientes</div>
      <div class="fs-2 fw-extrabold text-warning-emphasis font-monospace" id="kpiOrdersPendientes">1</div>
      <div class="text-muted small">Esperando confección</div>
    </div>
  </div>
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase">En Proceso</div>
      <div class="fs-2 fw-extrabold text-primary font-monospace" id="kpiOrdersProceso">1</div>
      <div class="text-muted small">En el telar / crochet</div>
    </div>
  </div>
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase">Ingresos Totales</div>
      <div class="fs-2 fw-extrabold text-success font-monospace" id="kpiOrdersIngresos">$1,090.00</div>
      <div class="text-muted small">Monto en pedidos activos</div>
    </div>
  </div>
</section>

<!-- TOOLBAR DE FILTROS Y BÚSQUEDA -->
<section class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      
      <!-- Píldoras de Filtro por Estado -->
      <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Filtro por Estado" id="orderStatusFilters">
        <button type="button" class="btn btn-dark active filter-order-btn" data-status="all">Todos (<span id="countFilterAll">2</span>)</button>
        <button type="button" class="btn btn-outline-secondary filter-order-btn" data-status="Pendiente">Pendientes (<span id="countFilterPendiente">1</span>)</button>
        <button type="button" class="btn btn-outline-secondary filter-order-btn" data-status="En Proceso">En Proceso (<span id="countFilterProceso">1</span>)</button>
        <button type="button" class="btn btn-outline-secondary filter-order-btn" data-status="Entregado">Entregados (<span id="countFilterEntregado">0</span>)</button>
        <button type="button" class="btn btn-outline-secondary filter-order-btn" data-status="Cancelado">Cancelados (<span id="countFilterCancelado">0</span>)</button>
      </div>

      <!-- Buscador Rápido -->
      <div class="col-12 col-md-4">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" id="searchOrdersInput" placeholder="Filtrar por cliente, producto o ID...">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- VISTA MÓVIL DE PEDIDOS: TARJETAS APILADAS [CR-2] (Visible en pantallas < 768px) -->
<div class="d-block d-md-none mb-4" id="mobileOrdersContainer">
  
  <!-- Tarjeta Móvil Pedido #1 -->
  <div class="order-card-mobile card-stitched mb-3" data-order-id="#1" data-status="En Proceso" data-price="450" data-search="1 mariana gomez mariana.g@example.com dragon ignis fantasia">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-bold font-monospace text-primary fs-5">#1</span>
      <span class="badge badge-order-proceso px-3 py-2 rounded-pill font-monospace order-status-badge">
        <i class="bi bi-gear-wide-connected me-1"></i>En Proceso
      </span>
    </div>
    <div class="mb-2">
      <h6 class="fw-bold mb-0 text-dark order-product-name">Dragón Ignis</h6>
      <small class="text-muted">Cliente: <strong class="order-client-name">Mariana Gómez</strong></small>
      <div class="small text-muted font-monospace">
        <a href="https://wa.me/525548921039" target="_blank" class="text-decoration-none text-muted">
          <i class="bi bi-whatsapp text-success me-1"></i>+52 55 4892 1039
        </a>
      </div>
    </div>
    <div class="row g-2 py-2 my-2 border-top border-bottom bg-light rounded px-2">
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Cantidad:</small>
        <strong class="font-monospace">1 unidad</strong>
      </div>
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Total / Cobro:</small>
        <strong class="text-dark font-monospace fs-6">$450.00 MXN</strong>
        <div>
          <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
            <i class="bi bi-coin me-1"></i>Anticipo 50%
          </span>
        </div>
      </div>
      <div class="col-12">
        <small class="text-muted d-block" style="font-size: 0.75rem;"><i class="bi bi-calendar3 me-1"></i>Compromiso: <strong class="font-monospace text-dark">2026-09-24</strong></small>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center pt-2">
      <button type="button" class="btn btn-outline-secondary btn-sm btn-inspect-order" 
              data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
              data-order-id="#1"
              data-cliente="Mariana Gómez"
              data-contacto="+52 55 4892 1039"
              data-estado-pago="Anticipo 50%"
              data-product="Dragón Ignis"
              data-qty="1"
              data-total="$450.00 MXN"
              data-fecha="2026-09-24"
              data-notes="Empaque para regalo con listón verde bosque y tarjeta con mensaje: 'Feliz Cumpleaños Sofía'.">
        <i class="bi bi-eye me-1"></i> Notas
      </button>
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          Estado
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li>
            <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#1" data-target-status="Pendiente">
              <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#1" data-target-status="En Proceso">
              <i class="bi bi-gear-wide-connected text-primary me-2"></i>En Confección
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#1" data-target-status="Entregado">
              <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
            </button>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item py-2 text-danger btn-trigger-cancel-order" href="#" 
               data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
               data-order-id="#1"
               data-qty="1"
               data-product="Dragón Ignis">
              <i class="bi bi-x-circle me-2"></i>Cancelar (Restaura Stock)
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Tarjeta Móvil Pedido #2 -->
  <div class="order-card-mobile card-stitched mb-3" data-order-id="#2" data-status="Pendiente" data-price="640" data-search="2 carlos mendoza carlos.m@example.com ajolote rosado pastel animales fauna">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-bold font-monospace text-primary fs-5">#2</span>
      <span class="badge badge-order-pendiente px-3 py-2 rounded-pill font-monospace order-status-badge">
        <i class="bi bi-hourglass-split me-1"></i>Pendiente
      </span>
    </div>
    <div class="mb-2">
      <h6 class="fw-bold mb-0 text-dark order-product-name">Ajolote Rosado Pastel</h6>
      <small class="text-muted">Cliente: <strong class="order-client-name">Carlos Mendoza</strong></small>
      <div class="small text-muted font-monospace">
        <a href="https://wa.me/525593018472" target="_blank" class="text-decoration-none text-muted">
          <i class="bi bi-whatsapp text-success me-1"></i>+52 55 9301 8472
        </a>
      </div>
    </div>
    <div class="row g-2 py-2 my-2 border-top border-bottom bg-light rounded px-2">
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Cantidad:</small>
        <strong class="font-monospace">2 unidades</strong>
      </div>
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Total / Cobro:</small>
        <strong class="text-dark font-monospace fs-6">$640.00 MXN</strong>
        <div>
          <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
            <i class="bi bi-clock-history me-1"></i>Pendiente
          </span>
        </div>
      </div>
      <div class="col-12">
        <small class="text-muted d-block" style="font-size: 0.75rem;"><i class="bi bi-calendar3 me-1"></i>Compromiso: <strong class="font-monospace text-dark">2026-09-30</strong></small>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center pt-2">
      <button type="button" class="btn btn-outline-secondary btn-sm btn-inspect-order" 
              data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
              data-order-id="#2"
              data-cliente="Carlos Mendoza"
              data-contacto="+52 55 9301 8472"
              data-estado-pago="Pendiente"
              data-product="Ajolote Rosado Pastel"
              data-qty="2"
              data-total="$640.00 MXN"
              data-fecha="2026-09-30"
              data-notes="Cliente solicita que ambos ajolotes lleven un tono ligeramente más pastel en las branquias.">
        <i class="bi bi-eye me-1"></i> Notas
      </button>
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          Estado
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li>
            <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#2" data-target-status="Pendiente">
              <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#2" data-target-status="En Proceso">
              <i class="bi bi-gear-wide-connected text-primary me-2"></i>Iniciar Confección
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#2" data-target-status="Entregado">
              <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
            </button>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item py-2 text-danger btn-trigger-cancel-order" href="#" 
               data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
               data-order-id="#2"
               data-qty="2"
               data-product="Ajolote Rosado Pastel">
              <i class="bi bi-x-circle me-2"></i>Cancelar (Restaura Stock)
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Estado Vacío Móvil -->
  <div id="emptyOrdersMobile" class="p-4 text-center bg-white rounded-3 border d-none">
    <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
    <p class="text-muted small mb-0">No se encontraron pedidos con los filtros aplicados.</p>
  </div>

</div>

<!-- VISTA TABULAR DE PEDIDOS (Visible en pantallas >= 768px) -->
<section class="card border-0 shadow-sm d-none d-md-block mb-5 card-stitched" style="border-radius: var(--craft-radius);">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="ordersTableDesktop">
      <thead style="background-color: var(--craft-surface-muted);">
        <tr>
          <th scope="col" class="py-3 px-3">ID</th>
          <th scope="col" class="py-3">Cliente</th>
          <th scope="col" class="py-3">Creación Amigurumi</th>
          <th scope="col" class="py-3 text-center">Cant.</th>
          <th scope="col" class="py-3">Total Acordado / Cobro</th>
          <th scope="col" class="py-3">Estado</th>
          <th scope="col" class="py-3">Fecha Entrega</th>
          <th scope="col" class="py-3 text-end pe-3">Acciones</th>
        </tr>
      </thead>
      <tbody id="ordersTableBody">
        
        <!-- FILA 1: Pedido #1 (En Proceso) -->
        <tr data-order-id="#1" data-status="En Proceso" data-price="450" data-search="1 mariana gomez mariana.g@example.com dragon ignis fantasia">
          <td class="fw-bold font-monospace text-primary px-3">#1</td>
          <td>
            <div class="fw-bold text-dark order-client-name">Mariana Gómez</div>
            <div class="small text-muted font-monospace">
              <a href="https://wa.me/525548921039" target="_blank" class="text-decoration-none text-muted">
                <i class="bi bi-whatsapp text-success me-1"></i>+52 55 4892 1039
              </a>
            </div>
          </td>
          <td>
            <div class="fw-semibold text-dark order-product-name">Dragón Ignis</div>
            <div class="small text-muted">Fantasía &bull; 18.5 cm</div>
          </td>
          <td class="text-center fw-bold">1</td>
          <td>
            <span class="fw-bold text-dark font-monospace">$450.00</span>
            <small class="text-muted" style="font-size: 0.72rem;">MXN</small>
            <div>
              <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.7rem;">
                <i class="bi bi-coin me-1"></i>Anticipo 50%
              </span>
            </div>
          </td>
          <td>
            <span class="badge badge-order-proceso px-3 py-2 rounded-pill font-monospace order-status-badge">
              <i class="bi bi-gear-wide-connected me-1"></i>En Proceso
            </span>
          </td>
          <td>
            <span class="font-monospace small text-dark"><i class="bi bi-calendar3 me-1"></i>2026-09-24</span>
          </td>
          <td class="text-end pe-3">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-inspect-order" 
                      data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
                      data-order-id="#1"
                      data-cliente="Mariana Gómez"
                      data-contacto="+52 55 4892 1039"
                      data-estado-pago="Anticipo 50%"
                      data-product="Dragón Ignis"
                      data-qty="1"
                      data-total="$450.00 MXN"
                      data-fecha="2026-09-24"
                      data-notes="Empaque para regalo con listón verde bosque y tarjeta con mensaje: 'Feliz Cumpleaños Sofía'."
                      title="Ver notas y detalles completos">
                <i class="bi bi-eye"></i>
              </button>

              <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Estado
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#1" data-target-status="Pendiente">
                    <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#1" data-target-status="En Proceso">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>En Confección
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#1" data-target-status="Entregado">
                    <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
                  </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item py-2 text-danger btn-trigger-cancel-order" href="#" 
                     data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
                     data-order-id="#1"
                     data-qty="1"
                     data-product="Dragón Ignis">
                    <i class="bi bi-x-circle me-2"></i>Cancelar Pedido (Restaura Stock)
                  </a>
                </li>
              </ul>
            </div>
          </td>
        </tr>

        <!-- FILA 2: Pedido #2 (Pendiente) -->
        <tr data-order-id="#2" data-status="Pendiente" data-price="640" data-search="2 carlos mendoza carlos.m@example.com ajolote rosado pastel animales fauna">
          <td class="fw-bold font-monospace text-primary px-3">#2</td>
          <td>
            <div class="fw-bold text-dark order-client-name">Carlos Mendoza</div>
            <div class="small text-muted font-monospace">
              <a href="https://wa.me/525593018472" target="_blank" class="text-decoration-none text-muted">
                <i class="bi bi-whatsapp text-success me-1"></i>+52 55 9301 8472
              </a>
            </div>
          </td>
          <td>
            <div class="fw-semibold text-dark order-product-name">Ajolote Rosado Pastel</div>
            <div class="small text-muted">Animales / Fauna &bull; 14.0 cm</div>
          </td>
          <td class="text-center fw-bold">2</td>
          <td>
            <span class="fw-bold text-dark font-monospace">$640.00</span>
            <small class="text-muted" style="font-size: 0.72rem;">MXN</small>
            <div>
              <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace" style="font-size: 0.7rem;">
                <i class="bi bi-clock-history me-1"></i>Pendiente
              </span>
            </div>
          </td>
          <td>
            <span class="badge badge-order-pendiente px-3 py-2 rounded-pill font-monospace order-status-badge">
              <i class="bi bi-hourglass-split me-1"></i>Pendiente
            </span>
          </td>
          <td>
            <span class="font-monospace small text-dark"><i class="bi bi-calendar3 me-1"></i>2026-09-30</span>
          </td>
          <td class="text-end pe-3">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-inspect-order" 
                      data-bs-toggle="modal" data-bs-target="#modalInspeccionarPedido"
                      data-order-id="#2"
                      data-cliente="Carlos Mendoza"
                      data-contacto="+52 55 9301 8472"
                      data-estado-pago="Pendiente"
                      data-product="Ajolote Rosado Pastel"
                      data-qty="2"
                      data-total="$640.00 MXN"
                      data-fecha="2026-09-30"
                      data-notes="Cliente solicita que ambos ajolotes lleven un tono ligeramente más pastel en las branquias."
                      title="Ver notas y detalles completos">
                <i class="bi bi-eye"></i>
              </button>

              <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Estado
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#2" data-target-status="Pendiente">
                    <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#2" data-target-status="En Proceso">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>Iniciar Confección (En Proceso)
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item py-2 btn-change-order-status" data-order-id="#2" data-target-status="Entregado">
                    <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
                  </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item py-2 text-danger btn-trigger-cancel-order" href="#" 
                     data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
                     data-order-id="#2"
                     data-qty="2"
                     data-product="Ajolote Rosado Pastel">
                    <i class="bi bi-x-circle me-2"></i>Cancelar Pedido (Restaura Stock)
                  </a>
                </li>
              </ul>
            </div>
          </td>
        </tr>

        <!-- Estado Vacío Tabla Desktop -->
        <tr id="emptyOrdersDesktopRow" class="d-none">
          <td colspan="8" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <span>No se encontraron pedidos con los filtros o búsqueda especificada.</span>
          </td>
        </tr>

      </tbody>
    </table>
  </div>
</section>
