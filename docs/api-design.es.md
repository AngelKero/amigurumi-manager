# Diseño de API y Especificaciones de Endpoints

Este documento define la arquitectura de endpoints REST en PHP, códigos de estado HTTP, esquemas homogéneos de respuesta JSON, manejo de errores sin fugas HTML, soporte CORS Preflight, representación monetaria dual y paginación estandarizada para la plataforma colaborativa **Crochet Manager**.

---

## 1. Estándares Técnicos Globales de la API

### 1.1 Envoltura Homogénea de Respuesta (Envelope)

Todas las respuestas emitidas por la API utilizan la abstracción `App\Core\Response`:

#### Respuesta Exitosa Estándar (HTTP 200 OK / HTTP 201 Created)
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
*(El objeto `paginacion` se incluye automáticamente en listados de colecciones).*

#### Respuesta de Error Estandarizada (HTTP 400, 401, 403, 404, 409, 422, 500)
```json
{
  "success": false,
  "error": "Mensaje descriptivo y comprensible del error",
  "details": []
}
```

### 1.2 Manejo Global de Errores (Cero Fugas HTML)
El manejador global `App\Core\ErrorHandler` intercepta cualquier error de PHP, advertencia, excepción no capturada o fallo fatal. Mediante `ob_end_clean()`, purga cualquier salida previa en el búfer para garantizar que el cliente reciba **100% JSON puro con código HTTP 500**:
```json
{
  "success": false,
  "error": "Error interno del servidor. Por favor intente más tarde.",
  "details": []
}
```

### 1.3 Negociación CORS y Peticiones Preflight (`OPTIONS`)
El método `Response::handleCors()` intercepta peticiones previas de comprobación (preflight `OPTIONS`), respondiendo inmediatamente con **HTTP 204 No Content** y las cabeceras requeridas:
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With`

### 1.4 Formato Monetario Dual
Para evitar errores de redondeo de punto flotante en cálculos contables:
1. La base de datos SQLite almacena exclusivamente **enteros en centavos** (`precio`, `costo_materiales`, `precio_final`).
2. Las respuestas JSON retornan una representación dual: el entero en centavos para cálculos y la cadena formateada para renderizado directo en la UI:
   - `"precio": 45000`
   - `"precio_formateado": "$450.00 MXN"`
   - `"costo_materiales": 12000`
   - `"costo_formateado": "$120.00 MXN"`

### 1.5 Paginación Estandarizada
Gestionada mediante `App\Utils\PaginationHelper`:
- Catálogo de Creaciones: límite por defecto de **12 ítems** (múltiplo óptimo para cuadrículas responsivas de 1, 2, 3 y 4 columnas).
- Dashboard de Pedidos: límite por defecto de **20 ítems**.
- Parámetros query aceptados: `?pagina=1&limite=12`.

---

## 2. Endpoints de Autenticación (`api/auth/`)

### `POST /api/auth/login.php`
- **Acceso:** Público (Invocado desde el modal dinámico del navbar)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "username": "admin",
    "password": "password123"
  }
  ```
- **Respuesta Exitosa (200 OK):**
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
- **Respuesta de Error (401 Unauthorized):**
  ```json
  {
    "success": false,
    "error": "Credenciales inválidas. Verifique su usuario y contraseña."
  }
  ```

