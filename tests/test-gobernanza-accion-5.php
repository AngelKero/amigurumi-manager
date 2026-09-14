<?php
/**
 * Gobernanza · Acción 5 · Re-sincronización Documental (suite propia)
 * Algodón Nórdico Design System
 *
 * Verifica las tres entregas de la Acción 5:
 *   H-006 · cifras de aserciones verificables y regenerables (tests/cuenta-aserciones.php).
 *   H-013 · índice maestro docs/README.md exhaustivo: todo documento .md bajo docs/ enlazado.
 *   H-022 · workflow ui-ux-audit sin rutas muertas + política de deprecación + docs/archive/README.md.
 *
 * Ejecución:
 *   php tests/test-gobernanza-accion-5.php > logs/gobernanza-accion-5-cli.log 2>&1
 */

declare(strict_types=1);

use Tests\TestHelper;

require_once __DIR__ . '/TestHelper.php';

TestHelper::init('Gobernanza · Acción 5 · Re-sincronización Documental (H-006, H-013, H-022)');

$root = dirname(__DIR__);

$techStack   = (string)@file_get_contents($root . '/spec/constitution/tech-stack.md');
$agents      = (string)@file_get_contents($root . '/AGENTS.md');
$readmeTest  = (string)@file_get_contents($root . '/docs/testing/README.md');
$rulesGen    = (string)@file_get_contents($root . '/.agents/rules/general.md');
$roadmap     = (string)@file_get_contents($root . '/spec/constitution/roadmap.md');
$phase3Plan  = (string)@file_get_contents($root . '/docs/architecture/phase-3-plan.md');
$feat003spec = (string)@file_get_contents($root . '/spec/features/003-backend-clean-architecture/spec.md');
$ctx7fase3   = (string)@file_get_contents($root . '/docs/testing/auditoria-context7-fase-3.md');
$indexMaster = (string)@file_get_contents($root . '/docs/README.md');
$uiuxWork    = (string)@file_get_contents($root . '/.agents/workflows/ui-ux-audit.md');
$truthRule   = (string)@file_get_contents($root . '/.agents/rules/docs-source-of-truth.md');
$archiveReadme = (string)@file_get_contents($root . '/docs/archive/README.md');

// ============================================================================
// H-006: CIFRAS VERIFICABLES Y REGENERABLES
// ============================================================================
TestHelper::section('H-006 · Cifras de aserciones verificables y regenerables');

TestHelper::assert(file_exists($root . '/tests/cuenta-aserciones.php'), 'Existe el runner regenerable tests/cuenta-aserciones.php');

$canonicals = [
    'tech-stack.md'      => $techStack,
    'testing/README.md'  => $readmeTest,
    'rules/general.md'   => $rulesGen,
    'phase-3-plan.md'    => $phase3Plan,
    'feature-003/spec.md'=> $feat003spec,
    'context7-fase-3.md' => $ctx7fase3,
];
foreach ($canonicals as $name => $content) {
    TestHelper::assertStringContains('1,287', $content, "{$name} usa la cifra verificada 1,287");
    TestHelper::assert(!str_contains($content, '1,307'), "{$name} no conserva la cifra obsoleta 1,307");
}
TestHelper::assertStringContains('1,287', $roadmap, 'roadmap.md usa la cifra verificada 1,287');
TestHelper::assert(substr_count($roadmap, '1,307') === 1, 'roadmap.md solo menciona 1,307 documentando la transición (1,307→1,287)');
TestHelper::assertStringContains('1,307→1,287', $roadmap, 'roadmap.md sitúa la antigua cifra como parte del cambio documentado');
TestHelper::assertStringContains('cuenta-aserciones.php', $agents, 'AGENTS.md documenta el comando regenerable (Running Tests)');
TestHelper::assertStringContains('cuenta-aserciones.php', $readmeTest, 'docs/testing/README.md documenta el comando regenerable');
TestHelper::assert(!str_contains($techStack, '1,146'), 'tech-stack.md elimina la cifra imposible 1,146');

TestHelper::assertStringContains('141 / 141 (100%)', $readmeTest, 'Tabla de Testing refleja 3.6.2 = 141 (baseline semilla)');
TestHelper::assertStringContains('755/755', $rulesGen, 'rules/general.md refleja 755/755 en la sub-agrupación 3.6.x (baseline semilla)');

