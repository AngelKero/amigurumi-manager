# Product Context: Amigurumi Micro-ERP & Catalog

## Why This Project Exists
Independent crochet artisans and craft businesses need more than a static portfolio; they require a lightweight **Micro-ERP** to handle catalog presentation, material investments, labor tracking, physical stock management, client commissions/orders, and administrative authentication without bloated enterprise software.

## Target User Personas
- **Crochet Artisan / Business Owner (`rol: admin`):**
  - Manages catalog creations and updates material costs, labor hours, and retail prices.
  - Logs and monitors physical inventory counts (`cantidad_stock`).
  - Records custom client orders (`pedidos`), tracks fulfillment deadlines, and reviews profit margins.
  - Controls administrative system access.
- **Client / Public Visitor (Unauthenticated):**
  - Explores the amigurumi catalog, examines detailed craftsmanship specifications, checks current stock availability, and requests custom commissions.

## Key User Journeys
1. **Catalog Exploration (`index.html`):**
   - Visitor browses creations by category, checks whether items are available or sold out via dynamic badges (`cantidad_stock`), and inspects full craft details.
2. **Creation Management (`formulario.html` - Authenticated):**
   - Artisan registers a new design or modifies an existing piece.
   - Real-time client-side calculation visualizes the profit margin ($) and effective hourly rate ($/hr) based on `precio`, `costo_materiales`, and `horas_tejido`.
3. **Product Inspection & Public Checkout (`detalle.html`):**
   - High-fidelity visual showcase of the amigurumi with technical specifications, materials, and artisan business metrics.
   - Public Client Checkout: Customers can directly purchase or commission an item using an interactive modal, with real-time stock deduction.
4. **Order & Commission Tracking (`pedidos.html` - Authenticated):**
   - Artisan logs custom commissions, monitors public orders, updates fulfillment stages (`Pendiente`, `En Proceso`, `Entregado`, `Cancelado`), and triggers automatic inventory restocking upon cancellation.
5. **Authentication & Session (Dynamic Navbar Modal):**
   - Artisan logs in seamlessly via a modal in the navbar without page disruption, unlocking role-based management tools.

## Design Principles
- **Warm, Crafted Aesthetic:** Harmonious color palette suitable for handmade arts, soft accents, and rounded cards.
- **Simplicity & Responsiveness:** Clean Bootstrap 5 mobile-first layout with accessible navigation.
- **Data Integrity & Immutability:** Financial figures preserved in cents, historical order prices locked, and foreign keys protected against accidental cascade deletions.
