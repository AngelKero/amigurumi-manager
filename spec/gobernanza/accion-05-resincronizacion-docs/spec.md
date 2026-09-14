# Gobernanza · Acción 5 · Re-sincronización Documental commiteada

**Fuente:** `reporte-auditoria-gobernanza.md` → Recomendación #5 → hallazgos **H-006**, **H-013**, **H-022**.
**Estado:** aprobado (14/09/2026). Decisiones ampliadas: corregir los históricos con cifras imposibles (1,146 → 1,166) y reescribir `ui-ux-audit.md` como glue (no deprecarlo).

## Qué hace

Repara las fracturas de credibilidad de la documentación canónica y la blinda contra regresión:

1. **(H-006 · Cifras verificables y regenerables)** `tech-stack.md:29` afirmaba "141 directas, 1,146 acumuladas" (1,146 es aritméticamente imposible: las 9 suites previas a 3.6.5 suman 1,166 y el total de Fase 3 es 1,307). Cada cifra de aserciones pasa a ser **comando regenerable**: se crea `tests/cuenta-aserciones.php`, que ejecuta las 10 suites de Fase 3, suma `Exitosas` en runtime y emite el total verificado. Se corrigen también los residuos "1,146" en el reporte 3.6.5 (incluido el row erróneo que listaba 3.6.2 con 141 aserciones) y en `auditoria-context7-fase-1.md`.
2. **(H-013 · Índice maestro sincronizado)** `docs/README.md` no reflejaba las subfases 3.6.2–3.6.5, el protocolo de divergencia, las auditorías context7 ni los reportes de gobernanza (violación commiteada del Cierre Documental). Se regenera el índice maestro de forma exhaustiva y se añade **verificación automatizada**: la suite de la Acción 5 garantiza que todo `docs/**/*.md` esté enlazado en `docs/README.md`.
3. **(H-022 · Ciclo de vida documental + workflow sin rutas muertas)** `.agents/workflows/ui-ux-audit.md` referenciaba rutas inexistentes (`index.html`, `css/styles.css`, `js/app.js`, `.docs/ui-ux-skill-report.md`). Se reescribe como glue hacia rutas reales con salida a `docs/design-system/audits.md`, y se crea la **política de deprecación y archivado** (P1) más el índice `docs/archive/README.md`.

## Criterios de aceptación

- [x] `tech-stack.md:29` corrige a "141 aserciones directas, 1,307 acumuladas" y cita el comando regenerable `tests/cuenta-aserciones.php` (sin cifras fijas duplicadas).
- [x] `tests/cuenta-aserciones.php` ejecuta las 10 suites 3.1–3.6.5, suma `Exitosas` en runtime y verifica **1,307** (o el total real resultante).
- [x] Históricos corregidos: `subfase-3.6.5-rendimiento-regresion.md` (totales, row de 3.6.2) y `auditoria-context7-fase-1.md:172` a la cifra aritmética correcta.
- [x] Comando documentado en `AGENTS.md §3` y `docs/testing/README.md`.
- [x] `docs/README.md` regenerado y exhaustivo (testing 3.6.2–3.6.5, protocolo, context7, gobernanza-*, archive/).
- [x] Suite de la Acción 5 verifica que **cada `docs/**/*.md` está enlazado** en `docs/README.md`.
- [x] `.agents/workflows/ui-ux-audit.md` reescrito como glue (rutas reales, sin `.docs/`).
- [x] Sección "Deprecación y Archivado" en `.agents/rules/docs-source-of-truth.md` (P1) + `docs/archive/README.md`.
- [x] Gate 3-tier: suite Acción 5 al 100% + regresión 3.1/3.6.5/gobernanza-2/3/4 + runner fase-4 en verde.
- [x] Reporte `docs/testing/gobernanza-accion-5-resincronizacion-docs.md` con "Fallos Detectados & Correcciones Quirúrgicas".
- [x] `spec/constitution/roadmap.md` actualizado y **HALT**.

## Fuera de alcance

H-001/H-009 (Acción 1, cerrada), H-002/H-003/H-004 (Acción 2, cerrada), H-008/H-015/H-020 (Acción 3, cerrada), H-005/H-011/H-012/H-023 (Acción 4, cerrada). H-007, H-010, H-014, H-016…H-021, H-024…H-027 → Acción 6 (sin planificar). Ningún cambio funcional de negocio ni a los recuentos internos de las suites existentes.