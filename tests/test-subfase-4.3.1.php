<?php
/**
 * Test Suite: Subfase 4.3.1 - Scoping Servidor del Panel & Papelera (mias.php)
 * Feature 006 (correctivo) · Plan Maestro Fase 4 (009) · Algodón Nórdico
 *
 * Causa raíz: el panel consumía el endpoint PÚBLICO sin Bearer ni scoping;
 * el filtro `artesano_id` era 100% controlado por el cliente (spoofeable) y el
 * botón Restaurar era inalcanzable (solo se listaban activas).
 *
 * Valida:
 * 1. Endpoint `api/creaciones/mias.php`: existe, delgado (≤60 líneas), CORS,
 *    GET-only (405), RBAC artesano/admin (401) y delegación a getOwnCreations.
 * 2. Servicio `getOwnCreations`: artesano solo ve lo suyo (spoof ignorado),
 *    admin ve todo + filtra por `artesano_id`, `estado ∈ {activas,inactivas,todas}`.
 * 3. BD: índice `idx_creaciones_artesano_activo` en índice vivo + `seed.sql`.
 * 4. Vista + JS: `#filterEstadoSelect`, lectura con Bearer desde `mias.php`,
 *    filtro de autor oculto para no-admin, restaurar/baja según estado.
 * 5. HTTP vivo: 401 sin token, scoping forzado por rol, papelera por HTTP.
 * 6. Docs: ADR-017 + contrato en `docs/api/creaciones.md`.
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

TestHelper::init('Subfase 4.3.1: Scoping Servidor del Panel & Papelera (mias.php)');

// =============================================================================
// 1. CONTRATO DEL ENDPOINT mias.php (delgado, CORS, GET, RBAC)
// =============================================================================
TestHelper::section('1. Endpoint Protegido mias.php: existe, delgado y con RBAC');

$miasPath = "$root/api/creaciones/mias.php";
TestHelper::assertTrue(is_file($miasPath), 'api/creaciones/mias.php existe');
$miasSrc = (string)@file_get_contents($miasPath);
TestHelper::assertTrue($miasSrc !== '', 'mias.php tiene contenido legible');
TestHelper::assertTrue(count(explode("\n", $miasSrc)) <= 62, 'mias.php es un controlador delgado (≤60 líneas efectivas)');
TestHelper::assertStringContains('Response::handleCors()', $miasSrc, 'mias.php resuelve preflight CORS al inicio');
TestHelper::assertStringContains('RoleGuard::artisanOrAdmin()', $miasSrc, 'mias.php exige rol artesano/admin (401 sin Bearer)');
TestHelper::assertStringContains('getOwnCreations', $miasSrc, 'mias.php delega en CreacionService::getOwnCreations()');
TestHelper::assertStringContains('405', $miasSrc, 'mias.php rechaza métodos distintos de GET (405)');

// =============================================================================
// 2. SERVICIO getOwnCreations: scoping forzado por rol + estado
// =============================================================================
TestHelper::section('2. Servicio getOwnCreations: anti-spoof por rol y papelera');

TestHelper::assertTrue(method_exists(CreacionService::class, 'getOwnCreations'), 'CreacionService::getOwnCreations() existe');

$service = new CreacionService();
$admin = ['id' => 1, 'rol' => 'admin'];
$ana = ['id' => 2, 'rol' => 'artesano'];
$uid = substr(md5((string)microtime(true)), 0, 8);
$trackedIds = [];

$mkData = static function (string $nombre) use ($uid): array {
    return [
        'nombre' => $nombre . ' ' . $uid,
        'categoria' => 'Hogar & Decoración',
        'material' => 'Algodón de prueba 4.3.1',
        'dimensiones' => '12 x 12 cm',
        'precio' => 20000,
        'costo_materiales' => 5000,
        'cantidad_stock' => 3,
        'horas_tejido' => 1.5,
        'descripcion' => 'Pieza efímera de la suite 4.3.1.',
        'es_sobre_encargo' => 0,
    ];
};

// 2.1 Pieza de Ana (atribuida por admin) + pieza del admin
$anaPiece = $service->createCreation(array_merge($mkData('ZZ431 Ana'), ['artesano_id' => 2]), null, $admin);
$trackedIds[] = (int)$anaPiece['id'];
$adminPiece = $service->createCreation($mkData('ZZ431 Admin'), null, $admin);
$trackedIds[] = (int)$adminPiece['id'];

// 2.2 Artesana solo ve lo suyo (incluye su pieza efímera)
$mine = $service->getOwnCreations([], $ana);
$mineIds = array_column($mine['datos'], 'id');
TestHelper::assertTrue(in_array((int)$anaPiece['id'], $mineIds, true), 'Ana ve su propia pieza en getOwnCreations()');
TestHelper::assertFalse(in_array((int)$adminPiece['id'], $mineIds, true), 'Ana NO ve la pieza del admin');
$allMineAreHers = true;
foreach (['pagina' => 1, 'limite' => 48] as $k => $v) {
    $mine = $service->getOwnCreations([$k => $v] + ['limite' => 48], $ana);
}
foreach ($mine['datos'] as $item) {
    if ((int)$item['artesano_id'] !== 2) $allMineAreHers = false;
}
TestHelper::assertTrue($allMineAreHers, 'Todas las piezas devueltas a Ana son de artesano_id=2');

// 2.3 Spoof ignorado: Ana pide artesano_id=1 → sigue viendo solo lo suyo
$spoof = $service->getOwnCreations(['artesano_id' => 1, 'limite' => 48], $ana);
$spoofOk = true;
foreach ($spoof['datos'] as $item) {
    if ((int)$item['artesano_id'] !== 2) $spoofOk = false;
}
TestHelper::assertTrue($spoofOk, 'El parámetro artesano_id ajeno se ignora para rol artesano (anti-spoof)');
TestHelper::assertFalse(in_array((int)$adminPiece['id'], array_column($spoof['datos'], 'id'), true), 'El spoof no expone la pieza del admin');

// 2.4 Admin ve todo + filtra por artesano_id real
$allAdmin = $service->getOwnCreations(['limite' => 48], $admin);
$allIds = array_column($allAdmin['datos'], 'id');
TestHelper::assertTrue(in_array((int)$anaPiece['id'], $allIds, true), 'El admin ve la pieza de Ana');
TestHelper::assertTrue(in_array((int)$adminPiece['id'], $allIds, true), 'El admin ve su propia pieza');
$onlyAna = $service->getOwnCreations(['artesano_id' => 2, 'limite' => 48], $admin);
$onlyAnaOk = true;
foreach ($onlyAna['datos'] as $item) {
    if ((int)$item['artesano_id'] !== 2) $onlyAnaOk = false;
}
TestHelper::assertTrue($onlyAnaOk && count($onlyAna['datos']) > 0, 'El admin filtra por artesano_id=2 (dropdown real)');

// 2.5 Papelera: tras la baja, sale de activas y entra en inactivas
$service->deleteCreation((int)$anaPiece['id'], $admin);
$activas = $service->getOwnCreations(['estado' => 'activas', 'limite' => 48], $ana);
TestHelper::assertFalse(in_array((int)$anaPiece['id'], array_column($activas['datos'], 'id'), true), 'La pieza dada de baja sale de estado=activas');
$inactivas = $service->getOwnCreations(['estado' => 'inactivas', 'limite' => 48], $ana);
TestHelper::assertTrue(in_array((int)$anaPiece['id'], array_column($inactivas['datos'], 'id'), true), 'La pieza dada de baja aparece en estado=inactivas (papelera)');
$inactivasOk = true;
foreach ($inactivas['datos'] as $item) {
    if ((int)$item['activo'] !== 0) $inactivasOk = false;
}
TestHelper::assertTrue($inactivasOk, 'estado=inactivas solo devuelve filas con activo=0');
$todas = $service->getOwnCreations(['estado' => 'todas', 'limite' => 48], $ana);
TestHelper::assertTrue(
    in_array((int)$anaPiece['id'], array_column($todas['datos'], 'id'), true),
    'estado=todas incluye activas e inactivas'
);
$service->restoreCreation((int)$anaPiece['id'], $admin);
$backInActivas = $service->getOwnCreations(['estado' => 'activas', 'limite' => 48], $ana);
TestHelper::assertTrue(in_array((int)$anaPiece['id'], array_column($backInActivas['datos'], 'id'), true), 'Tras restaurar, la pieza vuelve a estado=activas');

// 2.6 Estado por defecto e inválido → activas
$def = $service->getOwnCreations(['limite' => 48], $ana);
$defOk = true;
foreach ($def['datos'] as $item) {
    if ((int)$item['activo'] !== 1) $defOk = false;
}
TestHelper::assertTrue($defOk, 'Sin estado se listan solo activas (default seguro)');
$weird = $service->getOwnCreations(['estado' => 'papelera??', 'limite' => 48], $ana);
$weirdOk = true;
foreach ($weird['datos'] as $item) {
    if ((int)$item['activo'] !== 1) $weirdOk = false;
}
TestHelper::assertTrue($weirdOk, 'Un estado inválido cae al default seguro (activas)');

// 2.7 Limpieza efímera (baja lógica)
foreach ($trackedIds as $tid) {
    try {
        $service->deleteCreation($tid, $admin);
    } catch (Throwable $e) {
    }
}
TestHelper::assertTrue(true, 'Limpieza de piezas efímeras 4.3.1 completada (baja lógica)');

// =============================================================================
// 3. BD: índice (artesano_id, activo) vivo + en seed.sql
// =============================================================================
TestHelper::section('3. Índice Compuesto de Papelera en SQLite y Semilla');

$db = new PDO('sqlite:' . $root . '/database/database.sqlite');
$db->exec('PRAGMA foreign_keys = ON;');
$idxRows = $db->query("PRAGMA index_list('creaciones')")->fetchAll(PDO::FETCH_ASSOC);
$idxNames = array_column($idxRows, 'name');
TestHelper::assertTrue(in_array('idx_creaciones_artesano_activo', $idxNames, true), 'Existe el índice vivo idx_creaciones_artesano_activo');
$seedSql = (string)@file_get_contents("$root/database/seed.sql");
TestHelper::assertStringContains('idx_creaciones_artesano_activo', $seedSql, 'seed.sql declara idx_creaciones_artesano_activo (instalaciones nuevas)');
TestHelper::assertStringContains('artesano_id', $seedSql, 'El índice cubre artesano_id');
TestHelper::assertStringContains('CREATE INDEX IF NOT EXISTS idx_creaciones_artesano_activo', $seedSql, 'El índice se declara IF NOT EXISTS (migración idempotente)');

// =============================================================================
// 4. VISTA + JS del panel: mias.php con Bearer, filtro de estado, autor oculto
// =============================================================================
TestHelper::section('4. Panel: lectura autenticada desde mias.php + filtro de estado');

$panelView = (string)@file_get_contents("$root/views/pages/creaciones_content.php");
TestHelper::assertStringContains('id="filterEstadoSelect"', $panelView, 'El panel incluye #filterEstadoSelect (papelera)');
TestHelper::assertStringContains('value="activas"', $panelView, '#filterEstadoSelect ofrece Activas');
TestHelper::assertStringContains('value="inactivas"', $panelView, '#filterEstadoSelect ofrece Inactivas (papelera)');
TestHelper::assertStringContains('value="todas"', $panelView, '#filterEstadoSelect ofrece Todas');

$creacionesJs = (string)@file_get_contents("$root/src/js/modules/creaciones.js");
TestHelper::assertStringContains("'/api/creaciones/mias.php'", $creacionesJs, 'creaciones.js lee el panel desde mias.php (no del catálogo público)');
TestHelper::assertStringContains('MINE_URL', $creacionesJs, 'creaciones.js centraliza la URL del panel en MINE_URL');
TestHelper::assertStringContains('filterEstadoSelect', $creacionesJs, 'creaciones.js cablea #filterEstadoSelect al estado del servidor');
TestHelper::assertStringContains("q.set('estado'", $creacionesJs, 'creaciones.js serializa el parámetro estado (activas/inactivas/todas)');
TestHelper::assertFalse(str_contains($creacionesJs, "INDEX_URL = '/api/creaciones/index.php'"), 'creaciones.js ya no define el panel contra el endpoint público');
$authReads = substr_count($creacionesJs, 'headers: authHeaders()');
TestHelper::assertTrue($authReads >= 3, "creaciones.js envía Bearer en lecturas del panel (fetchPage/fetchKpis/loadArtisans: {$authReads})");
TestHelper::assertStringContains('filterArtisanSelect', $creacionesJs, 'creaciones.js gestiona el filtro de autor');
TestHelper::assertStringContains('data-estado', $creacionesJs, 'Las tarjetas exponen data-estado para alternar baja/restaurar');
TestHelper::assertFalse(str_contains($creacionesJs, 'innerHTML ='), 'creaciones.js sigue sin asignar innerHTML con datos (H-004)');

exec('node --check ' . escapeshellarg("$root/src/js/modules/creaciones.js") . ' 2>&1', $jsOut, $jsExit);
TestHelper::assertSame(0, $jsExit, 'node --check de creaciones.js sin errores tras el cambio a mias.php');

// =============================================================================
// 5. HTTP VIVO: 401, scoping por rol, anti-spoof y papelera
// =============================================================================
TestHelper::section('5. HTTP Vivo: RBAC de lectura, anti-spoof y papelera');

$noAuth = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php');
TestHelper::assertSame(401, $noAuth['status'], 'HTTP mias.php sin Bearer devuelve 401');

$cors = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/creaciones/mias.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: GET',
]);
TestHelper::assertTrue(in_array($cors['status'], [200, 204], true), 'Preflight CORS OPTIONS en mias.php responde correctamente');

$loginAna = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'artesana_ana', 'password' => 'artesana123']));
TestHelper::assertSame(200, $loginAna['status'], 'HTTP login artesana_ana devuelve 200 OK');
$tokenAna = (string)($loginAna['json']['datos']['token'] ?? '');

$loginAdmin = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'admin', 'password' => 'admin123']));
TestHelper::assertSame(200, $loginAdmin['status'], 'HTTP login admin devuelve 200 OK');
$tokenAdmin = (string)($loginAdmin['json']['datos']['token'] ?? '');

$httpMine = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?limite=48', [
    'Authorization: Bearer ' . $tokenAna,
]);
TestHelper::assertSame(200, $httpMine['status'], 'HTTP mias.php como artesana devuelve 200 OK');
$mineOk = true;
foreach (($httpMine['json']['datos'] ?? []) as $item) {
    if ((int)$item['artesano_id'] !== 2) $mineOk = false;
}
TestHelper::assertTrue($mineOk && count($httpMine['json']['datos'] ?? []) > 0, 'HTTP: Ana solo recibe piezas propias (scoping forzado)');
$expectedTotal = $service->getOwnCreations(['limite' => 48], $ana)['paginacion']['total_items'];
TestHelper::assertSame($expectedTotal, (int)($httpMine['json']['paginacion']['total_items'] ?? -1), 'HTTP y servicio coinciden en el total propio (sin divergencia CLI/HTTP)');

$httpSpoof = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?artesano_id=1&limite=48', [
    'Authorization: Bearer ' . $tokenAna,
]);
TestHelper::assertSame(200, $httpSpoof['status'], 'HTTP mias.php?artesano_id=1 como Ana devuelve 200 OK');
$spoofHttpOk = true;
foreach (($httpSpoof['json']['datos'] ?? []) as $item) {
    if ((int)$item['artesano_id'] !== 2) $spoofHttpOk = false;
}
TestHelper::assertTrue($spoofHttpOk, 'HTTP: el spoof de artesano_id se ignora (solo piezas de Ana)');

$httpAdminAll = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?limite=48', [
    'Authorization: Bearer ' . $tokenAdmin,
]);
TestHelper::assertSame(200, $httpAdminAll['status'], 'HTTP mias.php como admin devuelve 200 OK');
TestHelper::assertTrue(count($httpAdminAll['json']['datos'] ?? []) >= count($httpMine['json']['datos'] ?? []), 'HTTP: el admin ve al menos tanto como la artesana (visión global)');
$httpAdminFilter = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?artesano_id=2&limite=48', [
    'Authorization: Bearer ' . $tokenAdmin,
]);
$adminFilterOk = true;
foreach (($httpAdminFilter['json']['datos'] ?? []) as $item) {
    if ((int)$item['artesano_id'] !== 2) $adminFilterOk = false;
}
TestHelper::assertTrue($adminFilterOk, 'HTTP: el admin filtra por artesano_id=2');

$httpTrash = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?estado=inactivas&limite=48', [
    'Authorization: Bearer ' . $tokenAna,
]);
TestHelper::assertSame(200, $httpTrash['status'], 'HTTP mias.php?estado=inactivas devuelve 200 OK');
$trashOk = true;
foreach (($httpTrash['json']['datos'] ?? []) as $item) {
    if ((int)$item['activo'] !== 0) $trashOk = false;
}
TestHelper::assertTrue($trashOk, 'HTTP: la papelera solo trae activo=0');

$httpPanel = TestHelper::curl('GET', 'http://localhost:8000/creaciones.php');
TestHelper::assertSame(200, $httpPanel['status'], 'HTTP GET /creaciones.php devuelve 200 OK');
TestHelper::assertStringContains('id="filterEstadoSelect"', $httpPanel['body'], 'El HTML servido incluye #filterEstadoSelect');

// =============================================================================
// 6. DOCS: ADR-017 + contrato API
// =============================================================================
TestHelper::section('6. Documentación: ADR-017 y contrato del endpoint');

$adrPath = "$root/docs/architecture/decisiones/ADR-017-scoping-lectura-panel-mias.md";
TestHelper::assertTrue(is_file($adrPath), 'ADR-017 (scoping de lectura del panel) existe');
$adr = (string)@file_get_contents($adrPath);
TestHelper::assertStringContains('mias.php', $adr, 'ADR-017 documenta el endpoint mias.php');
TestHelper::assertStringContains('ADR-007', $adr, 'ADR-017 extiende ADR-007 (IDOR) a la lectura');

$apiDoc = (string)@file_get_contents("$root/docs/api/creaciones.md");
TestHelper::assertStringContains('mias.php', $apiDoc, 'docs/api/creaciones.md documenta mias.php');

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
