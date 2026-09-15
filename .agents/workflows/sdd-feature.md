---
description: Workflow-glue de enrutamiento SDD. El flujo guiado canónico (fases Specify → Plan → Tasks → Implement → Verify, plantillas y HALT) vive en el skill .agents/skills/sdd-feature/SKILL.md (universal: opencode y Antigravity).
---

# Workflow SDD para Features — Glue de Enrutamiento

> **Este archivo NO duplica contenido normativo.** Es solo enrutamiento (H-023).
> El flujo guiado canónico de Spec-Driven Development para **crear una feature nueva** vive en el skill:
>
> **`.agents/skills/sdd-feature/SKILL.md`** (se registra como `/sdd-feature` en Antigravity
> y es cargable en opencode por `skill` o el comando `.opencode/command/sdd-feature.md`).

## Cómo enrutar

1. **Feature nueva** o cambio no trivial → abrir el skill `sdd-feature` y seguir sus 6 fases
   (P0 contexto → Specify → Plan → Tasks → Implement → Verify) con **HALT por fase**.
2. **Plantillas:** `.agents/skills/sdd-feature/templates/` (`spec.md`, `plan.md`, `tasks.md`).
3. **Siguiente número NNN:** `.agents/skills/sdd-feature/scripts/next-feature-number.sh`.
4. **Metodología SDD de referencia:** skill `spec-driven-development`.
5. **Work on feature activa:** si el trabajo ocurre DENTRO de una feature ya existente
   (004 activa), usar `spec/features/NNN-nombre-feature/tasks.md` directamente, sin reabrir el skill.

> Límite: no regresar a la raíz SDD para bugs puntuales de una feature en curso ni para
> labores de gobernanza (`spec/gobernanza/`).