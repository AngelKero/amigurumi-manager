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

## Current State: Phase 2 UI/UX Refinement & "Algodón Nórdico" Implementation
- **User Decision:** Selected **Proposal B ("Algodón Nórdico")** from `.docs/ui-ux-skill-report.md` for full implementation across all views.
- **Scope of Current Action:**
  1. Palette & Typography: Shift design system to Nordic Dusty Heather Plum (`#8E5B74`), Nordic Spruce (`#52857C`), Nordic Honey (`#D99C52`), Alabaster Porcelain (`#F8F9FB`), and Google Font *Outfit* + *Plus Jakarta Sans*.
  2. Cloud Aesthetics: Floating keyframe animations, ultra-soft shadows (`0 12px 32px rgba(30, 37, 45, 0.09)`), and pill-shaped badge radiuses (`20px`).
  3. Catalog Hero Banner: Dreamy cloud-soft hero banner in `index.html` with welcoming artisan greeting and craft imagery.
  4. Decorative Assets: Inline cloud and yarn SVGs, Bootstrap Icons, and high-fidelity craft assets.
  5. Audit Remediations:
     - **[CR-1]**: Out-of-stock guard on `detalle.html` (`stock === 0` disables checkout).
     - **[CR-2]**: Stepper quantity bounds `[-] [ 1 ] [+]` & mobile card view for `pedidos.html`.
     - **[QW-1]**: WCAG AA compliant contrast for stock badges (`#235048` / `#3D5E49`).
     - **[QW-2]**: Cancellation modal with stock restitution warnings & lead-time notice (5-7 business days).
- **Phase Gate Status:** In Phase 2 Layout & UI refinement. Awaiting plan approval before source execution. Upon completion, full summary will be presented for Phase 2 sign-off before advancing to Phase 3 (Backend Connection & Auth Guard).
