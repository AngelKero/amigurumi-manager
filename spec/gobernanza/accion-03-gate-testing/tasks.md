# Gobernanza · Acción 3: Gate de Testing de la Fase 4 — Tareas

> Aprobado 14/09/2026 · Alcance: H-008, H-015, H-020.

## M1 · SDD de 004 con gate 3-tier (H-008)
- [x] Reescribir `spec/features/004-auth-sesion-cliente/tasks.md` con gate por subfase 4.1–4.5 (suite CLI, logs CLI/HTTP, reporte `docs/testing/`, criterios de aceptación).
- [x] Nota de prerrequisitos de Acción 2 en la subfase 4.1 (429, revocación, CSP).

## M2 · Protocolo de divergencia CLI/HTTP (H-015)
- [x] Publicar `docs/testing/protocolo-divergencia-cli-http.md` (matriz, triaje 6 pasos, entorno vs código, halting).
- [x] Añadir paso de triaje al gate de `.agents/rules/general.md`.
- [x] Referencia desde `AGENTS.md §3`.

## M3 · Regresión acumulada por fase (H-020)
- [x] Crear `tests/test-fase-4-acumulado.php` (glob dinámico + estado vacío exitoso + validación estructural README).
- [x] Mandato en `AGENTS.md §3` (features 004–008 ⇒ `php tests/test-fase-4-acumulado.php`).
- [x] `docs/testing/README.md`: sección "Suites Acumuladas por Fase" + árbol actualizado.

## M4 · Gate 3-tier de la Acción 3
- [x] Suite `tests/test-gobernanza-accion-3.php` al 100% (33/33).
- [x] `php -l` en PHP tocados + `node --check`.
- [x] Servidor local + regresión 3.1 (93/93) y 3.6.5 (141/141) en verde.
- [x] Logs en `logs/gobernanza-accion-3-cli.log` y `-http.log`.
- [x] Reporte `docs/testing/gobernanza-accion-3-gate-fase-4.md` con "Fallos Detectados & Correcciones Quirúrgicas".
- [x] Entrada en `spec/constitution/roadmap.md`.
- [x] HALT: aguardar aprobación explícita del usuario.
- [ ] (Posterior) Aplicar el mismo patrón de gate a los tasks.md de features 005–008 al abrirlos.