// Ejecución en vivo: el total debe regenerarse y coincidir.
$out = [];
$code = 0;
exec('php ' . escapeshellarg($root . '/tests/cuenta-aserciones.php') . ' 2>&1', $out, $code);
$runOutput = implode("\n", $out);
TestHelper::assertSame(0, $code, 'El runner regenerable termina con exit 0');
TestHelper::assertStringContains('TOTAL FASE 3 VERIFICADO: 1287 aserciones', $runOutput, 'El runner verifica 1,287 aserciones en runtime (100% verde)');

// ============================================================================
// H-013: ÍNDICE MAESTRO EXHAUSTIVO
// ============================================================================
TestHelper::section('H-013 · docs/README.md enlaza TODO docs/**/*.md');

TestHelper::assertStringContains('subfase-3.6.2-criptografia-autenticacion.md', $indexMaster, 'Índice maestro incluye reporte 3.6.2');
TestHelper::assertStringContains('subfase-3.6.5-rendimiento-regresion.md', $indexMaster, 'Índice maestro incluye reporte 3.6.5');
TestHelper::assertStringContains('auditoria-context7-fase-3.md', $indexMaster, 'Índice maestro incluye auditorías context7');
TestHelper::assertStringContains('protocolo-divergencia-cli-http.md', $indexMaster, 'Índice maestro incluye el protocolo de triaje');
TestHelper::assertStringContains('gobernanza-accion-4-consolidacion-reglas.md', $indexMaster, 'Índice maestro incluye los reportes de gobernanza');
TestHelper::assertStringContains('archive/README.md', $indexMaster, 'Índice maestro enlaza el histórico archive/README.md');

$missing = [];
foreach (glob($root . '/docs/**/*.md') ?: [] as $file) {
    if ($file === $root . '/docs/README.md') {
        continue;
    }
    $rel = str_replace($root . '/docs/', '', $file);
    if (!str_contains($indexMaster, './' . $rel)) {
        $missing[] = $rel;
    }
}
TestHelper::assert($missing === [], 'Cada docs/**/*.md está enlazado en el índice maestro' . ($missing ? ' → Huerfanos: ' . implode(', ', $missing) : ''));

// ============================================================================
// H-022: WORKFLOW SIN RUTAS MUERTAS + POLÍTICA DE DEPRECACIÓN
// ============================================================================
TestHelper::section('H-022 · Deprecación/archivado y workflow ui-ux-audit sin rutas muertas');

foreach (['index.html', 'css/styles.css', 'js/app.js', '.docs/ui-ux-skill-report.md'] as $deadRef) {
    TestHelper::assert(!str_contains($uiuxWork, $deadRef), "workflows/ui-ux-audit.md no referencia la ruta muerta '{$deadRef}'");
}
TestHelper::assertStringContains('views/pages/', $uiuxWork, 'workflows/ui-ux-audit.md apunta a vistas PHP reales');
TestHelper::assertStringContains('docs/design-system/audits.md', $uiuxWork, 'workflows/ui-ux-audit.md centraliza la salida en audits.md');
TestHelper::assertStringContains('design-auditor', $uiuxWork, 'workflows/ui-ux-audit.md enruta el skill design-auditor');

TestHelper::assertStringContains('Deprecación y Archivado', $truthRule, 'docs-source-of-truth.md incluye la política de deprecación');
TestHelper::assertStringContains('docs/archive/', $truthRule, 'La política canoniza docs/archive/ como histórico no autoritativo');
TestHelper::assertStringContains('ARQUIVADO', $truthRule, 'La política obliga a la cabecera de obsoleto ARQUIVADO');
TestHelper::assertStringContains('ARQUIVADO', $archiveReadme, 'docs/archive/README.md marca el histórico como no autoritativo');

// ============================================================================
// SANIDAD SINTÁCTICA DE ARCHIVOS NUEVOS/EDITADOS
// ============================================================================
TestHelper::section('Sanidad sintáctica (php -l y referencias)');

foreach (['tests/cuenta-aserciones.php', 'tests/test-gobernanza-accion-5.php'] as $phpFile) {
    $out2 = [];
    $code2 = 0;
    exec('php -l ' . escapeshellarg($root . '/' . $phpFile) . ' 2>&1', $out2, $code2);
    TestHelper::assertSame(0, $code2, "php -l {$phpFile} sin errores de sintaxis");
}

exit(TestHelper::summary());