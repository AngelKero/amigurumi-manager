# Guía de Pruebas de Base de Datos y Verificación de Restricciones SQLite

Esta guía proporciona comandos CLI de `sqlite3` y consultas reproducibles para la terminal de macOS con el objetivo de verificar que la base de datos física SQLite (`database.sqlite`), sus claves foráneas, restricciones CHECK y métricas de negocio funcionen exactamente según las especificaciones.

---

## 1. Inicio Rápido: CLI Interactiva de SQLite

Abra su terminal en el directorio raíz del proyecto (`/Users/angelzaragoza/Desktop/proyecto-web`):

```bash
# Iniciar sesión interactiva de SQLite con encabezados y formato columnar
sqlite3 database.sqlite
```

Dentro del prompt interactivo, **siempre active primero las claves foráneas**:
```sql
.headers on
.mode column
PRAGMA foreign_keys = ON;
```

Para salir del prompt en cualquier momento, escriba `.quit` o presione `Ctrl + D`.

---

## 2. Inspección de Tablas y Datos Semilla

### 2.1 Ver Usuario Administrador Inicial
```sql
SELECT id, username, rol, creado_en FROM usuarios;
```
*Resultado Esperado:*
```
id  username  rol    creado_en
--  --------  -----  -------------------
1   admin     admin  2026-09-10 16:19:40
```

### 2.2 Ver Catálogo de Amigurumis con Moneda Formateada ($ MXN)
Debido a que los precios y costos de materiales se almacenan en centavos (`INTEGER`), divida entre `100.0` para visualizar pesos mexicanos:

```sql
SELECT 
    id,
    nombre,
    categoria,
    tamano_cm,
    printf('$%.2f', precio / 100.0) AS precio_venta,
    printf('$%.2f', costo_materiales / 100.0) AS costo_mat,
    cantidad_stock AS stock,
    horas_tejido AS hrs
FROM amigurumis;
```
*Resultado Esperado:*
```
id  nombre                   categoria           tamano_cm  precio_venta  costo_mat  stock  hrs
--  -----------------------  ------------------  ---------  ------------  ---------  -----  ---
1   Dragón Ignis             Fantasía            18.5       $450.00       $120.00    4      6.5
2   Mini Suculenta en Maceta Plantas / Botánica  10.0       $180.00       $45.00     12     2.0
3   Ajolote Rosado Pastel    Animales / Fauna    14.0       $320.00       $85.00     2      4.5
```

### 2.3 Ver Pedidos de Encargo Vinculados con Productos y Artesanos
Verifica la relación entre `pedidos`, `amigurumis` y `usuarios`:

```sql
SELECT 
    p.id AS pedido_id,
    p.cliente_nombre AS cliente,
    a.nombre AS producto,
    u.username AS artesano,
    p.cantidad,
    printf('$%.2f', p.precio_final / 100.0) AS total_orden,
    p.estado_pedido AS estado,
    p.fecha_entrega
FROM pedidos p
JOIN amigurumis a ON p.amigurumi_id = a.id
JOIN usuarios u ON a.artesano_id = u.id;
```
*Resultado Esperado:*
```
pedido_id  cliente        producto               artesano  cantidad  total_orden  estado      fecha_entrega
---------  -------------  ---------------------  --------  --------  -----------  ----------  -------------
1          Mariana Gómez  Dragón Ignis           admin     1         $450.00      En Proceso  2026-09-24
2          Carlos Mendoza Ajolote Rosado Pastel  admin     2         $640.00      Pendiente   2026-09-30
```

---

## 3. Pruebas de Integridad Referencial y Claves Foráneas

> [!IMPORTANT]
> Asegúrese de ejecutar `PRAGMA foreign_keys = ON;` antes de correr estas pruebas. SQLite no valida claves foráneas a menos que se activen explícitamente por conexión.

### Prueba 3.1: Intento de Eliminar Usuario con Amigurumis Asociados (`ON DELETE RESTRICT`)
```sql
PRAGMA foreign_keys = ON;
DELETE FROM usuarios WHERE id = 1;
```
*Resultado Esperado:*
```text
Runtime error: FOREIGN KEY constraint failed (19)
```
*(El usuario está protegido contra borrado porque existen amigurumis que referencian `artesano_id = 1`)*

### Prueba 3.2: Intento de Eliminar Amigurumi con Pedidos Asociados (`ON DELETE RESTRICT`)
```sql
PRAGMA foreign_keys = ON;
DELETE FROM amigurumis WHERE id = 1;
```
*Resultado Esperado:*
```text
Runtime error: FOREIGN KEY constraint failed (19)
```
*(El amigurumi está protegido contra borrado porque el pedido #1 referencia `amigurumi_id = 1`)*

### Prueba 3.3: Intento de Insertar Pedido con Amigurumi Inexistente
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, amigurumi_id, cantidad, precio_final)
VALUES ('Cliente Fantasma', 999, 1, 35000);
```
*Resultado Esperado:*
```text
Runtime error: FOREIGN KEY constraint failed (19)
```
*(Inserción rechazada porque `amigurumi_id = 999` no existe)*

---

## 4. Pruebas de Restricciones CHECK

### Prueba 4.1: Prevención de Precio Negativo (`precio >= 1`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO amigurumis (artesano_id, nombre, categoria, material, tamano_cm, precio)
VALUES (1, 'Pieza Inválida', 'Fantasía', 'Algodón', 12.0, -5000);
```
*Resultado Esperado:*
```text
Runtime error: CHECK constraint failed: precio >= 1 AND precio <= 9999999 (19)
```

