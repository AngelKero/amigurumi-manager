<?php
/**
 * Test Suite: Subfase 3.6.5: Rendimiento SQLite, Arquitectura Limpia & Regresión Global Acumulada
 * Algodón Nórdico Design System — Clean Architecture Backend
 * 
 * Cobertura de Pruebas:
 * 1. Auditoría de Consultas con EXPLAIN QUERY PLAN & Cobertura de Índices (Cero SCAN TABLE)
 * 2. Erradicación de Patrones N+1, JOINs Optimizados & Benchmarks de Tiempo
 * 3. Concurrencia SQLite, PRAGMA busy_timeout = 5000 & Verificación de Integridad Relacional
 * 4. Auditoría de Arquitectura Limpia, SOLID, Cero PHP en src/, Cero SQL fuera de Repositorios & Controladores Delgados (<= 60 líneas)
 * 5. Suite de Regresión Global Acumulada (Ejecución y Verificación de Subfases 3.1 a 3.6.4)
 * 6. Pruebas de Integración HTTP en Vivo contra Servidor Local (Latencias & Trazas)
 */

declare(strict_types=1);

namespace Tests;

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Esta suite de pruebas solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\TokenManager;
use App\Repositories\CreacionRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\UsuarioRepository;
use App\Services\CreacionService;
use App\Services\PedidoService;
use App\Services\UsuarioService;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

TestHelper::init('Subfase 3.6.5: Rendimiento SQLite, Arquitectura Limpia & Regresión Global Acumulada');

$pdo = Database::getInstance();
$baseUrl = 'http://localhost:8000';
$httpLogFile = dirname(__DIR__) . '/logs/subfase-3.6.5-http.log';
file_put_contents($httpLogFile, "=== LOG DE INTEGRACIÓN HTTP EN VIVO — SUBFASE 3.6.5 ===\nFecha: " . date('Y-m-d H:i:s') . "\n\n");

