<?php
/**
 * Role-Based Access Control (RBAC) Guard Middleware (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Verifica que el usuario autenticado cuente con los roles y privilegios
 * necesarios ('admin', 'artesano', 'asistente') antes de ejecutar acciones de negocio.
 * Emite HTTP 403 Forbidden si el rol es insuficiente.
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class RoleGuard {
    /**
     * Verifica que el usuario cuente con al menos uno de los roles permitidos.
     * 
     * @param string[] $allowedRoles Lista de roles con permiso (ej. ['admin', 'artesano'])
     * @return array Datos del usuario autenticado y verificado
     */
    public static function hasRole(array $allowedRoles): array {
        $user = Request::user() ?? AuthGuard::handle();

        $userRole = (string)($user['rol'] ?? '');

        if (!in_array($userRole, $allowedRoles, true)) {
            $requiredRolesStr = implode(', ', $allowedRoles);
            Response::error(
                "No tienes permisos suficientes para realizar esta acción. Roles requeridos: [{$requiredRolesStr}]. Tu rol actual es '{$userRole}'.",
                403
            );
            return [];
        }

        return $user;
    }

    /**
     * Requiere exclusivamente el rol de Administrador ('admin').
     * 
     * @return array Datos del usuario
     */
    public static function adminOnly(): array {
        return self::hasRole(['admin']);
    }

    /**
     * Requiere privilegios de Creador/Artesano o Administrador ('admin', 'artesano').
     * 
     * @return array Datos del usuario
     */
    public static function artisanOrAdmin(): array {
        return self::hasRole(['admin', 'artesano']);
    }
}
