# Technical Context: Crochet Creations Micro-ERP & Catalog

## Technology Stack
- **Frontend Presentation:** Semantic HTML5, Bootstrap 5.3 (CDN), Bootstrap Icons (CDN), Google Fonts (*Fraunces* display headlines, *Outfit* secondary headers, *Plus Jakarta Sans* body).
- **Custom Styling & Design System:** Proposal B ("Algodón Nórdico") implemented in `src/css/` with cloud-soft aesthetics, floating keyframe micro-animations, pill geometry (`border-radius: 50px`), and codified in `.agents/rules/ui-ux-design-system.md`.
- **Frontend Scripting:** Vanilla JavaScript in native ES Modules (`src/js/main.js` and `src/js/modules/`) for DOM manipulation, dynamic Navbar Login Modal, stock bounds, out-of-stock guards, profit margin calculations, checkout modals, and reactive filtering.
- **Backend Architecture:** Clean Architecture in dedicated `app/` directory (`src/` remains 100% frontend only):
  - Autoloader: PSR-4 native autoloader (`app/autoload.php`) without external Composer dependencies.
  - Configuration: Centralized `app/config.php` & `App\Core\Config` protected by `.htaccess`.
  - Database Layer: SQLite 3 (`database/database.sqlite`) via PDO Singleton (`App\Core\Database`) with `PRAGMA foreign_keys = ON;` and `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
  - Authentication: Stateless HMAC-SHA256 Bearer Tokens (`App\Core\TokenManager`) with 24h TTL and timing-attack resistant `hash_equals()`.
  - Error Handling: Zero HTML leaks via `App\Core\ErrorHandler` (`set_error_handler`, `set_exception_handler`, `register_shutdown_function`) emitting standard JSON 500.
  - CORS Preflight: Centralized `OPTIONS` handling in `App\Core\Response` emitting HTTP 204 No Content.
  - Currency Standard: Dual representation via `App\Utils\CurrencyHelper` (integer cents in SQLite + formatted `$0.00 MXN` in JSON).
  - Pagination Standard: Standardized metadata via `App\Utils\PaginationHelper` (defaults: 12 for creaciones, 20 for pedidos).
- **Local Server:** PHP Built-in development server (`php -S localhost:8000`).

## Relational DDL Specification (3 Tables)
```sql
PRAGMA foreign_keys = ON;

-- 1. Table: usuarios (Authentication & Access Control)
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'admin',
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    -- Table Constraints
    CONSTRAINT uq_usuarios_username UNIQUE (username),
    CONSTRAINT chk_usuarios_username CHECK(length(trim(username)) >= 3 AND length(username) <= 50),
    CONSTRAINT chk_usuarios_rol CHECK(rol IN ('admin', 'artesano', 'asistente'))
);

-- 2. Table: creaciones (Core Catalog & Inventory)
CREATE TABLE IF NOT EXISTS creaciones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    artesano_id INTEGER NOT NULL,
    nombre TEXT NOT NULL,
    categoria TEXT NOT NULL,
    material TEXT NOT NULL,
    dimensiones TEXT NOT NULL,
    precio INTEGER NOT NULL,
    costo_materiales INTEGER NOT NULL DEFAULT 0,
    cantidad_stock INTEGER NOT NULL DEFAULT 0,
    horas_tejido REAL DEFAULT 0.0,
    descripcion TEXT,
    imagen_url TEXT,
    es_sobre_encargo INTEGER NOT NULL DEFAULT 0,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    actualizado_en TEXT DEFAULT NULL,
    -- Table Constraints
    CONSTRAINT fk_creaciones_artesano FOREIGN KEY (artesano_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_creaciones_nombre CHECK(length(trim(nombre)) >= 2 AND length(nombre) <= 100),
    CONSTRAINT chk_creaciones_categoria CHECK(length(trim(categoria)) >= 2 AND length(categoria) <= 50),
    CONSTRAINT chk_creaciones_material CHECK(length(trim(material)) >= 3 AND length(material) <= 80),
    CONSTRAINT chk_creaciones_dimensiones CHECK(length(trim(dimensiones)) >= 2 AND length(dimensiones) <= 100),
    CONSTRAINT chk_creaciones_precio CHECK(precio >= 1 AND precio <= 9999999),
    CONSTRAINT chk_creaciones_costo_materiales CHECK(costo_materiales >= 0 AND costo_materiales <= 9999999),
    CONSTRAINT chk_creaciones_cantidad_stock CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000),
    CONSTRAINT chk_creaciones_horas_tejido CHECK(horas_tejido IS NULL OR (horas_tejido >= 0.0 AND horas_tejido <= 500.0)),
    CONSTRAINT chk_creaciones_descripcion CHECK(descripcion IS NULL OR length(descripcion) <= 2000),
    CONSTRAINT chk_creaciones_imagen_url CHECK(imagen_url IS NULL OR length(trim(imagen_url)) <= 500),
    CONSTRAINT chk_creaciones_es_sobre_encargo CHECK(es_sobre_encargo IN (0, 1))
);

