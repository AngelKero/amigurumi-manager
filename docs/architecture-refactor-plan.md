# Plan Maestro de Arquitectura y Refactorización: Fullstack Limpio y Modular (Frontend & Backend)

**Proyecto:** Amigurumi Micro-ERP & Catalog  
**Guía Metodológica:** `clean-code-architect` (Principios SOLID, Clean Architecture de Robert C. Martin, Component-Based UI, Modular CSS y ES Modules)  
**Fecha:** Septiembre 2026  
**Estado:** Propuesta de Arquitectura Integral (Pendiente de Aprobación de Usuario)

---

## 1. Fundamentos y Necesidad de Refactorización

Tras la auditoría guiada por la habilidad de agente **`clean-code-architect`**, identificamos que tanto el **Backend** como el **Frontend** requerían erradicar sus monolitos para alcanzar un estándar profesional de mantenibilidad:

1. **Monolito de Vistas HTML:** Las vistas actuales (`index.html`, `detalle.html`, `formulario.html`, `pedidos.html`) duplican el 40% de su estructura (encabezados `<head>`, tipografías Google Fonts, Bootstrap CDN, la barra de navegación `navbar-craft`, el modal de login, las decoraciones de nubes/ovillos de fondo y el pie de página).
2. **Monolito de Hojas de Estilo (`css/styles.css`):** Un archivo masivo de más de 1,300 líneas donde conviven variables de diseño, animaciones `@keyframes`, estilos de layout, tarjetas, botones, tablas, formularios y sobreescrituras.
3. **Monolito de JavaScript (`js/app.js`):** Un único archivo procedural de más de 500 líneas con 7 responsabilidades distintas (simulador de márgenes, drag & drop de imágenes, stepper de cantidad, filtrado de catálogo, guardias de stock, inspección de pedidos y autenticación modal).

---

## 2. Arquitectura Frontend: Sistema de Componentes y Vistas PHP

Aprovechando que el entorno soporta PHP nativo, transformaremos las vistas estáticas HTML en un **Sistema de Componentes Reutilizables** con cero dependencias externas (utilizando la capacidad nativa de PHP como motor de plantillas).

### A. Estructura de Componentes y Vistas (`views/`)

```
views/
├── layouts/
│   └── main.php                    # Layout maestro: <head>, estilos CSS, decoraciones de fondo, modals y scripts
├── components/
│   ├── navbar.php                  # Barra de navegación artesanal con estado de sesión dinámico
│   ├── footer.php                  # Pie de página unificado del Micro-ERP
│   ├── background_decorations.php  # Nubes, ovillos y ganchillos SVG flotantes
│   ├── modal_login.php             # Diálogo modal de autenticación de artesanos
│   ├── modal_checkout.php          # Diálogo modal de compra pública con stepper acotado
│   ├── modal_cancel_order.php      # Diálogo con advertencia de restitución física de stock
│   ├── modal_inspect_order.php     # Diálogo de inspección técnica de encargos
│   └── product_card.php            # Componente de tarjeta de producto con costura perimetral
└── pages/
    ├── catalogo_content.php        # Contenido específico del catálogo y hero banner
    ├── detalle_content.php         # Ficha técnica, galería paspartú y relato artesanal
    ├── formulario_content.php      # Formulario de alta/edición y simulador de rentabilidad
    └── pedidos_content.php         # Tablero kanban/tabla de pedidos y tarjetas móviles
```

