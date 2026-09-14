---
name: spec-driven-development
description: Creates specs before coding. Use when starting a new project, feature, or significant change and no specification exists yet. Use when drafting a PRD or requirements document with objectives and scope, or when requirements are unclear, ambiguous, or only exist as a vague idea. Use when a single requirement spans several independently testable capabilities and needs decomposing into a capability map of modules before specifying.
---

# Spec-Driven Development (SDD)

## Overview

Write a structured specification before writing any code. The spec is the shared source of truth between you and the human engineer — it defines what we're building, why, and how we'll know it's done. Code without a spec is guessing.

In this repository, SDD operates in **Spec-Anchored** mode:
1. The project constitution lives in `spec/constitution/` (`mission.md`, `tech-stack.md`, `roadmap.md`).
2. Every feature lives in its own directory in `spec/features/NNN-nombre-feature/` with `spec.md`, `plan.md`, and `tasks.md`.
3. The human acts as the Intent Validator. Code is a transient artifact derived from the specification.

## The Gated Workflow

Spec-driven development follows a strict four-phase cycle:

```
SPECIFY ──→ PLAN ──→ TASKS ──→ IMPLEMENT ──→ VERIFY
   │          │        │           │            │
   ▼          ▼        ▼           ▼            ▼
 Human      Human    Human       Agent        Agent / QA
 reviews    reviews  reviews    executes      validates
```

### Phase 1: Specify (`spec.md`)
- Define what we are building from the user's perspective, without implementation details.
- State why it solves a real problem.
- Define specific, observable, binary acceptance criteria (`[ ]` / `[x]`).
- State what is explicitly out of scope.

### Phase 2: Plan (`plan.md`)
- Technical approach respecting `spec/constitution/tech-stack.md` and repository ADRs.
- List concrete architectural decisions and discarded alternatives.
- Enumerate files and modules to touch.
- Identify risks and mitigation strategies.

### Phase 3: Tasks (`tasks.md`)
- Actionable checklist derived from `plan.md`.
- Small, vertical slices with verification steps.
- Check off tasks as completed.
- Validate against acceptance criteria in `spec.md`.
- Move feature to "Hecho" in `spec/constitution/roadmap.md`.

### Phase 4: Implement & Verify
- Implement one task at a time.
- Follow test-driven development and loop engineering (`Actuar` → `Observar` → `Corregir`).
- Run native CLI tests and syntax checks.
- Do not proceed to the next feature without passing 100% of acceptance criteria.
