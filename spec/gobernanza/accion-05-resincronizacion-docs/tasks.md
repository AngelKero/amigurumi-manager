# Gobernanza · Acción 5: Re-sincronización Documental — Tareas

> Aprobado 14/09/2026 · Alcance: H-006, H-013, H-022 · Históricos con cifras imposibles incluidos.

## M1 · Cifras de aserciones verificables y regenerables (H-006)
- [x] Creado `tests/cuenta-aserciones.php` (runner runtime: ejecuta 3.1–3.6.5, suma Exitosas).
- [x] Ground truth ejecutado (total real verificado en runtime).
- [x] tech-stack.md:29 → "141 directas, 1,307 acumuladas" + comando regenerable.
- [x] Históricos: subfase-3.6.5-rendimiento-regresion.md (totales + row 3.6.2) y auditoria-context7-fase-1.md:172.
- [x] Comando documentado en AGENTS.md §3 y docs/testing/README.md.

## M2 · Índice maestro regenerado (H-013)
- [x] docs/README.md regenerado y exhaustivo (testing completo, context7, gobernanza-*, archive/).
- [x] Verificación automatizada "todo docs/**/*.md enlazado" en la suite.

## M3 · Deprecación/archivado + workflow sin rutas muertas (H-022)
- [x] workflows/ui-ux-audit.md reescrito como glue (rutas reales, salida a audits.md).
- [x] Sección "Deprecación y Archivado" en docs-source-of-truth.md.
- [x] Creado docs/archive/README.md.

## M4 · Suite de la Acción 5
- [x] Creado tests/test-gobernanza-accion-5.php (H-006/H-013/H-022 + sanity).

## M5 · Gate 3-tier de la Acción 5
- [x] Runner cuenta-aserciones: total verificado (1,287 en semilla).
- [x] Suite test-gobernanza-accion-5.php al 100% (42/42).
- [x] Regresión gobernanza-2/3/4 + 3.1 + 3.6.5 + runner fase-4 en verde (96/33/56/93/141/4).
- [x] `php -l` + node --check + logs + smoke HTTP (204/200/401 + CSP).
- [x] Reporte docs/testing/gobernanza-accion-5-resincronizacion-docs.md + re-sync índice (security/ enlazado).
- [x] Entrada roadmap.md.
- [x] HALT: aguardar aprobación explícita del usuario.