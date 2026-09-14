
# Docs como Fuente de Verdad Obligatoria (Anti-Alucinación)

## Precedencia de Fuentes (Canónico — Ver `AGENTS.md §6`)

`docs/` es la referencia técnica viva, pero **no** es la fuente de decisión cuando hay conflicto. Ante discrepancias entre fuentes se resuelve estrictamente en este orden:

1. **P1 · Reglas operativas** (`AGENTS.md` + `.agents/rules/*`) — mandan sobre la forma de trabajo.
2. **P2 · Constitución** (`spec/constitution/`: `mission.md` > `tech-stack.md` > `roadmap.md`).
3. **P3 · ADRs** (`docs/architecture/decisiones/`) — solo un ADR nuevo deroga a otro.
4. **P4 · Feature vigente** (`spec/features/NNN/`: `spec.md` > `plan.md` > `tasks.md`).
5. **P5 · `docs/`** — narrativa viva; si contradice a P1–P4 es *defecto del doc*: se corrige el doc, jamás la regla ni el código.
6. **P6 · Código fuente** — autoridad material. Si `docs/`/`spec/` quedan desincronizados con el código, **el código prevalece** y el agente **DEBE** reportar la desincronización al humano.

**Regla de discrepancia:** Nunca corregir en silencio una regla, spec o ADR para que coincida con el código. Reportar el conflicto; solo el humano re-ancle la fuente.

## Regla Principal

Antes de iniciar **cualquier tarea** de desarrollo, corrección, análisis o planificación, el agente **DEBE** leer `docs/README.md` como paso obligatorio. Este archivo es el índice maestro y punto de entrada a toda la documentación verificada del proyecto.

## Protocolo de Consulta (Obligatorio, Sin Excepciones)

### Paso 1: Leer el Índice Maestro
```
SIEMPRE leer → docs/README.md
```
El agente evalúa qué dominios de documentación son relevantes para la tarea actual consultando la tabla de navegación y la guía rápida del índice.

### Paso 2: Profundizar según el dominio de la tarea

| Si la tarea involucra... | Entonces leer TAMBIÉN... |
|:--|:--|
| Endpoints, fetch, AJAX, contratos HTTP | `docs/api/README.md` + el recurso específico (`auth.md`, `creaciones.md`, `pedidos.md`, `usuarios.md`) |
| Esquema de BD, columnas, relaciones, SQL | `docs/database/README.md` + `docs/database/schema.md` |
| Arquitectura, capas, dependencias, fases | `docs/architecture/README.md` + `docs/architecture/proceso-desarrollo-fases.md` |
| Decisiones de diseño técnico | `docs/architecture/decisiones/README.md` + el ADR específico |
| Vistas, CSS, tokens, componentes UI | `docs/design-system/README.md` + `docs/design-system/brand-identity.md` |
| Testing, aserciones, reportes QA | `docs/testing/README.md` + el reporte de subfase relevante |
| Seguridad, IDOR, OWASP, tokens | `docs/architecture/subfase-3.6-auditoria-seguridad.md` |

### Paso 3: Verificar contra código fuente

Después de consultar la documentación, el agente **DEBE** verificar la implementación real abriendo los archivos fuente (`app/`, `api/`, `src/`, `views/`) para confirmar que la documentación está sincronizada con el código actual. Si hay discrepancia, **el código fuente prevalece** y el agente debe señalar la desincronización al usuario.

## Prohibiciones Absolutas

1. **NUNCA asumir de memoria** firmas de métodos, nombres de columnas, rutas de endpoints o tokens CSS sin haberlos leído en el archivo fuente o en la documentación.
2. **NUNCA inventar** un contrato de API, un nombre de tabla, un parámetro de servicio o una variable CSS que no esté documentado o implementado.
3. **NUNCA omitir** la lectura de `docs/README.md` alegando que "ya lo sé de la sesión anterior". Cada tarea nueva requiere re-consultar el índice.

## Citación Obligatoria

