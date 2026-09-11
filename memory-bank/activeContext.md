# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Tipografía Display con Personalidad Artesanal (COMPLETADO Y VERIFICADO)
- **User Request:**
  - Instalar una fuente de tipo display que le aporte personalidad temática al sitio (crochet, amigurumis, artesanía textil nórdica).
  - Aplicarla a los encabezados importantes de todas las pantallas públicas (`index.php`, `detalle.php`, etc.).
- **Actions Executed:**
  - **1. Selección e Importación de Tipografía:**
    - Instalada la familia tipográfica **`Fraunces`** (`opsz,wght@9..144,600;700;800`) en [views/layouts/main.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/layouts/main.php) junto a `Fredoka`, `Outfit` y `Plus Jakarta Sans`.
    - `Fraunces` aporta una personalidad orgánica, táctil y de libro artesanal escandinavo con terminales en gota que evocan madejas de lana y ganchillos de crochet.
  - **2. Definición de Tokens en Sistema de Diseño:**
    - Registrado `--craft-font-theme: 'Fraunces', Georgia, serif;` en [src/css/01-settings/variables.css](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/01-settings/variables.css).
    - Creadas clases utilitarias `.font-theme-display` y reglas para `h1`, `h2` y `.display-1` a `.display-6` en [src/css/02-base/typography.css](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/02-base/typography.css).
  - **3. Despliegue en Encabezados Públicos Clave:**
    - **Hero Banner:** Título principal `"Creaciones Amigurumi con Alma y Ternura"` estilizado con `Fraunces` en [src/css/04-components/hero.css](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/04-components/hero.css).
    - **Marca en Navbar:** Título `"Amigurumi Manager"` en [src/css/04-components/navbar.css](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/04-components/navbar.css).
    - **Tarjetas del Catálogo:** Títulos de productos (`"Dragón Ignis"`, `"Mini Suculenta Maceta"`, `"Ajolote Rosado Pastel"`) en [src/css/04-components/cards.css](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/04-components/cards.css).
    - **Ficha Técnica (`detalle.php`):** Título principal `#detalleTitle` y precio `#detallePriceDisplay` en [src/css/04-components/detail.css](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/04-components/detail.css).
    - **Modal de Compra / Encargo:** Encabezados `.modal-title`, `#checkoutModalTitle` y `#modalProductName` en [src/css/04-components/modals.css](file:///Users/angelzaragoza/Desktop/proyecto-web/src/css/04-components/modals.css).
    - **Secciones Públicas y Pie de Página:** Título de estación `"Explorador de Creaciones"`, estado vacío y marca en [views/components/footer.php](file:///Users/angelzaragoza/Desktop/proyecto-web/views/components/footer.php).
  - **4. Verificación Visual en Navegador:**
    - Validado mediante `browser_subagent` con capturas de pantalla de catálogo, detalle y modal de compra. Contraste superior a 14:1 (cumple WCAG 2.1 AAA).
  - **5. Documentación de Reglas:**
    - Actualizada la Sección 2 (Jerarquía Tipográfica) en [.agents/rules/ui-ux-design-system.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.agents/rules/ui-ux-design-system.md).
- **Actions Executed:**
  - **1. Reestructuración de Documentación Técnica (`docs/`) bajo Clean Architecture:**
    - Creado e indexado el concentrador maestro de documentación [docs/README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/README.md) categorizando 17 archivos en 5 dominios claros:
      1. *Base de Datos & Modelos Relacionales:* `database-schema.*`, `data-model.*`, `database-testing.*` sincronizados con `es_sobre_encargo`, `cliente_contacto`, `estado_pago`, pruebas CLI 4.5-4.7 y layout físico.
      2. *Seguridad & Control de Acceso:* `auth-flow.*` actualizado con ciclo de sesión, vista `usuarios.php`, modal dinámico y salvaguarda de protección para el usuario raíz `#1` (`@admin`).
      3. *Contratos de API & Backend:* `api-design.*` actualizado con los endpoints de creación de pedidos manuales, modificación de roles RBAC y respuestas JSON tipadas.
      4. *Arquitectura de Software:* `architecture-refactor-plan.md` y `ui-ux-database-gap-analysis.md` (100% verificado y cerrado).
      5. *Diseño UI/UX & Wireframes:* `wireframes.*` (EN/ES) actualizado con diagramas ASCII de las vistas PHP (`index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`), filtros avanzados de precio y autor, WhatsApp y cálculo de moneda; `ui-ux-skill-report.*` actualizado con banner de resolución completa.
  - **2. Refinamiento de Capacidades en `.agents/`:**
    - **Skill Nativa Autodescubrible:** Creado [.agents/skills/clean-code-architect/SKILL.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.agents/skills/clean-code-architect/SKILL.md) con frontmatter YAML estándar para su detección nativa por el motor de Antigravity.
    - **Reglas Generales de Flujo:** Actualizado [.agents/rules/general.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.agents/rules/general.md) con la secuencia real de fases completadas (Fase 1 BD, Fase 2 Vistas PHP modulares en `views/`, ITCSS en `src/css/`, ES Modules en `src/js/`) y la especificación limpia para la Fase 3.
    - **Reglas de Diseño UI/UX "Algodón Nórdico":** Actualizado [.agents/rules/ui-ux-design-system.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.agents/rules/ui-ux-design-system.md) incorporando las Reglas 16 a 21:
      - Regla 16: Estética del directorio de equipo y badges RBAC (`usuarios.php`).
      - Regla 17: Salvaguarda inviolable de bloqueo para la cuenta raíz `#1` (ID #1 nunca puede ser degradada ni eliminada).
      - Regla 18: Barra de filtrado avanzado de catálogo (Min/Max precio y selector de autor en dos vías).
      - Regla 19: Estándar universal de formateo de moneda (`CurrencyHelper.php` y `currency.js` en céntimos).
      - Regla 20: Enlaces directos de WhatsApp para pedidos (`https://wa.me/...`) y badges tri-estado de cobro.
      - Regla 21: Insignia y tratamiento para confección exclusiva bajo encargo (`es_sobre_encargo = 1`).
    - **Workflow General:** Actualizado [.agents/workflows/general.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.agents/workflows/general.md) alineado con los componentes PHP y la arquitectura limpia de capas.
  - **3. Verificación de Código y Sintaxis:**
    - `100%` de los archivos PHP validados con `php -l` (0 errores).
    - `100%` de los archivos JavaScript validados con `node --check` (0 errores).
- **Current Milestone:** Fase 2 y Frontend 100% terminado, documentado y sincronizado.
- **Next Phase:** Fase 3 (Backend & Conexión PDO con Arquitectura Limpia en `src/` y controladores en `api/`) a la espera de la instrucción explícita del usuario.
