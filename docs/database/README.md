# Modelo de Datos & Base de Datos Relacional

[← Volver al Hub Principal de Documentación](../README.md)

Este directorio documenta el diseño relacional físico, las restricciones de integridad y las guías de verificación para la base de datos **SQLite 3** (`database/database.sqlite`) de **Crochet Manager**.

---

## 1. Documentos del Módulo

| Documento | Descripción |
| :--- | :--- |
| **[Esquema DDL & Diccionario de Datos](./schema.md)** | Sentencias SQL completas, restricciones `CHECK`, claves foráneas, índices y diccionarios de campos para `usuarios`, `creaciones` y `pedidos`. |
| **[Guía de Pruebas & Verificación CLI](./testing.md)** | Comandos reproducibles en terminal con `sqlite3`, scripts CLI `setup.php`, verificación de integridad y auditoría de claves foráneas. |

---

## 2. Diagrama Entidad-Relación Físico (ERD)

```mermaid
erDiagram
    usuarios ||--o{ creaciones : "crea y gestiona"
    creaciones ||--o{ pedidos : "forma parte de"

    usuarios {
        INTEGER id PK "AUTOINCREMENT"
        TEXT username UK "3-50 chars, no espacios vacíos"
        TEXT password_hash "Bcrypt cost=10 ($2y$)"
        TEXT rol "admin | artesano | asistente"
        INTEGER activo "1: activo | 0: baja lógica"
        TEXT creado_en "datetime('now', 'localtime')"
        TEXT eliminado_en "datetime de baja lógica o NULL"
    }

    creaciones {
        INTEGER id PK "AUTOINCREMENT"
        INTEGER artesano_id FK "REFERENCES usuarios(id)"
        TEXT nombre "2-100 chars"
        TEXT categoria "2-50 chars"
        TEXT material "3-80 chars"
        TEXT dimensiones "2-100 chars (ej: 22 cm, 35x30 cm)"
        INTEGER precio "Centavos enteros (>= 1)"
        INTEGER costo_materiales "Centavos enteros (>= 0)"
        INTEGER cantidad_stock "0-10000 unidades"
        REAL horas_tejido "0.0 - 500.0 hrs"
        TEXT descripcion "Opcional, máx 2000 chars"
        TEXT imagen_url "Ruta uploads/ o vector SVG"
        INTEGER es_sobre_encargo "0: catálogo | 1: bajo encargo"
        INTEGER activo "1: activo | 0: baja lógica"
        TEXT creado_en "datetime('now', 'localtime')"
        TEXT actualizado_en "datetime última modificación o NULL"
        TEXT eliminado_en "datetime de baja lógica o NULL"
    }

    pedidos {
        INTEGER id PK "AUTOINCREMENT"
        TEXT cliente_nombre "2-100 chars"
        TEXT cliente_contacto "Teléfono / WhatsApp (máx 50)"
        INTEGER creacion_id FK "REFERENCES creaciones(id)"
        INTEGER cantidad ">= 1 unidad"
        TEXT fecha_entrega "YYYY-MM-DD o NULL"
        TEXT estado_pedido "Pendiente | En Proceso | Entregado | Cancelado"
        TEXT estado_pago "Pendiente | Anticipo 50% | Liquidado"
        INTEGER precio_final "Centavos enteros congelados en servidor"
        TEXT notas "Opcional, máx 1000 chars"
        INTEGER activo "1: activo | 0: baja lógica"
        TEXT creado_en "datetime('now', 'localtime')"
        TEXT actualizado_en "datetime de última actualización o NULL"
        TEXT eliminado_en "datetime de baja lógica o NULL"
    }
```

---

## 3. Principios y Salvaguardas Relacionales

1. **Claves Foráneas Activas:** Toda conexión PDO ejecuta obligatoriamente `PRAGMA foreign_keys = ON;`.
2. **Concurrencia Segura:** Se configura `PRAGMA busy_timeout = 5000;` para mitigar bloqueos bajo peticiones concurrentes.
3. **Cero Eliminaciones Físicas:** Universalmente gobernado por `activo = 1` y `eliminado_en`.
4. **Moneda en Enteros:** Todos los precios y costos se almacenan como centavos enteros (`INTEGER`).
5. **Aislamiento de Archivo:** El archivo físico `database/database.sqlite` se encuentra fuera de la raíz pública y está blindado contra acceso web por Apache `.htaccess` (`HTTP 403 Forbidden`).
