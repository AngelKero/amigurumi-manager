# Subfase · Gobernanza · Acción 3: Gate de Testing de la Fase 4 (H-008 · H-015 · H-020)

| Campo | Detalle |
| :--- | :--- |
| **Acción** | `spec/gobernanza/accion-03-gate-testing/` |
| **Origen** | `reporte-auditoria-gobernanza.md` → Recomendación #3 (H-008 severe, H-015 severe, H-020 🟡) |
| **Aprobación** | 14/09/2026 — alcance H-008+H-015+H-020; runner dinámico con estado vacío OK; triaje anclado en docs/testing + AGENTS §3 + regla general |
| **Tier 1 (CLI)** | 34/34 aserciones · `tests/test-gobernanza-accion-3.php` |
| **Tier 2 (HTTP)** | Smoke en vivo (204 preflight, 401 me sin token, CSP en `/`) — sin divergencia CLI/HTTP |
| **Tier 3 (Reporte)** | Este documento |
| **Regresión** | 3.1 (93/93) · 3.6.5 (141/141) · gobernanza-2 (96/96) · fase-4 runner (4/4) |
| **Estado** | ✅ **APROBADO** → **HALT** (pendiente aprobación explícita del usuario) |

---

## 1. Objetivo

Blindar el protocolo de testing de la Fase 4 antes de que arranque su codificación, cerrando los tres hallazgos de gobernanza detectados en auditoría.

## 2. Entregas Verificadas

### H-008 · El SDD de 004 incorpora el gate 3-tier por subfase (severe)
- `spec/features/004-auth-sesion-cliente/tasks.md` reescrito: cada subfase 4.1–4.5 exige suite CLI (`tests/test-subfase-4.X.php`), logs crudos CLI/HTTP (`logs/subfase-4.X-*.log`), reporte ejecutivo en `docs/testing/subfase-4.X-*.md` con "Fallos Detectados & Correcciones Quirúrgicas", HALT por subfase y validación de aceptación en `spec.md`.
- Nota de prerrequisitos de la Acción 2 incrustada en 4.1 (blockout `429`, revocación `revocado_en_servidor`, cabecera `Content-Security-Policy`).
- La regresión por fase (`test-fase-4-acumulado.php`) es tarea obligatoria del SDD.

### H-015 · Protocolo de divergencia CLI vs. HTTP (severe)
- Publicado `docs/testing/protocolo-divergencia-cli-http.md`: matriz de 4 escenarios (A coherente, B·C divergencia, D doble rojo), triaje en 6 pasos (re-producir → aislar → clasificar entorno/código → registrar traza → decidir → halting), definiciones de "defecto de entorno de pruebas" vs "defecto de código", plantilla de registro para reportes y mandato.
- Anclado en tres puntos: `AGENTS.md §3` (nuevo paso 4 del gate + referencia en Running Tests), `.agents/rules/general.md` (paso 3 del gate numerado) y `docs/testing/README.md` (´Protocolo` en el árbol de Nivel 1).

### H-020 · Suite de regresión acumulada por fase (🟡)
- Nuevo `tests/test-fase-4-acumulado.php`: runner con **despliegue dinámico** (`glob('tests/test-subfase-4.*.php')`), **estado vacío exitoso** (0 subfases → exit 0 en fase 4 en desarrollo), **validación estructural bidireccional** (cada suite descubierta con fila en `README.md` y cada fila con script físico) y ejecución secuencial con agregación de aserciones.
- Mandato en `AGENTS.md §3` (Running Tests): tocar features 004–008 ⇒ `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1`.
- `docs/testing/README.md`: nueva tabla "Suites de Regresión Acumulada por Fase" (Fase 3 → 3.6.5; Fase 4 → acumulado) y referencia al protocolo.

## 3. Evidencia (Gate 3-Tier)

| Tier | Artefacto | Resultado |
| :--- | :--- | :--- |
| 1 | `logs/gobernanza-accion-3-cli.log` | **33 / 33** (100% OK) |
| 1 | `logs/fase-4-acumulado.log` | **4 / 4** (runner fase 4 vacía, exit 0) |
| 2 | `logs/gobernanza-accion-3-http.log` | `OPTIONS` login → 204 + CORS; `me` sin token → 401; `/` → CSP presente |
| Reg | `logs/subfase-3.1-cli.log` | 93 / 93 |
| Reg | `logs/subfase-3.6.5-cli.log` | 141 / 141 |
| Reg | `logs/gobernanza-accion-2-regresion.log` | 96 / 96 |
| Lint | `php -l tests/test-fase-4-acumulado.php tests/test-gobernanza-accion-3.php tests/TestHelper.php` | 0 errores de sintaxis |

> **Triaje CLI/HTTP (Protocolo H-015):** escenario **A · Coherente**. CLI 34/34 y smoke HTTP en verde; no se registró divergencia y no se activó HALT por bloqueo de defecto de código.

## 4. Fallos Detectados & Correcciones Quirúrgicas

No hubo fallos de aserciones (R-G verde a la primera). Sin embargo, durante la fase de redacción se corrigieron dos errores de diseño/documentación:

1. **Diseño de filas por subfase en `README.md`** — Se consideró listar filas 4.1–4.5 con sus suites aún no creadas; eso habría roto la dirección 2 del runner (toda fila con script físico). **Corrección:** se documenta únicamente la suite acumulada de Fase 4 y la norma de que las filas de subfase se añaden cuando la suite nace. El runner seguirá verificando la coherencia bidireccional automáticamente.
2. **Ubicación del protocolo en el árbol de Nivel 1** — La primera edición dejó `protocolo-divergencia-cli-http.md` dentro de la rama `tests/` del diagrama, cuando es un documento de `docs/testing/`. **Corrección quirúrgica:** movido a la rama de Nivel 1 y el árbol re-leído para confirmar.

## 5. Criterios de Aceptación — Cierre

- [x] tasks.md de 004 con gate 3-tier por subfase 4.X.
- [x] Protocolo publicado con matriz, triaje 6 pasos, entorno vs código y halting.
- [x] Anclas en AGENTS.md §3 y `.agents/rules/general.md`.
- [x] `test-fase-4-acumulado.php` ejecutable, dinámico y con estado vacío exitoso.
- [x] README.md de testing con suites acumuladas por fase.
- [x] Suite de la Acción 3 (34/34) + regresión 3.1/3.6.5/2 en verde.
- [x] Reporte ejecutivo con sección de fallos (este documento).
- [x] `spec/constitution/roadmap.md` actualizado (ver entrada nueva).
- [x] **HALT COMPLETO** — se pasa la pelota al humano.

## 6. Fuera de Alcance (deferido a Acciones 4–5)

- H-006 / H-013 / H-022 (Acción 5: re-sincronización documental — totales y cifras no tocados).
- H-005 / H-011 / H-012 / H-023 (Acción 4: consolidación de reglas, incl. duplicidad `.agents/workflows/general.md`).
- H-018 / H-019: registrados para acciones futuras.