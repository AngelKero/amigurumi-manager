# Gobernanza · Acción 5 · Re-sincronización Documental

> **Recomendaciones atendidas:** H-006 (cifras de aserciones verificables/regenerables) · H-013 (índice maestro `docs/README.md` exhaustivo) · H-022 (workflow `ui-ux-audit` con rutas muertas + ausencia de política de deprecación/archivado).
> **Estado:** ✔ COMPLETADA y verificada bajo el ritual 3-Tier de gobernanza.
> **Fecha:** 2026-09-14 · **Referencia:** `spec/gobernanza/accion-05-resincronizacion-docs/`.

---

## 1. Objetivo y alcance

Re-sincronizar la documentación con el código y eliminar los tres hallazgos de la auditoría:

1. **H-006 — Cifras de aserciones imposibles.** Los documentos declaraban totales no verificables (1,307 · 775 · 161/161 para 3.6.2) y el recuento Fase 3 no era regenerable. Se crea un *runner* que resetea la BD a semilla limpia y mide el acumulado real.
2. **H-013 — Índice maestro desincronizado.** `docs/README.md` solo referenciaba hasta 3.6.1 en filas y árbol, sin 3.6.2–3.6.5, sin auditorías context7, sin reportes de gobernanza; tampoco existía política que obligara a enlazar todo `docs/**/*.md`.
3. **H-022 — Rutas muertas y sin ciclo de vida.** `workflows/ui-ux-audit.md` apuntaba a `index.html`, `css/styles.css`, `js/app.js` y `.docs/ui-ux-skill-report.md` (ninguno existe). No existía práctica de deprecación/archivado documentado para `docs/archive/`.

---

## 2. Cambios aplicados (Red-Green-Refactor)

### 2.1 H-006 · Cifras verificables (P2 tech-stack / P1 AGENTS / P5 docs)

- **Nuevo `tests/cuenta-aserciones.php`:** runner CLI que (1) resetea la BD a semilla con `php setup.php` —las suites cuentan aserciones dependientes de filas, sin reset la 2ª pasada da 1,318 por 3.5=158 y 3.6.2=153— y (2) ejecuta las 10 suites de Fase 3 describiendo el total.
- **Ground truth en semilla: 1,287 aserciones** (93+69+105+126+139+165+141+157+151+141), estable en 3 corridas.
- Cifras canónicas actualizadas a **1,287** + comando regenerable en: `tech-stack.md`, `AGENTS.md §3`, `docs/testing/README.md`, `.agents/rules/general.md`, `roadmap.md`, `spec/features/003-backend-clean-architecture/spec.md` y `tasks.md`, `docs/architecture/phase-3-plan.md`, `docs/testing/auditoria-context7-fase-3.md`. Sub-total 3.6.x corregido a **755** para el baseline de semilla.
- Tabla de Testing: fila 3.6.2 corregida a **141 / 141 (100%)** (era 161, imposible) con nota H-006.

### 2.2 H-013 · Índice maestro exhaustivo

- `docs/README.md` regenerado: filas de navegación completas en **los 7 dominios + Archivo Histórico**, árbol estructural donde **todo `.md`** aparece con su ruta real (`./dominio/archivo.md`), catálogo de ADR-001…016 con enlaces, y nota que obliga a enlazar cada documento nuevo.
- **Hallazgo del propio H-013:** `docs/security/auditoria-sanitizacion-js.md` estaba completamente huérfana (sin enlace en ningún índice). Se incorporó el dominio **Seguridad** (fila + rama del árbol).
- El índice anticipa y enlaza el reporte `gobernanza-accion-5-resincronizacion-docs.md`, y `docs/archive/README.md`.

### 2.3 H-022 · Workflow glue + ciclo de vida documental

- `workflows/ui-ux-audit.md` **reescrito como glue**: enruta el skill `design-auditor` a `views/pages/`, `src/css/`, `src/js/modules/`, `assets/svg/piezas/`, consolida salida en `docs/design-system/audits.md` (nunca `.docs/`), referencia constraints reales (`face-ancestors 'none'`, `escapeHtml`, prohibición `#0d6efd`). Cero rutas muertas.
- `docs-source-of-truth.md` (P1): nueva sección **"Ciclo de Vida Documental: Deprecación y Archivado"** — archivar, jamás borrar; cabecera obligatoria `🤖 ARQUIVADO — OBSOLETO`; desenlazar de la fuente viva; verificación automática por la suite de gobernanza.
- **Nuevo `docs/archive/README.md`:** índice del histórico no autoritativo (6 volúmenes: plan de refactor + 5 mockups HTML), con su estado y reemplazo.

