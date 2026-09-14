# ADR-016: Revocación de Tokens (denylist por `jti`) y Blindaje Anti-Fuerza-Bruta del Login

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-14

## Contexto
La auditoría de gobernanza detectó dos hallazgos críticos (H-002 y H-003):

1. **H-002 · `POST /api/auth/logout.php` era un no-op:** al mantener tokens stateless puros,
   un token robado seguía siendo válido hasta su `exp` (24 h) aunque el usuario cerrara sesión.
2. **H-003 · Login expuesto a fuerza bruta:** no existía limitación de intentos por cuenta ni
   por IP, ni backoff temporal; `login_intentos` no se registraban ni consultaban.

Se requiere blindar el ciclo de vida del token **antes** de codificar la feature 004
(auth + sesión de cliente), dado que su aceptación incluye SSE con Heartbeat (`/api/auth/heartbeat.php`).

## Decisión

### Revocación por denylist de `jti` (H-002)
- Nueva tabla `tokens_revocados (jti PK, sub, expira_en, revocado_en)`.
- `POST /api/auth/logout.php` extrae el Bearer (`Request::bearerToken()`), verifica el token y
  revoca su `jti` **antes** de responder 200. Operación best-effort e idempotente (`INSERT OR IGNORE`).
- `AuthService::validateToken()` rechaza cualquier token cuyo `jti` figure en la denylist no expirada.
- Purga oportunista de la denylist (1% de validaciones) + `TokenRevocadoRepository::pruneExpirados()`.
- Se conserva el **estado stateless** (la denylist reconoce, no crea, sesiones).

### Rotación de secretos HMAC (complemento a ADR-002)
- `TokenManager::generate()` añade el claim `ver` (`auth.secret_version`).
- `TokenManager::verify()` valida la firma con `auth.token_secret` y, si esta no coincide,
  con `auth.token_secret_anterior` (rotación caliente sin invalidar tokens previos).

### Blindaje anti-fuerza-bruta (H-003)
- Nueva tabla `login_intentos (id, username, ip, intento_ok, creado_en)` + índices por username e IP.
- `AuthService::authenticate()`:
  1. Verifica bloqueo (username **y** IP) — `HTTP 429 Too Many Requests`.
  2. Ejecuta `password_verify` (con hash dummy ante usuarios inexistentes para mitigar timing).
  3. Ante fallo: registra intento, aplica `usleep(backoff_ms)` y lanza `401`.
  4. Ante éxito: limpia los fallos del usuario y registra acierto.
- Umbrales configurables en `config.php` (`auth.*`): `max_intentos_username=5`,
  `max_intentos_ip=20`, `ventana_segundos=900`, `backoff_ms=400`.
- `Request::clientIp()` confía solo en `REMOTE_ADDR` para impedir suplantación por `X-Forwarded-For`.

## Alternativas Consideradas
- **Cookies httpOnly en lugar de denylist:** contradice ADR-002/ADR-004 (API stateless para
  clientes móviles/CLI) y añade CSRF. Rechazada en la Decisión de gobernanza (14/09/2026).
- **Whitelist de tokens activos:** crece sin límite y exige tocar el payload en cada renovación;
  la denylist por `jti` es acotada (purga por `expira_en`).
- **Sleep fijo extenso:** degrada UX legítima; se optó por `backoff_ms` corto (400 ms) y bloqueo
  real al superar umbrales.

## Consecuencias
- `logout` revoca de forma efectiva; los tokens robados dejan de servir tras cerrar sesión.
- Login protegido contra fuerza bruta distribuida y dirigida (cuenta e IP) con respuesta estándar 429.
- Registros de gobernanza visibles en `login_intentos` (auditoría forense) y purgables por ventana.
- El almacenamiento de tokens en cliente sigue siendo `localStorage` (decisión de gobernanza,
  14/09/2026) **condicionado** al CSP estricto ya emitido y a la auditoría de sanitización JS (H-004).

---

[← Anterior (ADR-015)](./ADR-015-creation-restoration-lifecycle.md) • [Índice de ADRs](./README.md)