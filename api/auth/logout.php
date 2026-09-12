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

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Confirmación de cierre de sesión sin estado
Response::success(null, 'Sesión cerrada exitosamente. Descarte el token del cliente.', 200);
