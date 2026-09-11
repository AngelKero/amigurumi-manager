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

## Current State: Phase 2 Craft Detailing Enhancement (Header, Banner & Filters)
- **User Request:** "Aun hay muchas areas de oportunidad para implementar la nueva decoracion, como por ejemplo todos los elementos del header, el banner y todo el tema de filtros"
- **Focus Areas Identified:**
  1. **Header / Navbar Elements:** Brand identity badge with embroidered stitch, textile tag navigation tabs (`Catálogo`, `Panel del Artesano`), harmonious artisan session badge (`@admin (Artesano)`) replacing harsh dark box with a warm linen/honey stitched artisan seal, logout button polish, and running-stitch divider line along the navbar bottom.
  2. **Hero Banner (`.hero-cloud`):** Apply grand quilted inset running seam (`.card-stitched`), style collection ribbon as woven care tag, frame hero photo with stitched quilted borders, transform micro-trust indicators into stitched trust chips, and craft the secondary action button with stitched styling.
  3. **Filter Station / Toolbar:** Wrap search & filters inside a tailored quilted workstation card (`.card-stitched`), add interactive textile category chips (`.badge-textile-tag` with click filter support), style search input and selects with craft stitch focus and custom accents, and provide an embroidered reset button.
- **Phase Gate Status:** In Phase 2 Layout & UI refinement. Creating detailed implementation plan for user review.
