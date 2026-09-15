# 006 · Gestión de Creaciones & Subida Multipart — Plan

**Estado:** propuesto (sin código) · léase con `spec.md`

## Enfoque

Reescritura del dominio de creaciones a **server-driven con multipart autenticado**, replicando
el patrón validado de 005 (`catalog.js`: `buildQuery → fetchCatalog → renderCards(<template>` +
`textContent`) → `renderPagination`) pero contra los **endpoints protegidos**
`POST api/creaciones/crear|actualizar|eliminar|restaurar|ajustar-stock|toggle-encargo.php`
con `Authorization: Bearer getToken()` (`auth.js`, Feature 004) y `FormData` (no JSON) cuando
hay foto. El contrato backend ya existe y está completo (`CreacionService::create/update/delete/
restore/adjustStock/toggleCommission`, 9 controladores ≤60 líneas); **cero cambios backend
previstos** salvo hallazgo Red con su regresión.

Render seguro (H-004): el marcado artesanal vive constante en `views/` (`<template>`); los datos
se inyectan con `textContent`/DOM APIs + `escapeHtml` (`dom-safe.js`). Precios en centavos
enteros vía `currency.js` (R-06). Validación de cliente en **espejo total** del servidor para
feedback inmediato; el servidor es la autoridad (422).

## Implementación

| Capa | Archivo(s) | Cambio |
| :--- | :--- | :--- |
| `views/pages/` | `formulario_content.php` | Retirar `$seedItems`/`$currentItem` mock + `onsubmit=alert` + rutas falsas; `form#creacionForm enctype=multipart/form-data`; precarga edit (`?id=`) vía `GET detalle.php`; `accept="image/jpeg,image/png,image/webp"`; bloque de errores `role="alert"` por campo; `artesano_id` solo visible para `admin` |
| `views/pages/` | `creaciones_content.php` | Retirar `$creacionesList` + KPIs/filtros mock; vaciar `#creacionesGrid`; `<template id="creacionCardTemplate">` constante; mapear `filterStockStatus→en_stock/bajo_encargo/agotados`, `filterArtisan→artesano_id` (desde `artesanos.php`), `sort→recientes/precio_asc/precio_desc/nombre_asc/nombre_desc/stock_desc`; `#creacionesPaginationNav` + "Mostrando X de Y" + `#emptyCreacionesState`; añadir botón restaurar por tarjeta |
| `views/components/` | `modal_eliminar_creacion.php`, `modal_inspect_creacion.php` | Sustituir `onclick=alert('/api/eliminar.php')` por `fetch` real con Bearer; textos con `textContent`; `BtnEdit→formulario.php?id=`, `BtnViewDetail→detalle.php?id=`; nuevo `modal_restaurar_creacion.php` (o botón inline con confirmación) |
| `src/js/modules/` | `creaciones.js` (reescritura server-driven) | `CREACIONES_URL='/api/creaciones/index.php'`; `buildQuery()` (busqueda, categoria, `estado_stock`, `precio_min/max` en centavos, `artesano_id`, `orden`, `pagina`); `fetchPage()` con seq/AbortController; `renderCards()` clonando `<template>` con `textContent`; `renderPagination()`; `submitCreacionForm(FormData)` (crear 201 / actualizar 200); `adjustStock(id,delta)`, `toggleEncargo(id,state)`, `removeCreation(id)`, `restoreCreation(id)`; manejo `201/200/401/403/404/409/422`; debounce buscador; reset total de filtros |
| `src/js/modules/` | `dropzone.js` | Mantener preview `FileReader` + drag&drop; añadir validación espejo MIME (`image/jpeg/png/webp`) + ≤5MB + mensaje accesible; limpiar `input` en `btnRemoveImage` |
| `src/js/modules/` | `margin-calculator.js` | Sin cambios funcionales (ya cálculo vivo + lock); verificar que lee/escribe pesos y delega conversión a `currency.js` |
| `src/js/` | `main.js` | `initCreaciones()` + `initDropzone()` + `initMarginCalculator()` en `DOMContentLoaded` con guardas de existencia; guarda privada: sin token redirige a `index.php` (patrón 004) |
| `api/` + `app/` | (sin cambios) | `crear/actualizar/eliminar/restaurar/ajustar-stock/toggle-encargo/index/detalle/artesanos.php` + `CreacionService/Repository` ya cumplen contrato; solo extensión quirúrgica si Red lo exige (prepared statements, R-08) |
| `tests/` | `test-subfase-4.3.php` | Suite CLI §§1-7: vista (sin mock, template, `enctype`, IDs), JS (`FormData`, Bearer, `pesosToCents`, `textContent`, `node --check` ≥4 módulos), servicio (validaciones 422, cents R-06, preservación imagen), upload R-09 (MIME/size/nombre/`basename`/fallback SVG), IDOR 403 R-04, soft-delete R-01/R-02 + `restaurar`, HTTP vivo (OPTIONS 204, multipart 201 con `CURLFile`, 401/403/422/404/409, CSP `script-src 'self'` + `default-src 'none'` API) |
| `logs/` + `docs/testing/` | `subfase-4.3-{cli,http}.log` · `subfase-4.3-creaciones.md` + fila en `docs/testing/README.md` | Gate 3-tier (H-008/H-015) + tabla AC→evidencia + "Fallos & Correcciones" |

