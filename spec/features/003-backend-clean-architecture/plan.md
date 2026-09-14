# 003 · Backend Clean Architecture, API REST & Blindaje — Plan

## Enfoque

Estructurar el backend conforme a Clean Architecture y la regla de dependencias: el dominio y la persistencia no dependen de HTTP. Desarrollar en 6 subfases incrementales (3.1 a 3.6.5) respaldadas por un protocolo de pruebas en 3 niveles (CLI, raw logs, reportes en `docs/testing/`), culminando con una auditoría integral OWASP y regresión acumulada.

## Implementación

1. **Subfase 3.1: Infraestructura Nuclear (`app/Core/`, `app/Utils/`)**:
   - `autoload.php`, `config.php`, `Config.php`, `Database.php`, `ErrorHandler.php`, `Request.php`, `Response.php`, `TokenManager.php`.
   - Helpers: `CurrencyHelper.php`, `PaginationHelper.php`, `SvgHelper.php`.
2. **Subfase 3.2: Autenticación Stateless & Middleware (`app/Middleware/`, `api/auth/`)**:
   - `AuthService.php`, `UsuarioRepository.php` (búsquedas seguras), `AuthGuard.php`, `RoleGuard.php`.
   - Controladores: `login.php`, `logout.php`, `me.php`, `cambiar-password.php`.
3. **Subfase 3.3: Gestión de Usuarios & Borrado Lógico (`app/Services/UsuarioService.php`, `api/usuarios/`)**:
   - CRUD de usuarios, actualización de roles, reseteo de claves y reactivación formal.
4. **Subfase 3.4: Catálogo, Creaciones & Medios (`app/Services/CreacionService.php`, `api/creaciones/`)**:
   - Listados paginados, filtros multicriterio, mutaciones con IDOR, subida segura de fotos y fallback SVG.
5. **Subfase 3.5: Pedidos, Transacciones Atómicas & WhatsApp (`app/Services/PedidoService.php`, `api/pedidos/`)**:
   - Checkout público, reserva atómica de existencias, cálculo de precio en servidor y enlaces a WhatsApp.
6. **Subfase 3.6: Auditoría Integral OWASP & Regresión (5 sub-subfases)**:
   - 3.6.1: Acceso, Autorización, IDOR & RBAC (165 aserciones).
   - 3.6.2: Criptografía, Bcrypt, HMAC & Datos Sensibles (161 aserciones).
   - 3.6.3: Inyección SQL, XSS, Medios & Sanitización (157 aserciones).
   - 3.6.4: Lógica de Negocio, Precios, Stock & UTF-8 (151 aserciones).
   - 3.6.5: Rendimiento SQLite (`EXPLAIN QUERY PLAN`), Concurrencia & Regresión Global (141 aserciones).

## Decisiones

- **Autocargador PSR-4 nativo sin Composer:** Mantiene el proyecto ligero, portable y sin dependencias externas pesadas ni carpetas `vendor/`.
- **HMAC-SHA256 Bearer Tokens:** Autenticación sin estado ideal para REST APIs, eliminando problemas de fijación de sesiones o cookies en APIs móviles/desacopladas.
- **Transacciones `BEGIN IMMEDIATE` con `busy_timeout`:** Garantiza la serialización de escrituras concurrentes sin incurrir en lecturas sucias ni doble reserva de inventario.

## Riesgos

- **Fugas de información en errores:** Mitigado con `ErrorHandler` que captura toda excepción y emite únicamente JSON 500 estándar.
- **Colisión de transacciones SQLite:** Mitigado configurando `PRAGMA busy_timeout = 5000;` en el Singleton de base de datos.
