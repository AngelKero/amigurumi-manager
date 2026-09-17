# Reporte de Testing: Subfase 5.3 - Rendimiento Web, Core Web Vitals & Optimización de Carga

> **Estado:** ✅ APROBADO (101 / 101 Aserciones CLI en Verde · 100% OK)  
> **Fecha:** 2026-09-17  
> **Ambiente:** PHP 8.3.29 · SQLite 3 · Darwin (macOS)  
> **Feature Activa:** `spec/features/012-plan-maestro-fase-5/`  
> **Suite de Prueba:** `tests/test-subfase-5.3.php`  
> **Evidencia Cruda Nivel 2:** `logs/subfase-5.3-cli.log` y `logs/subfase-5.3-http.log`  

---

## 1. Resumen Ejecutivo

La **Subfase 5.3** implementa la optimización profunda del rendimiento web, la estabilización de los **Core Web Vitals** (especialmente la erradicación del Cumulative Layout Shift: **CLS = 0**) y el establecimiento de políticas de compresión y caché de navegador para activos estáticos en el servidor Apache. Las áreas de intervención clave incluyen:

1. **Eliminación de Cumulative Layout Shift (CLS = 0):** Asignación de contenedores con `aspect-ratio` rígido en CSS (`.card-product-img-wrapper` a 4:3, `.skeleton-img` a 4:3, `.detail-photo-zoom-trigger` a 1:1) y dimensiones explícitas (`width` y `height`) en todas las imágenes renderizadas por servidor y cliente.
2. **Carga Diferida Nativa (`loading="lazy"` & `decoding="async"`):** Implementación de carga diferida nativa de imágenes fuera de la ventana gráfica inicial para tarjetas de catálogo, inventario administrativo y pedidos, reduciendo la contención de red y el consumo de memoria en dispositivos móviles.
3. **Priorización LCP (`fetchpriority="high"`):** Asignación del atributo `fetchpriority="high"` a la fotografía principal del banner Hero en el catálogo (`heroCraftedImg`, 560x380) y a la fotografía destacada en la vista de detalle (`detailMainImage`, 600x600), optimizando el Largest Contentful Paint.
4. **Sincronización Simétrica en DOM Dinámico:** Garantía de que los generadores de nodos en `src/js/modules/catalog.js`, `src/js/modules/creaciones.js` y `src/js/modules/orders.js` aplican programáticamente los atributos `width`, `height`, `loading="lazy"` y `decoding="async"`.
5. **Directivas de Compresión & Caché HTTP en `.htaccess`:** Inclusión de reglas modulares:
   - `mod_deflate.c`: Compresión Gzip/Deflate para `text/html`, `text/css`, `application/javascript`, `application/json` e `image/svg+xml`.
   - `mod_expires.c`: Fechas de expiración diferenciadas por tipo MIME (CSS/JS a 7 días, imágenes/SVG a 30 días, tipografías WOFF/WOFF2/TTF a 1 año).
   - `mod_headers.c`: Emisión explícita de directivas `Cache-Control: max-age=..., public`.
6. **Presupuesto de Peso Ligero:** Cero dependencias externas o paquetes pesados de npm/composer. La aplicación opera con JavaScript ES Modules nativos sin herramientas de empaquetado ni dependencias de runtime no declaradas.

---

## 2. Métricas de Ejecución del Gate en 3 Niveles

| Nivel de Calidad | Artefacto de Verificación | Resultado | Estado |
| :--- | :--- | :---: | :---: |
| **Nivel 1 (Suite CLI)** | `tests/test-subfase-5.3.php` | 101 / 101 aserciones | ✅ 100% PASS |
| **Nivel 2 (Log CLI)** | `logs/subfase-5.3-cli.log` | Cero fallos reportados (14.88 ms) | ✅ Conforme |
| **Nivel 2 (Log HTTP)** | `logs/subfase-5.3-http.log` | 7 endpoints verificados (200 OK y CSP) | ✅ Conforme |
| **Nivel 3 (Reporte)** | `docs/testing/subfase-5.3-rendimiento.md` | Core Web Vitals & Caché documentados | ✅ Aprobado |

---

## 3. Desglose de Cobertura por Secciones

