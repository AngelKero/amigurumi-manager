<?php
/**
 * Fase 4 · Suite de Regresión Acumulada (Gobernanza · Acción 3 · H-020)
 * Algodón Nórdico Design System
 *
 * Runner de regresión por fase: descubre dinámicamente todas las subfases de la
 * Fase 4 (`tests/test-subfase-4.*.php`), las ejecuta en secuencia, agrega sus
 * aserciones y valida la coherencia estructural con el índice `docs/testing/README.md`.
 *
 * - Despliegue dinámico: cuando 4.1 se implemente, esta suite la detecta sola.
 * - Estado vacío: antes de que exista ninguna subfase, reporta "0 subfases aún"
 *   con código de salida 0 (no es un error; la Fase 4 está en desarrollo).
 * - Regla de gobernanza: obligatoria al tocar features 004–008 (AGENTS.md §3).
 *
 * Ejecución:
 *   php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1
 */

declare(strict_types=1);

use Tests\TestHelper;

require_once __DIR__ . '/TestHelper.php';

TestHelper::init('Fase 4 · Suite de Regresión Acumulada (test-subfase-4.*.php)');

$readmeFile = dirname(__DIR__) . '/docs/testing/README.md';
$readme = is_file($readmeFile) ? (string)file_get_contents($readmeFile) : '';

// ============================================================================
// SECCIÓN 1: DESPLIEGUE DINÁMICO DE SUBFASES
// ============================================================================
TestHelper::section('1. Despliegue dinámico de subfases de Fase 4');

$suiteFiles = glob(__DIR__ . '/test-subfase-4.*.php') ?: [];
sort($suiteFiles);

TestHelper::assertTrue(is_array($suiteFiles), 'El glob de suites de Fase 4 se ejecutó sin errores');

$activeSuites = [];
foreach ($suiteFiles as $suiteFile) {
    if (basename($suiteFile) === 'test-fase-4-acumulado.php') continue;
    $activeSuites[] = $suiteFile;
}

$count = count($activeSuites);

if ($count === 0) {
    TestHelper::assert(true, '0 subfases descubiertas: estado válido (Fase 4 aún sin implementar) — el runner queda operativo y detectará test-subfase-4.1.php en cuanto exista.');
} else {
    TestHelper::assert(true, sprintf('%d subfase(s) de Fase 4 descubierta(s) dinámicamente.', $count));
}

// ============================================================================
// SECCIÓN 2: INTEGRIDAD ESTRUCTURAL CON docs/testing/README.md
// ============================================================================
TestHelper::section('2. Coherencia estructural: suites <-> índice docs/testing/README.md');

// Dirección 1: cada suite presente debe estar documentada en el README
foreach ($activeSuites as $suiteFile) {
    $basename = basename($suiteFile);
    TestHelper::assert(
        $readme !== '' && str_contains($readme, $basename),
        "La suite descubierta '{$basename}' tiene fila referenciada en docs/testing/README.md"
    );
}

// Dirección 2: cada script 4.X referenciado en el README debe existir físicamente
$referencedSuites = [];
if ($readme !== '' && preg_match_all('/`(tests\/test-subfase-4\.[^`]+\.php)`/', $readme, $matches)) {
    $referencedSuites = array_unique($matches[1]);
}
foreach ($referencedSuites as $relativePath) {
    $absPath = dirname(__DIR__) . '/' . $relativePath;
    TestHelper::assert(
        is_file($absPath),
        "Toda fila del README '{$relativePath}' tiene su script CLI físico"
    );
}

// El runner acumulado está documentado como mandato de Fase 4
TestHelper::assert(
    $readme !== '' && str_contains($readme, 'test-fase-4-acumulado.php'),
    'docs/testing/README.md documenta la suite acumulada test-fase-4-acumulado.php'
);

// ============================================================================
// SECCIÓN 3: EJECUCIÓN SECUENCIAL DE LAS SUBFASES PRESENTES
// ============================================================================
TestHelper::section('3. Ejecución secuencial (regresión acumulada)');

$cumulativeAssertions = 0;
$allSuitesPassed = true;

foreach ($activeSuites as $suiteFile) {
    $suiteName = basename($suiteFile);
    $output = [];
    $exitCode = 0;
    $tRun0 = microtime(true);
    exec('php ' . escapeshellarg($suiteFile) . ' 2>&1', $output, $exitCode);
    $runDurationMs = round((microtime(true) - $tRun0) * 1000, 2);

    $rawText = preg_replace("/\x1b\[[0-9;]*m/", '', implode("\n", $output));
    preg_match('/Exitosas:\s+(\d+)/', (string)$rawText, $matchPass);
    preg_match('/Fallidas:\s+(\d+)/', (string)$rawText, $matchFail);

    $suitePassedCount = isset($matchPass[1]) ? (int)$matchPass[1] : 0;
    $suiteFailedCount = isset($matchFail[1]) ? (int)$matchFail[1] : 0;
    $cumulativeAssertions += $suitePassedCount;

    TestHelper::assertSame(0, $exitCode, "{$suiteName} finalizó con código de salida 0 (OK)");
    TestHelper::assertSame(0, $suiteFailedCount, "{$suiteName} reportó 0 aserciones fallidas");
    TestHelper::assert($suitePassedCount > 0, "{$suiteName} aprobó {$suitePassedCount} aserciones ({$runDurationMs} ms)");

    if ($exitCode !== 0 || $suiteFailedCount > 0) {
        $allSuitesPassed = false;
    }
}

if ($count > 0) {
    TestHelper::assertTrue($allSuitesPassed, 'El 100% de las subfases de Fase 4 pasaron en regresión secuencial');
    TestHelper::assert($cumulativeAssertions > 0, sprintf('Total acumulado de Fase 4: %d aserciones', $cumulativeAssertions));
} else {
    TestHelper::assert(true, 'Sin suites que ejecutar en esta corrida (Fase 4 en desarrollo).');
}

exit(TestHelper::summary());