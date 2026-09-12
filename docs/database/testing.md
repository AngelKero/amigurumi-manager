# Guía de Pruebas & Verificación CLI de SQLite

[← Volver al Índice de Base de Datos](./README.md)

Este documento detalla los procedimientos para inicializar, inspeccionar y verificar la integridad de la base de datos `database/database.sqlite` desde la terminal.

---

## 1. Inicialización de la Base de Datos

El script `setup.php` inicializa la base de datos a partir de `database/seed.sql`. Cuenta con un guardia estricto que restringe su ejecución exclusivamente al entorno CLI (`php_sapi_name() === 'cli'`).

```bash
# Inicializar o regenerar la base de datos
php setup.php
```

Salida esperada:
```
Base de datos SQLite inicializada exitosamente en database/database.sqlite
Tablas creadas y verificadas:
  - usuarios: 3 registros
  - creaciones: 5 registros
  - pedidos: 2 registros
PRAGMA foreign_keys = ON verificado.
```

---

## 2. Verificación de Integridad Relacional con SQLite3

Ejecutar las comprobaciones físicas y referenciales estándar:

```bash
# 1. Comprobación de integridad física de páginas
sqlite3 database/database.sqlite "PRAGMA integrity_check;"
# Salida esperada: ok

# 2. Comprobación de violaciones de claves foráneas
sqlite3 database/database.sqlite "PRAGMA foreign_key_check;"
# Salida esperada: (vacío, 0 violaciones)

# 3. Comprobación de estado de claves foráneas
sqlite3 database/database.sqlite "PRAGMA foreign_keys;"
# Salida esperada: 1 (activo por conexión PDO)

# 4. Inspección de índices de rendimiento
sqlite3 database/database.sqlite "PRAGMA index_list('creaciones');"
```

---

## 3. Consultas de Verificación Reproducibles

### 3.1 Verificar Preservación de Filas en Baja Lógica (Cero Borrado Físico)
```bash
sqlite3 -header -column database/database.sqlite \
  "SELECT id, username, rol, activo, eliminado_en FROM usuarios WHERE activo = 0;"
```

### 3.2 Verificar Conteo de Creaciones Activas por Artesano (Punto F)
```bash
sqlite3 -header -column database/database.sqlite "
SELECT u.id, u.username, COUNT(c.id) AS total_creaciones
FROM usuarios u
INNER JOIN creaciones c ON c.artesano_id = u.id
WHERE u.activo = 1 AND c.activo = 1
GROUP BY u.id, u.username
ORDER BY u.username ASC;
"
```

### 3.3 Probar Violación de Restricción CHECK en Precio Negativo
```bash
sqlite3 database/database.sqlite \
  "INSERT INTO creaciones (artesano_id, nombre, categoria, material, dimensiones, precio) VALUES (1, 'Fallo', 'Test', 'Lana', '10cm', -500);"
# Error esperado: CHECK constraint failed: chk_creaciones_precio
```
