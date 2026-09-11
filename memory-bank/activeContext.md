# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Multi-Layered Security Hardening & Database-First Phase 1 Execution
- **Multi-Layered Security Architecture Implemented:**
  1. **Data Loss Prevention (CLI-Only Setup):** Hardened `setup.php` to immediately abort with `Access Denied` if requested via HTTP/browser (`php_sapi_name() !== 'cli'`). Removed all HTML rendering to enforce command-line execution only.
  2. **Data Leak Mitigation (Relocated Database):** Moved physical database file to `database/database.sqlite`, out of the web server document root.
  3. **Apache Direct Access Blocking (.htaccess):** Generated root `.htaccess` and `database/.htaccess` strictly denying direct HTTP requests to `.sqlite`, `.sql`, and `.md` files, and blocking web access to `database/` and `memory-bank/`.
  4. **Orphaned File Deletion Policy:** Fully specified in `docs/api-design.md` and `docs/api-design.es.md` (`POST /api/eliminar.php`), requiring `unlink()` of local image files before database record deletion.
- **Files Synchronized (Bilingual Suite):**
  - `docs/api-design.md` & `docs/api-design.es.md`
  - `docs/database-schema.md` & `docs/database-schema.es.md`
  - `docs/database-testing.md` & `docs/database-testing.es.md`
  - `memory-bank/projectbrief.md`
  - `memory-bank/techContext.md`
  - `memory-bank/activeContext.md`
  - `memory-bank/progress.md`
- **Phase Gate Status:** Phase 1 (Database Implementation, Seeding & Testing) completed and approved. **Phase 2 (Layout & UI) Completed & Audited**: Generated official production views (`index.html`, `detalle.html`, `formulario.html`, `pedidos.html`), `css/styles.css`, and `js/app.js`. Installed AI Agent Skill (`Design Auditor`) and generated comprehensive UI/UX audit report `.docs/ui-ux-skill-report.md`.

## Immediate Focus: Live Browser UI/UX Evaluation via Chrome (`http://127.0.0.1:5500/index.html`)
1. **Live Browser Audit Completed in Chrome:** Active tab successfully positioned at `http://127.0.0.1:5500/index.html`. Full end-to-end verification performed across catalog, stepper modals, artisan margin math simulator, orders table, and error routes.
2. **AI Agent Skill Output:** Evaluated via `Design Auditor` (`Ashutos1997/claude-design-auditor-skill` v1.2.13). Full reports available in `.docs/ui-ux-skill-report.md` and `.docs/ui-ux-skill-report.es.md`.
3. **Recordings & Visual Evidence:**
   - Chrome live navigation recording: `chrome_return_index_1789083404263.webp`
   - Complete multi-page audit recording: `ui_ux_browser_audit_1789082444550.webp`
   - Key screenshots captured: Homepage catalog, Navbar modal, Stepper bounds, Detail specs, Artisan form simulator, and Orders dashboard.

## Next Steps
1. User selects preferred visual design system from the 3 proposals (Tierra & Telar, Algodón Nórdico, Café & Crochet).
2. Await user confirmation before altering any CSS tokens.
3. Upon approval, proceed to **Phase 3 - Backend & Connection** (`conexion.php`, `auth_guard.php`).

