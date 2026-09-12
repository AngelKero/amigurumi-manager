# ADR-011: Arquitectura de Pruebas de 3 Niveles y Compuertas Secuenciales

[← Volver al Índice de ADRs](./README.md) • [Arquitectura](../README.md) • [Hub Principal](../../README.md)

---

## Estado
Aceptada

## Fecha
2026-09-11

## Contexto
El desarrollo asistido por IA tiende a cometer dos errores habituales: bundling descontrolado (generar varias fases a la vez sin probar) y falsas afirmaciones de éxito basadas en memoria en lugar de ejecuciones reales. Se requería un protocolo de verificación riguroso, trazable y reproducible.

## Decisión
Se establece una arquitectura de pruebas de 3 niveles con compuertas secuenciales obligatorias:
1. **Nivel 1 — Reportes Ejecutivos (`docs/testing/`):** Documentos Markdown legibles para humanos versionados en Git (`subfase-3.X-[nombre].md`), con matrices de aserciones, evidencias JSON y verificación de SQLite.
2. **Nivel 2 — Suites Automatizadas CLI (`tests/`):** Scripts PHP nativos sin dependencias (`test-subfase-3.X.php`) que ejecutan aserciones unitarias y peticiones HTTP en vivo contra `localhost:8000`. Bloqueados en web por `.htaccess`.
3. **Nivel 3 — Logs Crudos y Trazas (`logs/`):** Volcados completos de salida de consola (`logs/subfase-3.X-cli.log`) y respuestas HTTP (`logs/subfase-3.X-http.log`), excluidos de Git por `.gitignore` y bloqueados por `.htaccess`.

**Regla de Compuerta Inviolable:** Al concluir cada subfase, el asistente de IA debe ejecutar las pruebas, redactar el reporte, sincronizar el Memory Bank y **detenerse completamente a esperar la aprobación explícita del usuario** antes de escribir código para la siguiente subfase.

## Alternativas Consideradas
- **PHPUnit vía Composer:** Requiere Composer e infraestructura externa; la suite nativa en `tests/TestHelper.php` ofrece aserciones exactas en milisegundos con 0 dependencias.

## Consecuencias
- 100% de trazabilidad entre requisitos, código y pruebas en vivo.
- Control total del usuario sobre el avance del proyecto sin saltos inesperados.

---

[← Anterior (ADR-010)](./ADR-010-root-admin-id1-lockout.md) • [Índice de ADRs](./README.md) • [Siguiente (ADR-012) →](./ADR-012-self-service-and-admin-password-recovery.md)
