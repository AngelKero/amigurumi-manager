---
trigger: always_on
---

# Iterative Development Workflow

Development will strictly follow these sequential phases. You must halt completely at the end of each phase and await my explicit instruction before proceeding. Do not assume or generate code for the next phase.

- **Phase 1 - Database Implementation, Seeding & Testing (Completed & Verified):**
  Created `database/seed.sql` (full DDL schema, foreign keys, CHECK constraints, performance indexes, and seed mock data), CLI-only `setup.php` to initialize `database/database.sqlite` via PDO with foreign keys enabled, and documented testing procedures in `docs/database-testing.md`.

- **Phase 2 - Layout & UI: PHP Component System & Design System (Completed & Verified):**
  Built modular PHP views (`views/layouts/main.php`, `views/components/`, `views/pages/`), entrypoints (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`), ITCSS layered styles in `src/css/`, and ES modules in `src/js/`. Fully compliant with the "Algodón Nórdico" Design System rules (`.agents/rules/ui-ux-design-system.md`).

- **Phase 3 - Backend & Connection: Clean Architecture in `app/` (Awaiting Approval to Begin):**
  Implement a modular backend exclusively in `app/` (`src/` remains 100% frontend only) following Clean Architecture, SOLID principles, and 6 sequential subphases with mandatory CLI/HTTP testing and documentation gates:
  - `app/autoload.php`: PSR-4 autoloader without Composer and global `ErrorHandler::register()`.
  - `app/config.php` & `app/Core/Config.php`: Centralized configuration protected by `.htaccess`.
  - `app/Core/`: `Database.php` (PDO SQLite Singleton with `PRAGMA foreign_keys = ON;`), `ErrorHandler.php` (zero HTML error leaks, JSON 500), `Request.php`, `Response.php` (JSON formatting & CORS `OPTIONS` 204), `TokenManager.php` (HMAC-SHA256 Bearer tokens, 24h TTL), `SessionManager.php`.
  - `app/Repositories/`: `CreacionRepository.php`, `PedidoRepository.php`, `UsuarioRepository.php` using 100% parameterized prepared statements and pagination.
  - `app/Services/`: `AuthService.php`, `CreacionService.php` (dual currency formatting, SVG fallback, `unlink()` lifecycle), `PedidoService.php` (atomic stock transactions, restitution on cancel), `UsuarioService.php` (RBAC, root admin ID #1 lockout).
  - `app/Middleware/`: `AuthGuard.php` (Bearer token validation) and `RoleGuard.php` (`admin`, `artesano`, `asistente`).
  - `app/Utils/`: `CurrencyHelper.php`, `PaginationHelper.php` (default 12 for creaciones, 20 for pedidos), `SvgHelper.php`.
  - `api/`: Thin controllers organized by thematic subfolders (`api/auth/`, `api/creaciones/`, `api/pedidos/`, `api/usuarios/`).
  - **Sequential Subphases:** 3.1 Base/Core Foundations, 3.2 Stateless Auth & Bearer Middleware, 3.3 User Management & RBAC, 3.4 Catalog & Creations Lifecycle, 3.5 Orders & Atomic Transactions, 3.6 Comprehensive Security & Regression Audit.
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
  Maintain comprehensive documentation in `docs/` and root `README.md` with single-command local server startup instructions (`php -S localhost:8000`).

# Design System Guardrail
Under no circumstances should code or UI revert to default Bootstrap colors, generic unstyled tables, or unverified color contrast ratios. Adherence to `.agents/rules/ui-ux-design-system.md` is strictly mandatory across all phases.
