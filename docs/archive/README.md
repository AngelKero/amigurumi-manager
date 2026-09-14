# Archivo Histórico (docs/archive/)

> 🤖 **NO AUTORITATIVO.** Esta carpeta conserva documentación obsoleta y mockups HTML preliminares
> por integridad de rastro (Gobernanza · Acción 5 · H-022). NADA de aquí debe usarse como fuente
> de decisión: consúltese la fuente viva en `docs/` o las reglas `.agents/rules/`.

## Índice del histórico

| Documento | Tipo | Estado | Reemplazado por |
| :--- | :--- | :--- | :--- |
| `architecture-refactor-plan.md` | Plan | Obsoleto | `docs/architecture/` + ADRs vigentes |
| `index.html` | Mockup UI | Obsoleto | `index.php` + `views/layouts/main.php` + `docs/design-system/` |
| `detalle.html` | Mockup UI | Obsoleto | `detalle.php` + `views/pages/` + `docs/design-system/` |
| `formulario.html` | Mockup UI | Obsoleto | `formulario.php` + `views/pages/` + `docs/design-system/` |
| `pedidos.html` | Mockup UI | Obsoleto | `pedidos.php` + `views/pages/` + `docs/design-system/` |
| `wireframes.html` | Mockup UI | Obsoleto | `docs/design-system/wireframes.md` |

## Protocolo de archivado

Regla canónica: `.agents/rules/docs-source-of-truth.md` → **"Ciclo de Vida Documental: Deprecación y Archivado"** (basado en el hallazgo H-022). Resumen:

1. Un doc obsoleto se mueve aquí con cabecera `🤖 ARQUIVADO — OBSOLETO` + fecha + reemplazo.
2. Se añade a este índice con estado y motivo.
3. Se desenlaza del índice maestro `docs/README.md` y de la fuente viva.
4. Nunca se borra físicamente; la verificación automática (`tests/test-gobernanza-accion-5.php`) mantiene el índice sincronizado (H-013).