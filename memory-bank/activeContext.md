# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Erradicación Total de SVGs Inline en Vistas & Decoraciones de Fondo con Nubes Nórdicas (Completado y Verificado)

- **User Request:**
  - Erradicar todos los SVGs hardcodeados restantes en las vistas (`views/`), reemplazándolos por recursos mejorados en `assets/svg/` consumidos mediante `svg('...')`.
  - Configurar las decoraciones de fondo (`background_decorations.php`) para utilizar exclusivamente las nubes artesanales `nube-ovillo.svg` y `nube-pespunte.svg`, retirando elementos secundarios para lograr una atmósfera limpia y serena.

- **Actions Executed:**
  - **1. Erradicación Total de `<svg>` Inline en `views/` (0 matches restantes en todo el proyecto):**
    - [views/components/background_decorations.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/components/background_decorations.php): Eliminados los 6 SVGs hardcodeados y reemplazados por una composición fluida de 5 nubes flotantes usando `nube-ovillo` y `nube-pespunte`.
    - [views/components/product_card.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/components/product_card.php): Eliminado el SVG genérico hardcodeado de fallback y creado [assets/svg/decorations/craft-placeholder.svg](file:///Users/angelzaragoza/Desktop/proyecto-web/assets/svg/decorations/craft-placeholder.svg), renderizado con `svg('decorations/craft-placeholder', ['class' => 'card-product-img'])`.
    - [views/pages/catalogo_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/catalogo_content.php): Eliminado el fallback SVG inline del marco hero y reemplazado por `svg('dragon-ignis', ['class' => 'img-fluid hero-crafted-img'])`.
    - [views/components/navbar.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/components/navbar.php): Integrado `svg('decorations/corazon-lana')` en el logotipo de la marca.
    - [views/pages/detalle_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/detalle_content.php): Integrado `svg('badges/sello-taller')` en el avatar del sello de taller verificado.
    - [views/pages/formulario_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/formulario_content.php): Integrado `svg('decorations/nube-ovillo')` en el dropzone de subida de fotografías.
    - [views/pages/pedidos_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/pedidos_content.php): Integrado `svg('tools/cinta-metrica')` en el icono del banner del panel.
    - [views/pages/usuarios_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/usuarios_content.php): Integrado `svg('badges/garantia-autor')` en el icono del banner de usuarios.
  - **2. Ajuste de Estilos de Decoración de Fondo (`decorations.css`):**
    - Elevada la opacidad de los fondos a `0.65` (con fallback de `0.45` en móvil) para que las nubes con pespunte y ovillos reposando sean claramente perceptibles pero sutiles tras las tarjetas.
    - Dimensiones responsivas ampliadas (`280px` - `360px`, `max-width: 30vw`) con animación continua `floatDrift1` y `floatDrift2`.
  - **3. Verificación Visual en Navegador:**
    - Subagente de navegador ejecutado en `http://127.0.0.1:8000/index.php`.
    - Capturas registradas (`hero_upper_clouds_*.png`, `catalog_lower_clouds_*.png`): las nubes enmarcan el banner hero y el catálogo de forma armónica sin invadir el contenido.
    - 0 errores en la consola de JavaScript.
  - **4. Validación de Código:**
    - `grep -r "<svg" views/` $\rightarrow$ 0 coincidencias (limpieza al 100%).
    - `php -l` ejecutado en el 100% de los archivos PHP (0 errores de sintaxis).

- **Current Milestone:** Erradicación total de SVGs inline y fondos de nubes escandinavas 100% completado.
- **Next Phase:** Fase 3 (Backend & Conexión PDO con Arquitectura Limpia en `src/` y controladores en `api/`) a la espera de la instrucción explícita del usuario.
