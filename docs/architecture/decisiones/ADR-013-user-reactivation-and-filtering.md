# ADR-013: Reactivación Lógica de Creadores y Filtrado por Estado

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
Debido a la restricción `uq_usuarios_username UNIQUE(username)` en SQLite, cuando un usuario es dado de baja lógica (`activo = 0`), su nombre de usuario continúa existiendo físicamente en la tabla. Si el creador regresa a la plataforma, el administrador no podría registrarlo con el mismo username (conflicto 409) si no existiera un mecanismo formal de reactivación.

## Decisión
1. Implementar `POST /api/usuarios/reactivar.php` y `UsuarioService::reactivateUser()`:
   - Valida que la cuenta exista (HTTP 404).
   - Valida que la cuenta esté inactiva (`activo = 0`), emitiendo `HTTP 409 Conflict` si ya estaba activa.
   - Restablece `activo = 1` y `eliminado_en = NULL`.
2. Actualizar el listado en `GET /api/usuarios/index.php` con el parámetro `?estado=activos|inactivos|todos` (defecto: `activos`), permitiendo al administrador auditar cuentas dadas de baja y reactivarlas desde la interfaz.

## Alternativas Consideradas
- **Modificar el username de los inactivos (ej. `ana_deleted_123`):** Corrompe el nombre histórico y genera ambigüedad.

## Consecuencias
- Ciclo de vida completo para cuentas de usuario (alta $\rightarrow$ baja lógica $\rightarrow$ reactivación).
- Preservación limpia del username original y su historial.
