<?php
/**
 * REST Controller: List Orders & Commissions with Multi-Artisan Isolation
 * Endpoint: GET /api/pedidos/index.php
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
if (Request::method() !== 'GET') {
    Response::error('Método HTTP no permitido. Se requiere GET.', 405);
}

// 3. Control de acceso RBAC: artesanos o administradores
$currentUser = RoleGuard::artisanOrAdmin();

// 4. Ejecución de la consulta con aislamiento de artesano
try {
    $pedidoService = new PedidoService();
    $result = $pedidoService->listOrders(Request::query(), $currentUser);

    Response::json([
        'exito'      => true,
        'mensaje'    => 'Listado de pedidos obtenido exitosamente.',
        'datos'      => $result['datos'],
        'paginacion' => $result['paginacion'],
    ], 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
