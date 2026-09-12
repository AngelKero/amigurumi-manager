<?php
/**
 * User & Artisan Business Service (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Orquesta la lógica de negocio para la administración de usuarios, creadores y roles RBAC.
 * Aplica validaciones estrictas de nombres de usuario y contraseñas, comprobación de unicidad,
 * paginación estandarizada y salvaguarda inviolable para el Administrador Raíz (ID #1).
 */

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Utils\PaginationHelper;
use InvalidArgumentException;
use RuntimeException;

class UsuarioService {
    private UsuarioRepository $usuarioRepo;

    public function __construct(?UsuarioRepository $usuarioRepo = null) {
        $this->usuarioRepo = $usuarioRepo ?? new UsuarioRepository();
    }

    /**
     * Lista a todos los creadores y colaboradores con el conteo de creaciones y paginación.
     * 
     * @param int $page Página actual (>= 1)
     * @param int $limit Cantidad de usuarios por página (1-100)
     * @return array{usuarios: array, paginacion: array}
     */
    public function listUsers(int $page = 1, int $limit = 20): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;

        $totalUsers = $this->usuarioRepo->countAll();
        $users = $this->usuarioRepo->listAllWithCreationsCount($limit, $offset);

        $paginationEnvelope = PaginationHelper::build($totalUsers, $page, $limit);
        $pagination = $paginationEnvelope['paginacion'] ?? $paginationEnvelope;

