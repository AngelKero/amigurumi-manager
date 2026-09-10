# Project Brief: Handmade Amigurumi Micro-ERP & Catalog System

## Overview
An academic web development project to build a responsive, dynamic web application designed as a **Relational Micro-ERP** for handmade amigurumi crochet creations. The system manages user authentication, product catalog, physical stock inventory, material costs, labor tracking, and custom client commission orders with full relational integrity and CRUD functionality.

## Core Objectives
1. **Catalog & Showcase:** Display creations in a responsive card grid with dynamic stock availability badges and rich specifications.
2. **Business & Inventory Metrics:** Track physical inventory (`cantidad_stock`), yarn investment (`costo_materiales`), and manual labor hours (`horas_tejido`) to compute net margins and hourly return rates.
3. **Order & Commission Tracking:** Record client custom orders (`pedidos`) linked relationally to catalog amigurumi items (`amigurumi_id`), locking in order prices (`precio_final`) and tracking fulfillment stages (`estado_pedido`).
4. **Authentication & Session Security:** Secure backend endpoints using native PHP password hashing (`password_hash`) and server-side sessions (`session_start()`), preventing unauthorized modifications.
5. **Clean Architectural Separation:** Built using clean semantic HTML5, Bootstrap 5, Vanilla JavaScript, and native PHP PDO with SQLite 3 (enforcing `PRAGMA foreign_keys = ON;`).

## Scope & Deliverables
- **Backend & Database Services:**
  - `database/seed.sql`: Complete SQLite DDL schema with relational constraints and seed data.
  - `setup.php`: Database initialization script executing `seed.sql` to generate `database.sqlite`.
  - `conexion.php`: PDO SQLite instance with `PRAGMA foreign_keys = ON;` and `ERRMODE_EXCEPTION`.
  - `auth_guard.php`: Session-based middleware for protecting mutating operations and role enforcement.
  - Modular API endpoints: `api/login.php`, `api/logout.php`, `api/usuarios.php`, `api/crear.php`, `api/leer.php`, `api/actualizar.php`, `api/eliminar.php`, `api/solicitar_pedido.php`, `api/pedidos.php`, `api/actualizar_pedido.php`.
- **Static & Dynamic User Interfaces:**
  - `index.html`: Public catalog and inventory view with category filtering and dynamic stock badges.
  - `formulario.html`: Dual-purpose form for adding and editing amigurumis with real image file uploads and margin preview.
  - `detalle.html`: Comprehensive creation specification, artisan economics view, and Public Client Checkout Modal.
  - `pedidos.html`: Order and custom commission tracking dashboard with state updating.
  - Shared Navigation: Reusable Header/Navbar featuring a dynamic Login/Logout Modal across all views.
- **Frontend Logic:**
  - `js/app.js`: Vanilla JS for client-side form validation, profit calculation previews, modal dialogues, and DOM event handling.
- **Documentation & Verification:**
  - Complete `.docs/` architecture documents (`database-schema.md`, `auth-flow.md`, `api-design.md`, `data-model.md`, `database-testing.md`).
  - `README.md`: Architecture overview and local execution instructions.

## Development Roadmap (Database-First Approach)
1. **Phase 1: Database Implementation, Seeding & Testing** (`database/seed.sql`, `setup.php`, SQLite constraints testing).
2. **Phase 2: Layout & UI** (HTML5, Bootstrap 5 CDN views, modals).
3. **Phase 3: Backend Connection & Auth Guard** (`conexion.php`, `auth_guard.php`).
4. **Phase 4: API Endpoints & DOM JS** (CRUD operations, `app.js`).
5. **Phase 5: Final Documentation** (`README.md`).

## Key Constraints
- Strict adherence to the sequential phase gates.
- Explicit user approval required at each phase gate.
- No external JS/CSS frameworks beyond Bootstrap 5 CDN.
- Pure Vanilla JS and native PHP PDO without external composer dependencies or heavy ORMs.