$logHttpTransaction = function(string $testName, array $res, ?string $requestPayload = null) use ($httpLogFile) {
    $entry = "--------------------------------------------------------------------------------\n";
    $entry .= "TEST: {$testName}\n";
    $entry .= "HTTP Status: {$res['status']} | Latencia: {$res['duration_ms']} ms\n";
    if ($requestPayload !== null) {
        $entry .= "Request Payload:\n{$requestPayload}\n";
    }
    $entry .= "Response Headers:\n" . json_encode($res['headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    $entry .= "Response Body:\n" . ($res['body'] ?: "(vacío)") . "\n\n";
    file_put_contents($httpLogFile, $entry, FILE_APPEND);
};

// ============================================================================
// SECCIÓN 1: EXPLAIN QUERY PLAN & COBERTURA DE ÍNDICES (CERO SCAN TABLE)
// ============================================================================
TestHelper::section('1. EXPLAIN QUERY PLAN & Cobertura de Índices SQLite');

// 1.1 Verificación de existencia de los 9 índices de rendimiento en SQLite
$indexesStmt = $pdo->query("SELECT name, tbl_name FROM sqlite_master WHERE type = 'index' AND sql IS NOT NULL");
$existingIndexes = [];
while ($row = $indexesStmt->fetch(PDO::FETCH_ASSOC)) {
    $existingIndexes[$row['name']] = $row['tbl_name'];
}

$expectedIndexes = [
    'idx_usuarios_username'   => 'usuarios',
    'idx_usuarios_activo'     => 'usuarios',
    'idx_creaciones_artesano' => 'creaciones',
    'idx_creaciones_categoria'=> 'creaciones',
    'idx_creaciones_stock'    => 'creaciones',
    'idx_creaciones_activo'   => 'creaciones',
    'idx_pedidos_creacion'    => 'pedidos',
    'idx_pedidos_estado'      => 'pedidos',
    'idx_pedidos_activo'      => 'pedidos',
];

foreach ($expectedIndexes as $indexName => $tableName) {
    TestHelper::assert(isset($existingIndexes[$indexName]), "Índice {$indexName} existe físicamente en SQLite");
    TestHelper::assertSame($tableName, $existingIndexes[$indexName] ?? '', "Índice {$indexName} pertenece a tabla {$tableName}");
}

// Helper para ejecutar y analizar EXPLAIN QUERY PLAN
$explainPlan = function(string $sql, array $params = []) use ($pdo): array {
    $stmt = $pdo->prepare("EXPLAIN QUERY PLAN " . $sql);
    $stmt->execute($params);
    $details = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $details[] = $row['detail'];
    }
    return $details;
};

// 1.2 Búsqueda de usuario por username
$planUserByUname = $explainPlan("SELECT * FROM usuarios WHERE username = :u", [':u' => 'admin']);
$planUserByUnameStr = implode(' | ', $planUserByUname);
TestHelper::assertStringContains('USING INDEX', $planUserByUnameStr, 'Búsqueda por username utiliza índice');
TestHelper::assert(!str_contains($planUserByUnameStr, 'SCAN TABLE usuarios'), 'Búsqueda por username erradica SCAN TABLE en usuarios');

// 1.3 Búsqueda de usuario por estado activo
$planUserByActivo = $explainPlan("SELECT * FROM usuarios WHERE activo = :act", [':act' => 1]);
$planUserByActivoStr = implode(' | ', $planUserByActivo);
TestHelper::assertStringContains('idx_usuarios_activo', $planUserByActivoStr, 'Filtro activo en usuarios utiliza idx_usuarios_activo');
TestHelper::assert(!str_contains($planUserByActivoStr, 'SCAN TABLE usuarios'), 'Filtro activo erradica SCAN TABLE en usuarios');

// 1.4 Búsqueda de creaciones por artesano creador
$planCreacionByArt = $explainPlan("SELECT * FROM creaciones WHERE artesano_id = :id", [':id' => 1]);
$planCreacionByArtStr = implode(' | ', $planCreacionByArt);
TestHelper::assertStringContains('idx_creaciones_artesano', $planCreacionByArtStr, 'Búsqueda por artesano utiliza idx_creaciones_artesano');
TestHelper::assert(!str_contains($planCreacionByArtStr, 'SCAN TABLE creaciones'), 'Búsqueda por artesano erradica SCAN TABLE en creaciones');

// 1.5 Búsqueda de creaciones por categoría
$planCreacionByCat = $explainPlan("SELECT * FROM creaciones WHERE categoria = :cat", [':cat' => 'Amigurumis & Figuras']);
$planCreacionByCatStr = implode(' | ', $planCreacionByCat);
TestHelper::assertStringContains('idx_creaciones_categoria', $planCreacionByCatStr, 'Filtro por categoría utiliza idx_creaciones_categoria');
TestHelper::assert(!str_contains($planCreacionByCatStr, 'SCAN TABLE creaciones'), 'Filtro por categoría erradica SCAN TABLE en creaciones');

// 1.6 Búsqueda de creaciones por stock físico disponible
$planCreacionByStock = $explainPlan("SELECT * FROM creaciones WHERE cantidad_stock > :minStock", [':minStock' => 0]);
$planCreacionByStockStr = implode(' | ', $planCreacionByStock);
TestHelper::assertStringContains('idx_creaciones_stock', $planCreacionByStockStr, 'Filtro por stock disponible utiliza idx_creaciones_stock');
TestHelper::assert(!str_contains($planCreacionByStockStr, 'SCAN TABLE creaciones'), 'Filtro por stock erradica SCAN TABLE en creaciones');

// 1.7 Búsqueda de creaciones activas
$planCreacionByActivo = $explainPlan("SELECT * FROM creaciones WHERE activo = :act", [':act' => 1]);
$planCreacionByActivoStr = implode(' | ', $planCreacionByActivo);
TestHelper::assertStringContains('idx_creaciones_activo', $planCreacionByActivoStr, 'Filtro activo en creaciones utiliza idx_creaciones_activo');
TestHelper::assert(!str_contains($planCreacionByActivoStr, 'SCAN TABLE creaciones'), 'Filtro activo erradica SCAN TABLE en creaciones');

// 1.8 Búsqueda de pedidos por creación vinculada
$planPedidoByCreacion = $explainPlan("SELECT * FROM pedidos WHERE creacion_id = :id", [':id' => 1]);
$planPedidoByCreacionStr = implode(' | ', $planPedidoByCreacion);
TestHelper::assertStringContains('idx_pedidos_creacion', $planPedidoByCreacionStr, 'Búsqueda por creacion_id utiliza idx_pedidos_creacion');
TestHelper::assert(!str_contains($planPedidoByCreacionStr, 'SCAN TABLE pedidos'), 'Búsqueda por creacion_id erradica SCAN TABLE en pedidos');

// 1.9 Búsqueda de pedidos por estado de flujo
$planPedidoByEstado = $explainPlan("SELECT * FROM pedidos WHERE estado_pedido = :st", [':st' => 'Pendiente']);
$planPedidoByEstadoStr = implode(' | ', $planPedidoByEstado);
TestHelper::assertStringContains('idx_pedidos_estado', $planPedidoByEstadoStr, 'Filtro por estado de pedido utiliza idx_pedidos_estado');
TestHelper::assert(!str_contains($planPedidoByEstadoStr, 'SCAN TABLE pedidos'), 'Filtro por estado erradica SCAN TABLE en pedidos');

// 1.10 Búsqueda de pedidos activos
$planPedidoByActivo = $explainPlan("SELECT * FROM pedidos WHERE activo = :act", [':act' => 1]);
$planPedidoByActivoStr = implode(' | ', $planPedidoByActivo);
TestHelper::assertStringContains('idx_pedidos_activo', $planPedidoByActivoStr, 'Filtro pedidos activos utiliza idx_pedidos_activo');
TestHelper::assert(!str_contains($planPedidoByActivoStr, 'SCAN TABLE pedidos'), 'Filtro pedidos activos erradica SCAN TABLE en pedidos');

// 1.11 Consulta compuesta del catálogo con ordenación y join
$planCatalog = $explainPlan("
    SELECT c.*, u.username 
    FROM creaciones c 
    INNER JOIN usuarios u ON u.id = c.artesano_id 
    WHERE c.activo = 1 
    ORDER BY c.id DESC 
    LIMIT 12 OFFSET 0
");
$planCatalogStr = implode(' | ', $planCatalog);
TestHelper::assertStringContains('USING INDEX idx_creaciones_activo', $planCatalogStr, 'Catálogo paginado aprovecha idx_creaciones_activo');
TestHelper::assertStringContains('INTEGER PRIMARY KEY', $planCatalogStr, 'Join con usuarios aprovecha primary key rowid (O(1))');

// 1.12 Consulta compuesta de pedidos con múltiples joins
$planOrders = $explainPlan("
    SELECT p.*, c.nombre, u.username 
    FROM pedidos p 
    INNER JOIN creaciones c ON c.id = p.creacion_id 
    INNER JOIN usuarios u ON u.id = c.artesano_id 
    WHERE p.activo = 1 
    ORDER BY p.id DESC 
    LIMIT 20 OFFSET 0
");
$planOrdersStr = implode(' | ', $planOrders);
TestHelper::assertStringContains('USING INDEX idx_pedidos_activo', $planOrdersStr, 'Listado de pedidos aprovecha idx_pedidos_activo');
TestHelper::assertStringContains('INTEGER PRIMARY KEY', $planOrdersStr, 'Joins relacionales de pedidos resueltos por primary keys');

// ============================================================================
// SECCIÓN 2: ERRADICACIÓN DE PATRONES N+1, JOINS Y BENCHMARKS
// ============================================================================
TestHelper::section('2. Erradicación de Patrones N+1 & Benchmarks de Tiempo');

$creacionRepo = new CreacionRepository();
$pedidoRepo = new PedidoRepository();
$usuarioRepo = new UsuarioRepository();

// 2.1 Catálogo recupera creaciones con autor en 1 sola consulta
$t0 = microtime(true);
$catalogItems = $creacionRepo->listCatalog([], 12, 0, 'recientes');
$catalogTimeMs = (microtime(true) - $t0) * 1000;

TestHelper::assert(is_array($catalogItems) && count($catalogItems) > 0, 'listCatalog() retorna array de creaciones hidratadas');
TestHelper::assert(isset($catalogItems[0]['artesano']['username']), 'listCatalog() incluye artesano.username embebido sin consultas N+1');
TestHelper::assert($catalogTimeMs < 25.0, sprintf('listCatalog() ejecuta en tiempo óptimo (%.2f ms < 25 ms)', $catalogTimeMs));

// 2.2 Detalle de creación recupera autor en 1 sola consulta
$t0 = microtime(true);
$detailItem = $creacionRepo->findById(1);
$detailTimeMs = (microtime(true) - $t0) * 1000;

TestHelper::assertNotNull($detailItem, 'findById(1) encuentra la creación');
TestHelper::assert(isset($detailItem['artesano']['username']), 'findById() incluye autor anidado');
TestHelper::assert($detailTimeMs < 15.0, sprintf('findById(1) ejecuta en tiempo óptimo (%.2f ms < 15 ms)', $detailTimeMs));

// 2.3 Listado de pedidos recupera creación y autor en 1 sola consulta
$t0 = microtime(true);
$ordersList = $pedidoRepo->listAll([], 20, 0, null);
$ordersTimeMs = (microtime(true) - $t0) * 1000;

TestHelper::assert(is_array($ordersList) && count($ordersList) > 0, 'listAll() retorna pedidos hidratados');
TestHelper::assert(isset($ordersList[0]['creacion']['nombre']), 'listAll() incluye creacion.nombre embebido');
TestHelper::assert(isset($ordersList[0]['creacion']['artesano_username']), 'listAll() incluye creacion.artesano_username embebido');
TestHelper::assert($ordersTimeMs < 25.0, sprintf('listAll() de pedidos ejecuta en tiempo óptimo (%.2f ms < 25 ms)', $ordersTimeMs));

// 2.4 Listado de usuarios con conteo de creaciones en 1 sola consulta (LEFT JOIN GROUP BY)
$t0 = microtime(true);
$usersWithCounts = $usuarioRepo->listAllWithCreationsCount(20, 0, true);
$usersTimeMs = (microtime(true) - $t0) * 1000;

TestHelper::assert(is_array($usersWithCounts) && count($usersWithCounts) >= 3, 'listAllWithCreationsCount() retorna creadores');
TestHelper::assert(isset($usersWithCounts[0]['creaciones_asociadas']), 'Cada usuario cuenta con creaciones_asociadas precalculadas');
TestHelper::assert(is_int($usersWithCounts[0]['creaciones_asociadas']), 'creaciones_asociadas es tipado entero estricto');
TestHelper::assert($usersTimeMs < 20.0, sprintf('listAllWithCreationsCount() ejecuta en tiempo óptimo (%.2f ms < 20 ms)', $usersTimeMs));

// 2.5 Endpoint de artesanos activos con creaciones (ADR-014) en 1 sola consulta
$t0 = microtime(true);
$activeArtisans = $creacionRepo->findActiveArtisansWithCreations();
$artisansTimeMs = (microtime(true) - $t0) * 1000;

TestHelper::assert(is_array($activeArtisans) && count($activeArtisans) > 0, 'findActiveArtisansWithCreations() retorna creadores activos');
TestHelper::assert(isset($activeArtisans[0]['id'], $activeArtisans[0]['username']), 'Creador tiene id y username');
TestHelper::assert($artisansTimeMs < 15.0, sprintf('findActiveArtisansWithCreations() ejecuta en tiempo óptimo (%.2f ms < 15 ms)', $artisansTimeMs));

// ============================================================================
// SECCIÓN 3: CONCURRENCIA SQLITE, BUSY_TIMEOUT & INTEGRIDAD RELACIONAL
// ============================================================================
TestHelper::section('3. Concurrencia SQLite, busy_timeout & Integridad Relacional');

// 3.1 Verificación de PRAGMA busy_timeout = 5000 en PDO
$busyTimeout = (int)$pdo->query('PRAGMA busy_timeout')->fetchColumn();
TestHelper::assertSame(5000, $busyTimeout, 'PRAGMA busy_timeout está configurado en exactamente 5000 ms (5 segundos)');

// 3.2 Verificación de PRAGMA foreign_keys = ON en PDO
$fkStatus = (int)$pdo->query('PRAGMA foreign_keys')->fetchColumn();
TestHelper::assertSame(1, $fkStatus, 'PRAGMA foreign_keys está estrictamente activado con valor 1 (ON)');

// 3.3 Verificación de integridad física SQLite
$integrityCheck = (string)$pdo->query('PRAGMA integrity_check')->fetchColumn();
TestHelper::assertSame('ok', strtolower($integrityCheck), 'PRAGMA integrity_check devuelve "ok" sin corrupción física');

// 3.4 Verificación de claves foráneas huérfanas
$fkCheck = $pdo->query('PRAGMA foreign_key_check')->fetchAll(PDO::FETCH_ASSOC);
TestHelper::assertSame(0, count($fkCheck), 'PRAGMA foreign_key_check no detecta violaciones ni registros huérfanos');

// 3.5 Simulación de bloqueo concurrente tolerado con busy_timeout
$dbPath = Config::get('database.path', dirname(__DIR__) . '/database/database.sqlite');
$pdo2 = new PDO("sqlite:{$dbPath}", null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo2->exec('PRAGMA busy_timeout = 5000;');
$pdo2->exec('PRAGMA foreign_keys = ON;');

// Conexión 1 inicia transacción exclusiva
$pdo->beginTransaction();
$pdo->exec("UPDATE creaciones SET actualizado_en = datetime('now', 'localtime') WHERE id = 1");

// Conexión 2 realiza lectura concurrente (lectura no bloqueada en SQLite WAL o concurrencia tolerada)
$readStmt2 = $pdo2->query("SELECT id, nombre FROM creaciones WHERE id = 1");
$readRow2 = $readStmt2->fetch();
$readStmt2->closeCursor();
unset($readStmt2);
TestHelper::assert(is_array($readRow2) && (int)$readRow2['id'] === 1, 'Lectura concurrente desde segunda conexión se resuelve exitosamente');

// Conexión 1 hace commit
$pdo->commit();
TestHelper::assert(!$pdo->inTransaction(), 'Transacción de conexión 1 confirmada con éxito');
unset($pdo2);

// 3.6 Reversión atómica de transacciones fallidas
$initialStockItem2 = (int)$pdo->query("SELECT cantidad_stock FROM creaciones WHERE id = 2")->fetchColumn();
try {
    $pdo->beginTransaction();
    $pdo->exec("UPDATE creaciones SET cantidad_stock = cantidad_stock - 5 WHERE id = 2");
    // Provocar fallo intencional de restricción CHECK
    $pdo->exec("UPDATE creaciones SET precio = -100 WHERE id = 2");
    $pdo->commit();
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
$stockAfterRollback = (int)$pdo->query("SELECT cantidad_stock FROM creaciones WHERE id = 2")->fetchColumn();
TestHelper::assertSame($initialStockItem2, $stockAfterRollback, 'Rollback atómico preserva el stock original tras fallo de transacción');

// ============================================================================
// SECCIÓN 4: AUDITORÍA DE ARQUITECTURA LIMPIA, SOLID & CONTROLADORES DELGADOS
// ============================================================================
TestHelper::section('4. Auditoría de Arquitectura Limpia, SOLID & Controladores Delgados');

// 4.1 Cero archivos PHP en src/ (100% frontend exclusivo)
$srcDir = dirname(__DIR__) . '/src';
$srcPhpFiles = [];
$srcIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir));
foreach ($srcIterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $srcPhpFiles[] = $file->getPathname();
    }
}
TestHelper::assertSame(0, count($srcPhpFiles), 'Cero archivos PHP en directorio src/ (100% frontend reservado a CSS/JS)');

// 4.2 Cero consultas SQL o llamadas directas a PDO fuera de app/Repositories/
$codeDirsToAudit = [
    dirname(__DIR__) . '/api',
    dirname(__DIR__) . '/app/Services',
    dirname(__DIR__) . '/app/Middleware',
    dirname(__DIR__) . '/app/Utils',
];

$pdoMethods = ['->prepare', '->query', '->exec', '->begintransaction', '->commit', '->rollback'];
$violatingPdoCalls = [];

foreach ($codeDirsToAudit as $dirPath) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $content = strtolower(file_get_contents($file->getPathname()));
            foreach ($pdoMethods as $method) {
                if (str_contains($content, $method)) {
                    $violatingPdoCalls[] = $file->getPathname() . " ({$method})";
                }
            }
        }
    }
}
TestHelper::assertSame(0, count($violatingPdoCalls), 'Cero llamadas PDO directas en controladores, servicios, middleware o utils (100% en Repositories)');

