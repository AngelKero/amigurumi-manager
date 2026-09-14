<?php
/**
 * Login Intents Guard Repository (Clean Architecture)
 * Algodón Nórdico Design System
 *
 * Persistencia para el endurecimiento anti-fuerza-bruta del login (H-003).
 * Aísla el 100% de las sentencias SQL de `login_intentos` mediante prepared
 * statements parametrizados sobre SQLite con PRAGMA busy_timeout.
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Config;
use PDO;

class LoginGuardRepository {
    private PDO $pdo;

    /**
     * Permite inyectar una conexión PDO o utilizar el Singleton Database por defecto.
     */
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    /**
     * Registra un intento de login (exitoso o fallido) en la bitácora.
     *
     * @param string $username Nombre de usuario intentado (ya normalizado)
     * @param string $ip Dirección IP desde la que se realiza el intento
     * @param bool $ok true si el intento fue exitoso, false si es fallido
     */
    public function registerIntent(string $username, string $ip, bool $ok): void {
        $stmt = $this->pdo->prepare('
            INSERT INTO login_intentos (username, ip, intento_ok)
            VALUES (:username, :ip, :intento_ok)
        ');
        $stmt->execute([
            ':username'    => trim($username),
            ':ip'          => substr($ip, 0, 45),
            ':intento_ok'  => $ok ? 1 : 0,
        ]);
    }

    /**
     * Cuenta los intentos fallidos de un nombre de usuario dentro de la ventana configurada.
     */
    public function countFailuresByUsername(string $username, int $windowSeconds): int {
        $sql = 'SELECT COUNT(*) FROM login_intentos
                WHERE username = :username AND intento_ok = 0
                  AND creado_en >= datetime("now", "localtime", :offset)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username' => trim($username),
            ':offset'   => sprintf('-%d seconds', max(1, $windowSeconds)),
        ]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Cuenta los intentos fallidos de una dirección IP dentro de la ventana configurada.
     */
    public function countFailuresByIp(string $ip, int $windowSeconds): int {
        $sql = 'SELECT COUNT(*) FROM login_intentos
                WHERE ip = :ip AND intento_ok = 0
                  AND creado_en >= datetime("now", "localtime", :offset)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':ip'       => substr($ip, 0, 45),
            ':offset'   => sprintf('-%d seconds', max(1, $windowSeconds)),
        ]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Elimina los intentos fallidos de un usuario tras una autenticación exitosa.
     */
    public function clearFailuresByUsername(string $username): void {
        $stmt = $this->pdo->prepare('
            DELETE FROM login_intentos
            WHERE username = :username AND intento_ok = 0
        ');
        $stmt->execute([':username' => trim($username)]);
    }

    /**
     * Limpia la bitácora conservando únicamente la ventana configurada.
     */
    public function prune(int $windowSeconds): void {
        $stmt = $this->pdo->prepare('
            DELETE FROM login_intentos
            WHERE creado_en < datetime("now", "localtime", :offset)
        ');
        $stmt->execute([':offset' => sprintf('-%d seconds', max(60, $windowSeconds))]);
    }
}