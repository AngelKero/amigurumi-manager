# Amigurumi Micro-ERP: Low-Fidelity UI Wireframes & Layout Specification

This document defines the structural, low-fidelity blueprints and responsive grid architecture for all application views following the modular PHP component architecture (`views/`) and "Algodón Nórdico" Design System.

---

## 1. Responsive Grid & Breakpoint Guidelines

The layout strictly follows Bootstrap 5's mobile-first responsive 12-column grid:
- **Mobile (`xs`, `< 576px`):** Full-width stacked columns (`col-12`), collapse toolbars into single-column vertical stacks, collapse navbar into hamburger toggler, transform order tables into stacked touch-friendly cards (`.order-card-mobile`).
- **Tablet (`md`, `≥ 768px`):** 2-column card layouts (`col-md-6`), horizontal search and filter toolbar, side-by-side modal inputs.
- **Desktop (`lg` / `xl`, `≥ 992px`):** 3-column catalog grids (`col-lg-4`), 2-column side-by-side detail and form splits (`col-lg-6` / `col-lg-8` + `col-lg-4` sticky margin summary), wide tabular dashboards.

---

## 2. Shared Header, Navbar & Artisan Panel Navigation

To preserve clear architectural boundaries between public customer browsing and private business management:
- **Public Navbar:** Contains **only** the brand logo, the public `[Catálogo]` link, and the `[👤 Iniciar Sesión]` trigger. Administrative actions (`Nuevo Amigurumi`, `Pedidos`, `Usuarios`) are strictly hidden from unauthenticated visitors.
- **Authenticated Navbar:** When logged in, the navbar unlocks the `[🛠️ Panel del Artesano ▼]` dropdown menu (linking to `Nuevo Amigurumi`, `Gestión de Pedidos`, and `Gestión de Usuarios`) alongside the artisan session seal and logout button (`.badge-artisan-seal`).
- **Artisan Panel Toolbar:** Private views (`formulario.php`, `pedidos.php`, `usuarios.php`) feature a dedicated artisan header bar with direct action buttons (`+ Nuevo Amigurumi`, `Gestión de Pedidos`, `Equipo Artesanal`).

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

