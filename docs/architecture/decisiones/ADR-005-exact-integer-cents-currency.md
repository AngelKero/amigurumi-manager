# ADR-005: Manejo de Moneda en Centavos Enteros y Enriquecimiento Dual

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-11

## Contexto
El uso de números en punto flotante (`REAL` o `float`) para cantidades financieras genera errores acumulativos de redondeo en operaciones matemáticas binarias (ej. `$19.99 * 3 = 59.970000000000006`).

## Decisión
1. Almacenar el 100% de montos monetarios como **enteros en centavos** en SQLite (`precio`, `costo_materiales`, `precio_final`). Ejemplo: `$250.00 MXN` se almacena como el entero `25000`.
2. Restricciones CHECK garantizan que los montos sean enteros positivos dentro del rango permitido (`CHECK(precio >= 1 AND precio <= 9999999)`).
3. La clase `App\Utils\CurrencyHelper` (PHP) y el módulo `src/js/modules/currency.js` (JavaScript) enriquecen simétricamente las respuestas JSON:
   - Clave numérica entera para operaciones (ej. `"precio": 25000`).
   - Clave formateada para interfaz de usuario (ej. `"precio_formateado": "$250.00 MXN"`).

## Alternativas Consideradas
- **Almacenar strings decimales (`DECIMAL` o `VARCHAR`):** SQLite no tiene tipo nativo DECIMAL y requiere conversiones de string en cada cálculo.
- **Flotantes directos:** Inaceptables para cálculos de márgenes brutos y cotizaciones de pedidos.

## Consecuencias
- Cero artefactos de precisión en cálculos contables y simuladores de márgenes.
- Homogeneidad absoluta entre backend y frontend.

---

[← Anterior (ADR-004)](./ADR-004-universal-soft-delete.md) • [Índice de ADRs](./README.md) • [Siguiente (ADR-006) →](./ADR-006-sqlite-busy-timeout-concurrency.md)
