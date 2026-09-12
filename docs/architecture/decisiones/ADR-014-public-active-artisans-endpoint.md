# ADR-014: Endpoint Público Ligero de Artesanos para Filtrado en Catálogo

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
El catálogo público (`index.php`) cuenta con un selector de filtrado por artesano (`#filterArtisan`). Sin embargo, el endpoint general `/api/usuarios/index.php` está protegido con `RoleGuard::adminOnly()` y devuelve datos privados (roles, estado, paginación). Se requería una forma limpia de popular el dropdown sin exponer datos de administración ni debilitar los permisos de la gestión de usuarios.

## Decisión
Implementar un endpoint público dedicado: `GET /api/creaciones/artesanos.php`:
- Ejecuta una consulta optimizada:
  ```sql
  SELECT DISTINCT u.id, u.username, COUNT(c.id) AS total_creaciones
  FROM usuarios u
  INNER JOIN creaciones c ON c.artesano_id = u.id
  WHERE u.activo = 1 AND c.activo = 1
  GROUP BY u.id, u.username
  ORDER BY u.username ASC;
  ```
- Devuelve únicamente `{ id, username, total_creaciones }`.
- Es 100% público (no requiere Bearer token) y solo incluye artesanos que efectivamente tienen piezas activas a la venta.

## Alternativas Consideradas
- **Permitir acceso público a `/api/usuarios/index.php` con flags:** Viola el principio de menor privilegio y arriesga fugas de información de usuarios que no son artesanos (ej. asistentes).
- **Extraer la lista de artesanos del payload del catálogo:** Ineficiente; si el catálogo está paginado a 12 ítems, solo se obtendrían los artesanos de la página actual.

## Consecuencias
- El frontend público puede poblar `#filterArtisan` de forma autónoma e inmediata.
- La gestión de usuarios en `/api/usuarios/` se mantiene 100% restringida a administradores.

---

[← Anterior (ADR-013)](./ADR-013-user-reactivation-and-filtering.md) • [Índice de ADRs](./README.md) • [Siguiente (ADR-015) →](./ADR-015-creation-restoration-lifecycle.md)
