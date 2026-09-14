---
trigger: always_on
---

# Iterative Development Workflow

> **Fuente canónica (Gobernanza · Acción 4 · H-023):** este archivo es el único lugar que contiene el flujo iterativo, el gate 3-tier y los guardrails operativos. `.agents/workflows/general.md` es solo un **workflow-glue** de enrutamiento que referencia este archivo; cualquier divergencia de contenido es un defecto (P5).
>
> **Índice de reglas (fan-out):**
> | Tipo de tarea | Fuente |
> | :--- | :--- |
> | Desarrollo/refactor/backend | Este archivo (§Fases + §Gate + §Guardrails) |
> | Feature nueva o cambio no trivial | `.agents/workflows/sdd-feature.md` |
> | UI/UX / design system | `.agents/rules/ui-ux-design-system.md` |
> | Docs / fuente de verdad | `.agents/rules/docs-source-of-truth.md` |
> | Seguridad de token/XSS/CSP/innerHTML | Este archivo `§R-01…R-10` + `.agents/rules/innerhtml-dom-safety.md` |

Development will strictly follow these sequential phases. You must halt completely at the end of each phase and await my explicit instruction before proceeding. Do not assume or generate code for the next phase.

- **Phase 1 - Database Implementation, Seeding & Testing (Completed & Verified):**
  Created `database/seed.sql` (full DDL schema, foreign keys, CHECK constraints, performance indexes, and seed mock data), CLI-only `setup.php` to initialize `database/database.sqlite` via PDO with foreign keys enabled, and documented in `docs/database/schema.md` and `docs/database/testing.md`.

- **Phase 2 - Layout & UI: PHP Component System & Design System (Completed & Verified):**
  Built modular PHP views (`views/layouts/main.php`, `views/components/`, `views/pages/`), entrypoints (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`), ITCSS layered styles in `src/css/`, and ES modules in `src/js/`. Fully compliant with the "Algodón Nórdico" Design System rules (`.agents/rules/ui-ux-design-system.md` y `docs/design-system/`).

- **Phase 3 - Backend & Connection: Clean Architecture in `app/` (Completed & Verified - 1,287/1,287 Aserciones OK sobre semilla limpia; total regenerable con `php tests/cuenta-aserciones.php`, H-006):**
  Implemented a modular backend exclusively in `app/` (`src/` remains 100% frontend only) following Clean Architecture, SOLID principles, and 6 sequential subphases with mandatory CLI/HTTP testing and documentation gates:
  - `app/autoload.php`: PSR-4 autoloader without Composer and global `ErrorHandler::register()`.
  - `app/config.php` & `app/Core/Config.php`: Centralized configuration protected by `.htaccess`.
  - `app/Core/`: `Database.php` (PDO SQLite Singleton with `PRAGMA foreign_keys = ON;` and `PRAGMA busy_timeout = 5000;`), `ErrorHandler.php` (zero HTML error leaks, JSON 500), `Request.php`, `Response.php` (JSON formatting & CORS `OPTIONS` 204), `TokenManager.php` (HMAC-SHA256 Bearer tokens, 24h TTL).
  - `app/Repositories/`: `UsuarioRepository.php`, `CreacionRepository.php`, `PedidoRepository.php` using 100% parameterized prepared statements, pagination and soft delete.
  - `app/Services/`: `AuthService.php`, `UsuarioService.php`, `CreacionService.php` (dual currency formatting, SVG fallback, `unlink()` lifecycle), `PedidoService.php` (atomic stock transactions, restitution on cancel, WhatsApp).
  - `app/Middleware/`: `AuthGuard.php` (Bearer token validation) and `RoleGuard.php` (`admin`, `artesano`, `asistente`).
  - `app/Utils/`: `CurrencyHelper.php`, `PaginationHelper.php` (default 12 for creaciones, 20 for pedidos), `SvgHelper.php`.
  - `api/`: Thin controllers organized by thematic subfolders (`api/auth/`, `api/creaciones/`, `api/pedidos/`, `api/usuarios/`).
  - **Sequential Subphases (All Completed & Verified):**
    - 3.1 Base/Core Foundations (93/93 aserciones OK).
    - 3.2 Stateless Auth & Bearer Middleware (69/69 aserciones OK).
    - 3.3 User Management & RBAC (105/105 aserciones OK).
    - 3.4 Catalog & Creations Lifecycle (126/126 aserciones OK).
    - 3.5 Orders & Atomic Transactions (139/139 aserciones OK).
    - 3.6 Comprehensive Security & Regression Audit (755/755 aserciones OK en 5 sub-subfases: 3.6.1 a 3.6.5).
    - Total acumulado Fase 3: 1,287/1,287 aserciones aprobadas en verde (regenerable: `php tests/cuenta-aserciones.php`, H-006).
  - **Mandatory 3-Tier Testing & Sign-off Gate for Any AI Assistant:**
    At the conclusion of EACH subphase, the agent MUST:
    1. Run native CLI test suite: `php tests/test-subfase-3.X.php > logs/subfase-3.X-cli.log 2>&1`.
    2. Perform HTTP curl checks logging responses to `logs/subfase-3.X-http.log`.
    3. If CLI and HTTP diverge, apply the **CLI/HTTP divergence triage protocol** (`docs/testing/protocolo-divergencia-cli-http.md`): environment vs. code, and record the decision before proceeding.
    4. Generate executive Markdown report in `docs/testing/subfase-3.X-[nombre].md` using the template in `docs/testing/README.md`.
    5. Include a dedicated "Fallos Detectados & Correcciones Quirúrgicas" section in the report if any test failed or required code adaptation during the Red-Green-Refactor cycle.
    6. Update feature `tasks.md`, check acceptance criteria in `spec.md`, and update `spec/constitution/roadmap.md`.
    7. **HALT COMPLETELY:** Stop calling tools and await the user's explicit written approval before writing any code for the next subphase. Never bundle multiple subphases together.
   - **Phase 4+ accumulated regression (H-020):** when touching features 004–008, also run `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` before the executive report.

- **Phase 4 - CRUD Operations & Fullstack Wiring (Pending):**
  Wire the frontend ES modules (`src/js/modules/`) to the backend `api/` endpoints with asynchronous `fetch()`, handling server validation errors, optimistic feedback, and reactive state updates.
  - Must be decomposed into sequential, verifiable subphases (e.g., 4.1 Auth & Client Session State, 4.2 Public Catalog & Dynamic Filters, 4.3 Creation Management & Image Uploads, 4.4 Orders & Public Checkout Flow, 4.5 User Directory & RBAC Administration).
  - Each subphase requires its own testing gate and explicit user approval before proceeding.

- **Phase 5 - Documentation & Handover (Continuous & Verified):**
  Maintain comprehensive, modular domain-driven documentation in `docs/` (`docs/architecture/`, `docs/api/`, `docs/database/`, `docs/design-system/`, `docs/testing/`) and root `README.md` with single-command local server startup instructions (`php -S localhost:8000`).

# Subphase Decomposition & Atomic Gate Invariant (Mandatory)
Whenever a development phase or subphase is broad in scope, the assistant MUST decompose it into clear, sequential sub-subphases (e.g., as executed in 3.6.1 through 3.6.5). Under no circumstances may an assistant bundle multiple sub-subphases together. Each individual sub-subphase must be documented, tested, and halted for user sign-off before proceeding to the next.

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
