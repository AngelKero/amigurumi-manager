# Gobernanza · Acción 2: Blindaje del Ciclo de Token

**Estado:** en curso
**Fuente:** `reporte-auditoria-gobernanza.md` → hallazgos **H-002**, **H-003**, **H-004**

## Qué hace

Cierra las 3 brechas 🔴 del ciclo de vida del Bearer token **antes** de que la feature 004 materialice el token en el cliente:

1. **CSP estricto (H-004):** emite `Content-Security-Policy` en las páginas HTML y en las respuestas JSON de la API.
2. **Auditoría de sanitización (H-004):** inventario completo de los 36 usos de `innerHTML` en `src/js/`, refactor a `textContent`/DOM de los vectores que interpolan datos de usuario/servidor, y regla de gobernanza para contenido dinámico.
3. **Brute-force del login (H-003):** contador de intentos fallidos por `username` e `ip` con lockout y `HTTP 429`, mitigación de timing y backoff.
4. **Revocación real del logout (H-002):** denylist por `jti` para invalidar el token en servidor, y rotación de secreto HMAC con verificación multi-secreto (claim `ver`).

No implementa la funcionalidad de 004 (login/logout XHR, navbar reactivo); solo fija sus prerrequisitos de seguridad y registra las decisiones en los artefactos de 004.

## Por qué

La auditoría (14/09/2026) confirmó: `logout` es un no-op (token válido 24h), `login.php` no tiene rate-limit/lockout (matriz RBAC declara "Ninguno"), no existe CSP y el plan de 004 "mitiga" el XSS del `localStorage` por decreto mientras hay 36 interpolaciones `innerHTML` sin auditoría.

## Criterios de aceptación

- [ ] `views/layouts/main.php` emite CSP (`script-src 'self'` + CDN, `connect-src 'self'`, `frame-ancestors 'none'`).
- [ ] `Response::json` añade CSP conservadora (`default-src 'none'`) a las respuestas JSON.
- [ ] `docs/security/auditoria-sanitizacion-js.md` inventaría los 36 `innerHTML` con veredicto.
- [ ] Los vectores de datos (checkout `${name}`, orders `${tel}`, celdas rol/pago, templates de fila) usan `textContent`/DOM, no interpolación en `innerHTML`.
- [ ] Existe bloqueo por fuerza bruta: ≥5 fallos en 15 min por `username` (o ≥20 por `ip`) → `HTTP 429`.
- [ ] `api/auth/logout.php` revoca el token en servidor; `validateToken()` rechaza `jti` revocados.
- [ ] Rotación de secreto: token firmado con secreto anterior sigue verificándose (claim `ver`).
- [ ] ADR-016 nuevo + enmienda ADR-002; matriz RBAC (`security.md`) actualizada.
- [ ] Suites 3.1 y 3.6.5 re-ejecutadas en verde + `tests/test-gobernanza-accion-2.php` al 100%.
- [ ] Reporte ejecutivo `docs/testing/gobernanza-accion-2-token-seguridad.md` con "Fallos Detectados & Correcciones Quirúrgicas".

## Fuera de alcance

- Funcionalidad de 004 (módulo `auth.js` reactivo, navbar, redirecciones).
- Acciones 3–5 de la auditoría.
- Renumeración de invariantes R-01…R-09 (Acción 4).
- Corrección de las 18/24 vistas SSR sin `htmlspecialchars` (se documenta como deuda, se trata en la re-sincronización documental).