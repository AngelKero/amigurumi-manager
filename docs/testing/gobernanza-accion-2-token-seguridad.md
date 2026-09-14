# Gobernanza · Acción 2 · Blindaje del Ciclo de Token (CSP · Sanitización · Brute-force · Revocación)

> **Auditoría:** 14/09/2026 · **Estado:** ✅ Cerrada · **Gate:** 3-tier completo en verde.
> **Fuentes:** hallazgos H-002 (logout no-op), H-003 (login sin anti-fuerza-bruta), H-004 (CSP/XSS/localStorage) — `docs/architecture/auditoria` (Acción 2).

---

## 1. Resumen Ejecutivo

Se blindó el ciclo de vida completo del Bearer token **antes** de codificar la feature 004
(Auth + Sesión de Cliente), que ahora consume estas garantías como prerrequisito:

- **H-004 · CSP estricto:** cabecera emitida antes del `<!DOCTYPE` en `views/layouts/main.php`
  (`script-src 'self'` + CDN, `style-src 'self' 'unsafe-inline'` + CDNs, `connect-src 'self'`,
  `object-src 'none'`, `base-uri 'self'`, `frame-ancestors 'none'`, `form-action 'self'`) y
  `default-src 'none'` en todas las respuestas JSON API.
- **H-004 · Sanitización JS:** auditoría integral de `innerHTML` (36 usos inventariados).
  **6 vectores de datos** remediados (3 → `textContent`/DOM vía `setIconText`, 2 plantillas →
  `escapeHtml`, 1 default de badge → `escapeHtml`); `onclick` inline con datos eliminado por
  delegación. Resultado: **0 vectores de datos sin escalar**. Inventario en
  `docs/security/auditoria-sanitizacion-js.md`; regla P1 en `.agents/rules/innerhtml-dom-safety.md`.
- **H-003 · Anti-fuerza-bruta:** tabla `login_intentos`, `LoginGuardRepository`,
  lockout 5/cuenta + 20/IP en ventana de 15 min → `HTTP 429` con backoff de 400 ms por fallo,
  restablecimiento del contador en éxito, `Request::clientIp()` solo `REMOTE_ADDR`.
  Invariante #10 en `AGENTS.md §5`.
- **H-002 · Revocación server-side:** `tokens_revocados` denylist por `jti`; logout revoca el
  Bearer (`revocado_en_servidor = true`) y los tokens revocados se rechazan en validación.
  Rotación caliente de secretos: claim `ver` + verificación con `auth.token_secret_anterior`.
  ADR-016 nuevo; enmienda a ADR-002.

## 2. Evidencia de Verificación (Gate 3-tier)

### Tier 1 · Suites CLI al 100%

| Suite | Aserciones | Resultado |
|-------|-----------|-----------|
| `tests/test-gobernanza-accion-2.php` (nueva) | **96/96** | ✅ 100% |
| Regresión `tests/test-subfase-3.1.php` | **93/93** | ✅ 100% |
| Regresión acumulada `tests/test-subfase-3.6.5.php` | **141/141** | ✅ 100% |

Grep de seguridad global: `find app api views tests *.php -name "*.php" -exec php -l {} +`
→ **91 archivos, 0 errores de sintaxis** · `find src/js -name "*.js" -exec node --check {} +`
→ **0 errores**.

### Tier 2 · Logs crudos

- `logs/gobernanza-accion-2-cli.log`
- `logs/gobernanza-accion-2-http.log` (7 transacciones curl en vivo: CSP página y API,
  fuerza bruta → 401→429 con envelope `{"codigo":429}`, admin intacto, y E2E logout:
  login→200, me→200, logout→200 con `revocado_en_servidor:true`, me→401 por `jti` revocado).
- `logs/subfase-3.1-cli.log`, `logs/subfase-3.6.5-cli.log` (regresión).

### Tier 3 · Documentos

- → este reporte (`docs/testing/gobernanza-accion-2-token-seguridad.md`)
- `docs/architecture/decisiones/ADR-016-token-revocacion-y-brute-force-guard.md` (nuevo)
- `docs/architecture/decisiones/ADR-002-stateless-hmac-bearer-tokens.md` (enmienda)
- `docs/architecture/security.md` (matriz RBAC login/logout + ciclo de vida del token)
- `docs/security/auditoria-sanitizacion-js.md` (inventario y correcciones)
- `.agents/rules/innerhtml-dom-safety.md` (regla P1) + `AGENTS.md §4` y `§5 #10`
- `spec/features/004-auth-sesion-cliente/` (spec.md + plan.md con decisiones de gobernanza)
- `spec/gobernanza/accion-02-token-seguridad/` (spec.md, plan.md, tasks.md) → M1–M6 ✅

## 3. Fallos Detectados & Correcciones Quirúrgicas

1. **Suite nueva → 1 aserción fallida (Red):** el test buscaba el literal `429` en
   `api/auth/login.php`, pero el mapeo del bloqueo es dinámico
   (`$e->getCode() > 0 ? $e->getCode() : 401`). **Corrección:** la aserción ahora verifica
   `getCode()` (mapeo dinámico del código HTTP de la excepción). La verificación funcional real
   del 429 ya la cubría el test HTTP (5 fallos → HTTP 429 con envelope correcto).
2. **`use PDO` / `use RuntimeException` sobrerresiduales** en el suite (warnings de sintaxis):
   eliminados por no aportar resolución de nombre en el namespace global.
3. **(Vigilancia)** `debug_backtrace` de `Request`: sin cambios; `clientIp()` usa única fuente
   fiable `REMOTE_ADDR` por decisión de diseño anti-suplantación.

## 4. Lecciones para la Feature 004

- `login()` del modal debe mapear **HTTP 429** con mensaje diferenciado (no solo 401).
- `logout()` debe considerarse completo solo tras el **200**; el cliente descarta `localStorage`
  y, ante caída de red, aplica descarte local (best-effort).
- `checkSession()` ya cubre tokens revocados por `jti` (401 en `/api/auth/me.php`).
- La validez de almacenar el token en `localStorage` queda **condicionada** al CSP estricto y a la
  higiene de `innerHTML` ahora aplicadas y auditadas.

## 5. Estado

Acción 2 cerrada en código, esquema, documentación y pruebas (gate 3-tier verde).
**HALT aplicado conforme a `AGENTS.md §3`:** se aguarda aprobación explícita del humano antes de
avanzar a la siguiente subfase/acción.