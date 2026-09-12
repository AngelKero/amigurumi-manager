<?php
/**
 * Component: Panel Left Sidebar (Algodón Nórdico)
 * Left navigation sidebar for artisan admin panel pages:
 * creaciones.php, pedidos.php, usuarios.php, formulario.php
 */
$activePage = $activePage ?? 'creaciones';
?>
<aside class="panel-sidebar-card card-stitched">
  <!-- Logotipo Oficial del Taller Artesanal -->
  <div class="px-2 pt-2 pb-3 mb-3 border-bottom text-center d-flex justify-content-center align-items-center">
    <?= svg('branding/logotipo-taller-artesanal', ['height' => 38, 'class' => 'mx-auto', 'style' => 'max-width: 100%; width: auto; display: block;']) ?>
  </div>

  <!-- Perfil Mini del Artesano -->
  <div class="panel-profile-box text-center">
    <div class="panel-profile-avatar mx-auto mb-2 d-flex align-items-center justify-content-center" style="background: var(--craft-surface-muted);">
      <?= svg('branding/isotipo-ovillo-corazon', ['width' => 42, 'height' => 42]) ?>
    </div>
    <div class="panel-profile-username text-center">@admin</div>
    <div class="text-center mb-1">
      <span class="panel-profile-role d-inline-flex align-items-center gap-1">
        <i class="bi bi-patch-check-fill text-warning"></i> Artesano Verificado
      </span>
    </div>
    <div class="mt-1 text-muted text-center" style="font-size: 0.72rem;">
      <span class="d-inline-block rounded-circle bg-success me-1" style="width: 7px; height: 7px;"></span> En línea en la Plataforma
    </div>
  </div>

  <!-- Título de Navegación del Panel -->
  <div class="panel-nav-title">
    <i class="bi bi-grid-fill me-1 text-primary"></i> Panel de Control
  </div>

  <!-- Lista de Enlaces de Administración -->
  <ul class="panel-nav-list">
    <li>
      <a href="usuarios.php" class="panel-nav-link <?= $activePage === 'usuarios' ? 'active' : '' ?>">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-people-fill text-primary"></i>
          <span>Comunidad de Artesanos</span>
        </span>
        <span class="panel-nav-badge" id="sidebarBadgeUsuarios">3</span>
      </a>
    </li>
    <li>
      <a href="creaciones.php" class="panel-nav-link <?= in_array($activePage, ['creaciones', 'piezas']) ? 'active' : '' ?>">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-collection-fill"></i>
          <span>Inventario & Creaciones</span>
        </span>
        <span class="panel-nav-badge" id="sidebarBadgeCreaciones">5</span>
      </a>
    </li>
    <li>
      <a href="pedidos.php" class="panel-nav-link <?= $activePage === 'pedidos' ? 'active' : '' ?>">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-box-seam-fill text-info"></i>
          <span>Control Pedidos</span>
        </span>
        <span class="panel-nav-badge" id="sidebarBadgePedidos">2</span>
      </a>
    </li>
  </ul>

  <!-- Separador Pespunte Hilvanado -->
  <hr class="divider-stitched my-3">

  <!-- Acciones Secundarias del Panel -->
  <div class="panel-nav-footer">
    <a href="index.php" class="panel-nav-footer-btn btn-view-store">
      <i class="bi bi-shop-window fs-6"></i>
      <span>Ver Tienda Pública</span>
    </a>
    <button type="button" class="panel-nav-footer-btn btn-logout-sidebar btn-nav-logout">
      <i class="bi bi-box-arrow-right fs-6"></i>
      <span>Cerrar Sesión</span>
    </button>
  </div>
</aside>
