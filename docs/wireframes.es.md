# Micro-ERP de Amigurumis: Wireframes de Baja Fidelidad y Especificación de Vistas

Este documento define la estructura esquemática y la arquitectura de rejilla responsiva para todas las vistas de la aplicación, siguiendo la arquitectura modular de componentes PHP (`views/`) y el sistema de diseño "Algodón Nórdico".

---

## 1. Directrices de Rejilla Responsiva y Puntos de Interrupción

El diseño sigue estrictamente la rejilla móvil-primero de 12 columnas de Bootstrap 5:
- **Móvil (`xs`, `< 576px`):** Columnas apiladas al 100% de ancho (`col-12`), barras de herramientas colapsadas verticalmente, barra de navegación colapsada en menú hamburguesa, tablas de pedidos transformadas en tarjetas táctiles apiladas (`.order-card-mobile`).
- **Tableta (`md`, `≥ 768px`):** Rejilla de 2 columnas para tarjetas (`col-md-6`), barra horizontal de búsqueda y filtros, campos emparejados en modales.
- **Escritorio (`lg` / `xl`, `≥ 992px`):** Catálogo en 3 columnas (`col-lg-4`), vistas de detalle y formulario en división de 2 columnas (`col-lg-6` / `col-lg-8` con simulador de margen fijo `col-lg-4`), paneles tabulares de gestión empresarial completa.

---

## 2. Barra de Navegación Compartida y Panel del Artesano

Para mantener una separación clara entre la navegación del cliente y la gestión privada:
- **Navbar Público:** Contiene **únicamente** el logo de marca, el enlace `[Catálogo]` y el botón `[👤 Iniciar Sesión]`. Las rutas administrativas (`Nuevo Amigurumi`, `Pedidos`, `Usuarios`) están estrictamente ocultas a visitantes no autenticados.
- **Navbar Autenticado:** Al iniciar sesión, la barra despliega el menú `[🛠️ Panel del Artesano ▼]` (con accesos directos a `Nuevo Amigurumi`, `Gestión de Pedidos` y `Gestión de Usuarios`), junto al sello artesanal y el botón de salida (`.badge-artisan-seal`).
- **Barra del Panel de Control:** Las vistas privadas (`formulario.php`, `pedidos.php`, `usuarios.php`) incluyen una barra de cabecera con accesos rápidos (`+ Nuevo Amigurumi`, `Gestión de Pedidos`, `Equipo Artesanal`).

### 2.1 Wireframe ASCII: Navbar Público (Visitante / Cliente)
```text
+----------------------------------------------------------------------------------------------------+
| [Logo] Amigurumi Manager   [Catálogo]                                          [👤 Iniciar Sesión] |
+----------------------------------------------------------------------------------------------------+
| Móvil (<768px): [Logo] Amigurumi Manager   [ ☰ ] -> (Desplegado: Catálogo, Iniciar Sesión)         |
+----------------------------------------------------------------------------------------------------+
```

### 2.2 Wireframe ASCII: Navbar Autenticado (Artesano / Administrador)
```text
+----------------------------------------------------------------------------------------------------+
| [Logo] Amigurumi Manager   [Catálogo]  [🛠️ Panel del Artesano ▼]       [👤 @admin | 🚪 Cerrar Sesión] |
|                                        ├─ ➕ Nuevo Amigurumi                                       |
|                                        ├─ 📦 Gestión de Pedidos                                    |
|                                        └─ 👥 Gestión de Usuarios (Admin)                            |
+----------------------------------------------------------------------------------------------------+
```

### 2.3 Wireframe ASCII: Cabecera Interna del Panel del Artesano
```text
+----------------------------------------------------------------------------------------------------+
| 🛠️ Panel de Administración del Artesano (Sesión Activa: @admin)                                    |
| [➕ Nuevo Amigurumi]     [📦 Gestión de Pedidos]     [👥 Equipo Artesanal]    [← Catálogo Público] |
+----------------------------------------------------------------------------------------------------+
```

