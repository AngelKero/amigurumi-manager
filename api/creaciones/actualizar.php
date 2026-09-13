<?php
/**
 * REST Controller: Update Creation in Catalog
 * Endpoint: POST /api/creaciones/actualizar.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Middleware\RoleGuard;
use App\Services\CreacionService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'POST') {
    Response::error('Método HTTP no permitido. Se requiere POST.', 405);
}

// 3. Control de acceso RBAC: solo artesanos y administradores
$currentUser = RoleGuard::artisanOrAdmin();

// 4. Extracción y validación del identificador
$id = (int)Request::input('id', 0);
if ($id <= 0) {
    Response::error('El identificador "id" de la creación es obligatorio para actualizar.', 422);
}

// 5. Extracción de datos del cuerpo (JSON o multipart)
$data = [
    'nombre'           => Request::input('nombre', ''),
    'categoria'        => Request::input('categoria', ''),
    'material'         => Request::input('material', ''),
    'dimensiones'      => Request::input('dimensiones', ''),
    'precio'           => Request::input('precio', null),
    'costo_materiales' => Request::input('costo_materiales', 0),
    'cantidad_stock'   => Request::input('cantidad_stock', 0),
    'horas_tejido'     => Request::input('horas_tejido', 0.0),
    'descripcion'      => Request::input('descripcion', null),
    'es_sobre_encargo' => Request::input('es_sobre_encargo', 0),
];

$fileData = Request::file('imagen');

// 6. Delegación a la capa de servicio con salvaguarda IDOR
try {
    $creacionService = new CreacionService();
    $updatedCreation = $creacionService->updateCreation($id, $data, $fileData, $currentUser);

    Response::success($updatedCreation, 'Creación actualizada exitosamente.', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    Response::error($e->getMessage(), (int)$e->getCode() ?: 500);
}
