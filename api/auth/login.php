<?php
/**
 * REST Controller: Login & Bearer Token Issuance
 * Endpoint: POST /api/auth/login.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Extracción de credenciales desde JSON o formulario
$username = (string)Request::input('username', '');
$password = (string)Request::input('password', '');

// 4. Delegación a la capa de servicio
try {
    $authService = new AuthService();
    $result = $authService->authenticate($username, $password);

    Response::success($result, 'Autenticación exitosa. Token emitido.', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    Response::error($e->getMessage(), 401);
}
