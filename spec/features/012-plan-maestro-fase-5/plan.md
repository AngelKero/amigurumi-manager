# 012 · Plan Maestro de la Fase 5 — Plan de Arquitectura & Ejecución

**Estado:** propuesto (sin código) · léase junto con `spec.md`

---

## 1. Enfoque de Coordinación

`012` es una feature umbrella de **coordinación y gobernanza de la Fase 5**, no un contenedor monolítico de código. Su propósito es estructurar la entrega de producción en 4 subfases atómicas, asegurando que cada una cuente con su propio ciclo **Red-Green-Refactor**, pruebas CLI/HTTP, reporte formal en `docs/testing/` y parada **HALT** para validación del usuario.

El plan maestro coordina:
1. **Re-anclaje del Roadmap (P2):** Actualizar `spec/constitution/roadmap.md` para formalizar las 4 subfases de la Fase 5.
2. **Despliegue Secuencial:** Subfase 5.1 (Diátaxis) → Subfase 5.2 (Accesibilidad/UX · Feature 011) → Subfase 5.3 (Rendimiento CWV) → Subfase 5.4 (Hardening & Release).
3. **Preservación Inviolable de Calidad:** Mantener las 1,287 aserciones de Fase 3 y las 841 aserciones de Fase 4 al 100% en verde sin excepciones.

---

## 2. Mapa de Subfases e Implementación

| Subfase | Feature / Ruta | Artefactos a Crear / Modificar | Tipo de Verificación (Gate 3-Tier) |
| :---: | :---: | :--- | :--- |
| **5.1** | `012` / `docs` | `docs/tutorials/` (Primeros pasos), `docs/how-to/` (Guías prácticas), `docs/api/` y `docs/database/` (Referencia consolidada), `docs/explanation/` (Arquitectura & Invariantes). | Suite CLI `test-subfase-5.1.php` (integridad de enlaces, H-013, consistencia de términos), logs y reporte ejecutivo `docs/testing/subfase-5.1-diataxis.md`. |
| **5.2** | `011` | `src/css/01-settings/reset.css`, `src/css/04-components/forms.css`, `src/js/modules/dialog.js`, `views/components/*.php` (Labels, `aria-live`, focus visible, skeletons, empty states). | Suite CLI `test-subfase-5.2.php`, comprobaciones HTTP curl y reporte `docs/testing/subfase-5.2-a11y-ux.md`. |
| **5.3** | `012` / `perf` | `views/components/product_card.php` (`loading="lazy"`, `aspect-ratio`), `.htaccess` (cabeceras `Cache-Control`), optimización de SVG y CSS. | Suite CLI `test-subfase-5.3.php` (presupuesto de peso, CLS, tiempos de respuesta), logs y reporte `docs/testing/subfase-5.3-rendimiento.md`. |
| **5.4** | `012` / `release` | `check-produccion.php` (CLI pre-vuelo), `.htaccess` (verificación de 403), `docs/guides/despliegue-produccion.md`, suite global acumulada. | Suite CLI `test-subfase-5.4.php`, ejecución de `check-produccion.php`, regresión completa y reporte `docs/testing/subfase-5.4-release.md`. |

---

## 3. Secuencia de Ejecución (Paso a Paso)

```
[Inicio Fase 5]
       │
       ▼
1. Validar spec.md y plan.md de 012 ──► HALT (Sign-off del Usuario)
       │
       ▼
2. Subfase 5.1: Documentación Diátaxis
   ├── Escribir Tutoriales, How-To, Referencia y Explicaciones
   ├── Validar árbol de enlaces en docs/README.md (H-013)
   └── Suite test-subfase-5.1.php ──► HALT (Sign-off)
       │
       ▼
3. Subfase 5.2: Accesibilidad & UX Polish (011)
   ├── Implementar Foco Visible, Focus Trap, ARIA y Skeletons
   └── Suite test-subfase-5.2.php ──► HALT (Sign-off)
       │
       ▼
4. Subfase 5.3: Rendimiento & Core Web Vitals
   ├── Optimizar CLS, Lazy Loading y Caché HTTP
   └── Suite test-subfase-5.3.php ──► HALT (Sign-off)
       │
       ▼
5. Subfase 5.4: Hardening Final & Release
   ├── Script CLI check-produccion.php y auditoría .htaccess
   ├── Regresión acumulada global (Fases 1 a 5)
   └── Suite test-subfase-5.4.php ──► Cierre Definitivo de Fase 5
```

---

## 4. Decisiones de Diseño

* **Adopción de Diátaxis en `docs/`:** El framework Diátaxis resuelve la ambigüedad entre "cómo se usa" (How-To), "cómo aprendo desde cero" (Tutorial), "cuáles son los parámetros exactos" (Referencia) y "por qué se diseñó así" (Explicación).
* **Alineación con Feature 011:** La subfase 5.2 asume formalmente el alcance de `spec/features/011-ux-ui-polish/`, eliminando la ambigüedad de numeración previa (4.7) e integrándola de manera limpia en la Fase 5.
* **Sin Dependencias Pesadas para Auditorías:** Las pruebas de rendimiento y accesibilidad se realizan con utilidades nativas en PHP y selectores DOM sin introducir paquetes Node o npm en el entorno.

---

## 5. Riesgos & Mitigaciones

* **Riesgo:** Generar archivos de documentación huérfanos que violen la regla `H-013` de gobernanza.  
  *Mitigación:* Cada archivo `.md` creado bajo `docs/` será enlazado inmediatamente en `docs/README.md` y verificado con `tests/test-gobernanza-accion-5.php`.
* **Riesgo:** Romper regresiones existentes al modificar componentes UI para accesibilidad.  
  *Mitigación:* Ejecutar `php tests/test-fase-4-acumulado.php` y `php tests/cuenta-aserciones.php` tras cualquier cambio en vistas PHP o JS.
* **Riesgo:** Empaquetar múltiples subfases.  
  *Mitigación:* Se impone un **HALT** absoluto al final de cada subfase (5.1, 5.2, 5.3, 5.4).
