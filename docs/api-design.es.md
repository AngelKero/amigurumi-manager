# Diseño de API y Especificaciones de Endpoints

Este documento describe los endpoints modulares de estilo REST en PHP, las convenciones de códigos de estado HTTP, los esquemas de respuesta JSON y el manejo estándar de errores para el Micro-ERP de Amigurumis.

---

## 1. Estándar de Respuestas JSON

Todas las respuestas del backend siguen una estructura homogénea:

### Estructura de Respuesta Exitosa
```json
{
  "success": true,
  "message": "Operación completada con éxito",
  "data": {}
}
```

### Estructura Estándar de Error (HTTP 400, 401, 403, 404, 409, 422, 500)
```json
{
  "success": false,
  "error": "Mensaje descriptivo del error",
  "details": []
}
```

---

## 2. Endpoints de Autenticación

### `POST /api/login.php`
- **Acceso:** Público
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "username": "admin",
    "password": "password123"
  }
  ```
- **Respuesta (200 OK):**
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
- **Acceso:** Protegido (Requiere sesión activa)
- **Respuesta (200 OK):**
  ```json
  {
    "success": true,
    "message": "Sesión cerrada correctamente"
  }
  ```

---

## 3. Endpoints del Catálogo de Amigurumis

### `GET /api/leer.php`
- **Acceso:** Público
- **Parámetros de Consulta (Query Params):**
  - `id` (opcional, entero): Retorna un único objeto de amigurumi.
  - `categoria` (opcional, texto): Filtra por categoría temática.
  - `artesano_id` (opcional, entero): Filtra creaciones de un artesano específico.
  - `stock` (opcional, texto): Filtra por disponibilidad (`in_stock` para `cantidad_stock > 0`).

#### Respuesta: Colección de Catálogo (200 OK)
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

#### Respuesta: Pieza Individual (200 OK vía `?id=1`)
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
- **Acceso:** Protegido (Sesión requerida: `admin` o `artesano`)
- **Seguridad y Atribución de Identidad:** El backend extrae automáticamente el ID del artesano autenticado desde `$_SESSION['user_id']` y lo asigna al campo `artesano_id`. El cliente **NO DEBE** enviar `artesano_id` en el cuerpo de la solicitud JSON; cualquier valor enviado por el cliente será ignorado para evitar suplantación de identidad.
- **Cuerpo de la Solicitud (JSON o multipart/form-data):**
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
- **Respuesta (201 Created):**
  ```json
  {
    "success": true,
    "message": "Amigurumi registrado exitosamente",
    "id": 3
  }
  ```

### `POST /api/actualizar.php`
- **Acceso:** Protegido (Sesión requerida: `admin` o el artesano autor de la pieza)
- **Cuerpo de la Solicitud (JSON):**
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
- **Respuesta (200 OK):**
  ```json
  {
    "success": true,
    "message": "Amigurumi actualizado exitosamente"
  }
  ```

### `POST /api/eliminar.php`
- **Acceso:** Protegido (Sesión requerida: `admin`)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 1
  }
  ```
- **Manejo de Restricción Referencial:** Si la pieza está asociada a encargos en `pedidos`, la regla `ON DELETE RESTRICT` de SQLite detiene la eliminación.
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Amigurumi eliminado correctamente"
  }
  ```
- **Respuesta de Conflicto (409 Conflict):**
  ```json
  {
    "success": false,
    "error": "No se puede eliminar el amigurumi porque tiene pedidos asociados. Debe cancelar o archivar los pedidos primero."
  }
  ```

---

## 4. Endpoints de Pedidos y Encargos (`pedidos`)

### `GET /api/pedidos.php`
- **Acceso:** Protegido (Sesión requerida)
- **Parámetros de Consulta:** `estado` (opcional: `Pendiente`, `En Proceso`, `Entregado`, `Cancelado`).
- **Respuesta (200 OK):**
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
- **Acceso:** Protegido (Sesión requerida)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "cliente_nombre": "Carlos Mendoza",
    "amigurumi_id": 2,
    "cantidad": 3,
    "fecha_entrega": "2026-10-15",
    "estado_pedido": "Pendiente",
    "precio_final": 126000,
    "notas": "Pedido para regalo corporativo, empaque individual"
  }
  ```
- **Respuesta (201 Created):**
  ```json
  {
    "success": true,
    "message": "Pedido registrado exitosamente",
    "id": 2
  }
  ```
