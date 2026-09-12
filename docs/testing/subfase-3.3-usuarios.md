# Reporte de Pruebas: Subfase 3.3 — Gestión de Usuarios, Autoría de Creadores & Roles RBAC

- **Fecha de Ejecución:** 2026-09-12 12:38:16 CST
- **Responsable:** Antigravity Agent (Pair Programming con Ingeniero Titular)
- **Entorno:** PHP 8.3.29 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (macOS Darwin)
- **Archivos de Log Crudos:**
  - CLI: [`logs/subfase-3.3-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-cli.log)
  - HTTP: [`logs/subfase-3.3-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.3.php`](file:///Users/angelzaragoza/Desktop/proyecto-web/tests/test-subfase-3.3.php)
- **Resultado General:** **54 / 54 Aserciones Aprobadas (100% OK) en 265.52 ms** — ✅ **APTO PARA AVANZAR**

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
| 15 | `UsuarioService` | Eliminación de usuario sin creaciones | `deleteUser($tempId)` | Elimina usuario regular y retorna `true` | Eliminado | ✅ PASS |
| 16 | `RoleGuard` | Acceso con rol de administrador | `RoleGuard::adminOnly()` (usuario admin) | Concede acceso y retorna datos de usuario | Acceso permitido | ✅ PASS |
| 17 | `RoleGuard` | Bloqueo a rol de artesano | `RoleGuard::adminOnly()` (usuario artesano) | Emite HTTP 403 Forbidden y `exito: false` | 403 Bloqueado | ✅ PASS |
| 18 | `RoleGuard` | Bloqueo a rol de asistente | `RoleGuard::adminOnly()` (usuario asistente) | Emite HTTP 403 Forbidden y `exito: false` | 403 Bloqueado | ✅ PASS |
| 19 | Servidor HTTP | Obtención de tokens de prueba | Login previo en `/api/auth/login.php` | Emite tokens legítimos para admin y artesano | Tokens listos | ✅ PASS |
| 20 | Servidor HTTP | Directorio de usuarios con admin | `GET /api/usuarios/index.php` (Admin Token) | HTTP 200 OK con array de creadores y paginación | `200` | ✅ PASS |
| 21 | Servidor HTTP | Directorio invocado por artesano | `GET /api/usuarios/index.php` (Artesano Token) | HTTP 403 Forbidden (privilegios insuficientes) | `403` | ✅ PASS |
| 22 | Servidor HTTP | Directorio sin autenticación | `GET /api/usuarios/index.php` (Sin Token) | HTTP 401 Unauthorized (falta Bearer token) | `401` | ✅ PASS |
| 23 | Servidor HTTP | Restricción de método en index | `POST /api/usuarios/index.php` | HTTP 405 Method Not Allowed | `405` | ✅ PASS |
| 24 | Servidor HTTP | Registro de creador vía POST | `POST /api/usuarios/crear.php` (Admin Token) | HTTP 201 Created con ID generado | `201` | ✅ PASS |
| 25 | Servidor HTTP | Registro con username duplicado | `POST /api/usuarios/crear.php` (Mismo username) | HTTP 409 Conflict (username no disponible) | `409` | ✅ PASS |
| 26 | Servidor HTTP | Registro con clave corta | `POST /api/usuarios/crear.php` (`password: 123`) | HTTP 422 Unprocessable Entity | `422` | ✅ PASS |
| 27 | Servidor HTTP | Registro invocado por artesano | `POST /api/usuarios/crear.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 28 | Servidor HTTP | Modificación de rol en usuario regular | `POST /api/usuarios/cambiar-rol.php` (Admin Token) | HTTP 200 OK con rol actualizado a asistente | `200` | ✅ PASS |
| 29 | Servidor HTTP | **Salvaguarda HTTP en Admin Raíz (ID #1)**| `POST /api/usuarios/cambiar-rol.php` (`id: 1, rol: artesano`) | HTTP 403 Forbidden (operación denegada en ID #1) | `403` | ✅ PASS |
| 30 | Servidor HTTP | Modificación de rol por artesano | `POST /api/usuarios/cambiar-rol.php` (Artesano Token) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 31 | Servidor HTTP | Preflight CORS en directorio | `OPTIONS /api/usuarios/index.php` | HTTP 204 No Content con cabeceras CORS | `204` | ✅ PASS |

---

## 2. Evidencia de Respuestas JSON y Cabeceras en Vivo

### 2.1 Directorio de Creadores (`GET /api/usuarios/index.php` — HTTP 200 OK)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With

{
  "exito": true,
  "mensaje": "Directorio de creadores obtenido exitosamente.",
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "rol": "admin",
      "creado_en": "2026-09-11 13:30:52",
      "creaciones_asociadas": 3
    },
    {
      "id": 2,
      "username": "artesana_ana",
      "rol": "artesano",
      "creado_en": "2026-09-01 13:30:52",
      "creaciones_asociadas": 2
    },
    {
      "id": 3,
      "username": "asistente_leo",
      "rol": "asistente",
      "creado_en": "2026-09-03 13:30:52",
      "creaciones_asociadas": 0
    }
  ],
  "paginacion": {
    "total_items": 3,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 10,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

### 2.2 Bloqueo de Acceso a Rol No-Admin (`GET /api/usuarios/index.php` — HTTP 403 Forbidden)
```http
HTTP/1.1 403 Forbidden
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "No tienes permisos suficientes para realizar esta acción. Roles requeridos: [admin]. Tu rol actual es 'artesano'."
  }
}
```

### 2.3 Registro Exitoso de Creador (`POST /api/usuarios/crear.php` — HTTP 201 Created)
```http
HTTP/1.1 201 Created
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Creador registrado exitosamente en la plataforma.",
  "datos": {
    "id": 4,
    "username": "creador_demo_1789238312",
    "rol": "artesano",
    "creado_en": "2026-09-12 12:38:32"
  }
}
```

### 2.4 Conflicto por Nombre de Usuario Duplicado (`POST /api/usuarios/crear.php` — HTTP 409 Conflict)
```http
HTTP/1.1 409 Conflict
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 409,
    "mensaje": "El nombre de usuario 'creador_demo_1789238312' ya se encuentra registrado."
  }
}
```

### 2.5 Actualización de Rol (`POST /api/usuarios/cambiar-rol.php` — HTTP 200 OK)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Rol de usuario actualizado exitosamente.",
  "datos": {
    "id": 4,
    "username": "creador_demo_1789238312",
    "rol": "asistente"
  }
}
```

### 2.6 Salvaguarda Inviolable de Cuenta Raíz (`POST /api/usuarios/cambiar-rol.php` — HTTP 403 Forbidden)
```http
HTTP/1.1 403 Forbidden
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "Operación denegada: La cuenta del administrador titular (ID #1) no puede ser degradada ni modificada."
  }
}
```

### 2.7 Preflight CORS (`OPTIONS /api/usuarios/index.php` — HTTP 204 No Content)
```http
HTTP/1.1 204 No Content
Host: localhost:8000
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Authorization, Content-Type
Access-Control-Max-Age: 86400
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

