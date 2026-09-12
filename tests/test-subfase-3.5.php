<?php
/**
 * Test Suite: Subfase 3.5 - Pedidos, Transacciones Atómicas & Notificaciones WhatsApp
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Valida de forma exhaustiva:
 * 1. PedidoRepository:
 *    - Consultas hidratadas con JOIN a creaciones y usuarios.
 *    - Filtros multicriterio (estado_pedido, estado_pago, creacion_id, busqueda, activo).
 *    - Aislamiento multi-artesano por autoría de la creación.
 *    - Conteo normalizado para paginación.
 *    - Transacciones atómicas: BEGIN IMMEDIATE TRANSACTION para verificar y descontar stock.
 *    - Congelamiento estricto de precio en servidor (creacion.precio * cantidad).
 *    - Actualización de estado y fecha de modificación.
 *    - Cancelación atómica idempotente con restitución de inventario físico.
 *    - Borrado lógico universal (activo = 0) y reactivación.
 * 2. PedidoService:
 *    - Validaciones estrictas de dominio para pedidos y encargos.
 *    - Enriquecimiento monetario dual (centavos <-> MXN formateado).
 *    - Generador de enlaces directos a WhatsApp (wa.me) con normalización telefónica y mensaje pre-redactado.
 *    - Salvaguardas de autorización IDOR (artesano restringido a sus propias piezas, admin omnímodo).
 *    - Delegación automática de cancelación al cambiar estado a 'Cancelado'.
 * 3. Endpoints REST en vivo contra localhost:8000:
 *    - POST /api/pedidos/solicitar.php (Público, 201 Created, 409 stock insuficiente, 422 inválido, 405 en GET).
 *    - GET /api/pedidos/index.php (Protegido, aislamiento artesano vs admin, filtros, paginación, 401 sin token, 405 en POST).
 *    - POST /api/pedidos/crear.php (Protegido, 201 Created, 403 IDOR artesano ajeno, 403 asistente, 422 inválido).
 *    - POST /api/pedidos/cambiar-estado.php (Protegido, 200 OK, 403 IDOR, 422 inválido).
 *    - POST /api/pedidos/cancelar.php (Protegido, 200 OK restitución de stock, 409 idempotencia en 2do intento, 403 IDOR).
 *    - Preflight CORS OPTIONS (204 No Content).
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
use App\Repositories\PedidoRepository;
use App\Repositories\UsuarioRepository;
use App\Services\PedidoService;
use App\Utils\CurrencyHelper;

// Iniciar suite
TestHelper::init('Subfase 3.5: Pedidos, Transacciones Atómicas & Notificaciones WhatsApp');

// Desactivar terminación forzada en Response para pruebas en memoria
Response::setExitOnSend(false);

$pedidoRepo = new PedidoRepository();
$creacionRepo = new CreacionRepository();
$usuarioRepo = new UsuarioRepository();
$pedidoService = new PedidoService($pedidoRepo, $creacionRepo);

// Asegurar stock base adecuado para pruebas concurrentes e idempotentes
$creacionRepo->adjustStock(1, 10);
$creacionRepo->adjustStock(2, 5);
$creacionRepo->adjustStock(5, 10);

// Buffer para registrar trazas HTTP
$httpLogBuffer = "=== LOG DE TRAZAS HTTP CURL - SUBFASE 3.5: PEDIDOS & TRANSACCIONES ATÓMICAS ===\n";
$httpLogBuffer .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
$httpLogBuffer .= "Servidor: http://localhost:8000\n";
$httpLogBuffer .= str_repeat("=", 80) . "\n\n";

$logTrace = function(string $title, array $res) use (&$httpLogBuffer): void {
    $httpLogBuffer .= "--------------------------------------------------------------------------------\n";
    $httpLogBuffer .= "[{$title}]\n";
    $httpLogBuffer .= "HTTP Status: {$res['status']}\n";
    $httpLogBuffer .= "Headers:\n" . trim($res['raw_headers'] ?? '') . "\n";
    $httpLogBuffer .= "Response Body:\n" . ($res['body'] ?? '') . "\n\n";
};

// =============================================================================
// 1. PEDIDOREPOSITORY: PERSISTENCIA, CONSULTAS Y TRANSACCIONES ATÓMICAS
// =============================================================================
TestHelper::section('1. PedidoRepository: Persistencia, Filtros y Transacciones Atómicas');

// 1.1 findById con registro existente (Pedido #1)
$order1 = $pedidoRepo->findById(1, true);
TestHelper::assertNotNull($order1, 'findById(1) encuentra el pedido semilla #1');
TestHelper::assertSame(1, (int)$order1['id'], 'El ID del pedido es 1');
TestHelper::assertSame('Mariana Gómez', $order1['cliente_nombre'], 'cliente_nombre es Mariana Gómez');
TestHelper::assertSame('+52 55 4892 1039', $order1['cliente_contacto'], 'cliente_contacto es correcto');
TestHelper::assertSame(1, (int)$order1['creacion_id'], 'creacion_id es 1');
TestHelper::assertSame(1, (int)$order1['cantidad'], 'cantidad es 1');
TestHelper::assertSame('En Proceso', $order1['estado_pedido'], 'estado_pedido es En Proceso');
TestHelper::assertSame('Anticipo 50%', $order1['estado_pago'], 'estado_pago es Anticipo 50%');
TestHelper::assertSame(45000, (int)$order1['precio_final'], 'precio_final es 45000 centavos');
TestHelper::assertSame(1, (int)$order1['activo'], 'activo es 1');

// 1.2 Hidratación de creación anidada
TestHelper::assertTrue(isset($order1['creacion']), 'El pedido incluye objeto anidado de creación');
TestHelper::assertSame('Dragón Ignis', $order1['creacion']['nombre'], 'Nombre de creación es Dragón Ignis');
TestHelper::assertSame(45000, (int)$order1['creacion']['precio'], 'Precio unitario de creación es 45000');
TestHelper::assertSame(1, (int)$order1['creacion']['artesano_id'], 'artesano_id de la creación es 1');
TestHelper::assertSame('admin', $order1['creacion']['artesano_username'], 'username del artesano es admin');

// 1.3 findById con registro inexistente
$orderNone = $pedidoRepo->findById(99999, true);
TestHelper::assertNull($orderNone, 'findById(99999) devuelve null');

// 1.4 listAll sin filtros
$allOrders = $pedidoRepo->listAll([], 20, 0);
TestHelper::assertTrue(is_array($allOrders), 'listAll() retorna un array');
TestHelper::assertTrue(count($allOrders) >= 2, 'Recupera al menos los 2 pedidos semilla');

// 1.5 listAll con filtro estado_pedido
$enProcesoOrders = $pedidoRepo->listAll(['estado_pedido' => 'En Proceso'], 20, 0);
TestHelper::assertTrue(count($enProcesoOrders) >= 1, 'Filtro estado_pedido = En Proceso retorna al menos 1 pedido');
foreach ($enProcesoOrders as $o) {
    TestHelper::assertSame('En Proceso', $o['estado_pedido'], 'Cada pedido filtrado tiene estado En Proceso');
}

// 1.6 listAll con filtro estado_pago
$pendientesPago = $pedidoRepo->listAll(['estado_pago' => 'Pendiente'], 20, 0);
TestHelper::assertTrue(count($pendientesPago) >= 1, 'Filtro estado_pago = Pendiente retorna al menos 1 pedido');
foreach ($pendientesPago as $o) {
    TestHelper::assertSame('Pendiente', $o['estado_pago'], 'Cada pedido filtrado tiene estado_pago Pendiente');
}

// 1.7 listAll con filtro creacion_id
$creacion1Orders = $pedidoRepo->listAll(['creacion_id' => 1], 20, 0);
TestHelper::assertTrue(count($creacion1Orders) >= 1, 'Filtro creacion_id = 1 retorna pedidos vinculados');
foreach ($creacion1Orders as $o) {
    TestHelper::assertSame(1, (int)$o['creacion_id'], 'creacion_id es 1');
}

// 1.8 listAll con filtro busqueda (por cliente y por pieza)
$searchCliente = $pedidoRepo->listAll(['busqueda' => 'Mariana'], 20, 0);
TestHelper::assertTrue(count($searchCliente) >= 1, 'Búsqueda por nombre de cliente "Mariana" encuentra el pedido');

$searchPieza = $pedidoRepo->listAll(['busqueda' => 'Ajolote'], 20, 0);
TestHelper::assertTrue(count($searchPieza) >= 1, 'Búsqueda por nombre de pieza "Ajolote" encuentra el pedido #2');

// 1.9 Ordenamientos dinámicos
$ordersDesc = $pedidoRepo->listAll(['orden' => 'precio_desc'], 20, 0);
TestHelper::assertTrue($ordersDesc[0]['precio_final'] >= $ordersDesc[count($ordersDesc) - 1]['precio_final'], 'Orden precio_desc ordena de mayor a menor precio');

$ordersAsc = $pedidoRepo->listAll(['orden' => 'precio_asc'], 20, 0);
TestHelper::assertTrue($ordersAsc[0]['precio_final'] <= $ordersAsc[count($ordersAsc) - 1]['precio_final'], 'Orden precio_asc ordena de menor a mayor precio');

// 1.10 Aislamiento Multi-Artesano en Repositorio
// artesanoId = 2 (Ana) solo debe ver pedidos vinculados a sus creaciones
$anaOrders = $pedidoRepo->listAll([], 20, 0, 2);
TestHelper::assertTrue(count($anaOrders) >= 1, 'Artesana Ana (ID 2) tiene pedidos en sus creaciones');
foreach ($anaOrders as $ao) {
    TestHelper::assertSame(2, (int)$ao['creacion']['artesano_id'], 'Pedido en vista de Ana pertenece a creacion de artesano 2');
}

// artesanoId = 1 (Admin) solo ve sus creaciones
$adminOrders = $pedidoRepo->listAll([], 20, 0, 1);
TestHelper::assertTrue(count($adminOrders) >= 1, 'Admin (ID 1) tiene pedidos en sus creaciones');
foreach ($adminOrders as $ado) {
    TestHelper::assertSame(1, (int)$ado['creacion']['artesano_id'], 'Pedido en vista de Admin pertenece a creacion de artesano 1');
}

// 1.11 countAll coincide con conteo de listAll
$totalCount = $pedidoRepo->countAll([]);
TestHelper::assertTrue($totalCount >= 2, 'countAll([]) devuelve al menos 2 registros');
$anaCount = $pedidoRepo->countAll([], 2);
TestHelper::assertSame(count($anaOrders), $anaCount, 'countAll([], 2) coincide exactamente con count(listAll([], 20, 0, 2))');

// 1.12 createAtomic: Creación con descuento atómico de stock físico
$piece2 = $creacionRepo->findById(2); // Zorro Ártico
$initialStock2 = (int)$piece2['cantidad_stock'];
$unitPrice2 = (int)$piece2['precio'];

$orderData = [
    'cliente_nombre'   => 'Cliente Prueba Stock',
    'cliente_contacto' => '5511223344',
    'creacion_id'      => 2,
    'cantidad'         => 2,
    'fecha_entrega'    => date('Y-m-d', strtotime('+7 days')),
    'estado_pedido'    => 'Pendiente',
    'estado_pago'      => 'Pendiente',
    'notas'            => 'Pedido de prueba unitaria de stock',
];

$createdOrderId = $pedidoRepo->createAtomic($orderData, false);
TestHelper::assertTrue($createdOrderId > 0, 'createAtomic() retorna ID positivo');

$createdOrder = $pedidoRepo->findById($createdOrderId);
TestHelper::assertSame($unitPrice2 * 2, (int)$createdOrder['precio_final'], 'precio_final fue calculado en servidor como precio_unitario * cantidad');

$piece2After = $creacionRepo->findById(2);
TestHelper::assertSame($initialStock2 - 2, (int)$piece2After['cantidad_stock'], 'cantidad_stock se redujo atómicamente en 2 unidades');

// 1.13 createAtomic: Intento de comprar más del stock disponible (Falla atómica HTTP 409)
$stockRemaining = (int)$piece2After['cantidad_stock'];
$excessOrderData = [
    'cliente_nombre'   => 'Cliente Exceso',
    'cliente_contacto' => '5599887766',
    'creacion_id'      => 2,
    'cantidad'         => $stockRemaining + 10,
    'fecha_entrega'    => null,
    'estado_pedido'    => 'Pendiente',
    'estado_pago'      => 'Pendiente',
    'notas'            => null,
];

$conflictThrown = false;
try {
    $pedidoRepo->createAtomic($excessOrderData, false);
} catch (RuntimeException $e) {
    $conflictThrown = ($e->getCode() === 409);
}
TestHelper::assertTrue($conflictThrown, 'createAtomic() lanza RuntimeException 409 ante stock insuficiente');

// Verificar que el stock NO fue modificado tras el fallo
$piece2AfterFail = $creacionRepo->findById(2);
TestHelper::assertSame($stockRemaining, (int)$piece2AfterFail['cantidad_stock'], 'Stock permanece inalterado tras fallo atómico');

// 1.14 createAtomic: Pieza por encargo (isCustomOrder = true) no descuenta stock físico
$piece3 = $creacionRepo->findById(3); // Ajolote Rosado (es_sobre_encargo = 1, stock = 0)
$customOrderData = [
    'cliente_nombre'   => 'Cliente Encargo',
    'cliente_contacto' => '5544332211',
    'creacion_id'      => 3,
    'cantidad'         => 3,
    'fecha_entrega'    => null,
    'estado_pedido'    => 'Pendiente',
    'estado_pago'      => 'Pendiente',
    'notas'            => 'Encargo personalizado',
];

$customOrderId = $pedidoRepo->createAtomic($customOrderData, true);
TestHelper::assertTrue($customOrderId > 0, 'createAtomic() en pieza por encargo genera pedido exitosamente con stock 0');
$piece3After = $creacionRepo->findById(3);
TestHelper::assertSame(0, (int)$piece3After['cantidad_stock'], 'Stock de pieza por encargo sigue siendo 0 (no se decrementa)');

// 1.15 updateStatus: Actualización de estado y cobro
$pedidoRepo->updateStatus($createdOrderId, 'En Proceso', 'Anticipo 50%');
$orderUpdated = $pedidoRepo->findById($createdOrderId);
TestHelper::assertSame('En Proceso', $orderUpdated['estado_pedido'], 'updateStatus() actualiza estado_pedido a En Proceso');
TestHelper::assertSame('Anticipo 50%', $orderUpdated['estado_pago'], 'updateStatus() actualiza estado_pago a Anticipo 50%');
TestHelper::assertNotNull($orderUpdated['actualizado_en'], 'actualizado_en fue establecido');

// 1.16 cancelOrderAtomic: Cancelación atómica y restitución de inventario físico
$cancelResult = $pedidoRepo->cancelOrderAtomic($createdOrderId);
TestHelper::assertSame(2, (int)$cancelResult['unidades_restituidas'], 'cancelOrderAtomic() restituye exactamente 2 unidades');
TestHelper::assertSame($initialStock2, (int)$cancelResult['nuevo_stock'], 'nuevo_stock regresa al stock inicial original');

$orderCancelled = $pedidoRepo->findById($createdOrderId);
TestHelper::assertSame('Cancelado', $orderCancelled['estado_pedido'], 'estado_pedido se actualizó a Cancelado');

$piece2Restored = $creacionRepo->findById(2);
TestHelper::assertSame($initialStock2, (int)$piece2Restored['cantidad_stock'], 'Stock de la creación en BD fue restituido al valor original');

// 1.17 Idempotencia de la cancelación: segundo intento debe fallar con 409
$idempotencyBlocked = false;
try {
    $pedidoRepo->cancelOrderAtomic($createdOrderId);
} catch (RuntimeException $e) {
    $idempotencyBlocked = ($e->getCode() === 409);
}
TestHelper::assertTrue($idempotencyBlocked, 'cancelOrderAtomic() en pedido ya cancelado lanza 409 Conflict (Idempotencia)');

// Verificar que el stock no se incrementó dos veces
$piece2DoubleCheck = $creacionRepo->findById(2);
TestHelper::assertSame($initialStock2, (int)$piece2DoubleCheck['cantidad_stock'], 'Stock no sufre doble restitución');

// 1.18 softDelete y restore
$pedidoRepo->softDelete($customOrderId);
$deletedOrderActive = $pedidoRepo->findById($customOrderId, true);
TestHelper::assertNull($deletedOrderActive, 'softDelete() oculta el pedido en consultas activas');

$deletedOrderAll = $pedidoRepo->findById($customOrderId, false);
TestHelper::assertNotNull($deletedOrderAll, 'softDelete() preserva el pedido en BD con activo = 0');
TestHelper::assertSame(0, (int)$deletedOrderAll['activo'], 'activo es 0');
TestHelper::assertNotNull($deletedOrderAll['eliminado_en'], 'eliminado_en contiene timestamp');

$pedidoRepo->restore($customOrderId);
$restoredOrder = $pedidoRepo->findById($customOrderId, true);
TestHelper::assertNotNull($restoredOrder, 'restore() reactiva el pedido');
TestHelper::assertSame(1, (int)$restoredOrder['activo'], 'activo vuelve a ser 1');
TestHelper::assertNull($restoredOrder['eliminado_en'], 'eliminado_en se restablece a NULL');

// Limpieza de pedidos de prueba de la sección 1
$pedidoRepo->softDelete($createdOrderId);
$pedidoRepo->softDelete($customOrderId);

// =============================================================================
// 2. PEDIDOSERVICE: REGLAS DE NEGOCIO, IDOR, MONEDA Y WHATSAPP
// =============================================================================
TestHelper::section('2. PedidoService: Reglas de Negocio, IDOR, Moneda y WhatsApp');

// 2.1 Normalización telefónica y enlace directo a WhatsApp (buildWhatsAppLink)
$waLink10 = $pedidoService->buildWhatsAppLink('5512345678', 'Valeria', 101, 'Oso Teddy');
TestHelper::assertNotNull($waLink10, 'buildWhatsAppLink con 10 dígitos genera enlace');
TestHelper::assertStringContains('wa.me/525512345678', $waLink10, 'Antepone código de país 52 para número mexicano');
TestHelper::assertStringContains('Valeria', $waLink10, 'El mensaje contiene el nombre del cliente');
TestHelper::assertStringContains('101', $waLink10, 'El mensaje contiene el ID del pedido');
TestHelper::assertStringContains('Oso%20Teddy', $waLink10, 'El mensaje contiene la pieza codificada con rawurlencode');

$waLinkIntl = $pedidoService->buildWhatsAppLink('+52 55 4892 1039', 'Mariana', 1, 'Dragón Ignis');
TestHelper::assertStringContains('wa.me/525548921039', $waLinkIntl, 'Limpia caracteres especiales y espacios (+52 55 ... -> 525548921039)');

$waLinkInvalid = $pedidoService->buildWhatsAppLink('1234', 'Juan', 5, 'Pieza');
TestHelper::assertNull($waLinkInvalid, 'buildWhatsAppLink con teléfono inválido (< 8 dígitos) devuelve null');

// 2.2 Validaciones de entrada en requestPublicOrder
$invalidNameThrown = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'A', // Demasiado corto (< 2 chars)
        'cliente_contacto' => '5511223344',
        'cantidad'         => 1,
    ]);
} catch (InvalidArgumentException $e) {
    $invalidNameThrown = ($e->getCode() === 422);
}
TestHelper::assertTrue($invalidNameThrown, 'requestPublicOrder() valida longitud mínima de cliente_nombre (422)');

$invalidContactThrown = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Cliente Válido',
        'cliente_contacto' => 'X', // Demasiado corto (< 3 chars)
        'cantidad'         => 1,
    ]);
} catch (InvalidArgumentException $e) {
    $invalidContactThrown = ($e->getCode() === 422);
}
TestHelper::assertTrue($invalidContactThrown, 'requestPublicOrder() valida longitud de cliente_contacto (422)');

$invalidQtyThrown = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Cliente Válido',
        'cliente_contacto' => '5511223344',
        'cantidad'         => 0, // Invalido (< 1)
    ]);
} catch (InvalidArgumentException $e) {
    $invalidQtyThrown = ($e->getCode() === 422);
}
TestHelper::assertTrue($invalidQtyThrown, 'requestPublicOrder() valida cantidad >= 1 (422)');

$invalidCreacionNotFound = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 99999,
        'cliente_nombre'   => 'Cliente Válido',
        'cliente_contacto' => '5511223344',
        'cantidad'         => 1,
    ]);
} catch (RuntimeException $e) {
    $invalidCreacionNotFound = ($e->getCode() === 404);
}
TestHelper::assertTrue($invalidCreacionNotFound, 'requestPublicOrder() devuelve 404 ante creacion_id inexistente');

// 2.3 Solicitud pública exitosa con cálculo de precio en servidor
$publicReqResult = $pedidoService->requestPublicOrder([
    'creacion_id'      => 1, // Dragón Ignis ($450.00 MXN)
    'cliente_nombre'   => 'Rodrigo Peña',
    'cliente_contacto' => '5598765432',
    'cantidad'         => 1,
    'notas'            => 'Empaque biodegradable',
]);
TestHelper::assertTrue(isset($publicReqResult['id']), 'requestPublicOrder() retorna ID de pedido');
TestHelper::assertSame(45000, (int)$publicReqResult['precio_final'], 'precio_final calculado en servidor es 45000');
TestHelper::assertSame('$450.00 MXN', $publicReqResult['precio_final_formateado'], 'precio_final_formateado es $450.00 MXN');
TestHelper::assertSame('Pendiente', $publicReqResult['estado_pedido'], 'estado inicial es Pendiente');

// 2.4 createManualOrder: Autorización y salvaguarda IDOR
$anaUser = ['id' => 2, 'username' => 'artesana_ana', 'rol' => 'artesano'];
$adminUser = ['id' => 1, 'username' => 'admin', 'rol' => 'admin'];

// Artesana Ana intenta registrar encargo en pieza del Admin (ID 1: Dragón Ignis) -> Debe fallar con 403
$idorManualThrown = false;
try {
    $pedidoService->createManualOrder([
        'creacion_id'      => 1, // Pieza del admin
        'cliente_nombre'   => 'Cliente Ajeno',
        'cliente_contacto' => '5511223344',
        'cantidad'         => 1,
    ], $anaUser);
} catch (RuntimeException $e) {
    $idorManualThrown = ($e->getCode() === 403);
}
TestHelper::assertTrue($idorManualThrown, 'createManualOrder() prohíbe a artesano crear encargo sobre pieza ajena (IDOR 403)');

// Artesana Ana registra encargo sobre su propia pieza (ID 5: Tote Bag) -> Exitoso
$anaManualOrder = $pedidoService->createManualOrder([
    'creacion_id'      => 5, // Pieza de Ana
    'cliente_nombre'   => 'Laura Vázquez',
    'cliente_contacto' => '5522334455',
    'cantidad'         => 1,
    'fecha_entrega'    => date('Y-m-d', strtotime('+10 days')),
    'estado_pedido'    => 'Pendiente',
    'estado_pago'      => 'Pendiente',
    'notas'            => 'Color lino natural',
], $anaUser);
TestHelper::assertTrue(isset($anaManualOrder['id']), 'createManualOrder() por artesana en su propia pieza genera encargo');
TestHelper::assertSame(5, (int)$anaManualOrder['creacion_id'], 'creacion_id es 5');
TestHelper::assertSame(2, (int)$anaManualOrder['creacion']['artesano_id'], 'artesano_id es 2');

// 2.5 listOrders con enriquecimiento monetario y aislamiento de rol
$anaOrderList = $pedidoService->listOrders([], $anaUser);
TestHelper::assertTrue(is_array($anaOrderList['datos']), 'listOrders() retorna array de datos');
TestHelper::assertTrue(isset($anaOrderList['paginacion']), 'listOrders() retorna paginacion estructurada');
foreach ($anaOrderList['datos'] as $item) {
    TestHelper::assertSame(2, (int)$item['creacion']['artesano_id'], 'Todos los pedidos listados por Ana son de sus creaciones');
    TestHelper::assertTrue(isset($item['precio_final_formateado']), 'El pedido incluye precio_final_formateado');
    TestHelper::assertTrue(isset($item['enlace_whatsapp']), 'El pedido incluye enlace_whatsapp');
}

// Admin lista pedidos y ve de múltiples artesanos
$adminOrderList = $pedidoService->listOrders([], $adminUser);
$hasAnaPiece = false;
$hasAdminPiece = false;
foreach ($adminOrderList['datos'] as $item) {
    if ((int)$item['creacion']['artesano_id'] === 1) $hasAdminPiece = true;
    if ((int)$item['creacion']['artesano_id'] === 2) $hasAnaPiece = true;
}
TestHelper::assertTrue($hasAdminPiece && $hasAnaPiece, 'Admin visualiza pedidos de múltiples artesanos');

// 2.6 getOrderById: Salvaguarda IDOR
// Ana intenta consultar un pedido del Admin (Pedido #1) -> Debe dar 403
$idorGetThrown = false;
try {
    $pedidoService->getOrderById(1, $anaUser);
} catch (RuntimeException $e) {
    $idorGetThrown = ($e->getCode() === 403);
}
TestHelper::assertTrue($idorGetThrown, 'getOrderById() prohíbe a un artesano consultar pedidos de otro artesano (IDOR 403)');

// Admin consulta Pedido #1 -> Exitoso
$adminOrderView = $pedidoService->getOrderById(1, $adminUser);
TestHelper::assertSame(1, (int)$adminOrderView['id'], 'Admin puede consultar cualquier pedido por ID');
TestHelper::assertTrue(isset($adminOrderView['enlace_whatsapp']), 'Detalle enriquecido incluye enlace de WhatsApp');

// 2.7 updateOrderStatus con IDOR y delegación automática a cancelación
// Ana intenta cambiar estado del pedido del Admin -> Debe dar 403
$idorUpdateThrown = false;
try {
    $pedidoService->updateOrderStatus(1, 'Entregado', 'Liquidado', $anaUser);
} catch (RuntimeException $e) {
    $idorUpdateThrown = ($e->getCode() === 403);
}
TestHelper::assertTrue($idorUpdateThrown, 'updateOrderStatus() prohíbe modificar pedidos ajenos (IDOR 403)');

// Ana cancela su propio pedido manual creado en 2.4 -> Restituye stock
$anaCancelResult = $pedidoService->cancelOrder($anaManualOrder['id'], $anaUser);
TestHelper::assertSame('Cancelado', $anaCancelResult['estado_pedido'], 'cancelOrder() actualiza estado a Cancelado');
TestHelper::assertSame(1, (int)$anaCancelResult['unidades_restituidas'], 'unidades_restituidas es 1');
TestHelper::assertTrue(str_contains($anaCancelResult['mensaje'], 'restituyeron'), 'Mensaje amigable confirma restitución');

// Limpieza de pedidos de prueba de la sección 2
$pedidoRepo->softDelete($publicReqResult['id']);
$pedidoRepo->softDelete($anaManualOrder['id']);

// =============================================================================
// 3. ENDPOINTS REST EN VIVO CONTRA LOCALHOST:8000 (HTTP CURL)
// =============================================================================
TestHelper::section('3. Endpoints REST en Vivo contra localhost:8000 (HTTP curl)');

// 3.1 Autenticación para obtener Bearer Tokens
$adminLoginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode([
    'username' => 'admin',
    'password' => 'admin123',
]));
$logTrace('1. POST /api/auth/login.php (Admin Login)', $adminLoginRes);
TestHelper::assertSame(200, $adminLoginRes['status'], 'Login de admin responde 200 OK');
$adminToken = $adminLoginRes['json']['datos']['token'] ?? '';
TestHelper::assertTrue(!empty($adminToken), 'Token Bearer de admin obtenido exitosamente');

$artisanLoginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode([
    'username' => 'artesana_ana',
    'password' => 'admin123',
]));
$logTrace('2. POST /api/auth/login.php (Artesana Ana Login)', $artisanLoginRes);
TestHelper::assertSame(200, $artisanLoginRes['status'], 'Login de artesana_ana responde 200 OK');
$artisanToken = $artisanLoginRes['json']['datos']['token'] ?? '';
TestHelper::assertTrue(!empty($artisanToken), 'Token Bearer de artesana_ana obtenido exitosamente');

$assistantLoginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode([
    'username' => 'asistente_leo',
    'password' => 'admin123',
]));
$logTrace('3. POST /api/auth/login.php (Asistente Leo Login)', $assistantLoginRes);
TestHelper::assertSame(200, $assistantLoginRes['status'], 'Login de asistente_leo responde 200 OK');
$assistantToken = $assistantLoginRes['json']['datos']['token'] ?? '';
TestHelper::assertTrue(!empty($assistantToken), 'Token Bearer de asistente_leo obtenido exitosamente');

// 3.2 POST /api/pedidos/solicitar.php (Público, Checkout Modal)
$publicSolicitarRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/solicitar.php', [
    'Content-Type: application/json'
], json_encode([
    'creacion_id'      => 1, // Dragón Ignis ($450.00 MXN)
    'cliente_nombre'   => 'Valeria Montiel',
    'cliente_contacto' => '5588776655',
    'cantidad'         => 1,
    'notas'            => 'Por favor envolver para regalo',
]));
$logTrace('4. POST /api/pedidos/solicitar.php (Público Exitoso: HTTP 201)', $publicSolicitarRes);

TestHelper::assertSame(201, $publicSolicitarRes['status'], 'POST solicitar.php público devuelve 201 Created');
TestHelper::assertTrue($publicSolicitarRes['json']['exito'] ?? false, 'Respuesta indica exito: true');
$solicitadoId = (int)($publicSolicitarRes['json']['datos']['id'] ?? 0);
TestHelper::assertTrue($solicitadoId > 0, 'Pedido solicitado tiene ID asignado');
TestHelper::assertSame(45000, (int)($publicSolicitarRes['json']['datos']['precio_final'] ?? 0), 'precio_final calculado en servidor es 45000');
TestHelper::assertSame('$450.00 MXN', $publicSolicitarRes['json']['datos']['precio_final_formateado'] ?? '', 'precio_final_formateado es $450.00 MXN');

// 3.3 POST /api/pedidos/solicitar.php con stock insuficiente (HTTP 409 Conflict)
$piece1Current = $creacionRepo->findById(1);
$piece1Stock = (int)$piece1Current['cantidad_stock'];

$excessPublicRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/solicitar.php', [
    'Content-Type: application/json'
], json_encode([
    'creacion_id'      => 1,
    'cliente_nombre'   => 'Comprador Masivo',
    'cliente_contacto' => '5500000000',
    'cantidad'         => $piece1Stock + 99,
]));
$logTrace('5. POST /api/pedidos/solicitar.php (Stock Insuficiente: HTTP 409)', $excessPublicRes);
TestHelper::assertSame(409, $excessPublicRes['status'], 'POST solicitar.php con exceso de stock devuelve 409 Conflict');

// 3.4 POST /api/pedidos/solicitar.php con campos inválidos (HTTP 422 Unprocessable Entity)
$invalidPublicRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/solicitar.php', [
    'Content-Type: application/json'
], json_encode([
    'creacion_id'      => 1,
    'cliente_nombre'   => '', // Vacío
    'cliente_contacto' => '55',
    'cantidad'         => 0,
]));
$logTrace('6. POST /api/pedidos/solicitar.php (Datos Inválidos: HTTP 422)', $invalidPublicRes);
TestHelper::assertSame(422, $invalidPublicRes['status'], 'POST solicitar.php con datos vacíos devuelve 422');

// 3.5 GET /api/pedidos/solicitar.php (Método no permitido: HTTP 405)
$getSolicitarRes = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/solicitar.php');
$logTrace('7. GET /api/pedidos/solicitar.php (Método Inválido: HTTP 405)', $getSolicitarRes);
TestHelper::assertSame(405, $getSolicitarRes['status'], 'GET en solicitar.php devuelve 405 Method Not Allowed');

// 3.6 GET /api/pedidos/index.php sin token (HTTP 401 Unauthorized)
$noAuthIndexRes = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php');
$logTrace('8. GET /api/pedidos/index.php (Sin Auth: HTTP 401)', $noAuthIndexRes);
TestHelper::assertSame(401, $noAuthIndexRes['status'], 'GET index.php sin Bearer token devuelve 401 Unauthorized');

// 3.7 GET /api/pedidos/index.php con artesana_ana (Aislamiento multi-artesano: HTTP 200 OK)
$artisanIndexRes = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php', [
    'Authorization: Bearer ' . $artisanToken
]);
$logTrace('9. GET /api/pedidos/index.php (Artesana Ana: HTTP 200)', $artisanIndexRes);
TestHelper::assertSame(200, $artisanIndexRes['status'], 'GET index.php con token de artesana devuelve 200 OK');
TestHelper::assertTrue(isset($artisanIndexRes['json']['paginacion']), 'Respuesta incluye paginacion');
$anaItems = $artisanIndexRes['json']['datos'] ?? [];
TestHelper::assertTrue(count($anaItems) >= 1, 'Ana tiene pedidos listados');
foreach ($anaItems as $ai) {
    TestHelper::assertSame(2, (int)($ai['creacion']['artesano_id'] ?? 0), 'Aislamiento HTTP: Cada pedido pertenece a la artesana Ana (ID 2)');
}

// 3.8 GET /api/pedidos/index.php con admin (Visibilidad global: HTTP 200 OK)
$adminIndexRes = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php', [
    'Authorization: Bearer ' . $adminToken
]);
$logTrace('10. GET /api/pedidos/index.php (Admin Global: HTTP 200)', $adminIndexRes);
TestHelper::assertSame(200, $adminIndexRes['status'], 'GET index.php con admin devuelve 200 OK');
$adminItems = $adminIndexRes['json']['datos'] ?? [];
TestHelper::assertTrue(count($adminItems) >= 2, 'Admin visualiza pedidos de todos los artesanos');

// 3.9 GET /api/pedidos/index.php con filtros (estado_pedido, estado_pago, paginacion)
$filteredIndexRes = TestHelper::curl('GET', 'http://localhost:8000/api/pedidos/index.php?estado_pedido=Pendiente&limite=5', [
    'Authorization: Bearer ' . $adminToken
]);
$logTrace('11. GET /api/pedidos/index.php (Filtros y Paginación: HTTP 200)', $filteredIndexRes);
TestHelper::assertSame(200, $filteredIndexRes['status'], 'GET index.php con filtros devuelve 200 OK');
TestHelper::assertSame(5, (int)($filteredIndexRes['json']['paginacion']['limite'] ?? 0), 'Paginación respeta limite=5');

// 3.10 POST /api/pedidos/index.php (Método no permitido: HTTP 405)
$postIndexRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/index.php', [
    'Authorization: Bearer ' . $adminToken
]);
$logTrace('12. POST /api/pedidos/index.php (Método Inválido: HTTP 405)', $postIndexRes);
TestHelper::assertSame(405, $postIndexRes['status'], 'POST en index.php devuelve 405 Method Not Allowed');

// 3.11 POST /api/pedidos/crear.php con rol asistente (HTTP 403 Forbidden - RoleGuard)
$assistantCrearRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/crear.php', [
    'Authorization: Bearer ' . $assistantToken,
    'Content-Type: application/json'
], json_encode([
    'creacion_id'      => 1,
    'cliente_nombre'   => 'Cliente Asistente',
    'cliente_contacto' => '5511223344',
    'cantidad'         => 1,
]));
$logTrace('13. POST /api/pedidos/crear.php (Asistente Bloqueado: HTTP 403)', $assistantCrearRes);
TestHelper::assertSame(403, $assistantCrearRes['status'], 'POST crear.php con rol asistente devuelve 403 Forbidden');

// 3.12 POST /api/pedidos/crear.php IDOR por artesano en pieza ajena (HTTP 403 Forbidden)
$artisanIdorCrearRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/crear.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'creacion_id'      => 1, // Dragón Ignis (artesano 1 - Admin)
    'cliente_nombre'   => 'Cliente Hack',
    'cliente_contacto' => '5511223344',
    'cantidad'         => 1,
]));
$logTrace('14. POST /api/pedidos/crear.php (IDOR Artesano Ajeno: HTTP 403)', $artisanIdorCrearRes);
TestHelper::assertSame(403, $artisanIdorCrearRes['status'], 'POST crear.php en pieza ajena devuelve 403 Forbidden');

// 3.13 POST /api/pedidos/crear.php exitoso por artesana en pieza propia (HTTP 201 Created)
$artisanOwnCrearRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/crear.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'creacion_id'      => 5, // Tote Bag (artesana 2 - Ana)
    'cliente_nombre'   => 'Camila Navarrete',
    'cliente_contacto' => '5533445566',
    'cantidad'         => 1,
    'fecha_entrega'    => date('Y-m-d', strtotime('+12 days')),
    'estado_pedido'    => 'Pendiente',
    'estado_pago'      => 'Anticipo 50%',
    'notas'            => 'Asas reforzadas con costura doble',
]));
$logTrace('15. POST /api/pedidos/crear.php (Creación Exitosa: HTTP 201)', $artisanOwnCrearRes);
TestHelper::assertSame(201, $artisanOwnCrearRes['status'], 'POST crear.php en pieza propia devuelve 201 Created');
$artisanCreatedOrderId = (int)($artisanOwnCrearRes['json']['datos']['id'] ?? 0);
TestHelper::assertTrue($artisanCreatedOrderId > 0, 'Encargo manual tiene ID generado');
TestHelper::assertSame(38000, (int)($artisanOwnCrearRes['json']['datos']['precio_final'] ?? 0), 'precio_final es 38000');

// 3.14 POST /api/pedidos/cambiar-estado.php IDOR en pedido ajeno (HTTP 403 Forbidden)
$idorCambiarRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cambiar-estado.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'            => 1, // Pedido del admin
    'estado_pedido' => 'Entregado',
    'estado_pago'   => 'Liquidado',
]));
$logTrace('16. POST /api/pedidos/cambiar-estado.php (IDOR Pedido Ajeno: HTTP 403)', $idorCambiarRes);
TestHelper::assertSame(403, $idorCambiarRes['status'], 'POST cambiar-estado en pedido ajeno devuelve 403 Forbidden');

// 3.15 POST /api/pedidos/cambiar-estado.php exitoso por propietario (HTTP 200 OK)
$ownCambiarRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cambiar-estado.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'            => $artisanCreatedOrderId,
    'estado_pedido' => 'En Proceso',
    'estado_pago'   => 'Liquidado',
]));
$logTrace('17. POST /api/pedidos/cambiar-estado.php (Actualización Exitosa: HTTP 200)', $ownCambiarRes);
TestHelper::assertSame(200, $ownCambiarRes['status'], 'POST cambiar-estado por artesana en su pedido devuelve 200 OK');
TestHelper::assertSame('En Proceso', $ownCambiarRes['json']['datos']['estado_pedido'] ?? '', 'estado_pedido actualizado');
TestHelper::assertSame('Liquidado', $ownCambiarRes['json']['datos']['estado_pago'] ?? '', 'estado_pago actualizado');

// 3.16 POST /api/pedidos/cambiar-estado.php con datos inválidos (HTTP 422)
$invalidCambiarRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cambiar-estado.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'            => $artisanCreatedOrderId,
    'estado_pedido' => 'EstadoFantasma', // No permitido
]));
$logTrace('18. POST /api/pedidos/cambiar-estado.php (Estado Inválido: HTTP 422)', $invalidCambiarRes);
TestHelper::assertSame(422, $invalidCambiarRes['status'], 'POST cambiar-estado con estado no permitido devuelve 422');

// 3.17 POST /api/pedidos/cancelar.php IDOR en pedido ajeno (HTTP 403 Forbidden)
$idorCancelarRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => 1, // Pedido del admin
]));
$logTrace('19. POST /api/pedidos/cancelar.php (IDOR Pedido Ajeno: HTTP 403)', $idorCancelarRes);
TestHelper::assertSame(403, $idorCancelarRes['status'], 'POST cancelar en pedido ajeno devuelve 403 Forbidden');

// 3.18 POST /api/pedidos/cancelar.php exitoso con restitución de inventario (HTTP 200 OK)
$stockBeforeCancel = (int)$creacionRepo->findById(5)['cantidad_stock'];

$cancelHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $artisanCreatedOrderId,
]));
$logTrace('20. POST /api/pedidos/cancelar.php (Cancelación y Restitución: HTTP 200)', $cancelHttpRes);

TestHelper::assertSame(200, $cancelHttpRes['status'], 'POST cancelar por artesana devuelve 200 OK');
TestHelper::assertSame('Cancelado', $cancelHttpRes['json']['datos']['estado_pedido'] ?? '', 'estado es Cancelado');
TestHelper::assertSame(1, (int)($cancelHttpRes['json']['datos']['unidades_restituidas'] ?? 0), 'unidades_restituidas es 1');
TestHelper::assertSame($stockBeforeCancel + 1, (int)($cancelHttpRes['json']['datos']['nuevo_stock'] ?? 0), 'nuevo_stock refleja la restitución');

$stockAfterCancel = (int)$creacionRepo->findById(5)['cantidad_stock'];
TestHelper::assertSame($stockBeforeCancel + 1, $stockAfterCancel, 'Stock en BD se incrementó en 1 unidad tras la cancelación');

// 3.19 POST /api/pedidos/cancelar.php en pedido ya cancelado (Idempotencia HTTP 409 Conflict)
$secondCancelHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $artisanCreatedOrderId,
]));
$logTrace('21. POST /api/pedidos/cancelar.php (Ya Cancelado - Idempotencia: HTTP 409)', $secondCancelHttpRes);
TestHelper::assertSame(409, $secondCancelHttpRes['status'], 'POST cancelar pedido ya cancelado devuelve 409 Conflict');

// Verificar que el stock no aumentó una segunda vez
$stockAfterSecondCancel = (int)$creacionRepo->findById(5)['cantidad_stock'];
TestHelper::assertSame($stockAfterCancel, $stockAfterSecondCancel, 'Stock no sufre incremento por segundo intento de cancelación');

// 3.20 Preflight CORS OPTIONS en endpoints de pedidos (HTTP 204 No Content)
$endpointsCors = [
    'index'          => 'http://localhost:8000/api/pedidos/index.php',
    'solicitar'      => 'http://localhost:8000/api/pedidos/solicitar.php',
    'crear'          => 'http://localhost:8000/api/pedidos/crear.php',
    'cambiar-estado' => 'http://localhost:8000/api/pedidos/cambiar-estado.php',
    'cancelar'       => 'http://localhost:8000/api/pedidos/cancelar.php',
];

$corsIdx = 22;
foreach ($endpointsCors as $name => $url) {
    $corsRes = TestHelper::curl('OPTIONS', $url, [
        'Origin: http://localhost:3000',
        'Access-Control-Request-Method: POST',
        'Access-Control-Request-Headers: Authorization, Content-Type',
    ]);
    $logTrace("{$corsIdx}. OPTIONS /api/pedidos/{$name}.php (Preflight CORS: HTTP 204)", $corsRes);
    TestHelper::assertTrue(in_array($corsRes['status'], [200, 204], true), "Preflight OPTIONS en pedidos/{$name}.php responde 200/204");
    $corsIdx++;
}

// Limpieza de pedidos de prueba generados vía HTTP
$pedidoRepo->softDelete($solicitadoId);
$pedidoRepo->softDelete($artisanCreatedOrderId);

// Restaurar existencias a valores semilla originales
$creacionRepo->adjustStock(1, 3);
$creacionRepo->adjustStock(2, 5);
$creacionRepo->adjustStock(5, 6);

// Guardar log de trazas HTTP
file_put_contents(dirname(__DIR__) . '/logs/subfase-3.5-http.log', $httpLogBuffer);

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
