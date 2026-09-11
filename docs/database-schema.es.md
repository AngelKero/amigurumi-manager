# Esquema de Base de Datos Relacional y Diccionario de Datos: Micro-ERP

Este documento define el esquema relacional de producción para el Micro-ERP de Creaciones de Amigurumi, abarcando autenticación (`usuarios`), catálogo de productos e inventario (`amigurumis`), y seguimiento de pedidos y encargos personalizados (`pedidos`).

---

## 1. Diagrama Entidad-Relación (ERD)

```mermaid
erDiagram
    USUARIOS {
        integer id PK "INTEGER AUTOINCREMENT"
        string username UK "TEXT UNIQUE (3-50 caracteres)"
        string password_hash "TEXT (bcrypt/argon2)"
        string rol "TEXT (admin, artesano, asistente)"
        string creado_en "TEXT (Marca de tiempo ISO 8601)"
    }

    AMIGURUMIS {
        integer id PK "INTEGER AUTOINCREMENT"
        integer artesano_id FK "REFERENCES usuarios(id)"
        string nombre "TEXT NOT NULL (2-100 caracteres)"
        string categoria "TEXT NOT NULL (Lista blanca de la app)"
        string material "TEXT NOT NULL (3-80 caracteres)"
        string dimensiones "TEXT NOT NULL (2-100 caracteres)"
        integer precio "INTEGER NOT NULL (Precio en centavos)"
        integer costo_materiales "INTEGER NOT NULL (Costo en centavos)"
        integer cantidad_stock "INTEGER NOT NULL (Unidades físicas >= 0)"
        real horas_tejido "REAL (Horas de labor >= 0.0)"
        string descripcion "TEXT (Máx 2000 caracteres)"
        string imagen_url "TEXT (Máx 500 caracteres)"
        string creado_en "TEXT (Marca de tiempo ISO 8601)"
        string actualizado_en "TEXT (Marca de tiempo ISO 8601)"
    }

    PEDIDOS {
        integer id PK "INTEGER AUTOINCREMENT"
        string cliente_nombre "TEXT NOT NULL (2-100 caracteres)"
        integer amigurumi_id FK "REFERENCES amigurumis(id)"
        integer cantidad "INTEGER NOT NULL (Unidades solicitadas >= 1)"
        string fecha_entrega "TEXT (YYYY-MM-DD)"
        string estado_pedido "TEXT (Pendiente, En Proceso, Entregado, Cancelado)"
        integer precio_final "INTEGER NOT NULL (Precio pactado en centavos)"
        string notas "TEXT (Máx 1000 caracteres)"
        string creado_en "TEXT (Marca de tiempo ISO 8601)"
    }

    USUARIOS ||--o{ AMIGURUMIS : "confecciona / registra"
    AMIGURUMIS ||--o{ PEDIDOS : "referenciado en pedidos"
```

---

## 2. Especificación DDL de SQLite

```sql
-- Habilitar la integridad de claves foráneas en SQLite
PRAGMA foreign_keys = ON;

-- ========================================================
-- 1. Tabla: usuarios (Autenticación y Control de Acceso)
-- ========================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'admin',
    creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    -- Restricciones de tabla
    CONSTRAINT uq_usuarios_username UNIQUE (username),
    CONSTRAINT chk_usuarios_username CHECK(length(trim(username)) >= 3 AND length(username) <= 50),
    CONSTRAINT chk_usuarios_rol CHECK(rol IN ('admin', 'artesano', 'asistente'))
);

-- ========================================================
-- 2. Tabla: amigurumis (Catálogo Central e Inventario)
-- ========================================================
CREATE TABLE IF NOT EXISTS amigurumis (
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
    -- Restricciones de tabla
    CONSTRAINT fk_amigurumis_artesano FOREIGN KEY (artesano_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_amigurumis_nombre CHECK(length(trim(nombre)) >= 2 AND length(nombre) <= 100),
    CONSTRAINT chk_amigurumis_categoria CHECK(length(trim(categoria)) >= 2 AND length(categoria) <= 50),
    CONSTRAINT chk_amigurumis_material CHECK(length(trim(material)) >= 3 AND length(material) <= 80),
    CONSTRAINT chk_amigurumis_dimensiones CHECK(length(trim(dimensiones)) >= 2 AND length(dimensiones) <= 100),
    CONSTRAINT chk_amigurumis_precio CHECK(precio >= 1 AND precio <= 9999999),
    CONSTRAINT chk_amigurumis_costo_materiales CHECK(costo_materiales >= 0 AND costo_materiales <= 9999999),
    CONSTRAINT chk_amigurumis_cantidad_stock CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000),
    CONSTRAINT chk_amigurumis_horas_tejido CHECK(horas_tejido IS NULL OR (horas_tejido >= 0.0 AND horas_tejido <= 500.0)),
    CONSTRAINT chk_amigurumis_descripcion CHECK(descripcion IS NULL OR length(descripcion) <= 2000),
    CONSTRAINT chk_amigurumis_imagen_url CHECK(imagen_url IS NULL OR length(trim(imagen_url)) <= 500)
);

-- ========================================================
-- 3. Tabla: pedidos (Seguimiento de Encargos y Ventas)
-- ========================================================
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
    -- Restricciones de tabla
    CONSTRAINT fk_pedidos_amigurumi FOREIGN KEY (amigurumi_id) REFERENCES amigurumis(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_pedidos_cliente_nombre CHECK(length(trim(cliente_nombre)) >= 2 AND length(cliente_nombre) <= 100),
    CONSTRAINT chk_pedidos_cantidad CHECK(cantidad >= 1 AND cantidad <= 1000),
    CONSTRAINT chk_pedidos_fecha_entrega CHECK(fecha_entrega IS NULL OR length(trim(fecha_entrega)) = 10),
    CONSTRAINT chk_pedidos_estado CHECK(estado_pedido IN ('Pendiente', 'En Proceso', 'Entregado', 'Cancelado')),
    CONSTRAINT chk_pedidos_precio_final CHECK(precio_final >= 1 AND precio_final <= 9999999),
    CONSTRAINT chk_pedidos_notas CHECK(notas IS NULL OR length(notas) <= 1000)
);

-- ========================================================
-- Índices para Optimización de Consultas
-- ========================================================
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_amigurumis_artesano ON amigurumis(artesano_id);
CREATE INDEX IF NOT EXISTS idx_amigurumis_categoria ON amigurumis(categoria);
CREATE INDEX IF NOT EXISTS idx_amigurumis_stock ON amigurumis(cantidad_stock);
CREATE INDEX IF NOT EXISTS idx_pedidos_amigurumi ON pedidos(amigurumi_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado_pedido);
```

