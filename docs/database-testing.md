# SQLite Database Testing & Constraint Verification Guide

This guide provides reproducible `sqlite3` CLI commands and queries for macOS terminal to verify that the physical SQLite database (`database/database.sqlite`), relational foreign keys, CHECK constraints, and business metrics are working as specified.

---

## 1. Quick Start: Interactive SQLite CLI

Open your macOS terminal in the project root directory (`/Users/angelzaragoza/Desktop/proyecto-web`):

```bash
# Launch interactive SQLite session with headers and column formatting
sqlite3 database/database.sqlite
```

Inside the interactive prompt, **always activate foreign keys first**:
```sql
.headers on
.mode column
PRAGMA foreign_keys = ON;
```

To exit the prompt at any time, type `.quit` or press `Ctrl + D`.

---

## 2. Inspecting Tables & Seed Data

### 2.1 View Seeded Admin User
```sql
SELECT id, username, rol, creado_en FROM usuarios;
```
*Expected Result:*
```
id  username  rol    creado_en
--  --------  -----  -------------------
1   admin     admin  2026-09-10 16:19:40
```

### 2.2 View Catalog Items with Formatted Currency ($ MXN)
Because prices and material costs are stored in cents (`INTEGER`), divide by `100.0` to display Mexican Pesos:

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
*Expected Result:*
```
id  nombre                   categoria           tamano_cm  precio_venta  costo_mat  stock  hrs
--  -----------------------  ------------------  ---------  ------------  ---------  -----  ---
1   Dragón Ignis             Fantasía            18.5       $450.00       $120.00    4      6.5
2   Mini Suculenta en Maceta Plantas / Botánica  10.0       $180.00       $45.00     12     2.0
3   Ajolote Rosado Pastel    Animales / Fauna    14.0       $320.00       $85.00     2      4.5
```

### 2.3 View Commission Orders Joined with Products & Artisans
Verify relational linkage between `pedidos`, `amigurumis`, and `usuarios`:

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
*Expected Result:*
```
pedido_id  cliente        producto               artesano  cantidad  total_orden  estado      fecha_entrega
---------  -------------  ---------------------  --------  --------  -----------  ----------  -------------
1          Mariana Gómez  Dragón Ignis           admin     1         $450.00      En Proceso  2026-09-24
2          Carlos Mendoza Ajolote Rosado Pastel  admin     2         $640.00      Pendiente   2026-09-30
```

---

## 3. Referential Integrity & Foreign Key Tests

> [!IMPORTANT]
> Ensure `PRAGMA foreign_keys = ON;` is executed before running these tests. SQLite does not enforce foreign keys by default unless explicitly enabled per connection.

### Test 3.1: Attempt to Delete a User with Linked Amigurumis (`ON DELETE RESTRICT`)
```sql
PRAGMA foreign_keys = ON;
DELETE FROM usuarios WHERE id = 1;
```
*Expected Result:*
```text
Runtime error: FOREIGN KEY constraint failed (19)
```
*(User is protected from deletion because amigurumis reference `artesano_id = 1`)*

### Test 3.2: Attempt to Delete an Amigurumi with Linked Orders (`ON DELETE RESTRICT`)
```sql
PRAGMA foreign_keys = ON;
DELETE FROM amigurumis WHERE id = 1;
```
*Expected Result:*
```text
Runtime error: FOREIGN KEY constraint failed (19)
```
*(Catalog item is protected from deletion because order #1 references `amigurumi_id = 1`)*

### Test 3.3: Attempt to Insert an Order Referencing Non-Existent Amigurumi
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, amigurumi_id, cantidad, precio_final)
VALUES ('Cliente Fantasma', 999, 1, 35000);
```
*Expected Result:*
```text
Runtime error: FOREIGN KEY constraint failed (19)
```
*(Insertion blocked because `amigurumi_id = 999` does not exist)*

---

## 4. CHECK Constraints Tests

### Test 4.1: Negative Price Prevention (`precio >= 1`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO amigurumis (artesano_id, nombre, categoria, material, tamano_cm, precio)
VALUES (1, 'Pieza Inválida', 'Fantasía', 'Algodón', 12.0, -5000);
```
*Expected Result:*
```text
Runtime error: CHECK constraint failed: precio >= 1 AND precio <= 9999999 (19)
```

### Test 4.2: Negative Stock Prevention (`cantidad_stock >= 0`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO amigurumis (artesano_id, nombre, categoria, material, tamano_cm, precio, cantidad_stock)
VALUES (1, 'Pieza Sin Stock', 'Fantasía', 'Algodón', 12.0, 25000, -3);
```
*Expected Result:*
```text
Runtime error: CHECK constraint failed: cantidad_stock >= 0 AND cantidad_stock <= 10000 (19)
```

### Test 4.3: Order Quantity Zero Prevention (`cantidad >= 1`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, amigurumi_id, cantidad, precio_final)
VALUES ('Ana López', 2, 0, 18000);
```
*Expected Result:*
```text
Runtime error: CHECK constraint failed: cantidad >= 1 AND cantidad <= 1000 (19)
```

