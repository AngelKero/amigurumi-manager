# Gobernanza · Acción 3 · Gate de Testing de la Fase 4

**Fuente:** `reporte-auditoria-gobernanza.md` → Recomendación #3: "Completar el gate de testing para la Fase 4" → hallazgos **H-008**, **H-015**, **H-020**.
**Estado:** aprobado (14/09/2026) · **Ejecución:** en curso.

## Qué hace

Blinda el protocolo de testing **antes** de que la Fase 4 (features 004–008) arranque, garantizando tres invariantes operativas:

1. **(H-008 · SDD)** El `tasks.md` de cada feature activa de Fase 4 incorpora el gate 3-tier por subfase (suite CLI `tests/test-subfase-4.X.php` + logs crudos CLI/HTTP + reporte ejecutivo en `docs/testing/`), no solo "validar en navegador".
2. **(H-015 · Brechas)** Existe un protocolo explícito de **divergencia CLI vs. HTTP**: qué hacer cuando uno pasa y el otro falla, con triaje "entorno vs. código", reproducibilidad, y criterio de halting. Vive como documento vivo en `docs/testing/` y se ancla en `AGENTS.md §3` y en el gate numerado de `.agents/rules/general.md`.
3. **(H-020 · Protocolo de testing)** Existe una suite de **regresión acumulada por fase** (`tests/test-fase-4-acumulado.php`) con despliegue dinámico de subfases, obligatoria al tocar features 004–008, y documentada en `docs/testing/README.md`.

## Criterios de aceptación

- [x] `spec/features/004-auth-sesion-cliente/tasks.md` contiene el gate 3-tier para cada subfase 4.X.
- [x] `docs/testing/protocolo-divergencia-cli-http.md` publicado con matriz de escenarios, triaje de 6 pasos, definición "entorno vs. código" y criterio de halting.
- [x] `AGENTS.md §3` y `.agents/rules/general.md` referencian el protocolo y el mandato de regresión por fase.
- [x] `tests/test-fase-4-acumulado.php` existe, es ejecutable, descubre subfases de forma dinámica y con 0 subphases reporta "0 subfases aún" sin fallar (exit 0).
- [x] `docs/testing/README.md` lista las suites acumuladas por fase.
- [x] Suite `tests/test-gobernanza-accion-3.php` al 100% (33/33) y regresión 3.1 (93/93) / 3.6.5 (141/141) / gobernanza-2 (96/96) en verde.
- [x] Reporte ejecutivo `docs/testing/gobernanza-accion-3-gate-fase-4.md` con sección "Fallos Detectados & Correcciones Quirúrgicas".
- [x] `spec/constitution/roadmap.md` actualizado y **HALT** (aprobación explícita del humano antes de la siguiente acción/subfase).

## Fuera de alcance

- Código funcional de la feature 004 (se codifica en su subfase 4.1 con su propio gate).
- H-006 / H-013 / H-022 (Acción 5 · Re-sincronización documental).
- H-005 / H-011 / H-012 / H-023 (Acción 4 · Consolidación de reglas).
- H-018 / H-019 (test irreparable / escapatoria de aserciones) — queda registrado para acciones futuras.