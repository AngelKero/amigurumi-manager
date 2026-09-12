# Reporte de Pruebas: Subfase 3.6.1 — Acceso, Autorización, IDOR & Blindaje RBAC (OWASP A01)

[← Volver al Hub de Testing](./README.md) | [Ver Plan Maestro de Fase 3](../architecture/phase-3-plan.md)

- **Fecha de Ejecución:** 2026-09-12
- **Responsable:** Antigravity (Advanced Agentic Coding)
- **Marco Metodológico:** OWASP Top 10:2021 — Categoría A01 (Broken Access Control)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (`PRAGMA busy_timeout = 5000;`, `PRAGMA foreign_keys = ON;`)
- **Archivo de Log Crudo (CLI):** [`logs/subfase-3.6.1-cli.log`](../../logs/subfase-3.6.1-cli.log)
- **Archivo de Trazas HTTP:** [`logs/subfase-3.6.1-http.log`](../../logs/subfase-3.6.1-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.6.1.php`](../../tests/test-subfase-3.6.1.php)
- **Resultado General:** **165 / 165 Aserciones Aprobadas (100% OK en 412.66 ms)** — ✅ **APTO PARA AVANZAR**
- **Total Acumulado Fase 3:** **697 / 697 Aserciones Aprobadas (100% OK en verde)** (3.1: 93, 3.2: 69, 3.3: 105, 3.4: 126, 3.5: 139, 3.6.1: 165)

---

## 1. Resumen Ejecutivo del Alcance Implementado

La **Subfase 3.6.1** constituye la primera etapa de la **Auditoría Integral de Seguridad y Blindaje (Opción B)**, focalizada rigurosamente en erradicar las vulnerabilidades de **Control de Acceso Roto (OWASP A01:2021)** en todas las capas del Micro-ERP colaborativo en crochet.

Se auditaron exhaustivamente tanto de forma unitaria en memoria como en integración HTTP en vivo contra `http://localhost:8000` las siguientes dimensiones:

1. **Control de Acceso Vertical (RBAC):**
   - Bloqueo de peticiones anónimas hacia todas las rutas de mutación o administración (HTTP 401 Unauthorized).
   - Bloqueo de tokens con firmas HMAC alteradas o malformadas (HTTP 401 Unauthorized).
   - Restricción del rol `asistente` frente a cualquier mutación de catálogo, gestión de pedidos o administración de usuarios (HTTP 403 Forbidden).
   - Restricción del rol `artesano` frente a la administración de usuarios del taller (HTTP 403 Forbidden).
2. **Prevención de IDOR Horizontal en Creaciones (ADR-007):**
   - Una artesana (`artesana_ana`, ID #2) no puede actualizar, eliminar, restaurar, ajustar stock ni conmutar la modalidad de encargo de piezas pertenecientes al administrador (`admin`, ID #1). Cada intento es repelido con HTTP 403 Forbidden.
   - El administrador mantiene privilegios globales omnímodos para gestionar cualquier creación del catálogo colaborativo.
3. **Prevención de IDOR Horizontal en Pedidos & Encargos:**
   - Una artesana no puede consultar, cambiar de estado ni cancelar pedidos asociados a creaciones de otros artesanos (HTTP 403 Forbidden).
   - Una artesana no puede registrar pedidos manuales referenciando creaciones de otros creadores (HTTP 403 Forbidden).
   - El listado de pedidos para una artesana filtra estrictamente las piezas de su autoría a nivel de consulta SQL, sin exponer pedidos ajenos.
4. **Salvaguardas Inmutables de Cuenta Raíz (ID #1) y Auto-eliminación:**
   - La cuenta del Administrador Titular (`id: 1`, `@admin`) está blindada en memoria y en base de datos contra degradación de rol (`updateRole` lanza HTTP 403 Forbidden).
   - El Administrador Titular no puede ser eliminado ni dado de baja lógica (`deleteUser` lanza HTTP 403 Forbidden).
   - Ningún usuario administrador puede eliminar su propia cuenta mientras mantenga su sesión activa (bloqueo de auto-eliminación accidental con HTTP 403 Forbidden).
5. **Aislamiento de Creaciones Inactivas y Usuarios Desactivados:**
   - El endpoint público de detalle (`GET /api/creaciones/detalle.php?id=X`) devuelve HTTP 404 Not Found cuando la pieza fue dada de baja (`activo = 0`), impidiendo la exposición de ítems archivados.
   - El catálogo público excluye de forma estricta las piezas inactivas.
   - Cuentas inactivadas (`activo = 0`) no pueden autenticarse y sus Bearer tokens activos quedan inmediatamente revocados sin esperar el TTL de 24 horas.
6. **Restricción Estricta de Métodos HTTP (405 Method Not Allowed):**
   - Se validaron los 25 controladores REST del sistema ante métodos HTTP erróneos (invocaciones GET en controladores POST, e invocaciones POST en controladores GET), confirmando que todos emiten formalmente código HTTP 405 Method Not Allowed.

---

## 2. Matriz Detallada de Aserciones y Casos Evaluados

### Sección 1: Preparación de Credenciales y Emisión de Tokens por Rol

| # | Caso de Prueba Evaluado | Entrada / Parámetros | Comportamiento Verificado | Estado |
| :-: | :--- | :--- | :--- | :-: |
| 1-2 | Login y validación de claims: Admin | `username: 'admin'` | ID = 1, rol = `'admin'`, token HMAC-SHA256 emitido | ✅ PASS |
| 3-4 | Login y validación de claims: Artesana Ana | `username: 'artesana_ana'` | ID = 2, rol = `'artesano'`, token emitido | ✅ PASS |
| 5-6 | Login y validación de claims: Asistente Leo | `username: 'asistente_leo'` | ID = 3, rol = `'asistente'`, token emitido | ✅ PASS |

### Sección 2: Control de Acceso Vertical (RBAC) & Rutas Protegidas

| # | Endpoint Evaluado | Método | Rol Evaluado | Código HTTP Verificado | Estado |
| :-: | :--- | :---: | :---: | :---: | :-: |
| 7-8 | `/api/auth/me.php` | GET | Anónimo (Sin Token) | 401 Unauthorized | ✅ PASS |
| 9-10 | `/api/auth/cambiar-password.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 11-12 | `/api/usuarios/index.php` | GET | Anónimo | 401 Unauthorized | ✅ PASS |
| 13-14 | `/api/usuarios/crear.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 15-16 | `/api/usuarios/cambiar-rol.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 17-18 | `/api/usuarios/eliminar.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 19-20 | `/api/creaciones/crear.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 21-22 | `/api/creaciones/actualizar.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 23-24 | `/api/creaciones/eliminar.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 25-26 | `/api/pedidos/index.php` | GET | Anónimo | 401 Unauthorized | ✅ PASS |
| 27-28 | `/api/pedidos/crear.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 29-30 | `/api/pedidos/cambiar-estado.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 31-32 | `/api/pedidos/cancelar.php` | POST | Anónimo | 401 Unauthorized | ✅ PASS |
| 33-34 | `/api/auth/me.php` | GET | Token manipulado / firma falsa | 401 Unauthorized con mensaje explicativo | ✅ PASS |
| 35-48 | `/api/usuarios/*` (7 endpoints) | GET/POST | `asistente` | 403 Forbidden (`RoleGuard::adminOnly()`) | ✅ PASS |
| 49-60 | `/api/creaciones/*` (6 endpoints de mutación) | POST | `asistente` | 403 Forbidden (`RoleGuard::artisanOrAdmin()`) | ✅ PASS |
| 61-64 | `/api/pedidos/*` (4 endpoints de gestión) | GET/POST | `asistente` | 403 Forbidden (`RoleGuard::artisanOrAdmin()`) | ✅ PASS |
| 65-71 | `/api/usuarios/*` (7 endpoints) | GET/POST | `artesano` | 403 Forbidden (`RoleGuard::adminOnly()`) | ✅ PASS |

### Sección 3: Prevención IDOR Horizontal en Catálogo / Creaciones

| # | Operación Auditada | Actor / Víctima | Endpoint / Método | Comportamiento Verificado | Estado |
| :-: | :--- | :--- | :--- | :--- | :-: |
| 72-74 | Verificación de autoría en catálogo | Creación 1 (Admin), Creación 3 y 5 (Ana) | Repositorio SQLite | Propiedad de autoría aislada en base de datos | ✅ PASS |
| 75-76 | Servicio en memoria | Ana evaluando Pieza #1 de Admin | `CreacionService::ensureArtisanOwnership` | Lanza excepción código 403 con mensaje descriptivo | ✅ PASS |
| 77-78 | Intento de Actualización (HTTP) | Ana intentando mutar Pieza #1 | `POST /api/creaciones/actualizar.php` | 403 Forbidden ("No tienes permisos...") | ✅ PASS |
| 79 | Intento de Ajuste de Stock (HTTP) | Ana intentando cambiar stock de Pieza #1 | `POST /api/creaciones/ajustar-stock.php` | 403 Forbidden | ✅ PASS |
| 80 | Intento de Toggle Encargo (HTTP) | Ana intentando conmutar modo de Pieza #1 | `POST /api/creaciones/toggle-encargo.php` | 403 Forbidden | ✅ PASS |
| 81 | Intento de Baja Lógica (HTTP) | Ana intentando desactivar Pieza #1 | `POST /api/creaciones/eliminar.php` | 403 Forbidden | ✅ PASS |
| 82 | Intento de Reactivación (HTTP) | Ana intentando reactivar Pieza #1 | `POST /api/creaciones/restaurar.php` | 403 Forbidden | ✅ PASS |
| 83-84 | Privilegio Global de Administrador | Admin actualizando stock en Pieza #5 de Ana | `POST /api/creaciones/ajustar-stock.php` | 200 OK exitoso y restauración de existencias | ✅ PASS |

### Sección 4: Prevención IDOR Horizontal en Pedidos / Encargos

| # | Operación Auditada | Actor / Víctima | Endpoint / Método | Comportamiento Verificado | Estado |
| :-: | :--- | :--- | :--- | :--- | :-: |
| 85-86 | Verificación de autoría en pedidos | Pedido 1 (Admin), Pedido 2 (Ana) | Repositorio SQLite | Pedidos vinculados a creaciones de artesanos distintos | ✅ PASS |
| 87-88 | Servicio en memoria | Ana evaluando Pedido #1 de Admin | `PedidoService::ensureArtisanOwnership` | Lanza código 403 ("otro artesano") | ✅ PASS |
| 89 | Intento de cambio de estado (HTTP) | Ana intentando modificar Pedido #1 | `POST /api/pedidos/cambiar-estado.php` | 403 Forbidden | ✅ PASS |
| 90 | Intento de cancelación (HTTP) | Ana intentando cancelar Pedido #1 | `POST /api/pedidos/cancelar.php` | 403 Forbidden | ✅ PASS |
| 91-92 | Registro manual para pieza ajena | Ana creando encargo para Pieza #1 | `POST /api/pedidos/crear.php` | 403 Forbidden ("creaciones de otros artesanos") | ✅ PASS |
| 93-94 | Aislamiento en listado de pedidos | Ana consultando `pedidos/index.php` | `GET /api/pedidos/index.php` | 200 OK conteniendo solo pedidos de sus piezas | ✅ PASS |
| 95-96 | Visibilidad global de Admin | Admin consultando `pedidos/index.php` | `GET /api/pedidos/index.php` | 200 OK conteniendo pedidos de múltiples artesanos | ✅ PASS |

### Sección 5: Salvaguardas Inmutables de Cuenta Raíz y Auto-eliminación

| # | Caso de Prueba Evaluado | Mecanismo | Entrada / Parámetros | Comportamiento Verificado | Estado |
| :-: | :--- | :--- | :--- | :--- | :-: |
| 97-98 | Degradación de rol de ID #1 | En memoria | `updateRole(1, 'artesano')` | Lanza excepción código 403 | ✅ PASS |
| 99 | Degradación de rol de ID #1 | API HTTP | `POST /api/usuarios/cambiar-rol.php` | 403 Forbidden | ✅ PASS |
| 100-101 | Eliminación de ID #1 | En memoria | `deleteUser(1, 1)` | Lanza excepción código 403 | ✅ PASS |
| 102 | Eliminación de ID #1 | API HTTP | `POST /api/usuarios/eliminar.php` | 403 Forbidden | ✅ PASS |
| 103-104 | Verificación en SQLite | Base de datos | Consulta directa a tabla `usuarios` | `rol = 'admin'` y `activo = 1` inalterados | ✅ PASS |
| 105-106 | Auto-eliminación accidental | API HTTP | Admin secundario eliminando su ID | 403 Forbidden ("propia cuenta") | ✅ PASS |
| 107 | Purga administrativa limpia | API HTTP | Admin ID #1 eliminando al admin temporal | 200 OK | ✅ PASS |

### Sección 6: Aislamiento de Creaciones Inactivas y Usuarios Desactivados

| # | Caso de Prueba Evaluado | Mecanismo | Endpoint / Acción | Comportamiento Verificado | Estado |
| :-: | :--- | :--- | :--- | :--- | :-: |
| 108-109 | Detalle público de pieza inactiva | API HTTP | `GET /api/creaciones/detalle.php?id=X` | 404 Not Found (`exito = false`) | ✅ PASS |
| 110-111 | Exclusión de catálogo público | API HTTP | `GET /api/creaciones/index.php?busqueda=...` | 200 OK con 0 resultados para piezas con `activo = 0` | ✅ PASS |
| 112 | Estado en base de datos de usuario inactivo | SQLite | Consulta directa | `activo = 0` | ✅ PASS |
| 113-114 | Intento de login con cuenta inactiva | En memoria / API | `AuthService::authenticate` | 401 Unauthorized sin enumeración de cuentas | ✅ PASS |
| 115 | Revocación inmediata de token activo | En memoria | `AuthService::validateToken` | Retorna estrictamente `null` (sesión revocada) | ✅ PASS |

### Sección 7: Restricción Estricta de Métodos HTTP (405 Method Not Allowed)

| # | Endpoint Auditado | Método Invocado | Método Requerido | Código HTTP Verificado | Estado |
| :-: | :--- | :---: | :---: | :---: | :-: |
| 116-117 | `/api/auth/login.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 118-119 | `/api/auth/logout.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 120-121 | `/api/auth/cambiar-password.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 122-123 | `/api/creaciones/crear.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 124-125 | `/api/creaciones/actualizar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 126-127 | `/api/creaciones/eliminar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 128-129 | `/api/creaciones/restaurar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 130-131 | `/api/creaciones/ajustar-stock.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 132-133 | `/api/creaciones/toggle-encargo.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 134-135 | `/api/pedidos/solicitar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 136-137 | `/api/pedidos/crear.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 138-139 | `/api/pedidos/cambiar-estado.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 140-141 | `/api/pedidos/cancelar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 142-143 | `/api/usuarios/crear.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 144-145 | `/api/usuarios/cambiar-rol.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 146-147 | `/api/usuarios/actualizar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 148-149 | `/api/usuarios/restablecer-password.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 150-151 | `/api/usuarios/eliminar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 152-153 | `/api/usuarios/reactivar.php` | GET | POST | 405 Method Not Allowed | ✅ PASS |
| 154-155 | `/api/auth/me.php` | POST | GET | 405 Method Not Allowed | ✅ PASS |
| 156-157 | `/api/creaciones/index.php` | POST | GET | 405 Method Not Allowed | ✅ PASS |
| 158-159 | `/api/creaciones/artesanos.php` | POST | GET | 405 Method Not Allowed | ✅ PASS |
| 160-161 | `/api/creaciones/detalle.php?id=1` | POST | GET | 405 Method Not Allowed | ✅ PASS |
| 162-163 | `/api/pedidos/index.php` | POST | GET | 405 Method Not Allowed | ✅ PASS |
| 164-165 | `/api/usuarios/index.php` | POST | GET | 405 Method Not Allowed | ✅ PASS |

---

## 3. Evidencias de Ejecución

### 3.1 Salida Resumida de la Suite CLI (`logs/subfase-3.6.1-cli.log`)
```
================================================================================
  SUITE DE PRUEBAS: Subfase 3.6.1: Acceso, Autorización, IDOR & Blindaje RBAC (OWASP A01)
  Iniciada: 2026-09-12 17:35:50 | PHP 8.3.29 | OS: Darwin
================================================================================
...
================================================================================
  RESUMEN DE PRUEBAS: Subfase 3.6.1: Acceso, Autorización, IDOR & Blindaje RBAC (OWASP A01)
--------------------------------------------------------------------------------
  Total Aserciones: 165
  Exitosas:         165
  Fallidas:         0
  Tiempo Total:     412.66 ms
================================================================================

  ✔ TODAS LAS PRUEBAS PASARON EXITOSAMENTE (100% OK)
```

### 3.2 Extracto de Trazas HTTP Curl (`logs/subfase-3.6.1-http.log`)
```http
--------------------------------------------------------------------------------
[1. GET /api/auth/me.php (Petición Anónima sin Token: HTTP 401)]
HTTP Status: 401
Response Body:
{"exito":false,"error":{"codigo":401,"mensaje":"Token de autenticación no proporcionado. Se requiere cabecera \"Authorization: Bearer <token>\"."}}

--------------------------------------------------------------------------------
[6. POST /api/creaciones/actualizar.php (IDOR Horizontal: Artesana Ana Actualizando Pieza #1 de Admin: HTTP 403)]
HTTP Status: 403
Response Body:
{"exito":false,"error":{"codigo":403,"mensaje":"Acceso denegado. No tienes permisos para gestionar la creación #1 (pertenece a otro artesano)."}}

--------------------------------------------------------------------------------
[13. POST /api/usuarios/cambiar-rol.php (Salvaguarda Administrador Raíz ID #1 contra Degradación: HTTP 403)]
HTTP Status: 403
Response Body:
{"exito":false,"error":{"codigo":403,"mensaje":"Operación denegada: La cuenta del administrador titular (ID #1) no puede ser degradada ni modificada."}}

--------------------------------------------------------------------------------
[15. GET /api/auth/login.php (Restricción de Método: GET en Endpoint POST-only: HTTP 405)]
HTTP Status: 405
Response Body:
{"exito":false,"error":{"codigo":405,"mensaje":"Método HTTP no permitido. Se requiere POST."}}
```

---

## 4. Estado del Sistema y Dictamen de Calidad

- **Blindaje OWASP A01:** Resuelto al 100% en todas las capas (Middleware `AuthGuard` / `RoleGuard`, Capa de Servicios con salvaguardas IDOR, Repositorios con aislamiento de autoría y Controladores con restricción de verbos HTTP).
- **Integridad de Base de Datos:** Los datos semilla (`admin`, `artesana_ana`, `asistente_leo`, 5 creaciones y 2 pedidos) permanecen inalterados y consistentes.
- **Dictamen:** ✅ **SUBFASE 3.6.1 APROBADA AL 100%**.

---

## 5. Compás de Espera Inviolable (Protocolo Iterativo)

En apego estricto a las directrices de desarrollo iterativo:
- Se detiene completamente la ejecución.
- Se presenta este reporte para revisión y dictamen del usuario.
- **Se solicita la aprobación explícita por escrito antes de iniciar la Subfase 3.6.2 (Criptografía, Autenticación & Protección de Datos Sensibles).**
