# 004 · Autenticación, Token Bearer & Estado Reactivo del Navbar — Tareas

- [ ] Identificar y preparar elementos semánticos del DOM en `views/components/navbar.php` (contenedores para invitado y autenticado).
- [ ] Implementar funciones de almacenamiento seguro y gestión de sesión en `src/js/modules/auth.js` (`getToken`, `setSession`, `clearSession`).
- [ ] Implementar llamada asíncrona de login a `POST /api/auth/login.php` con manejo de errores y estados de carga.
- [ ] Implementar llamada de verificación de sesión en segundo plano a `GET /api/auth/me.php`.
- [ ] Implementar llamada de cierre de sesión a `POST /api/auth/logout.php` con redirección defensiva.
- [ ] Integrar `auth.js` en `src/js/main.js` y activar listeners en `DOMContentLoaded`.
- [ ] Validar flujo en navegador: login exitoso, login fallido con feedback visual, logout y persistencia al recargar.
- [ ] Validar contra los criterios de aceptación de `spec.md`.
- [ ] Mover la feature a "Hecho" en `../../constitution/roadmap.md`.
