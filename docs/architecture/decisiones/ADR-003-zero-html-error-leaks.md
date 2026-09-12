# ADR-003: Cero Fugas de Error HTML y Captura Global con Salida JSON 500

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-11

## Contexto
Por defecto, errores fatales, advertencias o excepciones no capturadas en PHP imprimen texto plano o fragmentos HTML en la salida estándar, rompiendo los parsers JSON del cliente (`JSON.parse()` SyntaxError) y potencialmente filtrando rutas de archivos internas o credenciales en producción.

## Decisión
Implementar `App\Core\ErrorHandler` registrado globalmente en `app/autoload.php`:
- `set_error_handler`: Convierte advertencias y notices en `ErrorException`.
- `set_exception_handler`: Intercepta cualquier excepción no manejada.
- `register_shutdown_function`: Captura errores de compilación y fatales que impiden la ejecución normal.
- Limpieza de buffers de salida con `ob_end_clean()` para purgar cualquier salida previa.
- Emisión forzada de cabecera `Content-Type: application/json; charset=utf-8` y código HTTP 500 con cuerpo JSON estándar.

## Alternativas Consideradas
- **Configuración básica de `php.ini` (`display_errors = Off`):** Solo silencia errores pero no emite una respuesta JSON estructurada al cliente web.

## Consecuencias
- 100% de garantía de respuestas JSON válidas ante cualquier fallo del sistema.
- Cero fugas de información interna en respuestas web.

---

[← Anterior (ADR-002)](./ADR-002-stateless-hmac-bearer-tokens.md) • [Índice de ADRs](./README.md) • [Siguiente (ADR-004) →](./ADR-004-universal-soft-delete.md)
