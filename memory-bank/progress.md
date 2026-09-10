# Progress: Handmade Amigurumi Micro-ERP & Catalog

## Development Roadmap & Status

| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| **Phase 0** | Relational Micro-ERP Architecture | **Completed (Awaiting Sign-off)** | Designed 3-table relational schema (`usuarios`, `amigurumis`, `pedidos`), modular documentation (`.docs/`), ERD, session authentication flow, and DDL. |
| **Phase 1** | Layout & UI | **Pending (Blocked by Phase 0)** | Build `index.html`, `formulario.html`, `detalle.html`, `pedidos.html`, and `login.html` with Bootstrap 5 CDN and working navigation. |
| **Phase 2** | Database Setup | **Pending** | Create `setup.php` and initialize relational SQLite schema (`database.sqlite`) with foreign keys and admin seeder. |
| **Phase 3** | Backend & Connection | **Pending** | Implement `conexion.php` with `PRAGMA foreign_keys = ON;`, `ERRMODE_EXCEPTION`, and `auth_guard.php`. |
| **Phase 4** | CRUD Operations & JS | **Pending** | Implement modular endpoints (`login.php`, `crear.php`, `leer.php`, `actualizar.php`, `eliminar.php`, `pedidos.php`) and `app.js`. |
| **Phase 5** | Documentation | **Pending** | Write comprehensive `README.md` with setup and local server instructions (`php -S localhost:8000`). |

## What Works
- Memory Bank synchronized across all 5 core files.
- Modular architecture documentation created inside `.docs/`:
  - [data-model.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/data-model.md)
  - [database-schema.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/database-schema.md)
  - [auth-flow.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/auth-flow.md)
  - [api-design.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/api-design.md)
- Micro-ERP relational design with foreign key constraints, price immutability, and session security.

## What's Left to Build
- Final sign-off on Phase 0 relational architecture.
- Phase 1 UI templates.
- Phase 2 Database setup script (`setup.php`).
- Phase 3 PDO connection and session guard.
- Phase 4 CRUD API endpoints and DOM JavaScript (`app.js`).
- Phase 5 Project documentation (`README.md`).

## Known Issues / Blockers
- Awaiting final user approval of relational schema before generating Phase 1 application files.
