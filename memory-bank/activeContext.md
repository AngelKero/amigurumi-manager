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
- **Phase Gate Status:** Halting and awaiting explicit user approval of the Frontend & Backend Architecture Plan before moving or refactoring code files.
