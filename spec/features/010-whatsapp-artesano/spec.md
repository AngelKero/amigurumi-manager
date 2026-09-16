# 010 · WhatsApp al Artesano (Subfase 4.6)

**Estado:** en ejecución (decisiones del humano registradas 2026-09-15)

> Feature hija del plan maestro de la Fase 4 (`spec/features/009-plan-maestro-fase-4/`).
> Corrige el botón "Coordinar por WhatsApp": hoy apunta a `wa.me/<comprador>`
> (`PedidoService::buildWhatsAppLink($clienteContacto, ...)`) con mensaje en voz
> del artesano, así que el comprador abre un chat consigo mismo.

## Qué hace

- Nuevo campo opcional `usuarios.whatsapp` (número del artesano/vendedor).
- El `enlace_whatsapp` de pedidos se construye con el teléfono del **artesano**
  y mensaje en voz del comprador: *"¡Hola! Soy {comprador}, te escribo por mi
  pedido #{id} de '{creación}' en Crochet Manager."*
- El teléfono/enlace del artesano también se expone en público (ficha de
  creación) para contacto directo.
- Alta (admin) y autoservicio (el propio artesano) para registrar/editar el número.

## Decisiones del humano (vinculantes)

1. WhatsApp **opcional** (`NULL` = sin número; empty-state sin botón).
2. Edición: **admin (cualquiera) + autoservicio (propio número)**.
3. Exposición **también en público** (ficha de creación).
4. **Cambiar el campo existente** `enlace_whatsapp` (no campo dual).
5. Mensaje propuesto aceptado (voz comprador→artesano).

## Criterios de aceptación

- [x] `usuarios.whatsapp` existe (DDL + CHECK + migración no destructiva + seeds).
- [x] `POST solicitar.php` devuelve `enlace_whatsapp = wa.me/<artesano>?text=...` con el mensaje aprobado; si el artesano no tiene número, `enlace_whatsapp = null` y el frontend oculta el botón con hint alternativo.
- [x] `GET detalle.php?id=` (creaciones) y catálogo exponen `artesano_whatsapp` + `enlace_whatsapp_artesano` (null si no hay número).
- [x] Admin puede crear/editar el WhatsApp de cualquier artesano; el artesano solo el propio (otro → 403, IDOR R-04).
- [x] Validación: opcional; si se envía, 8–15 dígitos tras limpiar, crudo ≤ 20 chars (422 si no).
- [x] Frontend: alta con campo opcional, columna/acción en directorio, editor autoservicio en sidebar, checkout con hint sin-número, ficha pública con botón WhatsApp (H-004, cero `wa.me` en JS).
- [x] Suites actualizadas en su lugar (mismo conteo) + nueva suite de la subfase en verde; regresión Fase 4 y `cuenta-aserciones.php` coherentes; reporte `docs/testing/subfase-4.6-whatsapp-artesano.md`.

## Fuera de alcance

- Pasarelas de pago; verificación del número por SMS; múltiples números por artesano.
- Directorio server-driven de usuarios (Feature 008): el directorio sigue mock, el campo vive en `data-*` hasta 008.
