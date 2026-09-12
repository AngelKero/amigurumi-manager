# ADR-006: Mitigación de Concurrencia SQLite mediante `busy_timeout = 5000`

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
En SQLite, operaciones de escritura simultáneas (como creación de pedidos o ajuste de stock) pueden provocar errores inmediatos `SQLITE_BUSY: database is locked` si el timeout por defecto es 0 milisegundos.

## Decisión
Se configura obligatoriamente `PRAGMA busy_timeout = 5000;` en `App\Core\Database.php` inmediatamente después de abrir la conexión PDO, tanto en `getInstance()` como en `setInstance()`.

## Alternativas Consideradas
- **Modo WAL (`PRAGMA journal_mode = WAL;`):** Permite lecturas concurrentes con escrituras, pero puede presentar problemas de archivos auxiliares (`-wal`, `-shm`) en entornos con permisos de directorio restringidos o sistemas de archivos montados por red. El timeout de 5000ms resuelve el problema sin alterar el journal estándar.

## Consecuencias
- SQLite espera activamente hasta 5 segundos a que se libere el bloqueo de archivo antes de arrojar una excepción.
- Erradica los fallos intermitentes de bloqueo bajo peticiones simultáneas de checkout o pruebas en vivo.
