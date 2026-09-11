# 🎨 Biblioteca de Assets Vectoriales SVG & Helper de Renderizado

## 1. Resumen y Propósito

Para dotar a **Crochet Manager** de una identidad visual artesanal, táctil y coherente con el sistema de diseño **"Algodón Nórdico"**, se ha implementado una biblioteca completa de gráficos vectoriales SVG hechos a mano, organizados archivo por archivo en una estructura limpia bajo `assets/svg/`.

Adicionalmente, bajo los principios de **Clean Code & Clean Architecture**, se ha desarrollado la utilidad `App\Utils\SvgHelper` en `src/Utils/SvgHelper.php` junto con funciones globales de acceso (`svg()` y `svg_url()`) que permiten renderizar o enlazar cualquier vector de manera inmediata, con inyección segura de atributos HTML, fusión de clases CSS y caché en memoria para alto rendimiento.

---

## 2. Estructura de Directorios

```
assets/
└── svg/
    ├── branding/           # Isotipos, logotipos, imagotipos e isologos oficiales
    │   ├── imagotipo-horizontal.svg
    │   ├── imagotipo-vertical.svg
    │   ├── isologo-medallon-garantia.svg
    │   ├── isologo-sello-taller.svg
    │   ├── isotipo-hebra-nordica.svg
    │   ├── isotipo-osito-crochet.svg
    │   ├── isotipo-ovillo-corazon.svg
    │   ├── logotipo-crochet-manager.svg
    │   └── logotipo-taller-artesanal.svg
    ├── piezas/             # Piezas de catálogo: amigurumis, prendas, bolsos y hogar
    │   ├── ajolote-pastel.svg
    │   ├── cardigan-granny.svg
    │   ├── dragon-ignis.svg
    │   ├── gatito-ovillo.svg
    │   ├── hongo-bosque.svg
    │   ├── medusa-magica.svg
    │   ├── mini-suculenta.svg
    │   ├── osito-nordico.svg
    │   ├── pinguino-bufanda.svg
    │   └── tote-bag.svg
    ├── tools/              # Herramientas de crochet y costura artesanal
    │   ├── boton-madera.svg
    │   ├── cinta-metrica.svg
    │   ├── ganchillo-crochet.svg
    │   ├── madeja-textil.svg
    │   ├── ovillo-lana.svg
    │   └── tijeras-artesanales.svg
    ├── badges/             # Sellos de calidad, garantía y certificaciones
    │   ├── algodon-natural.svg
    │   ├── garantia-autor.svg
    │   └── sello-taller.svg
    └── decorations/        # Elementos decorativos y estados del sistema
        ├── aguja-hebra.svg
        ├── corazon-lana.svg
        ├── empty-basket.svg
        ├── nube-ovillo.svg
        └── nube-pespunte.svg
```

---

## 3. Catálogo de Recursos Vectoriales (31 Archivos)

### 3.1. Amigurumis de Catálogo (`assets/svg/amigurumis/`)

| Slug / Nombre | ViewBox | Temática / Descripción |
| :--- | :---: | :--- |
| `dragon-ignis` | `0 0 500 500` | Dragón de fantasía con cuernos de madera, escamas tejidas en relieve y etiqueta de algodón mercerizado. |
| `mini-suculenta` | `0 0 400 300` | Planta suculenta en maceta de terracota tejida con roseta botánica en hilazas verdes. |
| `ajolote-pastel` | `0 0 400 300` | Ajolote rosa en hilo chenille velvet extra suave con branquias aterciopeladas y pespunte frontal. |
| `osito-nordico` | `0 0 400 300` | Osito artesanal en lana miel con bufanda verde pino hilvanada. |
| `gatito-ovillo` | `0 0 400 300` | Gatito tierno jugando con una madeja de estambre ciruela. |
| `medusa-magica` | `0 0 400 300` | Medusa marina con tentáculos ondulados en tonos lavanda y menta. |
| `pinguino-bufanda` | `0 0 400 300` | Pingüino invernal con gorrito de pompón y bufanda a rayas. |
| `hongo-bosque` | `0 0 400 300` | Hongo del bosque con sombrero rojo terracota y motas bordadas. |

