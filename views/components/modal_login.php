<?php
/**
 * Component: Modal Login (Algodón Nórdico)
 * Modal de acceso seguro al panel administrativo del artesano.
 */
?>
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3 align-items-center" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex align-items-center gap-2">
          <?= svg('branding/isologo-sello-taller', ['width' => 32, 'height' => 32]) ?>
          <h5 class="modal-title fw-bold m-0" id="loginModalTitle" style="font-size: 1.05rem;">
            Acceso al Taller de Confección
          </h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <form id="loginForm">
        <div class="modal-body p-4">
          <div class="text-center mb-3">
            <div class="d-inline-block p-2 rounded-circle bg-light border shadow-xs mb-2">
              <?= svg('branding/isotipo-ovillo-corazon', ['width' => 52, 'height' => 52]) ?>
            </div>
            <h6 class="fw-bold text-dark font-theme-display mb-1">Portal del Artesano</h6>
            <p class="text-muted small mb-0">
              Ingresa tus credenciales para gestionar el catálogo y los pedidos:
            </p>
          </div>
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
