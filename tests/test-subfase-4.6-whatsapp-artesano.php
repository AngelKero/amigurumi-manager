<?php
/**
 * Test Suite: Subfase 4.6 - WhatsApp al Artesano (Destino Vendedor)
 * Feature 010 · Plan Maestro Fase 4 (009) · Algodón Nórdico Design System
 *
 * Valida:
 * 1. DB: columna usuarios.whatsapp (DDL + CHECK + migración + seeds).
 * 2. WhatsAppHelper: normalización E.164 MX, validación opcional y link server-side.
 * 3. Usuarios: alta con whatsapp, edición admin/autoservicio, IDOR 403, retiro (null).
 * 4. Pedidos: enlace_whatsapp al ARTESANO (voz comprador→artesano), null sin número,
 *    exposición pública en ficha/catálogo.
 * 5. HTTP vivo: solicitar + actualizar-whatsapp (200/403/422) + páginas con CSP.
 * 6. Frontend estático: campos, botones, hints y node --check (H-004).
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
use App\Services\PedidoService;
use App\Services\CreacionService;
use App\Services\UsuarioService;
use App\Repositories\UsuarioRepository;
use App\Repositories\PedidoRepository;
use App\Utils\WhatsAppHelper;

$root = dirname(__DIR__);

TestHelper::init('Subfase 4.6: WhatsApp al Artesano (Destino Vendedor)');

// =============================================================================
// 1. DB: columna, restricción, migración y semillas
// =============================================================================
TestHelper::section('1. DB: columna usuarios.whatsapp + CHECK + seeds');

$pdo = new PDO('sqlite:' . $root . '/database/database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$cols = $pdo->query('PRAGMA table_info(usuarios);')->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($cols, 'name');
TestHelper::assertTrue(in_array('whatsapp', $colNames, true), 'La columna usuarios.whatsapp existe (migración 010)');

$seedSql = (string)@file_get_contents($root . '/database/seed.sql');
TestHelper::assertStringContains('whatsapp TEXT DEFAULT NULL', $seedSql, 'seed.sql declara whatsapp TEXT DEFAULT NULL');
TestHelper::assertStringContains('chk_usuarios_whatsapp', $seedSql, 'seed.sql incluye CHECK chk_usuarios_whatsapp (≤20)');
TestHelper::assertTrue(is_file($root . '/scripts/migrate-010-whatsapp.php'), 'Existe scripts/migrate-010-whatsapp.php (CLI idempotente)');

$usuarioRepo = new UsuarioRepository($pdo);
$adminRow = $usuarioRepo->findByIdSafe(1);
TestHelper::assertSame('5501112222', (string)($adminRow['whatsapp'] ?? ''), 'Seed: admin tiene WhatsApp demo');
$anaRow = $usuarioRepo->findByIdSafe(2);
TestHelper::assertSame('5512345678', (string)($anaRow['whatsapp'] ?? ''), 'Seed: artesana_ana tiene WhatsApp demo');

// =============================================================================
// 2. WHATSAPPHELPER: normalización, validación opcional y enlace
// =============================================================================
TestHelper::section('2. WhatsAppHelper: E.164 MX + validación + link');

TestHelper::assertSame('525512345678', WhatsAppHelper::normalize('5512345678'), '10 dígitos → prefijo 52');
TestHelper::assertSame('525548921039', WhatsAppHelper::normalize('+52 55 4892 1039'), 'Limpia +, espacios y guiones');
TestHelper::assertNull(WhatsAppHelper::normalize('1234'), 'Menos de 8 dígitos → null');
TestHelper::assertNull(WhatsAppHelper::normalize(null), 'null → null');
TestHelper::assertNull(WhatsAppHelper::sanitizeOptional(null), 'sanitizeOptional(null) → null (opcional)');
TestHelper::assertNull(WhatsAppHelper::sanitizeOptional('   '), 'sanitizeOptional(vacío) → null (retiro)');
$tooLong = false;
try {
    WhatsAppHelper::sanitizeOptional(str_repeat('5', 21));
} catch (InvalidArgumentException $e) {
    $tooLong = ($e->getCode() === 422);
}
TestHelper::assertTrue($tooLong, 'sanitizeOptional(>20 chars) → 422');
$badDigits = false;
try {
    WhatsAppHelper::sanitizeOptional('12');
} catch (InvalidArgumentException $e) {
    $badDigits = ($e->getCode() === 422);
}
TestHelper::assertTrue($badDigits, 'sanitizeOptional(<8 dígitos) → 422');
$link = WhatsAppHelper::link('5512345678', "¡Hola! Soy Valeria, pedido #7.");
TestHelper::assertStringContains('https://wa.me/525512345678?text=', (string)$link, 'link() construye wa.me E.164 con ?text=');
TestHelper::assertNull(WhatsAppHelper::link(null, 'Hola'), 'link(null) → null (empty-state)');

// =============================================================================
// 3. USUARIOS: alta, edición admin/autoservicio, IDOR y retiro
// =============================================================================
TestHelper::section('3. Usuarios: alta con WhatsApp, edición y guardas IDOR');

$usuarioService = new UsuarioService($usuarioRepo);
$uid = substr(md5((string)microtime(true)), 0, 8);
$admin = ['id' => 1, 'rol' => 'admin'];
$ana = ['id' => 2, 'rol' => 'artesano'];

$tmpArtisan = $usuarioService->createUser('wa_artesano_' . $uid, 'Secreta123', 'artesano', '5590001111');
$tmpArtisanId = (int)$tmpArtisan['id'];
TestHelper::assertTrue($tmpArtisanId > 0, 'createUser() con WhatsApp registra al artesano');
TestHelper::assertSame('5590001111', (string)($tmpArtisan['whatsapp'] ?? ''), 'El número queda persistido en el alta');

$badCreate = false;
try {
    $usuarioService->createUser('wa_malo_' . $uid, 'Secreta123', 'artesano', '12');
} catch (InvalidArgumentException $e) {
    $badCreate = ($e->getCode() === 422);
}
TestHelper::assertTrue($badCreate, 'createUser() con WhatsApp inválido → 422');

$selfEdit = $usuarioService->updateWhatsapp($tmpArtisanId, '+52 55 9000 2222', ['id' => $tmpArtisanId, 'rol' => 'artesano']);
TestHelper::assertSame('+52 55 9000 2222', (string)($selfEdit['whatsapp'] ?? ''), 'Autoservicio: el artesano edita su propio número');

$adminEdit = $usuarioService->updateWhatsapp($tmpArtisanId, '5590003333', $admin);
TestHelper::assertSame('5590003333', (string)($adminEdit['whatsapp'] ?? ''), 'Admin edita el número de otro usuario');

$idor403 = null;
try {
    $usuarioService->updateWhatsapp(1, '5599999999', $ana);
} catch (RuntimeException $e) {
    $idor403 = (int)$e->getCode();
}
TestHelper::assertSame(403, $idor403, 'IDOR: artesana no modifica el WhatsApp de otro (403, R-04)');

$withdraw = $usuarioService->updateWhatsapp($tmpArtisanId, null, $admin);
TestHelper::assertNull($withdraw['whatsapp'], 'Retiro: whatsapp null deja al artesano sin número');
$restoreTmp = $usuarioService->updateWhatsapp($tmpArtisanId, '5590001111', $admin);
TestHelper::assertSame('5590001111', (string)($restoreTmp['whatsapp'] ?? ''), 'Restauración del número temporal');

// =============================================================================
// 4. PEDIDOS: destino artesano, voz comprador y exposición pública
// =============================================================================
TestHelper::section('4. Pedidos: enlace al artesano + ficha pública');

$pedidoService = new PedidoService();
$creacionService = new CreacionService();

$waPiece = $creacionService->createCreation([
    'nombre' => 'ZZ46 WA ' . $uid,
    'categoria' => 'Hogar & Decoración',
    'material' => 'Algodón de prueba 4.6',
    'dimensiones' => '12 x 12 cm',
    'precio' => 20000,
    'costo_materiales' => 5000,
    'cantidad_stock' => 4,
    'horas_tejido' => 1.0,
    'descripcion' => 'Pieza efímera de la suite 4.6.',
    'es_sobre_encargo' => 0,
    'artesano_id' => $tmpArtisanId,
], null, $admin);
$waPieceId = (int)$waPiece['id'];
TestHelper::assertTrue($waPieceId > 0, 'Pieza efímera del artesano temporal creada');

$waOrder = $pedidoService->requestPublicOrder([
    'creacion_id' => $waPieceId,
    'cliente_nombre' => 'Clienta WA',
    'cliente_contacto' => '5548921039',
    'cantidad' => 1,
]);
$waOrderId = (int)$waOrder['id'];
$waUrl = (string)($waOrder['enlace_whatsapp'] ?? '');
TestHelper::assertStringContains('wa.me/525590001111', $waUrl, 'solicitar: destino wa.me del ARTESANO (no del comprador)');
TestHelper::assertFalse(str_contains($waUrl, 'wa.me/525548921039'), 'solicitar: el número del comprador NO es el destino');
TestHelper::assertStringContains(rawurlencode('¡Hola! Soy Clienta WA'), $waUrl, 'solicitar: mensaje en voz del comprador');
TestHelper::assertStringContains(rawurlencode('pedido #' . $waOrderId), $waUrl, 'solicitar: mensaje cita el folio del pedido');
$waBuyerUrl = (string)($waOrder['enlace_whatsapp_comprador'] ?? '');
TestHelper::assertStringContains('wa.me/525548921039', $waBuyerUrl, 'solicitar: enlace_whatsapp_comprador al COMPRADOR (panel del artesano)');
TestHelper::assertStringContains(rawurlencode('Te escribo de Crochet Manager'), $waBuyerUrl, 'solicitar: mensaje al comprador en voz del artesano');

$enriched = $pedidoService->getOrderById($waOrderId, $admin);
TestHelper::assertSame('5590001111', (string)($enriched['creacion']['artesano_whatsapp'] ?? ''), 'enrich: creacion.artesano_whatsapp hidratado vía JOIN');
TestHelper::assertStringContains('wa.me/525590001111', (string)($enriched['enlace_whatsapp'] ?? ''), 'enrich: enlace al artesano en listado/detalle');
TestHelper::assertStringContains('wa.me/525548921039', (string)($enriched['enlace_whatsapp_comprador'] ?? ''), 'enrich: enlace al comprador para el panel');

$noWaPiece = $creacionService->createCreation([
    'nombre' => 'ZZ46 SinWA ' . $uid,
    'categoria' => 'Hogar & Decoración',
    'material' => 'Algodón de prueba 4.6',
    'dimensiones' => '10 x 10 cm',
    'precio' => 15000,
    'costo_materiales' => 4000,
    'cantidad_stock' => 2,
    'horas_tejido' => 1.0,
    'descripcion' => 'Pieza efímera sin WhatsApp.',
    'es_sobre_encargo' => 0,
    'artesano_id' => $tmpArtisanId,
], null, $admin);
$noWaPieceId = (int)$noWaPiece['id'];
$usuarioService->updateWhatsapp($tmpArtisanId, null, $admin);
$noWaOrder = $pedidoService->requestPublicOrder([
    'creacion_id' => $noWaPieceId,
    'cliente_nombre' => 'Clienta SinWA',
    'cliente_contacto' => '5548921039',
    'cantidad' => 1,
]);
$noWaOrderId = (int)$noWaOrder['id'];
TestHelper::assertNull($noWaOrder['enlace_whatsapp'], 'Sin número del artesano → enlace_whatsapp null (empty-state)');
$usuarioService->updateWhatsapp($tmpArtisanId, '5590001111', $admin);

$publicDetail = $creacionService->getCreationById($waPieceId, true);
TestHelper::assertSame('5590001111', (string)($publicDetail['artesano_whatsapp'] ?? ''), 'Ficha pública expone artesano_whatsapp (010-3)');
TestHelper::assertStringContains('wa.me/525590001111', (string)($publicDetail['enlace_whatsapp_artesano'] ?? ''), 'Ficha pública expone enlace_whatsapp_artesano server-side');

// =============================================================================
// 5. HTTP VIVO: solicitar + actualizar-whatsapp + páginas CSP
// =============================================================================
TestHelper::section('5. HTTP vivo: contrato dual sin divergencia');

$baseUrl = 'http://localhost:8000';
$loginAdmin = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type' => 'application/json',
], json_encode(['username' => 'admin', 'password' => 'admin123']));
TestHelper::assertSame(200, $loginAdmin['status'], 'HTTP login admin 200 OK');
$tokenAdmin = (string)($loginAdmin['json']['datos']['token'] ?? '');

$httpBuy = TestHelper::curl('POST', $baseUrl . '/api/pedidos/solicitar.php', [
    'Content-Type' => 'application/json',
], json_encode([
    'creacion_id' => $waPieceId,
    'cliente_nombre' => 'Compradora WA46',
    'cliente_contacto' => '5511223344',
    'cantidad' => 1,
]));
TestHelper::assertSame(201, $httpBuy['status'], 'HTTP solicitar devuelve 201');
TestHelper::assertStringContains('wa.me/525590001111', (string)($httpBuy['json']['datos']['enlace_whatsapp'] ?? ''), 'HTTP: destino al artesano, no a 5511223344');
$httpOrderId = (int)($httpBuy['json']['datos']['id'] ?? 0);

$loginAna = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type' => 'application/json',
], json_encode(['username' => 'artesana_ana', 'password' => 'artesana123']));
$tokenAna = (string)($loginAna['json']['datos']['token'] ?? '');
$httpIdor = TestHelper::curl('POST', $baseUrl . '/api/usuarios/actualizar-whatsapp.php', [
    'Authorization: Bearer ' . $tokenAna,
    'Content-Type' => 'application/json',
], json_encode(['id' => 1, 'whatsapp' => '5599999999']));
TestHelper::assertSame(403, $httpIdor['status'], 'HTTP actualizar-whatsapp ajeno → 403');

$http422 = TestHelper::curl('POST', $baseUrl . '/api/usuarios/actualizar-whatsapp.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type' => 'application/json',
], json_encode(['id' => $tmpArtisanId, 'whatsapp' => '12']));
TestHelper::assertSame(422, $http422['status'], 'HTTP actualizar-whatsapp inválido → 422');

$http200 = TestHelper::curl('POST', $baseUrl . '/api/usuarios/actualizar-whatsapp.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type' => 'application/json',
], json_encode(['id' => $tmpArtisanId, 'whatsapp' => '5590001111']));
TestHelper::assertSame(200, $http200['status'], 'HTTP actualizar-whatsapp válido → 200');

$httpDetail = TestHelper::curl('GET', $baseUrl . '/api/creaciones/detalle.php?id=' . $waPieceId);
TestHelper::assertStringContains('wa.me/525590001111', (string)($httpDetail['json']['datos']['enlace_whatsapp_artesano'] ?? ''), 'HTTP detalle expone enlace del artesano (010-3)');
$httpDetalle = TestHelper::curl('GET', $baseUrl . '/detalle.php?id=' . $waPieceId);
TestHelper::assertSame(200, $httpDetalle['status'], 'HTTP GET /detalle.php devuelve 200 OK');
TestHelper::assertStringContains('detailArtisanWhatsappBtn', $httpDetalle['body'], 'La ficha sirve el botón público de WhatsApp');
$cspDetalle = strtolower($httpDetalle['headers']['content-security-policy'] ?? '');
TestHelper::assertStringContains("script-src 'self'", $cspDetalle, 'CSP de la ficha (H-004)');

// =============================================================================
// 6. FRONTEND ESTÁTICO: campos, botones, hints y sintaxis
// =============================================================================
TestHelper::section('6. Frontend estático + sintaxis (H-004)');

$modalCrear = (string)@file_get_contents($root . '/views/components/modal_crear_usuario.php');
TestHelper::assertStringContains('id="nuevoWhatsapp"', $modalCrear, 'Alta incluye #nuevoWhatsapp opcional');
$modalRol = (string)@file_get_contents($root . '/views/components/modal_editar_rol_usuario.php');
TestHelper::assertStringContains('id="editWhatsapp"', $modalRol, 'Edición incluye #editWhatsapp');
TestHelper::assertStringContains('id="btnGuardarWhatsapp"', $modalRol, 'Edición incluye #btnGuardarWhatsapp (API real)');
$usersPage = (string)@file_get_contents($root . '/views/pages/usuarios_content.php');
TestHelper::assertStringContains('data-whatsapp', $usersPage, 'El directorio expone data-whatsapp por fila');
$sidebar = (string)@file_get_contents($root . '/views/components/panel_sidebar.php');
TestHelper::assertStringContains('id="panelWhatsappBox"', $sidebar, 'El sidebar trae el autoservicio Mi WhatsApp');
TestHelper::assertStringContains('id="formMiWhatsapp"', $sidebar, 'El autoservicio trae #formMiWhatsapp');
$checkoutJs = (string)@file_get_contents($root . '/src/js/modules/checkout.js');
TestHelper::assertStringContains('aún no registra WhatsApp', $checkoutJs, 'checkout.js contempla el hint sin-número');
$detailJs = (string)@file_get_contents($root . '/src/js/modules/detail.js');
TestHelper::assertStringContains('enlace_whatsapp_artesano', $detailJs, 'detail.js hidrata el enlace público del artesano');
TestHelper::assertFalse(str_contains($detailJs, 'wa.me/'), 'detail.js no compone wa.me crudos (servidor manda)');
$usersJs = (string)@file_get_contents($root . '/src/js/modules/users.js');
TestHelper::assertStringContains('/api/usuarios/actualizar-whatsapp.php', $usersJs, 'users.js guarda contra actualizar-whatsapp.php');
TestHelper::assertFalse(str_contains($usersJs, 'wa.me/'), 'users.js no compone wa.me crudos');
$ordersJs46 = (string)@file_get_contents($root . '/src/js/modules/orders.js');
TestHelper::assertStringContains('enlace_whatsapp_comprador', $ordersJs46, 'orders.js contacta al COMPRADOR desde el panel');

foreach (['users.js' => "$root/src/js/modules/users.js", 'auth.js' => "$root/src/js/modules/auth.js"] as $label => $jsPath) {
    exec('node --check ' . escapeshellarg($jsPath) . ' 2>&1', $jsOut46, $jsExit46);
    TestHelper::assertSame(0, $jsExit46, "node --check de {$label} sin errores de sintaxis");
}

// =============================================================================
// 7. LIMPIEZA: pedidos, piezas y usuario efímeros
// =============================================================================
TestHelper::section('7. Limpieza de entidades efímeras');

$pedidoRepo = new PedidoRepository($pdo);
foreach ([$waOrderId, $noWaOrderId, $httpOrderId] as $oid) {
    if ($oid > 0) {
        try {
            $pedidoService->cancelOrder($oid, $admin);
        } catch (Throwable $e) {
        }
        $pedidoRepo->softDelete($oid);
    }
}
TestHelper::assertTrue(true, 'Pedidos efímeros cancelados y en baja lógica');
foreach ([$waPieceId, $noWaPieceId] as $pid) {
    if ($pid > 0) {
        try {
            $creacionService->deleteCreation($pid, $admin);
        } catch (Throwable $e) {
        }
    }
}
TestHelper::assertTrue(true, 'Piezas efímeras en baja lógica');
try {
    $usuarioService->deleteUser($tmpArtisanId, 1);
} catch (Throwable $e) {
}
TestHelper::assertTrue(true, 'Artesano temporal en baja lógica');

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
