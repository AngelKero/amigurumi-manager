# Explicación Técnica: Los 10 Invariantes Canónicos de Seguridad (R-01 a R-10)

Los **Invariantes Canónicos de Seguridad** constituyen las leyes inmutables del sistema en **Crochet Manager**. Fueron concebidos para garantizar la integridad referencial, prevenir vulnerabilidades críticas (OWASP Top 10) y preservar la confianza en un entorno colaborativo multi-artesano. Este documento detalla la fundamentación conceptual, el vector de amenaza mitigado y la implementación en código de cada uno de los diez invariantes.

---

## Invariante R-01: Borrado Lógico Universal (Universal Soft-Deletion)
- **Concepto:** Las sentencias directas `DELETE FROM` están estrictamente prohibidas en las entidades de negocio (`usuarios`, `creaciones`, `pedidos`). Toda eliminación es una baja lógica:
  ```sql
  UPDATE <table> SET activo = 0, eliminado_en = datetime('now', 'localtime') WHERE id = :id AND activo = 1;
  ```
- **Amenaza Mitigada:** Pérdida irrevocable de datos históricos, rotura de claves foráneas huérfanas en SQLite y destrucción malintencionada o accidental de registros contables.
- **Implementación:** Todos los métodos de borrado en `app/Repositories/` ejecutan exclusivamente actualizaciones lógicas y las consultas de lectura filtran por `activo = 1` por defecto.

---

## Invariante R-02: Preservación Absoluta de Archivos Multimedia (Asset Preservation)
- **Concepto:** Al dar de baja una creación (`activo = 0`), los archivos de imagen asociados en la carpeta `uploads/` **jamás deben ser eliminados del disco (`unlink()`)**. La llamada a `unlink()` solo está permitida al reemplazar activamente una imagen existente por una nueva en una creación activa.
- **Amenaza Mitigada:** Ruptura de enlaces en órdenes de compra previas, recibos digitales de clientes y comprobantes de auditoría física que referencian la fotografía de la pieza tejida (ADR-008).

---

## Invariante R-03: Autonomía Operativa y Transparencia Multi-Artesanal
- **Concepto:** La plataforma es un espacio abierto para artesanos independientes. Queda terminantemente prohibido incluir textos o promesas que simulen tiempos centralizados de taller fabril (como *"nuestro taller despacha en 5 a 7 días hábiles"*).
- **Amenaza Mitigada:** Falsa publicidad, frustración de expectativas del consumidor y vulneración de la autonomía artesanal.
- **Implementación:** La plataforma canaliza la coordinación de plazos de confección, personalización y entrega directamente mediante WhatsApp con el creador.

---

## Invariante R-04: Prevención de IDOR Multi-Artesano (ADR-007)
- **Concepto:** Una artesana o artesano únicamente tiene autorización para mutar (`update`, `delete`, `adjustStock`, `toggleCommission`) los recursos de su propia autoría (`artesano_id === user.id`) y los pedidos asociados a sus piezas.
- **Amenaza Mitigada:** Insecure Direct Object References (IDOR / OWASP A01: Broken Access Control), donde un usuario manipulaba identificadores numéricos en la URL o cuerpo JSON para alterar o eliminar obras ajenas.
- **Implementación:** En `CreacionService` y `PedidoService`, cada operación verifica la coincidencia estricta del `artesano_id` contra el usuario autenticado en el token Bearer; de no coincidir y no ser `admin`, se emite de inmediato un `HTTP 403 Forbidden`.

---

## Invariante R-05: Inmunidad del Administrador Titular (ID #1 / ADR-010)
- **Concepto:** La cuenta con identificador primario `id: 1` (`@admin`) es la raíz administrativa inmutable del sistema. Bajo ninguna circunstancia puede ser dada de baja (`activo = 0`) ni degradada de rol.
- **Amenaza Mitigada:** Bloqueo permanente de la administración de la plataforma (*Administrator Lockout*), ya sea por error accidental de un operador o sabotaje interno.
- **Implementación:** Validación atómica en `UsuarioService::cambiarRol()` y `UsuarioService::eliminar()`, retornando de forma inmediata `HTTP 403 Forbidden`.

---

## Invariante R-06: Estándar Monetario en Enteros (Exact Integer Cents / ADR-005)
- **Concepto:** Todo valor monetario (`precio`, `costo_materiales`, `precio_final`) se persiste y calcula en **centavos enteros (`INTEGER`)** en la base de datos SQLite. Queda prohibido el tipo `REAL` o coma flotante.
- **Amenaza Mitigada:** Acumulación de errores de redondeo inherentes a la representación binaria IEEE 754 (ej. `$19.999999996` en lugar de `$20.00`), derivando en discrepancias contables en recibos e inventario.
- **Implementación:** Empleo simétrico de `App\Utils\CurrencyHelper` en PHP y `currency.js` en JavaScript.

---

## Invariante R-07: Transacciones Atómicas y Cancelación Idempotente (ADR-009)
- **Concepto:** Toda reserva o cancelación de inventario se ejecuta dentro de un bloque `BEGIN IMMEDIATE TRANSACTION`. La cancelación de un pedido devuelve de forma atómica las unidades a `creaciones.cantidad_stock` y es estrictamente **idempotente** (múltiples cancelaciones no restituyen stock más de una vez).
- **Amenaza Mitigada:** Condiciones de carrera por concurrencia (*Race Conditions*) y sobreventas de inventario artesanal no disponible.
- **Implementación:** Lógica transaccional aislada en `PedidoRepository` y verificada en `PedidoService`.

---

## Invariante R-08: Concurrencia Pragmática y Claves Foráneas en SQLite
- **Concepto:** Cada instancia de conexión PDO inicializa obligatoriamente:
  ```sql
  PRAGMA foreign_keys = ON;
  PRAGMA busy_timeout = 5000;
  ```
- **Amenaza Mitigada:** Inserción de registros huérfanos sin validación relacional y excepciones `SQLITE_BUSY` instantáneas ante accesos concurrentes de lectura y escritura.
- **Implementación:** Registrado en el constructor de `App\Core\Database`.

---

## Invariante R-09: Carga Segura y Aislada de Medios (Secure Uploads)
- **Concepto:** Los archivos subidos por los usuarios deben ser inspeccionados mediante análisis binario de su contenido (`finfo_file` / `mime_content_type`), restringidos a `image/jpeg`, `image/png` y `image/webp`, con peso $\le 5\text{MB}$. El nombre se genera mediante prefijos criptográficos aleatorios (`bin2hex(random_bytes(8))`) y `basename()` para neutralizar path traversal.
- **Amenaza Mitigada:** Carga maliciosa de scripts ejecutables (Remote Code Execution / Web Shells) y sobreescritura de archivos del sistema operativo.
- **Implementación:** Servicio `UploadHelper` y validaciones en controladores de creación.

---

## Invariante R-10: Blindaje contra Fuerza Bruta y Revocación de Tokens (ADR-016)
- **Concepto:** El endpoint `/api/auth/login.php` limita los intentos fallidos a nivel de usuario (máximo 5) e IP (máximo 20 en 15 minutos), devolviendo `HTTP 429 Too Many Requests`. Al cerrar sesión (`/api/auth/logout.php`), el identificador `jti` del token se persiste en la lista negra `tokens_revocados`, garantizando la invalidación inmediata de la sesión en el servidor.
- **Amenaza Mitigada:** Ataques de diccionario, fuerza bruta distribuida y secuestro de tokens Bearer reutilizados tras el cierre de sesión del usuario.
- **Implementación:** `TokenManager` con revocación dinámica por base de datos y tabla `login_intentos`.
