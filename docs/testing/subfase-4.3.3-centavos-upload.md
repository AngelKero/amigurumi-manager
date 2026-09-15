# Reporte de Pruebas: Subfase 4.3.3 — Centavos Multipart & Subida Clicable

- **Fecha de Ejecución:** 2026-09-15
- **Responsable:** Agente IA → humano (HALT antes de 4.4/007)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 + `node --check`
- **Archivo de Log Crudo:** `logs/subfase-4.3.3-cli.log` (suite, 22/22) · `logs/subfase-4.3.3-http.log` (detalle #316)
- **Script de Pruebas:** `tests/test-subfase-4.3.3.php` (Feature 006 correctivo · maestro 009)
- **Resultado General:** **22 / 22 Aprobados (100%)** — ✅ APTO PARA AVANZAR

---

## 1. Objetivo / Alcance

| Archivo | Cambio |
| :--- | :--- |
| `app/Services/CreacionService.php` | `resolveCents()`: `precio_centavos`/`costo_materiales_centavos` autoritativos; legado (`precio`/`costo_materiales`) con conducta histórica intacta |
| `api/creaciones/crear.php`, `actualizar.php` | Pasan `*_centavos` (actualizar sigue ≤60 líneas: 60 exactas) |
| `src/js/modules/creaciones.js` | El submit envía `precio_centavos`/`costo_materiales_centavos` (ya convertidos) |
| `views/pages/formulario_content.php` | Fuera `onclick` inline; dropzone con `role="button"` + `tabindex="0"` |
| `src/js/modules/dropzone.js` | Clic + teclado (Enter/Espacio) por `addEventListener` (compatible CSP) |
| `views/pages/catalogo_content.php` | Fuera `onerror` inline (`#heroCraftedImg` + `#heroFallback`) |
| `src/js/modules/catalog.js` | Listener `error` del hero con cadena de respaldo |
| BD (dato) | #316 Hollow Knight: `precio 3500000→35000`, `costo 1000000→10000` ($350/$100, verificado por HTTP) |
| `tests/test-subfase-4.3.3.php` | Suite §§1-4, 22 aserciones |
| `docs/testing/README.md` | Fila 4.3.3 (22/22) + total Fase 4 → **533/533** |

## 2. Matriz de Aserciones agrupadas

| # | Dominio | Caso | Aprobadas |
| - | :--- | :--- | :---: |
| 1 | Servicio | Panel `*_centavos` string íntegros; legado pesos intacto; int intacto; `"0"` → 422; limpieza | 7/7 |
| 2 | HTTP multipart | 201 + `precio_centavos`/`costo_materiales_centavos` exactos + limpieza | 5/5 |
| 3 | Vistas | Cero `onclick`/`onsubmit` en formulario; `role`/`tabindex`; cero `onerror` en catálogo | 6/6 |
| 4 | dropzone.js | Clic + teclado por listener, `inputFile.click()`, `node --check` | 4/4 |

## 2b. Trazabilidad de los reportes de usuario

| Reporte | Causa raíz | Fix + evidencia | ✅ |
| :--- | :--- | :--- | :--- |
| #316 guardada con 350 → $35,000 | `FormData` envía strings; `is_int("35000")` falso → `mxnToCents` de nuevo (×100) | `resolveCents()` dual explícito; HTTP multipart 38000→38000; auditoría: única pieza de panel afectada; dato #316 corregido y verificado (`$350.00 MXN`) | ✅ |
| Botón subir imagen muerto, sin foto | `onclick` inline bloqueado por CSP `script-src 'self'` (sin `unsafe-inline`) | Binding en `dropzone.js` + `role/tabindex`; suite §3-4 | ✅ |

## 3. Evidencia HTTP

- `GET detalle.php?id=316` → `precio: 35000 $350.00 MXN | costo: 10000`.
- Suite §2 (multipart con foto + centavos exactos) en `logs/subfase-4.3.3-cli.log`.

## 4. Fallos Detectados & Correcciones Quirúrgicas

| # | Fallo | Causa | Corrección | Estado |
| - | :--- | :--- | :--- | :--- |
| 1 | Mi primer fix (dígitos → centavos) alteraba el legado (`"450"` → $4.50) y rompía el seed (int 19900) | Heurística ambigua | **Contrato dual explícito**: `*_centavos` autoritativos, legado byte-idéntico; seed y suites 3.x intactos | ✅ |
| 2 | `actualizar.php` quedó en 61 líneas (>60) | 2 claves nuevas | Recorte de 1 línea (60 exactas, verificado) | ✅ 3.6.5 verde |
| 3 | Flake 4.2 bajo runner (1/136, 5ª aparición) | **Entorno** transitorio | Re-corrida en verde; registrado (H-015) | ✅ |
| 4 | 3.6.5 clobber de claves (3.2 §4.10) | **Entorno** conocido | Orden: Fase 3 → claves README → gate Fase 4 re-verificado | ✅ |

## 5. Integridad y datos

- `UPDATE` quirúrgico solo en #316 (`WHERE id = 316 AND activo = 1`, 1 fila); auditoría: resto de piezas de usuario inexistentes (solo seed + tests en baja lógica).
- Pendientes ajenos anotados (no tocados): `onclick confirm` en `usuarios_content.php:195` → 008; `onsubmit alert` en `modal_checkout.php:20` → 007.

## 6. Veredicto

- [x] Suite CLI 22/22 + HTTP vivo + trazabilidad de ambos reportes.
- [x] Regresiones: 4.3 (212/212), 4.3.1 (62/62), 4.3.2 (43/43), 3.6.5 (141/141), acumulado Fase 4 (**533/533**), H-006 (1.287, previo).
- [x] `006` (AC-17/18), `006/tasks.md` §9, `009/tasks.md`, `roadmap.md`, README actualizados.
- **Estado:** ⏸ **HALT — esperando autorización para 4.4 (Feature 007).**
