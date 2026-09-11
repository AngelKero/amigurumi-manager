---
name: clean-code-architect
description: Guides clean code and clean architecture principles across PHP, JS, CSS, SQL, HTML and project documentation. Use when writing, editing, reviewing, testing, or refactoring code in any language or framework; when creating files or deciding where code belongs; when designing or changing module boundaries, layers, and dependencies; when structuring docs; or when auditing or cleaning up an existing codebase. Covers naming, functions, comments, error handling, tests, single responsibility (SRP), code smells, SOLID, dependency inversion, component boundaries, layering, and verified surgical refactoring.
license: MIT
metadata:
  version: "3.2.0"
---

# Clean Code And Clean Architecture

Clean code makes intent, behavior, boundaries, and failure modes easy for the next maintainer to understand and safely change. Clean architecture keeps the cost of a change proportional to its scope instead of its shape.

Written for AI coding agents: adapt every rule to the project's language, framework, runtime, and style. Three agent-specific truths shape everything below:

1. You read faster than humans but forget context between sessions. Structure, names, placement, and written-down decisions are how your work survives you.
2. Your most common failures are not syntax errors. They are code in the wrong place, duplicated knowledge, mixed responsibilities, invented APIs, and unverified claims of success.
3. Your instinct is to wire the shortest path between two points — which is how a controller ends up calling a repository directly, skipping the only authorization check in the system.

## When To Use

Any coding work in any language: features, fixes, refactors, tests, reviews, scripts, SQL, documentation, UI, services, libraries. Specifically whenever you are about to create a file or directory, add behavior to an existing unit, decide where logic belongs, add a dependency, cross a boundary, introduce a layer, start a project, or clean one up.

## Operating Loop

### 1. Frame The Change
Before editing, identify the exact behavior or maintainability problem being solved, the assumptions that could change the implementation, the smallest useful scope, and the verification that will prove the change.

### 2. Read Local Context
Inspect the surrounding project before changing it: naming vocabulary and casing, directory layout, module and file boundaries, how files are registered or wired, error-handling style, test style, framework idioms — and whether the logic you are about to write already exists.

Search before you write. Local consistency beats generic preference.

### 3. Put Code In Its Place
Decide where the change belongs before writing it, at both scales: which unit owns this responsibility (see "Where Code Lives" and "One Job Per Unit"), and which side of which boundary it sits on (see "Architecture Rules"). Prefer extending the existing owner of a concern over creating a new home for it.

### 4. Change The Smallest Slice
Every changed line should trace to the request or to cleanup caused by the request.
- Keep the diff narrow and reviewable; prefer targeted edits over regenerating whole files.
- Remove imports, variables, functions, or files your change made unused.
- Leave the lines you touched slightly cleaner than you found them without widening the diff.
- Mention unrelated smells instead of fixing them silently.

### 5. Verify The Claim
Match verification to risk: a focused unit test or direct command for a small pure function; a reproducer test first for a bug fix; tests before and after for a refactor; an integration or contract check for an API or boundary change; a browser check for UI; a race-focused test for concurrency.

If verification cannot be run, say exactly what was not run and what risk remains. Never claim success from memory of what the code should do.

### 6. Review The Diff
Scan for unrelated edits and speculative abstractions; files created in the wrong place or duplicating existing ones; units that now do more than one job; dependencies that now point the wrong way; unclear names; comments compensating for confusing code; swallowed errors; hidden shared state; missing edge-case tests; new code nothing references or wiring never completed.

---

## Where Code Lives

Misplaced files and misplaced logic are among the most common agent failures. Placement is a design decision, not an afterthought.

### Placement Procedure
1. Find two or three existing artifacts most similar to what you are adding.
2. Mirror their directory, file naming pattern, internal structure, and registration.
3. Create a new file only when no cohesive home exists — a file is cohesive when its contents change for the same reason.
4. Wire the file in completely: imports, exports, module lists, route tables, DI registration, build config. An unreferenced file is dead code, not a feature.
5. If two homes are plausible, choose the one closest to the code that uses it, and say why.

