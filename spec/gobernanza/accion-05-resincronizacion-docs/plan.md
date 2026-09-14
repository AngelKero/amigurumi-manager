# Gobernanza · Acción 5: Re-sincronización Documental — Plan

## Decisiones (aprobadas por el usuario 14/09/2026)

1. **Alcance:** H-006 + H-013 + H-022 (recomendación #5), plan completo.
2. **H-006 con corrección de históricos:** además de `tech-stack.md`, se corrigen los residuos "1,146" (cifra aritméticamente imposible) en `docs/testing/subfase-3.6.5-rendimiento-regresion.md` y `docs/testing/auditoria-context7-fase-1.md` → 1,166 (pre-3.6.5) / 1,307 (total), incluido el row erróneo que listaba 3.6.2 con 141 aserciones.
3. **H-022 resolviendo como glue (no deprecación):** `.agents/workflows/ui-ux-audit.md` se reescribe para apuntar a rutas reales y volcar resultados a `docs/design-system/audits.md`, coherente con la filosofía single-source de H-023.
4. **Cifras no inventadas:** todos los totales se fijan a partir de la **ejecución real** de las suites (runner runtime), no de lectura de docs.

## Milestones

### M1 · Cifras de aserciones verificables y regenerables (H-006)
- `tests/cuenta-aserciones.php`: ejecuta `test-subfase-3.1…3.6.5` en secuencia vía `exec`, parsea "Exitosas:" por suite, suma y emite TOTAL verificable con exit code agregado.
- Ejecutar el runner en el gate para **ground truth** de cada suite (3.6.2 debe dar 161).
- `tech-stack.md:29`: "141 aserciones directas, **1,307 acumuladas**" + cita del comando `php tests/cuenta-aserciones.php` en lugar de la cifra fija.
- Históricos: `subfase-3.6.5-rendimiento-regresion.md` (l.40 → 1,166; tabla regresión: row 3.6.2 → 161; total → 1,166) y `auditoria-context7-fase-1.md:172` (1,146 → 1,166).
- `AGENTS.md §3` (bloque Running Tests) y `docs/testing/README.md`: comando regenerable documentado.

### M2 · Índice maestro regenerado (H-013)
- Regenerar `docs/README.md`: fila de navegación Testing completa (3.6.2–3.6.5, protocolo, gobernanza-*, context7), árbol estructural exhaustivo con enlaces `./dominio/archivo.md` para **todos** los `.md` de `docs/`, y sección `archive/` completa (incluidos `detalle.html`, `formulario.html`, `pedidos.html`).
- La suite de la Acción 5 verifica automáticamente: cada `docs/**/*.md` (excepto `docs/README.md`) está enlazado en el índice maestro.

### M3 · Deprecación/archivado + workflow `ui-ux-audit` sin rutas muertas (H-022)
- Reescribir `.agents/workflows/ui-ux-audit.md` como glue (≈40 líneas): no rutas muertas; assets reales (`views/pages/*.php`, `src/css/`, `src/js/modules/`, `assets/svg/piezas/`), histórico en `docs/archive/*.html`, salida a `docs/design-system/audits.md`.
- Sección **"Ciclo de vida: Deprecación y Archivado"** en `.agents/rules/docs-source-of-truth.md` (P1): protocolo move-to-archive, cabecera "obsoleto" con fecha y reemplazo, enlace en README de dominio/histórico, baja del índice maestro, sin borrado físico de docs.
- Crear `docs/archive/README.md` (índice + estatus no autoritativo de los históricos).

### M4 · Suite de la Acción 5
- `tests/test-gobernanza-accion-5.php` con assertos estáticos para H-006 (cifras y comando), H-013 (índice exhaustivo == cada `docs/**/*.md` enlazado), H-022 (workflow sin rutas muertas + política + `archive/README.md`) y sanity (php -l del runner/suite).

### M5 · Gate 3-tier
- Ejecutar `tests/cuenta-aserciones.php` (verifica 1,307 en runtime) y la suite de la Acción 5 al 100%.
- Regresión: gobernanza-2/3/4 + 3.1 + 3.6.5 + runner fase-4 en verde. `php -l` global, logs `logs/gobernanza-accion-5-{cli,http}.log`, smoke HTTP.
- Reporte `docs/testing/gobernanza-accion-5-resincronizacion-docs.md` con "Fallos Detectados & Correcciones Quirúrgicas"; re-sync final de `docs/README.md` (añadir el propio reporte) y re-run de la suite.
- `roadmap.md` + cierre tasks + **HALT**.

## Riesgos

- Runner de conteo: las suites de Fase 3 escriben sobre la BD/localidad (inofensivo); la 3.6.5 incluye una sección HTTP contra `localhost:8000` → el runner se ejecuta siempre con el servidor local activo.
- El total actual puede divergir del histórico si alguna suite evolucionó: se acepta el **total real resultante** como fuente de verdad (P6), que es justamente el valor que H-006 exige regenerable.
- Re-sincronizar el índice maestro es el punto de fallo de H-013: la suite fuerza cerrar todos los huecos antes del gate.