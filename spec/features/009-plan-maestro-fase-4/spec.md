# 009 · Plan Maestro de la Fase 4 (Cableado Fullstack)

**Estado:** completado (6 subfases culminadas · 841/841 aserciones en verde · Reporte docs/testing/fase-4-cierre-maestro.md)

> **Numeración umbrella (`009`):** `scripts/next-feature-number.sh` devuelve `005` porque solo
> enumera directorios existentes, pero `005–008` ya están **semánticamente reservados** en
> `spec/constitution/roadmap.md` (subfases 4.2–4.5, features hijas). Para no colisionar con esos
> números de producto, este feature maestro de coordinación usa `009`.

## Qué hace

Es el **registrador y plan maestro de toda la Fase 4** (cableado fullstack). No implementa
nada por sí mismo: revisa, reorganiza y coordina el alcance de las **características hijas
004–008 y 010** y sus **subfases 4.1–4.6**, de modo que exista un único artefacto coherente que el
Intent Validator pueda aprobar para toda la fase antes de tocar código.

Ordena y resuelve la desincronización actual entre `roadmap.md` (P2) y la feature 004 (P4):

| Subfase | Feature hija | Estados |
| :--- | :--- | :--- |
| **4.1** · Auth & Sesión de Cliente | `004-auth-sesion-cliente` | ✅ Completada (64/64) |
| **4.2** · Catálogo Dinámico & Filtros Textiles | `005-catalogo-dinamico-filtros` | ✅ Completada (136/136) |
| **4.3** · Gestión de Creaciones & Subida Multipart | `006-gestion-creaciones-multipart` | ✅ Completada (212/212 + 127/127) |
| **4.4** · Checkout Público, Pedidos Atómicos & WhatsApp | `007-pedidos-checkout-atomic` | ✅ Completada (139/139) |
| **4.5** · Directorio de Creadores & Roles RBAC | `008-directorio-creadores-rbac` | ✅ Completada (99/99) |
| **4.6** · WhatsApp al Artesano Vendedor | `010-whatsapp-artesano-vendedor` | ✅ Completada (64/64) |

Produce tres artefactos: `spec.md` (este), `plan.md` y `tasks.md`, con **gate 3-tier por
subfase** (suite CLI + logs CLI/HTTP + reporte ejecutivo) y regresión acumulada de fase
(`tests/test-fase-4-acumulado.php`, H-020).

## Por qué

- **Fase 4 es la primera fase 100% transversal** (5 features de cableado frontend–backend);
  planearla al vuelo feature por feature provoca desvío de alcance y gates duplicados.
- **Desincronización activa:** el `tasks.md` de 004 ya contiene subfases 4.1–4.5, mientras que
  el roadmap asigna 4.2–4.5 a features 005–008 que **aún no tienen `spec/plan/tasks`**. Este
  maestro despeja qué gobierna qué sin duplicar gates ni suites.
- **Un solo punto de aprobación** para todo el alcance de la fase: el Intent Validator aprueba
  009 antes de que su flujo guiado despliegue especificaciones por feature hija.

## Criterios de aceptación

- [x] `roadmap.md` (P2) refleja la Fase 4 como **subfases 4.1–4.6** mapeadas a sus features
      hijas 004–008 y 010, sin conflicto de numeración ni solape de alcance.
- [x] Todas las tareas de planificación residen en `spec/features/NNN-nombre-feature/` (`.md`)
      y **ningún archivo de código se ha escrito** durante las fases Specify y Plan.
- [x] El alcance real de `004` queda **reducido a su subfase 4.1** (auth), y las subfases
      restantes se delegan a 005–008 sin duplicación de tasks ni de suites CLI.
- [x] Cada subfase 4.1–4.6 queda cubierta por un **gate 3-tier completo**: suite CLI +
      `logs/subfase-4.X-cli.log`, trazas HTTP + `logs/subfase-4.X-http.log`, y reporte
      ejecutivo `docs/testing/subfase-4.X-*.md` (H-008/H-015).
- [x] La **regresión acumulada** `php tests/test-fase-4-acumulado.php` se registra tras cada
      subfase y corre en verde (H-020: **841/841**); la regresión Fase 3 (`test-subfase-3.6.5.php`) y la
      cifra regenerable `php tests/cuenta-aserciones.php` (**1,287**) permanecen en verde.
- [x] Ningún entregable promete **plazos de taller / lead times** centralizados; las garantías
      son solo de plataforma (fichas rigurosas, WhatsApp directo, perfiles verificados) — **R-03**.
- [x] Cada subfase termina en **HALT** con aprobación explícita del usuario antes de iniciar la
      siguiente (nunca se empaquetan subfases, `AGENTS.md §3`).

## Fuera de alcance

- **Implementación** de los módulos de código de cada subfase (vive en el flujo guiado de su
  feature hija 004–008).
- **Código backend/frontend** de la Fase 4 (cero archivos fuera de `spec/features/009-*/` en
  las fases Specify/Plan).
- **Fase 5** · Documentación Diátaxis, Rendimiento & Entrega.
- **Gobernanza** (`spec/gobernanza/`): las acciones de auditoría no entran en la numeración de
  features 004–008.