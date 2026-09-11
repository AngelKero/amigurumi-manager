# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Fullstack Architecture Plan & README Created
- **User Request:**
  - El frontend también debe ser refactorizado antes de proceder con el backend.
  - Diseñar las vistas en un sistema de componentes (en archivos PHP).
  - Modularizar el CSS y el JS (eliminar los monolitos de `styles.css` y `app.js`).
  - Planear la arquitectura completa del frontend antes de continuar con el backend.
  - Crear un `README.md` con las instrucciones para iniciar el servidor local con un solo comando.
- **Actions Executed:**
  - **Plan Integral de Arquitectura:** Actualizado [.docs/architecture-refactor-plan.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/architecture-refactor-plan.md) con:
    1. *Sistema de Vistas PHP:* `views/layouts/main.php`, componentes reutilizables en `views/components/` (`navbar.php`, `footer.php`, `background_decorations.php`, modales, `product_card.php`), y vistas de contenido en `views/pages/`.
    2. *Arquitectura CSS Modular:* Desglose de `styles.css` en capas (`01-settings/`, `02-base/`, `03-animations/`, `04-components/`).
    3. *Arquitectura JS Modular:* Desglose de `app.js` en ES Modules (`js/main.js` y `js/modules/auth.js`, `catalog.js`, `detail.js`, `checkout.js`, `margin-calculator.js`, `dropzone.js`, `orders.js`).
    4. *Arquitectura Backend:* `src/` modular (Core, Database Singleton, Repositories/DAO, Services, Middleware) y fachada de controladores en `api/`.
  - **Documentación README:** Creado [README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/README.md) con:
    - Comando de inicio inmediato: `php -S localhost:8000`.
    - Inicialización de BD: `php setup.php` (CLI-only).
    - Credenciales de prueba (`admin` / `admin123`).
    - Explicación de arquitectura y requerimientos.
- **Frontend Refactor Executed & Verified (Phase 2.5):**
  - Modularized PHP Views: `views/layouts/main.php`, components (`navbar.php`, `footer.php`, `background_decorations.php`, `product_card.php`, modales), page views (`views/pages/catalogo_content.php`, `detalle_content.php`, `formulario_content.php`, `pedidos_content.php`), and root entrypoints (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`).
  - Modularized ITCSS: Layered stylesheet in `css/01-settings/`, `02-base/`, `03-animations/`, `04-components/`, orchestrated via `css/styles.css`.
  - Modularized ES Modules: Domain-specific modules in `js/modules/` orchestrated via `js/main.js`.
  - Comprehensive Live Verification: Fully audited via automated `browser_subagent` recording all interactions (filter chips, bounded checkout stepper, detail view out-of-stock guard simulation, margin calculator live update, order inspection and cancel order stock restitution dialog).
- **Housekeeping Completed:** 
  - Antiguas vistas estáticas (`index.html`, `detalle.html`, `formulario.html`, `pedidos.html`) archivadas en [`docs/archive/`](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/archive).
  - Enlace simbólico duplicado `.docs` eliminado, dejando únicamente la carpeta canónica [`docs/`](file:///Users/angelzaragoza/Desktop/proyecto-web/docs).
- **Current Milestone: Resolución Integral de Tareas Pendientes del Informe de Brechas (COMPLETADO Y VERIFICADO):**
  - **1. Filtros Avanzados en Catálogo [2.2.D]:** Rango de presupuesto (Min/Max: `filterPriceMin`, `filterPriceMax`) y dropdown de selección de artesano (`filterArtisan`) integrados en `views/pages/catalogo_content.php` y reactivos en `js/modules/catalog.js` en sincronía con chips de categoría y buscador.
  - **2. Estandarización de Helpers Monetarios [2.3.3]:** Módulo `js/modules/currency.js` con conversión matemática exacta (`centsToPesos`, `pesosToCents`, `formatPesos`, `formatCents`, `formatCurrency`, `parseCurrency`) y sincronización simétrica con `src/Utils/CurrencyHelper.php`, aplicado a lo largo de `margin-calculator.js`, `checkout.js`, `orders.js` y `catalog.js`.
  - **3. Propuestas de Base de Datos y Sincronización [3]:** Sincronización integral del DDL en `database/seed.sql` (`es_sobre_encargo`, `cliente_contacto`, `estado_pago`) con sus restricciones CHECK SQLite, diccionarios de datos en `docs/database-schema.md` y `database-schema.es.md`, switch en `formulario_content.php`, enlaces directos a WhatsApp `https://wa.me/...` y badges de estado de cobro en `pedidos_content.php` y modales.
  - **4. Edición de Roles de Usuario [4]:** Modal interactivo `modal_editar_rol_usuario.php` registrado en `usuarios.php`, botones de edición en cada fila de `usuarios_content.php`, y lógica reactiva con salvaguarda para la cuenta raíz `#1` (`@admin`) y recálculo en vivo de tarjetas KPI en `js/modules/users.js`.
  - **Verificación en Navegador:** Validado exhaustivamente mediante `browser_subagent` registrando capturas de pantalla de edición de rol, salvaguarda de administrador, filtros de presupuesto y autoría, y creación de encargo manual con WhatsApp y anticipo del 50%.
- **Current Milestone: Reubicación de Activos Frontend (JS y CSS dentro de `src/`) (COMPLETADO Y VERIFICADO):**
  - Mover `css/` a `src/css/` y `js/` a `src/js/` preservando el historial con `git mv`.
  - Actualizadas rutas de carga en [views/layouts/main.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/layouts/main.php): `src/css/styles.css` y `src/js/main.js`.
  - Verificada la resolución de `@import` de ITCSS y los módulos nativos ES6 (`import ...`).
  - Limpieza de borradores redundantes (`src/js/utils/`).
  - Verificado en vivo en navegador con `0 errores` en consola y estilos / reactividad 100% operativos.
  - Sincronizados `README.md`, `docs/architecture-refactor-plan.md` y `memory-bank/`.
- **Next Phase:** Fase 3 (Backend & Conexión PDO con Arquitectura Limpia) pendiente de aprobación explícita del usuario.