// 4.3 Controladores REST delgados (Máximo 60 líneas por archivo)
$apiDir = dirname(__DIR__) . '/api';
$controllers = [];
$apiIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($apiDir));
foreach ($apiIterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $controllers[] = $file->getPathname();
    }
}

TestHelper::assertSame(25, count($controllers), 'Existen exactamente 25 controladores REST delgados en la suite de api/');

$controllersExceedingMax = [];
$totalLines = 0;
foreach ($controllers as $cPath) {
    $lines = count(file($cPath));
    $totalLines += $lines;
    if ($lines > 60) {
        $controllersExceedingMax[] = basename(dirname($cPath)) . '/' . basename($cPath) . " ({$lines} líneas)";
    }
}

TestHelper::assertSame(0, count($controllersExceedingMax), 'El 100% de los controladores REST tienen 60 líneas o menos');
$avgLines = round($totalLines / count($controllers), 1);
TestHelper::assert($avgLines < 50.0, sprintf('Promedio de líneas por controlador es altamente desacoplado (%.1f líneas < 50)', $avgLines));

// 4.4 Autocargador PSR-4 nativo y ausencia de dependencias externas Composer
$vendorDir = dirname(__DIR__) . '/vendor';
TestHelper::assert(!is_dir($vendorDir), 'El directorio vendor/ no existe (Cero dependencias externas de Composer)');
TestHelper::assert(class_exists('App\Core\Database'), 'PSR-4 autoloader resuelve App\Core\Database');
TestHelper::assert(class_exists('App\Repositories\CreacionRepository'), 'PSR-4 autoloader resuelve App\Repositories\CreacionRepository');
TestHelper::assert(class_exists('App\Services\PedidoService'), 'PSR-4 autoloader resuelve App\Services\PedidoService');
TestHelper::assert(class_exists('App\Middleware\AuthGuard'), 'PSR-4 autoloader resuelve App\Middleware\AuthGuard');
TestHelper::assert(class_exists('App\Utils\CurrencyHelper'), 'PSR-4 autoloader resuelve App\Utils\CurrencyHelper');

