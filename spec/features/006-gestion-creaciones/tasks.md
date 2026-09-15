# 006 · Gestión de Creaciones & Subida Multipart — Tareas

**Estado:** propuesto (sin código) · derivado de `plan.md`

> 🧭 **Feature hija del plan maestro `009` (subfase 4.3).** Su `tasks.md` vive aquí; el avance
> global de la fase se registra en `009/tasks.md`. Gate 3-tier obligatorio (H-008/H-015/H-020).

## 1. Especificación (antes de código)

- [x] `spec.md` validado y aprobado por el usuario (HALT cumplido).
- [x] `plan.md` validado y aprobado por el usuario (HALT cumplido).

## 2. Formulario multipart crear/editar (sin gate propio, verificado en §5)

- [x] `views/pages/formulario_content.php`: retirar `$seedItems`/`$currentItem` + `onsubmit=alert` + rutas falsas; `form#creacionForm enctype="multipart/form-data"`; precarga edit (`?id=`) vía `GET /api/creaciones/detalle.php?id=`; `accept="image/jpeg,image/png,image/webp"`; bloque errores `role="alert"`; `artesano_id` solo admin. Verificación: `php -l views/pages/formulario_content.php`.
- [x] `src/js/modules/creaciones.js`: `submitCreacionForm(FormData)` con `Authorization: Bearer getToken()` → `POST crear.php` (201 + redirect) / `actualizar.php` (200); `pesosToCents` (R-06); errores `401/403/404/409/422` diferenciados sin `alert()`. Verificación: `node --check src/js/modules/creaciones.js`.
- [x] `src/js/modules/dropzone.js`: validación espejo MIME + ≤5MB + mensaje accesible; conserva preview/drag&drop; `btnRemoveImage` limpia el `input`. Verificación: `node --check src/js/modules/dropzone.js`.
- [x] `src/js/modules/margin-calculator.js` + `src/js/main.js`: sin cambios funcionales; guarda privada (sin token → `index.php`) y `initCreaciones/initDropzone/initMarginCalculator` con guardas. Verificación: `node --check src/js/main.js && node --check src/js/modules/margin-calculator.js`.

## 3. Panel server-driven + restauración (sin gate propio, verificado en §5)

- [x] `views/pages/creaciones_content.php`: retirar `$creacionesList` + KPIs/filtros mock; vaciar `#creacionesGrid`; `<template id="creacionCardTemplate">` constante; mapear selects a valores reales (`en_stock/bajo_encargo/agotados`, `artesano_id`, `recientes/precio_asc/...`); `#creacionesPaginationNav` + "Mostrando X de Y" + `#emptyCreacionesState` con reset; botón restaurar por tarjeta. Verificación: `php -l views/pages/creaciones_content.php`.
- [x] `views/components/modal_eliminar_creacion.php` + `modal_inspect_creacion.php` + nuevo `modal_restaurar_creacion.php`: `fetch` real con Bearer, `textContent` (H-004), `BtnEdit→formulario.php?id=`, `BtnViewDetail→detalle.php?id=`. Verificación: `php -l` de cada componente.
- [x] `creaciones.js` (panel): `buildQuery/fetchPage/renderCards/renderPagination`, seq/AbortController, debounce, dropdown artesanos desde `GET artesanos.php`, KPIs desde servidor. Verificación: `grep -c 'innerHTML =' src/js/modules/creaciones.js` → solo asignaciones numéricas/constantes o 0 con datos.

## 4. Mutaciones stock/toggle/baja (sin gate propio, verificado en §5)

- [x] `adjustStock(id,delta)` → `POST ajustar-stock.php` (clamp `0-10000`) + refresh badge/contador; `toggleEncargo(id,state)` → `POST toggle-encargo.php` + refresh `on-demand`. Verificación: suite §5 + curl HTTP vivo.
- [x] `removeCreation(id)` → `POST eliminar.php` (200, `activo=0`, foto en disco R-02, `detalle→404`, reintento 409); `restoreCreation(id)` → `POST restaurar.php` (200, `detalle→200`, reintento 409); IDOR ajena → `403` (R-04), admin → `200/201`. Verificación: suite §5 + curl HTTP vivo.

