# Gobernanza · Acción 6: Desacople de Fases/Subfases — Plan

## Decisiones (aprobadas por el usuario 14/09/2026)

1. **Detalle en `roadmap.md` (P2):** no se crea un 4º archivo de constitución; se amplía `spec/constitution/roadmap.md` con el registrador de ciclo de vida. La triada `mission > tech-stack > roadmap` y las referencias P1–P6 quedan intactas.
2. **`proceso-desarrollo-fases.md` se resincroniza (P5):** corrige su estado obsoleto (Fase 3 en curso → completada) y pasa a ser la narrativa viva de detalle por fase, sin duplicar el gate normativo.
3. **Re-anclaje quirúrgico de la suite de la Acción 5:** los canónicos `1,287`/`755/755` pasan de `rules/general.md` a `roadmap.md`. Es condición necesaria, no opcional.
4. **AGENTS.md sin cambios:** su §3 es ya normativa P1 y no alberga estado histórico.

## Milestones

### M1 · Adelgazar `rules/general.md` (P1)
- Eliminar toda la narrativa `Phase 1…Phase 5`, el desglose de sub-subfases 3.6.x y todas las cifras (`93/93`, `69/69`, `105/105`, `126/126`, `139/139`, `755/755`, `1,287/1,287`, "Total acumulado Fase 3").
- Insertar §redirect: las fases/subfases y su estado viven en `spec/constitution/roadmap.md` y `spec/features/NNN/tasks.md`; en cada subfase se aplica el loop Red→Green→Refactor y el gate 3-tier; HALT con aprobación explícita; nunca empaquetar subfases.
- Conservar: header H-023, fan-out (actualizando la fila "Desarrollo/refactor/backend" para apuntar a "§Loop + §Gate + §Guardrails; estado en roadmap"), gate 3-tier, invariante de subfases atómicas, guardrails R-01/R-02/R-03, guardrails de documentación y design system.

### M2 · `roadmap.md` como registrador de ciclo de vida (P2)
- Añadir sección "Ciclo de vida por fases/subfases" al inicio del documento: Fase 1 ✔, Fase 2 ✔, Fase 3 ✔ (`1,287`, agrupación `755/755`), Fase 4 en curso (4.1 Auth & Sesión, puntero a `spec/features/004-auth-sesion-cliente/tasks.md`), Fase 5 continua.
- Conservar el resto del formato (Hecho/Gobernanza/Siguiente/Backlog) sin duplicar narrativa.

### M3 · Resincronizar `docs/architecture/proceso-desarrollo-fases.md` (P5)
- Actualizar estados: Fase 3 → **Completado & Verificado** (6 subfases, 1,287); sección 3.6.x completa; Fase 4 → **En curso** (4.1 Auth & Sesión de Cliente); diagrama ASCII y matriz comparativa coherentes.
- Verificar que no contradiga la constitución ni la regla (defecto de doc → se corrige el doc).

### M4 · `.agents/workflows/general.md` (glue)
- Fila de enrutamiento "Desarrollo/refactor/backend": de "fases 1–5, gate 3-tier, invariantes" a "loop + gate + guardrails; estado de fases en `spec/constitution/roadmap.md`".

### M5 · Re-anclar `tests/test-gobernanza-accion-5.php`
- Líneas 45–56: el array `$canonicals` sustituye `'rules/general.md' => $rulesGen` por `'roadmap.md' => $roadmap`.
- Línea 65: la aserción `755/755` pasa de `$rulesGen` a `$roadmap`.
- Nota: la aserción `1,287` de `roadmap` ya existe (línea 57); no se duplica.

### M6 · Crear `tests/test-gobernanza-accion-6.php`
- **Positivas sobre `general.md`:** contiene redirect `spec/constitution/roadmap.md`, términos de loop (Red/Green/Refactor) y las 5 anclas H-023.
- **Negativas sobre `general.md`:** no contiene `1,287`, `755/755`, `Phase 1`, `Phase 3`, `93/93`.
- **Sobre `roadmap.md`:** contiene `1,287`, `755/755`, `4.1`, `4.5`, `test-subfase-4.1.php`.
- **Sobre `proceso-desarrollo-fases.md`:** Fase 3 completada, Fase 4 en curso (4.1), sin contradecir el roadmap.
- **Sobre el glue:** referencia a `spec/constitution/roadmap.md` y a `rules/general.md`.

### M7 · Gate 3-tier y regresión
- Suite 6 → `logs/gobernanza-accion-6-cli.log` (100%).
- Regresión: `test-subfase-3.1`, `test-subfase-3.6.5`, `test-gobernanza-accion-2/3/4/5`, `test-fase-4-acumulado`, `tests/cuenta-aserciones.php` (1,287) + `php -l`/`node --check`.
- Divergencia CLI/HTTP → protocolo `docs/testing/protocolo-divergencia-cli-http.md`.

### M8 · Reporte y cierre
- `docs/testing/gobernanza-accion-6-desacople-fases.md` con "Fallos Detectados & Correcciones Quirúrgicas" (si el re-anclaje u otro fallo lo requiere) y fila en `docs/README.md` (H-013).
- Marcar criterios en `spec.md`, checkear `tasks.md`, actualizar `roadmap.md` y **HALT**.

## Riesgos

- **Rotura del gate de la Acción 5:** mitigado por M5 (confirmado por el usuario). El re-anclaje es la única línea de fallo si se adelgaza la regla sin tocarlo.
- **Anclas de las Acciones 3 y 4:** no dependen de cifras de estado; se conservan textualmente.
- **`proceso-desarrollo-fases.md` obsoleto:** si se omite M3, queda como defecto P5 activo; se resincroniza o se archiva (H-022) en la misma acción.
- **Duplicación nueva:** el registrador de roadap no repite narrativa; solo estado + cifras + puntero, por lo que no reintroduce la rotación eliminada.