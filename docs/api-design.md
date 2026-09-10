# API Design & Endpoint Specifications

This document outlines the modular REST-like PHP endpoints, HTTP status conventions, JSON response schemas, and error contract for the Micro-ERP.

---

## 1. Response Standard

All JSON responses follow a predictable structure:
```json
{
  "success": true,
  "message": "Operación completada con éxito",
  "data": { ... }
}
```

Error response structure (e.g., HTTP 400, 401, 404, 422, 500):
```json
{
  "success": false,
  "error": "Mensaje descriptivo del error",
  "details": [ ... ]
}
```

---

## 2. Authentication Endpoints

### `POST /api/login.php`
- **Access:** Public
- **Request Body:**
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
- **Access:** Protected
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
  - `id` (optional): Single item ID.
  - `categoria` (optional): Filter by theme.
  - `stock` (optional): `in_stock` (cantidad_stock > 0).
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "nombre": "Totoro Clásico",
        "categoria": "Pop Culture / Anime",
        "material": "100% Algodón",
        "tamano_cm": 18.5,
        "precio": 35000,
        "precio_formato": "$350.00",
        "costo_materiales": 8500,
        "costo_formato": "$85.00",
        "margen_ganancia": "$265.00",
        "cantidad_stock": 4,
        "horas_tejido": 5.5,
        "descripcion": "Tejido con hilo de algodón mercerizado...",
        "imagen_url": "https://...",
        "creado_en": "2026-09-10 14:00:00",
        "actualizado_en": null
      }
    ]
  }
  ```

### `POST /api/crear.php`
- **Access:** Protected (Session required)
- **Request Body:** Form-data or JSON with fields: `nombre`, `categoria`, `material`, `tamano_cm`, `precio`, `costo_materiales`, `cantidad_stock`, `horas_tejido`, `descripcion`, `imagen_url`.
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Amigurumi registrado exitosamente",
    "id": 1
  }
  ```

### `POST /api/actualizar.php`
- **Access:** Protected (Session required)
- **Request Body:** Must include `id` plus updated fields.
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Amigurumi actualizado exitosamente"
  }
  ```

### `POST /api/eliminar.php`
- **Access:** Protected (`admin` role required)
- **Request Body:** `{"id": 1}`
- **Constraint Handling:** If item has active foreign key orders in `pedidos`, the database triggers a foreign key restriction.
- **Response (200 OK):** `{"success": true, "message": "Item eliminado"}`
- **Response (409 Conflict):** `{"success": false, "error": "No se puede eliminar: existen pedidos asociados a esta creación."}`

---

## 4. Orders & Commissions Endpoints

### `GET /api/pedidos.php`
- **Access:** Protected
- **Query Parameters:** `estado` (optional: Pendiente, En Proceso, Entregado, Cancelado).
- **Response (200 OK):** Returns order list with joined amigurumi character name.

### `POST /api/pedidos.php`
- **Access:** Protected
- **Request Body:**
  ```json
  {
    "cliente_nombre": "Lucía Morales",
    "amigurumi_id": 1,
    "fecha_entrega": "2026-10-01",
    "estado_pedido": "Pendiente",
    "precio_final": 35000,
    "notas": "Versión con bufanda azul personalizada"
  }
  ```
- **Response (201 Created):** Order confirmed with unique order ID.
