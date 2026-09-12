<?php
/**
 * REST Controller: Soft Delete Creation from Catalog (ADR-008 Zero Unlink)
 * Endpoint: POST /api/creaciones/eliminar.php
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

// 4. Extracción y validación del identificador
$id = (int)Request::input('id', 0);
if ($id <= 0) {
    Response::error('El identificador "id" de la creación es obligatorio para la baja lógica.', 422);
}

// 5. Delegación a la capa de servicio con salvaguarda IDOR
try {
    $creacionService = new CreacionService();
    $result = $creacionService->deleteCreation($id, $currentUser);

    Response::success($result, 'Creación retirada exitosamente del catálogo (baja lógica).', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
