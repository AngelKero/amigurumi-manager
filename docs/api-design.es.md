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
- **Acceso:** Público (Activado desde el Modal Dinámico del Navbar)
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

## 3. Endpoints de Gestión de Usuarios (`/api/usuarios.php`)

> [!IMPORTANT]
> Todos los endpoints bajo `/api/usuarios.php` están estrictamente protegidos y restringidos exclusivamente al rol `'admin'`. Usuarios no administradores reciben **HTTP 403 Forbidden**.

### `GET /api/usuarios.php`
- **Acceso:** Protegido (Exclusivo rol `admin`)
- **Parámetros de Consulta:** `id` (opcional, entero)
- **Respuesta (200 OK):**
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
- **Acceso:** Protegido (Exclusivo rol `admin`)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "username": "artesano_carlos",
    "password": "SecurePassword2026",
    "rol": "artesano"
  }
  ```
- **Respuesta (201 Created):**
  ```json
  {
    "success": true,
    "message": "Usuario creado exitosamente",
    "id": 3
  }
  ```

### `PUT /api/usuarios.php`
- **Acceso:** Protegido (Exclusivo rol `admin`)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 3,
    "username": "carlos_crochet",
    "password": "NewOptionalPassword123",
    "rol": "artesano"
  }
  ```
- **Respuesta (200 OK):**
  ```json
  {
    "success": true,
    "message": "Usuario actualizado exitosamente"
  }
  ```

### `DELETE /api/usuarios.php`
- **Acceso:** Protegido (Exclusivo rol `admin`)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 3
  }
  ```
- **Manejo de Restricción:** La regla `ON DELETE RESTRICT` de SQLite detiene la eliminación si el usuario tiene piezas de amigurumi asociadas.
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Usuario eliminado correctamente"
  }
  ```
- **Respuesta de Conflicto (409 Conflict):**
  ```json
  {
    "success": false,
    "error": "No se puede eliminar el usuario porque tiene piezas de amigurumi asociadas. Reasigne o elimine las piezas primero."
  }
  ```

---

## 4. Endpoints del Catálogo de Amigurumis

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
- **Acceso:** Protegido (Sesión requerida: `admin` o `artesano`)
- **Seguridad y Atribución:** El backend extrae automáticamente `$_SESSION['user_id']` y lo asigna a `artesano_id`.
- **Carga Real de Archivos de Imagen (`multipart/form-data`):**
  - Acepta un archivo binario en el campo `imagen`.
  - El backend valida tipo MIME (`image/jpeg`, `image/png`, `image/webp`), peso máximo ($\le 5\text{MB}$), genera un nombre único (`amig_UUID.jpg`), lo traslada al directorio local `/uploads` y almacena la ruta relativa `uploads/amig_UUID.jpg` en la base de datos.
  - Si no se envía archivo, utiliza la imagen por defecto del sistema.
- **Campos en Form-Data:**
  - `nombre` (texto)
  - `categoria` (texto)
  - `material` (texto)
  - `tamano_cm` (número)
  - `precio` (decimal)
  - `costo_materiales` (decimal)
  - `cantidad_stock` (entero)
  - `horas_tejido` (número)
  - `descripcion` (texto)
  - `imagen` (archivo binario, opcional)
- **Respuesta (201 Created):**
  ```json
  {
    "success": true,
    "message": "Amigurumi registrado exitosamente con imagen local",
    "id": 3,
    "imagen_url": "uploads/amig_66e01b8a9c.jpg"
  }
  ```

### `POST /api/actualizar.php`
- **Acceso:** Protegido (Sesión requerida: `admin` o autor artesano)
- **Manejo de Imagen:** Acepta `multipart/form-data`. Si se adjunta un nuevo archivo en `imagen`, se sube a `/uploads/`, se actualiza la ruta y se elimina el archivo anterior en disco.
- **Respuesta (200 OK):**
  ```json
  {
    "success": true,
    "message": "Amigurumi actualizado exitosamente"
  }
  ```

### `POST /api/eliminar.php`
- **Acceso:** Protegido (Sesión requerida: `admin`)
- **Política de Gestión de Archivos Huérfanos:**
  - Antes de eliminar el registro de la base de datos, el backend DEBE consultar el campo `imagen_url` del amigurumi.
  - Si `imagen_url` está presente, apunta a un archivo local en `/uploads/` y el archivo existe físicamente en disco, el backend DEBE eliminarlo físicamente utilizando `unlink()` de PHP antes de ejecutar el `DELETE` en la base de datos.
  - Si fallan las comprobaciones de integridad referencial (por ejemplo, si existen pedidos asociados en `pedidos` protegidos por `ON DELETE RESTRICT`), la eliminación se interrumpe retornando HTTP 409, garantizando que no se eliminen registros de la base de datos ni archivos físicos por error.
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 1
  }
  ```
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Amigurumi y archivo de imagen eliminados correctamente"
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

## 5. Endpoints de Pedidos y Encargos (`pedidos`)

### `POST /api/solicitar_pedido.php` (Checkout Público de Clientes)
- **Acceso:** **Público** (Permite a cualquier cliente realizar compras desde `detalle.html`)
- **Seguridad e Integridad:**
  - El cliente NO proporciona `precio_final` ni `estado_pedido`.
  - El backend consulta `amigurumis.precio` y calcula `precio_final = precio * cantidad`.
  - Asigna por defecto `estado_pedido = 'Pendiente'`.
- **Transacción Atómica y Descuento de Inventario:** Ejecutado en transacción PDO (`BEGIN TRANSACTION`):
  1. Verifica que `cantidad <= amigurumis.cantidad_stock`. Si el inventario no alcanza, revierte y devuelve **HTTP 422**.
  2. Descuenta el inventario: `UPDATE amigurumis SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :amigurumi_id`.
  3. Inserta el pedido con el precio total verificado.
  4. Confirma la transacción (`COMMIT`).
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "cliente_nombre": "Mariana Gómez",
    "amigurumi_id": 1,
    "cantidad": 1,
    "fecha_entrega": "2026-09-25",
    "notas": "Empaque de regalo con moño rosa"
  }
  ```
- **Respuesta Exitosa (201 Created):**
  ```json
  {
    "success": true,
    "message": "Su pedido ha sido recibido y el stock ha sido reservado",
    "pedido_id": 5,
    "precio_total": 35000,
    "precio_total_formato": "$350.00"
  }
  ```
- **Respuesta de Error: Stock Insuficiente (422 Unprocessable Entity):**
  ```json
  {
    "success": false,
    "error": "Stock insuficiente para satisfacer su pedido. Stock disponible: 0 unidad(es)."
  }
  ```

### `GET /api/pedidos.php`
- **Acceso:** Protegido (Sesión requerida: `admin` o `artesano`)
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

### `POST /api/actualizar_pedido.php`
- **Acceso:** Protegido (Sesión requerida: `admin` o `artesano`)
- **Reintegro por Cancelación:** Si `estado_pedido` cambia a `'Cancelado'`, se restituye automáticamente la `cantidad` a `amigurumis.cantidad_stock`.
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 1,
    "estado_pedido": "Cancelado"
  }
  ```
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Estado del pedido actualizado a Cancelado y stock reintegrado al catálogo exitosamente"
  }
  ```
