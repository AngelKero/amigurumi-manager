<?php
/**
 * Page Content: Comunidad de Creadores y Artesanos de la Plataforma
 * Algodón Nórdico Design System (Admin Only)
 * 
 * Interfaz administrativa server-driven para gestión de creadores, roles RBAC,
 * reseteo de claves y estado de cuentas en SQLite.
 */
?>

<!-- CABECERA PRINCIPAL DEL MÓDULO DE USUARIOS & CREADORES (ALGODÓN NÓRDICO) -->
<section class="artisan-module-header card-stitched mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag mb-2">
        <?= svg('branding/isologo-sello-taller', ['width' => 20, 'height' => 20]) ?>
        <span>Comunidad &amp; Roles de la Plataforma</span>
      </div>
      <h1 class="h2 fw-bold font-theme-display text-dark mb-1">Comunidad de Artesanos &amp; Usuarios</h1>
      <p class="text-muted small mb-0" style="max-width: 650px;">
        Directorio de creadores registrados, roles operativos y autoría en la plataforma.
      </p>
    </div>
    <!-- ACCIONES RÁPIDAS -->
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-craft-primary btn-craft-stitched d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario" id="btnAbrirModalUsuario">
        <i class="bi bi-person-plus-fill"></i>
        <span>Registrar Creador</span>
      </button>
      <a href="creaciones.php" class="btn btn-craft-outline btn-craft-outline-stitched d-inline-flex align-items-center gap-2">
        <i class="bi bi-box2-heart"></i>
        <span>Inventario</span>
      </a>
      <a href="pedidos.php" class="btn btn-craft-outline btn-craft-outline-stitched d-inline-flex align-items-center gap-2">
        <i class="bi bi-journal-text"></i>
        <span>Ver Pedidos</span>
      </a>
      <a href="index.php" class="btn btn-craft-outline btn-craft-outline-stitched d-inline-flex align-items-center gap-2">
        <i class="bi bi-shop"></i>
        <span>Catálogo</span>
      </a>
    </div>
  </div>
</section>

<!-- TARJETAS DE MÉTRICAS KPI DE USUARIOS (TIPOGRAFÍA NÓRDICA MEJORADA) -->
<section class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Total Cuentas</span>
        <i class="bi bi-people card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-dark" id="kpiTotalUsers">—</div>
      <div class="card-kpi-desc">Cuentas registradas en SQLite</div>
    </div>
  </div>
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Administradores</span>
        <i class="bi bi-shield-check card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-primary" id="kpiAdminUsers">—</div>
      <div class="card-kpi-desc">Acceso integral al sistema</div>
    </div>
  </div>
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Artesanos</span>
        <i class="bi bi-palette card-kpi-icon" style="color: var(--craft-secondary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-secondary" id="kpiArtesanoUsers">—</div>
      <div class="card-kpi-desc">Autores de creaciones</div>
    </div>
  </div>
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Asistentes</span>
        <i class="bi bi-person-badge card-kpi-icon" style="color: var(--craft-accent-gold);"></i>
      </div>
      <div class="card-kpi-value kpi-val-gold" id="kpiAsistenteUsers">—</div>
      <div class="card-kpi-desc">Control de envíos e inventario</div>
    </div>
  </div>
</section>

<!-- TABLA DE USUARIOS Y ROLES -->
<section class="card border-0 shadow-sm mb-5 card-stitched" style="border-radius: var(--craft-radius);">
  <div class="card-body p-4">
    <!-- FILTROS Y CONTADORES -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h5 class="fw-bold mb-0 text-dark">
          <i class="bi bi-shield-check me-2 text-primary"></i>Directorio de Accesos al Micro-ERP
        </h5>
        <span class="text-muted small">Permisos regidos por SQLite <code>chk_usuarios_rol</code></span>
      </div>

      <div class="d-flex align-items-center gap-3 flex-wrap">
        <!-- Filtro por Estado de Cuenta -->
        <div class="btn-group btn-group-sm" role="group" id="filtroEstadoUsuarios" aria-label="Filtro por estado de cuenta">
          <button type="button" class="btn btn-craft-outline active" data-estado="activos">
            <i class="bi bi-person-check me-1"></i>Activos
          </button>
          <button type="button" class="btn btn-craft-outline" data-estado="inactivos">
            <i class="bi bi-person-dash me-1"></i>Inactivos
          </button>
          <button type="button" class="btn btn-craft-outline" data-estado="todos">
            <i class="bi bi-people me-1"></i>Todos
          </button>
        </div>

        <span class="badge bg-light text-muted font-monospace border px-3 py-2" id="badgeTotalUsersCount" style="border-radius: var(--craft-radius-pill);">
          <i class="bi bi-person-check-fill text-success me-1"></i><span id="labelTotalCount">Cargando...</span>
        </span>
      </div>
    </div>

    <!-- Alert de Feedback en la Vista Principal -->
    <div id="usuariosGlobalAlert" class="alert alert-info d-none py-2 px-3 mb-3 small" role="alert" style="border-radius: var(--craft-radius-sm);"></div>

    <div class="table-responsive">
      <table class="table table-users table-artisan-team align-middle mb-0" id="tablaUsuarios">
        <thead>
          <tr>
            <th scope="col" class="py-3 px-3">ID</th>
            <th scope="col" class="py-3">Usuario / Perfil</th>
            <th scope="col" class="py-3">WhatsApp</th>
            <th scope="col" class="py-3">Rol del Sistema</th>
            <th scope="col" class="py-3 text-center">Creaciones en Catálogo</th>
            <th scope="col" class="py-3">Fecha de Registro</th>
            <th scope="col" class="py-3 text-end pe-3">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaUsuariosBody">
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              <span>Cargando directorio de creadores...</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ESTADO VACÍO -->
    <div id="emptyStateUsuarios" class="text-center py-5 d-none">
      <div class="mb-3">
        <?= svg('empty-basket', ['width' => 100, 'height' => 90, 'class' => 'mx-auto mb-2']) ?>
      </div>
      <h5 class="fw-bold text-dark font-theme-display mb-1">No se encontraron creadores</h5>
      <p class="text-muted small mb-3">No hay cuentas registradas que coincidan con el estado seleccionado.</p>
      <button type="button" class="btn btn-craft-primary btn-craft-stitched btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario" id="btnCrearUsuarioEmpty">
        <i class="bi bi-person-plus-fill me-1"></i> Registrar Creador
      </button>
    </div>

    <!-- PAGINACIÓN -->
    <nav id="paginacionUsuarios" class="d-flex justify-content-center mt-4" aria-label="Paginación de usuarios"></nav>

    <!-- Template para renderizado dinámico de filas (H-004) -->
    <template id="usuarioRowTemplate">
      <tr data-user-id="" data-username="" data-rol="" data-activo="" data-whatsapp=""></tr>
    </template>

    <!-- Nota de Integridad Referencial SQLite -->
    <div class="alert alert-light border mt-4 mb-0 small text-muted d-flex align-items-center gap-2" style="border-radius: var(--craft-radius-sm);">
      <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
      <div>
        <strong>Integridad Referencial Garantizada (`ON DELETE RESTRICT`):</strong>
        Un artesano con creaciones registradas en catálogo no puede ser eliminado de la base de datos hasta que sus creaciones sean transferidas o eliminadas.
      </div>
    </div>

  </div>
</section>
