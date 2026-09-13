# Plan Maestro de la Fase 3: Backend & Clean Architecture

[← Volver al Índice de Arquitectura](./README.md) • [Ver Ciclo de Vida de Todas las Fases (1 a 5)](./proceso-desarrollo-fases.md)

La **Fase 3** implementa la capa de backend completa para el sistema **Crochet Manager**, estructurada en 6 subfases secuenciales bajo el protocolo de compuertas obligatorias de testing de 3 niveles.

---

## 1. Hoja de Ruta de Subfases & Estado

| Subfase | Nombre del Hito | Estado | Aserciones | Reporte QA |
| :---: | :--- | :---: | :---: | :--- |
| **3.1** | Base del Backend & Infraestructura Nuclear | ✅ **Completado & Verificado** | 93 / 93 OK | [subfase-3.1-core.md](../testing/subfase-3.1-core.md) |
| **3.2** | Autenticación Stateless & Bearer Middleware | ✅ **Completado & Verificado** | 69 / 69 OK | [subfase-3.2-auth.md](../testing/subfase-3.2-auth.md) |
| **3.3** | Gestión de Usuarios, Roles RBAC & Bajas Lógicas | ✅ **Completado & Verificado** | 105 / 105 OK | [subfase-3.3-usuarios.md](../testing/subfase-3.3-usuarios.md) |
| **3.4** | Catálogo, Creaciones & Ciclo de Vida de Fotos | ✅ **Completado & Verificado** | 126 / 126 OK | [subfase-3.4-creaciones.md](../testing/subfase-3.4-creaciones.md) |
| **3.5** | Pedidos, Transacciones Atómicas & WhatsApp | ✅ **Completado & Verificado** | 139 / 139 OK | [subfase-3.5-pedidos.md](../testing/subfase-3.5-pedidos.md) |
| **3.6.1** | Acceso, Autorización, IDOR & Blindaje RBAC (OWASP A01) | ✅ **Completado & Verificado** | 165 / 165 OK | [subfase-3.6.1-idor-access-control.md](../testing/subfase-3.6.1-idor-access-control.md) |
| **3.6.2** | Criptografía, Auth & Datos Sensibles (OWASP A02+A07) | ✅ **Completado & Verificado** | 161 / 161 OK | [subfase-3.6.2-criptografia-autenticacion.md](../testing/subfase-3.6.2-criptografia-autenticacion.md) |
| **3.6.3** | Inyección, Sanitización & Medios (OWASP A03+A08) | ✅ **Completado & Verificado** | 157 / 157 OK | [subfase-3.6.3-inyeccion-medios.md](../testing/subfase-3.6.3-inyeccion-medios.md) |
| **3.6.4** | Lógica de Negocio, Precios & Multibyte (OWASP A04) | ✅ **Completado & Verificado** | 151 / 151 OK | [subfase-3.6.4-logica-precios.md](../testing/subfase-3.6.4-logica-precios.md) |
| **3.6.5** | Rendimiento SQLite, Clean Architecture & Regresión | ✅ **Completado & Verificado** | 141 / 141 OK | [subfase-3.6.5-rendimiento-regresion.md](../testing/subfase-3.6.5-rendimiento-regresion.md) |

**Total Acumulado Fase 3 Completa (3.1 a 3.6.5):** **1,307 / 1,307 aserciones aprobadas (100% OK en verde)**.

---

## 2. Alcance por Subfase

