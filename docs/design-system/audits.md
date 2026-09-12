# Auditorías Heurísticas de UI/UX & Accesibilidad WCAG

[← Volver al Índice de Diseño](./README.md)

Este documento consolida las evaluaciones heurísticas de interfaz, los análisis de contraste cromático bajo la norma **WCAG 2.1 AA** y la resolución de brechas visuales implementadas en **Crochet Manager**.

---

## 1. Verificación de Contraste Cromático (WCAG 2.1 AA)

| Elemento Visual | Color Texto | Color Fondo | Ratio Medido | Exigencia WCAG | Estado |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Texto Principal** | `#1E252D` | `#F8F9FB` | **14.2 : 1** | $\ge 4.5:1$ | ✅ CUMPLE (AAA) |
| **Texto Secundario** | `#505963` | `#FFFFFF` | **7.1 : 1** | $\ge 4.5:1$ | ✅ CUMPLE (AAA) |
| **Botón Primario** | `#FFFFFF` | `#8E5B74` | **5.42 : 1** | $\ge 4.5:1$ | ✅ CUMPLE (AA) |
| **Badge Abeto Nórdico** | `#235048` | `#EBF4F2` | **6.45 : 1** | $\ge 4.5:1$ | ✅ CUMPLE (AA) |
| **Badge Miel Nórdica** | `#7A4C00` | `#FAF3E8` | **5.12 : 1** | $\ge 4.5:1$ | ✅ CUMPLE (AA) |
| **Badge Agotado** | `#8E3B3B` | `#FCEFEF` | **5.85 : 1** | $\ge 4.5:1$ | ✅ CUMPLE (AA) |

---

## 2. Guardias Heurísticas de Negocio

1. **[CR-1] Guardia de Stock Agotado (`cantidad_stock === 0`):**
   - El botón de compra inmediata se deshabilita automáticamente (`disabled`, `aria-disabled="true"`).
   - Se despliega el badge `.badge-stock-out` con el texto *"Agotado para Entrega Inmediata"*.
   - Se habilita como alternativa la solicitud de encargo al artesano.
2. **[CR-2] Control de Cantidad Acotado (Stepper):**
   - Input de cantidad en modo solo lectura (`readonly`).
   - Botones `[-] [ 1 ] [+]` acotados entre `1` y `cantidad_stock`.
   - Deshabilitación dinámica de botones al alcanzar los límites.
3. **[CR-2] Responsividad Móvil en Pedidos:**
   - En pantallas menores a 768px, las filas de tabla se transforman en tarjetas móviles apiladas (`.order-card-mobile`).
4. **[QW-2] Restitución de Stock al Cancelar:**
   - Todo diálogo de cancelación de pedido informa con precisión el número de unidades y el nombre de la creación que se reintegran físicamente al inventario.
5. **[QW-2] Microcopy de Coordinación Directa:**
   - Todos los modales de encargo especifican que los plazos y personalizaciones se coordinan de forma directa con el creador independiente a través de la plataforma.
6. **Protección Anti-Desbordamiento en Dimensiones:**
   - Contenedor `.card-product-meta` con `flex-wrap: wrap` y píldora `.card-product-dimension` con truncado elíptico protector para evitar que textos largos de medidas rompan la cuadrícula de tarjetas.
