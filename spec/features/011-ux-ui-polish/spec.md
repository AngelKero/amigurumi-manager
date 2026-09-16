# 011 · UX/UI Polish & Accesibilidad (Subfase 4.7)

**Estado:** propuesto (solo spec · sin código)

## Qué hace

Mejora la experiencia de usuario, accesibilidad y pulido visual de Crochet Manager
en todas las pantallas clave (catálogo, ficha de detalle, checkout, panel de
artesano, formularios y modales) aplicando estándares WCAG 2.1 AA, heurísticas
de Nielsen y el sistema de diseño "Algodón Nórdico" sin romper la arquitectura
Clean Architecture existente.

Cubre 20 mejoras priorizadas:

| # | Área | Mejora |
|---|------|--------|
| 1 | **Accesibilidad** | Foco visible consistente (`:focus` + `:focus-visible`) en inputs, botones, modales |
| 2 | **Accesibilidad** | Focus trap en modales con restauración al cerrar (Bootstrap 5 + verificación) |
| 3 | **Accesibilidad** | `aria-label` en stepper ± y botones solo-icono |
| 4 | **Accesibilidad** | Regiones `aria-live="polite"` en feedbacks dinámicos (checkout, usuarios, roles) |
| 5 | **Accesibilidad** | Dropzone navegable por teclado (Enter/Space) |
| 6 | **Formularios** | Validación inline en `blur`/`input` con debounce (verde ✓ / rojo ✗ + mensaje espejo 422) |
| 7 | **Carga** | Skeleton loaders en catálogo, pedidos, creaciones mientras `fetch` resuelve |
| 8 | **Estados vacíos** | Ilustración SVG + CTA primaria en cada empty state (catálogo, pedidos, creaciones, usuarios) |
| 9 | **Stepper** | Soporte teclado `ArrowUp/ArrowDown` + `aria-label` en input cantidad |
| 10 | **Inputs monetarios** | Formato `0.00` con `inputmode="decimal"` + auto-format al `blur` (centavos → pesos) |
| 11 | **Móvil** | `:active` scale/ripple en `.card-product` para `@media (hover: none)` |
| 12 | **Lightbox** | Swipe-down para cerrar + pinch-zoom nativo (touch events) |
| 13 | **Breadcrumb** | Separador estándar `›` o `/` (opcional, mantener estilo craft si se decide) |
| 14 | **Sidebar** | Animación `scale` breve en badge counts al cambiar |
| 15 | **Formulario** | `beforeunload` warning si hay cambios sin guardar |
| 16 | **Microcopia** | Unificación de verbos (Guardar/Confirmar/Actualizar) y tonos en tooltips/hints |
| 17 | **Entrada** | Staggered animation escalonada en tarjetas del catálogo |
| 18 | **Celebración** | Confetti/emoji burst en pedido exitoso (checkout + panel) |
| 19 | **Dark mode** | Variables CSS ya existentes + media query `prefers-color-scheme: dark` + toggle manual |
| 20 | **Atajos** | Hoja de atajos (`?` key) con `kbd` visible en panel |

## Por qué

- **Accesibilidad real**: hoy hay barreras para teclado/lector de pantalla (focos, labels, live regions).
- **Percepción de velocidad**: skeletons + transiciones reducen la sensación de lentitud en `fetch`.
- **Confianza artesanal**: micro-interacciones (stagger, confetti, animaciones) refuerzan la marca "hecho a mano".
- **Consistencia**: microcopia unificada evita confusión ("Guardar" vs "Confirmar" vs "Actualizar").
- **Potencia**: dark mode + atajos + pinch-zoom satisfacen a usuarios avanzados sin molestar a novatos.
- **Cumplimiento**: WCAG 2.1 AA + heurísticas Nielsen (visibilidad de estado, control usuario, prevención errores).

## Criterios de aceptación

- [ ] **A11y**: Todos los elementos interactivos tienen foco visible idéntico en `:focus` y `:focus-visible` (outline 2.5px dashed `--craft-primary`).
- [ ] **A11y**: Abrir y cerrar cualquier modal (`checkoutModal`, `modalEliminarCreacion`, `modalInspeccionarPedido`, `modalCrearUsuario`, `modalEditarRolUsuario`, `modalLogin`, `detailLightboxModal`) restaura el foco al trigger.
- [ ] **A11y**: Stepper ± y botones solo-icono (WhatsApp, cerrar, editar, eliminar) tienen `aria-label` descriptivo.
- [ ] **A11y**: `#checkoutFeedback`, `#usuarioAlert`, `#editarRolAlert`, `#loginAlert` usan `role="alert"` o `aria-live="polite"`.
- [ ] **A11y**: `.upload-dropzone` responde a `Enter`/`Space` para abrir selector de archivos.
- [ ] **Form**: `blur` en cualquier input requerido muestra validación inline (icono ✓/✗ + texto rojo) antes del submit; coincide con mensajes 422 del backend.
- [ ] **Carga**: Catálogo (`index.php`), panel pedidos (`pedidos.php`), panel creaciones (`creaciones.php`) muestran skeleton cards (mismo markup que `.card-product` + `.skeleton` shimmer) mientras `fetch` resuelve.
- [ ] **Empty**: Cada estado vacío (catálogo, pedidos, creaciones, usuarios) muestra SVG ilustrativo + botón CTA primaria ("Crear primera pieza", "Explorar catálogo", etc.).
- [ ] **Stepper**: Input cantidad responde a `ArrowUp`/`ArrowDown`; botones ± tienen `aria-label="Aumentar/Disminuir cantidad"`.
- [ ] **Moneda**: Inputs de precio/costo en `formulario.php` y `modal_nuevo_pedido.php` aceptan `0.00`, formatean a pesos al `blur` y envían centavos al servidor.
- [ ] **Móvil**: `.card-product` tiene `transform: scale(0.98)` en `:active` para `@media (hover: none)`.
- [ ] **Lightbox**: Swipe-down (>50px) cierra el modal; pinch-zoom nativo funcional en `detailLightboxModal`.
- [ ] **Breadcrumb**: Separador `›` (o `/`) en `.breadcrumb-craft-ribbon` sin romper estilo craft.
- [ ] **Sidebar**: Badge counts (`#sidebarBadgeCreaciones`, `#sidebarBadgePedidos`, `#sidebarBadgeUsuarios`) animan `transform: scale(1.2)` 150ms al cambiar.
- [ ] **Formulario**: `beforeunload` muestra aviso nativo si `formulario.php` tiene cambios sin guardar.
- [ ] **Microcopia**: Todos los tooltips/hints/botones usan verbo consistente: "Guardar" (crear), "Actualizar" (editar), "Confirmar" (acciones irreversibles).
- [ ] **Entrada**: Tarjetas del catálogo aparecen con `opacity:0 → 1` + `translateY(20px → 0)` escalonado 50ms/ tarjeta.
- [ ] **Celebración**: `checkoutSuccess` y panel pedidos (alta manual) disparan confetti/emoji burst (CSS-only o canvas ligero <2KB).
- [ ] **Dark**: Toggle en navbar + persistencia `localStorage` + `prefers-color-scheme`; variables CSS `--craft-bg`, `--craft-surface`, `--craft-text-*` ya definidas.
- [ ] **Atajos**: Tecla `?` abre modal con tabla de atajos (`kbd`) en panel (creaciones, pedidos, usuarios).

## Fuera de alcance

- Rediseño visual completo (eso es Feature 002, ya hecha).
- Pasarelas de pago (prohibido por misión).
- Internacionalización i18n (solo ES por ahora).
- PWA / Service Worker / Offline.
- Notificaciones push / email.