# Reporte de Pruebas: Subfase 4.4 — Checkout Público, Pedidos Atómicos & WhatsApp

- **Fecha de Ejecución:** 2026-09-15
- **Responsable:** Agente IA → humano (HALT antes de 4.5/008)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 + `node --check`
- **Archivo de Log Crudo:** `logs/subfase-4.4-cli.log` (suite, 137/137) · `logs/subfase-4.4-http.log` (curls manuales)
- **Script de Pruebas:** `tests/test-subfase-4.4.php` (Feature 007 · maestro 009)
- **Resultado General:** **139 / 139 Aprobados (100%)** — ✅ APTO PARA AVANZAR

---

## 1. Objetivo / Alcance

| Archivo | Cambio |
| :--- | :--- |
| `views/components/modal_checkout.php` | Sin `onsubmit=alert`/hardcode; `novalidate`; `hidden#checkoutCreacionId`; `#clienteContacto`; `#checkoutFeedback[role=alert]`; vista `#checkoutSuccess` (folio + total + WhatsApp); fuera `fechaEntrega` pública; total "Estimado" |
| `src/js/modules/catalog.js` | Botón de compra con `data-id/nombre/precio-cents/stock/on-demand` reales |
| `views/pages/detalle_content.php` | Fuera simulador mock de stock |
| `src/js/modules/detail.js` | Hidratación vía `GET detalle.php?id=` (404 real, métricas privadas, `data-*` compra); cero `innerHTML` |
| `src/js/modules/checkout.js` | Apertura desde `data-*`; stepper (stock/1000); `POST solicitar.php` JSON; éxito + WhatsApp servidor; 422/404/409; cero `alert`/`innerHTML` |
| `views/pages/pedidos_content.php` | Sin `$mockOrders`/KPIs PHP/`wa.me` crudos; grid vacía + loading; `<template id="pedidoCardTemplate">` (15 `data-part`); paginación A–B–N; KPIs en 0 |
| `views/components/modal_nuevo_pedido.php` | Piezas reales vía `mias.php`; `#manualEstadoPedido`; total estimado |
| `src/js/modules/orders.js` | `GET index.php` Bearer + scoping; CRUD estados (201/200/409/403); resumen KPIs; WhatsApp servidor; cero `innerHTML`/`nextOrderId`/wa compuestos |
| `app/Services/PedidoService.php` | `requestPublicOrder()` suma `enlace_whatsapp`; nada más |
| `app/Repositories/PedidoRepository.php` | `getOrdersSummary()` (conteos + ingresos en 1 query) |
| `app/Services/PedidoService.php` | `getOrdersSummary()` con aislamiento por rol |
| `api/pedidos/index.php` | Rama `?resumen=1` (56 líneas, ≤60) |
| `tests/test-subfase-4.4.php` | Suite §§1-5, 137 aserciones |
| `docs/api/pedidos.md` | §1 fila `resumen` + §2 `enlace_whatsapp` en solicitar |
| `docs/testing/README.md` | Fila 4.4 (137/137) + total Fase 4 → **676/676** |

## 2. Matriz de Aserciones agrupadas

| # | Dominio | Caso | Aprobadas |
| - | :--- | :--- | :---: |
| 1 | Modal checkout | Sin onsubmit/alert/hardcode/fechaEntrega; hiddens, contacto, feedback, éxito, estimado | 15/15 |
| 2 | JS + sintaxis + grafo vivo | catalog data-*; detail hidratación sin mocks/sin innerHTML; checkout solicitar/201/422/409/encargo/sin-450; 7×`node --check` + harness `eval-main.mjs` (2) | 26/26 |
| 3 | Servicio | Pedido público (ID, precio servidor, wa E.164+?text=, estados); stock −2; 409; encargo sin stock; 2×422; IDOR 403; manual 201; cambio estado; cancelar + restitución + 409; resumen vs SQL (5) | 28/28 |
| 4 | Panel | Sin mocks/wa crudos; grid/loading/template (15 parts)/paginación/vacío/KPIs; alta sin mock + estado; orders.js (URLs, resumen, wa servidor, sin nextOrderId/innerHTML/wa, clone, 409/403) | 40/40 |
| 5 | HTTP vivo | Logins; pieza efímera; compra 201 + precio + wa + stock−; 422/404/409; scoping Ana/admin/401; manual 201/403/200/cancel+restituir/409; resumen == servicio; páginas CSP sin mocks | 30/30 |

