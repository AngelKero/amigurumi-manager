# AGENTS.md

> Standardized guidance for AI coding agents operating on the **Crochet Manager** repository (Collaborative Micro-ERP & Multi-Artisan Textile Catalog).  
> Built in accordance with the open [AGENTS.md](https://agents.md/) standard under the Agentic AI Foundation / Linux Foundation.

---

## 1. Project Overview & Architectural Boundaries

**Crochet Manager** is an open, collaborative Micro-ERP and showcase catalog for autonomous independent crochet and textile artisans. It manages creations, physical stock, fair-trade profit margins, client commission orders, and multi-artisan accounts.

### Physical Directory Separation (Zero Monoliths / Zero PHP in `src/`)
- `app/`: **100% of Backend PHP code** (Clean Architecture, PSR-4 native autoloader, no Composer). Protected by `.htaccess` (HTTP 403).
- `api/`: **Thin REST Controllers** (maximum 60 lines per controller) grouped in thematic subdirectories (`api/auth/`, `api/creaciones/`, `api/pedidos/`, `api/usuarios/`).
- `src/`: **100% Frontend Assets Only**. Absolutely NO PHP files in `src/`.
  - `src/css/`: ITCSS layered architecture (`01-settings` through `04-components`).
  - `src/js/`: Vanilla JavaScript ES Modules (`src/js/main.js` + `src/js/modules/*.js`).
- `views/`: Modular PHP presentation layer (`views/layouts/main.php`, `views/components/`, `views/pages/`).
- `database/`: `database/seed.sql` (DDL & seeds) and `database/database.sqlite` (SQLite 3 physical database). Protected by `.htaccess` (HTTP 403).
- `tests/`: Native CLI test suites (`TestHelper.php`, `test-subfase-3.X.php`).
- `logs/`: Ephemeral CLI and HTTP test logs (git-ignored, HTTP 403).
- `docs/`: Domain-driven modular documentation (`docs/api/`, `docs/architecture/`, `docs/database/`, `docs/design-system/`, `docs/testing/`).
- `memory-bank/`: Canonical source of truth for AI agents (`projectbrief.md`, `productContext.md`, `techContext.md`, `activeContext.md`, `progress.md`).

---

## 2. Dev Environment & Setup Commands

### Prerequisites
- PHP 8.1+ with extensions: `pdo`, `pdo_sqlite`, `fileinfo`, `mbstring`.
- SQLite 3.
- No Node.js or Composer package dependencies are required for backend operation.

### Dev Commands
- **Start Local Server:**
  ```bash
  php -S localhost:8000
  ```
- **Initialize / Reset Database (CLI Only):**
  ```bash
  php setup.php
  ```
  *(Note: `setup.php` is strictly restricted to CLI execution; browser/HTTP access is denied).*
- **Syntax & Lint Checks:**
  ```bash
  # Check PHP syntax across all backend and views
  find app api views *.php -name "*.php" -exec php -l {} +
  
  # Check JS syntax across all modules
  find src/js -name "*.js" -exec node --check {} +
  ```

---

## 3. Testing Instructions & Quality Gates

The repository enforces a strict **3-Tier Testing Architecture** (ADR-011):
1. **Tier 1 (Execution):** CLI automated test suites located in `tests/test-subfase-3.X.php`.
2. **Tier 2 (Raw Logs):** Output redirection to `logs/subfase-3.X-cli.log` and HTTP curl logs to `logs/subfase-3.X-http.log`.
3. **Tier 3 (Executive Markdown Reports):** Structured QA documentation in `docs/testing/subfase-3.X-[name].md`.

### Running Tests
Execute the specific subphase test suite from the project root:
```bash
# Example for Subphase 3.1:
php tests/test-subfase-3.1.php > logs/subfase-3.1-cli.log 2>&1

# Run accumulated regression suite (all subphases):
php tests/test-subfase-3.6.5.php > logs/subfase-3.6.5-cli.log 2>&1
```

### Invariant: Subphase Testing & Halting Gate
When developing in phases or subphases:
1. Implement the subphase code.
2. Run the automated CLI test suite and verify 100% assertions pass.
3. Generate the executive markdown report in `docs/testing/`.
4. Include a dedicated **"Fallos Detectados & Correcciones Quirúrgicas"** section if any test failed or was adapted during Red-Green-Refactor.
5. Update `memory-bank/activeContext.md` and `memory-bank/progress.md`.
6. **HALT COMPLETELY:** Stop calling tools and await the user's explicit approval before proceeding to the next subphase. Never bundle multiple subphases together.

---

## 4. Code Style & Architecture Conventions

### Clean Architecture & SOLID in `app/`
- **Dependency Rule:** Dependencies must always point inward. Services and domain logic must never depend on HTTP delivery (`Request`/`Response`).
- **Isolation of SQL:** 100% of SQL statements reside strictly in `app/Repositories/`. Zero SQL in services, controllers, or views.
- **Thin REST Controllers (`api/`):** Controllers must not exceed 60 lines. They only extract input via `App\Core\Request`, delegate to an `App\Services\*` service, and emit output via `App\Core\Response`.
- **Stateless Bearer Tokens (HMAC-SHA256):** Handled via `App\Core\TokenManager` with 24-hour TTL and constant-time `hash_equals()` validation.
- **Zero HTML Error Leaks:** `App\Core\ErrorHandler` captures all errors and uncaught exceptions, purges output buffers with `ob_end_clean()`, and returns standard JSON 500 (`{"exito": false, "error": {"codigo": 500, "mensaje": "..."}}`).
- **Standardized JSON Envelope:**
  - Success: `{"exito": true, "mensaje": "...", "datos": { ... }}`
  - Paginated: `{"exito": true, "mensaje": "...", "datos": [ ... ], "paginacion": { ... }}`
  - Error: `{"exito": false, "error": {"codigo": 4XX, "mensaje": "...", "detalles": [ ... ]}}`
- **CORS Preflight:** Every API endpoint must invoke `Response::handleCors()` at the top to resolve HTTP `OPTIONS` preflights with `204 No Content`.

### Frontend Guidelines
- **Design System ("Algodón Nórdico"):**
  - Primary Brand: `--craft-primary: #8E5B74`
  - Spruce Guarantee: `--craft-secondary: #52857C` (Text contrast: `--craft-secondary-text: #235048` > 6.2:1)
  - Porcelain Background: `--craft-bg: #F8F9FB`
  - Typography: `Fraunces` (headlines/brand), `Outfit` (subheads/KPIs), `Plus Jakarta Sans` (body/forms).
  - Craft detailing: Dashed running seams (`.card-stitched`), textile care tags (`.badge-textile-tag`), quilted mat frames (`.product-photo-stitched-frame`).
  - Strict prohibition of default Bootstrap electric blue (`#0d6efd`).
- **Vanilla JS Modules:** Pure ES Modules in `src/js/modules/`. No build step or bundler needed.

---

## 5. Critical Invariants & Security Guardrails (Never Violate)

1. **Universal Soft-Deletion Guardrail (Zero Physical Deletions):**
   Direct SQL `DELETE FROM` statements are **strictly forbidden** across `usuarios`, `creaciones`, and `pedidos`. All removals must execute logical updates:
   ```sql
   UPDATE <table> SET activo = 0, eliminado_en = datetime('now', 'localtime') WHERE id = :id AND activo = 1;
   ```
   Read queries must filter `activo = 1` by default.

2. **Asset Preservation on Soft-Delete (ADR-008):**
   Uploaded photos in `uploads/` must **NEVER be unlinked (`unlink()`)** upon creation soft-deletion (`activo = 0`), guaranteeing referential audit integrity for historical orders and client receipts. `unlink()` is only permitted when replacing an image during active update.

3. **Multi-Artisan Autonomy & Platform Guarantees:**
   This application is an open, collaborative platform for independent creators. **Never write code or copy promising centralized workshop lead times (e.g. "5 a 7 días hábiles")**. Guarantees must focus strictly on platform transparency: rigorous specification sheets, direct WhatsApp communication, and verified artisan profiles.

4. **Multi-Artisan IDOR Prevention (ADR-007):**
   Artisans can only mutate (`update`, `delete`, `adjustStock`, `toggleCommission`) their own creations (`artesano_id === user.id`) and orders. Modifying another creator's resources must abort immediately with `HTTP 403 Forbidden`. Only `admin` has global oversight.

5. **Root Administrator Lockout Safeguard (ID #1 / ADR-010):**
   The root administrator (`id: 1`, `@admin`) can never be degraded in role or soft-deleted under any circumstance (`HTTP 403 Forbidden`).

6. **Monetary Value Standard (ADR-005):**
   All financial values (`precio`, `costo_materiales`, `precio_final`) must be stored as **integer cents** in SQLite. Floating-point types (`REAL`) for money are strictly prohibited. Use `App\Utils\CurrencyHelper` (PHP) and `src/js/modules/currency.js` (JS) for symmetric conversion.

7. **Atomic Stock Transactions & Idempotent Cancellation (ADR-009):**
   Order creations reserve inventory within `BEGIN IMMEDIATE TRANSACTION`. Order cancellations validate that the order is not already cancelled, restore reserved units to `creaciones.cantidad_stock`, and update timestamps idempotently.

8. **SQLite Concurrency & Foreign Keys:**
   Every PDO connection must execute:
   ```sql
   PRAGMA foreign_keys = ON;
   PRAGMA busy_timeout = 5000;
   ```

9. **Secure File Uploads:**
   Uploaded files must be verified using real binary MIME detection (`finfo_file` / `mime_content_type`) restricted strictly to `image/jpeg`, `image/png`, and `image/webp` with a $\le 5\text{MB}$ ceiling. Cryptographic file names (`creacion_[16-hex]_[timestamp].[ext]`) and `basename()` confinement prevent path traversal. If no photo is uploaded, automatically assign a thematic SVG fallback from `assets/svg/piezas/`.

---

## 6. Memory Bank Governance

The single, absolute source of truth for ongoing context is the `/memory-bank/` directory.
- **Before starting any task:** Read the 5 markdown files in `/memory-bank/` (`activeContext.md`, `productContext.md`, `progress.md`, `projectbrief.md`, `techContext.md`).
- **Session start:** Verify and update `memory-bank/activeContext.md` before writing code.
- **Milestone completion:** Keep `memory-bank/activeContext.md` and `memory-bank/progress.md` strictly synchronized.

---

## 7. Git & Commit Guidelines

- **Commit Message Format:** Structured, conventional commits in Spanish or English:
  - `feat(modulo): descripción clara del cambio`
  - `fix(modulo): corrección quirúrgica`
  - `docs(dominio): actualización de documentación modular`
  - `test(subfase): nuevas aserciones o suites`
- **Untracked / Ignored Artifacts:** Never commit `.sqlite`, `.sqlite3`, `logs/*.log`, temporary scratch files, or OS files (`.DS_Store`).
