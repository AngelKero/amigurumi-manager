# Reporte de Pruebas: Subfase 4.6 — WhatsApp al Artesano Vendedor (Feature 010)

- **Fecha de Ejecución:** 2026-09-15
- **Responsable:** Agente IA (Muse Spark) + validación humana
- **Entorno:** PHP 8.x CLI + Servidor Built-in (`localhost:8000`) + SQLite 3
- **Archivo de Log Crudo:** `logs/fase-4-acumulado.log` (Tier 2)
- **Script de Pruebas:** `tests/test-subfase-4.6-whatsapp-artesano.php`
- **Resultado General:** [64 / 64 Aprobados (100%)] — ✅ APTO PARA AVANZAR

---

## 1. Matriz de Aserciones y Casos Evaluados

| # | Componente / Endpoint | Caso de Prueba | Entrada / Payload | Código Esperado | Código Obtenido | Estado |
| - | :--- | :--- | :--- | :---: | :---: | :---: |
| 1–6 | DB `usuarios.whatsapp` | Columna, CHECK, migración idempotente, seeds demo | `PRAGMA table_info` + `seed.sql` | Existe + seeds | Existe + seeds | ✅ PASS |
| 7–17 | `App\Utils\WhatsAppHelper` | E.164 MX (10→52), limpieza, inválidos→null, `sanitizeOptional` 422, `link()` | `5512345678`, `+52 55...`, `1234`, 21 chars | link / null / 422 | link / null / 422 | ✅ PASS |
| 18–24 | Usuarios (servicio) | Alta con número, alta inválida 422, autoservicio, admin→otro, IDOR 403, retiro null | `wa_artesano_*`, `id:1` como ana | 403/422/200 | 403/422/200 | ✅ PASS |
| 25–38 | Pedidos (servicio) | `solicitar` destino artesano, voz comprador, buyer∉URL, `enlace_whatsapp_comprador` al comprador (voz artesano), null sin número, JOIN + ficha pública | pieza de artesano temporal | wa.me artesano / comprador / null | wa.me artesano / comprador / null | ✅ PASS |
| 40–50 | HTTP vivo | `solicitar` 201 artesano, `actualizar-whatsapp` 403/422/200, detalle + ficha + CSP | Bearer admin/ana | 201/403/422/200 | 201/403/422/200 | ✅ PASS |
| 51–64 | Frontend + limpieza | Campos, `data-whatsapp`, autoservicio sidebar, hint, `node --check`, baja de efímeros | estático + sintaxis | 0 errores | 0 errores | ✅ PASS |

---

## 2. Evidencia de Respuestas JSON y Cabeceras

### Solicitud pública (201) — destino artesano
```json
{
  "id": 76,
  "creacion_nombre": "Ajolote Rosado Pastel",
  "enlace_whatsapp": "https://wa.me/525512345678?text=%C2%A1Hola!%20Soy%20Test%20Buyer,%20te%20escribo%20por%20mi%20pedido%20%2376..."
}
```

### Sin número del artesano (201, empty-state)
```json
{ "id": 77, "enlace_whatsapp": null }
```
El frontend oculta `#checkoutWhatsAppBtn` y muestra: *"El artesano aún no registra WhatsApp; te contactará con los datos que dejaste."*

### IDOR (403) y validación (422)
```json
{ "exito": false, "error": { "codigo": 403, "mensaje": "No tienes autorización para modificar el WhatsApp de otro usuario." } }
```

---

## 3. Verificación de Integridad en SQLite

- `PRAGMA integrity_check;` -> `ok`
- Columna `usuarios.whatsapp TEXT DEFAULT NULL` + `chk_usuarios_whatsapp` en `seed.sql`; migración `scripts/migrate-010-whatsapp.php` idempotente (doble ejecución verificada).
- Fase 3 intacta: `php tests/cuenta-aserciones.php` → **1,287 / 1,287** (H-006).
- Fase 4 acumulada: **742 / 742** (678 + 64 de 4.6).

---

## 4. Fallos Detectados & Correcciones Quirúrgicas

1. **Conteo de controladores 26 → 27** (`test-subfase-3.6.5.php`): snapshot legítimo por el nuevo `api/usuarios/actualizar-whatsapp.php` (44 líneas, ≤60). Actualizado in situ, conteo total Fase 3 intacto (mismo nº de asserts).
2. **Claves de prueba `artesana123` vs seed `admin123`**: las suites 4.3–4.4 exigen la clave canónica del README (`artesana123`); sobre semilla limpia fallaban los logins de Ana. Aplicado el paso documentado en `spec/features/007-*/tasks.md` (normalizar clave de Ana a `artesana123` tras el gate de Fase 3 y antes del gate de Fase 4). **Desincronización P6 reportada**: `seed.sql` (admin123 para todos) vs `README.md` + suites 4.x (`artesana123`) — el humano debe decidir si el seed adopta las claves del README.
3. **Asserts de destino WhatsApp** (`3.6.3`, `3.6.4`, `4.4` ×2): actualizados in situ al nuevo destino (artesano) sin cambiar el conteo.

---

## 5. Veredicto y Siguientes Pasos

- [x] Bug original corregido: el botón apunta a `wa.me/<artesano>` con mensaje en voz del comprador.
- [x] Campo opcional con alta (admin), edición (admin/todos + autoservicio propio) y exposición pública en ficha.
- [x] Cero `wa.me` compuestos en frontend; H-004 intacto; R-01/R-04/R-08 intactos.
- [x] Logs respaldados en `logs/fase-4-acumulado.log`.
- **Estado:** Esperando autorización del usuario para dar por cerrada la 4.6 (pendiente 4.5/008).
