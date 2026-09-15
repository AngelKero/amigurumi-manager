---
name: sdd-feature
description: |
  Guided Spec-Driven Development (SDD) for creating a NEW feature in this repo.
  Use whenever the user asks to "crear una nueva feature", "nueva feature",
  "nuevo módulo/feature", "create a new feature", "spec out a feature",
  "start a feature the SDD way", or wants the spec/features/NNN-*/ docs
  (spec.md, plan.md, tasks.md) written BEFORE any code.
  Triggers on requests mentioning "SDD", "spec-driven", "spec antes de código",
  or "generar spec/plan/tasks". Do NOT use for bug fixes inside an existing
  feature (use the active feature's tasks.md instead) or for governance
  actions (those live under spec/gobernanza/).
---

# Guided SDD · Creación de una Feature Nueva

Flujo 100% guiado, **Spec-Driven y spec-anclado**, antes de tocar código.
Tes human acts as **Intent Validator**: cada fase termina con un **HALT** y la
aprobación explícita del usuario antes de avanzar.

> Compatible con **opencode** (`/sdd-feature` o carga del skill) y **Antigravity**
> (el skill `.agents/skills/<name>/SKILL.md` se registra solo como `/sdd-feature`).
> Metodología SDD de referencia: skill `spec-driven-development`.

## Normas duras (no negociables)

1. **No código antes del SPECIFY + PLAN aprobado.** Durante las fases 1–3 solo
   se crean/editan archivos `.md` dentro de `spec/features/NNN-nombre-feature/`.
2. Trabaja **solo dentro de `spec/features/NNN-nombre-feature/`** (P4) y respeta
   `spec/constitution/` (P2), los ADRs (P3) y `AGENTS.md §5` (R-01…R-10).
3. Cada fase acaba en **HALT**: detente y espera a que el usuario diga "continua".
4. No inventes el número `NNN`: usa el helper o enumera `spec/features/`.
5. El detalle de guías/hito vive ANTES de tocar código. Cifras, gates y registrador
   de fases: `spec/constitution/roadmap.md`.

## P0 · Preparación (contexto)

1. Lee `AGENTS.md §5–§6` y `spec/constitution/roadmap.md` (qué fase va, feature activa).
2. Enumera `spec/features/` para ver la numeración existente (001…008…).
3. Calcula el siguiente número: ejecuta `scripts/next-feature-number.sh --help`
   (o con el binario: `scripts/next-feature-number.sh`). Salida: `NNN` (3 cifras).
4. Crea `spec/features/NNN-nombre-feature/` donde `nombre-feature` es un kebab-case
   corto y descriptivo en español (p.ej. `009-carrito-compra-cliente`).

## Fase 1 · Specify (`spec.md`)

Redacta `spec.md` tomando la plantilla de
`templates/spec.md.template` (relativa a este skill: `.agents/skills/sdd-feature/`).
Contenido:
- **Qué hace:** vista desde el usuario, sin detalles de implementación.
- **Por qué:** valor real y por qué ahora.
- **Criterios de aceptación:** `- [ ]` observables, binarios y comprobables
  (suite CLI, curl HTTP o verificación manual concreta).
- **Fuera de alcance:** límites claros y qué feature futura lo cubre (005–008 ya planificadas).
- Respeta: el idioma español, el estilo del doc 004, y NO inventes plazos de taller (R-03).

**HALT:** entrega `spec.md` al usuario; NO vuelvas a tocar herramientas hasta su aprobación (o correcciones indicadas).

## Fase 2 · Plan (`plan.md`)

Usa `templates/plan.md.template`. Respeta `spec/constitution/tech-stack.md` y ADRs:
- **Enfoque:** estrategia arquitectónica (Clean Architecture: `app/` núcleo, `api/`
  controladores ≤ 60 líneas, `views/` presentación, `src/js/modules/` ES modules).
- **Implementación:** archivos concretos por capa y pasos técnicos.
- **Decisiones:** decisiones de diseño y alternativas descartadas.
- **Riesgos:** puntos de fallo y mitigación.

**HALT:** entrega `plan.md` al usuario; sin su aprobación no hay `tasks.md` ni implementación.

## Fase 3 · Tasks (`tasks.md`)

Usa `templates/tasks.md.template`. Desglose accionable:
- Subfases numeradas `NNN.1…NNN.x` con su **gate 3-tier** por subfase:
  suite `tests/test-subfase-NNN.x.php` → `logs/subfase-NNN.x-cli.log`; curl HTTP →
  `logs/subfase-NNN.x-http.log`; reporte `docs/testing/subfase-NNN.x-[nombre].md`;
  protocolo de divergencia `docs/testing/protocolo-divergencia-cli-http.md` (H-015).
- Checklist `- [ ]` con criterio de verificación por tarea (comando test/lint/curl).
- Regresión obligatoria: si es feature 004–008, `php tests/test-fase-4-acumulado.php`;
  siempre `php tests/test-subfase-3.6.5.php` (Fase 3) y `php tests/cuenta-aserciones.php` (1,287).
- Paso final: criterios de `spec.md` al 100%, mover a **Hecho ✅** en `spec/constitution/roadmap.md`.

**HALT:** entrega `tasks.md` al usuario para su validación.

## Fase 4 · Implementación (tras aprobación)

- Ejecuta las tareas una a una, marcándolas `- [x]` conforme se verifiquen.
- Por subfase: loop **Actuar → Observar → Corregir** y **gate 3-tier** completo.
- Código solo dentro de las capas correctas:
  cero SQL fuera de `app/Repositories/`, controladores `api/` delgados, sin
  `innerHTML` interpolado con datos (usa `textContent`/`escapeHtml`, H-004).
- Cierra cada subfase con **HALT** y aprobación antes de la siguiente.

## Fase 5 · Verificación & Cierre

1. Suite de la factory de cada subfase + regresión completa en verde.
2. Marca los criterios de aceptación de `spec.md` (`- [x]`).
3. Actualiza `spec/constitution/roadmap.md`: feature pasa a **Hecho ✅**
   (o queda "En curso" con su subfase activa en la sección de ciclo de vida).
4. Reporta el resumen e **HALT**: solicita la aprobación explícita del usuario
   antes de iniciar cualquier otra feature.

## Restricciones de directorio de recursos

- Plantillas: `templates/*.md.template` (léelas con la herramienta Read).
- Helper de numeración: `scripts/next-feature-number.sh`.
- Todos los recursos sean relativos al proyecto: ejecuta los comandos desde la
  raíz del repo; dentro del script se calculan rutas relativas a sí mismo.