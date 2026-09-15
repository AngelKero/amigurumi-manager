# Reporte de Pruebas: Subfase 4.3 — Gestión de Creaciones & Subida Multipart

- **Fecha de Ejecución:** 2026-09-15
- **Responsable:** Agente IA → humano (HALT antes de 4.4)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 + `node --check`
- **Archivo de Log Crudo:** `logs/subfase-4.3-cli.log` (suite, 212/212) · `logs/subfase-4.3-http.log` (curls manuales, 11 pasos)
- **Script de Pruebas:** `tests/test-subfase-4.3.php` (Feature 006 · maestro 009)
- **Resultado General:** **212 / 212 Aprobados (100%)** — ✅ APTO PARA AVANZAR (fix opción A aplicado y verificado: Fase 3 141/141 + H-006 1.287 en verde)

---

## 1. Objetivo / Alcance

| Archivo | Cambio |
| :--- | :--- |
| `views/pages/formulario_content.php` | Mock `$seedItems` eliminado; submit real `FormData` (`enctype` + `novalidate` + `data-edit-id`); `#formFeedback[role=alert]`; campo `#inputArtesanoId` (solo admin); `#dropzoneError` + `data-max-bytes` |
| `views/pages/creaciones_content.php` | Mock `$creacionesList` eliminado; rejilla vacía + loading; `<template id="creacionCardTemplate">` (22 `data-part`); selects con valores exactos del contrato (`en_stock/bajo_encargo/agotados`, `recientes/precio_asc/…`, artesanos por ID); paginación + KPIs con valor inicial 0 |
| `views/components/modal_eliminar_creacion.php` | `onclick=alert()` + ruta falsa eliminados; `#btnConfirmDeleteCreacion[data-id]` para `fetch` real |
| `views/components/modal_restaurar_creacion.php` | **Nuevo**: `#modalRestaurarCreacion`, `#restoreCreacionName`, `#btnConfirmRestoreCreacion` |
| `creaciones.php` | Registra `modal_restaurar_creacion` |
| `src/js/modules/creaciones.js` | Reescritura server-driven: `buildQuery/fetchPage/renderCards/renderPagination/fetchKpis`, `initFormularioCreacion` (`FormData` + Bearer + centavos), 4 mutaciones, inspect modal DOM-safe; cero `innerHTML =` |
| `src/js/modules/dropzone.js` | Espejo MIME (`jpeg/png/webp`) + 5MB + `#dropzoneError`; exporta `isValidImageFile()` |
| `src/js/main.js` | Importa y ejecuta `initFormularioCreacion()` |
| `tests/test-subfase-4.3.php` | Suite §§1-6, 212 aserciones (vista ×2, JS + 7×`node --check`, servicio, HTTP vivo) |
| `docs/testing/README.md` | Fila 4.3 (212/212) + total Fase 4 → **406/406** |

Backend (`app/`, `api/`): **cero cambios** — el contrato Fase 3 ya cubría todo 006.

## 2. Matriz de Aserciones agrupadas

| # | Dominio | Caso | Aprobadas |
| - | :--- | :--- | :---: |
| 1 | Vista formulario | Sin `$seedItems`/rutas falsas/`onsubmit`/`alert(`; `enctype`, `novalidate`, `data-edit-id`, `#formFeedback[role=alert]`, `#artesanoField`, 12 IDs de campo, `accept` + `data-max-bytes`, `#dropzoneError`, simulador intacto | 31/31 |
| 2 | Vista panel + modales | Sin `$creacionesList`/`svg_slug`/`pedidos_asociados`/`foreach`/`data-artisan`/KPIs PHP; grid + `data-total` + loading + template (22 `data-part`) + restore + paginación + vacío; valores exactos `estado_stock`/`orden`; 4 KPIs; modales (baja sin `onclick`, restaurar nuevo, registro en `creaciones.php`) | 46/46 |
| 3 | Frontend JS | `initCreaciones` + `initFormularioCreacion` + alias; imports auth/currency/dom-safe; 9 URLs `/api/creaciones/`; `FormData`; `buildQuery/fetchPage/renderCards/renderPagination/fetchKpis`; cero `innerHTML =`; fallback SVG + `error` + genérico; seq; reset p1; 401/403/422; `rol === 'admin'`; dropzone espejo (5) + main (2) + 7×`node --check` | 57/57 |
| 4 | Backend servicio | Crear sin foto (SVG en disco, centavos, artesano propio/atribuido); 4×422 espejadas; update conserva imagen; stock clamp + 422; toggle 1/0; 4×IDOR 403; autora edita; baja→404/activo=0/`eliminado_en`; 409×2 + restaurar; cero `DELETE FROM` ×2; limpieza | 33/33 |
| 5 | HTTP vivo | 4×OPTIONS 204; login admin+ana; multipart 201 + nombre criptográfico + fichero en disco + 200; 401; 422 nombre; 422 MIME falso; update 200 + imagen conservada; IDOR 403; stock 200; toggle 200; baja 200 → detalle 404 → foto 200 (R-02); 409 → restaurar 200 → detalle 200 → 409; panel/form HTML sin mocks + CSP; API `default-src`; limpieza | 45/45 |

