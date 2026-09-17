# Reporte de Testing: Subfase 5.2 - Accesibilidad WCAG 2.1 AA & Refinamiento Visual

> **Estado:** ✅ APROBADO (69 / 69 Aserciones CLI en Verde · 100% OK)  
> **Fecha:** 2026-09-17  
> **Ambiente:** PHP 8.3.29 · SQLite 3 · Darwin (macOS)  
> **Feature Activa:** `spec/features/012-plan-maestro-fase-5/`  
> **Suite de Prueba:** `tests/test-subfase-5.2.php`  
> **Evidencia Cruda Nivel 2:** `logs/subfase-5.2-cli.log` y `logs/subfase-5.2-http.log`  

---

## 1. Resumen Ejecutivo

La **Subfase 5.2** implementa la consolidación de la accesibilidad digital de acuerdo con el estándar **WCAG 2.1 Nivel AA**, así como el refinamiento visual de la interfaz del sistema de diseño **Algodón Nórdico**. Las áreas de intervención prioritarias abarcan:

1. **Navegación Completa por Teclado & Foco Visible:** Definición de tokens de foco accesible (`--focus-ring-width: 2.5px`, `--focus-ring-color: #8E5B74`) y estilos explícitos `:focus-visible` con contorno de 2px en campos de formulario, botones y enlaces. Soporte para activación de zonas de arrastre (`dropzone.js`) mediante teclas `Enter` y `Espacio` con semántica ARIA (`role="button"`, `tabindex="0"`, `aria-label`).
2. **Módulo de Accesibilidad (`src/js/modules/a11y.js`):** Arquitectura cliente orientada a la restauración automática de foco hacia el elemento activador al cerrarse cualquier modal de Bootstrap (`show.bs.modal` y `hidden.bs.modal`), control de cantidad acotado con flechas de teclado (`ArrowUp` / `ArrowDown`) y activación accesible de botones emulados con `role="button"`.
3. **Semántica ARIA & Regiones Vivas (`aria-live="polite"`):** Asignación de etiquetas `aria-label` en controles de incremento/decremento de stepper, botones de acción sin texto visible (inspeccionar, modificar rol, restablecer contraseña, dar de baja lógica) y anuncios accesibles de estado en cajas de alerta de modales y formularios.
4. **Placeholders de Carga Esquelética (Skeleton Loading) & Movimiento Inclusivo:** Componentes `.skeleton`, `.skeleton-card` con animación `@keyframes skeleton-shimmer` y `.stagger-in` con `@keyframes cardFadeUp`. Cumplimiento estricto de **WCAG 2.3.3** mediante la regla `@media (prefers-reduced-motion: reduce)` suprimiendo traslaciones y animaciones continuas para usuarios con sensibilidad vestibular.
5. **Estados Vacíos Coherentes con Ilustración Artesanal:** Estandarización de pantallas vacías con la ilustración vectorial `empty-basket.svg`, microcopy descriptivo y botón de llamada a la acción primario (`btn-craft-stitched`) en Catálogo (`#emptyCatalogState`), Pedidos (`#emptyOrdersGrid`), Creaciones (`#emptyCreacionesState`) y Directorio de Usuarios (`#emptyStateUsuarios`).
6. **Optimización de Entrada Numérica & Micro-interacciones Táctiles:** Inclusión del atributo `inputmode="decimal"` en inputs de moneda (`inputPrecio`, `inputCosto`), sustitución del separador de breadcrumb por chevron estilizado `›` en `detail.css`, y feedback táctil inmediato mediante `@media (hover: none)` con escala `0.985` al presionar tarjetas de producto en dispositivos móviles.

---

## 2. Métricas de Ejecución del Gate en 3 Niveles

| Nivel de Calidad | Artefacto de Verificación | Resultado | Estado |
| :--- | :--- | :---: | :---: |
| **Nivel 1 (Suite CLI)** | `tests/test-subfase-5.2.php` | 69 / 69 aserciones | ✅ 100% PASS |
| **Nivel 2 (Log CLI)** | `logs/subfase-5.2-cli.log` | Cero fallos reportados (38.69 ms) | ✅ Conforme |
| **Nivel 2 (Log HTTP)** | `logs/subfase-5.2-http.log` | 5 vistas verificadas (200 OK y CSP) | ✅ Conforme |
| **Nivel 3 (Reporte)** | `docs/testing/subfase-5.2-a11y-ux.md` | Estándar WCAG 2.1 AA documentado | ✅ Aprobado |

---

## 3. Desglose de Cobertura por Secciones

### Sección 1: Foco Visible Accesible y Navegación por Teclado (9 Aserciones)
- Tokens `--focus-ring-width` y `--focus-ring-color` presentes en `src/css/02-base/reset.css`.
- Reglas `:focus-visible` aplicadas universalmente con `outline: 2px solid var(--craft-primary)`.
- Manejo de teclas `Enter` y `' '` (Espacio) en `src/js/modules/dropzone.js`.
- Atributos `tabindex="0"`, `role="button"` y `aria-label="Zona para arrastrar y soltar o hacer clic para seleccionar fotografía de la creación"` en `views/pages/formulario_content.php`.

