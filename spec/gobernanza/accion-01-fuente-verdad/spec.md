# Gobernanza · Acción 1: Fuente de Verdad Única

**Estado:** en curso
**Fuente:** `reporte-auditoria-gobernanza.md` → hallazgos **H-001**, **H-009**

## Qué hace

Resuelve la tri-fuente de verdad que hoy contradice a los agentes:

1. Publica la jerarquía canónica de precedencia de fuentes (**P1–P6**) en `AGENTS.md §6` y la encadena desde `.agents/rules/docs-source-of-truth.md`.
2. Purga todas las referencias operativas al mecanismo obsoleto "Memory Bank" (`docs/`, `README.md`, `.htaccess`, tests), sustituyéndolas por la sincronización spec-driven (Cierre Documental).
3. Sincroniza el gate de testing en 3 niveles: `AGENTS.md §3` recupera el paso de comprobaciones HTTP curl que hoy solo está en `.agents/rules/general.md`.

## Por qué

La auditoría de gobernanza (14/09/2026) detectó que `AGENTS.md` declara `spec/` como *"single source of truth"*, `.agents/rules/docs-source-of-truth.md` otorga la autoridad material al código, y `docs/architecture/` aún ordena sincronizar un "Memory Bank" que ya no existe en el repositorio. Tres fuentes que mandan cosas distintas = comportamiento de agente impredecible.

## Criterios de aceptación

- [ ] `AGENTS.md §6` contiene la jerarquía de precedencia P1–P6 completa.
- [ ] `.agents/rules/docs-source-of-truth.md` incluye la sección "Precedencia de Fuentes" que reenvía al canon.
- [ ] `AGENTS.md §3` incluye el paso de HTTP curl checks en el gate de subfase.
- [ ] `grep -rin "memory-bank"` devuelve 0 resultados en `docs/`, `AGENTS.md`, `.agents/`, `.htaccess` y `README.md` (salvo el reporte de auditoría conservado como historia).
- [ ] `.htaccess` no contiene `memory-bank`.
- [ ] Las suites `tests/test-subfase-3.1.php` y `tests/test-subfase-3.6.5.php` pasan al 100%.
- [ ] Reporte ejecutivo `docs/testing/gobernanza-accion-1-fuente-verdad.md` con sección "Fallos Detectados & Correcciones Quirúrgicas".
- [ ] Nota de gobernanza registrada en `spec/constitution/roadmap.md`.

## Fuera de alcance

- Acciones 2–5 de la auditoría (seguridad de token, gate de testing Fase 4, consolidación de reglas, re-sincronización documental).
- Renumeración de invariantes R-01…R-09 (Acción 4).
- Cambios funcionales en el código de negocio (`app/`, `api/`, `views/`, `src/`).