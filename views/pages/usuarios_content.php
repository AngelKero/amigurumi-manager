<?php
/**
 * Page Content: Comunidad de Creadores y Artesanos de la Plataforma
 * Algodón Nórdico Design System (Admin Only)
 */

// Mock users representation matching schema
$usuariosList = [
  [
    'id' => 1,
    'username' => 'admin',
    'rol' => 'admin',
    'rol_label' => 'Administrador Titular',
    'creaciones_count' => 3,
    'creado_en' => '2026-09-10 14:00'
  ],
  [
    'id' => 2,
    'username' => 'artesana_ana',
    'rol' => 'artesano',
    'rol_label' => 'Artesano / Diseñadora',
    'creaciones_count' => 0,
    'creado_en' => '2026-09-11 10:30'
  ],
  [
    'id' => 3,
    'username' => 'asistente_leo',
    'rol' => 'asistente',
    'rol_label' => 'Asistente de Envíos',
    'creaciones_count' => 0,
    'creado_en' => '2026-09-11 11:15'
  ]
];
?>

<!-- CABECERA DEL PANEL DE USUARIOS -->
<section class="artisan-panel-banner p-3 p-md-4 mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="bg-white p-1 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
        <?= svg('branding/isologo-sello-taller', ['width' => 44, 'height' => 44]) ?>
      </div>
      <div>
        <h4 class="mb-0 fw-bold">Comunidad de Artesanos &amp; Usuarios</h4>
        <small class="text-white-50">Directorio de creadores registrados, roles operativos y autoría en la plataforma</small>
      </div>
    </div>
    <!-- ACCIONES RÁPIDAS -->
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-sm btn-panel-action active" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario" id="btnAbrirModalUsuario">
        <i class="bi bi-person-plus-fill me-1"></i>Registrar Creador
      </button>
      <a href="pedidos.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-box-seam me-1"></i>Ver Pedidos
      </a>
      <a href="index.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-arrow-left me-1"></i>Catálogo
      </a>
    </div>
  </div>
</section>

<!-- TARJETAS DE MÉTRICAS KPI DE USUARIOS (TIPOGRAFÍA NÓRDICA MEJORADA) -->
<section class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Total Usuarios</span>
        <i class="bi bi-people card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-dark" id="kpiTotalUsers"><?= count($usuariosList) ?></div>
      <div class="card-kpi-desc">Cuentas activas en SQLite</div>
    </div>
  </div>
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Administradores</span>
        <i class="bi bi-shield-check card-kpi-icon" style="color: var(--craft-primary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-primary" id="kpiAdminUsers">1</div>
      <div class="card-kpi-desc">Acceso integral al sistema</div>
    </div>
  </div>
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Artesanos</span>
        <i class="bi bi-palette card-kpi-icon" style="color: var(--craft-secondary);"></i>
      </div>
      <div class="card-kpi-value kpi-val-secondary" id="kpiArtesanoUsers">1</div>
      <div class="card-kpi-desc">Autores de creaciones</div>
    </div>
  </div>
  <div class="col">
    <div class="card-kpi card-stitched h-100">
      <div class="card-kpi-header">
        <span class="card-kpi-label">Asistentes</span>
        <i class="bi bi-person-badge card-kpi-icon" style="color: var(--craft-accent-gold);"></i>
      </div>
      <div class="card-kpi-value kpi-val-gold" id="kpiAsistenteUsers">1</div>
      <div class="card-kpi-desc">Control de envíos e inventario</div>
    </div>
  </div>
</section>

<!-- TABLA DE USUARIOS Y ROLES -->
<section class="card border-0 shadow-sm mb-5 card-stitched" style="border-radius: var(--craft-radius);">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <div>
        <h5 class="fw-bold mb-0 text-dark">
          <i class="bi bi-shield-check me-2 text-primary"></i>Directorio de Accesos al Micro-ERP
        </h5>
        <span class="text-muted small">Permisos regidos por SQLite <code>chk_usuarios_rol</code></span>
      </div>
      <span class="badge bg-light text-muted font-monospace border px-3 py-2" id="badgeTotalUsersCount" style="border-radius: var(--craft-radius-pill);">
        <i class="bi bi-person-check-fill text-success me-1"></i><?= count($usuariosList) ?> Cuentas Registradas
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-users align-middle mb-0" id="tablaUsuarios">
        <thead>
          <tr>
            <th scope="col" class="py-3 px-3">ID</th>
            <th scope="col" class="py-3">Usuario / Perfil</th>
            <th scope="col" class="py-3">Rol del Sistema</th>
            <th scope="col" class="py-3 text-center">Creaciones en Catálogo</th>
            <th scope="col" class="py-3">Fecha de Registro</th>
            <th scope="col" class="py-3 text-end pe-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuariosList as $u): ?>
            <tr data-user-id="<?= $u['id'] ?>" data-username="<?= htmlspecialchars($u['username']) ?>" data-rol="<?= $u['rol'] ?>" id="userRow_<?= $u['id'] ?>">
              <td class="fw-bold font-monospace text-primary px-3">#<?= $u['id'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <div class="user-avatar-circle">
                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                  </div>
                  <div>
                    <strong class="d-block text-dark username-text">@<?= htmlspecialchars($u['username']) ?></strong>
                    <small class="text-muted">Creador Independiente</small>
                  </div>
                </div>
              </td>
              <td class="user-role-cell">
                <?php if ($u['rol'] === 'admin'): ?>
                  <span class="badge badge-role-admin px-3 py-2 rounded-pill font-monospace">
                    <i class="bi bi-patch-check-fill text-warning me-1"></i>Administrador
                  </span>
                <?php elseif ($u['rol'] === 'artesano'): ?>
                  <span class="badge badge-role-artesano px-3 py-2 rounded-pill font-monospace">
                    <i class="bi bi-brush-fill me-1"></i>Artesano / Creador
                  </span>
                <?php else: ?>
                  <span class="badge badge-role-asistente px-3 py-2 rounded-pill font-monospace">
                    <i class="bi bi-box-seam me-1"></i>Asistente
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                  <i class="bi bi-balloon-heart me-1 text-primary"></i><?= $u['creaciones_count'] ?> piezas
                </span>
              </td>
              <td>
                <span class="font-monospace small text-dark"><i class="bi bi-calendar3 me-1"></i><?= $u['creado_en'] ?></span>
              </td>
              <td class="text-end pe-3">
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-outline-secondary btn-editar-rol" 
                          data-bs-toggle="modal" data-bs-target="#modalEditarRolUsuario"
                          data-user-id="<?= $u['id'] ?>"
                          data-username="<?= htmlspecialchars($u['username']) ?>"
                          data-rol="<?= $u['rol'] ?>"
                          title="Modificar Rol de Acceso">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <?php if ($u['creaciones_count'] > 0): ?>
                    <button type="button" class="btn btn-outline-secondary disabled" title="Bloqueado por clave foránea: no se puede eliminar porque tiene <?= $u['creaciones_count'] ?> creaciones asociadas">
                      <i class="bi bi-lock-fill text-muted"></i>
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn btn-outline-danger" onclick="if(confirm('¿Eliminar usuario @<?= $u['username'] ?>?')) this.closest('tr').remove();" title="Eliminar Usuario">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

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
