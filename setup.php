<?php
/**
 * Handmade Crochet Creations Micro-ERP - Database Setup & Seeder Script (CLI Only)
 * 
 * Multi-layered Security: Strictly blocked from browser/HTTP execution.
 * Initializes the SQLite database file inside the protected `database/` directory
 * (`database/database.sqlite`), verifies directory permissions, enables PRAGMA foreign keys,
 * and executes `database/seed.sql`.
 * 
 * Usage:
 *   php setup.php
 */

declare(strict_types=1);

// Strictly block execution via web browser (CLI only)
if (php_sapi_name() !== 'cli') {
    die('Access Denied: Setup can only be run via CLI.');
}

// CLI Styling helper
function outputMessage(string $message, string $type = 'info'): void {
    $colors = [
        'success' => "\033[32m",
        'error'   => "\033[31m",
        'warning' => "\033[33m",
        'info'    => "\033[36m",
        'bold'    => "\033[1m",
        'reset'   => "\033[0m"
    ];
    $prefix = match ($type) {
        'success' => "[✓] ",
        'error'   => "[✗] ",
        'warning' => "[!] ",
        default   => "[i] "
    };
    $color = $colors[$type] ?? $colors['info'];
    echo $color . $prefix . $message . $colors['reset'] . PHP_EOL;
}

try {
    $rootDir = __DIR__;
    $dbDir = $rootDir . '/database';
    $dbFile = $dbDir . '/database.sqlite';
    $seedFile = $dbDir . '/seed.sql';
    $uploadsDir = $rootDir . '/uploads';

    outputMessage("Iniciando aprovisionamiento del entorno y base de datos...", 'info');

    // 1. Ensure database directory exists
    if (!is_dir($dbDir)) {
        if (!mkdir($dbDir, 0755, true) && !is_dir($dbDir)) {
            throw new RuntimeException("No se pudo crear el directorio de base de datos: {$dbDir}");
        }
        outputMessage("Directorio de base de datos creado: /database", 'success');
    } else {
        outputMessage("Directorio de base de datos verificado: /database", 'info');
    }

    // 2. Ensure uploads directory exists
    if (!is_dir($uploadsDir)) {
        if (!mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
            throw new RuntimeException("No se pudo crear el directorio de subidas: {$uploadsDir}");
        }
        outputMessage("Directorio de almacenamiento local creado: /uploads", 'success');
    } else {
        outputMessage("Directorio de subidas verificado: /uploads", 'info');
    }

    // 3. Verify seed file presence
    if (!file_exists($seedFile)) {
        throw new RuntimeException("El archivo seed no existe en la ruta esperada: {$seedFile}");
    }
    outputMessage("Archivo de esquema y semillas localizado: database/seed.sql", 'info');

    // 4. Establish PDO connection to SQLite in database/database.sqlite
    $pdo = new PDO("sqlite:{$dbFile}", null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5
    ]);
    outputMessage("Conexión PDO con SQLite establecida: database/database.sqlite", 'success');

    // 5. Force foreign key constraint enforcement
    $pdo->exec('PRAGMA foreign_keys = ON;');
    $fkCheck = $pdo->query('PRAGMA foreign_keys;')->fetchColumn();
    if ((int)$fkCheck !== 1) {
        throw new RuntimeException("No fue posible habilitar PRAGMA foreign_keys en SQLite.");
    }
    outputMessage("Integridad referencial activada: PRAGMA foreign_keys = ON", 'success');

    // 6. Read and execute seed.sql
    $seedSql = file_get_contents($seedFile);
    if ($seedSql === false || trim($seedSql) === '') {
        throw new RuntimeException("El archivo seed.sql está vacío o no se pudo leer.");
    }

    outputMessage("Ejecutando DDL y sembrando datos de prueba...", 'info');
    $pdo->exec($seedSql);
    outputMessage("Esquema relacional y datos de prueba aplicados correctamente.", 'success');

    // 7. Verify seeded records
    $userCount = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $creacionCount = (int)$pdo->query('SELECT COUNT(*) FROM creaciones')->fetchColumn();
    $orderCount = (int)$pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();

    outputMessage("Verificación de registros creados:", 'bold');
    outputMessage("• Usuarios registrados: {$userCount}", 'success');
    outputMessage("• Creaciones en catálogo: {$creacionCount}", 'success');
    outputMessage("• Pedidos vinculados: {$orderCount}", 'success');

    // 8. Verify admin credentials hash
    $stmt = $pdo->query("SELECT id, username, rol, password_hash FROM usuarios WHERE username = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    if ($admin && password_verify('admin123', $admin['password_hash'])) {
        outputMessage("Credenciales de administrador verificadas: usuario 'admin' / clave 'admin123'", 'success');
    } else {
        outputMessage("Advertencia: El usuario admin no pudo ser verificado con 'admin123'.", 'warning');
    }

    outputMessage("¡Fase 1 completada con éxito! Base de datos inicializada en: database/database.sqlite", 'success');

} catch (Throwable $e) {
    outputMessage("Error crítico durante la inicialización: " . $e->getMessage(), 'error');
    exit(1);
}
