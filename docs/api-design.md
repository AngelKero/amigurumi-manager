# API Design & Endpoint Specifications

This document defines the REST-style PHP endpoint architecture, HTTP status conventions, homogeneous JSON response envelopes, HTML-leak-free error handling, CORS Preflight support, dual currency representation, and standardized pagination for the collaborative **Crochet Manager** platform.

---

## 1. Global API Technical Standards

### 1.1 Homogeneous Response Envelope

All API responses are issued via the `App\Core\Response` abstraction:

#### Standard Success Response (HTTP 200 OK / HTTP 201 Created)
```json
{
  "success": true,
  "message": "Operación completada con éxito",
  "data": {},
  "paginacion": {
    "pagina": 1,
    "limite": 12,
    "total_registros": 48,
    "total_paginas": 4,
    "tiene_siguiente": true,
    "tiene_anterior": false
  }
}
```
*(The `paginacion` object is automatically attached to collection queries).*

#### Standardized Error Response (HTTP 400, 401, 403, 404, 409, 422, 500)
```json
{
  "success": false,
  "error": "Descriptive and user-friendly error message",
  "details": []
}
```

### 1.2 Global Error Handling (Zero HTML Leaks)
The global error handler `App\Core\ErrorHandler` intercepts any PHP warning, notice, uncaught exception, or fatal error. Utilizing `ob_end_clean()`, it purges any preceding output buffer to ensure the client receives **100% pure JSON with HTTP 500 status**:
```json
{
  "success": false,
  "error": "Error interno del servidor. Por favor intente más tarde.",
  "details": []
}
```

### 1.3 CORS Negotiation & Preflight Requests (`OPTIONS`)
The `Response::handleCors()` method intercepts preflight `OPTIONS` requests, immediately returning **HTTP 204 No Content** alongside the required cross-origin headers:
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With`

### 1.4 Dual Currency Format
To eliminate floating-point precision artifacts in accounting operations:
1. SQLite database strictly stores **integers in cents** (`precio`, `costo_materiales`, `precio_final`).
2. JSON responses provide a dual format: the integer cents for mathematical operations and the formatted string for immediate UI rendering:
   - `"precio": 45000`
   - `"precio_formateado": "$450.00 MXN"`
   - `"costo_materiales": 12000`
   - `"costo_formateado": "$120.00 MXN"`

### 1.5 Standardized Pagination
Governed by `App\Utils\PaginationHelper`:
- Creations Catalog: default limit of **12 items** (optimal multiple for responsive grids of 1, 2, 3, and 4 columns).
- Orders Dashboard: default limit of **20 items**.
- Accepted query parameters: `?pagina=1&limite=12`.

---

## 2. Authentication Endpoints (`api/auth/`)

### `POST /api/auth/login.php`
- **Access:** Public (Triggered via the dynamic navbar modal)
- **Request Body (JSON):**
  ```json
  {
    "username": "admin",
    "password": "password123"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Sesión iniciada correctamente",
    "data": {
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "token_type": "Bearer",
      "expires_in": 86400,
      "user": {
        "id": 1,
        "username": "admin",
        "rol": "admin"
      }
    }
  }
  ```
- **Error Response (401 Unauthorized):**
  ```json
  {
    "success": false,
    "error": "Credenciales inválidas. Verifique su usuario y contraseña."
  }
  ```

### `POST /api/auth/logout.php`
- **Access:** Protected (Requires `Authorization: Bearer <token>` header)
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Sesión cerrada correctamente"
  }
  ```

### `GET /api/auth/me.php`
- **Access:** Protected (Requires `Authorization: Bearer <token>` header)
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Usuario autenticado",
    "data": {
      "id": 1,
      "username": "admin",
      "rol": "admin"
    }
  }
  ```

---

## 3. Catalog & Creations Endpoints (`api/creaciones/`)

### `GET /api/creaciones/index.php`
- **Access:** Public
- **Query Parameters:**
  - `pagina` (integer, default: 1)
  - `limite` (integer, default: 12)
  - `categoria` (string, optional): Amigurumis & Figuras, Prendas & Ropa, Bolsos & Accesorios, Hogar & Decoración, Bebé & Infantil.
  - `artesano_id` (integer, optional): Filters creations by a specific author.
  - `stock` (string, optional): `in` (stock > 0), `on-demand` (encargo = 1), `out` (stock = 0).
  - `precio_min` / `precio_max` (integers in cents or decimals in pesos, optional).
  - `buscar` (string, optional): Keyword search in title or material.
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Creaciones obtenidas correctamente",
    "data": [
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
        "imagen_url": "uploads/crochet_dragon.jpg",
        "es_sobre_encargo": 0,
        "creado_en": "2026-09-10 14:00:00",
        "actualizado_en": null
      }
    ],
    "paginacion": {
      "pagina": 1,
      "limite": 12,
      "total_registros": 1,
      "total_paginas": 1,
      "tiene_siguiente": false,
      "tiene_anterior": false
    }
  }
  ```

