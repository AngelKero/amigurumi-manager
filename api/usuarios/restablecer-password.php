<?php
/**
 * REST Controller: Reset / Restore User Password
 * Endpoint: POST /api/usuarios/restablecer-password.php
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

// 4. Extracción de datos del cuerpo
$id = (int)Request::input('id', 0);
$newPassword = Request::input('nueva_password', null);
if ($newPassword === null) {
    // También admitir alias 'password'
    $newPassword = Request::input('password', null);
}

// 5. Delegación a la capa de servicio
try {
    $usuarioService = new UsuarioService();
    $result = $usuarioService->resetPassword($id, $newPassword ? (string)$newPassword : null);

    Response::success($result, $result['mensaje'], 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
