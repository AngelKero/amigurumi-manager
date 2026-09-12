# ADR-001: Clean Architecture en `app/` (PHP Nativo + SQLite sin Composer)

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-11

## Contexto
El proyecto requiere una arquitectura de servidor desacoplada, mantenible y robusta para gestionar la lógica de inventario, costos y pedidos de la plataforma. La restricción del proyecto exige no utilizar dependencias externas pesadas ni Composer, manteniendo el despliegue mediante PHP embebido estándar (`php -S localhost:8000`).

## Decisión
Se aísla el 100% del backend PHP en un directorio dedicado `app/`, estructurado bajo principios de Clean Architecture:
- `app/autoload.php`: Autocargador PSR-4 nativo sin Composer (`App\` mapeado a `app/`).
- `app/Core/`: Infraestructura técnica (Base de datos PDO, configuración, tokens, respuestas).
- `app/Repositories/`: Capa de persistencia con 100% de consultas preparadas PDO aisladas.
- `app/Services/`: Casos de uso y reglas de negocio del dominio.
- `app/Middleware/`: Guardias de autenticación y autorización RBAC.
- `app/Utils/`: Helpers de cálculo monetario, paginación y renderizado vectorial.
- `api/`: Controladores REST delgados organizados en subcarpetas temáticas.
- `src/`: Queda 100% reservado al frontend (`src/css/`, `src/js/`) con **0 archivos PHP**.

## Alternativas Consideradas
- **Monolito de scripts PHP en raíz:** Dificulta el testing unitario, mezcla HTML con SQL y viola el principio de responsabilidad única.
- **Framework externo (Laravel / Slim vía Composer):** Rechazado por restricciones de portabilidad académica y peso innecesario.

## Consecuencias
- Código modular con separación estricta de responsabilidades (SRP).
- Facilita pruebas automatizadas CLI sin necesidad de servidor HTTP completo.
- Dependencias apuntan únicamente hacia adentro (Dependency Inversion).

---

[Índice de ADRs](./README.md) • [Siguiente (ADR-002) →](./ADR-002-stateless-hmac-bearer-tokens.md)
