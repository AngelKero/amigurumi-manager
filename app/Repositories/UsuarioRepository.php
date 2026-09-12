<?php
/**
 * User Persistence Repository (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Capa de persistencia para la entidad Usuario. Aísla el 100% de las sentencias SQL
 * mediante prepared statements parametrizados sobre SQLite, implementando salvaguardas
 * para el administrador raíz (ID #1) y consultas optimizadas por índice.
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use InvalidArgumentException;
use PDO;

class UsuarioRepository {
    private PDO $pdo;

    /**
     * Permite inyectar una conexión PDO o utilizar el Singleton Database por defecto.
     */
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    /**
     * Busca un usuario por su nombre de usuario exacto.
     * 
     * @param string $username Nombre de usuario
     * @return array|null Registro completo asociativo o null si no existe
     */
    public function findByUsername(string $username): ?array {
        $stmt = $this->pdo->prepare('
            SELECT id, username, password_hash, rol, creado_en 
            FROM usuarios 
            WHERE username = :username 
            LIMIT 1
        ');
        $stmt->execute([':username' => trim($username)]);
        $user = $stmt->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * Busca un usuario por su identificador primario.
     * 
     * @param int $id Identificador del usuario
     * @return array|null Registro completo o null si no existe
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare('
            SELECT id, username, password_hash, rol, creado_en 
            FROM usuarios 
            WHERE id = :id 
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * Busca un usuario por su identificador excluyendo el hash de contraseña (vista segura).
     * 
     * @param int $id Identificador del usuario
     * @return array|null Datos seguros del usuario o null
     */
    public function findByIdSafe(int $id): ?array {
        $stmt = $this->pdo->prepare('
            SELECT id, username, rol, creado_en 
            FROM usuarios 
            WHERE id = :id 
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * Comprueba si un nombre de usuario ya está registrado en el sistema.
     * 
     * @param string $username Nombre a verificar
     * @param int|null $excludeId ID opcional a excluir (ej. al actualizar)
     */
    public function existsUsername(string $username, ?int $excludeId = null): bool {
        $sql = 'SELECT 1 FROM usuarios WHERE username = :username';
        $params = [':username' => trim($username)];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Registra un nuevo creador o usuario en la plataforma.
     * 
     * @param string $username Nombre de usuario único (3-50 chars)
     * @param string $passwordHash Hash generado con password_hash()
     * @param string $rol Rol ('admin', 'artesano', 'asistente')
     * @return int Identificador insertado
     * @throws InvalidArgumentException Si los datos violan las reglas básicas de validación
     */
    public function create(string $username, string $passwordHash, string $rol = 'artesano'): int {
        $username = trim($username);
        $rol = trim($rol);

        if (!in_array($rol, ['admin', 'artesano', 'asistente'], true)) {
            throw new InvalidArgumentException("El rol '{$rol}' no es válido en el sistema.");
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO usuarios (username, password_hash, rol, creado_en)
            VALUES (:username, :password_hash, :rol, datetime("now", "localtime"))
        ');

        $stmt->execute([
            ':username'      => $username,
            ':password_hash' => $passwordHash,
            ':rol'           => $rol,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Actualiza el hash de contraseña de un usuario.
     */
    public function updatePassword(int $id, string $passwordHash): bool {
        $stmt = $this->pdo->prepare('
            UPDATE usuarios 
            SET password_hash = :password_hash 
            WHERE id = :id
        ');

        return $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id'            => $id,
        ]);
    }

    /**
     * Actualiza el nombre de usuario único de un usuario.
     */
    public function updateUsername(int $id, string $username): bool {
        $stmt = $this->pdo->prepare('
            UPDATE usuarios 
            SET username = :username 
            WHERE id = :id
        ');

        return $stmt->execute([
            ':username' => trim($username),
            ':id'       => $id,
        ]);
    }

    /**
     * Actualiza el rol de un usuario con salvaguarda absoluta para el administrador raíz (ID #1).
     * 
     * @param int $id Identificador del usuario
     * @param string $rol Nuevo rol ('admin', 'artesano', 'asistente')
     * @return bool True si se actualizó, false si fue denegado o no hubo cambios
     */
    public function updateRole(int $id, string $rol): bool {
        // Regla de salvaguarda de cuenta raíz: ID #1 nunca puede degradarse
        if ($id === 1 && $rol !== 'admin') {
            return false;
        }

        if (!in_array($rol, ['admin', 'artesano', 'asistente'], true)) {
            throw new InvalidArgumentException("El rol '{$rol}' no es válido.");
        }

        $stmt = $this->pdo->prepare('
            UPDATE usuarios 
            SET rol = :rol 
            WHERE id = :id
        ');

        return $stmt->execute([
            ':rol' => $rol,
            ':id'  => $id,
        ]);
    }

    /**
     * Elimina un usuario con salvaguarda para el administrador raíz (ID #1).
     */
    public function delete(int $id): bool {
        // Regla de salvaguarda de cuenta raíz: ID #1 nunca puede eliminarse
        if ($id === 1) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Obtiene una lista paginada de usuarios sin exponer hashes de contraseñas.
     * 
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento
     * @return array Lista de usuarios
     */
    public function listAll(int $limit = 20, int $offset = 0): array {
        $stmt = $this->pdo->prepare('
            SELECT id, username, rol, creado_en 
            FROM usuarios 
            ORDER BY id ASC 
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtiene el conteo total de usuarios registrados en el sistema.
     */
    public function countAll(): int {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM usuarios');
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtiene una lista paginada de usuarios con el conteo de creaciones asociadas.
     * 
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento
     * @return array Lista de usuarios con creaciones_asociadas
     */
    public function listAllWithCreationsCount(int $limit = 20, int $offset = 0): array {
        $stmt = $this->pdo->prepare('
            SELECT u.id, u.username, u.rol, u.creado_en, COUNT(c.id) AS creaciones_asociadas
            FROM usuarios u
            LEFT JOIN creaciones c ON c.artesano_id = u.id
            GROUP BY u.id
            ORDER BY u.id ASC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return array_map(function ($row) {
            $row['id'] = (int)$row['id'];
            $row['creaciones_asociadas'] = (int)$row['creaciones_asociadas'];
            return $row;
        }, $rows);
    }

    /**
     * Cuenta cuántas creaciones tiene registradas un usuario en el catálogo.
     */
    public function countCreationsByUser(int $userId): int {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM creaciones WHERE artesano_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}

