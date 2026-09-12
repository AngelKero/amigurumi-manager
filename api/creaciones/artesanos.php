<?php
/**
 * REST Controller: Public List of Active Artisans for Catalog Filtering (ADR-014)
 * Endpoint: GET /api/creaciones/artesanos.php
 * Algodón Nórdico Design System
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Services\CreacionService;

// 1. Manejo centralizado de preflight CORS
Response::handleCors();

// 2. Restricción de método HTTP
if (Request::method() !== 'GET') {
    Response::error('Método HTTP no permitido. Se requiere GET.', 405);
}

// 3. Ejecución de la consulta de artesanos con piezas activas
try {
    $creacionService = new CreacionService();
    $artesanos = $creacionService->getActiveArtisans();

    Response::success($artesanos, 'Lista de artesanos con creaciones activas obtenida exitosamente.', 200);
} catch (RuntimeException $e) {
    $code = (int)$e->getCode() ?: 500;
    Response::error($e->getMessage(), $code);
}
