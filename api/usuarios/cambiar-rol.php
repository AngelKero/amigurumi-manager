<?php
/**
 * REST Controller: Change User Role with Root Admin Safeguard
 * Endpoint: POST /api/usuarios/cambiar-rol.php
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
RoleGuard::adminOnly();

// 4. Extracción de datos del cuerpo (JSON o formulario)
$id = (int)Request::input('id', 0);
$rol = (string)Request::input('rol', '');

// 5. Delegación a la capa de servicio
try {
    $usuarioService = new UsuarioService();
    $updatedUser = $usuarioService->updateRole($id, $rol);

    Response::success($updatedUser, 'Rol de usuario actualizado exitosamente.', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