### `GET /api/creaciones/detalle.php`
- **Access:** Public
- **Query Parameters:** `id` (integer, required)
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Ficha técnica obtenida con éxito",
    "data": {
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
      "descripcion": "Dragón mítico con escamas en relieve y relleno antialérgico.",
      "imagen_url": "uploads/crochet_dragon.jpg",
      "es_sobre_encargo": 0,
      "creado_en": "2026-09-10 14:00:00"
    }
  }
  ```

### `POST /api/creaciones/crear.php`
- **Access:** Protected (`AuthGuard: admin, artesano`)
- **Security:** `artesano_id` is strictly extracted from verified Bearer Token.
- **Content-Type:** `multipart/form-data`
- **Form Fields:**
  - `nombre` (string, 2-100 chars, required)
  - `categoria` (string, 2-50 chars, required)
  - `material` (string, 3-80 chars, required)
  - `dimensiones` (string, 2-100 chars, required)
  - `precio` (integer cents or decimal pesos, required)
  - `costo_materiales` (integer cents or decimal pesos, default: 0)
  - `cantidad_stock` (integer >= 0, default: 0)
  - `horas_tejido` (float >= 0, default: 0.0)
  - `descripcion` (string, max 2000 chars, optional)
  - `es_sobre_encargo` (integer: 0 or 1, default: 0)
  - `imagen` (binary file JPG/PNG/WebP, max 5MB, optional)
- **Automatic SVG Fallback:** If no photo is uploaded, `CreacionService` automatically assigns a thematic vector from `assets/svg/piezas/`.
- **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Creación registrada exitosamente en el catálogo",
    "data": {
      "id": 6,
      "nombre": "Manta Nórdica Texturizada",
      "imagen_url": "uploads/crochet_manta_66e01b.jpg"
    }
  }
  ```

### `POST /api/creaciones/actualizar.php`
- **Access:** Protected (`AuthGuard: admin` or creation author)
- **Content-Type:** `multipart/form-data`
- **Image Lifecycle:** If a new image is provided in `imagen`, it is stored in `/uploads/` and the old image file is purged via `unlink()` (provided it resides in `uploads/` and is not a system SVG).
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Creación actualizada correctamente"
  }
  ```

### `POST /api/creaciones/eliminar.php`
- **Access:** Protected (`AuthGuard: admin` or creation author)
- **Request Body (JSON):**
  ```json
  {
    "id": 6
  }
  ```
- **Relational Integrity Guard:** If the item is linked to records in `pedidos`, SQLite `ON DELETE RESTRICT` aborts the operation and the backend yields **HTTP 409 Conflict**.
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Creación eliminada exitosamente del inventario"
  }
  ```
- **Conflict Response (409 Conflict):**
  ```json
  {
    "success": false,
    "error": "No se puede eliminar la creación porque tiene pedidos asociados. Cancele o archive los pedidos primero."
  }
  ```

### `POST /api/creaciones/ajustar-stock.php`
- **Access:** Protected (`AuthGuard: admin, artesano`)
- **Purpose:** In-situ increment or decrement (`+1` / `-1`) from the inventory management table.
- **Request Body (JSON):**
  ```json
  {
    "id": 1,
    "delta": 1
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Stock actualizado",
    "data": {
      "id": 1,
      "cantidad_stock": 5
    }
  }
  ```

### `POST /api/creaciones/toggle-encargo.php`
- **Access:** Protected (`AuthGuard: admin, artesano`)
- **Purpose:** Toggles on-demand commission modality (`es_sobre_encargo`).
- **Request Body (JSON):**
  ```json
  {
    "id": 1
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Modalidad de encargo actualizada",
    "data": {
      "id": 1,
      "es_sobre_encargo": 1
    }
  }
  ```

---

## 4. Orders & Commissions Endpoints (`api/pedidos/`)

### `POST /api/pedidos/solicitar.php` (Public Client Checkout)
- **Access:** **Public** (Triggered from `modal_checkout.php`)
- **Financial Security:** Client NEVER supplies `precio_final`. Server queries `creaciones.precio` and computes `precio_final = precio * cantidad`.
- **Atomic SQLite Transaction (`BEGIN IMMEDIATE TRANSACTION`):**
  1. If `es_sobre_encargo == 0`, verifies `cantidad_stock >= cantidad`. If insufficient, rolls back with **HTTP 422**.
  2. Deducts inventory: `UPDATE creaciones SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :id`.
  3. Inserts order with default `estado_pedido = 'Pendiente'` and `estado_pago = 'Pendiente'`.
  4. Commits transaction with `COMMIT`.
- **Request Body (JSON):**
  ```json
  {
    "cliente_nombre": "Mariana Gómez",
    "cliente_contacto": "+52 55 4892 1039",
    "creacion_id": 1,
    "cantidad": 1,
    "fecha_entrega": "2026-09-25",
    "notas": "Envoltura para obsequio artesanal"
  }
  ```
