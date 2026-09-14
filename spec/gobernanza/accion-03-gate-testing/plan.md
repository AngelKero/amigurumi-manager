# Gobernanza · Acción 3: Gate de Testing de la Fase 4 — Plan

## Decisiones (aprobadas por el usuario 14/09/2026)

1. **Alcance:** H-008 + H-015 + H-020 (recomendación #3). No se tocan H-018/H-019 (acción futura).
2. **Runner acumulado de Fase 4:** despliegue dinámico (`glob('tests/test-subfase-4.*.php')`) + estado vacío exitoso (0 subfases → exit 0) + validación estructural (cada suite presente tiene fila en `docs/testing/README.md` y viceversa).
3. **Protocolo de triaje CLI/HTTP:** documento vivo en `docs/testing/protocolo-divergencia-cli-http.md`, con ancla en `AGENTS.md §3` y un paso de triaje añadido al gate numerado de `.agents/rules/general.md`.

## Milestones

### M1 · Gate de testing en el SDD de 004 (H-008)
- Reescribir `spec/features/004-auth-sesion-cliente/tasks.md`: cada subfase 4.1–4.5 debe crear `tests/test-subfase-4.X.php` alineado a los criterios de aceptación de `spec.md`, redirigir CLI a `logs/subfase-4.X-cli.log`, lanzar curl HTTP a `logs/subfase-4.X-http.log`, y emitir reporte `docs/testing/subfase-4.X-*.md` con la sección "Fallos Detectados & Correcciones Quirúrgicas".
- Nota: la subfase 4.1 verifica prerrequisitos de Acción 2 (login 429, revocación del logout, cabeceras CSP).
- Fuera de alcance: no codificar aún `tests/test-subfase-4.1.php` (nace con la subfase 4.1).

### M2 · Protocolo de divergencia CLI vs. HTTP (H-015) — solo docs/reglas
- `docs/testing/protocolo-divergencia-cli-http.md`: matriz de 4 escenarios, triaje en 6 pasos (re-producir 2× → aislar entorno/código → re-test en Apache/FastCGI → registrar traza → decidir → halting), definición de "defecto de entorno" vs "defecto de código", y plantilla de registro para reportes.
- `.agents/rules/general.md`: añadir paso 7 al gate ("Aplicar el protocolo de triaje CLI/HTTP si CLI y HTTP divergen") + referencia.
- `AGENTS.md §3`: referencia al protocolo.

### M3 · Suite de regresión acumulada por fase (H-020)
- Crear `tests/test-fase-4-acumulado.php` (estilo §5 de 3.6.5): glob dinámico de `test-subfase-4.*.php`, agregación de aserciones, validación estructural contra `docs/testing/README.md`, y estado vacío exitoso.
- `AGENTS.md §3` (Running Tests): mandato "al tocar features 004–008 ejecutar `php tests/test-fase-4-acumulado.php`".
- `docs/testing/README.md`: sección "Suites Acumuladas por Fase" con filas (Fase 3 → `test-subfase-3.6.5.php`; Fase 4 → `test-fase-4-acumulado.php`) y árbol actualizado.

### M4 · Gate 3-tier de la propia Acción 3
- Suite `tests/test-gobernanza-accion-3.php`: asserts estáticos (tasks.md 004 con gate; protocolo publicado y anclado; mandato en AGENTS §3; runner acumulado dinámico y estable con fase 4 vacía) + ejecución real del runner + verificación de archivos.
- `php -l` en PHP tocados, `node --check`, servidor `php -S localhost:8000`, regresión 3.1 + 3.6.5. Logs en `logs/gobernanza-accion-3-cli.log` y `-http.log`.
- Reporte `docs/testing/gobernanza-accion-3-gate-fase-4.md` con la sección "Fallos Detectados & Correcciones Quirúrgicas".
- `spec/gobernanza/accion-03-gate-testing/tasks.md` al 100%. Entrada en `spec/constitution/roadmap.md`. **HALT.**

## Riesgos

- **Runner con 0 subphases:** estado vacío es un caso válido (exit 0) y la validación estructural solo aplica a suites ya presentes; se documenta el criterio en el propio runner.
- **Regresión:** la Acción 3 solo crea tests y documentación; se re-ejecuta 3.1/3.6.5 para demostrar ausencia de impacto.
- **Redundancia de reglas:** se añade el mínimo paso de triaje a `general.md`; la consolidación total de reglas duplicadas (H-023, también `.agents/workflows/general.md`) queda fuera de alcance como decisión aprobada.