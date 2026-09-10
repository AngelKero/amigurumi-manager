# Amigurumi Micro-ERP: Low-Fidelity UI Wireframes & Layout Specification

This document defines the structural, low-fidelity blueprints and responsive grid architecture for the main views of the application before generating the production HTML files.

---

## 1. Responsive Grid & Breakpoint Guidelines

The layout follows Bootstrap 5's mobile-first responsive 12-column grid:
- **Mobile (`xs`, `< 576px`):** Full-width stacked columns (`col-12`), collapse toolbars into single-column vertical stacks, collapse navbar into hamburger toggler.
- **Tablet (`md`, `≥ 768px`):** 2-column card layouts (`col-md-6`), horizontal search and filter toolbar.
- **Desktop (`lg` / `xl`, `≥ 992px`):** 3-column catalog grids (`col-lg-4`), 2-column side-by-side detail and form splits (`col-lg-6` / `col-lg-8` + `col-lg-4` sticky margin summary).

---

## 2. Shared Header, Navbar & Artisan Panel Navigation

To preserve clear architectural boundaries between public customer browsing and private business management:
- **Public Navbar:** Contains **only** the brand logo, the public `[Catálogo]` link, and the `[👤 Iniciar Sesión]` trigger. Administrative actions (`Nuevo Amigurumi` and `Pedidos`) are strictly hidden from unauthenticated visitors.
- **Authenticated Navbar:** When logged in, the navbar dynamically unlocks the `[🛠️ Panel del Artesano ▼]` dropdown menu (linking to `Nuevo Amigurumi`, `Gestión de Pedidos`, and `Gestión de Usuarios`) alongside the artisan session badge and logout button.
- **Artisan Panel Toolbar:** Private views (`formulario.html` and `pedidos.html`) feature a dedicated artisan header bar with direct action buttons (`+ Nuevo Amigurumi` and `Gestión de Pedidos`).

### 2.1 ASCII Wireframe: Public Navbar (Guest / Prospective Customer)
```text
+----------------------------------------------------------------------------------------------------+
| [Logo] Amigurumi Manager   [Catálogo]                                          [👤 Iniciar Sesión] |
+----------------------------------------------------------------------------------------------------+
| Mobile (<768px): [Logo] Amigurumi Manager   [ ☰ ] -> (Expanded: Catálogo, Iniciar Sesión)          |
+----------------------------------------------------------------------------------------------------+
```

### 2.2 ASCII Wireframe: Authenticated Navbar (Logged-in Artisan / Admin)
```text
+----------------------------------------------------------------------------------------------------+
| [Logo] Amigurumi Manager   [Catálogo]  [🛠️ Panel del Artesano ▼]       [👤 @admin | 🚪 Cerrar Sesión] |
|                                        ├─ ➕ Nuevo Amigurumi                                       |
|                                        ├─ 📦 Gestión de Pedidos                                    |
|                                        └─ 👥 Gestión de Usuarios (Admin)                            |
+----------------------------------------------------------------------------------------------------+
```

### 2.3 ASCII Wireframe: Artisan Panel Internal Header Bar (Inside `formulario.html` & `pedidos.html`)
```text
+----------------------------------------------------------------------------------------------------+
| 🛠️ Panel de Administración del Artesano (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]         [📦 Gestión de Pedidos]                  [← Ver Catálogo Público]     |
+----------------------------------------------------------------------------------------------------+
```

### 2.4 ASCII Wireframe: Dynamic Login Modal (Triggered from Navbar)
```text
+-------------------------------------------------------------+
| Iniciar Sesión en Amigurumi ERP                         [X] |
+-------------------------------------------------------------+
| Ingrese sus credenciales para acceder al Panel del Artesano:|
|                                                             |
| Usuario:                                                    |
| [ admin                                                   ] |
|                                                             |
| Contraseña:                                                 |
| [ ••••••••••••                                            ] |
|                                                             |
| [!] Alerta de error dinámica (Credenciales inválidas)       |
|                                                             |
| [ Cancelar ]                             [ Acceder (POST) ] |
+-------------------------------------------------------------+
```

---

## 3. `index.html`: Catalog & Inventory Showcase View

Primary landing and public showcase page with dynamic stock indicators and search filtering.

