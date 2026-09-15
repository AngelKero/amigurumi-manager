<?php
/**
 * Gobernanza · Acción 6 · Desacople de Fases/Subfases hacia la Constitución (suite propia)
 * Algodón Nórdico Design System
 *
 * Verifica el reparto correcto de fuentes de verdad tras la Acción 6:
 *   - `.agents/rules/general.md` (P1) queda como regla operativa: conserva el gate 3-tier,
 *     los guardrails R-01/R-03 y el anclaje H-023 del protocolo/regresión, pero ELIMINA
 *     todo estado histórico de fases y cifras de aserciones (ya no duplica el roadmap).
 *   - `spec/constitution/roadmap.md` (P2) asume el rol de registrador de ciclo de vida
 *     (fases/subfases, cifras verificadas 1,287 y agrupación 755/755, subfase 4.1 activa).
 *   - `docs/architecture/proceso-desarrollo-fases.md` (P5) resincronizado al estado real.
 *   - `.agents/workflows/general.md` (glue) redirige el estado de fases al roadmap.
 *
 * Ejecución:
 *   php tests/test-gobernanza-accion-6.php > logs/gobernanza-accion-6-cli.log 2>&1
 */

declare(strict_types=1);

use Tests\TestHelper;

require_once __DIR__ . '/TestHelper.php';

TestHelper::init('Gobernanza · Acción 6 · Desacople de Fases/Subfases (P1/P2/P5)');

$root = dirname(__DIR__);

$rulesGen    = (string)@file_get_contents($root . '/.agents/rules/general.md');
$roadmap     = (string)@file_get_contents($root . '/spec/constitution/roadmap.md');
$procesoFases = (string)@file_get_contents($root . '/docs/architecture/proceso-desarrollo-fases.md');
$workGlue    = (string)@file_get_contents($root . '/.agents/workflows/general.md');

// ============================================================================
// 1. RULES/GENERAL.MD ADELGAZADA (P1): SIN ESTADO HISTÓRICO NI CIFRAS
// ============================================================================
TestHelper::section('rules/general.md sin estado de fases ni cifras (negativas)');

foreach (['1,287', '755/755', 'Phase 1', 'Phase 3', '93/93', 'Total acumulado'] as $prohibited) {
    TestHelper::assert(
        !str_contains($rulesGen, $prohibited),
        "rules/general.md no contiene '{$prohibited}' (estado histórico fuera de P1)"
    );
}

// ============================================================================
// 2. RULES/GENERAL.MD CONSERVA ANCLAS Y REDIRIGE A SPEC (P1)
// ============================================================================
TestHelper::section('rules/general.md conserva anclas normativas y redirect a spec/');

TestHelper::assertStringContains('Fuente canónica (Gobernanza · Acción 4 · H-023)', $rulesGen, 'rules/general.md mantiene su estatus canónico');
TestHelper::assertStringContains('protocolo-divergencia-cli-http.md', $rulesGen, 'rules/general.md conserva ancla del protocolo de divergencia');
TestHelper::assertStringContains('test-fase-4-acumulado.php', $rulesGen, 'rules/general.md conserva mandato de regresión acumulada Fase 4');
TestHelper::assertStringContains('(R-01)', $rulesGen, 'rules/general.md conserva ancla R-01 (soft-delete)');
TestHelper::assertStringContains('(R-03)', $rulesGen, 'rules/general.md conserva ancla R-03 (autonomía multi-artesana)');
TestHelper::assertStringContains('spec/constitution/roadmap.md', $rulesGen, 'rules/general.md redirige el estado de fases al roadmap (P2)');
TestHelper::assertStringContains('spec/features/NNN/tasks.md', $rulesGen, 'rules/general.md enruta el detalle operativo a tasks.md de la feature activa');
TestHelper::assertStringContains('Red → Green → Refactor', $rulesGen, 'rules/general.md conserva el loop de ingeniería');
TestHelper::assertStringContains('HALT', $rulesGen, 'rules/general.md conserva el HALT por subfase');
TestHelper::assertStringContains('Divergencia CLI vs HTTP', $rulesGen, 'rules/general.md conserva el triaje de divergencia en el gate');

// ============================================================================
// 3. ROADMAP.MD COMO REGISTRADOR DE CICLO DE VIDA (P2)
// ============================================================================
TestHelper::section('spec/constitution/roadmap.md registra el ciclo de vida');

