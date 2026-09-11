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
  - `stock` (opcional, texto): Filtra por disponibilidad (`in` para `cantidad_stock > 0`, `on-demand` para `es_sobre_encargo = 1`, `out` para `cantidad_stock = 0`).
  - `precio_min` / `precio_max` (opcional, decimal): Rango de presupuesto.

#### Respuesta: Colección de Catálogo (200 OK)
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
      "id": 3,
      "artesano_id": 2,
      "artesano_nombre": "artesana_ana",
      "nombre": "Ajolote Rosado Pastel",
      "categoria": "Amigurumis & Figuras",
      "material": "Hilo Chenille Terciopelo",
      "dimensiones": "14.0 x 10.0 cm",
      "precio": 32000,
      "precio_formato": "$320.00 MXN",
      "costo_materiales": 8500,
      "costo_formato": "$85.00 MXN",
      "margen_ganancia": "$235.00 MXN",
      "cantidad_stock": 0,
      "horas_tejido": 4.5,
      "descripcion": "Ajolote confeccionado bajo pedido con hilaza aterciopelada.",
      "imagen_url": "uploads/ajolote.jpg",
      "es_sobre_encargo": 1,
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
- **Campos en Form-Data:**
  - `nombre` (texto, 2-100 car.)
  - `categoria` (texto, 2-50 car.)
  - `material` (texto, 3-80 car.)
  - `dimensiones` (texto, 2-100 car.)
  - `precio` (decimal en pesos o entero en centavos)
  - `costo_materiales` (decimal en pesos o entero en centavos)
  - `cantidad_stock` (entero >= 0)
  - `horas_tejido` (número >= 0)
  - `descripcion` (texto, máx 2000 car.)
  - `es_sobre_encargo` (entero: 0 o 1, default 0)
  - `imagen` (archivo binario, opcional)
- **Respuesta (201 Created):**
  ```json
  {
    "success": true,
    "message": "Amigurumi registrado exitosamente",
    "id": 3,
    "imagen_url": "uploads/amig_66e01b8a9c.jpg"
  }
  ```

### `POST /api/actualizar.php`
- **Acceso:** Protegido (Sesión requerida: `admin` o artesano autor)
- **Manejo de Imagen:** Acepta `multipart/form-data`. Si se adjunta un nuevo archivo en `imagen`, se sube a `/uploads/`, se actualiza la ruta y se elimina físicamente el archivo anterior mediante `unlink()`.
- **Campos Aceptados:** Mismos que creación más `id` obligatorio. Incluye `es_sobre_encargo`.
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
  - Si el amigurumi tiene pedidos asociados, la regla `ON DELETE RESTRICT` de SQLite aborta la operación y retorna **HTTP 409 Conflict**.
  - Si no tiene pedidos, se consulta `imagen_url` y, si existe físicamente en `/uploads/`, se elimina mediante `unlink()` antes del `DELETE` SQL.
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
- **Acceso:** **Público** (Activado desde `modal_checkout.php` en catálogo o detalle)
- **Seguridad e Integridad:**
  - El cliente NO proporciona `precio_final` ni `estado_pedido`.
  - El backend consulta `amigurumis.precio` y calcula `precio_final = precio * cantidad`.
  - Asigna por defecto `estado_pedido = 'Pendiente'` y `estado_pago = 'Pendiente'`.
- **Transacción Atómica y Descuento de Inventario:** Ejecutado en transacción PDO (`BEGIN TRANSACTION`):
  1. Si `amigurumis.es_sobre_encargo == 0`, verifica que `cantidad <= amigurumis.cantidad_stock`. Si no alcanza, revierte con **HTTP 422**.
  2. Si hay stock, descuenta: `UPDATE amigurumis SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :amigurumi_id`.
  3. Inserta el pedido vinculando `cliente_contacto`.
  4. Confirma la transacción (`COMMIT`).
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "cliente_nombre": "Mariana Gómez",
    "cliente_contacto": "+52 55 4892 1039",
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
    "precio_total": 45000,
    "precio_total_formato": "$450.00 MXN"
  }
  ```

### `POST /api/pedidos.php` (Registro de Encargo Manual por el Artesano)
- **Acceso:** Protegido (Sesión requerida: `admin` o `artesano`)
- **Propósito:** Registrar ventas directas captadas en talleres, ferias artesanales o WhatsApp.
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "cliente_nombre": "Sofía Morales",
    "cliente_contacto": "+52 55 1234 5678",
    "amigurumi_id": 2,
    "cantidad": 2,
    "fecha_entrega": "2026-10-05",
    "estado_pago": "Anticipo 50%",
    "notas": "Bordar iniciales 'SM' en la base"
  }
  ```
- **Respuesta Exitosa (201 Created):**
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
- **Acceso:** Protegido (Sesión requerida: `admin` o `artesano`)
- **Parámetros de Consulta:** `estado` (opcional: `Pendiente`, `En Proceso`, `Entregado`, `Cancelado`).
- **Respuesta (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "cliente_nombre": "Mariana Gómez",
        "cliente_contacto": "+52 55 4892 1039",
        "amigurumi_id": 1,
        "amigurumi_nombre": "Dragón Ignis",
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
- **Acceso:** Protegido (Sesión requerida: `admin` o `artesano`)
- **Restitución Automática:** Si `estado_pedido` pasa a `'Cancelado'`, el backend ejecuta una transacción atómica que incrementa `amigurumis.cantidad_stock += pedidos.cantidad`.
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
    "message": "Estado actualizado a Cancelado y stock restituido al inventario físico",
    "unidades_reintegradas": 1
  }
  ```

