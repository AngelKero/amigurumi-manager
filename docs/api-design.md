# 🧶 Human-Level REST API Reference & Manual — Crochet Manager

Welcome to the comprehensive **Crochet Manager REST API (Micro-ERP & Textile Catalog)** reference guide. This document is written at a **human level**, designed for frontend engineers, software artisans, auditors, and technical contributors to clearly understand the operational purpose, business contracts, parameters, security rules, and code examples for all endpoints across the platform.

---

## 📑 Table of Contents

1. [API Architecture & Design Philosophy](#1-api-architecture--design-philosophy)
2. [Global Technical Standards](#2-global-technical-standards)
   - [2.1 Base URL & Development Environment](#21-base-url--development-environment)
   - [2.2 Homogeneous Response Envelopes (`App\Core\Response`)](#22-homogeneous-response-envelopes-appcoreresponse)
   - [2.3 HTTP Status Codes Catalog](#23-http-status-codes-catalog)
   - [2.4 CORS Negotiation & Preflight Requests (`OPTIONS`)](#24-cors-negotiation--preflight-requests-options)
   - [2.5 Dual Currency Standard (Integer Cents in SQLite $\leftrightarrow$ Formatted Pesos)](#25-dual-currency-standard-integer-cents-in-sqlite-leftrightarrow-formatted-pesos)
   - [2.6 Standardized Pagination (`App\Utils\PaginationHelper`)](#26-standardized-pagination-apputilspaginationhelper)
   - [2.7 Stateless Bearer Authentication (HMAC-SHA256)](#27-stateless-bearer-authentication-hmac-sha256)
   - [2.8 Role-Based Access Control (RBAC)](#28-role-based-access-control-rbac)
   - [2.9 Zero HTML Error Leaks (`App\Core\ErrorHandler`)](#29-zero-html-error-leaks-appcoreerrorhandler)
3. [Module 1: Authentication & Session (`api/auth/`)](#3-module-1-authentication--session-apiauth)
   - [`POST /api/auth/login.php` — Log In](#post-apiauthloginphp--log-in)
   - [`POST /api/auth/logout.php` — Log Out](#post-apiauthlogoutphp--log-out)
   - [`GET /api/auth/me.php` — Retrieve Active Profile](#get-apiauthmephp--retrieve-active-profile)
4. [Module 2: Creator Directory & RBAC Roles (`api/usuarios/`)](#4-module-2-creator-directory--rbac-roles-apiusuarios)
   - [`GET /api/usuarios/index.php` — Creator Directory](#get-apiusuariosindexphp--creator-directory)
   - [`POST /api/usuarios/crear.php` — Register Creator](#post-apiusuarioscrearphp--register-creator)
   - [`POST /api/usuarios/cambiar-rol.php` — Modify Role & Root Safeguard](#post-apiusuarioscambiar-rolphp--modify-role--root-safeguard)
5. [Module 3: Catalog, Creations & Inventory (`api/creaciones/`)](#5-module-3-catalog-creations--inventory-apicreaciones)
   - [`GET /api/creaciones/index.php` — Public Catalog with Multi-Axis Filters](#get-apicreacionesindexphp--public-catalog-with-multi-axis-filters)
   - [`GET /api/creaciones/detalle.php` — Complete Technical Sheet](#get-apicreacionesdetallephp--complete-technical-sheet)
   - [`POST /api/creaciones/crear.php` — Register Creation (Upload & SVG Fallback)](#post-apicreacionescrearphp--register-creation-upload--svg-fallback)
   - [`POST /api/creaciones/actualizar.php` — Edit Creation & `unlink()` Lifecycle](#post-apicreacionesactualizarphp--edit-creation--unlink-lifecycle)
   - [`POST /api/creaciones/eliminar.php` — Delete with Referential Safeguard](#post-apicreacioneseliminarphp--delete-with-referential-safeguard)
   - [`POST /api/creaciones/ajustar-stock.php` — In-Situ Quick Stock Adjustment](#post-apicreacionesajustar-stockphp--in-situ-quick-stock-adjustment)
   - [`POST /api/creaciones/toggle-encargo.php` — Toggle Commission Mode](#post-apicreacionestoggle-encargophp--toggle-commission-mode)
6. [Module 4: Orders, Commissions & Stock Transactions (`api/pedidos/`)](#6-module-4-orders-commissions--stock-transactions-apipedidos)
   - [`POST /api/pedidos/solicitar.php` — Public Client Checkout with Atomic Reservation](#post-apipedidossolicitarphp--public-client-checkout-with-atomic-reservation)
   - [`GET /api/pedidos/index.php` — Artisan Orders Dashboard](#get-apipedidosindexphp--artisan-orders-dashboard)
   - [`POST /api/pedidos/crear.php` — Manual Commission Entry (WhatsApp / Market)](#post-apipedidoscrearphp--manual-commission-entry-whatsapp--market)
   - [`POST /api/pedidos/cambiar-estado.php` — Update Crafting & Tri-State Payment](#post-apipedidoscambiar-estadophp--update-crafting--tri-state-payment)
   - [`POST /api/pedidos/cancelar.php` — Cancellation with Physical Stock Restitution](#post-apipedidoscancelarphp--cancellation-with-physical-stock-restitution)
7. [Frontend Developer Quickstart Guide (Modern JavaScript)](#7-frontend-developer-quickstart-guide-modern-javascript)

---

## 1. API Architecture & Design Philosophy

The **Crochet Manager** API is built on **Clean Architecture**:

```
[ Web / Mobile Client ]
         │  (HTTP JSON / FormData + Bearer Token)
         ▼
[ api/ (Thin Controllers) ]  <-- Validates HTTP verb, CORS, and delegates immediately
         │
  [ Middleware ]             <-- AuthGuard (Bearer 401) / RoleGuard (RBAC 403)
         │
   [ Services/ ]             <-- Business rules, atomic transactions, validations
         │
 [ Repositories/ ]           <-- 100% SQL isolated using PDO Prepared Statements
         │
[ SQLite database.sqlite ]   <-- PRAGMA foreign_keys = ON, CHECK constraints, indexes
```

- **Strict Physical Separation:** Directory `src/` is strictly frontend (`src/css/`, `src/js/`) and contains **0 PHP files**. All backend code lives under `app/` and `api/`.
- **Thin Controllers:** Scripts in `api/` never execute raw SQL or complex business math; they validate methods, call middleware, invoke services, and output structured JSON via `App\Core\Response`.
- **Human-Friendly Design:** Every error clearly names the field, explains what was expected, and sends the precise HTTP status code.

---

## 2. Global Technical Standards

### 2.1 Base URL & Development Environment
Local development server default:
```text
http://localhost:8000
```
Example: `http://localhost:8000/api/auth/login.php`.

---

### 2.2 Homogeneous Response Envelopes (`App\Core\Response`)
All API endpoints follow a standardized response envelope:

#### Standard Success Envelope (HTTP 200 OK / 201 Created)
```json
{
  "exito": true,
  "mensaje": "Human-readable explanation of completed operation.",
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
*(The `paginacion` key is only present when returning paginated collections).*

#### Standard Error Envelope (HTTP 400, 401, 403, 404, 405, 409, 422, 500)
```json
{
  "exito": false,
  "error": {
    "codigo": 422,
    "mensaje": "The creation name must be between 2 and 100 characters.",
    "detalles": {
      "campo": "nombre",
      "longitud_recibida": 1
    }
  }
}
```

---

### 2.3 HTTP Status Codes Catalog

| Code | Status | Usage in Crochet Manager |
| :---: | :--- | :--- |
| **200** | `OK` | Successful `GET` queries, updates, and logout acknowledgment. |
| **201** | `Created` | Successful creation of a new database record (piece, order, user). |
| **204** | `No Content` | Preflight CORS `OPTIONS` immediate response. |
| **400** | `Bad Request` | Malformed JSON or unreadable request body. |
| **401** | `Unauthorized` | Missing/invalid Bearer token, expired token, or incorrect credentials. |
| **403** | `Forbidden` | Authenticated user lacks sufficient role privileges, or attempt to modify root admin ID #1. |
| **404** | `Not Found` | Requested resource does not exist in SQLite database. |
| **405** | `Method Not Allowed` | Endpoint invoked with an unsupported HTTP method (e.g. `GET` on a `POST` route). |
| **409** | `Conflict` | Referential integrity conflict (e.g. deleting a creation with existing orders, blocked by `ON DELETE RESTRICT`). |
| **422** | `Unprocessable Entity` | Semantic validation failures (out of stock, negative price, empty required fields). |
| **500** | `Internal Server Error` | Uncaught server exception; captured by `ErrorHandler` with zero HTML leaks. |

---

### 2.4 CORS Negotiation & Preflight Requests (`OPTIONS`)
`Response::handleCors()` intercepts `OPTIONS` requests and immediately responds with **HTTP 204 No Content** alongside standard CORS headers:
```http
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Max-Age: 86400
```

---

### 2.5 Dual Currency Standard (Integer Cents in SQLite $\leftrightarrow$ Formatted Pesos)
1. **In SQLite Database:** Monetary columns (`precio`, `costo_materiales`, `precio_final`) are strictly stored as **integer cents**. `$450.00 MXN` is stored as `45000`.
2. **In API JSON Responses:** Enriched automatically by `App\Utils\CurrencyHelper`:
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

### 2.6 Standardized Pagination (`App\Utils\PaginationHelper`)
- **Creations Catalog (`api/creaciones/`):** Default limit of **12 pieces** per page (optimal for responsive 1, 2, 3, and 4-column grids).
- **Orders Dashboard (`api/pedidos/`):** Default limit of **20 orders** per page.
- **Creator Directory (`api/usuarios/`):** Default limit of **20 users** per page.

Parameters: `?pagina=1&limite=12`.

---

### 2.7 Stateless Bearer Authentication (HMAC-SHA256)
- Signed tokens issued with HMAC-SHA256 using server secret (`Config::get('auth.secret_key')`).
- **Structure:** `payloadBase64Url.signatureHex` (contains `sub`, `username`, `rol`, `iat`, `exp`).
- **TTL:** 86,400 seconds (**24 hours**).
- **Timing-Attack Resistance:** Constant-time verification using `hash_equals()`.
- **Required Header:**
  ```http
  Authorization: Bearer <token>
  ```
- **FastCGI Apache Rule:** Forwarded safely via `.htaccess` (`SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1`).

---

### 2.8 Role-Based Access Control (RBAC)
```
[ admin ]      --> Full access (User management, role edits, catalog, orders)
    │
[ artesano ]   --> Catalog, inventory, stock adjustments, assigned orders
    │
[ asistente ]  --> Read catalog/inventory, register commissions (no delete)
```
- **Root Administrator Safeguard (ID #1):** User ID #1 (`@admin`) cannot be demoted or deleted.

---

### 2.9 Zero HTML Error Leaks (`App\Core\ErrorHandler`)
Captures all PHP errors, clears buffers via `ob_end_clean()`, and returns standard **HTTP 500 JSON**.

---

## 3. Module 1: Authentication & Session (`api/auth/`)

### `POST /api/auth/login.php` — Log In
- **Access:** Public
- **Method:** `POST`
- **Header:** `Content-Type: application/json`
- **Body:** `{"username": "admin", "password": "admin123"}`
- **Security:** Timing-attack mitigation via constant-time dummy hash.
- **Success Response (HTTP 200 OK):**
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

---

### `POST /api/auth/logout.php` — Log Out
- **Access:** Public / Authenticated
- **Method:** `POST`
- **Success Response (HTTP 200 OK):**
```json
{
  "exito": true,
  "mensaje": "Sesión cerrada exitosamente. Descarte el token del cliente.",
  "datos": null
}
```

---

### `GET /api/auth/me.php` — Retrieve Active Profile
- **Access:** Authenticated (`AuthGuard`)
- **Header:** `Authorization: Bearer <token>`
- **Success Response (HTTP 200 OK):**
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

---

## 4. Module 2: Creator Directory & RBAC Roles (`api/usuarios/`)

All endpoints under `api/usuarios/` require `RoleGuard: admin`.

### `GET /api/usuarios/index.php` — Creator Directory
- **Access:** `admin`
- **Method:** `GET`
- **Header:** `Authorization: Bearer <token>`
- **Query:** `?pagina=1&limite=20`
- **Success Response (HTTP 200 OK):**
```json
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
    }
  ]
}
```

---

### `POST /api/usuarios/crear.php` — Register Creator
- **Access:** `admin`
- **Method:** `POST`
- **Body:** `{"username": "artesano_carlos", "password": "SecurePassword2026", "rol": "artesano"}`
- **Success Response (HTTP 201 Created):**
```json
{
  "exito": true,
  "mensaje": "Creador registrado exitosamente en la plataforma.",
  "datos": {
    "id": 4,
    "username": "artesano_carlos",
    "rol": "artesano"
  }
}
```

---

### `POST /api/usuarios/cambiar-rol.php` — Modify Role & Root Safeguard
- **Access:** `admin`
- **Method:** `POST`
- **Body:** `{"id": 2, "rol": "admin"}`
- **Root Admin Safeguard:** Targeting ID #1 returns HTTP 403 Forbidden.
- **Success Response (HTTP 200 OK):**
```json
{
  "exito": true,
  "mensaje": "Rol de usuario actualizado exitosamente.",
  "datos": {
    "id": 2,
    "rol": "admin"
  }
}
```

---

## 5. Module 3: Catalog, Creations & Inventory (`api/creaciones/`)

### `GET /api/creaciones/index.php` — Public Catalog with Multi-Axis Filters
- **Access:** Public
- **Query:** `pagina`, `limite`, `categoria`, `artesano_id`, `stock`, `precio_min`, `precio_max`, `buscar`, `orden`.
- **Success Response (HTTP 200 OK):**
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
      "descripcion": "Dragón mítico tejido con escamas en relieve.",
      "imagen_url": "assets/svg/piezas/dragon-ignis.svg",
      "es_sobre_encargo": 0,
      "margen_bruto_porcentaje": 73.33,
      "retorno_por_hora_formateado": "$50.77 MXN/h",
      "creado_en": "2026-09-11 13:30:52"
    }
  ],
  "paginacion": {
    "total_items": 5,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 12,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

---

### `GET /api/creaciones/detalle.php` — Technical Sheet
- **Access:** Public
- **Query:** `?id=1` (Required)
- **Success Response (HTTP 200 OK):** Complete creation attributes, material specifications, and author attribution.

---

### `POST /api/creaciones/crear.php` — Register Creation (Upload & SVG Fallback)
- **Access:** Authenticated (`admin, artesano`)
- **Type:** `multipart/form-data`
- **Fields:** `nombre`, `categoria`, `material`, `dimensiones`, `precio`, `costo_materiales`, `cantidad_stock`, `horas_tejido`, `descripcion`, `es_sobre_encargo`, `imagen`.
- **SVG Fallback:** Automatically assigns a curated SVG from `assets/svg/piezas/` if no image is uploaded.
- **Success Response (HTTP 201 Created):**
```json
{
  "exito": true,
  "mensaje": "Creación artesanal registrada exitosamente en el catálogo.",
  "datos": {
    "id": 6,
    "nombre": "Cardigan Granny Square",
    "imagen_url": "uploads/crochet_cardigan_66e01a8f.webp"
  }
}
```

---

### `POST /api/creaciones/actualizar.php` — Edit Creation & `unlink()`
- **Access:** Authenticated (`admin` or piece author).
- **Image Lifecycle:** Unlinks previous custom image from `/uploads/` via `unlink()`.

---

### `POST /api/creaciones/eliminar.php` — Delete with Referential Safeguard
- **Access:** Authenticated (`admin` or author).
- **Referential Integrity:** If orders exist in `pedidos`, SQLite `ON DELETE RESTRICT` raises **HTTP 409 Conflict**.

---

### `POST /api/creaciones/ajustar-stock.php` — In-Situ Quick Stock Adjustment
- **Access:** Authenticated (`admin, artesano`)
- **Body:** `{"id": 1, "delta": 1}`
- **Success Response (HTTP 200 OK):** Returns updated `cantidad_stock`.

---

### `POST /api/creaciones/toggle-encargo.php` — Toggle Commission Mode
- **Access:** Authenticated (`admin, artesano`)
- **Body:** `{"id": 1}`
- **Success Response (HTTP 200 OK):** Toggles `es_sobre_encargo` between `0` and `1`.

---

## 6. Module 4: Orders, Commissions & Stock Transactions (`api/pedidos/`)

### `POST /api/pedidos/solicitar.php` — Public Client Checkout
- **Access:** Public
- **Body:** `{"cliente_nombre": "Mariana Gómez", "cliente_contacto": "+52 55 4892 1039", "creacion_id": 1, "cantidad": 1, "notas": "Obsequio"}`
- **Atomic Stock Reservation (`BEGIN IMMEDIATE TRANSACTION`):**
  1. Verifies `cantidad_stock >= cantidad`.
  2. Calculates `precio_final = precio * cantidad` server-side.
  3. Updates stock and commits order.
- **Success Response (HTTP 201 Created):**
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

### `GET /api/pedidos/index.php` — Orders Dashboard
- **Access:** Authenticated (`admin, artesano`)
- **Query:** `pagina`, `limite`, `estado`, `estado_pago`.
- **Success Response (HTTP 200 OK):** Paginated orders list with customer contact, crafting notes, and payment status.

---

### `POST /api/pedidos/crear.php` — Manual Commission Entry (WhatsApp / Market)
- **Access:** Authenticated (`admin, artesano`)
- **Body:** Same fields as `solicitar.php`, plus optional initial `estado_pago` (`Anticipo 50%`).

---

### `POST /api/pedidos/cambiar-estado.php` — Update Crafting & Payment
- **Access:** Authenticated (`admin, artesano`)
- **Body:** `{"id": 1, "estado_pedido": "Entregado", "estado_pago": "Liquidado"}`

---

### `POST /api/pedidos/cancelar.php` — Cancellation with Physical Stock Restitution
- **Access:** Authenticated (`admin, artesano`)
- **Body:** `{"id": 1}`
- **Atomic Restitution:** Cancels order and re-integrates units into `creaciones.cantidad_stock`.
- **Success Response (HTTP 200 OK):**
```json
{
  "exito": true,
  "mensaje": "Pedido cancelado exitosamente y stock restituido al inventario.",
  "datos": {
    "pedido_id": 1,
    "unidades_reintegradas": 1,
    "creacion_nombre": "Dragón Ignis"
  }
}
```

---

## 7. Frontend Developer Quickstart Guide (Modern JavaScript)

```javascript
/**
 * Simple HTTP client for Crochet Manager
 */
export class ApiClient {
  static BASE_URL = 'http://localhost:8000';

  static getToken() {
    return localStorage.getItem('crochet_token');
  }

  static async request(endpoint, options = {}) {
    const url = `${this.BASE_URL}${endpoint}`;
    const headers = options.headers || {};
    const token = this.getToken();

    if (token) headers['Authorization'] = `Bearer ${token}`;

    let body = options.body;
    if (body && !(body instanceof FormData) && typeof body === 'object') {
      headers['Content-Type'] = 'application/json';
      body = JSON.stringify(body);
    }

    const res = await fetch(url, { ...options, headers, body });
    const data = await res.json().catch(() => null);

    if (!res.ok || (data && data.exito === false)) {
      throw new Error(data?.error?.mensaje || 'Server communication error');
    }

    return data;
  }

  static get(endpoint, params = {}) {
    const q = new URLSearchParams(params).toString();
    return this.request(q ? `${endpoint}?${q}` : endpoint, { method: 'GET' });
  }

  static post(endpoint, body = {}) {
    return this.request(endpoint, { method: 'POST', body });
  }
}
```
