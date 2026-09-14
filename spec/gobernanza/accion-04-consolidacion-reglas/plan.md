# Gobernanza · Acción 4: Consolidación de Reglas — Plan

## Decisiones (aprobadas por el usuario 14/09/2026)

1. **Alcance:** H-005 + H-011 + H-012 + H-023 (recomendación #4), plan completo.
2. **H-012 íntegro en todo el repo:** además de reglas + audits.md + código vivo, se actualiza `docs/testing/qa-audit-report.md` y `docs/archive/*.html` con la numeración única (el usuario pidió incluirlos).
3. **Renumeración propuesta:** CR-1 (stock), CR-2 (stepper), CR-3 (mobile), CR-4 (aislamiento de roles, antes sin ID) y QW-1 (contraste), QW-2 (restitución), QW-3 (microcopy lead-time). Los IDs ya correctos (CR-1, CR-2, QW-1, QW-2) no se tocan; solo se asignan los duplicados y el sin-ID.
4. **H-011:** implementar (no deprecar): las clases existían conceptualmente renovadas en `.user-avatar-circle`/`.table-users`; se renombran a la identidad documentada y se marcan como implantadas.

## Milestones

### M1 · Canónizar invariantes R-01…R-10 (H-005)
- `AGENTS.md §5`: etiquetar ítems 1–10 con `(R-01)`…`(R-10)`.
- `tech-stack.md` "Límites Duros": reemplazar las 6 balas normativas por la **tabla-ancla R-01…R-10** (clave | invariante | ADR/ref) citando `AGENTS.md §5` como fuente canónica.
- Anclar R-01/R-02 en `docs/api/README.md §8` y en `ADR-004-universal-soft-delete.md`.

### M2 · Implantar reglas de directorio de creadores (H-011)
- `users.css`: renombrar `.user-avatar-circle` → `.avatar-artisan-initials` y añadir `.table-artisan-team` (detalles de hilo artesanal).
- Aplicar clases en `usuarios_content.php` (tabla + avatar), `creaciones_content.php` (avatar artesano) y `users.js` (render dinámico).
- Regla §5.16 marcada **implantada**.

### M3 · Renumerar heurísticas CR/QW (H-012)
- `ui-ux-design-system.md §4`: secuencia única CR-1…CR-4, QW-1…QW-3 + **tabla sumaria** (ID | regla | estado) y §5.16 con estado.
- `docs/design-system/audits.md`: espejo con la misma secuencia.
- Comentarios de código vivos cuyo significado cambia: `tables.css` (mobile CR-2→CR-3), `modals.css` (lead-time QW-2→QW-3).
- Históricos (decisión del usuario): `docs/testing/qa-audit-report.md` (CR-2 mobile→CR-3; lista de IDs actualizada) y `docs/archive/detalle.html` (QW-2→QW-3), `docs/archive/index.html` (QW-2→QW-3), `docs/archive/pedidos.html` (CR-2→CR-3).

### M4 · Single-source + workflow glue (H-023)
- `rules/general.md`: índice de fan-out + estatus canónico; guardrails soft-delete y autonomía convertidos en anclas R-01/R-02/R-03 (sin duplicar texto normativo). Se conservan las anclas de la Acción 3 (`protocolo-divergencia-cli-http.md`, `test-fase-4-acumulado.php`).
- `workflows/general.md`: reescritura como glue (~40 líneas) con tabla de enrutamiento; cero contenido normativo.

### M5 · Gate 3-tier (suite propia + regresión + reporte)
- `tests/test-gobernanza-accion-4.php`: asserts estáticos (R-01…R-10; tech-stack-ancla; docs §8/ADR-004; glue sin texto de invariante y con referencia a rules/general.md; CR-2/QW-2 únicos como ID; CR-3/QW-3/CR-4 presentes; audios/qa/archive sincronizados; clases fantasma en CSS y vistas; `.user-avatar-circle` erradicado).
- `php -l` en vistas PHP tocadas, `node --check` en `users.js`, servidor local, regresión 3.1 + 3.6.5 + gobernanza-2 + gobernanza-3 + runner fase-4.
- Logs `logs/gobernanza-accion-4-cli.log` (+ HTTP smoke). Reporte `docs/testing/gobernanza-accion-4-consolidacion-reglas.md` con "Fallos Detectados & Correcciones Quirúrgicas".
- `roadmap.md` + cierre tasks + **HALT**.

## Riesgos

- Renombrar `.user-avatar-circle` podría romper render si algún módulo lo referenciara: se verificó 4 usos (CSS + 2 vistas + users.js) y se migran todos en M2.
- Tests de acciones previas asertan sobre `rules/general.md` (anclas triaje/runner): M4 conserva esas anclas.
- Renumerar comentarios en archivos históricos: buscado y aplicado solo donde el significado cambió (CR-2 stepper y QW-2 restitución se mantienen).