- [x] Repositorio `UsuarioRepository` extendido con métodos de cálculo de creaciones asociadas (`listAllWithCreationsCount`, `countCreationsByUser`).
- [x] Servicio de negocio `UsuarioService` implementado con validación estricta de nombres de usuario, contraseñas, roles y unicidad.
- [x] Salvaguarda absoluta de la cuenta raíz (ID #1) garantizada en repositorio, servicio y endpoints REST.
- [x] Protección de rutas mediante `RoleGuard::adminOnly()`, bloqueando acceso no autorizado con HTTP 403.
- [x] Controladores REST delgados creados en `api/usuarios/index.php`, `api/usuarios/crear.php` y `api/usuarios/cambiar-rol.php`.
- [x] Soporte para paginación estandarizada (`PaginationHelper`) en el directorio de creadores.
- [x] Cero fugas HTML en errores; respuestas uniformes en español (`exito`, `mensaje`, `datos`, `error.codigo`).
- [x] 54 de 54 aserciones aprobadas en el script CLI automatizado en 265.52 ms.
- [x] Trazas crudas y volcados de terminal respaldados en [`logs/subfase-3.3-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-cli.log) y [`logs/subfase-3.3-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.3-http.log).

**ESTADO ACTUAL:** **COMPLETA Y VERIFICADA AL 100%.**
**ACCIONES SIGUIENTES:** En cumplimiento estricto del protocolo de compuerta secuencial de testing, se detiene completamente la ejecución y se solicita la autorización explícita del usuario para dar inicio a la **Subfase 3.4: Catálogo, Creaciones & Ciclo de Vida de Imágenes (`CreacionRepository`, `CreacionService` y `api/creaciones/*`)**.
