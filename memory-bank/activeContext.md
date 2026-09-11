# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Corrección Crítica de Error Fatal en `formulario.php` (Completado y Verificado)

- **User Request:**
  - *"Formulario se rompio completamente, arreglalo"* (acompañado de captura donde la vista se corta abruptamente en el campo de precio).

- **Root Cause Identificada:**
  - En [views/pages/formulario_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/formulario_content.php#L158), al cargar la pantalla en modo nuevo registro (sin `?id=X`), `$currentItem['precio']`, `$currentItem['costo']` y `$currentItem['horas']` contenían cadenas vacías (`""`).
  - En PHP 8.x, ejecutar `number_format("", 2, '.', '')` lanza un error fatal irrecuperable de tipo `TypeError: number_format(): Argument #1 ($num) must be of type int|float, string given`.
  - Esto interrumpía el renderizado inmediatamente tras el símbolo `$` del precio de venta, impidiendo la generación del resto de campos, del dropzone de imágenes, de los botones de acción y del footer.

- **Solución Implementada:**
  - **1. Sanitización Robusta de Variables Numéricas:**
    - Se extrajeron y validaron numéricamente `$valPrecio`, `$valCosto`, `$valHoras`, `$valTamano` y `$valStock` con `is_numeric()`.
    - Los inputs en el formulario ahora imprimen el formato numérico si existe un valor válido (`$valPrecio !== null ? number_format($valPrecio, 2, '.', '') : ''`), o cadena vacía sin provocar errores fatales.
  - **2. Blindaje del Simulador Financiero en PHP:**
    - Se agregaron cálculos seguros de margen porcentual y retorno horario: si no hay datos financieros iniciales, se muestran estados base en espera (`$0.00 MXN`, `0.0%`, `Esperando precio y costo`, `Ingrese horas confeccionadas`).
  - **3. Verificación End-to-End en Navegador:**
    - Subagente de navegador ejecutado en `http://127.0.0.1:8000/formulario.php`.
    - Capturas registradas: la página se renderiza al 100% de inicio a fin.
    - Se probó la reactividad en vivo del simulador ingresando `precio: 350`, `costo: 80`, `horas: 3`: calculó en tiempo real ganancia bruta `$270.00 MXN`, margen `77.1%` (Margen Saludable) y retorno `$90.00 MXN/hr` (Remuneración Digna).
    - 0 errores en consola JavaScript. 100% sintaxis PHP válida (`php -l`).

- **Current Milestone:** Error fatal de formulario resuelto y simulador financiero 100% operativo.
- **Next Phase:** Fase 3 (Backend & Conexión PDO con Arquitectura Limpia en `src/` y controladores en `api/`) a la espera de la instrucción explícita del usuario.

