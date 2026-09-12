<?php
/**
 * Component: Modal Crear Usuario / Artesano
 * Algodón Nórdico Design System
 * Formulario de alta para creadores, artesanos y colaboradores (Exclusivo Admin)
 */
?>
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3 align-items-center" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex align-items-center gap-2">
          <?= svg('branding/isotipo-ovillo-corazon', ['width' => 28, 'height' => 28]) ?>
          <h5 class="modal-title fw-bold m-0" id="modalCrearUsuarioTitle" style="font-size: 1.05rem;">
            Registrar Creador / Artesano
          </h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formCrearUsuario">
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            Crea una nueva cuenta para que un creador publique sus piezas y gestione sus encargos en la plataforma:
          </p>

          <div id="usuarioAlert" class="alert alert-danger d-none py-2 small" role="alert"></div>

          <!-- Nombre de Usuario -->
          <div class="mb-3">
            <label for="nuevoUsername" class="form-label fw-bold small">Nombre de Usuario (*)</label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">@</span>
              <input type="text" class="form-control input-craft-pill" id="nuevoUsername" placeholder="artesana_elena" required minlength="3" maxlength="50" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
            </div>
            <div class="form-text text-muted small">Entre 3 y 50 caracteres alfanuméricos. Será su identificador de autoría en el catálogo.</div>
          </div>

          <!-- Rol del Usuario -->
          <div class="mb-3">
            <label for="nuevoRol" class="form-label fw-bold small">Rol en la Plataforma (*)</label>
            <select class="form-select select-craft-pill" id="nuevoRol" required>
              <option value="artesano" selected>Artesano (Crea y edita sus propias piezas y pedidos)</option>
              <option value="asistente">Asistente (Monitorea pedidos e inventario físico)</option>
              <option value="admin">Administrador Titular (Control total del sistema y usuarios)</option>
            </select>
          </div>

          <!-- Contraseña Inicial -->
          <div class="mb-3">
            <label for="nuevoPassword" class="form-label fw-bold small">Contraseña Inicial (*)</label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);"><i class="bi bi-key"></i></span>
              <input type="password" class="form-control input-craft-pill" id="nuevoPassword" placeholder="••••••••" required minlength="6" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
            </div>
            <div class="form-text text-muted small">Mínimo 6 caracteres. Se almacenará encriptada con <code>password_hash()</code>.</div>
          </div>
        </div>

        <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-craft-primary btn-craft-stitched btn-sm">
            <i class="bi bi-check-circle me-1"></i> Guardar y Habilitar Acceso
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