Cuando el agente base una decisión en documentación específica, debe citar el archivo consultado. Ejemplo:
> Según `docs/api/creaciones.md`, el endpoint `GET /api/creaciones/` retorna paginación con 12 items por defecto.

---

## Actualización Obligatoria de Docs al Finalizar (Cierre del Ciclo)

Al completar y aprobar una subfase o feature, el agente **DEBE** actualizar la documentación relevante en `docs/` como **paso final obligatorio** antes de declarar la tarea terminada. El flujo no se considera cerrado hasta que los docs reflejen el estado actual del código.

### Protocolo de Cierre Documental

```
Leer docs/ → Trabajar → Tests en verde → Aprobación del usuario → Actualizar docs/ → ✅ FIN
```

### Qué actualizar según lo implementado

| Si se implementó... | Entonces actualizar... |
|:--|:--|
| Nuevo endpoint o cambio en API | `docs/api/` (el recurso afectado + `README.md` si hay endpoints nuevos) |
| Cambio en esquema de BD | `docs/database/schema.md` + ERD en `docs/database/README.md` |
| Nueva subfase completada | `docs/testing/` (reporte ejecutivo) + `docs/architecture/proceso-desarrollo-fases.md` |
| Decisión arquitectónica relevante | Nuevo ADR en `docs/architecture/decisiones/` |
| Nuevo componente UI o cambio de tokens | `docs/design-system/` (el archivo afectado) |
| Cualquier cambio | `docs/README.md` si se agregaron nuevos archivos de documentación |

### Reglas de Cierre

1. **NUNCA declarar una subfase como "completada"** si los `docs/` no han sido actualizados para reflejar los cambios.
2. **NUNCA dejar documentación desincronizada** — si el código cambió, los docs cambian.
3. **Actualizar `docs/README.md`** si se crearon nuevos archivos de documentación que necesiten aparecer en el índice maestro.
4. **Actualizar `spec/constitution/roadmap.md`** para reflejar el avance de la feature o subfase completada.

---

## Ciclo de Vida Documental: Deprecación y Archivado (Gobernanza · Acción 5 · H-022)

`docs/` es la biblioteca viva; `docs/archive/` es el histórico **no autoritativo**. Cuando una documentación queda obsoleta se **archiva, jamás se borra físicamente** (integridad del rastro).

### Protocolo de archivado (aplicar en este orden)

1. **Detectar obsoletos:** rutas muertas, decisiones reemplazadas por ADR posterior, procesos sustituidos por reglas (P1), o snapshots históricos cuya vigencia terminó.
2. **Mover** el archivo a `docs/archive/` (misma estructura de nombre legible).
3. **Cabecera de obsoleto:** al inicio del documento marcarlo con el formato obligatorio:
   ```markdown
   > 🤖 **ARQUIVADO — OBSOLETO:** [fecha ISO] · Reemplazado por <ruta/regla> · NO autoritativo.
   ```
4. **Indexar en histórico:** añadir la entrada en `docs/archive/README.md` con su estado y motivo.
5. **Desenlazar la fuente viva:** retirar la entrada del índice maestro `docs/README.md` (y del README de su dominio si la listaba); el histórico queda accesible solo vía `docs/archive/README.md`.
6. **Verificar enlaces:** la suite de gobernanza (`tests/test-gobernanza-accion-5.php`) valida que todo `docs/**/*.md` siga enlazado y que ningún archivo obsoleto figure como referencia viva en reglas o workflows.

### Reglas de deprecación

1. **NUNCA `rm` de un doc:** un documento archivado se conserva para auditoría y rastro de decisiones.
2. **Los mockups HTML históricos** (`docs/archive/*.html`) son no autoritativos: los workflows los referencian solo como lectura/contexto, nunca como target de implementación.
3. **Reemplazo obligatorio:** todo obsoleto debe indicar qué regla/doc lo sustituye (trazabilidad P1–P6); un doc en el limbo sin reemplazo no se archiva, se tria con el humano.
4. **Los workflows-glue** (`.agents/workflows/`) nunca deben apuntar a rutas muertas; cualquier desfase se corrige en el propio workflow o se archiva el workflow (mismo protocolo).
