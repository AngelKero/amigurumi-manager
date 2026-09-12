<?php
/**
 * Bearer Token Authentication Guard Middleware (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Intercepta las peticiones dirigidas a rutas protegidas, valida la presencia
 * y firma criptográfica del Bearer token e inyecta el usuario autenticado
 * en la abstracción Request para el ciclo de vida de la petición.
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthGuard {
    /**
     * Valida la autenticación de la petición activa.
     * Si no está autenticada, emite un error JSON 401 y detiene la ejecución.
     * 
     * @param AuthService|null $authService Servicio opcional para pruebas
     * @return array Datos del usuario autenticado
     */
    public static function handle(?AuthService $authService = null): array {
        // 1. Si el usuario ya fue autenticado en este ciclo de petición, reutilizarlo
        $cachedUser = Request::user();
        if ($cachedUser !== null) {
            return $cachedUser;
        }

        // 2. Extraer el Bearer token de la cabecera Authorization
        $token = Request::bearerToken();
        if ($token === null || trim($token) === '') {
            Response::error('Token de autenticación no proporcionado. Se requiere cabecera "Authorization: Bearer <token>".', 401);
            return [];
        }

        // 3. Validar token y verificar existencia del usuario en la base de datos
        $service = $authService ?? new AuthService();
        $user = $service->validateToken($token);

        if ($user === null) {
            Response::error('Token de autenticación inválido, manipulado o expirado.', 401);
            return [];
        }

        // 4. Inyectar usuario autenticado en la petición
        Request::setUser($user);

        return $user;
    }
}
