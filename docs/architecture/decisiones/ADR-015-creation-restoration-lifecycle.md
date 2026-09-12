# ADR-015: Restauración Lógica de Creaciones Inactivadas

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
Bajo la regla de borrado lógico universal, cuando un artesano retira temporalmente una pieza de su catálogo (`activo = 0`), la fila permanece físicamente en la tabla `creaciones`. Si el artesano decide volver a publicar la misma creación (por ejemplo, tras conseguir materiales o tejer nuevas existencias), debe existir un mecanismo formal para reactivarla sin tener que crear un registro duplicado desde cero.

## Decisión
Implementar `POST /api/creaciones/restaurar.php` y `CreacionService::restoreCreation()`:
- Valida que la pieza exista (HTTP 404).
- Valida permisos IDOR (solo el autor o un administrador pueden restaurarla; HTTP 403).
- Valida que la creación esté inactiva (`activo = 0`), arrojando `HTTP 409 Conflict` si ya está activa.
- Ejecuta `UPDATE creaciones SET activo = 1, eliminado_en = NULL, actualizado_en = datetime('now', 'localtime') WHERE id = :id`.

## Alternativas Consideradas
- **Obligar al artesano a crear una pieza nueva:** Duplica imágenes en disco, pierde el identificador histórico de la pieza y desvincula las estadísticas previas.

## Consecuencias
- Ciclo de vida reversible y completo para las creaciones del taller (alta $\leftrightarrow$ baja temporal $\leftrightarrow$ restauración).
- Conserva el historial de pedidos y enlaces directos existentes.

---

[← Anterior (ADR-014)](./ADR-014-public-active-artisans-endpoint.md) • [Índice de ADRs](./README.md)
