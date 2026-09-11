# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Eliminación del Menú Lateral en Formulario y Navegación de Retorno al Inventario (Completado y Verificado)

- **User Request:**
  - *"Mejor quita el menu izquierdo en el formulario y agrega dos botones, uno superior y otro inferior para regresar al inventario"*

- **Diagnóstico y Contexto:**
  - En `formulario.php`, el menú lateral izquierdo consumía 3 de las 12 columnas del layout general (`col-lg-3`), lo que limitaba el formulario y el simulador de márgenes a `col-lg-9` (~855px–990px), forzando un ancho innecesariamente estrecho.
  - Al remover el menú lateral de esta vista de confección enfocada, el formulario y el simulador se expanden fluidamente a lo largo de todo el `container-xl` (1140px a 1320px).

- **Cambios Implementados:**
  1. **Aislamiento en Layout Maestro (`views/layouts/main.php`):**
     - Se excluyó `'formulario'` de `$isPanelPage`, reservando el sidebar exclusivamente para las vistas administrativas de gestión (`amigurumis.php`, `pedidos.php`, `usuarios.php`).
     - El formulario ahora utiliza el 100% del contenedor sin interrupciones visuales laterales.
  2. **Botón Superior de Retorno al Inventario (`views/pages/formulario_content.php`):**
     - Se integró una barra de navegación superior con botón pespunteado `.btn-craft-outline-stitched` (`Volver al Inventario` hacia `amigurumis.php`) y una cinta de migas de pan artesanal (`breadcrumb-craft-ribbon`) que indica el estado actual (Nueva Creación o Modificación #ID).
  3. **Botón Inferior de Retorno al Inventario (`views/pages/formulario_content.php`):**
     - En la sección inferior de acciones del formulario, se sustituyeron los enlaces anteriores por un botón de acción secundario `Volver al Inventario` con icono de flecha hacia `amigurumis.php`, junto a los botones de `Limpiar` y `Guardar/Actualizar Creación`.
  4. **Distribución Espaciosa del Grid:**
     - Al disponer de todo el ancho del contenedor, la fila se organiza en `col-12 col-lg-7 col-xl-8` para el formulario y `col-12 col-lg-5 col-xl-4` para el simulador de márgenes, brindando entre 380px y 440px al simulador y más de 760px al formulario. Cero texto fragmentado.

## Next Steps:
- Mantener validación continua del sistema y esperar instrucciones del usuario para avanzar a la Fase 3 (Backend & Conexión Limpia).
