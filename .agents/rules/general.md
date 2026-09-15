---
trigger: always_on
---

# Iterative Development Workflow

> **Fuente canónica (Gobernanza · Acción 4 · H-023):** este archivo es el único lugar que contiene el flujo iterativo, el gate 3-tier y los guardrails operativos. `.agents/workflows/general.md` es solo un **workflow-glue** de enrutamiento que referencia este archivo; cualquier divergencia de contenido es un defecto (P5).
>
> **Estado de fases y subfases:** el avance por fases (1–5) y subfases secuenciales NO vive en esta regla. Su registrador canónico es `spec/constitution/roadmap.md` (P2); el detalle operativo de la feature activa está en `spec/features/NNN/tasks.md` (P4). Esta regla solo define CÓMO se trabaja (loop + gate + guardrails). Gobernanza · Acción 6.
>
> **Índice de reglas (fan-out):**
> | Tipo de tarea | Fuente |
> | :--- | :--- |
> | Desarrollo/refactor/backend | Este archivo (§Loop + §Gate + §Guardrails) · estado de fases en `spec/constitution/roadmap.md` |
> | Feature nueva o cambio no trivial | `.agents/workflows/sdd-feature.md` |
> | UI/UX / design system | `.agents/rules/ui-ux-design-system.md` |
> | Docs / fuente de verdad | `.agents/rules/docs-source-of-truth.md` |
> | Seguridad de token/XSS/CSP/innerHTML | Este archivo `§R-01…R-10` + `.agents/rules/innerhtml-dom-safety.md` |

## Fases y subfases (estado en spec/)

El proyecto avanza por **fases numeradas (1–5)** y **subfases secuenciales** cuyo estado, orden y criterio de avance están definidos y trazados en:

1. **`spec/constitution/roadmap.md`** — registrador de ciclo de vida: qué fase/subfase está Hecho, en curso o siguiente (P2).
2. **`spec/features/NNN-nombre-feature/tasks.md`** — checklist accionable de la feature activa, descompuesta en subfases con su propio gate (P4).

Antes de iniciar cualquier tarea, consulta ambas fuentes. El estado no se recuerda ni se asume: se lee de `spec/`.

## Loop de ingeniería (Red → Green → Refactor)

En cada subfase de la feature activa se ejecuta el bucle iterativo:

1. **Red:** escribir la suite CLI de la subfase (`tests/test-subfase-N.X.php`) que cubra los criterios de aceptación y verificar que falla contra el estado actual.
2. **Green:** implementar el código mínimo que ponga la suite en verde (100% aserciones).
3. **Refactor:** limpiar sin cambiar comportamiento (SRP, sin SQL fuera de repositorios, sin `innerHTML` interpolado); re-ejecutar la suite hasta mantener 100%.

Aplicar `Actuar` → `Observar` → `Corregir` ante cualquier fallo de tesis o sintaxis.

## 3-Tier Testing & Sign-off Gate

> **Referencias normativas ancladas:** protocolo de divergencia en `docs/testing/protocolo-divergencia-cli-http.md` (H-015) y regresión acumulada por fase en `tests/test-fase-4-acumulado.php` (H-020).

Al concluir CADA subfase, el agente DEBE ejecutar obligatoriamente el gate en 3 niveles antes de detenerse:

1. **Nivel 1 (Suite CLI):** `php tests/test-subfase-N.X.php > logs/subfase-N.X-cli.log 2>&1` → 100% aserciones en verde.
2. **Nivel 2 (Canal HTTP):** peticiones curl contra `php -S localhost:8000` registradas en `logs/subfase-N.X-http.log`.
3. **Nivel 3 (Reporte ejecutivo):** `docs/testing/subfase-N.X-[nombre].md` siguiendo la plantilla de `docs/testing/README.md`, con sección "Fallos Detectados & Correcciones Quirúrgicas" si algún test requirió adaptación Red-Green-Refactor.
4. **Divergencia CLI vs HTTP:** si discrepan, aplicar el protocolo de divergencia (`protocolo-divergencia-cli-http.md`): reproducir 2×, aislar la variable, clasificar **defecto de código** vs **defecto de entorno**, registrar la traza con escenario y NO silenciar ninguna divergencia. Defecto de código (C2/D) = bloqueo + HALT.
5. **Regresión por fase (H-020):** al tocar features 004–008, ejecutar también `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1`.
6. **Cierre documental:** actualizar feature `tasks.md`, **validar uno a uno los criterios de aceptación de `spec.md` (sección `Criterios de Aceptación · Gate de Validación` abajo)**, sincronizar `docs/` del dominio afectado y actualizar `spec/constitution/roadmap.md`.
7. **HALT COMPLETO:** detener el uso de herramientas y esperar la aprobación explícita por escrito del usuario antes de escribir código de la siguiente subfase. **Nunca empaquetar subfases múltiples.**

