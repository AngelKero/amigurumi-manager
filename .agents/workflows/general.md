---
description: Iterative step-by-step workflow for developing a PHP, SQLite, and Bootstrap CRUD web application with Clean Architecture and strict phase gates.
---

# Iterative Development Workflow

Development strictly follows these sequential phases. You must halt completely at the end of each phase and await explicit instruction before proceeding. Do not assume or generate code for the next phase.

- **Phase 1 - Database Implementation, Seeding & Testing (Completed & Verified):**
  Create `database/seed.sql` (full DDL schema, relational foreign keys, CHECK constraints, and seed data), write CLI-only `setup.php` to initialize `database/database.sqlite` via PDO with foreign keys enabled, and document in `docs/database/schema.md` and `docs/database/testing.md`.

- **Phase 2 - Layout & UI: PHP Component System & Design System (Completed & Verified):**
  Build modular PHP views (`views/layouts/main.php`, `views/components/`, `views/pages/`), entrypoints (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`), ITCSS layered styles in `src/css/`, and ES modules in `src/js/`. All views must strictly follow the "Algodón Nórdico" Design System rules (`.agents/rules/ui-ux-design-system.md` y `docs/design-system/`).

- **Phase 3 - Backend & Connection: Clean Architecture in `app/` (Completed & Verified - 1,307/1,307 Aserciones OK):**
  Implement modular backend exclusively in `app/` (`src/` remains 100% frontend only) following Clean Architecture, SOLID principles, and 6 sequential subphases with mandatory CLI/HTTP testing and documentation gates:
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
    - 3.6 Comprehensive Security & Regression Audit (775/775 aserciones OK en 5 sub-subfases: 3.6.1 a 3.6.5).
    - Total acumulado Fase 3: 1,307/1,307 aserciones aprobadas en verde.
  - **Mandatory 3-Tier Testing & Sign-off Gate for Any AI Assistant:**
    At the conclusion of EACH subphase, the agent MUST:
    1. Run native CLI test suite: `php tests/test-subfase-3.X.php > logs/subfase-3.X-cli.log 2>&1`.
    2. Perform HTTP curl checks logging responses to `logs/subfase-3.X-http.log`.
    3. Generate executive Markdown report in `docs/testing/subfase-3.X-[nombre].md` using the template in `docs/testing/README.md`.
    4. Include a dedicated "Fallos Detectados & Correcciones Quirúrgicas" section in the report if any test failed or required code adaptation during the Red-Green-Refactor cycle.
    5. Update `memory-bank/activeContext.md` and `memory-bank/progress.md`.
    6. **HALT COMPLETELY:** Stop calling tools and await the user's explicit written approval before writing any code for the next subphase. Never bundle multiple subphases together.

- **Phase 4 - CRUD Operations & Fullstack Wiring (Pending):**
  Wire the frontend ES modules (`src/js/modules/`) to the backend `api/` endpoints with asynchronous `fetch()`, handling server validation errors, optimistic feedback, and reactive state updates.
  - Decompose into sequential, verifiable subphases (4.1 Auth & Client Session State, 4.2 Public Catalog & Dynamic Filters, 4.3 Creation Management & Image Uploads, 4.4 Orders & Public Checkout Flow, 4.5 User Directory & RBAC Administration).
  - Each subphase requires its own testing gate and explicit user approval before proceeding.

- **Phase 5 - Documentation & Handover (Continuous & Verified):**
  Maintain comprehensive, modular domain-driven documentation in `docs/` (`docs/architecture/`, `docs/api/`, `docs/database/`, `docs/design-system/`, `docs/testing/`) and root `README.md` with single-command local server startup instructions (`php -S localhost:8000`).

# Subphase Decomposition & Atomic Gate Invariant (Mandatory)
Whenever a development phase or subphase is broad in scope, the assistant MUST decompose it into clear, sequential sub-subphases. Under no circumstances may an assistant bundle multiple sub-subphases together. Each individual sub-subphase must be documented, tested, and halted for user sign-off before proceeding to the next.

# Universal Soft-Deletion Guardrail (Zero Physical Deletion)
Direct SQL `DELETE FROM` statements are strictly forbidden across all application tables (`usuarios`, `creaciones`, `pedidos`). All removals execute logical updates (`activo = 0`, `eliminado_en = datetime('now', 'localtime') WHERE id = :id AND activo = 1`). In addition, uploaded physical assets (`uploads/`) must NEVER be unlinked upon soft-deletion (`activo = 0`), preserving historical orders and receipts.

# Collaborative Platform & Multi-Artisan Autonomy Guardrail
The application is an open, collaborative Micro-ERP for autonomous independent creators. Under no circumstances should copy or code make promises of centralized factory turnaround times (e.g. "nuestro taller teje en 5 a 7 días") or centralized manufacturing quality. Guarantees must strictly focus on platform assurances: transparent technical specification sheets, direct customer-to-artisan communication (WhatsApp), fair-trade costing tools, and verified artisan profiles.

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
