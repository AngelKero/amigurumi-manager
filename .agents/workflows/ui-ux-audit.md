---
description: "Executes a rigorous UI/UX heuristic audit, visual hierarchy evaluation, and design system review on HTML/CSS frontend views."
---

# UI/UX Heuristic & Design System Audit Workflow

This workflow executes the **Design Auditor** skill against the frontend code and views of the application.

## Prerequisites
- Frontend views located in project root: `index.html`, `detalle.html`, `formulario.html`, `pedidos.html`.
- Styles and scripts: `css/styles.css`, `js/app.js`.

## Audit Dimensions
1. **Visual Design Balance & Hierarchy:** Whitespace rhythm, typography scale, visual weight distribution, scannability.
2. **Information Architecture & User Flow:** Friction points across visitor browsing, public client checkout, and private artisan management.
3. **Usability Heuristics:** Feedback mechanisms, consistency across views, error prevention, recovery, state visibility.
4. **Design System & Palette Proposals:** Evaluation of current color tokens and creation of 3 curated, niche-specific design system proposals for handmade crochet amigurumis.

## Execution Steps
1. Inspect markup structure and semantic elements across all HTML files.
2. Audit CSS design tokens (`css/styles.css`) for color contrast, rhythm, elevation, and component styling.
3. Audit interactive DOM behaviors (`js/app.js`) for modal states, validation cues, and feedback loops.
4. Compile findings into `.docs/ui-ux-skill-report.md`.
