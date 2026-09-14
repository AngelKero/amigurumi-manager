<?php
/**
 * Gobernanza · Acción 4 · Consolidación de Reglas (suite propia)
 * Algodón Nórdico Design System
 *
 * Verifica las cuatro entregas de la Acción 4:
 *   H-005 · invariantes canónicos R-01…R-10 (AGENTS §5) citados por clave semántica (tech-stack, docs, ADR).
 *   H-011 · reglas de directorio de creadores implantadas (`.table-artisan-team`, `.avatar-artisan-initials`).
 *   H-012 · heurísticas CR/QW con IDs únicos (CR-1…CR-4, QW-1…QW-3) en reglas, audits, código e históricos.
 *   H-023 · single-source: `.agents/rules/general.md` canónico; `.agents/workflows/general.md` = glue.
 *
 * Ejecución:
 *   php tests/test-gobernanza-accion-4.php > logs/gobernanza-accion-4-cli.log 2>&1
 */

declare(strict_types=1);

use Tests\TestHelper;

require_once __DIR__ . '/TestHelper.php';

TestHelper::init('Gobernanza · Acción 4 · Consolidación de Reglas (H-005, H-011, H-012, H-023)');

$root = dirname(__DIR__);

$agents    = (string)@file_get_contents($root . '/AGENTS.md');
$techStack = (string)@file_get_contents($root . '/spec/constitution/tech-stack.md');
$apiReadme = (string)@file_get_contents($root . '/docs/api/README.md');
$adr004    = (string)@file_get_contents($root . '/docs/architecture/decisiones/ADR-004-universal-soft-delete.md');
$rulesGen  = (string)@file_get_contents($root . '/.agents/rules/general.md');
$workGlue  = (string)@file_get_contents($root . '/.agents/workflows/general.md');
$rulesUx   = (string)@file_get_contents($root . '/.agents/rules/ui-ux-design-system.md');
$audits    = (string)@file_get_contents($root . '/docs/design-system/audits.md');
$usersCss  = (string)@file_get_contents($root . '/src/css/04-components/users.css');
$usuarios  = (string)@file_get_contents($root . '/views/pages/usuarios_content.php');
$creaciones= (string)@file_get_contents($root . '/views/pages/creaciones_content.php');
$usersJs   = (string)@file_get_contents($root . '/src/js/modules/users.js');
$qaReport  = (string)@file_get_contents($root . '/docs/testing/qa-audit-report.md');
$archDet   = (string)@file_get_contents($root . '/docs/archive/detalle.html');
$archIdx   = (string)@file_get_contents($root . '/docs/archive/index.html');
$archPed   = (string)@file_get_contents($root . '/docs/archive/pedidos.html');

// ============================================================================
// H-005: INVARIANTES CANÓNICOS R-01…R-10
// ============================================================================
TestHelper::section('H-005 · Invariantes canónicos R-01…R-10 citados por clave semántica');

foreach ([1,2,3,4,5,6,7,8,9,10] as $n) {
    $key = sprintf('(R-%02d)', $n);
    TestHelper::assertStringContains($key, $agents, "AGENTS.md §5 etiqueta el invariante {$key}");
}

TestHelper::assertStringContains('R-01', $techStack, 'tech-stack referencia R-01 (soft-delete)');
TestHelper::assertStringContains('R-04', $techStack, 'tech-stack referencia R-04 (IDOR)');
TestHelper::assertStringContains('R-05', $techStack, 'tech-stack referencia R-05 (root admin)');
TestHelper::assertStringContains('R-10', $techStack, 'tech-stack referencia R-10 (brute-force/token)');
TestHelper::assertStringContains('nunca por número posicional', $techStack, 'tech-stack declara la regla de citación por clave (no posicional)');
TestHelper::assertStringContains('R-01 / R-02', $apiReadme, 'docs/api/README §8 ancla R-01/R-02');
TestHelper::assertStringContains('R-01', $adr004, 'ADR-004 ancla el invariante R-01');
TestHelper::assertStringContains('R-02', $adr004, 'ADR-004 ancla el invariante R-02 (junto a ADR-008)');

// ============================================================================
// H-011: REGLAS DE DIRECTORIO DE CREADORES IMPLANTADAS
// ============================================================================
TestHelper::section('H-011 · Reglas de directorio de creadores implantadas (CSS + vistas + JS)');

TestHelper::assertStringContains('.table-artisan-team', $usersCss, 'users.css define .table-artisan-team');
TestHelper::assertStringContains('.avatar-artisan-initials', $usersCss, 'users.css define .avatar-artisan-initials');
TestHelper::assertStringContains('table-artisan-team', $usuarios, 'usuarios_content.php usa table-artisan-team');
TestHelper::assertStringContains('avatar-artisan-initials', $usuarios, 'usuarios_content.php usa avatar-artisan-initials');
TestHelper::assertStringContains('avatar-artisan-initials', $creaciones, 'creaciones_content.php usa avatar-artisan-initials');
TestHelper::assertStringContains('avatar-artisan-initials', $usersJs, 'users.js renderiza avatar-artisan-initials');

