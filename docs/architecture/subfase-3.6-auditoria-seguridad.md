# Subfase 3.6: Auditoría Integral de Seguridad OWASP, Rendimiento SQLite & Regresión Global

[← Volver al Índice de Arquitectura](./README.md) • [Ver Plan Maestro de la Fase 3](./phase-3-plan.md) • [Ver Reportes de Testing](../testing/README.md)

La **Subfase 3.6** constituye la fase final de consolidación, blindaje y aseguramiento de calidad del backend de **Crochet Manager**. Atendiendo a las directrices de Clean Architecture y la política de granularidad técnica (*"Opción B"*), esta subfase se divide en **5 sub-subfases secuenciales temáticas**, cada una gobernada por el protocolo estricto de compuertas de testing de 3 niveles.

---

## 1. Hoja de Ruta de las 5 Sub-subfases de Seguridad

| Sub-subfase | Enfoque Principal | Estándar / Dominio | Estado | Aserciones | Reporte QA |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **3.6.1** | Acceso, Autorización, IDOR & Blindaje RBAC | OWASP A01:2021 (Broken Access Control) | ✅ **Completado & Verificado** | 165 / 165 OK | [subfase-3.6.1-idor-access-control.md](../testing/subfase-3.6.1-idor-access-control.md) |
| **3.6.2** | Criptografía, Auth & Datos Sensibles | OWASP A02:2021 + A07:2021 | ✅ **Completado & Verificado** | 161 / 161 OK | [subfase-3.6.2-criptografia-autenticacion.md](../testing/subfase-3.6.2-criptografia-autenticacion.md) |
| **3.6.3** | Inyección, Sanitización & Medios/Archivos | OWASP A03:2021 + A08:2021 | ✅ **Completado & Verificado** | 157 / 157 OK | [subfase-3.6.3-inyeccion-medios.md](../testing/subfase-3.6.3-inyeccion-medios.md) |
| **3.6.4** | Lógica de Negocio, Precios & Multibyte | OWASP A04:2021 (Insecure Design) | ✅ **Completado & Verificado** | 151 / 151 OK | [subfase-3.6.4-logica-precios.md](../testing/subfase-3.6.4-logica-precios.md) |
| **3.6.5** | Rendimiento SQLite & Regresión Global | EXPLAIN QUERY PLAN + Regresión Total | ✅ **Completado & Verificado** | 141 / 141 OK | [subfase-3.6.5-rendimiento-regresion.md](../testing/subfase-3.6.5-rendimiento-regresion.md) |

---

## 2. Alcance Detallado por Sub-subfase

### Subfase 3.6.1: Acceso, Autorización, IDOR & Blindaje RBAC (OWASP A01:2021)
- **Control de Acceso Vertical (RBAC):**
  - Validación de respuesta `401 Unauthorized` ante peticiones anónimas o con tokens inválidos a rutas administrativas.
  - Bloqueo con `403 Forbidden` al rol `asistente` en operaciones mutacionales (creación, edición, eliminación de piezas y pedidos) y gestión de usuarios.
  - Bloqueo con `403 Forbidden` al rol `artesano` en endpoints exclusivos de administración (`/api/usuarios/*`).
- **Prevención IDOR Horizontal en Creaciones:**
  - Verificación de que un artesano no puede actualizar, dar de baja lógica, restaurar, ajustar stock ni alternar encargo en piezas que pertenecen a otro artesano (`403 Forbidden`).
  - Verificación de que el Administrador (`admin`) cuenta con facultades de moderación global sobre cualquier pieza.
- **Prevención IDOR Horizontal en Pedidos:**
  - Un artesano no puede consultar el detalle, modificar el estado ni cancelar pedidos correspondientes a creaciones de otro artesano (`403 Forbidden`).
  - Un artesano no puede registrar encargos manuales (`/api/pedidos/crear.php`) vinculados a creaciones de otros artesanos (`403 Forbidden`).
  - Aislamiento estricto en el listado de pedidos: cada artesano solo visualiza los pedidos de sus creaciones.
