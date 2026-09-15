# Gobernanza · Acción 6 · Desacople de Fases/Subfases de `rules/general.md` hacia la Constitución

**Fuente:** auditoría de flujo del agente (14/09/2026) · recomendación sobre la arquitectura de fuentes de verdad (P1/P2).
**Estado:** completado & auditado (14/09/2026) · decisiones aprobadas por el usuario: detalle en `roadmap.md` (P2), resincronizar `proceso-desarrollo-fases.md` (P5), re-anclaje quirúrgico de la suite de la Acción 5.

## Qué hace

Dispersa el **estado histórico** que hoy vive incrustado en la regla operativa `.agents/rules/general.md` (P1), devolviendo cada contenido a su nivel de precedencia correcto:

1. **(P1 puro)** `.agents/rules/general.md` queda como regla operativa solamente: header canónico + fan-out, §loop Red→Green→Refactor con redirect a `spec/`, §gate 3-tier (normativo, intacto), §invariante de subfases atómicas y §guardrails. **Cero estados de fases, cero cifras de aserciones.**
2. **(P2)** `spec/constitution/roadmap.md` se convierte en el **registrador de ciclo de vida** por fases/subfases: estado de cada fase, subfase activa (4.1), cifras verificadas (`1,287`, `755/755`) y puntero a la feature activa `spec/features/NNN/tasks.md`.
3. **(P5)** `docs/architecture/proceso-desarrollo-fases.md` se resincroniza como narrativa histórica (estado real: Fase 3 completada, Fase 4 en curso).
4. **(Re-anclaje)** `tests/test-gobernanza-accion-5.php` traslada sus aserciones canónicas de cifras (`1,287`, `755/755`) de `rules/general.md` a `roadmap.md`, condición necesaria para adelgazar la regla sin romper el gate de la Acción 5.

## Criterios de aceptación

- [x] `rules/general.md` no contiene `1,287`, `755/755`, `Phase 1`, `Phase 3` ni ninguna cifra de subfases (3.6.x).
- [x] `rules/general.md` conserva las anclas exigidas por las Acciones 3 y 4: `protocolo-divergencia-cli-http.md`, `test-fase-4-acumulado.php`, `(R-01)`, `(R-03)` y el header `Fuente canónica (Gobernanza · Acción 4 · H-023)`.
- [x] `rules/general.md` contiene el redirect normativo a `spec/constitution/roadmap.md` y a `spec/features/NNN/tasks.md`, y describe el loop Red→Green→Refactor.
- [x] `roadmap.md` contiene la sección "Ciclo de vida por fases/subfases" con: Fase 1–2 Hecho, Fase 3 Hecho (`1,287`, `755/755`), Fase 4 en curso (4.1–4.5 con 4.1 activa) y Fase 5 continua.
- [x] `docs/architecture/proceso-desarrollo-fases.md` resincronizado: Fase 3 Completado, Fase 4 En curso (4.1 Auth & Sesión de Cliente); sin contradicciones con el roadmap.
- [x] `.agents/workflows/general.md` (glue) redirige el estado de fases a `spec/constitution/roadmap.md`.
- [x] Suite `tests/test-gobernanza-accion-6.php` al 100%: verifica el adelgazamiento, las anclas conservadas, el redirect y la coherencia del registro en `roadmap.md`.
- [x] `tests/test-gobernanza-accion-5.php` re-anclado a `roadmap.md` y en verde.
- [x] Gate 3-tier: regresión 3.1/3.6.5/gobernanza-2/3/4/5 + runner fase-4 + `cuenta-aserciones` (1,287) en verde.
- [x] Reporte `docs/testing/gobernanza-accion-6-desacople-fases.md` con "Fallos Detectados & Correcciones Quirúrgicas" y enlace en `docs/README.md`.
- [x] `roadmap.md` actualizado y **HALT** esperando aprobación explícita.

## Fuera de alcance

Cualquier cambio funcional de negocio, las Fases 1–5 en sí mismas, el contenido de los tests de producto (`test-subfase-*`), y el contenido narrativo de `AGENTS.md §3` (ya es normativa P1 sin estado histórico). No se renumera ni repite ninguna categoría de hallazgo H-00X previa.