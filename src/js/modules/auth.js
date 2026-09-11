/**
 * Module: Auth (Sesión de Artesano y Modal de Login)
 * Single Responsibility: Gestión del estado de autenticación y modales de acceso.
 */

export function initAuth() {
  const navArtisanDropdown = document.getElementById('navArtisanDropdown');
  const btnNavLogin = document.getElementById('btnNavLogin');
  const navUserBadge = document.getElementById('navUserBadge');

  // Verificar si la sesión simulada del artesano está activa
  const isArtisanSession = localStorage.getItem('crochet_session_active') === 'true' || localStorage.getItem('amigurumi_session_active') === 'true';

  if (isArtisanSession) {
    if (btnNavLogin) btnNavLogin.classList.add('d-none');
    if (navUserBadge) {
      navUserBadge.classList.remove('d-none');
      navUserBadge.classList.add('d-flex');
    }
    if (navArtisanDropdown) {
      navArtisanDropdown.classList.remove('d-none');
    }
  }

  // Manejar botones de cierre de sesión
  const logoutButtons = document.querySelectorAll('.btn-nav-logout');
  logoutButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      localStorage.removeItem('crochet_session_active');
      localStorage.removeItem('amigurumi_session_active');
      window.location.href = 'index.php';
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
        localStorage.setItem('crochet_session_active', 'true');
        localStorage.setItem('amigurumi_session_active', 'true');
        if (alertPlaceholder) alertPlaceholder.classList.add('d-none');
        
        const loginModalEl = document.getElementById('loginModal');
        if (loginModalEl && window.bootstrap) {
          const modalInstance = window.bootstrap.Modal.getInstance(loginModalEl);
          if (modalInstance) modalInstance.hide();
        }
        window.location.href = 'creaciones.php';
      } else {
        if (alertPlaceholder) {
          alertPlaceholder.textContent = 'Credenciales inválidas. Usuario o contraseña incorrectos.';
          alertPlaceholder.classList.remove('d-none');
        }
      }
    });
  }
}
