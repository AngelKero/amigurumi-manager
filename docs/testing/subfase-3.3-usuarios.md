# Reporte de Pruebas: Subfase 3.3 — Gestión de Usuarios, Autoría de Creadores & Roles RBAC

- **Fecha de Ejecución:** 2026-09-12 13:08:31 CST
- **Responsable:** Antigravity Agent (Pair Programming con Ingeniero Titular)
- **Entorno:** PHP 8.3.29 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (macOS Darwin)
- **Archivos de Log Crudos:**
  - CLI: [`logs/subfase-3.3-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-cli.log)
  - HTTP: [`logs/subfase-3.3-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.3.php`](file:///Users/angelzaragoza/Desktop/proyecto-web/tests/test-subfase-3.3.php)
- **Resultado General:** **79 / 79 Aserciones Aprobadas (100% OK) en 652.54 ms** — ✅ **APTO PARA AVANZAR**

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
| 21 | `UsuarioService` | Eliminación de usuario sin creaciones | `deleteUser($tempId)` | Elimina usuario regular y retorna `true` | Eliminado | ✅ PASS |
| 22 | `RoleGuard` | Acceso con rol de administrador | `RoleGuard::adminOnly()` (usuario admin) | Concede acceso y retorna datos de usuario | Acceso permitido | ✅ PASS |
| 23 | `RoleGuard` | Bloqueo a rol de artesano | `RoleGuard::adminOnly()` (usuario artesano) | Emite HTTP 403 Forbidden y `exito: false` | 403 Bloqueado | ✅ PASS |
| 24 | `RoleGuard` | Bloqueo a rol de asistente | `RoleGuard::adminOnly()` (usuario asistente) | Emite HTTP 403 Forbidden y `exito: false` | 403 Bloqueado | ✅ PASS |
| 25 | Servidor HTTP | Obtención de tokens de prueba | Login previo en `/api/auth/login.php` | Emite tokens legítimos para admin y artesano | Tokens listos | ✅ PASS |
| 26 | Servidor HTTP | Directorio de usuarios con admin | `GET /api/usuarios/index.php` (Admin Token) | HTTP 200 OK con array de creadores y paginación | `200` | ✅ PASS |
| 27 | Servidor HTTP | Directorio invocado por artesano | `GET /api/usuarios/index.php` (Artesano Token) | HTTP 403 Forbidden (privilegios insuficientes) | `403` | ✅ PASS |
| 28 | Servidor HTTP | Directorio sin autenticación | `GET /api/usuarios/index.php` (Sin Token) | HTTP 401 Unauthorized (falta Bearer token) | `401` | ✅ PASS |
| 29 | Servidor HTTP | Restricción de método en index | `POST /api/usuarios/index.php` | HTTP 405 Method Not Allowed | `405` | ✅ PASS |
| 30 | Servidor HTTP | Registro de creador vía POST | `POST /api/usuarios/crear.php` (Admin Token) | HTTP 201 Created con ID generado | `201` | ✅ PASS |
| 31 | Servidor HTTP | Registro con username duplicado | `POST /api/usuarios/crear.php` (Mismo username) | HTTP 409 Conflict (username no disponible) | `409` | ✅ PASS |
| 32 | Servidor HTTP | Registro con clave corta | `POST /api/usuarios/crear.php` (`password: 123`) | HTTP 422 Unprocessable Entity | `422` | ✅ PASS |
| 33 | Servidor HTTP | Registro invocado por artesano | `POST /api/usuarios/crear.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 34 | Servidor HTTP | Modificación de rol en usuario regular | `POST /api/usuarios/cambiar-rol.php` (Admin Token) | HTTP 200 OK con rol actualizado a asistente | `200` | ✅ PASS |
| 35 | Servidor HTTP | **Salvaguarda HTTP en Admin Raíz (ID #1)**| `POST /api/usuarios/cambiar-rol.php` (`id: 1, rol: artesano`) | HTTP 403 Forbidden (operación denegada en ID #1) | `403` | ✅ PASS |
| 36 | Servidor HTTP | Modificación de rol por artesano | `POST /api/usuarios/cambiar-rol.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 37 | Servidor HTTP | Preflight CORS en directorio | `OPTIONS /api/usuarios/index.php` | HTTP 204 No Content con cabeceras CORS | `204` | ✅ PASS |
| 38 | Servidor HTTP | Actualización de nombre de usuario | `POST /api/usuarios/actualizar.php` (Admin Token) | HTTP 200 OK con nombre modificado | `200` | ✅ PASS |
| 39 | Servidor HTTP | Nombre duplicado en actualización | `POST /api/usuarios/actualizar.php` (`username: admin`) | HTTP 409 Conflict | `409` | ✅ PASS |
| 40 | Servidor HTTP | Actualización de nombre por artesano | `POST /api/usuarios/actualizar.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 41 | Servidor HTTP | Restablecer clave autogenerada | `POST /api/usuarios/restablecer-password.php` (Admin Token) | HTTP 200 OK con `password_temporal` y `es_autogenerada: true` | `200` | ✅ PASS |
| 42 | Servidor HTTP | Restablecer clave manual | `POST /api/usuarios/restablecer-password.php` (`nueva_password`) | HTTP 200 OK con `es_autogenerada: false` | `200` | ✅ PASS |
| 43 | Servidor HTTP | Restablecer clave por artesano | `POST /api/usuarios/restablecer-password.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 44 | Servidor HTTP | **Salvaguarda Borrado ID #1 vía HTTP** | `POST /api/usuarios/eliminar.php` (`id: 1`) | HTTP 403 Forbidden (cuenta titular blindada) | `403` | ✅ PASS |
| 45 | Servidor HTTP | **Salvaguarda Creaciones Asociadas** | `POST /api/usuarios/eliminar.php` (`id: 2`, Ana) | HTTP 409 Conflict (artesana con piezas) | `409` | ✅ PASS |
| 46 | Servidor HTTP | Borrado de usuario por artesano | `POST /api/usuarios/eliminar.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 47 | Servidor HTTP | Borrado exitoso de usuario sin piezas | `POST /api/usuarios/eliminar.php` (Usuario temporal) | HTTP 200 OK con acuse de eliminación | `200` | ✅ PASS |

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

### 2.5 Eliminación Exitosa de Creador sin Creaciones (`POST /api/usuarios/eliminar.php` — HTTP 200 OK)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Usuario eliminado exitosamente de la plataforma.",
  "datos": {
    "id": 23
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
- **Relaciones Creador $\leftrightarrow$ Creación:**
  - El administrador (ID 1) posee 3 creaciones registradas en SQLite.
  - La artesana Ana (ID 2) posee 2 creaciones registradas en SQLite.
  - El asistente Leo (ID 3) posee 0 creaciones registradas en SQLite.
  - La consulta optimizada de `UsuarioRepository::listAllWithCreationsCount()` calcula con precisión el número de piezas por artesano sin incurrir en problemas N+1.

---

## 4. Veredicto y Siguientes Pasos

- [x] Repositorio `UsuarioRepository` extendido con métodos `updateUsername()`, `updatePassword()`, `delete()`, `listAllWithCreationsCount()` y `countCreationsByUser()`.
- [x] Servicio de negocio `UsuarioService` implementado con las 6 operaciones completas: listado, búsqueda por ID, creación, modificación de rol, actualización de nombre, restablecimiento de contraseña (manual y autogenerada) y eliminación con salvaguardas.
- [x] Salvaguarda absoluta de la cuenta raíz (ID #1) garantizada ante degradación de rol y ante eliminación.
- [x] Salvaguarda de auto-eliminación de la cuenta en sesión activa.
- [x] Salvaguarda de integridad referencial para evitar eliminar usuarios con creaciones asociadas en catálogo.
- [x] Protección de rutas mediante `RoleGuard::adminOnly()`, bloqueando acceso no autorizado con HTTP 403 en los 6 endpoints.
- [x] Controladores REST delgados creados en:
  - `api/usuarios/index.php` (GET)
  - `api/usuarios/crear.php` (POST)
  - `api/usuarios/cambiar-rol.php` (POST)
  - `api/usuarios/actualizar.php` (POST)
  - `api/usuarios/restablecer-password.php` (POST)
  - `api/usuarios/eliminar.php` (POST)
- [x] Soporte para paginación estandarizada (`PaginationHelper`) en el directorio de creadores.
- [x] Cero fugas HTML en errores; respuestas uniformes en español (`exito`, `mensaje`, `datos`, `error.codigo`).
- [x] Documentación humana exhaustiva en `docs/api-design.es.md` y `docs/api-design.md`.
- [x] 79 de 79 aserciones aprobadas en el script CLI automatizado en 652.54 ms.
- [x] Trazas crudas y volcados de terminal respaldados en [`logs/subfase-3.3-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-cli.log) y [`logs/subfase-3.3-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-http.log).

**ESTADO ACTUAL:** **COMPLETA Y VERIFICADA AL 100%.**
**ACCIONES SIGUIENTES:** En cumplimiento estricto del protocolo de compuerta secuencial de testing, se detiene completamente la ejecución y se solicita la autorización explícita del usuario para dar inicio a la **Subfase 3.4: Catálogo, Creaciones & Ciclo de Vida de Imágenes (`CreacionRepository`, `CreacionService` y `api/creaciones/*`)**.
