# Reporte de Pruebas: Subfase 3.5 — Pedidos, Transacciones Atómicas & Notificaciones WhatsApp

[← Volver al Hub de Testing](./README.md) | [Ver Plan Maestro de Fase 3](../architecture/phase-3-plan.md)

- **Fecha de Ejecución:** 2026-09-12
- **Responsable:** Antigravity (Advanced Agentic Coding)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (`PRAGMA busy_timeout = 5000;`, `PRAGMA foreign_keys = ON;`)
- **Archivo de Log Crudo (CLI):** [`logs/subfase-3.5-cli.log`](../../logs/subfase-3.5-cli.log)
- **Archivo de Trazas HTTP:** [`logs/subfase-3.5-http.log`](../../logs/subfase-3.5-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.5.php`](../../tests/test-subfase-3.5.php)
- **Resultado General:** **139 / 139 Aserciones Aprobadas (100% OK en 565.57 ms)** — ✅ **APTO PARA AVANZAR**
- **Total Acumulado Fase 3:** **532 / 532 Aserciones Aprobadas (100% OK en verde)** (3.1: 93, 3.2: 69, 3.3: 105, 3.4: 126, 3.5: 139)

---

## 1. Resumen Ejecutivo del Alcance Implementado

La **Subfase 3.5** culmina el circuito transaccional del Micro-ERP colaborativo en crochet, orquestando el ciclo de vida íntegro de solicitudes y pedidos de clientes, transacciones atómicas serializadas en SQLite, cálculo matemático de importes en el servidor, protección estricta contra ataques de referencia directa insegura (IDOR) y generación de enlaces universales a WhatsApp.

### Componentes Entregados & Validados:
1. **`App\Repositories\PedidoRepository` (`app/Repositories/PedidoRepository.php`):**
   - Aislamiento del 100% de consultas SQL parametrizadas con `PDO::prepare()`.
   - Hidratación profunda con `INNER JOIN creaciones c` e `INNER JOIN usuarios u` (metadatos de la pieza y creador).
   - Filtrado multicriterio (`estado_pedido`, `estado_pago`, `creacion_id`, `busqueda`, `activo`, `orden`).
   - Aislamiento multi-artesano estricto a nivel repositorio (`WHERE c.artesano_id = :artesano_id`).
   - Transacciones atómicas de compra con `BEGIN IMMEDIATE TRANSACTION`: validación de stock disponible, decremento físico en `creaciones.cantidad_stock` (solo para stock físico) y cálculo congelado de `precio_final = creacion.precio * cantidad`.
   - Transacción atómica de cancelación con **idempotencia estricta**: detección de pedido ya cancelado (HTTP 409 Conflict) y restitución exacta de unidades (`creaciones.cantidad_stock = cantidad_stock + :unidades`).
   - Borrado lógico universal (`activo = 0`, `eliminado_en`) y reactivación sin eliminaciones físicas.
2. **`App\Services\PedidoService` (`app/Services/PedidoService.php`):**
   - Validaciones de dominio para los campos del pedido (nombre de cliente, contacto, cantidad 1-1,000, fecha YYYY-MM-DD, estados permitidos).
   - Generación de enlaces directos a WhatsApp (`https://wa.me/...`) con normalización nacional mexicana (+52 automático a 10 dígitos) y mensaje pre-redactado con codificación `rawurlencode`.
   - Enriquecimiento monetario dual (`CurrencyHelper::formatCents` para `$450.00 MXN`).
   - Salvaguarda IDOR (`ensureArtisanOwnership`): el artesano solo puede consultar, crear o modificar pedidos vinculados a piezas de su autoría (`artesano_id === user.id`); el administrador posee visibilidad y control omnímodo.
   - Delegación inteligente: cambiar el estado de un pedido a `'Cancelado'` delega automáticamente en `cancelOrder()`, restituyendo las existencias al inventario físico.
3. **Suite de 5 Controladores REST Delgados en `api/pedidos/`:**
   - `GET /api/pedidos/index.php`: Listado paginado con filtros y aislamiento multi-artesano (`RoleGuard::artisanOrAdmin()`).
   - `POST /api/pedidos/solicitar.php`: Checkout público desde el modal de catálogo (sin autenticación requerida).
   - `POST /api/pedidos/crear.php`: Alta manual de encargos convenidos fuera de línea con salvaguarda IDOR (`RoleGuard::artisanOrAdmin()`).
   - `POST /api/pedidos/cambiar-estado.php`: Modificación de avance y cobro con `actualizado_en` y salvaguarda IDOR.
   - `POST /api/pedidos/cancelar.php`: Cancelación transaccional con restitución de inventario e idempotencia ante dobles peticiones.

---

## 2. Matriz Detallada de Aserciones y Casos Evaluados

### Sección 1: PedidoRepository (Persistencia, Filtros y Transacciones Atómicas)

| # | Operación / Método | Caso de Prueba Evaluado | Entrada / Parámetros | Comportamiento Verificado | Estado |
| :-: | :--- | :--- | :--- | :--- | :-: |
| 1-10 | `findById()` | Recuperación de pedido semilla #1 | `id = 1, onlyActive = true` | Registro hidratado con cliente, estados, precio centavos y activo = 1 | ✅ PASS |
| 11-14 | `findById()` | Objeto anidado de creación y creador | `id = 1` | `creacion.nombre = Dragón Ignis`, `precio = 45000`, `artesano_id = 1`, `username = admin` | ✅ PASS |
| 15 | `findById()` | Consulta de ID inexistente | `id = 99999` | Retorna estrictamente `null` | ✅ PASS |
| 16-17 | `listAll()` | Listado sin filtros | `filters = []` | Retorna array con al menos los 2 pedidos iniciales | ✅ PASS |
| 18-19 | `listAll()` | Filtro por `estado_pedido` | `estado_pedido = 'En Proceso'` | Retorna únicamente pedidos en confección activa | ✅ PASS |
| 20-21 | `listAll()` | Filtro por `estado_pago` | `estado_pago = 'Pendiente'` | Retorna únicamente pedidos pendientes de cobro | ✅ PASS |
| 22-23 | `listAll()` | Filtro por `creacion_id` | `creacion_id = 1` | Retorna únicamente pedidos vinculados al Dragón Ignis | ✅ PASS |
| 24-25 | `listAll()` | Filtro de búsqueda por cliente y pieza | `busqueda = 'Mariana'`, `'Ajolote'` | Localiza pedidos por coincidencia textual en cliente o nombre de creación | ✅ PASS |
| 26-27 | `listAll()` | Ordenamientos dinámicos | `orden = 'precio_desc'`, `'precio_asc'` | Ordena correctamente de mayor a menor y menor a mayor precio | ✅ PASS |
| 28-31 | `listAll()` | Aislamiento multi-artesano en repositorio | `artesanoId = 2` (Ana), `1` (Admin) | Ana solo visualiza pedidos de sus creaciones; Admin solo los suyos | ✅ PASS |
| 32-33 | `countAll()` | Conteo normalizado para paginación | `filters = []`, `artesanoId = 2` | Coincide exactamente con el conteo de `listAll()` | ✅ PASS |
| 34-37 | `createAtomic()` | Compra de pieza física con stock | `creacion_id = 2`, `cantidad = 2` | Genera ID positivo, calcula precio en servidor y descuenta 2 unidades en SQLite | ✅ PASS |
| 38-39 | `createAtomic()` | Stock físico insuficiente | `cantidad = stock + 10` | Lanza `RuntimeException 409 Conflict` y preserva el stock inalterado | ✅ PASS |
| 40-41 | `createAtomic()` | Pieza por encargo (`isCustomOrder = true`) | `creacion_id = 3` (Ajolote, stock 0) | Genera pedido exitosamente sin decrementar stock (se mantiene en 0) | ✅ PASS |
| 42-44 | `updateStatus()` | Actualización de confección y cobro | `estado_pedido = 'En Proceso'`, `estado_pago = 'Anticipo 50%'` | Actualiza columnas y registra `actualizado_en` | ✅ PASS |
| 45-48 | `cancelOrderAtomic()` | Cancelación y restitución de inventario | `id = createdOrderId` | Actualiza estado a 'Cancelado' y restituye 2 unidades a `creaciones.cantidad_stock` | ✅ PASS |
| 49-50 | `cancelOrderAtomic()` | Salvaguarda de idempotencia | 2do intento de cancelación en mismo pedido | Lanza `RuntimeException 409 Conflict` y bloquea doble restitución de inventario | ✅ PASS |
| 51-57 | `softDelete()` & `restore()` | Ciclo de baja lógica y reactivación | `activo = 0` $\rightarrow$ `activo = 1` | Oculta en consultas activas, preserva datos en BD y reactiva con `eliminado_en = NULL` | ✅ PASS |

### Sección 2: PedidoService (Reglas de Negocio, IDOR, Moneda y WhatsApp)

| # | Operación / Método | Caso de Prueba Evaluado | Entrada / Parámetros | Comportamiento Verificado | Estado |
| :-: | :--- | :--- | :--- | :--- | :-: |
| 58-62 | `buildWhatsAppLink()` | Generación de enlace a WhatsApp | `5512345678`, `Valeria`, `#101` | Antepone prefijo país `52`, limpia caracteres, codifica `rawurlencode` y pre-redacta mensaje | ✅ PASS |
| 63 | `buildWhatsAppLink()` | Limpieza de número internacional | `+52 55 4892 1039` | Produce URL limpia `https://wa.me/525548921039?text=...` | ✅ PASS |
| 64 | `buildWhatsAppLink()` | Teléfono inválido o truncado | `1234` (< 8 dígitos) | Retorna estrictamente `null` | ✅ PASS |
| 65-68 | `requestPublicOrder()` | Validaciones de dominio públicas | Nombre < 2, Contacto < 3, Cantidad < 1 | Lanza `InvalidArgumentException 422` con mensaje descriptivo | ✅ PASS |
| 69 | `requestPublicOrder()` | Creación inexistente en catálogo | `creacion_id = 99999` | Lanza `RuntimeException 404 Not Found` | ✅ PASS |
| 70-73 | `requestPublicOrder()` | Solicitud pública exitosa | `creacion_id = 1`, `cantidad = 1` | Retorna resumen, precio calculado `$450.00 MXN` y estado 'Pendiente' | ✅ PASS |
| 74 | `createManualOrder()` | Salvaguarda IDOR en encargo manual | Artesana Ana sobre pieza de Admin | Lanza `RuntimeException 403 Forbidden` bloqueando encargo ajeno | ✅ PASS |
| 75-77 | `createManualOrder()` | Encargo manual en pieza propia | Artesana Ana sobre pieza #5 | Genera encargo exitosamente vinculando `artesano_id = 2` | ✅ PASS |
| 78-83 | `listOrders()` | Listado con aislamiento y enriquecimiento | Usuario con rol artesano (Ana) | Filtra pedidos propios y enriquece con precio formateado y WhatsApp | ✅ PASS |
| 84 | `listOrders()` | Listado de administrador global | Usuario con rol admin (ID 1) | Visualiza pedidos de múltiples artesanos en la plataforma | ✅ PASS |
| 85-87 | `getOrderById()` | Consulta por ID con protección IDOR | Artesano ajeno vs Propietario/Admin | Lanza 403 a terceros; entrega detalle enriquecido al autor y al admin | ✅ PASS |
| 88 | `updateOrderStatus()` | Modificación de estado ajeno (IDOR) | Artesana Ana sobre pedido de Admin | Lanza `RuntimeException 403 Forbidden` | ✅ PASS |
| 89-91 | `cancelOrder()` | Cancelación y restitución por autor | Artesana Ana sobre su propio pedido | Restituye 1 unidad al stock e informa en mensaje amigable | ✅ PASS |

### Sección 3: Endpoints REST en Vivo contra `localhost:8000` (HTTP curl)

| # | Endpoint HTTP | Método | Headers / Autenticación | Payload / Parámetros | Código Esperado | Código Obtenido | Estado |
| :-: | :--- | :-: | :--- | :--- | :-: | :-: | :-: |
| 92-97 | `/api/auth/login.php` | POST | `Content-Type: application/json` | Credenciales de admin, artesana y asistente | 200 OK | 200 OK | ✅ PASS |
| 98-102 | `/api/pedidos/solicitar.php` | POST | Público (Sin token) | `creacion_id = 1`, `cantidad = 1`, cliente | 201 Created | 201 Created | ✅ PASS |
| 103 | `/api/pedidos/solicitar.php` | POST | Público | `cantidad = stock + 99` | 409 Conflict | 409 Conflict | ✅ PASS |
| 104 | `/api/pedidos/solicitar.php` | POST | Público | `cliente_nombre = ''` | 422 Unprocessable | 422 Unprocessable | ✅ PASS |
| 105 | `/api/pedidos/solicitar.php` | GET | Público | N/A | 405 Method Not Allowed | 405 Method Not Allowed | ✅ PASS |
| 106 | `/api/pedidos/index.php` | GET | Sin token | N/A | 401 Unauthorized | 401 Unauthorized | ✅ PASS |
| 107-111 | `/api/pedidos/index.php` | GET | `Bearer <artisanToken>` | N/A | 200 OK (Solo piezas de Ana) | 200 OK (Solo piezas de Ana) | ✅ PASS |
| 112-113 | `/api/pedidos/index.php` | GET | `Bearer <adminToken>` | N/A | 200 OK (Múltiples artesanos) | 200 OK (Múltiples artesanos) | ✅ PASS |
| 114-115 | `/api/pedidos/index.php` | GET | `Bearer <adminToken>` | `?estado_pedido=Pendiente&limite=5` | 200 OK (`limite = 5`) | 200 OK (`limite = 5`) | ✅ PASS |
| 116 | `/api/pedidos/index.php` | POST | `Bearer <adminToken>` | N/A | 405 Method Not Allowed | 405 Method Not Allowed | ✅ PASS |
| 117 | `/api/pedidos/crear.php` | POST | `Bearer <assistantToken>` | Encargo manual | 403 Forbidden | 403 Forbidden | ✅ PASS |
| 118 | `/api/pedidos/crear.php` | POST | `Bearer <artisanToken>` | Encargo en pieza ajena | 403 Forbidden | 403 Forbidden | ✅ PASS |
| 119-121 | `/api/pedidos/crear.php` | POST | `Bearer <artisanToken>` | Encargo en pieza propia | 201 Created | 201 Created | ✅ PASS |
| 122 | `/api/pedidos/cambiar-estado.php` | POST | `Bearer <artisanToken>` | Modificación de pedido ajeno | 403 Forbidden | 403 Forbidden | ✅ PASS |
| 123-125 | `/api/pedidos/cambiar-estado.php` | POST | `Bearer <artisanToken>` | Modificación de pedido propio | 200 OK (En Proceso, Liquidado) | 200 OK (En Proceso, Liquidado) | ✅ PASS |
| 126 | `/api/pedidos/cambiar-estado.php` | POST | `Bearer <artisanToken>` | `estado_pedido = 'EstadoFantasma'` | 422 Unprocessable | 422 Unprocessable | ✅ PASS |
| 127 | `/api/pedidos/cancelar.php` | POST | `Bearer <artisanToken>` | Cancelación de pedido ajeno | 403 Forbidden | 403 Forbidden | ✅ PASS |
| 128-132 | `/api/pedidos/cancelar.php` | POST | `Bearer <artisanToken>` | Cancelación de pedido propio | 200 OK (Restituye stock en BD) | 200 OK (Restituye stock en BD) | ✅ PASS |
| 133-134 | `/api/pedidos/cancelar.php` | POST | `Bearer <artisanToken>` | 2da cancelación de mismo pedido | 409 Conflict (Idempotente) | 409 Conflict (Idempotente) | ✅ PASS |
| 135-139 | Preflight CORS (5 endpoints) | OPTIONS | `Origin: http://localhost:3000` | Headers CORS | 204 No Content | 204 No Content | ✅ PASS |

---

## 3. Evidencias de Respuestas JSON y Trazas HTTP

### 3.1 Solicitud Pública de Pedido (`POST /api/pedidos/solicitar.php`) — HTTP 201 Created
```json
{
  "exito": true,
  "mensaje": "Pedido registrado exitosamente. El artesano se pondrá en contacto contigo para acordar la entrega.",
  "datos": {
    "id": 14,
    "creacion_id": 1,
    "creacion_nombre": "Dragón Ignis",
    "cantidad": 1,
    "precio_final": 45000,
    "precio_final_formateado": "$450.00 MXN",
    "estado_pedido": "Pendiente",
    "estado_pago": "Pendiente",
    "es_sobre_encargo": 0,
    "mensaje": "Pedido registrado exitosamente. El artesano se pondrá en contacto contigo para acordar la entrega."
  }
}
```

### 3.2 Listado Aislado por Artesana (`GET /api/pedidos/index.php`) — HTTP 200 OK
```json
{
  "exito": true,
  "mensaje": "Listado de pedidos obtenido exitosamente.",
  "datos": [
    {
      "id": 2,
      "cliente_nombre": "Carlos Mendoza",
      "cliente_contacto": "+52 55 9301 8472",
      "creacion_id": 3,
      "cantidad": 2,
      "fecha_entrega": "2026-10-02",
      "estado_pedido": "Pendiente",
      "estado_pago": "Pendiente",
      "precio_final": 64000,
      "precio_final_formateado": "$640.00 MXN",
      "notas": "Cliente solicita que ambos ajolotes lleven un tono ligeramente más pastel en las branquias.",
      "activo": 1,
      "creacion": {
        "id": 3,
        "nombre": "Ajolote Rosado Pastel",
        "imagen_url": "uploads/ajolote.jpg",
        "precio": 32000,
        "precio_unitario_formateado": "$320.00 MXN",
        "costo_materiales": 8500,
        "cantidad_stock": 0,
        "es_sobre_encargo": 1,
        "artesano_id": 2,
        "artesano_username": "artesana_ana"
      },
      "enlace_whatsapp": "https://wa.me/525593018472?text=%C2%A1Hola%20Carlos%20Mendoza%21%20Te%20escribo%20de%20Crochet%20Manager%20con%20respecto%20a%20tu%20pedido%20%232%20de%20%27Ajolote%20Rosado%20Pastel%27."
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

### 3.3 Cancelación Transaccional con Restitución (`POST /api/pedidos/cancelar.php`) — HTTP 200 OK
```json
{
  "exito": true,
  "mensaje": "Pedido cancelado exitosamente. Se restituyeron 1 unidad(es) al stock de 'Tote Bag Boho Trapillo'.",
  "datos": {
    "id": 15,
    "estado_pedido": "Cancelado",
    "unidades_restituidas": 1,
    "creacion_id": 5,
    "creacion_nombre": "Tote Bag Boho Trapillo",
    "nuevo_stock": 6,
    "actualizado_en": "2026-09-12 16:07:10",
    "mensaje": "Pedido cancelado exitosamente. Se restituyeron 1 unidad(es) al stock de 'Tote Bag Boho Trapillo'."
  }
}
```

### 3.4 Idempotencia de Cancelación — HTTP 409 Conflict
```json
{
  "exito": false,
  "error": {
    "codigo": 409,
    "mensaje": "El pedido #15 ya se encuentra cancelado. No se pueden restituir existencias por segunda vez."
  }
}
```

---

## 4. Verificación de Transacciones en Base de Datos Relacional SQLite

Se validó en la base de datos física `database/database.sqlite`:
1. **Transacción de Compra Serializada:**
   - La inserción del pedido y el decremento de existencias (`UPDATE creaciones SET cantidad_stock = cantidad_stock - :cantidad`) se ejecutan dentro de `BEGIN IMMEDIATE TRANSACTION`.
   - Si la consulta de existencia detecta `cantidad_stock < :cantidad`, SQLite ejecuta `ROLLBACK` de inmediato y la API responde HTTP 409 Conflict.
2. **Restitución Atómica e Idempotente:**
   - Al cancelar, se bloquea el registro con `BEGIN IMMEDIATE TRANSACTION`, se valida `estado_pedido !== 'Cancelado'` y se incrementa exactamente la cantidad reservada.
   - En una segunda invocación inmediata, el estado `'Cancelado'` aborta la operación con `ROLLBACK`, impidiendo la duplicación errónea del inventario físico.
3. **Cero Borrados Físicos (Universal Soft-Delete):**
   - No existe ninguna sentencia `DELETE FROM pedidos`.
   - Las bajas ejecutan `UPDATE pedidos SET activo = 0, eliminado_en = datetime(...)`.
   - Las reactivaciones ejecutan `UPDATE pedidos SET activo = 1, eliminado_en = NULL`.

---

## 5. Conclusión y Estado de Aprobación

La **Subfase 3.5 (Pedidos, Transacciones Atómicas & Notificaciones WhatsApp)** ha sido implementada, testeada y verificada de forma integral:
- **139 de 139 aserciones aprobadas (100% OK)**.
- **532 de 532 aserciones acumuladas en toda la Fase 3 en verde**.
- Cero fugas de información, protección IDOR exhaustiva y transacciones ACID garantizadas en SQLite.

### 🛑 COMPÁS DE ESPERA OBLIGATORIO
Conforme a la regla de gobernanza y control de calidad de `.agents/rules/general.md`, el agente **se detiene completamente aquí**. Se solicita la **autorización explícita del usuario** antes de iniciar la **Subfase 3.6 (Auditoría Integral de Seguridad, Blindaje OWASP & Regresión Global)**.
