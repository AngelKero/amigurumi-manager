# API Design & Endpoint Specifications

This document outlines the modular REST-like PHP endpoints, HTTP status conventions, JSON response schemas, and error contracts for the Handmade Crochet Creations Micro-ERP.

---

## 1. Response Standard

All JSON responses follow a predictable envelope structure:

### Standard Success Envelope
```json
{
  "success": true,
  "message": "Operación completada con éxito",
  "data": {}
}
```

### Standard Error Envelope (HTTP 400, 401, 403, 404, 409, 422, 500)
```json
{
  "success": false,
  "error": "Mensaje descriptivo del error",
  "details": []
}
```

---

## 2. Authentication Endpoints

### `POST /api/login.php`
- **Access:** Public (Triggered via the Dynamic Navbar Modal)
- **Request Body (JSON):**
  ```json
  {
    "username": "admin",
    "password": "password123"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Sesión iniciada correctamente",
    "user": {
      "id": 1,
      "username": "admin",
      "rol": "admin"
    }
  }
  ```

### `POST /api/logout.php`
- **Access:** Protected (Session required)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Sesión cerrada correctamente"
  }
  ```

---

## 3. User Management Endpoints (`/api/usuarios.php`)

> [!IMPORTANT]
> All endpoints under `/api/usuarios.php` are strictly protected and require an authenticated session with role `'admin'`. Non-admin users receive **HTTP 403 Forbidden**.

### `GET /api/usuarios.php`
- **Access:** Protected (`admin` role required)
- **Query Parameters:** `id` (optional, integer)
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "username": "admin",
        "rol": "admin",
        "creado_en": "2026-09-10 14:00:00"
      },
      {
        "id": 2,
        "username": "artesana_ana",
        "rol": "artesano",
        "creado_en": "2026-09-10 15:00:00"
      }
    ]
  }
  ```

### `POST /api/usuarios.php`
- **Access:** Protected (`admin` role required)
- **Request Body (JSON):**
  ```json
  {
    "username": "artesano_carlos",
    "password": "SecurePassword2026",
    "rol": "artesano"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Usuario creado exitosamente",
    "id": 3
  }
  ```

### `PUT /api/usuarios.php`
- **Access:** Protected (`admin` role required)
- **Request Body (JSON):**
  ```json
  {
    "id": 3,
    "username": "carlos_crochet",
    "password": "NewOptionalPassword123",
    "rol": "artesano"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Usuario actualizado exitosamente"
  }
  ```

### `DELETE /api/usuarios.php`
- **Access:** Protected (`admin` role required)
- **Request Body (JSON):**
  ```json
  {
    "id": 3
  }
  ```
- **Constraint Handling:** SQLite's `ON DELETE RESTRICT` raises a foreign key violation if the user has created creaciones (`fk_creaciones_artesano`).
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Usuario eliminado correctamente"
  }
  ```
- **Conflict Response (409 Conflict):**
  ```json
  {
    "success": false,
    "error": "No se puede eliminar el usuario porque tiene piezas de crochet asociadas. Reasigne o elimine las creaciones primero."
  }
  ```

---

## 4. Creations Catalog Endpoints (`creaciones`)

### `GET /api/leer.php`
- **Access:** Public
- **Query Parameters:**
  - `id` (optional, integer): Returns a single creation object.
  - `categoria` (optional, string): Filter by category (e.g. Amigurumis & Figuras, Prendas & Ropa, Bolsos & Accesorios, Hogar & Decoración, Bebé & Infantil).
  - `artesano_id` (optional, integer): Filter by specific artisan creator.
  - `stock` (optional, string): Filter by availability (`in` for `cantidad_stock > 0`, `on-demand` for `es_sobre_encargo = 1`, `out` for `cantidad_stock = 0`).
  - `precio_min` / `precio_max` (optional, decimal): Budget filter range.

#### Response: Catalog Collection (200 OK)
```json
{
  "success": true,
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
      "precio_formato": "$450.00 MXN",
      "costo_materiales": 12000,
      "costo_formato": "$120.00 MXN",
      "margen_ganancia": "$330.00 MXN",
      "cantidad_stock": 4,
      "horas_tejido": 6.5,
      "descripcion": "Dragón mítico con escamas en relieve y relleno antialérgico.",
      "imagen_url": "uploads/dragon.jpg",
      "es_sobre_encargo": 0,
      "creado_en": "2026-09-10 14:00:00",
      "actualizado_en": null
    },
    {
      "id": 4,
      "artesano_id": 1,
      "artesano_nombre": "admin",
      "nombre": "Cardigan Granny Squares",
      "categoria": "Prendas & Ropa",
      "material": "Lana Merino Fina & Algodón",
      "dimensiones": "Talla M (95x58 cm)",
      "precio": 98000,
      "precio_formato": "$980.00 MXN",
      "costo_materiales": 28000,
      "costo_formato": "$280.00 MXN",
      "margen_ganancia": "$700.00 MXN",
      "cantidad_stock": 2,
      "horas_tejido": 18.0,
      "descripcion": "Cardigan artesanal confeccionado con cuadros tradicionales de la abuela florales.",
      "imagen_url": "uploads/cardigan.jpg",
      "es_sobre_encargo": 0,
      "creado_en": "2026-09-10 14:30:00",
      "actualizado_en": null
    }
  ]
}
```

### `POST /api/crear.php`
- **Access:** Protected (Session required: `admin` or `artesano`)
- **Security & Identity Binding:** The backend automatically extracts `$_SESSION['user_id']` and assigns it to `artesano_id`. Client-provided IDs are discarded.
- **Image Upload Handling (`multipart/form-data`):**
  - Accepts a binary file upload under field `imagen`.
  - Backend verifies MIME type (`image/jpeg`, `image/png`, `image/webp`), enforces size limit ($\le 5\text{MB}$), generates a unique filename (`crochet_UUID.jpg`), moves it to the local `/uploads` directory, and writes the relative path `uploads/crochet_UUID.jpg` into `imagen_url`.
- **Request Form-Data Fields:**
  - `nombre` (text, 2-100 chars)
  - `categoria` (text, 2-50 chars)
  - `material` (text, 3-80 chars)
  - `dimensiones` (text, 2-100 chars)
  - `precio` (decimal in pesos or integer in cents)
  - `costo_materiales` (decimal in pesos or integer in cents)
  - `cantidad_stock` (integer >= 0)
  - `horas_tejido` (number >= 0)
  - `descripcion` (text, max 2000 chars)
  - `es_sobre_encargo` (integer: 0 or 1, default 0)
  - `imagen` (binary file, optional)
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Creación registrada exitosamente",
    "id": 6,
    "imagen_url": "uploads/crochet_66e01b8a9c.jpg"
  }
  ```

