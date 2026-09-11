# Active Context: Crochet Creations Micro-ERP & Catalog

## Completed Milestone: Renombrado Global de Dominio (Amigurumis -> Creaciones) en Base de Datos, Código y Vistas

- **User Request:**
  - *"tienes total libertad de modificar la bd y cambiar todos los nombres que hagan mencion a amigurumis, como aun no esta implementado datos reales ni conexiones no hay ningun problema, solo asegurate de documentar todo al final"*

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

