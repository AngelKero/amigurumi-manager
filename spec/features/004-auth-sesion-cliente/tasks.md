# 004 · Autenticación, Token Bearer & Estado Reactivo del Navbar — Tareas

> ⚙️ **Gate de testing por subfase (Gobernanza · Acción 3 · H-008):** la Fase 4 opera bajo el 3-Tier Testing Gate de `AGENTS.md §3`. Cada subfase 4.X crea SU propia suite CLI, SU log CLI y HTTP, y SU reporte ejecutivo antes de avanzar. Cualquier divergencia CLI/HTTP se triaje conforme a `docs/testing/protocolo-divergencia-cli-http.md`.
>
> 🧭 **Feature realineada por el plan maestro de la Fase 4 (`spec/features/009-plan-maestro-fase-4/`):**
> `004` cubre **solo la subfase 4.1 (Auth & Sesión de Cliente)**. Las subfases 4.2–4.5 se despliegan
> y ejecutan en sus features hijas `005` (catálogo), `006` (creaciones), `007` (pedidos) y
> `008` (usuarios/RBAC); el `tasks.md` de `009` registra el avance de toda la fase.

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

## Regresión tras la subfase (obligatorio, delegada al maestro 009)

- [ ] Tras 4.1: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` (H-020) y
      regresión Fase 3 (`test-subfase-3.6.5.php`) + `php tests/cuenta-aserciones.php` (1,287),
      conforme al gate del plan maestro `009/tasks.md`.

## Subfases 4.2–4.5 (movidas a features hijas)

Las subfases 4.2 (Catálogo), 4.3 (Creaciones), 4.4 (Pedidos) y 4.5 (Usuarios/RBAC) se planifican
y ejecutan en sus features hijas `005`–`008` bajo el plan maestro `009`.