-- ==============================================================================
-- Handmade Amigurumi Micro-ERP & Catalog System
-- Database Seed Script: DDL Schema & Initial Mock Data
-- Engine: SQLite 3
-- ==============================================================================

PRAGMA foreign_keys = ON;

-- ------------------------------------------------------------------------------
-- 1. CLEANUP PREVIOUS TABLES (Reverse order of dependencies)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS amigurumis;
DROP TABLE IF EXISTS usuarios;

-- ------------------------------------------------------------------------------
-- 2. DDL SCHEMA CREATION
-- ------------------------------------------------------------------------------

-- Table: usuarios (Authentication & Access Control)
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL CHECK(length(trim(username)) >= 3 AND length(username) <= 50),
    password_hash TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'admin' CHECK(rol IN ('admin', 'artesano', 'asistente')),
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
);

-- Table: amigurumis (Core Catalog & Physical Inventory)
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

-- Table: pedidos (Orders & Commissions)
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

-- ------------------------------------------------------------------------------
-- 3. QUERY PERFORMANCE INDEXES
-- ------------------------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_amigurumis_artesano ON amigurumis(artesano_id);
CREATE INDEX IF NOT EXISTS idx_amigurumis_categoria ON amigurumis(categoria);
CREATE INDEX IF NOT EXISTS idx_amigurumis_stock ON amigurumis(cantidad_stock);
CREATE INDEX IF NOT EXISTS idx_pedidos_amigurumi ON pedidos(amigurumi_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado_pedido);

-- ------------------------------------------------------------------------------
-- 4. INITIAL SEED MOCK DATA
-- ------------------------------------------------------------------------------

-- 4.1 Admin User
-- Password: 'admin123' (verified bcrypt hash via password_hash)
INSERT INTO usuarios (id, username, password_hash, rol, creado_en)
VALUES (
    1,
    'admin',
    '$2y$10$TiTdw7i0Dqey7iQKr1v6Ne/5GYbrWtUP/rMV8RsmT9BWR4k4ncb/S',
    'admin',
    datetime('now', 'localtime')
);

-- 4.2 Three Distinct Amigurumis
-- Item 1: Fantasía category
INSERT INTO amigurumis (
    id, artesano_id, nombre, categoria, material, tamano_cm, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, creado_en, actualizado_en
) VALUES (
    1,
    1,
    'Dragón Ignis',
    'Fantasía',
    '100% Algodón Mercerizado',
    18.5,
    45000,
    12000,
    4,
    6.5,
    'Amigurumi de dragón fantástico tejido a crochet con escamas en relieve, alas articuladas y relleno sintético hipoalergénico de alta densidad.',
    'uploads/dragon_ignis.jpg',
    datetime('now', '-5 days', 'localtime'),
    NULL
);

-- Item 2: Plantas / Botánica category
INSERT INTO amigurumis (
    id, artesano_id, nombre, categoria, material, tamano_cm, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, creado_en, actualizado_en
) VALUES (
    2,
    1,
    'Mini Suculenta en Maceta',
    'Plantas / Botánica',
    'Algodón Rústico y Lana Acrílica',
    10.0,
    18000,
    4500,
    12,
    2.0,
    'Pequeña maceta tejida con suculenta en relieve botánico. No requiere riego, ideal para escritorios, repisas y espacios de trabajo.',
    'uploads/suculenta.jpg',
    datetime('now', '-3 days', 'localtime'),
    NULL
);

-- Item 3: Animales / Fauna category
INSERT INTO amigurumis (
    id, artesano_id, nombre, categoria, material, tamano_cm, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, creado_en, actualizado_en
) VALUES (
    3,
    1,
    'Ajolote Rosado Pastel',
    'Animales / Fauna',
    'Hilo Chenille Terciopelo',
    14.0,
    32000,
    8500,
    2,
    4.5,
    'Tierno ajolote mexicano con textura aterciopelada ultra suave, branquias externas en color frambuesa y ojos de seguridad kawaii.',
    'uploads/ajolote.jpg',
    datetime('now', '-1 days', 'localtime'),
    NULL
);

-- 4.3 Two Commission Orders (Linked via Foreign Key)
-- Order 1: For Dragón Ignis (cantidad = 1, precio_final = 1 * 45000 = 45000)
INSERT INTO pedidos (
    id, cliente_nombre, amigurumi_id, cantidad, fecha_entrega,
    estado_pedido, precio_final, notas, creado_en
) VALUES (
    1,
    'Mariana Gómez',
    1,
    1,
    strftime('%Y-%m-%d', date('now', '+14 days')),
    'En Proceso',
    45000,
    'Empaque para regalo con listón verde bosque y dedicatoria para graduación.',
    datetime('now', '-2 days', 'localtime')
);

-- Order 2: For Ajolote Rosado Pastel (cantidad = 2, precio_final = 2 * 32000 = 64000)
INSERT INTO pedidos (
    id, cliente_nombre, amigurumi_id, cantidad, fecha_entrega,
    estado_pedido, precio_final, notas, creado_en
) VALUES (
    2,
    'Carlos Mendoza',
    3,
    2,
    strftime('%Y-%m-%d', date('now', '+20 days')),
    'Pendiente',
    64000,
    'Incluir tarjeta de felicitación personalizada de cumpleaños para mellizos.',
    datetime('now', '-4 hours', 'localtime')
);
