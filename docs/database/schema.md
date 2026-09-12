# Esquema DDL & Diccionario de Datos Relacional

[← Volver al Índice de Base de Datos](./README.md)

Este documento especifica la sintaxis DDL física de las 3 tablas relacionales del sistema, sus restricciones `CHECK`, claves foráneas e índices de rendimiento en **SQLite 3**.

---

## 1. Sentencias DDL Oficiales (`database/seed.sql`)

```sql
PRAGMA foreign_keys = ON;

-- 1. Tabla: usuarios (Autenticación y Roles RBAC)
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'admin',
    activo INTEGER NOT NULL DEFAULT 1,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    eliminado_en TEXT DEFAULT NULL,
    -- Restricciones de Tabla
    CONSTRAINT uq_usuarios_username UNIQUE (username),
    CONSTRAINT chk_usuarios_username CHECK(length(trim(username)) >= 3 AND length(username) <= 50),
    CONSTRAINT chk_usuarios_rol CHECK(rol IN ('admin', 'artesano', 'asistente')),
    CONSTRAINT chk_usuarios_activo CHECK(activo IN (0, 1))
);

-- 2. Tabla: creaciones (Catálogo e Inventario del Taller)
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
    activo INTEGER NOT NULL DEFAULT 1,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    actualizado_en TEXT DEFAULT NULL,
    eliminado_en TEXT DEFAULT NULL,
    -- Restricciones de Tabla
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
    CONSTRAINT chk_creaciones_es_sobre_encargo CHECK(es_sobre_encargo IN (0, 1)),
    CONSTRAINT chk_creaciones_activo CHECK(activo IN (0, 1))
);

-- 3. Tabla: pedidos (Encargos y Compras de Clientes)
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
    activo INTEGER NOT NULL DEFAULT 1,
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    actualizado_en TEXT DEFAULT NULL,
    eliminado_en TEXT DEFAULT NULL,
    -- Restricciones de Tabla
    CONSTRAINT fk_pedidos_creacion FOREIGN KEY (creacion_id) REFERENCES creaciones(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_pedidos_cliente_nombre CHECK(length(trim(cliente_nombre)) >= 2 AND length(cliente_nombre) <= 100),
    CONSTRAINT chk_pedidos_cantidad CHECK(cantidad >= 1 AND cantidad <= 1000),
    CONSTRAINT chk_pedidos_fecha_entrega CHECK(fecha_entrega IS NULL OR length(trim(fecha_entrega)) = 10),
    CONSTRAINT chk_pedidos_estado CHECK(estado_pedido IN ('Pendiente', 'En Proceso', 'Entregado', 'Cancelado')),
    CONSTRAINT chk_pedidos_precio_final CHECK(precio_final >= 1 AND precio_final <= 9999999),
    CONSTRAINT chk_pedidos_notas CHECK(notas IS NULL OR length(notas) <= 1000),
    CONSTRAINT chk_pedidos_cliente_contacto CHECK(length(trim(cliente_contacto)) <= 50),
    CONSTRAINT chk_pedidos_estado_pago CHECK(estado_pago IN ('Pendiente', 'Anticipo 50%', 'Liquidado')),
    CONSTRAINT chk_pedidos_activo CHECK(activo IN (0, 1))
);

-- Índices de Rendimiento
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_usuarios_activo ON usuarios(activo);
CREATE INDEX IF NOT EXISTS idx_creaciones_artesano ON creaciones(artesano_id);
CREATE INDEX IF NOT EXISTS idx_creaciones_categoria ON creaciones(categoria);
CREATE INDEX IF NOT EXISTS idx_creaciones_stock ON creaciones(cantidad_stock);
CREATE INDEX IF NOT EXISTS idx_creaciones_activo ON creaciones(activo);
CREATE INDEX IF NOT EXISTS idx_pedidos_creacion ON pedidos(creacion_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado_pedido);
CREATE INDEX IF NOT EXISTS idx_pedidos_activo ON pedidos(activo);
```

---

## 2. Diccionario de Datos

