<?php
/**
 * Test Suite: Subfase 3.6.4 - Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (Opción B)
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Alcance y Dimensiones Auditadas (OWASP A04:2021 - Insecure Design):
 * 1. Cálculo & Congelamiento de Precios en el Servidor:
 *    - Inmunidad contra manipulación de precios desde el cliente (tampered price injection).
 *    - El servidor calcula y congela precio_final = creacion.precio * cantidad de manera autoritativa.
 *    - Representación entera exacta en centavos (CurrencyHelper) sin desbordamientos de coma flotante.
 *    - Cálculo riguroso de margen bruto, ganancia y retorno por hora (incluso con costos >= precio o horas = 0).
 * 2. Aislamiento de Concurrencia, Stock Atómico & Restricciones CHECK:
 *    - Deducción atómica bajo BEGIN IMMEDIATE TRANSACTION en PedidoRepository::createAtomic.
 *    - Bloqueo inmediato con HTTP 409 Conflict ante solicitudes que excedan el stock físico disponible.
 *    - Inmunidad relacional contra stock negativo respaldada por CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000).
 *    - Creaciones sobre encargo (es_sobre_encargo = 1): pedidos registrados con éxito sin alterar stock físico.
 *    - Salvaguarda IDOR en creación manual de pedidos (artesano no puede crear pedidos sobre creaciones ajenas).
 * 3. Cancelación Idempotente & Restitución Transaccional (ADR-009):
 *    - Restitución exacta de inventario físico a creaciones.cantidad_stock al cancelar un pedido.
 *    - Idempotencia estricta: re-intentos de cancelación sobre pedidos ya cancelados responden con HTTP 409 Conflict,
 *      impidiendo la generación de inventario fantasma por doble restitución.
 *    - Delegación automática de cancelación al cambiar estado a 'Cancelado' en updateOrderStatus.
 *    - Transiciones de ciclo de confección: Pendiente -> En Proceso -> Entregado y validación de estados permitidos.
 * 4. Resiliencia UTF-8 4-Byte & Caracteres Multibyte:
 *    - Inserción, lectura, actualización y búsqueda con emojis de 4 bytes (🧶, 🧸, ✨, 🌸, 🐉, 🐱) y alfabetos internacionales.
 *    - Caracteres nórdicos/escandinavos (Å, Ø, ä, ö) y acentuados en nombres de artesanos, clientes y piezas.
 *    - Mediciones de longitud de texto con mb_strlen() sin rechazos prematuros ni truncados (strlen vs mb_strlen).
 *    - Enlaces dinámicos de WhatsApp con codificación rawurlencode() para caracteres especiales y emojis.
 * 5. Límites Numéricos y Casos Extremos (Boundary Testing):
 *    - Pedidos: validaciones de cantidad (1 a 1000), cliente_nombre (2 a 100), contacto y notas.
 *    - Creaciones: validaciones de precio (1 a 9,999,999 centavos), costo, stock (0 a 10,000), horas (0.0 a 500.0),
 *      dimensiones (2 a 100), material (3 a 80) y categoría (2 a 50).
 * 6. Pruebas de Integración HTTP en Vivo contra Servidor Local (http://localhost:8000):
 *    - Pruebas curl directas para inyección de precios, stock insuficiente 409, cancelación idempotente 409,
 *      cambio de estados, aislamiento multi-artesano y payloads multibyte, con volcado a logs/subfase-3.6.4-http.log.
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Core\Config;
use App\Core\Database;
use App\Repositories\CreacionRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use App\Services\CreacionService;
use App\Services\PedidoService;
use App\Utils\CurrencyHelper;

TestHelper::init('Subfase 3.6.4: Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)');

$pdo = Database::getInstance();
$creacionRepo = new CreacionRepository($pdo);
$pedidoRepo = new PedidoRepository($pdo);
$usuarioRepo = new UsuarioRepository($pdo);
$authService = new AuthService($usuarioRepo);
$creacionService = new CreacionService($creacionRepo, $usuarioRepo);
$pedidoService = new PedidoService($pedidoRepo, $creacionRepo);

// Helper para obtener token Bearer del admin y de la artesana
$adminAuth = $authService->authenticate('admin', 'admin123');
$adminToken = $adminAuth['token'] ?? '';
$adminUser = $adminAuth['usuario'] ?? [];

$artisanAuth = $authService->authenticate('artesana_ana', 'admin123');
$artisanToken = $artisanAuth['token'] ?? '';
$artisanUser = $artisanAuth['usuario'] ?? [];

$baseUrl = 'http://localhost:8000';
$httpLogPath = dirname(__DIR__) . '/logs/subfase-3.6.4-http.log';
// Reiniciar log HTTP
file_put_contents($httpLogPath, '');

$logHttpTransaction = function(string $title, array $res, ?string $reqPayload = null) use ($httpLogPath) {
    $out = str_repeat('=', 80) . PHP_EOL;
    $out .= "TRAZA HTTP: {$title}" . PHP_EOL;
    $out .= str_repeat('=', 80) . PHP_EOL;
    $out .= "HTTP Status: {$res['status']}" . PHP_EOL;
    $out .= "Latencia: {$res['duration_ms']} ms" . PHP_EOL;
    if ($reqPayload !== null) {
        $out .= "--- Payload Enviado ---" . PHP_EOL;
        $out .= $reqPayload . PHP_EOL;
    }
    $out .= "--- Headers de Respuesta ---" . PHP_EOL;
    foreach ($res['headers'] as $k => $v) {
        $out .= "{$k}: {$v}" . PHP_EOL;
    }
    $out .= "--- Cuerpo de Respuesta (JSON Format) ---" . PHP_EOL;
    if (is_array($res['json'])) {
        $out .= json_encode($res['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        $out .= $res['body'] . PHP_EOL;
    }
    $out .= PHP_EOL . PHP_EOL;
    file_put_contents($httpLogPath, $out, FILE_APPEND);
};

// ============================================================================
// SECCIÓN 1: CÁLCULO & CONGELAMIENTO DE PRECIOS EN EL SERVIDOR (OWASP A04:2021)
// ============================================================================
TestHelper::section('1. Cálculo & Congelamiento de Precios en el Servidor (OWASP A04:2021)');

// 1.1 Inyección de precio manipulado en requestPublicOrder()
// Intentamos forzar precio: 10 y precio_final: 50 en la creación #1 (precio unitario = 45000 / $450.00 MXN)
$tamperedOrderInput = [
    'creacion_id'      => 1,
    'cliente_nombre'   => 'Cliente Manipulador',
    'cliente_contacto' => '5511223344',
    'cantidad'         => 2,
    'precio'           => 10,        // Tampered!
    'precio_unitario'  => 20,        // Tampered!
    'precio_final'     => 30,        // Tampered!
    'notas'            => 'Intento de manipulación de precio en checkout',
];

$publicOrderRes = $pedidoService->requestPublicOrder($tamperedOrderInput);
TestHelper::assert($publicOrderRes['id'] > 0, 'Pedido público registrado con éxito');
TestHelper::assertSame(90000, $publicOrderRes['precio_final'], 'precio_final fue calculado por el servidor (45000 * 2 = 90000 centavos)');
TestHelper::assertSame('$900.00 MXN', $publicOrderRes['precio_final_formateado'], 'precio_final_formateado refleja cálculo real del servidor');

// Verificar que en base de datos física el precio congelado es exactamente 90000
$orderInDb = $pedidoRepo->findById((int)$publicOrderRes['id']);
TestHelper::assertNotNull($orderInDb, 'El pedido existe en la base de datos');
TestHelper::assertSame(90000, (int)$orderInDb['precio_final'], 'El precio_final almacenado en SQLite es exactamente 90000 centavos');

// Limpiar pedido de prueba y restaurar stock
$pedidoRepo->cancelOrderAtomic((int)$publicOrderRes['id']);
$pedidoRepo->softDelete((int)$publicOrderRes['id']);

// 1.2 Inyección de precio manipulado en createManualOrder()
$tamperedManualInput = [
    'creacion_id'      => 1,
    'cliente_nombre'   => 'Cliente Manual Fraudulento',
    'cliente_contacto' => '5599887766',
    'cantidad'         => 3,
    'precio'           => 1,       // Tampered!
    'precio_final'     => 5,       // Tampered!
    'notas'            => 'Intento de fraude en encargo manual',
];

$manualOrderRes = $pedidoService->createManualOrder($tamperedManualInput, $adminUser);
TestHelper::assert($manualOrderRes['id'] > 0, 'Encargo manual registrado con éxito');
TestHelper::assertSame(135000, (int)$manualOrderRes['precio_final'], 'createManualOrder ignora precio cliente y calcula 45000 * 3 = 135000');
TestHelper::assertSame('$1,350.00 MXN', $manualOrderRes['precio_final_formateado'], 'precio_final_formateado refleja $1,350.00 MXN');

// Limpiar pedido manual y restaurar stock
$pedidoRepo->cancelOrderAtomic((int)$manualOrderRes['id']);
$pedidoRepo->softDelete((int)$manualOrderRes['id']);

// 1.3 Exactitud matemática de CurrencyHelper (erradicación de errores de coma flotante)
TestHelper::assertSame(45000, CurrencyHelper::mxnToCents(450.00), 'CurrencyHelper::mxnToCents(450.00) = 45000');
TestHelper::assertSame(45000, CurrencyHelper::mxnToCents('$450.00 MXN'), 'CurrencyHelper::mxnToCents("$450.00 MXN") = 45000');
TestHelper::assertSame(18000, CurrencyHelper::mxnToCents('180'), 'CurrencyHelper::mxnToCents("180") = 18000');
TestHelper::assertSame(1, CurrencyHelper::mxnToCents('0.01'), 'CurrencyHelper::mxnToCents("0.01") = 1 centavo');
TestHelper::assertSame(9999999, CurrencyHelper::mxnToCents('99999.99'), 'CurrencyHelper::mxnToCents("99999.99") = 9999999 centavos');
TestHelper::assertSame(125050, CurrencyHelper::mxnToCents('1,250.50'), 'CurrencyHelper::mxnToCents("1,250.50") con comas = 125050');
TestHelper::assertSame(123456, CurrencyHelper::mxnToCents("  $ 1,234.56 MXN \t "), 'CurrencyHelper::mxnToCents con espacios, tabulaciones y símbolos = 123456');

TestHelper::assertSame(450.0, CurrencyHelper::centsToMxn(45000), 'CurrencyHelper::centsToMxn(45000) = 450.0');
TestHelper::assertSame(0.01, CurrencyHelper::centsToMxn(1), 'CurrencyHelper::centsToMxn(1) = 0.01');
TestHelper::assertSame(99999.99, CurrencyHelper::centsToMxn(9999999), 'CurrencyHelper::centsToMxn(9999999) = 99999.99');

TestHelper::assertSame(450.0, CurrencyHelper::centsToPesos(45000), 'Alias de conveniencia: centsToPesos(45000) = 450.0');
TestHelper::assertSame(45000, CurrencyHelper::pesosToCents(450.0), 'Alias de conveniencia: pesosToCents(450.0) = 45000');

TestHelper::assertSame('$450.00 MXN', CurrencyHelper::formatCents(45000), 'CurrencyHelper::formatCents(45000) = "$450.00 MXN"');
TestHelper::assertSame('$450.00', CurrencyHelper::formatCents(45000, false), 'CurrencyHelper::formatCents(45000, false) = "$450.00"');
TestHelper::assertSame('$1,250.50 MXN', CurrencyHelper::formatCents(125050), 'formatCents maneja miles con coma');

// Demostración de erradicación de errores de coma flotante clásicos de IEEE 754
// En float binario, 0.1 + 0.2 !== 0.3 (0.30000000000000004). En centavos:
$centsA = CurrencyHelper::mxnToCents(0.10); // 10 centavos
$centsB = CurrencyHelper::mxnToCents(0.20); // 20 centavos
TestHelper::assertSame(30, $centsA + $centsB, 'Aritmética entera en centavos: 10 + 20 === 30 (sin artefacto 0.30000000000000004)');
TestHelper::assertSame('$0.30 MXN', CurrencyHelper::formatCents($centsA + $centsB), 'Formato de 30 centavos = "$0.30 MXN"');

// Enriquecimiento de Pedido con CurrencyHelper::enrichOrder
$orderSample = ['precio_final' => 85000];
$enrichedOrderSample = CurrencyHelper::enrichOrder($orderSample);
TestHelper::assertSame(85000, $enrichedOrderSample['precio_final_centavos'], 'enrichOrder provee precio_final_centavos');
TestHelper::assertSame(850.0, $enrichedOrderSample['precio_final_mxn'], 'enrichOrder provee precio_final_mxn');
TestHelper::assertSame('$850.00 MXN', $enrichedOrderSample['precio_final_formateado'], 'enrichOrder provee precio_final_formateado');

// 1.4 Métricas de margen bruto y retorno por hora
// Caso 1: Ficha normal con ganancia
$creationDummyNormal = [
    'precio'           => 45000, // $450.00
    'costo_materiales' => 12000, // $120.00
    'horas_tejido'     => 4.0,   // 4 horas
];
$enrichedNormal = CurrencyHelper::enrichCreation($creationDummyNormal);
TestHelper::assertSame(33000, $enrichedNormal['ganancia_bruta_centavos'], 'Ganancia bruta = 33000 centavos ($330.00)');
TestHelper::assertSame(73.3, $enrichedNormal['margen_bruto_porcentaje'], 'Margen bruto = 73.3% ((330 / 450) * 100)');
TestHelper::assertSame(82.5, $enrichedNormal['retorno_por_hora_mxn'], 'Retorno por hora = $82.50 MXN (330 / 4)');
TestHelper::assertSame('$82.50 MXN/h', $enrichedNormal['retorno_por_hora_formateado'], 'Retorno formateado = "$82.50 MXN/h"');

// Caso 2: Precio igual a Costo (Margen Cero)
$creationDummyZero = [
    'precio'           => 20000,
    'costo_materiales' => 20000,
    'horas_tejido'     => 2.0,
];
$enrichedZero = CurrencyHelper::enrichCreation($creationDummyZero);
TestHelper::assertSame(0, $enrichedZero['ganancia_bruta_centavos'], 'Ganancia bruta cero');
TestHelper::assertSame(0.0, $enrichedZero['margen_bruto_porcentaje'], 'Margen bruto = 0.0%');
TestHelper::assertSame(0.0, $enrichedZero['retorno_por_hora_mxn'], 'Retorno por hora = 0.0');
TestHelper::assertSame('$0.00 MXN/h', $enrichedZero['retorno_por_hora_formateado'], 'Retorno formateado = "$0.00 MXN/h"');

// Caso 3: Costo mayor a Precio (Margen Negativo)
$creationDummyNegative = [
    'precio'           => 10000, // $100.00
    'costo_materiales' => 15000, // $150.00
    'horas_tejido'     => 2.5,
];
$enrichedNegative = CurrencyHelper::enrichCreation($creationDummyNegative);
TestHelper::assertSame(-5000, $enrichedNegative['ganancia_bruta_centavos'], 'Ganancia negativa = -5000 centavos');
TestHelper::assertSame(-50.0, $enrichedNegative['margen_bruto_porcentaje'], 'Margen negativo = -50.0%');
TestHelper::assertSame(-20.0, $enrichedNegative['retorno_por_hora_mxn'], 'Retorno por hora negativo = -20.0');

// Caso 4: Evitación de división por cero cuando horas_tejido = 0.0 o null
$creationDummyZeroHours = [
    'precio'           => 30000,
    'costo_materiales' => 10000,
    'horas_tejido'     => 0.0,
];
$enrichedZeroHours = CurrencyHelper::enrichCreation($creationDummyZeroHours);
TestHelper::assertSame(0.0, $enrichedZeroHours['retorno_por_hora_mxn'], 'Cero horas no produce error de división por cero');
TestHelper::assertSame('$0.00 MXN/h', $enrichedZeroHours['retorno_por_hora_formateado'], 'Retorno formateado para 0 horas = "$0.00 MXN/h"');

// Caso 5: Evitación de división por cero cuando precio = 0
$creationDummyZeroPrice = [
    'precio'           => 0,
    'costo_materiales' => 5000,
    'horas_tejido'     => 1.0,
];
$enrichedZeroPrice = CurrencyHelper::enrichCreation($creationDummyZeroPrice);
TestHelper::assertSame(0.0, $enrichedZeroPrice['margen_bruto_porcentaje'], 'Precio cero no produce error de división por cero en margen');

// ============================================================================
// SECCIÓN 2: AISLAMIENTO DE CONCURRENCIA, STOCK ATÓMICO & RESTRICCIONES CHECK
// ============================================================================
TestHelper::section('2. Aislamiento de Concurrencia, Stock Atómico & Restricciones CHECK');

// 2.1 Crear pieza temporal para probar deducción atómica de inventario
$tempStockCreationData = [
    'nombre'           => 'Pieza Prueba Stock Atómico',
    'categoria'        => 'Fantasía',
    'material'         => '100% Algodón',
    'dimensiones'      => '12 cm',
    'precio'           => '300.00',
    'costo_materiales' => '80.00',
    'cantidad_stock'   => 5, // 5 unidades disponibles
    'horas_tejido'     => '2.0',
    'es_sobre_encargo' => 0,
    'descripcion'      => 'Pieza para validación atómica de existencias',
];
$createdStockCreation = $creacionService->createCreation($tempStockCreationData, null, $adminUser);
$stockCreationId = (int)$createdStockCreation['id'];
TestHelper::assert($stockCreationId > 0, 'Creación temporal para pruebas de stock creada');

// Validar stock inicial
$initialStock = (int)$creacionRepo->findById($stockCreationId)['cantidad_stock'];
TestHelper::assertSame(5, $initialStock, 'Stock inicial verificado en 5 unidades');

// Pedido A: Pedir 3 unidades (quedan 2)
$orderA = $pedidoService->requestPublicOrder([
    'creacion_id'      => $stockCreationId,
    'cliente_nombre'   => 'Comprador A',
    'cliente_contacto' => '5512345678',
    'cantidad'         => 3,
]);
TestHelper::assert($orderA['id'] > 0, 'Pedido A registrado exitosamente para 3 unidades');
$stockAfterA = (int)$creacionRepo->findById($stockCreationId)['cantidad_stock'];
TestHelper::assertSame(2, $stockAfterA, 'Stock decrementado atómicamente a 2 unidades');

// Pedido B: Pedir 2 unidades (quedan exactamente 0, agotada)
$orderB = $pedidoService->requestPublicOrder([
    'creacion_id'      => $stockCreationId,
    'cliente_nombre'   => 'Comprador B',
    'cliente_contacto' => '5587654321',
    'cantidad'         => 2,
]);
TestHelper::assert($orderB['id'] > 0, 'Pedido B registrado exitosamente para 2 unidades');
$stockAfterB = (int)$creacionRepo->findById($stockCreationId)['cantidad_stock'];
TestHelper::assertSame(0, $stockAfterB, 'Stock decrementado exactamente a 0 unidades (agotado)');

// Pedido C: Intentar pedir 1 unidad cuando el stock es 0 -> Debe lanzar 409 Conflict
$exceptionThrownC = false;
$exceptionCodeC = 0;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => $stockCreationId,
        'cliente_nombre'   => 'Comprador C',
        'cliente_contacto' => '5500000000',
        'cantidad'         => 1,
    ]);
} catch (\RuntimeException $e) {
    $exceptionThrownC = true;
    $exceptionCodeC = $e->getCode();
}
TestHelper::assertTrue($exceptionThrownC, 'requestPublicOrder rechaza pedido cuando el stock físico es 0');
TestHelper::assertSame(409, $exceptionCodeC, 'Código de error es HTTP 409 Conflict');

// Pedido D: Intentar pedir 10 unidades cuando el stock es 0 -> Debe lanzar 409 Conflict
$exceptionThrownD = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => $stockCreationId,
        'cliente_nombre'   => 'Comprador D',
        'cliente_contacto' => '5599999999',
        'cantidad'         => 10,
    ]);
} catch (\RuntimeException $e) {
    $exceptionThrownD = true;
}
TestHelper::assertTrue($exceptionThrownD, 'requestPublicOrder rechaza pedido de 10 unidades sobre stock agotado');

// Verificar que el stock no cambió y sigue en 0
$stockAfterFailedOrders = (int)$creacionRepo->findById($stockCreationId)['cantidad_stock'];
TestHelper::assertSame(0, $stockAfterFailedOrders, 'Stock físico se mantiene intacto en 0 unidades');

// 2.2 Restricción CHECK de SQLite contra stock negativo (chk_creaciones_cantidad_stock)
$pdoCheckNegative = false;
try {
    $stmtNeg = $pdo->prepare('UPDATE creaciones SET cantidad_stock = -1 WHERE id = :id');
    $stmtNeg->execute([':id' => $stockCreationId]);
} catch (\PDOException $e) {
    $pdoCheckNegative = true;
}
TestHelper::assertTrue($pdoCheckNegative, 'SQLite DDL CHECK impide stock negativo (-1) arrojando PDOException');

$pdoCheckExcess = false;
try {
    $stmtExcess = $pdo->prepare('UPDATE creaciones SET cantidad_stock = 10001 WHERE id = :id');
    $stmtExcess->execute([':id' => $stockCreationId]);
} catch (\PDOException $e) {
    $pdoCheckExcess = true;
}
TestHelper::assertTrue($pdoCheckExcess, 'SQLite DDL CHECK impide stock excesivo (>10000) arrojando PDOException');

// 2.3 Creaciones Bajo Encargo (es_sobre_encargo = 1)
$tempCommissionCreation = $creacionService->createCreation([
    'nombre'           => 'Pieza Exclusiva Bajo Encargo',
    'categoria'        => 'Accesorios',
    'material'         => 'Trapillo & Lino',
    'dimensiones'      => '30 × 25 cm',
    'precio'           => '850.00',
    'costo_materiales' => '250.00',
    'cantidad_stock'   => 0, // 0 existencias físicas
    'horas_tejido'     => '8.0',
    'es_sobre_encargo' => 1, // Exclusivo bajo encargo
    'descripcion'      => 'Pieza confeccionada únicamente sobre pedido del cliente',
], null, $adminUser);
$commissionCreationId = (int)$tempCommissionCreation['id'];
TestHelper::assert($commissionCreationId > 0, 'Creación bajo encargo creada con éxito');

// Realizar pedido para 2 unidades con stock 0
$commissionOrder = $pedidoService->requestPublicOrder([
    'creacion_id'      => $commissionCreationId,
    'cliente_nombre'   => 'Cliente Comisión',
    'cliente_contacto' => '5577665544',
    'cantidad'         => 2,
    'notas'            => 'Colores tierra nórdicos',
]);
TestHelper::assert($commissionOrder['id'] > 0, 'Pedido bajo encargo registrado con éxito incluso con stock físico = 0');
TestHelper::assertSame(1, $commissionOrder['es_sobre_encargo'], 'La respuesta identifica el pedido como sobre encargo');
TestHelper::assertStringContains('tiempos de confección', $commissionOrder['mensaje'], 'El mensaje informa coordinación de tiempos de confección');

// Verificar que el stock físico de la pieza bajo encargo NO se decrementó a negativo (-2)
$stockCommissionAfter = (int)$creacionRepo->findById($commissionCreationId)['cantidad_stock'];
TestHelper::assertSame(0, $stockCommissionAfter, 'El stock físico de la pieza bajo encargo se mantiene en 0 (sin decremento)');

// 2.4 Salvaguarda IDOR en creación manual de pedidos
// artesana_ana (id: 2) intenta registrar un encargo para creacion_id: 1 (de admin, id: 1) -> HTTP 403
$idorManualException = false;
$idorManualCode = 0;
try {
    $pedidoService->createManualOrder([
        'creacion_id'      => 1, // Propiedad de admin
        'cliente_nombre'   => 'Cliente IDOR',
        'cliente_contacto' => '5511223344',
        'cantidad'         => 1,
    ], $artisanUser); // artesana_ana
} catch (\RuntimeException $e) {
    $idorManualException = true;
    $idorManualCode = $e->getCode();
}
TestHelper::assertTrue($idorManualException, 'artesano no puede crear encargos sobre creaciones de otros artesanos');
TestHelper::assertSame(403, $idorManualCode, 'Código de error IDOR es HTTP 403 Forbidden');

// En cambio, admin sí puede crear encargos para cualquier creación
$adminManualForArtisan = $pedidoService->createManualOrder([
    'creacion_id'      => 3, // Propiedad de artesana_ana
    'cliente_nombre'   => 'Cliente Supervisado por Admin',
    'cliente_contacto' => '5511223344',
    'cantidad'         => 1,
], $adminUser);
TestHelper::assert($adminManualForArtisan['id'] > 0, 'Administrador global tiene permiso omnímodo para registrar pedidos');

// Limpieza de pedidos de la sección 2
$pedidoRepo->cancelOrderAtomic((int)$orderA['id']);
$pedidoRepo->cancelOrderAtomic((int)$orderB['id']);
$pedidoRepo->cancelOrderAtomic((int)$commissionOrder['id']);
$pedidoRepo->cancelOrderAtomic((int)$adminManualForArtisan['id']);
$creacionRepo->softDelete($stockCreationId);
$creacionRepo->softDelete($commissionCreationId);

// ============================================================================
// SECCIÓN 3: CANCELACIÓN IDEMPOTENTE & RESTITUCIÓN TRANSACCIONAL (ADR-009)
// ============================================================================
TestHelper::section('3. Cancelación Idempotente & Restitución Transaccional (ADR-009)');

// 3.1 Crear pieza temporal con stock inicial conocido
$restitutionCreation = $creacionService->createCreation([
    'nombre'           => 'Pieza Prueba Restitución',
    'categoria'        => 'Bebé',
    'material'         => 'Algodón Antialérgico',
    'dimensiones'      => '18 cm',
    'precio'           => '400.00',
    'costo_materiales' => '100.00',
    'cantidad_stock'   => 10, // 10 unidades
    'horas_tejido'     => '3.0',
    'es_sobre_encargo' => 0,
    'descripcion'      => 'Prueba de restitución atómica',
], null, $adminUser);
$restitutionCreationId = (int)$restitutionCreation['id'];

// Crear pedido para 4 unidades (stock baja de 10 a 6)
$orderToCancel = $pedidoService->requestPublicOrder([
    'creacion_id'      => $restitutionCreationId,
    'cliente_nombre'   => 'Cliente Arrepentido',
    'cliente_contacto' => '5544332211',
    'cantidad'         => 4,
]);
$orderToCancelId = (int)$orderToCancel['id'];
$stockReserved = (int)$creacionRepo->findById($restitutionCreationId)['cantidad_stock'];
TestHelper::assertSame(6, $stockReserved, 'Stock disminuyó de 10 a 6 unidades al crear el pedido');

// Cancelar el pedido mediante cancelOrder()
$cancelResult = $pedidoService->cancelOrder($orderToCancelId, $adminUser);
TestHelper::assertSame('Cancelado', $cancelResult['estado_pedido'], 'Estado del pedido cambió a Cancelado');
TestHelper::assertSame(4, $cancelResult['unidades_restituidas'], 'unidades_restituidas es exactamente 4');
TestHelper::assertSame(10, $cancelResult['nuevo_stock'], 'nuevo_stock reporta 10 unidades');

// Verificar en base de datos física que el stock regresó a 10
$stockRestored = (int)$creacionRepo->findById($restitutionCreationId)['cantidad_stock'];
TestHelper::assertSame(10, $stockRestored, 'El stock físico en SQLite regresó íntegramente a 10 unidades');

// 3.2 Idempotencia Estricta (ADR-009): Intentar cancelar por SEGUNDA VEZ el pedido ya cancelado
$secondCancelException = false;
$secondCancelCode = 0;
try {
    $pedidoService->cancelOrder($orderToCancelId, $adminUser);
} catch (\RuntimeException $e) {
    $secondCancelException = true;
    $secondCancelCode = $e->getCode();
}
TestHelper::assertTrue($secondCancelException, 'Segundo intento de cancelación es rechazado');
TestHelper::assertSame(409, $secondCancelCode, 'Rechazo con HTTP 409 Conflict por idempotencia (ya cancelado)');

// Verificar que el stock físico NO aumentó a 14 por doble restitución
$stockAfterSecondCancel = (int)$creacionRepo->findById($restitutionCreationId)['cantidad_stock'];
TestHelper::assertSame(10, $stockAfterSecondCancel, 'Garantía de idempotencia: el stock se mantiene en 10 y NO se incrementó a 14');

// 3.3 Delegación automática de cancelación al cambiar estado a 'Cancelado'
// Creamos otro pedido de 3 unidades (stock de 10 a 7)
$orderDelegate = $pedidoService->requestPublicOrder([
    'creacion_id'      => $restitutionCreationId,
    'cliente_nombre'   => 'Cliente Delegación',
    'cliente_contacto' => '5511223344',
    'cantidad'         => 3,
]);
$orderDelegateId = (int)$orderDelegate['id'];
TestHelper::assertSame(7, (int)$creacionRepo->findById($restitutionCreationId)['cantidad_stock'], 'Stock baja a 7');

// Cambiar estado a 'Cancelado' usando updateOrderStatus()
$delegateCancelResult = $pedidoService->updateOrderStatus($orderDelegateId, 'Cancelado', null, $adminUser);
TestHelper::assertSame('Cancelado', $delegateCancelResult['estado_pedido'], 'updateOrderStatus delega y retorna estado Cancelado');
TestHelper::assertSame(3, $delegateCancelResult['unidades_restituidas'], 'Se restituyeron 3 unidades vía delegación');
TestHelper::assertSame(10, (int)$creacionRepo->findById($restitutionCreationId)['cantidad_stock'], 'Stock restaurado a 10 vía updateOrderStatus');

// 3.4 Transiciones de ciclo de confección válidas
// Creamos un pedido para transicionar estados: Pendiente -> En Proceso -> Entregado
$orderLifecycle = $pedidoService->requestPublicOrder([
    'creacion_id'      => $restitutionCreationId,
    'cliente_nombre'   => 'Cliente Ciclo Vida',
    'cliente_contacto' => '5522334455',
    'cantidad'         => 1,
]);
$lifecycleOrderId = (int)$orderLifecycle['id'];

// Transición 1: En Proceso con anticipo 50%
$trans1 = $pedidoService->updateOrderStatus($lifecycleOrderId, 'En Proceso', 'Anticipo 50%', $adminUser);
TestHelper::assertSame('En Proceso', $trans1['estado_pedido'], 'Transición a En Proceso exitosa');
TestHelper::assertSame('Anticipo 50%', $trans1['estado_pago'], 'Estado de pago actualizado a Anticipo 50%');

// Transición 2: Entregado con Liquidado
$trans2 = $pedidoService->updateOrderStatus($lifecycleOrderId, 'Entregado', 'Liquidado', $adminUser);
TestHelper::assertSame('Entregado', $trans2['estado_pedido'], 'Transición a Entregado exitosa');
TestHelper::assertSame('Liquidado', $trans2['estado_pago'], 'Estado de pago actualizado a Liquidado');

// Transición 3: Estado inválido es rechazado
$errStateInvalid = false;
try {
    $pedidoService->updateOrderStatus($lifecycleOrderId, 'Extraviado', null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errStateInvalid = true;
}
TestHelper::assertTrue($errStateInvalid, 'Estado de pedido no permitido es rechazado con InvalidArgumentException (422)');

$errPaymentInvalid = false;
try {
    $pedidoService->updateOrderStatus($lifecycleOrderId, 'Entregado', 'Fiado', $adminUser);
} catch (\InvalidArgumentException $e) {
    $errPaymentInvalid = true;
}
TestHelper::assertTrue($errPaymentInvalid, 'Estado de pago no permitido es rechazado con InvalidArgumentException (422)');

// Intento de cancelación de pedido inexistente
$nonExistentCancel = false;
$nonExistentCode = 0;
try {
    $pedidoService->cancelOrder(999999, $adminUser);
} catch (\RuntimeException $e) {
    $nonExistentCancel = true;
    $nonExistentCode = $e->getCode();
}
TestHelper::assertTrue($nonExistentCancel, 'Cancelar pedido inexistente es rechazado');
TestHelper::assertSame(404, $nonExistentCode, 'Código de error es HTTP 404 Not Found');

// Limpieza sección 3
$creacionRepo->softDelete($restitutionCreationId);

// ============================================================================
// SECCIÓN 4: RESILIENCIA UTF-8 4-BYTE & CARACTERES MULTIBYTE
// ============================================================================
TestHelper::section('4. Resiliencia UTF-8 4-Byte & Caracteres Multibyte');

// 4.1 Creación con emojis de 4 bytes, acentos, diéresis y caracteres orientales
$multibyteCreationInput = [
    'nombre'           => '🧸 Osito Polar Ártico & Corazón ✨ 🧶',
    'categoria'        => 'Hogar & Decoración 🌸',
    'material'         => 'Algodón Pima 100% & Seda Nórdica 🧵',
    'dimensiones'      => '22 × 16 × 14 cm (±1 cm)',
    'precio'           => '650.00',
    'costo_materiales' => '180.00',
    'cantidad_stock'   => 4,
    'horas_tejido'     => '5.5',
    'es_sobre_encargo' => 0,
    'descripcion'      => 'Diseño escandinavo artesanal con finos detalles bordados a mano 🪡. Ideal para ambientes acogedores ✨ y amantes del tejido 💖. Patrón original: アミグルミ (Amigurumi japonés) y técnica nórdica 🐉.',
];

$multibyteCreationRes = $creacionService->createCreation($multibyteCreationInput, null, $adminUser);
$multibyteCreationId = (int)$multibyteCreationRes['id'];
TestHelper::assert($multibyteCreationId > 0, 'Creación con emojis 4-byte y caracteres internacionales creada con éxito');

// Leer de la base de datos y verificar fidelidad byte a byte
$readMultibyte = $creacionService->getCreationById($multibyteCreationId);
TestHelper::assertSame('🧸 Osito Polar Ártico & Corazón ✨ 🧶', $readMultibyte['nombre'], 'Nombre recuperado con exactitud incluyendo emojis de 4 bytes');
TestHelper::assertSame('Hogar & Decoración 🌸', $readMultibyte['categoria'], 'Categoría con acento y emoji preservada');
TestHelper::assertSame('Algodón Pima 100% & Seda Nórdica 🧵', $readMultibyte['material'], 'Material con diéresis, acentos y emoji preservado');
TestHelper::assertStringContains('アミグルミ', $readMultibyte['descripcion'], 'Caracteres japoneses katakana preservados intactos en SQLite');
TestHelper::assertStringContains('🐉', $readMultibyte['descripcion'], 'Emoji de dragón (U+1F409, 4 bytes) preservado en descripción');

// Demostración de conteo preciso con mb_strlen vs strlen
$emojiStr = '🧸';
TestHelper::assertSame(4, strlen($emojiStr), 'strlen("🧸") reporta 4 bytes');
TestHelper::assertSame(1, mb_strlen($emojiStr, 'UTF-8'), 'mb_strlen("🧸") reporta exactamente 1 carácter Unicode');

// Validar que json_encode serializa limpiamente sin errores de codificación
$encodedJson = json_encode($readMultibyte, JSON_UNESCAPED_UNICODE);
TestHelper::assertSame(JSON_ERROR_NONE, json_last_error(), 'json_encode procesa el payload multibyte sin error UTF-8');
TestHelper::assertStringContains('🧸', (string)$encodedJson, 'JSON serializado contiene emoji sin secuencias de escape rotas');

// 4.2 Búsqueda reactiva con emojis y texto multibyte
$searchEmojiRes = $creacionService->getCatalog(['busqueda' => '🧸']);
TestHelper::assert($searchEmojiRes['paginacion']['total_items'] >= 1, 'Búsqueda por emoji 🧸 localiza la creación');

$searchJapaneseRes = $creacionService->getCatalog(['busqueda' => 'アミグルミ']);
TestHelper::assert($searchJapaneseRes['paginacion']['total_items'] >= 1, 'Búsqueda por texto katakana アミグルミ localiza la creación');

$searchAccentedRes = $creacionService->getCatalog(['busqueda' => 'Ártico']);
TestHelper::assert($searchAccentedRes['paginacion']['total_items'] >= 1, 'Búsqueda por palabra acentuada Ártico localiza la creación');

// 4.3 Caracteres escandinavos en autoría y pedidos
$nordicOrderInput = [
    'creacion_id'      => $multibyteCreationId,
    'cliente_nombre'   => 'Åse Øyvindson • Häkeln Taller 🧵',
    'cliente_contacto' => '+52 55 9876 5432',
    'cantidad'         => 1,
    'notas'            => 'Favor de envolver para regalo 🎁 con moño lila 🎀 y dedicatoria con cariño 💖',
];

$multibyteOrderRes = $pedidoService->requestPublicOrder($nordicOrderInput);
$multibyteOrderId = (int)$multibyteOrderRes['id'];
TestHelper::assert($multibyteOrderId > 0, 'Pedido con caracteres nórdicos y emojis 4-byte creado con éxito');

// Leer pedido completo con enlace WhatsApp
$enrichedMultibyteOrder = $pedidoService->getOrderById($multibyteOrderId, $adminUser);
TestHelper::assertSame('Åse Øyvindson • Häkeln Taller 🧵', $enrichedMultibyteOrder['cliente_nombre'], 'Nombre escandinavo preservado intacto en SQLite');
TestHelper::assertStringContains('🎁', (string)$enrichedMultibyteOrder['notas'], 'Notas preservadas con emoji 🎁');

// Validar enlace WhatsApp
$waLink = $enrichedMultibyteOrder['enlace_whatsapp'];
TestHelper::assertNotNull($waLink, 'Enlace de WhatsApp generado');
TestHelper::assertStringContains('https://wa.me/525598765432', $waLink, 'Número de WhatsApp normalizado a E.164 (+52 10 dígitos)');
TestHelper::assertStringContains(rawurlencode('Åse Øyvindson • Häkeln Taller 🧵'), $waLink, 'Nombre del cliente codificado con rawurlencode');

// Limpieza sección 4
$pedidoRepo->cancelOrderAtomic($multibyteOrderId);
$creacionRepo->softDelete($multibyteCreationId);

// ============================================================================
// SECCIÓN 5: LÍMITES NUMÉRICOS Y CASOS EXTREMOS (BOUNDARY TESTING)
// ============================================================================
TestHelper::section('5. Límites Numéricos y Casos Extremos (Boundary Testing)');

// 5.1 Límites en Creaciones
// Precio: límite inferior (0 y negativos rechazados; 1 centavo válido)
$errPriceZero = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['precio' => 0]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errPriceZero = true;
}
TestHelper::assertTrue($errPriceZero, 'Creación con precio 0 rechazada con InvalidArgumentException (422)');

$errPriceNeg = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['precio' => -100]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errPriceNeg = true;
}
TestHelper::assertTrue($errPriceNeg, 'Creación con precio negativo rechazada (422)');

$errPriceMax = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['precio' => 10000000]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errPriceMax = true;
}
TestHelper::assertTrue($errPriceMax, 'Creación con precio > $99,999.99 MXN rechazada (422)');

// Precio = 1 centavo y Precio = 9,999,999 centavos son válidos
$validLowPrice = $creacionService->createCreation(array_merge($tempStockCreationData, [
    'nombre' => 'Pieza Mínimo Precio',
    'precio' => 1 // 1 centavo
]), null, $adminUser);
TestHelper::assertSame(1, (int)$validLowPrice['precio'], 'Precio mínimo permitido: 1 centavo');
$creacionRepo->softDelete((int)$validLowPrice['id']);

$validHighPrice = $creacionService->createCreation(array_merge($tempStockCreationData, [
    'nombre' => 'Pieza Máximo Precio',
    'precio' => 9999999 // $99,999.99
]), null, $adminUser);
TestHelper::assertSame(9999999, (int)$validHighPrice['precio'], 'Precio máximo permitido: 9,999,999 centavos ($99,999.99)');
$creacionRepo->softDelete((int)$validHighPrice['id']);

// Costo de materiales: límites (0 a 9,999,999)
$errCostNeg = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['costo_materiales' => -1]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errCostNeg = true;
}
TestHelper::assertTrue($errCostNeg, 'Costo de materiales negativo rechazado (422)');

$errCostMax = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['costo_materiales' => 10000000]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errCostMax = true;
}
TestHelper::assertTrue($errCostMax, 'Costo de materiales > 9,999,999 centavos rechazado (422)');

// Horas de tejido: límites (0.0 a 500.0)
$errHorasNeg = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['horas_tejido' => -0.5]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errHorasNeg = true;
}
TestHelper::assertTrue($errHorasNeg, 'Horas de tejido negativas rechazadas (422)');

$errHorasMax = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['horas_tejido' => 500.1]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errHorasMax = true;
}
TestHelper::assertTrue($errHorasMax, 'Horas de tejido > 500.0 rechazadas (422)');

// Stock: límites (0 a 10000)
$errStockNeg = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['cantidad_stock' => -1]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errStockNeg = true;
}
TestHelper::assertTrue($errStockNeg, 'Stock negativo rechazado (422)');

$errStockMax = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['cantidad_stock' => 10001]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errStockMax = true;
}
TestHelper::assertTrue($errStockMax, 'Stock > 10,000 rechazado (422)');

// Límites de Dimensiones (2 a 100 caracteres)
$valid100Dim = $creacionService->createCreation(array_merge($tempStockCreationData, [
    'nombre'      => 'Pieza Dim Max',
    'dimensiones' => str_repeat('D', 100)
]), null, $adminUser);
TestHelper::assertSame(100, mb_strlen($valid100Dim['dimensiones']), 'Dimensiones con 100 caracteres exactos aceptadas');
$creacionRepo->softDelete((int)$valid100Dim['id']);

$errDimLong = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['dimensiones' => str_repeat('D', 101)]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errDimLong = true;
}
TestHelper::assertTrue($errDimLong, 'Dimensiones con 101 caracteres rechazadas (> 100)');

// Límites de Material (3 a 80 caracteres)
$valid80Mat = $creacionService->createCreation(array_merge($tempStockCreationData, [
    'nombre'   => 'Pieza Mat Max',
    'material' => str_repeat('M', 80)
]), null, $adminUser);
TestHelper::assertSame(80, mb_strlen($valid80Mat['material']), 'Material con 80 caracteres exactos aceptado');
$creacionRepo->softDelete((int)$valid80Mat['id']);

$errMatLong = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['material' => str_repeat('M', 81)]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errMatLong = true;
}
TestHelper::assertTrue($errMatLong, 'Material con 81 caracteres rechazado (> 80)');

// Límites de Categoría (2 a 50 caracteres)
$errCatLong = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['categoria' => str_repeat('C', 51)]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errCatLong = true;
}
TestHelper::assertTrue($errCatLong, 'Categoría con 51 caracteres rechazada (> 50)');

// Límites de Descripción (hasta 2000 caracteres)
$errDescLong = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['descripcion' => str_repeat('E', 2001)]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errDescLong = true;
}
TestHelper::assertTrue($errDescLong, 'Descripción con 2001 caracteres rechazada (> 2000)');

// Longitudes de strings en Creación (mb_strlen)
$errNameShort = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['nombre' => 'A']), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errNameShort = true;
}
TestHelper::assertTrue($errNameShort, 'Nombre con 1 carácter rechazado (< 2)');

$errNameLong = false;
try {
    $creacionService->createCreation(array_merge($tempStockCreationData, ['nombre' => str_repeat('X', 101)]), null, $adminUser);
} catch (\InvalidArgumentException $e) {
    $errNameLong = true;
}
TestHelper::assertTrue($errNameLong, 'Nombre con 101 caracteres rechazado (> 100)');

// 5.2 Límites en Pedidos
// Cantidad: límites (1 a 1000)
$errOrderQtyZero = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Test Boundary',
        'cliente_contacto' => '5512345678',
        'cantidad'         => 0,
    ]);
} catch (\InvalidArgumentException $e) {
    $errOrderQtyZero = true;
}
TestHelper::assertTrue($errOrderQtyZero, 'Pedido con cantidad 0 rechazado (422)');

$errOrderQtyNeg = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Test Boundary',
        'cliente_contacto' => '5512345678',
        'cantidad'         => -5,
    ]);
} catch (\InvalidArgumentException $e) {
    $errOrderQtyNeg = true;
}
TestHelper::assertTrue($errOrderQtyNeg, 'Pedido con cantidad negativa rechazado (422)');

$errOrderQtyMax = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Test Boundary',
        'cliente_contacto' => '5512345678',
        'cantidad'         => 1001,
    ]);
} catch (\InvalidArgumentException $e) {
    $errOrderQtyMax = true;
}
TestHelper::assertTrue($errOrderQtyMax, 'Pedido con cantidad > 1000 rechazado (422)');

// Cliente nombre: 1 carácter rechazado, 101 caracteres rechazados, 100 válido
$errClientShort = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'J',
        'cliente_contacto' => '5512345678',
        'cantidad'         => 1,
    ]);
} catch (\InvalidArgumentException $e) {
    $errClientShort = true;
}
TestHelper::assertTrue($errClientShort, 'Nombre de cliente con 1 carácter rechazado (422)');

$errClientLong = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => str_repeat('Z', 101),
        'cliente_contacto' => '5512345678',
        'cantidad'         => 1,
    ]);
} catch (\InvalidArgumentException $e) {
    $errClientLong = true;
}
TestHelper::assertTrue($errClientLong, 'Nombre de cliente con 101 caracteres rechazado (422)');

// Contacto: < 3 caracteres rechazado
$errContactShort = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Cliente Válido',
        'cliente_contacto' => '12',
        'cantidad'         => 1,
    ]);
} catch (\InvalidArgumentException $e) {
    $errContactShort = true;
}
TestHelper::assertTrue($errContactShort, 'Contacto de cliente con 2 caracteres rechazado (422)');

// Notas: > 1000 caracteres rechazadas
$errNotesLong = false;
try {
    $pedidoService->requestPublicOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Cliente Válido',
        'cliente_contacto' => '5512345678',
        'cantidad'         => 1,
        'notas'            => str_repeat('N', 1001),
    ]);
} catch (\InvalidArgumentException $e) {
    $errNotesLong = true;
}
TestHelper::assertTrue($errNotesLong, 'Notas de pedido con 1001 caracteres rechazadas (422)');

// Fecha de entrega en encargo manual: formato YYYY-MM-DD
$errDateInvalid = false;
try {
    $pedidoService->createManualOrder([
        'creacion_id'      => 1,
        'cliente_nombre'   => 'Cliente Fecha Mal',
        'cliente_contacto' => '5512345678',
        'cantidad'         => 1,
        'fecha_entrega'    => '15-10-2026', // Formato DD-MM-YYYY no permitido
    ], $adminUser);
} catch (\InvalidArgumentException $e) {
    $errDateInvalid = true;
}
TestHelper::assertTrue($errDateInvalid, 'Fecha de entrega con formato no YYYY-MM-DD rechazada (422)');

// ============================================================================
// SECCIÓN 6: PRUEBAS DE INTEGRACIÓN HTTP EN VIVO CONTRA SERVIDOR LOCAL
// ============================================================================
TestHelper::section('6. Pruebas de Integración HTTP en Vivo contra Servidor Local');

$getErrMsg = function(array $res): string {
    return (string)($res['json']['error']['mensaje'] ?? $res['json']['mensaje'] ?? $res['body']);
};

// 6.1 HTTP POST /api/pedidos/solicitar.php con manipulación de precio en payload JSON
// Intentamos enviar precio_final: 50 en la solicitud pública para creación #3 (Ajolote Rosado Pastel, precio = 32000 / $320.00 MXN)
$reqPayload61 = json_encode([
    'creacion_id'      => 3, // Ajolote Rosado Pastel (precio = 32000 / $320.00 MXN)
    'cliente_nombre'   => 'Cliente Tamper HTTP',
    'cliente_contacto' => '5533445566',
    'cantidad'         => 2,
    'precio'           => 10,  // Manipulación!
    'precio_final'     => 50,  // Manipulación!
]);
$httpPriceTamperRes = TestHelper::curl('POST', $baseUrl . '/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], $reqPayload61);
$logHttpTransaction('1. Intento de Manipulación de Precio en Checkout (POST /api/pedidos/solicitar.php)', $httpPriceTamperRes, $reqPayload61);

TestHelper::assertSame(201, $httpPriceTamperRes['status'], 'POST /api/pedidos/solicitar.php responde 201 Created');
TestHelper::assertTrue((bool)($httpPriceTamperRes['json']['exito'] ?? false), 'Respuesta indica exito = true');
$httpTamperOrderId = (int)($httpPriceTamperRes['json']['datos']['id'] ?? 0);
TestHelper::assert($httpTamperOrderId > 0, 'ID de pedido generado > 0');
TestHelper::assertSame(64000, (int)($httpPriceTamperRes['json']['datos']['precio_final'] ?? 0), 'HTTP: Servidor ignora manipulación y calcula 32000 * 2 = 64000 centavos');
TestHelper::assertSame('$640.00 MXN', (string)($httpPriceTamperRes['json']['datos']['precio_final_formateado'] ?? ''), 'HTTP: precio_final_formateado es $640.00 MXN');

// 6.2 HTTP POST /api/pedidos/solicitar.php con stock insuficiente -> HTTP 409
// Usamos creación #2 (Mini Suculenta en Maceta, es_sobre_encargo = 0, stock físico = 12) solicitando 20 unidades
$reqPayload62 = json_encode([
    'creacion_id'      => 2,
    'cliente_nombre'   => 'Cliente Excedido',
    'cliente_contacto' => '5599887766',
    'cantidad'         => 20, // Excede existencias (12)
]);
$httpOutOfStockRes = TestHelper::curl('POST', $baseUrl . '/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], $reqPayload62);
$logHttpTransaction('2. Solicitud con Exceso de Stock Físico (POST /api/pedidos/solicitar.php)', $httpOutOfStockRes, $reqPayload62);

TestHelper::assertSame(409, $httpOutOfStockRes['status'], 'POST /api/pedidos/solicitar.php con exceso de stock responde HTTP 409 Conflict');
TestHelper::assertStringContains('Stock insuficiente', $getErrMsg($httpOutOfStockRes), 'Mensaje HTTP informa stock insuficiente');

// 6.3 HTTP POST /api/pedidos/solicitar.php con cantidad 0 -> HTTP 422
$reqPayload63 = json_encode([
    'creacion_id'      => 2,
    'cliente_nombre'   => 'Cliente Cantidad Cero',
    'cliente_contacto' => '5511223344',
    'cantidad'         => 0,
]);
$httpZeroQtyRes = TestHelper::curl('POST', $baseUrl . '/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], $reqPayload63);
$logHttpTransaction('3. Validación de Cantidad Cero (POST /api/pedidos/solicitar.php)', $httpZeroQtyRes, $reqPayload63);

TestHelper::assertSame(422, $httpZeroQtyRes['status'], 'POST /api/pedidos/solicitar.php con cantidad 0 responde HTTP 422');

// 6.4 HTTP POST /api/pedidos/solicitar.php con caracteres UTF-8 4-byte (emojis)
$reqPayload64 = json_encode([
    'creacion_id'      => 3,
    'cliente_nombre'   => 'Valeria Gómez 🧸💖',
    'cliente_contacto' => '3312345678',
    'cantidad'         => 1,
    'notas'            => 'Edición especial nórdica con hilo dorado ✨🧶 y moño rosa 🎀',
], JSON_UNESCAPED_UNICODE);
$httpEmojiOrderRes = TestHelper::curl('POST', $baseUrl . '/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], $reqPayload64);
$logHttpTransaction('4. Registro de Pedido con Emojis 4-Byte (POST /api/pedidos/solicitar.php)', $httpEmojiOrderRes, $reqPayload64);

TestHelper::assertSame(201, $httpEmojiOrderRes['status'], 'POST /api/pedidos/solicitar.php con emojis 4-byte responde HTTP 201 Created');
$httpEmojiOrderId = (int)($httpEmojiOrderRes['json']['datos']['id'] ?? 0);
TestHelper::assert($httpEmojiOrderId > 0, 'Pedido con emojis registrado con ID válido');

// 6.5 HTTP POST /api/pedidos/cambiar-estado.php - Estado 'En Proceso'
$reqPayload65 = json_encode([
    'id'            => $httpEmojiOrderId,
    'estado_pedido' => 'En Proceso',
    'estado_pago'   => 'Anticipo 50%',
]);
$httpUpdateStatusRes = TestHelper::curl('POST', $baseUrl . '/api/pedidos/cambiar-estado.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
], $reqPayload65);
$logHttpTransaction('5. Cambio de Estado a En Proceso (POST /api/pedidos/cambiar-estado.php)', $httpUpdateStatusRes, $reqPayload65);

TestHelper::assertSame(200, $httpUpdateStatusRes['status'], 'POST /api/pedidos/cambiar-estado.php responde HTTP 200 OK');
TestHelper::assertSame('En Proceso', (string)($httpUpdateStatusRes['json']['datos']['estado_pedido'] ?? ''), 'Estado de pedido actualizado a En Proceso');

// 6.6 HTTP POST /api/pedidos/cambiar-estado.php con estado inválido -> HTTP 422
$reqPayload66 = json_encode([
    'id'            => $httpEmojiOrderId,
    'estado_pedido' => 'EstadoInexistente',
]);
$httpInvalidStatusRes = TestHelper::curl('POST', $baseUrl . '/api/pedidos/cambiar-estado.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
], $reqPayload66);
$logHttpTransaction('6. Estado de Pedido Inválido (POST /api/pedidos/cambiar-estado.php)', $httpInvalidStatusRes, $reqPayload66);

TestHelper::assertSame(422, $httpInvalidStatusRes['status'], 'POST /api/pedidos/cambiar-estado.php con estado inválido responde HTTP 422');

// 6.7 Cancelación Idempotente vía HTTP: POST /api/pedidos/cancelar.php
// Cancelación 1: Éxito
$reqPayload67 = json_encode(['id' => $httpTamperOrderId]);
$httpCancelRes1 = TestHelper::curl('POST', $baseUrl . '/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
], $reqPayload67);
$logHttpTransaction('7. Cancelación Exitosa con Restitución (POST /api/pedidos/cancelar.php)', $httpCancelRes1, $reqPayload67);

TestHelper::assertSame(200, $httpCancelRes1['status'], 'POST /api/pedidos/cancelar.php responde HTTP 200 OK');
TestHelper::assertSame(2, (int)($httpCancelRes1['json']['datos']['unidades_restituidas'] ?? 0), 'HTTP: Se restituyeron exactamente 2 unidades al stock');

// Cancelación 2: Idempotencia -> HTTP 409 Conflict
$httpCancelRes2 = TestHelper::curl('POST', $baseUrl . '/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
], $reqPayload67);
$logHttpTransaction('8. Re-intento de Cancelación Idempotente (POST /api/pedidos/cancelar.php)', $httpCancelRes2, $reqPayload67);

TestHelper::assertSame(409, $httpCancelRes2['status'], 'HTTP: Re-intento de cancelación responde HTTP 409 Conflict (idempotencia)');
TestHelper::assertStringContains('ya se encuentra cancelado', $getErrMsg($httpCancelRes2), 'Mensaje confirma que el pedido ya estaba cancelado');

// Cancelar el pedido emoji creado en 6.4 para restaurar stock
TestHelper::curl('POST', $baseUrl . '/api/pedidos/cancelar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
], json_encode(['id' => $httpEmojiOrderId]));

// 6.8 HTTP POST /api/creaciones/crear.php con emojis de 4 bytes en payload JSON
$reqPayload68 = json_encode([
    'nombre'           => 'Gatito Nórdico 🐱🧶',
    'categoria'        => 'Fantasía',
    'material'         => '100% Algodón Pima',
    'dimensiones'      => '14 cm',
    'precio'           => '320.00',
    'costo_materiales' => '75.00',
    'cantidad_stock'   => 3,
    'horas_tejido'     => '2.5',
    'descripcion'      => 'Gatito suave y tierno tejido con técnica nórdica ✨🐾',
], JSON_UNESCAPED_UNICODE);
$httpCreateCreationRes = TestHelper::curl('POST', $baseUrl . '/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
], $reqPayload68);
$logHttpTransaction('9. Creación de Pieza con Emojis 4-Byte (POST /api/creaciones/crear.php)', $httpCreateCreationRes, $reqPayload68);

TestHelper::assertSame(201, $httpCreateCreationRes['status'], 'POST /api/creaciones/crear.php con emojis responde HTTP 201 Created');
$httpNewCreationId = (int)($httpCreateCreationRes['json']['datos']['id'] ?? 0);
TestHelper::assert($httpNewCreationId > 0, 'Nueva creación con emojis creada');

// 6.9 Consultar vía HTTP GET con búsqueda por emoji 🐱
$httpSearchEmojiRes = TestHelper::curl('GET', $baseUrl . '/api/creaciones/index.php?busqueda=' . rawurlencode('🐱'));
$logHttpTransaction('10. Búsqueda de Catálogo por Emoji 🐱 (GET /api/creaciones/index.php)', $httpSearchEmojiRes);

TestHelper::assertSame(200, $httpSearchEmojiRes['status'], 'GET /api/creaciones/index.php con emoji responde HTTP 200 OK');
TestHelper::assert($httpSearchEmojiRes['json']['paginacion']['total_items'] >= 1, 'Búsqueda HTTP por emoji 🐱 retorna al menos 1 resultado');

// 6.10 Aislamiento multi-artesano en listado de pedidos HTTP: GET /api/pedidos/index.php con token de artesana_ana
$httpArtisanOrdersRes = TestHelper::curl('GET', $baseUrl . '/api/pedidos/index.php', [
    'Authorization: Bearer ' . $artisanToken,
]);
$logHttpTransaction('11. Aislamiento Multi-Artesano de Pedidos (GET /api/pedidos/index.php)', $httpArtisanOrdersRes);

TestHelper::assertSame(200, $httpArtisanOrdersRes['status'], 'GET /api/pedidos/index.php con token artesana responde HTTP 200 OK');
$artisanOrdersList = $httpArtisanOrdersRes['json']['datos'] ?? [];
$allBelongToArtisan = true;
foreach ($artisanOrdersList as $ord) {
    if ((int)($ord['creacion']['artesano_id'] ?? 0) !== (int)$artisanUser['id']) {
        $allBelongToArtisan = false;
        break;
    }
}
TestHelper::assertTrue($allBelongToArtisan, 'Aislamiento estricto: el 100% de los pedidos listados pertenecen a piezas de la artesana autenticada');

// Limpieza de creación HTTP creada
TestHelper::curl('POST', $baseUrl . '/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
], json_encode(['id' => $httpNewCreationId]));

// Limpiar pedidos en baja lógica y restaurar stock canónico de creación #3
$pedidoRepo->softDelete($httpTamperOrderId);
$pedidoRepo->softDelete($httpEmojiOrderId);
$pdo->prepare('UPDATE creaciones SET cantidad_stock = 0 WHERE id = 3')->execute();

// ============================================================================
// RESUMEN CONSOLIDADO
// ============================================================================
exit(TestHelper::summary());
