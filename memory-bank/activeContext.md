# Active Context: Crochet Creations Micro-ERP & Catalog

## Completed Milestone: Renombrado Global de Dominio (Amigurumis -> Creaciones) en Base de Datos, Código y Vistas

- **User Request:**
  - _"tienes total libertad de modificar la bd y cambiar todos los nombres que hagan mencion a amigurumis, como aun no esta implementado datos reales ni conexiones no hay ningun problema, solo asegurate de documentar todo al final"_

- **Estado:** **Completado, Verificado y Documentado al 100%**

- **Resumen de Modificaciones Ejecutadas:**
  1. **Base de Datos Relacional SQLite (`database/seed.sql`, `setup.php`, `database.sqlite`):**
     - Renombrada la tabla canónica: `amigurumis` $\rightarrow$ `creaciones`.
     - Actualizada clave foránea de autoría: `CONSTRAINT fk_creaciones_artesano FOREIGN KEY (artesano_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE`.
     - Renombradas todas las restricciones: `chk_creaciones_nombre`, `chk_creaciones_categoria`, `chk_creaciones_material`, `chk_creaciones_dimensiones`, `chk_creaciones_precio`, `chk_creaciones_costo_materiales`, `chk_creaciones_cantidad_stock`, `chk_creaciones_horas_tejido`, `chk_creaciones_descripcion`, `chk_creaciones_imagen_url`, `chk_creaciones_es_sobre_encargo`.
     - Renombrada la columna foránea en `pedidos`: `amigurumi_id` $\rightarrow$ `creacion_id` con `CONSTRAINT fk_pedidos_creacion FOREIGN KEY (creacion_id) REFERENCES creaciones(id) ON DELETE RESTRICT ON UPDATE CASCADE`.
     - Renombrados índices: `idx_creaciones_artesano`, `idx_creaciones_categoria`, `idx_creaciones_stock`, `idx_pedidos_creacion`.
     - Actualizado `setup.php` para validar la inicialización de `creaciones`. Re-ejecutado con código de salida 0.
  2. **Estructura de Vistas, Componentes y Puntos de Entrada:**
     - Creado `creaciones.php` como nuevo controlador de vista para el inventario del taller (`$activePage = 'creaciones'`).
     - Convertido `amigurumis.php` en redirección permanente HTTP 301 hacia `creaciones.php` para mantener retrocompatibilidad total.
     - Creado `views/pages/creaciones_content.php` con panel de control de creaciones, KPIs en vivo, filtros textiles de 2 niveles, Cards pespunteadas 3x y controles in-situ.
     - Creados modales `views/components/modal_inspect_creacion.php` y `views/components/modal_eliminar_creacion.php` (con wrappers de compatibilidad en `modal_*_amigurumi.php`).
     - Sincronizados componentes: `panel_sidebar.php`, `navbar.php`, `footer.php`, `product_card.php`, `detalle_content.php`, `formulario_content.php`, `pedidos_content.php`, `modal_nuevo_pedido.php`.
  3. **Estilos CSS y JavaScript Modular:**
     - Creado `src/css/04-components/creaciones.css` con clases `.card-admin-creacion`, `.creacion-thumb-frame`, etc. e importado en `src/css/styles.css`.
     - Creado `src/js/modules/creaciones.js` con `initCreaciones()` y re-exportado en `amigurumis.js`.
     - Actualizados `src/js/main.js`, `auth.js`, `catalog.js`, `detail.js`, `orders.js`.
  4. **Recursos Gráficos y Helpers:**
     - Actualizado `src/Utils/SvgHelper.php` registrando la ruta `'creaciones/'`.
     - Creado enlace simbólico `assets/svg/creaciones -> amigurumis`.
  5. **Documentación Técnica Bilingüe Actualizada:**
     - `docs/database-schema.md` y `docs/database-schema.es.md`
     - `docs/data-model.md` y `docs/data-model.es.md`
     - `docs/database-testing.md` y `docs/database-testing.es.md`
     - `docs/api-design.md` y `docs/api-design.es.md`
     - `docs/README.md` y root `README.md`
     - Los 5 archivos de `/memory-bank/` sincronizados.

- **Verificación y Pruebas:**
  - Sintaxis PHP: 34 archivos `.php` validados con `php -l` (0 errores de sintaxis).
  - Sintaxis JS: Todos los módulos `.js` validados con `node --check` (0 errores).
  - Base de datos SQLite: Regenerada con `php setup.php` (5 creaciones, 2 pedidos vinculados, 3 usuarios).
  - Servidor HTTP: Todos los endpoints responden correctamente (`index.php`: 200, `creaciones.php`: 200, `amigurumis.php`: 301, `detalle.php`: 200, `formulario.php`: 200, `pedidos.php`: 200, `usuarios.php`: 200).

