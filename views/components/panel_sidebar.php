<?php
/**
 * Component: Panel Left Sidebar (Algodón Nórdico)
 * Left navigation sidebar for artisan admin panel pages:
 * amigurumis.php, pedidos.php, usuarios.php, formulario.php
 */
$activePage = $activePage ?? 'amigurumis';
?>
<aside class="panel-sidebar-card card-stitched">
  <!-- Logotipo Oficial del Taller Artesanal -->
  <div class="px-2 pt-2 pb-3 mb-2 border-bottom text-center">
    <?= svg('branding/logotipo-taller-artesanal', ['height' => 38, 'style' => 'max-width: 100%; width: auto; display: inline-block;']) ?>
  </div>

  <!-- Perfil Mini del Artesano Titular -->
  <div class="panel-profile-box">
    <div class="panel-profile-avatar p-1 d-flex align-items-center justify-content-center" style="background: var(--craft-surface-muted);">
      <?= svg('branding/isotipo-osito-amigurumi', ['width' => 36, 'height' => 36]) ?>
    </div>
    <div class="panel-profile-username">@admin</div>
    <span class="panel-profile-role d-inline-flex align-items-center gap-1">
      <i class="bi bi-patch-check-fill text-warning"></i> Artesano Titular
    </span>
    <div class="mt-2 text-muted" style="font-size: 0.72rem;">
      <span class="d-inline-block rounded-circle bg-success me-1" style="width: 7px; height: 7px;"></span> En línea en el Taller
    </div>
  </div>

  <!-- Título de Navegación del Panel -->
  <div class="panel-nav-title">
    <i class="bi bi-grid-fill me-1 text-primary"></i> Gestión del Taller
  </div>

  <!-- Lista de Enlaces de Administración -->
  <ul class="panel-nav-list">
    <li>
      <a href="amigurumis.php" class="panel-nav-link <?= $activePage === 'amigurumis' ? 'active' : '' ?>">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-collection-fill"></i>
          <span>Inventario & Piezas</span>
        </span>
        <span class="panel-nav-badge" id="sidebarBadgeAmigurumis">3</span>
      </a>
    </li>
    <li>
      <a href="formulario.php" class="panel-nav-link <?= $activePage === 'formulario' ? 'active' : '' ?>">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-plus-circle-fill text-success"></i>
          <span>Nuevo Amigurumi</span>
        </span>
        <span class="panel-nav-badge text-success font-monospace">+</span>
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
    <li>
      <a href="usuarios.php" class="panel-nav-link <?= $activePage === 'usuarios' ? 'active' : '' ?>">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-people-fill text-primary"></i>
          <span>Equipo y Usuarios</span>
        </span>
        <span class="panel-nav-badge" id="sidebarBadgeUsuarios">3</span>
      </a>
    </li>
    <li>
      <a href="formulario.php#simuladorMargen" class="panel-nav-link">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-calculator-fill text-warning"></i>
          <span>Simulador Margen</span>
        </span>
        <span class="panel-nav-badge font-monospace">$/hr</span>
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
