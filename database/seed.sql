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
    -- -------------------------------------------------------------------------
    -- Definición de Columnas
    -- -------------------------------------------------------------------------
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'admin',
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),

    -- =========================================================================
    -- RESTRICCIONES DE LA TABLA (CONSTRAINTS) Y DOCUMENTACIÓN
    -- =========================================================================

    -- 1. Unicidad de Nombre de Usuario
    -- QUÉ HACE: Impide que existan dos cuentas con el mismo nombre de usuario.
    -- REGLA DE NEGOCIO: Garantiza que la autenticación y la autoría de creaciones sean únicas.
    CONSTRAINT uq_usuarios_username 
        UNIQUE (username),

    -- 2. Longitud y Limpieza de Nombre de Usuario
    -- QUÉ HACE: Exige que el nombre tenga entre 3 y 50 caracteres (sin contar espacios en blanco en los extremos).
    -- REGLA DE NEGOCIO: Evita nombres vacíos, cadenas de solo espacios o alias excesivamente cortos/largos.
    CONSTRAINT chk_usuarios_username 
        CHECK(length(trim(username)) >= 3 AND length(username) <= 50),

    -- 3. Lista Blanca de Roles de Acceso
    -- QUÉ HACE: Limita el valor del campo rol exclusivamente a ('admin', 'artesano', 'asistente').
    -- REGLA DE NEGOCIO: Seguridad basada en roles (RBAC); solo perfiles autorizados pueden operar el Micro-ERP.
    CONSTRAINT chk_usuarios_rol 
        CHECK(rol IN ('admin', 'artesano', 'asistente'))
);

-- Table: amigurumis (Core Catalog & Physical Inventory)
CREATE TABLE IF NOT EXISTS amigurumis (
    -- -------------------------------------------------------------------------
    -- Definición de Columnas
    -- -------------------------------------------------------------------------
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

    -- =========================================================================
    -- RESTRICCIONES DE LA TABLA (CONSTRAINTS) Y DOCUMENTACIÓN
    -- =========================================================================

    -- 1. Integridad Referencial: Autoría del Artesano
    -- QUÉ HACE: Relaciona artesano_id con usuarios(id). Bloquea el borrado del usuario si tiene piezas (ON DELETE RESTRICT)
    --           y propaga modificaciones de ID (ON UPDATE CASCADE).
    -- REGLA DE NEGOCIO: Toda pieza debe tener un artesano responsable; no se pueden eliminar artesanos con catálogo activo.
    CONSTRAINT fk_amigurumis_artesano 
        FOREIGN KEY (artesano_id) REFERENCES usuarios(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- 2. Longitud y Sanidad del Nombre
    -- QUÉ HACE: Obliga a que el nombre del amigurumi tenga entre 2 y 100 caracteres sin ser solo espacios.
    -- REGLA DE NEGOCIO: Títulos descriptivos válidos para la tienda y tarjetas de catálogo.
    CONSTRAINT chk_amigurumis_nombre 
        CHECK(length(trim(nombre)) >= 2 AND length(nombre) <= 100),

    -- 3. Longitud de Categoría
    -- QUÉ HACE: Asegura entre 2 y 50 caracteres para clasificar la creación.
    -- REGLA DE NEGOCIO: Clasificación taxonómica consistente (Fantasía, Plantas / Botánica, Animales / Fauna, etc.).
    CONSTRAINT chk_amigurumis_categoria 
        CHECK(length(trim(categoria)) >= 2 AND length(categoria) <= 50),

    -- 4. Detalle de Materiales de Tejido
    -- QUÉ HACE: Exige entre 3 y 80 caracteres en la especificación técnica de hilazas/fibras.
    -- REGLA DE NEGOCIO: Transparencia al cliente sobre calidad y composición textil (ej. 100% Algodón Mercerizado).
    CONSTRAINT chk_amigurumis_material 
        CHECK(length(trim(material)) >= 3 AND length(material) <= 80),

    -- 5. Rango de Tamaño Físico (cm)
    -- QUÉ HACE: Valida que la altura/largo sea mayor a 0 cm y menor o igual a 250 cm (2.5 metros).
    -- REGLA DE NEGOCIO: Evita dimensiones negativas, cero o desproporcionadas para amigurumis artesanales.
    CONSTRAINT chk_amigurumis_tamano 
        CHECK(tamano_cm > 0.0 AND tamano_cm <= 250.0),

    -- 6. Precio de Venta al Público (Almacenado en Centavos)
    -- QUÉ HACE: Obliga a que el precio sea entero entre 1 centavo ($0.01 MXN) y 9,999,999 centavos ($99,999.99 MXN).
    -- REGLA DE NEGOCIO: Integridad financiera sin errores de redondeo de punto flotante; prohíbe productos gratuitos o negativos.
    CONSTRAINT chk_amigurumis_precio 
        CHECK(precio >= 1 AND precio <= 9999999),

    -- 7. Costo de Inversión en Materiales (Almacenado en Centavos)
    -- QUÉ HACE: Obliga a que el costo sea entero >= 0 y <= 9,999,999 centavos.
    -- REGLA DE NEGOCIO: Base contable para calcular el margen de utilidad neta y retorno de inversión del taller.
    CONSTRAINT chk_amigurumis_costo_materiales 
        CHECK(costo_materiales >= 0 AND costo_materiales <= 9999999),

    -- 8. Control de Stock Físico Disponible
    -- QUÉ HACE: Asegura que las existencias no sean negativas y no superen 10,000 unidades.
    -- REGLA DE NEGOCIO: Evita vender inventario negativo; el badge "Agotado" se deriva en UI cuando stock = 0.
    CONSTRAINT chk_amigurumis_cantidad_stock 
        CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000),

    -- 9. Horas de Labor Manual Invertidas
    -- QUÉ HACE: Permite NULL o un valor numérico entre 0.0 y 500.0 horas de tejido.
    -- REGLA DE NEGOCIO: Registro del tiempo de trabajo para calcular la tasa de ganancia por hora ($/hr).
    CONSTRAINT chk_amigurumis_horas_tejido 
        CHECK(horas_tejido IS NULL OR (horas_tejido >= 0.0 AND horas_tejido <= 500.0)),

    -- 10. Longitud Máxima de Descripción
    -- QUÉ HACE: Permite NULL o texto de hasta 2000 caracteres.
    -- REGLA DE NEGOCIO: Espacio suficiente para historia, instrucciones de cuidado y detalles sin desbordar la memoria.
    CONSTRAINT chk_amigurumis_descripcion 
        CHECK(descripcion IS NULL OR length(descripcion) <= 2000),

    -- 11. Longitud y Sanidad de Ruta de Imagen
    -- QUÉ HACE: Permite NULL o texto de hasta 500 caracteres sin espacios vacíos exclusivos.
    -- REGLA DE NEGOCIO: Almacena la ruta relativa del archivo en el servidor local (/uploads/...).
    CONSTRAINT chk_amigurumis_imagen_url 
        CHECK(imagen_url IS NULL OR length(trim(imagen_url)) <= 500)
);