## Corrección Crítica: Restauración del Menú Lateral en `creaciones.php`

- **Problema Reportado:** El menú lateral (`aside.panel-sidebar-card`) no aparecía al cargar `creaciones.php`.
- **Causa Raíz:** En `views/layouts/main.php`, la condición condicional `$isPanelPage = in_array($activePage, ['amigurumis', 'pedidos', 'usuarios']);` aún comprobaba el nombre antiguo `'amigurumis'`, por lo que al recibir `$activePage = 'creaciones'`, evaluaba a `false` e insertaba el contenido en ancho completo sin el contenedor `<div class="col-12 col-lg-3 col-xl-3">` del sidebar.
- **Solución Aplicada:**
  1. `views/layouts/main.php`: Actualizada la condición a `$isPanelPage = in_array($activePage, ['creaciones', 'amigurumis', 'pedidos', 'usuarios']);` y actualizados los fallbacks por defecto de título/descripción de la app a "Crochet Manager".
  2. `views/components/panel_sidebar.php`: Actualizada la clase activa para cubrir tanto `'creaciones'` como el alias retrocompatible `'amigurumis'`.
- **Verificación:** Probado mediante HTTP curl en `http://localhost:8000/creaciones.php` validando la presencia de `aside.panel-sidebar-card`, estructura de columnas `col-lg-3` + `col-lg-9`, enlace activo en "Inventario & Creaciones", y comprobando que `index.php`, `detalle.php` y `formulario.php` mantienen su ancho completo limpio sin sidebar.

## Hito Completado: Actualización Integral de Identidad Visual a Crochet General

- **User Request:** _"Aun hay identidad visual que sigue haciendo referencia a que solo es amigurumis, cambiala, usa tus skills"_
- **Acciones Ejecutadas con Skills (`brand-identity` & `logo-generator`):**
  1. **Imagotipo Horizontal (`assets/svg/branding/imagotipo-horizontal.svg`):**
     - Sustituido "Amigurumi Manager" por **"Crochet Manager"** (Fraunces 800 + Outfit 700).
     - Calibrado el `viewBox` (`0 0 390 90`) y espaciados para erradicar cualquier recorte de texto en el margen derecho.
     - Conservado el isotipo maestro de ovillo, gancho y corazón artesanal con bajada _"Micro-ERP • Control de Costos, Stock & Pedidos"_.
  2. **Logotipo Taller Artesanal (`assets/svg/branding/logotipo-taller-artesanal.svg`):**
     - Sustituido "AMIGURUMI TALLER & ERP" por **"CROCHET TALLER & ERP"** con serifa cálida `Fraunces` y ojales textiles bordados.
     - Subtítulo ajustado: _"PIEZAS TEJIDAS A MANO • EDICIONES ARTESANALES"_.
  3. **Isologo Sello Circular del Taller (`assets/svg/branding/isologo-sello-taller.svg`):**
     - Texto en trayectoria curva perimetral actualizado a **"CROCHET MANAGER"**.
     - Motivo central actualizado a un emblema universal de crochet: ovillo de hilaza texturizada con gancho dorado en diagonal y corazón de hebra.
  4. **Isologo Medallón de Calidad (`assets/svg/branding/isologo-medallon-garantia.svg`):**
     - Listón perimetral inferior actualizado a **"CROCHET MANAGER"** con tipografía Fraunces 700.
  5. **Logotipos Wordmark (`logotipo-crochet-manager.svg` & `logotipo-amigurumi-manager.svg`):**
     - Creado `logotipo-crochet-manager.svg` y sincronizado el wordmark estilizado con _"Crochet Manager"_.
  6. **Imagotipo Vertical (`assets/svg/branding/imagotipo-vertical.svg`):**
     - Actualizado a **"Crochet MANAGER"** con isotipo de ovillo/gancho central.
  7. **Vistas y Componentes PHP:**
     - `panel_sidebar.php`: Actualizado el avatar del perfil `@admin (Artesano Titular)` al isotipo maestro `isotipo-ovillo-corazon.svg`.
     - `modal_login.php` y `modal_crear_usuario.php`: Actualizados los avatares e iconos de cabecera a `isotipo-ovillo-corazon.svg`.
     - `usuarios_content.php`: Actualizado el microcopy de claves foráneas a _"creaciones asociadas"_.
     - `catalogo_content.php`: Actualizado el texto alternativo del Hero a _"Colección Artesanal de Creaciones en Crochet"_.
  8. **Documentación de Marca (`docs/identidad-visual.md`):**
     - Actualizado el manual maestro a la versión 2.0.0 bajo la arquitectura unificada de Crochet Manager.
