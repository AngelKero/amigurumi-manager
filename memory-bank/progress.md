# Progress: Handmade Amigurumi Micro-ERP & Catalog

## Development Roadmap & Status

| Phase | Milestone | Status | Description |
| :--- | :--- | :--- | :--- |
| **Phase 0** | Relational Micro-ERP Architecture | **Completed & Signed Off** | Integrated User Management CRUD, Public Client Checkout, Image Uploads, Navbar Login Modal, and Orphaned File Cleanup across bilingual docs. |
| **Phase 1** | Database Implementation, Seeding & Testing | **Completed & Signed Off** | Implemented `database/seed.sql`, CLI-only `setup.php`, `database/database.sqlite`, Apache `.htaccess` protection, and `docs/database-testing.md`. |
| **Phase 2** | Layout & UI (Algodón Nórdico) | **Completed & Signed Off** | Built and refined initial layout with full craft detailing across Header, Banner, Filters, Detail View, and Modals. |
| **Phase 2.5 (Refactor)** | Frontend Componentization & Modularity | **Completed & Verified** | Refactored into pure PHP component system (`views/layouts/`, `views/components/`, `views/pages/`), entrypoints (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`), ITCSS layered CSS (`src/css/`), and native ES Modules (`src/js/modules/`). Verified via live browser subagent. |
| **Phase 2.6 (Completeness)** | UI/UX & Database Alignment Package | **Completed & Verified** | Added User/Artisan management (`usuarios.php`, `users.js`, `users.css`), Edit pre-population (`formulario.php?id=X`), Deletion modal with `ON DELETE RESTRICT` & `unlink()`, Empty state in catalog, and Manual commission orders with reactive filters, status updates, and live KPIs. |
| **Phase 2.7 (Gap Closure)** | 100% Cierre de Brechas UI/UX y BD | **Completed & Verified** | Completadas las 4 tareas de la auditoría: Filtros avanzados de catálogo (Min/Max precio y artesano), Helpers monetarios universales (`currency.js`), Sincronización completa de DDL en BD/Docs/UI (`cliente_contacto`, `estado_pago`, `es_sobre_encargo`), y Modal interactivo de modificación de rol de usuario con salvaguarda RBAC. |
| **Documentation & Skills** | Reestructuración Docs & Refinamiento `.agents/` | **Completed & Verified** | Reestructurada toda la carpeta `docs/` bajo Clean Architecture y creado `docs/README.md`. Convertido `clean-code-architect` a skill nativa autodescubrible (`.agents/skills/clean-code-architect/SKILL.md`), expandido el sistema de diseño con reglas 16–21 y actualizados los flujos en `.agents/rules/` y `.agents/workflows/`. |
| **Typography & Theme** | Instalación de Tipografía Display Artesanal | **Completed & Verified** | Integrada la fuente Google Font `Fraunces` para encabezados importantes en pantallas públicas (`index.php`, `detalle.php`, modal checkout, navbar), aportando calidez y personalidad artesanal nórdica. |
| **SVG Assets & Helper** | Galería Vectorial `assets/svg/` & `SvgHelper` | **Completed & Verified** | Creados 22 SVGs artesanales archivo por archivo en `assets/svg/` (amigurumis, tools, badges, decorations) e implementado `SvgHelper` con funciones globales `svg()` y `svg_url()` para renderizado inline de alto rendimiento. |
| **Hero Image & Showcase** | Fotografía Macro Hero de Gran Formato | **Completed & Verified** | Generada fotografía de estudio artesanal nórdica en `assets/img/hero_amigurumi.jpg` e integrada con marco pespunteado acolchado de 540px en `views/pages/catalogo_content.php`, abarcando un espacio protagónico. |
| **Pagination & Footer** | Estación Textil y Master Footer Nórdico | **Completed & Verified** | Rediseñados la paginación (`pagination.css`, `catalogo_content.php`) con botones pill bordados y contador reactivo, y el master footer (`footer.css`, `footer.php`) con 4 columnas, tarjeta de garantía nórdica y WhatsApp. |
| **Content Decoupling** | Desacoplamiento de Término "Nórdico" | **Completed & Verified** | Eliminadas todas las referencias a "nórdico" de productos, colecciones, talleres, materiales y garantías, preservándolo estrictamente como nombre del tema CSS. |
| **Panel & Amigurumis CRUD** | Rediseño de Navegación, Sidebar de Panel & Vista CRUD Amigurumis | **Completed & Verified** | Eliminado botón de catálogo y dropdown del header; acceso al panel mediante clic en el badge `@admin (Artesano Titular)`. Creado menú lateral izquierdo (`panel_sidebar.php`) con todas las áreas administrativas y pantalla completa de gestión de Amigurumis (`amigurumis.php`) con KPIs, búsqueda/filtros reactivos, ordenación multieje, ajuste de stock in-situ, toggle de modalidad de encargo, ficha técnica de inspección modal y eliminación con salvaguarda FK. |
| **Phase 3** | Backend & Connection (Clean Architecture) | **Awaiting Explicit Approval** | Implement `src/` modular backend (Autoloader, Singleton Database, Repositories/DAO, Services, Middleware) and `api/` controllers. |
| **Phase 4** | CRUD Operations & Fullstack Wiring | **Pending** | Wire modular frontend with clean backend endpoints via AJAX fetch. |
| **Phase 5** | Documentation & Final Delivery | **In Progress (README & Docs Hub created)** | Created `README.md` and master documentation hub `docs/README.md`. |

## What Works
- Memory Bank completely synchronized across all 5 core files.
- Clean Code Skill installed as auto-discoverable native skill at `.agents/skills/clean-code-architect/SKILL.md`.
- Master Documentation Hub created at `docs/README.md` with 5 logical domains indexing all 18 documentation files.
- Vector Asset Library (`assets/svg/`) with 22 handcrafted SVGs across 4 categories (`amigurumis`, `tools`, `badges`, `decorations`).
- Helper utility `App\Utils\SvgHelper` and global functions `svg($name, $attrs)` / `svg_url($name)` for simple, unified inline SVG rendering and URL generation.
- Views refactored (`catalogo_content.php`, `detalle_content.php`, `footer.php`) to use `svg()` helper instead of verbose inline strings.
- Wireframes (`docs/wireframes.md` and `docs/wireframes.es.md`) completely synchronized with PHP views, advanced filters, WhatsApp links, and user management.
- Multi-layered security: CLI-only `setup.php`, `database/database.sqlite` isolation, and Apache `.htaccess` access control.
- Phase 1 Database completed: `database/seed.sql`, `database/database.sqlite` initialized, verified via `docs/database-testing.md`.
- Phase 2 Layout & UI completed and visually verified in live browser.
- Phase 2.5 Frontend Refactor completed: Native PHP components, ITCSS modularity in `src/css/`, and ES Modules in `src/js/` verified end-to-end via automated browser subagent.
- Phase 2.6 & 2.7 UI/UX & Database Alignment Package completed: User Management (`usuarios.php`), Edit/Delete amigurumi safeguards, Empty states, Manual orders with live KPIs, universal currency helpers, and interactive user role modification.
- Agent Rules & Workflows in `.agents/` fully updated with learned project patterns (Rules 16 to 21).
- 100% of PHP and JS code validated via `php -l` and `node --check` with 0 errors.
- Estación de Paginación Textil (`pagination.css`) con botones pill pespunteados, contador dinámico reactivo en `catalog.js`, y Master Footer Nórdico (`footer.css`, `footer.php`) con 4 columnas, garantía artesanal y contacto WhatsApp.
- Pantalla de Gestión de Amigurumis (`amigurumis.php`, `amigurumis_content.php`, `amigurumis.js`, `amigurumis.css`) con estación de filtrado en 2 niveles (cero textos recortados, etiquetas claras y contador de piezas), cuadrícula responsiva de Cards 3x (`.card-admin-amigurumi`) con marco fotográfico acolchado, KPIs en vivo, ajuste de stock in-situ, toggle interactivo de modalidad (`es_sobre_encargo`), ficha técnica de inspección modal (`modal_inspect_amigurumi.php`) y modal de eliminación con salvaguarda `ON DELETE RESTRICT`.
- Header depurado (`navbar.php`): eliminado botón de catálogo y dropdown superfluo; acceso directo al panel administrativo mediante clic en `@admin (Artesano Titular)`.
- Ergonomía del Formulario y Simulador de Márgenes (`formulario_content.php`, `main.php`, `margin-calculator.js`, `forms.css`): eliminado el menú lateral del formulario para dedicar el 100% del contenedor al proceso de confección artesanal, integrando dos botones de navegación (`Volver al Inventario` superior con cinta breadcrumb e inferior en la botonera de acciones). Grid espacioso (`col-lg-7 col-xl-8` y `col-lg-5 col-xl-4`), micro-cards de métricas dedicadas para `Margen de Utilidad` y `Retorno por Hora`, y blindaje `text-nowrap` en cifras monetarias.


## What's Left to Build
- Phase 3: Modular Backend (`src/Core/`, `src/Database/`, `src/Middleware/`, `src/Services/`, `src/Repositories/`).
- Phase 4: Modular API endpoints (`api/`) and full AJAX integration.

## Known Issues / Blockers
- None. Frontend, SVG vector asset suite, and UI/UX alignment with SQLite database schema are 100% complete, tested, and thoroughly documented. Ready for Phase 3 when user instructs.