### 2.4 Wireframe ASCII: Modal Dinámico de Inicio de Sesión
```text
+-------------------------------------------------------------+
| Iniciar Sesión en Amigurumi ERP                         [X] |
+-------------------------------------------------------------+
| Ingrese sus credenciales para acceder al Panel del Artesano:|
|                                                             |
| Usuario:                                                    |
| [ admin                                                   ] |
|                                                             |
| Contraseña:                                                 |
| [ ••••••••••••                                            ] |
|                                                             |
| [!] Alerta de error dinámica (Credenciales inválidas)       |
|                                                             |
| [ Cancelar ]                             [ Acceder (POST) ] |
+-------------------------------------------------------------+
```

---

## 3. `index.php`: Catálogo e Inventario

Página principal de visualización pública (`views/pages/catalogo_content.php`) con indicadores de stock dinámicos, etiquetas textiles de categoría y filtros avanzados de precio y autoría artesanal.

### 3.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Cabecera Compartida con Logo y Acceso a Sesión)                                            |
+----------------------------------------------------------------------------------------------------+
|                                                                                                    |
| SECCIÓN HERO (.hero-cloud-stitched)                                                                |
| +------------------------------------------------------------------------------------------------+ |
| |  🧶 Catálogo de Amigurumis Hechos a Mano                                                       | |
| |  Explora creaciones artesanales únicas, revisa disponibilidad y encarga piezas personalizadas.  | |
| |  [ Ver Catálogo ↓ ]                     [ Encargar Amigurumi Especial ]                        | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| ESTACIÓN DE FILTRADO AVANZADO (col-12)                                                             |
| +------------------------------------------------------------------------------------------------+ |
| | [ 🔍 Buscar por nombre o material... ]  [ Min $: ___ ]  [ Max $: ___ ]  [ Artesano: Todos ▼ ]  | |
| | Chips Textiles: [🏷️ Todas] [✨ Fantasía] [🌿 Plantas/Botánica] [🐾 Animales/Fauna]               | |
| | Existencias:    [ Existencias: Todas ▼ ]                                    [ ↺ Limpiar ]      | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| REJILLA DE TARJETAS (row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4)                                |
| +------------------------------+ +------------------------------+ +------------------------------+ |
| | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | [MARCO DE FOTO ACOLCHADO]| | | | [MARCO DE FOTO ACOLCHADO]| | | | [MARCO DE FOTO ACOLCHADO]| | |
| | | Insignia: [En Stock (4)] | | | | Insignia:[En Stock (12)] | | | | Insignia:[Bajo Encargo]  | | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | Dragón Ignis             | | | | Mini Suculenta Maceta    | | | | Ajolote Rosado Pastel    | | |
| | | Categ: Fantasía          | | | | Categ: Plantas / Botánica| | | | Categ: Animales / Fauna  | | |
| | | Mat: Algodón Mercerizado | | | | Mat: Algodón Rústico     | | | | Mat: Hilo Chenille       | | |
| | | Tam: 18.5 cm             | | | | Tam: 10.0 cm             | | | | Tam: 14.0 cm             | | |
| | | Por: @admin              | | | | Por: @artesano_clara     | | | | Por: @admin              | | |
| | |                          | | | |                          | | | |                          | | |
| | | $450.00 MXN              | | | | $180.00 MXN              | | | | $320.00 MXN              | | |
| | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Encargar]| | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| +------------------------------+ +------------------------------+ +------------------------------+ |
|                                                                                                    |
| ESTADO VACÍO (Oculto por defecto, visible cuando ningún producto coincide con los filtros)        |
| +------------------------------------------------------------------------------------------------+ |
| |  🧶 No se encontraron piezas que coincidan con los filtros aplicados.                          | |
| |  [ Restablecer Filtros de Búsqueda ]                                                           | |
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

---

## 4. `detalle.php`: Ficha Técnica y Checkout Público de Clientes

Ficha técnica individual (`views/pages/detalle_content.php`) con especificaciones artesanales, cita de historia, sello de autoría verificado y disparador de compra modal.