### 2.1 Tabla `usuarios`
| Campo | Tipo SQLite | Nulo | Por Defecto | Descripción |
| :--- | :---: | :---: | :---: | :--- |
| `id` | `INTEGER` | No | PK AUTO | Identificador único del usuario. |
| `username` | `TEXT` | No | - | Nombre alfanumérico único (3 a 50 chars). |
| `password_hash`| `TEXT` | No | - | Hash bcrypt con prefijo `$2y$10$`. |
| `rol` | `TEXT` | No | `'admin'` | Rol de acceso (`admin`, `artesano`, `asistente`). |
| `activo` | `INTEGER` | No | `1` | Estado de cuenta: `1` activo, `0` baja lógica. |
| `creado_en` | `TEXT` | No | `now` | Marca temporal ISO de registro. |
| `eliminado_en`| `TEXT` | Sí | `NULL` | Marca temporal ISO de baja lógica o `NULL`. |

### 2.2 Tabla `creaciones`
| Campo | Tipo SQLite | Nulo | Por Defecto | Descripción |
| :--- | :---: | :---: | :---: | :--- |
| `id` | `INTEGER` | No | PK AUTO | Identificador único de la creación. |
| `artesano_id` | `INTEGER` | No | - | FK hacia `usuarios(id)` del autor. |
| `nombre` | `TEXT` | No | - | Título de la pieza (2 a 100 chars). |
| `categoria` | `TEXT` | No | - | Categoría textil (2 a 50 chars). |
| `material` | `TEXT` | No | - | Tipo de fibra o hilaza (3 a 80 chars). |
| `dimensiones` | `TEXT` | No | - | Medidas físicas (ej. `22 cm`, `35x30 cm`). |
| `precio` | `INTEGER` | No | - | Precio de venta en centavos enteros. |
| `costo_materiales`| `INTEGER` | No | `0` | Costo de insumos en centavos enteros. |
| `cantidad_stock`| `INTEGER` | No | `0` | Existencias físicas (0 a 10,000). |
| `horas_tejido` | `REAL` | Sí | `0.0` | Horas de labor manual (0.0 a 500.0). |
| `descripcion` | `TEXT` | Sí | `NULL` | Descripción artesanal (máx 2000 chars). |
| `imagen_url` | `TEXT` | Sí | `NULL` | Ruta de foto en `uploads/` o vector SVG. |
| `es_sobre_encargo`| `INTEGER` | No | `0` | `1` encargo exclusivo, `0` catálogo regular. |
| `activo` | `INTEGER` | No | `1` | `1` activo en catálogo, `0` baja lógica. |
| `creado_en` | `TEXT` | No | `now` | Fecha de alta en el catálogo. |
| `actualizado_en`| `TEXT` | Sí | `NULL` | Fecha de última edición o `NULL`. |
| `eliminado_en`| `TEXT` | Sí | `NULL` | Fecha de baja lógica o `NULL`. |

### 2.3 Tabla `pedidos`
| Campo | Tipo SQLite | Nulo | Por Defecto | Descripción |
| :--- | :---: | :---: | :---: | :--- |
| `id` | `INTEGER` | No | PK AUTO | Identificador único del encargo. |
| `cliente_nombre`| `TEXT` | No | - | Nombre del comprador (2 a 100 chars). |
| `cliente_contacto`| `TEXT` | No | `''` | Teléfono o WhatsApp (máx 50 chars). |
| `creacion_id` | `INTEGER` | No | - | FK hacia `creaciones(id)`. |
| `cantidad` | `INTEGER` | No | `1` | Unidades solicitadas ($\ge 1$). |
| `fecha_entrega`| `TEXT` | Sí | `NULL` | Fecha acordada en formato `YYYY-MM-DD`. |
| `estado_pedido`| `TEXT` | No | `'Pendiente'` | `Pendiente`, `En Proceso`, `Entregado`, `Cancelado`. |
| `estado_pago` | `TEXT` | No | `'Pendiente'` | `Pendiente`, `Anticipo 50%`, `Liquidado`. |
| `precio_final` | `INTEGER` | No | - | Importe total congelado en centavos. |
| `notas` | `TEXT` | Sí | `NULL` | Indicaciones especiales (máx 1000 chars). |
| `activo` | `INTEGER` | No | `1` | `1` activo en panel, `0` baja lógica. |
| `creado_en` | `TEXT` | No | `now` | Fecha de creación del pedido. |
| `actualizado_en`| `TEXT` | Sí | `NULL` | Fecha de última modificación de estado o pago. |
| `eliminado_en`| `TEXT` | Sí | `NULL` | Fecha de baja lógica o `NULL`. |
