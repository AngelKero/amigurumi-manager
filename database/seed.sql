-- ==============================================================================
-- Handmade Crochet Creations Micro-ERP & Catalog System
-- Database Seed Script: DDL Schema & Initial Mock Data
-- Engine: SQLite 3
-- ==============================================================================

PRAGMA foreign_keys = ON;

-- ------------------------------------------------------------------------------
-- 1. CLEANUP PREVIOUS TABLES (Reverse order of dependencies)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS creaciones;
DROP TABLE IF EXISTS amigurumis; -- Legacy cleanup
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

-- Table: creaciones (Core Catalog & Physical Inventory in Crochet)
CREATE TABLE IF NOT EXISTS creaciones (
    -- -------------------------------------------------------------------------
    -- Definición de Columnas
    -- -------------------------------------------------------------------------
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

    -- =========================================================================
    -- RESTRICCIONES DE LA TABLA (CONSTRAINTS) Y DOCUMENTACIÓN
    -- =========================================================================

    -- 1. Integridad Referencial: Autoría del Artesano
    -- QUÉ HACE: Relaciona artesano_id con usuarios(id). Bloquea el borrado del usuario si tiene piezas (ON DELETE RESTRICT)
    --           y propaga modificaciones de ID (ON UPDATE CASCADE).
    -- REGLA DE NEGOCIO: Toda pieza debe tener un artesano responsable; no se pueden eliminar artesanos con catálogo activo.
    CONSTRAINT fk_creaciones_artesano 
        FOREIGN KEY (artesano_id) REFERENCES usuarios(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- 2. Longitud y Sanidad del Nombre
    -- QUÉ HACE: Obliga a que el nombre de la creación tenga entre 2 y 100 caracteres sin ser solo espacios.
    -- REGLA DE NEGOCIO: Títulos descriptivos válidos para la tienda y tarjetas de catálogo.
    CONSTRAINT chk_creaciones_nombre 
        CHECK(length(trim(nombre)) >= 2 AND length(nombre) <= 100),

    -- 3. Longitud de Categoría
    -- QUÉ HACE: Asegura entre 2 y 50 caracteres para clasificar la creación.
    -- REGLA DE NEGOCIO: Clasificación taxonómica consistente (Amigurumis & Figuras, Prendas & Ropa, Bolsos & Accesorios, etc.).
    CONSTRAINT chk_creaciones_categoria 
        CHECK(length(trim(categoria)) >= 2 AND length(categoria) <= 50),

    -- 4. Detalle de Materiales de Tejido
    -- QUÉ HACE: Exige entre 3 y 80 caracteres en la especificación técnica de hilazas/fibras.
    -- REGLA DE NEGOCIO: Transparencia al cliente sobre calidad y composición textil (ej. 100% Algodón Mercerizado).
    CONSTRAINT chk_creaciones_material 
        CHECK(length(trim(material)) >= 3 AND length(material) <= 80),

    -- 5. Rango de Dimensiones Físicas y Talla
    -- QUÉ HACE: Valida que la cadena de dimensiones tenga entre 2 y 100 caracteres sin espacios vacíos exclusivos.
    -- REGLA DE NEGOCIO: Admite dimensiones 2D/3D (ej. '140 x 100 cm', '35 x 30 cm'), medidas de amigurumi (ej. '18.5 cm alto') o tallas de prendas (ej. 'Talla M (95 x 58 cm)').
    CONSTRAINT chk_creaciones_dimensiones 
        CHECK(length(trim(dimensiones)) >= 2 AND length(dimensiones) <= 100),

    -- 6. Precio de Venta al Público (Almacenado en Centavos)
    -- QUÉ HACE: Obliga a que el precio sea entero entre 1 centavo ($0.01 MXN) y 9,999,999 centavos ($99,999.99 MXN).
    -- REGLA DE NEGOCIO: Integridad financiera sin errores de redondeo de punto flotante; prohíbe productos gratuitos o negativos.
    CONSTRAINT chk_creaciones_precio 
        CHECK(precio >= 1 AND precio <= 9999999),

    -- 7. Costo de Inversión en Materiales (Almacenado en Centavos)
    -- QUÉ HACE: Obliga a que el costo sea entero >= 0 y <= 9,999,999 centavos.
    -- REGLA DE NEGOCIO: Base contable para calcular el margen de utilidad neta y retorno de inversión del taller.
    CONSTRAINT chk_creaciones_costo_materiales 
        CHECK(costo_materiales >= 0 AND costo_materiales <= 9999999),

    -- 8. Control de Stock Físico Disponible
    -- QUÉ HACE: Asegura que las existencias no sean negativas y no superen 10,000 unidades.
    -- REGLA DE NEGOCIO: Evita vender inventario negativo; el badge "Agotado" se deriva en UI cuando stock = 0.
    CONSTRAINT chk_creaciones_cantidad_stock 
        CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000),

    -- 9. Horas de Labor Manual Invertidas
    -- QUÉ HACE: Permite NULL o un valor numérico entre 0.0 y 500.0 horas de tejido.
    -- REGLA DE NEGOCIO: Registro del tiempo de trabajo para calcular la tasa de ganancia por hora ($/hr).
    CONSTRAINT chk_creaciones_horas_tejido 
        CHECK(horas_tejido IS NULL OR (horas_tejido >= 0.0 AND horas_tejido <= 500.0)),

    -- 10. Longitud Máxima de Descripción
    -- QUÉ HACE: Permite NULL o texto de hasta 2000 caracteres.
    -- REGLA DE NEGOCIO: Espacio suficiente para historia, instrucciones de cuidado y detalles sin desbordar la memoria.
    CONSTRAINT chk_creaciones_descripcion 
        CHECK(descripcion IS NULL OR length(descripcion) <= 2000),

    -- 11. Longitud y Sanidad de Ruta de Imagen
    -- QUÉ HACE: Permite NULL o texto de hasta 500 caracteres sin espacios vacíos exclusivos.
    -- REGLA DE NEGOCIO: Almacena la ruta relativa del archivo en el servidor local (/uploads/...).
    CONSTRAINT chk_creaciones_imagen_url 
        CHECK(imagen_url IS NULL OR length(trim(imagen_url)) <= 500),

    -- 12. Distintivo de Confección Sobre Encargo
    -- QUÉ HACE: Flag binario (0 o 1) que indica si la pieza se elabora exclusivamente bajo encargo personalizado.
    -- REGLA DE NEGOCIO: Permite piezas sin stock inmediato (stock = 0) que no están "Agotadas" sino que se tejen a pedido.
    CONSTRAINT chk_creaciones_es_sobre_encargo 
        CHECK(es_sobre_encargo IN (0, 1))
);

