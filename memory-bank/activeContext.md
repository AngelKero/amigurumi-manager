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
- **Phase Gate Status:** Phase 1 (Database Implementation, Seeding & Testing) completed and approved. **Phase 2 (Layout & UI) Completed**: Generated official production views (`index.html`, `detalle.html`, `formulario.html`, `pedidos.html`), `css/styles.css`, and `js/app.js` with all user-requested refinements.

## Immediate Focus: Phase 2 Review & Phase 3 Gate Transition
Phase 2 UI implementation completed with official production views:
1. `index.html`: Public catalog and inventory showcase with clickable product card images/titles, sorting dropdown (Precio / Más recientes), dynamic stock badges, disabled button on stock=0 ("Agotado"), and clean public navbar.
2. `detalle.html`: Comprehensive creation specification with subtle text return link, artisan guarantee/shipping badge block, and client checkout modal with quantity stepper `[-] [ 1 ] [+]` bounded by stock.
3. `formulario.html`: Artisan creation and edit form featuring a hidden image preview element on file selection, and a live margin calculator providing dual visual feedback (margin % and effective hourly rate).
4. `pedidos.html`: Orders dashboard with KPI metric cards, status filters, table transition dropdowns, order inspection modal, and order cancellation modal explicitly detailing automatic stock restoration.
5. `css/styles.css` & `js/app.js`: Warm artisan design system (terracotta, sage green, linen cream) and client-side UI interactivity.

## Next Steps
1. Present Phase 2 production views to the user.
2. Await explicit user sign-off on Phase 2 before proceeding to **Phase 3 - Backend & Connection** (`conexion.php`, `auth_guard.php`).
