<?php
/**
 * REST Controller: Public Client Order Request (Checkout Modal)
 * Endpoint: POST /api/pedidos/solicitar.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Services\PedidoService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Extracción de datos del cuerpo (soporta JSON y application/x-www-form-urlencoded)
$input = [
    'creacion_id'      => Request::input('creacion_id', null),
    'cliente_nombre'   => Request::input('cliente_nombre', ''),
    'cliente_contacto' => Request::input('cliente_contacto', ''),
    'cantidad'         => Request::input('cantidad', 1),
    'notas'            => Request::input('notas', null),
];

// 4. Delegación al servicio de pedidos con transacción atómica
try {
    $pedidoService = new PedidoService();
    $result = $pedidoService->requestPublicOrder($input);

    Response::json([
        'exito'   => true,
        'mensaje' => $result['mensaje'],
        'datos'   => $result,
    ], 201);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
