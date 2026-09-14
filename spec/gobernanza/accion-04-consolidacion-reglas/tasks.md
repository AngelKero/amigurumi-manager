# Gobernanza · Acción 4: Consolidación de Reglas — Tareas

> Aprobado 14/09/2026 · Alcance: H-005, H-011, H-012, H-023 · Históricos QA/archive incluidos.

## M1 · Invariantes canónicos R-01…R-10 (H-005)
- [x] AGENTS.md §5 etiquetado con claves (R-01)…(R-10).
- [x] tech-stack.md "Límites Duros" → tabla-ancla por clave R-0X (sin numeración posicional).
- [x] Anclas R-01/R-02 en docs/api/README.md §8 y ADR-004.

## M2 · Reglas de directorio de creadores implantadas (H-011)
- [x] users.css: `.table-artisan-team` + `.avatar-artisan-initials` (renombre de `.user-avatar-circle`).
- [x] Vista usuarios_content.php (tabla + avatar), creaciones_content.php y users.js migrados.
- [x] Regla §5.16 marcada como implantada.

## M3 · Renumeración CR/QW única (H-012)
- [x] ui-ux-design-system.md §4: CR-1…CR-4 y QW-1…QW-3 + tabla sumaria.
- [x] docs/design-system/audits.md espejo de la numeración.
- [x] Comentarios de código vivos: tables.css (CR-2→CR-3) y modals.css (QW-2→QW-3).
- [x] Históricos: qa-audit-report.md, archive/detalle.html, index.html, pedidos.html.

## M4 · Single-source + workflow glue (H-023)
- [x] rules/general.md canónico (índice fan-out + guardrails → anclas R-01/R-02/R-03 + anclas Acción 3 preservadas).
- [x] workflows/general.md reescrito como glue de enrutamiento (sin contenido normativo).

## M5 · Gate 3-tier de la Acción 4
- [x] Suite tests/test-gobernanza-accion-4.php al 100% (56/56).
- [x] `php -l` vistas PHP tocadas + `node --check` users.js (y lint global 0 errores).
- [x] Servidor + regresión 3.1 (93) / 3.6.5 (141) / gobernanza-2 (96) / gobernanza-3 (33) + runner fase-4 (4) en verde.
- [x] Logs logs/gobernanza-accion-4-cli.log (+ smoke HTTP escenario A, `gobernanza-accion-4-http.log`).
- [x] Reporte docs/testing/gobernanza-accion-4-consolidacion-reglas.md con "Fallos Detectados & Correcciones Quirúrgicas" (2 casos resueltos).
- [x] Entrada en spec/constitution/roadmap.md.
- [x] HALT: aguardar aprobación explícita del usuario.