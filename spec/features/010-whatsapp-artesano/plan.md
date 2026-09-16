# Plan 010 · WhatsApp al Artesano

## Orden de ejecución (Red → Green → Refactor, 3-tier por AGENTS.md §3)

### Etapa 1 — DB (R-08 pragmas intactos, R-01 sin físicos)
1. `database/seed.sql`: columna `whatsapp TEXT DEFAULT NULL` + `CHECK
   (whatsapp IS NULL OR length(trim(whatsapp)) <= 20)` + índice no necesario;
   seeds: admin y artesana_ana con números demo de 10 dígitos, asistente NULL
   (caso vacío ejercitado).
2. `scripts/migrate-010-whatsapp.php` (CLI-only, idempotente): `PRAGMA
   table_info(usuarios)` → `ALTER TABLE ... ADD COLUMN` solo si falta.
3. Ejecutar migración sobre `database/database.sqlite` (no destructiva; no se
   usa `setup.php` para no borrar datos de desarrollo).

### Etapa 2 — Backend
4. Nuevo `app/Utils/WhatsAppHelper`: `normalize()` (solo dígitos, 10→`52`,
   válido 8–15) + `link()` (`wa.me/<e164>?text=` + mensaje) + validación de
   crudo (≤20, 422). `PedidoService::buildWhatsAppLink()` delega (firma intacta).
5. `UsuarioRepository`: `whatsapp` en los 5 SELECTs + `create(..., ?whatsapp)` +
   `updateWhatsapp()`. `UsuarioService`: `normalizeWhatsapp()`, `createUser(...,
   ?whatsapp)`, `updateWhatsapp(id, whatsapp, currentUser)` (admin:cualquiera,
   artesano:propio, otro→403).
6. `PedidoRepository`: `u.whatsapp AS creacion_artesano_whatsapp` en `findById`
   y `listAll` + `hydrateOrder` (`creacion.artesano_whatsapp`).
7. `PedidoService`: `requestPublicOrder()` resuelve el WhatsApp del artesano vía
   `UsuarioRepository` (nueva dependencia) y construye el enlace con el mensaje
   aprobado; `enrichOrder()` igual desde el JOIN (null si no hay número).
8. `CreacionRepository`: `u.whatsapp AS artesano_whatsapp` en `findById` y
   `listCatalog` (+ resto de SELECTs con join a usuarios); `hydrateRow` anida
   `artesano.whatsapp`. `CreacionService::enrichCreation()` añade
   `artesano_whatsapp` + `enlace_whatsapp_artesano` (mensaje genérico sin pedido:
   *"¡Hola! Te escribo por tu creación '{nombre}' en Crochet Manager."*).
9. API: `crear.php` acepta `whatsapp` opcional; nuevo
   `api/usuarios/actualizar-whatsapp.php` (`artisanOrAdmin()` + ownership en
   servicio, ≤60 líneas, `handleCors` primero).

### Etapa 3 — Frontend (H-004, Algodón Nórdico)
10. `modal_crear_usuario.php` + `users.js`: campo opcional + `data-whatsapp` en
    filas (mock hasta 008) con validación espejo.
11. Autoservicio: bloque "Mi WhatsApp" en `panel_sidebar.php` + modal pequeño +
    fetch con Bearer en `users.js` (solo páginas de panel).
12. `checkout.js`: hint alternativo cuando `enlace_whatsapp` es null
    ("El artesano aún no registra WhatsApp; te contactará con los datos que
    dejaste.").
13. Ficha pública: botón "Contactar por WhatsApp" en `detalle_content.php`
    (sello del taller) hidratado por `detail.js` desde
    `enlace_whatsapp_artesano` (oculto si null) + CSS en `detail.css`.

### Etapa 4 — Calidad
14. Actualizar asserts in situ (mismo conteo): `test-subfase-3.5.php`
    (buildWhatsAppLink), `test-subfase-3.6.3/3.6.4.php` (enlace), `test-subfase-4.4.php`
    (wa.me comprador → artesano).
15. Nueva `tests/test-subfase-4.6-whatsapp-artesano.php` (migración, CRUD del
    campo, 403 IDOR, solicitar con/sin número, exposición pública).
16. Regresión `test-fase-4-acumulado.php` + `cuenta-aserciones.php`; actualizar
    cifras canónicas afectadas + `docs/api/*` + reporte de testing. HALT hasta
    aprobación antes de 008.
