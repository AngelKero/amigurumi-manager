# Technical Context: Amigurumi Micro-ERP & Catalog

## Technology Stack
- **Frontend Presentation:** Semantic HTML5, Bootstrap 5.3 (CDN), Bootstrap Icons (CDN).
- **Custom Styling:** Minimal custom CSS (`css/styles.css`) for warm craft aesthetic accents and status badge styling.
- **Frontend Scripting:** Vanilla JavaScript (`js/app.js`) for DOM manipulation, profit margin preview math, pre-flight validation, and modal dialogues.
- **Backend Language:** PHP 8.x (Native standard library, PDO, session management, native `password_hash`).
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

## Modular Documentation Structure (`docs/` / `.docs/`)
```
proyecto-web/
├── docs/ (symlinked to .docs/)
│   ├── data-model.md           (Master Architecture & ERD Index - English)
│   ├── data-model.es.md        (Índice Maestro y ERD - Español)
│   ├── database-schema.md      (Relational DDL, Data Dictionaries - English)
│   ├── database-schema.es.md   (Esquema DDL, Diccionario de Datos - Español)
│   ├── auth-flow.md            (Session Lifecycle, Identity Attribution - English)
│   ├── auth-flow.es.md         (Ciclo de Sesión y Atribución de Autoría - Español)
│   ├── api-design.md           (REST-like Endpoint Contracts & JSON - English)
│   └── api-design.es.md        (Contratos de Endpoints REST y JSON - Español)
├── memory-bank/
│   ├── projectbrief.md
│   ├── productContext.md
│   ├── techContext.md
│   ├── activeContext.md
│   └── progress.md
├── css/
│   └── styles.css              (Artisan styling & badge indicators)
├── js/
│   └── app.js                  (Client-side validation, calculations, event handlers)
├── api/
│   ├── conexion.php            (PDO SQLite connection with PRAGMA foreign_keys = ON)
│   ├── auth_guard.php          (Session verification helper)
│   ├── login.php               (Credential verification & session_start)
│   ├── logout.php              (Session termination)
│   ├── setup.php               (Schema creation & initial admin seeder)
│   ├── crear.php               (Insert amigurumi binding session user_id to artesano_id)
│   ├── leer.php                (Fetch catalog items with joined artisan username)
│   ├── actualizar.php          (Update amigurumi)
│   ├── eliminar.php            (Delete amigurumi with foreign key safeguard)
│   ├── pedidos.php             (Orders query & creation with atomic stock deduction)
│   └── actualizar_pedido.php   (Update order status & restocking on cancellation)
├── index.html                  (Catalog & list view)
├── formulario.html             (Add / Edit view)
├── detalle.html                (Detailed item view)
├── pedidos.html                (Orders & commission tracking view)
├── login.html                  (Admin login interface)
├── database.sqlite             (SQLite DB file - created in Phase 2)
└── README.md                   (Execution and setup documentation)
```

## Technical Constraints & Safety
- **Foreign Key Enforcement:** Explicit `PRAGMA foreign_keys = ON;` executed on every PDO connection.
- **Artisan Identity Binding:** `artesano_id` is automatically injected from `$_SESSION['user_id']` on creation. Client-side input is ignored.
- **Server Price Calculation:** `pedidos.precio_final` is calculated on the server (`precio * cantidad`) and locked into the database. Client input is not trusted.
- **Atomic Stock Deduction:** Creating an order requires an atomic transaction (`BEGIN TRANSACTION`). The backend validates that `cantidad <= amigurumis.cantidad_stock` and decrements physical stock (`UPDATE amigurumis SET cantidad_stock = cantidad_stock - :cantidad`).
- **Restocking on Cancellation:** Updating an order to `'Cancelado'` via `actualizar_pedido.php` restores reserved units to `amigurumis.cantidad_stock`.
- **Financial Exactness:** Cents storage (`INTEGER`) across `precio`, `costo_materiales`, and `precio_final`.
- **Order Quantity Tracking:** `cantidad INTEGER NOT NULL DEFAULT 1` allows multiple units per order.
- **Session Protection:** All mutating endpoints require valid PHP session with `session_regenerate_id(true)` and `HttpOnly` cookie flags.
- **SQL Injection Prevention:** 100% parameterized PDO prepared statements.
- **Input Sanitization:** Multi-tier validation via HTML5, Vanilla JS, and server-side PHP filters.
