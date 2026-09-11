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

## Current State: Phase 2 Craft Textures & Stitched Borders Completed
- **User Request:** Research and implement CSS border styles that evoke stitched, sewn, or knitted textures (pespuntes, hilvanes, etiquetas textiles, bordes acolchados), giving elements a tactile, handmade aesthetic aligned with "Algodón Nórdico".
- **Execution Completed:**
  1. **Research & Design:** Researched authentic needlework and craft web design techniques (inset running seams, embroidered outline offsets, woven cloth tags, running-stitch gradients).
  2. **CSS Token & Utility Implementation (`css/styles.css`):** Integrated `.card-stitched`, `.btn-craft-stitched`, `.badge-textile-tag`, `.divider-stitched`, and `.guarantee-stitched`.
  3. **Universal View Deployment:** Fully implemented across `index.html`, `detalle.html`, `pedidos.html`, and `formulario.html`.
  4. **Atmospheric Backgrounds:** Added floating cloud and yarn SVG decorations (`.cloud-yarn-bg-decorations`) to give an ethereal, soft handmade atmosphere.
  5. **Design System Codification:** Updated `.agents/rules/ui-ux-design-system.md` with Section 5 enshrining these craft border techniques.
  6. **Visual Browser Verification:** Verified live via browser subagent with screenshots and screen recordings across all views.
- **Phase Gate Status:** Phase 2 Layout & UI refinement complete. Awaiting user review and sign-off before proceeding to Phase 3.
