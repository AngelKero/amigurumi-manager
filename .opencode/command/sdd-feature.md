---
description: Crea una feature nueva 100% guiada por Spec-Driven Development (SDD). Genera spec.md, plan.md y tasks.md en spec/features/ (con plantillas y HALT por fase) antes de escribir ningún código.
---

Carga el skill **`sdd-feature`** (tool `skill` con `name: sdd-feature`) y ejecuta su
flujo guiado completo para crear la nueva feature solicitada.

**Solicitud del usuario:** $ARGUMENTS

Reglas no negociables mientras ejecutas el flujo:

1. **No escribas NI UN archivo de código** durante las fases Specify y Plan: solo
   `.md` dentro de `spec/features/NNN-nombre-feature/`.
2. Determina el número `NNN` con:
   `.agents/skills/sdd-feature/scripts/next-feature-number.sh` (o `--help`).
3. Usa las plantillas del skill:
   `.agents/skills/sdd-feature/templates/{spec,plan,tasks}.md.template`.
4. Acaba cada fase con **HALT**: entrega `spec.md`, luego `plan.md`, luego `tasks.md`
   y espera la aprobación explícita del usuario antes de continuar.
5. Consulta `AGENTS.md §6`, `spec/constitution/roadmap.md` y la feature activa actual
   antes de numerar/ubicar la feature. Respeta `AGENTS.md §5` (R-01…R-10).
6. Si el usuario omite el nombre de la feature, proponle un kebab-case breve en español.