-- Table: pedidos (Orders & Commissions)
CREATE TABLE IF NOT EXISTS pedidos (
    -- -------------------------------------------------------------------------
    -- Definición de Columnas
    -- -------------------------------------------------------------------------
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

    -- =========================================================================
    -- RESTRICCIONES DE LA TABLA (CONSTRAINTS) Y DOCUMENTACIÓN
    -- =========================================================================

    -- 1. Integridad Referencial: Pieza Encargada
    -- QUÉ HACE: Vincula creacion_id con creaciones(id). Bloquea el borrado de una pieza si tiene pedidos (ON DELETE RESTRICT)
    --           y propaga actualizaciones de ID (ON UPDATE CASCADE).
    -- REGLA DE NEGOCIO: Protección contable e histórica; no se puede borrar una creación del catálogo si fue pedida por clientes.
    CONSTRAINT fk_pedidos_creacion 
        FOREIGN KEY (creacion_id) REFERENCES creaciones(id) 
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
        CHECK(notas IS NULL OR length(notas) <= 1000),

    -- 8. Medio de Contacto del Cliente (WhatsApp, Teléfono o Correo)
    -- QUÉ HACE: Permite texto de hasta 50 caracteres para registrar teléfono/WhatsApp o email.
    -- REGLA DE NEGOCIO: Habilita el canal de comunicación directa del artesano para confirmación y entrega.
    CONSTRAINT chk_pedidos_cliente_contacto 
        CHECK(length(trim(cliente_contacto)) <= 50),

    -- 9. Estado Financiero / Cobro del Encargo
    -- QUÉ HACE: Restringe el estado de cobro a: 'Pendiente', 'Anticipo 50%', 'Liquidado'.
    -- REGLA DE NEGOCIO: Control de anticipos necesarios para compra de materia prima e hilazas antes de confeccionar.
    CONSTRAINT chk_pedidos_estado_pago 
        CHECK(estado_pago IN ('Pendiente', 'Anticipo 50%', 'Liquidado'))
);

