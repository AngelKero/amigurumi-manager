# Gobernanza · Acción 4 · Consolidación de Reglas (Anti-Rule-Rot)

**Fuente:** `reporte-auditoria-gobernanza.md` → Recomendación #4 → hallazgos **H-005**, **H-011**, **H-012**, **H-023**.
**Estado:** aprobado (14/09/2026) · decisión ampliada: H-012 se sincroniza también en `docs/testing/qa-audit-report.md` y `docs/archive/*.html` (históricos incluidos).

## Qué hace

Elimina la rotación de reglas y la duplicidad de fuentes normativas:

1. **(H-005 · Consistencia)** Los 10 invariantes de `AGENTS.md §5` se canónizan con claves semánticas **R-01…R-10**. `tech-stack.md` (constitución) pasa a citarlos por clave en una tabla-ancla, eliminando el conflicto de numeración posicional (#3 IDOR vs #3 garantías). Las fuentes narrativas soft-delete (`docs/api/README.md §8`, ADR-004) anclan a R-01/R-02.
2. **(H-011 · Realidad)** Las reglas fantasma `.table-artisan-team` y `.avatar-artisan-initials` se implantan de verdad en `src/css/04-components/users.css` y se aplican en `views/pages/usuarios_content.php`, `views/pages/creaciones_content.php` y `src/js/modules/users.js`; la regla §5.16 queda marcada como **implantada**.
3. **(H-012 · Unicidad)** Las heurísticas §4 se renumeran a IDs únicos (CR-1…CR-4, QW-1…QW-3) con tabla sumaria en la propia regla; el espejo `docs/design-system/audits.md` y todos los comentarios de código vivos se sincronizan, incluyendo los históricos `docs/testing/qa-audit-report.md` y `docs/archive/`.
4. **(H-023 · Single-source)** `.agents/rules/general.md` es la fuente canónica única del flujo iterativo + gate + guardrails; `.agents/workflows/general.md` queda como glue de enrutamiento sin contenido normativo duplicado; los guardrails duplicados (soft-delete, autonomía) pasan a anclar `R-01/R-02/R-03`.

## Criterios de aceptación

- [x] `AGENTS.md §5` etiquetado con claves `(R-01)`…`(R-10)`.
- [x] `tech-stack.md` "Límites Duros" como tabla-ancla por clave R-0X (sin redacción normativa duplicada, sin numeración posicional).
- [x] `docs/api/README.md §8` y `ADR-004` anclan R-01/R-02.
- [x] `.table-artisan-team` y `.avatar-artisan-initials` existen en CSS y se usan en vistas/JS; eliminada la clase `.user-avatar-circle`.
- [x] `ui-ux-design-system.md §4` con IDs únicos (CR-1…CR-4, QW-1…QW-3) y tabla sumaria; §5.16 marcada implantada.
- [x] Espejo `docs/design-system/audits.md`, comentarios de código, `qa-audit-report.md` y `docs/archive/` sincronizados.
- [x] `.agents/workflows/general.md` es glue (sin texto normativo, referencia `rules/general.md`).
- [x] Suite `tests/test-gobernanza-accion-4.php` al 100% + regresión 3.1/3.6.5/gobernanza-2/3 + runner fase-4 en verde.
- [x] Reporte `docs/testing/gobernanza-accion-4-consolidacion-reglas.md` con "Fallos Detectados & Correcciones Quirúrgicas".
- [x] `spec/constitution/roadmap.md` actualizado y **HALT**.

## Fuera de alcance

H-006 / H-013 / H-022 (Acción 5). H-007 (identidad de producto). H-010 (criterio Bootstrap dual). H-014…H-021, H-024…H-027. Sin cambios funcionales de negocio.