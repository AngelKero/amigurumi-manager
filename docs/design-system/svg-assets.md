# Biblioteca de Assets Vectoriales SVG & Helper de Renderizado

[← Volver al Índice de Diseño](./README.md)

Para dotar a **Crochet Manager** de una identidad visual artesanal, táctil y coherente con el sistema de diseño **"Algodón Nórdico"**, se implementó una biblioteca completa de gráficos vectoriales SVG hechos a mano bajo `assets/svg/` y la clase `App\Utils\SvgHelper` en `app/Utils/SvgHelper.php`.

---

## 1. Estructura de Directorios

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

## 2. Helper de Renderizado PHP: `App\Utils\SvgHelper`

`App\Utils\SvgHelper` optimiza la inyección inline de SVGs en vistas PHP, permitiendo que las clases de color de CSS (`currentColor`, `var(--craft-primary)`) estilicen el vector dinámicamente.

### 2.1 Uso de Funciones Globales
```php
// Renderizado inline con atributos HTML personalizados
echo svg('branding/imagotipo-horizontal', [
    'class' => 'brand-logo-img',
    'height' => '38',
    'aria-label' => 'Crochet Manager'
]);

// Obtención de ruta pública para etiquetas <img> o background-image
$url = svg_url('piezas/dragon-ignis'); // Retorna: "assets/svg/piezas/dragon-ignis.svg"
```

### 2.2 Características Técnicas
- **Caché en Memoria:** Los contenidos de los archivos SVG se almacenan en un array estático para evitar lecturas de disco redundantes en la misma petición HTTP.
- **Inyección Segura:** Inyecta atributos `class`, `width`, `height`, `aria-hidden` y `id` sin corromper la estructura XML del vector.
- **Mapeo Automático de Rutas:** Si se omite el prefijo de carpeta, busca secuencialmente en `piezas/`, `branding/`, `tools/`, `badges/` y `decorations/`.