### Placement Rules
- Resolve paths from the project root and its source layout, never from whatever directory happens to be current.
- Never default to the repository root. Root-level files are for project-wide concerns only.
- Place logic by responsibility, not by convenience: domain rules do not go in controllers, views, route handlers, or scripts; I/O does not go in pure domain modules; UI state does not go in data access code.
- Do not grow junk drawers. Adding to `utils`, `helpers`, `common`, or `misc` requires the same justification as creating a new module: name the domain concept instead (e.g. `CurrencyHelper`).
- Never create sibling variants of an existing file: no `_v2`, `_new`, `_final`, `_enhanced`, `_improved`, `_copy`, or date-suffixed names. Improve the original; version control keeps history.
- Scratch files, experiments, and one-off scripts go outside the project tree, or are deleted before completion. Leave no debug output or abandoned drafts in the repo.

---

## One Job Per Unit (Single Responsibility Principle)

Mixed responsibility is the smell agents produce most. Enforce it at every scale: function, class, module, file, directory, service.

**The one-sentence test:** Describe the unit's job in one sentence without "and", "also", or "then". If you cannot, it has more than one job.

**Kinds of responsibility** that belong in distinct units:
- Parsing and input validation
- Domain decisions and business rules
- Persistence and data access
- External integration
- Presentation and formatting
- Orchestration
- Logging and metrics
- Construction and wiring

New behavior goes to the unit that owns that responsibility, not the file you happen to be editing. Resist nearest-file gravity.

---

## Architecture Rules

**The Dependency Rule:** Source code dependencies must point only inward, toward higher-level policies. Nothing in an inner circle may know anything about an outer circle: not a class, a function, a variable, an annotation, or a data format.
- **Level is distance from the inputs and outputs**, not call order. Business rules are highest level; the database, web, UI, framework, and delivery mechanism are details.
- **Policy must not name a detail.** When policy needs something from a detail, declare the interface on the policy side and implement it outside (Dependency Inversion).
- **Confine details to their layer.** All SQL lives in the data-access layer. Result sets and HTTP request objects never travel inward.
- **Keep the component graph acyclic.** A cycle fuses components into one release unit.
- **Deduplicate only true duplication** — copies that must always change together. Copies that change at different rates for different reasons are not duplicates.

---

## Agent Failure Modes & Counter-Behaviors

| Failure Mode | Counter-Behavior |
| :--- | :--- |
| **Invented API** | Verify against the actual codebase, dependency versions, and schema. |
| **Reinvented Helper** | Search for existing implementations before writing (e.g. `CurrencyHelper.php`, `currency.js`). |
| **Wrong-Place File** | Follow Placement Procedure; mirror similar artifacts (e.g. styles in `src/css/`, scripts in `src/js/`). |
| **Sibling-Variant File** | Edit the original directly; never create `_v2` or `_new`. |
| **Nearest-File Gravity** | Route behavior to the unit that owns the responsibility. |
| **Shortest-Path Wiring** | Go through the layer that owns the rule (Middleware/Auth Guard before action). |
| **Detail Leaking Inward** | Keep outer-circle details out of inner-circle business logic. |
| **Regeneration Loss** | Make targeted edits; diff against the original before finishing. |
| **Placeholder as Done** | Ship complete working code; never leave stubs, `pass`, or fake demo logic. |
| **Unwired Artifact** | Complete registration, imports, and navigation links; prove reachability. |
| **False Completion** | Run verification in terminal and browser; quote exact output and remaining risks. |

---

## Completion Checklist

Before saying any task is complete, confirm:
1. The change solves the stated task, and every changed line traces to it.
2. New files sit in conventional locations, follow local naming, and are fully wired in.
3. No duplicate implementation or sibling-variant file was introduced.
4. Each new or grown unit passes the one-sentence test.
5. Every dependency added points inward, and no detail leaked into a policy module.
6. Names and structure reveal intent; errors, boundaries, and state are explicit.
7. Verification results are reported honestly with real tool outputs.
