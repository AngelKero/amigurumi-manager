# ADR-002: Autenticación Stateless con Bearer Tokens HMAC-SHA256 (24h TTL)

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-11

## Contexto
Se requiere proteger endpoints administrativos sin depender de cookies de sesión tradicionales (`PHPSESSID`), permitiendo que clientes móviles o navegadores web modernos consuman la API REST de forma desacoplada y sin estado.

## Decisión
Implementar un sistema de tokens Bearer firmado mediante HMAC-SHA256 en `App\Core\TokenManager`:
- Payload con claims estándar: `sub` (ID usuario), `username`, `rol`, `iat` (emisión), `exp` (expiración a 24 horas = 86,400s), `jti` (identificador criptográfico único).
- Firma validada en tiempo constante con `hash_equals()` para mitigar ataques de canal lateral (timing attacks).
- Cabecera estándar HTTP `Authorization: Bearer <token>` extraída por `AuthGuard`.
- Reenvío automático en Apache vía `.htaccess` para entornos FastCGI.

## Alternativas Consideradas
- **Sesiones PHP (`session_start()` + Cookies):** Acopla la API al navegador, dificulta pruebas CLI y pruebas en herramientas como curl o Postman.
- **JWT con librerías externas (Firebase/JWT):** Requiere Composer; la solución nativa HMAC-SHA256 en PHP cubre exactamente la misma función con 0 dependencias.

## Consecuencias
- Autenticación ligera, portable y comprobable mediante CLI y curl.
- No se almacena estado de sesión en disco ni memoria del servidor.
- La invalidación inmediata se refuerza validando que la cuenta esté activa (`activo = 1`) en cada consulta protegida.

### Enmienda 2026-09-14 (gobernanza · Acción 2 · H-002/H-003)

- El claim `ver` (versión de secreto) se incorpora al payload; `TokenManager::verify()`
  acepta firmas válidas con `auth.token_secret` actual o `auth.token_secret_anterior`, habilitando
  **rotación caliente de claves** sin invalidar tokens emitidos previos.
- La revocación de tokens ya no depende solo de `activo = 1` (no cubre logout explícito):
  `tokens_revocados` (denylist por `jti`) permite invalidación inmediata en cerrar sesión.
  Ver **ADR-016**. El comportamiento global sigue siendo stateless (no hay sesión en servidor);
  la denylist existe solo para tokens emitidos recientemente y se purga al expirar.

---

[← Anterior (ADR-001)](./ADR-001-clean-architecture-php-sqlite.md) • [Índice de ADRs](./README.md) • [Siguiente (ADR-003) →](./ADR-003-zero-html-error-leaks.md)
