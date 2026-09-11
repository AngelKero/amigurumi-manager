<?php
/**
 * Component: Modal Login (Algodón Nórdico)
 * Modal de acceso seguro al panel administrativo del artesano.
 */
?>
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3" style="background-color: var(--craft-surface-muted);">
        <h5 class="modal-title fw-bold" id="loginModalTitle">
          <i class="bi bi-shield-lock me-2 text-primary"></i>Acceso al Sistema Amigurumi ERP
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <form id="loginForm">
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            Ingresa tus credenciales de artesano o administrador:
          </p>
          <div id="loginAlert" class="alert alert-danger d-none py-2 small" role="alert"></div>

          <div class="mb-3">
            <label for="loginUsername" class="form-label fw-bold small">Usuario (*)</label>
            <input type="text" class="form-control input-craft-pill" id="loginUsername" placeholder="admin" value="admin" required autocomplete="username">
          </div>

          <div class="mb-3">
            <label for="loginPassword" class="form-label fw-bold small">Contraseña (*)</label>
            <input type="password" class="form-control input-craft-pill" id="loginPassword" placeholder="••••••••" value="admin123" required autocomplete="current-password">
          </div>
        </div>

        <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-craft-primary btn-craft-stitched btn-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> Acceder al Panel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
