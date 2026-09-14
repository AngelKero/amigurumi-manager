---
description: Workflow canónico para la creación, planificación, implementación y verificación de features bajo Spec-Driven Development (SDD).
---

# Flujo de Trabajo SDD para Features

Sigue estrictamente este ciclo de 5 pasos para cualquier feature nueva o cambio no trivial:

## 1. Specify (`spec.md`)
1. Crear la carpeta en `spec/features/NNN-nombre-feature/` con el siguiente número disponible.
2. Redactar `spec.md` definiendo:
   - **Qué hace:** Visión desde el punto de vista del usuario (sin código).
   - **Por qué:** Valor aportado y justificación del momento.
   - **Criterios de aceptación:** Lista de condiciones observables comprobables con sí/no (`- [ ]`).
   - **Fuera de alcance:** Límites claros de qué NO incluye esta feature.
3. Detenerse y validar con el usuario.

## 2. Plan (`plan.md`)
1. Redactar `plan.md` respetando la `spec/constitution/tech-stack.md` y los ADRs del proyecto:
   - **Enfoque:** Estrategia arquitectónica.
   - **Implementación:** Archivos concretos afectados y pasos técnicos.
   - **Decisiones:** Decisiones de diseño y alternativas descartadas.
   - **Riesgos:** Posibles puntos de fallo y mitigación.
2. Detenerse y validar con el usuario.

## 3. Tasks (`tasks.md`)
1. Desglosar el plan en una lista de tareas pequeñas y verificables en `tasks.md`:
   - Checklist accionable (`- [ ]`).
   - Criterio de verificación para cada tarea (comando test, lint, curl, verificación manual).
   - Paso final: Validar contra los criterios de aceptación de `spec.md` y mover a "Hecho" en `spec/constitution/roadmap.md`.

## 4. Implementación & Loop Engineering
1. Ejecutar las tareas una a una.
2. Aplicar el bucle `Actuar` → `Observar` → `Corregir` ante cualquier fallo en tests o sintaxis.
3. Marcar tareas completadas (`- [x]`) conforme se verifiquen.

## 5. Verificación & Cierre
1. Ejecutar la suite de tests (`php tests/...`) y validaciones sintácticas (`php -l`, `node --check`).
2. Marcar los criterios de aceptación en `spec.md`.
3. Actualizar `spec/constitution/roadmap.md` moviendo la feature a **Hecho ✅**.
4. Detenerse y solicitar la aprobación explícita del usuario antes de pasar a la siguiente feature.