### B. Ventajas de esta Arquitectura de Vistas:
- **Cero Duplicación de Código (DRY):** Cualquier mejora en el Header, en las nubes flotantes o en los modales se realiza en un único archivo (`views/components/navbar.php`) y se refleja automáticamente en todas las páginas.
- **Vistas Principales Limpias:** Las páginas raíz (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`) se reducen a archivos de 15 a 25 líneas que únicamente definen variables de página (título, descripción, scripts requeridos) e invocan el layout maestro.

---

## 3. Arquitectura CSS Modular (Desglose de `styles.css`)

Dividiremos el monolito de estilos siguiendo una arquitectura modular basada en el estándar ITCSS / SMACSS:

```
css/
├── styles.css                      # Master bundle que orquesta e importa los módulos
├── 01-settings/
│   └── variables.css               # Paleta Algodón Nórdico, tipografías, elevación y radios
├── 02-base/
│   ├── reset.css                   # Resets base, estructura flex para footer sticky
│   ├── typography.css              # Títulos Outfit, cuerpo Plus Jakarta Sans y monoespaciados
│   └── overrides.css               # Erradicación estricta del azul eléctrico de Bootstrap
├── 03-animations/
│   └── keyframes.css               # @keyframes floatSoft, floatGentle, pulseGlow, floatDrift
└── 04-components/
    ├── navbar.css                  # .navbar-craft, .brand-craft-badge, .badge-artisan-seal, .btn-craft-logout
    ├── hero.css                    # .hero-cloud, .hero-cloud-seam, .hero-photo-stitched-frame, .trust-chip
    ├── cards.css                   # .card-product, .card-stitched, .badge-textile-tag, .badge-stock
    ├── buttons.css                 # .btn-craft-primary, .btn-craft-stitched, .btn-craft-outline-stitched
    ├── filters.css                 # .filter-station-card, .btn-chip-textile, .input-craft-pill
    ├── detail.css                  # .breadcrumb-craft-ribbon, .product-photo-stitched-frame, .thumb-textile-item, .table-craft-specs, .story-quote-craft
    ├── forms.css                   # .upload-dropzone, .sticky-margin-card, .margin-feedback-pill
    ├── tables.css                  # .table-craft, .order-card-mobile, badges de estado de pedido
    ├── modals.css                  # .modal-content-stitched, .qty-stepper, .lead-time-notice
    └── decorations.css             # .cloud-yarn-bg-decorations y posicionamiento de SVGs
```

---

## 4. Arquitectura JavaScript Modular (ES Modules)

Reemplazaremos el archivo procedural `app.js` por módulos ES6 nativos con alcance encapsulado (`import` / `export`), ejecutados mediante `<script type="module" src="js/main.js"></script>`:

```
js/
├── main.js                         # Orquestador principal y despachador según la vista activa
└── modules/
    ├── auth.js                     # Gestión de sesión, modal de login y logout reactivo
    ├── catalog.js                  # Filtrado en vivo, chips textiles y contador dinámico
    ├── detail.js                   # Miniaturas interactivas, guardia out-of-stock y simulador UI/UX
    ├── checkout.js                 # Stepper de cantidad acotado por stock y cálculo de total
    ├── margin-calculator.js        # Simulador dual en tiempo real (% utilidad y retorno $/hora)
    ├── dropzone.js                 # Drag & drop de fotos, vista previa y validación MIME
    └── orders.js                   # Modales de restitución física de stock e inspección
```

---

## 5. Arquitectura Backend (PHP Puro / SOLID & Repository Pattern)

Para que el backend sea simétrico y mantenga la misma elegancia modular, implementamos la separación en capas en `src/`:

```
src/
├── .htaccess                       # Deny from all (código protegido del acceso web directo)
├── bootstrap.php                   # Autocargador PSR-4 nativo (spl_autoload_register)
├── Core/
│   ├── Autoloader.php              # Mapeo de namespaces a rutas físicas sin Composer
│   └── Response.php                # Emisor inmutable de respuestas JSON y cabeceras HTTP
├── Database/
│   └── Database.php                # Singleton PDO SQLite (PRAGMA foreign_keys = ON; ERRMODE_EXCEPTION)
├── Middleware/
│   └── AuthGuard.php               # Guardián de sesiones y roles ('admin', 'artesano')
├── Repositories/                   # CAPA DAO / PERSISTENCIA (100% del SQL aislado aquí)
│   ├── AmigurumiRepository.php     # CRUD de catálogo, conteo de stock y filtros
│   ├── PedidoRepository.php        # Transacción atómica de pedidos y restitución de inventario
│   └── UsuarioRepository.php       # Gestión de credenciales y usuarios del sistema
└── Services/                       # REGLAS DE NEGOCIO Y DOMINIO
    ├── AuthService.php             # password_verify(), hashing y tokens de sesión segura
    ├── AmigurumiService.php        # Validación de subida de imágenes y borrado físico con unlink()
    └── PedidoService.php           # Verificación de precios de servidor (anti-spoofing)
```

---

## 6. Directorio Completo del Proyecto Refactorizado

```
proyecto-web/
├── .agents/                        # Reglas y habilidades de los agentes AI
│   ├── rules/
│   │   ├── general.md              # Flujo iterativo y phase gates
│   │   └── ui-ux-design-system.md  # Sistema de diseño "Algodón Nórdico"
│   └── skills/
│       ├── clean-code-architect.md # Skill canónica de Clean Code & Architecture
│       └── ui-ux-reviewer/         # Habilidad de auditoría de interfaces
├── docs/ (symlinked to .docs/)     # Especificaciones de arquitectura
│   ├── architecture-refactor-plan.md # Este documento maestro
│   ├── api-design.md
│   ├── database-schema.md
│   ├── database-testing.md
│   └── archive/
│       └── wireframes.html         # Prototipo inicial archivado (limpieza de raíz)
├── database/                       # Almacenamiento SQLite protegido (.htaccess)
│   ├── .htaccess                   # Deny from all
│   ├── seed.sql                    # Esquema DDL y mock data
│   └── database.sqlite             # Base de datos física inicializada
├── uploads/                        # Imágenes físicas de creaciones
│   └── .gitkeep
├── views/                          # SISTEMA DE VISTAS Y COMPONENTES PHP
│   ├── layouts/main.php
│   ├── components/                 # navbar, footer, background_decorations, modals, product_card
│   └── pages/                      # catalogo_content, detalle_content, formulario_content, pedidos_content
├── src/                            # BACKEND LIMPIO PROTEGIDO (Core, Database, Repositories, Services)
├── api/                            # CONTROLADORES HTTP LIVIANOS (auth, amigurumis, pedidos, usuarios)
├── css/                            # CSS MODULAR (settings, base, animations, components)
├── js/                             # JS MODULAR (main.js, modules/*)
├── index.php                       # Punto de entrada público: Catálogo
├── detalle.php                     # Punto de entrada público: Detalle
├── formulario.php                  # Punto de entrada artesano: Formulario
├── pedidos.php                     # Punto de entrada artesano: Pedidos
├── setup.php                       # Inicializador CLI de la base de datos
├── .htaccess                       # Reglas de protección Apache en raíz
├── .gitignore                      # Exclusiones de control de versiones
└── README.md                       # Documentación de arranque y ejecución local
```

---

## 7. Plan de Transición Paso a Paso

1. **Paso A (Limpieza de Raíz):** Mover `wireframes.html` a `docs/archive/wireframes.html`.
2. **Paso B (Modularización de CSS):** Crear la estructura `css/01-settings/`, `css/02-base/`, `css/03-animations/`, `css/04-components/` y ensamblar el orquestador `css/styles.css`.
3. **Paso C (Modularización de JS):** Dividir `js/app.js` en módulos independientes dentro de `js/modules/` y coordinar en `js/main.js`.
4. **Paso D (Componentes de Vistas PHP):** Crear `views/layouts/main.php`, los componentes reutilizables en `views/components/` y transformar las vistas raíz en `index.php`, `detalle.php`, `formulario.php`, `pedidos.php`.
5. **Paso E (Documentación README):** Crear `README.md` detallando el comando para iniciar el servidor local y el ciclo de vida del proyecto.
6. **Paso F (Aprobación y Pase a Fase 3):** Detenerse y presentar el frontend refactorizado antes de escribir una sola línea de lógica de negocio o conexión en el backend.

---

## 8. Verificación y Aprobación

*No se ha alterado ningún archivo de producción ni se ha movido código hasta recibir tu visto bueno.*
