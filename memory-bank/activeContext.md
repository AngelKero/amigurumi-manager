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

## Current State: Phase 2 Atmosphere Refinement - Floating Background Clouds & Yarn SVGs
- **User Request:** Add floating decorative SVG icons of clouds and yarn/threads to the catalog page (`index.html`) background to amplify the soft, dreamy, floating ambiance of "Algodón Nórdico".
- **Execution Plan:**
  1. Add atmospheric background decorative container (`.floating-bg-decorations`) in `index.html` featuring high-quality, lightweight inline SVG elements of puffy clouds, yarn balls with thread loops, and delicate craft sparkles.
  2. Enhance `css/styles.css` with fixed background positioning, non-obstructive pointer events (`pointer-events: none; z-index: 0;`), staggered float animations (`@keyframes floatDrift1`, `@keyframes floatDrift2`), and delicate powdery opacity (15%–30%) matching Nordic Plum and Spruce tints.
  3. Ensure zero impact on interactivity, performance, and responsive layout.
- **Phase Gate Status:** In Phase 2 Layout & UI refinement. Delivering background atmosphere and verifying live in browser.
