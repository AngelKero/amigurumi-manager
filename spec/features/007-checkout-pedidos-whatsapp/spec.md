# 007 · Checkout Público, Pedidos Atómicos & WhatsApp (Subfase 4.4)

**Estado:** propuesto (solo spec · sin código)

> 🧭 **Feature hija del plan maestro de la Fase 4 (`spec/features/009-plan-maestro-fase-4/`).**
> Cubre la **subfase 4.4** con su gate 3-tier (suite CLI + logs CLI/HTTP + reporte) conforme a
> `009/tasks.md`.

## Qué hace

Conecta la compra pública y el panel de pedidos con la API real, sin recargas bruscas:

- **Checkout público (sin login):** los botones Comprar/Encargar del catálogo y la ficha de detalle abren el modal con los datos reales de la pieza (nombre, precio, stock y `creacion_id` propagado, sin el hardcode $450/4). El stepper acota la cantidad al stock (o a 1000 si es bajo encargo) con total en vivo. Al confirmar envía `POST /api/pedidos/solicitar.php` y ante `201` muestra confirmación con **precio congelado** y **enlace WhatsApp pre-formateado** (`?text=` codificado, E.164); ante `422/404/409`, feedback accesible en el propio modal.
- **Panel de pedidos server-driven (con Bearer):** `pedidos.php` lista desde `GET /api/pedidos/index.php` con scoping por rol (artesano solo los suyos, admin todos + filtro), KPIs y contadores reales, paginación y estado vacío con reset. Permite **crear pedido manual**, **cambiar estado de pedido/pago** y **cancelar** (idempotente: 2ª vez → `409`) con **restitución exacta de stock**. Todos los enlaces WhatsApp usan el formato del servidor (sin `wa.me` crudos).

## Por qué

El backend de pedidos está completo en Fase 3 (5 controladores delgados, transacción atómica con `409` sin stock, precio calculado en servidor, `buildWhatsAppLink`), pero el frontend es 100% mock: el modal usa `onsubmit="alert(...)"` con precio/stock hardcodeados y sin `creacion_id`; `pedidos_content.php` usa `$mockOrders` y `orders.js` muta solo el DOM (`nextOrderId=3`, `innerHTML`, WhatsApp sin `?text=` ni prefijo `52`). Sin este cableado ningún cliente puede comprar ni ningún artesano gestionar encargos reales. Es el tercer entregable funcional de la Fase 4 y valida end-to-end reserva atómica → restitución → contacto directo.

## Criterios de aceptación

- [x] Comprar/Encargar abre el modal con nombre, precio formateado, stock y `creacion_id` reales de la pieza (cero hardcode $450/4, cero `alert()`).
- [x] El submit envía `POST /api/pedidos/solicitar.php` (`creacion_id`, `cantidad`, `cliente_nombre`, `cliente_contacto`, `notas`); ante `201` muestra precio final congelado + enlace WhatsApp con `?text=`; ante `422/404/409`, error accesible (`role="alert"`) sin recargar.
- [x] La cantidad se acota al stock físico (1000 si bajo encargo) y el total se calcula en vivo en pesos (`currency.js`, R-06).
- [x] Tras un `201`, el stock de la pieza disminuye exactamente en la cantidad pedida; sin stock y sin encargo responde `409` (R-07).
- [x] El panel lista desde `GET /api/pedidos/index.php` con Bearer: artesano ve solo sus pedidos, admin todos; KPIs/contadores reales, paginación y vacío con reset total.
- [x] Crear pedido manual (artesano/admin) → `201`; sobre pieza ajena como artesano → `403` (R-04).
- [x] Cambiar estado de pedido/pago → `200`; cancelar → `200` con restitución exacta y segundo intento → `409` idempotente (R-07).
- [x] Todos los enlaces WhatsApp del panel y la confirmación usan el formato servidor (E.164 con `52`, `?text=` codificado); cero `wa.me` crudos o sin mensaje.
- [x] Cero datos del servidor en `innerHTML` (H-004); suite `tests/test-subfase-4.4.php` en verde, trazas en `logs/subfase-4.4-http.log` y reporte `docs/testing/subfase-4.4-pedidos.md` (H-008/H-015).
- [x] Regresión acumulada `php tests/test-fase-4-acumulado.php` en verde (H-020); Fase 3 y `1.287` intactas si se tocó backend compartido (H-006).

## Fuera de alcance

- **Directorio de creadores y RBAC de usuarios** (`usuarios.php`, reseteo de claves, salvaguarda ID #1): Feature 008, subfase 4.5.
- **Gestión de piezas** (crear/editar/stock/baja): Feature 006, subfases 4.3–4.3.3.
- **Pasarelas de pago o custodia de fondos**: la misión lo prohíbe; solo se registra `estado_pago` (Pendiente/Anticipo 50%/Liquidado) y se facilita el acuerdo por WhatsApp.
- **`fechaEntrega` en el checkout público**: `solicitar.php` no la contempla; vive solo en creación manual (o en `notas` a criterio del plan).
- **Plazos de taller centralizados**: prohibidos (R-03); la confirmación solo promete ficha rigurosa + contacto directo.
