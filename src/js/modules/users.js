/**
 * Module: Users (Gestión de Usuarios y Roles del Taller)
 * Algodón Nórdico Design System
 * 
 * Responsabilidad:
 * 1. Formulario de alta de nuevos artesanos con validaciones SQLite (chk_usuarios_username).
 * 2. Modal interactivo para modificar el rol de usuarios existentes (Admin, Artesano, Asistente) [4 - Usuarios].
 * 3. Sincronización reactiva de badges de rol y recálculo en vivo de tarjetas KPI.
 * 4. Salvaguardas de seguridad RBAC para proteger la cuenta raíz de administración.
 */

export function initUsers() {
  const formCrearUsuario = document.getElementById('formCrearUsuario');
  const formEditarRol = document.getElementById('formEditarRolUsuario');
  const tablaBody = document.querySelector('#tablaUsuarios tbody');

  if (!formCrearUsuario && !formEditarRol && !tablaBody) return;

  // Tarjetas KPI
  const kpiTotal = document.getElementById('kpiTotalUsers');
  const kpiAdmin = document.getElementById('kpiAdminUsers');
  const kpiArtesano = document.getElementById('kpiArtesanoUsers');
  const kpiAsistente = document.getElementById('kpiAsistenteUsers');
  const badgeTotalCount = document.getElementById('badgeTotalUsersCount');

  function recalculateUserKPIs() {
    if (!tablaBody) return;
    const rows = tablaBody.querySelectorAll('tr');
    let total = rows.length;
    let admins = 0;
    let artesanos = 0;
    let asistentes = 0;

    rows.forEach(row => {
      const rol = row.getAttribute('data-rol') || 'artesano';
      if (rol === 'admin') admins++;
      else if (rol === 'artesano') artesanos++;
      else if (rol === 'asistente') asistentes++;
    });

    if (kpiTotal) kpiTotal.textContent = String(total);
    if (kpiAdmin) kpiAdmin.textContent = String(admins);
    if (kpiArtesano) kpiArtesano.textContent = String(artesanos);
    if (kpiAsistente) kpiAsistente.textContent = String(asistentes);
    if (badgeTotalCount) {
      badgeTotalCount.innerHTML = `<i class="bi bi-person-check-fill text-success me-1"></i>${total} ${total === 1 ? 'Cuenta Registrada' : 'Cuentas Registradas'}`;
    }
  }

  function getRoleBadgeHtml(rol) {
    if (rol === 'admin') {
      return '<span class="badge badge-role-admin px-3 py-2 rounded-pill font-monospace"><i class="bi bi-patch-check-fill text-warning me-1"></i>Administrador</span>';
    } else if (rol === 'artesano') {
      return '<span class="badge badge-role-artesano px-3 py-2 rounded-pill font-monospace"><i class="bi bi-brush-fill me-1"></i>Artesano Titular</span>';
    } else {
      return '<span class="badge badge-role-asistente px-3 py-2 rounded-pill font-monospace"><i class="bi bi-box-seam me-1"></i>Asistente</span>';
    }
  }

  // 1. Vinculación del Modal de Edición de Rol [4 - Usuarios]
  function bindEditRoleButtons() {
    document.querySelectorAll('.btn-editar-rol').forEach(btn => {
      btn.onclick = () => {
        const userId = btn.getAttribute('data-user-id') || '1';
        const username = btn.getAttribute('data-username') || 'admin';
        const currentRol = btn.getAttribute('data-rol') || 'artesano';

        const inputUserId = document.getElementById('editRolUserId');
        const displayUsername = document.getElementById('editRolUsernameDisplay');
        const badgeId = document.getElementById('editRolIdBadge');
        const selectRol = document.getElementById('selectEditarRol');
        const alertBox = document.getElementById('editarRolAlert');
        const rootWarning = document.getElementById('adminRootWarning');

        if (inputUserId) inputUserId.value = userId;
        if (displayUsername) displayUsername.textContent = '@' + username;
        if (badgeId) badgeId.textContent = 'ID: #' + userId;
        if (selectRol) selectRol.value = currentRol;
        if (alertBox) alertBox.classList.add('d-none');

        // Salvaguarda especial para Administrador Principal (#1)
        if (userId === '1') {
          if (rootWarning) rootWarning.classList.remove('d-none');
        } else {
          if (rootWarning) rootWarning.classList.add('d-none');
        }
      };
    });
  }
  bindEditRoleButtons();

  // 2. Procesar Modificación de Rol
  if (formEditarRol) {
    formEditarRol.addEventListener('submit', (e) => {
      e.preventDefault();

      const inputUserId = document.getElementById('editRolUserId');
      const selectRol = document.getElementById('selectEditarRol');
      const alertBox = document.getElementById('editarRolAlert');

      const userId = inputUserId ? inputUserId.value : null;
      const newRol = selectRol ? selectRol.value : 'artesano';

      if (!userId) return;

      // Salvaguarda: No permitir degradar al root admin
      if (userId === '1' && newRol !== 'admin') {
        if (alertBox) {
          alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Acción Restringida:</strong> La cuenta principal de administrador (#1) no puede ser degradada a artesano o asistente para evitar perder el gobierno del Micro-ERP.';
          alertBox.classList.remove('d-none');
        }
        return;
      }

      // Buscar la fila correspondiente en la tabla
      const targetRow = document.querySelector(`#tablaUsuarios tr[data-user-id="${userId}"]`);
      if (targetRow) {
        targetRow.setAttribute('data-rol', newRol);
        const roleCell = targetRow.querySelector('.user-role-cell') || targetRow.children[2];
        if (roleCell) {
          roleCell.innerHTML = getRoleBadgeHtml(newRol);
        }

        const editBtn = targetRow.querySelector('.btn-editar-rol');
        if (editBtn) {
          editBtn.setAttribute('data-rol', newRol);
        }
      }

      recalculateUserKPIs();

      // Cerrar modal
      const modalEl = document.getElementById('modalEditarRolUsuario');
      if (modalEl && window.bootstrap) {
        const modalInstance = window.bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
      }
    });
  }

  // 3. Formulario de Alta de Nuevo Usuario
  if (formCrearUsuario) {
    const usernameInput = document.getElementById('nuevoUsername');
    const rolInput = document.getElementById('nuevoRol');
    const passwordInput = document.getElementById('nuevoPassword');
    const alertEl = document.getElementById('usuarioAlert');

    formCrearUsuario.addEventListener('submit', (e) => {
      e.preventDefault();

      const username = usernameInput ? usernameInput.value.trim().replace(/^@/, '') : '';
      const rol = rolInput ? rolInput.value : 'artesano';
      const password = passwordInput ? passwordInput.value : '';

      // Validar regla SQLite: chk_usuarios_username
      if (username.length < 3 || username.length > 50) {
        if (alertEl) {
          alertEl.textContent = 'El nombre de usuario debe contener entre 3 y 50 caracteres.';
          alertEl.classList.remove('d-none');
        }
        return;
      }

      if (password.length < 6) {
        if (alertEl) {
          alertEl.textContent = 'La contraseña debe tener al menos 6 caracteres.';
          alertEl.classList.remove('d-none');
        }
        return;
      }

      // Agregar fila interactiva en la tabla
      if (tablaBody) {
        const newId = tablaBody.children.length + 1;
        const initial = username.charAt(0).toUpperCase();
        const badgeHtml = getRoleBadgeHtml(rol);

        const tr = document.createElement('tr');
        tr.setAttribute('data-user-id', String(newId));
        tr.setAttribute('data-username', username);
        tr.setAttribute('data-rol', rol);
        tr.id = `userRow_${newId}`;
        tr.innerHTML = `
          <td class="fw-bold font-monospace text-primary px-3">#${newId}</td>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="user-avatar-circle">${initial}</div>
              <div>
                <strong class="d-block text-dark username-text">@${username}</strong>
                <small class="text-muted">Creador Independiente</small>
              </div>
            </div>
          </td>
          <td class="user-role-cell">${badgeHtml}</td>
          <td class="text-center">
            <span class="badge bg-light text-dark font-monospace border px-2 py-1">
              <i class="bi bi-balloon-heart me-1 text-primary"></i>0 piezas
            </span>
          </td>
          <td>
            <span class="font-monospace small text-dark"><i class="bi bi-calendar3 me-1"></i>Hoy</span>
          </td>
          <td class="text-end pe-3">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-editar-rol" 
                      data-bs-toggle="modal" data-bs-target="#modalEditarRolUsuario"
                      data-user-id="${newId}"
                      data-username="${username}"
                      data-rol="${rol}"
                      title="Modificar Rol de Acceso">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button type="button" class="btn btn-outline-danger" onclick="if(confirm('¿Eliminar usuario @${username}?')) { this.closest('tr').remove(); document.querySelector('#tablaUsuarios').dispatchEvent(new Event('userCountChanged')); }" title="Eliminar Usuario">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        `;
        tablaBody.appendChild(tr);

        bindEditRoleButtons();
        recalculateUserKPIs();
      }

      formCrearUsuario.reset();
      if (alertEl) alertEl.classList.add('d-none');

      const modalEl = document.getElementById('modalCrearUsuario');
      if (modalEl && window.bootstrap) {
        const modalInstance = window.bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
      }
    });
  }

  // Listener para borrado de filas
  const tablaUsuarios = document.getElementById('tablaUsuarios');
  if (tablaUsuarios) {
    tablaUsuarios.addEventListener('userCountChanged', recalculateUserKPIs);
  }

  recalculateUserKPIs();
}
