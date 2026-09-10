# Progress: Handmade Amigurumi Micro-ERP & Catalog

## Development Roadmap & Status

| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| **Phase 0** | Relational Micro-ERP Architecture | **Fully Corrected & Awaiting Sign-off** | Integrated User Management CRUD (Admin only), Public Client Checkout (`solicitar_pedido.php`), Local Image Uploads (`/uploads`), and Navbar Login Modal across bilingual documentation. |
| **Phase 1** | Layout & UI | **Pending (Blocked by Phase 0)** | Build `index.html`, `formulario.html`, `detalle.html`, and `pedidos.html` with Bootstrap 5 CDN, Navbar Login Modal, and working navigation. |
| **Phase 2** | Database Setup | **Pending** | Create `setup.php`, initialize SQLite schema with foreign keys, and create local `/uploads` directory. |
| **Phase 3** | Backend & Connection | **Pending** | Implement `conexion.php` with `PRAGMA foreign_keys = ON;`, `ERRMODE_EXCEPTION`, and `auth_guard.php`. |
| **Phase 4** | CRUD Operations & JS | **Pending** | Implement modular endpoints (`login.php`, `usuarios.php`, `crear.php`, `leer.php`, `actualizar.php`, `eliminar.php`, `solicitar_pedido.php`, `pedidos.php`, `actualizar_pedido.php`) and `app.js`. |
| **Phase 5** | Documentation | **Pending** | Write comprehensive `README.md` with setup and local server instructions (`php -S localhost:8000`). |

## What Works
- Memory Bank completely synchronized across all 5 core files.
- Modular architecture documentation in `docs/` (`.docs/`) in both English and Spanish.
- User Management CRUD (`api/usuarios.php`) restricted strictly to `'admin'`.
- Public Client Checkout (`api/solicitar_pedido.php`) with atomic stock deduction and anti-price spoofing.
- Real file upload specifications to `/uploads` with MIME/size validation.
- Transition of login interface to a reusable Navbar Modal.
- All 24 JSON blocks across bilingual API docs tested and validated.

## What's Left to Build
- Final sign-off on Phase 0 relational architecture.
- Phase 1 UI templates.
- Phase 2 Database setup script (`setup.php`).
- Phase 3 PDO connection and session guard.
- Phase 4 CRUD API endpoints and DOM JavaScript (`app.js`).
- Phase 5 Project documentation (`README.md`).

## Known Issues / Blockers
- Awaiting final user approval of Phase 0 deliverables before generating Phase 1 application files.
