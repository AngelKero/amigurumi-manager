# Progress: Handmade Amigurumi Micro-ERP & Catalog

## Development Roadmap & Status

| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| **Phase 0** | Relational Micro-ERP Architecture | **Completed & Signed Off** | Integrated User Management CRUD, Public Client Checkout, Image Uploads, Navbar Login Modal, and Orphaned File Cleanup across bilingual docs. |
| **Phase 1** | Database Implementation, Seeding & Testing | **Completed & Signed Off** | Implemented `database/seed.sql`, CLI-only `setup.php`, `database/database.sqlite`, Apache `.htaccess` protection, and `.docs/database-testing.md`. |
| **Phase 2** | Layout & UI (Algodón Nórdico) | **Completed & Awaiting Sign-off** | Built and refined `index.html`, `detalle.html`, `formulario.html`, `pedidos.html`, `css/styles.css`, and `js/app.js` with full craft detailing across Header, Banner, Filters, and Cards. |
| **Phase 3** | Backend & Connection | **Pending (Next)** | Implement `conexion.php` with `PRAGMA foreign_keys = ON;`, `ERRMODE_EXCEPTION`, and `auth_guard.php`. |
| **Phase 4** | CRUD Operations & JS | **Pending** | Implement modular endpoints (`login.php`, `usuarios.php`, `crear.php`, `leer.php`, `actualizar.php`, `eliminar.php`, `solicitar_pedido.php`, `pedidos.php`, `actualizar_pedido.php`) and `app.js`. |
| **Phase 5** | Documentation | **Pending** | Write comprehensive `README.md` with setup and local server instructions (`php -S localhost:8000`). |

## What Works
- Memory Bank completely synchronized across all 5 core files.
- Modular architecture documentation in `docs/` (`.docs/`) in both English and Spanish.
- User Management CRUD (`api/usuarios.php`) restricted strictly to `'admin'`.
- Public Client Checkout (`api/solicitar_pedido.php`) with atomic stock deduction and anti-price spoofing.
- Real file upload specifications to `/uploads` with MIME/size validation.
- Physical asset cleanup policy on deletion (`POST /api/eliminar.php`) using PHP `unlink()` to eliminate orphaned files.
- Multi-layered security: CLI-only `setup.php`, `database/database.sqlite` isolation, and Apache `.htaccess` access control.
- Phase 1 Database completed: `database/seed.sql`, `database/database.sqlite` initialized, verified via `docs/database-testing.md`.
- Phase 2 Layout & UI completed & refined:
  - Design System Rules: Enshrined Proposal B ("Algodón Nórdico") in `.agents/rules/ui-ux-design-system.md` and `.agents/rules/general.md`.
  - Header & Navbar Detailing: Pespunte inferior discontinuo (`navbar-craft`), emblema de marca hilvanado (`.brand-craft-badge`), sello artesanal de sesión pergamino/miel con bordado dorado (`.badge-artisan-seal`), y botón de logout textil (`.btn-craft-logout`).
  - Hero Banner Acolchado ("Nube Artesanal"): Pespunte interior acolchado (`.hero-cloud-seam`), marco exterior e interior para la foto de lanas (`.hero-photo-stitched-frame`), chips de confianza textiles (`.trust-chip-stitched`), y botón secundario pespunteado (`.btn-craft-outline-stitched`).
  - Taller de Búsqueda y Filtros: Tarjeta hilvanada de trabajo (`.filter-station-card` con `.card-stitched`), chips textiles interactivos (`.btn-chip-textile`) con sincronización bidireccional y bordado activo, contador de piezas dinámico (`#filterResultsCount`), e inputs píldora (`.input-craft-pill`).
  - `detalle.html`: Out-of-stock guard [CR-1] (`stock === 0` disables checkout CTA), darkened WCAG AA contrast (`#235048` on `#EBF4F2`), lead-time advisory (5-7 business days), and interactive state simulator.
  - `pedidos.html`: Orders dashboard with stacked mobile card view [CR-2] (`#mobileOrdersContainer`), stock restitution confirmation modal [QW-2], and order inspection modal.
  - `formulario.html`: Dual-metric real-time margin simulator with Algodón Nórdico status pills, dropzone image preview, and artisan navigation bar.
  - `css/styles.css`: Complete Algodón Nórdico token overhaul, keyframe floating animations, pill radiuses (`50px`), cloud elevation shadows, and craft stitched border utilities (`.card-stitched`, `.btn-craft-stitched`, `.badge-textile-tag`, `.divider-stitched`, `.guarantee-stitched`).
  - Tactile Craft Borders & Atmospheric Decor: Deployed inset dashed running seams, embroidered button stitches, woven cloth care tags, and floating ambient cloud/yarn SVGs across all views.
  - Design Rules Updated: Codified Rules 1 to 10 in `.agents/rules/ui-ux-design-system.md` to protect craft border aesthetics in all future phases.
  - `js/app.js`: Out-of-stock guard listener, stock-bounded quantity steppers, margin formulas, textile chip filtering, live piece counter, and modal lifecycle handlers.

## What's Left to Build
- Phase 3: PDO database connection (`conexion.php`) and session authentication middleware (`auth_guard.php`).
- Phase 4: CRUD API endpoints and AJAX integration with `app.js`.
- Phase 5: Comprehensive project documentation (`README.md`).

## Known Issues / Blockers
- None. Phase 2 completed. Ready for user inspection and sign-off before commencing Phase 3.