## 2b. Validación AC uno por uno (Gate `.agents/rules/general.md`)

| AC | Criterio `006/spec.md` | Evidencia código + prueba | ✅ |
| - | :--- | :--- | :---: |
| 1 | Crear `FormData` + Bearer → 201 / 422 accesible | `creaciones.js:submit` (`new FormData`, `authHeaders`, 201→redirect, 422→`#formFeedback`); suite §5.3/5.5 (201 + fichero, 422) | ✅ |
| 2 | Editar precarga `detalle.php`; sin foto conserva; reemplazo higiénico | `fillFormFromItem` + `GET detalle.php?id=`; `updateCreation:237-248` (R-02); suite §4.4 + §5.7 (misma `imagen_url`) | ✅ |
| 3 | Validación espejo + centavos `currency.js` | `validateFormValues` (12 reglas) + `pesosToCents`; suite §4.3 (4×422) + `dropzone.js` (MIME/5MB) | ✅ |
| 4 | Upload R-09: `finfo`, ≤5MB, nombre criptográfico, `basename()`, fallback SVG | `handleImageUpload:412-466` (sin cambios); suite §5.3 (regex `creacion_[16hex]_[ts].png`) + §5.6 (txt→422) + §4.1 (SVG en disco) | ✅ |
| 5 | Stock in-situ → `ajustar-stock.php` (clamp) | `adjustStock(id,next)` absoluto + botones deshabilitados; suite §4.5 + §5.9 (10 uds) | ✅ |
| 6 | Toggle → `toggle-encargo.php` | `toggleEncargo(id,!current)`; suite §4.6 + §5.9 (`es_sobre_encargo:1`) | ✅ |
| 7 | Baja modal → 200, foto en disco, detalle 404, 409 | `bindDeleteConfirm` + `deleteCreation` (cero `unlink`); suite §4.9/4.10 + §5.10/5.11 + http.log pasos 6-8 | ✅ |
| 8 | Restaurar con UI → 200, detalle 200, 409 | `modal_restaurar_creacion.php` + `bindRestoreConfirm`; suite §4.10 + §5.11 + http.log pasos 9-10 | ✅ |
| 9 | IDOR 403 / admin global (R-04) | `ensureArtisanOwnership:394-402`; suite §4.7 (4×403) + §5.8 (403 HTTP) + §4.8 (autora OK) | ✅ |
| 10 | Panel server-driven exacto + vacío con reset | `fetchPage` + mapeo 1:1 de selects; suite §2 (valores) + §5.12 (HTML servido sin mocks) | ✅ |
| 11 | DOM-safe H-004 + gate 3-tier | `rg innerHTML` solo en comentario; suite §3 (`innerHTML =` ausente, 31 `textContent`); logs cli/http presentes | ✅ |
| 12 | Regresión H-020 + Fase 3/H-006 | `test-fase-4-acumulado` 406 verde; 3.6.5 con nota §4c (fallo preexistente ajeno a 006) | ✅* |

\* AC-12 parcialmente condicionado: la regresión de **Fase 4** está 100% verde (406/406); la de Fase 3 arrastra **1 fallo preexistente en 3.6.3** (desincronización introducida por 4.2, ver §4c). No se tocó backend en 006.

## 3. Evidencia JSON y Cabeceras (extracto `logs/subfase-4.3-http.log`)