-- Table: pedidos (Orders & Commissions)
CREATE TABLE IF NOT EXISTS pedidos (
    -- -------------------------------------------------------------------------
    -- Definición de Columnas
    -- -------------------------------------------------------------------------
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_nombre TEXT NOT NULL,
    amigurumi_id INTEGER NOT NULL,
    cantidad INTEGER NOT NULL DEFAULT 1,
    fecha_entrega TEXT,
    estado_pedido TEXT NOT NULL DEFAULT 'Pendiente',
    precio_final INTEGER NOT NULL,
    notas TEXT,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),

    -- =========================================================================
    -- RESTRICCIONES DE LA TABLA (CONSTRAINTS) Y DOCUMENTACIÓN
    -- =========================================================================

    -- 1. Integridad Referencial: Pieza Encargada
    -- QUÉ HACE: Vincula amigurumi_id con amigurumis(id). Bloquea el borrado de una pieza si tiene pedidos (ON DELETE RESTRICT)
    --           y propaga actualizaciones de ID (ON UPDATE CASCADE).
    -- REGLA DE NEGOCIO: Protección contable e histórica; no se puede borrar una creación del catálogo si fue pedida por clientes.
    CONSTRAINT fk_pedidos_amigurumi 
        FOREIGN KEY (amigurumi_id) REFERENCES amigurumis(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- 2. Nombre del Cliente Comprador
    -- QUÉ HACE: Exige entre 2 y 100 caracteres sin espacios vacíos exclusivos.
    -- REGLA DE NEGOCIO: Identificación obligatoria del cliente para contacto y entrega del pedido.
    CONSTRAINT chk_pedidos_cliente_nombre 
        CHECK(length(trim(cliente_nombre)) >= 2 AND length(cliente_nombre) <= 100),

    -- 3. Volumen del Pedido (Cantidad de Unidades)
    -- QUÉ HACE: Restringe la cantidad a un entero entre 1 y 1,000 unidades.
    -- REGLA DE NEGOCIO: Prohíbe pedidos con 0 o unidades negativas; controla capacidades de producción del taller.
    CONSTRAINT chk_pedidos_cantidad 
        CHECK(cantidad >= 1 AND cantidad <= 1000),

    -- 4. Formato de Fecha de Entrega Comprometida
    -- QUÉ HACE: Permite NULL o valida que la cadena tenga exactamente 10 caracteres (formato ISO YYYY-MM-DD).
    -- REGLA DE NEGOCIO: Cronograma de entrega claro para planificación del artesano.
    CONSTRAINT chk_pedidos_fecha_entrega 
        CHECK(fecha_entrega IS NULL OR length(trim(fecha_entrega)) = 10),

    -- 5. Máquina de Estados del Flujo de Producción
    -- QUÉ HACE: Restringe el estado del pedido a: 'Pendiente', 'En Proceso', 'Entregado', 'Cancelado'.
    -- REGLA DE NEGOCIO: Flujo de vida estricto del encargo. Al cambiar a 'Cancelado', el backend reintegra el stock.
    CONSTRAINT chk_pedidos_estado 
        CHECK(estado_pedido IN ('Pendiente', 'En Proceso', 'Entregado', 'Cancelado')),

    -- 6. Inmutabilidad del Precio Facturado (Almacenado en Centavos)
    -- QUÉ HACE: Exige que el total cobrado sea entero entre 1 y 9,999,999 centavos.
    -- REGLA DE NEGOCIO: Bloquea el precio acordado en el momento de la compra; cambios futuros en el catálogo no alteran ventas pasadas.
    CONSTRAINT chk_pedidos_precio_final 
        CHECK(precio_final >= 1 AND precio_final <= 9999999),

    -- 7. Longitud de Notas y Personalizaciones
    -- QUÉ HACE: Permite NULL o texto de hasta 1000 caracteres.
    -- REGLA DE NEGOCIO: Espacio para variantes personalizadas solicitadas por el cliente (colores, dedicatorias, empaque).
    CONSTRAINT chk_pedidos_notas 
        CHECK(notas IS NULL OR length(notas) <= 1000)
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
