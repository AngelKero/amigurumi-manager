# Project Brief: Multi-Artisan Crochet Platform & Collaborative Micro-ERP

## Overview
An academic web development project to build a responsive, dynamic web application designed as a **Relational Collaborative Micro-ERP & Marketplace Platform** for handmade crochet textile creations (including amigurumis, garments/clothing, bags/accessories, and home decor). The platform enables multiple independent artisans to register, publish, and manage their creations and custom client commissions with full relational integrity and autonomous operational control.

Because multiple independent artisans register and operate autonomously, the platform **does not control what, how, or when artisans craft**. Platform assurances focus on **transparency, verified artisan profiles, direct WhatsApp communication, accurate technical sheets, and ethical labor/margin calculation tools**.

## Core Objectives
1. **Collaborative Catalog & Showcase:** Display multi-artisan crochet creations in a responsive card grid with dynamic stock availability badges, author attribution, and rich specifications (dimensions, materials, care).
2. **Business & Inventory Metrics for Creators:** Provide independent artisans with tools to track physical inventory (`cantidad_stock`), yarn investment (`costo_materiales`), and manual labor hours (`horas_tejido`) to compute net margins and hourly return rates.
3. **Direct Order & Commission Coordination:** Record custom client orders (`pedidos`) linked relationally to catalog items (`creacion_id`), locking in order prices (`precio_final`) and facilitating direct client-artisan coordination.
4. **Authentication & Session Security:** Secure backend endpoints using native PHP password hashing (`password_hash`) and server-side sessions (`session_start()`), with RBAC role governance (`admin`, `artesano`, `asistente`).
5. **Clean Architectural Separation:** Built using clean semantic HTML5, Bootstrap 5, native PHP component architecture, ITCSS styles, Vanilla JavaScript ES Modules, and native PHP PDO with SQLite 3 (enforcing `PRAGMA foreign_keys = ON;`).

## Scope & Deliverables
- **Backend & Database Services:**
  - `database/seed.sql`: Complete SQLite DDL schema with relational constraints (`creaciones`, `usuarios`, `pedidos`) and seed data.
  - `database/database.sqlite`: Physical SQLite database file located safely inside the protected `database/` directory.
  - `setup.php`: CLI-only database initialization script executing `seed.sql` to generate `database/database.sqlite`.
  - `.htaccess`: Root Apache security configuration blocking web access to `.sqlite`, `.sql`, `.md`, `app/`, `database/`, `memory-bank/`, `logs/`, and `tests/`.
  - `tests/`: Automated CLI test suites (`TestHelper.php`, `test-subfase-3.X.php`) validating classes and HTTP curl endpoints.
  - `logs/`: Ephemeral execution and curl logs protected by `logs/.htaccess` and git-ignored.
  - `docs/testing/`: Formal executive test reports per subphase (`subfase-3.1-core.md` to `subfase-3.6-seguridad.md`).
  - `app/Core/Database.php`: Singleton PDO SQLite instance connecting to `database/database.sqlite` with `PRAGMA foreign_keys = ON;` and `ERRMODE_EXCEPTION`.
  - `app/Core/TokenManager.php`: Stateless HMAC-SHA256 Bearer Token issuer and verifier (24h TTL) with timing-attack resistant `hash_equals()`.
  - `app/Middleware/AuthGuard.php`: Middleware for Bearer token validation and RBAC role enforcement (`admin`, `artesano`, `asistente`).
  - Modular API endpoints in `api/`: thin controllers in thematic subdirectories (`api/auth/`, `api/creaciones/`, `api/pedidos/`, `api/usuarios/`) exposing RESTful JSON responses.
- **Static & Dynamic User Interfaces:**
  - `index.php`: Public catalog and inventory view with category filtering, dynamic stock badges, and textile chips.
  - `creaciones.php`: Administrative workshop creations and inventory dashboard with quick stock controls and modals.
  - `piezas.php`: HTTP 301 permanent redirect forwarder to `creaciones.php`.
  - `formulario.php`: Dual-purpose form for adding and editing creations with real image file uploads and margin preview.
  - `detalle.php`: Comprehensive creation specification, artisan economics view, and Public Client Checkout Modal.
  - `pedidos.php`: Order and custom commission tracking dashboard with responsive card grid and WhatsApp contact.
  - `usuarios.php`: Artisan team directory and role administration with root user lockout safeguard.
  - Shared Navigation: Reusable Header/Navbar featuring a dynamic Login/Logout Modal and left panel sidebar.
- **Frontend Logic:**
  - `src/js/main.js` and modular ES modules (`auth.js`, `catalog.js`, `creaciones.js`, `detail.js`, `margin-calculator.js`, `orders.js`, `users.js`, `currency.js`).
- **Documentation & Verification:**
  - Complete `docs/` architecture documents (`database-schema.md`, `auth-flow.md`, `api-design.md`, `data-model.md`, `database-testing.md`, `identidad-visual.md`, `svg-assets-and-helper.md`, `phase-3-backend-architecture-plan.md`, `README.md`).
  - `README.md`: Architecture overview and local execution instructions.

## Development Roadmap (Database-First Approach)
1. **Phase 1: Database Implementation, Seeding & Testing** (`database/seed.sql`, CLI-only `setup.php`, `database/database.sqlite`, `.htaccess`, SQLite constraints testing).
2. **Phase 2: Layout & UI** (Component-based PHP views, ITCSS Algodón Nórdico, ES Modules).
3. **Phase 3: Backend & Connection in `app/` (6 Sequential Subphases)** (`app/autoload.php`, `app/config.php`, `Database.php`, `TokenManager.php`, `ErrorHandler.php`, `AuthGuard.php`, Clean Architecture Repositories/Services, and thin `api/` controllers).
4. **Phase 4: API Endpoints & DOM JS** (CRUD operations, fullstack wiring).
5. **Phase 5: Final Documentation & Handover** (`README.md`, `docs/`).

## Key Constraints
- Strict adherence to the sequential phase gates.
- Explicit user approval required at each phase gate.
- No external JS/CSS frameworks beyond Bootstrap 5 CDN.
- Pure Vanilla JS and native PHP PDO without external composer dependencies or heavy ORMs.
