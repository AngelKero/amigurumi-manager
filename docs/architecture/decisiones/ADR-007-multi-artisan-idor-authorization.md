# ADR-007: Autorización Multi-Artesano y Prevención Estricta de IDOR

## Estado
Aceptada

## Fecha
2026-09-12

## Contexto
En una plataforma colaborativa con múltiples artesanos independientes, existe el riesgo de que un artesano intente modificar o dar de baja piezas o pedidos pertenecientes a otro creador enviando un identificador ajeno en el cuerpo de la petición (Insecure Direct Object Reference - IDOR).

## Decisión
1. Todo endpoint de modificación de creaciones (`actualizar.php`, `eliminar.php`, `restaurar.php`, `ajustar-stock.php`, `toggle-encargo.php`) debe validar la autoría mediante `CreacionService::ensureArtisanOwnership()`:
   ```php
   if ($creacion['artesano_id'] !== $currentUser['id'] && $currentUser['rol'] !== 'admin') {
       throw new RuntimeException('Operación denegada: No tienes permisos para modificar esta creación.', 403);
   }
   ```
2. En pedidos (`GET /api/pedidos/index.php`), los artesanos solo pueden visualizar pedidos vinculados a piezas de su propia autoría (`WHERE c.artesano_id = :userId`), mientras que el administrador puede ver la totalidad.

## Alternativas Consideradas
- **Validación solo por rol:** Insuficiente, permitiría que cualquier artesano modifique las piezas de otro artesano.

## Consecuencias
- Privacidad y aislamiento total entre los inventarios de los distintos creadores.
- Los administradores retienen visibilidad y control global sin vulnerar la seguridad.
