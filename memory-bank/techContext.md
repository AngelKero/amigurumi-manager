# Technical Context: Amigurumi Micro-ERP & Catalog

## Technology Stack
- **Frontend Presentation:** Semantic HTML5, Bootstrap 5.3 (CDN), Bootstrap Icons (CDN).
- **Custom Styling:** Minimal custom CSS (`css/styles.css`) for warm craft aesthetic accents, image previews, and status badge styling.
- **Frontend Scripting:** Vanilla JavaScript (`js/app.js`) for DOM manipulation, dynamic Navbar Login Modal, profit margin calculations, checkout modals, and AJAX operations.
- **Backend Language:** PHP 8.x (Native standard library, PDO, session management, native `password_hash`, file upload processing).
- **Database:** SQLite 3 (`database.sqlite`) with `PRAGMA foreign_keys = ON;` and `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
- **Local Server:** PHP Built-in development server (`php -S localhost:8000`).

## Relational DDL Specification (3 Tables)
```sql
PRAGMA foreign_keys = ON;

-- 1. Table: usuarios (Authentication & Access Control)
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL CHECK(length(trim(username)) >= 3 AND length(username) <= 50),
    password_hash TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'admin' CHECK(rol IN ('admin', 'artesano', 'asistente')),
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
);

-- 2. Table: amigurumis (Core Catalog & Inventory)
CREATE TABLE IF NOT EXISTS amigurumis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    artesano_id INTEGER NOT NULL,
    nombre TEXT NOT NULL CHECK(length(trim(nombre)) >= 2 AND length(nombre) <= 100),
    categoria TEXT NOT NULL CHECK(length(trim(categoria)) >= 2 AND length(categoria) <= 50),
    material TEXT NOT NULL CHECK(length(trim(material)) >= 3 AND length(material) <= 80),
    tamano_cm REAL NOT NULL CHECK(tamano_cm > 0.0 AND tamano_cm <= 250.0),
    precio INTEGER NOT NULL CHECK(precio >= 1 AND precio <= 9999999),
    costo_materiales INTEGER NOT NULL DEFAULT 0 CHECK(costo_materiales >= 0 AND costo_materiales <= 9999999),
    cantidad_stock INTEGER NOT NULL DEFAULT 0 CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000),
    horas_tejido REAL DEFAULT 0.0 CHECK(horas_tejido IS NULL OR (horas_tejido >= 0.0 AND horas_tejido <= 500.0)),
    descripcion TEXT CHECK(descripcion IS NULL OR length(descripcion) <= 2000),
    imagen_url TEXT CHECK(imagen_url IS NULL OR length(trim(imagen_url)) <= 500),
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    actualizado_en TEXT DEFAULT NULL,
    FOREIGN KEY (artesano_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 3. Table: pedidos (Orders & Commissions)
CREATE TABLE IF NOT EXISTS pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_nombre TEXT NOT NULL CHECK(length(trim(cliente_nombre)) >= 2 AND length(cliente_nombre) <= 100),
    amigurumi_id INTEGER NOT NULL,
    cantidad INTEGER NOT NULL DEFAULT 1 CHECK(cantidad >= 1 AND cantidad <= 1000),
    fecha_entrega TEXT CHECK(fecha_entrega IS NULL OR length(trim(fecha_entrega)) = 10),
    estado_pedido TEXT NOT NULL DEFAULT 'Pendiente' CHECK(estado_pedido IN (
        'Pendiente', 
        'En Proceso', 
        'Entregado', 
        'Cancelado'
    )),
    precio_final INTEGER NOT NULL CHECK(precio_final >= 1 AND precio_final <= 9999999),
    notas TEXT CHECK(notas IS NULL OR length(notas) <= 1000),
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    FOREIGN KEY (amigurumi_id) REFERENCES amigurumis(id) ON DELETE RESTRICT ON UPDATE CASCADE
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
│   └── seed.sql                (Full SQLite DDL schema and initial seed data)
├── css/
│   └── styles.css              (Artisan styling, preview frames, badge indicators)
├── js/
│   └── app.js                  (Client-side validation, Navbar Modal, calculations)
├── uploads/                    (Local directory storing uploaded product images)
├── api/
│   ├── conexion.php            (PDO SQLite connection with PRAGMA foreign_keys = ON)
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
├── setup.php                   (Database initialization script executing seed.sql)
├── index.html                  (Catalog & inventory view + Navbar Login Modal)
├── formulario.html             (Add / Edit view with real file upload)
├── detalle.html                (Detailed item view with Public Checkout trigger)
├── pedidos.html                (Orders & commission tracking view)
├── database.sqlite             (SQLite DB file - created in Phase 1)
└── README.md                   (Execution and setup documentation)
```

## Technical Constraints & Safety
- **Foreign Key Enforcement:** Explicit `PRAGMA foreign_keys = ON;` executed on every PDO connection.
- **Server-Side Price Calculation:** `pedidos.precio_final` is calculated on the server (`precio * cantidad`). Client input is never trusted.
- **Atomic Stock Transactions:** Creating an order (`solicitar_pedido.php` or `pedidos.php`) requires `BEGIN TRANSACTION`, checking available stock and updating `cantidad_stock`.
- **Restocking on Cancellation:** Updating an order to `'Cancelado'` via `actualizar_pedido.php` restores units to `cantidad_stock`.
- **Role-Based Protection:** `/api/usuarios.php` is strictly locked to `admin`. Non-admin requests receive HTTP 403.
- **Secure Image Uploads & Asset Lifecycle:** Binary files validated by MIME type, size limit ($\le 5\text{MB}$), unique file naming, stored in `/uploads/`. When updating or deleting an amigurumi (`POST /api/eliminar.php`), previous or associated image files are unlinked from `/uploads/` using PHP `unlink()` to eliminate orphaned files.
- **Dynamic Navbar Modal Authentication:** Login is embedded as a reusable modal dialog in the header, streamlining navigation without page reloads.
- **SQL Injection Prevention:** 100% parameterized PDO prepared statements.