### `POST /api/auth/logout.php`
- **Acceso:** Protegido (Requiere cabecera `Authorization: Bearer <token>`)
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Sesión cerrada correctamente"
  }
  ```

### `GET /api/auth/me.php`
- **Acceso:** Protegido (Requiere cabecera `Authorization: Bearer <token>`)
- **Respuesta Exitosa (200 OK):**
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

## 3. Endpoints del Catálogo & Creaciones (`api/creaciones/`)

### `GET /api/creaciones/index.php`
- **Acceso:** Público
- **Parámetros de Consulta (Query Params):**
  - `pagina` (entero, default: 1)
  - `limite` (entero, default: 12)
  - `categoria` (texto, opcional): Amigurumis & Figuras, Prendas & Ropa, Bolsos & Accesorios, Hogar & Decoración, Bebé & Infantil.
  - `artesano_id` (entero, opcional): Filtra creaciones de un creador específico.
  - `stock` (texto, opcional): `in` (stock > 0), `on-demand` (encargo = 1), `out` (stock = 0).
  - `precio_min` / `precio_max` (enteros en centavos o decimales en pesos, opcional).
  - `buscar` (texto, opcional): Búsqueda por nombre o material.
- **Respuesta Exitosa (200 OK):**
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
- **Acceso:** Público
- **Parámetros de Consulta:** `id` (entero, obligatorio)
- **Respuesta Exitosa (200 OK):**
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
- **Acceso:** Protegido (`AuthGuard: admin, artesano`)
- **Seguridad:** El `artesano_id` se extrae estrictamente del Bearer Token validado.
- **Tipo de Contenido:** `multipart/form-data`
- **Campos del Formulario:**
  - `nombre` (string, 2-100 car., obligatorio)
  - `categoria` (string, 2-50 car., obligatorio)
  - `material` (string, 3-80 car., obligatorio)
  - `dimensiones` (string, 2-100 car., obligatorio)
  - `precio` (entero centavos o decimal pesos, obligatorio)
  - `costo_materiales` (entero centavos o decimal pesos, default: 0)
  - `cantidad_stock` (entero >= 0, default: 0)
  - `horas_tejido` (float >= 0, default: 0.0)
  - `descripcion` (string, máx 2000 car., opcional)
  - `es_sobre_encargo` (entero: 0 o 1, default: 0)
  - `imagen` (archivo binario JPG/PNG/WebP, máx 5MB, opcional)
- **Fallback Automático SVG:** Si no se adjunta imagen, `CreacionService` asigna el vector temático correspondiente desde `assets/svg/piezas/`.
- **Respuesta Exitosa (201 Created):**
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
- **Acceso:** Protegido (`AuthGuard: admin` o creador autor)
- **Tipo de Contenido:** `multipart/form-data`
- **Ciclo de Vida de Imágenes:** Si se adjunta un nuevo archivo en `imagen`, se procesa en `/uploads/` y se elimina la imagen anterior mediante `unlink()` (siempre que estuviera en `uploads/` y no sea un SVG del sistema).
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Creación actualizada correctamente"
  }
  ```

### `POST /api/creaciones/eliminar.php`
- **Acceso:** Protegido (`AuthGuard: admin` o creador autor)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 6
  }
  ```
- **Integridad Relacional SQLite:** Si la creación tiene pedidos asociados en `pedidos`, la regla `ON DELETE RESTRICT` detiene la operación y el backend retorna **HTTP 409 Conflict**.
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Creación eliminada exitosamente del inventario"
  }
  ```
- **Respuesta de Conflicto (409 Conflict):**
  ```json
  {
    "success": false,
    "error": "No se puede eliminar la creación porque tiene pedidos asociados. Cancele o archive los pedidos primero."
  }
  ```

### `POST /api/creaciones/ajustar-stock.php`
- **Acceso:** Protegido (`AuthGuard: admin, artesano`)
- **Propósito:** Incremento o decremento in-situ (`+1` / `-1`) desde el panel de inventario.
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 1,
    "delta": 1
  }
  ```
- **Respuesta Exitosa (200 OK):**
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
- **Acceso:** Protegido (`AuthGuard: admin, artesano`)
- **Propósito:** Alternar la modalidad de confección bajo encargo (`es_sobre_encargo`).
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
    "message": "Modalidad de encargo actualizada",
    "data": {
      "id": 1,
      "es_sobre_encargo": 1
    }
  }
  ```

---

## 4. Endpoints de Pedidos & Encargos (`api/pedidos/`)

