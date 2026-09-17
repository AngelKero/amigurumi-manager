# 012 · Plan Maestro de la Fase 5 — Tareas

**Estado:** propuesto (solo planificación · sin código)

> `012` coordina y registra el avance de la **Fase 5**. Cada subfase (5.1 a 5.4) se ejecuta de forma atómica bajo el ciclo Red-Green-Refactor, con **Gate 3-Tier obligatorio** (suite CLI + logs + reporte ejecutivo) y parada en **HALT** para sign-off del usuario.

---

## 1. Especificación & Alineación del Plan Maestro (Prerequisito)

- [x] `spec.md` de 012 creado con objetivos, matriz de subfases y criterios de aceptación.
- [x] `plan.md` de 012 creado con secuencia paso a paso, mitigaciones de riesgos y gobernanza.
- [ ] Validación y aprobación explícita del usuario de `spec.md` y `plan.md` (**HALT inicial**).
- [ ] Actualizar `spec/constitution/roadmap.md` para reflejar la descomposición de la Fase 5 en subfases 5.1–5.4 coordinadas por `012`.

---

## 2. Subfase 5.1: Documentación Diátaxis & Manuales Operativos

- [ ] **Tutoriales (Aprendizaje):**
  - [ ] `docs/tutorials/primera-creacion.md`: Guía para artesanas (alta, costeo, fotos y margen).
  - [ ] `docs/tutorials/primer-encargo-whatsapp.md`: Guía de compra y coordinación con la creadora.
- [ ] **Guías How-To (Operación):**
  - [ ] `docs/how-to/gestion-roles-y-claves.md`: Procedimiento de roles RBAC y recuperación de acceso.
  - [ ] `docs/how-to/cancelacion-y-restitucion-stock.md`: Manejo de devoluciones e inventario atómico.
  - [ ] `docs/how-to/configurar-whatsapp-artesano.md`: Normalización y enlace directo E.164.
- [ ] **Referencia Técnica (Datos & Contratos):**
  - [ ] `docs/api/catalogo-completo-endpoints.md`: Índice consolidado de los 25 endpoints REST y códigos HTTP.
  - [ ] `docs/database/diccionario-datos.md`: Diccionario de datos de tablas, constraints y relaciones SQLite.
- [ ] **Explicación / Arquitectura (Comprensión profunda):**
  - [ ] `docs/explanation/clean-architecture-sin-framework.md`: Principios SOLID y aislamiento de capas.
  - [ ] `docs/explanation/invariantes-seguridad.md`: Los 10 Invariantes Canónicos (R-01 a R-10) explicados.
- [ ] **Integridad Documental:**
  - [ ] Enlazar todos los nuevos `.md` en `docs/README.md` (garantizar regla H-013).
- [ ] **Gate 3-Tier Subfase 5.1:**
  - [ ] Suite CLI: `php tests/test-subfase-5.1.php > logs/subfase-5.1-cli.log 2>&1` (100% verde).
  - [ ] Comprobaciones HTTP curl registradas en `logs/subfase-5.1-http.log`.
  - [ ] Reporte ejecutivo: `docs/testing/subfase-5.1-diataxis.md`.
  - [ ] **HALT:** Aprobación explícita del usuario antes de pasar a la subfase 5.2.

---

## 3. Subfase 5.2: Accesibilidad WCAG 2.1 AA & Refinamiento Visual (011)

- [ ] **Foco & Navegación por Teclado:**
  - [ ] Contorno `:focus` y `:focus-visible` accesible y consistente con pespunte `--craft-primary`.
  - [ ] Navegabilidad por teclado en `.upload-dropzone` (`Enter` / `Space`).
- [ ] **Modales & Feedback Asistivo:**
  - [ ] Focus trap y restauración de foco al trigger al cerrar cualquiera de los 7 modales.
  - [ ] Atributos `aria-label` en steppers `[ - ] [ + ]` y botones solo-icono (WhatsApp, cerrar, editar, eliminar).
  - [ ] Regiones `role="alert"` y `aria-live="polite"` en alertas dinámicas y toasts SweetAlert2.
- [ ] **Percepción de Velocidad & Estados:**
  - [ ] Skeletons de carga ligeros durante `fetch` en catálogo, pedidos y creaciones.
  - [ ] Empty states con ilustraciones SVG artesanales y botones CTA primarios.
- [ ] **Gate 3-Tier Subfase 5.2:**
  - [ ] Suite CLI: `php tests/test-subfase-5.2.php > logs/subfase-5.2-cli.log 2>&1` (100% verde).
  - [ ] Trazas HTTP y reporte ejecutivo `docs/testing/subfase-5.2-a11y-ux.md`.
  - [ ] **HALT:** Aprobación explícita del usuario antes de pasar a la subfase 5.3.

---

## 4. Subfase 5.3: Rendimiento, Core Web Vitals & Optimización de Carga

- [ ] **Eliminación de Cumulative Layout Shift (CLS = 0):**
  - [ ] Dimensiones y `aspect-ratio` explícitos en contenedores de imágenes (`.product-photo-stitched-frame`, vitrina).
- [ ] **Optimización de Carga:**
  - [ ] Atributo nativo `loading="lazy"` y `decoding="async"` en tarjetas de producto.
  - [ ] Directivas de caché HTTP (`Cache-Control`) en `.htaccess` para tipografías, CSS y SVGs.
- [ ] **Presupuesto de Peso:**
  - [ ] Verificación de footprint ligero (cero dependencias externas no declaradas).
- [ ] **Gate 3-Tier Subfase 5.3:**
  - [ ] Suite CLI: `php tests/test-subfase-5.3.php > logs/subfase-5.3-cli.log 2>&1` (100% verde).
  - [ ] Trazas HTTP y reporte ejecutivo `docs/testing/subfase-5.3-rendimiento.md`.
  - [ ] **HALT:** Aprobación explícita del usuario antes de pasar a la subfase 5.4.

---

## 5. Subfase 5.4: Hardening Final, Empaquetado & Release Readiness

- [ ] **Auditoría de Seguridad de Directorios:**
  - [ ] Verificación de protección `.htaccess` (HTTP 403) en `app/`, `database/`, `logs/`, `spec/`, `.agents/`.
- [ ] **Script de Verificación Pre-Vuelo:**
  - [ ] Crear herramienta CLI `check-produccion.php` que verifique entorno (PHP 8.1+, extensions, permisos de escritura).
- [ ] **Guía de Despliegue en Producción:**
  - [ ] `docs/guides/despliegue-produccion.md` con checklist de instalación limpia para Apache / Nginx.
- [ ] **Regresión Global Acumulada:**
  - [ ] Ejecución de suite acumulada de Fase 4 (`841/841`) y Fase 3 (`1,287`).
  - [ ] Suite CLI: `php tests/test-subfase-5.4.php > logs/subfase-5.4-cli.log 2>&1`.
  - [ ] Reporte ejecutivo: `docs/testing/subfase-5.4-release.md`.
  - [ ] **HALT:** Aprobación explícita del usuario para el cierre definitivo.

---

## 6. Verificación & Cierre de la Fase 5

- [ ] Todos los Criterios de Aceptación (AC-1 a AC-8) de `spec.md` validados `[x]`.
- [ ] `spec/constitution/roadmap.md` actualizado con la Fase 5 como **Hecho ✅**.
- [ ] Reporte final de entrega y release readiness.
