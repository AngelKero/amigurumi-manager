/**
 * Module: Auth (Autenticación Real Bearer Token contra /api/auth/*)
 * Single Responsibility: Gestión de sesión (token Bearer + perfil), login/logout asíncronos
 * contra la API real y reactividad del Navbar/sidebar según rol (admin vs artesano).
 *
 * Cumple los prerrequisitos de seguridad de Gobernanza · Acción 2:
 *  - Almacenamiento del Bearer en localStorage bajo CSP estricto (H-004).
 *  - Logout con revocación server-side (POST /api/auth/logout.php, ADR-016).
 *  - Login con feedback diferenciado ante HTTP 429 (fuerza bruta, H-003).
 */

export const TOKEN_KEY = 'crochet_auth_token';
export const USER_KEY = 'crochet_auth_user';

const PRIVATE_PAGES = ['creaciones.php', 'pedidos.php', 'usuarios.php'];

/** Token Bearer almacenado (o null). */
export function getToken() {
  return localStorage.getItem(TOKEN_KEY) || null;
}

/** Perfil de usuario almacenado (objeto) o null. */
export function getUser() {
  const raw = localStorage.getItem(USER_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

/** Indica si existe un token almacenado (sesión potencialmente activa). */
export function isAuthenticated() {
  const token = getToken();
  return !!token && token.length > 0;
}

/** Persiste la sesión (token + perfil) en localStorage. */
export function setSession(token, user) {
  localStorage.setItem(TOKEN_KEY, token);
  if (user) {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
  }
}

/** Descarta la sesión local (token + perfil). */
export function clearSession() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
}

/** Petición autenticada con Bearer token (si existe) y parseo seguro de JSON. */
async function authedFetch(url, options = {}) {
  const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
  const token = getToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;

  const res = await fetch(url, { ...options, headers });

  let body = null;
  const contentType = res.headers.get('content-type') || '';
  if (contentType.includes('application/json')) {
    try {
      body = await res.json();
    } catch {
      body = null;
    }
  }
  return { ok: res.ok, status: res.status, body };
}

/** Realiza login contra la API y persiste la sesión ante 200. Lanza Error con status. */
export async function login(username, password) {
  const res = await fetch('/api/auth/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password }),
  });

  let body = null;
  const contentType = res.headers.get('content-type') || '';
  if (contentType.includes('application/json')) {
    try {
      body = await res.json();
    } catch {
      body = null;
    }
  }

  if (res.ok && body && body.exito) {
    const datos = body.datos || {};
    if (datos.token && datos.usuario) {
      setSession(datos.token, datos.usuario);
    }
    return body;
  }

  const error = body && body.error ? body.error : {};
  const err = new Error(error.mensaje || `Error de autenticación (HTTP ${res.status}).`);
  err.status = res.status;
  throw err;
}

/** Cierra la sesión: revocación server-side best-effort y descarte local siempre. (ADR-016) */
export async function logout() {
  try {
    await authedFetch('/api/auth/logout.php', { method: 'POST' });
  } catch {
    // Red caída: el cliente descarta igualmente (best-effort).
  }
  clearSession();
}

/** Verifica la sesión en segundo plano contra /api/auth/me.php. */
export async function checkSession() {
  if (!isAuthenticated()) {
    renderAuthState(null);
    return null;
  }

  const { ok, status, body } = await authedFetch('/api/auth/me.php');

  if (ok && body && body.exito && body.datos) {
    const token = getToken();
    setSession(token, body.datos);
    renderAuthState(body.datos);
    return body.datos;
  }

  // Token expirado, revocado o cuenta inactiva → limpiar y restaurar estado público.
  if (status === 401 || status === 403) {
    clearSession();
    renderAuthState(null);
    if (isPrivatePage()) {
      window.location.replace('index.php');
    }
  }
  return null;
}

/** Actualiza reactivamente Navbar, sidebar y perfil del panel según el rol. */
export function renderAuthState(user) {
  const btnNavLogin = document.getElementById('btnNavLogin');
  const navUserBadge = document.getElementById('navUserBadge');
  const navUsername = document.getElementById('navArtisanUsername');

  const authenticated = !!user;

  if (btnNavLogin) btnNavLogin.classList.toggle('d-none', authenticated);
  if (navUserBadge) {
    navUserBadge.classList.toggle('d-none', !authenticated);
    navUserBadge.classList.toggle('d-flex', authenticated);
  }
  if (navUsername) {
    navUsername.textContent = authenticated ? `@${user.username}` : '';
  }

  // RBAC: solo admin ve el acceso a Usuarios en el menú lateral (R-05).
  const sidebarUsuarios = document.getElementById('sidebarLinkUsuarios');
  if (sidebarUsuarios) {
    sidebarUsuarios.style.display = authenticated && user.rol === 'admin' ? '' : 'none';
  }

  const panelProfile = document.getElementById('panelProfileUsername');
  if (panelProfile && authenticated) {
    panelProfile.textContent = `@${user.username}`;
  }
}

/** Indica si la página actual es una vista administrativa privada. */
export function isPrivatePage() {
  const page = (window.location.pathname.split('/').pop() || 'index.php').toLowerCase();
  return PRIVATE_PAGES.includes(page);
}

/** Página del panel según el rol (post-login). */
export function pageForRole(rol) {
  if (rol === 'admin') return 'usuarios.php';
  if (rol === 'artesano') return 'creaciones.php';
  if (rol === 'asistente') return 'pedidos.php';
  return 'index.php';
}

/** Activa los listeners del modal de login con manejo de estados 401/429/carga. */
function attachLoginModalListeners() {
  const loginForm = document.getElementById('loginForm');
  if (!loginForm) return;

  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const usernameInput = document.getElementById('loginUsername');
    const passwordInput = document.getElementById('loginPassword');
    const alertEl = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('btnLoginSubmit');

    if (!usernameInput || !passwordInput || !submitBtn) return;

    if (alertEl) {
      alertEl.textContent = '';
      alertEl.classList.add('d-none');
    }

    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Validando...';

    try {
      const body = await login(usernameInput.value.trim(), passwordInput.value);

      if (alertEl) alertEl.classList.add('d-none');

      const modalEl = document.getElementById('loginModal');
      if (modalEl && window.bootstrap) {
        const instance = window.bootstrap.Modal.getInstance(modalEl);
        if (instance) instance.hide();
      }
      const user = getUser();
      renderAuthState(user);
      window.location.href = pageForRole(user && user.rol);
    } catch (err) {
      if (alertEl) {
        alertEl.textContent =
          err.status === 429
            ? 'Demasiados intentos fallidos. La cuenta está temporalmente bloqueada (15 min).'
            : (err.message || 'No se pudo iniciar sesión. Inténtalo de nuevo.');
        alertEl.classList.remove('d-none');
      }
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHtml;
    }
  });
}

/** Activa los listeners de cierre de sesión (navbar y sidebar). */
function attachLogoutListeners() {
  document.querySelectorAll('.btn-nav-logout').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      await logout();
      renderAuthState(null);
      if (isPrivatePage()) {
        window.location.replace('index.php');
      } else {
        window.location.reload();
      }
    });
  });
}

/** Inicialización de la autenticación en todas las páginas. */
export function initAuth() {
  renderAuthState(getUser());
  attachLoginModalListeners();
  attachLogoutListeners();

  // Guarda de rutas privadas: sin token válido → redirección al catálogo público.
  if (isPrivatePage() && !isAuthenticated()) {
    window.location.replace('index.php');
    return;
  }

  checkSession();
}