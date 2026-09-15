<?php
/**
 * Test Suite: Subfase 4.3 - Gestión de Creaciones & Subida Multipart (server-driven)
 * Feature 006 · Plan Maestro Fase 4 (009) · Clean Architecture - Algodón Nórdico Design System
 *
 * Valida de forma exhaustiva:
 * 1. Vista formulario: mock $seedItems eliminado, submit real (FormData), feedback
 *    accesible, campo artesano admin, validación espejo de imagen (MIME + 5MB).
 * 2. Vista panel: mock $creacionesList eliminado, <template id="creacionCardTemplate">,
 *    rejilla vacía con loading, paginación con IDs reactivos, valores exactos del
 *    contrato (estado_stock, orden, artesano_id) y modal de restauración nuevo.
 * 3. Módulos JS: creaciones.js server-driven (fetch + Bearer + FormData + centavos)
 *    con CERO `innerHTML` (H-004); dropzone.js con espejo MIME/tamaño; main.js cablea
 *    initFormularioCreacion; `node --check` de los módulos.
 * 4. Backend vía servicio: crear/actualizar/baja/restaurar/ajustar-stock/toggle,
 *    validaciones 422, centavos enteros (R-06), preservación de imagen (R-02),
 *    IDOR 403 (R-04) y cero DELETE FROM (R-01).
 * 5. Upload R-09 vía servicio: se cubre en §6 por HTTP multipart real.
 * 6. Pruebas HTTP en vivo contra localhost:8000: multipart 201 con foto real
 *    (patrón creacion_[hex]_[ts].png), 401 sin token, 422 validación/MIME, 403 IDOR,
 *    ciclo baja 200 → detalle 404 → foto preservada 200 (R-02) → restaurar 200 →
 *    detalle 200, idempotencia 409 y páginas con CSP intacta.
 *
 * Prerequisito: servidor local `php -S localhost:8000` en la raíz del repo.
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

TestHelper::init('Subfase 4.3: Gestión de Creaciones & Subida Multipart (server-driven)');

// =============================================================================
// 1. CONTRATO DE VISTA: formulario_content.php (sin mock, submit real)
// =============================================================================
TestHelper::section('1. Contrato de Vista Formulario: mock eliminado y submit multipart real');

$formView = (string)@file_get_contents("$root/views/pages/formulario_content.php");

TestHelper::assertFalse(str_contains($formView, '$seedItems'), 'formulario_content.php ya no define el mock $seedItems');
TestHelper::assertFalse(str_contains($formView, "'Dragón Ignis'"), 'formulario_content.php ya no precarga la pieza mock Dragón Ignis');
TestHelper::assertFalse(str_contains($formView, '/api/crear.php'), 'La vista ya no cita la ruta falsa POST /api/crear.php');
TestHelper::assertFalse(str_contains($formView, '/api/actualizar.php'), 'La vista ya no cita la ruta falsa POST /api/actualizar.php');
TestHelper::assertFalse(str_contains($formView, 'onsubmit='), 'El formulario ya no usa onsubmit con alert() mock');
TestHelper::assertFalse(str_contains($formView, 'alert(\''), 'La vista no invoca diálogos nativos alert()');
TestHelper::assertStringContains('id="creacionForm"', $formView, 'El formulario conserva id="creacionForm"');
TestHelper::assertStringContains('enctype="multipart/form-data"', $formView, 'El formulario declara enctype multipart/form-data');
TestHelper::assertStringContains('novalidate', $formView, 'El formulario delega la validación espejo al módulo JS (novalidate)');
TestHelper::assertStringContains('data-edit-id', $formView, 'El formulario expone data-edit-id para el modo edición');
TestHelper::assertStringContains('id="formFeedback"', $formView, 'El formulario incluye #formFeedback para errores accesibles');
TestHelper::assertStringContains('role="alert"', $formView, 'El feedback usa role="alert" (accesible, sin alert() nativo)');
TestHelper::assertStringContains('id="artesanoField"', $formView, 'El formulario incluye el campo de artesano autor (solo admin)');
TestHelper::assertStringContains('id="inputArtesanoId"', $formView, 'El campo de artesano expone #inputArtesanoId (artesano_id real)');
TestHelper::assertStringContains('id="inputNombre"', $formView, 'El formulario conserva #inputNombre');
TestHelper::assertStringContains('id="inputCategoria"', $formView, 'El formulario conserva #inputCategoria');
TestHelper::assertStringContains('id="inputMaterial"', $formView, 'El formulario conserva #inputMaterial');
TestHelper::assertStringContains('id="inputDimensiones"', $formView, 'El formulario conserva #inputDimensiones');
TestHelper::assertStringContains('id="inputStock"', $formView, 'El formulario conserva #inputStock');
TestHelper::assertStringContains('id="inputPrecio"', $formView, 'El formulario conserva #inputPrecio');
TestHelper::assertStringContains('id="inputCosto"', $formView, 'El formulario conserva #inputCosto');
TestHelper::assertStringContains('id="inputHoras"', $formView, 'El formulario conserva #inputHoras');
TestHelper::assertStringContains('id="inputDescripcion"', $formView, 'El formulario conserva #inputDescripcion');
TestHelper::assertStringContains('id="inputEsSobreEncargo"', $formView, 'El formulario conserva #inputEsSobreEncargo');
TestHelper::assertStringContains('id="inputImagen"', $formView, 'El formulario conserva #inputImagen (file)');
TestHelper::assertStringContains('accept="image/jpeg,image/png,image/webp"', $formView, '#inputImagen restringe a JPEG/PNG/WebP (R-09)');
TestHelper::assertStringContains('data-max-bytes="5242880"', $formView, '#inputImagen expone el techo de 5MB para la validación espejo (R-09)');
TestHelper::assertStringContains('id="dropzoneError"', $formView, 'La zona de subida incluye #dropzoneError accesible');
TestHelper::assertStringContains('id="simuladorMargen"', $formView, 'Se conserva el simulador de márgenes #simuladorMargen');

// =============================================================================
// 2. CONTRATO DE VISTA: creaciones_content.php + modales (server-driven)
// =============================================================================
TestHelper::section('2. Contrato de Vista Panel: mock eliminado, template y paginación reactiva');

$panelView = (string)@file_get_contents("$root/views/pages/creaciones_content.php");

TestHelper::assertFalse(str_contains($panelView, '$creacionesList'), 'creaciones_content.php ya no define el mock $creacionesList');
TestHelper::assertFalse(str_contains($panelView, 'svg_slug'), 'La vista ya no usa slugs mock de SVG (svg_slug)');
TestHelper::assertFalse(str_contains($panelView, 'pedidos_asociados'), 'La vista ya no usa el contador mock pedidos_asociados');
TestHelper::assertFalse(str_contains($panelView, 'foreach ($creacionesList'), 'La vista ya no renderiza tarjetas con foreach PHP');
TestHelper::assertFalse(str_contains($panelView, 'data-artisan='), 'La vista ya no expone data-artisan con usernames mock');
TestHelper::assertFalse(str_contains($panelView, '$kpiValorInventario'), 'Los KPIs ya no se calculan en PHP sobre el mock');
TestHelper::assertStringContains('id="creacionesGrid"', $panelView, 'La rejilla #creacionesGrid permanece como destinataria del render');
TestHelper::assertStringContains('data-total="0"', $panelView, '#creacionesGrid expone data-total actualizable');
TestHelper::assertStringContains('creacionesLoadingState', $panelView, 'La vista incluye el indicador de carga #creacionesLoadingState');
TestHelper::assertStringContains('<template id="creacionCardTemplate">', $panelView, 'La vista declara el <template id="creacionCardTemplate">');
TestHelper::assertStringContains('data-part="cardCol"', $panelView, 'El template expone la parte [data-part=cardCol]');
TestHelper::assertStringContains('data-part="idBadge"', $panelView, 'El template expone la parte [data-part=idBadge]');
TestHelper::assertStringContains('data-part="encargoToggle"', $panelView, 'El template expone la parte [data-part=encargoToggle]');
TestHelper::assertStringContains('data-part="photo"', $panelView, 'El template expone la parte [data-part=photo]');
TestHelper::assertStringContains('data-part="nombre"', $panelView, 'El template expone la parte [data-part=nombre]');
TestHelper::assertStringContains('data-part="categoria"', $panelView, 'El template expone la parte [data-part=categoria]');
TestHelper::assertStringContains('data-part="dimensiones"', $panelView, 'El template expone la parte [data-part=dimensiones]');
TestHelper::assertStringContains('data-part="material"', $panelView, 'El template expone la parte [data-part=material]');
TestHelper::assertStringContains('data-part="precio"', $panelView, 'El template expone la parte [data-part=precio]');
TestHelper::assertStringContains('data-part="costo"', $panelView, 'El template expone la parte [data-part=costo]');
TestHelper::assertStringContains('data-part="margen"', $panelView, 'El template expone la parte [data-part=margen]');
TestHelper::assertStringContains('data-part="retorno"', $panelView, 'El template expone la parte [data-part=retorno]');
TestHelper::assertStringContains('data-part="stockDec"', $panelView, 'El template expone la parte [data-part=stockDec]');
TestHelper::assertStringContains('data-part="stockVal"', $panelView, 'El template expone la parte [data-part=stockVal]');
TestHelper::assertStringContains('data-part="stockInc"', $panelView, 'El template expone la parte [data-part=stockInc]');
TestHelper::assertStringContains('data-part="stockBadge"', $panelView, 'El template expone la parte [data-part=stockBadge]');
TestHelper::assertStringContains('data-part="authorInitial"', $panelView, 'El template expone la parte [data-part=authorInitial]');
TestHelper::assertStringContains('data-part="authorName"', $panelView, 'El template expone la parte [data-part=authorName]');
TestHelper::assertStringContains('data-part="inspectBtn"', $panelView, 'El template expone la parte [data-part=inspectBtn]');
TestHelper::assertStringContains('data-part="editLink"', $panelView, 'El template expone la parte [data-part=editLink]');
TestHelper::assertStringContains('data-part="deleteBtn"', $panelView, 'El template expone la parte [data-part=deleteBtn]');
TestHelper::assertStringContains('data-part="restoreBtn"', $panelView, 'El template expone la parte [data-part=restoreBtn] (restauración con UI)');
TestHelper::assertStringContains('btn-card-restore', $panelView, 'La tarjeta incluye el botón .btn-card-restore');
TestHelper::assertStringContains('id="creacionesPaginationNav"', $panelView, 'La estación de paginación expone #creacionesPaginationNav');
TestHelper::assertStringContains('id="creacionesShowingFrom"', $panelView, 'El chip "Mostrando A–B de N" expone #creacionesShowingFrom (rango real, 4.3.2)');
TestHelper::assertStringContains('id="creacionesTotalCount"', $panelView, 'El chip "Mostrando X de Y" expone #creacionesTotalCount');
TestHelper::assertStringContains('id="emptyCreacionesState"', $panelView, 'Se conserva #emptyCreacionesState con botón de restablecimiento');
TestHelper::assertStringContains('value="en_stock"', $panelView, 'El filtro de stock usa el valor real del contrato (en_stock)');
TestHelper::assertStringContains('value="bajo_encargo"', $panelView, 'El filtro de stock usa el valor real del contrato (bajo_encargo)');
TestHelper::assertStringContains('value="agotados"', $panelView, 'El filtro de stock usa el valor real del contrato (agotados)');
TestHelper::assertStringContains('value="recientes"', $panelView, 'El orden usa el valor real del contrato (recientes)');
TestHelper::assertStringContains('value="precio_asc"', $panelView, 'El orden usa el valor real del contrato (precio_asc)');
TestHelper::assertStringContains('value="nombre_desc"', $panelView, 'El orden usa el valor real del contrato (nombre_desc)');
TestHelper::assertStringContains('value="stock_desc"', $panelView, 'El orden usa el valor real del contrato (stock_desc)');
TestHelper::assertStringContains('id="kpiCreacionesModelos"', $panelView, 'Se conserva el KPI #kpiCreacionesModelos (valor inicial 0, JS lo puebla)');
TestHelper::assertStringContains('id="kpiCreacionesStock"', $panelView, 'Se conserva el KPI #kpiCreacionesStock');
TestHelper::assertStringContains('id="kpiCreacionesValor"', $panelView, 'Se conserva el KPI #kpiCreacionesValor');
TestHelper::assertStringContains('id="kpiCreacionesCostos"', $panelView, 'Se conserva el KPI #kpiCreacionesCostos');

// 2.2 Modales: baja con confirmación real + restauración nueva
$deleteModal = (string)@file_get_contents("$root/views/components/modal_eliminar_creacion.php");
TestHelper::assertStringContains('id="btnConfirmDeleteCreacion"', $deleteModal, 'El modal de baja expone #btnConfirmDeleteCreacion');
TestHelper::assertFalse(str_contains($deleteModal, 'onclick='), 'El modal de baja ya no usa onclick con alert() mock');
TestHelper::assertFalse(str_contains($deleteModal, '/api/eliminar.php'), 'El modal de baja ya no cita la ruta falsa /api/eliminar.php');

$restoreModalPath = "$root/views/components/modal_restaurar_creacion.php";
TestHelper::assertTrue(is_file($restoreModalPath), 'views/components/modal_restaurar_creacion.php existe (restauración con UI)');
$restoreModal = (string)@file_get_contents($restoreModalPath);
TestHelper::assertStringContains('id="modalRestaurarCreacion"', $restoreModal, 'El modal de restauración expone #modalRestaurarCreacion');
TestHelper::assertStringContains('id="restoreCreacionName"', $restoreModal, 'El modal de restauración expone #restoreCreacionName');
TestHelper::assertStringContains('id="btnConfirmRestoreCreacion"', $restoreModal, 'El modal de restauración expone #btnConfirmRestoreCreacion');

$creacionesEntry = (string)@file_get_contents("$root/creaciones.php");
TestHelper::assertStringContains('modal_restaurar_creacion', $creacionesEntry, 'creaciones.php registra el modal de restauración');

// =============================================================================
// 3. MÓDULOS JS: creaciones server-driven + dropzone espejo + node --check
// =============================================================================
TestHelper::section('3. Contrato Frontend: mutaciones autenticadas y DOM seguro (H-004)');

$creacionesJs = (string)@file_get_contents("$root/src/js/modules/creaciones.js");

TestHelper::assertStringContains('export function initCreaciones', $creacionesJs, 'creaciones.js exporta initCreaciones()');
TestHelper::assertStringContains('export function initFormularioCreacion', $creacionesJs, 'creaciones.js exporta initFormularioCreacion()');
TestHelper::assertStringContains('export const initAmigurumis = initCreaciones', $creacionesJs, 'creaciones.js conserva el alias initAmigurumis');
TestHelper::assertStringContains("from './auth.js'", $creacionesJs, 'creaciones.js consume el módulo de sesión (Bearer)');
TestHelper::assertStringContains('getToken', $creacionesJs, 'creaciones.js adjunta el Bearer con getToken()');
TestHelper::assertStringContains("from './currency.js'", $creacionesJs, 'creaciones.js convierte pesos→centavos vía currency.js (R-06)');
TestHelper::assertStringContains('pesosToCents', $creacionesJs, 'creaciones.js serializa precios en centavos enteros (R-06)');
TestHelper::assertStringContains("from './dom-safe.js'", $creacionesJs, 'creaciones.js usa el helper DOM-safe (H-004)');
TestHelper::assertStringContains('setIconText', $creacionesJs, 'creaciones.js inyecta insignias con setIconText (H-004)');
TestHelper::assertStringContains("'/api/creaciones/mias.php'", $creacionesJs, 'creaciones.js lee el panel desde mias.php (scoping servidor, 4.3.1)');
TestHelper::assertStringContains("'/api/creaciones/artesanos.php'", $creacionesJs, 'creaciones.js puebla artesanos desde artesanos.php (IDs reales)');
TestHelper::assertStringContains("'/api/creaciones/detalle.php'", $creacionesJs, 'creaciones.js precarga edición desde detalle.php');
TestHelper::assertStringContains("'/api/creaciones/crear.php'", $creacionesJs, 'creaciones.js crea vía POST crear.php (201)');
TestHelper::assertStringContains("'/api/creaciones/actualizar.php'", $creacionesJs, 'creaciones.js edita vía POST actualizar.php');
TestHelper::assertStringContains("'/api/creaciones/eliminar.php'", $creacionesJs, 'creaciones.js da de baja vía POST eliminar.php');
TestHelper::assertStringContains("'/api/creaciones/restaurar.php'", $creacionesJs, 'creaciones.js restaura vía POST restaurar.php');
TestHelper::assertStringContains("'/api/creaciones/ajustar-stock.php'", $creacionesJs, 'creaciones.js ajusta stock vía POST ajustar-stock.php');
TestHelper::assertStringContains("'/api/creaciones/toggle-encargo.php'", $creacionesJs, 'creaciones.js alterna encargo vía POST toggle-encargo.php');
TestHelper::assertStringContains('new FormData()', $creacionesJs, 'El formulario envía FormData multipart (foto real, R-09)');
TestHelper::assertStringContains('function buildQuery', $creacionesJs, 'creaciones.js define buildQuery() (busqueda, categoria, estado_stock, artesano_id, orden, pagina)');
TestHelper::assertStringContains('estado_stock', $creacionesJs, 'creaciones.js serializa estado_stock con valores del contrato');
TestHelper::assertStringContains('artesano_id', $creacionesJs, 'creaciones.js serializa artesano_id (no usernames mock)');
TestHelper::assertStringContains('function fetchPage', $creacionesJs, 'creaciones.js define fetchPage()');
TestHelper::assertStringContains('function renderCards', $creacionesJs, 'creaciones.js define renderCards()');
TestHelper::assertStringContains('function renderPagination', $creacionesJs, 'creaciones.js define renderPagination()');
TestHelper::assertStringContains('function fetchKpis', $creacionesJs, 'creaciones.js define fetchKpis() (KPIs contra el total del servidor)');
TestHelper::assertStringContains('URLSearchParams', $creacionesJs, 'creaciones.js construye la query con URLSearchParams');
TestHelper::assertStringContains('.cloneNode(true)', $creacionesJs, 'creaciones.js clona el template administrativo por tarjeta');
TestHelper::assertStringContains('.textContent =', $creacionesJs, 'creaciones.js inyecta datos con textContent (H-004)');
TestHelper::assertFalse(str_contains($creacionesJs, 'innerHTML ='), 'creaciones.js NO asigna innerHTML con datos (cero vectores H-004)');
TestHelper::assertStringContains('imagen_fallback_svg', $creacionesJs, 'creaciones.js encadena el SVG temático de respaldo (R-09)');
TestHelper::assertStringContains("addEventListener('error'", $creacionesJs, 'creaciones.js aplica el respaldo de imagen ante error de carga');
TestHelper::assertStringContains('ovillo-generico.svg', $creacionesJs, 'creaciones.js cierra la cadena con el SVG genérico (R-09)');
TestHelper::assertStringContains('state.seq', $creacionesJs, 'creaciones.js descarta respuestas obsoletas con secuencia');
TestHelper::assertStringContains('state.page = 1', $creacionesJs, 'creaciones.js normaliza la página al cambiar filtros');
TestHelper::assertStringContains('status === 401', $creacionesJs, 'creaciones.js gestiona 401 (limpia sesión, checkSession redirige)');
TestHelper::assertStringContains('status === 403', $creacionesJs, 'creaciones.js muestra feedback ante 403 IDOR (sin alert)');
TestHelper::assertStringContains('status === 422', $creacionesJs, 'creaciones.js muestra errores 422 por campo (sin alert)');
TestHelper::assertStringContains("rol === 'admin'", $creacionesJs, 'Solo admin ve el selector de artesano autor');

// 3.2 dropzone.js: validación espejo MIME + 5MB
$dropzoneJs = (string)@file_get_contents("$root/src/js/modules/dropzone.js");
TestHelper::assertStringContains('image/jpeg', $dropzoneJs, 'dropzone.js espeja el allowlist MIME del servidor (R-09)');
TestHelper::assertStringContains('image/webp', $dropzoneJs, 'dropzone.js admite WebP como el servidor (R-09)');
TestHelper::assertStringContains('5 * 1024 * 1024', $dropzoneJs, 'dropzone.js espeja el techo de 5MB del servidor (R-09)');
TestHelper::assertStringContains('dropzoneError', $dropzoneJs, 'dropzone.js reporta en #dropzoneError (accesible)');
TestHelper::assertStringContains('export function isValidImageFile', $dropzoneJs, 'dropzone.js exporta isValidImageFile() reutilizable');

// 3.3 main.js cablea el formulario
$mainJs = (string)@file_get_contents("$root/src/js/main.js");
TestHelper::assertStringContains('initFormularioCreacion', $mainJs, 'main.js importa initFormularioCreacion()');
TestHelper::assertStringContains('initFormularioCreacion();', $mainJs, 'main.js ejecuta initFormularioCreacion() en DOMContentLoaded');

// 3.4 node --check de los módulos tocados
$jsChecks = [
    'creaciones.js' => "$root/src/js/modules/creaciones.js",
    'dropzone.js' => "$root/src/js/modules/dropzone.js",
    'main.js' => "$root/src/js/main.js",
    'currency.js' => "$root/src/js/modules/currency.js",
    'dom-safe.js' => "$root/src/js/modules/dom-safe.js",
    'auth.js' => "$root/src/js/modules/auth.js",
    'margin-calculator.js' => "$root/src/js/modules/margin-calculator.js",
];
foreach ($jsChecks as $label => $jsPath) {
    exec('node --check ' . escapeshellarg($jsPath) . ' 2>&1', $jsOut, $jsExit);
    TestHelper::assertSame(0, $jsExit, "node --check de {$label} sin errores de sintaxis");
}

// =============================================================================
// 4. BACKEND VÍA SERVICIO: ciclo de vida + validaciones + IDOR + R-01/R-02/R-06
// =============================================================================
TestHelper::section('4. Backend Servicio: crear/actualizar/stock/encargo/baja/restaurar + blindaje');

$service = new CreacionService();
$admin = ['id' => 1, 'rol' => 'admin'];
$ana = ['id' => 2, 'rol' => 'artesano'];
$uid = substr(md5((string)microtime(true)), 0, 8);
$trackedIds = [];

$baseData = static function (string $nombre) use ($uid): array {
    return [
        'nombre' => $nombre . ' ' . $uid,
        'categoria' => 'Amigurumis & Figuras',
        'material' => 'Algodón de prueba 4.3',
        'dimensiones' => '10 x 10 cm',
        'precio' => 45000,
        'costo_materiales' => 12000,
        'cantidad_stock' => 4,
        'horas_tejido' => 2.5,
        'descripcion' => 'Pieza efímera de la suite 4.3.',
        'es_sobre_encargo' => 0,
    ];
};

// 4.1 Crear sin foto → SVG temático de respaldo (R-09) + centavos enteros (R-06)
$created = $service->createCreation($baseData('ZZ43 Base'), null, $admin);
$trackedIds[] = (int)$created['id'];
TestHelper::assertTrue((int)$created['id'] > 0, 'createCreation() registra la pieza y devuelve su ID');
TestHelper::assertSame(45000, (int)$created['precio_centavos'], 'El precio viaja en centavos enteros (R-06, sin REAL)');
TestHelper::assertSame(12000, (int)$created['costo_materiales_centavos'], 'El costo viaja en centavos enteros (R-06)');
TestHelper::assertTrue(is_string($created['imagen_fallback_svg'] ?? null) && $created['imagen_fallback_svg'] !== '', 'La pieza expone imagen_fallback_svg (R-09)');
TestHelper::assertTrue(is_file($root . '/' . ltrim((string)$created['imagen_fallback_svg'], '/')), 'El SVG de respaldo existe físicamente en disco (R-09)');
TestHelper::assertSame(1, (int)$created['artesano_id'], 'Sin artesano_id explícito, el admin publica como sí mismo');

// 4.2 Admin atribuye a otra artesana (artesano_id real)
$attributed = $service->createCreation(
    array_merge($baseData('ZZ43 Atribuida'), ['artesano_id' => 2]),
    null,
    $admin
);
$trackedIds[] = (int)$attributed['id'];
TestHelper::assertSame(2, (int)$attributed['artesano_id'], 'El admin puede atribuir la pieza a otra artesana por ID real');

// 4.3 Validaciones de dominio → 422 (una por regla espejada en cliente)
$invalidCases = [
    'nombre de 1 carácter' => array_merge($baseData('ZZ43 X'), ['nombre' => 'X']),
    'precio cero' => array_merge($baseData('ZZ43 Y'), ['precio' => 0]),
    'stock 10001' => array_merge($baseData('ZZ43 Z'), ['cantidad_stock' => 10001]),
    'material de 2 caracteres' => array_merge($baseData('ZZ43 W'), ['material' => 'AB']),
];
foreach ($invalidCases as $label => $payload) {
    $code422 = null;
    try {
        $service->createCreation($payload, null, $admin);
    } catch (InvalidArgumentException $e) {
        $code422 = 422;
    }
    TestHelper::assertSame(422, $code422, "Validación espejada rechaza: {$label} (422)");
}

// 4.4 Actualizar sin foto conserva imagen_url (R-02: sin reemplazo no hay unlink)
$beforeUrl = (string)$created['imagen_url'];
$updated = $service->updateCreation((int)$created['id'], array_merge($baseData('ZZ43 Base Editada'), ['cantidad_stock' => 9]), null, $admin);
TestHelper::assertSame($beforeUrl, (string)$updated['imagen_url'], 'Actualizar sin foto conserva imagen_url (sin reemplazo, sin unlink)');
TestHelper::assertSame(9, (int)$updated['cantidad_stock'], 'Actualizar persiste el nuevo stock');

// 4.5 Stock in-situ con clamp de contrato
$adjusted = $service->adjustStock((int)$created['id'], 7, $admin);
TestHelper::assertSame(7, (int)$adjusted['cantidad_stock'], 'adjustStock() fija las existencias (respuesta con nuevo valor)');
$range422 = null;
try {
    $service->adjustStock((int)$created['id'], 10001, $admin);
} catch (InvalidArgumentException $e) {
    $range422 = 422;
}
TestHelper::assertSame(422, $range422, 'adjustStock() rechaza 10001 unidades (clamp 0-10000, 422)');

// 4.6 Toggle bajo encargo
$toggled = $service->toggleCommission((int)$created['id'], 1, $admin);
TestHelper::assertSame(1, (int)$toggled['es_sobre_encargo'], 'toggleCommission(1) activa la modalidad bajo encargo');
$untoggled = $service->toggleCommission((int)$created['id'], 0, $admin);
TestHelper::assertSame(0, (int)$untoggled['es_sobre_encargo'], 'toggleCommission(0) vuelve a entrega inmediata');

// 4.7 IDOR multi-artesano (R-04 / ADR-007): Ana sobre pieza del admin → 403
$idorCodes = [];
foreach (['update' => null, 'delete' => null, 'stock' => null, 'toggle' => null] as $op => $_) {
    try {
        match ($op) {
            'update' => $service->updateCreation((int)$created['id'], $baseData('ZZ43 IDOR'), null, $ana),
            'delete' => $service->deleteCreation((int)$created['id'], $ana),
            'stock' => $service->adjustStock((int)$created['id'], 3, $ana),
            'toggle' => $service->toggleCommission((int)$created['id'], 1, $ana),
        };
    } catch (RuntimeException $e) {
        $idorCodes[$op] = (int)$e->getCode();
    }
}
TestHelper::assertSame(403, $idorCodes['update'] ?? 0, 'IDOR: artesana ajena no puede actualizar (403)');
TestHelper::assertSame(403, $idorCodes['delete'] ?? 0, 'IDOR: artesana ajena no puede dar de baja (403)');
TestHelper::assertSame(403, $idorCodes['stock'] ?? 0, 'IDOR: artesana ajena no puede ajustar stock (403)');
TestHelper::assertSame(403, $idorCodes['toggle'] ?? 0, 'IDOR: artesana ajena no puede alternar encargo (403)');

// 4.8 La autora sí puede mutar su pieza atribuida (autonomía, no bloqueo global)
$anaUpdate = $service->updateCreation((int)$attributed['id'], array_merge($baseData('ZZ43 Ana Edita'), ['cantidad_stock' => 5]), null, $ana);
TestHelper::assertSame(5, (int)$anaUpdate['cantidad_stock'], 'La artesana autora actualiza su propia pieza (200)');

// 4.9 Baja lógica → invisible en catálogo, visible con onlyActive=false (R-01)
$service->deleteCreation((int)$created['id'], $admin);
$gone = null;
try {
    $service->getCreationById((int)$created['id'], true);
} catch (RuntimeException $e) {
    $gone = (int)$e->getCode();
}
TestHelper::assertSame(404, $gone, 'Tras la baja, getCreationById(onlyActive) responde 404');
$kept = $service->getCreationById((int)$created['id'], false);
TestHelper::assertSame(0, (int)$kept['activo'], 'La fila se conserva con activo=0 (baja lógica, R-01)');
TestHelper::assertTrue(!empty($kept['eliminado_en']), 'La baja registra eliminado_en para auditoría');

// 4.10 Idempotencia: segunda baja → 409; restaurar → 200; segunda restauración → 409
$second409 = null;
try {
    $service->deleteCreation((int)$created['id'], $admin);
} catch (RuntimeException $e) {
    $second409 = (int)$e->getCode();
}
TestHelper::assertSame(409, $second409, 'La segunda baja responde 409 (idempotente)');
$restored = $service->restoreCreation((int)$created['id'], $admin);
TestHelper::assertSame(1, (int)$restored['activo'], 'restoreCreation() reactiva la pieza (activo=1)');
$backOnline = $service->getCreationById((int)$created['id'], true);
TestHelper::assertSame((int)$created['id'], (int)$backOnline['id'], 'Tras restaurar, el detalle vuelve a 200');
$restore409 = null;
try {
    $service->restoreCreation((int)$created['id'], $admin);
} catch (RuntimeException $e) {
    $restore409 = (int)$e->getCode();
}
TestHelper::assertSame(409, $restore409, 'La segunda restauración responde 409 (idempotente)');

// 4.11 R-01 estructural: cero DELETE FROM en servicio y repositorio de creaciones
$serviceSrc = (string)@file_get_contents("$root/app/Services/CreacionService.php");
$repoSrc = (string)@file_get_contents("$root/app/Repositories/CreacionRepository.php");
TestHelper::assertFalse(stripos($serviceSrc, 'DELETE FROM') !== false, 'CreacionService.php: cero DELETE FROM (R-01)');
TestHelper::assertFalse(stripos($repoSrc, 'DELETE FROM') !== false, 'CreacionRepository.php: cero DELETE FROM (R-01)');

// 4.12 Limpieza de piezas efímeras de servicio (baja lógica, nunca física)
foreach ($trackedIds as $tid) {
    try {
        $service->deleteCreation($tid, $admin);
    } catch (Throwable $e) {
        // Ya inactiva o inexistente: estado final válido igualmente.
    }
}
TestHelper::assertTrue(true, 'Limpieza de piezas efímeras de servicio completada (baja lógica)');

// =============================================================================
// 5. PRUEBAS HTTP EN VIVO CONTRA LOCALHOST:8000 (multipart real + ciclo baja)
// =============================================================================
TestHelper::section('5. Pruebas HTTP en Vivo: multipart, 401/422/403/404/409 y páginas');

// 5.1 Preflight CORS de los endpoints de mutación
foreach (['crear.php', 'actualizar.php', 'eliminar.php', 'restaurar.php'] as $endpoint) {
    $cors = TestHelper::curl('OPTIONS', "http://localhost:8000/api/creaciones/{$endpoint}", [
        'Origin: http://localhost:3000',
        'Access-Control-Request-Method: POST',
    ]);
    TestHelper::assertTrue(
        in_array($cors['status'], [200, 204], true),
        "Preflight CORS OPTIONS en /api/creaciones/{$endpoint} responde correctamente ({$cors['status']})"
    );
}

// 5.2 Login real de admin y artesana (admin123, seed)
$loginAdmin = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'admin', 'password' => 'admin123']));
TestHelper::assertSame(200, $loginAdmin['status'], 'HTTP login admin/admin123 devuelve 200 OK');
$tokenAdmin = (string)($loginAdmin['json']['datos']['token'] ?? '');
TestHelper::assertTrue($tokenAdmin !== '', 'HTTP login admin entrega token Bearer');

$loginAna = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'artesana_ana', 'password' => 'artesana123']));
TestHelper::assertSame(200, $loginAna['status'], 'HTTP login artesana_ana devuelve 200 OK');
$tokenAna = (string)($loginAna['json']['datos']['token'] ?? '');
TestHelper::assertTrue($tokenAna !== '', 'HTTP login artesana_ana entrega token Bearer');

// 5.3 Fixture PNG real de 1px para el multipart
$pngPath = sys_get_temp_dir() . '/zz43-foto-' . $uid . '.png';
file_put_contents($pngPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

$httpPieceName = 'ZZ43 HTTP ' . $uid;
$multipart = [
    'nombre' => $httpPieceName,
    'categoria' => 'Bolsos & Accesorios',
    'material' => 'Trapillo de prueba HTTP',
    'dimensiones' => '30 x 25 cm',
    'precio' => '38000',
    'costo_materiales' => '9500',
    'cantidad_stock' => '6',
    'horas_tejido' => '4.5',
    'descripcion' => 'Pieza efímera HTTP de la suite 4.3.',
    'es_sobre_encargo' => '0',
    'imagen' => new CURLFile($pngPath, 'image/png', 'foto.png'),
];
$httpCreate = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $tokenAdmin,
], $multipart);
TestHelper::assertSame(201, $httpCreate['status'], 'HTTP multipart con foto real devuelve 201 Created');
TestHelper::assertTrue($httpCreate['json']['exito'] ?? false, 'La creación multipart responde exito: true');
$httpId = (int)($httpCreate['json']['datos']['id'] ?? 0);
TestHelper::assertTrue($httpId > 0, 'La creación multipart devuelve el ID de la pieza');
$httpImageUrl = (string)($httpCreate['json']['datos']['imagen_url'] ?? '');
TestHelper::assertTrue(
    (bool)preg_match('#^uploads/creacion_[0-9a-f]{16}_[0-9]+\.(jpg|jpeg|png|webp)$#', $httpImageUrl),
    "La foto se guarda con nombre criptográfico confinado en uploads/ ({$httpImageUrl})"
);
TestHelper::assertTrue(is_file($root . '/' . $httpImageUrl), 'El archivo subido existe físicamente en disco');
TestHelper::assertSame(200, TestHelper::curl('GET', 'http://localhost:8000/' . $httpImageUrl)['status'], 'La foto subida se sirve con 200 OK');

// 5.4 Sin token → 401
$noAuth = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [], $multipart);
TestHelper::assertSame(401, $noAuth['status'], 'HTTP crear sin Bearer devuelve 401');

// 5.5 Validación 422 por HTTP (nombre de 1 carácter)
$badPayload = $multipart;
$badPayload['nombre'] = 'X';
unset($badPayload['imagen']);
$badCreate = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $tokenAdmin,
], $badPayload);
TestHelper::assertSame(422, $badCreate['status'], 'HTTP crear con nombre inválido devuelve 422');

// 5.6 MIME falso por HTTP (texto plano como imagen) → 422 (R-09 binario real)
$txtPath = sys_get_temp_dir() . '/zz43-falso-' . $uid . '.txt';
file_put_contents($txtPath, 'no soy una imagen');
$fakePayload = $multipart;
$fakePayload['nombre'] = 'ZZ43 Falsa ' . $uid;
$fakePayload['imagen'] = new CURLFile($txtPath, 'text/plain', 'falsa.txt');
$fakeCreate = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $tokenAdmin,
], $fakePayload);
TestHelper::assertSame(422, $fakeCreate['status'], 'HTTP crear con MIME text/plain devuelve 422 (detección binaria, R-09)');

// 5.7 Actualizar pieza propia por HTTP (sin foto → conserva imagen, R-02)
$httpUpdate = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/actualizar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId, 'nombre' => $httpPieceName . ' Ed', 'categoria' => 'Bolsos & Accesorios', 'material' => 'Trapillo de prueba HTTP', 'dimensiones' => '30 x 25 cm', 'precio' => 38000, 'costo_materiales' => 9500, 'cantidad_stock' => 8, 'horas_tejido' => 4.5, 'es_sobre_encargo' => 0]));
TestHelper::assertSame(200, $httpUpdate['status'], 'HTTP actualizar pieza propia devuelve 200 OK');
TestHelper::assertSame($httpImageUrl, (string)($httpUpdate['json']['datos']['imagen_url'] ?? ''), 'HTTP actualizar sin foto conserva imagen_url (R-02)');

// 5.8 IDOR por HTTP: Ana sobre pieza del admin → 403
$httpIdor = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/actualizar.php', [
    'Authorization: Bearer ' . $tokenAna,
    'Content-Type: application/json',
], json_encode(['id' => $httpId, 'nombre' => 'ZZ43 IDOR', 'categoria' => 'Bolsos & Accesorios', 'material' => 'Trapillo', 'dimensiones' => '30 x 25 cm', 'precio' => 38000]));
TestHelper::assertSame(403, $httpIdor['status'], 'HTTP actualizar pieza ajena devuelve 403 (R-04)');

// 5.9 Stock y encargo por HTTP sobre pieza propia
$httpStock = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/ajustar-stock.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId, 'cantidad_stock' => 10]));
TestHelper::assertSame(200, $httpStock['status'], 'HTTP ajustar-stock devuelve 200 OK');
TestHelper::assertSame(10, (int)($httpStock['json']['datos']['cantidad_stock'] ?? -1), 'HTTP ajustar-stock refleja las 10 unidades');
$httpToggle = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/toggle-encargo.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId, 'es_sobre_encargo' => 1]));
TestHelper::assertSame(200, $httpToggle['status'], 'HTTP toggle-encargo devuelve 200 OK');
TestHelper::assertSame(1, (int)($httpToggle['json']['datos']['es_sobre_encargo'] ?? -1), 'HTTP toggle-encargo activa bajo encargo');

// 5.10 Ciclo de baja: eliminar 200 → detalle 404 → foto preservada 200 (R-02)
$httpDelete = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId]));
TestHelper::assertSame(200, $httpDelete['status'], 'HTTP eliminar devuelve 200 OK (baja lógica)');
$httpDetailGone = TestHelper::curl('GET', "http://localhost:8000/api/creaciones/detalle.php?id={$httpId}");
TestHelper::assertSame(404, $httpDetailGone['status'], 'HTTP detalle tras la baja devuelve 404');
TestHelper::assertSame(200, TestHelper::curl('GET', 'http://localhost:8000/' . $httpImageUrl)['status'], 'La foto sigue servida tras la baja (preservación R-02, cero unlink)');

// 5.11 Idempotencia y restauración por HTTP: 409 → 200 → 200 → 409
$httpDeleteAgain = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId]));
TestHelper::assertSame(409, $httpDeleteAgain['status'], 'HTTP segunda baja devuelve 409 (idempotente)');
$httpRestore = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/restaurar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId]));
TestHelper::assertSame(200, $httpRestore['status'], 'HTTP restaurar devuelve 200 OK');
$httpDetailBack = TestHelper::curl('GET', "http://localhost:8000/api/creaciones/detalle.php?id={$httpId}");
TestHelper::assertSame(200, $httpDetailBack['status'], 'HTTP detalle tras restaurar devuelve 200 OK');
$httpRestoreAgain = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/restaurar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId]));
TestHelper::assertSame(409, $httpRestoreAgain['status'], 'HTTP segunda restauración devuelve 409 (idempotente)');

// 5.12 Páginas del panel con CSP intacta y sin mocks
$httpPanel = TestHelper::curl('GET', 'http://localhost:8000/creaciones.php');
TestHelper::assertSame(200, $httpPanel['status'], 'HTTP GET /creaciones.php devuelve 200 OK');
TestHelper::assertStringContains('id="creacionesGrid"', $httpPanel['body'], 'El HTML servido incluye #creacionesGrid');
TestHelper::assertStringContains('id="creacionCardTemplate"', $httpPanel['body'], 'El HTML servido incluye el template creacionCardTemplate');
TestHelper::assertFalse(str_contains($httpPanel['body'], '$creacionesList'), 'El HTML servido no contiene restos del mock PHP');
TestHelper::assertStringContains('id="modalRestaurarCreacion"', $httpPanel['body'], 'El HTML servido incluye el modal de restauración');
$cspPanel = strtolower($httpPanel['headers']['content-security-policy'] ?? '');
TestHelper::assertStringContains("script-src 'self'", $cspPanel, 'CSP del panel restringe scripts al propio origen (H-004)');

$httpForm = TestHelper::curl('GET', 'http://localhost:8000/formulario.php');
TestHelper::assertSame(200, $httpForm['status'], 'HTTP GET /formulario.php devuelve 200 OK');
TestHelper::assertStringContains('id="creacionForm"', $httpForm['body'], 'El HTML servido incluye #creacionForm');
TestHelper::assertFalse(str_contains($httpForm['body'], '$seedItems'), 'El HTML servido no contiene restos del mock $seedItems');

$httpApi = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php');
$cspApi = strtolower($httpApi['headers']['content-security-policy'] ?? '');
TestHelper::assertTrue(
    str_contains($cspApi, "default-src 'none'") || str_contains($cspApi, "default-src none"),
    'CSP de las respuestas API bloquea cualquier fuente por defecto'
);

// 5.13 Limpieza HTTP: la pieza efímera vuelve a baja lógica; fixtures temporales fuera
$httpCleanup = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId]));
TestHelper::assertTrue(in_array($httpCleanup['status'], [200, 409], true), 'Limpieza HTTP: la pieza efímera queda en baja lógica');
@unlink($pngPath);
@unlink($txtPath);

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