        return [
            'usuarios'   => $users,
            'paginacion' => $pagination,
        ];
    }

    /**
     * Obtiene el perfil seguro de un usuario por su identificador primario.
     * 
     * @param int $id Identificador del usuario
     * @return array Datos seguros del usuario
     * @throws RuntimeException Si el usuario no existe (HTTP 404)
     */
    public function getUserById(int $id): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El identificador de usuario debe ser un número entero positivo.', 422);
        }

        $user = $this->usuarioRepo->findByIdSafe($id);
        if ($user === null) {
            throw new RuntimeException("El usuario con ID #{$id} no existe en el sistema.", 404);
        }

        return $user;
    }

    /**
     * Registra un nuevo creador o colaborador en la plataforma.
     * 
     * @param string $username Nombre de usuario (3-50 chars, alfanumérico)
     * @param string $password Contraseña en texto plano (>= 6 chars)
     * @param string $rol Rol ('admin', 'artesano', 'asistente')
     * @return array Datos seguros del usuario creado
     * @throws InvalidArgumentException Si los datos violan las reglas de validación (HTTP 422)
     * @throws RuntimeException Si el nombre de usuario ya está ocupado (HTTP 409)
     */
    public function createUser(string $username, string $password, string $rol = 'artesano'): array {
        $username = trim($username);
        $password = trim($password);
        $rol = trim($rol);

        // 1. Validaciones del nombre de usuario
        if ($username === '') {
            throw new InvalidArgumentException('El nombre de usuario es obligatorio.', 422);
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new InvalidArgumentException('El nombre de usuario debe tener entre 3 y 50 caracteres.', 422);
        }

        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $username)) {
            throw new InvalidArgumentException('El nombre de usuario solo puede contener letras, números, guiones y puntos.', 422);
        }

        // 2. Comprobación de unicidad
        if ($this->usuarioRepo->existsUsername($username)) {
            throw new RuntimeException("El nombre de usuario '{$username}' ya se encuentra registrado.", 409);
        }

        // 3. Validaciones de contraseña
        if ($password === '') {
            throw new InvalidArgumentException('La contraseña es obligatoria.', 422);
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.', 422);
        }

        if (strlen($password) > 100) {
            throw new InvalidArgumentException('La contraseña no puede exceder los 100 caracteres.', 422);
        }

        // 4. Validación de rol
        if (!in_array($rol, ['admin', 'artesano', 'asistente'], true)) {
            throw new InvalidArgumentException("El rol '{$rol}' no es válido. Roles permitidos: admin, artesano, asistente.", 422);
        }

        // 5. Cifrado bcrypt y persistencia
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $createdId = $this->usuarioRepo->create($username, $passwordHash, $rol);

        $createdUser = $this->usuarioRepo->findByIdSafe($createdId);
        if ($createdUser === null) {
            throw new RuntimeException('Error interno al recuperar el usuario registrado.', 500);
        }

        return $createdUser;
    }

    /**
     * Modifica el rol de un usuario existente con salvaguarda absoluta para el Administrador Raíz.
     * 
     * @param int $id Identificador del usuario
     * @param string $newRole Nuevo rol ('admin', 'artesano', 'asistente')
     * @return array Datos del usuario con el rol actualizado
     * @throws RuntimeException Si el usuario no existe (404) o si se intenta alterar al root admin (403)
     * @throws InvalidArgumentException Si el rol no es válido (422)
     */
    public function updateRole(int $id, string $newRole): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de usuario no es válido.', 422);
        }

        $newRole = trim($newRole);

        // 1. Verificar existencia del usuario
        $user = $this->usuarioRepo->findByIdSafe($id);
        if ($user === null) {
            throw new RuntimeException("El usuario con ID #{$id} no existe.", 404);
        }

        // 2. Salvaguarda Inviolable del Administrador Raíz (ID #1)
        if ($id === 1 && $newRole !== 'admin') {
            throw new RuntimeException('Operación denegada: La cuenta del administrador titular (ID #1) no puede ser degradada ni modificada.', 403);
        }

        // 3. Validar que el rol pertenezca al catálogo permitido
        if (!in_array($newRole, ['admin', 'artesano', 'asistente'], true)) {
            throw new InvalidArgumentException("El rol '{$newRole}' no es válido. Roles permitidos: admin, artesano, asistente.", 422);
        }

        // 4. Actualizar rol en la persistencia
        $success = $this->usuarioRepo->updateRole($id, $newRole);
        if (!$success) {
            throw new RuntimeException('No fue posible actualizar el rol del usuario.', 500);
        }

        return [
            'id'       => $id,
            'username' => $user['username'],
            'rol'      => $newRole,
        ];
    }

    /**
     * Actualiza el nombre de usuario de un creador o colaborador.
     * 
     * @param int $id Identificador del usuario
     * @param string $newUsername Nuevo nombre de usuario
     * @return array Datos actualizados del usuario
     * @throws RuntimeException Si no existe (404) o el nombre ya está en uso (409)
     * @throws InvalidArgumentException Si la sintaxis o longitud es inválida (422)
     */
    public function updateUsername(int $id, string $newUsername): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de usuario no es válido.', 422);
        }

        $newUsername = trim($newUsername);

        // 1. Validaciones del nombre de usuario
        if ($newUsername === '') {
            throw new InvalidArgumentException('El nombre de usuario no puede estar vacío.', 422);
        }

        if (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
            throw new InvalidArgumentException('El nombre de usuario debe tener entre 3 y 50 caracteres.', 422);
        }

        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $newUsername)) {
            throw new InvalidArgumentException('El nombre de usuario solo puede contener letras, números, guiones y puntos.', 422);
        }

        // 2. Verificar existencia del usuario
        $user = $this->usuarioRepo->findByIdSafe($id);
        if ($user === null) {
            throw new RuntimeException("El usuario con ID #{$id} no existe.", 404);
        }

        // 3. Comprobar que no esté en uso por otro usuario
        if ($this->usuarioRepo->existsUsername($newUsername, $id)) {
            throw new RuntimeException("El nombre de usuario '{$newUsername}' ya está siendo utilizado por otro usuario.", 409);
        }

        // 4. Actualizar en persistencia
        $success = $this->usuarioRepo->updateUsername($id, $newUsername);
        if (!$success) {
            throw new RuntimeException('No fue posible actualizar el nombre de usuario.', 500);
        }

        return [
            'id'       => $id,
            'username' => $newUsername,
            'rol'      => $user['rol'],
        ];
    }

    /**
     * Restablece la contraseña de un usuario (recuperación / reseteo administrativo).
     * Si no se proporciona una contraseña explícita, genera una contraseña temporal segura.
     * 
     * @param int $id Identificador del usuario
     * @param string|null $newPassword Contraseña opcional suministrada (>= 6 chars)
     * @return array Resultado con acuse y contraseña temporal si fue autogenerada
     * @throws RuntimeException Si el usuario no existe (404)
     * @throws InvalidArgumentException Si la contraseña manual es menor a 6 caracteres (422)
     */
    public function resetPassword(int $id, ?string $newPassword = null): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de usuario no es válido.', 422);
        }

        // 1. Verificar existencia
        $user = $this->usuarioRepo->findByIdSafe($id);
        if ($user === null) {
            throw new RuntimeException("El usuario con ID #{$id} no existe.", 404);
        }

        // 2. Determinar si se usa contraseña suministrada o se autogenera
        $isGenerated = false;
        $finalPassword = trim((string)$newPassword);

        if ($finalPassword === '') {
            $isGenerated = true;
            $randomSuffix = bin2hex(random_bytes(3)); // 6 hex chars
            $finalPassword = "Crochet!{$randomSuffix}!";
        } else {
            if (strlen($finalPassword) < 6) {
                throw new InvalidArgumentException('La nueva contraseña debe tener al menos 6 caracteres.', 422);
            }
            if (strlen($finalPassword) > 100) {
                throw new InvalidArgumentException('La contraseña no puede exceder los 100 caracteres.', 422);
            }
        }

        // 3. Generar hash bcrypt y persistir
        $newHash = password_hash($finalPassword, PASSWORD_BCRYPT);
        $success = $this->usuarioRepo->updatePassword($id, $newHash);
        if (!$success) {
            throw new RuntimeException('No fue posible actualizar la contraseña en el sistema.', 500);
        }

        return [
            'id'                => $id,
            'username'          => $user['username'],
            'password_temporal' => $isGenerated ? $finalPassword : null,
            'es_autogenerada'   => $isGenerated,
            'mensaje'           => "Contraseña restablecida exitosamente para el usuario '{$user['username']}'." .
                                   ($isGenerated ? " Entregue la clave temporal al artesano: {$finalPassword}" : ""),
        ];
    }

    /**
     * Elimina un usuario del sistema con salvaguardas de cuenta raíz, auto-eliminación y comprobación referencial.
     * 
     * @param int $id Identificador del usuario a eliminar
     * @param int|null $currentUserId ID del usuario en sesión activa para prevenir auto-eliminación
     * @return bool True si fue eliminado
     * @throws RuntimeException Si es el admin raíz (403), auto-eliminación (403), si tiene creaciones (409) o no existe (404)
     */
    public function deleteUser(int $id, ?int $currentUserId = null): bool {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de usuario no es válido.', 422);
        }

        // 1. Salvaguarda del Administrador Raíz (ID #1)
        if ($id === 1) {
            throw new RuntimeException('Operación denegada: La cuenta del administrador titular (ID #1) no puede ser eliminada.', 403);
        }

        // 2. Prevención de auto-eliminación accidental de la cuenta activa
        if ($currentUserId !== null && $id === $currentUserId) {
            throw new RuntimeException('Operación denegada: No puedes eliminar tu propia cuenta mientras te encuentras en sesión activa.', 403);
        }

        // 3. Verificar existencia
        $user = $this->usuarioRepo->findByIdSafe($id);
        if ($user === null) {
            throw new RuntimeException("El usuario con ID #{$id} no existe.", 404);
        }

        // 4. Comprobar integridad referencial (creaciones asociadas)
        $creationsCount = $this->usuarioRepo->countCreationsByUser($id);
        if ($creationsCount > 0) {
            throw new RuntimeException("No se puede eliminar al usuario '{$user['username']}' porque tiene {$creationsCount} creación(es) asociada(s) en el catálogo. Reasigne o elimine sus piezas antes de continuar.", 409);
        }

        // 5. Proceder a la eliminación física
        return $this->usuarioRepo->delete($id);
    }
}

