<?php
/**
 * REST Controller: Detailed Creation Technical Sheet
 * Endpoint: GET /api/creaciones/detalle.php?id=X
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

// 3. Extracción y validación del identificador
$id = (int)Request::get('id', 0);
if ($id <= 0) {
    Response::error('El parámetro "id" de la creación es obligatorio y debe ser un número entero positivo.', 422);
}

// 4. Recuperación del detalle
try {
    $creacionService = new CreacionService();
    $creacion = $creacionService->getCreationById($id, true);

    Response::success($creacion, 'Detalle de creación recuperado exitosamente.', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
