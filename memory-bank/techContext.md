# Technical Context: Amigurumi Micro-ERP & Catalog

## Technology Stack
- **Frontend Presentation:** Semantic HTML5, Bootstrap 5.3 (CDN), Bootstrap Icons (CDN), Google Fonts (*Outfit* display, *Plus Jakarta Sans* body).
- **Custom Styling & Design System:** Proposal B ("Algodón Nórdico") implemented in `css/styles.css` with cloud-soft aesthetics, floating keyframe micro-animations, pill geometry (`border-radius: 50px`), and codified in `.agents/rules/ui-ux-design-system.md`.
- **Frontend Scripting:** Vanilla JavaScript (`js/app.js`) for DOM manipulation, dynamic Navbar Login Modal, stock bounds, out-of-stock guards, profit margin calculations, checkout modals, and AJAX operations.
- **Backend Language:** PHP 8.x (Native standard library, PDO, session management, native `password_hash`, file upload processing).
- **Database:** SQLite 3 (`database.sqlite`) with `PRAGMA foreign_keys = ON;` and `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
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

-- 2. Table: amigurumis (Core Catalog & Inventory)
CREATE TABLE IF NOT EXISTS amigurumis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    artesano_id INTEGER NOT NULL,
    nombre TEXT NOT NULL,
    categoria TEXT NOT NULL,
    material TEXT NOT NULL,
    tamano_cm REAL NOT NULL,
    precio INTEGER NOT NULL,
    costo_materiales INTEGER NOT NULL DEFAULT 0,
    cantidad_stock INTEGER NOT NULL DEFAULT 0,
    horas_tejido REAL DEFAULT 0.0,
    descripcion TEXT,
    imagen_url TEXT,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    actualizado_en TEXT DEFAULT NULL,
    -- Table Constraints
    CONSTRAINT fk_amigurumis_artesano FOREIGN KEY (artesano_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_amigurumis_nombre CHECK(length(trim(nombre)) >= 2 AND length(nombre) <= 100),
    CONSTRAINT chk_amigurumis_categoria CHECK(length(trim(categoria)) >= 2 AND length(categoria) <= 50),
    CONSTRAINT chk_amigurumis_material CHECK(length(trim(material)) >= 3 AND length(material) <= 80),
    CONSTRAINT chk_amigurumis_tamano CHECK(tamano_cm > 0.0 AND tamano_cm <= 250.0),
    CONSTRAINT chk_amigurumis_precio CHECK(precio >= 1 AND precio <= 9999999),
    CONSTRAINT chk_amigurumis_costo_materiales CHECK(costo_materiales >= 0 AND costo_materiales <= 9999999),
    CONSTRAINT chk_amigurumis_cantidad_stock CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000),
    CONSTRAINT chk_amigurumis_horas_tejido CHECK(horas_tejido IS NULL OR (horas_tejido >= 0.0 AND horas_tejido <= 500.0)),
    CONSTRAINT chk_amigurumis_descripcion CHECK(descripcion IS NULL OR length(descripcion) <= 2000),
    CONSTRAINT chk_amigurumis_imagen_url CHECK(imagen_url IS NULL OR length(trim(imagen_url)) <= 500)
);

-- 3. Table: pedidos (Orders & Commissions)
CREATE TABLE IF NOT EXISTS pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_nombre TEXT NOT NULL,
    amigurumi_id INTEGER NOT NULL,
    cantidad INTEGER NOT NULL DEFAULT 1,
    fecha_entrega TEXT,
    estado_pedido TEXT NOT NULL DEFAULT 'Pendiente',
    precio_final INTEGER NOT NULL,
    notas TEXT,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    -- Table Constraints
    CONSTRAINT fk_pedidos_amigurumi FOREIGN KEY (amigurumi_id) REFERENCES amigurumis(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_pedidos_cliente_nombre CHECK(length(trim(cliente_nombre)) >= 2 AND length(cliente_nombre) <= 100),
    CONSTRAINT chk_pedidos_cantidad CHECK(cantidad >= 1 AND cantidad <= 1000),
    CONSTRAINT chk_pedidos_fecha_entrega CHECK(fecha_entrega IS NULL OR length(trim(fecha_entrega)) = 10),
    CONSTRAINT chk_pedidos_estado CHECK(estado_pedido IN ('Pendiente', 'En Proceso', 'Entregado', 'Cancelado')),
    CONSTRAINT chk_pedidos_precio_final CHECK(precio_final >= 1 AND precio_final <= 9999999),
    CONSTRAINT chk_pedidos_notas CHECK(notas IS NULL OR length(notas) <= 1000)
);

-- Performance Indexes
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_amigurumis_artesano ON amigurumis(artesano_id);
CREATE INDEX IF NOT EXISTS idx_amigurumis_categoria ON amigurumis(categoria);
CREATE INDEX IF NOT EXISTS idx_amigurumis_stock ON amigurumis(cantidad_stock);
CREATE INDEX IF NOT EXISTS idx_pedidos_amigurumi ON pedidos(amigurumi_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado_pedido);
```

