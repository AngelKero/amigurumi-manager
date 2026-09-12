<?php
/**
 * REST Controller: Register New Creator or Platform User
 * Endpoint: POST /api/usuarios/crear.php
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
$username = (string)Request::input('username', '');
$password = (string)Request::input('password', '');
$rol = (string)Request::input('rol', 'artesano');

// 5. Delegación a la capa de servicio
try {
    $usuarioService = new UsuarioService();
    $newUser = $usuarioService->createUser($username, $password, $rol);

    Response::success($newUser, 'Creador registrado exitosamente en la plataforma.', 201);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
