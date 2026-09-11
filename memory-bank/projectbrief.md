# Project Brief: Handmade Crochet Creations Micro-ERP & Catalog System

## Overview
An academic web development project to build a responsive, dynamic web application designed as a **Relational Micro-ERP** for handmade crochet textile creations (including amigurumis, garments/clothing, bags/accessories, and home decor). The system manages user authentication, product catalog, physical stock inventory, material costs, labor tracking, and custom client commission orders with full relational integrity and CRUD functionality.

## Core Objectives
1. **Catalog & Showcase:** Display crochet creations in a responsive card grid with dynamic stock availability badges and rich specifications (dimensions, sizing, materials).
2. **Business & Inventory Metrics:** Track physical inventory (`cantidad_stock`), yarn investment (`costo_materiales`), and manual labor hours (`horas_tejido`) to compute net margins and hourly return rates.
3. **Order & Commission Tracking:** Record client custom orders (`pedidos`) linked relationally to catalog crochet items (`creacion_id`), locking in order prices (`precio_final`) and tracking fulfillment stages (`estado_pedido`).
4. **Authentication & Session Security:** Secure backend endpoints using native PHP password hashing (`password_hash`) and server-side sessions (`session_start()`), preventing unauthorized modifications.
5. **Clean Architectural Separation:** Built using clean semantic HTML5, Bootstrap 5, native PHP component architecture, ITCSS styles, Vanilla JavaScript ES Modules, and native PHP PDO with SQLite 3 (enforcing `PRAGMA foreign_keys = ON;`).

## Scope & Deliverables
- **Backend & Database Services:**
  - `database/seed.sql`: Complete SQLite DDL schema with relational constraints (`creaciones`, `usuarios`, `pedidos`) and seed data.
  - `database/database.sqlite`: Physical SQLite database file located safely inside the protected `database/` directory.
  - `setup.php`: CLI-only database initialization script executing `seed.sql` to generate `database/database.sqlite`.
  - `.htaccess`: Root Apache security configuration blocking web access to `.sqlite`, `.sql`, `.md`, `database/`, and `memory-bank/`.
  - `src/Database/Conexion.php`: PDO SQLite instance connecting to `database/database.sqlite` with `PRAGMA foreign_keys = ON;` and `ERRMODE_EXCEPTION`.
  - `src/Middleware/AuthGuard.php`: Session-based middleware for protecting mutating operations and role enforcement.
  - Modular API endpoints in `api/`: thin controllers exposing RESTful JSON responses.
- **Static & Dynamic User Interfaces:**
  - `index.php`: Public catalog and inventory view with category filtering, dynamic stock badges, and textile chips.
  - `creaciones.php`: Administrative workshop creations and inventory dashboard with quick stock controls and modals.
  - `amigurumis.php`: HTTP 301 permanent redirect forwarder to `creaciones.php`.
  - `formulario.php`: Dual-purpose form for adding and editing creations with real image file uploads and margin preview.
  - `detalle.php`: Comprehensive creation specification, artisan economics view, and Public Client Checkout Modal.
  - `pedidos.php`: Order and custom commission tracking dashboard with responsive card grid and WhatsApp contact.
  - `usuarios.php`: Artisan team directory and role administration with root user lockout safeguard.
  - Shared Navigation: Reusable Header/Navbar featuring a dynamic Login/Logout Modal and left panel sidebar.
- **Frontend Logic:**
  - `src/js/main.js` and modular ES modules (`auth.js`, `catalog.js`, `creaciones.js`, `detail.js`, `margin-calculator.js`, `orders.js`, `users.js`, `currency.js`).
- **Documentation & Verification:**
  - Complete `docs/` architecture documents (`database-schema.md`, `auth-flow.md`, `api-design.md`, `data-model.md`, `database-testing.md`, `identidad-visual.md`, `svg-assets-and-helper.md`, `README.md`).
  - `README.md`: Architecture overview and local execution instructions.

## Development Roadmap (Database-First Approach)
1. **Phase 1: Database Implementation, Seeding & Testing** (`database/seed.sql`, CLI-only `setup.php`, `database/database.sqlite`, `.htaccess`, SQLite constraints testing).
2. **Phase 2: Layout & UI** (Component-based PHP views, ITCSS Algodón Nórdico, ES Modules).
3. **Phase 3: Backend Connection & Auth Guard** (`Conexion.php`, `AuthGuard.php`, Clean Architecture Repositories/Services).
4. **Phase 4: API Endpoints & DOM JS** (CRUD operations, fullstack wiring).
5. **Phase 5: Final Documentation & Handover** (`README.md`, `docs/`).

## Key Constraints
- Strict adherence to the sequential phase gates.
- Explicit user approval required at each phase gate.
- No external JS/CSS frameworks beyond Bootstrap 5 CDN.
- Pure Vanilla JS and native PHP PDO without external composer dependencies or heavy ORMs.
