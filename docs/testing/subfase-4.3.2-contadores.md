# Reporte de Pruebas: Subfase 4.3.2 — Contadores Correctos del Panel

- **Fecha de Ejecución:** 2026-09-15
- **Responsable:** Agente IA → humano (HALT antes de 4.4/007)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 + `node --check`
- **Archivo de Log Crudo:** `logs/subfase-4.3.2-cli.log` (suite, 35/35) · `logs/subfase-4.3.2-http.log` (curls manuales)
- **Script de Pruebas:** `tests/test-subfase-4.3.2.php` (Feature 006 correctivo · maestro 009)
- **Resultado General:** **43 / 43 Aprobados (100%)** — ✅ APTO PARA AVANZAR

---

## 1. Objetivo / Alcance

| Archivo | Cambio |
| :--- | :--- |
| `app/Repositories/CreacionRepository.php` | `getStockSummary()`: agregado exacto en 1 query (modelos, unidades, valor/costo en centavos), misma gramática de filtros |
| `app/Services/CreacionService.php` | `getOwnSummary()` + extracción de `applyOwnershipScope()` (reutilizado por `getOwnCreations`, cero duplicación) |
| `api/creaciones/mias.php` | Rama `?resumen=1` → sobre `{modelos, unidades, valor_centavos, costo_centavos}` (55 líneas, ≤60 verificado) |
| `views/pages/creaciones_content.php` | Chip `Mostrando A–B de N` (`#creacionesShowingFrom/To/TotalCount`); fuera `#creacionesShowingCount` |
| `src/js/modules/creaciones.js` | `initSidebarBadges()` (misma fuente que el KPI) + llamada en `main.js` |
| `views/components/panel_sidebar.php` | `#sidebarBadgeCreaciones` inicial `0` (fuera el mock `5`) |
| `src/js/modules/creaciones.js` | Badge `N piezas`; rango real en `renderPagination`; `fetchKpis` vía `resumen=1` con `seq` + Bearer + 401; fuera el loop 20×48 (`buildQueryWithoutPage`) |
| `docs/api/creaciones.md` | §2b: fila `resumen` |
| `tests/test-subfase-4.3.php` | Chip actualizado al rango A–B (evolución 4.3.2) |
| `tests/test-subfase-4.3.2.php` | Suite §§1-4, 35 aserciones |
| `docs/testing/README.md` | Fila 4.3.2 (35/35) + total Fase 4 → **503/503** |

## 2. Matriz de Aserciones agrupadas

| # | Dominio | Caso | Aprobadas |
| - | :--- | :--- | :---: |
| 1 | Vista | Badge + spans From/To/Total; fuera ShowingCount | 5/5 |
| 2 | JS | From/To, formato `N piezas`, sin `X de Y`, `resumen`, sin loop, centavos, sin `innerHTML =`, `node --check` | 9/9 |
| 3 | Servicio | Método existe; Ana vs SQL independiente (4); admin global (2) + cobertura; anti-spoof (2); papelera (2) | 11/11 |
| 4 | HTTP vivo | 401; login; resumen Ana == servicio (3) + `costo_centavos`; admin; papelera | 10/10 |
| 5 | Insignia lateral | `#sidebarBadgeCreaciones` sin mock (parte de 0) + `initSidebarBadges()` con misma fuente que el KPI + cableado en `main.js` | 8/8 |

## 2b. Trazabilidad con el re-plan aprobado

| Requisito | Evidencia | ✅ |
| :--- | :--- | :--- |
| Badge `N piezas` (total filtrado) | `updateCountBadge(total)` + `setIconText(…, \`${total} piezas\`)`; suite §2 | ✅ |
| Paginación `Mostrando A–B de N` (0 si vacío) | `renderPagination`: `from = total? (page-1)*limit+1 : 0`, `to = min(page*limit, total)`; suite §1 | ✅ |
| KPIs globales sin filtros, exactos | `getStockSummary` (1 query, sin tope) + `resumen=1` con `estado` + scoping (+ selección de autor en admin); suite §3 vs SQL independiente | ✅ |

## 3. Evidencia HTTP (`logs/subfase-4.3.2-http.log`)

- `GET resumen` sin token → **401**; como Ana → **200** con sobre exacto == servicio; papelera con sus `activo=0`.
- Sin divergencia CLI↔HTTP.

## 4. Fallos Detectados & Correcciones Quirúrgicas

| # | Fallo | Causa | Corrección | Estado |
| - | :--- | :--- | :--- | :--- |
| 1 | Red: `getOwnSummary()` inexistente → 500 | Fase Red esperada | Implementación Green | ✅ |
| 2 | Suite: needle `resumen=1` no aparecía (JS usa `q.set('resumen','1')`) | **Defecto de suite** | Needle al `q.set` real | ✅ 35/35 |
| 3 | Suite 4.3: esperaba `#creacionesShowingCount` eliminado | Evolución de spec (4.3.2) | Assert al rango A–B | ✅ 212/212 |
| 4 | Flake 4.2 (1/136 solo bajo runner, 4ª aparición; verde aislado y en re-corridas) | **Entorno** transitorio no capturado | Re-corrida: acumulado 503/503 EXIT 0; registrado (H-015) | ✅ |
| 5 | 3.6.5 clobber: `3.2 §4.10` revierte la clave de Ana a `admin123` | **Entorno** (asume semilla) | Orden: Fase 3 → normalizar claves README → gate Fase 4 re-verificado | ✅ |

## 5. Integridad SQLite

- Sin DDL (solo `SELECT` nuevo + `activo` existente); `integrity_check` verificado en 4.3 (`ok`, 0 FK).
- Nota operativa (reiterada): tras cualquier regresión Fase 3, re-aplicar claves README y re-correr el gate Fase 4.

## 6. Veredicto

- [x] Suite CLI 43/43 + HTTP vivo + trazabilidad completa.
- [x] Regresiones: 4.3 (212/212), 4.3.1 (62/62), 3.6.5 (141/141), acumulado Fase 4 (**511/511**), H-006 (1.287, previo; sin nuevos asserts Fase 3).
- [x] `006` (AC-15/16), `006/tasks.md` §8, `009/tasks.md`, `roadmap.md`, README actualizados.
- **Estado:** ⏸ **HALT — esperando autorización para 4.4 (Feature 007).**