// 4.5 Blindaje Apache .htaccess verificado
$htaccessPath = dirname(__DIR__) . '/.htaccess';
TestHelper::assert(file_exists($htaccessPath), 'Archivo raíz .htaccess presente');
$htaccessContent = file_get_contents($htaccessPath);
TestHelper::assertStringContains('Options -Indexes', $htaccessContent, '.htaccess desactiva listado de directorios');
TestHelper::assertStringContains('app|database|memory-bank|logs|tests', $htaccessContent, '.htaccess bloquea carpetas críticas de backend');
TestHelper::assertStringContains('(sqlite|sqlite3|sql|md)$', $htaccessContent, '.htaccess bloquea extensiones sensibles (.sqlite, .sqlite3, .sql, .md)');
TestHelper::assertStringContains('HTTP:Authorization', $htaccessContent, '.htaccess preserva cabecera Authorization para FastCGI');

// ============================================================================
// SECCIÓN 5: SUITE DE REGRESIÓN GLOBAL ACUMULADA (SUBFASES 3.1 A 3.6.4)
// ============================================================================
TestHelper::section('5. Suite de Regresión Global Acumulada');

// Re-sincronizar base de datos con setup canónico previo a la ejecución secuencial
exec('php ' . escapeshellarg(dirname(__DIR__) . '/setup.php') . ' > /dev/null 2>&1');

