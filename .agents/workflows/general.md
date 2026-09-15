---
description: Workflow-glue de enrutamiento. El contenido normativo del flujo iterativo, del gate 3-tier y de los guardrails vive en .agents/rules/general.md (fuente canónica, H-023).
---

# Workflow General — Enrutador de Flujos (Glue)

> **Este archivo NO contiene reglas normativas.** Es un índice de enrutamiento.
> La fuente canónica del flujo iterativo, el **3-Tier Testing & Sign-off Gate** y los guardrails operativos es
> **`.agents/rules/general.md`** (Gobernanza · Acción 4 · H-023). Cualquier edición de fondo se hace ahí, no aquí.

## Cómo enrutar según el tipo de tarea

| Tipo de tarea | Sigue el flujo de |
| :--- | :--- |
| Desarrollo / refactor / backend / gates de subfase | [`.agents/rules/general.md`](../rules/general.md) — loop Red→Green→Refactor, gate 3-tier, guardrails R-01…R-10 · **estado de fases/subfases en [`spec/constitution/roadmap.md`](../../spec/constitution/roadmap.md)** |
| Feature nueva o cambio no trivial (SDD) | skill [`sdd-feature`](../skills/sdd-feature/SKILL.md) · comando `/sdd-feature` (opencode: [`.opencode/command/sdd-feature.md`](../../.opencode/command/sdd-feature.md)) · glue [`.agents/workflows/sdd-feature.md`](./sdd-feature.md) · artefactos en `spec/features/NNN/` |
| UI/UX / design system "Algodón Nórdico" | [`.agents/rules/ui-ux-design-system.md`](../rules/ui-ux-design-system.md) |
| Documentación / fuente de verdad (P1–P6) | [`.agents/rules/docs-source-of-truth.md`](../rules/docs-source-of-truth.md) |
| Seguridad de token / XSS / CSP / `innerHTML` | [`.agents/rules/innerhtml-dom-safety.md`](../rules/innerhtml-dom-safety.md) + `AGENTS.md §5 (R-01…R-10)` |
| Dudas de precedencia entre fuentes | `AGENTS.md §6` (jerarquía P1–P6 de Spec-Driven Development) |

## Regla única de este glue

No duplicar contenido normativo. Si necesitas una regla nueva: editarla en su archivo canónico
(`rules/` o `AGENTS.md`) y enlazarla desde aquí; jamás copiarla.