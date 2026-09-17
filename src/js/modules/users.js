/**
 * Module: Users (Directorio de Creadores & Roles RBAC — Subfase 4.5)
 * Single Responsibility: Gestión integral server-driven de usuarios y artesanos
 * contra /api/usuarios/* (Bearer token, rol admin exclusivo, salvaguarda ID #1).
 *
 * Seguridad DOM (H-004): Cero interpolación de datos dinámicos en innerHTML.
 * Manipulación exclusiva mediante textContent, DOM APIs y escapeHtml.
 */

import { escapeHtml } from './dom-safe.js';
import { getToken, getUser, isAuthenticated, clearSession } from './auth.js';
import { confirmModal, showSuccessModal, showErrorModal, showToastAlert } from './dialog.js';

const INDEX_URL = '/api/usuarios/index.php';
const CREAR_URL = '/api/usuarios/crear.php';
const ROL_URL = '/api/usuarios/cambiar-rol.php';
const RESET_PWD_URL = '/api/usuarios/restablecer-password.php';
const ELIMINAR_URL = '/api/usuarios/eliminar.php';
const REACTIVAR_URL = '/api/usuarios/reactivar.php';
const WHATSAPP_URL = '/api/usuarios/actualizar-whatsapp.php';
const PAGE_LIMIT = 20;

const state = {
  estado: 'activos',
  page: 1,
  seq: 0,
};

const userCache = new Map();

/** Encabezados con token Bearer */
function authHeaders(extra = {}) {
  const headers = { ...extra };
  const token = getToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;
  return headers;
}

/** Parseo seguro de respuestas JSON */
async function parseJson(res) {
  const contentType = res.headers.get('content-type') || '';
  if (!contentType.includes('application/json')) return null;
  try {
    return await res.json();
  } catch {
    return null;
  }
}

/** Petición autenticada POST JSON */
async function postJson(url, payload) {
  const headers = authHeaders({ 'Content-Type': 'application/json' });
  const res = await fetch(url, {
    method: 'POST',
    headers,
    body: JSON.stringify(payload),
  });
  const json = await parseJson(res);
  return { ok: res.ok, status: res.status, json };
}

/** Validación espejo del servidor (WhatsAppHelper) */
export function validateWhatsapp(raw) {
  const value = String(raw || '').trim();
  if (value === '') return { ok: true, value: '' };
  if (value.length > 20) return { ok: false };
  const digits = value.replace(/\D/g, '');
  if (digits.length < 8 || digits.length > 15) return { ok: false };
  return { ok: true, value };
}

/** Render DOM-safe (H-004) de la celda WhatsApp */
function renderWhatsappCell(cell, value) {
  if (!cell) return;
  cell.replaceChildren();
  const clean = String(value || '').trim();
  if (clean === '') {
    const dash = document.createElement('span');
    dash.className = 'text-muted small';
    dash.textContent = '—';
    cell.appendChild(dash);
    return;
  }
  const wrap = document.createElement('span');
  wrap.className = 'font-monospace small text-dark text-nowrap';
  const icon = document.createElement('i');
  icon.className = 'bi bi-whatsapp text-success me-1';
  wrap.appendChild(icon);
  wrap.appendChild(document.createTextNode(clean));
  cell.appendChild(wrap);
}

/** Crea el badge DOM-safe para el rol */
function createRoleBadge(rol) {
  const badge = document.createElement('span');
  badge.className = 'badge px-3 py-2 rounded-pill font-monospace';
  const icon = document.createElement('i');
  icon.className = 'me-1';

  if (rol === 'admin') {
    badge.classList.add('badge-role-admin');
    icon.className += ' bi bi-patch-check-fill text-warning';
    badge.appendChild(icon);
    badge.appendChild(document.createTextNode('Administrador'));
  } else if (rol === 'artesano') {
    badge.classList.add('badge-role-artesano');
    icon.className += ' bi bi-brush-fill';
    badge.appendChild(icon);
    badge.appendChild(document.createTextNode('Artesano / Creador'));
  } else {
    badge.classList.add('badge-role-asistente');
    icon.className += ' bi bi-box-seam';
    badge.appendChild(icon);
    badge.appendChild(document.createTextNode('Asistente'));
  }
  return badge;
}

