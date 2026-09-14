# Gobernanza · Acción 1: Fuente de Verdad Única — Reporte Ejecutivo

**Fecha:** 14/09/2026
**Origen:** Auditoría de gobernanza (`reporte-auditoria-gobernanza.md`) — hallazgos **H-001** y **H-009**.
**Artefactos SDD:** `spec/gobernanza/accion-01-fuente-verdad/` (`spec.md`, `plan.md`, `tasks.md`).

## Resumen

Se eliminó la tri-fuente de verdad que contradecía a los agentes y se purgó por completo el mecanismo obsoleto "Memory Bank". El repositorio queda con una única jerarquía de precedencia de fuentes publicada en `AGENTS.md §6` y encadenada desde `.agents/rules/docs-source-of-truth.md`, y con el gate de testing en 3 niveles sincronizado entre `AGENTS.md §3` y `.agents/rules/general.md`.

## Firma de cambios

| Cambio | Archivo | Detalle |
|---|---|---|
| Jerarquía de precedencia P1–P6 | `AGENTS.md §6` | Bloque "Canonical Source-of-Truth Precedence". |
| Regla de discrepancia | `AGENTS.md §6` | Prohibida la corrección silenciosa de reglas/spec/ADR. |
| Gobernanza SDD | `AGENTS.md §6` | Trabajo de gobernanza vive en `spec/gobernanza/`. |
| Gate 3-tier con HTTP curl (H-009) | `AGENTS.md §3` | Paso 3: comprobaciones HTTP curl a `logs/subfase-3.X-http.log`. |
| Precedencia de fuentes | `.agents/rules/docs-source-of-truth.md` | Nueva sección que reenvía al canon y jerarquiza `docs/` como P5. |
| Purga Memory Bank | `docs/architecture/` · `README.md` | Sustitución por sincronización spec-driven (Cierre Documental) en 7 documentos. |
| Bloqueo `.htaccess` | `.htaccess` | Eliminado `memory-bank` del listado de directorios bloqueados. |
| Aserciones actualizadas | `tests/test-subfase-3.1.php` · `tests/test-subfase-3.6.5.php` | Patrón `app|database|logs|tests|spec`. |
| Nota de gobernanza | `spec/constitution/roadmap.md` | Nueva sección "Gobernanza ✅". |

## Resultados de pruebas

| Suite | Aserciones | Resultado | Log |
|---|---|---|---|
| `tests/test-subfase-3.1.php` | 93/93 | ✔ 100% | `logs/subfase-3.1-cli.log` |
| `tests/test-subfase-3.6.5.php` (regresión acumulada) | 141/141 | ✔ 100% | `logs/subfase-3.6.5-cli.log` |

Grep residual: `rg -rin "memory[- ]bank" docs/ AGENTS.md .agents/ .htaccess README.md tests/` → **0 resultados**. `reporte-auditoria-gobernanza.md` se conserva íntegro como registro histórico de auditoría.

## Fallos Detectados & Correcciones Quirúrgicas

- **Adaptación justificada de aserción (cambio de contrato, NO debilitamiento):** las suites 3.1 (`test-subfase-3.1.php:322`) y 3.6.5 (`test-subfase-3.6.5.php:380`) fijaban una cadena literal `app|database|memory-bank|logs|tests` extraída de `.htaccess`. Al decidirse la purga del directorio inexistente `memory-bank/`, ambas aserciones se actualizaron al patrón resultante `app|database|logs|tests|spec`. El veto de carpetas del sistema (`tests/`, `logs/`, `database/`, `spec/`, `app/`) se mantiene íntegro; únicamente desaparece una ruta que nunca existió en el repositorio. Ambas suites confirman el nuevo contrato en verde.

## Pérdida de derechos de acción (invariante de subfase)

La Acción 2 del plan de gobernanza (blindaje de seguridad del ciclo de token: H-002, H-003, H-004) **no** se inició. Este informe cierra la Acción 1 conforme al invariante *Subphase Testing & Halting Gate*.