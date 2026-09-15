# Gobernanza · Acción 6 · Desacople de Fases/Subfases hacia la Constitución

- **Fecha de Ejecución:** 14/09/2026
- **Responsable:** Agente IA (opencode) · aprobación humana previa del plan (via question tool)
- **Enmarca:** H-005 (anti-rule-rot) · H-006 (cifras regenerables) · H-013 (índice maestro) · H-023 (single-source)
- **Log crudo:** `logs/gobernanza-accion-6-cli.log`

## Objetivo y ámbito

`.agents/rules/general.md` (P1) mezclaba regla operativa con **estado histórico**: narrativa `Phase 1…Phase 5`, desglose 3.6.x y cifras de aserciones (`93/93`, `69/69`, `105/105`, `126/126`, `139/139`, `165/165`, `141/141`, `157/157`, `151/151`, `755/755`, `1,287/1,287`) que duplicaban `spec/constitution/roadmap.md` (P2) y `docs/architecture/proceso-desarrollo-fases.md` (P5). La Acción 6 reparte correctamente por precedencia:

1. **P1 → regla operativa pura:** loop Red→Green→Refactor, gate 3-tier, invariante de subfases atómicas, guardrails y **redirect** del estado de fases a `spec/`.
2. **P2 → `roadmap.md`** se convierte en el **registrador de ciclo de vida** (estado por fase, subfase activa 4.1, cifras verificadas, puntero a la feature 004).
3. **P5 → `proceso-desarrollo-fases.md`** resincronizado al estado real (Fase 3 completada; Fase 4 en curso 4.1).
4. **Re-anclaje →** `tests/test-gobernanza-accion-5.php` traslada sus canónicos `1,287`/`755/755` de `rules/general.md` a `roadmap.md`.

## 1. Matriz de entregables

| # | Entregable | Cambio verificado |
| - | :--- | :--- |
| 1 | `.agents/rules/general.md` | Sin `1,287`, `755/755`, `Phase 1`, `Phase 3`, `93/93` ni "Total acumulado"; conserva header H-023, `protocolo-divergencia-cli-http.md`, `test-fase-4-acumulado.php`, `(R-01)`, `(R-03)`; añade redirect a `spec/constitution/roadmap.md` y `spec/features/NNN/tasks.md` |
| 2 | `spec/constitution/roadmap.md` | Nueva sección "Ciclo de vida por fases/subfases (registrador canónico)": Fase 1–2 Hecho, Fase 3 Hecho (`1,287` · `755/755`), Fase 4 En curso (4.1 activa → `tests/test-subfase-4.1.php`), Fase 5 Continua |
| 3 | `docs/architecture/proceso-desarrollo-fases.md` | Fase 3 → Completado & Verificado (`1,287/1,287`, sub-subfases 3.6.2–3.6.5 con su conteo); Fase 4 → En Curso (Subfase 4.1); diagrama ASCII coherente; sin restos "Pendiente"/"En Ejecución" |
| 4 | `.agents/workflows/general.md` | Glue: fila de desarrollo enruta estado de fases a `spec/constitution/roadmap.md`; sin duplicar gate ni guardrails |
| 5 | `tests/test-gobernanza-accion-5.php` | Canonicals H-006 ya no incluyen `rules/general.md`; aserción `755/755` movida a `roadmap.md`; variable muerta `$rulesGen` eliminada |
| 6 | `tests/test-gobernanza-accion-6.php` | Suite nueva (45 aserciones) que blinda el adelgazamiento y el reparto P1/P2/P5 |

## 2. Evidencia de pruebas (Nivel 1 · CLI)

| Suite | Resultado | Evidencia |
| :--- | :---: | :--- |
| `tests/test-gobernanza-accion-6.php` | **45/45 (100%)** | `logs/gobernanza-accion-6-cli.log` |
| `tests/test-gobernanza-accion-5.php` | **43/43 (100%)** | `logs/gobernanza-accion-5-cli.log` |
| `tests/test-gobernanza-accion-4.php` | 100% (56) | `logs/gobernanza-accion-4-cli.log` |
| `tests/test-gobernanza-accion-3.php` | 100% (33) | `logs/gobernanza-accion-3-cli.log` |
| `tests/test-gobernanza-accion-2.php` | 100% | `logs/gobernanza-accion-2-cli.log` |
| `tests/test-subfase-3.1.php` | 100% (93) | `logs/subfase-3.1-cli.log` |
| `tests/test-subfase-3.6.5.php` | 100% (141) | `logs/subfase-3.6.5-cli.log` |
| `tests/test-fase-4-acumulado.php` | 100% (exit 0) | `logs/fase-4-acumulado.log` |
| `php tests/cuenta-aserciones.php` | **1,287 regeneradas** | salida CLI (H-006) |
| `php -l` (app/api/views/gobernanza/agents/tests) | 0 errores | — |

> **Divergencia CLI vs. HTTP:** no aplica. La Acción 6 es documental/gobernanza; las suites 2–6 son lectura estricta de archivos sin tocar `api/`. Las suites de producto (3.1/3.6.5/fase-4/cuenta-aserciones) quedaron en verde, por lo que no se activó el protocolo H-015.

## 3. Fallos Detectados & Correcciones Quirúrgicas

Durante la regresión de la Acción 5 se detectó **un fallo ajeno al re-anclaje**:

| # | Falla | Causa raíz | Corrección quirúrgica |
| - | :--- | :--- | :--- |
| 1 | `test-gobernanza-accion-5.php` exit 1: "Cada `docs/**/*.md` está enlazado en el índice maestro → Huerfanos: `archive/reporte-flujo-004-auth.md`" (H-013) | El reporte de auditoría del flujo 004 (`reporte-flujo-004-auth.md`), generado en sesiones previas y trasladado a `docs/archive/` entre sesiones, nunca fue registrado en el índice maestro ni en el histórico | Añadida su fila en `docs/archive/README.md` (estado: Histórico → reemplazado por `spec/features/004-auth-sesion-cliente/` + `docs/testing/`) y su línea en el árbol de `docs/README.md` (`./archive/reporte-flujo-004-auth.md`) |

Tras la corrección nº1 (parte Cierre Documental H-013, no del ámbito de desacople), la suite 5 queda **43/43 (100%)** sin más fallos.

## 4. Veredicto

- [x] `rules/general.md` adelgazado (negativas verificadas) y con anclas conservadas.
- [x] `roadmap.md` como registrador de ciclo de vida (P2) con cifras y subfase 4.1 activa.
- [x] `proceso-desarrollo-fases.md` (P5) resincronizado sin contradicciones.
- [x] `test-gobernanza-accion-5.php` re-anclado y verde.
- [x] Nueva suite 6 (45/45) + regresión completa en verde + `1,287` regeneradas.
- **Estado final:** Acción 6 implementada, testada y cerrada documentalmente. Resta actualización de `spec/gobernanza/accion-06/`. **Solicitando autorización explícita del usuario para continuar (HALT).**