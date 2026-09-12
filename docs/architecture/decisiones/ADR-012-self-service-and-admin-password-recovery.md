# ADR-012: Recuperación de Contraseñas: Autoservicio y Reseteo Administrativo

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
El sistema necesitaba resolver dos escenarios distintos de actualización de contraseñas:
1. El usuario autenticado que conoce su clave y desea cambiarla por seguridad (autoservicio).
2. El artesano que olvidó su clave o perdió el acceso por completo (recuperación administrativa). Al no contar con servicio de correo SMTP configurado, se requería una entrega segura fuera de banda.

## Decisión
1. **Autoservicio (`POST /api/auth/cambiar-password.php`):** Disponible para cualquier usuario autenticado; exige la contraseña actual (`password_actual`) y valida que la nueva tenga al menos 6 caracteres.
2. **Reseteo Administrativo (`POST /api/usuarios/restablecer-password.php`):** Restringido a administradores; permite especificar una contraseña manual o autogenerar una clave temporal segura con el formato `Crochet!<hex>!` (ej. `Crochet!4d8ec3!`) que se devuelve en la respuesta JSON para que el administrador la comparta directamente por WhatsApp con el artesano.

## Alternativas Consideradas
- **Tokens de recuperación por email (magic links):** Requiere servidor SMTP y dependencias de correo que no forman parte del alcance del Micro-ERP.

## Consecuencias
- Cobertura completa tanto para el autoservicio como para la pérdida de credenciales, alineada con el canal de WhatsApp utilizado por la comunidad de artesanos.

---

[← Anterior (ADR-011)](./ADR-011-three-tier-testing-architecture.md) • [Índice de ADRs](./README.md) • [Siguiente (ADR-013) →](./ADR-013-user-reactivation-and-filtering.md)
