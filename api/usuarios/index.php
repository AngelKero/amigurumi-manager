<?php
/**
 * REST Controller: Creator & User Directory
 * Endpoint: GET /api/usuarios/index.php
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
if (Request::method() !== 'GET') {
    Response::error('Método HTTP no permitido. Se requiere GET.', 405);
}

// 3. Control de acceso estricto: solo administradores
RoleGuard::adminOnly();

// 4. Extracción de parámetros de paginación y estado
$page = (int)Request::query('pagina', 1);
$limit = (int)Request::query('limite', 20);
$estado = strtolower(trim((string)Request::query('estado', 'activos')));

$onlyActive = match ($estado) {
    'inactivos' => false,
    'todos'     => null,
    default     => true,
};

// 5. Delegación a la capa de servicio
try {
    $usuarioService = new UsuarioService();
    $result = $usuarioService->listUsers($page, $limit, $onlyActive);

    Response::success(
        $result['usuarios'],
        'Directorio de creadores obtenido exitosamente.',
        200,
        ['paginacion' => $result['paginacion']]
    );
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    Response::error($e->getMessage(), (int)$e->getCode() ?: 500);
}
