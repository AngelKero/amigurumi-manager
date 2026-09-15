<?php
/**
 * Test Suite: Subfase 4.2 - Catálogo Dinámico & Filtros Textiles (server-driven)
 * Feature 005 · Plan Maestro Fase 4 (009) · Clean Architecture - Algodón Nórdico Design System
 *
 * Valida de forma exhaustiva:
 * 1. Contrato de vista: mock PHP eliminado, `<template id="catalogCardTemplate">`,
 *    rejilla `#productCardGrid` vacía con loading y estación de paginación con IDs reactivos.
 * 2. Módulo `catalog.js`: server-driven (fetch a /api/creaciones/index.php), render con
 *    textContent/DOM APIs y CERO `innerHTML` (H-004); `node --check` de los módulos.
 * 3. Script de siembra `scripts/seed-catalogo-pruebas.php`: CLI-only, matriz combinatoria
 *    5×3×3×3×2 = 270 e idempotente (re-ejecución sin duplicados).
 * 4. Contrato backend `estado_stock` (en_stock / agotados / bajo_encargo): partición del
 *    dataset, filtros por categoría/artesano/precio en centavos/búsqueda/orden y clamps de
 *    página y límite.
 * 5. Pruebas HTTP en vivo contra localhost:8000: catálogo paginado, filtros combinados,
 *    endpoint de artesanos (ADR-014) y preflight CORS.
 *
 * Prerequisito de dataset (pipeline de la subfase):
 *   php setup.php
 *   php scripts/seed-catalogo-pruebas.php
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Services\CreacionService;

$root = dirname(__DIR__);

TestHelper::init('Subfase 4.2: Catálogo Dinámico & Filtros Textiles (server-driven)');

// =============================================================================
// 1. CONTRATO DE VISTA (catalogo_content.php)
// =============================================================================
TestHelper::section('1. Contrato de Vista: mock eliminado, template artesanal y rejilla reactiva');

$view = (string)@file_get_contents("$root/views/pages/catalogo_content.php");

TestHelper::assertFalse(str_contains($view, '$catalogItems'), 'catalogo_content.php ya no define el mock $catalogItems');
TestHelper::assertFalse(str_contains($view, 'product_card.php'), 'La vista ya no renderiza tarjetas vía require(product_card.php)');
TestHelper::assertStringContains('id="productCardGrid"', $view, 'La rejilla #productCardGrid permanece como destinataria del render');
TestHelper::assertStringContains('data-total="0"', $view, '#productCardGrid expone data-total actualizable');
TestHelper::assertStringContains('catalogLoadingState', $view, 'La vista incluye el indicador de carga #catalogLoadingState');
TestHelper::assertStringContains('<template id="catalogCardTemplate">', $view, 'La vista declara el <template id="catalogCardTemplate">');
TestHelper::assertStringContains('data-part="category"', $view, 'El template expone la parte [data-part=category]');
TestHelper::assertStringContains('data-part="dimensions"', $view, 'El template expone la parte [data-part=dimensions]');
TestHelper::assertStringContains('data-part="description"', $view, 'El template expone la parte [data-part=description]');
TestHelper::assertStringContains('data-part="price"', $view, 'El template expone la parte [data-part=price]');
TestHelper::assertStringContains('data-part="actionLabel"', $view, 'El template expone la parte [data-part=actionLabel]');
TestHelper::assertStringContains('data-part="stockBadge"', $view, 'El template expone la parte [data-part=stockBadge]');
TestHelper::assertStringContains('data-part="artisanMeta"', $view, 'El template expone la parte [data-part=artisanMeta]');
TestHelper::assertStringContains('data-part="productImg"', $view, 'El template expone la parte [data-part=productImg]');
TestHelper::assertStringContains('id="paginationNav"', $view, 'La estación de paginación expone #paginationNav');
TestHelper::assertStringContains('id="paginationShowingCount"', $view, 'El chip "Mostrando X de Y" expone #paginationShowingCount');
TestHelper::assertStringContains('id="paginationTotalCount"', $view, 'El chip "Mostrando X de Y" expone #paginationTotalCount');
TestHelper::assertStringContains('id="filterResultsCountText"', $view, 'El contador de piezas expone #filterResultsCountText');
TestHelper::assertStringContains('id="emptyCatalogState"', $view, 'Se conserva #emptyCatalogState con botón de restablecimiento');
TestHelper::assertStringContains('id="textileCategoryChips"', $view, 'Se conserva el grupo de chips textiles #textileCategoryChips');
TestHelper::assertStringContains('value="name-asc"', $view, 'El selector de orden ofrece Nombre: A → Z (name-asc)');
TestHelper::assertStringContains('value="name-desc"', $view, 'El selector de orden ofrece Nombre: Z → A (name-desc)');
TestHelper::assertStringContains('value="stock-desc"', $view, 'El selector de orden ofrece Mayor existencia (stock-desc)');

// =============================================================================
// 2. MÓDULO catalog.js (server-driven) + node --check
// =============================================================================
TestHelper::section('2. Contrato Frontend catalog.js: server-driven y DOM seguro (H-004)');

$catalogJs = (string)@file_get_contents("$root/src/js/modules/catalog.js");

TestHelper::assertStringContains('export function initCatalog', $catalogJs, 'catalog.js exporta initCatalog()');
TestHelper::assertStringContains("const CATALOG_URL = '/api/creaciones/index.php';", $catalogJs, 'catalog.js consume GET /api/creaciones/index.php (API real)');
TestHelper::assertStringContains("const ARTISANS_URL = '/api/creaciones/artesanos.php';", $catalogJs, 'catalog.js consume GET /api/creaciones/artesanos.php (ADR-014)');
TestHelper::assertStringContains('import { pesosToCents } from \'./currency.js\';', $catalogJs, 'catalog.js convierte pesos→centavos vía currency.js (R-06)');
TestHelper::assertStringContains('import { isAuthenticated } from \'./auth.js\';', $catalogJs, 'catalog.js conserva isAuthenticated() para acciones de artesano');
TestHelper::assertStringContains('function buildQuery', $catalogJs, 'catalog.js define buildQuery() (serialización de filtros)');
TestHelper::assertStringContains('function fetchCatalog', $catalogJs, 'catalog.js define fetchCatalog()');
TestHelper::assertStringContains('function renderCards', $catalogJs, 'catalog.js define renderCards()');
TestHelper::assertStringContains('function renderPagination', $catalogJs, 'catalog.js define renderPagination()');
TestHelper::assertStringContains('estado_stock', $catalogJs, 'catalog.js serializa el parámetro estado_stock');
TestHelper::assertStringContains('precio_min', $catalogJs, 'catalog.js serializa precio_min');
TestHelper::assertStringContains('precio_max', $catalogJs, 'catalog.js serializa precio_max');
TestHelper::assertStringContains('artesano_id', $catalogJs, 'catalog.js serializa artesano_id (no usernames mock)');
TestHelper::assertStringContains('URLSearchParams', $catalogJs, 'catalog.js construye la query con URLSearchParams (encoding correcto de &)');
TestHelper::assertStringContains('.cloneNode(true)', $catalogJs, 'catalog.js clona el template artesanal por tarjeta');
TestHelper::assertStringContains('.textContent =', $catalogJs, 'catalog.js inyecta datos con textContent (H-004)');
TestHelper::assertFalse(str_contains($catalogJs, 'innerHTML ='), 'catalog.js NO asigna innerHTML en absoluto (cero vectores H-004)');
TestHelper::assertStringContains('imagen_fallback_svg', $catalogJs, 'catalog.js resuelve el SVG temático de respaldo por categoría (R-09)');
TestHelper::assertStringContains("addEventListener('error'", $catalogJs, 'catalog.js escucha el error de carga de imagen para aplicar el respaldo en vivo');
TestHelper::assertStringContains('sources.push', $catalogJs, 'catalog.js encadena fuentes de imagen (imagen → temático → genérico)');
TestHelper::assertStringContains('ovillo-generico.svg', $catalogJs, 'catalog.js cierra la cadena con el SVG genérico como último recurso (R-09)');
TestHelper::assertStringContains("'name-desc': 'nombre_desc'", $catalogJs, 'catalog.js mapea name-desc → nombre_desc');
TestHelper::assertStringContains("'stock-desc': 'stock_desc'", $catalogJs, 'catalog.js mapea stock-desc → stock_desc');
TestHelper::assertStringContains('state.seq', $catalogJs, 'catalog.js usa secuencia/token para descartar respuestas obsoletas');
TestHelper::assertStringContains('page > totalPages', $catalogJs, 'catalog.js aplica clamp de página fuera de rango tras filtrar');

// 2.2 node --check de los módulos JS touchados
$jsChecks = [
    'catalog.js' => "$root/src/js/modules/catalog.js",
    'main.js'    => "$root/src/js/main.js",
    'currency.js' => "$root/src/js/modules/currency.js",
    'dom-safe.js' => "$root/src/js/modules/dom-safe.js",
];
foreach ($jsChecks as $label => $jsPath) {
    exec('node --check ' . escapeshellarg($jsPath) . ' 2>&1', $jsOut, $jsExit);
    TestHelper::assertSame(0, $jsExit, "node --check de {$label} sin errores de sintaxis");
}

// =============================================================================
// 3. SCRIPT DE SIEMBRA COMBINATORIA (CLI-only · idempotente)
// =============================================================================
TestHelper::section('3. Script de Siembra: matriz 5×3×3×3×2 = 270 e idempotencia');

$seedScript = "$root/scripts/seed-catalogo-pruebas.php";
$seedPhp = (string)@file_get_contents($seedScript);

TestHelper::assertTrue(is_file($seedScript), 'scripts/seed-catalogo-pruebas.php existe');
TestHelper::assertStringContains("if (php_sapi_name() !== 'cli')", $seedPhp, 'El seed bloquea ejecución vía HTTP (CLI-only como setup.php)');
TestHelper::assertStringContains('$categorias = [', $seedPhp, 'El seed define el eje de categorías');
TestHelper::assertStringContains('$materiales = [', $seedPhp, 'El seed define el eje de materiales');
TestHelper::assertStringContains('$estados = [', $seedPhp, 'El seed define el eje de estados de stock');
TestHelper::assertStringContains('$bandas = [', $seedPhp, 'El seed define el eje de bandas de precio');
TestHelper::assertStringContains('$artesanos = [', $seedPhp, 'El seed define el eje de artesanos');
TestHelper::assertStringContains('CreacionService::createCreation', $seedPhp, 'El seed inserta vía servicio de negocio (cero SQL directo)');
TestHelper::assertStringContains('existsCreationByName', $seedPhp, 'El seed omite variantes ya existentes (idempotencia)');

$service = new CreacionService();

// 3.1 Re-ejecución del seed: idempotencia real (no duplica creaciones)
$totalBefore = $service->getCatalog()['paginacion']['total_items'];
exec('php ' . escapeshellarg($seedScript) . ' 2>&1', $seedOut, $seedExit);
$totalAfter = $service->getCatalog()['paginacion']['total_items'];
TestHelper::assertSame(0, $seedExit, 'El seed termina con código de salida 0');
TestHelper::assertSame($totalBefore, $totalAfter, 'Re-ejecutar el seed NO modifica el total de creaciones (idempotente)');

// =============================================================================
// 4. CONTRATO BACKEND: estado_stock, filtros, centavos y paginación
// =============================================================================
TestHelper::section('4. Contrato Backend: estado_stock, filtros en centavos y paginación');

// 4.1 Dataset sembrado suficiente para paginar (AC del spec 005)
$catalogAll = $service->getCatalog();
$pagAll = $catalogAll['paginacion'];
TestHelper::assertArrayHasKey('datos', $catalogAll, 'getCatalog() devuelve la clave datos');
TestHelper::assertArrayHasKey('paginacion', $catalogAll, 'getCatalog() devuelve la clave paginacion');
TestHelper::assertTrue($pagAll['total_items'] >= 205, 'Sin filtros, total_items >= 205 (dataset residual post-siembra)');
TestHelper::assertTrue($pagAll['total_paginas'] > 1, 'Dataset suficiente para paginar (total_paginas > 1)');
TestHelper::assertTrue(isset($catalogAll['datos']) && is_array($catalogAll['datos']), 'La respuesta expone el arreglo datos');
TestHelper::assertArrayHasKey('total_paginas', $pagAll, 'paginacion expone total_paginas');
TestHelper::assertArrayHasKey('total_items', $pagAll, 'paginacion expone total_items');
TestHelper::assertArrayHasKey('pagina_actual', $pagAll, 'paginacion expone pagina_actual');
TestHelper::assertArrayHasKey('limite', $pagAll, 'paginacion expone limite');
TestHelper::assertArrayHasKey('tiene_siguiente', $pagAll, 'paginacion expone tiene_siguiente');
TestHelper::assertArrayHasKey('tiene_anterior', $pagAll, 'paginacion expone tiene_anterior');

// 4.2 Campos monetarios duales en centavos enteros (R-06)
$firstItem = $catalogAll['datos'][0] ?? [];
TestHelper::assertTrue(isset($firstItem['precio_centavos']) && is_int($firstItem['precio_centavos']), 'Los datos expuestos incluyen precio_centavos entero');
TestHelper::assertTrue(isset($firstItem['precio_formateado']) && is_string($firstItem['precio_formateado']), 'Los datos expuestos incluyen precio_formateado');
TestHelper::assertTrue(str_starts_with((string)($firstItem['precio_formateado'] ?? ''), '$'), 'precio_formateado inicia con símbolo $');

// 4.2b Vector temático SVG de respaldo por categoría (R-09) — renders resilientes
TestHelper::assertTrue(isset($firstItem['imagen_fallback_svg']) && is_string($firstItem['imagen_fallback_svg']), 'Cada pieza del catálogo expone imagen_fallback_svg');
TestHelper::assertStringContains('assets/svg/piezas/', (string)($firstItem['imagen_fallback_svg'] ?? ''), 'imagen_fallback_svg apunta a la biblioteca de vectores temáticos');
$fallbacksOk = true;
foreach ($catalogAll['datos'] as $item) {
    $fb = (string)($item['imagen_fallback_svg'] ?? '');
    if ($fb === '' || !is_file($root . '/' . $fb)) $fallbacksOk = false;
}
TestHelper::assertTrue($fallbacksOk, 'Todas las piezas de la primera página mapean a SVG temáticos que existen en disco');

// 4.2c SVG genérico como último recurso: sin imagen o sin coincidencia temática → ovillo-generico.svg
$generic = $service->getThematicSvgFallback('Categoría Inexistente 123', 'Nombre Sin Coincidencia Temática');
TestHelper::assertStringContains('ovillo-generico.svg', $generic, 'Sin coincidencia temática, el resolutor cede ante el SVG genérico');
TestHelper::assertTrue(is_file($root . '/' . $generic), 'El SVG genérico ovillo-generico.svg existe en disco');
$allFallbacksDiskOk = true;
$pages = (int)$service->getCatalog()['paginacion']['total_paginas'];
for ($pg = 1; $pg <= $pages; $pg++) {
    foreach ($service->getCatalog(['pagina' => $pg, 'limite' => 48])['datos'] as $item) {
        $fb = (string)($item['imagen_fallback_svg'] ?? '');
        if ($fb === '' || !is_file($root . '/' . $fb)) $allFallbacksDiskOk = false;
    }
}
TestHelper::assertTrue($allFallbacksDiskOk, 'TODAS las 275 piezas del catálogo (todas las páginas) tienen imagen_fallback_svg en disco sin excepciones');

// 4.3 Partición por estado_stock (en_stock + agotados + bajo_encargo = total)
$enStock = $service->getCatalog(['estado_stock' => 'en_stock']);
$agotados = $service->getCatalog(['estado_stock' => 'agotados']);
$bajoEncargo = $service->getCatalog(['estado_stock' => 'bajo_encargo']);

TestHelper::assertTrue($enStock['paginacion']['total_items'] > 0, 'estado_stock=en_stock devuelve piezas con existencias');
TestHelper::assertTrue($agotados['paginacion']['total_items'] > 0, 'estado_stock=agotados devuelve piezas sin stock ni encargo');
TestHelper::assertTrue($bajoEncargo['paginacion']['total_items'] > 0, 'estado_stock=bajo_encargo devuelve piezas bajo encargo');
TestHelper::assertSame(
    $pagAll['total_items'],
    $enStock['paginacion']['total_items'] + $agotados['paginacion']['total_items'] + $bajoEncargo['paginacion']['total_items'],
    'Los tres estados de stock particionan todo el catálogo (en_stock + agotados + bajo_encargo = total)'
);

// 4.3b Orden por defecto (recientes): los agotados SIEMPRE al final (refinamiento UX)
$defaultItems = $service->getCatalog()['datos'];
$tiers = array_map(
    static fn(array $i): int => ((int)$i['cantidad_stock']) > 0 ? 0 : (((int)$i['es_sobre_encargo']) === 1 ? 1 : 2),
    $defaultItems
);
$nonDecreasing = true;
for ($t = 1; $t < count($tiers); $t++) {
    if ($tiers[$t] < $tiers[$t - 1]) $nonDecreasing = false;
}
TestHelper::assertTrue($nonDecreasing, 'Orden por defecto: agotados (sin stock ni encargo) nunca preceden a piezas disponibles');
TestHelper::assertTrue(isset($tiers[0]) && $tiers[0] === 0, 'La primera pieza del catálogo por defecto está en stock');
TestHelper::assertTrue((int)($defaultItems[0]['cantidad_stock'] ?? 0) > 0, 'El primer ítem por defecto expone cantidad_stock > 0');

// 4.4 Cada filtro estado_stock filtra correctamente
$filtrados = true;
foreach ($enStock['datos'] as $item) {
    if ((int)$item['cantidad_stock'] <= 0) $filtrados = false;
}
TestHelper::assertTrue($filtrados, 'estado_stock=en_stock devuelve únicamente cantidad_stock > 0');

$filtrados = true;
foreach ($agotados['datos'] as $item) {
    if ((int)$item['cantidad_stock'] !== 0 || (int)$item['es_sobre_encargo'] !== 0) $filtrados = false;
}
TestHelper::assertTrue($filtrados, 'estado_stock=agotados devuelve únicamente stock = 0 y sin encargo');

$filtrados = true;
foreach ($bajoEncargo['datos'] as $item) {
    if ((int)$item['es_sobre_encargo'] !== 1) $filtrados = false;
}
TestHelper::assertTrue($filtrados, 'estado_stock=bajo_encargo devuelve únicamente es_sobre_encargo = 1');

// 4.5 Filtro por categoría
$catCatalog = $service->getCatalog(['categoria' => 'Amigurumis & Figuras']);
TestHelper::assertTrue($catCatalog['paginacion']['total_items'] > 40, 'Filtrando por categoría Amigurumis & Figuras hay más de 40 piezas (54 variantes + 2 base)');
$filtrados = true;
foreach ($catCatalog['datos'] as $item) {
    if ($item['categoria'] !== 'Amigurumis & Figuras') $filtrados = false;
}
TestHelper::assertTrue($filtrados, 'La categoría se filtra en servidor (match exacto)');

// 4.6 Filtro por rango de precio en centavos enteros
$priceCatalog = $service->getCatalog(['precio_min' => 25000, 'precio_max' => 50000]);
TestHelper::assertTrue($priceCatalog['paginacion']['total_items'] > 0, 'El rango de precio 250–500 MXN devuelve coincidencias');
$filtrados = true;
foreach ($priceCatalog['datos'] as $item) {
    if ((int)$item['precio_centavos'] < 25000 || (int)$item['precio_centavos'] > 50000) $filtrados = false;
}
TestHelper::assertTrue($filtrados, 'precio_min/precio_max se envían en centavos y el servidor respeta el rango');

// 4.7 Filtro por artesano real (artesano_id)
$artisanCatalog = $service->getCatalog(['artesano_id' => 2]);
TestHelper::assertTrue($artisanCatalog['paginacion']['total_items'] > 100, 'El artesano #2 (artesana_ana) posee más de 100 piezas (135 variantes + 2 base)');
$filtrados = true;
foreach ($artisanCatalog['datos'] as $item) {
    if ((int)$item['artesano_id'] !== 2) $filtrados = false;
}
TestHelper::assertTrue($filtrados, 'El filtro artesano_id filtra por el creador real (no username)');

// 4.8 Búsqueda por texto (material)
$searchCatalog = $service->getCatalog(['busqueda' => 'Trapillo']);
TestHelper::assertTrue($searchCatalog['paginacion']['total_items'] > 80, 'La búsqueda "Trapillo" devuelve más de 80 piezas');
$filtrados = true;
foreach ($searchCatalog['datos'] as $item) {
    $haystack = mb_strtolower((string)$item['nombre'] . ' ' . (string)$item['material']);
    if (!str_contains($haystack, 'trapillo')) $filtrados = false;
}
TestHelper::assertTrue($filtrados, 'La búsqueda por texto coincide con nombre o material');

// 4.9 Ordenación por precio ascendente
$sortCatalog = $service->getCatalog(['orden' => 'precio_asc', 'limite' => 48]);
$asc = true;
$prevPrice = -1;
foreach ($sortCatalog['datos'] as $item) {
    $p = (int)$item['precio_centavos'];
    if ($p < $prevPrice) $asc = false;
    $prevPrice = $p;
}
TestHelper::assertTrue($asc, 'orden=precio_asc devuelve precios no decrecientes');

// 4.9b Nuevos ordenamientos: nombre A-Z / Z-A (NOCASE) y mayor existencia (stock_desc)
$nameAsc = $service->getCatalog(['orden' => 'nombre_asc', 'limite' => 48]);
$nameAscOk = true;
$prevName = '';
foreach ($nameAsc['datos'] as $item) {
    $n = mb_strtolower((string)$item['nombre']);
    if ($prevName !== '' && $n < $prevName) $nameAscOk = false;
    $prevName = $n;
}
TestHelper::assertTrue($nameAscOk, 'orden=nombre_asc devuelve nombres en orden alfabético A→Z (NOCASE)');
TestHelper::assertTrue(isset($nameAsc['datos'][0]['nombre']) && $nameAsc['datos'][0]['nombre'] !== '', 'orden=nombre_asc devuelve piezas (primer ítem con nombre)');

$nameDesc = $service->getCatalog(['orden' => 'nombre_desc', 'limite' => 48]);
$nameDescOk = true;
$prevName = '';
foreach ($nameDesc['datos'] as $item) {
    $n = mb_strtolower((string)$item['nombre']);
    if ($prevName !== '' && $n > $prevName) $nameDescOk = false;
    $prevName = $n;
}
TestHelper::assertTrue($nameDescOk, 'orden=nombre_desc devuelve nombres en orden alfabético inverso Z→A (NOCASE)');
TestHelper::assertTrue($nameDesc['datos'][0]['nombre'] !== $nameAsc['datos'][0]['nombre'], 'nombre_desc invierte el sentido: primer ítem distinto al de nombre_asc');

$stockDesc = $service->getCatalog(['orden' => 'stock_desc', 'limite' => 48]);
$stockDescOk = true;
$prevStock = PHP_INT_MAX;
foreach ($stockDesc['datos'] as $item) {
    $s = (int)$item['cantidad_stock'];
    if ($s > $prevStock) $stockDescOk = false;
    $prevStock = $s;
}
TestHelper::assertTrue($stockDescOk, 'orden=stock_desc devuelve existencias no crecientes (más stock primero)');
TestHelper::assertTrue((int)($stockDesc['datos'][0]['cantidad_stock'] ?? 0) > 0, 'orden=stock_desc arranca con una pieza con existencia disponible');

// 4.10 Clamps de página y límite
$outOfRange = $service->getCatalog(['pagina' => 9999]);
TestHelper::assertSame(0, count($outOfRange['datos']), 'Una página fuera de rango devuelve datos vacíos (sin error)');
TestHelper::assertTrue($outOfRange['paginacion']['total_items'] > 0, 'paginacion conserva el total real pese a la página fuera de rango');
$pageOne = $service->getCatalog(['pagina' => 0]);
TestHelper::assertTrue(count($pageOne['datos']) > 0, 'pagina=0 se normaliza a la página 1 (sin respuestas vacías)');
$limitCapped = $service->getCatalog(['limite' => 999]);
TestHelper::assertTrue(count($limitCapped['datos']) <= 48, 'limite=999 queda limitado al máximo del contrato (48)');

// =============================================================================
// 5. PRUEBAS HTTP EN VIVO CONTRA LOCALHOST:8000
// =============================================================================
TestHelper::section('5. Pruebas HTTP en Vivo contra el Servidor (catálogo, filtros, artesanos)');

// 5.1 Preflight CORS del endpoint de catálogo
$corsRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/creaciones/index.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: GET',
]);
TestHelper::assertTrue(
    in_array($corsRes['status'], [200, 204], true),
    'Preflight CORS OPTIONS en /api/creaciones/index.php responde correctamente (' . $corsRes['status'] . ')'
);

// 5.2 Catálogo paginado sin filtros
$httpCat = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php');
TestHelper::assertSame(200, $httpCat['status'], 'HTTP GET /api/creaciones/index.php devuelve 200 OK');
TestHelper::assertTrue($httpCat['json']['exito'] ?? false, 'Respuesta de catálogo contiene exito: true');
TestHelper::assertTrue(is_array($httpCat['json']['datos'] ?? null), 'Respuesta de catálogo contiene datos[]');
TestHelper::assertTrue(($httpCat['json']['paginacion']['total_items'] ?? 0) >= 205, 'HTTP: total_items >= 205 (dataset sembrado)');
TestHelper::assertTrue(($httpCat['json']['paginacion']['total_paginas'] ?? 1) > 1, 'HTTP: total_paginas > 1');

// 5.3 Filtros combinados por HTTP (categoría + estado_stock + precio en centavos)
$httpFiltered = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php?categoria=' . rawurlencode('Bolsos & Accesorios') . '&estado_stock=agotados&precio_min=20000&precio_max=90000');
TestHelper::assertSame(200, $httpFiltered['status'], 'HTTP GET con filtros combinados devuelve 200 OK');
$httpFilteredOk = true;
foreach (($httpFiltered['json']['datos'] ?? []) as $item) {
    if ($item['categoria'] !== 'Bolsos & Accesorios') $httpFilteredOk = false;
    if ((int)$item['cantidad_stock'] !== 0 || (int)$item['es_sobre_encargo'] !== 0) $httpFilteredOk = false;
    if ((int)$item['precio_centavos'] < 20000 || (int)$item['precio_centavos'] > 90000) $httpFilteredOk = false;
}
TestHelper::assertTrue($httpFilteredOk, 'HTTP: los filtros combinados se respetan en servidor');

// 5.3b Pieza base con fotografía ausente: el API expone el SVG temático de respaldo y se sirve 200
$httpDragon = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php?busqueda=Ignis');
TestHelper::assertSame(1, count($httpDragon['json']['datos'] ?? []), 'La búsqueda "Ignis" localiza la pieza base #1 (foto uploads/ ausente)');
$fbHttpUrl = 'http://localhost:8000/' . ltrim((string)($httpDragon['json']['datos'][0]['imagen_fallback_svg'] ?? ''), '/');
TestHelper::assertStringContains('dragon-ignis.svg', (string)($httpDragon['json']['datos'][0]['imagen_fallback_svg'] ?? ''), 'El respaldo de Dragón Ignis es el vector temático dragon-ignis.svg');
TestHelper::assertSame(200, TestHelper::curl('GET', $fbHttpUrl)['status'], 'El SVG temático de respaldo de la pieza base se sirve con 200 OK');

// 5.3c HTTP: nuevos ordenamientos (nombre A-Z / Z-A, mayor existencia) por query string
$httpNameAsc = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php?orden=nombre_asc&limite=12');
$httpNameAscOk = true;
foreach (($httpNameAsc['json']['datos'] ?? []) as $i => $item) {
    if ($i > 0) {
        $prev = mb_strtolower((string)$httpNameAsc['json']['datos'][$i - 1]['nombre']);
        if (mb_strtolower((string)$item['nombre']) < $prev) $httpNameAscOk = false;
    }
}
TestHelper::assertSame(200, $httpNameAsc['status'], 'HTTP ?orden=nombre_asc devuelve 200 OK');
TestHelper::assertTrue($httpNameAscOk, 'HTTP: orden=nombre_asc respeta el orden alfabético en servidor');

$httpStockDesc = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php?orden=stock_desc&limite=12');
TestHelper::assertSame(200, $httpStockDesc['status'], 'HTTP ?orden=stock_desc devuelve 200 OK');
TestHelper::assertTrue((int)($httpStockDesc['json']['datos'][0]['cantidad_stock'] ?? 0) > 0, 'HTTP: orden=stock_desc arranca con una pieza con existencia disponible');

// 5.4 Endpoint de artesanos activos (ADR-014)
$httpArtisans = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/artesanos.php');
TestHelper::assertSame(200, $httpArtisans['status'], 'HTTP GET /api/creaciones/artesanos.php devuelve 200 OK');
TestHelper::assertTrue($httpArtisans['json']['exito'] ?? false, 'Respuesta de artesanos contiene exito: true');
TestHelper::assertTrue(is_array($httpArtisans['json']['datos'] ?? null) && count($httpArtisans['json']['datos']) > 0, 'El dropdown de artesanos recibe datos reales');
$artisanShapeOk = true;
foreach (($httpArtisans['json']['datos'] ?? []) as $artisan) {
    if (!isset($artisan['id'], $artisan['username'], $artisan['total_creaciones'])) $artisanShapeOk = false;
}
TestHelper::assertTrue($artisanShapeOk, 'Cada artesano expone id, username y total_creaciones');

// 5.5 Página pública con rejilla reactiva y CSP intacta
$httpPage = TestHelper::curl('GET', 'http://localhost:8000/index.php');
TestHelper::assertSame(200, $httpPage['status'], 'HTTP GET /index.php devuelve 200 OK');
TestHelper::assertStringContains('id="productCardGrid"', $httpPage['body'], 'El HTML servido incluye #productCardGrid');
TestHelper::assertStringContains('id="catalogCardTemplate"', $httpPage['body'], 'El HTML servido incluye el template catalogCardTemplate');
TestHelper::assertFalse(str_contains($httpPage['body'], '$catalogItems'), 'El HTML servido no contiene restos del mock PHP');
$cspPage = strtolower($httpPage['headers']['content-security-policy'] ?? '');
TestHelper::assertStringContains("script-src 'self'", $cspPage, 'CSP de la página HTML restringe scripts al propio origen (H-004)');

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);