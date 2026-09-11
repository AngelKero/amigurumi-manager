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
- **Current Milestone: Paquete Integral de Completitud UI/UX y Base de Datos (COMPLETADO):**
  - **Gestión de Usuarios (`usuarios.php`):** Creado directorio de artesanos y equipo con tarjetas KPI, tabla responsiva con iniciales en avatar circular, badges de rol (`admin`, `artesano`, `asistente`), modal de creación (`modal_crear_usuario.php`) con validación de restricciones SQLite (`chk_usuarios_username`, min 6 caracteres en contraseña), aviso de protección referencial (`ON DELETE RESTRICT`) y módulo interactivo (`js/modules/users.js`). Navbar actualizado con enlace directo para artesanos.
  - **Ciclo de Vida de Amigurumis y Salvaguardas:**
    - `formulario.php?id=X`: Detección de `$isEditing`, pre-carga de atributos físicos, económicos e imagen para Dragón Ignis, Mini Suculenta y Ajolote, título dinámico ("Modificar Creación Artesanal"), badge de modo ("Modo: Edición #X") y botón de cancelación directa.
    - `modal_eliminar_amigurumi.php`: Diálogo con explicación explícita de `ON DELETE RESTRICT` (bloqueo si hay pedidos históricos) y limpieza de huérfanos con `unlink()` en `/uploads/`.
    - `detalle.php`: Barra contextual del artesano (`#artisanDetailToolbar`) con acciones directas ("Editar Creación", "Eliminar Pieza") y panel de métricas privadas del taller (`#artisanPrivateMetricsCard`) con costo de materiales, ganancia neta, margen % y retorno horario ($/hr).
    - `catalogo_content.php`: Componente de estado vacío escandinavo (`#emptyCatalogState`) con ilustración vectorial e interactividad de restablecimiento.
  - **Gestión Avanzada de Pedidos (`pedidos.php`):**
    - `modal_nuevo_pedido.php`: Registro de encargos directos/manuales con selección de pieza de catálogo, teléfono/WhatsApp, cantidad, fecha de compromiso, notas y cómputo de precio total en vivo.
    - Píldoras reactivas de estado (`Todos`, `Pendientes`, `En Proceso`, `Entregados`, `Cancelados`) con contadores dinámicos.
    - Acciones de cambio de estado en vivo en tabla y tarjetas móviles con recálculo automático de KPIs (`#kpiOrdersTotal`, `#kpiOrdersPendientes`, `#kpiOrdersProceso`, `#kpiOrdersIngresos`).
    - Búsqueda reactiva instantánea por cliente, producto, teléfono o ID con vista de estado vacío condicional.
- **Phase Gate Status:** Fase 2 y mejoras de completitud UI/UX 100% terminadas y verificadas. Sistema preparado para recibir la Fase 3 (Backend PDO & Endpoints) cuando el usuario lo determine.

