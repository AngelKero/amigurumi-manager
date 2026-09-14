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
- **`localStorage` aprobado por gobernanza (Acción 2 · H-004) ÚNICAMENTE bajo CSP estricto y sanitización JS:** El layout ya emite `Content-Security-Policy` (`script-src 'self'` + CDN, `connect-src 'self'`, `frame-ancestors 'none'`) y la auditoría eliminó los vectores de `innerHTML`. Violar cualquiera de las dos condiciones invalida esta decisión (ver ADR-016 y `docs/security/auditoria-sanitizacion-js.md`).
- **`logout()` debe manejar la respuesta del backend:** tras el 200 de `POST /api/auth/logout.php` (revocación server-side del `jti`, ADR-016) el cliente descarta `localStorage`. Ante red caída, descarte local igualmente (best-effort del cliente).
- **`login()` debe mapear `HTTP 429`:** el backend devuelve 429 por bloqueo de fuerza bruta (`login_intentos`, 5/cuenta · 20/IP · 15 min). El modal mostrará mensaje diferenciado.
- **Validación silenciosa de sesión en background:** En cada carga de página se invoca `GET /api/auth/me.php` en segundo plano para corroborar que el token no haya expirado, haya sido revocado (`tokens_revocados`) ni la cuenta haya sido dada de baja lógicamente (`activo = 0`), garantizando revocación inmediata.

## Riesgos

- **Exposición a XSS:** Mitigado por el CSP estricto en el layout, el escape riguroso en vistas y la auditoría de sanitización de `innerHTML` (H-004: `docs/security/auditoria-sanitizacion-js.md`). Cualquier nueva interpolación de datos en `innerHTML` debe usar `escapeHtml` (`src/js/modules/dom-safe.js`) — regla en `.agents/rules/innerhtml-dom-safety.md`.
- **Desincronización visual momentánea (FOUC de autenticación):** Ocultar suavemente el contenedor de usuario hasta que `auth.js` confirme el estado del token o mostrar placeholder no invasivo.
