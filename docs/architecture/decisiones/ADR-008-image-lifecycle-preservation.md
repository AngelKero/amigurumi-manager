# ADR-008: Preservación de Fotografías en Disco ante Baja Lógica de Creaciones

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
Originalmente se contemplaba ejecutar `unlink()` sobre el archivo de imagen en disco al eliminar una creación. Sin embargo, al adoptarse la regla universal de borrado lógico, los pedidos históricos continúan referenciando a la pieza eliminada (`creaciones.id`). Si la imagen se borrara del disco, la vista de pedidos históricos (`pedidos.php`) renderizaría imágenes rotas (error 404).

## Decisión
1. **Regla Inviolable de Borrado:** Al dar de baja lógica una creación (`deleteCreation`), **NUNCA se invoca `unlink()`** sobre el archivo de imagen. La fotografía física permanece en `uploads/` para que los pedidos históricos muestren su miniatura intacta.
2. **Reemplazo de Imagen en Edición:** El borrado físico mediante `unlink()` queda estrictamente reservado para `actualizar.php`, únicamente cuando un artesano reemplaza deliberadamente una fotografía existente por una nueva, y la anterior era un archivo subido en `uploads/` (nunca si era un vector de `assets/svg/`).

## Alternativas Consideradas
- **Borrar imagen y poner placeholder en pedidos:** Degrada la experiencia visual del artesano y el cliente al revisar pedidos anteriores.

## Consecuencias
- Cero imágenes rotas en el historial de pedidos y auditoría.
- Limpieza efectiva de archivos obsoletos cuando el artesano actualiza fotos.