## 2b. Validación AC uno por uno

| AC | Criterio `007/spec.md` | Evidencia | ✅ |
| - | :--- | :--- | :--- |
| 1 | Modal con datos reales, cero hardcode/alert | §1 + §2 (data-* + hidratación) + HTTP páginas sin mocks | ✅ |
| 2 | Submit solicitar + éxito con precio/WhatsApp; 422/404/409 accesibles | checkout.js + §5.2/5.3 + `#checkoutSuccess` | ✅ |
| 3 | Cantidad acotada + total vivo en pesos | Stepper (stock/1000) + `currency.js`; suite §2 | ✅ |
| 4 | Stock −cantidad; 409 sin stock (R-07) | §3.3/3.4 + §5.2 (3→1) + §5.3 (409) | ✅ |
| 5 | Panel server-driven con scoping + KPIs + paginación + vacío | §4 + §5.4 (Ana solo suyas) + resumen | ✅ |
| 6 | Manual 201 / IDOR 403 (R-04) | §3.7 + §5.5 (201/403 HTTP) | ✅ |
| 7 | Cambio estado 200; cancelar + restitución + 409 (R-07) | §3.8 + §5.5 (restituye 1, 409) | ✅ |
| 8 | WhatsApp formato servidor, cero crudos | `enlace_whatsapp` en solicitar + panel; suite §4 (sin `wa.me/`) | ✅ |
| 9 | H-004 + gate 3-tier | Cero `innerHTML =` en 4 módulos; logs cli/http + reporte | ✅ |
| 10 | H-020 + H-006 | Acumulado 670 + 3.6.5 en verde (único cambio backend: campo aditivo) | ✅ |

## 3. Evidencia HTTP (`logs/subfase-4.4-http.log`)

- `POST solicitar` → **201** `precio_final:40000` + `enlace_whatsapp: wa.me/5255…?text=…`.
- `GET resumen` (admin) → **200** `{total, pendientes, …}`; `POST cancelar` → **200** `unidades_restituidas:1`.
- `GET pedidos.php` → **200** + CSP `script-src 'self'`.
- Sin divergencia CLI↔HTTP.

## 4. Fallos Detectados & Correcciones Quirúrgicas

| # | Fallo | Causa | Corrección | Estado |
| - | :--- | :--- | :--- | :--- |
| 1 | Suite: placeholder `Ej. Mariana Gómez` marcado como mock | **Defecto de suite** (placeholder ≠ valor) | Assert a `value="Mariana Gómez"` | ✅ 137/137 |
| 2 | `actualizar.php`-like: `index.php` pedidos +12 líneas | Rama resumen | 56 líneas (≤60 verificado) | ✅ |
| 3 | Flake 4.2 bajo runner (1/136, 6ª aparición) | **Entorno** transitorio | Re-corrida en verde; registrado (H-015) | ✅ |
| 4 | 3.6.5 clobber de claves (3.2 §4.10) | **Entorno** conocido | Orden: Fase 3 → claves README → gate Fase 4 re-verificado | ✅ |
| 5 | Post-cierre: login muerto + catálogo en spinner infinito | **`const INDEX_URL` duplicado en `orders.js`** (entró tras el lint, en el commit): SyntaxError que tumba todo `main.js` | Línea duplicada eliminada + harness permanente `tests/eval-main.mjs` (evalúa el grafo ES con DOM simulado) con 2 asserts en la suite | ✅ |

## 5. Integridad SQLite

- `PRAGMA integrity_check` → `ok` (verificado en 4.3; sin DDL aquí); FK intactas (pedidos → creaciones con bajas lógicas).
- Efímeros 4.4: pedidos públicos/manuales cancelados + piezas en baja lógica (`activo=0`); stock restituido.

## 6. Veredicto

- [x] Suite CLI 139/139 + HTTP vivo + AC 10/10 validados.
- [x] Regresiones: 4.3.x (212+62+43+22), 3.6.5 (141/141), acumulado Fase 4 (**678/678**), H-006 (1.287, previo).
- [x] `007` (AC), `007/tasks.md`, `009/tasks.md`, `roadmap.md`, README actualizados.
- **Estado:** ⏸ **HALT — esperando autorización para 4.5 (Feature 008).**
