# Referencia Técnica: Diccionario de Datos Relacional SQLite

Este documento constituye el diccionario de datos oficial y exhaustivo de la base de datos física SQLite (`database/database.sqlite`) de **Crochet Manager**. Describe las 5 tablas del esquema, tipos de datos, restricciones de integridad referencial, índices y directivas pragmáticas de concurrencia.

---

## 1. Directivas de Motor y Concurrencia (Invariante R-08)

Toda conexión PDO instanciada mediante `App\Core\Database::getConnection()` ejecuta de manera obligatoria e inmediata las siguientes directivas PRAGMA:

```sql
PRAGMA foreign_keys = ON;
PRAGMA busy_timeout = 5000;
```

### Justificación Técnica:
- **`PRAGMA foreign_keys = ON;`**: Garantiza la verificación rigurosa de integridad referencial entre tablas dependientes en SQLite (desactivada por defecto en el motor C nativo).
- **`PRAGMA busy_timeout = 5000;`**: Establece una ventana de espera activa de hasta 5,000 milisegundos ante bloqueos de escritura concurrentes (`SQLITE_BUSY`), mitigando fallos por contención de hilos.

---

## 2. Convención Monetaria en Enteros (Invariante R-06)

Todos los valores económicos (`precio`, `costo_materiales`, `precio_final`) se modelan estrictamente como:
```
INTEGER (Centavos)
```
Queda estrictamente prohibido el uso de tipos `REAL`, `FLOAT` o `DOUBLE` para magnitudes financieras. `$150.00 MXN` se persiste exactamente como `15000`. La conversión bidireccional simétrica se delega a `App\Utils\CurrencyHelper` (PHP) y `src/js/modules/currency.js` (JavaScript).

---

## 3. Diccionario Exhaustivo de Tablas

### Tabla: `usuarios`
Almacena los perfiles de los artesanos, colaboradores y administradores de la plataforma.

| Columna | Tipo de Dato | Nulo | Por Defecto | Descripción |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `INTEGER` | NO | `AUTOINCREMENT` | Llave primaria subrogada única. |
| `nombre` | `TEXT` | NO | - | Nombre completo o nombre artístico del creador. |
| `usuario` | `TEXT` | NO | - | Nombre de usuario único para inicio de sesión (`UNIQUE`). |
| `password` | `TEXT` | NO | - | Hash criptográfico seguro generado con `PASSWORD_BCRYPT`. |
| `rol` | `TEXT` | NO | `'artesano'` | Rol de acceso (`admin`, `artesano`, `asistente`). |
| `whatsapp` | `TEXT` | SÍ | `NULL` | Teléfono en formato internacional E.164 (`525512345678`). |
| `activo` | `INTEGER` | NO | `1` | Bandera de borrado lógico (Invariante R-01). `1`: activo, `0`: baja. |
| `creado_en` | `TEXT` | NO | `datetime('now', 'localtime')` | Marca temporal ISO 8601 de registro. |
| `actualizado_en` | `TEXT` | NO | `datetime('now', 'localtime')` | Marca temporal de última modificación. |
| `eliminado_en` | `TEXT` | SÍ | `NULL` | Marca temporal de baja lógica. |

---

### Tabla: `creaciones`
Contiene el catálogo de piezas textiles, amigurumis y productos artesanales.

| Columna | Tipo de Dato | Nulo | Por Defecto | Descripción |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `INTEGER` | NO | `AUTOINCREMENT` | Llave primaria subrogada única. |
| `artesano_id` | `INTEGER` | NO | - | Llave foránea que referencia a `usuarios(id)`. |
| `titulo` | `TEXT` | NO | - | Nombre comercial de la creación. |
| `descripcion` | `TEXT` | SÍ | `NULL` | Narrativa, especificaciones técnicas y materiales. |
| `categoria` | `TEXT` | NO | - | Categoría textil (`Amigurumis`, `Prendas`, `Accesorios`, `Hogar`). |
| `precio` | `INTEGER` | NO | - | Precio de venta en **INTEGER (Centavos)**. |
| `costo_materiales` | `INTEGER` | NO | `0` | Costo directo de insumos en **INTEGER (Centavos)**. |
| `horas_tejido` | `REAL` | NO | `0.0` | Horas aproximadas invertidas en la confección. |
| `cantidad_stock` | `INTEGER` | NO | `0` | Unidades físicas disponibles para despacho inmediato. |
| `es_sobre_encargo` | `INTEGER` | NO | `0` | Bandera binaria (`1`: bajo encargo exclusivo, `0`: stock inmediato). |
| `foto` | `TEXT` | SÍ | `NULL` | Ruta relativa o nombre del archivo en `uploads/` o fallback SVG. |
| `activo` | `INTEGER` | NO | `1` | Bandera de borrado lógico (Invariantes R-01 y R-02). |
| `creado_en` | `TEXT` | NO | `datetime('now', 'localtime')` | Marca temporal de creación. |
| `actualizado_en` | `TEXT` | NO | `datetime('now', 'localtime')` | Marca temporal de última actualización. |
| `eliminado_en` | `TEXT` | SÍ | `NULL` | Marca temporal de baja lógica. |

