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
7. **Navbar Textile Seam & Artisan Master Seal (`.brand-craft-badge`, `.badge-artisan-seal`, `.btn-craft-logout`):**
   - Navbars must display a dashed running-stitch bottom seam (`border-bottom: 2px dashed rgba(142, 91, 116, 0.22)`).
   - The brand title is framed in a delicate pill badge with dashed micro-stitch (`.brand-craft-badge`).
   - Authenticated artisan status must NEVER revert to harsh black boxes (`bg-dark`). It must always use the warm parchment seal with golden dashed stitches (`.badge-artisan-seal`).
   - Logout actions use `.btn-craft-logout` with soft rose dashed borders.
8. **Quilted Hero Banner & Stitched Photo Frame (`.hero-cloud-stitched`, `.hero-photo-stitched-frame`):**
   - The hero banner includes a grand running-stitch inset seam (`.hero-cloud-seam`).
   - Hero photography is framed in a white canvas mat with dashed running stitch (`.hero-photo-stitched-frame`).
9. **Stitched Trust Chips & Secondary CTA (`.trust-chip-stitched`, `.btn-craft-outline-stitched`):**
   - Trust indicators must be displayed in delicate stitched pill chips (`.trust-chip-stitched`).
   - Secondary actions must use dashed running stitch borders (`.btn-craft-outline-stitched`).
10. **Interactive Textile Category Chips (`.btn-chip-textile`):**
    - The catalog filter station must provide clickable textile tags with dashed borders that activate an embroidered running stitch when selected, maintaining 100% two-way sync with the category select dropdown.
11. **Strict Eradication of Bootstrap Electric Blue:**
    - Under no circumstances should default Bootstrap blue (`#0d6efd`) appear via `.text-primary`, `.border-primary`, or `.bg-primary`. All primary elements must resolve strictly to `--craft-primary` (`#8E5B74`).
12. **Breadcrumb Craft Ribbon (`.breadcrumb-craft-ribbon`):**
    - Navigation breadcrumbs must be styled as a stitched ribbon pill with dashed borders (`1.5px dashed rgba(142, 91, 116, 0.25)`), thread bullet separators (`•`), and the active item wrapped in a delicate plum pill tag.
13. **Quilted Photo Mat Frame (`.product-photo-stitched-frame`):**
    - Product showcase imagery must be nested inside a quilted white mat frame with an inset dashed running stitch (`outline: 1.5px dashed rgba(142, 91, 116, 0.28); outline-offset: -8px`).
14. **Artisan Workshop Verified Seal (`.artisan-workshop-seal-card`):**
    - Authorship must be displayed in a warm parchment card (`#FFFDF9`) framed by golden honey dashed stitches (`1.5px dashed #E0A868`) with a crest avatar and official workshop registration.
15. **Tailored Specifications Table & Story Quote (`.table-craft-specs`, `.story-quote-craft`):**
    - Technical specifications must use dashed thread dividers (`1px dashed var(--craft-border)`), plum micro-badges for spec icons, and artisan descriptions framed with a 3.5px primary left border and lino-tinted background.
16. **Team Directory & Artisan Management Aesthetic (`.table-artisan-team`, `.avatar-artisan-initials`):**
    - The team directory (`usuarios.php`) uses soft circular avatars with primary/secondary tinted background initials, high-contrast role badges (`badge-role-admin`, `badge-role-artesano`, `badge-role-asistente`), and stitched role modification triggers.
17. **Root Administrator Lockout Safeguard (ID #1):**
    - The root admin user (`id: 1`, `@admin`) must NEVER have their administrator role downgraded or deleted. The UI must disable role editing controls for ID #1 with a lock indicator and clear explanation tooltip.
18. **Advanced Catalog Filter Bar (`#filterPriceMin`, `#filterPriceMax`, `#filterArtisan`):**
    - Price range inputs (Min/Max) and artisan author dropdowns must operate in complete two-way harmony with the textile category chips and keyword search, backed by an instant "Limpiar Filtros" reset trigger.
19. **Universal Currency Formatting Standard:**
    - All monetary values must be stored in database integers (cents) and formatted symmetrically using `src/Utils/CurrencyHelper.php` (PHP) and `src/js/modules/currency.js` (ES Modules). Floating point arithmetic artifacts (e.g. `$12.300000004`) are strictly forbidden.
20. **Direct WhatsApp Commission Action & Tri-State Payment Badges:**
    - Orders featuring customer phone contacts must render a direct WhatsApp button (`https://wa.me/...`). Payment statuses must strictly follow the Tri-State model: `Pendiente` (Nordic Honey amber), `Anticipo 50%` (Nordic Spruce), and `Liquidado` (Forest Craft Green).
21. **On-Demand Exclusivity Badge (`es_sobre_encargo === 1`):**
    - Catalog items flagged as `es_sobre_encargo = 1` must display the distinctive textile tag *"Bajo Encargo Exclusivo"*, disabling immediate stock checkout and redirecting the user to custom commission scheduling.


