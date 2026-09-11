# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Rediseño Integral de Gestión de Pedidos: Cards Responsivas y Armonización Algodón Nórdico (Completado y Verificado)

- **User Request:**
  - *"Hay area de mejora de diseño en pedido, mejoralo y cambia la tabla por cards asi como lo hiciste en amigurumis"* (con captura adjunta de `pedidos.php` mostrando la tabla rígida y el banner oscuro con contraste deficiente).

- **Resultados y Soluciones Implementadas:**
  1. **Cabecera Luminosa con Contraste Superior a 7:1 (`pedidos_content.php`):**
     - Se reemplazó el banner oscuro `.artisan-panel-banner` por `.artisan-module-header card-stitched` con pespunte perimetral, sello oficial `isologo-sello-taller.svg`, tipografía `Fraunces` para el título y botones de acción pespunteados (`Nuevo Encargo Manual`, `Inventario`, `Ver Catálogo`).
  2. **Reemplazo Total de la Tabla por Cuadrícula de Cards 3x / 2x (`#ordersGrid`):**
     - Se eliminó la tabla rígida `#ordersTableDesktop` y la vista móvil duplicada `#mobileOrdersContainer`, unificando la interfaz en una cuadrícula responsiva (`row-cols-1 row-cols-md-2 row-cols-xl-3`) con tarjetas artesanales `.card-admin-pedido.card-stitched`.
     - Cada card incluye:
       - **Header:** Insignia ID en fuente monoespaciada (`.order-id-badge`) y chip de fecha límite de entrega con icono de calendario (`.order-delivery-chip`).
       - **Strip de Amigurumi:** Marco fotográfico pespunteado (`.order-product-thumb-frame`) con SVG renderizado del producto (`dragon-ignis`, `ajolote-pastel`), título, tag textil de categoría y tag de cantidad.
       - **Bloque de Cliente:** Nombre del destinatario y botón de acción directa de WhatsApp (`.btn-wa-pill` hacia `https://wa.me/...`).
       - **Franja Financiera:** Monto total acordado y badge tri-estado de pago (`Pendiente`, `Anticipo 50%`, `Liquidado`).
       - **Notas de Confección:** Cita estilizada para especificaciones especiales (`.order-notes-preview`).
       - **Footer de Card:** Badge de fase de confección (`En Proceso`, `Pendiente`, `Entregado`, `Cancelado`), botón de inspección modal (`.btn-inspect-order` abriendo `#modalInspeccionarPedido`) y dropdown interactivo de cambio de estado y cancelación con restitución de inventario (`#modalCancelarPedido`).
  3. **Toolbar y Filtros Textiles Interactivos:**
     - Barra de pestañas textiles `.btn-filter-order-tab` con contadores dinámicos sincronizados (`#countFilterAll`, `#countFilterPendiente`, `#countFilterProceso`, etc.) y buscador reactivo en tiempo real con estado vacío dedicado (`#emptyOrdersGrid`).
  4. **Estilos Modulares Dedicados (`src/css/04-components/orders.css`):**
     - Creado módulo CSS e importado en `src/css/styles.css`.
  5. **Lógica JavaScript Sincronizada (`src/js/modules/orders.js`):**
     - Actualizada la manipulación reactiva de `#ordersGrid`, recálculo dinámico de métricas KPI, transiciones de estado in-situ e inyección de nuevas tarjetas para encargos manuales registrados por el artesano.
  6. **Validación:**
     - Sintaxis PHP (`php -l`) y JS (`node --check`) verificadas con 0 errores; respuesta HTTP 200 OK en `http://localhost:8000/pedidos.php`.
