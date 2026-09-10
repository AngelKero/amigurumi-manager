# Progress: Handmade Amigurumi Micro-ERP & Catalog

## Development Roadmap & Status

| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| **Phase 0** | Relational Micro-ERP Architecture | **Completed & Signed Off** | Integrated User Management CRUD, Public Client Checkout, Image Uploads, Navbar Login Modal, and Orphaned File Cleanup across bilingual docs. |
| **Phase 1** | Database Implementation, Seeding & Testing | **In Progress** | Create `database/seed.sql`, `setup.php`, initialize `database.sqlite`, verify SQLite constraints, and provide `.docs/database-testing.md`. |
| **Phase 2** | Layout & UI | **Pending (Next)** | Build `index.html`, `formulario.html`, `detalle.html`, and `pedidos.html` with Bootstrap 5 CDN, Navbar Login Modal, and working navigation. |
| **Phase 3** | Backend & Connection | **Pending** | Implement `conexion.php` with `PRAGMA foreign_keys = ON;`, `ERRMODE_EXCEPTION`, and `auth_guard.php`. |
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
- All 24 JSON blocks across bilingual API docs tested and validated.

## What's Left to Build
- Complete Phase 1: Physical database creation, seed execution, and testing guide.
- Phase 2: HTML5/Bootstrap 5 UI templates and client navigation.
- Phase 3: PDO database connection and session authentication middleware.
- Phase 4: CRUD API endpoints and Vanilla JS DOM logic (`app.js`).
- Phase 5: Comprehensive project documentation (`README.md`).

## Known Issues / Blockers
- None. Proceeding with Phase 1 execution under strict Database-First approach.
