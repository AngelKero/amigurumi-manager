# Tasks 011 · UX/UI Polish

- [x] spec.md + plan.md + tasks.md

## Subfases (gate 3-tier por subfase)

### 4.7.1 — Accesibilidad Core (Focus, Labels, Live Regions)
- [ ] 4.7.1.1 `:focus` visible idéntico a `:focus-visible` en `reset.css` + tokens
- [ ] 4.7.1.2 `aria-label` en stepper ±, botones solo-icono (WhatsApp, cerrar, editar, eliminar)
- [ ] 4.7.1.3 `role="alert"` / `aria-live="polite"` en `#checkoutFeedback`, `#usuarioAlert`, `#editarRolAlert`, `#loginAlert`
- [ ] 4.7.1.4 `.upload-dropzone` navegable por teclado (Enter/Space)
- [ ] 4.7.1.5 Focus trap + restauración en todos los modales (verificar 7 modales)
- [ ] **Gate:** `php tests/test-subfase-4.7.1.php > logs/subfase-4.7.1-cli.log 2>&1` + curl HTTP + reporte `docs/testing/subfase-4.7.1-a11y-core.md`

### 4.7.2 — Validación Inline + Formato Monetario
- [ ] 4.7.2.1 Nuevo `src/js/modules/forms.js`: `validateField()`, `showInline()`, `debounce()`, espejo 422
- [ ] 4.7.2.2 `checkout.js` + `creaciones.js` + `users.js` importan `forms.js` y validan en `blur`
- [ ] 4.7.2.3 Inputs precio/costo: `inputmode="decimal"` + auto-format pesos↔centavos al `blur`
- [ ] 4.7.2.4 Marcado inline en `formulario_content.php`, `modal_checkout.php`, `modal_nuevo_pedido.php`, `modal_crear_usuario.php`
- [ ] **Gate:** `php tests/test-subfase-4.7.2.php > logs/subfase-4.7.2-cli.log 2>&1` + curl HTTP + reporte

### 4.7.3 — Skeletons + Empty States + Stagger Entrance
- [ ] 4.7.3.1 `.skeleton` + `@keyframes skeleton-shimmer` en `forms.css` / `keyframes.css`
- [ ] 4.7.3.2 `catalog.js` / `orders.js` / `creaciones.js`: render skeleton mientras fetch, swap con stagger
- [ ] 4.7.3.3 Empty states: SVG + CTA primaria en `catalogo_content.php`, `pedidos_content.php`, `creaciones_content.php`, `usuarios_content.php`
- [ ] 4.7.3.4 `@keyframes stagger-in` + `.stagger-in` clase; `catalog.js` aplica escalonado 50ms
- [ ] **Gate:** `php tests/test-subfase-4.7.3.php > logs/subfase-4.7.3-cli.log 2>&1` + curl HTTP + reporte

### 4.7.4 — Stepper, Lightbox Touch, Mobile Card Feedback
- [ ] 4.7.4.1 Stepper: `ArrowUp/Down` en input cantidad; `aria-label` en botones ±
- [ ] 4.7.4.2 Lightbox: swipe-down (>50px) cierra; pinch-zoom nativo (touch events)
- [ ] 4.7.4.3 `.card-product:active` scale(0.98) para `@media (hover: none)`
- [ ] 4.7.4.4 Breadcrumb separador `›` en `.breadcrumb-craft-ribbon`
- [ ] **Gate:** `php tests/test-subfase-4.7.4.php > logs/subfase-4.7.4-cli.log 2>&1` + curl HTTP + reporte

### 4.7.5 — Dark Mode + Shortcuts + Celebration + Microcopia
- [ ] 4.7.5.1 Dark mode: `data-theme` toggle en navbar + `localStorage` + `prefers-color-scheme` + overrides CSS
- [ ] 4.7.5.2 Shortcuts: tecla `?` abre modal con tabla `kbd` en panel (navbar + auth.js)
- [ ] 4.7.5.3 Confetti burst CSS-only en `checkoutSuccess` + alta manual pedidos
- [ ] 4.7.5.4 Microcopia: glosario verbos (Guardar/Actualizar/Confirmar) + grep consistencia
- [ ] 4.7.5.5 Sidebar badge pulse animation al cambiar count
- [ ] 4.7.5.6 `beforeunload` en `formulario.php` si form dirty
- [ ] **Gate:** `php tests/test-subfase-4.7.5.php > logs/subfase-4.7.5-cli.log 2>&1` + curl HTTP + reporte

### 4.7.6 — Integración & Regresión Completa
- [ ] 4.7.6.1 Ejecutar `php tests/test-fase-4-acumulado.php` (todas 4.1–4.7)
- [ ] 4.7.6.2 Ejecutar `php tests/cuenta-aserciones.php` (Fase 3: 1.287)
- [ ] 4.7.6.3 Ejecutar `php tests/test-subfase-3.6.5.php` (regresión Fase 3)
- [ ] 4.7.6.4 Marcar criterios `spec.md` como `[x]` al 100%
- [ ] 4.7.6.5 Actualizar `spec/constitution/roadmap.md`: 011 → **Hecho ✅**
- [ ] **Gate final:** suites 4.7.1–4.7.5 + regresión 4 + regresión 3 en verde; reporte `docs/testing/subfase-4.7-ux-ui-polish.md`

## Verificación por tarea (comando)
- CSS/JS: `php -l <php-file>` / `node --check <js-file>`
- Test subfase: `php tests/test-subfase-4.7.x.php > logs/subfase-4.7.x-cli.log 2>&1`
- HTTP: `php -S localhost:8000 & curl ...` (verificar 200, CSP, JSON shape)
- Regresión: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1`
- Cuenta: `php tests/cuenta-aserciones.php`