### 4.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Cabecera Compartida)                                                                       |
+----------------------------------------------------------------------------------------------------+
| MIGAS DE PAN: [ Inicio • Catálogo • Dragón Ignis ] (.breadcrumb-craft-ribbon)                      |
+----------------------------------------------------------------------------------------------------+
| DIVISIÓN EN 2 COLUMNAS (row g-5)                                                                   |
|                                                                                                    |
| [COLUMNA IZQ: col-12 col-lg-6]               | [COLUMNA DER: col-12 col-lg-6]                      |
| +------------------------------------------+ | +-------------------------------------------------+ |
| |                                          | | | [🏷️ Fantasía]   Insignia: [ En Stock: 4 u. ]    | |
| |  [ MARCO DE FOTO CON PESPUNTE ARTESANAL ]| | |                                                 | |
| |  (Proporción 4:3 con borde discontinuo)  | | | Dragón Ignis                                    | |
| |                                          | | | $450.00 MXN (Precio final IVA incluido)         | |
| +------------------------------------------+ | |                                                 | |
| | SELLO DE TALLER ARTESANAL REGISTRADO:    | | | ESPECIFICACIONES TÉCNICAS (.table-craft-specs): | |
| | +--------------------------------------+ | | | • Material: 100% Algodón Mercerizado            | |
| | | 🛡️ Confeccionado a mano por @admin   | | | | • Altura/Largo: 18.5 cm                         | |
| | | Taller Artesanal Registrado          | | | | • Tiempo de Confección: 6.5 horas de tejido     | |
| | +--------------------------------------+ | | | • Disponibilidad: Inmediata (4 unidades)        | |
| +------------------------------------------+ | |                                                 | |
|                                              | | HISTORIA Y DESCRIPCIÓN (.story-quote-craft):    | |
|                                              | | "Amigurumi de dragón fantástico tejido a        | |
|                                              | |  crochet con escamas en relieve y relleno suave"| |
|                                              | |                                                 | |
|                                              | | ACCIÓN DE COMPRA / ENCARGO:                     | |
|                                              | | +---------------------------------------------+ | |
|                                              | | | [🛒 Encargar / Comprar Ahora (Abre Modal)]  | | |
|                                              | | | [← Volver al Catálogo Completo]             | | |
|                                              | | +---------------------------------------------+ | |
|                                              | +-------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

### 4.2 Wireframe ASCII: Modal de Compra y Encargo Público (En `detalle.php`)
```text
+-------------------------------------------------------------+
| Solicitud de Pedido: Dragón Ignis                       [X] |
+-------------------------------------------------------------+
| Precio unitario: $450.00 MXN | Stock disponible: 4 unidad(es)
|                                                             |
| Nombre Completo del Cliente (*):                            |
| [ Mariana Gómez                                           ] |
|                                                             |
| Teléfono / WhatsApp de Contacto (*):                        |
| [ 5512345678                                              ] |
|                                                             |
| Cantidad de Unidades (*):                                   |
| [ - ] [  1  ] [ + ]  (Control acotado: min 1, max stock)    |
|                                                             |
| Fecha Deseada de Entrega:                                   |
| [ YYYY-MM-DD (Selector de fecha)                          ] |
|                                                             |
| Notas / Personalizaciones Especiales:                       |
| [ Empaque para regalo con listón verde bosque...          ] |
|                                                             |
| ℹ️ Nota: Confección artesanal requiere de 5 a 7 días        |
|          hábiles para encargos personalizados.              |
|                                                             |
| RESUMEN ECONÓMICO (Cálculo Universal de Moneda):            |
| Total a Pagar: 1 x $450.00 = $450.00 MXN                    |
|                                                             |
| [ Cancelar ]                          [ Confirmar Pedido ]  |
+-------------------------------------------------------------+
```

---

## 5. `formulario.php`: Creación y Edición con Simulador de Márgenes

Formulario dual (`views/pages/formulario_content.php`) con subida de imagen, selector de exclusividad bajo encargo y tarjeta fija de rendimiento financiero.

