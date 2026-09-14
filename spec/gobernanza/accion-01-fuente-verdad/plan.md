# Gobernanza · Acción 1: Fuente de Verdad Única — Plan

## Enfoque

Cambio quirúrgico de documentación de gobernanza + purga de un mecanismo muerto ("Memory Bank"). No toca código de negocio. La libertad de edición se acota a: `AGENTS.md`, `.agents/rules/docs-source-of-truth.md`, docs de arquitectura/testing afectadas, `README.md`, `.htaccess` y las 2 aserciones de test que fijan el patrón `.htaccess`.

## Jerarquía de precedencia canónica (P1–P6)

Definida y aprobada por el usuario (14/09/2026):

1. **P1 · Reglas operativas:** `AGENTS.md` + `.agents/rules/*` — cómo trabajar. Permanentes.
2. **P2 · Constitución:** `spec/constitution/` (`mission.md` > `tech-stack.md` > `roadmap.md`).
3. **P3 · ADRs:** solo un ADR nuevo deroga a otro.
4. **P4 · Feature vigente:** `spec/features/NNN/` (`spec.md` > `plan.md` > `tasks.md`).
5. **P5 · Docs de referencia:** si contradicen a P1–P4 es *defecto del doc*; se corrige el doc, jamás la regla ni el código.
6. **P6 · Código fuente:** autoridad material; desincronización → el código prevalece y el agente DEBE reportar al humano.

## Implementación

1. `spec/gobernanza/accion-01-fuente-verdad/` → `spec.md`, `plan.md`, `tasks.md` (este documento).
2. `AGENTS.md §6`: insertar bloque "Canonical Source-of-Truth Precedence".
3. `AGENTS.md §3`: añadir paso de HTTP curl checks al gate de subfase.
4. `.agents/rules/docs-source-of-truth.md`: sección "Precedencia de Fuentes (Canónico)".
5. Purga de "Memory Bank" en `docs/architecture/` (proceso-desarrollo-fases, ADR-011, phase-3-plan, security, subfase-3.6-auditoria-seguridad), `docs/testing/` (qa-audit-report, subfase-3.6.5-rendimiento-regresion) y `README.md`.
6. `.htaccess`: eliminar `memory-bank` del bloqueo de directorios.
7. `tests/test-subfase-3.1.php:322` y `tests/test-subfase-3.6.5.php:380`: actualizar la aserción al patrón `app|database|logs|tests|spec`. Se documenta como **cambio de contrato justificado** (purgar un directorio inexistente, no debilitamiento de aserción).
8. Gate de cierre: `php -l` en tests, `php -S localhost:8000`, suites 3.1 y 3.6.5 en verde.
9. Reporte ejecutivo + nota en `roadmap.md`.

## Decisiones

- **`.htaccess` purgado:** aprobado por el usuario (Opción A) pese a exigir la adaptación justificada de 2 aserciones.
- **Ubicación de artefactos SDD:** `spec/gobernanza/` (no numerado, fuera de la numeración de features de producto 005–008).
- **Históricos:** `reporte-auditoria-gobernanza.md` se conserva íntegro como registro de auditoría.

## Riesgos

- Romper suites 3.1/3.6.5 por el patrón `.htaccess` → mitigado con actualización coordinada de aserciones y re-ejecución.
- Referencias residuales no detectadas en el grep inicial → mitigado con gate de `grep` final.
- El servidor local no está corriendo → las suites HTTP exigen levantar `php -S localhost:8000`.