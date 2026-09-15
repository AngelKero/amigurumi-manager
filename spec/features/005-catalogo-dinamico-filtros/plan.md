# 005 · Catálogo Dinámico & Filtros Textiles — Plan

**Estado:** propuesto (sin código) · léase con `spec.md`

## Enfoque

Sustituir el **filtrado 100% cliente** de `catalog.js` (oculta tarjetas ya renderizadas por el
mock PHP) por un flujo **server-driven**: la vitrina parte vacía, `catalog.js` serializa los
filtros en _query params_, invoca `GET /api/creaciones/index.php` y re-renderiza la rejilla con
la respuesta (`datos` + `paginacion`). El contrato API ya existe y está completo
(`CreacionService::getCatalog`); solo se completa un filtro que el contrato actual no cubre
(ver Decisiones · `estado_stock`).

Render seguro (H-004): se usa un `<template>` en la vista con marcado artesanal **constante** y
los datos se inyectan con `textContent`/DOM APIs; cero interpolación en `innerHTML`.

## Implementación

| Capa | Archivo(s) | Cambio |
| :--- | :--- | :--- |
| `views/pages/` | `catalogo_content.php` | Eliminar mock `$catalogItems` y su `foreach`; vaciar `#productCardGrid`; añadir `<template id="catalogCardTemplate">` con la tarjeta artesanal constante (botones buy/encargar con `data-*`, acciones de artesano) e indicador de carga (skeleton/spinner) y atributo `data-total` actualizable. |
| `src/js/modules/` | `catalog.js` | **Reescritura a server-driven:** `buildQuery()` (busqueda, categoria, `estado_stock`, `precio_min/max` en centavos vía `currency.js`, `artesano_id`, `orden`, `pagina`), `fetchCatalog()` con fetch + manejo de error, `renderCards(items)` clonando el `<template>` con `textContent`/`escapeHtml`, `renderPagination(paginacion)` (anterior/siguiente/páginas + "Mostrando X de Y"), sincronización bidireccional chips ↔ `#filterCategory`, población del dropdown de artesanos desde `GET /api/creaciones/artesanos.php`, debounce del buscador y estado vacío con reset. |
| `src/js/modules/` | `dom-safe.js` | Sin cambios (helpers ya existentes); se confirma su uso para nombre/material/descripción. |
| `src/js/` | `main.js` | Verificar que `initCatalog()` se ejecuta en `DOMContentLoaded` (carga o guardia si no existe). |
| `api/creaciones/` | (sin cambios) | Los endpoints `index.php` y `artesanos.php` ya cumplen el contrato. |
| `app/Services/` + `app/Repositories/` | `CreacionService.php` · `CreacionRepository.php` | **Extensión mínima de contrato (completar filtro stock):** añadir `estado_stock ∈ {en_stock, encargo, agotados}` a `getCatalog()`, `listCatalog()` y `countCatalog()` (ver Decisiones). Cero SQL fuera de repositorios (R-08/aïslamiento SQL). |
| `tests/` | `test-subfase-4.2.php` | Suite CLI: contrato del endpoint con/ sin filtros (datos + `paginacion`), valores en centavos, `estado_stock`, clamps de página; `node --check` de `catalog.js` y `main.js`; aserciones del template/vista (sin interpolación dinámica). |
| `scripts/` | `seed-catalogo-pruebas.php` | **Script de siembra combinatoria (CLI-only):** guard `php_sapi_name() === 'cli'`; define los ejes de variantes; calcula el **producto cartesiano** (5 categorías × 3 materiales × 3 estados × 3 bandas de precio × 2 artesanos = **270 creaciones**); **lista la matriz completa** en consola; inserta cada combinación vía `CreacionService::createCreation($data, null, currentUser=admin)` con `artesano_id` distribuido (imágenes = SVG temático repetible por categoría, R-09); **idiempotente** omitiendo nombres ya existentes; resumen final (insertadas / omitidas / totales). |
| `logs/` + `docs/testing/` | `subfase-4.2-{cli,http}.log` · `subfase-4.2-catalogo.md` | Gate 3-tier (H-008/H-015). |

