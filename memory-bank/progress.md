# Progress: Handmade Amigurumi Micro-ERP & Catalog

## Development Roadmap & Status

| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| **Phase 0** | Relational Micro-ERP Architecture | **Completed & Signed Off** | Integrated User Management CRUD, Public Client Checkout, Image Uploads, Navbar Login Modal, and Orphaned File Cleanup across bilingual docs. |
| **Phase 1** | Database Implementation, Seeding & Testing | **Completed & Signed Off** | Implemented `database/seed.sql`, CLI-only `setup.php`, `database/database.sqlite`, Apache `.htaccess` protection, and `.docs/database-testing.md`. |
| **Phase 2** | Layout & UI | **Completed & Awaiting Sign-off** | Built `index.html`, `detalle.html`, `formulario.html`, `pedidos.html`, `css/styles.css`, and `js/app.js` with all user-requested UX refinements. |
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
- Transition of login interface to a reusable Navbar Modal.
- Multi-layered security: CLI-only `setup.php`, `database/database.sqlite` isolation, and Apache `.htaccess` access control.
- Phase 1 Database completed: `database/seed.sql`, `database/database.sqlite` initialized, verified via `docs/database-testing.md`.
- Phase 2 Layout & UI completed:
  - `index.html`: Clickable cards/titles, sorting dropdown, stock=0 disabled button, and stepper modal.
  - `detalle.html`: Subtle return link, artisan guarantee badge block, specifications table, and stepper checkout modal.
  - `formulario.html`: Hidden image preview, refined dual-metric live margin calculator, and artisan panel bar.
  - `pedidos.html`: Orders dashboard, stock restoration cancellation modal, and order inspection modal.
  - `css/styles.css`: Warm artisan design system with terracotta, sage green, and linen styling.
  - `js/app.js`: Client-side interactivity for steppers, margin math, image preview, sorting, and modals.

## What's Left to Build
- Phase 3: PDO database connection (`conexion.php`) and session authentication middleware (`auth_guard.php`).
- Phase 4: CRUD API endpoints and AJAX integration with `app.js`.
- Phase 5: Comprehensive project documentation (`README.md`).

## Known Issues / Blockers
- None. Phase 2 completed. Ready for user inspection and sign-off before commencing Phase 3.
