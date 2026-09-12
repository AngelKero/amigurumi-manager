# ADR-010: Salvaguarda Inviolable de la Cuenta del Administrador Titular (ID #1)

## Estado
Aceptada

## Fecha
2026-09-11

## Contexto
En sistemas con múltiples administradores o paneles de gestión de usuarios, existe el peligro de que una acción errónea o maliciosa degrade los privilegios del administrador principal o lo elimine de la base de datos, provocando un bloqueo irreversible (lockout) del sistema.

## Decisión
Se implementa una salvaguarda de doble capa (persistencia y servicio) para el usuario con `id = 1`:
1. `UsuarioRepository::updateRole()`: Si `$id === 1`, la operación retorna `false` inmediatamente sin ejecutar SQL.
2. `UsuarioRepository::delete()`: Si `$id === 1`, la operación retorna `false` inmediatamente sin ejecutar SQL.
3. `UsuarioService::updateRole()` y `deleteUser()`: Si el target es ID #1, arroja una `RuntimeException('Operación denegada...', 403)`.
4. La interfaz de usuario (`usuarios.php`) deshabilita visualmente los controles de edición para la fila del administrador titular.

## Alternativas Consideradas
- **Proteger únicamente en la interfaz visual:** Inseguro; cualquier petición curl a la API podría despojar al administrador de sus privilegios.

## Consecuencias
- La gobernanza de la plataforma queda permanentemente asegurada contra bloqueos accidentales.