## 5. Gate de testing 3-tier (obligatorio, subfase 4.3)

- [x] Suite `tests/test-subfase-4.3.php` (§§1-7 del plan: vista, JS + `node --check`, servicio, upload R-09, IDOR R-04, soft-delete R-01/R-02 + restaurar, HTTP vivo). Ejecutar: `php tests/test-subfase-4.3.php > logs/subfase-4.3-cli.log 2>&1` → 100% verde.
- [x] Pruebas HTTP contra `php -S localhost:8000` → `logs/subfase-4.3-http.log` (OPTIONS 204, multipart 201 con `CURLFile png 1px`, 401 sin token, 403 ajena, 422 validación, 404 inactiva, 409 idempotente, CSP `script-src 'self'` + API `default-src 'none'`); divergencias según `docs/testing/protocolo-divergencia-cli-http.md` (H-015).
- [x] Reporte ejecutivo `docs/testing/subfase-4.3-creaciones.md` (plantilla `docs/testing/README.md`) con matriz de aserciones + tabla AC→evidencia + "Fallos Detectados & Correcciones Quirúrgicas" + integridad SQLite.
- [x] Regresión acumulada de fase: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` (H-020, auto-descubre 4.3).
- [x] Si se tocó backend compartido: `php tests/test-subfase-3.6.5.php` (141) + `php tests/cuenta-aserciones.php` (**1.287**, H-006) en verde sobre semilla limpia.
- [x] **HALT:** registrar en `009/tasks.md §4` y esperar aprobación explícita del usuario antes de 4.4.

## 6. Verificación & Cierre

- [x] Criterios de aceptación de `spec.md` al 100% (`- [x]` con tabla AC→evidencia en el reporte).
- [x] Regresión Fase 3: `php tests/test-subfase-3.6.5.php > logs/subfase-3.6.5-cli.log 2>&1`.
- [x] Regresión acumulada Fase 4: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1`.
- [x] Cifra regenerable: `php tests/cuenta-aserciones.php` (ground truth Fase 3 = 1.287).
- [x] `spec/constitution/roadmap.md`: subfase 4.3 → avance/Hecho según cierre; `docs/testing/README.md`: fila 4.3 añadida y total Fase 4 actualizado.

## 7. Correctivo 4.3.1 — Scoping servidor & papelera (ejecutado)

- [x] `api/creaciones/mias.php` + `CreacionService::getOwnCreations()` + índice `idx_creaciones_artesano_activo` (seed + BD viva) + ADR-017 + contrato §2b.
- [x] Panel con Bearer desde `mias.php`, `#filterEstadoSelect`, filtro de autor solo admin, baja/restaurar por `data-estado`.
- [x] Suite `tests/test-subfase-4.3.1.php` → `logs/subfase-4.3.1-cli.log` (**62/62**); HTTP → `logs/subfase-4.3.1-http.log`; reporte `docs/testing/subfase-4.3.1-panel-scoping.md`.
- [x] Regresiones: 4.3 (212/212), 3.6.5 (141/141, conteo 26), acumulado (**468/468**), H-006 (1.287).
- [x] README testing (fila 4.3.1 + total 468), `009/tasks.md`, `roadmap.md`.

## 8. Correctivo 4.3.2 — Contadores (ejecutado)

- [x] `getStockSummary()` + `getOwnSummary()` + `applyOwnershipScope()` + `mias.php?resumen=1` + contrato §2b.
- [x] Badge `N piezas`, rango `A–B de N`, KPIs globales exactos (sin loop 960).
- [x] Suite `tests/test-subfase-4.3.2.php` → `logs/subfase-4.3.2-{cli,http}.log` (**35/35**); reporte `docs/testing/subfase-4.3.2-contadores.md`.
- [x] Regresiones: 4.3 (212/212), 4.3.1 (62/62), 3.6.5 (141/141), acumulado (**503/503**).
- [x] README testing (fila 4.3.2 + total 503), spec AC-15/16, `009/tasks.md`, `roadmap.md`.
- [ ] **HALT:** aprobación explícita del usuario antes de la siguiente feature (007 / 4.4).
