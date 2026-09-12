<?php
/**
 * REST Controller: Current Authenticated User Profile
 * Endpoint: GET /api/auth/me.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthGuard;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'GET') {
    Response::error('Método HTTP no permitido. Se requiere GET.', 405);
}

// 3. Verificación de autenticación vía Bearer token
$user = AuthGuard::handle();

// 4. Retorno del perfil seguro
Response::success($user, 'Perfil de usuario recuperado exitosamente.', 200);
