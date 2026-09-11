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

## Current State: Phase 2 "Algodón Nórdico" & UI/UX Audit Execution Completed
- **User Decision & Rules Enshrinement:**
  - Implemented **Proposal B ("Algodón Nórdico")** across all views (`css/styles.css`, `index.html`, `detalle.html`, `pedidos.html`, `formulario.html`, `js/app.js`).
  - Created permanent agent rule in `.agents/rules/ui-ux-design-system.md` (`trigger: always_on`) codifying palette tokens, *Outfit* + *Plus Jakarta Sans* typography, cloud animations, pill radiuses, and mandatory heuristic guardrails.
  - Linked design system compliance into `.agents/rules/general.md`.
- **Delivered Enhancements:**
  1. Palette & Typography: Dusty Heather Plum (`#8E5B74`), Nordic Spruce (`#52857C` with `#235048` high-contrast text), Nordic Honey (`#D99C52`), Alabaster Porcelain (`#F8F9FB`), and Google Fonts *Outfit* & *Plus Jakarta Sans*.
  2. Cloud Aesthetics: Micro-animations (`floatSoft`, `floatGentle`, `pulseGlow`), ultra-soft cloud shadows, and pill-shaped badge radiuses (`50px`).
  3. Catalog Hero Banner: Dreamy cloud-soft hero banner in `index.html` with welcoming artisan greeting, verified craft imagery, and quick action buttons.
  4. Audit Remediations:
     - **[CR-1]**: Out-of-stock guard on `detalle.html` (`stock === 0` disables direct checkout, shows "Agotado para Entrega Inmediata", and offers custom commission trigger).
     - **[CR-2]**: Stepper quantity bounds `[-] [ 1 ] [+]` bounded by physical stock & mobile stacked order cards (`#mobileOrdersContainer`) on `pedidos.html`.
     - **[QW-1]**: WCAG AA compliant text contrast for stock badges (`#235048` on `#EBF4F2` = 6.2:1 contrast).
     - **[QW-2]**: Cancellation confirmation modal with stock restitution notification & lead-time notice (5-7 business days).
- **Phase Gate Status:** Phase 2 (Layout & UI) is fully refined and ready for formal user sign-off before commencing **Phase 3 - Backend & Connection** (`conexion.php`, `auth_guard.php`).
