# Gobernanza · Acción 2: Blindaje del Ciclo de Token — Plan

## Decisiones (aprobadas por el usuario 14/09/2026)

1. **Almacenamiento del token en 004:** `localStorage` + CSP estricto + auditoría de sanitización como prerrequisito (mantiene ADR-002 y el plan de 004).
2. **Revocación del logout:** denylist real por `jti` (tabla nueva, `logout.php` deja de ser no-op).
3. **Alcance de innerHTML:** auditar los 36 y refactorizar los vectores de datos de usuario/servidor; los badges constantes y contadores numéricos se documentan como seguros y se conservan.

## Milestones

### M1 · CSP estricto (H-004)
- `views/layouts/main.php`: `header('Content-Security-Policy: …')` antes del DOCTYPE. Sin scripts inline en el repo, así que `script-src 'self'` + CDN jsdelivr alcanzan; `style-src 'unsafe-inline'` por los atributos `style=` existentes; `frame-ancestors 'none'`.
- `app/Core/Response.php` → `json()`: añadir `Content-Security-Policy: default-src 'none'`.

### M2 · Auditoría de sanitización + refactor (H-004)
- Inventario `docs/security/auditoria-sanitizacion-js.md` (36 usos, tabla con veredicto).
- Refactor a `textContent`/DOM: `checkout.js` (name ×2), `orders.js` (tel, badge pago, nueva columna), `users.js` (badge rol, template fila), `detail.js` (stock/estado dinámicos no numéricos si los hay), `creaciones.js`/`catalog.js` (contadores: se conservan si numéricos).
- Regla en `.agents/rules/ui-ux-design-system.md` §nueva y en `AGENTS.md §4`.

### M3 · Brute-force del login (H-003)
- `database/seed.sql`: tabla `login_intentos(id, username, ip, intento_ok, creado_en)` + índices + `DROP TABLE IF EXISTS`.
- `app/Repositories/LoginGuardRepository.php` (nuevo): `registerIntent`, `countFailures(username)`, `countFailuresIp(ip)`, `clearFailures(username)`, `prune`.
- `app/Services/AuthService.php`: pre-chequeo de bloqueo (429), `registerIntent(false)` + `usleep(backoff)` en fallo, `registerIntent(true)` + `clearFailures` en éxito.
- `app/Core/Request.php`: helper `clientIp()`.
- `app/config.php`: `auth.max_intentos_username=5`, `auth.max_intentos_ip=20`, `auth.ventana_segundos=900`, `auth.backoff_ms=400`.
- `api/auth/login.php`: mapear `429`.
- `AGENTS.md §5`: invariante #10.

### M4 · Denylist jti + revocación + rotación (H-002)
- `database/seed.sql`: tabla `tokens_revocados(jti PK, sub, expira_en, revocado_en)`.
- `app/Repositories/TokenRevocadoRepository.php` (nuevo): `revoke`, `isRevoked`, `pruneExpirados`.
- `app/Services/AuthService.php`: `revokeToken(token)`; `validateToken` rechaza jti revocado (con purge oportunista).
- `api/auth/logout.php`: revocación best-effort del `Authorization: Bearer`.
- `app/Core/TokenManager.php`: claim `ver` + verificación con secreto actual y anterior (`auth.token_secret`, `auth.token_secret_anterior`, `auth.secret_version`).

### M5 · Docs, ADRs y especificación 004
- `docs/architecture/decisiones/ADR-016-… (nuevo)`, enmienda `ADR-002`.
- `docs/architecture/security.md`: sección "Ciclo de vida del Bearer token" + matriz (login → LoginGuard · logout → Denylist jti).
- `spec/features/004-auth-sesion-cliente/plan.md` + `spec.md`: registrar decisión localStorage+CSP y revocación server-side como criterio de aceptación.
- `spec/constitution/roadmap.md`: entrada "Gobernanza · Acción 2".

### M6 · Gate 3-tier
- Nueva suite `tests/test-gobernanza-accion-2.php` (Secciones: CSP, sanitización, brute-force directo + HTTP 429, revocación logout, rotación de secreto).
- `php -l` en PHP tocados. Servidor `php -S localhost:8000`. Re-ejecutar 3.1 y 3.6.5 (regresión) + suite nueva. Logs en `logs/`.
- Reporte `docs/testing/gobernanza-accion-2-token-seguridad.md` con "Fallos Detectados & Correcciones Quirúrgicas".
- **HALT.**

## Riesgos

- **Bloqueo de cuentas reales:** el test de fuerza bruta usa un usuario de prueba dedicado (nunca `admin`), mantiene `Config::set` con umbrales bajos solo dentro del test y limpia fallos tras cada bloqueo.
- **CSP vs. vistas:** `style 'unsafe-inline'` es aceptable; ningún script inline presente → `script-src` estricto viable.
- **Esquema nuevo:** `setup.php` regenera la BD (las suites ya lo hacen); ninguna tabla existente se altera.
- **Regresión:** re-ejecución de 3.1/3.6.5 obligatoria; si el nuevo `AuthService` cambia el flujo de login, adaptar con "cambio de contrato" documentado.