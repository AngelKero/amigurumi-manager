# 001 · Base de Datos, DDL & Concurrencia SQLite

**Estado:** implementado ✅

## Qué hace

Define e inicializa el modelo de datos relacional físico para la plataforma colaborativa **Crochet Manager**. Implementa tres entidades nucleares (`usuarios`, `creaciones`, `pedidos`) con validaciones de negocio estrictas a nivel de esquema, integridad referencial con claves foráneas, columnas universales de borrado lógico, montos financieros exactos y un script de inicialización CLI-only.

## Por qué

Proporciona la base inmutable sobre la cual se construye todo el Micro-ERP. Al resolver la integridad y las restricciones en la base de datos (Database-First), se previenen estados inconsistentes, transacciones corruptas, colisiones de concurrencia y desbordamientos financieros.

## Criterios de aceptación

- [x] Las tres tablas del sistema (`usuarios`, `creaciones`, `pedidos`) se crean con DDL estricto en SQLite 3.
- [x] Claves foráneas configuradas con `ON DELETE RESTRICT ON UPDATE CASCADE` para preservar la trazabilidad referencial.
- [x] Restricciones CHECK de dominio que impiden precios negativos, stocks inválidos, estados desconocidos o nombres vacíos.
- [x] Montos financieros almacenados obligatoriamente en centavos enteros (`INTEGER`).
- [x] Columnas de borrado lógico universal (`activo INTEGER DEFAULT 1`, `eliminado_en TEXT DEFAULT NULL`) presentes en las tres tablas.
- [x] 9 índices relacionales creados para optimizar búsquedas por usuario, categoría, stock, artesano y estados.
- [x] Inicializador seguro `setup.php` restringido exclusivamente a ejecución en línea de comandos (`CLI-only`).

## Fuera de alcance

- Controladores HTTP, interfaces gráficas o servicios web (abordados en features posteriores).