## Decisiones

- **Filtrado server-driven, no cliente:** con paginación real el filtrado local es incorrecto
  (solo filtra la página visible). Alternativa descartada: mantener filtrado DOM y cargar todo
  el catálogo (rompe paginación y escala mal).
- **Completar el contrato con `estado_stock` (extensión de backend):** el contrato actual cubre
  `solo_en_stock` y `es_sobre_encargo` pero no "agotados" (stock = 0 y no encargo). Para
  respetar la paginación se añade el parámetro `estado_stock` en `CreacionService`/
  `CreacionRepository`. Alternativa descartada: filtrar "agotados" en cliente (incorrecto con
  paginación) y: descartada también retirar el chip "Agotados" del spec 005 (se mantiene).
- **Render con `<template>` + `textContent` (H-004):** el marcado artesanal vive constante en la
  vista; los datos se inyectan con DOM APIs → cero vectores `innerHTML`. Alternativa descartada:
  construir la tarjeta con template strings e interpolación (prohibido por la regla H-004).
- **Precios en centavos enteros (R-06):** la UI pide pesos, `currency.js.pesosToCents` convierte
  antes de serializar; el servidor ya acepta enteros > 1000 como centavos.
- **Dropdown de artesanos con datos reales (ADR-014):** se puebla desde
  `GET /api/creaciones/artesanos.php` usando `artesano_id`, no usernames mock.
- **Siembra combinatoria vía servicio de negocio (no SQL):** el seed inserta con
  `CreacionService::createCreation`, reutilizando validación y asignación automática de SVG
  temático (R-09); `imagen_url` queda `NULL` para que el fallback se repita por categoría. Cero
  INSERT directos fuera de repositorios. Alternativa descartada: `INSERT` masivo con `PDO::exec`
  en el script (viola aislamiento SQL y esquiva validaciones).
- **Dataset 5×3×3×3×2 = 270 variantes:** ejes = categorías oficiales del UI (5) × materiales
  (3) × estados de stock (en_stock / agotado / bajo_encargo) × bandas de precio (económico ≤
  $250, medio 250–600, premium ≥ $600) × artesanos (admin #1, artesana_ana #2). Suficiente para
  paginar (> 23 páginas a 12 por página). Alternativa descartada: una fila por categoría
  (dataset insuficiente para probar paginación).

## Riesgos

- **Race conditions en filtrado rápido:** respuestas fuera de orden → **Mitigación:** debounce
  (≈250 ms) en búsqueda y un token/secuencia de petición que descarta respuestas obsoletas
  (AbortController).
- **Página fuera de rango tras filtrar:** la página actual puede superar `total_paginas` →
  **Mitigación:** clamp desde la respuesta `paginacion.pagina_actual/total_paginas` y reset a
  página 1 al cambiar cualquier filtro.
- **Desincronización contrato API ↔ render:** campos renombrados en `enrichCreation` →
  **Mitigación:** suite 4.2 valida contra los campos reales del endpoint (precio_centavos,
  precio_formateado, dimensiones, imagen_url…).
- **`estado_stock` toca backend Fase 3:** riesgo de regresión → **Mitigación:** cambio
  quirúrgico SOLO en service/repositorio con prepared statements; regresión `test-subfase-3.6.5`
  (141) + `test-fase-4-acumulado` + `cuenta-aserciones` (1,287) tras la extensión.
- **Seed no idempotente / dataset inflado en re-ejecuciones:** → **Mitigación:** nombre único
  derivado de la combinación → consulta previa por nombre y **skip en lugar de insert**, con
  contadores de insertadas/omitidas en el resumen.
- **Dataset sembrado altera las cifras de la regresión 4.2 en BD viva:** las suites 4.2 corren
  sobre BD con dataset (tras `setup.php` + seed); las suites 3.X y `cuenta-aserciones` se
  ejecutan siempre sobre **semilla limpia** (resetean BD), por lo que no se contaminan.
- **FOUC / carga inicial en blanco:** → **Mitigación:** skeleton artesanal visible hasta el
  primer render exitoso.