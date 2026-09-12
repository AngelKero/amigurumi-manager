<?php
/**
 * REST Controller: Public Paginated Catalog of Creations
 * Endpoint: GET /api/creaciones/index.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Services\CreacionService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'GET') {
    Response::error('Método HTTP no permitido. Se requiere GET.', 405);
}

// 3. Ejecución de la consulta de catálogo
try {
    $creacionService = new CreacionService();
    $result = $creacionService->getCatalog(Request::query());

    Response::json([
        'exito'      => true,
        'mensaje'    => 'Catálogo de creaciones obtenido exitosamente.',
        'datos'      => $result['datos'],
        'paginacion' => $result['paginacion'],
    ], 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
