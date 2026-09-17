# Tutorial: Recepción y Gestión de tu Primer Encargo con WhatsApp

La filosofía de **Crochet Manager** sitúa la comunicación directa y transparente entre el cliente y la creadora o creador en el centro de la experiencia. Este tutorial enseña cómo un comprador interactúa con una pieza en el catálogo, cómo el sistema congela el precio pactado y cómo se establece el enlace directo a través de la API universal de **WhatsApp**.

---

## Objetivos del Tutorial

Al finalizar este tutorial, sabrás cómo:
1. Experimentar el flujo de compra desde la perspectiva del cliente en el catálogo público.
2. Utilizar el selector interactivo de cantidad acotada (**stepper**) y sus límites de seguridad.
3. Comprender cómo la plataforma genera el registro de pedido con su precio inmutable (`precio_final`).
4. Contactar directamente a la artesana o artesano mediante el protocolo `wa.me` sin intermediarios opacos.
5. Gestionar el avance del pedido a través de los estados del taller textil.

---

## Requisitos Previos

- Tener al menos una creación publicada en el catálogo (puedes apoyarte en el [Tutorial: Primera Creación](./primera-creacion.md)).
- Para la artesana: haber configurado el número telefónico en formato internacional en su perfil (ver guía [How-To: Configurar WhatsApp](../how-to/configurar-whatsapp-artesano.md)).

---

## Paso 1: Exploración del Catálogo Público

Cuando un cliente potencial ingresa a la vitrina virtual (`/index.php`):
1. Puede filtrar creaciones por categoría (chips textiles interactivos `.btn-chip-textile`), rangos de precio o artesana creadora.
2. Cada tarjeta muestra el distintivo de stock:
   - **En Stock:** Muestra la etiqueta verde abeto nórdico (`.badge-stock`) indicando las unidades disponibles.
   - **Bajo Encargo Exclusivo:** Muestra la etiqueta de encargo y deshabilita la compra inmediata.
3. El cliente hace clic en el botón de compra o en **"Ver Detalle"** para examinar la historia y especificaciones de la pieza.

---

## Paso 2: El Modal de Pedido y el Control de Cantidad (Stepper)

Al seleccionar una pieza en stock, se abre el modal de solicitud rápida (`#modalSolicitar`):
1. **Control de Cantidad Bounded Stepper:**
   - La cantidad no se digita en texto libre; se ajusta con botones simétricos de incremento `[+]` y decremento `[-]` que rodean un campo de sólo lectura.
   - **Límites estrictos:** El **stepper** respeta un mínimo de 1 unidad y un límite máximo correspondiente a la `cantidad_stock` física disponible en base de datos.
   - Si el stock es 3 unidades, el botón `[+]` se deshabilitará automáticamente al llegar a 3 para evitar sobreventas o discrepancias de almacén.
2. **Datos de Contacto del Comprador:**
   - El cliente introduce su nombre, teléfono o correo electrónico y notas personalizadas (por ejemplo: *"Deseo envoltorio para regalo"*).

---

## Paso 3: Confirmación y Congelamiento del Precio (`precio_final`)

Cuando el cliente pulsa el botón **"Confirmar Pedido"**:
1. El backend procesa la petición en una transacción atómica segura (`BEGIN IMMEDIATE TRANSACTION`).
2. El sistema toma el precio unitario vigente de la creación en ese instante y calcula el `precio_final` en centavos multiplicando por la cantidad seleccionada.
3. **Inmutabilidad del Contrato:** Una vez creado el pedido, el campo `precio_final` queda sellado en la tabla `pedidos`. Incluso si la artesana aumenta el precio de la creación en el futuro, el pedido histórico conservará exactamente el precio acordado con el cliente al momento de la compra.
4. El stock de la creación se descuenta de forma inmediata para reservar las unidades.

---

## Paso 4: Coordinación Directa vía WhatsApp (`wa.me`)

Inmediatamente después de confirmarse el encargo, la plataforma habilita la comunicación instantánea:
1. Si el cliente registró su teléfono o si el pedido es consultado en el panel de administración, se genera un botón de acción rápida con el enlace canónico a **WhatsApp**:
   ```
   https://wa.me/{telefono}?text={mensaje_preformateado}
   ```
2. **Mensaje Preformateado:** El enlace incluye automáticamente el identificador del pedido (`#ID`), el nombre de la pieza y la cantidad acordada.
3. **Pacto Autónomo de Tiempos:** A diferencia de las tiendas masivas que prometen plazos centralizados de fábrica (como "envío en 48 horas"), la creadora y el comprador acuerdan directamente por chat los detalles del envío, los puntos de entrega local o el avance del tejido.

---

## Paso 5: Seguimiento de Estados y Liquidación

Dentro del panel de control de la artesana (`/pedidos.php`):
1. El pedido inicia en estado **Pendiente** con el distintivo color miel nórdica (`--craft-accent-gold`).
2. La artesana puede cambiar el estado a:
   - **En Proceso:** Cuando el tejido o empaque está en marcha.
   - **Completado:** Cuando la pieza ha sido entregada y liquidada en su totalidad.
   - **Cancelado:** Si el cliente desiste de la compra, ejecutando la restitución automática de stock (ver [How-To: Cancelación y Restitución](../how-to/cancelacion-y-restitucion-stock.md)).

---

## Resumen

El ciclo de encargo combina la precisión transaccional en base de datos con la cercanía humana del trato artesanal. Mediante el **stepper** acotado, el congelamiento del `precio_final` y los enlaces universales a **WhatsApp**, artesanas y clientes disfrutan de una relación de confianza y claridad técnica.
