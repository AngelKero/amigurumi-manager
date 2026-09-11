# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Corrección Ergonómica de Tipografía en KPIs y Reparación Estructural de Cards de Pedidos (Completado y Verificado)

- **User Request:**
  - *"Mejora los tamaños de fuentes de estos y las cards estan rotas"* (adjunta 2 capturas: KPIs con tipografía monoespaciada gigante y desproporcionada; cards con badges cortados como `2 unida...` y layout apretado).

- **Diagnóstico y Soluciones:**
  1. **Tipografía de KPIs Refinada y Proporcionada:**
     - Se eliminó el uso de `fs-2 fw-extrabold font-monospace` que distorsionaba las métricas.
     - Se aplicó la tipografía artesanal display `font-theme-display` (`Fraunces` / `Outfit`) con tamaño `fs-2` y `fs-3` proporcionado.
     - Se separó la cifra numérica de la unidad textual descriptiva (`órdenes`, `en espera`, `activos`, `MXN`) en un `<span>` independiente (`text-muted small fw-medium`), evitando textos sobrecargados y respetando el estándar de `amigurumis.php`.
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
