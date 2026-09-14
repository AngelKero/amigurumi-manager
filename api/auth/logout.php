<?php
/**
 * REST Controller: Logout & Token Acknowledgment
 * Endpoint: POST /api/auth/logout.php
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

// 3. Revocación en servidor del token mediante denylist por jti (H-002)
//    Best-effort e idempotente: si no viaja token válido, solo se informa el descarte del cliente.
$revoked = false;
$token = Request::bearerToken();
if ($token !== null && trim($token) !== '') {
    $authService = new AuthService();
    $revoked = $authService->revokeToken($token);
}

// 4. Confirmación de cierre de sesión
Response::success(
    ['revocado_en_servidor' => $revoked],
    'Sesión cerrada exitosamente. Descarte el token del cliente.',
    200
);
