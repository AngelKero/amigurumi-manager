# 012 · Plan Maestro de la Fase 5 (Documentación Diátaxis, Rendimiento & Entrega)

**Estado:** propuesto (solo spec · sin código)

> **Numeración umbrella (`012`):** Actúa como el registrador, orquestador y plan maestro de toda la **Fase 5**. Al igual que `009` coordinó las subfases 4.1–4.6 de la Fase 4, este artefacto coordina las subfases 5.1–5.4 garantizando la regla de descomposición atómica y el Gate 3-Tier por subfase.

---

## Qué hace

Es el **registrador y plan maestro de la Fase 5 (Producción & Entrega)**. No implementa código por sí mismo: define el alcance, orden, dependencias y criterios de calidad para las 4 subfases secuenciales que llevan a Crochet Manager a su versión de producción estable:

| Subfase | Feature Asociada | Dominio de Trabajo | Objetivos Clave |
| :---: | :---: | :--- | :--- |
| **5.1** | `012` / `docs` | **Documentación Diátaxis & Manuales de Taller** | 4 cuadrantes: Tutoriales (onboarding artesano/cliente), Guías How-To (operación), Referencia API/DDL exhaustiva y Explicación arquitectónica (Clean Arch, SQLite WAL, Invariantes). |
| **5.2** | `011` | **Accesibilidad WCAG 2.1 AA & Refinamiento UI/UX** | Foco visible (`:focus-visible` dashed), focus trap y restauración en modales, etiquetas `aria-label`/`aria-live`, skeletons de carga y empty states artesanales. |
| **5.3** | `012` / `perf` | **Rendimiento & Core Web Vitals (CWV)** | Eliminación de CLS con dimensiones explícitas, `loading="lazy"` nativo en catálogo, auditoría de scripting JS sin layout thrashing y caché HTTP de assets estáticos. |
| **5.4** | `012` / `release` | **Hardening Final, Empaquetado & Release Readiness** | Auditoría estricta de `.htaccess` (403 en carpetas privadas), script pre-vuelo CLI `check-produccion.php`, guía de despliegue en servidor real y regresión global integral. |

Produce tres artefactos maestros: `spec.md` (este), `plan.md` y `tasks.md`, con **Gate 3-Tier por subfase** (suite CLI + logs CLI/HTTP + reporte ejecutivo) y suite acumulada de fase.

---

## Por qué

1. **Evitar Desvío de Alcance en la Fase Final:** La fase de entrega abarca documentación, pulido visual, rendimiento y seguridad de despliegue. Sin un plan maestro, los esfuerzos se dispersan sin un criterio claro de cuándo está "terminado el proyecto".
2. **Cumplimiento Estricto de la Regla de Descomposición Atómica:** Cada subfase debe ejecutarse individualmente con su propia suite de pruebas, bitácoras, reporte y parada **HALT** para validación humana.
3. **Calidad de Nivel Producción:** El sistema ya cuenta con backend robusto y frontend reactivo; la Fase 5 asegura que la plataforma sea intuitiva para artesanas reales, accesible para cualquier usuario y segura para operar en Internet.

---

## Criterios de Aceptación (Gate de Validación)

- [x] **AC-1 (Roadmap Canónico):** `spec/constitution/roadmap.md` refleja la Fase 5 descompuesta en las 4 subfases secuenciales (5.1–5.4) bajo la coordinación del maestro `012`.
- [x] **AC-2 (Diátaxis Completo):** La documentación en `docs/` se organiza en los 4 cuadrantes Diátaxis (Tutoriales, How-To, Referencia, Explicación) sin enlaces huérfanos (H-013).
- [ ] **AC-3 (Accesibilidad WCAG 2.1 AA):** Todos los componentes interactivos cumplen estándares de navegación por teclado, contraste y compatibilidad con lectores de pantalla.
- [ ] **AC-4 (Métricas Web):** El catálogo y vitrina operan con CLS = 0 (Cumulative Layout Shift) y carga diferida optimizada sin frameworks pesados.
- [ ] **AC-5 (Hardening de Producción):** Se audita la inviolabilidad de `.htaccess` para rutas privadas (`app/`, `database/`, `logs/`, `spec/`) y se provee un script CLI de validación pre-vuelo (`check-produccion.php`).
- [ ] **AC-6 (Regresión Acumulada Invicta):** Todas las suites acumuladas de fases previas (Fase 3: 1,287 aserciones, Fase 4: 841 aserciones) se mantienen 100% en verde.
- [ ] **AC-7 (Invariantes Preservados):** Ninguna tarea de Fase 5 compromete los 10 Invariantes Canónicos (`R-01` a `R-10`).
- [ ] **AC-8 (Gate 3-Tier & HALT):** Cada subfase termina en HALT con aprobación explícita del usuario antes de avanzar a la siguiente.

---

## Fuera de Alcance

- Pasarelas de pago automatizadas o comisiones bancarias centralizadas (prohibido por modelo colaborativo fair-trade).
- Reescribir módulos de backend o frontend existentes (salvo mejoras quirúrgicas de accesibilidad o rendimiento).
- Dependencias de Node.js o paquetes Composer en runtime de producción.