- **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Su pedido ha sido recibido y el stock ha sido reservado",
    "data": {
      "pedido_id": 7,
      "precio_final": 45000,
      "precio_final_formateado": "$450.00 MXN"
    }
  }
  ```

### `GET /api/pedidos/index.php`
- **Access:** Protected (`AuthGuard: admin, artesano`)
- **Query Parameters:**
  - `pagina` (integer, default: 1)
  - `limite` (integer, default: 20)
  - `estado` (string, optional: `Pendiente`, `En Proceso`, `Entregado`, `Cancelado`)
  - `estado_pago` (string, optional: `Pendiente`, `Anticipo 50%`, `Liquidado`)
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Pedidos obtenidos con éxito",
    "data": [
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
        "creado_en": "2026-09-10 15:30:00"
      }
    ],
    "paginacion": {
      "pagina": 1,
      "limite": 20,
      "total_registros": 1,
      "total_paginas": 1,
      "tiene_siguiente": false,
      "tiene_anterior": false
    }
  }
  ```

### `POST /api/pedidos/crear.php` (Artisan Direct Commission)
- **Access:** Protected (`AuthGuard: admin, artesano`)
- **Purpose:** Records manual commission orders arranged via WhatsApp, craft fairs, or in-person workshop sales.
- **Request Body (JSON):**
  ```json
  {
    "cliente_nombre": "Sofía Morales",
    "cliente_contacto": "+52 55 1234 5678",
    "creacion_id": 2,
    "cantidad": 2,
    "fecha_entrega": "2026-10-05",
    "estado_pago": "Anticipo 50%",
    "notas": "Bordar iniciales 'SM' en la solapa"
  }
  ```
- **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Encargo manual agendado correctamente",
    "data": {
      "pedido_id": 8,
      "precio_final": 36000,
      "precio_final_formateado": "$360.00 MXN",
      "estado_pago": "Anticipo 50%"
    }
  }
  ```

### `POST /api/pedidos/cambiar-estado.php`
- **Access:** Protected (`AuthGuard: admin, artesano`)
- **Request Body (JSON):**
  ```json
  {
    "id": 1,
    "estado_pedido": "Entregado",
    "estado_pago": "Liquidado"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Estado del pedido actualizado correctamente"
  }
  ```

### `POST /api/pedidos/cancelar.php`
- **Access:** Protected (`AuthGuard: admin, artesano`)
- **Atomic Stock Restitution:** Executes an atomic transaction setting `estado_pedido = 'Cancelado'` and restocking units:
  `UPDATE creaciones SET cantidad_stock = cantidad_stock + :cantidad WHERE id = :creacion_id`.
- **Request Body (JSON):**
  ```json
  {
    "id": 1
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Pedido cancelado y stock restituido al inventario físico",
    "data": {
      "pedido_id": 1,
      "unidades_reintegradas": 1,
      "creacion_nombre": "Dragón Ignis"
    }
  }
  ```

---

## 5. Creator & Role Management Endpoints (`api/usuarios/`)

> [!IMPORTANT]
> All endpoints under `api/usuarios/` strictly require administrative privileges (`RoleGuard: admin`). Users with `artesano` or `asistente` role immediately receive **HTTP 403 Forbidden**.

### `GET /api/usuarios/index.php`
- **Access:** Protected (`AuthGuard` + `RoleGuard: admin`)
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Directorio de creadores obtenido exitosamente",
    "data": [
      {
        "id": 1,
        "username": "admin",
        "rol": "admin",
        "creado_en": "2026-09-10 14:00:00",
        "creaciones_asociadas": 3
      },
      {
        "id": 2,
        "username": "artesana_ana",
        "rol": "artesano",
        "creado_en": "2026-09-10 15:00:00",
        "creaciones_asociadas": 2
      }
    ]
  }
  ```

### `POST /api/usuarios/crear.php`
- **Access:** Protected (`AuthGuard` + `RoleGuard: admin`)
- **Request Body (JSON):**
  ```json
  {
    "username": "artesano_carlos",
    "password": "SecurePassword2026",
    "rol": "artesano"
  }
  ```
- **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Creador registrado exitosamente",
    "data": {
      "id": 4,
      "username": "artesano_carlos",
      "rol": "artesano"
    }
  }
  ```

### `POST /api/usuarios/cambiar-rol.php`
- **Access:** Protected (`AuthGuard` + `RoleGuard: admin`)
- **Root Admin Safeguard (ID #1):**
  If the payload targets `id = 1`, backend aborts execution before database queries, issuing **HTTP 403 Forbidden**:
  ```json
  {
    "success": false,
    "error": "Operación denegada: La cuenta del administrador titular (ID #1) no puede ser modificada ni degradada."
  }
  ```
- **Request Body (JSON):**
  ```json
  {
    "id": 2,
    "rol": "admin"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Rol de usuario actualizado correctamente",
    "data": {
      "id": 2,
      "rol": "admin"
    }
  }
  ```
