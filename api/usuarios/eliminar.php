<?php
/**
 * REST Controller: Delete Platform User
 * Endpoint: POST /api/usuarios/eliminar.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Middleware\RoleGuard;
use App\Services\UsuarioService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Control de acceso estricto: solo administradores
$currentUser = RoleGuard::adminOnly();
$currentUserId = (int)($currentUser['id'] ?? 0);

// 4. Extracción de ID a eliminar
$id = (int)Request::input('id', 0);

// 5. Delegación a la capa de servicio con salvaguardas
try {
    $usuarioService = new UsuarioService();
    $usuarioService->deleteUser($id, $currentUserId);

    Response::success(
        ['id' => $id],
        'Usuario eliminado exitosamente de la plataforma.',
        200
    );
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