## Project Directory Layout
```
proyecto-web/
├── docs/ (symlinked to .docs/)
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
│   ├── database.sqlite         (Physical SQLite DB file - created in Phase 1)
│   └── .htaccess               (Internal folder protection denying all direct web access)
├── css/
│   └── styles.css              (Artisan styling, preview frames, badge indicators)
├── js/
│   └── app.js                  (Client-side validation, Navbar Modal, calculations)
├── uploads/                    (Local directory storing uploaded product images)
│   └── .gitkeep                (Git retention marker for uploads directory)
├── api/
│   ├── conexion.php            (PDO SQLite connection to database/database.sqlite with foreign keys ON)
│   ├── auth_guard.php          (Session and role authorization helper)
│   ├── login.php               (Credential verification & session_start)
│   ├── logout.php              (Session termination)
│   ├── usuarios.php            (User Management CRUD - Admin only)
│   ├── crear.php               (Insert amigurumi, file upload to /uploads, binds session artesano_id)
│   ├── leer.php                (Fetch catalog items with joined artisan username)
│   ├── actualizar.php          (Update amigurumi & replace local image)
│   ├── eliminar.php            (Delete amigurumi with foreign key safeguard & physical image unlink)
│   ├── solicitar_pedido.php    (Public checkout with atomic stock deduction)
│   ├── pedidos.php             (Protected orders dashboard & query)
│   └── actualizar_pedido.php   (Update order status & restocking on cancellation)
├── setup.php                   (CLI-only database initialization script executing seed.sql)
├── index.html                  (Catalog & inventory view + Navbar Login Modal)
├── formulario.html             (Add / Edit view with real file upload)
├── detalle.html                (Detailed item view with Public Checkout trigger)
├── pedidos.html                (Orders & commission tracking view)
├── .htaccess                   (Root Apache security blocking .sqlite, .sql, .md, database/, memory-bank/)
├── .gitignore                  (Git exclusions for binaries, OS artifacts, and uploads)
└── README.md                   (Execution and setup documentation)
```

## Technical Constraints & Safety
- **Multi-Layered Web & Database Security:** SQLite database relocated to `database/database.sqlite`. Direct HTTP/browser access to `database/`, `memory-bank/`, `.sqlite`, `.sql`, and `.md` files is strictly blocked via Apache `.htaccess`.
- **CLI-Only Database Setup:** `setup.php` is strictly restricted to CLI execution (`php_sapi_name() === 'cli'`), completely preventing remote browser-driven database resets or data loss.
- **Foreign Key Enforcement:** Explicit `PRAGMA foreign_keys = ON;` executed on every PDO connection.
- **Server-Side Price Calculation:** `pedidos.precio_final` is calculated on the server (`precio * cantidad`). Client input is never trusted.
- **Atomic Stock Transactions:** Creating an order (`solicitar_pedido.php` or `pedidos.php`) requires `BEGIN TRANSACTION`, checking available stock and updating `cantidad_stock`.
- **Restocking on Cancellation:** Updating an order to `'Cancelado'` via `actualizar_pedido.php` restores units to `cantidad_stock`.
- **Role-Based Protection:** `/api/usuarios.php` is strictly locked to `admin`. Non-admin requests receive HTTP 403.
- **Secure Image Uploads & Asset Lifecycle:** Binary files validated by MIME type, size limit ($\le 5\text{MB}$), unique file naming, stored in `/uploads/`. When updating or deleting an amigurumi (`POST /api/eliminar.php`), previous or associated image files are unlinked from `/uploads/` using PHP `unlink()` to eliminate orphaned files.
- **Dynamic Navbar Modal Authentication:** Login is embedded as a reusable modal dialog in the header, streamlining navigation without page reloads.
- **SQL Injection Prevention:** 100% parameterized PDO prepared statements.
