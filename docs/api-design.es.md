# 🧶 Manual Humano & Referencia de la API REST — Crochet Manager

Bienvenido al manual integral de la **API REST de Crochet Manager (Micro-ERP & Catálogo Textil)**. Este documento está redactado **a nivel humano**, diseñado para que desarrolladores frontend, artesanos de software, auditores y colaboradores técnicos comprendan con total claridad el funcionamiento, propósito de negocio, contratos de datos y reglas de seguridad de cada uno de los endpoints de la plataforma.

---

## 📑 Tabla de Contenidos

1. [Filosofía y Arquitectura de la API](#1-filosofía-y-arquitectura-de-la-api)
2. [Estándares Técnicos Globales](#2-estándares-técnicos-globales)
   - [2.1 URL Base y Entorno de Desarrollo](#21-url-base-y-entorno-de-desarrollo)
   - [2.2 Envolvente Homogénea de Respuesta (`App\Core\Response`)](#22-envolvente-homogénea-de-respuesta-appcoreresponse)
   - [2.3 Catálogo de Códigos de Estado HTTP](#23-catálogo-de-códigos-de-estado-http)
   - [2.4 Negociación CORS y Peticiones Preflight (`OPTIONS`)](#24-negociación-cors-y-peticiones-preflight-options)
   - [2.5 Estándar Monetario Dual (Centavos Enteros en SQLite $\leftrightarrow$ Pesos MXN)](#25-estándar-monetario-dual-centavos-enteros-en-sqlite-leftrightarrow-pesos-mxn)
   - [2.6 Paginación Estandarizada (`App\Utils\PaginationHelper`)](#26-paginación-estandarizada-apputilspaginationhelper)
   - [2.7 Autenticación Stateless Bearer (HMAC-SHA256)](#27-autenticación-stateless-bearer-hmac-sha256)
   - [2.8 Control de Acceso Basado en Roles (RBAC)](#28-control-de-acceso-basado-en-roles-rbac)
   - [2.9 Cero Fugas de HTML (`App\Core\ErrorHandler`)](#29-cero-fugas-de-html-appcoreerrorhandler)
   - [2.10 Estándar Universal de Borrado Lógico (Cero Eliminaciones Físicas)](#210-estándar-universal-de-borrado-lógico-cero-eliminaciones-físicas)
3. [Módulo 1: Autenticación & Sesión (`api/auth/`)](#3-módulo-1-autenticación--sesión-apiauth)
   - [`POST /api/auth/login.php` — Iniciar Sesión](#post-apiauthloginphp--iniciar-sesión)
   - [`POST /api/auth/logout.php` — Cerrar Sesión](#post-apiauthlogoutphp--cerrar-sesión)
   - [`GET /api/auth/me.php` — Consultar Perfil Activo](#get-apiauthmephp--consultar-perfil-activo)
   - [`POST /api/auth/cambiar-password.php` — Cambiar Contraseña Propia](#post-apiauthcambiar-passwordphp--cambiar-contraseña-propia)
4. [Módulo 2: Directorio de Creadores & Roles RBAC (`api/usuarios/`)](#4-módulo-2-directorio-de-creadores--roles-rbac-apiusuarios)
   - [`GET /api/usuarios/index.php` — Directorio de Creadores & Filtro de Estado](#get-apiusuariosindexphp--directorio-de-creadores--filtro-de-estado)
   - [`POST /api/usuarios/crear.php` — Registrar Nuevo Creador](#post-apiusuarioscrearphp--registrar-nuevo-creador)
   - [`POST /api/usuarios/cambiar-rol.php` — Modificar Rol & Salvaguarda ID #1](#post-apiusuarioscambiar-rolphp--modificar-rol--salvaguarda-id-1)
   - [`POST /api/usuarios/actualizar.php` — Modificar Nombre de Usuario / Perfil](#post-apiusuariosactualizarphp--modificar-nombre-de-usuario--perfil)
   - [`POST /api/usuarios/restablecer-password.php` — Restaurar Contraseña (Recuperación Administrativa)](#post-apiusuariosrestablecer-passwordphp--restaurar-contraseña-recuperación-administrativa)
   - [`POST /api/usuarios/eliminar.php` — Eliminar Usuario (Baja Lógica)](#post-apiusuarioseliminarphp--eliminar-usuario-baja-lógica)
   - [`POST /api/usuarios/reactivar.php` — Reactivar Cuenta de Usuario](#post-apiusuariosreactivarphp--reactivar-cuenta-de-usuario)
5. [Módulo 3: Catálogo, Creaciones & Control de Inventario (`api/creaciones/`)](#5-módulo-3-catálogo-creaciones--control-de-inventario-apicreaciones)
   - [`GET /api/creaciones/index.php` — Listar Catálogo Público con Filtros & KPIs](#get-apicreacionesindexphp--listar-catálogo-público-con-filtros--kpis)
   - [`GET /api/creaciones/artesanos.php` — Directorio Público de Creadores para Filtro](#get-apicreacionesartesanosphp--directorio-público-de-creadores-para-filtro)
   - [`GET /api/creaciones/detalle.php` — Ficha Técnica Completa](#get-apicreacionesdetallephp--ficha-técnica-completa)
   - [`POST /api/creaciones/crear.php` — Registrar Creación (Upload & Fallback SVG)](#post-apicreacionescrearphp--registrar-creación-upload--fallback-svg)
   - [`POST /api/creaciones/actualizar.php` — Editar Creación & Ciclo `unlink()`](#post-apicreacionesactualizarphp--editar-creación--ciclo-unlink)
   - [`POST /api/creaciones/eliminar.php` — Baja Lógica & Salvaguarda Referencial](#post-apicreacioneseliminarphp--baja-lógica--salvaguarda-referencial)
   - [`POST /api/creaciones/restaurar.php` — Restaurar Creación Archivada](#post-apicreacionesrestaurarphp--restaurar-creación-archivada)
   - [`POST /api/creaciones/ajustar-stock.php` — Ajuste Rápido In-Situ](#post-apicreacionesajustar-stockphp--ajuste-rápido-in-situ)
   - [`POST /api/creaciones/toggle-encargo.php` — Alternar Modalidad de Encargo](#post-apicreacionestoggle-encargophp--alternar-modalidad-de-encargo)
6. [Módulo 4: Pedidos, Encargos & Transacciones de Stock (`api/pedidos/`)](#6-módulo-4-pedidos-encargos--transacciones-de-stock-apipedidos)
   - [`POST /api/pedidos/solicitar.php` — Checkout Público de Clientes con Reserva Atómica](#post-apipedidossolicitarphp--checkout-público-de-clientes-con-reserva-atómica)
   - [`GET /api/pedidos/index.php` — Panel de Pedidos & Aislamiento por Creador](#get-apipedidosindexphp--panel-de-pedidos--aislamiento-por-creador)
   - [`POST /api/pedidos/crear.php` — Agendar Encargo Manual (WhatsApp / Feria)](#post-apipedidoscrearphp--agendar-encargo-manual-whatsapp--feria)
   - [`POST /api/pedidos/cambiar-estado.php` — Actualizar Confección y Cobro Tri-Estado](#post-apipedidoscambiar-estadophp--actualizar-confección-y-cobro-tri-estado)
   - [`POST /api/pedidos/cancelar.php` — Cancelación Idempotente con Restitución Física](#post-apipedidoscancelarphp--cancelación-idempotente-con-restitución-física)
7. [Guía Rápida para Desarrolladores Frontend (JavaScript Moderno)](#7-guía-rápida-para-desarrolladores-frontend-javascript-moderno)

---

## 1. Filosofía y Arquitectura de la API

La API de **Crochet Manager** sigue los principios de **Clean Architecture**:

```
[ Cliente Web / Móvil / Postman ]
                │  (HTTP + JSON / FormData + Bearer Token)
                ▼
      [ api/ (Thin Controllers) ]  <-- Validan método HTTP, CORS y delegan inmediatamente
                │
         [ Middleware ]             <-- AuthGuard (Bearer 401) / RoleGuard (RBAC 403)
                │
          [ Services/ ]             <-- Reglas de negocio, transacciones atómicas, validaciones
                │
        [ Repositories/ ]           <-- 100% SQL aislado mediante Prepared Statements PDO
                │
    [ SQLite database.sqlite ]      <-- PRAGMA foreign_keys = ON, CHECK constraints, índices
```

- **Aislamiento Físico Total:** El directorio `src/` está reservado exclusivamente para frontend (`src/css/`, `src/js/`) y contiene **0 archivos PHP**. Todo el código backend reside bajo `app/` (infraestructura, servicios, repositorios) y `api/` (controladores delgados).
- **Controladores Delgados (Thin Controllers):** Los archivos dentro de `api/` nunca ejecutan sentencias SQL directas ni implementan lógica compleja de negocio; su única misión es recibir la petición, verificar el método HTTP, llamar al middleware correspondiente, invocar la capa de servicio y devolver una respuesta estructurada con `App\Core\Response`.
- **Diseñado para Humanos:** Cada respuesta de error indica claramente qué campo falló, por qué falló y qué código de estado HTTP corresponde a la situación.

---

## 2. Estándares Técnicos Globales

### 2.1 URL Base y Entorno de Desarrollo
En el servidor local de desarrollo, todos los endpoints cuelgan de:
```text
http://localhost:8000
```
Ejemplo de llamada completa: `http://localhost:8000/api/auth/login.php`.

---

### 2.2 Envolvente Homogénea de Respuesta (`App\Core\Response`)
Para evitar que el cliente tenga que adivinar la forma del JSON según el endpoint, **el 100% de las respuestas siguen una envoltura unificada en español**:

#### Estructura Exitosa (HTTP 200 OK / 201 Created)
```json
{
  "exito": true,
  "mensaje": "Mensaje legible explicando qué se realizó con éxito.",
  "datos": { ... },
  "paginacion": {
    "total_items": 48,
    "pagina_actual": 1,
    "total_paginas": 4,
    "limite": 12,
    "tiene_siguiente": true,
    "tiene_anterior": false
  }
}
```
> [!NOTE]
> La clave `paginacion` es opcional y solo se adjunta automáticamente cuando el endpoint devuelve un listado o colección paginada.

#### Estructura de Error Controlado (HTTP 400, 401, 403, 404, 405, 409, 422, 500)
```json
{
  "exito": false,
  "error": {
    "codigo": 422,
    "mensaje": "El nombre de la creación debe tener entre 2 y 100 caracteres.",
    "detalles": {
      "campo": "nombre",
      "longitud_recibida": 1
    }
  }
}
```

---

### 2.3 Catálogo de Códigos de Estado HTTP

| Código | Nombre Estándar | Cuándo se emite en Crochet Manager |
| :---: | :--- | :--- |
| **200** | `OK` | Consulta exitosa (`GET`), actualización completada (`POST`) o cierre de sesión. |
| **201** | `Created` | Creación exitosa de un nuevo recurso en base de datos (nueva pieza, nuevo pedido, nuevo usuario). |
| **204** | `No Content` | Respuesta exitosa inmediata para peticiones preflight CORS `OPTIONS`. |
| **400** | `Bad Request` | JSON malformado o cuerpo de petición ilegible. |
| **401** | `Unauthorized` | Falta la cabecera `Authorization`, el token es inválido, manipulado o ha expirado; o credenciales incorrectas en login. |
| **403** | `Forbidden` | El token es válido, pero el usuario no cuenta con el rol requerido (ej. un `artesano` intentando cambiar roles en `api/usuarios/`); o intento de alterar al admin raíz ID #1. |
| **404** | `Not Found` | El recurso solicitado (creación, pedido o usuario) no existe en la base de datos. |
| **405** | `Method Not Allowed` | Se invocó un endpoint con un método HTTP no permitido (ej. hacer `GET` en `api/auth/login.php` que exige `POST`). |
| **409** | `Conflict` | Conflicto de integridad referencial (ej. intentar eliminar una creación que tiene pedidos asociados en SQLite, bloqueada por `ON DELETE RESTRICT`). |
| **422** | `Unprocessable Entity` | Errores de validación semántica (ej. stock insuficiente, precios negativos, campos obligatorios vacíos). |
| **500** | `Internal Server Error` | Excepción no capturada en el servidor; capturada por `ErrorHandler` sin fugas de HTML. |

---

### 2.4 Negociación CORS y Peticiones Preflight (`OPTIONS`)
El método `Response::handleCors()` intercepta automáticamente cualquier petición con método `OPTIONS` y responde de inmediato con **HTTP 204 No Content**, enviando las cabeceras requeridas para permitir peticiones cross-origin desde clientes web (SPAs, React, Vue o aplicaciones móviles):
```http
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Max-Age: 86400
```

---

### 2.5 Estándar Monetario Dual (Centavos Enteros en SQLite $\leftrightarrow$ Pesos MXN)
Para garantizar la máxima precisión contable y **erradicar al 100% las imprecisiones de coma flotante** (como `$12.30000000000004`):
1. **En Base de Datos SQLite:** Todos los campos financieros (`precio`, `costo_materiales`, `precio_final`) se almacenan estrictamente como **números enteros en centavos**. Ejemplo: `$450.00 MXN` se guarda como `45000`.
2. **En las Respuestas JSON:** La API enriquece automáticamente las respuestas a través de `App\Utils\CurrencyHelper`, entregando tanto el valor entero (para cálculos matemáticos o filtros del cliente) como la cadena formateada en pesos mexicanos (para pintar directamente en el DOM):
```json
{
  "precio": 45000,
  "precio_formateado": "$450.00 MXN",
  "costo_materiales": 12000,
  "costo_formateado": "$120.00 MXN",
  "margen_bruto_porcentaje": 73.33,
  "retorno_por_hora_formateado": "$50.77 MXN/h"
}
```

---

### 2.6 Paginación Estandarizada (`App\Utils\PaginationHelper`)
Todas las colecciones admiten los parámetros URL `pagina` y `limite`:
- **Catálogo de Creaciones (`api/creaciones/`):** Límite por defecto de **12 piezas** por página (óptimo para cuadrículas responsivas de 1, 2, 3 y 4 columnas en desktop y móvil).
- **Dashboard de Pedidos (`api/pedidos/`):** Límite por defecto de **20 pedidos** por página.
- **Directorio de Creadores (`api/usuarios/`):** Límite por defecto de **20 usuarios** por página.

Ejemplo de llamada: `GET /api/creaciones/index.php?pagina=2&limite=12`.

---

### 2.7 Autenticación Stateless Bearer (HMAC-SHA256)
- La API no depende de sesiones PHP en disco para sus endpoints REST; utiliza **Tokens Bearer firmados criptográficamente**.
- **Algoritmo:** HMAC-SHA256 con clave secreta del servidor (`Config::get('auth.secret_key')`).
- **Estructura del Token:** `payloadBase64Url.firmaHexadecimal`
  - Contiene: `sub` (ID del usuario), `username`, `rol`, `iat` (timestamp de emisión) y `exp` (timestamp de caducidad).
- **Vigencia:** 86,400 segundos (**24 horas**).
- **Resistencia a Ataques:** Las firmas se comparan en tiempo constante mediante `hash_equals()`.
- **Cabecera Requerida en Peticiones Protegidas:**
  ```http
  Authorization: Bearer <token_aqui>
  ```
- **Compatibilidad con Apache y FastCGI:** El archivo `.htaccess` del proyecto incluye la directiva `SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1`, garantizando que la cabecera llegue intacta al entorno PHP sin ser eliminada por el servidor web.

---

### 2.8 Control de Acceso Basado en Roles (RBAC)
La plataforma reconoce tres roles con jerarquías y privilegios estrictos:

```
[ admin ]      --> Acceso total (Gestión de usuarios, cambio de roles, catálogo, pedidos)
    │
[ artesano ]   --> Gestión de catálogo propio, inventario, stock y pedidos asignados
    │
[ asistente ]  --> Consulta de catálogo e inventario, registro de encargos (sin borrado)
```

- **Salvaguarda de la Cuenta Raíz (ID #1):** El usuario administrador titular (`id: 1`, `@admin`) cuenta con una salvaguarda inviolable tanto en la base de datos como en `UsuarioRepository` y `RoleGuard`: **bajo ninguna circunstancia puede ser degradado a otro rol ni eliminado del sistema**.

---

### 2.9 Cero Fugas de HTML (`App\Core\ErrorHandler`)
En sistemas PHP tradicionales, un error inesperado puede arrojar una traza HTML fea que corrompe el JSON en el frontend. En Crochet Manager:
1. `ErrorHandler::register()` captura cualquier `Notice`, `Warning`, `Fatal Error` o `Exception`.
2. Ejecuta `while (ob_get_level() > 0) ob_end_clean();` para purgar cualquier salida parcial del búfer.
3. Devuelve inmediatamente un código **HTTP 500** con JSON estructurado. Ningún tag `<br>` o HTML sale jamás de la API.

---

### 2.10 Estándar Universal de Borrado Lógico (Cero Eliminaciones Físicas)
Como directriz arquitectural absoluta en toda la base de datos y la API:
- **Prohibición de Eliminación Física:** Ninguna entidad (`usuarios`, `creaciones`, `pedidos`) ejecuta sentencias destructivas `DELETE FROM`.
- **Baja Lógica Transparente:** Todas las eliminaciones se efectúan mediante:
  ```sql
  UPDATE <table> SET activo = 0, eliminado_en = datetime('now', 'localtime') WHERE id = :id AND activo = 1;
  ```
- **Integridad y Trazabilidad Histórica:** Los pedidos pasados, las creaciones previas y la autoría de los artesanos se preservan de forma inmutable, protegiendo las relaciones foráneas (`ON DELETE RESTRICT`) y la trazabilidad contable.
- **Filtro Activo por Defecto:** Las consultas de catálogo, listados de panel, autenticación y resolución de tokens filtran de manera obligatoria y por defecto `activo = 1`.
- **Invalidación Inmediata de Tokens:** Si una cuenta de usuario es desactivada lógicamente, cualquier Bearer Token previamente emitido es revocado de facto en la siguiente petición protegida, respondiendo con **HTTP 401 Unauthorized** sin requerir esperar a la expiración de las 24 horas del TTL.
- **Detección de Re-eliminación / Recursos Inactivos:** Intentar dar de baja un registro previamente desactivado emite un error **HTTP 409 Conflict** descriptivo.

---

## 3. Módulo 1: Autenticación & Sesión (`api/auth/`)

Este módulo gestiona el ciclo de vida del acceso seguro a la plataforma.

---

### `POST /api/auth/login.php` — Iniciar Sesión

- **Propósito:** Autentica a un usuario mediante sus credenciales (nombre de usuario y contraseña en texto plano) y emite un token Bearer HMAC-SHA256 con vigencia de 24 horas.
- **Acceso:** **Público** (accesible desde el modal de login en el navbar).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `username` | `string` | **Sí** | 3 a 50 caracteres, no vacío. | Nombre de usuario registrado (ej. `admin`, `artesana_ana`). |
| `password` | `string` | **Sí** | No vacío. | Contraseña en texto plano a verificar mediante bcrypt. |

#### Reglas de Negocio & Seguridad
1. **Mitigación de Timing Attacks:** Si el usuario no existe en la base de datos, el backend ejecuta un `password_verify` contra un hash bcrypt dummy precalculado, asegurando que el tiempo de respuesta sea constante y previniendo la enumeración de nombres de usuario por análisis de tiempos.
2. **Exclusión de Hash:** La respuesta jamás expone la columna `password_hash`.

#### Ejemplo de Petición (cURL)
```bash
curl -X POST http://localhost:8000/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin123"}'
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Autenticación exitosa. Token emitido.",
  "datos": {
    "token": "eyJzdWIiOjEsInVzZXJuYW1lIjoiYWRtaW4iLCJyb2wiOiJhZG1pbiIsImlhdCI6MTc4OTIzNTMwOCwiZXhwIjoxNzg5MzIxNzA4LCJqdGkiOiJiMWE3MDMxNTlmOWRkZTY2ZTA1ODI2MDZmNmVjNjQyZCJ9.5bb046f117afb512766d72e5ba3ec04c38c3df381b04749e80bae83d1ca67371",
    "tipo_token": "Bearer",
    "expira_en": 86400,
    "usuario": {
      "id": 1,
      "username": "admin",
      "rol": "admin",
      "creado_en": "2026-09-11 13:30:52"
    }
  }
}
```

#### Respuestas de Error
- **HTTP 401 Unauthorized (Credenciales Incorrectas):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 401,
      "mensaje": "Credenciales de acceso incorrectas."
    }
  }
  ```
- **HTTP 422 Unprocessable Entity (Campos Vacíos):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 422,
      "mensaje": "El nombre de usuario y la contraseña son obligatorios."
    }
  }
  ```
- **HTTP 405 Method Not Allowed (Si se intenta llamar con `GET`):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 405,
      "mensaje": "Método HTTP no permitido. Se requiere POST."
    }
  }
  ```

---

### `POST /api/auth/logout.php` — Cerrar Sesión

- **Propósito:** Proporciona un acuse de recibo estándar de cierre de sesión. Al tratarse de una arquitectura stateless (sin estado en servidor), el cliente es instruido para destruir y descartar el token almacenado en `localStorage` o `sessionStorage`.
- **Acceso:** **Público / Autenticado**
- **Método HTTP:** `POST`

#### Ejemplo de Petición (cURL)
```bash
curl -X POST http://localhost:8000/api/auth/logout.php
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Sesión cerrada exitosamente. Descarte el token del cliente.",
  "datos": null
}
```

---

### `GET /api/auth/me.php` — Consultar Perfil Activo

- **Propósito:** Retorna la información de identidad segura del usuario actualmente autenticado a partir de su Bearer Token. El frontend utiliza este endpoint al cargar la página para saber si la sesión sigue activa y qué rol tiene el usuario.
- **Acceso:** **Protegido** (`AuthGuard: admin, artesano, asistente`).
- **Método HTTP:** `GET`
- **Cabecera Requerida:** `Authorization: Bearer <token>`

#### Ejemplo de Petición (cURL)
```bash
curl -X GET http://localhost:8000/api/auth/me.php \
  -H "Authorization: Bearer <tu_token_aqui>"
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Perfil de usuario recuperado exitosamente.",
  "datos": {
    "id": 1,
    "username": "admin",
    "rol": "admin",
    "creado_en": "2026-09-11 13:30:52"
  }
}
```

#### Respuestas de Error
- **HTTP 401 Unauthorized (Sin Cabecera Authorization):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 401,
      "mensaje": "Token de autenticación no proporcionado. Se requiere cabecera \"Authorization: Bearer <token>\"."
    }
  }
  ```
- **HTTP 401 Unauthorized (Token Expirado o Manipulado):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 401,
      "mensaje": "Token de autenticación inválido, manipulado o expirado."
    }
  }
  ```

---

### `POST /api/auth/cambiar-password.php` — Cambiar Contraseña Propia

- **Propósito:** Permite a cualquier usuario autenticado (`admin`, `artesano`, `asistente`) actualizar su propia contraseña de acceso de forma autónoma. Valida la contraseña actual contra el hash bcrypt en SQLite y exige que la nueva clave cumpla con un mínimo de 6 caracteres.
- **Acceso:** **Protegido** (`AuthGuard: cualquier usuario autenticado activo`).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `password_actual` | `string` | **Sí** | Texto plano. Alias: `current_password`. | Contraseña vigente del usuario para verificar su identidad. |
| `nueva_password` | `string` | **Sí** | Mínimo 6 caracteres. Alias: `new_password`. | Nueva clave de acceso que reemplazará la anterior. |

#### Ejemplo de Petición (cURL)
```bash
curl -X POST http://localhost:8000/api/auth/cambiar-password.php \
  -H "Authorization: Bearer <tu_token_aqui>" \
  -H "Content-Type: application/json" \
  -d '{
    "password_actual": "mi_clave_vieja_123",
    "nueva_password": "MiNuevaClaveSegura2026!"
  }'
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Contraseña actualizada exitosamente.",
  "datos": {
    "id": 2,
    "username": "artesana_ana"
  }
}
```

#### Respuestas de Error Comunes
- **HTTP 401 Unauthorized (Contraseña actual incorrecta):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 401,
      "mensaje": "La contraseña actual es incorrecta."
    }
  }
  ```
- **HTTP 422 Unprocessable Entity (Nueva contraseña menor a 6 caracteres):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 422,
      "mensaje": "La nueva contraseña debe tener al menos 6 caracteres."
    }
  }
  ```
- **HTTP 422 Unprocessable Entity (Campos faltantes o vacíos):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 422,
      "mensaje": "La contraseña actual y la nueva contraseña son obligatorias."
    }
  }
  ```

---

## 4. Módulo 2: Directorio de Creadores & Roles RBAC (`api/usuarios/`)

Este módulo permite al Administrador gobernar la comunidad de artesanos y colaboradores de la plataforma.

> [!IMPORTANT]
> Todos los endpoints de este módulo exigen estrictamente el rol **`admin`** (`RoleGuard::adminOnly()`). Cualquier usuario con rol `artesano` o `asistente` recibe automáticamente **HTTP 403 Forbidden**.

---

### `GET /api/usuarios/index.php` — Directorio de Creadores

- **Propósito:** Retorna la lista paginada de todos los creadores y colaboradores registrados en la plataforma, calculando el número de creaciones que cada uno tiene asociadas en el catálogo.
- **Acceso:** **Protegido** (`RoleGuard: admin`).
- **Método HTTP:** `GET`
- **Cabecera Requerida:** `Authorization: Bearer <token>`
- **Query Params:** 
  - `?pagina=1&limite=20` (opcionales).
  - `?estado=activos|inactivos|todos` (opcional, default: `'activos'`). Permite filtrar solo usuarios activos, solo usuarios dados de baja lógica (`inactivos`) o el censo histórico completo (`todos`).

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Directorio de creadores obtenido exitosamente.",
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "rol": "admin",
      "activo": 1,
      "creado_en": "2026-09-11 13:30:52",
      "eliminado_en": null,
      "creaciones_asociadas": 3
    },
    {
      "id": 2,
      "username": "artesana_ana",
      "rol": "artesano",
      "activo": 1,
      "creado_en": "2026-09-11 13:30:52",
      "eliminado_en": null,
      "creaciones_asociadas": 2
    },
    {
      "id": 3,
      "username": "asistente_leo",
      "rol": "asistente",
      "activo": 1,
      "creado_en": "2026-09-11 13:30:52",
      "eliminado_en": null,
      "creaciones_asociadas": 0
    }
  ],
  "paginacion": {
    "total_items": 3,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 20,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

---

### `POST /api/usuarios/crear.php` — Registrar Nuevo Creador

- **Propósito:** Da de alta a una nueva artesana o colaborador en la plataforma, asignándole su rol inicial y encriptando su contraseña con bcrypt.
- **Acceso:** **Protegido** (`RoleGuard: admin`).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `username` | `string` | **Sí** | 3 a 50 car., alfanumérico/guiones, único en el sistema. | Nombre de usuario único para el login. |
| `password` | `string` | **Sí** | Mínimo 6 caracteres. | Contraseña en texto plano (se hashea con bcrypt en servidor). |
| `rol` | `string` | No | `'admin'`, `'artesano'`, `'asistente'` (Default: `'artesano'`). | Rol y nivel de permisos asignado. |

#### Respuesta Exitosa (HTTP 201 Created)
```json
{
  "exito": true,
  "mensaje": "Creador registrado exitosamente en la plataforma.",
  "datos": {
    "id": 4,
    "username": "artesano_carlos",
    "rol": "artesano",
    "creado_en": "2026-09-12 12:00:00"
  }
}
```

#### Respuestas de Error Comunes
- **HTTP 409 Conflict (Username ya ocupado):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 409,
      "mensaje": "El nombre de usuario 'artesano_carlos' ya se encuentra registrado."
    }
  }
  ```

---

### `POST /api/usuarios/cambiar-rol.php` — Modificar Rol & Salvaguarda ID #1

- **Propósito:** Cambia el rol de un usuario existente (promover o revocar privilegios).
- **Acceso:** **Protegido** (`RoleGuard: admin`).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | **Sí** | Entero mayor a 0. | ID del usuario a modificar. |
| `rol` | `string` | **Sí** | `'admin'`, `'artesano'`, `'asistente'`. | Nuevo rol a establecer. |

#### 🛡️ Salvaguarda Inviolable de Cuenta Raíz (ID #1)
Si la petición envía `id: 1` con cualquier rol distinto de `admin`, el sistema aborta de inmediato con **HTTP 403 Forbidden**:
```json
{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "Operación denegada: La cuenta del administrador titular (ID #1) no puede ser degradada ni modificada."
  }
}
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Rol de usuario actualizado exitosamente.",
  "datos": {
    "id": 3,
    "username": "asistente_leo",
    "rol": "artesano"
  }
}
```

---

### `POST /api/usuarios/actualizar.php` — Modificar Nombre de Usuario / Perfil

- **Propósito:** Permite al administrador actualizar el nombre de usuario de una artesana o colaborador (por ejemplo, corrección tipográfica, cambio de nombre artístico o actualización de identidad).
- **Acceso:** **Protegido** (`RoleGuard: admin`).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | **Sí** | Entero mayor a 0. | ID del usuario a modificar. |
| `username` | `string` | **Sí** | 3 a 50 caracteres, alfanumérico con guiones y puntos. | Nuevo nombre de usuario único. |

#### Reglas de Negocio & Seguridad
1. **Comprobación de Unicidad Excluyente:** El backend verifica que el nuevo nombre de usuario no pertenezca ya a otro usuario (`existsUsername($username, $id)`). Si está ocupado, responde con **HTTP 409 Conflict**.
2. **Validación Sintáctica:** Debe cumplir `^[a-zA-Z0-9_\-\.]+$` con longitud entre 3 y 50 caracteres.

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Nombre de usuario actualizado exitosamente.",
  "datos": {
    "id": 2,
    "username": "artesana_ana_talleres",
    "rol": "artesano"
  }
}
```

---

### `POST /api/usuarios/restablecer-password.php` — Restaurar Contraseña (Recuperación Administrativa)

- **Propósito:** Permite al administrador restaurar la contraseña de un usuario en caso de que este la haya olvidado o perdido. Si el administrador no especifica una contraseña manual, el sistema genera automáticamente una clave temporal segura lista para ser entregada al artesano por un canal privado (como WhatsApp).
- **Acceso:** **Protegido** (`RoleGuard: admin`).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | **Sí** | Entero mayor a 0. | ID del usuario cuya clave se restaurará. |
| `nueva_password` | `string` | No | Opcional. Mínimo 6 caracteres. Alias: `password`. | Nueva clave manual deseada. Si se omite o está vacía, el servidor autogenera una clave temporal segura. |

#### Comportamiento Inteligente de Generación
- Si se envía `"nueva_password": "MiNuevaClaveSegura2026"`, se valida su longitud y se aplica directamente.
- Si se omite `nueva_password`, el backend genera una contraseña segura tipo `Crochet!a8b9c0!` y la retorna en texto plano en la respuesta de éxito para que el administrador pueda copiarla y compartirla con el usuario.

#### Respuesta Exitosa (HTTP 200 OK — Clave Autogenerada)
```json
{
  "exito": true,
  "mensaje": "Contraseña restablecida exitosamente para el usuario 'artesana_ana'. Entregue la clave temporal al artesano: Crochet!a8b9c0!",
  "datos": {
    "id": 2,
    "username": "artesana_ana",
    "password_temporal": "Crochet!a8b9c0!",
    "es_autogenerada": true
  }
}
```

---

### `POST /api/usuarios/eliminar.php` — Eliminar Usuario & Salvaguardas

- **Propósito:** Da de baja a un creador o usuario del sistema, validando múltiples candados de seguridad e integridad referencial.
- **Acceso:** **Protegido** (`RoleGuard: admin`).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | **Sí** | Entero mayor a 0. | ID del usuario a eliminar. |

#### 🛡️ Candados de Seguridad y Salvaguardas
1. **Salvaguarda de Cuenta Raíz (ID #1):** Si se intenta eliminar al usuario ID #1 (`@admin`), la solicitud se rechaza con **HTTP 403 Forbidden**.
2. **Prevención de Auto-Eliminación:** El administrador no puede eliminar su propia cuenta mientras se encuentra en sesión activa (**HTTP 403 Forbidden**).
3. **Integridad Referencial en Catálogo:** Si el usuario tiene creaciones activas asociadas en la tabla `creaciones`, el servicio aborta la operación con **HTTP 409 Conflict** indicando cuántas piezas activas tiene registradas.
4. **Borrado Lógico Estricto:** La eliminación nunca destruye físicamente el registro. Modifica `activo = 0` y actualiza `eliminado_en = datetime('now', 'localtime')`.
5. **Detección de Cuenta Inactiva:** Si se intenta dar de baja a un usuario que ya fue eliminado previamente (`activo = 0`), el servicio responde con **HTTP 409 Conflict**.

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Usuario eliminado lógicamente de la plataforma.",
  "datos": {
    "id": 4,
    "activo": 0
  }
}
```

#### Respuestas de Error
- **HTTP 409 Conflict (Usuario ya inactivo o eliminado previamente):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 409,
      "mensaje": "El usuario ya se encuentra inactivo o fue eliminado previamente."
    }
  }
  ```
- **HTTP 409 Conflict (Tiene creaciones asociadas):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 409,
      "mensaje": "No se puede eliminar al usuario 'artesana_ana' porque tiene 2 creación(es) asociada(s) en el catálogo. Reasigne o elimine sus piezas antes de continuar."
    }
  }
  ```
- **HTTP 403 Forbidden (Intento de borrar al administrador titular):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 403,
      "mensaje": "Operación denegada: La cuenta del administrador titular (ID #1) no puede ser eliminada."
    }
  }
  ```

---

### `POST /api/usuarios/reactivar.php` — Reactivar Cuenta de Usuario

- **Propósito:** Permite al administrador restituir el estado activo (`activo = 1`) de una cuenta que fue previamente dada de baja lógica. Esto limpia `eliminado_en = NULL`, permitiendo que el usuario vuelva a iniciar sesión con sus credenciales y restableciendo la disponibilidad operativa sin violar la restricción `UNIQUE(username)` en SQLite.
- **Acceso:** **Protegido** (`RoleGuard: admin`).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Reglas & Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | **Sí** | Entero mayor a 0. | ID del usuario inactivo a reactivar. |

#### Ejemplo de Petición (cURL)
```bash
curl -X POST http://localhost:8000/api/usuarios/reactivar.php \
  -H "Authorization: Bearer <token_admin>" \
  -H "Content-Type: application/json" \
  -d '{"id": 4}'
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Cuenta de usuario reactivada exitosamente.",
  "datos": {
    "id": 4,
    "username": "artesano_carlos",
    "activo": 1
  }
}
```

#### Respuestas de Error Comunes
- **HTTP 404 Not Found (Usuario no encontrado):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 404,
      "mensaje": "Usuario no encontrado."
    }
  }
  ```
- **HTTP 409 Conflict (Usuario ya se encuentra activo):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 409,
      "mensaje": "El usuario ya se encuentra activo."
    }
  }
  ```
- **HTTP 403 Forbidden (Invocado por no-admin):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 403,
      "mensaje": "Acceso restringido: Se requieren privilegios de administrador."
    }
  }
  ```

---

## 5. Módulo 3: Catálogo, Creaciones & Control de Inventario (`api/creaciones/`)


Este módulo permite consultar el catálogo público, obtener fichas técnicas detalladas y realizar la gestión de piezas para artesanos autenticados.

---

### `GET /api/creaciones/index.php` — Listar Catálogo Público con Filtros

- **Propósito:** Obtiene el listado de creaciones artesanales con soporte para búsqueda reactiva, filtrado multieje y paginación.
- **Acceso:** **Público** (utilizado por `index.php` y `creaciones.php`).
- **Método HTTP:** `GET`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Default | Descripción |
| :--- | :---: | :---: | :--- |
| `pagina` | `int` | `1` | Número de página a recuperar. |
| `limite` | `int` | `12` | Cantidad de piezas por página. |
| `categoria` | `string` | — | Filtro exacto de categoría: `Amigurumis & Figuras`, `Prendas & Ropa`, `Bolsos & Accesorios`, `Hogar & Decoración`, `Bebé & Infantil`. |
| `artesano_id` | `int` | — | Filtra por el autor/artesano creador de la pieza. |
| `precio_min` | `int\|float`| — | Precio mínimo (admite pesos `$400` o centavos `40000`). |
| `precio_max` | `int\|float`| — | Precio máximo admitido. |
| `stock` | `string` | — | `'in'` (con existencias > 0), `'out'` (agotados = 0), `'on-demand'` (encargo = 1). |
| `buscar` | `string` | — | Búsqueda parcial (`LIKE %query%`) en nombre, material o descripción. |
| `orden` | `string` | `'recientes'`| `'precio_asc'`, `'precio_desc'`, `'nombre_asc'`, `'recientes'`. |

#### Ejemplo de Petición (cURL)
```bash
curl -X GET "http://localhost:8000/api/creaciones/index.php?categoria=Amigurumis%20%26%20Figuras&pagina=1&limite=6"
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Catálogo de creaciones recuperado exitosamente.",
  "datos": [
    {
      "id": 1,
      "artesano_id": 1,
      "artesano_nombre": "admin",
      "nombre": "Dragón Ignis",
      "categoria": "Amigurumis & Figuras",
      "material": "100% Algodón Mercerizado",
      "dimensiones": "18.5 cm (Alto)",
      "precio": 45000,
      "precio_formateado": "$450.00 MXN",
      "costo_materiales": 12000,
      "costo_formateado": "$120.00 MXN",
      "cantidad_stock": 4,
      "horas_tejido": 6.5,
      "descripcion": "Dragón mítico tejido con escamas en relieve y relleno hipoalergénico.",
      "imagen_url": "assets/svg/piezas/dragon-ignis.svg",
      "es_sobre_encargo": 0,
      "margen_bruto_porcentaje": 73.33,
      "retorno_por_hora_formateado": "$50.77 MXN/h",
      "creado_en": "2026-09-11 13:30:52",
      "actualizado_en": null
    }
  ],
  "paginacion": {
    "total_items": 5,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 6,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

---

### `GET /api/creaciones/artesanos.php` — Directorio Público de Creadores para Filtro

- **Propósito:** Provee la lista pública y ligera de artesanas y creadores activos que cuentan con al menos una creación publicada y activa en el catálogo. Este endpoint está específicamente diseñado para poblar dinámicamente el selector desplegable `#filterArtisan` en la barra de filtros del catálogo público (`index.php`), sin exponer rutas administrativas ni datos privados.
- **Acceso:** **Público** (no requiere token de sesión).
- **Método HTTP:** `GET`

#### Ejemplo de Petición (cURL)
```bash
curl -X GET http://localhost:8000/api/creaciones/artesanos.php
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Listado de creadores con piezas activas obtenido exitosamente.",
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "total_creaciones": 3
    },
    {
      "id": 2,
      "username": "artesana_ana",
      "total_creaciones": 2
    }
  ]
}
```

---

### `GET /api/creaciones/detalle.php` — Ficha Técnica Completa

- **Propósito:** Devuelve la información técnica minuciosa de una pieza específica, incluyendo datos del creador, medidas, fibras, cuidados de lavado y métricas económicas.
- **Acceso:** **Público** (usado por `detalle.php` y el modal de inspección).
- **Método HTTP:** `GET`
- **Query Params:** `?id=1` (**Obligatorio**, entero).

#### Ejemplo de Petición (cURL)
```bash
curl -X GET "http://localhost:8000/api/creaciones/detalle.php?id=1"
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Ficha técnica obtenida con éxito.",
  "datos": {
    "id": 1,
    "artesano_id": 1,
    "artesano_nombre": "admin",
    "nombre": "Dragón Ignis",
    "categoria": "Amigurumis & Figuras",
    "material": "100% Algodón Mercerizado",
    "dimensiones": "18.5 cm (Alto)",
    "precio": 45000,
    "precio_formateado": "$450.00 MXN",
    "costo_materiales": 12000,
    "costo_formateado": "$120.00 MXN",
    "cantidad_stock": 4,
    "horas_tejido": 6.5,
    "descripcion": "Dragón mítico con escamas en relieve y ojos de seguridad.",
    "imagen_url": "assets/svg/piezas/dragon-ignis.svg",
    "es_sobre_encargo": 0,
    "margen_bruto_porcentaje": 73.33,
    "retorno_por_hora_formateado": "$50.77 MXN/h",
    "creado_en": "2026-09-11 13:30:52"
  }
}
```

---

### `POST /api/creaciones/crear.php` — Registrar Creación (Upload & Fallback SVG)

- **Propósito:** Permite a una artesana registrar una nueva pieza en su catálogo. Soporta subida de archivos fotográficos reales o asignación automática de gráficos SVG temáticos cuando no se proporciona fotografía.
- **Acceso:** **Protegido** (`RoleGuard: admin, artesano`).
- **Método HTTP:** `POST`
- **Tipo de Contenido:** `multipart/form-data`
- **Cabecera Requerida:** `Authorization: Bearer <token>`

#### Campos del Formulario (`multipart/form-data`)

| Campo | Tipo | Obligatorio | Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `nombre` | `string` | **Sí** | 2 a 100 caracteres. | Nombre comercial de la pieza. |
| `categoria` | `string` | **Sí** | 2 a 50 caracteres. | Una de las categorías oficiales de la plataforma. |
| `material` | `string` | **Sí** | 3 a 80 caracteres. | Fibras textiles utilizadas (ej. `100% Algodón Mercerizado`). |
| `dimensiones`| `string` | **Sí** | 2 a 100 caracteres. | Especificación dimensional (ej. `18.5 cm (Alto)` o `35x30 cm`). |
| `precio` | `numeric`| **Sí** | Entero en centavos (`45000`) o flotante en pesos (`450.00`). | Precio de venta final al público. |
| `costo_materiales` | `numeric` | No | $\ge 0$ (Default: `0`). | Inversión directa en hilos y forros. |
| `cantidad_stock` | `int` | No | $\ge 0$ a $10,000$ (Default: `0`). | Inventario físico terminado disponible para despacho inmediato. |
| `horas_tejido` | `float` | No | $0.0$ a $500.0$ (Default: `0.0`). | Horas dedicadas para calcular el retorno de labor. |
| `descripcion` | `string` | No | Máximo 2,000 caracteres. | Reseña o historia artesanal de la pieza. |
| `es_sobre_encargo` | `int` | No | `0` o `1` (Default: `0`). | Si se elabora exclusivamente bajo pedido. |
| `imagen` | `file` | No | JPG, PNG o WebP; máx. 5 MB. | Archivo fotográfico real de la pieza. |

#### 🎨 Regla del Fallback Automático SVG
Si el creador no sube un archivo fotográfico en el campo `imagen`, `CreacionService` analiza la categoría y el nombre asignado, vinculando automáticamente un vector temático optimizado desde `assets/svg/piezas/` (ej. `assets/svg/piezas/dragon-ignis.svg` o `cardigan-granny.svg`), evitando imágenes rotas o marcadores de posición genéricos.

#### Respuesta Exitosa (HTTP 201 Created)
```json
{
  "exito": true,
  "mensaje": "Creación artesanal registrada exitosamente en el catálogo.",
  "datos": {
    "id": 6,
    "nombre": "Cardigan Granny Square Bohemio",
    "imagen_url": "uploads/crochet_cardigan_66e01a8f.webp",
    "precio_formateado": "$850.00 MXN"
  }
}
```

---

### `POST /api/creaciones/actualizar.php` — Editar Creación & Ciclo `unlink()`

- **Propósito:** Modifica los atributos de una creación existente. Si se adjunta una nueva imagen, el backend borra la imagen anterior del disco físico (`uploads/`) usando la función nativa `unlink()` de PHP para evitar almacenar archivos huérfanos.
- **Acceso:** **Protegido** (`RoleGuard: admin` o el artesano autor de la pieza).
- **Tipo de Contenido:** `multipart/form-data` o `application/json` (si no se actualiza imagen).
- **Parámetro Clave:** `id` (entero obligatorio).

#### 🛡️ Candados de Seguridad, Multi-Autoría (IDOR) & Ciclo de Imágenes
1. **Prevención de Ataques IDOR (Control de Autoría):** Una artesana solo tiene potestad para editar sus propias creaciones (`creaciones.artesano_id === currentUserId`). Si intenta modificar una creación perteneciente a otro artesano, el servicio responde de inmediato con **HTTP 403 Forbidden**. El rol `admin` tiene permiso global para editar cualquier pieza.
2. **Ciclo de Vida de Archivos Físicos (`unlink()`):** La eliminación del archivo previo en disco (`uploads/`) se ejecuta **exclusivamente** si se sube con éxito una nueva imagen válida que reemplace a la existente. Si no se sube imagen nueva, la imagen previa se conserva intacta.

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Creación actualizada correctamente.",
  "datos": {
    "id": 6,
    "nombre": "Cardigan Granny Square Bohemio (Edición Otoño)"
  }
}
```

---

### `POST /api/creaciones/eliminar.php` — Baja Lógica & Salvaguardas Referenciales

- **Propósito:** Da de baja lógica una creación del inventario del taller.
- **Acceso:** **Protegido** (`RoleGuard: admin` o el creador autor de la pieza).
- **Método HTTP:** `POST`
- **Cuerpo (JSON):** `{"id": 6}`

#### 🛡️ Candados de Seguridad, Multi-Autoría & Preservación de Miniaturas
1. **Control de Autoría (IDOR):** Solo el creador que tejió la pieza o un administrador pueden darla de baja. Intentos cruzados responden con **HTTP 403 Forbidden**.
2. **Borrado Lógico Estricto:** La pieza nunca se destruye con `DELETE FROM`. Se marca `activo = 0` y `eliminado_en = datetime('now', 'localtime')`. Esto preserva el censo histórico y la integridad contable.
3. **Cero `unlink()` en Baja Lógica (Preservación de Fotos para Pedidos):** Al desactivar una creación, **NUNCA se borra el archivo fotográfico de `uploads/`**. Los pedidos históricos registrados en `pedidos` conservan la miniatura de la pieza en sus tarjetas de control.
4. **Salvaguarda de Pedidos Activos:** Si la creación está asociada a pedidos en estado `Pendiente` o `En Proceso`, el backend puede prevenir la baja retornando **HTTP 409 Conflict**:
```json
{
  "exito": false,
  "error": {
    "codigo": 409,
    "mensaje": "No se puede dar de baja la creación porque tiene pedidos activos en confección. Concluya o cancele los pedidos asociados primero."
  }
}
```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Creación dada de baja lógicamente del inventario.",
  "datos": {
    "id": 6,
    "activo": 0
  }
}
```

---

### `POST /api/creaciones/restaurar.php` — Restaurar Creación Archivada

- **Propósito:** Permite restituir una pieza previamente dada de baja lógica (`activo = 1`, `eliminado_en = NULL`), haciéndola visible nuevamente en el catálogo público e inventario activo.
- **Acceso:** **Protegido** (`RoleGuard: admin` o el creador autor de la pieza).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json` y `Authorization: Bearer <token>`
- **Cuerpo (JSON):** `{"id": 6}`

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Creación restaurada exitosamente en el catálogo.",
  "datos": {
    "id": 6,
    "nombre": "Cardigan Granny Square Bohemio",
    "activo": 1
  }
}
```

#### Respuestas de Error Comunes
- **HTTP 404 Not Found:** La creación no existe.
- **HTTP 409 Conflict:** La creación ya se encuentra activa en el catálogo.
- **HTTP 403 Forbidden:** Intento de restaurar una pieza de otra artesana sin rol admin.

---

### `POST /api/creaciones/ajustar-stock.php` — Ajuste Rápido In-Situ

- **Propósito:** Permite a la artesana incrementar o decrementar el stock (`+1` o `-1`) con un solo clic desde las tarjetas del panel de control sin abrir el formulario completo.
- **Acceso:** **Protegido** (`RoleGuard: admin, artesano`).
- **Método HTTP:** `POST`
- **Cuerpo (JSON):**
  ```json
  {
    "id": 1,
    "delta": 1
  }
  ```

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Stock actualizado correctamente.",
  "datos": {
    "id": 1,
    "cantidad_stock": 5
  }
}
```

---

### `POST /api/creaciones/toggle-encargo.php` — Alternar Modalidad de Encargo

- **Propósito:** Alterna el interruptor `es_sobre_encargo` entre `0` (entrega inmediata sujeta a existencias) y `1` (elaboración exclusiva bajo encargo).
- **Acceso:** **Protegido** (`RoleGuard: admin, artesano`).
- **Método HTTP:** `POST`
- **Cuerpo (JSON):** `{"id": 1}`

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Modalidad de encargo actualizada.",
  "datos": {
    "id": 1,
    "es_sobre_encargo": 1
  }
}
```

---

## 6. Módulo 4: Pedidos, Encargos & Transacciones de Stock (`api/pedidos/`)

Este módulo orquesta la venta de piezas, la reserva inmediata de existencias y la coordinación de encargos personalizados.

---

### `POST /api/pedidos/solicitar.php` — Checkout Público de Clientes con Reserva Atómica

- **Propósito:** Procesa la compra de un cliente desde el modal público (`modal_checkout.php`).
- **Acceso:** **Público** (no requiere token de sesión).
- **Método HTTP:** `POST`
- **Cabecera Requerida:** `Content-Type: application/json`

#### Parámetros del Cuerpo (JSON)

| Campo | Tipo | Obligatorio | Restricciones | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `cliente_nombre` | `string` | **Sí** | 2 a 100 caracteres. | Nombre completo del comprador. |
| `cliente_contacto`| `string`| **Sí** | Hasta 50 caracteres. | Teléfono/WhatsApp para coordinar entrega. |
| `creacion_id` | `int` | **Sí** | Debe existir en catálogo. | ID de la creación solicitada. |
| `cantidad` | `int` | **Sí** | Mínimo 1, máximo stock disponible. | Cantidad de unidades a adquirir. |
| `fecha_entrega` | `string` | No | Formato `YYYY-MM-DD` (10 car.). | Fecha estimada o acordada. |
| `notas` | `string` | No | Máximo 1,000 caracteres. | Instrucciones especiales o envoltura. |

#### 🛡️ Reglas Financieras & Transacción Atómica SQLite (`BEGIN IMMEDIATE TRANSACTION`)
1. **El cliente NUNCA envía el precio:** Para prevenir manipulaciones maliciosas de precios, el backend ignora cualquier precio enviado por el cliente. Consulta `creaciones.precio` en SQLite y calcula en servidor:
   $$\text{precio\_final} = \text{precio} \times \text{cantidad}$$
2. **Reserva Atómica de Inventario:** Si la pieza no es sobre encargo (`es_sobre_encargo == 0`), se verifica que `cantidad_stock >= cantidad`. Si hay stock, se descuenta de forma atómica:
   ```sql
   UPDATE creaciones SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :id;
   ```
3. Si el stock no alcanza, la transacción ejecuta `ROLLBACK` y emite **HTTP 422**.

#### Ejemplo de Petición (cURL)
```bash
curl -X POST http://localhost:8000/api/pedidos/solicitar.php \
  -H "Content-Type: application/json" \
  -d '{
    "cliente_nombre": "Mariana Gómez",
    "cliente_contacto": "+52 55 4892 1039",
    "creacion_id": 1,
    "cantidad": 1,
    "notas": "Envoltura para obsequio artesanal"
  }'
```

#### Respuesta Exitosa (HTTP 201 Created)
```json
{
  "exito": true,
  "mensaje": "Su pedido ha sido registrado exitosamente y el stock ha sido reservado.",
  "datos": {
    "pedido_id": 7,
    "precio_final": 45000,
    "precio_final_formateado": "$450.00 MXN",
    "estado_pedido": "Pendiente",
    "estado_pago": "Pendiente"
  }
}
```

---

### `GET /api/pedidos/index.php` — Panel de Pedidos del Taller

- **Propósito:** Devuelve la lista paginada de encargos recibidos, facilitando el seguimiento del estado de confección y cobro.
- **Acceso:** **Protegido** (`RoleGuard: admin, artesano`).
- **Método HTTP:** `GET`
- **Query Params:**
  - `pagina` (int, default: 1)
  - `limite` (int, default: 20)
  - `estado` (`Pendiente`, `En Proceso`, `Entregado`, `Cancelado`)
  - `estado_pago` (`Pendiente`, `Anticipo 50%`, `Liquidado`)

#### 🛡️ Aislamiento de Pedidos Multi-Artesano
- **Rol `artesano`:** La consulta a SQLite aplica automáticamente un filtro forzado `WHERE c.artesano_id = :current_user_id`, garantizando que cada creador visualice y administre **única y exclusivamente los encargos de sus propias piezas tejidas**.
- **Rol `admin`:** Tiene visibilidad global de todos los encargos y transacciones de la plataforma.

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Pedidos recuperados exitosamente.",
  "datos": [
    {
      "id": 1,
      "cliente_nombre": "Mariana Gómez",
      "cliente_contacto": "+52 55 4892 1039",
      "creacion_id": 1,
      "creacion_nombre": "Dragón Ignis",
      "cantidad": 1,
      "fecha_entrega": "2026-09-24",
      "estado_pedido": "En Proceso",
      "estado_pago": "Anticipo 50%",
      "precio_final": 45000,
      "precio_final_formateado": "$450.00 MXN",
      "notas": "Detalles dorados en las alas",
      "creado_en": "2026-09-11 13:30:52",
      "actualizado_en": "2026-09-12 10:15:00"
    }
  ],
  "paginacion": {
    "total_items": 1,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 20,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

---

### `POST /api/pedidos/crear.php` — Agendar Encargo Manual (WhatsApp / Feria)

- **Propósito:** Permite a la artesana registrar un encargo acordado fuera del sitio (por chat directo, teléfono o venta presencial).
- **Acceso:** **Protegido** (`RoleGuard: admin, artesano`).
- **Método HTTP:** `POST`
- **Cuerpo (JSON):** Mismos campos que `solicitar.php`, permitiendo además definir el `estado_pago` inicial (ej. `'Anticipo 50%'`).

#### Respuesta Exitosa (HTTP 201 Created)
```json
{
  "exito": true,
  "mensaje": "Encargo manual agendado correctamente en el taller.",
  "datos": {
    "pedido_id": 8,
    "precio_final": 36000,
    "precio_final_formateado": "$360.00 MXN"
  }
}
```

---

### `POST /api/pedidos/cambiar-estado.php` — Actualizar Confección y Cobro Tri-Estado

- **Propósito:** Transiciona el estado de confección (`Pendiente` $\to$ `En Proceso` $\to$ `Entregado`) y/o el estado financiero del encargo (`Pendiente` $\to$ `Anticipo 50%` $\to$ `Liquidado`), registrando la marca de tiempo de auditoría en `actualizado_en`.
- **Acceso:** **Protegido** (`RoleGuard: admin` o el creador de la pieza solicitada).
- **Método HTTP:** `POST`
- **Cuerpo (JSON):**
  ```json
  {
    "id": 1,
    "estado_pedido": "Entregado",
    "estado_pago": "Liquidado"
  }
  ```

#### 🛡️ Control de Autoría & Auditoría Temporal
1. **Control de Autoría (IDOR):** Un artesano solo puede modificar pedidos vinculados a piezas de su autoría. Intentos no autorizados retornan **HTTP 403 Forbidden**.
2. **Auditoría de Transición:** Cada modificación exitosa actualiza automáticamente `actualizado_en = datetime('now', 'localtime')` en SQLite.

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Estados del pedido actualizados correctamente."
}
```

---

### `POST /api/pedidos/cancelar.php` — Cancelación Idempotente con Restitución Física de Stock

- **Propósito:** Cancela un pedido activo y **reintegra de forma atómica y automática las unidades apartadas al inventario físico de la creación**.
- **Acceso:** **Protegido** (`RoleGuard: admin` o el artesano creador de la pieza).
- **Método HTTP:** `POST`
- **Cuerpo (JSON):** `{"id": 1}`

#### 🛡️ Restitución Atómica, Idempotencia & Control IDOR
Dentro de una transacción `BEGIN IMMEDIATE TRANSACTION`:
1. **Control de Autoría:** Se verifica que el usuario autenticado sea el autor de la creación vinculada o administrador (**HTTP 403 Forbidden** si no coincide).
2. **Salvaguarda de Idempotencia (Prevención de Doble Restitución):** Si el pedido ya se encuentra en `estado_pedido = 'Cancelado'`, el backend aborta la transacción y emite **HTTP 409 Conflict** (`"El pedido ya fue cancelado previamente"`), imposibilitando que clics múltiples o llamadas duplicadas inflen fraudulentamente el inventario de la creación.
3. El pedido cambia a `estado_pedido = 'Cancelado'` y registra `actualizado_en = datetime('now', 'localtime')`.
4. Se reincorporan las piezas al inventario físico de la creación:
   ```sql
   UPDATE creaciones SET cantidad_stock = cantidad_stock + :cantidad WHERE id = :creacion_id;
   ```
5. El frontend muestra la notificación de certeza: `"+X unidad(es) reintegradas al inventario de [Nombre de Pieza]"`.

#### Respuesta Exitosa (HTTP 200 OK)
```json
{
  "exito": true,
  "mensaje": "Pedido cancelado exitosamente y stock restituido al inventario.",
  "datos": {
    "pedido_id": 1,
    "unidades_reintegradas": 1,
    "creacion_id": 1,
    "creacion_nombre": "Dragón Ignis"
  }
}
```

#### Respuestas de Error
- **HTTP 409 Conflict (Ya Cancelado):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": 409,
      "mensaje": "El pedido ya se encuentra cancelado. No se realizaron modificaciones en el inventario."
    }
  }
  ```

---

## 7. Guía Rápida para Desarrolladores Frontend (JavaScript Moderno)

A continuación, un cliente JavaScript reutilizable que puedes utilizar directamente en tus componentes ES6 para comunicarte con la API:

```javascript
/**
 * Cliente HTTP para la API de Crochet Manager
 * Archivo: src/js/modules/api-client.js
 */

export class ApiClient {
  static BASE_URL = 'http://localhost:8000';

  /**
   * Obtiene el token guardado en el navegador
   */
  static getToken() {
    return localStorage.getItem('crochet_token');
  }

  /**
   * Guarda la sesión activa
   */
  static setSession(token, user) {
    localStorage.setItem('crochet_token', token);
    localStorage.setItem('crochet_user', JSON.stringify(user));
  }

  /**
   * Limpia la sesión
   */
  static clearSession() {
    localStorage.removeItem('crochet_token');
    localStorage.removeItem('crochet_user');
  }

  /**
   * Ejecuta peticiones fetch seguras
   */
  static async request(endpoint, options = {}) {
    const url = `${this.BASE_URL}${endpoint}`;
    const headers = options.headers || {};

    // Adjuntar Bearer token automáticamente si existe
    const token = this.getToken();
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    // Si el body es un objeto plano (no FormData), formatear a JSON
    let body = options.body;
    if (body && !(body instanceof FormData) && typeof body === 'object') {
      headers['Content-Type'] = 'application/json';
      body = JSON.stringify(body);
    }

    const response = await fetch(url, {
      ...options,
      headers,
      body,
    });

    const data = await response.json().catch(() => null);

    // Si el token expiró (HTTP 401), desloguear y redirigir
    if (response.status === 401 && token) {
      this.clearSession();
      window.dispatchEvent(new CustomEvent('auth:expired'));
    }

    if (!response.ok || (data && data.exito === false)) {
      const errorMsg = data?.error?.mensaje || 'Error en la comunicación con el servidor';
      throw new Error(errorMsg);
    }

    return data;
  }

  // Métodos de conveniencia
  static get(endpoint, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = query ? `${endpoint}?${query}` : endpoint;
    return this.request(url, { method: 'GET' });
  }

  static post(endpoint, body = {}) {
    return this.request(endpoint, { method: 'POST', body });
  }
}
```

### Ejemplo de Uso en Frontend: Iniciar Sesión y Cargar Catálogo

```javascript
import { ApiClient } from './api-client.js';

// 1. Iniciar sesión
async function handleLogin(username, password) {
  try {
    const res = await ApiClient.post('/api/auth/login.php', { username, password });
    ApiClient.setSession(res.datos.token, res.datos.usuario);
    console.log('¡Bienvenida!', res.datos.usuario.username);
  } catch (error) {
    alert(`Error de acceso: ${error.message}`);
  }
}

// 2. Consultar creaciones con filtros
async function loadCreaciones(categoria = '') {
  try {
    const res = await ApiClient.get('/api/creaciones/index.php', {
      categoria,
      pagina: 1,
      limite: 12
    });
    console.log('Piezas recibidas:', res.datos);
    console.log('Paginación:', res.paginacion);
  } catch (error) {
    console.error('Error al cargar catálogo:', error.message);
  }
}
```
