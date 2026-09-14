# Misión

## Qué construimos

**Crochet Manager** es un Micro-ERP colaborativo y catálogo interactivo de código abierto para artesanos y creadores textiles independientes de crochet. Permite a múltiples creadores registrar piezas, gestionar existencias físicas en tiempo real, simular y blindar márgenes de beneficio éticos respetando el trabajo manual, y coordinar encargos personalizados directamente con los clientes.

### Piezas principales del producto:
1. **Catálogo Colectivo & Vitrina Pública (`index.php`, `detalle.php`):** Cuadrícula responsiva de creaciones con filtros textiles, badges dinámicos de stock, especificaciones técnicas transparentes y modal de checkout/pedido directo.
2. **Gestor de Inventario & Creaciones (`creaciones.php`, `formulario.php`):** Panel administrativo con KPIs en vivo, ajuste de stock in-situ, alternador de modalidad bajo encargo, ficha técnica modal y simulador de margen ético con escudo protector.
3. **Control de Pedidos & Encargos (`pedidos.php`):** Tablero responsivo de pedidos con tarjetas pespunteadas, enlace directo a WhatsApp (E.164), badges tri-estado de pago (`Pendiente`, `Anticipo 50%`, `Liquidado`) y restitución atómica de existencias al cancelar.
4. **Directorio & Gobernanza de Creadores (`usuarios.php`):** Administración de creadores con roles RBAC (`admin`, `artesano`, `asistente`), recuperación de claves y salvaguarda de cuenta raíz.

## Para quién

- **Artesano / Creador Independiente (`rol: artesano` / `rol: admin`):** Necesita visibilidad para sus creaciones, control exacto de existencias y costos de materiales/labor, y un canal ágil para coordinar entregas y anticipos con clientes sin comisiones abusivas.
- **Administrador del Colectivo (`rol: admin`):** Supervisa el directorio de artesanos, modera accesos y vela por la integridad de la plataforma sin interferir en la autonomía artística de los creadores.
- **Cliente / Visitante Comprador (Público):** Explora piezas artesanales únicas, revisa medidas y materiales fidedignos, y solicita compras o encargos personalizados directamente con el artesano vía WhatsApp.

## Principios

- **Autonomía del Creador:** La plataforma no controla qué, cómo ni cuándo tejen los artesanos. No existen cadenas de montaje ni estandarizaciones forzadas.
- **Transparencia en Fichas Técnicas:** Cada pieza documenta rigurosamente sus dimensiones, fibra/composición y cuidados de lavado para generar confianza genuina en el comprador.
- **Comercio Ético y Margen Justo:** El simulador de precios protege el valor de la hora de tejido manual, asegurando que ningún creador venda por debajo de sus costos reales.
- **Comunicación Directa:** La relación comercial y los acuerdos de entrega se establecen directamente entre cliente y creador mediante WhatsApp, sin intermediarios opacos.
- **Integridad y Trazabilidad:** Todo precio pactado se congela al ordenar; el stock físico se gestiona transaccionalmente; los datos históricos y comprobantes se preservan de forma inmutable.

## Qué NO es

- **NO es una fábrica centralizada:** Jamás promete plazos fijos de manufactura industrial (v.gr. "nuestro taller teje en 5 a 7 días hábiles"). Los tiempos de confección se acuerdan caso a caso entre artesano y cliente.
- **NO es un marketplace de producción masiva:** No admite intermediarios mayoristas ni productos maquilados en serie.
- **NO es una plataforma transaccional bancaria cerrada:** No retiene fondos ni custodia pagos con pasarelas obligatorias; facilita el acuerdo directo y la confirmación transparente de anticipos.