-- ------------------------------------------------------------------------------
-- 3. QUERY PERFORMANCE INDEXES
-- ------------------------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_creaciones_artesano ON creaciones(artesano_id);
CREATE INDEX IF NOT EXISTS idx_creaciones_categoria ON creaciones(categoria);
CREATE INDEX IF NOT EXISTS idx_creaciones_stock ON creaciones(cantidad_stock);
CREATE INDEX IF NOT EXISTS idx_pedidos_creacion ON pedidos(creacion_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado_pedido);

-- ------------------------------------------------------------------------------
-- 4. INITIAL SEED MOCK DATA
-- ------------------------------------------------------------------------------

-- 4.1 Users (Admin, Artisan, Assistant)
-- Password: 'admin123' (verified bcrypt hash via password_hash)
INSERT INTO usuarios (id, username, password_hash, rol, creado_en)
VALUES 
(
    1,
    'admin',
    '$2y$10$TiTdw7i0Dqey7iQKr1v6Ne/5GYbrWtUP/rMV8RsmT9BWR4k4ncb/S',
    'admin',
    datetime('now', 'localtime')
),
(
    2,
    'artesana_ana',
    '$2y$10$TiTdw7i0Dqey7iQKr1v6Ne/5GYbrWtUP/rMV8RsmT9BWR4k4ncb/S',
    'artesano',
    datetime('now', '-10 days', 'localtime')
),
(
    3,
    'asistente_leo',
    '$2y$10$TiTdw7i0Dqey7iQKr1v6Ne/5GYbrWtUP/rMV8RsmT9BWR4k4ncb/S',
    'asistente',
    datetime('now', '-8 days', 'localtime')
);

-- 4.2 Five Distinct Crochet Creations
-- Item 1: Amigurumis & Figuras category (@admin)
INSERT INTO creaciones (
    id, artesano_id, nombre, categoria, material, dimensiones, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, es_sobre_encargo, creado_en, actualizado_en
) VALUES (
    1,
    1,
    'Dragón Ignis',
    'Amigurumis & Figuras',
    '100% Algodón Mercerizado',
    '18.5 cm (Alto)',
    45000,
    12000,
    4,
    6.5,
    'Amigurumi de dragón fantástico tejido a crochet con escamas en relieve, alas articuladas y relleno sintético hipoalergénico de alta densidad.',
    'uploads/dragon_ignis.jpg',
    0,
    datetime('now', '-5 days', 'localtime'),
    NULL
);

-- Item 2: Hogar & Decoración category (@admin)
INSERT INTO creaciones (
    id, artesano_id, nombre, categoria, material, dimensiones, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, es_sobre_encargo, creado_en, actualizado_en
) VALUES (
    2,
    1,
    'Mini Suculenta en Maceta',
    'Hogar & Decoración',
    'Algodón Rústico y Lana Acrílica',
    '10.0 cm x 8.0 cm',
    18000,
    4500,
    12,
    2.0,
    'Pequeña maceta tejida con suculenta en relieve botánico. No requiere riego, ideal para escritorios, repisas y espacios de trabajo.',
    'uploads/suculenta.jpg',
    0,
    datetime('now', '-3 days', 'localtime'),
    NULL
);

