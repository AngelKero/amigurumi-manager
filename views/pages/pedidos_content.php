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
      <div class="bg-white p-2 rounded-circle text-dark shadow-sm">
        <i class="bi bi-kanban fs-4 text-primary"></i>
      </div>
      <div>
        <h4 class="mb-0 fw-bold">Panel de Administración del Artesano</h4>
        <small class="text-white-50">Control integral de encargos, fechas de entrega y ciclo de vida de pedidos</small>
      </div>
    </div>
    <!-- BOTONES DE ACCIÓN RÁPIDA DENTRO DEL PANEL -->
    <div class="d-flex gap-2 flex-wrap">
      <a href="formulario.php" class="btn btn-sm btn-panel-action">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Amigurumi
      </a>
      <a href="pedidos.php" class="btn btn-sm btn-panel-action active">
        <i class="bi bi-box-seam me-1"></i>Gestión de Pedidos
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
      <div class="fs-2 fw-extrabold text-dark font-monospace">2</div>
      <div class="text-muted small">Registros históricos</div>
    </div>
  </div>
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase">Pendientes</div>
      <div class="fs-2 fw-extrabold text-warning-emphasis font-monospace">1</div>
      <div class="text-muted small">Esperando confección</div>
    </div>
  </div>
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase">En Proceso</div>
      <div class="fs-2 fw-extrabold text-primary font-monospace">1</div>
      <div class="text-muted small">En el telar / crochet</div>
    </div>
  </div>
  <div class="col">
    <div class="card border-0 shadow-sm p-3 bg-white h-100 card-stitched" style="border-radius: var(--craft-radius);">
      <div class="text-muted small fw-bold text-uppercase">Ingresos Totales</div>
      <div class="fs-2 fw-extrabold text-success font-monospace">$1,090.00</div>
      <div class="text-muted small">Monto en pedidos activos</div>
    </div>
  </div>
</section>

<!-- TOOLBAR DE FILTROS Y BÚSQUEDA -->
<section class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      
      <!-- Píldoras de Filtro por Estado -->
      <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Filtro por Estado">
        <button type="button" class="btn btn-dark active">Todos (2)</button>
        <button type="button" class="btn btn-outline-secondary">Pendientes (1)</button>
        <button type="button" class="btn btn-outline-secondary">En Proceso (1)</button>
        <button type="button" class="btn btn-outline-secondary">Entregados (0)</button>
        <button type="button" class="btn btn-outline-secondary">Cancelados (0)</button>
      </div>

      <!-- Buscador Rápido -->
      <div class="col-12 col-md-4">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" placeholder="Filtrar por cliente, producto o ID...">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- VISTA MÓVIL DE PEDIDOS: TARJETAS APILADAS [CR-2] (Visible en pantallas < 768px) -->
<div class="d-block d-md-none mb-4" id="mobileOrdersContainer">
  
  <!-- Tarjeta Móvil Pedido #1 -->
  <div class="order-card-mobile card-stitched">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-bold font-monospace text-primary fs-5">#1</span>
      <span class="badge badge-order-proceso px-3 py-2 rounded-pill font-monospace">
        <i class="bi bi-gear-wide-connected me-1"></i>En Proceso
      </span>
    </div>
    <div class="mb-2">
      <h6 class="fw-bold mb-0 text-dark">Dragón Ignis</h6>
      <small class="text-muted">Cliente: <strong>Mariana Gómez</strong></small>
      <div class="small text-muted font-monospace" style="font-size: 0.75rem;">mariana.g@example.com</div>
    </div>
    <div class="row g-2 py-2 my-2 border-top border-bottom bg-light rounded px-2">
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Cantidad:</small>
        <strong class="font-monospace">1 unidad</strong>
      </div>
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Total:</small>
        <strong class="text-dark font-monospace fs-6">$450.00 MXN</strong>
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
              data-product="Dragón Ignis"
              data-qty="1"
              data-total="$450.00 MXN"
              data-fecha="2026-09-24"
              data-notes="Empaque para regalo con listón verde bosque y tarjeta con mensaje: 'Feliz Cumpleaños Sofía'.">
        <i class="bi bi-eye me-1"></i> Ver Notas
      </button>
      <button type="button" class="btn btn-outline-danger btn-sm btn-trigger-cancel-order" 
              data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
              data-order-id="#1"
              data-qty="1"
              data-product="Dragón Ignis">
        <i class="bi bi-x-circle me-1"></i> Cancelar
      </button>
    </div>
  </div>

  <!-- Tarjeta Móvil Pedido #2 -->
  <div class="order-card-mobile card-stitched">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-bold font-monospace text-primary fs-5">#2</span>
      <span class="badge badge-order-pendiente px-3 py-2 rounded-pill font-monospace">
        <i class="bi bi-hourglass-split me-1"></i>Pendiente
      </span>
    </div>
    <div class="mb-2">
      <h6 class="fw-bold mb-0 text-dark">Ajolote Rosado Pastel</h6>
      <small class="text-muted">Cliente: <strong>Carlos Mendoza</strong></small>
      <div class="small text-muted font-monospace" style="font-size: 0.75rem;">carlos.m@example.com</div>
    </div>
    <div class="row g-2 py-2 my-2 border-top border-bottom bg-light rounded px-2">
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Cantidad:</small>
        <strong class="font-monospace">2 unidades</strong>
      </div>
      <div class="col-6">
        <small class="text-muted d-block" style="font-size: 0.72rem;">Total:</small>
        <strong class="text-dark font-monospace fs-6">$640.00 MXN</strong>
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
              data-product="Ajolote Rosado Pastel"
              data-qty="2"
              data-total="$640.00 MXN"
              data-fecha="2026-09-30"
              data-notes="Cliente solicita que ambos ajolotes lleven un tono ligeramente más pastel en las branquias.">
        <i class="bi bi-eye me-1"></i> Ver Notas
      </button>
      <button type="button" class="btn btn-outline-danger btn-sm btn-trigger-cancel-order" 
              data-bs-toggle="modal" data-bs-target="#modalCancelarPedido"
              data-order-id="#2"
              data-qty="2"
              data-product="Ajolote Rosado Pastel">
        <i class="bi bi-x-circle me-1"></i> Cancelar
      </button>
    </div>
  </div>

