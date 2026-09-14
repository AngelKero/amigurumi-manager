# Seguridad, Autenticación & Control de Acceso (RBAC)

[← Volver al Índice de Arquitectura](./README.md)

Este documento detalla el modelo de seguridad integral del sistema, las capas de blindaje contra vulnerabilidades comunes (OWASP Top Ten) y las políticas de autorización de **Crochet Manager**.

---

## 1. Autenticación Stateless con Tokens HMAC-SHA256

El sistema no utiliza cookies de sesión PHP (`PHPSESSID`) para la API REST. Toda comunicación con endpoints protegidos se autentica mediante Bearer Tokens firmados criptográficamente.

### Estructura del Token
El token consta de dos segmentos codificados en Base64Url unidos por un punto (`.`):
```
<Header_and_Payload_Base64Url>.<Firma_HMAC_SHA256>
```

#### Claims del Payload:
- `sub`: ID del usuario (`int`).
- `username`: Nombre de usuario (`string`).
- `rol`: Rol asignado (`admin`, `artesano`, `asistente`).
- `iat`: Timestamp Unix de emisión (`int`).
- `exp`: Timestamp Unix de expiración (`iat + 86400`).
- `jti`: Identificador único de token de 32 caracteres hexadecimales.
- `ver`: Versión de secreto HMAC usada en la firma (rotación de claves).

### Verificación y Resistencia a Ataques de Temporización
La verificación de la firma se ejecuta mediante `hash_equals()`, garantizando comparación en tiempo constante para prevenir fugas por canales laterales (timing attacks). Para soportar **rotación de claves**, la firma se recalcula con `auth.token_secret` (actual) y, si no coincide, con `auth.token_secret_anterior` (secreto previo configurado durante el cambio).

### Ciclo de Vida del Token (con revocación, ADR-016)
Diagrama del ciclo completo tras el blindaje de la Acción 2:

```
┌────────────────────────────────────────────────────────────────────┐
│ 1. Emisión      POST /api/auth/login.php  → payload + firma HMAC    │
│ 2. Almacenado   localStorage (cliente) + CSP estricto (H-004)       │
│ 3. Uso          Authorization: Bearer <token> en cada petición      │
│ 4. Bloqueo      login_intentos: 429 tras 5 fallos/cuenta o 20/IP    │
│ 5. Heartbeat    GET /api/auth/me.php (+ SSE /heartbeat en 004)     │
│ 6. Renovación   N/A (24 h TTL — cliente vuelve a autenticar)        │
│ 7. Cierre       POST /api/auth/logout.php → revoca jti en           │
│                tokens_revocados (denylist) → 200                    │
│ 8. Purga        pruneExpirados() borra jti vencidos de la denylist │
└────────────────────────────────────────────────────────────────────┘
```

Un token cuya `jti` figure en `tokens_revocados` (no expirado) es rechazado por
`AuthService::validateToken()` antes de resolver el usuario.

---

## 2. Matriz de Control de Acceso Basado en Roles (RBAC)

