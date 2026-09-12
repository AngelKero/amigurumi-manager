# API REST: Pedidos & Encargos (`/api/pedidos/`)

[← Volver al Índice de API](./README.md)

Este módulo gestiona la recepción pública de solicitudes de compra/encargo, el seguimiento del ciclo de vida de los pedidos para los artesanos, el enlace directo por WhatsApp y la restitución atómica de inventario ante cancelaciones.

---

## 1. Listado de Pedidos (`GET /api/pedidos/index.php`)

Recupera los pedidos con soporte para filtrado por estado de entrega, estado de pago y paginación.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()`).
- **Aislamiento Multi-Artesano:**
  - Si el usuario autenticado tiene rol `artesano`, solo visualiza los pedidos vinculados a piezas de su propia autoría (`WHERE c.artesano_id = :userId`).
  - Si el usuario es `admin`, visualiza la totalidad de pedidos de la plataforma.

### Parámetros de Consulta (Query String)
| Parámetro | Tipo | Opcional | Descripción |
| :--- | :---: | :---: | :--- |
| `estado_pedido` | `string` | Sí | `Pendiente`, `En Proceso`, `Entregado`, `Cancelado`. |
| `estado_pago` | `string` | Sí | `Pendiente`, `Anticipo 50%`, `Liquidado`. |
| `pagina` | `int` | Sí | Número de página (defecto: `1`). |
| `limite` | `int` | Sí | Elementos por página (defecto: `20`). |

### Ejemplo de Petición
```bash
curl -X GET "http://localhost:8000/api/pedidos/index.php?estado_pedido=Pendiente" \
  -H "Authorization: Bearer <token>"
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Listado de pedidos obtenido exitosamente.",
  "datos": [
    {
      "id": 1,
      "cliente_nombre": "Sofía Martínez",
      "cliente_contacto": "+52 55 1234 5678",
      "enlace_whatsapp": "https://wa.me/525512345678",
      "creacion": {
        "id": 1,
        "nombre": "Dragón Ignis",
        "imagen_url": "uploads/dragon_ignis.jpg",
        "precio_unitario_formateado": "$450.00 MXN"
      },
      "cantidad": 1,
      "precio_final": 45000,
      "precio_final_formateado": "$450.00 MXN",
      "fecha_entrega": "2026-10-15",
      "estado_pedido": "Pendiente",
      "estado_pago": "Pendiente",
      "notas": "Detalle con ojos bordados en hilo de seguridad.",
      "creado_en": "2026-09-11 13:30:52",
      "actualizado_en": null
    }
  ],
  "paginacion": {
    "total_items": 1,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 20,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

---

## 2. Solicitud Pública de Compra / Encargo (`POST /api/pedidos/solicitar.php`)

Endpoint público invocado desde el modal de checkout por los clientes de la web.

- **Acceso:** Público (Unauthenticated)
- **Cálculo de Precio en Servidor:** El cliente **NUNCA envía el total monetario**. El backend consulta el `precio` unitario de la creación en SQLite y calcula `precio_final = precio * cantidad`.
- **Transacción Atómica de Stock:** Ejecuta `BEGIN IMMEDIATE TRANSACTION`. Si la pieza no es bajo encargo (`es_sobre_encargo = 0`), valida que `cantidad_stock >= cantidad` y descuenta inmediatamente las unidades del inventario físico.

### Parámetros de Entrada (JSON Body)
| Campo | Tipo | Obligatorio | Descripción / Reglas |
| :--- | :---: | :---: | :--- |
| `creacion_id` | `int` | Sí | ID de la pieza que se adquiere. |
| `cliente_nombre`| `string` | Sí | Nombre del cliente (2 a 100 caracteres). |
| `cliente_contacto`| `string` | Sí | Teléfono / WhatsApp para coordinación (máx 50 caracteres). |
| `cantidad` | `int` | Sí | Unidades solicitadas ($\ge 1$). |
| `notas` | `string` | No | Personalizaciones o notas especiales (máx 1000 caracteres). |

### Respuesta Exitosa (`HTTP 201 Created`)
```json
{
  "exito": true,
  "mensaje": "Pedido registrado exitosamente. El artesano se pondrá en contacto contigo a la brevedad.",
  "datos": {
    "id": 14,
    "creacion_nombre": "Dragón Ignis",
    "cantidad": 1,
    "precio_final_formateado": "$450.00 MXN",
    "estado_pedido": "Pendiente"
  }
}
```

### Errores Posibles
- **`HTTP 409 Conflict`:** Stock insuficiente para entrega inmediata.
- **`HTTP 404 Not Found`:** Creación no encontrada o inactiva (`activo = 0`).

---

## 3. Registrar Encargo Directo (`POST /api/pedidos/crear.php`)

Permite al artesano o administrador registrar un encargo acordado fuera de línea.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()`).

---

## 4. Modificar Estado del Pedido / Pago (`POST /api/pedidos/cambiar-estado.php`)

Actualiza el avance de confección o el estado del pago, actualizando el campo de auditoría `actualizado_en`.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()`).
- **Payload:**
```json
{
  "id": 1,
  "estado_pedido": "En Proceso",
  "estado_pago": "Anticipo 50%"
}
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Estado del pedido actualizado exitosamente.",
  "datos": {
    "id": 1,
    "estado_pedido": "En Proceso",
    "estado_pago": "Anticipo 50%",
    "actualizado_en": "2026-09-12 14:15:22"
  }
}
```

---

## 5. Cancelar Pedido con Restitución de Stock (`POST /api/pedidos/cancelar.php`)

Cancela un pedido y reintegra automáticamente las unidades reservadas al inventario físico de la creación.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()`).
- **Cancelación Idempotente:** Si el pedido ya se encuentra en estado `Cancelado`, la operación es abortada con **`HTTP 409 Conflict`**, evitando duplicar unidades restituidas erróneamente.
- **Transacción Atómica:** Se ejecuta `UPDATE pedidos SET estado_pedido = 'Cancelado', actualizado_en = datetime(...) WHERE id = :id` y `UPDATE creaciones SET cantidad_stock = cantidad_stock + :cantidad WHERE id = :creacion_id` dentro de una transacción serializada.

### Ejemplo de Petición
```bash
curl -X POST http://localhost:8000/api/pedidos/cancelar.php \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"id": 1}'
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Pedido cancelado exitosamente. Se restituyeron 1 unidad(es) al stock de 'Dragón Ignis'.",
  "datos": {
    "id": 1,
    "estado_pedido": "Cancelado",
    "unidades_restituidas": 1,
    "creacion_id": 1,
    "nuevo_stock": 4,
    "actualizado_en": "2026-09-12 14:20:00"
  }
}
```
