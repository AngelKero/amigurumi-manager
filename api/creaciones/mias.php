<?php
/**
 * REST Controller: Own Inventory for Artisan Panel (ADR-017)
 * Endpoint: GET /api/creaciones/mias.php?estado=activas|inactivas|todas
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
if (Request::method() !== 'GET') {
    Response::error('Método HTTP no permitido. Se requiere GET.', 405);
}

// 3. Control de acceso RBAC: solo artesanos y administradores
$currentUser = RoleGuard::artisanOrAdmin();

// 4. Delegación con scoping forzado por rol (anti-spoof)
try {
    $creacionService = new CreacionService();

    // 4a. Agregado exacto para los KPIs globales del panel (sin paginación)
    if (Request::input('resumen', '') === '1') {
        $summary = $creacionService->getOwnSummary(Request::query(), $currentUser);

        Response::json([
            'exito'   => true,
            'mensaje' => 'Resumen de inventario propio recuperado exitosamente.',
            'datos'   => $summary,
        ], 200);
    }

    $result = $creacionService->getOwnCreations(Request::query(), $currentUser);

    Response::json([
        'exito'      => true,
        'mensaje'    => 'Inventario propio recuperado exitosamente.',
        'datos'      => $result['datos'],
        'paginacion' => $result['paginacion'],
    ], 200);
} catch (InvalidArgumentException $e) {
    Response::error($e->getMessage(), 422);
} catch (RuntimeException $e) {
    Response::error($e->getMessage(), (int)$e->getCode() ?: 500);
}
