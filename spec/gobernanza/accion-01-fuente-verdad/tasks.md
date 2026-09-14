# Gobernanza · Acción 1: Fuente de Verdad Única — Tareas

- [ ] Publicar jerarquía P1–P6 en `AGENTS.md §6` y encadenarla desde `.agents/rules/docs-source-of-truth.md`.
- [ ] Añadir paso HTTP curl checks al gate de `AGENTS.md §3`.
- [ ] Purgar "Memory Bank" en `docs/architecture/proceso-desarrollo-fases.md` (77, 201, 283).
- [ ] Purgar "Memory Bank" en `ADR-011`, `phase-3-plan.md`, `security.md` y `subfase-3.6-auditoria-seguridad.md`.
- [ ] Purgar "Memory Bank" en `docs/testing/qa-audit-report.md`, `subfase-3.6.5-rendimiento-regresion.md` y `README.md`.
- [ ] Eliminar `memory-bank` de `.htaccess`.
- [ ] Actualizar aserciones `.htaccess` en `tests/test-subfase-3.1.php` y `tests/test-subfase-3.6.5.php`.
- [ ] Gate: `grep -rin "memory-bank"` = 0 en docs/reglas/.htaccess/README (salvo reporte histórico).
- [ ] Gate: `php -l` en los 2 tests + suites 3.1 y 3.6.5 en verde con servidor local.
- [ ] Redactar `docs/testing/gobernanza-accion-1-fuente-verdad.md` con sección "Fallos Detectados & Correcciones Quirúrgicas".
- [ ] Registrar nota de gobernanza en `spec/constitution/roadmap.md`.
- [ ] HALT: aguardar aprobación explícita del usuario.