<?php
/**
 * Test Suite: Subfase 4.4 - Checkout Público, Pedidos Atómicos & WhatsApp
 * Feature 007 · Plan Maestro Fase 4 (009) · Algodón Nórdico Design System
 *
 * Valida:
 * 1. Modal checkout: sin alert/hardcode, creacion_id propagado, feedback + éxito.
 * 2. catalog.js/detail.js/checkout.js: data-* reales, hidratación detalle,
 *    submit JSON a solicitar.php, DOM-safe, node --check.
 * 3. Servicio: enlace_whatsapp en requestPublicOrder, atomicidad ±stock, 409,
 *    precio servidor, idempotencia + restitución, IDOR 403, E.164 + ?text=.
 * 4. Panel: sin $mockOrders/wa crudos, template + paginación + resumen exacto.
 * 5. HTTP vivo: flujo público completo + panel Bearer + páginas CSP.
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

$root = dirname(__DIR__);

TestHelper::init('Subfase 4.4: Checkout Público, Pedidos Atómicos & WhatsApp');

// =============================================================================
// 1. MODAL CHECKOUT: sin mocks, con creacion_id, feedback y éxito
// =============================================================================
TestHelper::section('1. Modal Checkout: compra real sin mocks ni alert()');

$checkoutModal = (string)@file_get_contents("$root/views/components/modal_checkout.php");
TestHelper::assertFalse(str_contains($checkoutModal, 'onsubmit='), 'El modal ya no usa onsubmit con alert() mock');
TestHelper::assertFalse(str_contains($checkoutModal, "alert('"), 'El modal no invoca alert() nativo');
TestHelper::assertFalse(str_contains($checkoutModal, '/api/pedidos/solicitar.php') && str_contains($checkoutModal, 'Fase 4 se conectará'), 'El modal ya no cita la conexión pendiente de Fase 4');
TestHelper::assertFalse(str_contains($checkoutModal, 'value="Mariana Gómez"'), 'El modal ya no precarga el cliente mock');
TestHelper::assertFalse(str_contains($checkoutModal, 'fechaEntrega'), 'El flujo público ya no pide fechaEntrega (solo alta manual)');
TestHelper::assertStringContains('id="publicCheckoutForm"', $checkoutModal, 'El formulario conserva id="publicCheckoutForm"');
TestHelper::assertStringContains('novalidate', $checkoutModal, 'La validación espejo vive en checkout.js (novalidate)');
TestHelper::assertStringContains('id="checkoutCreacionId"', $checkoutModal, 'El modal propaga hidden#checkoutCreacionId');
TestHelper::assertStringContains('id="clienteContacto"', $checkoutModal, 'El modal pide #clienteContacto (WhatsApp del cliente)');
TestHelper::assertStringContains('id="checkoutFeedback"', $checkoutModal, 'El modal incluye #checkoutFeedback accesible');
TestHelper::assertStringContains('role="alert"', $checkoutModal, 'El feedback usa role="alert"');
TestHelper::assertStringContains('id="checkoutSuccess"', $checkoutModal, 'El modal incluye la vista de éxito #checkoutSuccess');
TestHelper::assertStringContains('id="checkoutSuccessFolio"', $checkoutModal, 'El éxito muestra folio #checkoutSuccessFolio');
TestHelper::assertStringContains('id="checkoutWhatsAppBtn"', $checkoutModal, 'El éxito incluye #checkoutWhatsAppBtn (enlace servidor)');
TestHelper::assertStringContains('Total Estimado', $checkoutModal, 'El total del modal se etiqueta estimado (el oficial lo congela el servidor)');

// =============================================================================
// 2. JS: data-* reales, hidratación detalle, submit JSON, DOM-safe
// =============================================================================
TestHelper::section('2. Frontend: data reales, hidratación y compra JSON (H-004)');

$catalogJs = (string)@file_get_contents("$root/src/js/modules/catalog.js");
TestHelper::assertStringContains('data-precio-cents', $catalogJs, 'catalog.js expone data-precio-cents en el botón de compra');
TestHelper::assertStringContains("setAttribute('data-id'", $catalogJs, 'catalog.js propaga data-id real al botón de compra');

$detailJs = (string)@file_get_contents("$root/src/js/modules/detail.js");
TestHelper::assertStringContains('/api/creaciones/detalle.php', $detailJs, 'detail.js hidrata desde GET detalle.php');
TestHelper::assertFalse(str_contains($detailJs, "idParam === '9999'"), 'detail.js ya no simula el 404 con id 9999');
TestHelper::assertFalse(str_contains($detailJs, 'btnSimulateStock'), 'detail.js ya no usa el simulador mock de stock');
TestHelper::assertFalse(str_contains($detailJs, 'innerHTML ='), 'detail.js no asigna innerHTML con datos (H-004)');
TestHelper::assertStringContains('showNotFound', $detailJs, 'detail.js muestra el 404 real ante pieza inexistente');

$checkoutJs = (string)@file_get_contents("$root/src/js/modules/checkout.js");
TestHelper::assertStringContains("'/api/pedidos/solicitar.php'", $checkoutJs, 'checkout.js confirma contra POST solicitar.php');
TestHelper::assertStringContains('creacion_id', $checkoutJs, 'checkout.js envía creacion_id real (no hardcode)');
TestHelper::assertStringContains('enlace_whatsapp', $checkoutJs, 'checkout.js renderiza el enlace WhatsApp del servidor');
TestHelper::assertStringContains('status === 201', $checkoutJs, 'checkout.js gestiona el 201 con vista de éxito');
TestHelper::assertStringContains('status === 422', $checkoutJs, 'checkout.js muestra errores 422 accesibles');
TestHelper::assertStringContains('status === 409', $checkoutJs, 'checkout.js muestra el 409 sin stock');
TestHelper::assertStringContains('ENCARGO_MAX', $checkoutJs, 'checkout.js acota el encargo a 1000 unidades');
TestHelper::assertFalse(str_contains($checkoutJs, 'innerHTML ='), 'checkout.js no asigna innerHTML con datos (H-004)');
TestHelper::assertFalse(str_contains($checkoutJs, "|| 450"), 'checkout.js ya no usa el precio mock 450');

$jsChecks = [
    'checkout.js' => "$root/src/js/modules/checkout.js",
    'detail.js' => "$root/src/js/modules/detail.js",
    'catalog.js' => "$root/src/js/modules/catalog.js",
    'orders.js' => "$root/src/js/modules/orders.js",
    'currency.js' => "$root/src/js/modules/currency.js",
    'dom-safe.js' => "$root/src/js/modules/dom-safe.js",
    'main.js' => "$root/src/js/main.js",
];
foreach ($jsChecks as $label => $jsPath) {
    exec('node --check ' . escapeshellarg($jsPath) . ' 2>&1', $jsOut, $jsExit);
    TestHelper::assertSame(0, $jsExit, "node --check de {$label} sin errores de sintaxis");
}

// 2b. Grafo ES vivo: main.js debe evaluarse e inicializarse sin excepciones
// (caza duplicados/top-level throws que rompen login + catálogo a la vez).
TestHelper::assertTrue(is_file("$root/tests/eval-main.mjs"), 'Existe el harness tests/eval-main.mjs (DOM simulado)');
exec('node ' . escapeshellarg("$root/tests/eval-main.mjs") . ' 2>&1', $evalOut, $evalExit);
TestHelper::assertSame(0, $evalExit, 'El grafo ES de main.js se evalúa e inicializa sin excepciones (login + catálogo vivos)');

// =============================================================================
// 3. SERVICIO: atomicidad, precio servidor, idempotencia, IDOR, WhatsApp
// =============================================================================
TestHelper::section('3. Servicio: stock atómico, precio servidor y WhatsApp E.164');

$pedidoService = new PedidoService();
$creacionService = new CreacionService();
$admin = ['id' => 1, 'rol' => 'admin'];
$ana = ['id' => 2, 'rol' => 'artesano'];
$uid = substr(md5((string)microtime(true)), 0, 8);

$mkCreation = static function (string $nombre, int $stock, int $encargo = 0) use ($uid): array {
    return [
        'nombre' => $nombre . ' ' . $uid,
        'categoria' => 'Hogar & Decoración',
        'material' => 'Algodón de prueba 4.4',
        'dimensiones' => '12 x 12 cm',
        'precio' => 20000,
        'costo_materiales' => 5000,
        'cantidad_stock' => $stock,
        'horas_tejido' => 1.0,
        'descripcion' => 'Pieza efímera de la suite 4.4.',
        'es_sobre_encargo' => $encargo,
    ];
};

// 3.1 Pieza efímera con stock conocido (atribuida a Ana para probar scoping)
$piece = $creacionService->createCreation(array_merge($mkCreation('ZZ44 Pieza', 5), ['artesano_id' => 2]), null, $admin);
$pieceId = (int)$piece['id'];
$stockBefore = (int)$piece['cantidad_stock'];
TestHelper::assertSame(5, $stockBefore, 'Pieza efímera creada con stock 5');

// 3.2 Pedido público: precio congelado por servidor + enlace WhatsApp
$order = $pedidoService->requestPublicOrder([
    'creacion_id' => $pieceId,
    'cliente_nombre' => 'Clienta Prueba',
    'cliente_contacto' => '5548921039',
    'cantidad' => 2,
    'notas' => 'Nota efímera 4.4.',
]);
$orderId = (int)$order['id'];
TestHelper::assertTrue($orderId > 0, 'requestPublicOrder() registra el pedido y devuelve su ID');
TestHelper::assertSame(40000, (int)$order['precio_final'], 'El precio final lo calcula el servidor (20000×2, R-06)');
TestHelper::assertStringContains('wa.me/525512345678', (string)($order['enlace_whatsapp'] ?? ''), 'El enlace apunta al WhatsApp del artesano (E.164 con prefijo 52)');
TestHelper::assertStringContains('?text=', (string)($order['enlace_whatsapp'] ?? ''), 'El enlace incluye mensaje ?text= codificado');
TestHelper::assertSame('Pendiente', (string)$order['estado_pedido'], 'El pedido nace Pendiente');
TestHelper::assertSame('Pendiente', (string)$order['estado_pago'], 'El pago nace Pendiente');

// 3.3 Atomicidad: el stock baja exactamente la cantidad
$after = $creacionService->getCreationById($pieceId, true);
TestHelper::assertSame($stockBefore - 2, (int)$after['cantidad_stock'], 'El stock disminuye exactamente en 2 (R-07 atómico)');

// 3.4 Sin stock y sin encargo → 409 (sin crear fila)
$empty = $creacionService->createCreation($mkCreation('ZZ44 Agotada', 0), null, $admin);
$emptyId = (int)$empty['id'];
$code409 = null;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id' => $emptyId,
        'cliente_nombre' => 'Clienta Prueba',
        'cliente_contacto' => '5548921039',
        'cantidad' => 1,
    ]);
} catch (RuntimeException $e) {
    $code409 = (int)$e->getCode();
}
TestHelper::assertSame(409, $code409, 'Sin stock y sin encargo responde 409 (R-07)');

// 3.5 Bajo encargo sin stock sí permite pedir
$custom = $creacionService->createCreation($mkCreation('ZZ44 Encargo', 0, 1), null, $admin);
$customId = (int)$custom['id'];
$customOrder = $pedidoService->requestPublicOrder([
    'creacion_id' => $customId,
    'cliente_nombre' => 'Clienta Prueba',
    'cliente_contacto' => '5548921039',
    'cantidad' => 3,
]);
TestHelper::assertTrue((int)$customOrder['id'] > 0, 'Bajo encargo sin stock sí registra (confección a pedido)');

// 3.6 Validaciones 422 del flujo público
foreach (['nombre corto' => ['cliente_nombre' => 'X'], 'sin creacion' => ['creacion_id' => 0]] as $label => $override) {
    $code422 = null;
    try {
        $pedidoService->requestPublicOrder(array_merge([
            'creacion_id' => $pieceId,
            'cliente_nombre' => 'Clienta Prueba',
            'cliente_contacto' => '5548921039',
            'cantidad' => 1,
        ], $override));
    } catch (InvalidArgumentException $e) {
        $code422 = 422;
    }
    TestHelper::assertSame(422, $code422, "Validación pública rechaza: {$label} (422)");
}

// 3.7 IDOR: Ana crea sobre pieza del admin → 403; admin sí puede
$adminPiece = $creacionService->createCreation($mkCreation('ZZ44 Admin', 5), null, $admin);
$adminPieceId = (int)$adminPiece['id'];
$idor403 = null;
try {
    $pedidoService->createManualOrder([
        'creacion_id' => $adminPieceId,
        'cliente_nombre' => 'Clienta Prueba',
        'cliente_contacto' => '5548921039',
        'cantidad' => 1,
    ], $ana);
} catch (RuntimeException $e) {
    $idor403 = (int)$e->getCode();
}
TestHelper::assertSame(403, $idor403, 'IDOR: artesana no registra pedidos sobre pieza ajena (403, R-04)');
$manual = $pedidoService->createManualOrder([
    'creacion_id' => $adminPieceId,
    'cliente_nombre' => 'Clienta Manual',
    'cliente_contacto' => '5587654321',
    'cantidad' => 1,
    'fecha_entrega' => '2026-10-15',
    'estado_pago' => 'Anticipo 50%',
], $admin);
$manualId = (int)$manual['id'];
TestHelper::assertTrue($manualId > 0, 'El admin registra pedido manual con fecha y anticipo (201)');

// 3.8 Cambio de estado + cancelación idempotente con restitución exacta
$changed = $pedidoService->updateOrderStatus($manualId, 'En Proceso', null, $admin);
TestHelper::assertSame('En Proceso', (string)$changed['estado_pedido'], 'Cambiar estado a En Proceso (200)');
$stockPreCancel = (int)$creacionService->getCreationById($adminPieceId, true)['cantidad_stock'];
$cancelled = $pedidoService->cancelOrder($manualId, $admin);
TestHelper::assertSame('Cancelado', (string)$cancelled['estado_pedido'], 'Cancelar responde estado Cancelado (200)');
TestHelper::assertSame(1, (int)$cancelled['unidades_restituidas'], 'La cancelación restituye 1 unidad');
$stockPostCancel = (int)$creacionService->getCreationById($adminPieceId, true)['cantidad_stock'];
TestHelper::assertSame($stockPreCancel + 1, $stockPostCancel, 'El stock se restituye exactamente (R-07)');
$cancel409 = null;
try {
    $pedidoService->cancelOrder($manualId, $admin);
} catch (RuntimeException $e) {
    $cancel409 = (int)$e->getCode();
}
TestHelper::assertSame(409, $cancel409, 'La segunda cancelación responde 409 (idempotente)');

// 3.9 Resumen con scoping (Ana vs admin) verificado contra SQL independiente
$db = new PDO('sqlite:' . $root . '/database/database.sqlite');
$db->exec('PRAGMA foreign_keys = ON;');
$expAna = $db->query('SELECT COUNT(*) FROM pedidos p INNER JOIN creaciones c ON c.id = p.creacion_id WHERE p.activo = 1 AND c.artesano_id = 2')->fetchColumn();
$sumAna = $pedidoService->getOrdersSummary([], $ana);
TestHelper::assertSame((int)$expAna, (int)$sumAna['total'], 'Resumen de Ana: total exacto vs SQL (scoping forzado)');
$expAdmin = $db->query('SELECT COUNT(*) FROM pedidos WHERE activo = 1')->fetchColumn();
$sumAdmin = $pedidoService->getOrdersSummary([], $admin);
TestHelper::assertSame((int)$expAdmin, (int)$sumAdmin['total'], 'Resumen admin: total global exacto');
TestHelper::assertTrue(isset($sumAdmin['pendientes'], $sumAdmin['proceso'], $sumAdmin['ingresos_centavos']), 'El resumen incluye pendientes/proceso/ingresos_centavos');

// 3.10 Limpieza: cancelar el pedido público (restituye) y dar de baja piezas (lógica)
try {
    $pedidoService->cancelOrder($orderId, $admin);
} catch (Throwable $e) {
}
try {
    $pedidoService->cancelOrder((int)$customOrder['id'], $admin);
} catch (Throwable $e) {
}
foreach ([$pieceId, $emptyId, $customId, $adminPieceId] as $cid) {
    try {
        $creacionService->deleteCreation($cid, $admin);
    } catch (Throwable $e) {
    }
}
TestHelper::assertTrue(true, 'Limpieza 4.4 completada (cancelaciones + bajas lógicas)');

// =============================================================================
// 4. PANEL: sin mocks, template, resumen y WhatsApp servidor
// =============================================================================
TestHelper::section('4. Panel: sin mocks, template reactivo y resumen exacto');

$panelView = (string)@file_get_contents("$root/views/pages/pedidos_content.php");
TestHelper::assertFalse(str_contains($panelView, '$mockOrders'), 'pedidos_content.php ya no define $mockOrders');
TestHelper::assertFalse(str_contains($panelView, 'Mariana Gómez'), 'El panel ya no hardcodea la clienta mock');
TestHelper::assertFalse(str_contains($panelView, 'wa.me/'), 'El panel ya no compone wa.me crudos (enlaces del servidor)');
TestHelper::assertFalse(str_contains($panelView, 'cliente_wa'), 'Desaparece cliente_wa mock (el servidor construye el enlace)');
TestHelper::assertStringContains('id="ordersGrid"', $panelView, 'La rejilla #ordersGrid permanece');
TestHelper::assertStringContains('data-total="0"', $panelView, '#ordersGrid expone data-total actualizable');
TestHelper::assertStringContains('ordersLoadingState', $panelView, 'La vista incluye #ordersLoadingState');
TestHelper::assertStringContains('<template id="pedidoCardTemplate">', $panelView, 'La vista declara <template id="pedidoCardTemplate">');
foreach (['cardCol', 'codigo', 'fecha', 'photo', 'nombre', 'qty', 'clienteNombre', 'waLink', 'clienteContacto', 'precioTotal', 'pagoBadge', 'notasBox', 'notas', 'estadoBadge', 'inspectBtn'] as $part) {
    TestHelper::assertStringContains('data-part="' . $part . '"', $panelView, "El template expone [data-part={$part}]");
}
TestHelper::assertStringContains('id="pedidosPaginationNav"', $panelView, 'La paginación expone #pedidosPaginationNav');
TestHelper::assertStringContains('id="pedidosShowingFrom"', $panelView, 'El rango expone #pedidosShowingFrom');
TestHelper::assertStringContains('id="pedidosShowingTo"', $panelView, 'El rango expone #pedidosShowingTo');
TestHelper::assertStringContains('id="pedidosTotalCount"', $panelView, 'El rango expone #pedidosTotalCount');
TestHelper::assertStringContains('id="emptyOrdersGrid"', $panelView, 'Se conserva #emptyOrdersGrid');
TestHelper::assertStringContains('id="kpiOrdersTotal"', $panelView, 'Se conserva #kpiOrdersTotal (inicial 0)');
TestHelper::assertStringContains('id="kpiOrdersIngresos"', $panelView, 'Se conserva #kpiOrdersIngresos (inicial $0.00)');

$nuevoModal = (string)@file_get_contents("$root/views/components/modal_nuevo_pedido.php");
TestHelper::assertFalse(str_contains($nuevoModal, 'Dragón Ignis'), 'El alta manual ya no lista piezas mock');
TestHelper::assertFalse(str_contains($nuevoModal, 'value="custom"'), 'Desaparece la opción "custom" sin contrato');
TestHelper::assertStringContains('id="manualCreacionSelect"', $nuevoModal, 'El alta manual expone #manualCreacionSelect (piezas reales)');
TestHelper::assertStringContains('id="manualEstadoPedido"', $nuevoModal, 'El alta manual expone #manualEstadoPedido');

$ordersJs = (string)@file_get_contents("$root/src/js/modules/orders.js");
TestHelper::assertStringContains("'/api/pedidos/index.php'", $ordersJs, 'orders.js lista desde GET index.php con Bearer');
TestHelper::assertStringContains("'/api/pedidos/crear.php'", $ordersJs, 'orders.js crea vía POST crear.php');
TestHelper::assertStringContains("'/api/pedidos/cambiar-estado.php'", $ordersJs, 'orders.js cambia estado vía POST cambiar-estado.php');
TestHelper::assertStringContains("'/api/pedidos/cancelar.php'", $ordersJs, 'orders.js cancela vía POST cancelar.php');
TestHelper::assertStringContains('resumen', $ordersJs, 'orders.js pide KPIs al agregado resumen=1');
TestHelper::assertStringContains('enlace_whatsapp', $ordersJs, 'orders.js renderiza el WhatsApp del servidor (cero wa.me compuestos)');
TestHelper::assertFalse(str_contains($ordersJs, 'https://wa.me/'), 'orders.js no compone ningún wa.me (H-004/DOM + servidor)');
TestHelper::assertFalse(str_contains($ordersJs, 'nextOrderId'), 'Desaparece nextOrderId mock (IDs reales del servidor)');
TestHelper::assertFalse(str_contains($ordersJs, 'innerHTML ='), 'orders.js no asigna innerHTML con datos (H-004)');
TestHelper::assertStringContains('.cloneNode(true)', $ordersJs, 'orders.js clona el template por tarjeta');
TestHelper::assertStringContains('status === 409', $ordersJs, 'orders.js gestiona el 409 idempotente');
TestHelper::assertStringContains('status === 403', $ordersJs, 'orders.js muestra el 403 IDOR');

// =============================================================================
// 5. HTTP VIVO: flujo público completo + panel Bearer + páginas
// =============================================================================
TestHelper::section('5. HTTP Vivo: compra pública, panel RBAC y páginas CSP');

$loginAdmin = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'admin', 'password' => 'admin123']));
TestHelper::assertSame(200, $loginAdmin['status'], 'HTTP login admin devuelve 200 OK');
$tokenAdmin = (string)($loginAdmin['json']['datos']['token'] ?? '');

$loginAna = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'artesana_ana', 'password' => 'artesana123']));
TestHelper::assertSame(200, $loginAna['status'], 'HTTP login artesana_ana devuelve 200 OK');
$tokenAna = (string)($loginAna['json']['datos']['token'] ?? '');

// 5.1 Pieza efímera HTTP con stock 3 (atribuida a Ana)
$httpPiece = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode([
    'nombre' => 'ZZ44 HTTP ' . $uid,
    'categoria' => 'Hogar & Decoración',
    'material' => 'Trapillo HTTP',
    'dimensiones' => '30 x 25 cm',
    'precio_centavos' => 20000,
    'costo_materiales_centavos' => 5000,
    'cantidad_stock' => 3,
    'artesano_id' => 2,
]));
TestHelper::assertSame(201, $httpPiece['status'], 'HTTP crear pieza efímera devuelve 201');
$httpPieceId = (int)($httpPiece['json']['datos']['id'] ?? 0);

// 5.2 Compra pública: 201 + precio congelado + WhatsApp + stock−2
$httpBuy = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], json_encode([
    'creacion_id' => $httpPieceId,
    'cliente_nombre' => 'Compradora HTTP',
    'cliente_contacto' => '5511223344',
    'cantidad' => 2,
]));
TestHelper::assertSame(201, $httpBuy['status'], 'HTTP solicitar público devuelve 201 Created');
TestHelper::assertSame(40000, (int)($httpBuy['json']['datos']['precio_final'] ?? 0), 'HTTP: precio congelado 40000¢ por servidor');
TestHelper::assertStringContains('wa.me/525512345678', (string)($httpBuy['json']['datos']['enlace_whatsapp'] ?? ''), 'HTTP: WhatsApp del artesano (E.164 con prefijo 52)');
$httpOrderId = (int)($httpBuy['json']['datos']['id'] ?? 0);
$httpDetail = TestHelper::curl('GET', "http://localhost:8000/api/creaciones/detalle.php?id={$httpPieceId}");
TestHelper::assertSame(1, (int)($httpDetail['json']['datos']['cantidad_stock'] ?? -1), 'HTTP: el stock bajó de 3 a 1 (atómico)');

// 5.3 Validación y 404/409 públicos
$httpBad = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], json_encode(['creacion_id' => $httpPieceId, 'cliente_nombre' => 'X', 'cliente_contacto' => '5511223344', 'cantidad' => 1]));
TestHelper::assertSame(422, $httpBad['status'], 'HTTP solicitar con nombre corto devuelve 422');
$http404 = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], json_encode(['creacion_id' => 999999, 'cliente_nombre' => 'Compradora HTTP', 'cliente_contacto' => '5511223344', 'cantidad' => 1]));
TestHelper::assertSame(404, $http404['status'], 'HTTP solicitar pieza inexistente devuelve 404');
$http409 = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], json_encode(['creacion_id' => $httpPieceId, 'cliente_nombre' => 'Compradora HTTP', 'cliente_contacto' => '5511223344', 'cantidad' => 5]));
TestHelper::assertSame(409, $http409['status'], 'HTTP solicitar sin stock suficiente devuelve 409');

// 5.4 Panel: Ana ve su pedido; admin ve global; resumen coincide
$httpAnaOrders = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php?limite=50', [
    'Authorization: Bearer ' . $tokenAna,
]);
TestHelper::assertSame(200, $httpAnaOrders['status'], 'HTTP index pedidos como Ana devuelve 200 OK');
$anaOwn = true;
foreach (($httpAnaOrders['json']['datos'] ?? []) as $o) {
    if ((int)($o['creacion']['artesano_id'] ?? 0) !== 2) $anaOwn = false;
}
TestHelper::assertTrue($anaOwn, 'HTTP: Ana solo ve pedidos de sus piezas (scoping)');
$httpAdminOrders = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php?limite=50', [
    'Authorization: Bearer ' . $tokenAdmin,
]);
TestHelper::assertTrue(count($httpAdminOrders['json']['datos'] ?? []) >= count($httpAnaOrders['json']['datos'] ?? []), 'HTTP: el admin ve al menos tanto como Ana');
$httpNoAuth = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php');
TestHelper::assertSame(401, $httpNoAuth['status'], 'HTTP index pedidos sin Bearer devuelve 401');

// 5.5 Manual + estados + cancelar/restituir/409 por HTTP (admin)
$httpManual = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/crear.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['creacion_id' => $httpPieceId, 'cliente_nombre' => 'Manual HTTP', 'cliente_contacto' => '5599887766', 'cantidad' => 1]));
TestHelper::assertSame(201, $httpManual['status'], 'HTTP crear manual devuelve 201');
$httpManualId = (int)($httpManual['json']['datos']['id'] ?? 0);
$httpIdor = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/crear.php', [
    'Authorization: Bearer ' . $tokenAna,
    'Content-Type: application/json',
], json_encode(['creacion_id' => 1, 'cliente_nombre' => 'Manual HTTP', 'cliente_contacto' => '5599887766', 'cantidad' => 1]));
TestHelper::assertSame(403, $httpIdor['status'], 'HTTP crear manual sobre pieza ajena devuelve 403');
$httpEstado = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cambiar-estado.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpManualId, 'estado_pedido' => 'En Proceso']));
TestHelper::assertSame(200, $httpEstado['status'], 'HTTP cambiar estado devuelve 200 OK');
$httpCancel = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpManualId]));
TestHelper::assertSame(200, $httpCancel['status'], 'HTTP cancelar devuelve 200 OK');
TestHelper::assertSame(1, (int)($httpCancel['json']['datos']['unidades_restituidas'] ?? 0), 'HTTP cancelar restituye 1 unidad');
$httpCancel2 = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpManualId]));
TestHelper::assertSame(409, $httpCancel2['status'], 'HTTP segunda cancelación devuelve 409');

// 5.6 Resumen HTTP == servicio + páginas con CSP
$httpResumen = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php?resumen=1', [
    'Authorization: Bearer ' . $tokenAdmin,
]);
TestHelper::assertSame((int)$pedidoService->getOrdersSummary([], $admin)['total'], (int)($httpResumen['json']['datos']['total'] ?? -1), 'HTTP resumen == servicio (sin divergencia)');
$httpPedidos = TestHelper::curl('GET', 'http://localhost:8000/pedidos.php');
TestHelper::assertSame(200, $httpPedidos['status'], 'HTTP GET /pedidos.php devuelve 200 OK');
TestHelper::assertStringContains('id="ordersGrid"', $httpPedidos['body'], 'El HTML servido incluye #ordersGrid');
TestHelper::assertStringContains('id="pedidoCardTemplate"', $httpPedidos['body'], 'El HTML incluye pedidoCardTemplate');
TestHelper::assertFalse(str_contains($httpPedidos['body'], '$mockOrders'), 'El HTML no contiene restos del mock');
$cspPedidos = strtolower($httpPedidos['headers']['content-security-policy'] ?? '');
TestHelper::assertStringContains("script-src 'self'", $cspPedidos, 'CSP del panel (H-004)');
$httpIndex = TestHelper::curl('GET', 'http://localhost:8000/index.php');
TestHelper::assertStringContains('id="checkoutModal"', $httpIndex['body'], 'El catálogo incluye #checkoutModal');
TestHelper::assertFalse(str_contains($httpIndex['body'], "alert('¡Pedido solicitado"), 'El catálogo ya no sirve el alert() mock');

// 5.7 Limpieza HTTP: cancelar la compra pública (restituye 2) y baja de la pieza
$httpCleanOrder = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpOrderId]));
TestHelper::assertTrue(in_array($httpCleanOrder['status'], [200, 409], true), 'Limpieza: compra pública cancelada (restituye)');
try {
    $creacionService->deleteCreation($httpPieceId, $admin);
} catch (Throwable $e) {
}
TestHelper::assertTrue(true, 'Limpieza: pieza efímera HTTP en baja lógica');

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
