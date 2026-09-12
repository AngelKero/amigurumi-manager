# ADR-004: Regla Universal de Borrado Lógico (Cero Eliminaciones Físicas)

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
El usuario estableció como regla inviolable de negocio que no deben existir eliminaciones físicas en la base de datos para preservar la trazabilidad contable, el historial de pedidos y la autoría de creaciones.

## Decisión
1. Se prohíbe terminantemente la sentencia SQL `DELETE FROM` en todo el sistema (`usuarios`, `creaciones`, `pedidos`).
2. Se añaden columnas `activo INTEGER NOT NULL DEFAULT 1 CHECK(activo IN (0, 1))` y `eliminado_en TEXT DEFAULT NULL` a las 3 tablas relacionales.
3. Se crean índices de rendimiento: `idx_usuarios_activo`, `idx_creaciones_activo`, `idx_pedidos_activo`.
4. Las operaciones de baja ejecutan:
   ```sql
   UPDATE <tabla> SET activo = 0, eliminado_en = datetime('now', 'localtime') WHERE id = :id AND activo = 1;
   ```
5. Todas las consultas de lectura filtran `WHERE activo = 1` por defecto, ocultando los registros a usuarios regulares pero preservándolos físicamente en SQLite.

## Alternativas Consideradas
- **Tablas de auditoría / historial (`usuarios_historico`):** Complejidad innecesaria de mantenimiento de esquema; el borrado lógico en la misma tabla satisface el 100% del requerimiento.

## Consecuencias
- Historial contable y de auditoría 100% garantizado.
- Las cuentas de usuario desactivadas son bloqueadas inmediatamente en el login y en validación de tokens activos.
- Los pedidos históricos conservan su relación de clave foránea intacta hacia la pieza comprada.

---

[← Anterior (ADR-003)](./ADR-003-zero-html-error-leaks.md) • [Índice de ADRs](./README.md) • [Siguiente (ADR-005) →](./ADR-005-exact-integer-cents-currency.md)
