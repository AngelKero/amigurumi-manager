# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Creación de Galería de SVGs en `assets/` y Sistema de Renderizado con Helper (Completado y Verificado)

- **User Request:**
  - Crear una colección completa de SVGs hardcodeados archivo por archivo para la temática del sitio (amigurumis, herramientas de crochet, ovillos, plantas, personajes, sellos artesanales, decoraciones).
  - Organizar una carpeta `assets/` donde se almacenen estos recursos de forma estructurada.
  - Implementar una metodología sencilla y limpia para utilizarlos con una simple función (ej. `svg('nombre', $attr)` / `SvgHelper`).

- **Actions Executed:**
  - **1. Creación de Estructura de Directorios en `assets/`:**
    - Creado `assets/svg/` con 4 subcarpetas temáticas:
      - `assets/svg/amigurumis/` (8 piezas/personajes)
      - `assets/svg/tools/` (6 herramientas artesanales)
      - `assets/svg/badges/` (3 sellos de calidad y garantía)
      - `assets/svg/decorations/` (5 elementos decorativos textiles y estados)
  - **2. Creación de los 22 Recursos Vectoriales SVG Hechos a Mano:**
    - **Amigurumis:** `dragon-ignis.svg`, `mini-suculenta.svg`, `ajolote-pastel.svg`, `osito-nordico.svg`, `gatito-ovillo.svg`, `medusa-magica.svg`, `pinguino-bufanda.svg`, `hongo-bosque.svg`.
    - **Herramientas:** `ovillo-lana.svg`, `ganchillo-crochet.svg`, `tijeras-artesanales.svg`, `cinta-metrica.svg`, `boton-madera.svg`, `madeja-textil.svg`.
    - **Badges:** `sello-taller.svg`, `algodon-natural.svg`, `garantia-autor.svg`.
    - **Decoraciones:** `nube-pespunte.svg`, `nube-ovillo.svg`, `empty-basket.svg`, `aguja-hebra.svg`, `corazon-lana.svg`.
  - **3. Implementación de `SvgHelper` y Funciones Globales en `src/Utils/SvgHelper.php`:**
    - Clase `App\Utils\SvgHelper` con resolución automática de rutas (busca por nombre simple o relativo en las 4 categorías), memoria caché estática (`static $cache = []`), e inyección no destructiva de atributos HTML (`class`, `width`, `height`, `id`, `style`, etc.).
    - Funciones globales `svg(string $name, array $attributes = []): string` y `svg_url(string $name): string`.
  - **4. Integración en Layout Maestro y Vistas:**
    - Requerido en [views/layouts/main.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/layouts/main.php).
    - En [views/pages/catalogo_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/catalogo_content.php): Reemplazados los enormes bloques inline por `svg('dragon-ignis')`, `svg('mini-suculenta')`, `svg('ajolote-pastel')` e implementado `svg('empty-basket')` en `#emptyCatalogState`.
    - En [views/pages/detalle_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/detalle_content.php): Integrado `svg('dragon-ignis', ['class' => 'card-product-img', 'id' => 'detailMainProductSvg'])`.
    - En [views/components/footer.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/components/footer.php): Integrado `svg('badges/sello-taller', ['width' => 28, 'height' => 28])`.
  - **5. Documentación Técnica:**
    - Creado [docs/svg-assets-and-helper.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/svg-assets-and-helper.md) con catálogo tabular completo, especificaciones de la API y ejemplos de uso.
    - Indexado en el mapa maestro de documentación [docs/README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/README.md).
  - **6. Verificación en Navegador:**
    - Verificado con `browser_subagent` en `http://127.0.0.1:8000/index.php` y `detalle.php?id=1`.
    - Capturas registradas: catálogo con ilustraciones nítidas, estado vacío con cesta de mimbre y detalle con paspartú acolchado. 0 errores en consola.
  - **7. Sintaxis PHP:**
    - `100%` de los archivos PHP validados con `php -l` (0 errores).

- **Current Milestone:** Fase 2 y Galería de Assets SVG 100% completada y verificada.
- **Next Phase:** Fase 3 (Backend & Conexión PDO con Arquitectura Limpia en `src/` y controladores en `api/`) a la espera de la instrucción explícita del usuario.
