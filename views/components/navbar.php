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
        <span class="brand-icon"><?= svg('decorations/corazon-lana', ['width' => 18, 'height' => 18, 'style' => 'vertical-align: -2px;']) ?></span>
        <span>Amigurumi Manager</span>
      </span>
    </a>
    
    <div class="d-flex align-items-center gap-2 ms-auto">
      <button class="btn btn-craft-outline btn-craft-outline-stitched btn-sm d-flex align-items-center gap-2" id="btnNavLogin" data-bs-toggle="modal" data-bs-target="#loginModal">
        <i class="bi bi-person-circle fs-6"></i>
        <span>Iniciar Sesión</span>
      </button>

      <div class="d-none align-items-center gap-2" id="navUserBadge">
        <a href="amigurumis.php" class="badge badge-artisan-seal text-decoration-none d-inline-flex align-items-center" id="navArtisanBadge" title="Ir al Panel de Administración">
          <i class="bi bi-patch-check-fill text-warning me-1"></i>@admin (Artesano Titular)
        </a>
        <button class="btn btn-craft-logout btn-sm btn-nav-logout" title="Cerrar Sesión">
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>
</nav>
