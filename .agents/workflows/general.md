---
description: Iterative step-by-step workflow for developing a PHP, SQLite, and Bootstrap CRUD web application with phase gates.
---

# Iterative Development Workflow

Development will strictly follow these sequential phases. You must halt completely at the end of each phase and await my explicit instruction before proceeding. Do not assume or generate code for the next phase.

- **Phase 1 - Database Implementation, Seeding & Testing:** Create the `database/seed.sql` script (full DDL schema and seed mock data), write `setup.php` to initialize `database.sqlite` via PDO with foreign keys enabled, and document testing procedures in `.docs/database-testing.md`.
- **Phase 2 - Layout & UI:** Build `index.html` (Catalog & Inventory view), `formulario.html` (Create/Edit form with file upload), `detalle.html` (Detail view with public checkout modal), and `pedidos.html` (Orders dashboard) using semantic HTML5 and Bootstrap 5 via CDN. Ensure navigation works across views.
- **Phase 3 - Backend & Connection:** Create `conexion.php` using PHP PDO to connect to SQLite with `PRAGMA foreign_keys = ON;` and `ERRMODE_EXCEPTION`. Implement `auth_guard.php` for session authentication.
- **Phase 4 - CRUD Operations & JS:** Create modular PHP endpoints (`login.php`, `logout.php`, `usuarios.php`, `crear.php`, `leer.php`, `actualizar.php`, `eliminar.php`, `solicitar_pedido.php`, `pedidos.php`, `actualizar_pedido.php`). Add `app.js` with vanilla JavaScript to handle DOM events, modals, and validation.
- **Phase 5 - Documentation:** Create a `README.md` file detailing project structure and local execution instructions (e.g., `php -S localhost:8000`).