TestHelper::assertStringContains('Ciclo de vida por fases/subfases', $roadmap, 'roadmap.md incorpora el registrador de ciclo de vida');
TestHelper::assertStringContains('1,287', $roadmap, 'roadmap.md registra la cifra verificada de Fase 3');
TestHelper::assertStringContains('755/755', $roadmap, 'roadmap.md registra la agrupación 3.6.x (755/755)');
TestHelper::assertStringContains('4.1', $roadmap, 'roadmap.md registra la subfase 4.1');
TestHelper::assertStringContains('4.5', $roadmap, 'roadmap.md registra la subfase 4.5');
TestHelper::assertStringContains('test-subfase-4.1.php', $roadmap, 'roadmap.md ancla la suite CLI de la subfase 4.1');
TestHelper::assertStringContains('005-catalogo-dinamico-filtros', $roadmap, 'roadmap.md apunta a la feature activa del siguiente hito (005 / 4.2)');
TestHelper::assertStringContains('1,307→1,287', $roadmap, 'roadmap.md documenta la transición de cifras (H-006)');
TestHelper::assert(substr_count($roadmap, '1,307') === 1, 'roadmap.md solo menciona 1,307 como transición documentada');
TestHelper::assertStringContains('registrador canónico', $roadmap, 'roadmap.md se identifica como registrador canónico de fases');

// ============================================================================
// 4. PROCESO-DESARROLLO-FASES.MD RESINCRONIZADO (P5)
// ============================================================================
TestHelper::section('docs/architecture/proceso-desarrollo-fases.md resincronizado');

TestHelper::assertStringContains('Completado & Verificado', $procesoFases, 'Fase 3 marcada como completada y verificada');
TestHelper::assertStringContains('1,287/1,287', $procesoFases, 'Fase 3 documenta el total 1,287/1,287');
TestHelper::assertStringContains('[COMPLETADA]', $procesoFases, 'El diagrama ASCII refleja Fase 3 completada');
TestHelper::assertStringContains('En Curso (Subfase 4.1', $procesoFases, 'Fase 4 marcada en curso con subfase 4.1 activa');
TestHelper::assertStringContains('[EN CURSO: 4.1]', $procesoFases, 'El diagrama ASCII refleja Fase 4 en curso (4.1)');
foreach (['Completado: 165/165', 'Completado: 141/141', 'Completado: 157/157', 'Completado: 151/151'] as $subfaseDone) {
    TestHelper::assertStringContains($subfaseDone, $procesoFases, "Las sub-subfases 3.6.x quedan completadas ('{$subfaseDone}')");
}
TestHelper::assert(!str_contains($procesoFases, 'Pendiente (Inicia tras concluir la Fase 3)'), 'Fase 4 ya no figura como pendiente');
TestHelper::assert(!str_contains($procesoFases, '[EN CURSO: 3.1'), 'El diagrama ASCII no conserva el estado obsoleto de Fase 3');
TestHelper::assert(!str_contains($procesoFases, 'En Ejecución'), 'Ninguna sub-subfase 3.6.x queda marcada en ejecución');

// ============================================================================
// 5. WORKFLOWS/GENERAL.MD RESPETA EL REPARTO (glue)
// ============================================================================
TestHelper::section('workflows/general.md (glue) enruta sin duplicar');

TestHelper::assertStringContains('rules/general.md', $workGlue, 'El glue referencia la regla canónica');
TestHelper::assertStringContains('spec/constitution/roadmap.md', $workGlue, 'El glue redirige el estado de fases al roadmap');
TestHelper::assert(!str_contains($workGlue, 'fases 1–5,'), 'El glue no repite el rango de fases como si fuera estado');
TestHelper::assert(!str_contains($workGlue, 'DELETE FROM'), 'El glue no duplica la regla de soft-delete');
TestHelper::assert(!str_contains($workGlue, 'protocolo-divergencia-cli-http.md'), 'El glue no duplica el gate normativo');

// ============================================================================
// SANIDAD SINTÁCTICA DE ARCHIVOS NUEVOS/EDITADOS
// ============================================================================
TestHelper::section('Sanidad sintáctica (php -l)');

foreach (['tests/test-gobernanza-accion-5.php', 'tests/test-gobernanza-accion-6.php'] as $phpFile) {
    $out = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($root . '/' . $phpFile) . ' 2>&1', $out, $code);
    TestHelper::assertSame(0, $code, "php -l {$phpFile} sin errores de sintaxis");
}

exit(TestHelper::summary());