### 3.2. Herramientas Artesanales (`assets/svg/tools/`)

| Slug / Nombre | ViewBox | Temática / Descripción |
| :--- | :---: | :--- |
| `ovillo-lana` | `0 0 160 160` | Ovillo esférico de hilaza con texturas de punto y hebra curva suelta. |
| `ganchillo-crochet` | `0 0 160 160` | Ganchillo ergonómico con mango de madera pulida y punta dorada. |
| `tijeras-artesanales` | `0 0 160 160` | Tijeras vintage estilo garza / cigüeña con acabado dorado antiguo. |
| `cinta-metrica` | `0 0 160 160` | Cinta de sastre enrollada en caracol con marcas milimétricas. |
| `boton-madera` | `0 0 160 160` | Botón rústico de 4 orificios con pespunte en cruz de hilo ciruela. |
| `madeja-textil` | `0 0 160 160` | Madeja trenzada tradicional con banda de papel kraft rotulada. |

### 3.3. Sellos y Badges (`assets/svg/badges/`)

| Slug / Nombre | ViewBox | Temática / Descripción |
| :--- | :---: | :--- |
| `sello-taller` | `0 0 160 160` | Sello circular de taller oficial verificado con madeja central y borde dentado. |
| `algodon-natural` | `0 0 160 160` | Flor de algodón orgánico con cápsula vegetal y anillo de calidad. |
| `garantia-autor` | `0 0 160 160` | Insignia de garantía de autor con escudo y estrella artesanal. |

### 3.4. Decoraciones y Estados (`assets/svg/decorations/`)

| Slug / Nombre | ViewBox | Temática / Descripción |
| :--- | :---: | :--- |
| `nube-pespunte` | `0 0 300 180` | Nube acolchada con línea interior de pespunte discontinuo. |
| `nube-ovillo` | `0 0 320 200` | Nube acolchada con ovillo reposando y destellos dorados. |
| `empty-basket` | `0 0 240 220` | Cesta de mimbre tejida con ovillos de lana, ganchillo y etiqueta colgante (utilizada para estado vacío del catálogo). |
| `aguja-hebra` | `0 0 200 120` | Aguja de costura con hebra ondulada y cruces de pespunte decorativas. |
| `corazon-lana` | `0 0 160 160` | Corazón tejido a crochet con costuras visibles y relieve textil. |

### 3.5. Identidad Visual y Branding (`assets/svg/branding/`)

| Slug / Nombre | Arquetipo | ViewBox | Temática / Aplicación |
| :--- | :---: | :---: | :--- |
| `isotipo-ovillo-corazon` | **Isotipo** | `0 0 100 100` | Ovillo de hilaza con hebra continua que dibuja un corazón y gancho cruzado. Ideal para favicons y avatares. |
| `isotipo-osito-crochet` | **Isotipo** | `0 0 100 100` | Rostro de osito en crochet bordado con pespunte perimetral y mejillas de hilo rosa. |
| `isotipo-hebra-nordica` | **Isotipo** | `0 0 100 100` | Nube de algodón peinado con aguja botánica y lazo infinito dorado. |
| `logotipo-crochet-manager` | **Logotipo** | `0 0 320 75` | Wordmark estilizado en Fraunces y Outfit con hebra pespunteada y puntos de costura. |
| `logotipo-taller-artesanal` | **Logotipo** | `0 0 320 65` | Marca verbal institucional "CROCHET TALLER & ERP" enmarcada en cinta textil con ojales de costura. |
| `imagotipo-horizontal` | **Imagotipo** | `0 0 390 90` | Símbolo de ovillo en píldora a la izquierda + Wordmark "Crochet Manager" y bajada Micro-ERP a la derecha. Ideal para el `navbar`. |
| `imagotipo-vertical` | **Imagotipo** | `0 0 220 200` | Símbolo de ovillo centrado arriba + Wordmark "Crochet MANAGER" y píldora de categoría abajo. Ideal para modales y portadas. |
| `isologo-sello-taller` | **Isologo** | `0 0 160 160` | Sello circular indivisible con texto perimetral "CROCHET MANAGER" sobre trayectoria SVG, año de fundación y emblema universal de crochet central. |
| `isologo-medallon-garantia` | **Isologo** | `0 0 160 160` | Medallón festoneado con listón de garantía artesanal "CROCHET MANAGER - 100% HECHO A MANO". |

