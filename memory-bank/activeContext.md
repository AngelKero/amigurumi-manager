# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Phase 0 Officially Approved & Phase 1 (Layout & UI) In Progress
- **Phase 0 Approval:**
  - The user has officially reviewed and approved all Phase 0 deliverables.
  - Final architectural requirement integrated: `POST /api/eliminar.php` mandates deleting associated physical image files in `/uploads/` using PHP's `unlink()` before or upon database record removal to prevent orphaned files.
  - Documentation updated across `docs/api-design.md`, `docs/api-design.es.md`, and `memory-bank/`.
- **Phase 1 Active (Layout & UI):**
  - Project directory structure setup: `css/`, `js/`, `uploads/`.
  - Building semantic HTML5 and Bootstrap 5.3 CDN views:
    1. `index.html`: Public catalog and inventory showcase, stock badges, statistics banner, category filtering, shared Header/Navbar with Login Modal.
    2. `formulario.html`: Add / Edit amigurumi form with `enctype="multipart/form-data"`, file input with live preview, real-time margin/hourly rate preview, and shared Header/Navbar.
    3. `detalle.html`: Comprehensive creation specification, artisan economics table, Public Client Checkout Modal, and shared Header/Navbar.
    4. `pedidos.html`: Orders & commissions management table with status badges and status update modal.
    5. `css/styles.css`: Warm craft design system tokens, card hover states, badge accents, and modal styling.
  - **Constraint:** Do NOT generate backend PHP files (`conexion.php`, `setup.php`, `crear.php`, etc.) or SQLite database files until Phase 1 is officially approved.

## Immediate Steps in Phase 1
1. Update `docs/api-design.md` and `docs/api-design.es.md` with the `unlink()` orphaned file cleanup requirement for `POST /api/eliminar.php`.
2. Update `memory-bank/progress.md` and `memory-bank/techContext.md`.
3. Create `css/styles.css` with craft design tokens and utility classes.
4. Implement semantic HTML5 views: `index.html`, `formulario.html`, `detalle.html`, and `pedidos.html` with working cross-navigation and dynamic Login Modal.
5. Create sample mock assets in `uploads/` for visual verification.
6. Verify cross-navigation and responsiveness.
7. Halt completely and request user review for Phase 1.

