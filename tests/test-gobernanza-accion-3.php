<?php
/**
 * Gobernanza · Acción 3 · Gate de Testing de la Fase 4 (suite propia)
 * Algodón Nórdico Design System
 *
 * Verifica las tres entregas de la Acción 3:
 *   H-008 · el SDD de 004 incorpora el gate 3-tier por subfase.
 *   H-015 · el protocolo de divergencia CLI/HTTP existe y está anclado (AGENTS §3 + regla general).
 *   H-020 · la suite de regresión acumulada de Fase 4 existe, descubre dinámicamente
 *           y con 0 subfases reporta un estado vacío válido (exit 0).
 *
 * Ejecución:
 *   php tests/test-gobernanza-accion-3.php > logs/gobernanza-accion-3-cli.log 2>&1
 */

declare(strict_types=1);

use Tests\TestHelper;

require_once __DIR__ . '/TestHelper.php';

TestHelper::init('Gobernanza · Acción 3 · Gate de Testing de la Fase 4 (H-008, H-015, H-020)');

$rootDir  = dirname(__DIR__);
$tasks004 = (string)@file_get_contents($rootDir . '/spec/features/004-auth-sesion-cliente/tasks.md');
$agents   = (string)@file_get_contents($rootDir . '/AGENTS.md');
$generalRule = (string)@file_get_contents($rootDir . '/.agents/rules/general.md');
$readmeTesting = (string)@file_get_contents($rootDir . '/docs/testing/README.md');

// ============================================================================
// H-008: EL SDD DE 004 INCORPORA EL GATE 3-TIER POR SUBFASE
// ============================================================================
TestHelper::section('H-008 · SDD de 004: gate 3-tier por subfase (tasks.md)');

TestHelper::assert($tasks004 !== '', 'spec/features/004-auth-sesion-cliente/tasks.md es legible');
TestHelper::assertStringContains('tests/test-subfase-4.1.php', $tasks004, 'tasks.md genera suite CLI por subfase (4.1)');
TestHelper::assertStringContains('logs/subfase-4.1-cli.log', $tasks004, 'tasks.md exige log CLI por subfase');
TestHelper::assertStringContains('logs/subfase-4.1-http.log', $tasks004, 'tasks.md exige log HTTP por subfase');
TestHelper::assertStringContains('docs/testing/subfase-4.1-auth-sesion.md', $tasks004, 'tasks.md exige reporte ejecutivo en docs/testing/');
TestHelper::assertStringContains('Fallos Detectados & Correcciones Quirúrgicas', $tasks004, 'tasks.md hereda la sección de fallos detectados');
TestHelper::assertStringContains('HALT', $tasks004, 'tasks.md conserva el HALT por subfase');
TestHelper::assertStringContains('tests/test-subfase-4.2.php', $tasks004, 'tasks.md prevé el gate para subfase 4.2');
TestHelper::assertStringContains('tests/test-subfase-4.5.php', $tasks004, 'tasks.md prevé el gate para subfase 4.5');
TestHelper::assertStringContains('Prerrequisitos de seguridad', $tasks004, 'tasks.md ancla los prerrequisitos de la Acción 2');
TestHelper::assertStringContains('429', $tasks004, 'La subfase 4.1 verificará el bloqueo 429 del login (Acción 2)');
TestHelper::assertStringContains('test-fase-4-acumulado.php', $tasks004, 'tasks.md incluye la regresión por fase obligatoria');

// ============================================================================
// H-015: PROTOCOLO DE DIVERGENCIA CLI/HTTP
// ============================================================================
TestHelper::section('H-015 · Protocolo de divergencia CLI vs. HTTP');

$protocoloFile = $rootDir . '/docs/testing/protocolo-divergencia-cli-http.md';
$protocolo = is_file($protocoloFile) ? (string)file_get_contents($protocoloFile) : '';

TestHelper::assert(is_file($protocoloFile), 'docs/testing/protocolo-divergencia-cli-http.md está publicado');
TestHelper::assertStringContains('H-015', $protocolo, 'El protocolo referencia su hallazgo de origen (H-015)');
TestHelper::assertStringContains('Matriz de Escenarios', $protocolo, 'El protocolo define la matriz de escenarios CLI/HTTP');
TestHelper::assertStringContains('Defecto de código', $protocolo, 'El protocolo define el defecto de código');
TestHelper::assertStringContains('Defecto de entorno de pruebas', $protocolo, 'El protocolo define el defecto de entorno');
TestHelper::assertStringContains('6 pasos', $protocolo, 'El protocolo documenta el triaje en 6 pasos');
TestHelper::assertStringContains('Halting', $protocolo, 'El protocolo fija el criterio de halting');
TestHelper::assertStringContains('Plantilla de Registro', $protocolo, 'El protocolo ofrece plantilla de registro para reportes');

// Anclas
TestHelper::assertStringContains('protocolo-divergencia-cli-http.md', $agents, 'AGENTS.md §3 ancla el protocolo');
TestHelper::assertStringContains('test-fase-4-acumulado.php', $agents, 'AGENTS.md §3 manda la regresión acumulada de Fase 4');
TestHelper::assertStringContains('protocolo-divergencia-cli-http.md', $generalRule, '.agents/rules/general.md ancla el protocolo en su gate numerado');
TestHelper::assertStringContains('test-fase-4-acumulado.php', $readmeTesting, 'docs/testing/README.md registra la suite acumulada de Fase 4');

// ============================================================================
// H-020: SUITE DE REGRESIÓN ACUMULADA DE FASE 4
// ============================================================================
TestHelper::section('H-020 · tests/test-fase-4-acumulado.php (runner dinámico)');

$runnerFile = $rootDir . '/tests/test-fase-4-acumulado.php';
TestHelper::assert(is_file($runnerFile), 'tests/test-fase-4-acumulado.php existe');
$runnerCode = (string)@file_get_contents($runnerFile);
TestHelper::assertStringContains("glob(__DIR__ . '/test-subfase-4.*.php')", $runnerCode, 'El runner hace despliegue dinámico (glob) de subfases');
TestHelper::assertStringContains('0 subfases', $runnerCode, 'El runner contempla explícitamente el estado vacío (0 subfases)');
TestHelper::assertStringContains('README', $runnerCode, 'El runner incluye la validación estructural contra docs/testing/README.md');

TestHelper::section('H-020 · Ejecución real del runner (fase 4 vacía → exit 0)');

$output = [];
$exitCode = 0;
$t0 = microtime(true);
exec('php ' . escapeshellarg($runnerFile) . ' 2>&1', $output, $exitCode);
$durationMs = round((microtime(true) - $t0) * 1000, 2);
$joined = preg_replace("/\x1b\[[0-9;]*m/", '', implode("\n", $output));

TestHelper::assertSame(0, $exitCode, "El runner con fase 4 vacía finaliza con exit 0 ({$durationMs} ms)");
TestHelper::assertStringContains('0 subfases', $joined, 'El runner reporta el estado vacío sin errores');
TestHelper::assertStringContains('100% OK', $joined, 'El runner cerrado en verde (100% OK)');

// ============================================================================
// PRERREQUISITOS TÉCNICOS DEL RUNNER (TestHelper)
// ============================================================================
TestHelper::section('Sanidad técnica del runner acumulado');

TestHelper::assertStringContains('Exitosas:', $runnerCode, 'El runner agrega el contador Exitosas de cada subfase');
TestHelper::assertStringContains('TestHelper::summary()', $runnerCode, 'El runner devuelve el código de salida del resumen');

exit(TestHelper::summary());