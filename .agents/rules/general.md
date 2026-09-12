---
trigger: always_on
---

# Iterative Development Workflow

Development will strictly follow these sequential phases. You must halt completely at the end of each phase and await my explicit instruction before proceeding. Do not assume or generate code for the next phase.

- **Phase 1 - Database Implementation, Seeding & Testing (Completed & Verified):**
  Created `database/seed.sql` (full DDL schema, foreign keys, CHECK constraints, performance indexes, and seed mock data), CLI-only `setup.php` to initialize `database/database.sqlite` via PDO with foreign keys enabled, and documented in `docs/database/schema.md` and `docs/database/testing.md`.

- **Phase 2 - Layout & UI: PHP Component System & Design System (Completed & Verified):**
  Built modular PHP views (`views/layouts/main.php`, `views/components/`, `views/pages/`), entrypoints (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`), ITCSS layered styles in `src/css/`, and ES modules in `src/js/`. Fully compliant with the "Algodón Nórdico" Design System rules (`.agents/rules/ui-ux-design-system.md` y `docs/design-system/`).

- **Phase 3 - Backend & Connection: Clean Architecture in `app/` (Subfases 3.1, 3.2, 3.3 Completadas y Verificadas; 3.4 Aprobada):**
  Implement a modular backend exclusively in `app/` (`src/` remains 100% frontend only) following Clean Architecture, SOLID principles, and 6 sequential subphases with mandatory CLI/HTTP testing and documentation gates:
  - `app/autoload.php`: PSR-4 autoloader without Composer and global `ErrorHandler::register()`.
  - `app/config.php` & `app/Core/Config.php`: Centralized configuration protected by `.htaccess`.
  - `app/Core/`: `Database.php` (PDO SQLite Singleton with `PRAGMA foreign_keys = ON;` and `PRAGMA busy_timeout = 5000;`), `ErrorHandler.php` (zero HTML error leaks, JSON 500), `Request.php`, `Response.php` (JSON formatting & CORS `OPTIONS` 204), `TokenManager.php` (HMAC-SHA256 Bearer tokens, 24h TTL).
  - `app/Repositories/`: `UsuarioRepository.php`, `CreacionRepository.php`, `PedidoRepository.php` using 100% parameterized prepared statements, pagination and soft delete.
  - `app/Services/`: `AuthService.php`, `UsuarioService.php`, `CreacionService.php` (dual currency formatting, SVG fallback, `unlink()` lifecycle), `PedidoService.php` (atomic stock transactions, restitution on cancel, WhatsApp).
  - `app/Middleware/`: `AuthGuard.php` (Bearer token validation) and `RoleGuard.php` (`admin`, `artesano`, `asistente`).
  - `app/Utils/`: `CurrencyHelper.php`, `PaginationHelper.php` (default 12 for creaciones, 20 for pedidos), `SvgHelper.php`.
  - `api/`: Thin controllers organized by thematic subfolders (`api/auth/`, `api/creaciones/`, `api/pedidos/`, `api/usuarios/`).
  - **Sequential Subphases:** 3.1 Base/Core Foundations (Done: 93/93), 3.2 Stateless Auth & Bearer Middleware (Done: 69/69), 3.3 User Management & RBAC (Done: 105/105), 3.4 Catalog & Creations Lifecycle (Approved), 3.5 Orders & Atomic Transactions, 3.6 Comprehensive Security & Regression Audit.
  - **Mandatory 3-Tier Testing & Sign-off Gate for Any AI Assistant:**
    At the conclusion of EACH subphase, the agent MUST:
    1. Run native CLI test suite: `php tests/test-subfase-3.X.php > logs/subfase-3.X-cli.log 2>&1`.
    2. Perform HTTP curl checks logging responses to `logs/subfase-3.X-http.log`.
    3. Generate executive Markdown report in `docs/testing/subfase-3.X-[nombre].md` using the template in `docs/testing/README.md`.
    4. Update `memory-bank/activeContext.md` and `memory-bank/progress.md`.
    5. **HALT COMPLETELY:** Stop calling tools and await the user's explicit written approval before writing any code for the next subphase. Never bundle multiple subphases together.

- **Phase 4 - CRUD Operations & Fullstack Wiring (Pending):**
  Wire the frontend ES modules (`src/js/modules/`) to the backend `api/` endpoints with asynchronous `fetch()`, handling server validation errors, optimistic feedback, and reactive state updates.

- **Phase 5 - Documentation & Handover (Continuous & Verified):**
  Maintain comprehensive, modular domain-driven documentation in `docs/` (`docs/architecture/`, `docs/api/`, `docs/database/`, `docs/design-system/`, `docs/testing/`) and root `README.md` with single-command local server startup instructions (`php -S localhost:8000`).

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