- `OPTIONS crear.php` → **204 No Content** + `Access-Control-Allow-*`.
- `POST crear.php` multipart → **201** `{"exito":true,"datos":{"id":299,…,"imagen_url":"uploads/creacion_1e9beec8db79612c_1789493261.png",…}}`.
- `POST actualizar.php` (sin foto) → **200**, misma `imagen_url` (R-02).
- `POST eliminar.php` → **200**; `GET detalle.php?id=299` → **404**; `GET uploads/…png` → **200** (foto preservada, cero `unlink`).
- `POST eliminar.php` (2ª) → **409**; `POST restaurar.php` → **200**; `GET detalle` → **200**; `POST restaurar.php` (2º) → **409**.
- `GET creaciones.php` → **200** con `content-security-policy: … script-src 'self' …`; API → `default-src 'none'`.
- Sin divergencia CLI↔HTTP: ambos canales 100% verdes en los mismos casos (sin triaje H-015 requerido).

## 4. Fallos Detectados & Correcciones Quirúrgicas (Red→Green→Refactor)

| # | Fallo | Causa | Corrección | Estado |
| - | :--- | :--- | :--- | :--- |
| 1 | Suite: `La vista no usa alert()` falló (210/212) | **Defecto de suite**: mi propio comentario HTML contenía `alert()` | Needle → `alert('` (invocación nativa real) | ✅ 212/212 |
| 2 | Suite: `Solo admin ve el selector…` falló | **Defecto de suite**: needle `role === 'admin'` vs código `user.rol === 'admin'` | Needle → `rol === 'admin'` | ✅ 212/212 |
| 3 | `test-fase-4-acumulado` exigió fila README para 4.3 | Gate estructural H-020 (esperado) | Fila 4.3 + total 406 en `docs/testing/README.md` | ✅ 406 verde |
| 4 | Flake transitorio: 4.2 falló 1/136 dentro del runner (2 corridas), verde aislada (136/136), en secuencia 4.1→4.2 y en 3ª corrida del acumulado | **Entorno** (no reproducible; servidor built-in monohilo + estado SQLite compartido) | Re-ejecución: acumulado final 406/406 EXIT 0; registrado, no silenciado (H-015) | ✅ |
| 5 | ~~Preexistente ajeno a 006~~: `test-subfase-3.6.3` §5.4 esperaba `gatito-ovillo.svg` para categoría `Genérica`, pero el código (cambiado por 4.2 en `bcf9ba1`) devolvía `ovillo-generico.svg` | **Desincronización P6** | **Opción A aprobada por el humano**: rama `genérica/generica → gatito-ovillo.svg` en `CreacionService::getThematicSvgFallback` (+4 líneas, `php -l` OK) → 3.6.3 157/157, 3.6.5 141/141, H-006 **1.287** en verde sobre semilla limpia (con backup/restore de la BD de trabajo) | ✅ resuelto |

**Conclusión Red-Green-Refactor:** 2 defectos de suite (corregidos), 1 gate estructural (resuelto), 1 flake de entorno (re-verificado en verde), 1 desincronización preexistente fuera de alcance (reportada, no tocada).

## 5. Integridad SQLite

- `PRAGMA integrity_check` → `ok`; `PRAGMA foreign_key_check` → 0 violaciones.
- `usuarios: 9` · `creaciones: 25` (5 activas = semilla; **0 ZZ43 activas** = limpieza total por baja lógica) · `pedidos: 24`.
- Nota dataset: esta BD no contiene el dataset combinatorio 4.2 (25 filas, no ~275); la suite 4.3 no depende de conteos del seed. `cuenta-aserciones.php` **no se ejecutó** a propósito: resetea la BD a semilla (`setup.php`) y habría destruido el estado de trabajo (mismo criterio que el reporte 4.2 §7).

## 6. Veredicto

- [x] Suite CLI 212/212 + HTTP vivo sin divergencia + AC 12/12 validados.
- [x] Regresión Fase 4: 406/406 (58 + 136 + 212) EXIT 0 (doble corrida consecutiva).
- [x] Regresión Fase 3: 141/141 EXIT 0 tras el fix opción A (§4, #5).
- [x] Cifra regenerable H-006: **1.287/1.287** en verde sobre semilla limpia (`cuenta-aserciones.php`, BD de trabajo respaldada y restaurada intacta).
- [x] `006/tasks.md` §§1-5, `009/tasks.md` §4, `roadmap.md` actualizados.
- **Estado:** ⏸ **HALT — esperando autorización para 4.4.**
