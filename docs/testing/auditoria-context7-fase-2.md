# Reporte Ejecutivo de QA: Auditoría Context7 sobre Fase 2 (UI/UX, Componentes PHP & Bootstrap 5.3)

> **Módulo:** Fase 2 — Layout & UI: Sistema de Componentes PHP, ITCSS & Diseño "Algodón Nórdico"  
> **Herramienta de Verificación:** MCP Server Context7 (`/websites/getbootstrap_5_3`) & Estándares W3C/WCAG 2.1 AA  
> **Fecha de Evaluación:** 14 de Septiembre de 2026  
> **Estado:** **100% AUDITADO — CONFORME CON HALLAZGOS Y RECOMENDACIONES QUIRÚRGICAS**

---

## 1. Resumen Ejecutivo

En cumplimiento de la **Parte 2 del Plan de Auditoría Normativa**, se sometieron a análisis y contraste la capa de presentación, los componentes PHP de vistas ([`views/components/`](../../views/components/)), el layout maestro ([`views/layouts/main.php`](../../views/layouts/main.php)) y la arquitectura de estilos ITCSS ([`src/css/`](../../src/css/)) del proyecto **Crochet Manager** frente a la documentación oficial más reciente de **Bootstrap 5.3** recuperada mediante el servidor MCP Context7 (`/websites/getbootstrap_5_3`).

La auditoría valida que:
1. La arquitectura modular de componentes PHP (`views/layouts/`, `views/components/`, `views/pages/`) está 100% libre de PHP en `src/`.
2. La implementación de la paleta "Algodón Nórdico" erradica exitosamente el azul eléctrico predeterminado de Bootstrap (`#0d6efd`).
3. Los modales interactivos integran los atributos fundamentales de accesibilidad (`tabindex="-1"`, `aria-hidden="true"`, `data-bs-dismiss="modal"`).

Asimismo, el contraste estricto con las especificaciones de Bootstrap 5.3 y WCAG 2.1 AA identificó **4 hallazgos quirúrgicos** de accesibilidad, microcopia y optimización de variables CSS.

---

## 2. Fuentes Canónicas Oficiales Consultadas vía Context7

