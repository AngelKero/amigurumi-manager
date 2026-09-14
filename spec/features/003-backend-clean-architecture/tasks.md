# 003 · Backend Clean Architecture, API REST & Blindaje — Tareas

- [x] Implementar autocargador PSR-4 nativo `app/autoload.php` y configuración centralizada `app/config.php`.
- [x] Crear clases nucleares de infraestructura: `Database.php`, `Config.php`, `ErrorHandler.php`, `Request.php`, `Response.php`, `TokenManager.php`.
- [x] Desarrollar capa de persistencia: `UsuarioRepository.php`, `CreacionRepository.php`, `PedidoRepository.php`.
- [x] Desarrollar capa de servicios: `AuthService.php`, `UsuarioService.php`, `CreacionService.php`, `PedidoService.php`.
- [x] Desarrollar middleware de seguridad: `AuthGuard.php`, `RoleGuard.php`.
- [x] Construir 25 controladores REST delgados en `api/auth/`, `api/creaciones/`, `api/pedidos/`, `api/usuarios/`.
- [x] Implementar helpers de servidor: `CurrencyHelper.php`, `PaginationHelper.php`, `SvgHelper.php`.
- [x] Ejecutar e integrar suites de pruebas CLI para Subfases 3.1 a 3.5 (532 aserciones aprobadas).
- [x] Ejecutar e integrar suites de auditoría de seguridad Subfases 3.6.1 a 3.6.5 (775 aserciones aprobadas).
- [x] Verificar que el 100% de consultas utilicen índices (`EXPLAIN QUERY PLAN` con 0 `SCAN TABLE`).
- [x] Certificar la suite de regresión acumulada con 1,307/1,307 aserciones en verde.
- [x] Validar contra los criterios de aceptación de `spec.md`.
- [x] Mover la feature a "Hecho" en `../../constitution/roadmap.md`.
