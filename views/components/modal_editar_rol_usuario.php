<?php
/**
 * Component: Modal Modificar Rol de Usuario
 * Algodón Nórdico Design System
 * Permite al Administrador cambiar los privilegios de acceso (Admin, Artesano, Asistente)
 */
?>
<div class="modal fade" id="modalEditarRolUsuario" tabindex="-1" aria-labelledby="modalEditarRolUsuarioTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3" style="background-color: var(--craft-surface-muted);">
        <h5 class="modal-title fw-bold" id="modalEditarRolUsuarioTitle">
          <i class="bi bi-shield-lock-fill me-2 text-primary"></i>Modificar Rol de Acceso
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formEditarRolUsuario">
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            Actualiza los privilegios operativos del usuario o creador según las reglas de acceso RBAC de SQLite:
          </p>

          <div id="editarRolAlert" class="alert alert-warning d-none py-2 small" role="alert"></div>

          <!-- Usuario Seleccionado (Solo lectura) -->
          <div class="p-3 mb-3 bg-light border rounded card-stitched" style="border-radius: var(--craft-radius-sm);">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small d-block" style="font-size: 0.75rem;">Usuario a modificar:</span>
                <strong class="text-dark font-monospace fs-6" id="editRolUsernameDisplay">@artesana_ana</strong>
              </div>
              <span class="badge bg-white text-primary border font-monospace px-2 py-1" id="editRolIdBadge">ID: #2</span>
            </div>
          </div>

          <!-- ID Oculto -->
          <input type="hidden" id="editRolUserId" value="">

          <!-- Selección del Nuevo Rol -->
          <div class="mb-3">
            <label for="selectEditarRol" class="form-label fw-bold small">Nuevo Rol Asignado (*)</label>
            <select class="form-select select-craft-pill" id="selectEditarRol" required>
              <option value="artesano">Artesano Titular (Crea y gestiona piezas y pedidos propios)</option>
              <option value="asistente">Asistente de Plataforma (Monitorea pedidos e inventario de creadores)</option>
              <option value="admin">Administrador Titular (Control integral de catálogo y cuentas)</option>
            </select>
            <div class="form-text text-muted small">
              Regulado por la restricción SQLite <code>chk_usuarios_rol CHECK(rol IN ('admin', 'artesano', 'asistente'))</code>.
            </div>
          </div>

          <!-- Advertencia para Administrador Principal -->
          <div id="adminRootWarning" class="alert alert-info border-0 p-3 mb-0 small d-none" style="background-color: var(--craft-secondary-subtle); color: var(--craft-secondary-text);">
            <i class="bi bi-shield-fill-exclamation me-1"></i>
            <strong>Salvaguarda de Acceso:</strong> La cuenta principal de administrador titular (ID #1) debe conservar el rol <code>admin</code> para no comprometer el gobierno del sistema.
          </div>
        </div>

        <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-craft-primary btn-craft-stitched btn-sm" id="btnGuardarRolUsuario">
            <i class="bi bi-check-circle me-1"></i> Actualizar Rol de Usuario
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
