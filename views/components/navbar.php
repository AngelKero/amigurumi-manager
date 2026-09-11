<?php
/**
 * Component: Navbar (Algodón Nórdico)
 * Parameters: $activePage (string) - 'catalogo' | 'formulario' | 'pedidos' | 'detalle'
 */
$activePage = $activePage ?? 'catalogo';
?>
<nav class="navbar navbar-expand-lg navbar-craft sticky-top">
  <div class="container-xl">
    <a class="navbar-brand p-0" href="index.php">
      <span class="brand-craft-badge">
        <span class="brand-icon"><i class="bi bi-balloon-heart-fill"></i></span>
        <span>Amigurumi Manager</span>
      </span>
    </a>
    
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Alternar navegación">
      <i class="bi bi-list fs-2 text-dark"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= $activePage === 'catalogo' ? 'active' : '' ?>" href="index.php">
            <i class="bi bi-grid me-1"></i>Catálogo
          </a>
        </li>

        <!-- MENÚ DEL PANEL DEL ARTESANO (Oculto en público, visible con sesión activa) -->
        <li class="nav-item dropdown d-none" id="navArtisanDropdown">
          <a class="nav-link dropdown-toggle fw-bold text-primary <?= in_array($activePage, ['formulario', 'pedidos']) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-tools me-1"></i>Panel del Artesano
          </a>
          <ul class="dropdown-menu shadow-sm border-0" style="border-radius: var(--craft-radius-sm);">
            <li>
              <a class="dropdown-item py-2 <?= $activePage === 'formulario' ? 'active fw-bold' : '' ?>" href="formulario.php">
                <i class="bi bi-plus-circle text-success me-2"></i>Nuevo Amigurumi
              </a>
            </li>
            <li>
              <a class="dropdown-item py-2 <?= $activePage === 'pedidos' ? 'active fw-bold' : '' ?>" href="pedidos.php">
                <i class="bi bi-box-seam text-info me-2"></i>Gestión de Pedidos
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item py-2 text-muted" href="#">
                <i class="bi bi-people me-2"></i>Gestión de Usuarios (Admin)
              </a>
            </li>
          </ul>
        </li>
      </ul>

      <!-- ACCIONES DE AUTENTICACIÓN -->
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-craft-outline btn-craft-outline-stitched btn-sm d-flex align-items-center gap-2" id="btnNavLogin" data-bs-toggle="modal" data-bs-target="#loginModal">
          <i class="bi bi-person-circle fs-6"></i>
          <span>Iniciar Sesión</span>
        </button>

        <div class="d-none align-items-center gap-2" id="navUserBadge">
          <span class="badge badge-artisan-seal">
            <i class="bi bi-patch-check-fill text-warning me-1"></i>@admin (Artesano Titular)
          </span>
          <button class="btn btn-craft-logout btn-sm btn-nav-logout" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</nav>
