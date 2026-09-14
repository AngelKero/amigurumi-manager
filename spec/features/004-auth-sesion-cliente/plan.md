# 004 · Autenticación, Token Bearer & Estado Reactivo del Navbar — Plan

## Enfoque

Encapsular toda la lógica de autenticación del cliente dentro del módulo ES6 `src/js/modules/auth.js`. Exponer métodos claros (`login`, `logout`, `getToken`, `getUser`, `isAuthenticated`, `initAuth`) que se inicialicen desde `src/js/main.js` en todas las páginas. Mantener el DOM reactivo modificando dinámicamente las clases del Navbar (`views/components/navbar.php`) y del panel lateral (`views/components/panel_sidebar.php`).

## Implementación

1. **`src/js/modules/auth.js`**:
   - `TOKEN_KEY = 'crochet_auth_token'` y `USER_KEY = 'crochet_auth_user'`.
   - `login(username, password)`: `fetch('/api/auth/login.php', { method: 'POST', body: JSON.stringify(...) })`.
   - `logout()`: `fetch('/api/auth/logout.php', ...)` con cabecera `Authorization: Bearer <token>`.
   - `checkSession()`: invoca `/api/auth/me.php`. Si responde 200, sincroniza datos de usuario; si responde 401/403, ejecuta `clearSession()`.
   - `updateNavbars(user)`: manipula el DOM para alternar visibilidad entre botón de login y sello de artesano autenticado, respetando roles (`admin` vs `artesano`).
   - `attachLoginModalListeners()`: previene el submit tradicional del formulario en `#modalLogin`, deshabilita el botón de envío durante la petición y muestra spinner sutil.
2. **`src/js/main.js`**:
   - Importar `initAuth` de `./modules/auth.js` y ejecutarlo en el evento `DOMContentLoaded`.
   - Proteger rutas privadas en frontend: si la página actual es `creaciones.php`, `pedidos.php` o `usuarios.php` y no hay token válido, redirigir a `index.php`.
3. **`views/components/navbar.php`**:
   - Añadir IDs e identificadores semánticos para que el JavaScript pueda alternar limpiamente entre estado invitado y estado autenticado sin dependencias de recarga PHP.

## Decisiones

- **`localStorage` para persistencia de Bearer token:** Permite que la sesión sobreviva a la navegación entre distintas páginas estáticas del sitio (`index.php` $\rightarrow$ `detalle.php` $\rightarrow$ `creaciones.php`) sin depender de cookies de sesión PHP en SSR.
- **Validación silenciosa de sesión en background:** En cada carga de página se invoca `GET /api/auth/me.php` en segundo plano para corroborar que el token no haya expirado ni la cuenta haya sido dada de baja lógicamente (`activo = 0`), garantizando revocación inmediata.

## Riesgos

- **Exposición a XSS:** Mitigado por el escape riguroso en vistas y la inexistencia de inyección de scripts en el backend.
- **Desincronización visual momentánea (FOUC de autenticación):** Ocultar suavemente el contenedor de usuario hasta que `auth.js` confirme el estado del token o mostrar placeholder no invasivo.
