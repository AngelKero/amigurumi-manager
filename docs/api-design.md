# API Design & Endpoint Specifications

This document outlines the modular REST-like PHP endpoints, HTTP status conventions, JSON response schemas, and error contracts for the Amigurumi Micro-ERP.

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
- **Constraint Handling:** SQLite's `ON DELETE RESTRICT` raises a foreign key violation if the user has created amigurumis.
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
    "error": "No se puede eliminar el usuario porque tiene piezas de amigurumi asociadas. Reasigne o elimine las piezas primero."
  }
  ```

---

## 4. Amigurumis Endpoints

### `GET /api/leer.php`
- **Access:** Public
- **Query Parameters:**
  - `id` (optional, integer): Returns a single amigurumi object.
  - `categoria` (optional, string): Filter by category.
  - `artesano_id` (optional, integer): Filter by specific artisan creator.
  - `stock` (optional, string): Filter by availability (`in_stock` for `cantidad_stock > 0`).

#### Response: Catalog Collection (200 OK)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "artesano_id": 1,
      "artesano_nombre": "admin",
      "nombre": "Totoro Clásico",
      "categoria": "Pop Culture / Anime",
      "material": "100% Algodón Mercerizado",
      "tamano_cm": 18.5,
      "precio": 35000,
      "precio_formato": "$350.00",
      "costo_materiales": 8500,
      "costo_formato": "$85.00",
      "margen_ganancia": "$265.00",
      "cantidad_stock": 4,
      "horas_tejido": 5.5,
      "descripcion": "Tejido con hilo de algodón mercerizado, relleno siliconado hipoalergénico y ojos de seguridad.",
      "imagen_url": "uploads/amigurumi_66e01a2b.jpg",
      "creado_en": "2026-09-10 14:00:00",
      "actualizado_en": null
    },
    {
      "id": 2,
      "artesano_id": 1,
      "artesano_nombre": "admin",
      "nombre": "Axolotl Rosado",
      "categoria": "Animales",
      "material": "Chenille / Terciopelo",
      "tamano_cm": 22.0,
      "precio": 42000,
      "precio_formato": "$420.00",
      "costo_materiales": 11000,
      "costo_formato": "$110.00",
      "margen_ganancia": "$310.00",
      "cantidad_stock": 0,
      "horas_tejido": 6.0,
      "descripcion": "Textura ultrasuave con branquias en relieve y detalles bordados a mano.",
      "imagen_url": "uploads/amigurumi_66e01a3f.jpg",
      "creado_en": "2026-09-10 14:15:00",
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
  - Backend verifies MIME type (`image/jpeg`, `image/png`, `image/webp`), enforces size limit ($\le 5\text{MB}$), generates a unique filename (`amig_UUID.jpg`), moves it to the local `/uploads` directory, and writes the relative path `uploads/amig_UUID.jpg` into `imagen_url`.
  - If no file is uploaded, falls back to a default placeholder path.
- **Request Form-Data Fields:**
  - `nombre` (text)
  - `categoria` (text)
  - `material` (text)
  - `tamano_cm` (number)
  - `precio` (decimal)
  - `costo_materiales` (decimal)
  - `cantidad_stock` (integer)
  - `horas_tejido` (number)
  - `descripcion` (text)
  - `imagen` (file, optional)
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Amigurumi registrado exitosamente con imagen local",
    "id": 3,
    "imagen_url": "uploads/amig_66e01b8a9c.jpg"
  }
  ```

### `POST /api/actualizar.php`
- **Access:** Protected (Session required: `admin` or original artisan owner)
- **Image Upload Handling:**
  - Can accept `multipart/form-data` with an updated `imagen` file. If a new image is provided, the backend saves it to `/uploads`, replaces `imagen_url`, and unlinks the previous local file.
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Amigurumi actualizado exitosamente"
  }
  ```

### `POST /api/eliminar.php`
- **Access:** Protected (Session required: `admin`)
- **File Management & Orphan Cleanup (Mandatory):**
  - Before or upon deleting the record from the database, the backend MUST query the item's `imagen_url`.
  - **Foreign Key Safety:** The backend checks that no orders reference this amigurumi (`pedidos.amigurumi_id`), preventing deletion if active/past orders exist.
  - **Physical Cleanup (`unlink`):** If the deletion is permitted and `imagen_url` references a file stored in the local `/uploads/` directory (e.g., `uploads/amigurumi_67...jpg`), the backend MUST physically delete the file using PHP's `unlink()` to eliminate orphaned files and conserve disk space.
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
    "message": "Amigurumi eliminado correctamente"
  }
  ```
- **Conflict Response (409 Conflict):**
  ```json
  {
    "success": false,
    "error": "No se puede eliminar el amigurumi porque tiene pedidos asociados. Debe cancelar o archivar los pedidos primero."
  }
  ```

---

## 5. Orders & Commissions Endpoints (`pedidos`)

### `POST /api/solicitar_pedido.php` (Public Client Checkout)
- **Access:** **Public** (Allows customers to purchase directly from `detalle.html`)
- **Security & Integrity:**
  - The client does NOT provide `precio_final` or `estado_pedido`.
  - The backend automatically queries `amigurumis.precio` and computes `precio_final = amigurumis.precio * cantidad`.
  - Defaults `estado_pedido` to `'Pendiente'`.
- **Atomic Inventory Transaction:** Wrapped in a database transaction (`BEGIN TRANSACTION`):
  1. Validates that requested `cantidad` $\le \text{amigurumis.cantidad\_stock}$. If stock is insufficient, rolls back and returns **HTTP 422**.
  2. Decrements physical stock: `UPDATE amigurumis SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :amigurumi_id`.
  3. Inserts order into `pedidos`.
  4. Commits transaction.
- **Request Body (JSON):**
  ```json
  {
    "cliente_nombre": "Mariana Gómez",
    "amigurumi_id": 1,
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
    "precio_total": 35000,
    "precio_total_formato": "$350.00"
  }
  ```
- **Error Response: Insufficient Stock (422 Unprocessable Entity):**
  ```json
  {
    "success": false,
    "error": "Stock insuficiente para satisfacer su pedido. Stock disponible: 0 unidad(es)."
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
        "cliente_nombre": "Lucía Morales",
        "amigurumi_id": 1,
        "amigurumi_nombre": "Totoro Clásico",
        "cantidad": 2,
        "fecha_entrega": "2026-10-01",
        "estado_pedido": "En Proceso",
        "precio_final": 70000,
        "precio_final_formato": "$700.00",
        "notas": "Versión con bufanda azul personalizada",
        "creado_en": "2026-09-10 15:30:00"
      }
    ]
  }
  ```

### `POST /api/actualizar_pedido.php`
- **Access:** Protected (Session required: `admin` or `artesano`)
- **Cancellation & Restocking:** If `estado_pedido` transitions to `'Cancelado'`, the backend executes a transaction restoring `cantidad` back into `amigurumis.cantidad_stock`.
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
    "message": "Estado del pedido actualizado a Cancelado y stock reintegrado al catálogo exitosamente"
  }
  ```