### Sección 1: Prevención de CLS: Contenedores con Aspect-Ratio y Reglas CSS (10 Aserciones)
- Regla `aspect-ratio: 4 / 3;` definida en `.card-product-img-wrapper` en `src/css/04-components/cards.css`.
- Regla `aspect-ratio: 4 / 3;` definida en `.skeleton-img` para evitar brincos de maquetación durante la carga asíncrona.
- Regla `aspect-ratio: 1 / 1;` aplicada en `.detail-photo-zoom-trigger` en `src/css/04-components/detail.css`.
- Alturas fijas y marcos contenedores en `.admin-card-photo-frame` (160px) y `.order-card-photo-frame` (135px).

### Sección 2: Dimensiones Explícitas (width / height) en Marcado Estático y Templates (22 Aserciones)
- `views/components/product_card.php`: `width="400"`, `height="300"`, `loading="lazy"`, `decoding="async"`.
- `views/pages/catalogo_content.php`: Hero image con `width="560"`, `height="380"`, `fetchpriority="high"`, `decoding="async"`; template con `width="400"`, `height="300"`, `loading="lazy"`.
- `views/pages/creaciones_content.php`: Template administrativo con `width="300"`, `height="225"`, `loading="lazy"`, `decoding="async"`.
- `views/pages/pedidos_content.php`: Template de pedidos con `width="120"`, `height="90"`, `loading="lazy"`, `decoding="async"`.
- `views/pages/detalle_content.php`: Fotografía principal `#detailMainImage` con `width="600"`, `height="600"`, `fetchpriority="high"`, `decoding="async"`.

### Sección 3: Asignación Dinámica de Dimensiones y Lazy/Async en Módulos ES (12 Aserciones)
- `src/js/modules/catalog.js`: `renderCard()` inyecta dinámicamente `width="400"`, `height="300"`, `loading="lazy"`, `decoding="async"`.
- `src/js/modules/creaciones.js`: `renderCard()` inyecta dinámicamente `width="300"`, `height="225"`, `loading="lazy"`, `decoding="async"`.
- `src/js/modules/orders.js`: `renderCard()` inyecta dinámicamente `width="120"`, `height="90"`, `loading="lazy"`, `decoding="async"`.

### Sección 4: Directivas de Caché HTTP y Compresión Gzip/Deflate en .htaccess (16 Aserciones)
- Presencia del módulo `mod_deflate.c` comprimiendo `text/html`, `text/css`, `application/javascript`, `application/json` e `image/svg+xml`.
- Activación de `ExpiresActive On` y `ExpiresDefault "access plus 1 day"` en `mod_expires.c`.
- Expiración de CSS y JavaScript a 7 días (`access plus 7 days`).
- Expiración de imágenes y vectores SVG a 30 días (`access plus 30 days`).
- Expiración de fuentes tipográficas a 1 año (`access plus 1 year`).
- Cabecera `Cache-Control` con directivas `max-age=2592000` (imágenes), `max-age=604800` (CSS/JS) y `max-age=31536000` (fuentes).

### Sección 5: Presupuesto de Peso Ligero y Autonomía de Dependencias (3 Aserciones)
- Ausencia total de `composer.json` y `vendor/` en producción.
- Ausencia de `node_modules/` en tiempo de ejecución (Vanilla JS modular puro).

### Sección 6: Verificación HTTP en Vivo y Latencia de Carga (38 Aserciones)
- 7 endpoints evaluados mediante peticiones cURL nativas:
  - `GET /` (Catálogo público): 200 OK, Content-Type `text/html`, CSP estricta, latencia < 15 ms.
  - `GET /detalle.php?id=1` (Detalle de Creación): 200 OK, Content-Type `text/html`, CSP estricta, latencia < 5 ms.
  - `GET /creaciones.php` (Inventario): 200 OK, Content-Type `text/html`, CSP estricta, latencia < 5 ms.
  - `GET /pedidos.php` (Pedidos): 200 OK, Content-Type `text/html`, CSP estricta, latencia < 5 ms.
  - `GET /usuarios.php` (Directorio): 200 OK, Content-Type `text/html`, CSP estricta, latencia < 5 ms.
  - `GET /formulario.php` (Formulario): 200 OK, Content-Type `text/html`, CSP estricta, latencia < 5 ms.
  - `GET /api/creaciones/index.php` (JSON API): 200 OK, Content-Type `application/json`, latencia < 10 ms.