</div>

<!-- VISTA TABULAR DE PEDIDOS (Visible en pantallas >= 768px) -->
<section class="card border-0 shadow-sm d-none d-md-block mb-5 card-stitched" style="border-radius: var(--craft-radius);">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead style="background-color: var(--craft-surface-muted);">
        <tr>
          <th scope="col" class="py-3 px-3">ID</th>
          <th scope="col" class="py-3">Cliente</th>
          <th scope="col" class="py-3">Creación Amigurumi</th>
          <th scope="col" class="py-3 text-center">Cant.</th>
          <th scope="col" class="py-3">Total Acordado</th>
          <th scope="col" class="py-3">Estado</th>
          <th scope="col" class="py-3">Fecha Entrega</th>
          <th scope="col" class="py-3 text-end pe-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        
        <!-- FILA 1: Pedido #1 (En Proceso) -->
        <tr>
          <td class="fw-bold font-monospace text-primary px-3">#1</td>
          <td>
            <div class="fw-bold text-dark">Mariana Gómez</div>
            <div class="small text-muted font-monospace">mariana.g@example.com</div>
          </td>
          <td>
            <div class="fw-semibold text-dark">Dragón Ignis</div>
            <div class="small text-muted">Fantasía &bull; 18.5 cm</div>
          </td>
          <td class="text-center fw-bold">1</td>
          <td>
            <span class="fw-bold text-dark font-monospace">$450.00</span>
            <small class="text-muted d-block" style="font-size: 0.72rem;">MXN</small>
          </td>
          <td>
            <span class="badge badge-order-proceso px-3 py-2 rounded-pill font-monospace">
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
                  <a class="dropdown-item py-2" href="#" onclick="alert('En Fase 4 actualizará vía POST a /api/pedidos/actualizar_estado.php')">
                    <i class="bi bi-hourglass-split text-warning me-2"></i>Mover a Pendiente
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2" href="#" onclick="alert('En Fase 4 actualizará vía POST a /api/pedidos/actualizar_estado.php')">
                    <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
                  </a>
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
        <tr>
          <td class="fw-bold font-monospace text-primary px-3">#2</td>
          <td>
            <div class="fw-bold text-dark">Carlos Mendoza</div>
            <div class="small text-muted font-monospace">carlos.m@example.com</div>
          </td>
          <td>
            <div class="fw-semibold text-dark">Ajolote Rosado Pastel</div>
            <div class="small text-muted">Animales / Fauna &bull; 14.0 cm</div>
          </td>
          <td class="text-center fw-bold">2</td>
          <td>
            <span class="fw-bold text-dark font-monospace">$640.00</span>
            <small class="text-muted d-block" style="font-size: 0.72rem;">MXN</small>
          </td>
          <td>
            <span class="badge badge-order-pendiente px-3 py-2 rounded-pill font-monospace">
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
                  <a class="dropdown-item py-2" href="#" onclick="alert('En Fase 4 actualizará vía POST a /api/pedidos/actualizar_estado.php')">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>Iniciar Confección (En Proceso)
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2" href="#" onclick="alert('En Fase 4 actualizará vía POST a /api/pedidos/actualizar_estado.php')">
                    <i class="bi bi-check2 text-success me-2"></i>Marcar como Entregado
                  </a>
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

      </tbody>
    </table>
  </div>
</section>
