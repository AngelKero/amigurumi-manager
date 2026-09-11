# Technical Context: Crochet Creations Micro-ERP & Catalog

## Technology Stack
- **Frontend Presentation:** Semantic HTML5, Bootstrap 5.3 (CDN), Bootstrap Icons (CDN), Google Fonts (*Fraunces* display headlines, *Outfit* secondary headers, *Plus Jakarta Sans* body).
- **Custom Styling & Design System:** Proposal B ("Algodón Nórdico") implemented in `src/css/` with cloud-soft aesthetics, floating keyframe micro-animations, pill geometry (`border-radius: 50px`), and codified in `.agents/rules/ui-ux-design-system.md`.
- **Frontend Scripting:** Vanilla JavaScript in native ES Modules (`src/js/main.js` and `src/js/modules/`) for DOM manipulation, dynamic Navbar Login Modal, stock bounds, out-of-stock guards, profit margin calculations, checkout modals, and reactive filtering.
- **Backend Language:** PHP 8.x (Native standard library, PDO, session management, native `password_hash`, file upload processing).
- **Database:** SQLite 3 (`database/database.sqlite`) with `PRAGMA foreign_keys = ON;` and `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
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
├── docs/ (symlinked to .docs/)
│   ├── README.md               (Documentation Hub & Navigation Map)
│   ├── data-model.md           (Master Architecture Index - English)
│   ├── data-model.es.md        (Índice Maestro - Español)
│   ├── database-schema.md      (Relational DDL, Data Dictionaries - English)
│   ├── database-schema.es.md   (Esquema DDL, Diccionario de Datos - Español)
│   ├── auth-flow.md            (Session Lifecycle, Modal Flow - English)
│   ├── auth-flow.es.md         (Ciclo de Sesión y Flujo Modal - Español)
│   ├── api-design.md           (REST-like Endpoint Contracts & JSON - English)
│   ├── api-design.es.md        (Contratos de Endpoints REST y JSON - Español)
│   ├── database-testing.md     (SQLite Terminal Verification Guide - English)
│   └── database-testing.es.md  (Guía de Verificación en Terminal SQLite - Español)
├── memory-bank/
│   ├── projectbrief.md
│   ├── productContext.md
│   ├── techContext.md
│   ├── activeContext.md
│   └── progress.md
├── database/
│   ├── seed.sql                (Full SQLite DDL schema and initial seed data)
│   └── database.sqlite         (Physical SQLite DB file - created in Phase 1)
├── views/
│   ├── layouts/main.php        (Master HTML layout, fonts, CSS/JS links)
│   ├── components/             (Navbar, modals, sidebar, footer, product_card)
│   └── pages/                  (catalogo, creaciones, detalle, formulario, pedidos, usuarios)
├── src/
│   ├── css/                    (Modular ITCSS styles: 01-settings, 02-base, 03-animations, 04-components)
│   ├── js/                     (Modular ES modules: auth, catalog, creaciones, detail, margin, orders, users)
│   └── Utils/                  (Universal utilities: CurrencyHelper.php, SvgHelper.php)
├── uploads/                    (Local directory storing uploaded product images)
├── api/                        (Lightweight RESTful endpoints in Phase 3/4)
├── setup.php                   (CLI-only database initialization script executing seed.sql)
├── index.php                   (Catalog & inventory showcase)
├── creaciones.php              (Admin Creations and Stock Management)
├── amigurumis.php              (HTTP 301 Permanent Redirect to creaciones.php)
├── formulario.php              (Add / Edit creation with image upload and margin simulator)
├── detalle.php                 (Detailed piece view with public checkout modal)
├── pedidos.php                 (Order and commission tracking dashboard)
├── usuarios.php                (Artisan user team management)
├── .htaccess                   (Root Apache security blocking .sqlite, .sql, .md, database/, memory-bank/)
├── .gitignore                  (Git exclusions for binaries, OS artifacts, and uploads)
└── README.md                   (Execution and setup documentation)
```

## Technical Constraints & Safety
- **Multi-Layered Web & Database Security:** SQLite database relocated to `database/database.sqlite`. Direct HTTP/browser access to `database/`, `memory-bank/`, `.sqlite`, `.sql`, and `.md` files is strictly blocked via Apache `.htaccess`.
- **CLI-Only Database Setup:** `setup.php` is strictly restricted to CLI execution (`php_sapi_name() === 'cli'`), completely preventing remote browser-driven database resets or data loss.
- **Foreign Key Enforcement:** Explicit `PRAGMA foreign_keys = ON;` executed on every PDO connection.
- **Server-Side Price Calculation:** `pedidos.precio_final` is calculated on the server (`precio * cantidad`). Client input is never trusted.
- **Atomic Stock Transactions:** Creating an order requires `BEGIN TRANSACTION`, checking available stock and updating `cantidad_stock`.
- **Restocking on Cancellation:** Updating an order to `'Cancelado'` via `actualizar_pedido.php` restores units to `cantidad_stock`.
- **Role-Based Protection:** Administrative routes are strictly locked to authenticated users (`admin` or `artesano`).
- **Secure Image Uploads & Asset Lifecycle:** Binary files validated by MIME type, size limit ($\le 5\text{MB}$), unique file naming, stored in `/uploads/`. When updating or deleting a creation (`POST /api/eliminar.php`), previous or associated image files are unlinked from `/uploads/` using PHP `unlink()` to eliminate orphaned files.
- **Dynamic Navbar Modal Authentication:** Login is embedded as a reusable modal dialog in the header, streamlining navigation without page reloads.
- **SQL Injection Prevention:** 100% parameterized PDO prepared statements.
