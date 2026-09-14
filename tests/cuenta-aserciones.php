<?php
/**
 * Gobernanza · Acción 5 · Contador regenerable de aserciones Fase 3 (H-006)
 * Algodón Nórdico Design System
 *
 * Ejecuta las 10 suites CLI de Fase 3 en secuencia, parsea el contador
 * "Exitosas" de cada una en runtime y emite el total verificado de aserciones.
 * Cualquier cifra documentada debe regenerarse con este comando, jamás copiarse:
 *
 *   php tests/cuenta-aserciones.php
 *
 * Las suites de Fase 3 cuentan aserciones parcialmente por filas (según el
 * volumen de la base); por eso el runner RESETEA la base a la semilla limpia
 * (`php setup.php`) ANTES de medir, garantizando un baseline reproducible.
 * La suite 3.6.5 incluye una sección HTTP contra localhost:8000 → ejecutar
 * con el servidor local activo.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

// 0) Baseline reproducible: BD limpia desde la semilla (antes de medir).
exec('php ' . escapeshellarg($root . '/setup.php') . ' > /dev/null 2>&1', $setupOut, $setupCode);
if ($setupCode !== 0) {
    fwrite(STDERR, "Fallo al resetear la base (setup.php). Abortando.\n");
    exit(2);
}

$suites = [
    '3.1'   => 'tests/test-subfase-3.1.php',
    '3.2'   => 'tests/test-subfase-3.2.php',
    '3.3'   => 'tests/test-subfase-3.3.php',
    '3.4'   => 'tests/test-subfase-3.4.php',
    '3.5'   => 'tests/test-subfase-3.5.php',
    '3.6.1' => 'tests/test-subfase-3.6.1.php',
    '3.6.2' => 'tests/test-subfase-3.6.2.php',
    '3.6.3' => 'tests/test-subfase-3.6.3.php',
    '3.6.4' => 'tests/test-subfase-3.6.4.php',
    '3.6.5' => 'tests/test-subfase-3.6.5.php',
];

$total = 0;
$allGreen = true;

fwrite(STDOUT, "Contador regenerable de aserciones — Fase 3 (H-006)\n");
fwrite(STDOUT, str_repeat('-', 60) . "\n");

foreach ($suites as $id => $script) {
    $out = [];
    $code = 0;
    $cmd = 'php ' . escapeshellarg($root . '/' . $script) . ' 2>&1';
    exec($cmd, $out, $code);

    $text = implode("\n", $out);
    $text = preg_replace('/\x1b\[[0-9;]*m/', '', $text);
    preg_match('/(?:Exitosas:\s+)(\d+)/', $text, $m);
    $passed = isset($m[1]) ? (int) $m[1] : 0;
    $total += $passed;

    $status = ($code === 0 && $passed > 0) ? 'OK' : 'FALLO';
    if ($code !== 0 || $passed === 0) {
        $allGreen = false;
    }
    printf("  • Subfase %-5s %5d aserciones   exit=%d   [%s]\n", $id, $passed, $code, $status);
}

fwrite(STDOUT, str_repeat('-', 60) . "\n");
printf("TOTAL FASE 3 VERIFICADO: %d aserciones (%s)\n", $total, $allGreen ? "100% en verde" : "CON FALLOS");

exit($allGreen ? 0 : 1);