$suitesToRun = [
    'Subfase 3.1: Base del Backend'           => dirname(__DIR__) . '/tests/test-subfase-3.1.php',
    'Subfase 3.2: Autenticación Stateless'    => dirname(__DIR__) . '/tests/test-subfase-3.2.php',
    'Subfase 3.3: Usuarios & RBAC'            => dirname(__DIR__) . '/tests/test-subfase-3.3.php',
    'Subfase 3.4: Creaciones & Catálogo'      => dirname(__DIR__) . '/tests/test-subfase-3.4.php',
    'Subfase 3.5: Pedidos & Stock Atómico'    => dirname(__DIR__) . '/tests/test-subfase-3.5.php',
    'Subfase 3.6.1: Acceso, IDOR & RBAC'      => dirname(__DIR__) . '/tests/test-subfase-3.6.1.php',
    'Subfase 3.6.2: Criptografía & Auth'      => dirname(__DIR__) . '/tests/test-subfase-3.6.2.php',
    'Subfase 3.6.3: Inyección SQL & Medios'   => dirname(__DIR__) . '/tests/test-subfase-3.6.3.php',
    'Subfase 3.6.4: Lógica Negocio & Precios' => dirname(__DIR__) . '/tests/test-subfase-3.6.4.php',
];

$cumulativeAssertions = 0;
$allSuitesPassed = true;

