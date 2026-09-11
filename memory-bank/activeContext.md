# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Refactorización Ergonómica del Simulador de Márgenes (Completado y Verificado)

- **User Request:**
  - *"El simulador sigue roto"*

- **Diagnóstico Preciso del Problema Resuelto:**
  1. **Ruptura de Textos en 'd-flex justify-content-between':**
     - En la tarjeta del simulador (~280px-330px), los pares etiqueta-valor con textos largos colisionaban:
       - `Ganancia Bruta:` y `$0.00 MXN` se partían en 4 líneas fragmentadas (`Ganancia` / `Bruta:` a la izquierda, `$0.00` / `MXN` a la derecha).
       - `2. Retorno Efectivo por Hora:` (30 caracteres) y `$0.00 MXN/hr` (13 caracteres en `fs-5`) no cabían en una sola línea, fracturándose en `2. Retorno Efectivo` / `por Hora:` y `$0.00` / `MXN/hr`.
       - En `Costo Materiales: - $0.00 MXN`, el signo `-` quedaba descolgado.
  2. **Anomalía de Breakpoint XXL:**
     - El simulador tenía `col-xxl-4`, lo cual provocaba que al abrir la ventana en pantallas grandes (`>= 1400px`), la columna del simulador se reducía de 41.6% (`col-xl-5`) a solo 33.3% (`col-xxl-4`), haciéndolo más angosto en monitores grandes.
  3. **Pills de Feedback Desalineadas:**
     - Los badges de feedback (`.margin-feedback-pill`) tenían ancho variable e irregular sin llenar el ancho del contenedor.

- **Soluciones de Diseño Implementadas:**
  1. **Micro-Cards de Métricas Dedicadas:**
     - Se separó cada métrica clave (`Margen de Utilidad` y `Retorno por Hora`) en tarjetas estructuradas independientes (`.card-stitched bg-white`).
     - Títulos concisos (`Margen de Utilidad`, `Retorno por Hora`) con subtítulos de apoyo y valores en `fs-4 font-monospace text-nowrap` que nunca se fracturan ni envuelven.
     - Badges de feedback a ancho completo (`width: 100%; justify-content: center;`) como barras de estado uniformes.
  2. **Formato Monetario Conciso y 'text-nowrap':**
     - En `margin-calculator.js`, se implementó `formatPesos(retornoHora, false) + '/hr'` (`$0.00/hr`) y se blindó `displayGanancia` con `text-nowrap font-monospace`.
     - En el desglose financiero, se utilizó `text-nowrap` en precios y ganancia.
  3. **Corrección de Cuadrícula Responsive:**
     - Asignado `col-12 col-xl-7` para el formulario y `col-12 col-xl-5` para el simulador tanto en `xl` como en `xxl`, garantizando entre 360px y 420px de anchura óptima para la tarjeta financiera.
  4. **Blindaje de Desbordamiento y Padding:**
     - `sticky-margin-card` con `overflow: hidden` y padding responsivo `p-3 p-sm-4`.

## Next Steps:
- Mantener validación continua del sistema y esperar instrucciones del usuario para avanzar a la Fase 3 (Backend & Conexión Limpia).
