<?php
/**
 * Migración 010 · WhatsApp del Artesano (Feature 010) — CLI Only
 * Algodón Nórdico Design System
 *
 * Añade la columna opcional `usuarios.whatsapp` (contacto comercial del
 * artesano/vendedor) sin destruir datos existentes. Idempotente: si la columna
 * ya existe, no hace nada y sale con código 0.
 *
 * Nota SQLite: ADD COLUMN no admite CHECK inline en versiones antiguas, por lo
 * que la restricción de longitud vive en `seed.sql` (instalaciones nuevas) y
 * la validación en `UsuarioService` (todas las escrituras vía API).
 *
 * Uso:
 *   php scripts/migrate-010-whatsapp.php
 */

declare(strict_types=1);

// Guardia de seguridad: bloqueo estricto de ejecución vía HTTP (como setup.php)
if (php_sapi_name() !== 'cli') {
    die('Acceso Denegado: las migraciones solo pueden ejecutarse desde la terminal CLI.');
}

require dirname(__DIR__) . '/app/autoload.php';

function migrateMessage(string $message, string $type = 'info'): void {
    $colors = [
        'success' => "\033[32m",
        'error'   => "\033[31m",
        'warning' => "\033[33m",
        'info'    => "\033[36m",
        'reset'   => "\033[0m",
    ];
    $prefix = match ($type) {
        'success' => '[✓] ',
        'error'   => '[✗] ',
        'warning' => '[!] ',
        default   => '[i] ',
    };
    $color = $colors[$type] ?? $colors['info'];
    echo $color . $prefix . $message . $colors['reset'] . PHP_EOL;
}

try {
    $dbFile = dirname(__DIR__) . '/database/database.sqlite';
    if (!is_file($dbFile)) {
        throw new RuntimeException("No existe {$dbFile}. Ejecuta primero: php setup.php");
    }

    $pdo = new PDO('sqlite:' . $dbFile, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON;');
    $pdo->exec('PRAGMA busy_timeout = 5000;');

    $columns = $pdo->query('PRAGMA table_info(usuarios);')->fetchAll();
    foreach ($columns as $col) {
        if (($col['name'] ?? '') === 'whatsapp') {
            migrateMessage('La columna usuarios.whatsapp ya existe. Nada que migrar.', 'success');
            exit(0);
        }
    }

    $pdo->exec('ALTER TABLE usuarios ADD COLUMN whatsapp TEXT DEFAULT NULL;');
    migrateMessage('Columna usuarios.whatsapp creada (TEXT NULL, sin backfill: NULL = sin número).', 'success');

    $count = (int)$pdo->query('SELECT COUNT(*) FROM usuarios WHERE whatsapp IS NOT NULL')->fetchColumn();
    migrateMessage("Usuarios con WhatsApp registrado: {$count}", 'info');
} catch (Throwable $e) {
    migrateMessage('Error crítico en la migración 010: ' . $e->getMessage(), 'error');
    exit(1);
}
