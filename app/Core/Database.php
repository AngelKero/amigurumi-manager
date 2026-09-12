<?php
/**
 * Database Connection Singleton (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Gestiona una única conexión persistente PDO con SQLite 3 asegurando
 * el cumplimiento estricto de claves foráneas (PRAGMA foreign_keys = ON;),
 * excepciones activas y modo asociativo predeterminado.
 */

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

class Database {
    /**
     * Instancia única de la conexión PDO
     */
    private static ?PDO $instance = null;

    /**
     * Constructor privado para prevenir instanciación directa
     */
    private function __construct() {}

    /**
     * Prevenir clonación del singleton
     */
    private function __clone() {}

    /**
     * Prevenir deserialización del singleton
     * 
     * @throws RuntimeException
     */
    public function __wakeup(): void {
        throw new RuntimeException('No se puede deserializar una instancia del Singleton Database.');
    }

    /**
     * Obtiene la instancia activa de PDO SQLite.
     * Si no existe, inicializa la conexión y aplica PRAGMA foreign_keys = ON;.
     * 
     * @throws RuntimeException si la base de datos no existe o no se puede conectar
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $defaultPath = dirname(__DIR__, 2) . '/database/database.sqlite';
            $dbPath = (string)Config::get('database.path', $defaultPath);

            if (!file_exists($dbPath)) {
                throw new RuntimeException(
                    "No se encontró el archivo de base de datos SQLite en: {$dbPath}. Ejecuta 'php setup.php' para inicializarlo."
                );
            }

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $dsn = "sqlite:{$dbPath}";
            $pdo = new PDO($dsn, null, null, $options);

            // Activación obligatoria de integridad referencial
            $pdo->exec('PRAGMA foreign_keys = ON;');

            self::$instance = $pdo;
        }

        return self::$instance;
    }

    /**
     * Permite reiniciar la instancia Singleton (útil para pruebas y suites CLI).
     */
    public static function resetInstance(): void {
        self::$instance = null;
    }

    /**
     * Permite inyectar una instancia PDO personalizada (ej. base de datos en memoria para tests).
     */
    public static function setInstance(?PDO $pdo): void {
        if ($pdo !== null) {
            $pdo->exec('PRAGMA foreign_keys = ON;');
        }
        self::$instance = $pdo;
    }
}