### `POST /api/pedidos/solicitar.php` (Checkout Público de Clientes)
- **Acceso:** **Público** (Invocado desde `modal_checkout.php`)
- **Seguridad Financiera:** El cliente NO suministra `precio_final`. El servidor consulta `creaciones.precio` y calcula `precio_final = precio * cantidad`.
- **Transacción Atómica SQLite (`BEGIN IMMEDIATE TRANSACTION`):**
  1. Si `es_sobre_encargo == 0`, verifica disponibilidad de `cantidad_stock >= cantidad`. Si es insuficiente, revierte con **HTTP 422**.
  2. Descuenta el inventario: `UPDATE creaciones SET cantidad_stock = cantidad_stock - :cantidad WHERE id = :id`.
  3. Inserta el pedido con `estado_pedido = 'Pendiente'` y `estado_pago = 'Pendiente'`.
  4. Confirma la transacción con `COMMIT`.
- **Cuerpo de la Solicitud (JSON):**
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
- **Respuesta Exitosa (201 Created):**
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
- **Acceso:** Protegido (`AuthGuard: admin, artesano`)
- **Parámetros de Consulta:**
  - `pagina` (entero, default: 1)
  - `limite` (entero, default: 20)
  - `estado` (texto, opcional: `Pendiente`, `En Proceso`, `Entregado`, `Cancelado`)
  - `estado_pago` (texto, opcional: `Pendiente`, `Anticipo 50%`, `Liquidado`)
- **Respuesta Exitosa (200 OK):**
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

### `POST /api/pedidos/crear.php` (Encargo Manual del Creador)
- **Acceso:** Protegido (`AuthGuard: admin, artesano`)
- **Propósito:** Registrar encargos coordinados directamente por WhatsApp, ferias o talleres.
- **Cuerpo de la Solicitud (JSON):**
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
- **Respuesta Exitosa (201 Created):**
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
- **Acceso:** Protegido (`AuthGuard: admin, artesano`)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 1,
    "estado_pedido": "Entregado",
    "estado_pago": "Liquidado"
  }
  ```
- **Respuesta Exitosa (200 OK):**
  ```json
  {
    "success": true,
    "message": "Estado del pedido actualizado correctamente"
  }
  ```

### `POST /api/pedidos/cancelar.php`
- **Acceso:** Protegido (`AuthGuard: admin, artesano`)
- **Restitución Automática de Stock:** Ejecuta una transacción atómica que cambia `estado_pedido = 'Cancelado'` y reincorpora las unidades:
  `UPDATE creaciones SET cantidad_stock = cantidad_stock + :cantidad WHERE id = :creacion_id`.
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
    "message": "Pedido cancelado y stock restituido al inventario físico",
    "data": {
      "pedido_id": 1,
      "unidades_reintegradas": 1,
      "creacion_nombre": "Dragón Ignis"
    }
  }
  ```

---

## 5. Endpoints de Gestión de Creadores & Roles (`api/usuarios/`)

> [!IMPORTANT]
> Todos los endpoints bajo `api/usuarios/` requieren rol administrativo estricto (`RoleGuard: admin`). Usuarios con rol `artesano` o `asistente` reciben inmediatamente **HTTP 403 Forbidden**.

### `GET /api/usuarios/index.php`
- **Acceso:** Protegido (`AuthGuard` + `RoleGuard: admin`)
- **Respuesta Exitosa (200 OK):**
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
- **Acceso:** Protegido (`AuthGuard` + `RoleGuard: admin`)
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "username": "artesano_carlos",
    "password": "SecurePassword2026",
    "rol": "artesano"
  }
  ```
- **Respuesta Exitosa (201 Created):**
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
- **Acceso:** Protegido (`AuthGuard` + `RoleGuard: admin`)
- **Salvaguarda Inviolable de Cuenta Raíz (ID #1):**
  Si la petición apunta a `id = 1`, el backend aborta la operación antes de interactuar con la base de datos y responde con **HTTP 403 Forbidden**:
  ```json
  {
    "success": false,
    "error": "Operación denegada: La cuenta del administrador titular (ID #1) no puede ser modificada ni degradada."
  }
  ```
- **Cuerpo de la Solicitud (JSON):**
  ```json
  {
    "id": 2,
    "rol": "admin"
  }
  ```
- **Respuesta Exitosa (200 OK):**
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
