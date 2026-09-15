# 004 · Autenticación, Token Bearer & Estado Reactivo del Navbar

**Estado:** en curso

## Qué hace

Conecta la interfaz de usuario con la API de autenticación del backend. Permite a los artesanos y administradores iniciar sesión desde el modal oficial de la barra de navegación (`#modalLogin`) y cerrar sesión desde `.btn-craft-logout` de forma asíncrona (`fetch`), sin recargas bruscas de página. Gestiona el token Bearer en el cliente (`localStorage`), valida la sesión activa contra `GET /api/auth/me.php` y actualiza reactivamente los accesos del Navbar y del menú lateral según el rol (`admin` vs `artesano`).

> ⚙️ **Decisiones de gobernanza (Acción 2 · 14/09/2026) ya aplicadas al backend y que 004 debe consumir:**
> 1. **Almacenamiento en `localStorage`** + **CSP estricto** (`script-src 'self'` + CDN, `connect-src 'self'`, `frame-ancestors 'none'` en `views/layouts/main.php`) + auditoría de sanitización de `innerHTML` (H-004). Véase `docs/security/auditoria-sanitizacion-js.md`.
> 2. **Logout con revocación en servidor**: `POST /api/auth/logout.php` revoca el `jti` del Bearer en `tokens_revocados` (ADR-016). El cliente debe **descartar el token local** tras el 200.
> 3. **Login protegido**: `login_intentos` con umbrales 5/cuenta y 20/IP en ventana de 15 min → `HTTP 429`. El modal debe mostrar feedback claro ante 429, no solo 401.
> 4. **Rotación de secretos HMAC**: claim `ver` + `auth.token_secret_anterior` (transparente para el frontend).

## Por qué

Actualmente, las vistas del frontend y los controladores de `api/auth/` operan de forma independiente. Este cableado permite que el usuario inicie sesión real, almacene su token de forma persistente y acceda de inmediato a las funcionalidades de administración protegidas por `AuthGuard` y `RoleGuard`, manteniendo la navegación fluida y la experiencia visual "Algodón Nórdico".

## Criterios de aceptación

> ✅ **Verificados en la subfase 4.1 (2026-09-14)** — traceabilidad AC-1…AC-8 y evidencia en `docs/testing/subfase-4.1-auth-sesion.md` §Criterios de aceptación.
> Observación de nomenclatura: la spec menciona `#modalLogin`, pero el componente `modal_login.php` usa el id real `#loginModal` (referenciado por `#btnNavLogin`); correspondencia funcional exacta. Sin cambio de código ni spec (regla de discrepancia P6).

- [x] El envío de credenciales en el modal `#loginModal` realiza una petición asíncrona `POST /api/auth/login.php`.
- [x] Ante **bloqueo por fuerza bruta (HTTP 429)**, muestra feedback diferenciado (cuenta/IP temporalmente bloqueada) sin recargar la página.
- [x] Ante credenciales erróneas o cuenta inactiva (401), muestra feedback visual de error accesible dentro del propio modal, sin recargar la página ni usar diálogos nativos `alert()`.
- [x] Ante login exitoso (200), almacena el token Bearer y la información de perfil en `localStorage` y cierra el modal automáticamente.
- [x] El Navbar reacciona de inmediato: sustituye el botón "Iniciar Sesión" por el sello artesanal pespunteado (`.badge-artisan-seal`) con el nombre del usuario y el botón de cierre de sesión (`.btn-craft-logout`).
- [x] Si el rol es `artesano`, despliega los accesos a Creaciones y Pedidos; si es `admin`, habilita adicionalmente el acceso a Usuarios.
- [x] Al cargar cualquier página, el cliente verifica el token almacenado invocando `GET /api/auth/me.php`. Si el token expiró o fue revocado (401), limpia `localStorage` y restaura el estado público sin errores en consola.
- [x] El clic en el botón de logout invoca `POST /api/auth/logout.php`. Ante **200**, descarta el token y perfil de `localStorage` (revocación en servidor ya confirmada por el backend) y, si el usuario se encuentra en una vista administrativa privada (`creaciones.php`, `pedidos.php`, `usuarios.php`), lo redirige al catálogo público (`index.php`).

## Fuera de alcance

- Peticiones del catálogo público y filtros reactivos (Feature 005).
- Mutaciones de creaciones o subida multipart de fotos (Feature 006).
- Creación de nuevos usuarios o reseteo de claves desde el panel (Feature 008).