### 5.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Cabecera Compartida con [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])      |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi (Activo)]   [📦 Gestión de Pedidos]   [👥 Equipo]       [← Catálogo Público]   |
+----------------------------------------------------------------------------------------------------+
| DIVISIÓN EN 2 COLUMNAS (row g-4)                                                                   |
|                                                                                                    |
| [IZQ: Formulario Principal (col-12 col-lg-8)] | [DER: Simulador de Márgenes (col-12 col-lg-4)]      |
| +-------------------------------------------+ | +------------------------------------------------+ |
| | Nombre del Amigurumi (*):                 | | | 📊 RENDIMIENTO FINANCIERO (Tarjeta Fija)       | |
| | [ Dragón Ignis                          ] | | +------------------------------------------------+ |
| |                                           | | | Precio Venta:       $450.00 MXN                | |
| | Categoría (*):        Material (*):       | | | Costo Materiales: - $120.00 MXN                | |
| | [ Fantasía        ▼] [ 100% Algodón     ] | | | ------------------------------------           | |
| |                                           | | | Ganancia Bruta:     $330.00 MXN                | |
| | Tamaño (cm) (*):      Stock Inicial (*):  | | | Margen de Utilidad: 73.3%                      | |
| | [ 18.5             ] [ 4                ] | | |                                                | |
| |                                           | | | Horas de Trabajo:   6.5 hrs                    | |
| | Precio Venta (MXN)*:  Costo Materiales*:  | | | Retorno por Hora:   $50.77 MXN/hr              | |
| | [ 450.00           ] [ 120.00           ] | | |                                                | |
| |                                           | | | [ Insignia: Margen Saludable (> 60%) ]         | |
| | Horas Estimadas de Confección:            | | +------------------------------------------------+ |
| | [ 6.5                                   ] |                                                      |
| |                                           |                                                      |
| | [X] Confección Exclusiva Bajo Encargo     |                                                      |
| |     (Sin existencia física inmediata)     |                                                      |
| |                                           |                                                      |
| | Descripción / Cuidados:                   |                                                      |
| | [ Amigurumi fantástico con detalles...  ] |                                                      |
| |                                           |                                                      |
| | Fotografía del Producto (enctype file):   |                                                      |
| | +---------------------------------------+ |                                                      |
| | | [ Arrastre imagen o clic para buscar] | |                                                      |
| | | Formatos: JPG, PNG, WEBP (Máx 5MB)    | |                                                      |
| | +---------------------------------------+ |                                                      |
| |                                           |                                                      |
| | [ Ir a Pedidos ]     [ Guardar Creación ] |                                                      |
| +-------------------------------------------+ |                                                      |
+----------------------------------------------------------------------------------------------------+
```

---

## 6. `pedidos.php`: Panel de Gestión de Encargos

Panel de control para artesanos y administradores (`views/pages/pedidos_content.php`) con métricas en tiempo real, enlace directo a WhatsApp, estado de cobro y modal de alta manual.

### 6.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Cabecera Compartida con [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])      |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]         [📦 Pedidos (Activo)]        [👥 Equipo]             [← Catálogo]      |
+----------------------------------------------------------------------------------------------------+
| GESTIÓN DE PEDIDOS Y ENCARGOS ARTESANALES               [ ➕ Registrar Encargo Manual (Modal) ]    |
|                                                                                                    |
| TARJETAS DE MÉTRICAS (row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4)                         |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
| | Total Pedidos: 2   | | Pendientes: 1      | | En Proceso: 1      | | Ingresos: $1,090   |       |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
|                                                                                                    |
| FILTROS DE ESTADO Y BÚSQUEDA (col-12 mb-3)                                                         |
| +------------------------------------------------------------------------------------------------+ |
| | [ Todos (2) ]  [ Pendientes (1) ]  [ En Proceso (1) ]  [ Entregados (0) ]  [ Cancelados (0) ]    | |
| | [ 🔍 Buscar por cliente, amigurumi o teléfono... ]                                             | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| TABLA DE DATOS RESPONSIVA (table-responsive d-none d-md-table)                                     |
| +------------------------------------------------------------------------------------------------+ |
| | ID | Cliente & Contacto   | Producto      | Cant | Total   | Cobro      | Estado   | Acciones  |
| |----+----------------------+---------------+------+---------+------------+----------+-----------|
| | #1 | Mariana Gómez        | Dragón Ignis  | 1    | $450.00 | [Anticipo  | [En      | [Ver /    |
| |    | 📲 5512345678 (WA)   |               |      |         |  50%]      |  Proceso]|  Actual.] |
| |----+----------------------+---------------+------+---------+------------+----------+-----------|
| | #2 | Carlos Mendoza       | Ajolote Pastel| 2    | $640.00 | [Pendiente]| [Pend.]  | [Ver /    |
| |    | 📲 5598765432 (WA)   |               |      |         |            |          |  Actual.] |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| TARJETAS MÓVILES (d-block d-md-none)                                                               |
| +------------------------------------------------------------------------------------------------+ |
| | Pedido #1: Mariana Gómez • Dragón Ignis (1 u.) • $450.00 • [📲 WhatsApp] • [⚙️ Cambiar Estado] | |
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

---

## 7. `usuarios.php`: Gestión de Equipo y Roles (Solo Administrador)

Panel de administración de usuarios y roles (`views/pages/usuarios_content.php`) con métricas de equipo, modal de alta de usuario y modal de modificación de rol con salvaguarda de cuenta raíz.

### 7.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| NAVBAR (Cabecera Compartida con [Catálogo], [Panel del Artesano ▼], [@admin | Cerrar Sesión])      |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]         [📦 Pedidos]                 [👥 Equipo (Activo)]    [← Catálogo]      |
+----------------------------------------------------------------------------------------------------+
| GESTIÓN DE ARTESANOS Y EQUIPO                           [ ➕ Registrar Nuevo Usuario (Modal) ]     |
|                                                                                                    |
| TARJETAS DE MÉTRICAS (row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4)                         |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
| | Total Usuarios: 3  | | Administradores: 1 | | Artesanos: 1       | | Asistentes: 1      |       |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
|                                                                                                    |
| TABLA DE DIRECTORIO DE EQUIPO (.table-artisan-team)                                                |
| +------------------------------------------------------------------------------------------------+ |
| | ID | Usuario / Avatar     | Rol Asignado   | Fecha Alta   | Estado Permisos | Acciones         |
| |----+----------------------+----------------+--------------+-----------------+------------------|
| | #1 | 🛡️ admin             | [Administrador]| 2026-09-10   | [🔒 Cuenta Raíz]| [🔒 Protegido]   |
| |----+----------------------+----------------+--------------+-----------------+------------------|
| | #2 | 👤 artesano_clara    | [Artesano]     | 2026-09-10   | [Activo]        | [✏️ Modificar Rol]|
| |----+----------------------+----------------+--------------+-----------------+------------------|
| | #3 | 👤 asistente_pablo   | [Asistente]    | 2026-09-10   | [Activo]        | [✏️ Modificar Rol]|
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

### 7.2 Wireframe ASCII: Modal de Modificación de Rol
```text
+-------------------------------------------------------------+
| Modificar Rol de Usuario: artesano_clara                [X] |
+-------------------------------------------------------------+
| Seleccione el nuevo nivel de acceso para este colaborador:  |
|                                                             |
| Usuario:                                                    |
| [ artesano_clara (Solo lectura)                           ] |
|                                                             |
| Rol Asignado (*):                                           |
| ( ) Administrador (Acceso total a catálogo, pedidos y users)|
| (•) Artesano (Gestión de creaciones y pedidos propios)      |
| ( ) Asistente (Revisión de pedidos e inventario)            |
|                                                             |
| [ Cancelar ]                          [ Guardar Cambios ]   |
+-------------------------------------------------------------+
```

---

## 8. Matriz de Resumen de Puntos de Interrupción Responsivos

| Vista | Móvil (`< 768px`) | Tableta (`768px - 991px`) | Escritorio (`≥ 992px`) |
| :--- | :--- | :--- | :--- |
| **Navbar** | Desplegable hamburguesa (`collapse`) | Horizontal en línea completa | Horizontal completa + Sello de Artesano |
| **Catálogo (`index.php`)** | 1 columna (`col-12`) | 2 columnas (`col-md-6`) | 3 columnas (`col-lg-4`) + Filtros avanzados |
| **Ficha Detalle (`detalle.php`)**| Pila vertical: Foto arriba, ficha abajo | Pila vertical con marco ampliado | División 50/50 (`col-lg-6` / `col-lg-6`) |
| **Formulario (`formulario.php`)**| Pila vertical: Formulario arriba, margen abajo | Formulario arriba, margen abajo | División 66/33: Formulario (`col-lg-8`), Margen (`col-lg-4`) |
| **Pedidos (`pedidos.php`)** | Tarjetas móviles apiladas, enlace WhatsApp | Tabla de datos desplazable | Tarjetas KPI en 4 columnas (`col-lg-3`), tabla ancha |
| **Usuarios (`usuarios.php`)** | Tarjetas de usuario apiladas | Directorio tabular limpio | Tarjetas KPI en 4 columnas, tabla administrativa |
