# Gobernanza · Acción 4 · Consolidación de Reglas (Anti-Rule-Rot)

> **Fecha:** 14/09/2026 · **Recomendación #4** del `reporte-auditoria-gobernanza.md` · **Hallazgos:** H-005, H-011, H-012, H-023
> **Alcance aprobado:** plan completo + sincronización de históricos (`docs/testing/qa-audit-report.md`, `docs/archive/*.html`) solicitada por el usuario.

---

## Resumen ejecutivo

La gobernanza del repositorio sufría **rotación de reglas**: heurísticas con IDs duplicados sin secuencia estable, clases CSS documentadas pero no implementadas, invariantes citados por número posicional con colisión (IDOR vs. garantías) y contenido normativo duplicado entre `.agents/rules/general.md` y `.agents/workflows/general.md` (desincronización byte-level). Esta acción consolida las reglas en **una única secuencia canónica** y elimina el riesgo de interpretaciones divergentes (P1–P6).

| Entregable | Hallazgo | Estado |
| :--- | :--- | :--- |
| Invariantes R-01…R-10 citados por clave semántica | H-005 | ✅ |
| Reglas de directorio implantadas (`.table-artisan-team`, `.avatar-artisan-initials`) | H-011 | ✅ |
| Heurísticas CR/QW con IDs únicos en todo el repo (incl. históricos) | H-012 | ✅ |
| `rules/general.md` canónico + `workflows/general.md` = glue | H-023 | ✅ |
| Gate 3-tier de la acción: suite 56/56 + regresión + smoke HTTP | — | ✅ |

## Desglose por hallazgo

### H-005 · Consistencia (invariantes canónicos)
- `AGENTS.md §5`: los 10 invariantes llevan clave semántica `(R-01)`…`(R-10)`.
- `spec/constitution/tech-stack.md` "Límites Duros": las 6 balas normativas fueron sustituidas por una **tabla-ancla** que cita cada invariante por clave `R-0X` (P1 > P2), con declaración explícita de que la citación es por clave, nunca por número posicional.
- `docs/api/README.md §8` y `docs/architecture/decisiones/ADR-004-universal-soft-delete.md` anclan R-01/R-02 (P5 remite a P1).

### H-011 · Realidad (reglas implantadas)
- `src/css/04-components/users.css`: selector renombrado `.user-avatar-circle` → `.avatar-artisan-initials` y nuevo componente `.table-artisan-team` (detalles de hilo artesanal: bordes pespunteados, `thead` con costura, hover suave).
- Aplicado en `views/pages/usuarios_content.php` (tabla + avatar), `views/pages/creaciones_content.php` (avatar de artesano) y `src/js/modules/users.js` (render dinámico del avatar).
- Regla §5.16 de `.agents/rules/ui-ux-design-system.md` marcada **"✓ Implantada"**.

### H-012 · Unicidad (renumeración CR/QW)
- Secuencia única: **CR-1** stock / **CR-2** stepper / **CR-3** móvil / **CR-4** aislamiento de roles · **QW-1** contraste / **QW-2** restitución / **QW-3** microcopy lead-time.
- Tabla sumaria de registro canónico añadida al §4 de la regla; `docs/design-system/audits.md` espejado.
- Código vivo: `tables.css` (CR-2→CR-3 móvil) y `modals.css` (QW-2→QW-3 lead-time). Comentarios de stepper/restitución (CR-2/QW-2) intactos por significado correcto.
- **Históricos (extensión pedida por el usuario):** `docs/testing/qa-audit-report.md` (renumeración y lista de IDs) y `docs/archive/detalle.html`, `index.html` (QW-2→QW-3), `pedidos.html` (CR-2→CR-3).

### H-023 · Single-source (workflow-glue)
- `.agents/rules/general.md`: estatus canónico declarado, índice de fan-out por tipo de tarea, y guardrails de soft-delete/autonomía convertidos en **anclas R-01/R-02/R-03** (sin texto normativo duplicado). Las anclas de la Acción 3 (`protocolo-divergencia-cli-http.md`, `test-fase-4-acumulado.php`) se conservan.
- `.agents/workflows/general.md`: reescrito como glue de enrutamiento (~40 líneas, tabla por tipo de tarea, regla única "no duplicar contenido normativo").

## Fallos Detectados & Correcciones Quirúrgicas