### Prueba 4.2: Prevención de Inventario Negativo (`cantidad_stock >= 0`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO amigurumis (artesano_id, nombre, categoria, material, tamano_cm, precio, cantidad_stock)
VALUES (1, 'Pieza Sin Stock', 'Fantasía', 'Algodón', 12.0, 25000, -3);
```
*Resultado Esperado:*
```text
Runtime error: CHECK constraint failed: cantidad_stock >= 0 AND cantidad_stock <= 10000 (19)
```

### Prueba 4.3: Prevención de Cantidad Cero en Pedidos (`cantidad >= 1`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, amigurumi_id, cantidad, precio_final)
VALUES ('Ana López', 2, 0, 18000);
```
*Resultado Esperado:*
```text
Runtime error: CHECK constraint failed: cantidad >= 1 AND cantidad <= 1000 (19)
```

### Prueba 4.4: Prevención de Estado de Pedido Inválido
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, amigurumi_id, cantidad, estado_pedido, precio_final)
VALUES ('Ana López', 2, 1, 'Desconocido', 18000);
```
*Resultado Esperado:*
```text
Runtime error: CHECK constraint failed: estado_pedido IN (
        'Pendiente', 
        'En Proceso', 
        'Entregado', 
        'Cancelado'
    ) (19)
```

---

## 5. Consultas de Métricas de Negocio (Micro-ERP)

### 5.1 Márgenes de Ganancia y Retorno Horario Efectivo ($/hr)
Cálculo de margen bruto ($), porcentaje de margen (%) y rendimiento por hora de tejido ($/hr):

```sql
SELECT 
    nombre,
    printf('$%.2f', precio / 100.0) AS p_venta,
    printf('$%.2f', costo_materiales / 100.0) AS c_mat,
    printf('$%.2f', (precio - costo_materiales) / 100.0) AS ganancia_neta,
    printf('%.1f%%', ((precio - costo_materiales) * 100.0) / precio) AS margen_pct,
    horas_tejido AS hrs_labor,
    printf('$%.2f/hr', ((precio - costo_materiales) / 100.0) / horas_tejido) AS retorno_por_hora
FROM amigurumis;
```
*Resultado Esperado:*
```
nombre                   p_venta  c_mat    ganancia_neta  margen_pct  hrs_labor  retorno_por_hora
-----------------------  -------  -------  -------------  ----------  ---------  ----------------
Dragón Ignis             $450.00  $120.00  $330.00        73.3%       6.5        $50.77/hr
Mini Suculenta en Maceta $180.00  $45.00   $135.00        75.0%       2.0        $67.50/hr
Ajolote Rosado Pastel    $320.00  $85.00   $235.00        73.4%       4.5        $52.22/hr
```

### 5.2 Valuación Total del Inventario Físico
Calcula el capital total invertido en insumos frente al valor comercial potencial en stock:

```sql
SELECT 
    SUM(cantidad_stock) AS total_unidades_stock,
    printf('$%.2f', SUM(costo_materiales * cantidad_stock) / 100.0) AS inversion_total_materiales,
    printf('$%.2f', SUM(precio * cantidad_stock) / 100.0) AS valor_comercial_total,
    printf('$%.2f', SUM((precio - costo_materiales) * cantidad_stock) / 100.0) AS ganancia_potencial
FROM amigurumis;
```
*Resultado Esperado:*
```
total_unidades_stock  inversion_total_materiales  valor_comercial_total  ganancia_potencial
--------------------  --------------------------  ---------------------  ------------------
18                    $1190.00                    $4600.00               $3410.00
```

---

## 6. Comandos Rápidos de una Sola Línea para Terminal macOS

Ejecute estos comandos directamente desde su terminal bash/zsh sin ingresar a la CLI interactiva:

```bash
# 1. Comprobar protección por clave foránea
sqlite3 database.sqlite "PRAGMA foreign_keys = ON; DELETE FROM usuarios WHERE id = 1;"

# 2. Listar catálogo e inventario
sqlite3 -column -header database.sqlite "SELECT id, nombre, cantidad_stock, printf('$%.2f', precio/100.0) AS precio FROM amigurumis;"

# 3. Listar pedidos activos
sqlite3 -column -header database.sqlite "SELECT id, cliente_nombre, amigurumi_id, cantidad, estado_pedido, printf('$%.2f', precio_final/100.0) AS total FROM pedidos;"

# 4. Restaurar base de datos a estado inicial limpio
php setup.php
```