### 3.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header)                                                                             |
+----------------------------------------------------------------------------------------------------+
|                                                                                                    |
| HERO SECTION                                                                                       |
| +------------------------------------------------------------------------------------------------+ |
| |  🧶 Catálogo de Amigurumis Hechos a Mano                                                       | |
| |  Explora creaciones artesanales únicas, revisa disponibilidad y encarga piezas personalizadas.  | |
| |  [ Ver Catálogo ↓ ]                     [ Encargar Amigurumi Especial ]                        | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| FILTER TOOLBAR (col-12)                                                                            |
| +------------------------------------------------------------------------------------------------+ |
| | [ 🔍 Buscar por nombre o material... ]  [ Categoría: Todas ▼ ]  [ Stock: Todos ▼ ]  [ ↺ Limpiar ] | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| PRODUCT CARD GRID (row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4)                                 |
| +------------------------------+ +------------------------------+ +------------------------------+ |
| | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | [IMG PLACEHOLDER (4:3)]  | | | | [IMG PLACEHOLDER (4:3)]  | | | | [IMG PLACEHOLDER (4:3)]  | | |
| | | Badge: [ En Stock (4) ]  | | | | Badge: [ En Stock (12)]  | | | | Badge: [ Agotado ]       | | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | Dragón Ignis             | | | | Mini Suculenta Maceta    | | | | Ajolote Rosado Pastel    | | |
| | | Categ: Fantasía          | | | | Categ: Plantas / Botánica| | | | Categ: Animales / Fauna  | | |
| | | Mat: Algodón Mercerizado | | | | Mat: Algodón Rústico     | | | | Mat: Hilo Chenille       | | |
| | | Tam: 18.5 cm             | | | | Tam: 10.0 cm             | | | | Tam: 14.0 cm             | | |
| | |                          | | | |                          | | | |                          | | |
| | | $450.00 MXN              | | | | $180.00 MXN              | | | | $320.00 MXN              | | |
| | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Encargar]| | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| +------------------------------+ +------------------------------+ +------------------------------+ |
|                                                                                                    |
| PAGINATION / STATUS BAR                                                                            |
| +------------------------------------------------------------------------------------------------+ |
| | Mostrando 3 de 3 piezas artesanales                     [ « ]  [ 1 ]  [ 2 ]  [ » ]             | |
| +------------------------------------------------------------------------------------------------+ |
| FOOTER (Semantic <footer>)                                                                         |
+----------------------------------------------------------------------------------------------------+
```

### 3.2 Responsive Grid Breakdown
- **Filter Toolbar:** Stacked 100% on `xs`/`sm`, unified flex/grid row (`col-md-5`, `col-md-3`, `col-md-2`, `col-md-2`) on `md+`.
- **Card Grid:** 
  - `col-12` (< 768px): 1 card per row for clear mobile readability.
  - `col-md-6` (768px – 991px): 2 cards per row.
  - `col-lg-4` (≥ 992px): 3 cards per row.

---

## 4. `detalle.html`: Creation Specification & Public Checkout View

Detailed individual product showcase with craftsmanship specs and Public Checkout Modal trigger.

### 4.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header)                                                                             |
+----------------------------------------------------------------------------------------------------+
| BREADCRUMB: Inicio / Catálogo / Dragón Ignis                                                       |
+----------------------------------------------------------------------------------------------------+
| 2-COLUMN SPLIT (row g-5)                                                                           |
|                                                                                                    |
| [LEFT COLUMN: col-12 col-lg-6]              | [RIGHT COLUMN: col-12 col-lg-6]                     |
| +-----------------------------------------+ | +--------------------------------------------------+ |
| |                                         | | | Badge: [ Fantasía ]   Badge: [ En Stock: 4 u. ]  | |
| |  [ MAIN HIGH-RES IMAGE PLACEHOLDER ]    | | |                                                  | |
| |  (Aspect ratio 1:1 or 4:3)              | | | Dragón Ignis                                     | |
| |                                         | | | $450.00 MXN (Precio final IVA incluido)          | |
| |                                         | | |                                                  | |
| +-----------------------------------------+ | | ESPECIFICACIONES TÉCNICAS:                       | |
| | THUMBNAIL PREVIEWS:                     | | | • Material: 100% Algodón Mercerizado             | |
| | [Thumb 1]  [Thumb 2]  [Thumb 3]         | | | • Altura/Largo: 18.5 cm                          | |
| |                                         | | | • Tiempo de Confección: 6.5 horas de tejido      | |
| | BADGE DE AUTORÍA ARTESANAL:             | | | • Artesano Responsable: admin                    | |
| | Confeccionado a mano por @admin         | | |                                                  | |
| +-----------------------------------------+ | | DESCRIPCIÓN ARTESANAL:                           | |
|                                             | | Amigurumi de dragón fantástico tejido a crochet  | |
|                                             | | con escamas en relieve y relleno hipoalergénico. | |
|                                             | |                                                  | |
|                                             | | ACCIÓN DE COMPRA / ENCARGO:                      | |
|                                             | | +----------------------------------------------+ | |
|                                             | | | [🛒 Encargar / Comprar Ahora (Abre Modal)]   | | |
|                                             | | | [← Volver al Catálogo]                       | | |
|                                             | | +----------------------------------------------+ | |
|                                             | +--------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

### 4.2 ASCII Wireframe: Public Client Checkout Modal (Triggered on `detalle.html`)
```text
+-------------------------------------------------------------+
| Solicitud de Pedido: Dragón Ignis                       [X] |
+-------------------------------------------------------------+
| Precio unitario: $450.00 MXN | Stock disponible: 4 unidad(es)
|                                                             |
| Nombre Completo del Cliente (*):                            |
| [ Mariana Gómez                                           ] |
|                                                             |
| Cantidad de Unidades (*):                                   |
| [ 1                                                       ] |
|                                                             |
| Fecha Deseada de Entrega:                                   |
| [ YYYY-MM-DD (Selector de fecha)                          ] |
|                                                             |
| Notas / Personalizaciones Especiales:                       |
| [ Empaque para regalo con listón verde bosque...          ] |
|                                                             |
| RESUMEN ECONÓMICO (Cálculo Dinámico en Tiempo Real):        |
| Total a Pagar: 1 x $450.00 = $450.00 MXN                    |
|                                                             |
| [ Cancelar ]                        [ Confirmar Pedido ]    |
+-------------------------------------------------------------+
```

---

## 5. `formulario.html`: Add & Edit Creation with Margin Calculator

Dual-purpose form for adding and editing amigurumis with real file upload and sticky margin math card.

### 5.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header with [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])           |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi (Activo)]         [📦 Gestión de Pedidos]                 [← Catálogo Público] |
+----------------------------------------------------------------------------------------------------+
| 2-COLUMN SPLIT (row g-4)                                                                           |
|                                                                                                    |
| [LEFT: Formulario Principal (col-12 col-lg-8)]| [RIGHT: Simulador de Márgenes (col-12 col-lg-4)]     |
| +-------------------------------------------+ | +------------------------------------------------+ |
| | Nombre del Amigurumi (*):                 | | | 📊 RENDIMIENTO FINANCIERO (Sticky Card)        | |
| | [ Dragón Ignis                          ] | | +------------------------------------------------+ |
| |                                           | | | Precio Venta:       $450.00 MXN                | |
| | Categoría (*):        Material (*):       | | | Costo Materiales: - $120.00 MXN                | |
| | [ Fantasía        ▼] [ 100% Algodón     ] | | | ------------------------------------           | |
| |                                           | | | Ganancia Bruta:     $330.00 MXN                | |
| | Tamaño (cm) (*):      Stock Inicial (*):  | | | Margen de Utilidad: 73.3%                      | |
| | [ 18.5             ] [ 4                ] | | |                                                | |
| |                                           | | | Horas de Trabajo:   6.5 hrs                    | |
| | Precio Venta (MXN)*:  Costo Materiales*:  | | | Retorno por Hora:   $50.77 MXN/hr              | |
| | [ 450.00           ] [ 120.00           ] | | |                                                | |
| |                                           | | | [ Badge: Margen Saludable (> 60%) ]            | |
| | Horas Estimadas de Confección:            | | +------------------------------------------------+ |
| | [ 6.5                                   ] |                                                      |
| |                                           |                                                      |
| | Descripción / Cuidados:                   |                                                      |
| | [ Amigurumi fantástico con detalles...  ] |                                                      |
| |                                           |                                                      |
| | Fotografía del Producto (enctype file):   |                                                      |
| | +---------------------------------------+ |                                                      |
| | | [ Arrastre imagen o clic para buscar] | |                                                      |
| | | Formatos: JPG, PNG, WEBP (Máx 5MB)    | |                                                      |
| | +---------------------------------------+ |                                                      |
| |                                           |                                                      |
| | [ Ir a Pedidos ]     [ Guardar Creación ] |                                                      |
| +-------------------------------------------+ |                                                      |
+----------------------------------------------------------------------------------------------------+
```

