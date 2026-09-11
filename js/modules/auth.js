/**
 * Module: Auth (Sesión de Artesano y Modal de Login)
 * Single Responsibility: Gestión del estado de autenticación y modales de acceso.
 */

export function initAuth() {
  const navArtisanDropdown = document.getElementById('navArtisanDropdown');
  const btnNavLogin = document.getElementById('btnNavLogin');
  const navUserBadge = document.getElementById('navUserBadge');

  // Verificar si la sesión simulada del artesano está activa
  const isArtisanSession = localStorage.getItem('amigurumi_session_active') === 'true';

  if (isArtisanSession && navArtisanDropdown && btnNavLogin && navUserBadge) {
    navArtisanDropdown.classList.remove('d-none');
    btnNavLogin.classList.add('d-none');
    navUserBadge.classList.remove('d-none');
    navUserBadge.classList.add('d-flex');
  }

  // Manejar botones de cierre de sesión
  const logoutButtons = document.querySelectorAll('.btn-nav-logout');
  logoutButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      localStorage.removeItem('amigurumi_session_active');
      window.location.reload();
    });
  });

  // Manejar envío de credenciales dentro del modal de acceso
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const usernameInput = document.getElementById('loginUsername');
      const passwordInput = document.getElementById('loginPassword');
      const alertPlaceholder = document.getElementById('loginAlert');

      if (!usernameInput || !passwordInput) return;

      const user = usernameInput.value.trim();
      const pass = passwordInput.value.trim();

      if (user === 'admin' && pass === 'admin123') {
        localStorage.setItem('amigurumi_session_active', 'true');
        if (alertPlaceholder) alertPlaceholder.classList.add('d-none');
        
        const loginModalEl = document.getElementById('loginModal');
        if (loginModalEl && window.bootstrap) {
          const modalInstance = window.bootstrap.Modal.getInstance(loginModalEl);
          if (modalInstance) modalInstance.hide();
        }
        window.location.reload();
      } else {
        if (alertPlaceholder) {
          alertPlaceholder.textContent = 'Credenciales inválidas. Usuario o contraseña incorrectos.';
          alertPlaceholder.classList.remove('d-none');
        }
      }
    });
  }
}
