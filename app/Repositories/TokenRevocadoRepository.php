<?php
/**
 * Revoked Tokens Repository (Clean Architecture)
 * Algodón Nórdico Design System
 *
 * Persistencia de la denylist de Bearer tokens revocados por `jti` (H-002).
 * Aísla el 100% de las sentencias SQL de `tokens_revocados` mediante prepared
 * statements parametrizados sobre SQLite.
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class TokenRevocadoRepository {
    private PDO $pdo;

    /**
     * Permite inyectar una conexión PDO o utilizar el Singleton Database por defecto.
     */
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    /**
     * Añade un token a la denylist de forma idempotente (INSERT OR IGNORE por pk jti).
     *
     * @param string $jti Identificador criptográfico único del token
     * @param int $sub ID del usuario al que pertenecía el token
     * @param int $expiresAt Timestamp Unix de expiración del token
     */
    public function revoke(string $jti, int $sub, int $expiresAt): void {
        $stmt = $this->pdo->prepare('
            INSERT OR IGNORE INTO tokens_revocados (jti, sub, expira_en)
            VALUES (:jti, :sub, :expira_en)
        ');
        $stmt->execute([
            ':jti'       => substr($jti, 0, 64),
            ':sub'       => $sub,
            ':expira_en' => $expiresAt,
        ]);
    }

    /**
     * Comprueba si un token por su jti ha sido revocado y aún no expira.
     */
    public function isRevoked(string $jti): bool {
        $sql = 'SELECT 1 FROM tokens_revocados
                WHERE jti = :jti AND expira_en > :ahora
                LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':jti'   => substr($jti, 0, 64),
            ':ahora' => time(),
        ]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Elimina las filas de la denylist cuya expiración ya ha dejado de ser relevante.
     *
     * @return int Número de filas purgadas
     */
    public function pruneExpirados(): int {
        $stmt = $this->pdo->prepare('
            DELETE FROM tokens_revocados
            WHERE expira_en <= :ahora
        ');
        $stmt->execute([':ahora' => time()]);
        return $stmt->rowCount();
    }
}