### Sección 2: Módulo a11y.js: Focus Trap, Restauración y Stepper (7 Aserciones)
- Módulo `src/js/modules/a11y.js` exporta `initA11y()`.
- Captura del disparador en eventos `show.bs.modal` y retorno garantizado del foco en `hidden.bs.modal`.
- Interceptación de `ArrowUp` y `ArrowDown` en elementos con `.btn-stepper` para modificar la cantidad respetando cotas mínimas y máximas de inventario.
- Integración en el punto de entrada `src/js/main.js`.

### Sección 3: Atributos ARIA en Steppers, Botones e Indicadores Dinámicos (16 Aserciones)
- Atributos `aria-label="Disminuir cantidad"`, `aria-label="Aumentar cantidad"` y `aria-label="Cantidad a solicitar"` en `views/components/modal_checkout.php`.
- Regiones dinámicas con `role="alert"` y `aria-live="polite"` en `#checkoutFeedback`, `#usuarioAlert`, `#editarRolAlert`, `#loginAlert` y `#nuevoPedidoAlert`.
- Botones de acción en tarjetas de inventario y pedidos con `aria-label` descriptivos de su función concreta (`stockDec`, `stockInc`, `inspectBtn`, `editLink`, `deleteBtn`).
- Inyección dinámica de `aria-label` en botones de edición de rol y reseteo de contraseña en `src/js/modules/users.js`.

### Sección 4: Skeletons de Carga y Micro-animaciones Inclusivas (7 Aserciones)
- Clase `.skeleton` en `src/css/04-components/cards.css` con degradado lineal animado.
- Definición de animación `@keyframes skeleton-shimmer` y `@keyframes cardFadeUp` en `src/css/03-animations/keyframes.css`.
- Clase de entrada `.stagger-in` para suavizar el renderizado asíncrono.
- Bloque `@media (prefers-reduced-motion: reduce)` desactivando animaciones continuas y fijando fondos neutros.
- Tarjetas esqueléticas `.skeleton-card` integradas en `views/pages/catalogo_content.php` dentro de `#catalogLoadingState`.

### Sección 5: Estados Vacíos con SVG Artesanal y Botón CTA Primario (12 Aserciones)
- Rejilla del catálogo con contenedor `#emptyCatalogState`, vector temático `empty-basket` y botón `#btnResetFiltersEmpty`.
- Gestión de pedidos con contenedor `#emptyOrdersGrid`, vector temático y botón primario `#btnNuevoPedidoEmpty`.
- Gestión de inventario de creaciones con `#emptyCreacionesState`, ilustración y botón `#btnResetEmptyCreaciones`.
- Directorio de usuarios con `#emptyStateUsuarios`, ilustración artesanal y botón `#btnCrearUsuarioEmpty`.

### Sección 6: Formulario, Moneda, Breadcrumb y Toque Móvil (3 Aserciones)
- Atributo `inputmode="decimal"` asignado a `#inputPrecio` e `#inputCosto` en `views/pages/formulario_content.php` para desplegar el teclado numérico óptimo en dispositivos móviles táctiles.
- Separador de migas de pan `.breadcrumb-craft-ribbon` actualizado con glifo chevron `›` y cuerpo tipográfico de `1.1rem` en `src/css/04-components/detail.css`.
- Consulta `@media (hover: none)` con micro-animación `:active` escalando suavemente las tarjetas al tacto en pantallas táctiles.

### Sección 7: Verificación HTTP en Vivo de las Vistas Optimizadas (15 Aserciones)
- `GET /` (Catálogo): HTTP 200 OK con cabecera `Content-Security-Policy`.
- `GET /pedidos.php` (Pedidos): HTTP 200 OK con cabecera `Content-Security-Policy`.
- `GET /creaciones.php` (Creaciones): HTTP 200 OK con cabecera `Content-Security-Policy`.
- `GET /usuarios.php` (Directorio de Usuarios): HTTP 200 OK con cabecera `Content-Security-Policy`.
- `GET /formulario.php` (Formulario): HTTP 200 OK con cabecera `Content-Security-Policy`.
- Sanidad sintáctica con `node --check src/js/main.js` (código de salida 0).

---

## 4. Fallos Detectados & Correcciones Quirúrgicas

Durante el ciclo Red-Green-Refactor de la Subfase 5.2, la suite CLI reportó inicialmente 12 fallos (57 exitosas / 12 fallidas). Las adaptaciones quirúrgicas ejecutadas fueron:

1. **Atributos `aria-label` en Botones Dinámicos de Usuarios (`users.js`):**
   - *Hallazgo:* Los botones creados dinámicamente en `renderUserRow` (`btnEdit` y `btnReset`) carecían de atributo de accesibilidad para lectores de pantalla.
   - *Corrección Quirúrgica:* Se incorporó `btnEdit.setAttribute('aria-label', ...)` y `btnReset.setAttribute('aria-label', ...)` incluyendo el nombre de usuario asociado (`@${u.username}`).