foreach ($suitesToRun as $suiteName => $suiteFile) {
    TestHelper::assert(file_exists($suiteFile), "Archivo de prueba existe: " . basename($suiteFile));
    
    $output = [];
    $exitCode = 0;
    $tRun0 = microtime(true);
    exec("php " . escapeshellarg($suiteFile) . " 2>&1", $output, $exitCode);
    $runDurationMs = round((microtime(true) - $tRun0) * 1000, 2);
    
    $rawText = preg_replace("/\x1b\[[0-9;]*m/", "", implode("\n", $output));
    
    preg_match("/Exitosas:\s+(\d+)/", $rawText, $matchPass);
    preg_match("/Fallidas:\s+(\d+)/", $rawText, $matchFail);
    
    $suitePassedCount = isset($matchPass[1]) ? (int)$matchPass[1] : 0;
    $suiteFailedCount = isset($matchFail[1]) ? (int)$matchFail[1] : 0;
    
    $cumulativeAssertions += $suitePassedCount;
    
    TestHelper::assertSame(0, $exitCode, "{$suiteName} finalizó con código de salida 0 (OK)");
    TestHelper::assertSame(0, $suiteFailedCount, "{$suiteName} reportó 0 aserciones fallidas");
    TestHelper::assert($suitePassedCount > 0, "{$suiteName} aprobó exitosamente {$suitePassedCount} aserciones ({$runDurationMs} ms)");
    
    if ($exitCode !== 0 || $suiteFailedCount > 0) {
        $allSuitesPassed = false;
    }
}