/** Alerta global en el panel (Toast modal con SweetAlert2 + feedback DOM) */
function showGlobalAlert(message, type = 'info') {
  const toastType = type === 'danger' ? 'error' : (type === 'success' ? 'success' : 'info');
  showToastAlert(message, toastType);

  const alertBox = document.getElementById('usuariosGlobalAlert');
  if (!alertBox) return;
  alertBox.className = `alert alert-${type} py-2 px-3 mb-3 small`;
  alertBox.textContent = message;
  alertBox.classList.remove('d-none');
  setTimeout(() => {
    alertBox.classList.add('d-none');
  }, 5000);
}

/** Inicialización principal del módulo de usuarios */
export function initUsers() {
  const table = document.getElementById('tablaUsuarios');
  const tableBody = document.getElementById('tablaUsuariosBody');
  if (!table || !tableBody) return;

  // 1. Control de acceso estricto RBAC en cliente
  const token = getToken();
  const currentUser = getUser();
  if (!token || !currentUser || currentUser.rol !== 'admin') {
    if (typeof window !== 'undefined' && window.location && window.location.pathname.includes('usuarios.php')) {
      window.location.replace('index.php');
    }
    return;
  }

  // Elementos de métricas KPI
  const kpiTotal = document.getElementById('kpiTotalUsers');
  const kpiAdmin = document.getElementById('kpiAdminUsers');
  const kpiArtesano = document.getElementById('kpiArtesanoUsers');
  const kpiAsistente = document.getElementById('kpiAsistenteUsers');
  const labelTotalCount = document.getElementById('labelTotalCount');
  const emptyState = document.getElementById('emptyStateUsuarios');
  const paginacionContainer = document.getElementById('paginacionUsuarios');

  function updateKPIs(resumen) {
    if (!resumen) return;
    if (kpiTotal) kpiTotal.textContent = String(resumen.total ?? 0);
    if (kpiAdmin) kpiAdmin.textContent = String(resumen.admin ?? 0);
    if (kpiArtesano) kpiArtesano.textContent = String(resumen.artesano ?? 0);
    if (kpiAsistente) kpiAsistente.textContent = String(resumen.asistente ?? 0);
    if (labelTotalCount) {
      const tot = resumen.total ?? 0;
      labelTotalCount.textContent = `${tot} ${tot === 1 ? 'Cuenta Registrada' : 'Cuentas Registradas'}`;
    }
  }

  /** Renderiza una fila de usuario en el DOM de forma completamente segura (H-004) */
  function renderUserRow(u) {
    const tr = document.createElement('tr');
    tr.id = `userRow_${u.id}`;
    tr.setAttribute('data-user-id', String(u.id));
    tr.setAttribute('data-username', u.username);
    tr.setAttribute('data-rol', u.rol);
    tr.setAttribute('data-activo', String(u.activo));
    tr.setAttribute('data-whatsapp', u.whatsapp || '');

    // Col 1: ID
    const tdId = document.createElement('td');
    tdId.className = 'fw-bold font-monospace text-primary px-3';
    tdId.textContent = `#${u.id}`;
    tr.appendChild(tdId);

    // Col 2: Usuario / Perfil
    const tdUser = document.createElement('td');
    const userWrap = document.createElement('div');
    userWrap.className = 'd-flex align-items-center gap-3';

    const avatar = document.createElement('div');
    avatar.className = 'avatar-artisan-initials';
    avatar.textContent = (u.username.charAt(0) || 'U').toUpperCase();
    userWrap.appendChild(avatar);

    const userTextWrap = document.createElement('div');
    const userStrong = document.createElement('strong');
    userStrong.className = 'd-block text-dark username-text';
    userStrong.textContent = `@${u.username}`;
    userTextWrap.appendChild(userStrong);

    const userDesc = document.createElement('small');
    userDesc.className = 'text-muted';
    if (u.activo === 0) {
      userDesc.className = 'badge bg-secondary text-white font-monospace';
      userDesc.textContent = 'Inactivo / Suspendido';
    } else {
      userDesc.textContent = u.rol === 'admin' ? 'Administrador Titular' : 'Creador Independiente';
    }
    userTextWrap.appendChild(userDesc);

    userWrap.appendChild(userTextWrap);
    tdUser.appendChild(userWrap);
    tr.appendChild(tdUser);

    // Col 3: WhatsApp
    const tdWa = document.createElement('td');
    tdWa.className = 'user-whatsapp-cell';
    renderWhatsappCell(tdWa, u.whatsapp);
    tr.appendChild(tdWa);

    // Col 4: Rol del Sistema
    const tdRol = document.createElement('td');
    tdRol.className = 'user-role-cell';
    tdRol.appendChild(createRoleBadge(u.rol));
    tr.appendChild(tdRol);

    // Col 5: Creaciones en Catálogo
    const tdCreaciones = document.createElement('td');
    tdCreaciones.className = 'text-center';
    const badgeCreaciones = document.createElement('span');
    badgeCreaciones.className = 'badge bg-light text-dark font-monospace border px-2 py-1';
    const iconHeart = document.createElement('i');
    iconHeart.className = 'bi bi-balloon-heart me-1 text-primary';
    badgeCreaciones.appendChild(iconHeart);
    const piezasCount = Number(u.creaciones_asociadas ?? 0);
    badgeCreaciones.appendChild(document.createTextNode(`${piezasCount} piezas`));
    tdCreaciones.appendChild(badgeCreaciones);
    tr.appendChild(tdCreaciones);

    // Col 6: Fecha de Registro
    const tdFecha = document.createElement('td');
    const fechaSpan = document.createElement('span');
    fechaSpan.className = 'font-monospace small text-dark';
    const iconCal = document.createElement('i');
    iconCal.className = 'bi bi-calendar3 me-1';
    fechaSpan.appendChild(iconCal);
    fechaSpan.appendChild(document.createTextNode(String(u.creado_en || '')));
    tdFecha.appendChild(fechaSpan);
    tr.appendChild(tdFecha);

    // Col 7: Acciones
    const tdAcciones = document.createElement('td');
    tdAcciones.className = 'text-end pe-3';
    const btnGroup = document.createElement('div');
    btnGroup.className = 'btn-group btn-group-sm';

    // Botón: Modificar Rol
    const btnEdit = document.createElement('button');
    btnEdit.type = 'button';
    btnEdit.className = 'btn btn-outline-secondary btn-editar-rol';
    btnEdit.setAttribute('data-bs-toggle', 'modal');
    btnEdit.setAttribute('data-bs-target', '#modalEditarRolUsuario');
    btnEdit.setAttribute('data-user-id', String(u.id));
    btnEdit.setAttribute('data-username', u.username);
    btnEdit.setAttribute('data-rol', u.rol);
    btnEdit.setAttribute('data-whatsapp', u.whatsapp || '');
    btnEdit.title = 'Modificar Rol de Acceso';
    const iconEdit = document.createElement('i');
    iconEdit.className = 'bi bi-pencil-square';
    btnEdit.appendChild(iconEdit);
    btnGroup.appendChild(btnEdit);

    // Botón: Restablecer Contraseña
    const btnReset = document.createElement('button');
    btnReset.type = 'button';
    btnReset.className = 'btn btn-outline-secondary btn-reset-password';
    btnReset.setAttribute('data-bs-toggle', 'modal');
    btnReset.setAttribute('data-bs-target', '#modalRestablecerPassword');
    btnReset.setAttribute('data-user-id', String(u.id));
    btnReset.setAttribute('data-username', u.username);
    btnReset.title = 'Restablecer Contraseña';
    const iconKey = document.createElement('i');
    iconKey.className = 'bi bi-key';
    btnReset.appendChild(iconKey);
    btnGroup.appendChild(btnReset);

    // Acciones de ciclo de vida (Baja Lógica / Reactivación)
    if (u.activo === 1) {
      if (u.id === 1) {
        // Salvaguarda ID #1: Bloqueado
        const btnLock = document.createElement('button');
        btnLock.type = 'button';
        btnLock.className = 'btn btn-outline-secondary disabled';
        btnLock.title = 'Salvaguarda de Administrador Raíz (ID #1): No puede ser eliminado ni desactivado.';
        const iconLock = document.createElement('i');
        iconLock.className = 'bi bi-lock-fill text-muted';
        btnLock.appendChild(iconLock);
        btnGroup.appendChild(btnLock);
      } else {
        // Botón: Baja Lógica (R-01)
        const btnDel = document.createElement('button');
        btnDel.type = 'button';
        btnDel.className = 'btn btn-outline-danger btn-eliminar-usuario';
        btnDel.setAttribute('data-user-id', String(u.id));
        btnDel.setAttribute('data-username', u.username);
        btnDel.setAttribute('data-creaciones', String(piezasCount));
        btnDel.title = 'Dar de baja lógicamente';
        const iconTrash = document.createElement('i');
        iconTrash.className = 'bi bi-trash';
        btnDel.appendChild(iconTrash);
        btnGroup.appendChild(btnDel);
      }
    } else {
      // Botón: Reactivar Cuenta
      const btnReact = document.createElement('button');
      btnReact.type = 'button';
      btnReact.className = 'btn btn-outline-success btn-reactivar-usuario';
      btnReact.setAttribute('data-user-id', String(u.id));
      btnReact.setAttribute('data-username', u.username);
      btnReact.title = 'Reactivar Cuenta en el Sistema';
      const iconReact = document.createElement('i');
      iconReact.className = 'bi bi-arrow-counterclockwise';
      btnReact.appendChild(iconReact);
      btnGroup.appendChild(btnReact);
    }

    tdAcciones.appendChild(btnGroup);
    tr.appendChild(tdAcciones);

    return tr;
  }

  /** Renderiza la paginación DOM-safe */
  function renderPagination(pag) {
    if (!paginacionContainer) return;
    paginacionContainer.replaceChildren();
    if (!pag || pag.total_paginas <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0';

    // Botón Anterior
    const liPrev = document.createElement('li');
    liPrev.className = `page-item ${pag.tiene_anterior ? '' : 'disabled'}`;
    const aPrev = document.createElement('button');
    aPrev.type = 'button';
    aPrev.className = 'page-link';
    aPrev.textContent = '« Anterior';
    if (pag.tiene_anterior) {
      aPrev.onclick = () => {
        state.page = pag.pagina_actual - 1;
        fetchUsers();
      };
    }
    liPrev.appendChild(aPrev);
    ul.appendChild(liPrev);

    // Páginas numéricas
    for (let p = 1; p <= pag.total_paginas; p++) {
      const liPage = document.createElement('li');
      liPage.className = `page-item ${p === pag.pagina_actual ? 'active' : ''}`;
      const aPage = document.createElement('button');
      aPage.type = 'button';
      aPage.className = 'page-link';
      aPage.textContent = String(p);
      if (p !== pag.pagina_actual) {
        aPage.onclick = () => {
          state.page = p;
          fetchUsers();
        };
      }
      liPage.appendChild(aPage);
      ul.appendChild(liPage);
    }

    // Botón Siguiente
    const liNext = document.createElement('li');
    liNext.className = `page-item ${pag.tiene_siguiente ? '' : 'disabled'}`;
    const aNext = document.createElement('button');
    aNext.type = 'button';
    aNext.className = 'page-link';
    aNext.textContent = 'Siguiente »';
    if (pag.tiene_siguiente) {
      aNext.onclick = () => {
        state.page = pag.pagina_actual + 1;
        fetchUsers();
      };
    }
    liNext.appendChild(aNext);
    ul.appendChild(liNext);

    paginacionContainer.appendChild(ul);
  }

  /** Consulta asíncrona de usuarios al servidor */
  async function fetchUsers() {
    const seq = ++state.seq;
    tableBody.replaceChildren();

    const loadingTr = document.createElement('tr');
    const loadingTd = document.createElement('td');
    loadingTd.colSpan = 7;
    loadingTd.className = 'text-center py-5 text-muted';
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border spinner-border-sm text-primary me-2';
    spinner.setAttribute('role', 'status');
    loadingTd.appendChild(spinner);
    loadingTd.appendChild(document.createTextNode('Cargando directorio de creadores...'));
    loadingTr.appendChild(loadingTd);
    tableBody.appendChild(loadingTr);

    const url = `${INDEX_URL}?estado=${encodeURIComponent(state.estado)}&pagina=${state.page}&limite=${PAGE_LIMIT}`;
    try {
      const res = await fetch(url, { headers: authHeaders() });
      if (seq !== state.seq) return;

      if (res.status === 401) {
        clearSession();
        window.location.replace('index.php');
        return;
      }

      const json = await parseJson(res);
      if (!res.ok || !json || !json.exito) {
        tableBody.replaceChildren();
        const errTr = document.createElement('tr');
        const errTd = document.createElement('td');
        errTd.colSpan = 7;
        errTd.className = 'text-center py-4 text-danger';
        errTd.textContent = (json && json.error && json.error.mensaje) || 'No fue posible cargar los usuarios.';
        errTr.appendChild(errTd);
        tableBody.appendChild(errTr);
        return;
      }

      const users = json.datos || [];
      const pag = (json.meta && json.meta.paginacion) || json.paginacion || null;
      const resumen = (json.meta && json.meta.resumen) || json.resumen || null;

      userCache.clear();
      users.forEach(u => userCache.set(String(u.id), u));

      tableBody.replaceChildren();

      if (users.length === 0) {
        if (emptyState) emptyState.classList.remove('d-none');
        table.classList.add('d-none');
      } else {
        if (emptyState) emptyState.classList.add('d-none');
        table.classList.remove('d-none');
        users.forEach(u => tableBody.appendChild(renderUserRow(u)));
      }

      renderPagination(pag);
      updateKPIs(resumen);
    } catch (err) {
      if (seq !== state.seq) return;
      tableBody.replaceChildren();
      const errTr = document.createElement('tr');
      const errTd = document.createElement('td');
      errTd.colSpan = 7;
      errTd.className = 'text-center py-4 text-danger';
      errTd.textContent = 'Error de conexión al cargar los usuarios.';
      errTr.appendChild(errTd);
      tableBody.appendChild(errTr);
    }
  }

  // 2. Filtro por Estado (Tabs / Pill buttons)
  const filtroEstado = document.getElementById('filtroEstadoUsuarios');
  if (filtroEstado) {
    filtroEstado.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        const est = btn.getAttribute('data-estado');
        if (!est || est === state.estado) return;
        filtroEstado.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        state.estado = est;
        state.page = 1;
        fetchUsers();
      });
    });
  }

  // 3. Formulario de Alta de Usuario (#formCrearUsuario)
  const formCrearUsuario = document.getElementById('formCrearUsuario');
  if (formCrearUsuario) {
    const inputUsername = document.getElementById('nuevoUsername');
    const selectRol = document.getElementById('nuevoRol');
    const inputPassword = document.getElementById('nuevoPassword');
    const inputWa = document.getElementById('nuevoWhatsapp');
    const alertEl = document.getElementById('usuarioAlert');
    const submitBtn = document.getElementById('btnSubmitCrearUsuario');

    formCrearUsuario.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (alertEl) alertEl.classList.add('d-none');

      const username = inputUsername ? inputUsername.value.trim().replace(/^@/, '') : '';
      const rol = selectRol ? selectRol.value : 'artesano';
      const password = inputPassword ? inputPassword.value : '';
      const waCheck = validateWhatsapp(inputWa ? inputWa.value : '');

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

      if (!waCheck.ok) {
        if (alertEl) {
          alertEl.textContent = 'WhatsApp inválido: usa entre 8 y 15 dígitos (máx. 20 caracteres) o déjalo vacío.';
          alertEl.classList.remove('d-none');
        }
        return;
      }

      if (submitBtn) submitBtn.disabled = true;

      try {
        const payload = {
          username,
          password,
          rol,
          whatsapp: waCheck.value === '' ? null : waCheck.value,
        };
        const { ok, status, json } = await postJson(CREAR_URL, payload);

        if (!ok || !json || !json.exito) {
          const msg = (json && json.error && json.error.mensaje) || `Error al crear creador (HTTP ${status}).`;
          if (alertEl) {
            alertEl.textContent = msg;
            alertEl.classList.remove('d-none');
          }
          return;
        }

        formCrearUsuario.reset();
        const modalEl = document.getElementById('modalCrearUsuario');
        if (modalEl && window.bootstrap) {
          const modalInstance = window.bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();
        }

        showGlobalAlert(`Creador @${username} registrado exitosamente.`, 'success');
        await fetchUsers();
      } catch {
        if (alertEl) {
          alertEl.textContent = 'Error de comunicación con el servidor.';
          alertEl.classList.remove('d-none');
        }
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // 4. Modal Modificar Rol (#modalEditarRolUsuario)
  const formEditarRol = document.getElementById('formEditarRolUsuario');
  const modalEditarRolEl = document.getElementById('modalEditarRolUsuario');

  if (modalEditarRolEl) {
    modalEditarRolEl.addEventListener('show.bs.modal', (e) => {
      const triggerBtn = e.relatedTarget;
      if (!triggerBtn) return;

      const userId = triggerBtn.getAttribute('data-user-id') || '1';
      const username = triggerBtn.getAttribute('data-username') || 'usuario';
      const currentRol = triggerBtn.getAttribute('data-rol') || 'artesano';
      const currentWa = triggerBtn.getAttribute('data-whatsapp') || '';

      const inputUserId = document.getElementById('editRolUserId');
      const displayUsername = document.getElementById('editRolUsernameDisplay');
      const badgeId = document.getElementById('editRolIdBadge');
      const selectRol = document.getElementById('selectEditarRol');
      const alertBox = document.getElementById('editarRolAlert');
      const rootWarning = document.getElementById('adminRootWarning');
      const inputWa = document.getElementById('editWhatsapp');

      if (inputUserId) inputUserId.value = userId;
      if (displayUsername) displayUsername.textContent = '@' + username;
      if (badgeId) badgeId.textContent = 'ID: #' + userId;
      if (selectRol) {
        selectRol.value = currentRol;
        // Salvaguarda ID #1: Deshabilitar cambio de rol para cuenta raíz
        if (userId === '1') {
          selectRol.disabled = true;
          if (rootWarning) rootWarning.classList.remove('d-none');
        } else {
          selectRol.disabled = false;
          if (rootWarning) rootWarning.classList.add('d-none');
        }
      }
      if (inputWa) inputWa.value = currentWa;
      if (alertBox) alertBox.classList.add('d-none');
    });
  }

  if (formEditarRol) {
    const submitBtn = document.getElementById('btnGuardarRolUsuario');

    formEditarRol.addEventListener('submit', async (e) => {
      e.preventDefault();

      const inputUserId = document.getElementById('editRolUserId');
      const selectRol = document.getElementById('selectEditarRol');
      const alertBox = document.getElementById('editarRolAlert');

      const userId = inputUserId ? Number(inputUserId.value) : 0;
      const newRol = selectRol ? selectRol.value : 'artesano';

      if (!userId) return;

      // Salvaguarda ID #1 en cliente (R-05)
      if (userId === 1 && newRol !== 'admin') {
        showErrorModal({
          title: 'Acción Restringida (R-05)',
          text: 'La cuenta principal de administrador titular (ID #1) está protegida y no puede ser degradada.',
        });
        if (alertBox) {
          alertBox.textContent = 'Acción Restringida: La cuenta principal de administrador titular (ID #1) no puede ser degradada.';
          alertBox.classList.remove('d-none');
        }
        return;
      }

      if (submitBtn) submitBtn.disabled = true;

      try {
        const { ok, status, json } = await postJson(ROL_URL, { id: userId, rol: newRol });

        if (!ok || !json || !json.exito) {
          const msg = (json && json.error && json.error.mensaje) || `Error al actualizar rol (HTTP ${status}).`;
          if (alertBox) {
            alertBox.textContent = msg;
            alertBox.classList.remove('d-none');
          }
          return;
        }

        if (modalEditarRolEl && window.bootstrap) {
          const modalInstance = window.bootstrap.Modal.getInstance(modalEditarRolEl);
          if (modalInstance) modalInstance.hide();
        }

        showGlobalAlert('Rol de usuario actualizado exitosamente.', 'success');
        await fetchUsers();
      } catch {
        if (alertBox) {
          alertBox.textContent = 'Error de comunicación con el servidor.';
          alertBox.classList.remove('d-none');
        }
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // 4b. Guardar WhatsApp dentro del modal de rol
  const btnSaveWa = document.getElementById('btnGuardarWhatsapp');
  if (btnSaveWa) {
    btnSaveWa.addEventListener('click', async () => {
      const inputUserId = document.getElementById('editRolUserId');
      const inputWa = document.getElementById('editWhatsapp');
      const alertBox = document.getElementById('editarRolAlert');
      const say = (msg) => {
        if (alertBox) {
          alertBox.textContent = msg;
          alertBox.classList.remove('d-none');
        }
      };

      const userId = inputUserId ? Number(inputUserId.value) : 0;
      if (!userId) return;

      const check = validateWhatsapp(inputWa ? inputWa.value : '');
      if (!check.ok) {
        say('WhatsApp inválido: usa entre 8 y 15 dígitos (máx. 20 caracteres) o déjalo vacío.');
        return;
      }

      btnSaveWa.disabled = true;
      try {
        const { ok, status, json } = await postJson(WHATSAPP_URL, {
          id: userId,
          whatsapp: check.value === '' ? null : check.value,
        });

        if (!ok || !json || !json.exito) {
          const msg = (json && json.error && json.error.mensaje) || `No se pudo guardar (HTTP ${status}).`;
          say(msg);
          return;
        }

        const saved = (json.datos && json.datos.whatsapp != null) ? String(json.datos.whatsapp) : '';
        say(saved === '' ? 'WhatsApp retirado exitosamente.' : `WhatsApp guardado: ${saved}`);
        await fetchUsers();
      } catch {
        say('Error de comunicación con el servidor.');
      } finally {
        btnSaveWa.disabled = false;
      }
    });
  }

  // 5. Modal Restablecer Contraseña (#modalRestablecerPassword)
  const modalResetEl = document.getElementById('modalRestablecerPassword');
  const formResetPwd = document.getElementById('formRestablecerPassword');

  if (modalResetEl) {
    modalResetEl.addEventListener('show.bs.modal', (e) => {
      const triggerBtn = e.relatedTarget;
      if (!triggerBtn) return;

      const userId = triggerBtn.getAttribute('data-user-id') || '0';
      const username = triggerBtn.getAttribute('data-username') || 'usuario';

      const inputUserId = document.getElementById('resetPasswordUserId');
      const displayUsername = document.getElementById('resetPasswordUsernameDisplay');
      const badgeId = document.getElementById('resetPasswordIdBadge');
      const inputPwd = document.getElementById('nuevaPasswordInput');
      const alertBox = document.getElementById('resetPasswordAlert');
      const resultBox = document.getElementById('resetPasswordResult');
      const tempBox = document.getElementById('tempPasswordBox');

      if (inputUserId) inputUserId.value = userId;
      if (displayUsername) displayUsername.textContent = '@' + username;
      if (badgeId) badgeId.textContent = 'ID: #' + userId;
      if (inputPwd) inputPwd.value = '';
      if (alertBox) alertBox.classList.add('d-none');
      if (resultBox) resultBox.classList.add('d-none');
      if (tempBox) tempBox.classList.add('d-none');
    });
  }

  if (formResetPwd) {
    const submitBtn = document.getElementById('btnSubmitResetPassword');

    formResetPwd.addEventListener('submit', async (e) => {
      e.preventDefault();

      const inputUserId = document.getElementById('resetPasswordUserId');
      const inputPwd = document.getElementById('nuevaPasswordInput');
      const alertBox = document.getElementById('resetPasswordAlert');
      const resultBox = document.getElementById('resetPasswordResult');
      const successMsg = document.getElementById('resetResultSuccessMsg');
      const tempBox = document.getElementById('tempPasswordBox');
      const tempDisplay = document.getElementById('tempPasswordDisplay');

      const userId = inputUserId ? Number(inputUserId.value) : 0;
      const pwdValue = inputPwd ? inputPwd.value.trim() : '';

      if (!userId) return;

      if (pwdValue !== '' && pwdValue.length < 6) {
        if (alertBox) {
          alertBox.textContent = 'La nueva contraseña debe tener al menos 6 caracteres.';
          alertBox.classList.remove('d-none');
        }
        return;
      }

      if (alertBox) alertBox.classList.add('d-none');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const payload = {
          id: userId,
          nueva_password: pwdValue !== '' ? pwdValue : null,
        };
        const { ok, status, json } = await postJson(RESET_PWD_URL, payload);

        if (!ok || !json || !json.exito) {
          const msg = (json && json.error && json.error.mensaje) || `Error al restablecer contraseña (HTTP ${status}).`;
          if (alertBox) {
            alertBox.textContent = msg;
            alertBox.classList.remove('d-none');
          }
          return;
        }

        const datos = json.datos || {};
        if (resultBox) {
          if (successMsg) successMsg.textContent = json.mensaje || 'Contraseña actualizada exitosamente.';
          if (datos.es_autogenerada && datos.password_temporal && tempDisplay && tempBox) {
            tempDisplay.textContent = datos.password_temporal;
            tempBox.classList.remove('d-none');
          } else if (tempBox) {
            tempBox.classList.add('d-none');
          }
          resultBox.classList.remove('d-none');
        }

        if (datos.es_autogenerada && datos.password_temporal) {
          showSuccessModal({
            title: 'Contraseña Temporal Generada',
            html: `Se generó una clave temporal para el usuario:<br><br>` +
                  `<div class="p-3 bg-light border rounded font-monospace fs-5 text-primary text-center fw-bold select-all" style="letter-spacing: 0.08em;">${escapeHtml(datos.password_temporal)}</div>` +
                  `<br><small class="text-muted">Proporciona esta clave al colaborador para que pueda acceder.</small>`,
          });
        }

        if (inputPwd) inputPwd.value = '';
        showGlobalAlert('Contraseña restablecida exitosamente.', 'success');
      } catch {
        if (alertBox) {
          alertBox.textContent = 'Error de comunicación con el servidor.';
          alertBox.classList.remove('d-none');
        }
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // 6. Delegación de eventos en la tabla para Baja Lógica y Reactivación
  table.addEventListener('click', async (e) => {
    // A. Baja Lógica de Usuario (R-01)
    const delBtn = e.target.closest('.btn-eliminar-usuario');
    if (delBtn) {
      const userId = Number(delBtn.getAttribute('data-user-id'));
      const username = delBtn.getAttribute('data-username') || 'usuario';
      const creaciones = Number(delBtn.getAttribute('data-creaciones') || 0);

      if (userId === 1) {
        showErrorModal({
          title: 'Acción Restringida (R-05)',
          text: 'La cuenta principal de administrador titular (ID #1) no puede ser eliminada bajo ninguna circunstancia.',
        });
        showGlobalAlert('Operación denegada: La cuenta del administrador titular (ID #1) no puede ser eliminada (R-05).', 'danger');
        return;
      }

      const confirmed = await confirmModal({
        title: 'Confirmar Baja Lógica',
        html: `¿Estás seguro de que deseas dar de baja la cuenta <strong>@${escapeHtml(username)}</strong>?<br><small class="text-muted">La cuenta pasará a estado inactivo y podrá ser reactivada en cualquier momento.</small>`,
        icon: 'warning',
        confirmText: 'Sí, dar de baja',
        cancelText: 'Conservar cuenta',
        danger: true,
      });

      if (!confirmed) {
        return;
      }

      delBtn.disabled = true;
      try {
        const { ok, status, json } = await postJson(ELIMINAR_URL, { id: userId });

        if (!ok || !json || !json.exito) {
          const msg = (json && json.error && json.error.mensaje) || `No se pudo eliminar al usuario (HTTP ${status}).`;
          showErrorModal({
            title: status === 409 ? 'Protección de Integridad Referencial' : 'Error al Procesar Baja',
            text: msg,
          });
          showGlobalAlert(msg, 'danger');
          return;
        }

        showGlobalAlert(`Usuario @${username} dado de baja de forma lógica en SQLite.`, 'success');
        await fetchUsers();
      } catch {
        showErrorModal({
          title: 'Error de Comunicación',
          text: 'Ocurrió un error al comunicarse con el servidor al procesar la baja.',
        });
        showGlobalAlert('Error de comunicación con el servidor al procesar la baja.', 'danger');
      } finally {
        delBtn.disabled = false;
      }
      return;
    }

    // B. Reactivación de Cuenta
    const reactBtn = e.target.closest('.btn-reactivar-usuario');
    if (reactBtn) {
      const userId = Number(reactBtn.getAttribute('data-user-id'));
      const username = reactBtn.getAttribute('data-username') || 'usuario';

      const confirmed = await confirmModal({
        title: 'Reactivar Creador',
        html: `¿Deseas restaurar la cuenta de <strong>@${escapeHtml(username)}</strong> a estado activo?<br><small class="text-muted">El usuario podrá volver a iniciar sesión en la plataforma.</small>`,
        icon: 'question',
        confirmText: 'Sí, reactivar',
        cancelText: 'Cancelar',
      });

      if (!confirmed) {
        return;
      }

      reactBtn.disabled = true;
      try {
        const { ok, status, json } = await postJson(REACTIVAR_URL, { id: userId });

        if (!ok || !json || !json.exito) {
          const msg = (json && json.error && json.error.mensaje) || `No se pudo reactivar al usuario (HTTP ${status}).`;
          showErrorModal({
            title: 'Error al Reactivar',
            text: msg,
          });
          showGlobalAlert(msg, 'danger');
          return;
        }

        showGlobalAlert(`Cuenta @${username} reactivada exitosamente.`, 'success');
        await fetchUsers();
      } catch {
        showErrorModal({
          title: 'Error de Comunicación',
          text: 'Ocurrió un error al comunicarse con el servidor al reactivar la cuenta.',
        });
        showGlobalAlert('Error de comunicación con el servidor al reactivar la cuenta.', 'danger');
      } finally {
        reactBtn.disabled = false;
      }
    }
  });

  // Carga inicial de usuarios
  fetchUsers();
}
