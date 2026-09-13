# Arquitectura del Sistema & Decisiones de Diseño

[← Volver al Hub Principal de Documentación](../README.md)

Este directorio documenta la arquitectura técnica del backend de **Crochet Manager**, su diseño en capas bajo principios de **Clean Architecture & SOLID**, y el registro histórico de decisiones arquitectónicas (**ADRs**).

---

## 1. Documentos del Módulo

| Documento | Descripción |
| :--- | :--- |
| **[Proceso de Todas las Fases (1 a 5)](./proceso-desarrollo-fases.md)** | **Ciclo de vida integral del proyecto**: objetivos, metodologías, entregables y compuertas de las Fases 0 a 5. |
| **[Plan Maestro de la Fase 3](./phase-3-plan.md)** | Hoja de ruta secuencial de las 6 subfases del backend (3.1 a 3.6), estado y compuertas de testing. |
| **[Especificación Subfase 3.6 (Seguridad OWASP)](./subfase-3.6-auditoria-seguridad.md)** | **Auditoría Integral de Seguridad**: Desglose y especificación de las 5 sub-subfases (OWASP Top 10, SQLite y regresión). |
| **[Contratos de Clases & Capas](./contracts.md)** | Responsabilidades y firmas de métodos para Repositorios, Servicios, Middleware y Helpers. |
| **[Seguridad, Auth & RBAC](./security.md)** | Ciclo de vida de tokens Bearer HMAC-SHA256, matriz RBAC, protección IDOR y blindaje Apache. |
| **[Registro de Decisiones (ADRs)](./decisiones/README.md)** | Índice con los 15 Architecture Decision Records individuales y numerados del proyecto. |

---

## 2. Principios de Clean Architecture

El backend reside exclusivamente en el directorio `app/`. El directorio `src/` queda 100% reservado a frontend (`src/css/`, `src/js/`) con **0 archivos PHP**.

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Delivery / Web Layer: api/ (Controladores REST delgados) │
├─────────────────────────────────────────────────────────────┤
│ 2. Security & Gateways: app/Middleware/ (AuthGuard, Roles)  │
├─────────────────────────────────────────────────────────────┤
│ 3. Use Cases / Business Logic: app/Services/               │
├─────────────────────────────────────────────────────────────┤
│ 4. Persistence Layer: app/Repositories/ (100% SQL aislado)  │
├─────────────────────────────────────────────────────────────┤
│ 5. Infrastructure Core: app/Core/ (DB, Tokens, Errors, Req) │
└─────────────────────────────────────────────────────────────┘
```

### Reglas Inviolables de Capas:
1. **Regla de Dependencia:** Las dependencias apuntan exclusivamente hacia adentro. Las entidades y reglas de negocio en `app/Services/` no dependen de formatos HTTP (`Request`, `Response`).
2. **Aislamiento de SQL:** Ningún controlador ni servicio ejecuta sentencias SQL. El 100% de consultas preparadas PDO reside en `app/Repositories/`.
3. **Controladores Delgados:** Los archivos en `api/` se limitan a:
   - Validar parámetros de entrada mediante `Request`.
   - Delegar la lógica de negocio a un Servicio.
   - Emitir la respuesta estructurada mediante `Response::success()` o `Response::error()`.
4. **Cero Fugas HTML:** `ErrorHandler` captura cualquier anomalía y emite JSON estándar.