### 2.3 ASCII Wireframe: Artisan Panel Internal Header Bar
```text
+----------------------------------------------------------------------------------------------------+
| 🛠️ Panel de Administración del Artesano (Sesión Activa: @admin)                                    |
| [➕ Nuevo Amigurumi]     [📦 Gestión de Pedidos]     [👥 Equipo Artesanal]    [← Catálogo Público] |
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

## 3. `index.php`: Catalog & Inventory Showcase View

Primary landing and public showcase page (`views/pages/catalogo_content.php`) with dynamic stock indicators, textile category chips, and advanced price/artisan filters.

### 3.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header with Brand & Auth Trigger)                                                   |
+----------------------------------------------------------------------------------------------------+
|                                                                                                    |
| HERO SECTION (.hero-cloud-stitched)                                                                |
| +------------------------------------------------------------------------------------------------+ |
| |  🧶 Catálogo de Amigurumis Hechos a Mano                                                       | |
| |  Explora creaciones artesanales únicas, revisa disponibilidad y encarga piezas personalizadas.  | |
| |  [ Ver Catálogo ↓ ]                     [ Encargar Amigurumi Especial ]                        | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| ADVANCED FILTER STATION (col-12)                                                                  |
| +------------------------------------------------------------------------------------------------+ |
| | [ 🔍 Buscar por nombre o material... ]  [ Min $: ___ ]  [ Max $: ___ ]  [ Artesano: Todos ▼ ]  | |
| | Category Chips: [🏷️ Todas] [✨ Fantasía] [🌿 Plantas/Botánica] [🐾 Animales/Fauna]             | |
| | Stock Filter:   [ Existencias: Todas ▼ ]                                    [ ↺ Limpiar ]      | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| PRODUCT CARD GRID (row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4)                                 |
| +------------------------------+ +------------------------------+ +------------------------------+ |
| | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | [QUILTED PHOTO MAT FRAME]| | | | [QUILTED PHOTO MAT FRAME]| | | | [QUILTED PHOTO MAT FRAME]| | |
| | | Badge: [ En Stock (4) ]  | | | | Badge: [ En Stock (12)]  | | | | Badge: [ Bajo Encargo ]  | | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | Dragón Ignis             | | | | Mini Suculenta Maceta    | | | | Ajolote Rosado Pastel    | | |
| | | Categ: Fantasía          | | | | Categ: Plantas / Botánica| | | | Categ: Animales / Fauna  | | |
| | | Mat: Algodón Mercerizado | | | | Mat: Algodón Rústico     | | | | Mat: Hilo Chenille       | | |
| | | Tam: 18.5 cm             | | | | Tam: 10.0 cm             | | | | Tam: 14.0 cm             | | |
| | | Por: @admin              | | | | Por: @artesano_clara     | | | | Por: @admin              | | |
| | |                          | | | |                          | | | |                          | | |
| | | $450.00 MXN              | | | | $180.00 MXN              | | | | $320.00 MXN              | | |
| | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Encargar]| | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| +------------------------------+ +------------------------------+ +------------------------------+ |
|                                                                                                    |
| EMPTY STATE (Hidden by default, shown when filters match 0 results)                                |
| +------------------------------------------------------------------------------------------------+ |
| |  🧶 No se encontraron piezas que coincidan con los filtros aplicados.                          | |
| |  [ Restablecer Filtros de Búsqueda ]                                                           | |
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

---

## 4. `detalle.php`: Creation Specification & Public Checkout View

Detailed individual product showcase (`views/pages/detalle_content.php`) with craftsmanship specs, story quotes, verified artisan seal, and public checkout modal trigger.

### 4.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header)                                                                             |
+----------------------------------------------------------------------------------------------------+
| BREADCRUMB: [ Inicio • Catálogo • Dragón Ignis ] (.breadcrumb-craft-ribbon)                        |
+----------------------------------------------------------------------------------------------------+
| 2-COLUMN SPLIT (row g-5)                                                                           |
|                                                                                                    |
| [LEFT COLUMN: col-12 col-lg-6]              | [RIGHT COLUMN: col-12 col-lg-6]                     |
| +-----------------------------------------+ | +--------------------------------------------------+ |
| |                                         | | | [🏷️ Fantasía]   Badge: [ En Stock: 4 u. ]        | |
| |  [ PRODUCT PHOTO STITCHED FRAME ]       | | |                                                  | |
| |  (Aspect ratio 4:3 with dashed border)  | | | Dragón Ignis                                     | |
| |                                         | | | $450.00 MXN (Precio final IVA incluido)          | |
| +-----------------------------------------+ | |                                                  | |
| | ARTISAN WORKSHOP VERIFIED SEAL:         | | | ESPECIFICACIONES TÉCNICAS (.table-craft-specs):  | |
| | +-------------------------------------+ | | | • Material: 100% Algodón Mercerizado             | |
| | | 🛡️ Confeccionado a mano por @admin  | | | • Altura/Largo: 18.5 cm                          | |
| | | Taller Artesanal Registrado         | | | • Tiempo de Confección: 6.5 horas de tejido      | |
| | +-------------------------------------+ | | | • Disponibilidad: Inmediata (4 unidades)         | |
| +-----------------------------------------+ | |                                                  | |
|                                             | | HISTORIA & DESCRIPCIÓN (.story-quote-craft):     | |
|                                             | | "Amigurumi de dragón fantástico tejido a crochet | |
|                                             | |  con escamas en relieve y relleno siliconado."   | |
|                                             | |                                                  | |
|                                             | | ACCIÓN DE COMPRA / ENCARGO:                      | |
|                                             | | +----------------------------------------------+ | |
|                                             | | | [🛒 Encargar / Comprar Ahora (Abre Modal)]   | | |
|                                             | | | [← Volver al Catálogo Completo]              | | |
|                                             | | +----------------------------------------------+ | |
|                                             | +--------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

### 4.2 ASCII Wireframe: Public Client Checkout Modal (Triggered on `detalle.php`)
```text
+-------------------------------------------------------------+
| Solicitud de Pedido: Dragón Ignis                       [X] |
+-------------------------------------------------------------+
| Precio unitario: $450.00 MXN | Stock disponible: 4 unidad(es)
|                                                             |
| Nombre Completo del Cliente (*):                            |
| [ Mariana Gómez                                           ] |
|                                                             |
| Teléfono / WhatsApp de Contacto (*):                        |
| [ 5512345678                                              ] |
|                                                             |
| Cantidad de Unidades (*):                                   |
| [ - ] [  1  ] [ + ]  (Bounded Stepper: min 1, max stock)    |
|                                                             |
| Fecha Deseada de Entrega:                                   |
| [ YYYY-MM-DD (Selector de fecha)                          ] |
|                                                             |
| Notas / Personalizaciones Especiales:                       |
| [ Empaque de regalo con listón verde bosque...            ] |
|                                                             |
| ℹ️ Nota: Confección artesanal requiere de 5 a 7 días        |
|          hábiles para encargos personalizados.              |
|                                                             |
| RESUMEN ECONÓMICO (Cálculo Universal de Moneda):            |
| Total a Pagar: 1 x $450.00 = $450.00 MXN                    |
|                                                             |
| [ Cancelar ]                          [ Confirmar Pedido ]  |
+-------------------------------------------------------------+
```

---

## 5. `formulario.php`: Add & Edit Creation with Margin Calculator

Dual-purpose creation form (`views/pages/formulario_content.php`) with image upload dropzone, on-demand exclusivity switch, and sticky profit margin preview.

### 5.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header with [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])           |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi (Activo)]   [📦 Gestión de Pedidos]   [👥 Equipo]       [← Catálogo Público]   |
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
| | [X] Confección Exclusiva Bajo Encargo     |                                                      |
| |     (Sin stock físico inmediato)          |                                                      |
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

---

## 6. `pedidos.php`: Orders & Commission Tracking Dashboard

Artisan and administrator order fulfillment dashboard (`views/pages/pedidos_content.php`) with KPI cards, WhatsApp direct link, payment status badge, and manual commission modal.

### 6.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header with [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])           |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]         [📦 Pedidos (Activo)]        [👥 Equipo]             [← Catálogo]      |
+----------------------------------------------------------------------------------------------------+
| GESTIÓN DE PEDIDOS Y ENCARGOS ARTESANALES               [ ➕ Registrar Encargo Manual (Modal) ]    |
|                                                                                                    |
| METRIC CARDS (row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4)                                  |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
| | Total Pedidos: 2   | | Pendientes: 1      | | En Proceso: 1      | | Ingresos: $1,090   |       |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
|                                                                                                    |
| FILTER PILLS & SEARCH BAR (col-12 mb-3)                                                            |
| +------------------------------------------------------------------------------------------------+ |
| | [ Todos (2) ]  [ Pendientes (1) ]  [ En Proceso (1) ]  [ Entregados (0) ]  [ Cancelados (0) ]    | |
| | [ 🔍 Buscar por cliente, amigurumi o teléfono... ]                                             | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| ORDERS DATA TABLE (table-responsive d-none d-md-table)                                             |
| +------------------------------------------------------------------------------------------------+ |
| | ID | Cliente & Contacto   | Producto      | Cant | Total   | Pago       | Estado   | Acciones  |
| |----+----------------------+---------------+------+---------+------------+----------+-----------|
| | #1 | Mariana Gómez        | Dragón Ignis  | 1    | $450.00 | [Anticipo  | [En      | [Ver /    |
| |    | 📲 5512345678 (WA)   |               |      |         |  50%]      |  Proceso]|  Actual.] |
| |----+----------------------+---------------+------+---------+------------+----------+-----------|
| | #2 | Carlos Mendoza       | Ajolote Pastel| 2    | $640.00 | [Pendiente]| [Pend.]  | [Ver /    |
| |    | 📲 5598765432 (WA)   |               |      |         |            |          |  Actual.] |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| MOBILE ORDER CARDS (d-block d-md-none)                                                             |
| +------------------------------------------------------------------------------------------------+ |
| | Pedido #1: Mariana Gómez • Dragón Ignis (1 u.) • $450.00 • [📲 WhatsApp] • [⚙️ Cambiar Estado] | |
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

---

## 7. `usuarios.php`: Team Directory & User Management View (Admin Only)

Administrative user and role management view (`views/pages/usuarios_content.php`) with KPI metrics, user creation modal, and role modification modal protected by the root admin safeguard.

### 7.1 ASCII Layout
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Shared Header with [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])           |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]         [📦 Pedidos]                 [👥 Equipo (Activo)]    [← Catálogo]      |
+----------------------------------------------------------------------------------------------------+
| GESTIÓN DE ARTESANOS Y EQUIPO                           [ ➕ Registrar Nuevo Usuario (Modal) ]     |
|                                                                                                    |
| METRIC CARDS (row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4)                                  |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
| | Total Usuarios: 3  | | Administradores: 1 | | Artesanos: 1       | | Asistentes: 1      |       |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
|                                                                                                    |
| TEAM DIRECTORY TABLE (.table-artisan-team)                                                         |
| +------------------------------------------------------------------------------------------------+ |
| | ID | Usuario / Avatar     | Rol Asignado   | Fecha Alta   | Estado Permisos | Acciones         |
| |----+----------------------+----------------+--------------+-----------------+------------------|
| | #1 | 🛡️ admin             | [Administrador]| 2026-09-10   | [🔒 Cuenta Raíz]| [🔒 Protegido]   |
| |----+----------------------+----------------+--------------+-----------------+------------------|
| | #2 | 👤 artesano_clara    | [Artesano]     | 2026-09-10   | [Activo]        | [✏️ Modificar Rol]|
| |----+----------------------+----------------+--------------+-----------------+------------------|
| | #3 | 👤 asistente_pablo   | [Asistente]    | 2026-09-10   | [Activo]        | [✏️ Modificar Rol]|
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

### 7.2 ASCII Wireframe: Role Edit Modal (With Root Safeguard)
```text
+-------------------------------------------------------------+
| Modificar Rol de Usuario: artesano_clara                [X] |
+-------------------------------------------------------------+
| Seleccione el nuevo nivel de acceso para este colaborador:  |
|                                                             |
| Usuario:                                                    |
| [ artesano_clara (Solo lectura)                           ] |
|                                                             |
| Rol Asignado (*):                                           |
| ( ) Administrador (Acceso total a catálogo, pedidos y users)|
| (•) Artesano (Gestión de creaciones y pedidos propios)      |
| ( ) Asistente (Revisión de pedidos e inventario)            |
|                                                             |
| [ Cancelar ]                          [ Guardar Cambios ]   |
+-------------------------------------------------------------+
```

---

## 8. Responsive Breakpoint Summary Matrix

| View | Mobile (`< 768px`) | Tablet (`768px - 991px`) | Desktop (`≥ 992px`) |
| :--- | :--- | :--- | :--- |
| **Navbar** | Hamburger dropdown (`collapse`) | Full horizontal inline | Full horizontal inline + Artisan Seal badge |
| **Catalog Grid (`index.php`)** | 1 column (`col-12`) | 2 columns (`col-md-6`) | 3 columns (`col-lg-4`) + Advanced Filters |
| **Product Detail (`detalle.php`)**| Vertical stack: Image top, specs bottom | Vertical stack with enlarged frame | 50/50 2-Column Split (`col-lg-6` / `col-lg-6`) |
| **Product Form (`formulario.php`)**| Vertical stack: Form top, margin card bottom | Form top, margin card bottom | 66/33 Split: Form (`col-lg-8`), Sticky Margin (`col-lg-4`) |
| **Orders (`pedidos.php`)** | Stacked cards (`.order-card-mobile`), WhatsApp CTA | Scrollable data table | 4-column KPI cards (`col-lg-3`), wide data table |
| **Users (`usuarios.php`)** | Stacked user cards, role badges | Clean directory table | 4-column KPI cards, full administrative table |
