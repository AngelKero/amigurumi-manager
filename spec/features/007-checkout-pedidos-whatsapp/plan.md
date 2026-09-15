# 007 · Checkout Público, Pedidos Atómicos & WhatsApp — Plan

**Estado:** propuesto (sin código) · léase con `spec.md`

## Enfoque

Doble cableado con el mismo patrón server-driven de 005/006: **checkout público sin
auth** (JSON a `solicitar.php`, precio autoritativo del servidor) y **panel privado con
Bearer** (scoping por rol ya existente en `listOrders`, como `mias.php` en lectura).
El `creacion_id` viaja por `data-*` desde catálogo/detalle hasta el modal; el total del
modal se etiqueta **estimado** (el oficial lo congela el servidor). El enlace WhatsApp
se genera **solo en servidor** (`buildWhatsAppLink`, E.164 + `?text=`); el cliente lo
renderiza tal cual. Única extensión backend prevista: exponer `enlace_whatsapp` en la
respuesta de `requestPublicOrder` (hoy solo vive en `enrichOrder`).

## Implementación

| Capa | Archivo(s) | Cambio |
| :--- | :--- | :--- |
| `views/components/` | `modal_checkout.php` | Retirar `onsubmit=alert` + hardcode ($450/4, Mariana Gómez, fecha/notas default); `novalidate`; `hidden#checkoutCreacionId`; `#checkoutFeedback[role=alert]`; vista de éxito (`#checkoutSuccess`: precio final + botón WhatsApp + folio); fuera `fechaEntrega` del flujo público (solo manual) |
| `src/js/modules/` | `catalog.js` (`renderCard`) | Botón de compra con `data-id/data-precio-cents/data-stock/data-on-demand/data-name` reales (hoy sin `data-id`) |
| `views/pages/` | `detalle_content.php` + `src/js/modules/detail.js` | Hidratar ficha vía `GET detalle.php?id=` (adiós $450/stock 4 simulado); botón compra propaga `creacion_id` |
| `src/js/modules/` | `checkout.js` (reescritura) | Apertura desde `data-*`/detalle; stepper acotado (stock o 1000 encargo); total estimado (`currency.js`); `POST solicitar.php` JSON; `201` → éxito + WhatsApp servidor; `422/404/409` → feedback; cero `alert()`/`innerHTML` |
| `views/pages/` | `pedidos_content.php` | Retirar `$mockOrders`/KPIs PHP; grid vacía + loading; `<template id="pedidoCardTemplate">` (`data-part`); filtros (estado pedido/pago, búsqueda), paginación, vacío con reset, KPIs en 0; `wa.me/cliente_wa` fuera |
| `views/components/` | `modal_nuevo_pedido.php` (**nuevo**) + `modal_cancelar_pedido.php` (**nuevo**) | Alta manual (nombre, contacto, pieza, cantidad, fecha_entrega, estados, notas) + confirmación de cancelación con restitución |
| `src/js/modules/` | `orders.js` (reescritura) | `PEDIDOS_URL=index.php` (Bearer); `buildQuery/fetchPage/renderCards/renderPagination`; crear (`crear.php` 201, IDOR 403); cambiar estado (`cambiar-estado.php`); cancelar (`cancelar.php`, 409 idempotente + restitución visible); WhatsApp solo servidor; `textContent`/DOM |
| `src/js/` | `main.js` | `initCheckout()` + `initOrders()` ya existen: verificar guardas por página |
| `api/pedidos/` | (sin cambios) | Contrato completo (público `solicitar`, resto con RBAC + IDOR) |
| `app/Services/` | `PedidoService.php` | **Extensión mínima:** `requestPublicOrder()` suma `enlace_whatsapp` (`buildWhatsAppLink`); nada más. Regresión 3.6.5 + `cuenta-aserciones` (1.287) |
| `tests/` | `test-subfase-4.4.php` | §§1-6: vista checkout (sin mocks, `creacion_id`, feedback, éxito); `checkout.js` + `node --check`; servicio (atomicidad ±stock, 409, precio servidor, idempotencia+restitución, IDOR 403, WhatsApp E.164+`?text=`); panel (Bearer, scoping, CRUD estados, wa sin crudos); HTTP vivo (201 público + stock−, 422/404/409, cancelar→restituye→409, páginas CSP) |
| `logs/` + `docs/testing/` | `subfase-4.4-{cli,http}.log` · `subfase-4.4-pedidos.md` + fila README | Gate 3-tier (H-008/H-015) |

## Decisiones

- **WhatsApp solo en servidor:** `buildWhatsAppLink` ya implementa E.164 + `?text=`; duplicarlo en JS diverge (como hoy: crudos sin `52`). El cliente renderiza el enlace tal cual.
  - Alternativa descartada: componer el enlace en `orders.js/checkout.js` (duplicación, ya probado roto).
- **`fechaEntrega` fuera del modal público:** `solicitar.php` la ignora; enviarla en silencio es UX deshonesta. Vive en alta manual.
  - Alternativa descartada: concatenarla a `notas` automáticamente (ensucia el canal artesano-cliente).
- **Total del modal etiquetado "estimado":** el oficial lo congela el servidor (anti-manipulación); el modal muestra estimación viva.
  - Alternativa descartada: no mostrar total hasta el 201 (peor UX, el usuario confirma a ciegas).
- **Checkout público sin login (contrato vigente):** fricción mínima para comprar; el scoping solo aplica al panel.
  - Alternativa descartada: exigir sesión para comprar (rompe vitrina pública, fuera del contrato).
- **Sin cambios en la transacción:** `createAtomic/cancelOrderAtomic` ya cumplen R-07; el desfase doc (`BEGIN IMMEDIATE` vs `beginTransaction()`) se resuelve como riesgo (auditar y alinear doc o código según evidencia).

## Riesgos

- **Riesgo:** sobreventa concurrente (dos `solicitar` simultáneos sin stock) → **Mitigación:** check de stock dentro de la transacción + `409`; prueba de doble submit en la suite; `busy_timeout` (R-08).
- **Riesgo:** spam de pedidos (endpoint público sin throttle) → **Mitigación:** validaciones estrictas ya existentes; throttle (H-003-like) se registra como follow-up, no bloquea 4.4.
- **Riesgo:** `creacion_id` manipulado (pieza inexistente/inactiva o precio alterado) → **Mitigación:** el servidor re-lee la pieza (`404` si inactiva) y calcula el total (el cliente nunca envía precio); suite lo prueba.
- **Riesgo:** regresión Fase 3 por `enlace_whatsapp` en `requestPublicOrder` → **Mitigación:** adición de campo (sin cambiar lógica) + `test-subfase-3.6.5` + `cuenta-aserciones` (1.287).
- **Riesgo:** divergencia CLI/HTTP en multipart/JSON y estados → **Mitigación:** protocolo H-015; sin triaje no hay APTO.
- **Riesgo:** dataset de pedidos insuficiente para paginación → **Mitigación:** las suites crean piezas/pedidos efímeros con limpieza por baja lógica; no se requiere seed nuevo.
