# Reporte de Pruebas: Subfase 4.3.1 — Scoping Servidor del Panel & Papelera

- **Fecha de Ejecución:** 2026-09-15
- **Responsable:** Agente IA → humano (HALT antes de 4.3.2)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 + `node --check`
- **Archivo de Log Crudo:** `logs/subfase-4.3.1-cli.log` (suite, 62/62) · `logs/subfase-4.3.1-http.log` (curls manuales)
- **Script de Pruebas:** `tests/test-subfase-4.3.1.php` (Feature 006 correctivo · maestro 009)
- **Resultado General:** **62 / 62 Aprobados (100%)** — ✅ APTO PARA AVANZAR

---

## 1. Objetivo / Alcance

| Archivo | Cambio |
| :--- | :--- |
| `api/creaciones/mias.php` | **Nuevo** (45 líneas): CORS + `GET` (405) + `RoleGuard::artisanOrAdmin()` (401) + `getOwnCreations()` |
| `app/Services/CreacionService.php` | `getOwnCreations()`: scoping por rol (artesano fuerza `user.id`, admin global) + `estado ∈ {activas,inactivas,todas}`; reutiliza `getCatalog()` |
| `database/seed.sql` | `idx_creaciones_artesano_activo ON creaciones(artesano_id, activo)` (`IF NOT EXISTS`); aplicado también en la BD viva |
| `views/pages/creaciones_content.php` | `#filterEstadoSelect` (Activas/Inactivas/Todas); filtros a 3+2 (`col-md-4`) |
| `src/js/modules/creaciones.js` | `MINE_URL`; Bearer en `fetchPage/fetchKpis/loadArtisans`; `estado` en query + reset; filtro de autor oculto a no-admin; `data-estado` + baja/restaurar según `activo` |
| `docs/architecture/decisiones/ADR-017-*` | Scoping de lectura (extiende ADR-007) |
| `docs/api/creaciones.md` | §2b con contrato de `mias.php` |
| `tests/test-subfase-4.3.1.php` | Suite §§1-6, 62 aserciones |
| `tests/test-subfase-3.6.5.php` | Conteo de controladores 25 → **26** (`mias.php`, ≤60 líneas verificado) |
| `docs/testing/README.md` | Fila 4.3.1 (62/62) + total Fase 4 → **468/468** |

Catálogo público (`index.php`, `detalle.php`, `artesanos.php`): **sin cambios**.

## 2. Matriz de Aserciones agrupadas

| # | Dominio | Caso | Aprobadas |
| - | :--- | :--- | :---: |
| 1 | Endpoint | Existe, contenido, ≤60 líneas, CORS, RBAC, delegación, 405 | 7/7 |
| 2 | Servicio | Método existe; Ana ve solo lo suyo; spoof `artesano_id=1` ignorado; admin global + filtro; papelera (sale de activas, entra en inactivas solo `activo=0`, `todas`, restaurar); default/estado inválido → activas; limpieza | 17/17 |
| 3 | BD | Índice vivo + en `seed.sql` + cubre `artesano_id` + `IF NOT EXISTS` | 4/4 |
| 4 | Vista + JS | `#filterEstadoSelect` + 3 opciones; `MINE_URL` (no más `INDEX_URL` público); `q.set('estado'`; ≥3 `authHeaders()` en lecturas; autor gestionado; `data-estado`; cero `innerHTML =`; `node --check` | 13/13 |
| 5 | HTTP vivo | 4×preflight mias (1 en suite + manuales); 401 sin token; login Ana/admin; Ana solo suyas (total == servicio); spoof ignorado; admin global + filtro `artesano_id=2`; papelera `activo=0`; panel 200 con `#filterEstadoSelect` | 17/17 |
| 6 | Docs | ADR-017 (existe, cita `mias.php` y ADR-007); contrato en `docs/api/creaciones.md` | 4/4 |

