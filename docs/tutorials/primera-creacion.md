# Tutorial: Publicación de tu Primera Creación Artesanal

Bienvenido al taller digital de **Crochet Manager**. Esta guía práctica de aprendizaje te acompañará paso a paso en el proceso de publicar tu primera pieza textil o amigurumi en la plataforma, respetando los principios del sistema visual **Algodón Nórdico**, el cálculo de precios justos y la gestión transparente de tu inventario.

---

## Objetivos del Tutorial

Al finalizar este tutorial, habrás aprendido a:
1. Acceder al panel de administración con tu cuenta de artesana o artesano.
2. Completar la ficha técnica con todos los atributos físicos y descriptivos.
3. Comprender la estructura de costos: materiales (`costo_materiales`), labor (`horas_tejido`) y margen de beneficio para definir el `precio` de venta en centavos.
4. Diferenciar entre piezas para entrega inmediata (`cantidad_stock > 0`) y modelos bajo encargo exclusivo (`es_sobre_encargo = 1`).
5. Adjuntar una fotografía optimizada en formatos modernos (WebP, PNG, JPEG) bajo las políticas de seguridad del taller.

---

## Requisitos Previos

- Disponer de una cuenta activa con rol `artesano` o `admin`.
- Iniciar sesión en la plataforma en `http://localhost:8000` (o el dominio de tu servidor).
- Contar con una fotografía clara de tu pieza tejida (peso menor a 5 MB).
- Conocer los costos reales de tus insumos (estambres, ojos de seguridad, relleno siliconado) y el tiempo aproximado invertido en el tejido.

---

## Paso 1: Navegar al Formulario de Nueva Creación

Una vez iniciada tu sesión en la barra de navegación superior:
1. Observa el sello artesanal con tu nombre en la esquina superior derecha (`.badge-artisan-seal`).
2. Haz clic en el botón principal con pespunte artesanal **"Nueva Creación"** o visita la ruta `/formulario.php`.
3. El sistema desplegará el formulario enmarcado con el diseño **Algodón Nórdico**, caracterizado por sus bordes suaves de 18px (`--craft-radius`), tonos ciruela nórdico (`--craft-primary: #8E5B74`) y costuras punteadas (`.card-stitched`).

---

## Paso 2: Identificación y Ficha Técnica de la Pieza

En la primera sección del formulario, completa los datos fundamentales que ayudarán a tus clientes a valorar la artesanía:

1. **Título de la Creación:**
   - Escribe un nombre descriptivo y evocador (ejemplo: *Oso Bosque Nórdico en Lana Alpaca*).
2. **Categoría Textil:**
   - Selecciona la categoría adecuada del desplegable: *Amigurumis*, *Prendas*, *Accesorios* u *Hogar*.
3. **Historia y Descripción:**
   - Describe la inspiración, las técnicas de puntada utilizadas y los cuidados especiales de lavado. La plataforma fomenta la transparencia y el valor humano detrás de cada puntada.
4. **Dimensiones y Materiales:**
   - Especifica el tamaño en centímetros (alto × ancho) y el tipo de fibra (ejemplo: *100% Algodón Pima Peruano, Ojos con traba de seguridad*).

---

## Paso 3: Estructura Financiera y Comercio Justo (Fair-Trade)

La plataforma integra una calculadora de precio sugerido basada en principios de comercio justo. Debes ingresar:

1. **Costo de Materiales (`costo_materiales`):**
   - Ingresa la suma monetaria de los insumos directos utilizados en la pieza.
   - En la base de datos se almacena en centavos enteros para evitar errores de coma flotante. Por ejemplo, `$120.50` se registra internamente como `12050` centavos.
2. **Horas de Tejido (`horas_tejido`):**
   - Registra el tiempo dedicado a confeccionar la obra (ejemplo: `6.5` horas).
   - Valorar tus horas de trabajo garantiza que el precio final reconozca la maestría artesanal y no solo el gasto material.
3. **Precio de Venta al Público (`precio`):**
   - Define el precio definitivo visible en el catálogo. La interfaz te sugerirá un precio sugerido respetando un margen saludable, pero como artesana autónoma tienes el control total de fijar el valor final.

---

## Paso 4: Modalidad Operativa: Stock Inmediato vs. Sobre Encargo

La plataforma contempla dos realidades operativas del taller textil:

### Opción A: Entrega Inmediata (`cantidad_stock > 0`)
- Si ya tienes la pieza terminada en tu estante, ingresa el número de unidades disponibles en el campo **Cantidad en Stock**.
- Los clientes podrán adquirirla inmediatamente con un solo clic en el catálogo.

### Opción B: Confección Bajo Encargo (`es_sobre_encargo = 1`)
- Si prefieres tejer la pieza a petición del cliente o personalizar colores y medidas, activa la casilla **"Pieza Exclusiva Bajo Encargo"** (`es_sobre_encargo = 1`).
- En este caso, el stock inmediato se ajusta a 0. En el catálogo público, el botón de compra inmediata se reemplaza automáticamente por el botón distintivo **"Solicitar Encargo Especial"**, el cual abrirá un canal directo de WhatsApp para coordinar tiempos y especificaciones.

---

## Paso 5: Fotografía y Preservación de Archivos

1. Arrastra tu imagen al área de carga (`dropzone`) o selecciónala desde tu dispositivo.
2. El sistema validará:
   - Tipo de medio MIME real binario (`image/webp`, `image/png`, `image/jpeg`).
   - Límite de tamaño máximo: 5 MB.
3. Si decides no adjuntar foto en este momento, la plataforma asignará automáticamente un avatar vectorial temático de ovillo o aguja desde `assets/svg/piezas/`.
4. Haz clic en **"Publicar Creación"**. El sistema guardará el registro atómicamente y te redirigirá a la vista de detalle con tu nueva pieza lista en el catálogo.

---

## Resumen y Siguientes Pasos

¡Felicidades! Has completado la publicación de tu primera creación. Ahora puedes:
- Consultar cómo se ve tu pieza en la vitrina pública visitando `/index.php`.
- Gestionar tus encargos entrantes siguiendo el [Tutorial: Primer Encargo y WhatsApp](./primer-encargo-whatsapp.md).
- Si necesitas modificar precios o inventario más adelante, consulta la guía [How-To: Cancelación y Restitución de Stock](../how-to/cancelacion-y-restitucion-stock.md).