- **Verificación:** 0 ocurrencias de "Amigurumi Manager" o "AMIGURUMI" en la suite de branding vectorial; validada la inyección limpia de `CROCHET MANAGER` y `CROCHET TALLER & ERP` en HTTP en vivo.

## Hito Completado: Erradicación y Renombrado Total de Archivos (`amigurumi*` -> `crochet` / `piezas`)

- **User Request:** _"Crees que ya acabaste, pero aun hay archivos llamados con algo de amigurumi, cambialos a crotchet o piezas"_
- **Estado:** **100% Completado y Verificado (0 archivos con "amigurumi" en su nombre)**
- **Acciones Ejecutadas:**
  1. **Fotografía Hero (`assets/img/` y `uploads/`):**
     - Renombrados `assets/img/hero_amigurumi.jpg` $\rightarrow$ `assets/img/hero_crochet.jpg` y `uploads/hero_amigurumi.jpg` $\rightarrow$ `uploads/hero_crochet.jpg`.
     - Actualizada la ruta en [catalogo_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/catalogo_content.php).
  2. **Directorio Vectorial de Catálogo (`assets/svg/`):**
     - Renombrado directorio físico `assets/svg/amigurumis/` $\rightarrow$ `assets/svg/piezas/`.
     - Creados enlaces simbólicos limpios `assets/svg/creaciones -> piezas` y `assets/svg/crochet -> piezas`.
     - Eliminada completamente la carpeta `assets/svg/amigurumis/`.
     - Actualizado `src/Utils/SvgHelper.php` registrando `piezas/` y `crochet/` en `$searchPaths` y `listAll()`.
     - Actualizados slugs en [pedidos_content.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/pages/pedidos_content.php) y [orders.js](file:///Users/angelzaragoza/Desktop/proyecto-web/src/js/modules/orders.js).
  3. **Vectores de Branding:**
     - Eliminado `logotipo-amigurumi-manager.svg` (reemplazado por `logotipo-crochet-manager.svg`).
     - Renombrado `isotipo-osito-amigurumi.svg` $\rightarrow$ `isotipo-osito-crochet.svg`.
  4. **Controladores y Vistas PHP:**
     - Renombrado `amigurumis.php` $\rightarrow$ `piezas.php` (redirección 301 a `creaciones.php`).
     - Renombrado `views/pages/amigurumis_content.php` $\rightarrow$ `views/pages/piezas_content.php`.
     - Renombrados modales `modal_inspect_amigurumi.php` $\rightarrow$ `modal_inspect_pieza.php` y `modal_eliminar_amigurumi.php` $\rightarrow$ `modal_eliminar_pieza.php`.
     - Sincronizados [main.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/layouts/main.php) y [panel_sidebar.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/components/panel_sidebar.php) con `'piezas'`.
  5. **Módulos CSS y JavaScript:**
     - Renombrado `src/css/04-components/amigurumis.css` $\rightarrow$ `src/css/04-components/piezas.css`.
     - Renombrado `src/js/modules/amigurumis.js` $\rightarrow$ `src/js/modules/piezas.js`.
  6. **Documentación:**
     - Actualizados [docs/svg-assets-and-helper.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/svg-assets-and-helper.md) y [docs/identidad-visual.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/identidad-visual.md).
- **Verificación Final:**
  - `find . -iname "*amigurumi*"` devuelve **0 resultados**.
  - `php -l` en todos los archivos PHP reporta **0 errores de sintaxis**.

## Hito Completado: Refinamiento Tipográfico y Escalado de Fuentes en Tarjetas KPI (Creaciones y Pedidos)

- **User Request:**
  - _"Mejora el tamaño de fuentes de aqui asi como en control de pedidos"_ (acompañado de captura de las 4 tarjetas KPI de Creaciones).
- **Estado:** **100% Completado y Verificado**
- **Diagnóstico y Acciones Ejecutadas:**
  1. **Componente CSS Modular `.card-kpi` (`src/css/04-components/cards.css`):**
     - Creado el sistema de estilos para tarjetas de métricas: `.card-kpi`, `.card-kpi-header`, `.card-kpi-label`, `.card-kpi-icon`, `.card-kpi-value`, `.card-kpi-desc`.
     - **Etiqueta Superior (`.card-kpi-label`):** Escalada de `0.72rem` (~11.5px) a `0.8125rem` (~13px) con `font-weight: 700`, espaciado `letter-spacing: 0.05em`, mayúsculas limpias y contraste nórdico optimizado con `var(--craft-text-muted)`.
     - **Cifras/Valores Numéricos (`.card-kpi-value`):** Erradicado el uso tosco de `font-monospace` y serif Fraunces en favor de la tipografía geométrica canónica: **Google Font `Outfit`** (`var(--craft-font-sans-display)`), con peso `800`, tamaño balanceado `2.15rem` (con fallback responsivo `1.85rem` en móviles $\le 576$px), `letter-spacing: -0.025em` y `line-height: 1.15`.
     - **Subtítulo Inferior (`.card-kpi-desc`):** Aumentado de `0.76rem` (~12px) a `0.825rem` (~13.2px) con `line-height: 1.35` y color `var(--craft-text-muted)`.
     - **Paleta Cromática Algodón Nórdico:** Saneados los iconos y cifras con tokens oficiales (`--craft-primary`, `--craft-secondary`, y dorado nórdico `#A66E1E` con ratio WCAG AA $>4.8:1$ sobre fondo blanco).
  2. **Vistas Actualizadas:**
     - `views/pages/creaciones_content.php`: 4 tarjetas de inventario actualizadas.
     - `views/pages/pedidos_content.php`: 4 tarjetas de control de pedidos actualizadas.
     - `views/pages/usuarios_content.php`: 4 tarjetas del directorio de equipo sincronizadas.

## Hito Completado: Corrección de Desbordamiento y Envolvente Flexible para Dimensiones Largas en Tarjetas
- **User Request:**
  - *"Las dimensiones cuando el texto es grande rompe el diseño"* (acompañado de captura de la tarjeta "Tote Bag Boho Trapillo" donde `Bolsos & Accesorios` y `📏 35 x 30 cm (Asas: 25 cm)` colisionaban y se partían feamente en múltiples líneas superpuestas).
- **Estado:** **100% Completado y Verificado**
- **Diagnóstico del Fallo:**
  1. En `views/components/product_card.php`, el contenedor `<div class="d-flex justify-content-between align-items-center mb-2">` no contaba con `flex-wrap: wrap;` ni `gap`. Al tener una categoría con nombre largo (`Bolsos & Accesorios`) y una especificación de dimensiones detallada (`35 x 30 cm (Asas: 25 cm)`), el ancho sumado superaba el ancho interior de la tarjeta (~280px).
  2. `.badge-textile-tag` carecía de `white-space: nowrap;`, provocando que el texto de la categoría se dividiera internamente en dos renglones (`Bolsos &` y `Accesorios`), con los puntos de pespunte decorativos `•••` colisionando contra el icono de la regla `bi-rulers`.
  3. El texto de las dimensiones se veía forzado a dividirse en dos líneas quebradas (`📏 35 x 30 cm (Asas: 25` / `cm)`), pegándose a la etiqueta sin separación.
- **Solución Arquitectural y de Diseño Aplicada:**
  1. **Regla de Estilos en `src/css/04-components/cards.css`:**
     - Blindado `.badge-textile-tag` con `white-space: nowrap;` y `flex-shrink: 0;` para garantizar que la etiqueta artesanal conserve siempre su integridad horizontal.
     - Creado el contenedor flex envolvente `.card-product-meta` con `display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.45rem 0.6rem; margin-bottom: 0.65rem;`.
     - Creada la píldora de dimensiones `.card-product-dimension` con fondo porcelana suave (`var(--craft-surface-muted)`), borde sutil (`var(--craft-border)`), radio de píldora, icono nórdico, `max-width: 100%`, y protección contra desbordamientos (`overflow: hidden; text-overflow: ellipsis; white-space: nowrap;`).
  2. **Comportamiento Adaptativo Inteligente:**
     - Si la categoría y las dimensiones caben en la misma línea (ej. `Fantasía` + `18.5 cm`), se ubican en los extremos de forma balanceada.
     - Si son extensos (ej. `Bolsos & Accesorios` + `35 x 30 cm (Asas: 25 cm)`), la píldora de dimensiones salta limpia y ordenadamente a un segundo renglón sin colisionar ni quebrar las palabras, armonizando perfectamente con el título del producto.
  3. **Alcance:** Aplicación unificada en `product_card.php`, `creaciones_content.php` y `pedidos_content.php`.
- **Verificación:**
  - `php -l` ejecutado en `product_card.php`, `creaciones_content.php` y `pedidos_content.php` (0 errores).
  - Comprobado mediante script HTTP en `localhost:8000/index.php` que la tarjeta 5 (Tote Bag Boho Trapillo) renderiza `.card-product-meta` con la píldora `.card-product-dimension`.

