# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Rediseño de Navegación del Header, Panel Administrativo con Sidebar Izquierdo y Vista CRUD de Amigurumis (Completado y Verificado)

- **User Request:**
  - *"No es nesesario el boton de catalogo, y el panel de artesano lo debes de quitar del header, para ingresar al panel debes de dar click donde dice tu nombre (@admin (Artesano Titular) ), dentro del panel debe de haber un menu lateral izquierdo con todo lo que se puede administrar, y por cierto falta una pantalla de la lista de amigurimis con posibilidad de hacer todas las operaciones crud y mas cosas (para eso lee la base de datos)"*

- **Resultados de Implementación:**
  1. **Depuración del Header (`views/components/navbar.php`):**
     - Enlace `Catálogo` eliminado de la barra superior (el logotipo de marca enlaza limpiamente a la raíz).
     - Dropdown `Panel del Artesano` eliminado de la barra superior.
     - Badge `@admin (Artesano Titular)` convertido en enlace interactivo directo al panel (`amigurumis.php`).
  2. **Menú Lateral Izquierdo Unificado (`views/components/panel_sidebar.php`):**
     - Integrado automáticamente en todas las vistas de administración (`amigurumis.php`, `pedidos.php`, `usuarios.php`, `formulario.php`) a través de `views/layouts/main.php` en rejilla responsiva de 2 columnas (`col-lg-3` sidebar / `col-lg-9` contenido).
     - Incluye tarjeta de perfil del artesano, enlaces activos con pespunte y badges de conteo (`Inventario & Piezas`, `Nuevo Amigurumi`, `Control Pedidos`, `Equipo y Usuarios`, `Simulador Margen`), botón de retorno a tienda pública y cierre de sesión.
  3. **Nueva Pantalla Administrativa de Amigurumis (`amigurumis.php` & `views/pages/amigurumis_content.php`):**
     - Basada fielmente en el esquema relacional DDL de la base de datos (`seed.sql`).
     - **Tarjetas KPI Superiores:** Modelos registrados (3), Unidades en almacén (16), Valor total de inventario ($3,960.00 MXN) e Inversión en insumos ($1,020.00 MXN).
     - **Barra de Búsqueda y Filtrado:** Búsqueda en vivo (nombre, material, descripción), categoría, estado de stock (en stock, bajo stock, agotado, sobre encargo), artesano autor y ordenación multieje.
     - **Tabla Administrativa (`table-artisan-amigurumis`):**
       - Miniatura con marco pespunteado, nombre en `Fraunces`, categoría textil y medidas (`tamano_cm`).
       - Desglose económico: Precio venta, costo materiales, margen neto monetario ($), margen porcentual (%) y tasa de retorno horario ($/hr).
       - Ajuste rápido de existencias in-situ (`+` / `-`) que recalcula stock, badges de estado y KPIs en vivo.
       - Modalidad de encargo con distintivo `es_sobre_encargo`.
       - Artesano creador responsable.
       - Acciones CRUD: Modal de Ficha Técnica detallada (`modal_inspect_amigurumi.php`), edición directa (`formulario.php?id=X`) y eliminación con salvaguarda de clave foránea (`modal_eliminar_amigurumi.php`).
  4. **Módulos Frontend y Estilos ITCSS:**
     - Creado `src/js/modules/amigurumis.js` e inicializado en `src/js/main.js`.
     - Actualizado `src/js/modules/auth.js` para redirección directa al panel.
     - Creados `src/css/04-components/sidebar.css` y `src/css/04-components/amigurumis.css`, registrados en `src/css/styles.css`.

- **Verificaciones Ejecutadas:**
  - `php -l` limpio en todas las vistas y controladores (0 errores sintácticos).
  - `node --check` limpio en todos los módulos JS (0 errores).
  - Servidor HTTP probado vía curl: `index.php`, `amigurumis.php`, `pedidos.php`, `usuarios.php`, `formulario.php` devuelven HTTP 200 OK y renderizado completo.

