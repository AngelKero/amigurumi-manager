# 005 · Catálogo Dinámico & Filtros Textiles — Tareas

**Estado:** propuesto (sin código) · derivado de `plan.md`

> 🧭 **Feature hija del plan maestro `009` (subfase 4.2).** Su `tasks.md` vive aquí; el avance
> global de la fase se registra en `009/tasks.md`. Gate 3-tier obligatorio (H-008/H-015/H-020).

## 1. Especificación (aprobada)

- [x] `spec.md` validado y aprobado por el usuario (incluye el script de siembra combinatoria).
- [x] `plan.md` validado y aprobado por el usuario (incluye dataset 5×3×3×3×2 = 270).

## 2. Script de siembra combinatoria (prerequisito del dataset)

- [x] Crear `scripts/seed-catalogo-pruebas.php` **CLI-only** (guard `php_sapi_name() !== 'cli'`).
- [x] Definir los ejes: 5 categorías oficiales × 3 materiales × 3 estados de stock
      (`en_stock`, `agotado`, `bajo_encargo`) × 3 bandas de precio (≤ $250, 250–600, ≥ $600) ×
      2 artesanos (`admin` #1, `artesana_ana` #2).
- [x] Listar en consola la **matriz completa de combinaciones posibles** (todas las variantes).
- [x] Insertar cada combinación vía `CreacionService::createCreation($data, null, admin)`,
      con `artesano_id` distribuido; `imagen_url` quedará `NULL` → **SVG temático repetible por
      categoría** (R-09). Cero SQL directo fuera de repositorios.
- [x] **Idempotencia:** consultar por nombre único antes de insertar; omitir si ya existe.
- [x] Resumen de salida: creadas / omitidas / total de creaciones activas en BD.
- [x] Flujo de despliegue documentado: `php setup.php` → `php scripts/seed-catalogo-pruebas.php`.

## 3. Contrato backend `estado_stock` (extensión quirúrgica)

- [x] Añadir `estado_stock ∈ {en_stock, agotado, bajo_encargo}` a `CreacionService::getCatalog`
      y a `CreacionRepository::listCatalog`/`countCatalog` (prepared statements, R-08).
- [x] Regresión Fase 3 tras el cambio: `php tests/test-subfase-3.6.5.php` (141) y
      `php tests/cuenta-aserciones.php` (**1,287**) en verde.

## 4. Catálogo reactivo (server-driven)

- [x] `views/pages/catalogo_content.php`: eliminar mock `$catalogItems` + `foreach`; vaciar
      `#productCardGrid`; añadir `<template id="catalogCardTemplate">` (tarjeta artesanal
      constante, datos vía `textContent`/DOM APIs) e indicador de carga.
- [x] `src/js/modules/catalog.js`: `buildQuery()` (busqueda, categoria, `estado_stock`,
      `precio_min/max` en centavos con `currency.js`, `artesano_id`, `orden`, `pagina`),
      `fetchCatalog()` (fetch + secuencia/AbortController), `renderCards()` clonando el
      template, `renderPagination()` ("Mostrando X de Y" + navegación), sincronización
      bidireccional chips ↔ `#filterCategory`, dropdown de artesanos desde
      `GET /api/creaciones/artesanos.php`, debounce (≈250 ms) y estado vacío con reset.
- [x] `src/js/main.js`: asegurar `initCatalog()` en `DOMContentLoaded`.
- [x] Verificación estable de seguridad DOM (H-004): cero datos interpolados en `innerHTML`.

## 5. Gate de testing 3-tier (obligatorio)

- [x] Suite `tests/test-subfase-4.2.php`: contrato del endpoint con/sin filtros (datos +
      `paginacion`), valores en centavos, `estado_stock`, clamps de página, mínimo de dataset
      (total ≥ 205), `node --check` de módulos JS; aserciones de template/vista.
- [x] Sembrar dataset: `php setup.php` + `php scripts/seed-catalogo-pruebas.php`.
- [x] Ejecutar suite: `php tests/test-subfase-4.2.php > logs/subfase-4.2-cli.log 2>&1` → 100%.
- [x] Pruebas HTTP contra `php -S localhost:8000` → `logs/subfase-4.2-http.log` (filtros,
      centavos, paginación, `estado_stock`); divergencias según
      `docs/testing/protocolo-divergencia-cli-http.md` (H-015).
- [x] Reporte ejecutivo `docs/testing/subfase-4.2-catalogo.md` con "Fallos Detectados &
      Correcciones Quirúrgicas".
- [x] Regresión acumulada de fase: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` (H-020).
- [x] **HALT:** registrar en `009/tasks.md` y esperar aprobación explícita del usuario antes de 4.3.

## 6. Verificación & Cierre

- [x] Criterios de aceptación de `spec.md` al 100% (`- [x]`).
- [x] Actualizar `spec/constitution/roadmap.md`: subfase 4.2 → avance/Hecho según cierre.
- [x] **HALT:** aprobación explícita del usuario antes de la siguiente feature.