# 009 · Plan Maestro de la Fase 4 — Tareas

**Estado:** propuesto (sin código) · derivado de `plan.md`

> `009` es coordinación: este `tasks.md` registra el avance de TODO el esqueleto de Fase 4 y
> deriva las subfases 4.1–4.5 hacia sus **features hijas 004–008**. Cada subfase se ejecuta en
> su feature hija con **gate 3-tier completo** (suite CLI + logs CLI/HTTP + reporte ejecutivo).

## 1. Especificación (antes de código)

- [x] `spec.md` de 009 validado y aprobado por el usuario (HALT cumplido).
- [x] `plan.md` de 009 validado y aprobado por el usuario (HALT cumplido).

## 2. Alineación del esqueleto maestro (prerequisito)

- [x] Realinear `004-auth-sesion-cliente/tasks.md`: retirar subfases 4.2–4.5 y la sección de
      regresión; añadir nota de remisión a `009`.
- [x] Confirmar que `004/spec.md` y `004/plan.md` no desdicen del alcance auth (4.1); ajustar
      únicamente si divergen.
- [x] Re-anclar `spec/constitution/roadmap.md`: Fase 4 = subfases 4.1–4.5 ↔ features 004–008;
      anotar `009` como maestro de coordinación (fuera del backlog de producto).
- [x] Baseline limpio (cero código tocado): `find app api views *.php -name "*.php" -exec php -l {} +`
      y `find src/js -name "*.js" -exec node --check {} +`.
- [x] Regresión Fase 3: `php tests/test-subfase-3.6.5.php > logs/subfase-3.6.5-cli.log 2>&1` → 100% verde.
- [x] Cifra regenerable: `php tests/cuenta-aserciones.php` → **1,287** (H-006).

## 3. Despliegue de features hijas 005–008 (planificación, sin código)

> Cada feature hija sigue el flujo guiado SDD: `spec.md` → HALT → `plan.md` → HALT → `tasks.md` → HALT.

- [x] `005` · Catálogo Dinámico & Filtros Textiles (4.2) — spec/plan/tasks creados y aprobados.
- [x] `006` · Gestión de Creaciones & Subida Multipart (4.3) — spec/plan/tasks creados y aprobados.
- [x] `007` · Checkout Público, Pedidos Atómicos & WhatsApp (4.4) — spec/plan/tasks creados y aprobados.
- [ ] `008` · Directorio de Creadores & Roles RBAC (4.5) — spec/plan/tasks creados y aprobados.
- [x] Actualizar `roadmap.md` al estado "planificado" de 005–008.

## 4. Ejecución de subfases 4.1–4.5 (en features hijas · gate 3-tier)

- [x] **4.1** Auth & Sesión de Cliente (`004`): suite `tests/test-subfase-4.1.php` → **64/64**;
      logs `logs/subfase-4.1-cli.log` + `logs/subfase-4.1-http.log` (sin divergencia); reporte
      `docs/testing/subfase-4.1-auth-sesion.md` · **HALT** (aprobación pendiente para 4.2).
- [x] **4.2** Catálogo Dinámico & Filtros (`005`): suite `tests/test-subfase-4.2.php` →
      `logs/subfase-4.2-cli.log` (136/136, incl. fallback SVG R-09 y regla "agotados al
      final"); HTTP → `logs/subfase-4.2-http.log` (sin divergencia); reporte
      `docs/testing/subfase-4.2-catalogo.md` con **validación AC 11/11** · **HALT** (aprobación
      pendiente para 4.3).
- [x] **4.3** Gestión de Creaciones & Multipart (`006`): suite `tests/test-subfase-4.3.php` →
      **212/212** (`logs/subfase-4.3-cli.log`); HTTP multipart real → `logs/subfase-4.3-http.log`
      (201/200/401/422/403/404/409, foto preservada R-02, sin divergencia); reporte
      `docs/testing/subfase-4.3-creaciones.md` con **validación AC 12/12**; fix opción A
      (`Genérica→gatito-ovillo.svg`, 3.6.5 141/141 + H-006 1.287 en verde) · **HALT** (aprobación
      pendiente para 4.4).
- [x] **4.3.1** Scoping servidor & papelera (`006` correctivo): `api/creaciones/mias.php` +
      `getOwnCreations()` + índice + ADR-017; suite `tests/test-subfase-4.3.1.php` → **62/62**;
      HTTP → `logs/subfase-4.3.1-http.log`; reporte `docs/testing/subfase-4.3.1-panel-scoping.md`;
      regresiones 4.3 (212/212), 3.6.5 (141/141, conteo 26), acumulado (**474/474**), H-006 (1.287).
- [x] **4.3.2** Contadores del panel (`006` correctivo): `getStockSummary()` + `getOwnSummary()` +
      `mias.php?resumen=1`; badge `N piezas` + rango `A–B de N` + KPIs globales exactos; suite
      `tests/test-subfase-4.3.2.php` → **43/43**; HTTP → `logs/subfase-4.3.2-http.log`; reporte
      `docs/testing/subfase-4.3.2-contadores.md`; acumulado (**517/517**).
- [x] **4.3.3** Centavos multipart & subida clicable (`006` correctivo): `resolveCents()` dual +
      `*_centavos` en crear/actualizar/JS; dato #316 ($350/$100); dropzone + hero sin handlers
      inline (CSP); suite `tests/test-subfase-4.3.3.php` → **22/22**; reporte
      `docs/testing/subfase-4.3.3-centavos-upload.md`; acumulado (**539/539**).
- [x] **4.4** Checkout Público & Pedidos Atómicos (`007`): suite `tests/test-subfase-4.4.php` →
      **139/139** (`logs/subfase-4.4-cli.log); HTTP → `logs/subfase-4.4-http.log` (201 público +
      precio congelado + WhatsApp, 422/404/409, panel Bearer con scoping, manual/estados/
      cancelar+restituir, sin divergencia); reporte `docs/testing/subfase-4.4-pedidos.md` con
      **validación AC 10/10** · **HALT** (aprobación pendiente para 4.5).
- [ ] **4.5** Directorio de Creadores & RBAC (`008`): suite `tests/test-subfase-4.5.php` →
      `logs/subfase-4.5-cli.log`; HTTP → `logs/subfase-4.5-http.log`; reporte
      `docs/testing/subfase-4.5-usuarios.md` · **HALT**.

## 5. Regresión por fase (obligatorio, H-020)

- [x] Tras **cada** subfase: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1`
      (tras 4.3: **412/412** — 64 + 136 + 212, EXIT 0; tras 4.3.1/4.3.2: **517/517** —
      64 + 136 + 212 + 62 + 43, EXIT 0; tras 4.3.3: **539/539** — 64 + 136 + 212 + 62 + 43 + 22,
      EXIT 0; tras 4.4: **678/678** — 64 + 136 + 212 + 62 + 43 + 22 + 139, EXIT 0).
- [ ] Tras la última subfase: regresión acumulada completa + Fase 3 (`test-subfase-3.6.5.php`)
      + `php tests/cuenta-aserciones.php` (**1,287**) en verde.

## 6. Verificación & Cierre

- [ ] Criterios de aceptación de `spec.md` de 009 al 100% (`- [x]`).
- [ ] `spec/constitution/roadmap.md`: Fase 4 movida a **Hecho ✅** (4.1–4.5 cerradas).
- [ ] Reporte de cierre de la fase con resumen de "Fallos Detectados & Correcciones Quirúrgicas".
- [ ] **HALT:** aprobación explícita del usuario antes de iniciar cualquier otra feature
      (p.ej. Fase 5).