-- 3. Table: pedidos (Orders & Commissions)
CREATE TABLE IF NOT EXISTS pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_nombre TEXT NOT NULL,
    cliente_contacto TEXT NOT NULL DEFAULT '',
    creacion_id INTEGER NOT NULL,
    cantidad INTEGER NOT NULL DEFAULT 1,
    fecha_entrega TEXT,
    estado_pedido TEXT NOT NULL DEFAULT 'Pendiente',
    estado_pago TEXT NOT NULL DEFAULT 'Pendiente',
    precio_final INTEGER NOT NULL,
    notas TEXT,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    -- Table Constraints
    CONSTRAINT fk_pedidos_creacion FOREIGN KEY (creacion_id) REFERENCES creaciones(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_pedidos_cliente_nombre CHECK(length(trim(cliente_nombre)) >= 2 AND length(cliente_nombre) <= 100),
    CONSTRAINT chk_pedidos_cantidad CHECK(cantidad >= 1 AND cantidad <= 1000),
    CONSTRAINT chk_pedidos_fecha_entrega CHECK(fecha_entrega IS NULL OR length(trim(fecha_entrega)) = 10),
    CONSTRAINT chk_pedidos_estado CHECK(estado_pedido IN ('Pendiente', 'En Proceso', 'Entregado', 'Cancelado')),
    CONSTRAINT chk_pedidos_precio_final CHECK(precio_final >= 1 AND precio_final <= 9999999),
    CONSTRAINT chk_pedidos_notas CHECK(notas IS NULL OR length(notas) <= 1000),
    CONSTRAINT chk_pedidos_cliente_contacto CHECK(length(trim(cliente_contacto)) <= 50),
    CONSTRAINT chk_pedidos_estado_pago CHECK(estado_pago IN ('Pendiente', 'Anticipo 50%', 'Liquidado'))
);