### Test 4.4: Invalid Order Status Prevention
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, amigurumi_id, cantidad, estado_pedido, precio_final)
VALUES ('Ana López', 2, 1, 'Desconocido', 18000);
```
*Expected Result:*
```text
Runtime error: CHECK constraint failed: estado_pedido IN (
        'Pendiente', 
        'En Proceso', 
        'Entregado', 
        'Cancelado'
    ) (19)
```

### Test 4.5: On-Demand Exclusivity Validation (`es_sobre_encargo IN (0, 1)`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO amigurumis (artesano_id, nombre, categoria, material, tamano_cm, precio, es_sobre_encargo)
VALUES (1, 'Invalid On Demand Item', 'Fantasía', 'Algodón', 15.0, 30000, 5);
```
*Expected Result:*
```text
Runtime error: CHECK constraint failed: es_sobre_encargo IN (0, 1) (19)
```

### Test 4.6: Order Payment Status Validation (`chk_pedidos_estado_pago`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, cliente_contacto, amigurumi_id, cantidad, estado_pago, precio_final)
VALUES ('Valeria Luna', '5512345678', 1, 1, 'Unknown Payment', 45000);
```
*Expected Result:*
```text
Runtime error: CHECK constraint failed: estado_pago IN ('Pendiente', 'Anticipo 50%', 'Liquidado') (19)
```

### Test 4.7: Client Contact String Length Validation (`chk_pedidos_cliente_contacto <= 50`)
```sql
PRAGMA foreign_keys = ON;
INSERT INTO pedidos (cliente_nombre, cliente_contacto, amigurumi_id, cantidad, precio_final)
VALUES ('Valeria Luna', 'This contact value is purposefully longer than the maximum allowed fifty characters limit', 1, 1, 45000);
```
*Expected Result:*
```text
Runtime error: CHECK constraint failed: length(trim(cliente_contacto)) <= 50 (19)
```

---

## 5. Micro-ERP Business Metrics Queries

### 5.1 Profit Margins & Effective Hourly Return Rate ($/hr)
Compute gross margin ($), margin percentage (%), and return per labor hour ($/hr) directly in SQL:

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
*Expected Result:*
```
nombre                   p_venta  c_mat    ganancia_neta  margen_pct  hrs_labor  retorno_por_hora
-----------------------  -------  -------  -------------  ----------  ---------  ----------------
Dragón Ignis             $450.00  $120.00  $330.00        73.3%       6.5        $50.77/hr
Mini Suculenta en Maceta $180.00  $45.00   $135.00        75.0%       2.0        $67.50/hr
Ajolote Rosado Pastel    $320.00  $85.00   $235.00        73.4%       4.5        $52.22/hr
```

### 5.2 Total Physical Inventory Valuation
Calculate total capital invested in materials vs potential revenue from current stock:

```sql
SELECT 
    SUM(cantidad_stock) AS total_unidades_stock,
    printf('$%.2f', SUM(costo_materiales * cantidad_stock) / 100.0) AS inversion_total_materiales,
    printf('$%.2f', SUM(precio * cantidad_stock) / 100.0) AS valor_comercial_total,
    printf('$%.2f', SUM((precio - costo_materiales) * cantidad_stock) / 100.0) AS ganancia_potencial
FROM amigurumis;
```
*Expected Result:*
```
total_unidades_stock  inversion_total_materiales  valor_comercial_total  ganancia_potencial
--------------------  --------------------------  ---------------------  ------------------
18                    $1190.00                    $4600.00               $3410.00
```

---

## 6. One-Liner Terminal Commands for macOS

Run these standalone commands from your terminal shell to verify functionality without entering the interactive SQLite CLI:

```bash
# 1. Check foreign key enforcement
sqlite3 database/database.sqlite "PRAGMA foreign_keys = ON; DELETE FROM usuarios WHERE id = 1;"

# 2. Check catalog stock list
sqlite3 -column -header database/database.sqlite "SELECT id, nombre, cantidad_stock, printf('$%.2f', precio/100.0) AS precio FROM amigurumis;"

# 3. Check active orders
sqlite3 -column -header database/database.sqlite "SELECT id, cliente_nombre, amigurumi_id, cantidad, estado_pedido, printf('$%.2f', precio_final/100.0) AS total FROM pedidos;"

# 4. Re-run setup anytime to restore pristine seed data (CLI only)
php setup.php
```
