<?php
/**
 * REST Controller: Change Own Password (Self-Service)
 * Endpoint: POST /api/auth/cambiar-password.php
 * Algodón Nórdico Design System
 * 
 * Permite a cualquier usuario autenticado (admin, artesano, asistente) cambiar
 * su propia contraseña verificando la clave actual y aplicando bcrypt a la nueva.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthGuard;
use App\Services\AuthService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Verificación de autenticación vía Bearer token (Cualquier rol activo)
$currentUser = AuthGuard::handle();

// 4. Extracción de parámetros
$body = Request::json();
$passwordActual = (string)($body['password_actual'] ?? $body['current_password'] ?? '');
$nuevaPassword = (string)($body['nueva_password'] ?? $body['password_nueva'] ?? $body['new_password'] ?? '');

// 5. Delegación a la capa de servicio
try {
    $service = new AuthService();
    $result = $service->changePassword((int)$currentUser['id'], $passwordActual, $nuevaPassword);

    Response::success(
        $result,
        'Contraseña actualizada exitosamente.',
        200
    );
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 422);
} catch (RuntimeException $e) {
    Response::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 400);
}
