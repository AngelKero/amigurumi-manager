# Reporte de Pruebas: Subfase 3.3 — Gestión de Usuarios, Autoría de Creadores & Roles RBAC

- **Fecha de Ejecución:** 2026-09-12 13:46:00 CST
- **Responsable:** Antigravity Agent (Pair Programming con Ingeniero Titular)
- **Entorno:** PHP 8.3.29 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (macOS Darwin)
- **Archivos de Log Crudos:**
  - CLI: [`logs/subfase-3.3-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-cli.log)
  - HTTP: [`logs/subfase-3.3-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.3.php`](file:///Users/angelzaragoza/Desktop/proyecto-web/tests/test-subfase-3.3.php)
- **Resultado General:** **105 / 105 Aserciones Aprobadas (100% OK) en 847.15 ms** — ✅ **APTO PARA AVANZAR**

---

## 1. Matriz de Aserciones y Casos Evaluados

| # | Componente / Capa | Caso de Prueba Evaluado | Entrada / Condición | Comportamiento Esperado | Resultado | Estado |
| :-: | :--- | :--- | :--- | :--- | :---: | :---: |
| 1 | `UsuarioService` | Listado paginado de usuarios | `listUsers(1, 10)` | Retorna array con `usuarios` y sobre `paginacion` | Estructura válida | ✅ PASS |
| 2 | `UsuarioService` | Conteo de usuarios iniciales | Verificación de usuarios semilla | Contabiliza $\ge 3$ creadores iniciales | $\ge 3$ usuarios | ✅ PASS |
| 3 | `UsuarioService` | Conteo de creaciones por artesano | Consulta con `LEFT JOIN creaciones` | Clave `creaciones_asociadas` es un entero $\ge 0$ | Conteo exacto | ✅ PASS |
| 4 | `UsuarioService` | Confidencialidad de contraseñas | Verificación de claves del array | Cero exposición de `password_hash` en listado | Sin hashes expuestos | ✅ PASS |
| 5 | `UsuarioService` | Estructura de paginación | `paginacion: { total_items, pagina_actual, limite }` | Metadatos normalizados por `PaginationHelper` | Sobre estándar | ✅ PASS |
| 6 | `UsuarioService` | Búsqueda por ID existente | `getUserById(1)` | Retorna datos seguros del usuario admin | Usuario ID 1 | ✅ PASS |
| 7 | `UsuarioService` | Búsqueda de ID inexistente | `getUserById(99999)` | Lanza `RuntimeException` con código HTTP 404 | 404 capturado | ✅ PASS |
| 8 | `UsuarioService` | Creación de creador con datos válidos | `createUser('usr_...', 'Pass123', 'artesano')` | Inserta registro en SQLite y retorna ID > 0 | Creado exitosamente | ✅ PASS |
| 9 | `UsuarioService` | Validación de unicidad de username | Inserción con username duplicado | Lanza `RuntimeException` con código HTTP 409 Conflict | 409 Conflict | ✅ PASS |
| 10 | `UsuarioService` | Validación de longitud de contraseña | Contraseña menor a 6 caracteres (`12345`) | Lanza `InvalidArgumentException` con código 422 | 422 Unprocessable | ✅ PASS |
| 11 | `UsuarioService` | Validación de rol inexistente | Rol desconocido (`superhacker`) | Lanza `InvalidArgumentException` con código 422 | 422 Unprocessable | ✅ PASS |
| 12 | `UsuarioService` | Actualización de rol regular | `updateRole($id, 'asistente')` | Actualiza rol a asistente en usuario no-admin | Rol modificado | ✅ PASS |
| 13 | `UsuarioService` | **Salvaguarda Admin Raíz (ID #1): Rol** | `updateRole(1, 'artesano')` | Lanza `RuntimeException` con código HTTP 403 Forbidden | 403 Bloqueado | ✅ PASS |
| 14 | `UsuarioService` | **Salvaguarda Admin Raíz (ID #1): Borrado** | `deleteUser(1)` | Lanza `RuntimeException` con código HTTP 403 Forbidden | 403 Bloqueado | ✅ PASS |
| 15 | `UsuarioService` | Modificación de nombre de usuario | `updateUsername($id, 'nuevo_nombre')` | Modifica `username` y valida unicidad | Nombre actualizado | ✅ PASS |
| 16 | `UsuarioService` | Conflicto por nombre duplicado | `updateUsername($id, 'admin')` | Lanza `RuntimeException` con código HTTP 409 | 409 Conflict | ✅ PASS |
| 17 | `UsuarioService` | Restablecer contraseña manual | `resetPassword($id, 'NuevaClaveValida2026')` | Encripta nueva clave y `es_autogenerada: false` | Clave actualizada | ✅ PASS |
| 18 | `UsuarioService` | Restablecer contraseña autogenerada | `resetPassword($id)` | Genera contraseña temporal tipo `Crochet!<hex>!` | Clave autogenerada | ✅ PASS |
| 19 | `UsuarioService` | **Salvaguarda Auto-Eliminación** | `deleteUser($id, $id)` (sesión activa) | Lanza `RuntimeException` con código HTTP 403 | 403 Bloqueado | ✅ PASS |
| 20 | `UsuarioService` | **Salvaguarda Creaciones Asociadas** | `deleteUser(2)` (`artesana_ana`) | Lanza `RuntimeException` con código HTTP 409 Conflict | 409 Bloqueado | ✅ PASS |
| 21 | `UsuarioService` | **Baja Lógica Exitosa (Soft Delete)** | `deleteUser($tempId)` | Marca `activo = 0`, `eliminado_en = datetime(...)` | Baja lógica aplicada | ✅ PASS |
| 22 | `UsuarioRepository` | **Exclusión de Inactivos en Lookups** | `findByIdSafe($tempId, true)` | Retorna `null` al filtrar por cuentas activas | Usuario oculto | ✅ PASS |
| 23 | `UsuarioRepository` | **Persistencia Física en SQLite** | `findByIdSafe($tempId, false)` | Retorna registro con `activo = 0` y fecha en `eliminado_en` | Fila preservada | ✅ PASS |
| 24 | `UsuarioService` | **Prevención de Doble Baja Lógica** | `deleteUser($tempId)` en cuenta ya inactiva | Lanza `RuntimeException` con código HTTP 409 Conflict | 409 Conflict | ✅ PASS |
| 25 | `AuthService` | **Bloqueo de Login para Inactivos** | `authenticate($inactivo, $password)` | Lanza `RuntimeException` con código HTTP 401 Unauthorized | Login denegado 401 | ✅ PASS |
| 26 | `RoleGuard` | Acceso con rol de administrador | `RoleGuard::adminOnly()` (usuario admin) | Concede acceso y retorna datos de usuario | Acceso permitido | ✅ PASS |
| 27 | `RoleGuard` | Bloqueo a rol de artesano | `RoleGuard::adminOnly()` (usuario artesano) | Emite HTTP 403 Forbidden y `exito: false` | 403 Bloqueado | ✅ PASS |
| 28 | `RoleGuard` | Bloqueo a rol de asistente | `RoleGuard::adminOnly()` (usuario asistente) | Emite HTTP 403 Forbidden y `exito: false` | 403 Bloqueado | ✅ PASS |
| 29 | Servidor HTTP | Obtención de tokens de prueba | Login previo en `/api/auth/login.php` | Emite tokens legítimos para admin y artesano | Tokens listos | ✅ PASS |
| 30 | Servidor HTTP | Directorio de usuarios con admin | `GET /api/usuarios/index.php` (Admin Token) | HTTP 200 OK con array de creadores y paginación | `200` | ✅ PASS |
| 31 | Servidor HTTP | Directorio invocado por artesano | `GET /api/usuarios/index.php` (Artesano Token) | HTTP 403 Forbidden (privilegios insuficientes) | `403` | ✅ PASS |
| 32 | Servidor HTTP | Directorio sin autenticación | `GET /api/usuarios/index.php` (Sin Token) | HTTP 401 Unauthorized (falta Bearer token) | `401` | ✅ PASS |
| 33 | Servidor HTTP | Restricción de método en index | `POST /api/usuarios/index.php` | HTTP 405 Method Not Allowed | `405` | ✅ PASS |
| 34 | Servidor HTTP | Registro de creador vía POST | `POST /api/usuarios/crear.php` (Admin Token) | HTTP 201 Created con ID generado | `201` | ✅ PASS |
| 35 | Servidor HTTP | Registro con username duplicado | `POST /api/usuarios/crear.php` (Mismo username) | HTTP 409 Conflict (username no disponible) | `409` | ✅ PASS |
| 36 | Servidor HTTP | Registro con clave corta | `POST /api/usuarios/crear.php` (`password: 123`) | HTTP 422 Unprocessable Entity | `422` | ✅ PASS |
| 37 | Servidor HTTP | Registro invocado por artesano | `POST /api/usuarios/crear.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 38 | Servidor HTTP | Modificación de rol en usuario regular | `POST /api/usuarios/cambiar-rol.php` (Admin Token) | HTTP 200 OK con rol actualizado a asistente | `200` | ✅ PASS |
| 39 | Servidor HTTP | **Salvaguarda HTTP en Admin Raíz (ID #1)**| `POST /api/usuarios/cambiar-rol.php` (`id: 1, rol: artesano`) | HTTP 403 Forbidden (operación denegada en ID #1) | `403` | ✅ PASS |
| 40 | Servidor HTTP | Modificación de rol por artesano | `POST /api/usuarios/cambiar-rol.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 41 | Servidor HTTP | Preflight CORS en directorio | `OPTIONS /api/usuarios/index.php` | HTTP 204 No Content con cabeceras CORS | `204` | ✅ PASS |
| 42 | Servidor HTTP | Actualización de nombre de usuario | `POST /api/usuarios/actualizar.php` (Admin Token) | HTTP 200 OK con nombre modificado | `200` | ✅ PASS |
| 43 | Servidor HTTP | Nombre duplicado en actualización | `POST /api/usuarios/actualizar.php` (`username: admin`) | HTTP 409 Conflict | `409` | ✅ PASS |
| 44 | Servidor HTTP | Actualización de nombre por artesano | `POST /api/usuarios/actualizar.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 45 | Servidor HTTP | Restablecer clave autogenerada | `POST /api/usuarios/restablecer-password.php` (Admin Token) | HTTP 200 OK con `password_temporal` y `es_autogenerada: true` | `200` | ✅ PASS |
| 46 | Servidor HTTP | Restablecer clave manual | `POST /api/usuarios/restablecer-password.php` (`nueva_password`) | HTTP 200 OK con `es_autogenerada: false` | `200` | ✅ PASS |
| 47 | Servidor HTTP | Restablecer clave por artesano | `POST /api/usuarios/restablecer-password.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 48 | Servidor HTTP | **Salvaguarda Borrado ID #1 vía HTTP** | `POST /api/usuarios/eliminar.php` (`id: 1`) | HTTP 403 Forbidden (cuenta titular blindada) | `403` | ✅ PASS |
| 49 | Servidor HTTP | **Salvaguarda Creaciones Asociadas** | `POST /api/usuarios/eliminar.php` (`id: 2`, Ana) | HTTP 409 Conflict (artesana con piezas) | `409` | ✅ PASS |
| 50 | Servidor HTTP | Borrado de usuario por artesano | `POST /api/usuarios/eliminar.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 51 | Servidor HTTP | **Baja Lógica Exitosa vía HTTP** | `POST /api/usuarios/eliminar.php` (Usuario temporal) | HTTP 200 OK con `activo: 0` y mensaje formal | `200` | ✅ PASS |
| 52 | Servidor HTTP | **Intento de Borrado en Usuario Inactivo** | `POST /api/usuarios/eliminar.php` (Mismo usuario) | HTTP 409 Conflict (usuario ya inactivo) | `409` | ✅ PASS |
| 53 | `UsuarioService` | **Reactivación Lógica** | `reactivateUser($id)` | Restablece `activo = 1`, `eliminado_en = NULL` | Cuenta reactivada | ✅ PASS |
| 54 | `UsuarioService` | **Conflicto al Reactivar Cuenta Activa** | `reactivateUser($id)` en cuenta ya activa | Lanza `RuntimeException` con código HTTP 409 | 409 Conflict | ✅ PASS |
| 55 | `UsuarioService` | **Filtro de Creadores por Estado** | `listUsers(1, 10, null|false)` | Soporta listado de activos, inactivos o todos | Filtrado exacto | ✅ PASS |
| 56 | Servidor HTTP | Reactivación rechazada a no-admin | `POST /api/usuarios/reactivar.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 57 | Servidor HTTP | **Reactivación Exitosa vía HTTP** | `POST /api/usuarios/reactivar.php` (Admin Token) | HTTP 200 OK con `activo: 1` | `200` | ✅ PASS |
| 58 | Servidor HTTP | Reactivación en cuenta ya activa | `POST /api/usuarios/reactivar.php` (Cuenta activa) | HTTP 409 Conflict | `409` | ✅ PASS |
| 59 | Servidor HTTP | **Login de Cuenta Reactivada** | `POST /api/auth/login.php` (cuenta reactivada) | HTTP 200 OK con nuevo token Bearer | `200` | ✅ PASS |
| 60 | Servidor HTTP | **Listado de Usuarios Inactivos** | `GET /api/usuarios/index.php?estado=inactivos` | HTTP 200 OK con array de creadores inactivos | `200` | ✅ PASS |
| 61 | Servidor HTTP | **Listado de Todos los Usuarios** | `GET /api/usuarios/index.php?estado=todos` | HTTP 200 OK con creadores activos e inactivos | `200` | ✅ PASS |

---

---

## 2. Evidencia de Respuestas JSON y Cabeceras en Vivo

### 2.1 Modificación de Nombre de Usuario (`POST /api/usuarios/actualizar.php` — HTTP 200 OK)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Nombre de usuario actualizado exitosamente.",
  "datos": {
    "id": 23,
    "username": "creador_renombrado_1789240111",
    "rol": "asistente"
  }
}
```

### 2.2 Restablecimiento Administrativo de Contraseña Autogenerada (`POST /api/usuarios/restablecer-password.php` — HTTP 200 OK)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Contraseña restablecida exitosamente para el usuario 'creador_renombrado_1789240111'. Entregue la clave temporal al artesano: Crochet!4d8ec3!",
  "datos": {
    "id": 23,
    "username": "creador_renombrado_1789240111",
    "password_temporal": "Crochet!4d8ec3!",
    "es_autogenerada": true,
    "mensaje": "Contraseña restablecida exitosamente para el usuario 'creador_renombrado_1789240111'. Entregue la clave temporal al artesano: Crochet!4d8ec3!"
  }
}
```

### 2.3 Salvaguarda de Integridad Referencial al Eliminar (`POST /api/usuarios/eliminar.php` — HTTP 409 Conflict)
```http
HTTP/1.1 409 Conflict
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 409,
    "mensaje": "No se puede eliminar al usuario 'artesana_ana' porque tiene 2 creación(es) asociada(s) en el catálogo. Reasigne o elimine sus piezas antes de continuar."
  }
}
```

### 2.4 Salvaguarda Inviolable de la Cuenta Raíz ID #1 (`POST /api/usuarios/eliminar.php` — HTTP 403 Forbidden)
```http
HTTP/1.1 403 Forbidden
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "Operación denegada: La cuenta del administrador titular (ID #1) no puede ser eliminada."
  }
}
```

### 2.5 Baja Lógica Exitosa de Creador sin Creaciones (`POST /api/usuarios/eliminar.php` — HTTP 200 OK)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Usuario eliminado lógicamente de la plataforma.",
  "datos": {
    "id": 6,
    "activo": 0
  }
}
```

