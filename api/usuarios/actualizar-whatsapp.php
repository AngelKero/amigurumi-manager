<?php
/**
 * REST Controller: Update Artisan WhatsApp Contact
 * Endpoint: POST /api/usuarios/actualizar-whatsapp.php
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

// 3. Control de acceso: artesanos y administradores (el servicio aplica IDOR)
$currentUser = RoleGuard::artisanOrAdmin();

// 4. Extracción de datos del cuerpo
$id = (int)Request::input('id', 0);
$whatsappRaw = Request::input('whatsapp', null);
$whatsapp = $whatsappRaw !== null ? (string)$whatsappRaw : null;

// 5. Delegación a la capa de servicio
try {
    $usuarioService = new UsuarioService();
    $updated = $usuarioService->updateWhatsapp($id, $whatsapp, $currentUser);

    Response::success($updated, 'WhatsApp de contacto actualizado exitosamente.', 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