### `POST /api/actualizar.php`
- **Access:** Protected (Session required: `admin` or original artisan owner)
- **Image Upload Handling:**
  - Can accept `multipart/form-data` with an updated `imagen` file. If a new image is provided, the backend saves it to `/uploads`, replaces `imagen_url`, and unlinks the previous local file.
  - Accepts `es_sobre_encargo` along with standard attributes and mandatory `id`.
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Creación actualizada exitosamente"
  }
  ```

### `POST /api/eliminar.php`
- **Access:** Protected (Session required: `admin`)
- **Orphaned File Management Policy:**
  - If the creation has associated orders in `pedidos`, SQLite's `ON DELETE RESTRICT` aborts the operation with **HTTP 409 Conflict**.
  - If there are no orders, the backend verifies `imagen_url`, and if it points to a physical file in `/uploads/`, deletes it via PHP's `unlink()` before executing the SQL `DELETE`.
- **Request Body (JSON):**
  ```json
  {
    "id": 1
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Creación y archivo de imagen eliminados correctamente"
  }
  ```
- **Conflict Response (409 Conflict):**
  ```json
  {
    "success": false,
    "error": "No se puede eliminar la creación porque tiene pedidos asociados. Debe cancelar o archivar los pedidos primero."
  }
  ```

---

## 5. Orders & Commissions Endpoints (`pedidos`)

### `POST /api/solicitar_pedido.php` (Public Client Checkout)
- **Access:** **Public** (Triggered from `modal_checkout.php` in catalog or detail views)
- **Security & Integrity:**
  - The client does NOT provide `precio_final` or `estado_pedido`.
  - The backend automatically queries `creaciones.precio` and computes `precio_final = creaciones.precio * cantidad`.
  - Defaults `estado_pedido` and `estado_pago` to `'Pendiente'`.
- **Atomic Inventory Transaction:** Wrapped in a database transaction (`BEGIN TRANSACTION`):
  1. If `creaciones.es_sobre_encargo == 0`, validates requested `cantidad <= creaciones.cantidad_stock`. If stock is insufficient, rolls back and returns **HTTP 422**.
  2. If stock is available, decrements: `UPDATE creaciones SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :creacion_id`.
  3. Inserts order into `pedidos` storing `cliente_contacto`.
  4. Commits transaction.
- **Request Body (JSON):**
  ```json
  {
    "cliente_nombre": "Mariana Gómez",
    "cliente_contacto": "+52 55 4892 1039",
    "creacion_id": 1,
    "cantidad": 1,
    "fecha_entrega": "2026-09-25",
    "notas": "Empaque de regalo con moño rosa"
  }
  ```
- **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Su pedido ha sido recibido y el stock ha sido reservado",
    "pedido_id": 5,
    "precio_total": 45000,
    "precio_total_formato": "$450.00 MXN"
  }
  ```

### `POST /api/pedidos.php` (Manual Artisan Order Registration)
- **Access:** Protected (Session required: `admin` or `artesano`)
- **Purpose:** Registers direct commissions received through workshops, craft fairs, or WhatsApp.
- **Request Body (JSON):**
  ```json
  {
    "cliente_nombre": "Sofía Morales",
    "cliente_contacto": "+52 55 1234 5678",
    "creacion_id": 2,
    "cantidad": 2,
    "fecha_entrega": "2026-10-05",
    "estado_pago": "Anticipo 50%",
    "notas": "Bordar iniciales 'SM' en la base"
  }
  ```
- **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Encargo manual agendado exitosamente",
    "pedido_id": 6,
    "precio_total": 36000,
    "precio_total_formato": "$360.00 MXN",
    "estado_pago": "Anticipo 50%"
  }
  ```

### `GET /api/pedidos.php`
- **Access:** Protected (Session required: `admin` or `artesano`)
- **Query Parameters:** `estado` (optional: `Pendiente`, `En Proceso`, `Entregado`, `Cancelado`).
- **Response (200 OK):**
  ```json
  {
    "success": true,
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
        "precio_final_formato": "$450.00 MXN",
        "notas": "Detalles dorados en las alas",
        "creado_en": "2026-09-10 15:30:00"
      }
    ]
  }
  ```

### `POST /api/actualizar_pedido.php`
- **Access:** Protected (Session required: `admin` or `artesano`)
- **Cancellation & Restocking:** If `estado_pedido` transitions to `'Cancelado'`, the backend executes an atomic transaction restoring `cantidad` back into `creaciones.cantidad_stock`.
- **Request Body (JSON):**
  ```json
  {
    "id": 1,
    "estado_pedido": "Cancelado"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Estado actualizado a Cancelado y stock restituido al inventario físico",
    "unidades_reintegradas": 1
  }
  ```