- **Salvaguardas Inmutables de Cuenta Raíz (ID #1) y Auto-eliminación:**
  - La cuenta del administrador titular (`@admin`, ID #1) no puede ser degradada de rol ni desactivada/eliminada (`403 Forbidden`).
  - Ningún usuario puede auto-eliminarse mientras se encuentra en sesión activa (`403 Forbidden`).
- **Aislamiento de Recursos Inactivos:**
  - Consulta pública de creaciones dadas de baja lógica (`activo = 0`) responde con `404 Not Found`.
- **Restricción Estricta de Métodos HTTP:**
  - Verificación de respuesta uniforme `405 Method Not Allowed` ante métodos HTTP no permitidos en los 25 controladores REST del sistema.

### Subfase 3.6.2: Criptografía, Autenticación & Protección de Datos Sensibles (OWASP A02:2021 + A07:2021)
- **Integridad Criptográfica de Tokens Bearer HMAC-SHA256:**
  - Firma HMAC recalculada y comparada en tiempo constante mediante `hash_equals()`.
  - Detección y rechazo inmediato (`null` / `HTTP 401`) ante payload adulterado, firma corrompida, firma generada con clave secreta errónea y formatos malformados.
- **Ciclo de Vida y Expiración Estricta de Tokens:**
  - Adherencia al TTL configurado (86400 segundos / 24 horas).
  - Rechazo instantáneo con `HTTP 401 Unauthorized` ante tokens cuya estampa temporal `exp` pertenezca al pasado.
- **Almacenamiento Criptográfico Bcrypt & Mitigación Timing Attack:**
  - Almacenamiento exclusivo de hashes Bcrypt con cost factor 10 (`$2y$10$...`) y longitud canónica de 60 caracteres.
  - CERO almacenamiento de contraseñas en texto plano.
  - Mitigación de timing attack en `AuthService::authenticate`: ejecución de `password_verify` contra un dummy hash cuando el usuario no existe, impidiendo enumeración de cuentas.
  - Mensajes de error no enumerables: fallo por usuario inexistente y fallo por contraseña incorrecta devuelven el mismo error genérico (`Credenciales de acceso incorrectas.`) con `HTTP 401`.
- **Cero Exposición de Datos Sensibles:**
  - Exclusión garantizada de campos `password_hash`, `password` o claves secretas en `login.php`, `me.php`, `usuarios/index.php` y en las consultas de persistencia `findByIdSafe()`, `listAll()` y `listAllWithCreationsCount()`.
  - Guardia de acceso directo en `app/config.php` con bloqueo `HTTP 403`.
- **Políticas de Higiene de Contraseñas:**
  - Exigencia de longitud mínima de 6 caracteres en creación de cuentas, cambio de contraseña por autoservicio y reseteo administrativo (`HTTP 422`).
  - Autoservicio de cambio de clave exige verificación estricta de la contraseña actual (`HTTP 401`).
  - Reseteo administrativo autogenera contraseñas temporales seguras bajo el patrón criptográfico `Crochet!<6-hex-chars>!`.
- **Revocación Inmediata de Tokens en Bajas Lógicas:**
  - La desactivación de una cuenta (`activo = 0`) provoca que cualquier Bearer token emitido previamente quede revocado de forma instantánea en `AuthService::validateToken()`, respondiendo con `HTTP 401` sin esperar a que transcurran las 24 horas de TTL.
  - Reactivación formal de cuenta mediante `/api/usuarios/reactivar.php` y restitución del acceso.
- **Ciclo de Cierre de Sesión (Logout):**
  - Endpoint `POST /api/auth/logout.php` confirma el cierre de sesión con código `200 OK` e instruye al cliente a purgar el token de almacenamiento local.

### Subfase 3.6.3: Inyección, Sanitización & Seguridad de Medios/Archivos (OWASP A03:2021 + A08:2021)
- **Blindaje Total Contra Inyección SQL (SQLi):**
  - 100% de consultas preparadas PDO parametrizadas en `app/Repositories/`. Cero interpolación directa de variables.
  - Inyección de vectores de prueba SQL clásicos (`' OR '1'='1`, `UNION SELECT`, `; DROP TABLE`, comillas y caracteres nulos `%00`) en parámetros de búsqueda, filtros de categoría y campos de texto.
- **Defensa en Profundidad Contra Cross-Site Scripting (XSS):**
  - Sanitización en entrada (`trim`, longitud máxima) y escape seguro en capa de presentación con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
  - Pruebas con payloads maliciosos (`<script>alert(1)</script>`, `"><img src=x onerror=...>`, URLs `javascript:`) en nombres, descripciones y notas.
- **Seguridad en Carga de Medios & Detección MIME Real:**
  - Validación de tipo de archivo mediante inspección binaria (`finfo_file` / `mime_content_type`), restringida a `image/jpeg`, `image/png` y `image/webp`.
  - Bloqueo de archivos con doble extensión o camuflados (ej. `malware.php.jpg`, scripts ejecutables con cabeceras de imagen falsas).
  - Límite de tamaño estricto ($\le 5\text{MB}$).
- **Prevención de Path Traversal & Carga Arbitraria:**
  - Nombres de archivo generados criptográficamente en el servidor (`uniqid('creacion_', true)`).
  - Inmunidad contra secuencias de escape de directorio (`../../`, `..\\`).
- **Seguridad Vectorial SVG:**
  - Verificación de que los archivos SVG servidos por `SvgHelper` no contengan scripts embebidos, eventos `onload` ni entidades externas XXE.

### Subfase 3.6.4: Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)
- **Cálculo y Congelamiento de Precios en el Servidor:**
  - El cliente jamás envía el precio final del pedido; el servidor calcula y congela `precio_final = creacion.precio * cantidad`.
  - Intentos de manipulación de precio en el payload JSON son ignorados por completo.
- **Aislamiento de Concurrencia & Stock Atómico:**
  - Operaciones de reserva y deducción de existencias ejecutadas bajo `BEGIN IMMEDIATE TRANSACTION`.
  - Bloqueo inmediato con `HTTP 409 Conflict` cuando la cantidad solicitada excede el stock físico disponible.
  - Salvaguarda para piezas por encargo (`es_sobre_encargo = 1`): no descuentan existencias físicas pero registran el encargo.
- **Cancelación Idempotente & Restitución Transaccional:**
  - Cancelación de pedidos restituye exactamente las unidades reservadas a `creaciones.cantidad_stock`.
  - Idempotencia estricta: re-intentos de cancelación sobre un pedido ya cancelado responden con `HTTP 409 Conflict`, impidiendo duplicación ilegítima de inventario.
- **Resiliencia UTF-8 4-Byte & Caracteres Multibyte:**
  - Validación de inserción, lectura y búsqueda con caracteres especiales, acentos, diéresis y emojis de 4 bytes (🧶, 🧸, ✨, 🌸, 🐉) en SQLite y respuestas JSON sin romper la codificación ni truncar texto.

### Subfase 3.6.5: Rendimiento SQLite, Arquitectura Limpia & Regresión Global Acumulada
- **Auditoría de Consultas con `EXPLAIN QUERY PLAN`:**
  - Verificación de uso de índices de cobertura (`idx_creaciones_artesano`, `idx_creaciones_categoria`, `idx_creaciones_stock`, `idx_creaciones_activo`, `idx_pedidos_creacion`, `idx_pedidos_estado`, `idx_pedidos_activo`, `idx_usuarios_activo`).
  - Erradicación de escaneos de tabla completa (`SCAN TABLE`) en consultas críticas de catálogo y pedidos.
- **Erradicación de Patrones N+1:**
  - Consultas combinadas con `LEFT JOIN` para recuperar artesanos creadores o conteos de creaciones en una sola consulta estructurada.
- **Concurrencia SQLite & `PRAGMA busy_timeout`:**
  - Validación de tolerancia a escrituras concurrentes gracias al valor configurado de `busy_timeout = 5000` (5 segundos).
- **Auditoría de Arquitectura Limpia & SOLID:**
  - Verificación de cero código PHP en `src/` (100% frontend).
  - Verificación de cero sentencias SQL en controladores y servicios (100% aislado en repositorios).
  - Verificación de controladores delgados (menos de 60 líneas por archivo en `api/`).
- **Suite de Regresión Global Acumulada:**
  - Ejecución consecutiva y automatizada de todas las suites previas (`test-subfase-3.1.php` a `test-subfase-3.6.4.php`), garantizando 100% de aserciones en verde y cero efectos colaterales.

---

## 3. Protocolo Inviolable de Compuertas de Testing (Testing Gates)

Para cada una de las 5 sub-subfases de la 3.6, se ejecuta rigurosamente el siguiente ciclo:

1. **Implementación Quirúrgica:** Escritura y ajuste de clases o suites de prueba de la sub-subfase activa.
2. **Suite CLI Nativa:** Ejecución de `php tests/test-subfase-3.6.X.php > logs/subfase-3.6.X-cli.log 2>&1`.
3. **Trazas HTTP en Vivo:** Captura de peticiones curl contra el servidor local en `logs/subfase-3.6.X-http.log`.
4. **Reporte Ejecutivo Formal:** Redacción de `docs/testing/subfase-3.6.X-[nombre].md` con matriz de aserciones, evidencias JSON y estado de base de datos.
5. **Sincronización de Memoria:** Actualización inmediata de `memory-bank/activeContext.md` y `memory-bank/progress.md`.
6. **Compás de Espera Inviolable (*Halt*):** Detener toda ejecución de herramientas y aguardar la instrucción explícita y por escrito del usuario antes de avanzar a la siguiente sub-subfase. Queda estrictamente prohibido agrupar o adelantar subfases.