### 2.6 Intento de Eliminación en Cuenta Ya Inactiva (`POST /api/usuarios/eliminar.php` — HTTP 409 Conflict)
```http
HTTP/1.1 409 Conflict
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 409,
    "mensaje": "El usuario con ID #6 ya se encuentra inactivo/eliminado."
  }
}
```

### 2.7 Reactivación Lógica Exitosa de Cuenta (`POST /api/usuarios/reactivar.php` — HTTP 200 OK)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Usuario reactivado exitosamente en la plataforma.",
  "datos": {
    "id": 6,
    "username": "usuario_inactivo_temp",
    "rol": "artesano",
    "activo": 1
  }
}
```

---

## 3. Verificación de Integridad en SQLite

Ejecución directa en [`database/database.sqlite`](file:///Users/angelzaragoza/Desktop/proyecto-web/database/database.sqlite):
- **Integridad Física:**
  ```sql
  PRAGMA integrity_check;
  -- Resultado: ok (0 anomalías)
  ```
- **Integridad Referencial:**
  ```sql
  PRAGMA foreign_key_check;
  -- Resultado: 0 violaciones
  ```
- **Estándar Universal de Cero Eliminaciones Físicas (Baja Lógica):**
  ```sql
  SELECT id, username, rol, activo, eliminado_en FROM usuarios WHERE activo = 0;
  -- Resultado: Registros persisten físicamente con activo = 0 y timestamp ISO en eliminado_en.
  -- Cero filas destruidas; historial contable y autoría 100% preservados.
  ```
- **Relaciones Creador $\leftrightarrow$ Creación:**
  - El administrador (ID 1) posee 3 creaciones registradas en SQLite.
  - La artesana Ana (ID 2) posee 2 creaciones registradas en SQLite.
  - El asistente Leo (ID 3) posee 0 creaciones registradas en SQLite.
  - La consulta optimizada de `UsuarioRepository::listAllWithCreationsCount()` filtra por `activo = 1` y calcula con precisión el número de piezas por artesano sin incurrir en problemas N+1.

---

## 4. Veredicto y Siguientes Pasos

- [x] Repositorio `UsuarioRepository` extendido con métodos `updateUsername()`, `updatePassword()`, `delete()`, `reactivate()`, `listAllWithCreationsCount()` y `countCreationsByUser()`, integrando el estándar universal de bajas lógicas (`UPDATE ... SET activo = 0, eliminado_en = datetime('now', 'localtime')`).
- [x] Servicio de negocio `UsuarioService` implementado con las 7 operaciones completas: listado con filtro de estado (`?estado=activos|inactivos|todos`), búsqueda por ID, creación, modificación de rol, actualización de nombre, restablecimiento de contraseña (manual y autogenerada), baja lógica con salvaguardas y reactivación de cuentas inactivas.
- [x] Salvaguarda absoluta de la cuenta raíz (ID #1) garantizada ante degradación de rol y ante baja lógica.
- [x] Salvaguarda de auto-eliminación de la cuenta en sesión activa.
- [x] Salvaguarda de integridad referencial para evitar dar de baja usuarios con creaciones asociadas activas en catálogo.
- [x] Regla universal de CERO eliminaciones físicas verificada: las filas permanecen en SQLite con `activo = 0` y `eliminado_en` registrado.
- [x] Bloqueo inmediato de autenticación en `AuthService` para cuentas inactivadas (`activo = 0`).
- [x] Protección de rutas mediante `RoleGuard::adminOnly()`, bloqueando acceso no autorizado con HTTP 403 en los 7 endpoints.
- [x] Controladores REST delgados creados en:
  - `api/usuarios/index.php` (GET con soporte `?estado=activos|inactivos|todos`)
  - `api/usuarios/crear.php` (POST)
  - `api/usuarios/cambiar-rol.php` (POST)
  - `api/usuarios/actualizar.php` (POST)
  - `api/usuarios/restablecer-password.php` (POST)
  - `api/usuarios/eliminar.php` (POST)
  - `api/usuarios/reactivar.php` (POST)
- [x] Soporte para paginación estandarizada (`PaginationHelper`) en el directorio de creadores.
- [x] Cero fugas HTML en errores; respuestas uniformes en español (`exito`, `mensaje`, `datos`, `error.codigo`).
- [x] Documentación humana exhaustiva en `docs/api-design.es.md` y `docs/api-design.md`.
- [x] 105 de 105 aserciones aprobadas en el script CLI automatizado en 847.15 ms.
- [x] Trazas crudas y volcados de terminal respaldados en [`logs/subfase-3.3-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-cli.log) y [`logs/subfase-3.3-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-http.log).

**ESTADO ACTUAL:** **COMPLETA Y VERIFICADA AL 100%.**
**ACCIONES SIGUIENTES:** En cumplimiento estricto del protocolo de compuerta secuencial de testing, se detiene completamente la ejecución y se solicita la autorización explícita del usuario para dar inicio a la **Subfase 3.4: Catálogo, Creaciones & Ciclo de Vida de Imágenes (`CreacionRepository`, `CreacionService` y `api/creaciones/*`)**.
