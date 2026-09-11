# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Reestructuración Integral de Documentación & Refinamiento de Capacidades de Agente (COMPLETADO)
- **User Request:**
  - Realizar una reestructuración completa de toda la documentación en `docs/` siguiendo la habilidad `clean-code-architect.md`.
  - Revisar todos los cambios realizados en el proyecto, actualizar todo lo que haya cambiado.
  - Con lo aprendido, refinar las capacidades del agente en `.agents/` (skills, rules, workflows).
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
