# 004 · Autenticación, Token Bearer & Estado Reactivo del Navbar — Tareas

> ⚙️ **Gate de testing por subfase (Gobernanza · Acción 3 · H-008):** la Fase 4 opera bajo el 3-Tier Testing Gate de `AGENTS.md §3`. Cada subfase 4.X crea SU propia suite CLI, SU log CLI y HTTP, y SU reporte ejecutivo antes de avanzar. Cualquier divergencia CLI/HTTP se triaje conforme a `docs/testing/protocolo-divergencia-cli-http.md`.

## Subfase 4.1 · Auth & Sesión de Cliente

### Implementación
- [ ] Identificar y preparar elementos semánticos del DOM en `views/components/navbar.php` (contenedores para invitado y autenticado).
- [ ] Implementar funciones de almacenamiento seguro y gestión de sesión en `src/js/modules/auth.js` (`getToken`, `setSession`, `clearSession`).
- [ ] Implementar llamada asíncrona de login a `POST /api/auth/login.php` con manejo de errores y estados de carga.
- [ ] Implementar llamada de verificación de sesión en segundo plano a `GET /api/auth/me.php`.
- [ ] Implementar llamada de cierre de sesión a `POST /api/auth/logout.php` con redirección defensiva.
- [ ] Integrar `auth.js` en `src/js/main.js` y activar listeners en `DOMContentLoaded`.

### Gate de testing de la subfase (obligatorio)
- [ ] Crear `tests/test-subfase-4.1.php` (suite CLI nativa) cubriendo los criterios de aceptación de `spec.md`.
- [ ] Ejecutar la suite: `php tests/test-subfase-4.1.php > logs/subfase-4.1-cli.log 2>&1` → 100% en verde.
- [ ] Pruebas HTTP en vivo contra `php -S localhost:8000` registradas en `logs/subfase-4.1-http.log`.
- [ ] Reporte ejecutivo `docs/testing/subfase-4.1-auth-sesion.md` con sección "Fallos Detectados & Correcciones Quirúrgicas".
- [ ] Validar contra los criterios de aceptación de `spec.md` y marcar tareas completadas.
- [ ] Mover la subfase a "Hecho" en `../../constitution/roadmap.md`.
- [ ] **HALT:** esperar aprobación explícita del usuario antes de la subfase 4.2.

> 🔒 **Prerrequisitos de seguridad (Gobernanza · Acción 2) que 4.1 debe verificar en su suite CLI/HTTP:**
> login bloqueado tras 5 fallos → `HTTP 429` (envelope `{"codigo":429}`); logout revocación server-side `revocado_en_servidor=true` y `me.php` → 401 tras logout; cabeceras `Content-Security-Policy` presentes en página y API.

## Subfase 4.2 · Catálogo Dinámico & Filtros Textiles

- [ ] Cablear vitrina con `GET /api/creaciones/index.php` (chips textiles, rango de precio, paginación).
- [ ] Crear `tests/test-subfase-4.2.php` + logs `subfase-4.2-cli.log` / `-http.log` + reporte `docs/testing/subfase-4.2-catalogo.md`.
- [ ] Validar criterios de `spec.md`, actualizar roadmap y **HALT** antes de 4.3.

## Subfase 4.3 · Gestión de Creaciones & Subida Multipart

- [ ] Cablear `formulario.php` (creación/edición, subida multipart real, ajuste de stock, toggle encargo, baja lógica).
- [ ] Crear `tests/test-subfase-4.3.php` + logs + reporte `docs/testing/subfase-4.3-creaciones.md`.
- [ ] Validar criterios de `spec.md`, actualizar roadmap y **HALT** antes de 4.4.

## Subfase 4.4 · Checkout Público, Pedidos Atómicos & WhatsApp

- [ ] Cablear modal de compra rápida, reserva atómica de stock, cálculo de precio en servidor y enlaces WhatsApp.
- [ ] Crear `tests/test-subfase-4.4.php` + logs + reporte `docs/testing/subfase-4.4-pedidos.md`.
- [ ] Validar criterios de `spec.md`, actualizar roadmap y **HALT** antes de 4.5.

## Subfase 4.5 · Directorio de Creadores & Roles RBAC

- [ ] Cablear panel `usuarios.php` (roles reactivos con salvaguarda ID #1, reseteo de claves, reactivación).
- [ ] Crear `tests/test-subfase-4.5.php` + logs + reporte `docs/testing/subfase-4.5-usuarios.md`.
- [ ] Validar criterios de `spec.md`, actualizar roadmap y **HALT**.

## Regresión por fase (obligatorio)

- [ ] Tras cada subfase 4.X: ejecutar `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` y, al cerrar la Fase 4, la regresión acumulada completa.