---
trigger: always_on
---

# UI/UX & Design System Guidelines: "Algodón Nórdico" (Mandatory)

These rules govern all present and future design decisions, HTML layouts, CSS styling, and client-side interactions for the **Amigurumi Micro-ERP & Catalog** application. You must never deviate from these established tokens, aesthetic principles, or heuristic safety guardrails.

---

## 1. Color Palette Tokens ("Algodón Nórdico")

All components, styles, and templates must strictly use the following semantic tokens defined in `css/styles.css`:

| Semantic Role | Hex Code | Token / CSS Variable | Usage |
| :--- | :---: | :--- | :--- |
| **Primary Brand** | `#8E5B74` | `--craft-primary` | Main CTAs, navbar branding, active navigation links, focal highlights. |
| **Primary Hover** | `#75475E` | `--craft-primary-hover` | Hover/focus states for primary buttons and interactive brand elements. |
| **Primary Subtle** | `#F7EFF3` | `--craft-primary-subtle` | Lavender Milk background for active nav items, hovered rows, pill tags. |
| **Nordic Spruce** | `#52857C` | `--craft-secondary` | Secondary brand accent, craft guarantee icons, success indicators. |
| **Spruce Text (WCAG)**| `#235048` | `--craft-secondary-text` | **Mandatory high-contrast text** for stock badges on light spruce (>6.2:1 ratio). |
| **Glacier Mist** | `#EBF4F2` | `--craft-secondary-subtle` | Background for in-stock badges and healthy margin feedback pills. |
| **Nordic Honey** | `#D99C52` | `--craft-accent-gold` | Pending orders, artisan panel accents, warning badges. |
| **Canvas Background**| `#F8F9FB` | `--craft-bg` | Alabaster porcelain body canvas (replaces harsh stark whites or dark gray). |
| **Surface (Card)** | `#FFFFFF` | `--craft-surface` | Crisp white card backgrounds with soft elevation. |
| **Surface Muted** | `#F1F4F8` | `--craft-surface-muted` | Table headers, dropzone background, modal header stripes. |
| **Frost Border** | `#E4E8ED` | `--craft-border` | Subtle dividers and card outlines. |
| **Title Typography** | `#1E252D` | `--craft-text-main` | Nordic Midnight Slate for headings, titles, prices, and emphasis. |
| **Body Typography** | `#505963` | `--craft-text-muted` | Descriptive copy, technical specifications, and labels. |

---

## 2. Typography Hierarchy

- **Display & Headings:** Always use Google Font **`Outfit`** (`weights: 600, 700, 800`) for all `<h1>`–`<h6>`, brand titles, prices, and modal headers. Letter-spacing should be `-0.02em`.
- **Body & Controls:** Always use Google Font **`Plus Jakarta Sans`** (`weights: 400, 500, 600`) for body text, tables, form inputs, badges, and microcopy.
- **Monospace Metrics:** Use standard monospace fonts for IDs (`#1`), timestamps (`2026-09-24`), stock integers, and currency denominations.

---

## 3. Shape, Elevation & Cloud Aesthetics

- **Border Radius:**
  - **Cards & Containers:** `--craft-radius: 18px;` (ultra-soft Scandinavian corners).
  - **Badges & Pill Buttons:** `--craft-radius-pill: 50px;` (pill-shaped tags and primary action buttons).
  - **Inputs & Tables:** `--craft-radius-sm: 10px;`.
- **Elevation & Shadows:**
  - Standard Card: `0 4px 16px rgba(30, 37, 45, 0.04)`.
  - Hover Card: `0 14px 36px rgba(30, 37, 45, 0.09)` with a gentle `-4px` vertical lift (`translateY(-4px)`).
  - Cloud Hero / Modals: `0 16px 42px -8px rgba(142, 91, 116, 0.12)`.
- **Micro-Animations:**
  - Incorporate subtle floating keyframe motion (`@keyframes floatSoft`, `@keyframes floatGentle`) on decorative elements (floating cloud pill badges, hero craft imagery).
  - Use `backdrop-filter: blur(12px)` on sticky navbars and hero badges for a modern glassmorphic feel.

---