2. **Keyframes de Carga y Entrada Suave (`keyframes.css`):**
   - *Hallazgo:* Los fotogramas clave `@keyframes skeleton-shimmer` y `@keyframes cardFadeUp` no se encontraban definidos en la capa de animación.
   - *Corrección Quirúrgica:* Se agregaron en `src/css/03-animations/keyframes.css` especificando el desplazamiento horizontal de fondo para skeletons y la elevación vertical suave (14px a 0px) para las tarjetas entrantes.
3. **Clases de Skeleton, Stagger y Preferencia de Movimiento Reducido (`cards.css`):**
   - *Hallazgo:* `cards.css` no contaba con las clases utilitarias de esqueleto ni con la guardia de accesibilidad para usuarios sensibles al movimiento.
   - *Corrección Quirúrgica:* Se implementaron `.skeleton`, `.skeleton-card`, `.skeleton-img`, `.skeleton-text`, `.skeleton-btn`, `.stagger-in` y las directivas `@media (prefers-reduced-motion: reduce)` con `animation: none !important;` y fondos fijos sin transiciones.
4. **Marcado Esquelético en el Estado de Carga del Catálogo (`catalogo_content.php`):**
   - *Hallazgo:* `#catalogLoadingState` únicamente presentaba un spinner genérico centrado.
   - *Corrección Quirúrgica:* Se integró una rejilla de 3 tarjetas `.skeleton-card` que simula la silueta de imagen, título, metadatos y botón de compra mientras concluye la petición asíncrona del catálogo.
5. **Teclado Táctil para Moneda e Insumos (`formulario_content.php`):**
   - *Hallazgo:* Los campos de precio y costo utilizaban `type="number"` sin especificar `inputmode="decimal"`, forzando el teclado alfanumérico en ciertos navegadores móviles.
   - *Corrección Quirúrgica:* Se agregó `inputmode="decimal"` a ambos controles.
6. **Separador Estilizado de Miga de Pan (`detail.css`):**
   - *Hallazgo:* El separador entre elementos de la miga de pan utilizaba el punto tipográfico `•` en lugar del chevron `›`.
   - *Corrección Quirúrgica:* Se actualizó la propiedad `content: "›"` con tamaño de `1.1rem` y color suave en `src/css/04-components/detail.css`.

Tras estas intervenciones, la suite pasó a **69 / 69 aserciones exitosas (100% OK)**.

---

## 5. Trazabilidad de Criterios de Aceptación (`spec.md`)

| Criterio de Aceptación | Descripción | Evidencia de Validación | Estado |
| :--- | :--- | :--- | :---: |
| **AC-3** | Accesibilidad WCAG 2.1 AA & Refinamiento Visual | Módulo `a11y.js`, focus trap y retorno de foco en modales, navegación por teclado en dropzone y stepper, `aria-label` en botones y steppers, regiones `aria-live="polite"`, skeletons con `prefers-reduced-motion`, estados vacíos consistentes con ilustración SVG y botón primario, `inputmode="decimal"`. Suite `tests/test-subfase-5.2.php` con 69/69 PASS (exit 0). | ✅ Cumplido |

---

## 6. Verificación de Regresión Acumulada & Gobernanza

Se ejecutó la batería completa de verificación para asegurar cero impacto regresivo:

- **Fase 3 (Regresión Total Regenerable):** `php tests/cuenta-aserciones.php` → **1,287 / 1,287 aserciones aprobadas** (100% en verde).
- **Fase 4 (Regresión Acumulada):** `php tests/test-fase-4-acumulado.php` → **841 / 841 aserciones aprobadas** (100% en verde).
- **Gobernanza Acción 5 (Documentación y Cero Huérfanos H-013):** `php tests/test-gobernanza-accion-5.php` → **41 / 41 aserciones aprobadas** (100% en verde).
- **Gobernanza Acción 6 (Desacople de Fases P1/P2/P5):** `php tests/test-gobernanza-accion-6.php` → **45 / 45 aserciones aprobadas** (100% en verde).
- **Subfase 5.1 (Documentación Diátaxis):** `php tests/test-subfase-5.1.php` → **116 / 116 aserciones aprobadas** (100% en verde).
- **Sanidad Sintáctica:** Cero errores en análisis de sintaxis PHP (`php -l`) y JavaScript (`node --check`).

---

## 7. Conclusión y Gate de Validación

La **Subfase 5.2** ha completado de forma impecable el 3-Tier Quality Gate. La plataforma cuenta ahora con soporte integral de accesibilidad WCAG 2.1 AA, navegación fluida por teclado, micro-interacciones respetuosas con la salud vestibular del usuario y estados visuales pulidos en todos los módulos de la aplicación.

**Estado:** APTO PARA REVISIÓN Y SIGN-OFF DEL USUARIO.
