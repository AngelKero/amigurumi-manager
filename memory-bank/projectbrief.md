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
- **Static & Dynamic User Interfaces:**
  - `index.html`: Public catalog and inventory view with category filtering and dynamic stock badges.
  - `formulario.html`: Dual-purpose form for adding and editing amigurumis with margin preview.
  - `detalle.html`: Comprehensive creation specification and artisan economics view.
  - `pedidos.html`: Order and custom commission tracking dashboard.
  - `login.html`: Administrative login interface.
- **Backend Services:**
  - `setup.php`: Database initialization and relational schema migration (`usuarios`, `amigurumis`, `pedidos`).
  - `conexion.php`: PDO SQLite instance with `PRAGMA foreign_keys = ON;` and `ERRMODE_EXCEPTION`.
  - `auth_guard.php`: Session-based middleware for protecting mutating operations.
  - Modular API endpoints: `api/login.php`, `api/logout.php`, `api/crear.php`, `api/leer.php`, `api/actualizar.php`, `api/eliminar.php`, `api/pedidos.php`.
- **Frontend Logic:**
  - `js/app.js`: Vanilla JS for client-side form validation, profit calculation previews, modal dialogues, and DOM event handling.
- **Documentation:**
  - Complete `.docs/` architecture documents (`database-schema.md`, `auth-flow.md`, `api-design.md`, `data-model.md`).
  - `README.md`: Architecture overview and local execution instructions.

## Key Constraints
- Strict adherence to the sequential phase gates.
- Explicit user approval required at each phase gate.
- No external JS/CSS frameworks beyond Bootstrap 5 CDN.
- Pure Vanilla JS and native PHP PDO without external composer dependencies or heavy ORMs.