## Decisiones

- **FormData multipart, no JSON-base64:** respeta `Request::file('imagen')` + `finfo` real + `move_uploaded_file` (R-09).
  - Alternativa descartada: serializar foto en base64 dentro de JSON (rompe límite 5MB, MIME y `$_FILES`).
- **Panel server-driven completo, no solo mutaciones:** con paginación real el filtrado local es incorrecto (solo filtra la página visible).
  - Alternativa descartada: mantener filtrado/KPI cliente y cablear solo mutaciones (decisión revertida por el usuario; rompe paginación y escala mal).
- **Restaurar con UI dedicada, no solo API:** sin ella la baja lógica es irreversible desde UX aunque `restaurar.php` exista (ADR-015).
  - Alternativa descartada: cubrir `restaurar.php` solo en tests (peor recuperabilidad del artesano).
- **Validación espejo completa en cliente, servidor autoridad:** duplica límites/tabla §4.1 de `docs/api/creaciones.md` en JS para feedback inmediato; todo rechazo final es 422 del servidor.
  - Alternativa descartada: validación mínima en cliente (peor UX, más roundtrips).
- **Render `<template>` + `textContent` (H-004):** marcado constante en vista, datos por DOM APIs; sanea los `innerHTML` actuales de `creaciones.js:100,292,299`.
  - Alternativa descartada: template strings interpolados (prohibido por H-004).
- **Cero cambios backend por defecto:** el contrato Fase 3 ya cubre todo 006; cualquier toque service/repo dispara regresión 3.6.5 + `cuenta-aserciones` (1.287).
  - Alternativa descartada: reescribir `CreacionService` (riesgo de regresión innecesario).

## Riesgos

- **Riesgo:** races en stock/toggle con clics rápidos (respuestas fuera de orden) → **Mitigación:** seq/AbortController + refetch tras mutación + deshabilitar botón durante `fetch`.
- **Riesgo:** página fuera de rango tras filtrar/mutar → **Mitigación:** clamp desde `paginacion.pagina_actual/total_paginas` + reset a p1 al cambiar filtros.
- **Riesgo:** desincronización `select` admin ↔ contrato (`estado_stock`, `orden`, `artesano_id`) → **Mitigación:** suite valida valores exactos del endpoint + `artesanos.php` real.
- **Riesgo:** fotos huérfanas/enormes en tests manuales → **Mitigación:** fixtures `CURLFile png 1px`, limpieza por baja lógica (nunca `DELETE`/`unlink` en baja, R-01/R-02), ≤5MB enforced.
- **Riesgo:** regresión Fase 3 / contaminación de dataset → **Mitigación:** suites 4.3 sobre BD sembrada; `test-subfase-3.6.5.php` (141) + `cuenta-aserciones.php` (1.287) siempre sobre semilla limpia; `test-fase-4-acumulado.php` tras el gate (H-020).
- **Riesgo:** divergencia CLI vs HTTP (multipart/CORS/CSP) → **Mitigación:** protocolo H-015 (reproducir 2×, clasificar entorno vs código, registrar traza); sin triaje no hay APTO.