| Módulo / Endpoint | Público | `asistente` | `artesano` | `admin` | Mecanismo de Control |
| :--- | :---: | :---: | :---: | :---: | :--- |
| `POST /api/auth/login.php` | ✅ | ✅ | ✅ | ✅ | `LoginGuard` (5/cuenta · 20/IP · ventana 15 min → 429 + backoff) |
| `POST /api/auth/logout.php` | ✅ | ✅ | ✅ | ✅ | Revoca Bearer: denylist `tokens_revocados` por `jti` (ADR-016) |
| `GET /api/auth/me.php` | ❌ | ✅ | ✅ | ✅ | `AuthGuard` |
| `POST /api/auth/cambiar-password.php` | ❌ | ✅ | ✅ | ✅ | `AuthGuard` |
| `GET /api/creaciones/index.php` | ✅ | ✅ | ✅ | ✅ | Ninguno (Filtra `activo = 1`) |
| `GET /api/creaciones/artesanos.php` | ✅ | ✅ | ✅ | ✅ | Ninguno (Público) |
| `GET /api/creaciones/detalle.php` | ✅ | ✅ | ✅ | ✅ | Ninguno (Filtra `activo = 1`) |
| `POST /api/creaciones/crear.php` | ❌ | ❌ | ✅ | ✅ | `RoleGuard::artisanOrAdmin()` |
| `POST /api/creaciones/actualizar.php` | ❌ | ❌ | ✅ (IDOR) | ✅ | `RoleGuard` + Validación de Autoría |
| `POST /api/creaciones/eliminar.php` | ❌ | ❌ | ✅ (IDOR) | ✅ | `RoleGuard` + Validación de Autoría |
| `POST /api/creaciones/restaurar.php` | ❌ | ❌ | ✅ (IDOR) | ✅ | `RoleGuard` + Validación de Autoría |
| `POST /api/creaciones/ajustar-stock.php`| ❌ | ❌ | ✅ (IDOR) | ✅ | `RoleGuard` + Validación de Autoría |
| `POST /api/creaciones/toggle-encargo.php`| ❌ | ❌ | ✅ (IDOR) | ✅ | `RoleGuard` + Validación de Autoría |
| `GET /api/pedidos/index.php` | ❌ | ❌ | ✅ (Propios) | ✅ (Todos) | `RoleGuard` + Aislamiento SQL |
| `POST /api/pedidos/solicitar.php` | ✅ | ✅ | ✅ | ✅ | Ninguno (Validación de Stock) |
| `POST /api/pedidos/crear.php` | ❌ | ❌ | ✅ | ✅ | `RoleGuard::artisanOrAdmin()` |
| `POST /api/pedidos/cambiar-estado.php` | ❌ | ❌ | ✅ (Propios) | ✅ | `RoleGuard` + Aislamiento |
| `POST /api/pedidos/cancelar.php` | ❌ | ❌ | ✅ (Propios) | ✅ | `RoleGuard` + Aislamiento |
| `GET /api/usuarios/index.php` | ❌ | ❌ | ❌ | ✅ | `RoleGuard::adminOnly()` |
| `POST /api/usuarios/crear.php` | ❌ | ❌ | ❌ | ✅ | `RoleGuard::adminOnly()` |
| `POST /api/usuarios/cambiar-rol.php` | ❌ | ❌ | ❌ | ✅ (No ID #1) | `RoleGuard::adminOnly()` + Lockout |
| `POST /api/usuarios/actualizar.php` | ❌ | ❌ | ❌ | ✅ | `RoleGuard::adminOnly()` |
| `POST /api/usuarios/restablecer-password.php`| ❌ | ❌ | ❌ | ✅ | `RoleGuard::adminOnly()` |
| `POST /api/usuarios/eliminar.php` | ❌ | ❌ | ❌ | ✅ (No ID #1) | `RoleGuard::adminOnly()` + Lockout |
| `POST /api/usuarios/reactivar.php` | ❌ | ❌ | ❌ | ✅ | `RoleGuard::adminOnly()` |

---

## 3. Protección Contra Vulnerabilidades Comunes

### 3.1 Prevención de Inyección SQL
El 100% de las sentencias SQL en `app/Repositories/` se ejecutan mediante **Prepared Statements parametrizados** con PDO. No existe concatenación de variables en consultas SQL.

### 3.2 Protección contra IDOR (Insecure Direct Object References)
Cualquier intento de un artesano de modificar o eliminar una creación o pedido que no le pertenece es interceptado por `CreacionService::ensureArtisanOwnership()` o `PedidoService::ensureOrderOwnership()`, abortando con `HTTP 403 Forbidden`.

### 3.3 Salvaguarda del Administrador Titular (ID #1)
El usuario ID #1 (`@admin`) tiene salvaguardas inviolables en la capa de persistencia (`UsuarioRepository`) y en la capa de negocio (`UsuarioService`):
- No puede ser degradado de rol.
- No puede ser eliminado física ni lógicamente.

### 3.4 Cero Fugas de Información Sensible
- Los campos `password_hash` se excluyen de todas las respuestas de API.
- `ErrorHandler` captura errores fatales y emite JSON 500 sin exponer trazas de stack trace al cliente en producción.

### 3.5 Blindaje Apache (`.htaccess`)
Bloquea acceso directo HTTP a extensiones sensibles (`.sqlite`, `.sql`, `.md`, `.log`, `.env`) y directorios internos (`app/`, `database/`, `tests/`, `logs/`, `spec/`) con código `HTTP 403 Forbidden`.
