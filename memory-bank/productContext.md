# Product Context: Crochet Creations Micro-ERP & Catalog

## Why This Project Exists
Independent crochet artisans and textile craft businesses need more than a static portfolio; they require a lightweight **Micro-ERP** to handle catalog presentation, material investments, labor tracking, physical stock management, client commissions/orders, and administrative authentication across all crochet disciplines: **Amigurumis & Figuras**, **Prendas & Ropa**, **Bolsos & Accesorios**, **Hogar & Decoración**, and **Bebé & Infantil**.

## Target User Personas
- **Crochet Artisan / Business Owner (`rol: admin`):**
  - Manages catalog creations (garments, plushies, bags, blankets) and updates material costs, labor hours, and retail prices.
  - Logs and monitors physical inventory counts (`cantidad_stock`) and on-demand commission status (`es_sobre_encargo`).
  - Records custom client orders (`pedidos`), tracks fulfillment deadlines, and reviews profit margins.
  - Controls administrative system access and team members.
- **Client / Public Visitor (Unauthenticated):**
  - Explores the crochet catalog, examines detailed craftsmanship specifications (sizes, dimensions, fiber composition), checks current stock availability, and requests custom commissions.

## Key User Journeys
1. **Catalog Exploration (`index.php`):**
   - Visitor browses creations by crochet category, checks whether items are available or sold out via dynamic badges (`cantidad_stock`), and inspects full craft details.
2. **Creations & Stock Administration (`creaciones.php` - Authenticated):**
   - Artisan manages creations with live KPIs, quick stock increments/decrements, on-demand toggle, inspection modal, and deletion with referential integrity safeguards.
3. **Creation Management (`formulario.php` - Authenticated):**
   - Artisan registers a new design or modifies an existing piece with flexible dimensions / sizing.
   - Real-time client-side calculation visualizes the profit margin ($) and effective hourly rate ($/hr) based on `precio`, `costo_materiales`, and `horas_tejido`.
4. **Product Inspection & Public Checkout (`detalle.php`):**
   - High-fidelity visual showcase of the crochet piece with technical specifications, materials, sizing, and artisan business metrics.
   - Public Client Checkout: Customers can directly purchase or commission an item using an interactive modal, with real-time stock deduction.
5. **Order & Commission Tracking (`pedidos.php` - Authenticated):**
   - Artisan logs custom commissions, monitors public orders in responsive cards, updates fulfillment stages (`Pendiente`, `En Proceso`, `Entregado`, `Cancelado`), contacts customers via WhatsApp, and triggers automatic inventory restocking upon cancellation.
6. **Authentication & Session (Dynamic Navbar Modal):**
   - Artisan logs in seamlessly via a modal in the navbar without page disruption, unlocking role-based management tools.

## Design Principles
- **Warm, Crafted Aesthetic:** "Algodón Nórdico" palette, Fraunces serif display headlines, running-stitch borders, quilted photo frames, and subtle micro-animations.
- **Simplicity & Responsiveness:** Clean Bootstrap 5 mobile-first layout with accessible navigation.
- **Data Integrity & Immutability:** Financial figures preserved in cents, historical order prices locked, and foreign keys protected against accidental cascade deletions (`ON DELETE RESTRICT`).