## 4. Heuristic Usability & Business Guardrails (Never Violate)

1. **[CR-1] Out-of-Stock Guard (`cantidad_stock === 0`):**
   - In catalog grids and detail pages, any product with `stock === 0` MUST have its direct purchase button **disabled** (`disabled`, `aria-disabled="true"`).
   - Display a distinct badge: `.badge-stock-out` with text *"Agotado para Entrega Inmediata"*.
   - Never allow a customer to check out an in-stock order if stock is zero. Offer a *"Solicitar Encargo Especial"* trigger instead.
2. **[CR-2] Bounded Stepper Quantity Control:**
   - Quantity inputs in public checkout modals must be `readonly` and controlled via `[-] [ 1 ] [+]` stepper buttons.
   - The stepper must be strictly bounded: minimum `1`, maximum `cantidad_stock`.
   - Stepper buttons must disable (`disabled`) dynamically when limits are reached.
3. **[CR-2] Mobile-First Order Responsiveness:**
   - On screens `< 768px`, order dashboards must display stacked order cards (`.order-card-mobile` via `d-block d-md-none`) instead of forcing horizontal scrolling on data tables (`d-none d-md-block`).
4. **[QW-1] WCAG 2.1 AA Contrast Standard:**
   - Text contrast on badges, buttons, and status pills must always meet or exceed **4.5:1** (e.g. `--craft-secondary-text: #235048` on `--craft-secondary-subtle: #EBF4F2` provides > 6.2:1 contrast).
5. **[QW-2] Stock Restitution on Cancellation:**
   - Any order cancellation dialog MUST clearly notify the artisan of the exact number of units and product name being reintegrated into physical inventory (`+X unidad(es) reintegradas a [Producto]`).
6. **[QW-2] Transparent Lead-Time Microcopy:**
   - All custom commission modals must include the standard lead time note: *"Los pedidos personalizados o sin existencias requieren de 5 a 7 días hábiles de confección artesanal."*
7. **Artisan Role Isolation:**
   - Public customer view must NEVER reveal administration routes (`Nuevo Amigurumi`, `Gestión de Pedidos`). These are displayed only when an authenticated session (`rol: admin/artesano`) is active.

---

## 5. Craft Border & Textile Detailing System (Mandatory)

To evoke the handmade, cozy, tactile nature of crochet and textile sewing, views must employ the following craft detailing patterns:

1. **Inset Running Seams (`.card-stitched`):**
   - Implemented via a non-intrusive pseudo-element: `position: absolute; inset: 7px; border: 1.5px dashed rgba(142, 91, 116, 0.22); border-radius: calc(var(--craft-radius) - 7px); pointer-events: none;`.
   - Used on product cards, specification summaries, KPI blocks, and primary containers.
2. **Embroidered Action Buttons (`.btn-craft-stitched`):**
   - Applies an internal dashed stitch line: `outline: 1.5px dashed rgba(255, 255, 255, 0.55); outline-offset: -5px;`.
   - Used on key purchase CTAs, login submissions, and order confirmation buttons.
3. **Woven Cloth Care Tags (`.badge-textile-tag`):**
   - Replaces generic badges for product categories and operational modes.
   - Features a thick sewn edge (`border-left: 3.5px solid var(--craft-primary)`), dashed textile border (`1px dashed rgba(142, 91, 116, 0.35)`), and simulated thread holes (`::after { content: "•••"; }`).
4. **Running-Stitch Dividers (`.divider-stitched`):**
   - Replaces standard `<hr>` with a repeating dashed thread pattern: `repeating-linear-gradient(90deg, var(--craft-border) 0, var(--craft-border) 8px, transparent 8px, transparent 16px)`.
5. **Blanket Stitch Blocks (`.guarantee-stitched`):**
   - Used on trust cards, guarantees, and artisan assurances with a distinct spruce-tinted dashed border (`1.5px dashed rgba(82, 133, 124, 0.35)`).
6. **Atmospheric Background Decorations (`.cloud-yarn-bg-decorations`):**
   - Ambient, non-obtrusive floating SVG clouds, yarn balls, and crochet hooks in the background canvas (`pointer-events: none`, opacity 0.18–0.22).
