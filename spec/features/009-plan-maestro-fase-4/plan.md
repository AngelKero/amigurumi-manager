# 009 · Plan Maestro de la Fase 4 — Plan

**Estado:** propuesto (sin código) · léase con `spec.md`

## Enfoque

`009` es una feature de **coordinación planificadora**, no de producto: ningún código.
Su misión es (1) reanclar el **registrador canónico de la fase** en `spec/constitution/roadmap.md`
(P2), (2) **realinear la feature 004** a su alcance real (subfase 4.1 · auth) retirando de su
`tasks.md` las subfases 4.2–4.5, y (3) **desplegar los specs/plans/tasks de las features hijas
005–008** antes de que su flujo guiado produzca una sola línea de código.

El maestro no sustituye a las features hijas: **cada subfase 4.X implementa en su feature hija**
(006, 007…), conservando la arquitectura Clean Architecture de `tech-stack.md` (`app/` núcleo,
`api/` ≤ 60 líneas, `views/`, `src/js/modules/`) y los invariantes R-01…R-10 de `AGENTS.md §5`.
`009` apenas endosa casos y gates.

## Implementación

| Capa / Artefacto | Archivo(s) | Cambio |
| :--- | :--- | :--- |
| Registrar fase (P2) | `spec/constitution/roadmap.md` | Tabla del ciclo de vida reescrita: Fase 4 = subfases 4.1–4.5 mapeadas a features 004–008; `009` anotado como maestro de coordinación (no cubre backlog 005–008 como features de producto). |
| Feature 004 (realinear) | `spec/features/004-auth-sesion-cliente/tasks.md` | Reducir a subfase 4.1 única: retirar los bloques 4.2–4.5 y la sección "Regresión por fase" (la absorbe 009); añadir nota de remisión a `009`. `spec.md`/`plan.md` de 004 se ajustan solo si desdicen del alcance auth. |
| Esqueleto child (005–008) | `spec/features/NNN-{catalogo-dinamico-filtros,creaciones-multipart,checkout-pedidos-whatsapp,usuarios-rbac}/spec.md` | Flujo guiado SDD (`sdd-feature`) por feature hija: `spec.md` → HALT → `plan.md` → HALT → `tasks.md` → HALT. Cada tareas.md desglosa SOLO su subfase 4.X con su gate 3-tier. |
| Gates de subfase (H-008/H-015) | `tests/test-subfase-4.X.php` · `logs/subfase-4.X-{cli,http}.log` · `docs/testing/subfase-4.X-<nombre>.md` | Por subfase 4.1→4.5, ejecutar suite CLI, trazas HTTP contra `php -S localhost:8000`, y reporte ejecutivo con "Fallos Detectados & Correcciones Quirúrgicas". Divergencias → protocolo `docs/testing/protocolo-divergencia-cli-http.md`. |
| Regresión de fase (H-020) | `tests/test-fase-4-acumulado.php` | Tras cada subfase y al cierre: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1`. |
| Regresión Fase 3 | `tests/test-subfase-3.6.5.php` · `tests/cuenta-aserciones.php` | `php tests/test-subfase-3.6.5.php` y cifra regenerable **1,287** en verde (H-006). |

**Secuencia maestro (orden de ejecución):**
1. Realinear `004` a 4.1 (edição quirúrgica de `tasks.md`).
2. Re-anclar `roadmap.md` como registrador (4.1–4.5 ↔ 004–008, 009 como maestro).
3. Desplegar spec/plan/tasks de **005 → 006 → 007 → 008** (cada uno con su HALT).
4. Ejecutar subfases 4.1→4.5 en las features hijas con gate 3-tier y HALT por subfase.
5. Cerrar la fase: criterios de `009/spec.md` al 100%, regresión acumulada en verde, Fase 4 → **Hecho ✅**.

## Decisiones

- **`009` como umbrella de coordinación (no producto):** las features 004–008 conservan su
  número semántico de producto y su propio flujo SDD. Alternativa descartada: fusionar toda la
  fase en `004` (mega-feature, rompe SRP y el mapeo del roadmap).
- **Numeración `009`:** el helper devuelve `005` (solo directorios), pero 005–008 están
  reservados en el roadmap → el maestro salta a `009` para evitar colisión semántica.
  Alternativa descartada: renumerar 005–008 (rompería referencias en `docs/` y Gobernanza).
- **Un solo registrador de estado (P2):** `roadmap.md` es la fuente única del ciclo de vida;
  `009/tasks.md` solo referencia. Alternativa descartada: un segundo registrador en `spec/`.
- **`004` realineado, no destruido:** su `spec/plan` de auth es correcto; solo `tasks.md` pierde
  los bloques 4.2–4.5 duplicados. Alternativa descartada: reescribir 004 desde cero (pérdida de
  contexto ya validado por Gobernanza · Acciones 2–3).
- **Gates H-008/H-020 innegociables:** cada subfase cierra su suite/los/reporte y dispara la
  regresión acumulada; nunca se empaquetan subfases (`AGENTS.md §3`).

## Riesgos

- **Riesgo:** 009 degenera en mega-feature de implementación → **Mitigación:** `spec.md` y
  `plan.md` limitan 009 a coordinación; todo código vive en features hijas 004–008.
- **Riesgo:** desincronización persistente si `roadmap.md` y `004/tasks.md` no se corrigen al
  inicio → **Mitigación:** los pasos 1–2 de la secuencia son prerequisito del resto y están
  cubiertos por el criterio de aceptación del roadmap.
- **Riesgo:** features 005–008 se planifican sin esperar HALT (aceleración) → **Mitigación:**
  el flujo guiado `sdd-feature` impone HALT por artefacto y el maestro registra la aprobación
  en `tasks.md` de 009.
- **Riesgo:** regresión de Fase 3 rota por cambio de 004 → **Mitigación:** suite
  `test-subfase-3.6.5.php` + `cuenta-aserciones.php` (1,287) después de cada edición del
  esqueleto de planificación y de cada subfase.
- **Riesgo:** gates 4.X duplicados entre `tasks.md` de 004 (heredados) y 009 → **Mitigación:**
  barrido de `tasks.md` de 004 retirando subfases 4.2–4.5 antes de desplegar 005–008.