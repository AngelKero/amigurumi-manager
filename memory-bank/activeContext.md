# Active Context: Crochet Creations Micro-ERP & Catalog

## Current Task: Generalización Exitosa a Crochet Integral (Prendas, Accesorios, Hogar, Bebé y Amigurumis)

- **User Request:**
  - *"Vamos a hacer modificaciones algo importantes pero no mucho, el sistema no se va a limitar unicamente a amigurumis, si no que a crotchet en general, por lo que hay que hacer un analisis de que cambios o cosas serian necesarias (por ejemplo en vez de tamaño manejar dimensiones). Usa todas tus skills o con la investigacion que vas a hacer en internet descarga nuevas que te sirvan a aterrizar el producto. En si en la base de datos seran las mismas tablas pero puede que con unos cuantos cambios"*

- **Alcance y Arquitectura Implementada:**
  1. **Evolución del Esquema Relacional (3 Tablas Preservadas):**
     - Se mantuvo intacta la estructura de 3 tablas (`usuarios`, `amigurumis`, `pedidos`) para evitar rupturas de claves foráneas.
     - Campo migrado: `tamano_cm REAL NOT NULL` -> `dimensiones TEXT NOT NULL` con restricción estricta `chk_amigurumis_dimensiones CHECK(length(trim(dimensiones)) >= 2 AND length(dimensiones) <= 100)`.
     - Permite almacenar medidas 2D/3D (ej. `'140 x 100 cm'`, `'35 x 30 cm'`), tallas de prendas (ej. `'Talla M (95 x 58 cm)'`) y alturas de amigurumis (ej. `'18.5 cm (Alto)'`).
  2. **Taxonomía Canónica de Crochet (5 Categorías Oficiales):**
     - `Amigurumis & Figuras`
     - `Prendas & Ropa` (Tops, Suéteres, Cardigans, Gorros)
     - `Bolsos & Accesorios` (Tote Bags, Monederos, Cuellos)
     - `Hogar & Decoración` (Mantas, Cojines, Suculentas, Tapices)
     - `Bebé & Infantil` (Mantas de apego, Zapatitos, Sonajeros)
  3. **Suite Vectorial Ampliada (`assets/svg/amigurumis/`):**
     - Creado `cardigan-granny.svg`: Ilustración artesanal de cardigan con cuadros de la abuela florales en paleta Algodón Nórdico y botones de madera.
     - Creado `tote-bag.svg`: Ilustración artesanal de tote bag con tejido espiga, textura de trapillo, chevrons decorativos y borla boho.
  4. **Semillas Realistas (`database/seed.sql` & `database.sqlite`):**
     - 5 creaciones representativas de la taxonomía:
       1. *Dragón Ignis* (Amigurumi, $450 MXN, 18.5 cm alto, stock 4)
       2. *Mini Suculenta en Maceta* (Hogar, $180 MXN, 10x8 cm, stock 12)
       3. *Ajolote Rosado Pastel* (Amigurumi, $320 MXN, 14x10 cm, bajo encargo)
       4. *Cardigan Granny Squares* (Prenda, $980 MXN, Talla M, stock 2)
       5. *Tote Bag Boho Trapillo* (Bolso, $380 MXN, 35x30 cm, stock 6)
  5. **Sincronización Total de Vistas y Componentes PHP:**
     - `views/components/product_card.php`: Soporte nativo para `$item['dimensiones']` con fallback.
     - `views/pages/catalogo_content.php`: Hero banner generalizado a crochet, 5 chips textiles de categoría, dropdown sincronizado y 5 items pre-cargados.
     - `views/pages/detalle_content.php`: Ficha técnica con fila *"Dimensiones / Talla"*, categoría actualizada y tiempos de entrega para prendas (5-10 días).
     - `views/pages/amigurumis_content.php`: Array de 5 items, cabecera de taller, selector de categorías y renderizado de `dimensiones` en cards 3x.
     - `views/pages/formulario_content.php`: Selector con las 5 categorías de crochet y campo de texto `#inputDimensiones`.
     - `views/components/modal_nuevo_pedido.php`: Selector enriquecido con las 5 piezas del catálogo.
     - `views/pages/pedidos_content.php`: Renderizado de dimensiones en tarjetas pespunteadas de pedidos.
  6. **Sincronización de JavaScript:**
     - `src/js/modules/amigurumis.js`: Extracción de `data-dimensiones` y formateo inteligente en modal de inspección.
     - `src/js/modules/catalog.js`: Filtrado reactivo por las 5 nuevas categorías.
     - `src/js/modules/orders.js`: Compatibilidad total con dimensiones de producto.
  7. **Documentación Técnica Bilingüe Actualizada:**
     - `docs/database-schema.md` y `docs/database-schema.es.md`
     - `docs/data-model.md` y `docs/data-model.es.md`
     - `docs/database-testing.md` y `docs/database-testing.es.md`
     - `docs/api-design.md` y `docs/api-design.es.md`
     - `memory-bank/techContext.md`, `productContext.md` y `progress.md`

- **Verificación y Pruebas:**
  - Sintaxis PHP (`php -l`) y JS (`node --check`) validadas con 0 errores en todos los archivos modificados.
  - Verificación HTTP en `http://localhost:8000/` (`index.php`, `detalle.php`, `amigurumis.php`, `formulario.php`, `pedidos.php`): Todos responden HTTP 200 OK.
  - Base de datos `database/database.sqlite` regenerada mediante `setup.php` con integridad referencial activa.
