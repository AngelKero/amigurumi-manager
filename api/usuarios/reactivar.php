<?php
/**
 * REST Controller: Reactivate Deactivated User
 * Endpoint: POST /api/usuarios/reactivar.php
 * Algodón Nórdico Design System
 * 
 * Permite al administrador reactivar una cuenta dada de baja de forma lógica (activo = 1).
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
RoleGuard::adminOnly();

// 4. Extracción de ID a reactivar
$id = (int)Request::input('id', 0);

// 5. Delegación a la capa de servicio
try {
    $usuarioService = new UsuarioService();
    $result = $usuarioService->reactivateUser($id);

    Response::success(
        $result,
        "Usuario '{$result['username']}' reactivado exitosamente en la plataforma.",
        200
    );
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