## Criterios de Aceptación · Gate de Validación Obligatorio (spec.md)

> **Mandatorio al terminar CADA subfase o feature** (antes de declarar "Hecho" / "APTO PARA AVANZAR"). Refuerza el paso 6 del gate 3-tier y es independiente del resultado del testing: una suite 100% en verde NO equivale a AC verificados si el plan cambió de alcance.

1. **Validar AC uno a uno:** leer `spec/features/NNN/spec.md` → sección `## Criterios de aceptación` y verificar CADA criterio `[ ]` contra la implementación real (`código + evidencia de prueba`). Documentar la trazabilidad en el reporte ejecutivo (tabla AC → evidencia).
2. **Si TODOS los AC son válidos** → marcarlos `[x]` en `spec.md`, cerrar documentalmente y **continuar al siguiente feature/subfase**, que a su vez debe validar sus propios AC. No hay avance sin validación de AC (todo debe quedar validado).
3. **Si ALGÚN AC NO es válido** (incumplido, cambiado de alcance o divergente de la spec) → **NO** marcar `[x]`, **NO** declarar la subfase "Hecho" y **NO** avanzar. En su lugar:
   a. **Modificar el plan y las tasks** (`plan.md` y `tasks.md` de la feature) para re-alinear el alcance con el AC fallido y añadir las tareas/checks necesarios.
   b. **STOP / HALT forzoso:** detener por completo el uso de herramientas y **pedir al humano que revise** los cambios (plan + tasks + AC cuestionado).
   c. **Solo tras confirmación explícita por escrito** del humano → **re-ejecutar el loop Red→Green→Refactor** (re-implementar, re-testear, re-documentar) y volver al paso 1 hasta que el/los AC queden validados.
   d. La confirmación debe ser explícita para RE-EJECUTAR; una negativa o revisión pendiente mantiene el HALT.
4. **Ciclo completo:** repetir hasta validar todos los AC de todas las features del hito/fase en curso.

## Subphase Decomposition & Atomic Gate Invariant (Mandatory)

Siempre que una fase o subfase sea amplia en alcance, el asistente DEBE descomponerla en sub-subfases claras y secuenciales (p.ej. 3.6.1 a 3.6.5). Bajo ninguna circunstancia puede agrupar varias sub-subfases. Cada sub-subfase individual debe documentarse, testearse y detenerse en HALT para el sign-off del usuario antes de continuar.

## Guardrails

# Universal Soft-Deletion Guardrail (Invariantes R-01 / R-02)
Borrado lógico universal (cero `DELETE FROM`, `UPDATE ... activo = 0, eliminado_en = datetime('now','localtime')`) y preservación de assets (`unlink()` prohibido en bajas). **Texto normativo canónico:** `AGENTS.md §5` → **(R-01)** y **(R-02)**.

# Collaborative Platform & Multi-Artisan Autonomy Guardrail (Invariante R-03)
La aplicación es un Micro-ERP colaborativo, abierto a creadores independientes. Prohibido prometer plazos de taller centralizados ("nuestro taller teje en 5 a 7 días") o calidad de manufactura central. Las garantías se centran en transparencia de plataforma: fichas técnicas rigurosas, comunicación directa con la artesana (WhatsApp), costeo justo y perfiles verificados. **Texto normativo canónico:** `AGENTS.md §5` → **(R-03)**.

# Documentation Architecture Guardrail (Zero Monoliths)
Under no circumstances should documentation be created or maintained as monolithic single files exceeding manageable scope. All documentation must strictly adhere to modular domain separation:
- `docs/api/` for API endpoint contracts and HTTP standards.
- `docs/architecture/` for system design, layer contracts, and numbered ADRs (`docs/architecture/decisiones/`).
- `docs/database/` for relational ERD, DDL schema, and CLI testing.
- `docs/design-system/` for visual tokens, brand identity, vector assets, and UI/UX audits.
- `docs/testing/` for 3-tier testing reports and QA audits.
Every domain folder must maintain its own `README.md` navigation index linked from the master `docs/README.md`.

# Design System Guardrail
Under no circumstances should code or UI revert to default Bootstrap colors, generic unstyled tables, or unverified color contrast ratios. Adherence to `.agents/rules/ui-ux-design-system.md` is strictly mandatory across all phases.