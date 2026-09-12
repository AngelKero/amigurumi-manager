# ADR-009: Cancelación Idempotente de Pedidos con Restitución de Stock

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
Cuando un pedido es cancelado por el artesano, las unidades reservadas deben reintegrarse al inventario físico de la creación. Si la operación no es idempotente, peticiones repetidas o fallos de red podrían causar que el stock se incremente múltiples veces artificialmente.

## Decisión
1. `PedidoService::cancelOrder()` valida primero el estado actual del pedido:
   ```php
   if ($pedido['estado_pedido'] === 'Cancelado') {
       throw new RuntimeException('El pedido ya se encuentra cancelado.', 409);
   }
   ```
2. La cancelación se realiza dentro de una transacción serializada (`BEGIN IMMEDIATE TRANSACTION`):
   - Se actualiza `pedidos.estado_pedido = 'Cancelado'` y `pedidos.actualizado_en = datetime(...)`.
   - Se restituyen las unidades: `UPDATE creaciones SET cantidad_stock = cantidad_stock + :cantidad WHERE id = :creacionId`.

## Alternativas Consideradas
- **Restitución manual por parte del artesano:** Propenso a errores humanos y desincronización entre pedidos y catálogo.

## Consecuencias
- Cero desajustes de stock ante reintentos de cancelación.
- Trazabilidad del momento exacto de la cancelación mediante `actualizado_en`.
