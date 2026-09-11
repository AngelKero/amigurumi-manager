# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Phase 2 Craft Detailing Enhancement for Product Detail View (`detalle.html`)
- **User Request:** "dentro de detalle tambien hay mucha oportunidad" (acompañado de captura de pantalla de `detalle.html`).
- **Critical Issues & Opportunities Identified:**
  1. **Bootstrap Default Blue Eradication:** Bootstrap's `text-primary` and `border-primary` default to `#0d6efd` (electric blue), causing the price `$450.00` and multiple icons to clash with the "Algodón Nórdico" palette. Global overrides to `--craft-primary` (`#8E5B74`) planned in `styles.css`.
  2. **Breadcrumb Craft Ribbon (`.breadcrumb-craft-ribbon`):** Replace plain white card with a stitched ribbon bar with dashed borders and needle/spool dividers.
  3. **Left Column Elevation:** Add `.card-stitched` to the photo card, frame the amigurumi illustration in `.product-photo-stitched-frame` (quilted photo mat with inset running stitch), replace thumbnails with `.thumb-textile-item` with embroidered active states, and upgrade artisan authorship to a warm parchment `.artisan-workshop-seal-card` with golden honey stitches.
  4. **Right Column Elevation:** Style price as `.price-tag-craft` in *Outfit* 800 with currency pill, add `.table-craft-specs` with dashed thread dividers and pill spec icons, frame description in `.story-quote-craft` with a vertical plum seam, and style the UI/UX audit stock switcher as a sleek mini-toolbar.
  5. **Modal Quilted Seams:** Apply `.modal-content-stitched` with dashed inset seams to the checkout and login dialogs.
- **Current Milestone:** Planning Mode complete. Implementation plan generated in `implementation_plan.md` (with `request_feedback: true`).
- **Phase Gate Status:** In Phase 2 Layout & UI refinement. Awaiting user approval of `implementation_plan.md` before executing changes.