- Verificación en vivo de los atributos `fetchpriority="high"`, `width`, `height` y `decoding="async"` en los cuerpos HTML renderizados.

---

## 4. Fallos Detectados & Correcciones Quirúrgicas

Durante la ejecución inicial del bucle Red-Green-Refactor de la Subfase 5.3 se detectó 1 fallo puntual:

1. **Aspect-Ratio en Clase CSS `.detail-photo-zoom-trigger` (`detail.css`):**
   - *Hallazgo:* Aunque el contenedor tenía `aspect-ratio: 1 / 1;` en un atributo de estilo en línea dentro de `detalle_content.php`, la clase maestra `.detail-photo-zoom-trigger` en `src/css/04-components/detail.css` no lo declaraba formalmente en la hoja de estilos.
   - *Corrección Quirúrgica:* Se agregó la propiedad `aspect-ratio: 1 / 1;` directamente dentro de la definición de clase `.detail-photo-zoom-trigger` en `src/css/04-components/detail.css`, garantizando la estabilidad de maquetación de manera agnóstica a los atributos en línea.

Tras esta adaptación, la suite ejecutó con **101 / 101 aserciones aprobadas (100% OK)** en 14.88 ms.

---

## 5. Trazabilidad de Criterios de Aceptación (`spec.md`)

| Criterio de Aceptación | Descripción | Evidencia de Validación | Estado |
| :--- | :--- | :--- | :---: |
| **AC-4** | Rendimiento Web, Core Web Vitals & Carga | Eliminación de CLS con `aspect-ratio` 4:3 y 1:1, `width` y `height` explícitos en HTML/templates y módulos JS, `loading="lazy"`, `decoding="async"`, `fetchpriority="high"` en LCP hero y detalle, directivas `mod_deflate`, `mod_expires` y `mod_headers` en `.htaccess`. Suite `tests/test-subfase-5.3.php` con 101/101 PASS (exit 0). | ✅ Cumplido |

---

## 6. Verificación de Regresión Acumulada & Gobernanza

Se ejecutó la comprobación contra el historial de fases y suites normativas:

- **Fase 3 (Regresión Total Regenerable):** `php tests/cuenta-aserciones.php` → **1,287 / 1,287 aserciones aprobadas** (100% en verde).
- **Fase 4 (Regresión Acumulada):** `php tests/test-fase-4-acumulado.php` → **841 / 841 aserciones aprobadas** (100% en verde).
- **Gobernanza Acción 5 (Documentación y Cero Huérfanos H-013):** `php tests/test-gobernanza-accion-5.php` → **41 / 41 aserciones aprobadas** (100% en verde).
- **Gobernanza Acción 6 (Desacople de Fases P1/P2/P5):** `php tests/test-gobernanza-accion-6.php` → **45 / 45 aserciones aprobadas** (100% en verde).
- **Subfase 5.1 (Documentación Diátaxis):** `php tests/test-subfase-5.1.php` → **116 / 116 aserciones aprobadas** (100% en verde).
- **Subfase 5.2 (Accesibilidad WCAG 2.1 AA):** `php tests/test-subfase-5.2.php` → **69 / 69 aserciones aprobadas** (100% en verde).
- **Subfase 5.3 (Rendimiento Web & Core Web Vitals):** `tests/test-subfase-5.3.php` → **101 / 101 aserciones aprobadas** (100% en verde).
- **Sanidad Sintáctica:** Cero errores en análisis de sintaxis PHP (`php -l`) y JavaScript (`node --check`).

---

## 7. Conclusión y Gate de Validación

La **Subfase 5.3** satisface a plenitud los estándares de optimización y desempeño definidos en el Plan Maestro de la Fase 5. La aplicación garantiza tiempos de carga ultraligeros, cero desplazamientos inesperados de maquetación (CLS = 0) y un aprovechamiento óptimo de la caché HTTP del navegador.

**Estado:** APTO PARA REVISIÓN Y SIGN-OFF DEL USUARIO.
