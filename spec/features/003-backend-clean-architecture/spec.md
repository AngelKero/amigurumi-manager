# 003 · Backend Clean Architecture, API REST & Blindaje

**Estado:** implementado ✅

## Qué hace

Construye el núcleo del backend de **Crochet Manager** bajo Clean Architecture y principios SOLID dentro del directorio protegido `app/`. Proporciona un autocargador PSR-4 nativo sin dependencias de Composer, conexión Singleton PDO SQLite, captura global de errores con salida estandarizada JSON 500, capas desacopladas de repositorios y servicios, middleware de autenticación (HMAC-SHA256 Bearer tokens) y autorización RBAC, 25 controladores REST delgados en `api/`, y una suite de pruebas automatizadas con 1,307 aserciones aprobadas al 100%.

## Por qué

Garantiza que la lógica de negocio, la persistencia de datos y la seguridad operen de forma robusta, escalable y desacoplada del protocolo HTTP y de la capa de presentación. Previene vulnerabilidades críticas (OWASP Top 10: IDOR, SQLi, XSS, fugas de credenciales, concurrencia de stock) y asegura que la aplicación opere con latencias inferiores a 1 ms en SQLite.

## Criterios de aceptación

- [x] Autocargador PSR-4 nativo sin Composer (`app/autoload.php`) mapeando `App\` a `app/`.
- [x] Captura global de errores (`App\Core\ErrorHandler`) con purga de buffers `ob_end_clean()` y cero fugas de trazas HTML.
- [x] 100% de sentencias SQL encapsuladas en `app/Repositories/` utilizando consultas preparadas parametrizadas (`bindValue`).
- [x] Emisor estandarizado de respuestas JSON (`App\Core\Response`) con gestión de CORS preflight `OPTIONS` (HTTP 204).
- [x] Autenticación stateless con Bearer Tokens HMAC-SHA256 (24h TTL) validados en tiempo constante con `hash_equals()`.
- [x] Hashing seguro de contraseñas con Bcrypt (cost factor 10) y mitigación de timing attacks con dummy hash.
- [x] 25 controladores REST delgados en `api/` divididos en `auth/`, `creaciones/`, `pedidos/`, `usuarios/`, todos con $\le 60$ líneas.
- [x] Transacciones atómicas de reserva y restitución de existencias (`BEGIN IMMEDIATE TRANSACTION`) con idempotencia estricta en cancelaciones (ADR-009).
- [x] Aislamiento horizontal de recursos IDOR: los artesanos solo pueden mutar sus creaciones y pedidos propios (ADR-007).
- [x] Salvaguarda inmutable para el administrador raíz (ID #1: `@admin` no degradable ni eliminable, ADR-010).
- [x] Validación binaria estricta de imágenes (`finfo_file`, JPEG/PNG/WebP $\le 5\text{MB}$) y preservación estricta de fotos en bajas lógicas (ADR-008).
- [x] 1,307 de 1,307 aserciones aprobadas en verde en las suites de prueba nativas CLI (`tests/test-subfase-3.X.php`).

## Fuera de alcance

- Cableado del frontend asíncrono con JavaScript (reservado para la Feature 004 en adelante).
