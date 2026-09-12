<?php
/**
 * REST Controller: In-Situ Quick Stock Adjustment
 * Endpoint: POST /api/creaciones/ajustar-stock.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Middleware\RoleGuard;
use App\Services\CreacionService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Control de acceso RBAC: solo artesanos y administradores
$currentUser = RoleGuard::artisanOrAdmin();

// 4. Extracción y validación de parámetros
$id = (int)Request::input('id', 0);
if ($id <= 0) {
    Response::error('El identificador "id" de la creación es obligatorio.', 422);
}

if (!isset($_POST['cantidad_stock']) && !isset(Request::json()['cantidad_stock'])) {
    Response::error('El campo "cantidad_stock" es obligatorio.', 422);
}

$newStock = (int)Request::input('cantidad_stock', 0);

// 5. Delegación a la capa de servicio con salvaguarda IDOR
try {
    $creacionService = new CreacionService();
    $result = $creacionService->adjustStock($id, $newStock, $currentUser);

    Response::success($result, 'Stock de la creación actualizado exitosamente.', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