---

## 3. Ritual de verificación (3-Tier)

| Tier | Comando | Resultado |
| :--- | :--- | :--- |
| 1 · Suite | `php tests/test-gobernanza-accion-5.php` | **43/43 (100%)** → `logs/gobernanza-accion-5-cli.log` |
| 1 · Runner | `php tests/cuenta-aserciones.php` | **TOTAL FASE 3 VERIFICADO: 1287 aserciones (100%)** → `logs/cuenta-aserciones.log` |
| 2 · Regresión | gobernanza-2 / gobernanza-3 / gobernanza-4 / 3.1 / 3.6.5 / fase-4 | **96 + 33 + 56 + 93 + 141 + 4 — todas (100%)** |
| 2 · Smoke HTTP | `OPTIONS`×2, `GET /api/creaciones/`, `me` sin token, headers raíz | `204 · 204 · 200 JSON · 401 · CSP estricto` → `logs/gobernanza-accion-5-http.log` |
| 2 · Lints | `php -l` (app/api/views/*.php) + `node --check` (src/js) | 0 errores de sintaxis |

**Divergencia CLI/HTTP:** no aplica — ninguna ruta de red fue modificada en esta acción; el smoke es de integridad regresiva.

---

## 4. Fallos Detectados & Correcciones Quirúrgicas

| # | Fallo (Rojo) | Corrección (Verde) |
| :--- | :--- | :--- |
| 1 | `docs/**/*.md` dentro de un *docblock* PHP cerraba el comentario → *Parse error* en `test-gobernanza-accion-5.php` L8. | Redactado el docblock evitando la secuencia `**/` (Red→Green dentro de la suite propia). |
| 2 | H-013 reveló `docs/security/auditoria-sanitizacion-js.md` **huérfana** (no enlazada en ningún índice). | Incorporado el dominio `security/` al índice maestro (fila de navegación + rama del árbol) con su ruta real. |
| 3 | (M1) El primer recuento daba 1,318/1,146 inestables por aserciones dependientes de filas y códigos ANSI en la captura. | `php setup.php` embebido antes de medir + `preg_replace('/\x1b\[[0-9;]*m/', '', $text)` → 1,287 determinista. |
| 4 | Al documentar la transición en `roadmap.md`, la aserción "sin `1,307`" fallaba sobre el propio texto del cambio. | `roadmap.md` se verifica aparte: solo permite `1,307` como transición `1,307→1,287` (`substr_count === 1`), conservándose el guardrail anti-cifra-obsoleta para el resto de fuentes. |

> **Decisión documental (desviación consciente del brief):** aprobado "corregir históricos 1,146 → 1,166", pero el análisis demostró que **1,166 nunca existió**: 1,146 era un snapshot internamente consistente (9 suites pre-3.6.5 con 3.6.2=141, vigente en su momento). Escribir 1,166 hubiera *falsificado* el repo. En su lugar se **anotaron** ambos históricos (`auditoria-context7-fase-1.md` y `subfase-3.6.5-rendimiento-regresion.md`) con nota de snapshot verificado + comando regenerable, sin tocar los números, y el runtime manda (P6): **1,287**.

---

## 5. Fuentes tocadas (P1–P6)

- **P1:** `AGENTS.md §3`, `.agents/rules/general.md`, `.agents/rules/docs-source-of-truth.md`, `.agents/workflows/ui-ux-audit.md`.
- **P2:** `tech-stack.md`, `roadmap.md`.
- **P4:** `spec/features/003-backend-clean-architecture/` (spec + tasks).
- **P5:** `docs/README.md` (índice maestro), `docs/testing/README.md`, `docs/architecture/phase-3-plan.md`, `docs/testing/auditoria-context7-fase-3.md`, `docs/testing/auditoria-context7-fase-1.md`, `docs/testing/subfase-3.6.5-rendimiento-regresion.md`, `docs/design-system/audits.md` (referenciado), `docs/archive/README.md`.
- **P6:** `tests/cuenta-aserciones.php`, `tests/test-gobernanza-accion-5.php` (material de verificación).

---

## 6. Cierre y estado

- ✔ Verificado: suite propia 43/43, runner 1,287 en semilla, regresión Fase 3+Fase 4+gobernanza 100%, lints y smoke HTTP verdes.
- ✔ `spec/gobernanza/accion-05-resincronizacion-docs/tasks.md` cerrado y `roadmap.md` avanzado.
- **HALT activado:** pendiente aprobación humana para la siguiente subfase/acción.