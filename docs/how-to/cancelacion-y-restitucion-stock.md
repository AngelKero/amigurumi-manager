# Guía How-To: Cancelación de Pedidos y Restitución Atómica de Stock

Esta guía de resolución operativa explica el procedimiento para cancelar un pedido en el sistema, detallando el mecanismo atómico de base de datos que garantiza la restitución inmediata del inventario físico y la preservación de la consistencia contable sin carreras de concurrencia.

---

## 1. El Problema Operativo

Cuando un cliente solicita un producto terminado en el catálogo, las unidades reservadas se descuentan inmediatamente del inventario para proteger al taller de ventas duplicadas. 

Sin embargo, si el cliente desiste de la compra, no responde en los canales de coordinación o solicita una anulación, el pedido debe marcarse como **Cancelado** y el inventario físico debe restaurarse con exactitud matemática. Si este proceso no se realiza de forma atómica e **idempotente**, podrían ocurrir duplicaciones erróneas de stock o pérdidas de auditoría.

---

## 2. Transacción Atómica Inmediata (`BEGIN IMMEDIATE`)

Para garantizar que múltiples solicitudes concurrentes no corrompan el stock en SQLite, el servicio `PedidoService` implementa el Invariante **R-07** (ADR-009):

1. Se inicia una transacción exclusiva mediante la instrucción:
   ```sql
   BEGIN IMMEDIATE TRANSACTION;
   ```
2. El motor SQLite bloquea las escrituras concurrentes desde el primer instante, evitando condiciones de carrera entre cajeros o artesanas.
3. Se verifica el estado actual del pedido:
   - Si el pedido ya se encuentra en estado **Cancelado**, la transacción realiza un rollback inmediato y devuelve un resultado exitoso sin alterar el inventario, demostrando ser una operación estrictamente **idempotente**.
   - Si el pedido está activo (`Pendiente` o `En Proceso`), se procede con la restitución.

---

## 3. Restitución al Inventario Físico (`cantidad_stock`)

Durante la transacción activa:
1. Se extrae la cantidad de unidades solicitadas en el pedido (`pedidos.cantidad`) y el identificador de la pieza vinculada (`pedidos.creacion_id`).
2. Se ejecuta la actualización directa del stock físico:
   ```sql
   UPDATE creaciones
   SET cantidad_stock = cantidad_stock + :cantidad,
       actualizado_en = datetime('now', 'localtime')
   WHERE id = :creacion_id;
   ```
3. Se actualiza el estado del pedido:
   ```sql
   UPDATE pedidos
   SET estado = 'Cancelado',
       actualizado_en = datetime('now', 'localtime')
   WHERE id = :pedido_id;
   ```
4. Se confirma la transacción con `COMMIT`. A partir de este microsegundo, la `cantidad_stock` reflejada en el catálogo público se incrementa automáticamente, permitiendo que otro comprador adquiera las unidades liberadas.

---

## 4. Procedimiento Operativo Paso a Paso

### En el Panel de Administración (`/pedidos.php`)
1. Inicie sesión como `admin` o como la artesana propietaria de la creación (protección IDOR R-04).
2. Localice el pedido en la lista y verifique que su estado no sea ya **Cancelado**.
3. Haga clic en el botón de acción **"Cancelar Pedido"**.
4. El sistema mostrará un diálogo de confirmación indicando explícitamente el impacto en el inventario:
   > *"¿Deseas cancelar el pedido #42? Se reintegrarán +2 unidad(es) al stock físico de 'Conejo Nórdico en Lino'."*
5. Confirme la acción. La interfaz actualizará la insignia visual a gris suave y refrescará el contador de existencias.

### Mediante Endpoint REST (`/api/pedidos/cancelar.php`)
```http
POST /api/pedidos/cancelar.php HTTP/1.1
Host: localhost:8000
Authorization: Bearer <TOKEN_ARTESANO>
Content-Type: application/json

{
  "pedido_id": 42,
  "motivo": "Cliente canceló por WhatsApp antes del envío"
}
```

Respuesta JSON exitosa (HTTP 200 OK):
```json
{
  "exito": true,
  "mensaje": "Pedido #42 cancelado correctamente. Se han reintegrado 2 unidad(es) al inventario.",
  "datos": {
    "pedido_id": 42,
    "estado": "Cancelado",
    "unidades_restituidas": 2,
    "creacion_id": 15,
    "nuevo_stock": 5
  }
}
```

---

## 5. Garantía de Idempotencia y Códigos de Error

Si el endpoint se invoca múltiples veces consecutivas para el mismo pedido:
- La primera llamada restituye las unidades y marca el estado **Cancelado**.
- Las llamadas subsiguientes detectan que el estado ya es **Cancelado**, retornan `200 OK` informando que el pedido ya estaba cancelado, y **NO vuelven a sumar unidades** al stock.
- Esta garantía **idempotente** protege contra pulsaciones dobles del usuario en conexiones lentas o reintentos automáticos de clientes HTTP.