### 5.2 Responsive Grid Breakdown
- **Inputs:** `col-12` on mobile, paired in `col-md-6` (e.g., Precio and Costo side by side).
- **Sticky Margin Card:** Renders below form on mobile (`col-12`), docks persistently as sticky card (`col-lg-4`) on desktop.

---

## 6. `pedidos.html`: Orders & Commission Tracking Dashboard

Artisan and administrator order fulfillment dashboard with status filtering and state transitions.

### 6.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header with [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])           |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]                 [📦 Gestión de Pedidos (Activo)]        [← Catálogo Público]  |
+----------------------------------------------------------------------------------------------------+
| GESTIÓN DE PEDIDOS Y ENCARGOS                                                                      |
|                                                                                                    |
| METRIC CARDS (row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4)                                  |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
| | Total Pedidos: 2   | | Pendientes: 1      | | En Proceso: 1      | | Ingresos: $1,090   |       |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
|                                                                                                    |
| FILTER PILLS & SEARCH (col-12 d-flex justify-content-between mb-3)                                 |
| +------------------------------------------------------------------------------------------------+ |
| | [ Todos (2) ]  [ Pendientes (1) ]  [ En Proceso (1) ]  [ Entregados (0) ]  [ Cancelados (0) ]    | |
| | [ 🔍 Filtrar cliente o ID... ]                                                                 | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| ORDERS DATA TABLE (table-responsive)                                                               |
| +------------------------------------------------------------------------------------------------+ |
| | ID | Cliente         | Producto            | Cant | Total    | Estado     | Entrega   | Acciones   |
| |----+-----------------+---------------------+------+----------+------------+-----------+------------|
| | #1 | Mariana Gómez   | Dragón Ignis        | 1    | $450.00  | [En Proceso| 2026-09-24| [⚙ Cambiar |
| |    |                 |                     |      |          |  (Azul)]   |           |    Estado ▼|
| |----+-----------------+---------------------+------+----------+------------+-----------+------------|
| | #2 | Carlos Mendoza  | Ajolote Rosado      | 2    | $640.00  | [Pendiente | 2026-09-30| [⚙ Cambiar |
| |    |                 |                     |      |          |  (Ámbar)]  |           |    Estado ▼|
| +------------------------------------------------------------------------------------------------+ |
| (Estado dropdown options: 'En Proceso', 'Entregado', 'Cancelar Pedido [Devuelve Stock]')           |
+----------------------------------------------------------------------------------------------------+
```

---

## 7. Responsive Breakpoint Summary Matrix

| View | Mobile (`< 768px`) | Tablet (`768px - 991px`) | Desktop (`≥ 992px`) |
| :--- | :--- | :--- | :--- |
| **Navbar** | Hamburger dropdown (`collapse`) | Full horizontal inline | Full horizontal inline + User status |
| **Catalog Grid** | 1 column (`col-12`) | 2 columns (`col-md-6`) | 3 columns (`col-lg-4`) |
| **Product Detail** | Vertical stack: Image top, specs bottom | Vertical stack with enlarged image | 50/50 2-Column Split (`col-lg-6` / `col-lg-6`) |
| **Product Form** | Vertical stack: Form top, margin card bottom | Form top, margin card bottom | 66/33 Split: Form (`col-lg-8`), Sticky Margin (`col-lg-4`) |
| **Orders Dashboard**| KPI cards stack (`col-12`), scrollable table | 2x2 KPI cards, scrollable table | 4-column KPI cards (`col-lg-3`), wide data table |
