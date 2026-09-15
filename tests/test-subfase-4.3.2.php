<?php
/**
 * Test Suite: Subfase 4.3.2 - Contadores Correctos del Panel (badge/rango/KPIs)
 * Feature 006 (correctivo) · Plan Maestro Fase 4 (009) · Algodón Nórdico
 *
 * Causa raíz: el badge mezclaba tamaño de página con total ("12 de 45"),
 * `fetchKpis` truncaba a 960 piezas (20×48) con catch silente, y los KPIs
 * heredaban filtros aunque sus etiquetas implican globales.
 *
 * Valida:
 * 1. Badge `#creacionesCountBadge` muestra el total filtrado ("N piezas").
 * 2. Paginación muestra el rango real ("Mostrando A–B de N", 0 si vacío).
 * 3. KPIs globales sin filtros vía agregado servidor (`resumen=1`, exacto sin tope).
 * 4. Servicio `getOwnSummary`: scoping por rol + `estado`, verificado contra SQL
 *    independiente.
 * 5. HTTP vivo del resumen (200 + sobre exacto, 401 sin token).
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

TestHelper::init('Subfase 4.3.2: Contadores Correctos del Panel (badge/rango/KPIs)');

// =============================================================================
// 1. VISTA: chip de rango con A–B–N y badge de total
// =============================================================================
TestHelper::section('1. Vista: rango de paginación A–B–N y badge de total');

$panelView = (string)@file_get_contents("$root/views/pages/creaciones_content.php");
TestHelper::assertStringContains('id="creacionesCountBadge"', $panelView, 'El panel conserva #creacionesCountBadge (total filtrado)');
TestHelper::assertStringContains('id="creacionesShowingFrom"', $panelView, 'La paginación expone #creacionesShowingFrom (inicio del rango)');
TestHelper::assertStringContains('id="creacionesShowingTo"', $panelView, 'La paginación expone #creacionesShowingTo (fin del rango)');
TestHelper::assertStringContains('id="creacionesTotalCount"', $panelView, 'La paginación expone #creacionesTotalCount (total)');
TestHelper::assertFalse(str_contains($panelView, 'id="creacionesShowingCount"'), 'Desaparece #creacionesShowingCount (mezclaba página con total)');

// =============================================================================
// 2. JS: badge total, rango real y KPIs vía resumen (sin loop de 960)
// =============================================================================
TestHelper::section('2. JS: totales claros, rango real y resumen servidor');

$creacionesJs = (string)@file_get_contents("$root/src/js/modules/creaciones.js");
TestHelper::assertStringContains('creacionesShowingFrom', $creacionesJs, 'creaciones.js puebla el inicio del rango');
TestHelper::assertStringContains('creacionesShowingTo', $creacionesJs, 'creaciones.js puebla el fin del rango');
TestHelper::assertStringContains('piezas`', $creacionesJs, 'El badge usa el formato "N piezas" (total filtrado)');
TestHelper::assertFalse(str_contains($creacionesJs, 'de ${totalItems} piezas'), 'El badge ya no mezcla página con total ("X de Y")');
TestHelper::assertStringContains("q.set('resumen', '1')", $creacionesJs, 'Los KPIs se piden al agregado servidor (resumen=1, exacto sin tope)');
TestHelper::assertFalse(str_contains($creacionesJs, 'buildQueryWithoutPage'), 'Desaparece el loop paginado de KPIs (tope 960)');
TestHelper::assertStringContains('valor_centavos', $creacionesJs, 'Los KPIs leen el agregado en centavos (R-06)');
TestHelper::assertFalse(str_contains($creacionesJs, 'innerHTML ='), 'Sin regresión H-004 en el retrabajo de contadores');

exec('node --check ' . escapeshellarg("$root/src/js/modules/creaciones.js") . ' 2>&1', $jsOut, $jsExit);
TestHelper::assertSame(0, $jsExit, 'node --check de creaciones.js sin errores tras 4.3.2');

// =============================================================================
// 3. SERVICIO getOwnSummary: agregado exacto con scoping + estado
// =============================================================================
TestHelper::section('3. Servicio getOwnSummary: exacto, con scoping y sin tope');

TestHelper::assertTrue(method_exists(CreacionService::class, 'getOwnSummary'), 'CreacionService::getOwnSummary() existe');

$service = new CreacionService();
$admin = ['id' => 1, 'rol' => 'admin'];
$ana = ['id' => 2, 'rol' => 'artesano'];

$db = new PDO('sqlite:' . $root . '/database/database.sqlite');
$db->exec('PRAGMA foreign_keys = ON;');
$expectedAna = $db->query("SELECT COUNT(*) AS modelos, COALESCE(SUM(cantidad_stock),0) AS unidades, COALESCE(SUM(cantidad_stock*precio),0) AS valor, COALESCE(SUM(cantidad_stock*costo_materiales),0) AS costo FROM creaciones WHERE activo = 1 AND artesano_id = 2")->fetch(PDO::FETCH_ASSOC);
$expectedAdmin = $db->query("SELECT COUNT(*) AS modelos, COALESCE(SUM(cantidad_stock),0) AS unidades, COALESCE(SUM(cantidad_stock*precio),0) AS valor, COALESCE(SUM(costo_materiales*cantidad_stock),0) AS costo FROM creaciones WHERE activo = 1")->fetch(PDO::FETCH_ASSOC);

$sumAna = $service->getOwnSummary([], $ana);
TestHelper::assertSame((int)$expectedAna['modelos'], (int)$sumAna['modelos'], 'Resumen de Ana: modelos exactos vs SQL independiente');
TestHelper::assertSame((int)$expectedAna['unidades'], (int)$sumAna['unidades'], 'Resumen de Ana: unidades exactas vs SQL independiente');
TestHelper::assertSame((int)$expectedAna['valor'], (int)$sumAna['valor_centavos'], 'Resumen de Ana: valor en centavos exacto (R-06)');
TestHelper::assertSame((int)$expectedAna['costo'], (int)$sumAna['costo_centavos'], 'Resumen de Ana: costo en centavos exacto (R-06)');

$sumAdmin = $service->getOwnSummary([], $admin);
TestHelper::assertSame((int)$expectedAdmin['modelos'], (int)$sumAdmin['modelos'], 'Resumen admin: modelos globales exactos');
TestHelper::assertSame((int)$expectedAdmin['unidades'], (int)$sumAdmin['unidades'], 'Resumen admin: unidades globales exactas');
TestHelper::assertTrue((int)$sumAdmin['modelos'] >= (int)$sumAna['modelos'], 'El resumen admin cubre al menos lo de Ana (visión global)');

// Spoof ignorado también en el resumen
$sumSpoof = $service->getOwnSummary(['artesano_id' => 1], $ana);
TestHelper::assertSame((int)$sumAna['modelos'], (int)$sumSpoof['modelos'], 'El resumen ignora artesano_id ajeno para artesana (anti-spoof)');
$sumAdminFilter = $service->getOwnSummary(['artesano_id' => 2], $admin);
TestHelper::assertSame((int)$sumAna['modelos'], (int)$sumAdminFilter['modelos'], 'El admin filtra el resumen por artesano_id=2');

// Papelera en el resumen
$expectedTrash = $db->query("SELECT COUNT(*) AS modelos, COALESCE(SUM(cantidad_stock),0) AS unidades FROM creaciones WHERE activo = 0 AND artesano_id = 2")->fetch(PDO::FETCH_ASSOC);
$sumTrash = $service->getOwnSummary(['estado' => 'inactivas'], $ana);
TestHelper::assertSame((int)$expectedTrash['modelos'], (int)$sumTrash['modelos'], 'Resumen papelera: modelos exactos (activo=0)');
TestHelper::assertSame((int)$expectedTrash['unidades'], (int)$sumTrash['unidades'], 'Resumen papelera: unidades exactas');

// =============================================================================
// 4. HTTP VIVO del resumen
// =============================================================================
TestHelper::section('4. HTTP Vivo: resumen exacto, 401 y scoping');

$noAuth = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?resumen=1');
TestHelper::assertSame(401, $noAuth['status'], 'HTTP mias.php?resumen=1 sin Bearer devuelve 401');

$loginAna = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'artesana_ana', 'password' => 'artesana123']));
TestHelper::assertSame(200, $loginAna['status'], 'HTTP login artesana_ana devuelve 200 OK');
$tokenAna = (string)($loginAna['json']['datos']['token'] ?? '');

$httpSum = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?resumen=1', [
    'Authorization: Bearer ' . $tokenAna,
]);
TestHelper::assertSame(200, $httpSum['status'], 'HTTP resumen como artesana devuelve 200 OK');
TestHelper::assertSame((int)$sumAna['modelos'], (int)($httpSum['json']['datos']['modelos'] ?? -1), 'HTTP y servicio coinciden en modelos (sin divergencia)');
TestHelper::assertSame((int)$sumAna['unidades'], (int)($httpSum['json']['datos']['unidades'] ?? -1), 'HTTP y servicio coinciden en unidades');
TestHelper::assertSame((int)$sumAna['valor_centavos'], (int)($httpSum['json']['datos']['valor_centavos'] ?? -1), 'HTTP y servicio coinciden en valor');
TestHelper::assertArrayHasKey('costo_centavos', $httpSum['json']['datos'] ?? [], 'El sobre del resumen incluye costo_centavos');

$loginAdmin = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'admin', 'password' => 'admin123']));
$tokenAdmin = (string)($loginAdmin['json']['datos']['token'] ?? '');
$httpSumAdmin = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?resumen=1', [
    'Authorization: Bearer ' . $tokenAdmin,
]);
TestHelper::assertSame((int)$sumAdmin['modelos'], (int)($httpSumAdmin['json']['datos']['modelos'] ?? -1), 'HTTP resumen admin: modelos globales exactos');

$httpSumTrash = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/mias.php?resumen=1&estado=inactivas', [
    'Authorization: Bearer ' . $tokenAna,
]);
TestHelper::assertSame((int)$sumTrash['modelos'], (int)($httpSumTrash['json']['datos']['modelos'] ?? -1), 'HTTP resumen papelera: modelos exactos');

// =============================================================================
// 5. INSIGNIA LATERAL: mismo total real que el KPI (adiós mock "5")
// =============================================================================
TestHelper::section('5. Insignia Lateral: total real del ámbito visible');

$sidebar = (string)@file_get_contents("$root/views/components/panel_sidebar.php");
TestHelper::assertStringContains('id="sidebarBadgeCreaciones"', $sidebar, 'El menú lateral conserva #sidebarBadgeCreaciones');
TestHelper::assertStringContains('id="sidebarBadgeCreaciones">0<', $sidebar, 'La insignia parte de 0 (sin mock de semilla)');
TestHelper::assertFalse(str_contains($sidebar, 'id="sidebarBadgeCreaciones">5<'), 'La insignia ya no hardcodea el mock "5"');

TestHelper::assertStringContains('export async function initSidebarBadges', $creacionesJs, 'creaciones.js exporta initSidebarBadges()');
TestHelper::assertStringContains('resumen', $creacionesJs, 'La insignia usa la misma fuente que el KPI (resumen del ámbito)');
TestHelper::assertStringContains('json.datos.modelos', $creacionesJs, 'La insignia muestra modelos del resumen (total real)');
TestHelper::assertStringContains('initSidebarBadges', file_get_contents("$root/src/js/main.js"), 'main.js importa initSidebarBadges()');
TestHelper::assertStringContains('initSidebarBadges();', file_get_contents("$root/src/js/main.js"), 'main.js ejecuta initSidebarBadges() en DOMContentLoaded');

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