---

### Tabla: `pedidos`
Registra las solicitudes de compra y encargos personalizados coordinados con los artesanos.

| Columna | Tipo de Dato | Nulo | Por Defecto | Descripción |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `INTEGER` | NO | `AUTOINCREMENT` | Llave primaria subrogada. |
| `creacion_id` | `INTEGER` | NO | - | Llave foránea hacia `creaciones(id)`. |
| `cliente_nombre` | `TEXT` | NO | - | Nombre completo del comprador o cliente. |
| `cliente_contacto`| `TEXT` | NO | - | Teléfono móvil o correo de contacto directo. |
| `cantidad` | `INTEGER` | NO | `1` | Unidades pactadas en la orden. |
| `precio_final` | `INTEGER` | NO | - | Monto total congelado al crear la orden en **INTEGER (Centavos)**. |
| `estado` | `TEXT` | NO | `'Pendiente'` | Estado del ciclo de vida (`Pendiente`, `En Proceso`, `Completado`, `Cancelado`). |
| `notas` | `TEXT` | SÍ | `NULL` | Instrucciones especiales, personalización de colores o empaque. |
| `activo` | `INTEGER` | NO | `1` | Bandera de borrado lógico (R-01). |
| `creado_en` | `TEXT` | NO | `datetime('now', 'localtime')` | Marca temporal del encargo. |
| `actualizado_en` | `TEXT` | NO | `datetime('now', 'localtime')` | Marca temporal del último cambio de estado. |
| `eliminado_en` | `TEXT` | SÍ | `NULL` | Marca temporal de baja lógica. |

---

### Tabla: `tokens_revocados`
Lista de revocación del lado del servidor (denylist) para invalidar tokens Bearer tras el cierre de sesión o reseteo de claves (ADR-016 / Invariante R-10).

| Columna | Tipo de Dato | Nulo | Por Defecto | Descripción |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `INTEGER` | NO | `AUTOINCREMENT` | Llave primaria única. |
| `jti` | `TEXT` | NO | - | Identificador criptográfico único del token JWT/HMAC (`UNIQUE`). |
| `usuario_id` | `INTEGER` | NO | - | Identificador del usuario titular del token. |
| `expiracion` | `INTEGER` | NO | - | Marca de tiempo UNIX epoch en la que el token habría expirado. |
| `revocado_en` | `TEXT` | NO | `datetime('now', 'localtime')` | Marca temporal del momento de revocación. |

---

### Tabla: `login_intentos`
Registro de intentos fallidos de inicio de sesión para mitigar ataques de fuerza bruta y enumeración de cuentas (ADR-016 / Invariante R-10).

| Columna | Tipo de Dato | Nulo | Por Defecto | Descripción |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `INTEGER` | NO | `AUTOINCREMENT` | Llave primaria única. |
| `usuario` | `TEXT` | NO | - | Identificador o alias intentado. |
| `ip` | `TEXT` | NO | - | Dirección IP origen de la solicitud HTTP. |
| `intentos` | `INTEGER` | NO | `1` | Contador acumulado de intentos fallidos en la ventana temporal. |
| `ultimo_intento` | `INTEGER` | NO | - | Timestamp UNIX epoch del intento fallido más reciente. |
| `bloqueado_hasta`| `INTEGER` | SÍ | `NULL` | Timestamp UNIX epoch hasta el cual se aplica HTTP 429 Too Many Requests. |

---

## 4. Índices y Optimización de Consultas

El esquema define los siguientes índices B-Tree para maximizar la velocidad de respuesta:
1. `idx_creaciones_artesano`: Optimiza la resolución de creaciones por artesano (`artesano_id, activo`).
2. `idx_creaciones_catalogo`: Acelera los filtros públicos del catálogo (`activo, categoria, precio`).
3. `idx_pedidos_creacion`: Vinculación relacional eficiente de pedidos por creación (`creacion_id, activo`).
4. `idx_tokens_jti`: Búsqueda en tiempo constante $O(1)$ de tokens revocados durante la autenticación.
5. `idx_login_ip_usuario`: Verificación instantánea del umbral de fuerza bruta en `/api/auth/login.php`.
