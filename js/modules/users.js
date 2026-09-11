/**
 * Module: Users (Gestión de Usuarios y Roles del Taller)
 * Single Responsibility: Gestión del formulario modal de alta de artesanos y validaciones.
 */

export function initUsers() {
  const formCrearUsuario = document.getElementById('formCrearUsuario');
  if (!formCrearUsuario) return;

  const usernameInput = document.getElementById('nuevoUsername');
  const rolInput = document.getElementById('nuevoRol');
  const passwordInput = document.getElementById('nuevoPassword');
  const alertEl = document.getElementById('usuarioAlert');
  const tablaBody = document.querySelector('#tablaUsuarios tbody');
  const kpiTotal = document.getElementById('kpiTotalUsers');

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

    // Agregar fila simulada en la tabla
    if (tablaBody) {
      const newId = tablaBody.children.length + 1;
      const initial = username.charAt(0).toUpperCase();
      let badgeHtml = '';

      if (rol === 'admin') {
        badgeHtml = '<span class="badge badge-role-admin px-3 py-2 rounded-pill font-monospace"><i class="bi bi-patch-check-fill text-warning me-1"></i>Administrador</span>';
      } else if (rol === 'artesano') {
        badgeHtml = '<span class="badge badge-role-artesano px-3 py-2 rounded-pill font-monospace"><i class="bi bi-brush-fill me-1"></i>Artesano Titular</span>';
      } else {
        badgeHtml = '<span class="badge badge-role-asistente px-3 py-2 rounded-pill font-monospace"><i class="bi bi-box-seam me-1"></i>Asistente</span>';
      }

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-bold font-monospace text-primary px-3">#${newId}</td>
        <td>
          <div class="d-flex align-items-center gap-3">
            <div class="user-avatar-circle">${initial}</div>
            <div>
              <strong class="d-block text-dark">@${username}</strong>
              <small class="text-muted">Taller Textil Nórdico</small>
            </div>
          </div>
        </td>
        <td>${badgeHtml}</td>
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
            <button type="button" class="btn btn-outline-secondary" onclick="alert('En Fase 4 editará rol vía PUT /api/usuarios.php')">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="if(confirm('¿Eliminar usuario @${username}?')) this.closest('tr').remove();">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      `;
      tablaBody.appendChild(tr);

      if (kpiTotal) {
        kpiTotal.textContent = String(tablaBody.children.length);
      }
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
