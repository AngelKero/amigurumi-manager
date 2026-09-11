# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Ajustes de Layout y Ergonomía en Formulario y Simulador de Márgenes (Completado y Verificado)

- **User Request:**
  - *"El formulario se rompe el diseño con todo lo nuevo, dale unos ajustes"*

- **Diagnóstico del Problema Resuelto:**
  1. **Triple Columna Hacinada:** En `formulario.php`, el sidebar del panel ocupa `col-lg-3` y el área de contenido `col-lg-9`. Dentro de `formulario_content.php`, el formulario tenía `col-lg-8` y el simulador `col-lg-4`. Esto provocaba una división en 3 columnas simultáneas en pantallas estándar (Sidebar ~280px + Formulario ~480px + Simulador ~280px).
  2. **Ruptura de Input Groups Económicos:** En los 480px del formulario, los campos de `Precio Venta (*)` y `Costo Materiales (*)` estaban divididos en tres `col-md-4` (~140px cada uno). Debido al `flex-wrap: wrap` por defecto de Bootstrap y a la clase `.input-craft-pill` con `border-radius: 50px !important`, el símbolo `$` se desprendía a una línea superior y el input numérico caía debajo.
  3. **Títulos y Badges Comprimidos:** El título `Registrar Nueva Creación Artesanal` colisionaba con el badge de modo a la derecha.

- **Soluciones de Diseño Implementadas:**
  1. **Reorganización del Grid Responsive:**
     - En pantallas estándar (`< 1200px`), el formulario y el simulador adoptan ancho completo (`col-12`) del área del panel (~830px–950px), permitiendo que todos los pares de campos (`Categoría/Material`, `Tamaño/Stock`, `Precio/Costo/Horas`) respiren con espacio generoso sin desbordamiento.
     - En pantallas amplias (`xl` / `xxl`), se distribuye en `col-12 col-xl-7 col-xxl-8` para el formulario y `col-12 col-xl-5 col-xxl-4` para el simulador de márgenes.
  2. **Sistema `.input-group-craft` Antirruptura:**
     - `flex-wrap: nowrap !important` y `min-width: 0 !important` en `.form-control` para que el símbolo `$` y las unidades `cm`, `unidades`, `hrs` permanezcan siempre soldados a su campo sin importar la resolución.
     - Esquinas exteriores redondeadas en píldora (`--craft-radius-pill`), bordes interiores contiguos limpios e iluminación de borde `:focus-within` en `--craft-primary`.
  3. **Anclaje de Navegación del Panel:**
     - Agregado `id="simuladorMargen"` al contenedor del simulador para responder armónicamente al enlace `#simuladorMargen` del menú lateral izquierdo.
  4. **Microcopy y Jerarquía Visual:**
     - Header con flexbox envolvente (`d-flex flex-wrap justify-content-between align-items-center gap-2`), tipografía display artesanal (`Fraunces`) y sello artesanal cálido (`.badge-artisan-seal`). Microcopy de apoyo debajo de cada métrica financiera.

## Next Steps:
- Mantener validación continua del sistema y esperar instrucciones del usuario para avanzar a la Fase 3 (Backend & Conexión Limpia).