TestHelper::assert(!str_contains($usersCss, '.user-avatar-circle'), 'users.css no conserva la clase obsoleta .user-avatar-circle');
TestHelper::assert(!str_contains($usuarios, 'user-avatar-circle'), 'usuarios_content.php sin .user-avatar-circle');
TestHelper::assert(!str_contains($creaciones, 'user-avatar-circle'), 'creaciones_content.php sin .user-avatar-circle');
TestHelper::assert(!str_contains($usersJs, 'user-avatar-circle'), 'users.js sin .user-avatar-circle');
TestHelper::assertStringContains('Implantada', $rulesUx, 'Regla §5.16 de DIRECTORIO marcada implantada');

// ============================================================================
// H-012: IDs ÚNICOS DE HEURÍSTICAS CR/QW
// ============================================================================
TestHelper::section('H-012 · Heurísticas CR/QW con IDs únicos (CR-1…CR-4, QW-1…QW-3)');

TestHelper::assertSame(1, substr_count($rulesUx, '[CR-2] '), '[CR-2] (stepper) aparece una sola vez como ID en la regla');
TestHelper::assertSame(1, substr_count($rulesUx, '[QW-2] '), '[QW-2] (restitución) aparece una sola vez como ID en la regla');
TestHelper::assertSame(1, substr_count($rulesUx, '[CR-3] '), '[CR-3] existe como ID único');
TestHelper::assertSame(1, substr_count($rulesUx, '[QW-3] '), '[QW-3] existe como ID único');
TestHelper::assertSame(1, substr_count($rulesUx, '[CR-4] '), '[CR-4] existe como ID único');
TestHelper::assertStringContains('Registro canónico de heurísticas', $rulesUx, 'La regla incluye tabla sumaria de heurísticas');

TestHelper::assertStringContains('[CR-3]', $audits, 'audits.md refleja CR-3 (mobile)');
TestHelper::assertStringContains('[QW-3]', $audits, 'audits.md refleja QW-3 (lead-time)');

$tablesCss = (string)@file_get_contents($root . '/src/css/04-components/tables.css');
$modalsCss = (string)@file_get_contents($root . '/src/css/04-components/modals.css');
TestHelper::assertStringContains('MOBILE ORDER CARDS (CR-3)', $tablesCss, 'tables.css actualizado a CR-3 (mobile)');
TestHelper::assertStringContains('Lead Time Alert Box in Modal (QW-3)', $modalsCss, 'modals.css actualizado a QW-3 (lead-time)');

TestHelper::assertStringContains('[CR-3]', $qaReport, 'qa-audit-report.md renumerado a CR-3 (mobile)');
TestHelper::assertStringContains('[QW-1]–[QW-3]', $qaReport, 'qa-audit-report.md lista IDs únicos');
TestHelper::assertStringContains('[CR-3]', $archPed, 'archive/pedidos.html renumerado a CR-3');
TestHelper::assertStringContains('[QW-3]', $archDet, 'archive/detalle.html renumerado a QW-3');
TestHelper::assertStringContains('[QW-3]', $archIdx, 'archive/index.html renumerado a QW-3');

// ============================================================================
// H-023: SINGLE-SOURCE + WORKFLOW GLUE
// ============================================================================
TestHelper::section('H-023 · rules/general.md canónico y workflows/general.md como glue');

TestHelper::assertStringContains('Fuente canónica (Gobernanza · Acción 4 · H-023)', $rulesGen, 'rules/general.md declara su estatus canónico');
TestHelper::assertStringContains('protocolo-divergencia-cli-http.md', $rulesGen, 'rules/general.md conserva ancla del protocolo (Acción 3)');
TestHelper::assertStringContains('test-fase-4-acumulado.php', $rulesGen, 'rules/general.md conserva mandato de regresión Fase 4 (Acción 3)');
TestHelper::assertStringContains('(R-01)', $rulesGen, 'rules/general.md ancla R-01 en su guardrail soft-delete');
TestHelper::assertStringContains('(R-03)', $rulesGen, 'rules/general.md ancla R-03 en su guardrail de autonomía');

TestHelper::assertStringContains('rules/general.md', $workGlue, 'workflows/general.md referencia la fuente canónica');
TestHelper::assertStringContains('Glue', $workGlue, 'workflows/general.md se identifica como glue');
TestHelper::assert(!str_contains($workGlue, 'DELETE FROM'), 'workflows/general.md no duplica la regla de soft-delete');
TestHelper::assert(!str_contains($workGlue, 'protocolo-divergencia-cli-http.md'), 'workflows/general.md no duplica el gate normativo');

// ============================================================================
// SANIDAD SINTÁCTICA DE ARCHIVOS TOCADOS
// ============================================================================
TestHelper::section('Sanidad sintáctica (php -l / node --check)');

foreach (['views/pages/usuarios_content.php', 'views/pages/creaciones_content.php'] as $phpFile) {
    $out = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($root . '/' . $phpFile) . ' 2>&1', $out, $code);
    TestHelper::assertSame(0, $code, "php -l {$phpFile} sin errores de sintaxis");
}
$out = [];
$code = 0;
exec('node --check ' . escapeshellarg($root . '/src/js/modules/users.js') . ' 2>&1', $out, $code);
TestHelper::assertSame(0, $code, 'node --check users.js sin errores de sintaxis');

exit(TestHelper::summary());