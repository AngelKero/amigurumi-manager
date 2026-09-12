# Contratos de Clases & Capas del Backend

[← Volver al Índice de Arquitectura](./README.md)

Este documento detalla las interfaces, métodos y responsabilidades de las clases que conforman la arquitectura en `app/`.

---

## 1. Capa de Infraestructura (`app/Core/`)

### `App\Core\Database` (Singleton PDO SQLite)
- `getInstance(): PDO`: Obtiene la conexión PDO única con `PRAGMA foreign_keys = ON;`, `PRAGMA busy_timeout = 5000;` y modo de excepciones activo.
- `setInstance(?PDO $pdo): void`: Permite inyectar una conexión mock o en memoria para pruebas automatizadas.
- `beginTransaction()`, `commit()`, `rollBack()`: Envoltorios transaccionales.

### `App\Core\TokenManager` (Gestor Stateless Bearer HMAC-SHA256)
- `generateToken(array $user): string`: Genera token firmado con claims (`sub`, `username`, `rol`, `iat`, `exp`, `jti`).
- `verifyToken(string $token): ?array`: Valida firma mediante `hash_equals()` y comprueba expiración (24h TTL). Retorna los claims o `null`.

### `App\Core\ErrorHandler` (Cero Fugas HTML)
- `register(): void`: Registra manejadores de error, excepción y función de apagado (`register_shutdown_function`). Purga buffers con `ob_end_clean()` y emite JSON 500.

### `App\Core\Request` & `Response`
- `Request::get()`, `post()`, `json()`, `query()`, `file()`, `header()`, `bearerToken()`, `user()`, `setUser()`.
- `Response::success(string $msg, mixed $datos, int $status = 200, ?array $paginacion = null): void`.
- `Response::error(string $msg, int $status = 400, mixed $detalles = null): void`.
- `Response::handleCors(): void`: Maneja preflights HTTP `OPTIONS` con 204.

---

## 2. Capa de Persistencia (`app/Repositories/`)

### `App\Repositories\UsuarioRepository`
- `findByUsername(string $username, bool $onlyActive = true): ?array`
- `findById(int $id, bool $onlyActive = true): ?array`
- `findByIdSafe(int $id, bool $onlyActive = true): ?array` *(excluye `password_hash`)*
- `existsUsername(string $username, ?int $excludeId = null): bool`
- `create(string $username, string $passwordHash, string $rol = 'artesano'): int`
- `updateRole(int $id, string $nuevoRol): bool` *(salvaguarda ID #1)*
- `updateUsername(int $id, string $nuevoUsername): bool`
- `updatePassword(int $id, string $nuevoPasswordHash): bool`
- `delete(int $id): bool` *(baja lógica: `activo = 0`, salvaguarda ID #1)*
- `reactivate(int $id): bool` *(reactivación: `activo = 1`, `eliminado_en = NULL`)*
- `listAll(int $limit, int $offset, ?bool $onlyActive = true): array`
- `countAll(?bool $onlyActive = true): int`
- `listAllWithCreationsCount(int $limit, int $offset, ?bool $onlyActive = true): array`
- `countCreationsByUser(int $userId): int`

### `App\Repositories\CreacionRepository`
- `findById(int $id, bool $onlyActive = true): ?array`
- `listCatalog(array $filters, int $limit, int $offset): array`
- `countCatalog(array $filters): int`
- `listByArtisan(int $artesanoId, int $limit, int $offset, ?bool $onlyActive = true): array`
- `countByArtisan(int $artesanoId, ?bool $onlyActive = true): int`
- `findActiveArtisansWithCreations(): array` *(Punto F)*
- `create(array $data): int`
- `update(int $id, array $data): bool`
- `softDelete(int $id): bool`
- `restore(int $id): bool`
- `adjustStock(int $id, int $nuevoStock): bool`
- `toggleCommission(int $id, int $esSobreEncargo): bool`

### `App\Repositories\PedidoRepository`
- `findById(int $id): ?array`
- `listAll(array $filters, int $limit, int $offset, ?int $artesanoId = null): array`
- `countAll(array $filters, ?int $artesanoId = null): int`
- `createAtomic(array $pedidoData, bool $isCustomOrder): int`
- `updateStatus(int $id, string $estadoPedido, ?string $estadoPago = null): bool`
- `cancelOrderAtomic(int $id): array` *(restitución de inventario)*

---

## 3. Capa de Servicios de Negocio (`app/Services/`)

### `App\Services\AuthService`
- `authenticate(string $username, string $password): array`
- `validateToken(string $token): array`
- `changePassword(int $userId, string $currentPassword, string $newPassword): bool`

### `App\Services\UsuarioService`
- `listUsers(int $page, int $limit, ?bool $onlyActive = true): array`
- `getUserById(int $id): array`
- `createUser(string $username, string $password, string $rol): array`
- `updateRole(int $id, string $nuevoRol): array`
- `updateUsername(int $id, string $nuevoUsername): array`
- `resetPassword(int $id, ?string $nuevaPassword = null): array`
- `deleteUser(int $id, int $currentUserId): array`
- `reactivateUser(int $id): array`

### `App\Services\CreacionService`
- `getCatalog(array $filters, int $page, int $limit): array`
- `getActiveArtisans(): array`
- `getCreationById(int $id): array`
- `createCreation(array $data, ?array $file, array $currentUser): array`
- `updateCreation(int $id, array $data, ?array $file, array $currentUser): array`
- `deleteCreation(int $id, array $currentUser): array`
- `restoreCreation(int $id, array $currentUser): array`
- `adjustStock(int $id, int $newStock, array $currentUser): array`
- `toggleCommission(int $id, int $state, array $currentUser): array`
- `ensureArtisanOwnership(array $creacion, array $currentUser): void`

### `App\Services\PedidoService`
- `listOrders(array $filters, int $page, int $limit, array $currentUser): array`
- `requestPublicOrder(array $input): array`
- `createManualOrder(array $input, array $currentUser): array`
- `updateOrderStatus(int $id, string $orderStatus, ?string $paymentStatus, array $currentUser): array`
- `cancelOrder(int $id, array $currentUser): array`

---

## 4. Capa de Middleware (`app/Middleware/`)

- `AuthGuard::handle(?AuthService $authService = null): array`: Extrae el Bearer token de la cabecera HTTP. Si es inválido o no existe, emite `HTTP 401 Unauthorized` en JSON e interrumpe la ejecución (`exit`).
- `RoleGuard::adminOnly(?array $user = null): array`: Valida que el rol sea `'admin'`. Si no, emite `HTTP 403 Forbidden`.
- `RoleGuard::artisanOrAdmin(?array $user = null): array`: Valida que el rol sea `'admin'` o `'artesano'`. Si no, emite `HTTP 403 Forbidden`.
