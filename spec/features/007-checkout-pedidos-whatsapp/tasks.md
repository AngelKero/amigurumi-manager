# 007 · Checkout Público, Pedidos Atómicos & WhatsApp — Tareas

**Estado:** propuesto (sin código) · derivado de `plan.md`

> 🧭 **Feature hija del plan maestro `009` (subfase 4.4).** Su `tasks.md` vive aquí; el avance
> global de la fase se registra en `009/tasks.md`. Gate 3-tier obligatorio (H-008/H-015/H-020).

## 1. Especificación (antes de código)

- [x] `spec.md` validado y aprobado por el usuario (HALT cumplido).
- [x] `plan.md` validado y aprobado por el usuario (HALT cumplido).

## 2. Checkout público (sin gate propio, verificado en §5)

- [x] `views/components/modal_checkout.php`: retirar `onsubmit=alert` + hardcode ($450/4, Mariana, fecha/notas default); `novalidate`; `hidden#checkoutCreacionId`; `#checkoutFeedback[role=alert]`; vista de éxito (`#checkoutSuccess`: folio, precio final, botón WhatsApp); fuera `fechaEntrega`. Verificación: `php -l` + `rg 'alert\('` vacío.
- [x] `src/js/modules/catalog.js` (`renderCard`): botón de compra con `data-id/data-precio-cents/data-stock/data-on-demand/data-name` reales. Verificación: `node --check`.
- [x] `views/pages/detalle_content.php` + `detail.js`: hidratar vía `GET detalle.php?id=`; compra propaga `creacion_id`. Verificación: `node --check` + curl página sin `$450` hardcodeado.
- [x] `src/js/modules/checkout.js`: apertura desde `data-*`/detalle; stepper (stock o 1000 encargo); total estimado (`currency.js`); `POST solicitar.php` JSON; `201` → éxito + WhatsApp servidor; `422/404/409` → feedback; cero `alert`/`innerHTML`. Verificación: `node --check` + `rg 'innerHTML ='` vacío.

## 3. Panel de pedidos server-driven (sin gate propio, verificado en §5)

- [x] `views/pages/pedidos_content.php`: retirar `$mockOrders`/KPIs PHP/`wa.me` crudo; grid vacía + loading; `<template id="pedidoCardTemplate">` (`data-part`); filtros (estado pedido/pago, búsqueda), paginación, vacío con reset, KPIs en 0. Verificación: `php -l`.
- [x] `views/components/modal_nuevo_pedido.php` (**nuevo**) + `modal_cancelar_pedido.php` (**nuevo**); registro en `pedidos.php`. Verificación: `php -l`.
- [x] `src/js/modules/orders.js`: `GET index.php` con Bearer + scoping; `buildQuery/fetchPage/renderCards/renderPagination`; crear manual (201/403); cambiar estado (200); cancelar (200/409 + restitución visible); WhatsApp solo servidor; `textContent`/DOM. Verificación: `node --check` + `rg 'innerHTML ='` vacío.
- [x] `app/Services/PedidoService.php`: `requestPublicOrder()` suma `enlace_whatsapp`; nada más. Verificación: `php -l` + regresión §6.

## 4. Gate de testing 3-tier (obligatorio, subfase 4.4)

- [x] Suite `tests/test-subfase-4.4.php` (§§1-6 del plan: vista checkout, JS + `node --check`, servicio —atomicidad, 409, precio servidor, idempotencia+restitución, IDOR 403, WhatsApp—, panel, HTTP vivo). Ejecutar: `php tests/test-subfase-4.4.php > logs/subfase-4.4-cli.log 2>&1` → 100% verde.
- [x] Pruebas HTTP contra `php -S localhost:8000` → `logs/subfase-4.4-http.log` (`solicitar` público 201 + stock−, 422 validación, 404 pieza inexistente, 409 agotado, cancelar → restituye → 409, páginas con CSP); divergencias según `docs/testing/protocolo-divergencia-cli-http.md` (H-015).
- [x] Reporte ejecutivo `docs/testing/subfase-4.4-pedidos.md` (plantilla `docs/testing/README.md`) con matriz + tabla AC→evidencia + "Fallos & Correcciones" + integridad SQLite.
- [x] Regresión acumulada: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` (H-020, auto-descubre 4.4).
- [x] Si se tocó backend: `php tests/test-subfase-3.6.5.php` (141) + `php tests/cuenta-aserciones.php` (**1.287**, H-006, con backup/restore de BD) en verde.
- [x] Orden seguro de claves: Fase 3 → normalizar claves README (`artesana123`/`asistente123`, 3.2 §4.10 las revierte) → gate Fase 4.
- [x] **HALT:** registrar en `009/tasks.md §4` y esperar aprobación explícita antes de 4.5.

## 5. Verificación & Cierre

- [x] Criterios de aceptación de `spec.md` al 100% (`- [x]` con tabla AC→evidencia en el reporte).
- [x] Regresión Fase 3: `php tests/test-subfase-3.6.5.php > logs/subfase-3.6.5-cli.log 2>&1`.
- [x] Regresión acumulada Fase 4: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1`.
- [x] Cifra regenerable: `php tests/cuenta-aserciones.php` (ground truth Fase 3 = 1.287, previo; backend solo aditivo).
- [x] `spec/constitution/roadmap.md`: subfase 4.4 → avance/Hecho; `docs/testing/README.md`: fila 4.4 + total Fase 4.
- [ ] **HALT:** aprobación explícita del usuario antes de la siguiente feature (008 / 4.5).
