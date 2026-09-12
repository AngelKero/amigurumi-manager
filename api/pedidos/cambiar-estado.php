<?php
/**
 * REST Controller: Update Order Status & Payment Status
 * Endpoint: POST /api/pedidos/cambiar-estado.php
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

// 4. Extracción de parámetros
$id = (int)Request::input('id', 0);
$estadoPedido = trim((string)Request::input('estado_pedido', ''));
$estadoPago = Request::input('estado_pago', null);
if ($estadoPago !== null) {
    $estadoPago = trim((string)$estadoPago);
}

if ($id <= 0) {
    Response::error('El parámetro id es obligatorio y debe ser un entero positivo.', 422);
}
if ($estadoPedido === '') {
    Response::error('El parámetro estado_pedido es obligatorio.', 422);
}

// 5. Delegación a la capa de servicio con salvaguarda IDOR
try {
    $pedidoService = new PedidoService();
    $result = $pedidoService->updateOrderStatus($id, $estadoPedido, $estadoPago, $currentUser);

    Response::json([
        'exito'   => true,
        'mensaje' => 'Estado del pedido actualizado exitosamente.',
        'datos'   => $result,
    ], 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
