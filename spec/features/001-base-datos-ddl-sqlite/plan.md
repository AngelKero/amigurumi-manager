# 001 · Base de Datos, DDL & Concurrencia SQLite — Plan

## Enfoque

Implementar un esquema SQL canónico en un único archivo de definición y semillas (`database/seed.sql`) y orquestar su ejecución reproducible mediante un script CLI en PHP (`setup.php`). Utilizar PDO con el driver `sqlite` asegurando que las directivas de integridad y concurrencia se apliquen desde la primera conexión.

## Implementación

1. **`database/seed.sql`**:
   - Declaración explícita de `PRAGMA foreign_keys = ON;`.
   - DDL para `usuarios`, `creaciones` y `pedidos` con restricciones CHECK y FKs.
   - Creación de 9 índices relacionales para erradicar escaneos completos de tabla.
   - Inserción de datos semilla realistas (usuarios administrador/artesano, piezas de muestra y pedidos históricos).
2. **`setup.php`**:
   - Guardia de seguridad `php_sapi_name() === 'cli'`.
   - Creación del directorio `database/` y conexión PDO con `ERRMODE_EXCEPTION`.
   - Ejecución transaccional del archivo `seed.sql`.
   - Reporte de verificación en consola.
3. **`.htaccess`**:
   - Bloqueo de acceso HTTP directo a archivos `.sqlite`, `.sqlite3` y `.sql` con HTTP 403 Forbidden.

## Decisiones

- **SQLite 3 embebido en archivo físico:** Simplicidad operacional, cero configuración externa y portabilidad total para el Micro-ERP artesanal.
- **Centavos enteros (`INTEGER`) vs Float (`REAL`):** Erradica por diseño errores de precisión aritmética de coma flotante (ADR-005).
- **Borrado lógico universal sin `DELETE FROM`:** Asegura que los registros históricos y comprobantes no rompan relaciones ni pierdan contexto (ADR-008).

## Riesgos

- **Bloqueo concurrente (`SQLITE_BUSY`):** Se mitiga configurando `PRAGMA busy_timeout = 5000;` en todas las conexiones PDO.
- **Acceso web no autorizado a la base física:** Se mitiga aislando la base en `database/database.sqlite` protegida por directivas `.htaccess`.
