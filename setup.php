<?php
/**
 * Handmade Amigurumi Micro-ERP - Database Setup & Seeder Script
 * 
 * Initializes the SQLite database file (`database.sqlite`), ensures directory
 * structure integrity (including `/uploads`), enables foreign keys via PRAGMA,
 * and executes `database/seed.sql`.
 * 
 * Usage:
 *   CLI: php setup.php
 *   Web: http://localhost:8000/setup.php
 */

declare(strict_types=1);

$isCli = (php_sapi_name() === 'cli');

// Styling helpers for CLI and Web output
function outputMessage(string $message, string $type = 'info', bool $isCli = true): void {
    if ($isCli) {
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
    } else {
        $badgeClass = match ($type) {
            'success' => 'background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;',
            'error'   => 'background: #f8d7da; color: #842029; border: 1px solid #f5c2c7;',
            'warning' => 'background: #fff3cd; color: #664d03; border: 1px solid #ffecb5;',
            default   => 'background: #cff4fc; color: #055160; border: 1px solid #b6effb;'
        };
        echo "<div style='margin: 8px 0; padding: 10px 14px; border-radius: 6px; font-family: system-ui, -apple-system, sans-serif; {$badgeClass}'>"
            . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
            . "</div>";
    }
}

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html lang='es'><head><title>Setup Base de Datos - Amigurumi Micro-ERP</title>";
    echo "<style>body{max-width:760px;margin:40px auto;padding:0 20px;font-family:system-ui,-apple-system,sans-serif;color:#333;line-height:1.5;background:#f9fafb;}h1{color:#1f2937;border-bottom:2px solid #e5e7eb;padding-bottom:10px;}pre{background:#1e293b;color:#f8fafc;padding:14px;border-radius:8px;overflow-x:auto;}</style></head><body>";
    echo "<h1>🧵 Amigurumi Micro-ERP: Inicialización de Base de Datos</h1>";
}

try {
    $rootDir = __DIR__;
    $dbFile = $rootDir . '/database.sqlite';
    $seedFile = $rootDir . '/database/seed.sql';
    $uploadsDir = $rootDir . '/uploads';

    outputMessage("Iniciando aprovisionamiento del entorno y base de datos...", 'info', $isCli);

    // 1. Ensure uploads directory exists
    if (!is_dir($uploadsDir)) {
        if (!mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
            throw new RuntimeException("No se pudo crear el directorio de subidas: {$uploadsDir}");
        }
        outputMessage("Directorio de almacenamiento local creado: /uploads", 'success', $isCli);
    } else {
        outputMessage("Directorio de subidas verificado: /uploads", 'info', $isCli);
    }

    // 2. Verify seed file presence
    if (!file_exists($seedFile)) {
        throw new RuntimeException("El archivo seed no existe en la ruta esperada: {$seedFile}");
    }
    outputMessage("Archivo de esquema y semillas localizado: database/seed.sql", 'info', $isCli);

    // 3. Establish PDO connection to SQLite
    $pdo = new PDO("sqlite:{$dbFile}", null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5
    ]);
    outputMessage("Conexión PDO con SQLite establecida: database.sqlite", 'success', $isCli);

    // 4. Force foreign key constraint enforcement
    $pdo->exec('PRAGMA foreign_keys = ON;');
    $fkCheck = $pdo->query('PRAGMA foreign_keys;')->fetchColumn();
    if ((int)$fkCheck !== 1) {
        throw new RuntimeException("No fue posible habilitar PRAGMA foreign_keys en SQLite.");
    }
    outputMessage("Integridad referencial activada: PRAGMA foreign_keys = ON", 'success', $isCli);

    // 5. Read and execute seed.sql
    $seedSql = file_get_contents($seedFile);
    if ($seedSql === false || trim($seedSql) === '') {
        throw new RuntimeException("El archivo seed.sql está vacío o no se pudo leer.");
    }

    outputMessage("Ejecutando DDL y sembrando datos de prueba...", 'info', $isCli);
    $pdo->exec($seedSql);
    outputMessage("Esquema relacional y datos de prueba aplicados correctamente.", 'success', $isCli);

    // 6. Verify seeded records
    $userCount = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $amigurumiCount = (int)$pdo->query('SELECT COUNT(*) FROM amigurumis')->fetchColumn();
    $orderCount = (int)$pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();

    outputMessage("Verificación de registros creados:", 'bold', $isCli);
    outputMessage("• Usuarios registrados: {$userCount}", 'success', $isCli);
    outputMessage("• Amigurumis en catálogo: {$amigurumiCount}", 'success', $isCli);
    outputMessage("• Pedidos vinculados: {$orderCount}", 'success', $isCli);

    // 7. Verify admin credentials hash
    $stmt = $pdo->query("SELECT id, username, rol, password_hash FROM usuarios WHERE username = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    if ($admin && password_verify('admin123', $admin['password_hash'])) {
        outputMessage("Credenciales de administrador verificadas: usuario 'admin' / clave 'admin123'", 'success', $isCli);
    } else {
        outputMessage("Advertencia: El usuario admin no pudo ser verificado con 'admin123'.", 'warning', $isCli);
    }

    outputMessage("¡Fase 1 completada con éxito! Base de datos lista para pruebas y consumo.", 'success', $isCli);

} catch (Throwable $e) {
    outputMessage("Error crítico durante la inicialización: " . $e->getMessage(), 'error', $isCli);
    if ($isCli) {
        exit(1);
    }
}

if (!$isCli) {
    echo "<hr><p style='color: #6b7280; font-size: 0.9em;'>Ejecutado desde el servidor web local. Base de datos SQLite inicializada en <code>" . htmlspecialchars($dbFile, ENT_QUOTES, 'UTF-8') . "</code></p>";
    echo "</body></html>";
}
