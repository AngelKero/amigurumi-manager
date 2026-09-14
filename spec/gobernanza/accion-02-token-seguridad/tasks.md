# Gobernanza · Acción 2: Blindaje del Ciclo de Token — Tareas

> Actualizado: 14/09/2026 · M1–M4 implementados; M5 en curso; M6 pendiente (Gate + HALT).

## M1 · CSP
- [x] Emitir CSP en `views/layouts/main.php` (antes del DOCTYPE).
- [x] Añadir `Content-Security-Policy: default-src 'none'` en `Response::json`.

## M2 · Sanitización
- [x] Refactor `checkout.js` (${name} ×2) → `setIconText`/DOM (`dom-safe.js`).
- [x] Refactor `orders.js` (${tel}, contacto, plantilla card, `getBadgeConfig` default) → DOM/`escapeHtml`.
- [x] Refactor `users.js` (fila tabla, onclick inline → delegación) → `escapeHtml` + eventos.
- [x] Revisar `detail.js`, `catalog.js`, `creaciones.js`, `margin-calculator.js`: solo constantes puras y numéricos exentos.
- [x] Redactar `docs/security/auditoria-sanitizacion-js.md` (36 → 0 vectores sin escalar).
- [x] Regla en `.agents/rules/innerhtml-dom-safety.md` + mención en `AGENTS.md §4`.

## M3 · Brute-force
- [x] Tabla `login_intentos` en `seed.sql` (con índice username/IP).
- [x] `app/Repositories/LoginGuardRepository.php`.
- [x] Lockout + backoff + 429 en `AuthService` y mapeo en `login.php`.
- [x] `Request::clientIp()` + claves `auth.*` en `config.php`.
- [x] Invariante #10 en `AGENTS.md §5`.

## M4 · Revocación y rotación
- [x] Tabla `tokens_revocados` en `seed.sql` (pk jti + índice expira).
- [x] `app/Repositories/TokenRevocadoRepository.php`.
- [x] `revokeToken` + chequeo en `validateToken`; `logout.php` revoca Bearer.
- [x] Claim `ver` + verificación multi-secreto en `TokenManager`.

## M5 · Docs
- [x] ADR-016 nuevo + enmienda ADR-002.
- [x] `security.md`: matriz login/logout + sección ciclo de vida.
- [x] `spec/features/004`: registrar decisiones (localStorage+CSP; revocación server-side; 429).
- [x] `roadmap.md`: entrada Gobernanza · Acción 2.

## M6 · Gate
- [x] Suite `tests/test-gobernanza-accion-2.php` al 100% (96/96).
- [x] `php -l` en PHP tocados (91 archivos, 0 errores).
- [x] Regresión 3.1 (93/93) y 3.6.5 (141/141) en verde (servidor local).
- [x] Logs en `logs/` + reporte `docs/testing/gobernanza-accion-2-token-seguridad.md`.
- [x] HALT: aguardar aprobación explícita del usuario.