---

## 3. Diccionarios de Datos

### 3.1 Tabla: `usuarios`
| Campo | Tipo Lógico | Clase SQLite | Nulable | Valor por Defecto | Restricciones | Descripción |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | Identificador | `INTEGER` | **No** | *Autoincremental* | `PRIMARY KEY AUTOINCREMENT` | Identificador único del usuario. |
| `username` | Texto | `TEXT` | **No** | *Ninguno* | `UNIQUE`, Longitud 3-50 | Nombre de usuario del artesano o administrador. |
| `password_hash` | Texto | `TEXT` | **No** | *Ninguno* | Cadena de hash válida | Generado por PHP `password_hash($pwd, PASSWORD_DEFAULT)`. |
| `rol` | Enumeración | `TEXT` | **No** | `'admin'` | En `admin`, `artesano`, `asistente` | Nivel de autorización y permisos en el sistema. |
| `creado_en` | Marca de tiempo | `TEXT` | **No** | `datetime('now', 'localtime')` | Formato ISO 8601 | Fecha y hora de creación de la cuenta. |

### 3.2 Tabla: `amigurumis`
| Campo | Tipo Lógico | Clase SQLite | Nulable | Valor por Defecto | Restricciones | Descripción |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | Identificador | `INTEGER` | **No** | *Autoincremental* | `PRIMARY KEY AUTOINCREMENT` | Identificador único de la pieza de amigurumi. |
| `artesano_id` | Clave Foránea | `INTEGER` | **No** | *Ninguno* | `REFERENCES usuarios(id)` | Usuario artesano creador de la pieza. Protegido con `ON DELETE RESTRICT`. |
| `nombre` | Texto | `TEXT` | **No** | *Ninguno* | Longitud 2-100 | Nombre de la creación o personaje tejido. |
| `categoria` | Texto | `TEXT` | **No** | *Ninguno* | Longitud 2-50 | Clasificación temática (validada en lista blanca de la app). |
| `material` | Texto | `TEXT` | **No** | *Ninguno* | Longitud 3-80 | Tipo de hilo o fibra principal (ej. 100% Algodón, Chenille). |
| `dimensiones` | Texto | `TEXT` | **No** | *Ninguno* | Longitud 2-100 | Dimensiones 2D/3D (ej. 140x100 cm), talla de prendas o medidas de amigurumi. |
| `precio` | Moneda (Centavos) | `INTEGER` | **No** | *Ninguno* | `1` a `9999999` | Precio de venta al público en centavos ($150.50 MXN = 15050). |
| `costo_materiales`| Moneda (Centavos) | `INTEGER` | **No** | `0` | `0` a `9999999` | Inversión en insumos (hilo, ojos, vellón) en centavos. |
| `cantidad_stock` | Entero | `INTEGER` | **No** | `0` | `0` a `10000` | Unidades físicas disponibles en existencia. |
| `horas_tejido` | Decimal | `REAL` | **Sí** | `0.0` | `>= 0.0 AND <= 500.0` | Horas estimadas de trabajo manual invertidas en tejer la pieza. |
| `descripcion` | Texto Largo | `TEXT` | **Sí** | `NULL` | Longitud `<= 2000` | Notas de confección, cuidados de lavado y especificaciones. |
| `imagen_url` | URL Web | `TEXT` | **Sí** | `NULL` | Longitud `<= 500` | Enlace a la fotografía de la pieza o ruta local. |
| `es_sobre_encargo`| Bandera Binaria| `INTEGER` | **No** | `0` | En `0, 1` | 1 si la pieza se teje exclusivamente sobre pedido sin stock inmediato. |
| `creado_en` | Marca de tiempo | `TEXT` | **No** | `datetime('now', 'localtime')` | Formato ISO 8601 | Fecha y hora de registro de la pieza. |
| `actualizado_en` | Marca de tiempo | `TEXT` | **Sí** | `NULL` | Formato ISO 8601 | Auditoría de fecha de última modificación. |

