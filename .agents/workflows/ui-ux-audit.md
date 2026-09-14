---
description: "Workflow-glue que enruta el skill design-auditor (proyecto .agents/skills/ui-ux-reviewer) contra los assets FRONTEND REALES de Crochet Manager y centraliza la salida en el espejo de auditorías del design system. NO contiene reglas normativas (ver .agents/rules/ui-ux-design-system.md)."
---

# UI/UX Heuristic & Design System Audit Workflow (Glue)

> **Fuente normativa del diseño:** `.agents/rules/ui-ux-design-system.md` ("Algodón Nórdico").
> **Salida canónica de auditorías:** `docs/design-system/audits.md`.
> Este workflow solo **enruta** el skill [design-auditor](../../../.agents/skills/ui-ux-reviewer/SKILL.md) a las rutas reales del proyecto.

## Objetos bajo auditoría (rutas REALES del repo, verificadas)
- Vistas de la aplicación activa: `views/pages/*.php` (cableadas a `views/layouts/main.php` + `src/js/main.js`).
- Estilos vivos: `src/css/01-settings/` a `src/css/04-components/` (ITCSS + tokens Algodón Nórdico).
- Comportamiento dinámico: `src/js/modules/*.js` (ES Modules, sin build step).
- Iconografía artesanal: `assets/svg/piezas/`.
- Mockups históricos (solo lectura, NO autoritativos): `docs/archive/*.html`.

## Flujo de ejecución
1. Invocar el skill **design-auditor** con foco en las vistas `views/pages/` y el sistema de tokens `src/css/`, respetando `prefers-reduced-motion` y WCAG.
2. Constrastar cada heurística del design system ([CR-x]/[QW-x], ver `.agents/rules/ui-ux-design-system.md`) contra el CÓDIGO VIVO, no contra los mockups archivados.
3. Verificar reglas de seguridad de UI: `face-ancestors 'none'`, prohibición de `#0d6efd` resultante, **cero interpolación de datos en `innerHTML` sin `escapeHtml`** (`src/js/modules/dom-safe.js`).
4. Volcar el resultado en `docs/design-system/audits.md` (espejo canónico) como nueva pasada fechada; NUNCA en `.docs/` ni en archivos sueltos.
5. Si una regla del design system no es verificable (faltan assets, rutas muertas): marcar como defecto y reportar (P5/P6), sin silenciarlo.

## Reglas del glue
- No duplicar contenido normativo: la regla vive en `.agents/rules/ui-ux-design-system.md`; este workflow solo enruta.
- Rutas apuntan SIEMPRE a archivos existentes; cualquier ruta muerta es un defecto que se reporta y corrige.
- La salida consolida en `docs/design-system/audits.md`; el índice maestro `docs/README.md` la mantiene enlazada (H-013).