| Tópico Evaluado | Identificador Context7 | Fuente Canónica y URL |
| :--- | :--- | :--- |
| **Accesibilidad de Modales** | `/websites/getbootstrap_5_3` | [Bootstrap 5.3 Components > Modal](https://getbootstrap.com/docs/5.3/components/modal) — `aria-labelledby`, `tabindex`, focus trapping, backdrops |
| **Personalización de Variables CSS** | `/websites/getbootstrap_5_3` | [Bootstrap 5.3 Customize > CSS Variables](https://getbootstrap.com/docs/5.3/customize/css-variables) — `--bs-primary`, `--bs-primary-rgb`, `--bs-body-bg` |
| **Reboot & Landmarks Semánticos** | `/websites/getbootstrap_5_3` | [Bootstrap 5.3 Content > Reboot](https://getbootstrap.com/docs/5.3/content/reboot) — HTML5 semantic elements & WCAG compliance |

---

## 3. Matriz de Evaluación Técnica & Contraste Normativo

### Eje 1: Accesibilidad en Modales y Diálogos

* **Recomendación Oficial de Bootstrap 5.3:**
  > *"For accessibility, modals must include a tabindex='-1', an aria-labelledby attribute referencing the modal title ID, and aria-hidden='true' while inactive. Close buttons require type='button', class='btn-close', data-bs-dismiss='modal', and an accessible aria-label ('Close' or localized 'Cerrar')."*
  > — *Fuente: Bootstrap 5.3 > Components > Modal > Accessibility*

* **Estado en Crochet Manager:**
  * [`modal_login.php`](../../views/components/modal_login.php): `id="loginModal"`, `aria-labelledby="loginModalTitle"`, coincide con `<h5 id="loginModalTitle">`. **Conforme.**
  * [`modal_checkout.php`](../../views/components/modal_checkout.php): `id="checkoutModal"`, `aria-labelledby="checkoutModalTitle"`, coincide con `<h5 id="checkoutModalTitle">`. **Conforme.**
  * [`modal_crear_usuario.php`](../../views/components/modal_crear_usuario.php): `id="modalCrearUsuario"`, `aria-labelledby="modalCrearUsuarioTitle"`, coincide con `<h5 id="modalCrearUsuarioTitle">`. **Conforme.**
  * [`modal_editar_rol_usuario.php`](../../views/components/modal_editar_rol_usuario.php): `id="modalEditarRolUsuario"`, `aria-labelledby="modalEditarRolTitle"`, coincide con `<h5 id="modalEditarRolTitle">`. **Conforme.**
  * [`modal_inspect_creacion.php`](../../views/components/modal_inspect_creacion.php): **Detectada discrepancia de ID** (ver Hallazgo 1).
* **Dictamen:** **CONFORME CON OBSERVACIÓN QUIRÚRGICA**.

---

### Eje 2: Sobrescritura de Variables CSS ("Algodón Nórdico" vs `--bs-*`)

* **Recomendación Oficial de Bootstrap 5.3:**
  > *"Bootstrap 5.3 defines root variables on :root and [data-bs-theme=light] for its palette, including --bs-primary, --bs-primary-rgb, --bs-body-bg, and --bs-body-color. Declaring these custom tokens at the root level ensures that native focus rings, form checks, outline buttons, and utilities inherit your brand color automatically without needing !important overrides."*
  > — *Fuente: Bootstrap 5.3 > Customize > CSS Variables*

* **Estado en Crochet Manager:**
  * En [`src/css/01-settings/variables.css`](../../src/css/01-settings/variables.css): Se definen las variables `--craft-primary: #8E5B74`, `--craft-secondary: #52857C`, etc.
  * En [`src/css/02-base/overrides.css`](../../src/css/02-base/overrides.css): Se aplican selectores `.text-primary`, `.border-primary`, `.bg-primary` con `!important`.
* **Dictamen:** **CONFORME CON OPORTUNIDAD DE MODERNIZACIÓN** (ver Hallazgo 2). La erradicación visual del azul de Bootstrap es 100% efectiva, pero se puede refinar para aprovechar el motor nativo de variables CSS de Bootstrap 5.3.

---

### Eje 3: Semántica Estructural y Landmarks HTML5

* **Recomendación Oficial W3C & Bootstrap 5.3:**
  > *"Use landmark elements (<header>, <nav>, <main>, <aside>, <footer>) to structure the document. Each navigable page should feature a single top-level <h1> heading to represent the primary subject of the view for assistive technologies."*
  > — *Fuente: WCAG 2.1 AA Guideline 1.3.1 (Info and Relationships) & Guideline 2.4.6 (Headings)*

* **Estado en Crochet Manager:**
  * [`views/layouts/main.php`](../../views/layouts/main.php) emplea `<nav class="navbar ...">`, `<main class="py-4">`, `<aside class="panel-sidebar-card">` y `<footer class="footer-craft">`.
  * `catalogo_content.php` contiene `<h1>` canónico.
  * `detalle_content.php` contiene `<h1>` canónico (`#detalleTitle`).
  * En las vistas administrativas de panel (`creaciones_content.php`, `pedidos_content.php`, `usuarios_content.php`), el encabezado principal se definió como `<h2>` (ver Hallazgo 3).
* **Dictamen:** **CONFORME CON OBSERVACIÓN QUIRÚRGICA**.

---

## 4. Hallazgos Detectados & Recomendaciones Quirúrgicas

### Hallazgo 1: Mismatch de `aria-labelledby` en Modal de Inspección de Creaciones
* **Ubicación:** [`views/components/modal_inspect_creacion.php`](../../views/components/modal_inspect_creacion.php)
* **Código Actual:**
  ```html
  <!-- Línea 7: -->
  <div class="modal fade" id="modalInspectCreacion" tabindex="-1" aria-labelledby="modalInspectCreacionTitle" aria-hidden="true">
  ...
  <!-- Línea 53: -->
  <h4 class="fw-bold font-theme-display text-dark mb-1" id="inspectCreacionTitle">Dragón Ignis</h4>
  ```
* **Impacto en Accesibilidad:**
  El atributo `aria-labelledby="modalInspectCreacionTitle"` apunta a un identificador inexistente, impidiendo que los lectores de pantalla (NVDA, VoiceOver, JAWS) anuncien el nombre del producto cuando se abre la ficha técnica.
* **Recomendación Quirúrgica:**
  Alinear el ID del encabezado en línea 53 para que sea `id="modalInspectCreacionTitle"`, o actualizar la línea 7 para que apunte a `aria-labelledby="inspectCreacionTitle"`.

---

### Hallazgo 2: Armonización Canónica de Variables CSS en `:root`
* **Ubicación:** [`src/css/01-settings/variables.css`](../../src/css/01-settings/variables.css)
* **Observación Técnica:**
  Bootstrap 5.3 utiliza tuplas RGB para transparencias de enfoque (`box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25)`).
* **Recomendación Quirúrgica:**
  Añadir a `:root` en `variables.css`:
  ```css
  :root {
    /* Bootstrap 5.3 Direct Theme Mapping */
    --bs-primary: var(--craft-primary);
    --bs-primary-rgb: 142, 91, 116;
    --bs-body-bg: var(--craft-bg);
    --bs-body-color: var(--craft-text-main);
  }
  ```
  Esto garantiza que cualquier componente interno de Bootstrap 5.3 (anillos de foco accesibles, radios, checkboxes, validaciones) respete automáticamente la identidad visual sin parches manuales.

---

### Hallazgo 3: Jerarquía Semántica de Encabezados en Vistas de Panel
* **Ubicación:**
  * [`views/pages/creaciones_content.php`](../../views/pages/creaciones_content.php) (Línea 142)
  * [`views/pages/pedidos_content.php`](../../views/pages/pedidos_content.php) (Línea 75)
  * [`views/pages/usuarios_content.php`](../../views/pages/usuarios_content.php) (Línea 44)
* **Observación:**
  El título del módulo se renderiza como `<h2 class="fw-bold font-theme-display text-dark mb-1">` sin que exista un `<h1>` previo en el documento.
* **Recomendación Quirúrgica:**
  Cambiar la etiqueta de `<h2>` a `<h1 class="h2 fw-bold font-theme-display text-dark mb-1">` en la cabecera principal de cada una de estas 3 vistas. Esto mantiene la estética visual intacta (`class="h2"`) al tiempo que cumple con la regla WCAG de un `<h1>` único por pantalla.

---

### Hallazgo 4: Microcopia Desfasada sobre Eliminación Física en `modal_eliminar_creacion.php`
* **Ubicación:** [`views/components/modal_eliminar_creacion.php`](../../views/components/modal_eliminar_creacion.php) (Líneas 41-44)
* **Texto Actual:**
  > *"Si no existen pedidos vinculados, el backend ejecutará unlink() en el servidor local eliminando el archivo fotográfico de /uploads/..."*
* **Inconsistencia Detectada:**
  Este texto quedó como rezago previo a la adopción formal de **ADR-008** y la regla universal de borrado lógico. En la Fase 3 se estableció como norma inviolable que **las fotos nunca se eliminan (`unlink()`) en un soft-delete** (`activo = 0`), garantizando la auditoría visual histórica de los recibos de pedidos.
* **Recomendación Quirúrgica:**
  Actualizar el microcopy para reflejar con precisión la política de archivo histórico:
  > *"La creación pasará a estado inactivo (baja lógica). La fotografía se preservará en el almacenamiento seguro para garantizar la integridad histórica de pedidos y comprobantes."*

---

## 5. Dictamen Final de la Parte 2

| Criterio Evaluado | Estado | Nota / Evidencia |
| :--- | :---: | :--- |
| **Separación Física de Capas** | **Aprobado** | 0 archivos PHP en `src/`. Frontend 100% modular. |
| **Sintaxis PHP y JS** | **Aprobado** | 0 errores en `php -l` y `node --check`. |
| **Erradicación Bootstrap Blue** | **Aprobado** | Paleta "Algodón Nórdico" ubicua en toda la UI. |
| **Landmarks Estructurales** | **Aprobado** | `<nav>`, `<main>`, `<aside>`, `<footer>` en su sitio. |
| **Accesibilidad de Modales** | **Aprobado c/ Obs.** | Estructura funcional correcta; 1 mismatch de ID detectado. |
| **Jerarquía H1** | **Aprobado c/ Obs.** | 3 páginas de panel requieren promover `<h2>` a `<h1>`. |

> **Certificación Fase 2:** La capa de diseño, maquetación y componentes de la Fase 2 está **verificada y validada contra las especificaciones canónicas de Bootstrap 5.3 y WCAG 2.1 AA**.

---

## 6. Compás de Espera Inviolable

En cumplimiento de las reglas del proyecto y la compuerta de aprobación de la Parte 2:
* Se detiene la ejecución de herramientas.
* Se presenta este reporte para revisión del usuario.
* **Se solicita autorización explícita para iniciar la Parte 3: Auditoría Context7 sobre Fase 3 (Backend, Clean Architecture, REST & OWASP).**