### 3.3 Tabla: `pedidos`
| Campo | Tipo Lógico | Clase SQLite | Nulable | Valor por Defecto | Restricciones | Descripción |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | Identificador | `INTEGER` | **No** | *Autoincremental* | `PRIMARY KEY AUTOINCREMENT` | Identificador único del pedido o encargo. |
| `cliente_nombre` | Texto | `TEXT` | **No** | *Ninguno* | Longitud 2-100 | Nombre del cliente que solicitó el encargo. |
| `cliente_contacto`| Texto | `TEXT` | **No** | `''` | Longitud `<= 50` | Vía de contacto directo del cliente (WhatsApp, teléfono o email). |
| `amigurumi_id` | Clave Foránea | `INTEGER` | **No** | *Ninguno* | `REFERENCES amigurumis(id)` | Creación solicitada. Protegida con `ON DELETE RESTRICT`. |
| `cantidad` | Entero | `INTEGER` | **No** | `1` | `1` a `1000` | Cantidad de piezas solicitadas en este pedido. |
| `fecha_entrega` | Fecha (Texto) | `TEXT` | **Sí** | `NULL` | Formato `YYYY-MM-DD` | Fecha estimada o pactada de entrega. |
| `estado_pedido` | Enumeración | `TEXT` | **No** | `'Pendiente'` | En `Pendiente`, `En Proceso`, `Entregado`, `Cancelado` | Estado del ciclo de vida del encargo. |
| `estado_pago` | Enumeración | `TEXT` | **No** | `'Pendiente'` | En `Pendiente`, `Anticipo 50%`, `Liquidado` | Estado financiero y de cobro del encargo artesanal. |
| `precio_final` | Moneda (Centavos) | `INTEGER` | **No** | *Ninguno* | `1` a `9999999` | Precio total pactado bloqueado al momento de la orden en centavos. |
| `notas` | Texto | `TEXT` | **Sí** | `NULL` | Longitud `<= 1000` | Instrucciones de personalización (color de accesorios, mensaje de regalo). |
| `creado_en` | Marca de tiempo | `TEXT` | **No** | `datetime('now', 'localtime')` | Formato ISO 8601 | Fecha y hora de alta del pedido. |

---

## 4. Reglas de Integridad Referencial y Negocio
- **Activación Forzosa de Claves Foráneas:** Ejecución de `PRAGMA foreign_keys = ON;` al inicializar cualquier conexión PDO.
- **Atribución de Autoría (`artesano_id`):** Cada amigurumi está vinculado al artesano que lo registró. Un usuario no puede eliminarse si tiene creaciones registradas en el catálogo (`ON DELETE RESTRICT`).
- **Protección contra Eliminaciones Accidentales (`ON DELETE RESTRICT`):** Un amigurumi no se puede eliminar de la base de datos si existen encargos (activos o históricos) asociados a su ID.
- **Inmutabilidad de Precios (`precio_final`):** Calculado de forma segura en el servidor (`amigurumis.precio * pedidos.cantidad`) y congelado en `pedidos.precio_final` al crear la orden. Actualizaciones posteriores del catálogo no alteran el histórico contable.
- **Control de Cantidades (`cantidad`):** Cada pedido puede registrar múltiples unidades de un mismo diseño, permitiendo el cálculo exacto de ingresos e insumos requeridos.
- **Descuento Atómico de Inventario:** La creación de un pedido requiere una transacción de base de datos (`BEGIN TRANSACTION`). El backend verifica que `cantidad <= amigurumis.cantidad_stock` y descuenta el stock físico (`UPDATE amigurumis SET cantidad_stock = cantidad_stock - :cantidad`).
- **Reintegro de Stock por Cancelación:** Si se actualiza el pedido a `'Cancelado'` mediante `POST /api/actualizar_pedido.php`, se ejecuta una transacción que restituye las unidades apartadas a `amigurumis.cantidad_stock`.
- **Limpieza de Archivos Físicos (Cero Archivos Huérfanos):** Al eliminar un registro de amigurumi mediante `POST /api/eliminar.php`, el backend debe consultar `imagen_url` y eliminar el archivo físico asociado de `/uploads/` mediante `unlink()` de PHP antes o durante la eliminación. Si la eliminación es bloqueada por pedidos asociados (`ON DELETE RESTRICT`), el archivo físico se preserva en disco.
