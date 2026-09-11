---
trigger: always_on
---

# Iterative Development Workflow

Development will strictly follow these sequential phases. You must halt completely at the end of each phase and await my explicit instruction before proceeding. Do not assume or generate code for the next phase.

- **Phase 1 - Database Implementation, Seeding & Testing (Completed & Verified):**
  Created `database/seed.sql` (full DDL schema, foreign keys, CHECK constraints, performance indexes, and seed mock data), CLI-only `setup.php` to initialize `database/database.sqlite` via PDO with foreign keys enabled, and documented testing procedures in `docs/database-testing.md`.

- **Phase 2 - Layout & UI: PHP Component System & Design System (Completed & Verified):**
  Built modular PHP views (`views/layouts/main.php`, `views/components/`, `views/pages/`), entrypoints (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`), ITCSS layered styles in `src/css/`, and ES modules in `src/js/`. Fully compliant with the "Algodón Nórdico" Design System rules (`.agents/rules/ui-ux-design-system.md`).

- **Phase 3 - Backend & Connection: Clean Architecture (Awaiting Approval to Begin):**
  Implement a modular backend in `src/` following Clean Architecture and SOLID principles:
  - `src/Core/`: Base Controller, Request/Response abstractions, Session manager.
  - `src/Database/`: Database singleton (`conexion.php` wrapping PDO with `PRAGMA foreign_keys = ON;` and `ERRMODE_EXCEPTION`).
  - `src/Repositories/`: Data Access Objects (`AmigurumiRepository`, `PedidoRepository`, `UsuarioRepository`) using parameterized prepared statements.
  - `src/Services/`: Business logic, atomic stock transactions, pricing rules, image lifecycle.
  - `src/Middleware/`: `AuthGuard` protecting administrative and mutating routes with RBAC role verification (`admin`, `artesano`).
  - `src/Utils/`: Shared helpers (`CurrencyHelper.php`).
  - `api/`: Thin controllers/endpoints exposing RESTful JSON responses.

- **Phase 4 - CRUD Operations & Fullstack Wiring (Pending):**
  Wire the frontend ES modules (`src/js/modules/`) to the backend `api/` endpoints with asynchronous `fetch()`, handling server validation errors, optimistic feedback, and reactive state updates.

- **Phase 5 - Documentation & Handover (Continuous & Verified):**
  Maintain comprehensive documentation in `docs/` and root `README.md` with single-command local server startup instructions (`php -S localhost:8000`).

# Design System Guardrail
Under no circumstances should code or UI revert to default Bootstrap colors, generic unstyled tables, or unverified color contrast ratios. Adherence to `.agents/rules/ui-ux-design-system.md` is strictly mandatory across all phases.
