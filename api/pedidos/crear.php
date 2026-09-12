<?php
/**
 * REST Controller: Manual Artisan Commission Order Creation
 * Endpoint: POST /api/pedidos/crear.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Middleware\RoleGuard;
use App\Services\PedidoService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Control de acceso RBAC: artesanos o administradores
$currentUser = RoleGuard::artisanOrAdmin();

// 4. Extracción de datos del encargo
$input = [
    'creacion_id'      => Request::input('creacion_id', null),
    'cliente_nombre'   => Request::input('cliente_nombre', ''),
    'cliente_contacto' => Request::input('cliente_contacto', ''),
    'cantidad'         => Request::input('cantidad', 1),
    'fecha_entrega'    => Request::input('fecha_entrega', null),
    'estado_pedido'    => Request::input('estado_pedido', 'Pendiente'),
    'estado_pago'      => Request::input('estado_pago', 'Pendiente'),
    'notas'            => Request::input('notas', null),
];

// 5. Delegación al servicio de pedidos con salvaguarda IDOR
try {
    $pedidoService = new PedidoService();
    $result = $pedidoService->createManualOrder($input, $currentUser);

    Response::json([
        'exito'   => true,
        'mensaje' => 'Encargo artesanal registrado exitosamente.',
        'datos'   => $result,
    ], 201);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