-- Item 3: Amigurumis & Figuras category (@artesana_ana) - Confección Exclusiva Bajo Encargo
INSERT INTO creaciones (
    id, artesano_id, nombre, categoria, material, dimensiones, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, es_sobre_encargo, creado_en, actualizado_en
) VALUES (
    3,
    2,
    'Ajolote Rosado Pastel',
    'Amigurumis & Figuras',
    'Hilo Chenille Terciopelo',
    '14.0 x 10.0 cm',
    32000,
    8500,
    0,
    4.5,
    'Tierno ajolote mexicano con textura aterciopelada ultra suave, branquias externas en color frambuesa y ojos de seguridad kawaii. Se elabora exclusivamente bajo encargo.',
    'uploads/ajolote.jpg',
    1,
    datetime('now', '-1 days', 'localtime'),
    NULL
);

-- Item 4: Prendas & Ropa category (@admin)
INSERT INTO creaciones (
    id, artesano_id, nombre, categoria, material, dimensiones, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, es_sobre_encargo, creado_en, actualizado_en
) VALUES (
    4,
    1,
    'Cardigan Granny Squares',
    'Prendas & Ropa',
    'Lana Merino y Algodón Soft',
    'Talla M (95 x 58 cm)',
    98000,
    28000,
    2,
    18.0,
    'Cardigan bohemio tejido a mano con cuadros de la abuela (granny squares) florales en paleta nórdica y botones de madera rústica.',
    'uploads/cardigan_granny.jpg',
    0,
    datetime('now', '-6 days', 'localtime'),
    NULL
);

-- Item 5: Bolsos & Accesorios category (@artesana_ana)
INSERT INTO creaciones (
    id, artesano_id, nombre, categoria, material, dimensiones, precio,
    costo_materiales, cantidad_stock, horas_tejido, descripcion,
    imagen_url, es_sobre_encargo, creado_en, actualizado_en
) VALUES (
    5,
    2,
    'Tote Bag Boho Trapillo',
    'Bolsos & Accesorios',
    'Trapillo de Algodón Reciclado',
    '35 x 30 cm (Asas: 25 cm)',
    38000,
    9500,
    6,
    4.5,
    'Bolsa estilo tote bag resistente tejida con punto espiga tupido, base ovalada reforzada y asas dobles ergonómicas.',
    'uploads/tote_bag.jpg',
    0,
    datetime('now', '-2 days', 'localtime'),
    NULL
);

-- 4.3 Two Commission Orders (Linked via Foreign Key)
-- Order 1: For Dragón Ignis (cantidad = 1, precio_final = 1 * 45000 = 45000)
INSERT INTO pedidos (
    id, cliente_nombre, cliente_contacto, creacion_id, cantidad, fecha_entrega,
    estado_pedido, estado_pago, precio_final, notas, creado_en
) VALUES (
    1,
    'Mariana Gómez',
    '+52 55 4892 1039',
    1,
    1,
    strftime('%Y-%m-%d', date('now', '+14 days')),
    'En Proceso',
    'Anticipo 50%',
    45000,
    'Empaque para regalo con listón verde bosque y dedicatoria para Sofía.',
    datetime('now', '-2 days', 'localtime')
);

-- Order 2: For Ajolote Rosado Pastel (cantidad = 2, precio_final = 2 * 32000 = 64000)
INSERT INTO pedidos (
    id, cliente_nombre, cliente_contacto, creacion_id, cantidad, fecha_entrega,
    estado_pedido, estado_pago, precio_final, notas, creado_en
) VALUES (
    2,
    'Carlos Mendoza',
    '+52 55 9301 8472',
    3,
    2,
    strftime('%Y-%m-%d', date('now', '+20 days')),
    'Pendiente',
    'Pendiente',
    64000,
    'Cliente solicita que ambos ajolotes lleven un tono ligeramente más pastel en las branquias.',
    datetime('now', '-4 hours', 'localtime')
);