---

## 4. Metodología de Implementación: `SvgHelper` & Helpers Globales

### 4.1. Filosofía de Diseño
1. **Zero Bloat:** 100% PHP nativo, sin dependencias externas ni compiladores pesados.
2. **Caché en Memoria:** Las lecturas de disco se cachean en un array estático durante el ciclo de vida del request, evitando lecturas redundantes en rejillas de productos.
3. **Resolución Inteligente de Nombres:** Permite invocar un asset por su slug simple (`svg('dragon-ignis')`) o por su ruta relativa explícita (`svg('piezas/dragon-ignis')` o `svg('creaciones/dragon-ignis')`), con o sin extensión `.svg`.
4. **Inyección Segura de Atributos:** Permite añadir o sobrescribir `class`, `width`, `height`, `id`, `style`, `aria-hidden`, etc., fusionando clases CSS de forma no destructiva.
5. **Tolerancia a Fallos:** Si un archivo no existe, genera un comentario HTML legible (`<!-- [SvgHelper] Archivo SVG no encontrado: "..." -->`) en lugar de arrojar una excepción fatal.

### 4.2. Especificación de la API

```php
// 1. Renderizado inline directo en PHP
<?= svg(string $name, array $attributes = []): string ?>

// 2. Obtención de URL web para <img src="..."> o background-image CSS
<?= svg_url(string $name): string ?>
```

---

## 5. Ejemplos Prácticos de Uso

### 5.1. Renderizado Inline Básico
```php
<!-- Busca automáticamente en piezas/, creaciones/, tools/, badges/, decorations/ -->
<?= svg('dragon-ignis') ?>
```

### 5.2. Personalización de Clases y Dimensiones
```php
<?= svg('mini-suculenta', [
    'class' => 'card-product-img shadow-sm',
    'width' => 320,
    'height' => 240,
    'id' => 'heroProductVector'
]) ?>
```

### 5.3. Iconos y Herramientas Decorativas
```php
<button class="btn btn-craft-outline">
  <?= svg('tools/tijeras-artesanales', ['width' => 20, 'height' => 20, 'class' => 'me-2']) ?>
  <span>Cortar Hilo</span>
</button>
```

### 5.4. Estado Vacío en Catálogo
```php
<div id="emptyCatalogState" class="text-center py-5">
  <?= svg('empty-basket', ['width' => 140, 'height' => 130, 'class' => 'mx-auto mb-3']) ?>
  <h4>No se encontraron piezas artesanales</h4>
</div>
```

### 5.5. Uso como URL Estática (`<img src="...">` o CSS)
```php
<img src="<?= svg_url('dragon-ignis') ?>" alt="Dragón Ignis" class="img-fluid">
```

---

## 6. Métodos Avanzados de `App\Utils\SvgHelper`

| Método Estático | Retorno | Descripción |
| :--- | :---: | :--- |
| `SvgHelper::render($name, $attrs)` | `string` | Carga el SVG, inyecta atributos y retorna la cadena final. |
| `SvgHelper::url($name)` | `string` | Resuelve la ruta relativa para el navegador web. |
| `SvgHelper::exists($name)` | `bool` | Comprueba si un recurso SVG existe físicamente. |
| `SvgHelper::getPath($name)` | `?string` | Retorna la ruta física absoluta en el servidor. |
| `SvgHelper::listAll()` | `array` | Retorna el árbol completo de assets indexado por categoría. |
| `SvgHelper::clearCache()` | `void` | Vacía la memoria caché estática. |