TestHelper::assertTrue($allSuitesPassed, "El 100% de las 9 suites previas pasaron con éxito total en regresión secuencial");
TestHelper::assert($cumulativeAssertions >= 1140, "Total de aserciones previas verificadas en verde: {$cumulativeAssertions} aserciones");

// ============================================================================
// SECCIÓN 6: PRUEBAS DE INTEGRACIÓN HTTP EN VIVO CONTRA LOCALHOST:8000
// ============================================================================
TestHelper::section('6. Pruebas de Integración HTTP en Vivo (Latencias & Endpoints)');

// 6.1 Login administrativo y obtención de token
$loginPayload = json_encode(['username' => 'admin', 'password' => 'admin123']);
$loginRes = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', ['Content-Type: application/json'], $loginPayload);
$logHttpTransaction('1. Login Administrativo (POST /api/auth/login.php)', $loginRes, $loginPayload);

TestHelper::assertSame(200, $loginRes['status'], 'POST /api/auth/login.php responde HTTP 200');
TestHelper::assert($loginRes['duration_ms'] < 300.0, sprintf('Latencia de login (bcrypt cost 10) óptima: %.2f ms', $loginRes['duration_ms']));
$adminToken = (string)($loginRes['json']['datos']['token'] ?? '');
TestHelper::assert(strlen($adminToken) > 20, 'Bearer token obtenido exitosamente');

$authHeaders = [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json',
];

// 6.2 Catálogo público paginado con filtros
$catalogRes = TestHelper::curl('GET', $baseUrl . '/api/creaciones/index.php?categoria=' . urlencode('Amigurumis & Figuras') . '&limite=6');
$logHttpTransaction('2. Catálogo Filtrado (GET /api/creaciones/index.php)', $catalogRes);

TestHelper::assertSame(200, $catalogRes['status'], 'GET /api/creaciones/index.php responde HTTP 200');
TestHelper::assert($catalogRes['duration_ms'] < 100.0, sprintf('Latencia de catálogo público es ultrarrápida: %.2f ms (< 100 ms)', $catalogRes['duration_ms']));
TestHelper::assert(is_array($catalogRes['json']['datos']) && count($catalogRes['json']['datos']) > 0, 'Respuesta contiene array de creaciones');
TestHelper::assert(isset($catalogRes['json']['paginacion']), 'Respuesta contiene bloque de paginación estructurado');

