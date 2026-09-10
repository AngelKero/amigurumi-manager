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
- **Access:** Public
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

## 3. Amigurumis Endpoints

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
      "imagen_url": "https://images.unsplash.com/photo-1615486511484-92e172cc4fe0",
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
      "imagen_url": "https://images.unsplash.com/photo-1584917865442-de89df76afd3",
      "creado_en": "2026-09-10 14:15:00",
      "actualizado_en": null
    }
  ]
}
```

#### Response: Single Item (200 OK via `?id=1`)
```json
{
  "success": true,
  "data": {
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
    "imagen_url": "https://images.unsplash.com/photo-1615486511484-92e172cc4fe0",
    "creado_en": "2026-09-10 14:00:00",
    "actualizado_en": null
  }
}
```

### `POST /api/crear.php`
- **Access:** Protected (Session required: `admin` or `artesano`)
- **Security & Identity Binding:** The backend automatically extracts the authenticated artisan's ID from `$_SESSION['user_id']` and injects it into `artesano_id`. The client **MUST NOT** include `artesano_id` in the request body; any client-provided ID will be ignored to prevent identity spoofing.
- **Request Body (JSON or multipart/form-data):**
  ```json
  {
    "nombre": "Baby Yoda Crochet",
    "categoria": "Pop Culture / Anime",
    "material": "Acrílico Premium",
    "tamano_cm": 15.0,
    "precio": 38000,
    "costo_materiales": 9000,
    "cantidad_stock": 3,
    "horas_tejido": 4.5,
    "descripcion": "Incluye túnica removible y vasito tejido.",
    "imagen_url": "https://images.unsplash.com/photo-1607604276583-eef5d076aa5f"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Amigurumi registrado exitosamente",
    "id": 3
  }
  ```

### `POST /api/actualizar.php`
- **Access:** Protected (Session required: `admin` or original artisan owner)
- **Request Body (JSON):**
  ```json
  {
    "id": 1,
    "nombre": "Totoro Clásico Gigante",
    "categoria": "Pop Culture / Anime",
    "material": "100% Algodón Mercerizado",
    "tamano_cm": 28.0,
    "precio": 55000,
    "costo_materiales": 14000,
    "cantidad_stock": 2,
    "horas_tejido": 8.0,
    "descripcion": "Edición ampliada con hoja paraguas incluida.",
    "imagen_url": "https://images.unsplash.com/photo-1615486511484-92e172cc4fe0"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Amigurumi actualizado exitosamente"
  }
  ```

### `POST /api/eliminar.php`
- **Access:** Protected (Session required: `admin`)
- **Request Body (JSON):**
  ```json
  {
    "id": 1
  }
  ```
- **Constraint Handling:** If the item is referenced by orders in `pedidos`, SQLite's `ON DELETE RESTRICT` raises a foreign key violation.
- **Success Response (200 OK):**
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

## 4. Orders & Commissions Endpoints (`pedidos`)

### `GET /api/pedidos.php`
- **Access:** Protected (Session required)
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

### `POST /api/pedidos.php`
- **Access:** Protected (Session required)
- **Anti-Tampering Price Calculation:** The client **MUST NOT** send `precio_final`. The PHP backend queries the current unit price (`amigurumis.precio`) from the database and calculates the total locked price:
  $$\text{precio\_final} = \text{amigurumi.precio} \times \text{cantidad}$$
- **Atomic Transaction & Inventory Deduction:** The operation is wrapped in a PDO database transaction (`BEGIN TRANSACTION`):
  1. Checks if the requested `cantidad` is $\le \text{amigurumis.cantidad\_stock}$. If stock is insufficient, rolls back and returns **HTTP 422 Unprocessable Entity**.
  2. Deducts physical inventory:
     ```sql
     UPDATE amigurumis SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :amigurumi_id;
     ```
  3. Inserts the order record into `pedidos` with the server-calculated `precio_final`.
  4. Commits the transaction.
- **Request Body (JSON):**
  ```json
  {
    "cliente_nombre": "Carlos Mendoza",
    "amigurumi_id": 2,
    "cantidad": 3,
    "fecha_entrega": "2026-10-15",
    "estado_pedido": "Pendiente",
    "notas": "Pedido para regalo corporativo, empaque individual"
  }
  ```
- **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Pedido registrado exitosamente y stock descontado del inventario",
    "id": 2,
    "precio_final": 126000,
    "precio_final_formato": "$1,260.00",
    "stock_restante": 1
  }
  ```
- **Error Response: Insufficient Stock (422 Unprocessable Entity):**
  ```json
  {
    "success": false,
    "error": "Stock insuficiente para completar el pedido. Stock disponible: 1 unidad(es)."
  }
  ```

### `POST /api/actualizar_pedido.php`
- **Access:** Protected (Session required: `admin` or `artesano`)
- **Order State Transitions:** Allows updating `estado_pedido` (`Pendiente`, `En Proceso`, `Entregado`, `Cancelado`).
- **Inventory Restocking Rule on Cancellation:** Wrapped in a database transaction (`BEGIN TRANSACTION`):
  - If the new state is `'Cancelado'` and the previous state was not `'Cancelado'`, the backend restores the reserved units back into the inventory:
    ```sql
    UPDATE amigurumis SET cantidad_stock = cantidad_stock + :cantidad WHERE id = :amigurumi_id;
    ```
- **Request Body (JSON):**
  ```json
  {
    "id": 2,
    "estado_pedido": "Cancelado"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Estado del pedido actualizado a Cancelado y stock reintegrado al catálogo exitosamente"
  }
  ```
