# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Corrección de Duplicación y Desbordamiento en KPI Ingresos Activos (Completado y Verificado)

- **User Request:**
  - *"esta se sigue viendo mal"* (adjunta captura del KPI 'Ingresos Activos' mostrando `$1,090.00 MXN` con un segundo `MXN` en verde gigante en una línea separada y otro `MXN` a la derecha).

- **Causa Raíz Identificada:**
  1. En `views/pages/pedidos_content.php`, el contenedor incluía un `<span>MXN</span>` estático junto a `#kpiOrdersIngresos`.
  2. En `src/js/modules/orders.js`, la función `formatPesos(totalRevenue)` agregaba por defecto el sufijo `' MXN'`, provocando que `#kpiOrdersIngresos` recibiera `"$1,090.00 MXN"`. Al no caber en la tarjeta, el `MXN` del string saltaba a una segunda línea con tipografía gigante verde `font-theme-display`, coexistiendo con el `MXN` gris estático.
  3. En `amigurumis.php`, los KPIs numéricos y monetarios no llevan sufijo dentro de la cifra principal ni palabras como `órdenes` o `pedidos`, sino el número limpio (`2`, `1`, `1`, `$1,090.00`) con icono temático en la cabecera.

- **Solución Implementada:**
  1. Eliminado el `<span>MXN</span>` redundante en `pedidos_content.php`.
  2. Invocado `formatPesos(totalRevenue, false)` en `orders.js` para entregar únicamente `"$1,090.00"` sin sufijo.
  3. Aplicado `text-nowrap` a `#kpiOrdersIngresos` para blindar contra cualquier salto de línea.
  4. Homogeneizados los 4 KPIs con icono temático superior (`bi-journal-text`, `bi-hourglass-split`, `bi-gear-wide-connected`, `bi-cash-coin`) y número limpio sin palabras concatenadas.
  5. Verificado mediante `php -l`, `node --check` y respuesta HTML en `http://localhost:8000/pedidos.php`.
  2. **Reparación Estructural de las Cards de Pedidos (`.card-admin-pedido`):**
     - Se eliminó el layout comprimido de 68px lateral que provocaba el desbordamiento y corte de texto (`2 unida`).
     - Se implementó el **Marco Fotográfico Acolchado Pespunteado Centrado** (`.order-card-photo-frame`, 135px de altura) idéntico al de `amigurumis.php`, con fondo suave, outline pespunteado y SVG del amigurumi con sombra y micro-animación al hover.
     - El título del amigurumi, el tag textil de categoría, el tag de cantidad (`.order-qty-tag`) y la medida en cm ahora ocupan el ancho completo de la card con `d-flex flex-wrap align-items-center gap-2`, eliminando por completo cualquier riesgo de recorte o desbordamiento.
     - Se refinaron los bloques de cliente con botón directo de WhatsApp, franja financiera estilizada y notas de personalización con limitación a 2 líneas.
  3. **Sincronización de CSS y JavaScript:**
     - [`src/css/04-components/orders.css`](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/04-components/orders.css) actualizado con la nueva arquitectura visual.
     - [`src/js/modules/orders.js`](file:///Users/angelzaragoza/Desktop/proyecto-web/src/js/modules/orders.js) adaptado para actualizar solo las cifras numéricas de los KPIs y generar el template de card centrado al registrar encargos manuales.
  4. **Validación:**
     - Sintaxis PHP (`php -l`) y JS (`node --check`) validadas con 0 errores; respuesta HTTP 200 OK en `http://localhost:8000/pedidos.php`.
