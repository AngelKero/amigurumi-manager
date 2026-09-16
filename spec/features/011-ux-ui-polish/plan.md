# 011 · UX/UI Polish — Plan

**Estado:** propuesto (sin código) · léase con `spec.md`

## Enfoque

Clean Architecture respetando capas:
- **CSS** (`src/css/04-components/*.css`, `02-base/reset.css`, `03-animations/keyframes.css`): tokens, focus, skeletons, animaciones, dark mode.
- **JS Modules** (`src/js/modules/*.js`): inline validation, stepper keyboard, lightbox touch, focus restore, confetti, shortcuts, beforeunload.
- **Views** (`views/components/*.php`, `views/pages/*.php`): `aria-*`, skeleton markup, empty-state SVG + CTA, price input formatting.
- **Tests** (`tests/test-subfase-4.7.php` + regresión): aserciones CLI + curl HTTP por subfase.
- **Docs** (`docs/testing/subfase-4.7-ux-ui-polish.md`): reporte ejecutivo.

Cero SQL fuera de `app/Repositories/`. Controladores `api/` ≤ 60 líneas. H-004: `textContent`/`escapeHtml` only.

## Implementación

| Capa | Archivo(s) | Cambio |
| :--- | :--- | :--- |
| **CSS base** | `src/css/02-base/reset.css` | `:focus` visible = `:focus-visible`; `--focus-ring` token |
| **CSS comp.** | `src/css/04-components/forms.css` | `.skeleton` shimmer; `.form-field` inline validation states (`.has-error`, `.has-success`); `.upload-dropzone:focus` |
| **CSS comp.** | `src/css/04-components/modals.css` | Focus trap verification; `.qty-stepper` keyboard styles |
| **CSS comp.** | `src/css/04-components/detail.css` | Lightbox swipe/pinch; `.card-product:active` mobile |
| **CSS comp.** | `src/css/04-components/cards.css` | Stagger entrance (`.stagger-in`); confetti keyframes |
| **CSS comp.** | `src/css/04-components/navbar.css` | Dark toggle; shortcut `?` modal |
| **CSS comp.** | `src/css/04-components/sidebar.css` | Badge count pulse animation |
| **CSS comp.** | `src/css/03-animations/keyframes.css` | `@keyframes stagger-in`, `confetti-burst`, `skeleton-shimmer`, `pulse-badge` |
| **CSS theme** | `src/css/01-settings/tokens.css` | Dark mode overrides (`@media (prefers-color-scheme: dark)` + `[data-theme="dark"]`) |
| **JS Auth** | `src/js/modules/auth.js` | Focus restore helper; shortcut `?` listener (panel pages) |
| **JS Checkout** | `src/js/modules/checkout.js` | Stepper ArrowUp/Down; inline validation on blur; inline feedback |
| **JS Detail** | `src/js/modules/detail.js` | Lightbox swipe-down close; pinch-zoom; focus trap |
| **JS Catalog** | `src/js/modules/catalog.js` | Skeleton render + stagger-in class; empty state CTA |
| **JS Orders** | `src/js/modules/orders.js` | Skeleton render; confetti on manual create |
| **JS Creaciones** | `src/js/modules/creaciones.js` | Price input auto-format (pesos↔centavos); beforeunload |
| **JS Forms** | `src/js/modules/forms.js` (nuevo) | Shared inline validation logic (debounce, mirror 422) |
| **Views Forms** | `views/pages/formulario_content.php` | Price inputs `inputmode="decimal"`; inline validation markup |
| **Views Modal** | `views/components/modal_checkout.php` | Stepper `aria-label`; inline feedback container |
| **Views Modal** | `views/components/modal_nuevo_pedido.php` | Price input formatting |
| **Views Modal** | `views/components/modal_crear_usuario.php` | Inline validation markup |
| **Views Empty** | `views/pages/catalogo_content.php`, `pedidos_content.php`, `creaciones_content.php`, `usuarios_content.php` | SVG + CTA primary |
| **Views Lightbox** | `views/components/modal_lightbox_detalle.php` | Touch events for swipe/pinch |
| **Views Navbar** | `views/components/navbar.php` | Dark toggle + shortcuts modal trigger |
| **Tests** | `tests/test-subfase-4.7.php` | 7 subfases (4.7.1–4.7.7) con gate 3-tier |
| **Docs** | `docs/testing/subfase-4.7-ux-ui-polish.md` | Reporte ejecutivo |

## Decisiones

- **Decisión:** Usar `inputmode="decimal"` + formateo JS en lugar de `type="number"` step para precios.
  - Alternativa: `type="number" step="0.01"` — descartada por inconsistencia cross-browser y paso 0.01 = 1 centavo confuso.
- **Decisión:** Skeleton cards reutilizan markup de `.card-product` con clase `.skeleton` + shimmer CSS.
  - Alternativa: spinners genéricos — descartada por no transmitir estructura de contenido.
- **Decisión:** Confetti CSS-only (keyframes + `::before`/`::after` en contenedor) sin librería externa.
  - Alternativa: canvas-confetti — descartada por peso (>10KB) y CSP.
- **Decisión:** Dark mode via `data-theme="dark"` en `<html>` + `localStorage` + `prefers-color-scheme`.
  - Alternativa: solo CSS `prefers-color-scheme` — descartada por falta de control manual.
- **Decisión:** Inline validation compartida en `src/js/modules/forms.js` (ES module) importada por `checkout.js`, `creaciones.js`, `users.js`.
  - Alternativa: duplicar lógica — descartada por DRY y consistencia de mensajes.

## Riesgos

| Riesgo | Mitigación |
| :--- | :--- |
| **Regresión visual en dark mode** (tokens no cubren todo) | Audit visual completo tras activar; fallback `prefers-color-scheme` solo si toggle off |
| **Focus trap roto en modales anidados** (ej. checkout → login) | Test manual + `FocusTrap` helper centralizado en `auth.js` |
| **Skeleton flash en conexiones rápidas** | `requestAnimationFrame` + mínimo 300ms display antes de swap |
| **Pinch-zoom interfiere con swipe** | Umbral direccional: vertical > horizontal = swipe; dos dedos = zoom |
| **Confetti rompe CSP** | Keyframes CSS-only, sin `inline-script`, sin canvas |
| **beforeunload molesta en mobile** | Solo en `formulario.php` (admin/artesano), no en checkout público |
| **Microcopia inconsistente en español** | Glosario único en `docs/design-system/microcopia.md` + grep de verbos |