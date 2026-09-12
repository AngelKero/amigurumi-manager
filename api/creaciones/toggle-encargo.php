<?php
/**
 * REST Controller: Toggle On-Demand Commission Mode
 * Endpoint: POST /api/creaciones/toggle-encargo.php
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

if (!isset($_POST['es_sobre_encargo']) && !isset(Request::json()['es_sobre_encargo'])) {
    Response::error('El campo "es_sobre_encargo" es obligatorio.', 422);
}

$state = !empty(Request::input('es_sobre_encargo')) ? 1 : 0;

// 5. Delegación a la capa de servicio con salvaguarda IDOR
try {
    $creacionService = new CreacionService();
    $result = $creacionService->toggleCommission($id, $state, $currentUser);

    Response::success($result, 'Modalidad de encargo conmutada exitosamente.', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