| # | Fallo (rojo) | Causa raíz | Corrección quirúrgica | Estado |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Suite propia, aserción `tech-stack declara la regla de citación por clave` | Needle de la aserción disparaba en mitad de una marca Markdown (`clave **semántica` vs `**clave semántica`)** | Aserción relajada al fragmento estable `nunca por número posicional` (semántica idéntica, robusta a formato) | ✅ |
| 2 | Suite propia, aserción `rules/general.md conserva mandato de regresión Fase 4` | El mandato del runner acumulado de Fase 4 vivía en `AGENTS.md §3` y `docs/testing/README.md` (Acción 3), pero no en la fuente canónica `.agents/rules/general.md` | Extensión del gate en `rules/general.md`: nuevo paso (H-020) que obliga a `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` al tocar features 004–008; la regla queda alineada con AGENTS y README | ✅ |

Ningún fallo funcional de negocio ni de regresión: todos los fallos fueron de robustez de aserciones y de cobertura de la regla canónica, corregidos de forma quirúrgica y verificados en verde.

## Script de verificación (Tier 1 · CLI)

```bash
# Suite propia de la acción (56 aserciones)
php tests/test-gobernanza-accion-4.php > logs/gobernanza-accion-4-cli.log 2>&1   # 56/56 ✅

# Regresión acumulada
php tests/test-subfase-3.1.php                 > logs/subfase-3.1-cli.log        2>&1   # 93/93 ✅
php tests/test-subfase-3.6.5.php               > logs/subfase-3.6.5-cli.log      2>&1   # 141/141 ✅
php tests/test-gobernanza-accion-2.php         > logs/gobernanza-accion-2-cli.log 2>&1  # 96/96 ✅
php tests/test-gobernanza-accion-3.php         > logs/gobernanza-accion-3-cli.log 2>&1  # 34/34 ✅
php tests/test-fase-4-acumulado.php            > logs/fase-4-acumulado.log       2>&1   # 4/4 ✅

# Lint global
find app api views *.php -name "*.php" -exec php -l {} +     # 0 errores
find src/js -name "*.js" -exec node --check {} +             # 0 errores
```

## Verificación HTTP (Tier 2 · smoke, `logs/gobernanza-accion-4-http.log`)

| Check | Resultado |
| :--- | :--- |
| `OPTIONS /api/creaciones/index.php` | **204** No Content (CORS preflight resuelto) |
| `GET /api/auth/me.php` sin token | **401** JSON `{"exito":false,...}` sin fuga HTML |
| CSP en raíz | `default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; ... frame-ancestors 'none'` |

**Escenario A — Sin divergencia CLI/HTTP:** los checks HTTP no ejercitan reglas de UI ni renumeraciones de código (CSS/vistas estáticas), por lo que no hay divergencia que triar (protocolo `docs/testing/protocolo-divergencia-cli-http.md`).

## Estado del roadmap

- `spec/constitution/roadmap.md`: entrada **"Consolidación de Reglas (Acción 4)"** añadida en la sección Gobernanza ✅.
- `spec/gobernanza/accion-04-consolidacion-reglas/tasks.md`: M1–M4 ✅, M5 ✅ (cerrada).
- **HALT aplicado:** se requiere aprobación explícita del usuario antes de iniciar la **Acción 5** (H-006/H-013/H-022).

## Archivos tocados (resumen)

- **Reglas/constitución:** `AGENTS.md*`, `spec/constitution/tech-stack.md`, `.agents/rules/general.md`, `.agents/rules/ui-ux-design-system.md`, `.agents/workflows/general.md`, `docs/api/README.md`, `docs/architecture/decisiones/ADR-004-universal-soft-delete.md`.
- **Código:** `src/css/04-components/users.css`, `tables.css`, `modals.css`, `views/pages/usuarios_content.php`, `creaciones_content.php`, `src/js/modules/users.js`.
- **Docs/espejos:** `docs/design-system/audits.md`, `docs/testing/qa-audit-report.md`, `docs/archive/detalle.html`, `index.html`, `pedidos.html`.
- **Gobernanza:** `spec/gobernanza/accion-04-consolidacion-reglas/` (spec.md, plan.md, tasks.md), `tests/test-gobernanza-accion-4.php`, `spec/constitution/roadmap.md`.