---
description: Iterative step-by-step workflow for developing a PHP, SQLite, and Bootstrap CRUD web application with phase gates.
---

# Iterative Development Workflow

Development will strictly follow these sequential phases. You must halt completely at the end of each phase and await my explicit instruction before proceeding. Do not assume or generate code for the next phase.

- **Phase 1 - Layout & UI:** Create the project directory structure. Build `index.html` (List view), `formulario.html` (Create/Edit form), and `detalle.html` (Detail view) using semantic HTML5 and Bootstrap via CDN. Ensure navigation works across all 3 pages.
- **Phase 2 - Database Setup:** Write a `setup.php` script to automatically initialize the SQLite database file (`database.sqlite`) and create the primary table schema.
- **Phase 3 - Backend & Connection:** Create `conexion.php` using PHP PDO to connect to SQLite. Configure error handling with `ERRMODE_EXCEPTION`.
- **Phase 4 - CRUD Operations & JS:** Create modular PHP endpoints (`crear.php`, `leer.php`, `actualizar.php`, `eliminar.php`). Add `app.js` with vanilla JavaScript to handle DOM events and validate forms before submission.
- **Phase 5 - Documentation:** Create a `README.md` file detailing project structure and local execution instructions (e.g., `php -S localhost:8000`).