### Subfase 3.1: Infraestructura Nuclear & Base
- `app/autoload.php`: Autocargador PSR-4 nativo sin Composer (`App\` $\rightarrow$ `app/`).
- `app/config.php` & `App\Core\Config`: Configuración estática centralizada.
- `App\Core\Database`: Singleton PDO SQLite con `PRAGMA foreign_keys = ON;` y `PRAGMA busy_timeout = 5000;`.
- `App\Core\ErrorHandler`: Captura global de errores con `ob_end_clean()` y JSON 500.
- `App\Core\Request` & `App\Core\Response`: Abstracciones seguras de entrada y salida con preflight CORS 204.
- `App\Core\TokenManager`: Gestor de tokens HMAC-SHA256 con 24h TTL y `hash_equals()`.
- `App\Utils\`: `PaginationHelper`, `CurrencyHelper` (centavos enteros $\leftrightarrow$ MXN) y `SvgHelper`.

### Subfase 3.2: Autenticación Stateless & Bearer Middleware
- `App\Repositories\UsuarioRepository`: Consultas preparadas de usuarios con salvaguarda ID #1.
- `App\Services\AuthService`: Hashing bcrypt, mitigación de timing attack con dummy hash, emisión de tokens y autoservicio de cambio de contraseña (`changePassword()`).
- `App\Middleware\AuthGuard`: Validación Bearer compatible con FastCGI y respuesta uniforme 401.
- `App\Middleware\RoleGuard`: Control RBAC con respuesta uniforme 403.
- Controladores en `api/auth/`: `login.php`, `logout.php`, `me.php`, `cambiar-password.php`.

### Subfase 3.3: Gestión de Usuarios & Roles RBAC
- Estándar universal de baja lógica: `UPDATE usuarios SET activo = 0, eliminado_en = datetime(...)`.
- `UsuarioService`: Listado paginado con filtro `?estado=activos|inactivos|todos`, alta de creador, modificación de rol con salvaguarda ID #1, actualización de nombre, restablecimiento de contraseña (manual y temporal autogenerada), baja lógica con salvaguardas (auto-eliminación, creaciones activas 409, cuenta ya inactiva 409), y reactivación (`reactivateUser()`).
- Controladores en `api/usuarios/`: `index.php`, `crear.php`, `cambiar-rol.php`, `actualizar.php`, `restablecer-password.php`, `eliminar.php`, `reactivar.php`.

### Subfase 3.4: Catálogo, Creaciones & Ciclo de Vida de Imágenes
- `App\Repositories\CreacionRepository`: Consultas de catálogo paginado, filtros de categoría, precio min/max, texto, estado de encargo, stock, inventario del taller, conteos y lista de artesanos activos para `#filterArtisan` (Punto F).
- `App\Services\CreacionService`: Enriquecimiento monetario dual (`CurrencyHelper`), subida de imágenes JPEG/PNG/WebP ($\le 5\text{MB}$), fallback a vector SVG de `assets/svg/piezas/`, ciclo de vida de fotos (reemplazo purga archivo previo con `unlink()`; **baja lógica preserva archivo sin `unlink()`**), protección IDOR multi-artesano y métricas KPI de inventario.
- Controladores en `api/creaciones/`: `index.php`, `artesanos.php`, `detalle.php`, `crear.php`, `actualizar.php`, `eliminar.php`, `restaurar.php`, `ajustar-stock.php`, `toggle-encargo.php`.

### Subfase 3.5: Pedidos, Transacciones Atómicas & WhatsApp
- `App\Repositories\PedidoRepository`: Transacciones atómicas de stock con `BEGIN IMMEDIATE TRANSACTION`, listados aislados por artesano, actualización de estado de entrega y pago, y cancelación con restitución de inventario.
- `App\Services\PedidoService`: Validación de existencias, cálculo de precios congelados en servidor (`precio * cantidad`), enlaces directos a WhatsApp y verificación de cancelación idempotente (409 Conflict si ya estaba cancelado).
- Controladores en `api/pedidos/`: `index.php`, `solicitar.php`, `crear.php`, `cambiar-estado.php`, `cancelar.php`.

### Subfase 3.6: Auditoría Integral de Seguridad OWASP, Rendimiento SQLite & Regresión Global
Para garantizar la máxima exhaustividad y robustez técnica, esta subfase se desglosa en 5 sub-subfases individuales ([Ver Documento de Especificación Completo](./subfase-3.6-auditoria-seguridad.md)):
- **3.6.1 (OWASP A01:2021):** Control vertical RBAC, prevención IDOR horizontal en Creaciones y Pedidos, salvaguarda de cuenta raíz ID #1, bloqueo de auto-eliminación activa, aislamiento de recursos inactivos y método 405 en los 25 controladores (165/165 OK).
- **3.6.2 (OWASP A02:2021 + A07:2021):** Integridad criptográfica de tokens Bearer HMAC-SHA256 con `hash_equals()`, ciclo de vida y TTL 24h, higiene bcrypt cost factor 10, mitigación timing attack con dummy hash, mensajes no enumerables, cero exposición de `password_hash`, políticas de contraseñas ($\ge 6$ chars y temporales `Crochet!<hex>!`), revocación inmediata en bajas lógicas y logout stateless.
- **3.6.3 (OWASP A03:2021 + A08:2021):** Blindaje SQLi 100% prepared statements, mitigación XSS en capa de presentación, detección MIME binaria real (`finfo`), protección contra path traversal (`../../`) y desinfección SVG.
- **3.6.4 (OWASP A04:2021):** Cálculo de precios en servidor, aislamiento transaccional de stock atómico, cancelación idempotente y resiliencia UTF-8 4-byte (emojis 🧶🧸).
- **3.6.5 (Rendimiento & Regresión):** Auditoría `EXPLAIN QUERY PLAN` sobre índices (`idx_*`), erradicación N+1, tolerancia `busy_timeout = 5000` y suite de regresión acumulada total (3.1 a 3.6.4).

---

## 3. Protocolo de Compuertas de Testing (Testing Gate)

Al concluir el código de cada subfase, se ejecuta rigurosamente:
1. **Suite CLI Nativa:** `php tests/test-subfase-3.X.php > logs/subfase-3.X-cli.log 2>&1`.
2. **Pruebas HTTP en Vivo:** Pruebas curl con cabeceras hacia `logs/subfase-3.X-http.log`.
3. **Reporte Ejecutivo:** Redacción de `docs/testing/subfase-3.X-[nombre].md`.
4. **Sincronización:** Actualización del Memory Bank.
5. **Compás de Espera Inviolable:** Detención total para aguardar la aprobación explícita del usuario antes de iniciar la siguiente subfase.
