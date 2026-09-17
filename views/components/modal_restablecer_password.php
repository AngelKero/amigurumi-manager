<?php
/**
 * Component: Modal Restablecer Contraseña de Usuario
 * Algodón Nórdico Design System (Admin Only)
 * 
 * Permite al Administrador restablecer la contraseña de cualquier colaborador,
 * ya sea ingresando una clave manual o permitiendo que el backend autogenere
 * una contraseña segura temporal.
 */
?>
<div class="modal fade" id="modalRestablecerPassword" tabindex="-1" aria-labelledby="modalRestablecerPasswordTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3 align-items-center" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex align-items-center gap-2">
          <?= svg('branding/isotipo-ovillo-corazon', ['width' => 24, 'height' => 24]) ?>
          <h5 class="modal-title fw-bold m-0" id="modalRestablecerPasswordTitle" style="font-size: 1.05rem;">
            <i class="bi bi-key-fill me-1 text-primary"></i>Restablecer Contraseña
          </h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formRestablecerPassword" novalidate>
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            Asigna una nueva clave o deja el campo vacío para que el sistema genere automáticamente una clave temporal segura:
          </p>

          <div id="resetPasswordAlert" class="alert alert-danger d-none py-2 small" role="alert"></div>

          <!-- Usuario Seleccionado (Solo lectura) -->
          <div class="p-3 mb-3 bg-light border rounded card-stitched" style="border-radius: var(--craft-radius-sm);">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small d-block" style="font-size: 0.75rem;">Cuenta a restablecer:</span>
                <strong class="text-dark font-monospace fs-6" id="resetPasswordUsernameDisplay">@usuario</strong>
              </div>
              <span class="badge bg-white text-primary border font-monospace px-2 py-1" id="resetPasswordIdBadge">ID: #0</span>
            </div>
          </div>

          <!-- ID Oculto -->
          <input type="hidden" id="resetPasswordUserId" value="">

          <!-- Nueva Contraseña Opcional -->
          <div class="mb-3" id="fieldNuevaPasswordContainer">
            <label for="nuevaPasswordInput" class="form-label fw-bold small">Nueva Contraseña (Opcional)</label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);"><i class="bi bi-shield-lock"></i></span>
              <input type="password" class="form-control input-craft-pill" id="nuevaPasswordInput" placeholder="Dejar vacío para clave autogenerada" minlength="6" maxlength="100" autocomplete="new-password" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
            </div>
            <div class="form-text text-muted small">
              Si se escribe una clave, debe tener mínimo 6 caracteres. Si se deja vacía, el servidor creará una temporal segura.
            </div>
          </div>

          <!-- Resultado de Éxito con Clave Temporal -->
          <div id="resetPasswordResult" class="alert alert-success d-none py-3 small" role="status" style="border-radius: var(--craft-radius-sm);">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-check-circle-fill fs-5 text-success"></i>
              <div class="flex-grow-1">
                <strong class="d-block mb-1" id="resetResultSuccessMsg">Contraseña actualizada exitosamente.</strong>
                <div id="tempPasswordBox" class="p-2 bg-white border rounded mt-2 d-none">
                  <span class="text-muted d-block small" style="font-size: 0.75rem;">Clave temporal para entregar al creador:</span>
                  <code class="font-monospace fw-bold fs-6 text-primary user-select-all" id="tempPasswordDisplay"></code>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-craft-primary btn-craft-stitched btn-sm" id="btnSubmitResetPassword">
            <i class="bi bi-key-fill me-1"></i> Restablecer Clave
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