-- Performance Indexes
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_creaciones_artesano ON creaciones(artesano_id);
CREATE INDEX IF NOT EXISTS idx_creaciones_categoria ON creaciones(categoria);
CREATE INDEX IF NOT EXISTS idx_creaciones_stock ON creaciones(cantidad_stock);
CREATE INDEX IF NOT EXISTS idx_pedidos_creacion ON pedidos(creacion_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado_pedido);
```

## Project Directory Layout
```
proyecto-web/
├── app/                                # 🟢 NUEVO: CAPA BACKEND PHP EXCLUSIVA (Protegida por .htaccess)
│   ├── autoload.php                   # Autocargador PSR-4 nativo & registro de ErrorHandler
│   ├── config.php                     # Configuración centralizada (claves secretas, timezone, TTL tokens, límites)
│   ├── Core/                          # Componentes nucleares de infraestructura
│   │   ├── Database.php               # Singleton PDO SQLite (PRAGMA foreign_keys = ON; ERRMODE_EXCEPTION)
│   │   ├── Config.php                 # Clase de acceso estático con caché a configuración y notación por puntos
│   │   ├── ErrorHandler.php           # Capturador global de errores, excepciones y fatal errors (cero fugas HTML)
│   │   ├── Request.php                # Abstracción segura de entrada (POST, GET, JSON, Files, Bearer Token)
│   │   ├── Response.php               # Emisor estandarizado de respuestas JSON, códigos HTTP y Preflight CORS
│   │   ├── TokenManager.php           # Gestor de Tokens Bearer HMAC-SHA256 (firma, verificación, expiración)
│   │   └── SessionManager.php         # Gestión complementaria para sesiones web/SSR si se requiere
│   ├── Database/                      # Clases auxiliares o migraciones si se requieren
│   ├── Repositories/                  # CAPA DE PERSISTENCIA (100% del SQL aislado aquí)
│   │   ├── CreacionRepository.php     # Consultas de creaciones, filtros, paginación, stock y catálogo
│   │   ├── PedidoRepository.php       # Consultas de pedidos, paginación, transacciones y estados de pago
│   │   └── UsuarioRepository.php      # Consultas de usuarios, verificación de credenciales y roles
│   ├── Services/                      # REGLAS DE NEGOCIO Y CASOS DE USO
│   │   ├── AuthService.php            # Verificación de credenciales, hashing bcrypt y ciclo de sesión
│   │   ├── CreacionService.php        # Validaciones de piezas, enriquecimiento monetario dual y fallback SVG
│   │   ├── PedidoService.php          # Transacción atómica de stock, congelamiento de precios y WhatsApp
│   │   └── UsuarioService.php         # Alta de creadores, cambio de roles RBAC y protección de admin raíz
│   ├── Middleware/                    # CONTROLADORES DE ACCESO Y SEGURIDAD
│   │   ├── AuthGuard.php              # Verificación de sesión activa (retorna 401 si no hay sesión)
│   │   └── RoleGuard.php              # Verificación de privilegios de rol (admin, artesano, asistente)
│   └── Utils/                         # UTILIDADES Y HELPERS DE SERVIDOR
│       ├── CurrencyHelper.php         # Conversión bidireccional centavos enteros <-> pesos MXN y enriquecimiento
│       ├── PaginationHelper.php       # Cálculo unificado de límites, offsets y metadatos de paginación
│       └── SvgHelper.php              # Helper de renderizado vectorial SVG con caché en memoria
├── docs/ (symlinked to .docs/)
│   ├── README.md                      # (Documentation Hub & Navigation Map)
│   ├── phase-3-backend-architecture-plan.md # (Master Architecture Plan for Phase 3)
│   ├── data-model.md / .es.md         # (Master Architecture Index & Physical ERD)
│   ├── database-schema.md / .es.md    # (Relational DDL, Data Dictionaries)
│   ├── auth-flow.md / .es.md          # (Bearer Token Lifecycle & RBAC Matrix)
│   ├── api-design.md / .es.md         # (REST Endpoints & Standard JSON Payloads)
│   ├── database-testing.md / .es.md   # (SQLite Terminal Verification Guide)
│   └── qa-audit-report.md             # (Comprehensive Multi-Axis Quality Assurance Report)
├── memory-bank/
│   ├── projectbrief.md
│   ├── productContext.md
│   ├── techContext.md
│   ├── activeContext.md
│   └── progress.md
├── database/
│   ├── seed.sql                       # Full SQLite DDL schema and initial seed data
│   └── database.sqlite                # Physical SQLite DB file (Phase 1)
├── views/
│   ├── layouts/main.php               # Master HTML layout, fonts, CSS/JS links
│   ├── components/                    # Navbar, modals, sidebar, footer, product_card
│   └── pages/                         # catalogo, creaciones, detalle, formulario, pedidos, usuarios
├── src/                               # 🔵 EXCLUSIVO FRONTEND (Sin archivos PHP)
│   ├── css/                           # Modular ITCSS styles (01-settings to 04-components)
│   └── js/                            # JavaScript modular nativo (ES Modules: main.js + modules/)
├── uploads/                           # Local directory storing uploaded product images
├── tests/                             # 🧪 CLI Automated Test Suites (TestHelper.php, test-subfase-3.X.php)
├── logs/                              # 🪵 Raw CLI & HTTP Execution Logs (Protected by .htaccess & .gitignore)
├── api/                               # 🌐 CONTROLADORES REST DELGADOS (Subcarpetas temáticas)
│   ├── auth/                          # login.php, logout.php, me.php
│   ├── creaciones/                    # index.php, detalle.php, crear.php, actualizar.php, eliminar.php, etc.
│   ├── pedidos/                       # index.php, solicitar.php, crear.php, cambiar-estado.php, cancelar.php
│   └── usuarios/                      # index.php, crear.php, cambiar-rol.php
├── setup.php                          # CLI-only database initialization script executing seed.sql
├── index.php                          # Catalog & inventory showcase
├── creaciones.php                     # Admin Creations and Stock Management
├── piezas.php                         # HTTP 301 Permanent Redirect to creaciones.php
├── formulario.php                     # Add / Edit creation with image upload and margin simulator
├── detalle.php                        # Detailed piece view with public checkout modal
├── pedidos.php                        # Order and commission tracking dashboard
├── usuarios.php                       # Artisan user team management
├── .htaccess                          # Apache security blocking .sqlite, .sql, .md, app/, database/, memory-bank/, logs/, tests/
├── .gitignore                         # Git exclusions for binaries, OS artifacts, uploads, and logs/*.log
└── README.md                          # Execution and setup documentation
```

## Technical Constraints & Safety
- **Strict Backend / Frontend Physical Separation:** Directory `src/` is 100% reserved for frontend assets (`src/css/`, `src/js/`). All backend PHP classes and infrastructure reside strictly in `app/`.
- **Multi-Layered Web & Database Security:** SQLite database relocated to `database/database.sqlite`. Direct HTTP/browser access to `app/`, `database/`, `memory-bank/`, `logs/`, `tests/`, `.sqlite`, `.sql`, and `.md` files is strictly blocked via Apache `.htaccess` (HTTP 403 Forbidden).
- **3-Tier Testing & Sign-Off Architecture:** Native CLI test suites in `tests/`, raw execution and HTTP curl dumps in `logs/` (excluded from git, blocked in web), and human-readable QA reports in `docs/testing/`. Any assistant must halt and await user approval after each subphase report before writing code for the next subphase.
- **Pass Authorization Header:** Apache `.htaccess` explicitly forwards `HTTP:Authorization` header to PHP environment to ensure Bearer tokens reach PHP in FastCGI environments.
- **CLI-Only Database Setup:** `setup.php` is strictly restricted to CLI execution (`php_sapi_name() === 'cli'`), completely preventing remote browser-driven database resets or data loss.
- **Foreign Key Enforcement:** Explicit `PRAGMA foreign_keys = ON;` executed on every PDO connection.
- **Stateless Bearer Token Security:** `App\Core\TokenManager` generates HMAC-SHA256 tokens with 24h TTL, validated using timing-attack resistant `hash_equals()`.
- **Zero HTML Error Leaks:** `App\Core\ErrorHandler` captures all uncaught exceptions, errors, and fatal errors, clearing the output buffer with `ob_end_clean()` and emitting standard JSON 500.
- **Preflight CORS Compliance:** `App\Core\Response::handleCors()` immediately answers HTTP `OPTIONS` with `204 No Content` and access control headers.
- **Server-Side Price Calculation & Exact Cent Storage:** Financial amounts are stored as integer cents in SQLite. `pedidos.precio_final` is calculated on the server (`precio * cantidad`). `CurrencyHelper` provides dual JSON output (integer cents + formatted `$0.00 MXN`).
- **Standardized Pagination:** Catalog list defaults to 12 items (optimal for responsive 1, 2, 3, 4 grid); orders dashboard defaults to 20 items. Handled via `App\Utils\PaginationHelper`.
- **Atomic Stock Transactions:** Creating an order requires `BEGIN IMMEDIATE TRANSACTION`, checking available stock and updating `cantidad_stock`.
- **Restocking on Cancellation:** Updating an order to `'Cancelado'` restores units to `cantidad_stock` within a transaction.
- **Role-Based Protection & Root Admin Lockout:** Administrative routes are strictly locked to authenticated users (`admin` or `artesano`). User ID #1 is protected from role modification or deletion.
- **Secure Image Uploads, Fallback SVG & Asset Lifecycle:** Binary files validated by MIME type, size limit ($\le 5\text{MB}$), unique cryptographic file naming, stored in `/uploads/`. If no image is provided, `CreacionService` automatically assigns a thematic SVG vector from `assets/svg/piezas/`. When updating or deleting a creation, previous images are unlinked from `/uploads/` using PHP `unlink()`.
- **SQL Injection Prevention:** 100% parameterized PDO prepared statements.
