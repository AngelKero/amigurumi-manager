# Gobernanza · Acción 6: Desacople de Fases/Subfases — Tareas

## Implementación
- [x] M1: Adelgazar `.agents/rules/general.md` — eliminar fases/cifras, añadir §redirect a `spec/` y loop.
- [x] M2: Ampliar `spec/constitution/roadmap.md` con la sección "Ciclo de vida por fases/subfases" (estado + cifras + subfase activa).
- [x] M3: Resincronizar `docs/architecture/proceso-desarrollo-fases.md` (corregir estados a realidad).
- [x] M4: Actualizar `.agents/workflows/general.md` — fila de enrutamiento hacia `spec/constitution/roadmap.md`.
- [x] M5: Re-anclar `tests/test-gobernanza-accion-5.php` — trasladar canónicos `1,287`/`755/755` de `rules/general.md` a `roadmap.md`.

## Gate de testing de la acción (obligatorio)
- [x] Crear `tests/test-gobernanza-accion-6.php` (suite CLI nativa) cubriendo los criterios de aceptación de `spec.md`.
- [x] Ejecutar la suite: `php tests/test-gobernanza-accion-6.php > logs/gobernanza-accion-6-cli.log 2>&1` → **45/45 (100%)** en verde.
- [x] Regresión completa: `test-subfase-3.1`, `test-subfase-3.6.5`, `test-gobernanza-accion-2`, `test-gobernanza-accion-3`, `test-gobernanza-accion-4`, `test-gobernanza-accion-5`, `test-fase-4-acumulado`, `cuenta-aserciones.php` (1,287).
- [x] Reporte ejecutivo `docs/testing/gobernanza-accion-6-desacople-fases.md` con sección "Fallos Detectados & Correcciones Quirúrgicas".
- [x] Validar criterios en `spec.md`, marcar tareas completadas en `tasks.md`.
- [x] Actualizar `spec/constitution/roadmap.md` y **HALT** esperando aprobación explícita.