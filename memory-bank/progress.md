# Progress: Handmade Amigurumi Micro-ERP & Catalog

## Development Roadmap & Status

| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| **Phase 0** | Relational Micro-ERP Architecture | **Completed (Awaiting Final Sign-off)** | Designed 3-table relational schema with full foreign keys (`usuarios -> amigurumis -> pedidos`), bilingual documentation (`.es.md`), tested valid JSON, and verified SQLite DDL. |
| **Phase 1** | Layout & UI | **Pending (Blocked by Phase 0)** | Build `index.html`, `formulario.html`, `detalle.html`, `pedidos.html`, and `login.html` with Bootstrap 5 CDN and working navigation. |
| **Phase 2** | Database Setup | **Pending** | Create `setup.php` and initialize relational SQLite schema (`database.sqlite`) with foreign keys and admin seeder. |
| **Phase 3** | Backend & Connection | **Pending** | Implement `conexion.php` with `PRAGMA foreign_keys = ON;`, `ERRMODE_EXCEPTION`, and `auth_guard.php`. |
| **Phase 4** | CRUD Operations & JS | **Pending** | Implement modular endpoints (`login.php`, `crear.php`, `leer.php`, `actualizar.php`, `eliminar.php`, `pedidos.php`) and `app.js`. |
| **Phase 5** | Documentation | **Pending** | Write comprehensive `README.md` with setup and local server instructions (`php -S localhost:8000`). |

## What Works
- Memory Bank completely synchronized across all 5 core files.
- Modular architecture documentation in `docs/` (`.docs/`) in both English and Spanish.
- Fully connected ERD: `USUARIOS` 1-to-many `AMIGURUMIS` 1-to-many `PEDIDOS`.
- SQLite DDL schema with foreign key constraints, indexes, and precision rules tested with 100% success.
- Session-based artisan identity attribution (`artesano_id = $_SESSION['user_id']`) preventing tampering.

## What's Left to Build
- Final sign-off on Phase 0 relational architecture.
- Phase 1 UI templates.
- Phase 2 Database setup script (`setup.php`).
- Phase 3 PDO connection and session guard.
- Phase 4 CRUD API endpoints and DOM JavaScript (`app.js`).
- Phase 5 Project documentation (`README.md`).

## Known Issues / Blockers
- Awaiting final user approval of Phase 0 deliverables before generating Phase 1 application files.
