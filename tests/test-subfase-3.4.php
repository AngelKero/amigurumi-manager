<?php
/**
 * Test Suite: Subfase 3.4 - Catálogo, Creaciones & Ciclo de Vida de Imágenes
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Valida de forma exhaustiva:
 * 1. CreacionRepository: consultas filtradas, ordenamientos, paginación, DDL mutations y ADR-014
 * 2. CreacionService: validaciones de dominio, enriquecimiento dual, fallback SVG, IDOR (ADR-007),
 *    ciclo de vida de fotos sin unlink en baja lógica (ADR-008) y restauración (ADR-015)
 * 3. Endpoints REST en vivo contra localhost:8000:
 *    - GET /api/creaciones/index.php (200 OK público, filtros, paginación, 405 en POST)
 *    - GET /api/creaciones/artesanos.php (200 OK público, ADR-014)
 *    - GET /api/creaciones/detalle.php (200 OK público, 404 inexistente/inactivo)
 *    - POST /api/creaciones/crear.php (201 Created con token, 401 sin token, 422 inválido)
 *    - POST /api/creaciones/actualizar.php (200 OK autor/admin, 403 IDOR no-propietario)
 *    - POST /api/creaciones/ajustar-stock.php (200 OK autor/admin, 403 IDOR)
 *    - POST /api/creaciones/toggle-encargo.php (200 OK autor/admin, 403 IDOR)
 *    - POST /api/creaciones/eliminar.php (200 OK baja lógica, CERO unlink en disco, 403 IDOR, 409 ya inactivo)
 *    - POST /api/creaciones/restaurar.php (200 OK reactivación ADR-015, 403 IDOR, 409 ya activo)
 *    - OPTIONS Preflight CORS
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CreacionRepository;
use App\Repositories\UsuarioRepository;
use App\Services\CreacionService;
use App\Utils\CurrencyHelper;

// Iniciar suite
TestHelper::init('Subfase 3.4: Catálogo, Creaciones & Ciclo de Vida de Imágenes');

// Desactivar terminación forzada en Response para pruebas en memoria
Response::setExitOnSend(false);

$creacionRepo = new CreacionRepository();
$usuarioRepo = new UsuarioRepository();
$service = new CreacionService($creacionRepo, $usuarioRepo);

// =============================================================================
// 1. CREACIONREPOSITORY: PERSISTENCIA, CONSULTAS Y MUTACIONES
// =============================================================================
TestHelper::section('1. CreacionRepository: Persistencia, Filtros y Mutaciones');

// 1.1 findById con registro existente
$item1 = $creacionRepo->findById(1, true);
TestHelper::assertNotNull($item1, 'findById(1) encuentra la pieza Dragón Ignis');
TestHelper::assertSame(1, (int)$item1['id'], 'El ID es 1');
TestHelper::assertSame('Dragón Ignis', $item1['nombre'], 'El nombre es Dragón Ignis');
TestHelper::assertTrue(isset($item1['artesano']), 'El registro incluye array anidado de artesano');
TestHelper::assertSame(1, (int)$item1['artesano']['id'], 'El artesano ID es 1');
TestHelper::assertSame('admin', $item1['artesano']['username'], 'El username del artesano es admin');
TestHelper::assertSame(45000, (int)$item1['precio'], 'El precio en centavos es 45000');
TestHelper::assertSame(1, (int)$item1['activo'], 'El campo activo es 1');

// 1.2 findById con registro inexistente
$itemNone = $creacionRepo->findById(99999, true);
TestHelper::assertNull($itemNone, 'findById(99999) devuelve null');

// 1.3 listCatalog sin filtros (valores por defecto)
$defaultList = $creacionRepo->listCatalog([], 12, 0, 'recientes');
TestHelper::assertTrue(is_array($defaultList), 'listCatalog() retorna un array');
TestHelper::assertTrue(count($defaultList) >= 5, 'Se recuperan al menos las 5 creaciones semilla');
TestHelper::assertTrue($defaultList[0]['id'] > $defaultList[count($defaultList) - 1]['id'], 'El orden recientes es descendente por ID');

// 1.4 listCatalog con filtro de categoría
$amigurumisList = $creacionRepo->listCatalog(['categoria' => 'Amigurumis & Figuras'], 12, 0);
TestHelper::assertTrue(count($amigurumisList) >= 1, 'Filtro por categoría Amigurumis & Figuras devuelve resultados');
foreach ($amigurumisList as $fItem) {
    TestHelper::assertSame('Amigurumis & Figuras', $fItem['categoria'], 'Cada ítem filtrado pertenece a Amigurumis & Figuras');
}

// 1.5 listCatalog con filtro por artesano
$adminList = $creacionRepo->listCatalog(['artesano_id' => 1], 12, 0);
TestHelper::assertTrue(count($adminList) >= 1, 'Filtro por artesano_id=1 devuelve creaciones');
foreach ($adminList as $aItem) {
    TestHelper::assertSame(1, (int)$aItem['artesano_id'], 'Cada ítem pertenece al artesano 1');
}

// 1.6 listCatalog con filtro por rango de precio
$priceFiltered = $creacionRepo->listCatalog(['precio_min' => 20000, 'precio_max' => 50000], 12, 0);
TestHelper::assertTrue(count($priceFiltered) >= 1, 'Filtro por rango de precio devuelve ítems');
foreach ($priceFiltered as $pItem) {
    TestHelper::assertTrue((int)$pItem['precio'] >= 20000 && (int)$pItem['precio'] <= 50000, 'El precio está dentro del rango especificado');
}

// 1.7 listCatalog con búsqueda textual
$searchList = $creacionRepo->listCatalog(['busqueda' => 'Dragón'], 12, 0);
TestHelper::assertTrue(count($searchList) >= 1, 'Búsqueda por "Dragón" encuentra resultados');
TestHelper::assertTrue(str_contains($searchList[0]['nombre'], 'Dragón'), 'El nombre contiene "Dragón"');

// 1.8 listCatalog con ordenación precio_asc y precio_desc
$ascList = $creacionRepo->listCatalog([], 12, 0, 'precio_asc');
$descList = $creacionRepo->listCatalog([], 12, 0, 'precio_desc');
TestHelper::assertTrue((int)$ascList[0]['precio'] <= (int)$ascList[count($ascList) - 1]['precio'], 'Orden precio_asc coloca el menor precio primero');
TestHelper::assertTrue((int)$descList[0]['precio'] >= (int)$descList[count($descList) - 1]['precio'], 'Orden precio_desc coloca el mayor precio primero');

// 1.9 countCatalog
$totalCount = $creacionRepo->countCatalog([]);
TestHelper::assertTrue($totalCount >= 5, 'countCatalog() devuelve total >= 5');
$amigurumisCount = $creacionRepo->countCatalog(['categoria' => 'Amigurumis & Figuras']);
TestHelper::assertSame(count($amigurumisList), $amigurumisCount, 'countCatalog con categoría coincide con listCatalog');

// 1.10 findActiveArtisansWithCreations (ADR-014)
$activeArtisans = $creacionRepo->findActiveArtisansWithCreations();
TestHelper::assertTrue(is_array($activeArtisans), 'findActiveArtisansWithCreations() retorna un array');
TestHelper::assertTrue(count($activeArtisans) >= 2, 'Al menos 2 artesanos tienen creaciones activas');
TestHelper::assertTrue(isset($activeArtisans[0]['id'], $activeArtisans[0]['username'], $activeArtisans[0]['total_creaciones']), 'Cada item incluye id, username y total_creaciones');
TestHelper::assertTrue((int)$activeArtisans[0]['total_creaciones'] > 0, 'El conteo de creaciones activas es mayor a cero');

// 1.11 create() inserción en SQLite
$tempData = [
    'artesano_id'        => 1,
    'nombre'             => 'Pieza Test Repo ' . time(),
    'categoria'          => 'Fantasía',
    'material'           => 'Hilaza de Algodón',
    'dimensiones'        => '15 cm',
    'precio'             => 25000,
    'costo_materiales'   => 5000,
    'cantidad_stock'     => 4,
    'horas_tejido'       => 3.5,
    'descripcion'        => 'Descripción de prueba para repositorio',
    'imagen_url'         => 'assets/svg/piezas/dragon-ignis.svg',
    'es_sobre_encargo'   => 0,
];
$createdRepoId = $creacionRepo->create($tempData);
TestHelper::assertTrue($createdRepoId > 0, 'create() inserta una creación y retorna su ID');

// 1.12 update() modificación en SQLite
$tempData['nombre'] = 'Pieza Test Repo Modificada';
$updateSuccess = $creacionRepo->update($createdRepoId, $tempData);
TestHelper::assertTrue($updateSuccess, 'update() actualiza la creación');
$itemUpdated = $creacionRepo->findById($createdRepoId, true);
TestHelper::assertSame('Pieza Test Repo Modificada', $itemUpdated['nombre'], 'El nombre se actualizó en la BD');

// 1.13 adjustStock() y toggleCommission()
$stockAdjustSuccess = $creacionRepo->adjustStock($createdRepoId, 15);
TestHelper::assertTrue($stockAdjustSuccess, 'adjustStock() actualiza las existencias');
$itemStock = $creacionRepo->findById($createdRepoId, true);
TestHelper::assertSame(15, (int)$itemStock['cantidad_stock'], 'El nuevo stock es 15');

$toggleSuccess = $creacionRepo->toggleCommission($createdRepoId, 1);
TestHelper::assertTrue($toggleSuccess, 'toggleCommission() cambia a modalidad bajo encargo');
$itemCommission = $creacionRepo->findById($createdRepoId, true);
TestHelper::assertSame(1, (int)$itemCommission['es_sobre_encargo'], 'es_sobre_encargo es 1');

// 1.14 softDelete() y restore()
$softDelSuccess = $creacionRepo->softDelete($createdRepoId);
TestHelper::assertTrue($softDelSuccess, 'softDelete() marca baja lógica');
$itemActiveCheck = $creacionRepo->findById($createdRepoId, true);
TestHelper::assertNull($itemActiveCheck, 'Ítem dado de baja no aparece con onlyActive=true');

$itemInactiveCheck = $creacionRepo->findById($createdRepoId, false);
TestHelper::assertNotNull($itemInactiveCheck, 'Ítem dado de baja persiste físicamente en SQLite');
TestHelper::assertSame(0, (int)$itemInactiveCheck['activo'], 'activo es 0 tras softDelete');
TestHelper::assertTrue(!empty($itemInactiveCheck['eliminado_en']), 'eliminado_en contiene marca temporal');

$restoreSuccess = $creacionRepo->restore($createdRepoId);
TestHelper::assertTrue($restoreSuccess, 'restore() revierte la baja lógica');
$itemRestored = $creacionRepo->findById($createdRepoId, true);
TestHelper::assertNotNull($itemRestored, 'Ítem restaurado vuelve a aparecer en consultas activas');
TestHelper::assertSame(1, (int)$itemRestored['activo'], 'activo es 1 tras restore');
TestHelper::assertNull($itemRestored['eliminado_en'], 'eliminado_en es NULL tras restore');

// Limpieza de registro de prueba
$creacionRepo->softDelete($createdRepoId);

// =============================================================================
// 2. CREACIONSERVICE: LÓGICA DE NEGOCIO, VALIDACIONES, IDOR & IMÁGENES
// =============================================================================
TestHelper::section('2. CreacionService: Reglas de Dominio, IDOR y Ciclo de Vida');

// 2.1 getCatalog() con enriquecimiento y sobre de paginación
$catalogEnvelope = $service->getCatalog(['pagina' => 1, 'limite' => 12]);
TestHelper::assertTrue(isset($catalogEnvelope['datos']), 'getCatalog() devuelve clave "datos"');
TestHelper::assertTrue(isset($catalogEnvelope['paginacion']), 'getCatalog() devuelve clave "paginacion"');
TestHelper::assertTrue(count($catalogEnvelope['datos']) >= 5, 'Hay al menos 5 creaciones activas');

$firstCatItem = $catalogEnvelope['datos'][0];
TestHelper::assertTrue(isset($firstCatItem['precio_formateado']), 'El ítem contiene precio_formateado');
TestHelper::assertTrue(isset($firstCatItem['costo_formateado']), 'El ítem contiene costo_formateado');
TestHelper::assertTrue(isset($firstCatItem['metricas']), 'El ítem contiene sub-objeto metricas');
TestHelper::assertTrue(isset($firstCatItem['metricas']['margen_bruto_porcentaje']), 'metricas incluye margen_bruto_porcentaje');
TestHelper::assertTrue(isset($firstCatItem['metricas']['retorno_por_hora_formateado']), 'metricas incluye retorno_por_hora_formateado');

// 2.2 getCreationById() existente y no existente
$detailItem = $service->getCreationById(1, true);
TestHelper::assertSame('Dragón Ignis', $detailItem['nombre'], 'getCreationById(1) devuelve Dragón Ignis');
TestHelper::assertSame('$450.00 MXN', $detailItem['precio_formateado'], 'precio_formateado es $450.00 MXN');

$caughtNotFoundService = false;
try {
    $service->getCreationById(99999);
} catch (\RuntimeException $e) {
    $caughtNotFoundService = ($e->getCode() === 404);
}
TestHelper::assertTrue($caughtNotFoundService, 'getCreationById(99999) lanza RuntimeException 404');

// 2.3 IDOR Authorization (ADR-007)
$mockCreationArtisan2 = [
    'id'          => 10,
    'artesano_id' => 2,
    'nombre'      => 'Creación de Artesana Ana',
];

// 2.3.1 Propietario artesano ID 2 tiene acceso
$ownerArtisanUser = ['id' => 2, 'username' => 'artesana_ana', 'rol' => 'artesano'];
$idorPassedOwner = false;
try {
    $service->ensureArtisanOwnership($mockCreationArtisan2, $ownerArtisanUser);
    $idorPassedOwner = true;
} catch (\RuntimeException $e) {
    $idorPassedOwner = false;
}
TestHelper::assertTrue($idorPassedOwner, 'ensureArtisanOwnership permite acceso al artesano propietario');

// 2.3.2 Administrador tiene acceso global
$adminUser = ['id' => 1, 'username' => 'admin', 'rol' => 'admin'];
$idorPassedAdmin = false;
try {
    $service->ensureArtisanOwnership($mockCreationArtisan2, $adminUser);
    $idorPassedAdmin = true;
} catch (\RuntimeException $e) {
    $idorPassedAdmin = false;
}
TestHelper::assertTrue($idorPassedAdmin, 'ensureArtisanOwnership permite acceso a administrador sobre pieza ajena');

// 2.3.3 Artesano ajeno es rechazado con HTTP 403 (Prevención IDOR)
$otherArtisanUser = ['id' => 3, 'username' => 'otro_artesano', 'rol' => 'artesano'];
$caughtIdorViolation = false;
try {
    $service->ensureArtisanOwnership($mockCreationArtisan2, $otherArtisanUser);
} catch (\RuntimeException $e) {
    $caughtIdorViolation = ($e->getCode() === 403);
}
TestHelper::assertTrue($caughtIdorViolation, 'ensureArtisanOwnership bloquea con HTTP 403 a artesano no propietario (IDOR prevenido)');

// 2.4 Asignación de fallback temático SVG
$svgFantasia = $service->getThematicSvgFallback('Fantasía', 'Dragón Bebé');
TestHelper::assertSame('assets/svg/piezas/dragon-ignis.svg', $svgFantasia, 'Fallback para Dragón asigna dragon-ignis.svg');

$svgPrendas = $service->getThematicSvgFallback('Prendas & Ropa', 'Cardigan Nórdico');
TestHelper::assertSame('assets/svg/piezas/cardigan-granny.svg', $svgPrendas, 'Fallback para Cardigan asigna cardigan-granny.svg');

$svgBolsos = $service->getThematicSvgFallback('Bolsos & Accesorios', 'Tote Trapillo');
TestHelper::assertSame('assets/svg/piezas/tote-bag.svg', $svgBolsos, 'Fallback para Bolsos asigna tote-bag.svg');

$svgHogar = $service->getThematicSvgFallback('Hogar & Decoración', 'Cactus Maceta');
TestHelper::assertSame('assets/svg/piezas/mini-suculenta.svg', $svgHogar, 'Fallback para Hogar asigna mini-suculenta.svg');

// 2.5 createCreation() con fallback SVG (sin subir archivo)
$newCreationPayload = [
    'nombre'           => 'Muñeco Fantasía ' . time(),
    'categoria'        => 'Fantasía',
    'material'         => 'Hilaza 100% Algodón',
    'dimensiones'      => '20 cm',
    'precio'           => 32000,
    'costo_materiales' => 8000,
    'cantidad_stock'   => 5,
    'horas_tejido'     => 4.0,
    'descripcion'      => 'Pieza creada por servicio para testing',
    'es_sobre_encargo' => 0,
];
$createdPiece = $service->createCreation($newCreationPayload, null, $ownerArtisanUser);
TestHelper::assertTrue($createdPiece['id'] > 0, 'createCreation() registra la pieza y retorna ID > 0');
TestHelper::assertSame(2, (int)$createdPiece['artesano']['id'], 'El artesano asignado es el usuario en sesión (ID 2)');
TestHelper::assertTrue(str_contains($createdPiece['imagen_url'], '.svg'), 'Se asignó un vector temático SVG al no enviar fotografía');

$createdPieceId = (int)$createdPiece['id'];

// 2.6 updateCreation() con nuevo archivo simulado y reemplazo físico (ADR-008)
// Creamos un archivo temporal simulado de imagen en uploads/
$uploadsDir = dirname(__DIR__) . '/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}
$dummyOldFile = $uploadsDir . '/dummy_old_' . time() . '.jpg';
file_put_contents($dummyOldFile, "\xFF\xD8\xFF\xE0\x00\x10JFIF" . str_repeat("\x00", 100)); // JPEG mínimo

// Actualizamos directamente la BD para que la creación apunte a este archivo local
$creacionRepo->update($createdPieceId, array_merge($newCreationPayload, [
    'imagen_url' => 'uploads/' . basename($dummyOldFile),
]));
TestHelper::assertTrue(is_file($dummyOldFile), 'El archivo dummy_old existe físicamente en uploads/');

// Simulamos la subida de un nuevo archivo temporal
$dummyNewTemp = tempnam(sys_get_temp_dir(), 'test_img_');
file_put_contents($dummyNewTemp, "\xFF\xD8\xFF\xE0\x00\x10JFIF" . str_repeat("\x01", 100)); // JPEG mínimo
$mockUploadFile = [
    'name'     => 'nueva_foto.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => $dummyNewTemp,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($dummyNewTemp),
];

$updatePayload = array_merge($newCreationPayload, [
    'nombre' => 'Muñeco Fantasía con Nueva Foto',
]);
$updatedPiece = $service->updateCreation($createdPieceId, $updatePayload, $mockUploadFile, $ownerArtisanUser);
TestHelper::assertSame('Muñeco Fantasía con Nueva Foto', $updatedPiece['nombre'], 'updateCreation() actualizó el nombre');
TestHelper::assertTrue(str_starts_with($updatedPiece['imagen_url'], 'uploads/creacion_'), 'imagen_url apunta a la nueva subida');
TestHelper::assertFalse(is_file($dummyOldFile), 'El archivo anterior en uploads/ fue eliminado físicamente con unlink()');

// 2.7 deleteCreation() preserva la fotografía física en disco (ADR-008 Regla de Oro)
$currentImageOnDisk = dirname(__DIR__) . '/' . $updatedPiece['imagen_url'];
TestHelper::assertTrue(is_file($currentImageOnDisk), 'El nuevo archivo de imagen existe físicamente en disco');

$deleteServiceRes = $service->deleteCreation($createdPieceId, $ownerArtisanUser);
TestHelper::assertSame(0, (int)$deleteServiceRes['activo'], 'deleteCreation() marca activo: 0');
TestHelper::assertTrue(is_file($currentImageOnDisk), 'REGLA DE ORO ADR-008: La fotografía NO fue eliminada del disco en baja lógica');

// 2.8 restoreCreation() reactiva la creación (ADR-015)
$restoreServiceRes = $service->restoreCreation($createdPieceId, $ownerArtisanUser);
TestHelper::assertSame(1, (int)$restoreServiceRes['activo'], 'restoreCreation() reactiva la creación con activo: 1');

// Limpieza del archivo de prueba
if (is_file($currentImageOnDisk)) {
    @unlink($currentImageOnDisk);
}
// Soft delete de la pieza de prueba
$creacionRepo->softDelete($createdPieceId);

// =============================================================================
// 3. PRUEBAS HTTP EN VIVO (CURL CONTRA LOCALHOST:8000/api/creaciones/*)
// =============================================================================
TestHelper::section('3. Pruebas HTTP en Vivo contra Servidor (api/creaciones/)');

$httpLogBuffer = "================================================================================\n";
$httpLogBuffer .= "  HTTP TRACE LOG: Subfase 3.4 - Catálogo, Creaciones & Ciclo de Vida\n";
$httpLogBuffer .= "  Fecha: " . date("r") . "\n";
$httpLogBuffer .= "  Host: http://localhost:8000\n";
$httpLogBuffer .= "================================================================================\n\n";

$logTrace = function(string $title, array $res) use (&$httpLogBuffer): void {
    $httpLogBuffer .= "--- {$title} ---\n";
    $httpLogBuffer .= "HTTP Status: {$res['status']}\n";
    foreach ($res['headers'] as $k => $v) {
        $httpLogBuffer .= "{$k}: {$v}\n";
    }
    $httpLogBuffer .= "\n" . $res['body'] . "\n\n";
};

// 3.0 Obtener tokens para admin y para artesana_ana
$adminLoginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'admin', 'password' => 'admin123']));

$adminToken = (string)($adminLoginRes['json']['datos']['token'] ?? '');
TestHelper::assertTrue(!empty($adminToken), 'Login de admin exitoso para obtener token');

$artisanLoginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'artesana_ana', 'password' => 'admin123']));

$artisanToken = (string)($artisanLoginRes['json']['datos']['token'] ?? '');
TestHelper::assertTrue(!empty($artisanToken), 'Login de artesana_ana exitoso para obtener token');

// 3.1 GET /api/creaciones/index.php público sin token (HTTP 200 OK)
$publicCatalogRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php?pagina=1&limite=12');
$logTrace('1. GET /api/creaciones/index.php (Público: HTTP 200 OK)', $publicCatalogRes);

TestHelper::assertSame(200, $publicCatalogRes['status'], 'GET /api/creaciones/index.php público responde 200 OK');
TestHelper::assertTrue($publicCatalogRes['json']['exito'] ?? false, 'Catálogo contiene exito: true');
TestHelper::assertTrue(is_array($publicCatalogRes['json']['datos'] ?? null), 'Catálogo contiene array de datos');
TestHelper::assertTrue(isset($publicCatalogRes['json']['paginacion']), 'Catálogo contiene sobre de paginacion');
TestHelper::assertTrue(count($publicCatalogRes['json']['datos']) >= 5, 'El catálogo público retorna las creaciones');

// 3.2 GET /api/creaciones/index.php con filtros (categoría y orden)
$filteredCatRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php?categoria=Amigurumis%20%26%20Figuras&orden=precio_asc');
$logTrace('2. GET /api/creaciones/index.php?categoria=Amigurumis & Figuras (Filtrado: HTTP 200 OK)', $filteredCatRes);

TestHelper::assertSame(200, $filteredCatRes['status'], 'GET /api/creaciones/index.php filtrado responde 200 OK');
TestHelper::assertTrue(count($filteredCatRes['json']['datos']) >= 1, 'Retorna datos filtrados');

// 3.3 GET /api/creaciones/artesanos.php público sin token (ADR-014: HTTP 200 OK)
$artesanosRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/artesanos.php');
$logTrace('3. GET /api/creaciones/artesanos.php (Público ADR-014: HTTP 200 OK)', $artesanosRes);

TestHelper::assertSame(200, $artesanosRes['status'], 'GET /api/creaciones/artesanos.php responde 200 OK');
TestHelper::assertTrue($artesanosRes['json']['exito'] ?? false, 'Respuesta contiene exito: true');
TestHelper::assertTrue(is_array($artesanosRes['json']['datos'] ?? null), 'Respuesta contiene array de artesanos');
TestHelper::assertTrue(count($artesanosRes['json']['datos']) >= 2, 'Al menos 2 artesanos con creaciones activas');
TestHelper::assertTrue(isset($artesanosRes['json']['datos'][0]['total_creaciones']), 'Cada artesano incluye total_creaciones');

// 3.4 GET /api/creaciones/detalle.php?id=1 público (HTTP 200 OK)
$detailRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/detalle.php?id=1');
$logTrace('4. GET /api/creaciones/detalle.php?id=1 (Detalle: HTTP 200 OK)', $detailRes);

TestHelper::assertSame(200, $detailRes['status'], 'GET /api/creaciones/detalle.php?id=1 responde 200 OK');
TestHelper::assertTrue($detailRes['json']['exito'] ?? false, 'Detalle contiene exito: true');
TestHelper::assertSame('Dragón Ignis', $detailRes['json']['datos']['nombre'] ?? '', 'Nombre coincide con Dragón Ignis');

// 3.5 GET /api/creaciones/detalle.php?id=99999 inexistente (HTTP 404 Not Found)
$detailNotFoundRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/detalle.php?id=99999');
$logTrace('5. GET /api/creaciones/detalle.php?id=99999 (Inexistente: HTTP 404)', $detailNotFoundRes);
TestHelper::assertSame(404, $detailNotFoundRes['status'], 'GET /api/creaciones/detalle.php inexistente devuelve 404');

// 3.6 POST en /api/creaciones/index.php (HTTP 405 Method Not Allowed)
$wrongMethodCat = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/index.php');
$logTrace('6. POST /api/creaciones/index.php (Método Inválido: HTTP 405)', $wrongMethodCat);
TestHelper::assertSame(405, $wrongMethodCat['status'], 'POST en endpoint GET de catálogo devuelve 405 Method Not Allowed');

// 3.7 POST /api/creaciones/crear.php sin token (HTTP 401 Unauthorized)
$unauthCreateRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Content-Type: application/json'
], json_encode(['nombre' => 'Intento Sin Token']));
$logTrace('7. POST /api/creaciones/crear.php (Sin Token: HTTP 401 Unauthorized)', $unauthCreateRes);
TestHelper::assertSame(401, $unauthCreateRes['status'], 'POST crear sin token devuelve 401 Unauthorized');

// 3.8 POST /api/creaciones/crear.php con token de artesano (HTTP 201 Created)
$newHttpPieceName = 'Amigurumi Test HTTP ' . time();
$createHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'nombre'           => $newHttpPieceName,
    'categoria'        => 'Fantasía',
    'material'         => 'Hilaza de Algodón',
    'dimensiones'      => '18 cm',
    'precio'           => 28000,
    'costo_materiales' => 7000,
    'cantidad_stock'   => 3,
    'horas_tejido'     => 5.0,
    'descripcion'      => 'Creación insertada vía prueba HTTP en vivo',
    'es_sobre_encargo' => 0,
]));
$logTrace('8. POST /api/creaciones/crear.php (Alta Exitosa: HTTP 201 Created)', $createHttpRes);

TestHelper::assertSame(201, $createHttpRes['status'], 'POST crear con token de artesano devuelve 201 Created');
TestHelper::assertTrue($createHttpRes['json']['exito'] ?? false, 'Respuesta contiene exito: true');
$httpCreatedId = (int)($createHttpRes['json']['datos']['id'] ?? 0);
TestHelper::assertTrue($httpCreatedId > 0, 'ID de creación devuelto es > 0');
TestHelper::assertSame(2, (int)($createHttpRes['json']['datos']['artesano']['id'] ?? 0), 'Autoría asignada a artesana_ana (ID 2)');
TestHelper::assertTrue(str_contains($createHttpRes['json']['datos']['imagen_url'] ?? '', '.svg'), 'Fallback SVG asignado al no enviar archivo');

// 3.9 POST /api/creaciones/crear.php con datos inválidos (HTTP 422 Unprocessable)
$invalidCreateRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'nombre' => 'X', // Menor a 2 caracteres
    'precio' => -50,
]));
$logTrace('9. POST /api/creaciones/crear.php (Datos Inválidos: HTTP 422)', $invalidCreateRes);
TestHelper::assertSame(422, $invalidCreateRes['status'], 'POST crear con datos inválidos devuelve 422');

// 3.10 POST /api/creaciones/actualizar.php por el autor propietario (HTTP 200 OK)
$updateHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/actualizar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'               => $httpCreatedId,
    'nombre'           => $newHttpPieceName . ' Modificado',
    'categoria'        => 'Fantasía',
    'material'         => 'Hilaza de Algodón Mercerizado',
    'dimensiones'      => '19 cm',
    'precio'           => 30000,
    'costo_materiales' => 7500,
    'cantidad_stock'   => 4,
    'horas_tejido'     => 5.5,
    'descripcion'      => 'Descripción actualizada por el autor',
    'es_sobre_encargo' => 0,
]));
$logTrace('10. POST /api/creaciones/actualizar.php (Autor Propietario: HTTP 200 OK)', $updateHttpRes);

TestHelper::assertSame(200, $updateHttpRes['status'], 'POST actualizar por el autor devuelve 200 OK');
TestHelper::assertSame($newHttpPieceName . ' Modificado', $updateHttpRes['json']['datos']['nombre'] ?? '', 'Nombre actualizado en la respuesta');

// 3.11 PREVENCIÓN IDOR: POST /api/creaciones/actualizar.php por artesano ajeno sobre pieza de admin (HTTP 403 Forbidden)
// Dragón Ignis (ID 1) pertenece al admin (artesano_id = 1). Artesana Ana (ID 2) intenta modificarlo.
$idorUpdateRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/actualizar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'          => 1,
    'nombre'      => 'Hackeo de Dragon Ignis',
    'categoria'   => 'Fantasía',
    'material'    => 'Hilaza',
    'dimensiones' => '22 cm',
    'precio'      => 99900,
]));
$logTrace('11. POST /api/creaciones/actualizar.php (IDOR No-Propietario: HTTP 403 Forbidden)', $idorUpdateRes);

TestHelper::assertSame(403, $idorUpdateRes['status'], 'PREVENCIÓN IDOR: Intento de modificar pieza ajena por artesano devuelve 403 Forbidden');
TestHelper::assertFalse($idorUpdateRes['json']['exito'] ?? true, 'Respuesta 403 contiene exito: false');

// 3.12 POST /api/creaciones/actualizar.php por Admin sobre pieza de artesano (HTTP 200 OK - Admin Bypass)
$adminUpdateRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/actualizar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id'               => $httpCreatedId, // Pertenece a artesana_ana
    'nombre'           => $newHttpPieceName . ' Supervisado por Admin',
    'categoria'        => 'Fantasía',
    'material'         => 'Hilaza de Algodón Mercerizado',
    'dimensiones'      => '19 cm',
    'precio'           => 31000,
    'costo_materiales' => 7500,
    'cantidad_stock'   => 4,
    'horas_tejido'     => 5.5,
    'descripcion'      => 'Supervisión administrativa de pieza',
    'es_sobre_encargo' => 0,
]));
$logTrace('12. POST /api/creaciones/actualizar.php (Admin sobre Pieza Ajena: HTTP 200 OK)', $adminUpdateRes);
TestHelper::assertSame(200, $adminUpdateRes['status'], 'Administrador puede actualizar piezas de otros creadores (200 OK)');

// 3.13 POST /api/creaciones/ajustar-stock.php con propietario (HTTP 200 OK)
$adjustStockRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/ajustar-stock.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'             => $httpCreatedId,
    'cantidad_stock' => 9,
]));
$logTrace('13. POST /api/creaciones/ajustar-stock.php (Stock: HTTP 200 OK)', $adjustStockRes);

TestHelper::assertSame(200, $adjustStockRes['status'], 'POST ajustar-stock por propietario devuelve 200 OK');
TestHelper::assertSame(9, (int)($adjustStockRes['json']['datos']['cantidad_stock'] ?? 0), 'Stock actualizado a 9');

// 3.14 POST /api/creaciones/ajustar-stock.php IDOR no-propietario (HTTP 403 Forbidden)
$idorStockRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/ajustar-stock.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'             => 1, // Pieza del admin
    'cantidad_stock' => 50,
]));
$logTrace('14. POST /api/creaciones/ajustar-stock.php (IDOR No-Propietario: HTTP 403)', $idorStockRes);
TestHelper::assertSame(403, $idorStockRes['status'], 'POST ajustar-stock en pieza ajena devuelve 403 Forbidden');

// 3.15 POST /api/creaciones/toggle-encargo.php con propietario (HTTP 200 OK)
$toggleEncargoRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/toggle-encargo.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'               => $httpCreatedId,
    'es_sobre_encargo' => 1,
]));
$logTrace('15. POST /api/creaciones/toggle-encargo.php (Toggle: HTTP 200 OK)', $toggleEncargoRes);

TestHelper::assertSame(200, $toggleEncargoRes['status'], 'POST toggle-encargo por propietario devuelve 200 OK');
TestHelper::assertSame(1, (int)($toggleEncargoRes['json']['datos']['es_sobre_encargo'] ?? 0), 'es_sobre_encargo es 1');

// 3.16 POST /api/creaciones/toggle-encargo.php IDOR no-propietario (HTTP 403 Forbidden)
$idorToggleRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/toggle-encargo.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'               => 1,
    'es_sobre_encargo' => 1,
]));
$logTrace('16. POST /api/creaciones/toggle-encargo.php (IDOR No-Propietario: HTTP 403)', $idorToggleRes);
TestHelper::assertSame(403, $idorToggleRes['status'], 'POST toggle-encargo en pieza ajena devuelve 403 Forbidden');

// 3.17 POST /api/creaciones/eliminar.php IDOR no-propietario (HTTP 403 Forbidden)
$idorDeleteRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => 1, // Pieza del admin
]));
$logTrace('17. POST /api/creaciones/eliminar.php (IDOR No-Propietario: HTTP 403)', $idorDeleteRes);
TestHelper::assertSame(403, $idorDeleteRes['status'], 'POST eliminar en pieza ajena devuelve 403 Forbidden');

// 3.18 POST /api/creaciones/eliminar.php por el autor propietario (HTTP 200 OK)
$deleteHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $httpCreatedId,
]));
$logTrace('18. POST /api/creaciones/eliminar.php (Baja Lógica Exitosa: HTTP 200 OK)', $deleteHttpRes);

TestHelper::assertSame(200, $deleteHttpRes['status'], 'POST eliminar por autor devuelve 200 OK');
TestHelper::assertSame(0, (int)($deleteHttpRes['json']['datos']['activo'] ?? 1), 'Respuesta confirma activo: 0 (baja lógica)');

// 3.19 GET /api/creaciones/detalle.php de creación dada de baja (HTTP 404 Not Found)
$deletedDetailRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/detalle.php?id=' . $httpCreatedId);
$logTrace('19. GET /api/creaciones/detalle.php (Pieza Eliminada: HTTP 404)', $deletedDetailRes);
TestHelper::assertSame(404, $deletedDetailRes['status'], 'GET detalle de pieza dada de baja devuelve 404');

// 3.20 POST /api/creaciones/eliminar.php de pieza ya inactiva (HTTP 409 Conflict)
$alreadyDelHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $httpCreatedId,
]));
$logTrace('20. POST /api/creaciones/eliminar.php (Ya Inactiva: HTTP 409 Conflict)', $alreadyDelHttpRes);
TestHelper::assertSame(409, $alreadyDelHttpRes['status'], 'POST eliminar en pieza ya inactiva devuelve 409 Conflict');

// 3.21 POST /api/creaciones/restaurar.php con artesano ajeno (HTTP 403 Forbidden)
$idorRestoreRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/restaurar.php', [
    'Authorization: Bearer ' . $adminToken, // admin tiene permiso, usemos artisan sobre pieza del admin
    'Content-Type: application/json'
], json_encode([
    'id' => 1,
]));
// Para probar 403 IDOR en restaurar, usamos artisanToken en pieza ajena inactiva
// Creemos una pieza rápida inactiva del admin para probar
$adminInactiveId = $creacionRepo->create([
    'artesano_id'      => 1,
    'nombre'           => 'Pieza Admin Inactiva ' . time(),
    'categoria'        => 'Fantasía',
    'material'         => 'Hilaza',
    'dimensiones'      => '10 cm',
    'precio'           => 10000,
    'costo_materiales' => 2000,
    'cantidad_stock'   => 1,
    'horas_tejido'     => 1.0,
    'es_sobre_encargo' => 0,
]);
$creacionRepo->softDelete($adminInactiveId);

$artisanIdorRestoreRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/restaurar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $adminInactiveId,
]));
$logTrace('21. POST /api/creaciones/restaurar.php (IDOR No-Propietario: HTTP 403)', $artisanIdorRestoreRes);
TestHelper::assertSame(403, $artisanIdorRestoreRes['status'], 'POST restaurar pieza ajena devuelve 403 Forbidden');

// 3.22 POST /api/creaciones/restaurar.php por propietario (ADR-015: HTTP 200 OK)
$restoreHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/restaurar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $httpCreatedId,
]));
$logTrace('22. POST /api/creaciones/restaurar.php (Reactivación Exitosa: HTTP 200 OK)', $restoreHttpRes);

TestHelper::assertSame(200, $restoreHttpRes['status'], 'POST restaurar por propietario devuelve 200 OK');
TestHelper::assertSame(1, (int)($restoreHttpRes['json']['datos']['activo'] ?? 0), 'Respuesta confirma activo: 1 tras restauración');

// 3.23 POST /api/creaciones/restaurar.php en pieza ya activa (HTTP 409 Conflict)
$alreadyActiveHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/restaurar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $httpCreatedId,
]));
$logTrace('23. POST /api/creaciones/restaurar.php (Ya Activa: HTTP 409 Conflict)', $alreadyActiveHttpRes);
TestHelper::assertSame(409, $alreadyActiveHttpRes['status'], 'POST restaurar en pieza ya activa devuelve 409 Conflict');

// 3.24 Preflight CORS OPTIONS en /api/creaciones/index.php y /api/creaciones/crear.php
$corsCatRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/creaciones/index.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: GET',
    'Access-Control-Request-Headers: Authorization, Content-Type',
]);
$logTrace('24. OPTIONS /api/creaciones/index.php (Preflight CORS: HTTP 204)', $corsCatRes);
TestHelper::assertTrue(in_array($corsCatRes['status'], [200, 204], true), 'Preflight OPTIONS en creaciones index responde 200/204');

$corsCrearRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/creaciones/crear.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: Authorization, Content-Type',
]);
$logTrace('25. OPTIONS /api/creaciones/crear.php (Preflight CORS: HTTP 204)', $corsCrearRes);
TestHelper::assertTrue(in_array($corsCrearRes['status'], [200, 204], true), 'Preflight OPTIONS en creaciones crear responde 200/204');

// Limpieza de piezas temporales
$creacionRepo->softDelete($httpCreatedId);
$creacionRepo->softDelete($adminInactiveId);

// Guardar log de trazas HTTP
file_put_contents(dirname(__DIR__) . '/logs/subfase-3.4-http.log', $httpLogBuffer);

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