// 6.3 Endpoint de creadores artesanos activos (ADR-014)
$artisansRes = TestHelper::curl('GET', $baseUrl . '/api/creaciones/artesanos.php');
$logHttpTransaction('3. Artesanos Activos (GET /api/creaciones/artesanos.php)', $artisansRes);

TestHelper::assertSame(200, $artisansRes['status'], 'GET /api/creaciones/artesanos.php responde HTTP 200');
TestHelper::assert($artisansRes['duration_ms'] < 80.0, sprintf('Latencia de artesanos activos: %.2f ms (< 80 ms)', $artisansRes['duration_ms']));
TestHelper::assert(is_array($artisansRes['json']['datos']), 'Retorna array de artesanos');

// 6.4 Ficha técnica de detalle por ID
$detailRes = TestHelper::curl('GET', $baseUrl . '/api/creaciones/detalle.php?id=1');
$logHttpTransaction('4. Ficha de Detalle (GET /api/creaciones/detalle.php?id=1)', $detailRes);

TestHelper::assertSame(200, $detailRes['status'], 'GET /api/creaciones/detalle.php?id=1 responde HTTP 200');
TestHelper::assert($detailRes['duration_ms'] < 80.0, sprintf('Latencia de ficha técnica: %.2f ms (< 80 ms)', $detailRes['duration_ms']));
TestHelper::assertSame('Dragón Ignis', (string)($detailRes['json']['datos']['nombre'] ?? ''), 'Nombre de pieza corresponde a Dragón Ignis');

// 6.5 Panel de pedidos autenticado
$ordersRes = TestHelper::curl('GET', $baseUrl . '/api/pedidos/index.php?limite=10', $authHeaders);
$logHttpTransaction('5. Listado de Pedidos Autenticado (GET /api/pedidos/index.php)', $ordersRes);

TestHelper::assertSame(200, $ordersRes['status'], 'GET /api/pedidos/index.php responde HTTP 200');
TestHelper::assert($ordersRes['duration_ms'] < 100.0, sprintf('Latencia de listado de pedidos: %.2f ms (< 100 ms)', $ordersRes['duration_ms']));
TestHelper::assert(is_array($ordersRes['json']['datos']), 'Respuesta contiene array de pedidos');
TestHelper::assert(isset($ordersRes['json']['paginacion']), 'Respuesta de pedidos contiene bloque de paginación');

// 6.6 Directorio de usuarios autenticado (solo admin)
$usersRes = TestHelper::curl('GET', $baseUrl . '/api/usuarios/index.php', $authHeaders);
$logHttpTransaction('6. Directorio de Creadores (GET /api/usuarios/index.php)', $usersRes);

TestHelper::assertSame(200, $usersRes['status'], 'GET /api/usuarios/index.php responde HTTP 200');
TestHelper::assert($usersRes['duration_ms'] < 100.0, sprintf('Latencia de usuarios: %.2f ms (< 100 ms)', $usersRes['duration_ms']));
TestHelper::assert(is_array($usersRes['json']['datos']) && count($usersRes['json']['datos']) >= 3, 'Respuesta contiene array de creadores');
TestHelper::assert(isset($usersRes['json']['paginacion']), 'Respuesta de usuarios contiene bloque de paginación');

// 6.7 Preflight CORS OPTIONS en solicitar.php
$corsRes = TestHelper::curl('OPTIONS', $baseUrl . '/api/pedidos/solicitar.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: Content-Type, Authorization',
]);
$logHttpTransaction('7. Preflight CORS (OPTIONS /api/pedidos/solicitar.php)', $corsRes);

TestHelper::assert(in_array($corsRes['status'], [200, 204], true), 'Preflight CORS responde HTTP 200 o 204');
TestHelper::assert($corsRes['duration_ms'] < 60.0, sprintf('Latencia de preflight CORS: %.2f ms (< 60 ms)', $corsRes['duration_ms']));

// ============================================================================
// RESUMEN CONSOLIDADO
// ============================================================================
exit(TestHelper::summary());