## 2b. Trazabilidad con el re-plan aprobado

| Requisito | Evidencia | ✅ |
| :--- | :--- | :--- |
| Artesano no ve piezas ajenas (servidor, no solo UI) | §2.2/2.3 + §5 spoof (servicio y HTTP) | ✅ |
| Admin conserva visión global + filtro por autor | §2.4 + HTTP admin | ✅ |
| Papelera usable (restaurar alcanzable) | §2.5 + `#filterEstadoSelect` + visibilidad por `data-estado` | ✅ |
| Sin Bearer → 401 | §5 + http.log paso 2 | ✅ |
| Índice + migración idempotente | §3 (`PRAGMA index_list` + `seed.sql`) | ✅ |

## 3. Evidencia HTTP (`logs/subfase-4.3.1-http.log`)

- `OPTIONS mias.php` → **204** + CORS; `GET mias.php` sin token → **401**.
- Ana: `total: 138, ids: [2]`; con `?artesano_id=1`: `total: 138, ids: [2]` (spoof ignorado).
- `?estado=inactivas`: `total: 11, activos: [0]` (papelera real).
- Sin divergencia CLI↔HTTP (totales servicio == HTTP).

## 4. Fallos Detectados & Correcciones Quirúrgicas (Red→Green→Refactor)

| # | Fallo | Causa | Corrección | Estado |
| - | :--- | :--- | :--- | :--- |
| 1 | Red: suite abortaba con 500 `getOwnCreations()` inexistente | Fase Red esperada | Implementación Green | ✅ |
| 2 | `datos.datos` en la respuesta HTTP (doble envoltorio) | **Defecto de producto**: usé `Response::success($result)` con el sobre completo | Mismo patrón que `index.php`: `Response::json([datos => $result['datos'], paginacion => …])` | ✅ |
| 3 | 3.6.5: conteo hardcodeado 25 → 26 controladores | Snapshot legítimo (nuevo `mias.php`, ≤60 líneas) | 25 → 26 con justificación (nº de asserts Fase 3 intacto) | ✅ 141/141 |
| 4 | Regresión Fase 4 masiva: logins Ana fallaban tras correr 3.6.5 | **Entorno (H-015)**: `test-subfase-3.2.php:316-331` reescribe la clave de Ana y la restaura hardcodeada a `admin123` (asume semilla) | Orden canónico: Fase 3 → normalizar claves README → gate Fase 4; claves re-aplicadas + 4.3/4.3.1/acumulado re-verificados | ✅ 468 verde |

**Conclusión:** 1 defecto real de producto (corregido), 1 snapshot (actualizado), 1 interacción de entorno (triaje + orden documentado). Cero divergencias CLI/HTTP pendientes.

## 5. Integridad SQLite

- `PRAGMA integrity_check` → `ok`; `foreign_key_check` → 0 (verificado en 4.3; sin DDL destructivo aquí, solo `CREATE INDEX IF NOT EXISTS`).
- Dataset vivo: 270 piezas del seed combinatorio + base (el acumulado 4.2 lo re-siembra idempotentemente); piezas ZZ efímeras en baja lógica (`activo=0`).
- Nota operativa: **re-ejecutar `3.6.5`/`3.2` revierte la clave de Ana a `admin123`** (efecto colateral documentado arriba); tras cualquier regresión Fase 3, re-aplicar claves README y re-correr el gate Fase 4.

## 6. Veredicto

- [x] Suite CLI 62/62 + HTTP vivo + trazabilidad del re-plan completa.
- [x] Regresiones: 4.3 (212/212), 3.6.5 (141/141), acumulado Fase 4 (468/468), H-006 (1.287, previo al índice; el índice no añade asserts).
- [x] `006` (AC-13/14), `009/tasks.md`, `roadmap.md`, README actualizados (ver cierre).
- **Estado:** ⏸ **HALT — esperando autorización para 4.3.2 (contadores).**
