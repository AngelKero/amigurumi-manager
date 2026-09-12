# Registro de Decisiones de Arquitectura (ADRs)

[← Volver al Índice de Arquitectura](../README.md) • [Hub Principal](../../README.md)

Este directorio contiene los **Architecture Decision Records (ADRs)** de Crochet Manager, registrando el contexto, las alternativas evaluadas y las consecuencias de cada decisión técnica fundamental del sistema.

---

## Índice Maestro de Decisiones

| ADR | Título | Estado | Fecha | Categoría |
| :---: | :--- | :---: | :---: | :--- |
| **[ADR-001](./ADR-001-clean-architecture-php-sqlite.md)** | Clean Architecture en `app/` (PHP Nativo + SQLite sin Composer) | Aceptada | 2026-09-11 | Arquitectura Core |
| **[ADR-002](./ADR-002-stateless-hmac-bearer-tokens.md)** | Autenticación Stateless con Bearer Tokens HMAC-SHA256 (24h TTL) | Aceptada | 2026-09-11 | Seguridad / Auth |
| **[ADR-003](./ADR-003-zero-html-error-leaks.md)** | Cero Fugas de Error HTML y Captura Global con Salida JSON 500 | Aceptada | 2026-09-11 | Resiliencia |
| **[ADR-004](./ADR-004-universal-soft-delete.md)** | Regla Universal de Borrado Lógico (Cero Eliminaciones Físicas) | Aceptada | 2026-09-12 | Integridad de Datos |
| **[ADR-005](./ADR-005-exact-integer-cents-currency.md)** | Manejo de Moneda en Centavos Enteros y Enriquecimiento Dual | Aceptada | 2026-09-11 | Dominio / Finanzas |
| **[ADR-006](./ADR-006-sqlite-busy-timeout-concurrency.md)** | Mitigación de Concurrencia SQLite mediante `busy_timeout = 5000` | Aceptada | 2026-09-12 | Base de Datos |
| **[ADR-007](./ADR-007-multi-artisan-idor-authorization.md)** | Autorización Multi-Artesano y Prevención Estricta de IDOR | Aceptada | 2026-09-12 | Seguridad / RBAC |
| **[ADR-008](./ADR-008-image-lifecycle-preservation.md)** | Preservación de Fotografías en Disco ante Baja Lógica de Creaciones | Aceptada | 2026-09-12 | Almacenamiento |
| **[ADR-009](./ADR-009-idempotent-order-cancellation.md)** | Cancelación Idempotente de Pedidos con Restitución de Stock | Aceptada | 2026-09-12 | Transacciones |
| **[ADR-010](./ADR-010-root-admin-id1-lockout.md)** | Salvaguarda Inviolable de la Cuenta del Administrador Titular (ID #1) | Aceptada | 2026-09-11 | Gobernanza RBAC |
| **[ADR-011](./ADR-011-three-tier-testing-architecture.md)** | Arquitectura de Pruebas de 3 Niveles y Compuertas Secuenciales | Aceptada | 2026-09-11 | Calidad / QA |
| **[ADR-012](./ADR-012-self-service-and-admin-password-recovery.md)** | Recuperación de Contraseñas: Autoservicio y Reseteo Administrativo | Aceptada | 2026-09-12 | Seguridad / Auth |
| **[ADR-013](./ADR-013-user-reactivation-and-filtering.md)** | Reactivación Lógica de Creadores y Filtrado por Estado | Aceptada | 2026-09-12 | Gestión de Usuarios |
| **[ADR-014](./ADR-014-public-active-artisans-endpoint.md)** | Endpoint Público Ligero de Artesanos para Filtrado en Catálogo | Aceptada | 2026-09-12 | API / Catálogo |
| **[ADR-015](./ADR-015-creation-restoration-lifecycle.md)** | Restauración Lógica de Creaciones Inactivadas | Aceptada | 2026-09-12 | Catálogo |
