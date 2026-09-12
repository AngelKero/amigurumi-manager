# Plan Maestro de la Fase 3: Backend & Clean Architecture

[← Volver al Índice de Arquitectura](./README.md)

La **Fase 3** implementa la capa de backend completa para el sistema **Crochet Manager**, estructurada en 6 subfases secuenciales bajo el protocolo de compuertas obligatorias de testing de 3 niveles.

---

## 1. Hoja de Ruta de Subfases & Estado

| Subfase | Nombre del Hito | Estado | Aserciones | Reporte QA |
| :---: | :--- | :---: | :---: | :--- |
| **3.1** | Base del Backend & Infraestructura Nuclear | ✅ **Completado & Verificado** | 93 / 93 OK | [subfase-3.1-core.md](../testing/subfase-3.1-core.md) |
| **3.2** | Autenticación Stateless & Bearer Middleware | ✅ **Completado & Verificado** | 69 / 69 OK | [subfase-3.2-auth.md](../testing/subfase-3.2-auth.md) |
| **3.3** | Gestión de Usuarios, Roles RBAC & Bajas Lógicas | ✅ **Completado & Verificado** | 105 / 105 OK | [subfase-3.3-usuarios.md](../testing/subfase-3.3-usuarios.md) |
| **3.4** | Catálogo, Creaciones & Ciclo de Vida de Fotos | 📋 **Aprobado (Listo para Código)** | ~70 previstas | `docs/testing/subfase-3.4-creaciones.md` |
| **3.5** | Pedidos, Transacciones Atómicas & WhatsApp | ⏳ Pendiente | ~60 previstas | `docs/testing/subfase-3.5-pedidos.md` |
| **3.6** | Auditoría Integral de Seguridad & Regresión | ⏳ Pendiente | Suite completa | `docs/testing/subfase-3.6-seguridad.md` |

**Total Acumulado a la fecha:** **267 / 267 aserciones aprobadas (100% OK)**.

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

### Subfase 3.6: Auditoría Integral de Seguridad & Regresión
- Auditoría OWASP Top Ten: prevención de inyección SQL, XSS, autenticación rota y control de acceso roto (IDOR).
- Pruebas de estrés y concurrencia SQLite con `busy_timeout`.
- Suite completa de regresión ejecutando las suites 3.1 a 3.5 consecutivamente.

---

## 3. Protocolo de Compuertas de Testing (Testing Gate)

Al concluir el código de cada subfase, se ejecuta rigurosamente:
1. **Suite CLI Nativa:** `php tests/test-subfase-3.X.php > logs/subfase-3.X-cli.log 2>&1`.
2. **Pruebas HTTP en Vivo:** Pruebas curl con cabeceras hacia `logs/subfase-3.X-http.log`.
3. **Reporte Ejecutivo:** Redacción de `docs/testing/subfase-3.X-[nombre].md`.
4. **Sincronización:** Actualización del Memory Bank.
5. **Compás de Espera Inviolable:** Detención total para aguardar la aprobación explícita del usuario antes de iniciar la siguiente subfase.
