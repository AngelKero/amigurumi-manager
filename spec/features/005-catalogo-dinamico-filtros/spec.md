# 005 · Catálogo Dinámico & Filtros Textiles (Subfase 4.2)

**Estado:** implementada y validada (AC 11/11 · suite 113/113 · gate 3-tier verde)

> 🧭 **Feature hija del plan maestro de la Fase 4 (`spec/features/009-plan-maestro-fase-4/`).**
> Cubre la **subfase 4.2** con su gate 3-tier (suite CLI + logs CLI/HTTP + reporte) conforme a
> `009/tasks.md`.

## Qué hace

Convierte la vitrina pública (`catalogo_content.php`, embebida en `index.php`) de un catálogo
estático renderizado en PHP (mock en `$catalogItems`) a un **catálogo reactivo alimentado por
la API real** `GET /api/creaciones/index.php`, sin recargar la página. El usuario puede:

- Ver las **creaciones activas** con stock en vivo, modalidad bajo encargo y precios en MXN.
- **Filtrar en tiempo real** por texto (nombre/material), categoría (chips textiles + dropdown
  sincronizados bidireccionalmente), estado de stock (En Stock / Bajo Encargo / Agotados), rango
  de precio mínimo-máximo y artesano creador (con listado real desde
  `GET /api/creaciones/artesanos.php`).
- **Ordenar** por más recientes o por precio (ascendente/descendente).
- **Paginarse** entre páginas con navegación artesanal, viendo "Mostrando X de Y creaciones".
- Ver el **estado vacío** ("Restablecer Filtros") cuando ningún producto coincide.

Los filtros se envían al servidor y la respuesta (datos + `paginacion`) se re-renderiza en la
rejilla; la sincronización chips ↔ dropdown y el contador de piezas se mantienen reactivos.

Además, provee un **script de siembra masiva** (`scripts/seed-catalogo-pruebas.php`, CLI-only)
que **registra en la base de datos todas las variantes posibles** del catálogo iterando el
producto cartesiano de los ejes: **categoría × material × estado de stock × rango de precio ×
artesano**. El script **lista todas las combinaciones posibles** (matriz de variantes) y las
inserta una por una; las imágenes pueden repetirse (se usa el **SVG temático de respaldo por
categoría**, R-09). Así filtros y paginación se prueban contra un dataset amplio y real en
lugar de un puñado de piezas mock.

## Por qué

El catálogo actual muestra un `$catalogItems` **mock hardcodeado** en la vista
(`views/pages/catalogo_content.php:8`), sin conexión al repositorio, de modo que las piezas
visibles no reflejan los datos reales ni el stock de la base de datos. El backend ya expone el
contrato completo (`CreacionService::getCatalog` con filtros `categoria, artesano_id,
precio_min, precio_max, busqueda, es_sobre_encargo, solo_en_stock, orden, pagina, limite` y
paginación). Este cableado es el primer entregable funcional de la Fase 4 visible para el
público y valida end-to-end el ciclo API → render → UX del sistema Algodón Nórdico. Para
verificar filtros y paginación de forma significativa se necesita un **dataset combinatorio
amplio**, por lo que la feature incluye el script de siembra que agota las variantes posibles
(véase arriba).

## Criterios de aceptación

- [x] Al cargar `index.php`, la rejilla `#productCardGrid` se puebla asíncronamente desde
      `GET /api/creaciones/index.php` (sin el mock PHP) y muestra el contador real de piezas.
- [x] Un clic en un **chip textil** (`.btn-chip-textile`) activa el chip, sincroniza el dropdown
      `#filterCategory` y dispara una petición con `categoria`; el dropdown provoca el mismo
      efecto inverso en los chips (sincronización bidireccional).
- [x] El **rango de precio** se transmite como **centavos enteros** (`precio_min`/`precio_max` a
      `GET /api/creaciones/index.php`) usando `currency.js` (R-06); la UI muestra el rango real
      en pesos y el servidor responde únicamente piezas dentro del rango.
- [x] El filtro **artesano** puebla su dropdown desde `GET /api/creaciones/artesanos.php` y
      filtra por `artesano_id` real (no por username mock).
- [x] La **paginación** es reactiva: los botones anterior/siguiente y las páginas consumen el
      bloque `paginacion` del API (total_items, pagina_actual, total_paginas) y re-renderizan la
      rejilla sin recarga; el chip "Mostrando X de Y" refleja el total real.
- [x] Ante **cero coincidencias**, se muestra `#emptyCatalogState` y su botón restablece TODOS
      los filtros (chips, dropdowns, precios, buscador y página).
- [x] Todo render de datos del servidor usa **DOM APIs/`textContent` o `escapeHtml`
      (`src/js/modules/dom-safe.js`)**: cero interpolación de datos en `innerHTML` (H-004).
- [x] Suite `tests/test-subfase-4.2.php` en verde (contrato de filtros/paginación del endpoint +
      `node --check` de `catalog.js`), trazas HTTP en `logs/subfase-4.2-http.log` y reporte
      `docs/testing/subfase-4.2-catalogo.md` (H-008/H-015).
- [x] `scripts/seed-catalogo-pruebas.php` es **CLI-only** (bloquea ejecución vía HTTP, como
      `setup.php`) e **idiempotente**: al re-ejecutarlo no duplica creaciones (omite los nombres
      ya existentes).
- [x] El script **lista todas las combinaciones posibles** (matriz de variantes:
      categoría × material × estado de stock × rango de precio × artesano) e inserta **≥ 200
      creaciones nuevas** en una base recién inicializada; las imágenes registradas son **SVG
      temáticos repetibles por categoría** (R-09, sin subir archivos).
- [x] Tras sembrar, `GET /api/creaciones/index.php` sin filtros devuelve `paginacion` con
      `total_items ≥ 205` y `total_paginas > 1` (dataset suficiente para paginar).

## Fuera de alcance

- **Gestión de creaciones** (crear/editar/stock/baja): Feature 006, subfase 4.3.
- **Checkout, pedidos atómicos y WhatsApp**: Feature 007, subfase 4.4.
- **Usuarios y roles RBAC**: Feature 008, subfase 4.5.
- **Autenticación** (login/logout/reactividad navbar): Feature 004, subfase 4.1.
- **Catálogo administrativo** (`creaciones_content.php`, panel del artesano): se mantiene como
  está; si requiere cableado adicional se registra como tarea de 006.