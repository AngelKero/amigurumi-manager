# Micro-ERP de Amigurumis: Wireframes de Baja Fidelidad y Especificación de Diseño

Este documento define la estructura visual, la distribución de componentes y la arquitectura de grilla responsiva para las vistas principales de la aplicación antes de programar los archivos HTML definitivos de producción.

---

## 1. Directrices de Grilla Responsiva y Puntos de Interrupción

El diseño adopta la grilla móvil-primero de 12 columnas de Bootstrap 5:
- **Móvil (`xs`, `< 576px`):** Columnas apiladas al 100% de ancho (`col-12`), barras de herramientas apiladas verticalmente, menú de navegación hamburguesa colapsable.
- **Tableta (`md`, `≥ 768px`):** Cuadrícula de 2 columnas para tarjetas de productos (`col-md-6`), barra de filtros horizontal.
- **Escritorio (`lg` / `xl`, `≥ 992px`):** Cuadrícula de 3 columnas para el catálogo (`col-lg-4`), división de pantalla de 2 columnas en ficha técnica (`col-lg-6` / `col-lg-6`) y formulario con simulador fijado sticky (`col-lg-8` + `col-lg-4`).

---

## 2. Barra de Navegación Compartida y Navegación del Panel del Artesano

Para mantener una separación arquitectónica estricta entre la experiencia pública del cliente y la administración privada del negocio:
- **Barra de Navegación Pública:** Contiene **únicamente** el logotipo de marca, el enlace a `[Catálogo]` y el botón disparador `[👤 Iniciar Sesión]`. Las opciones de gestión interna (`Nuevo Amigurumi` y `Pedidos`) no se muestran al público visitante.
- **Barra de Navegación Autenticada:** Al iniciar sesión exitosamente, la barra desbloquea de manera dinámica el menú desplegable `[🛠️ Panel del Artesano ▼]` (con accesos a `Nuevo Amigurumi`, `Gestión de Pedidos` y `Gestión de Usuarios`) junto a la insignia del artesano y el botón de cierre de sesión.
- **Barra de Herramientas del Panel del Artesano:** Las vistas privadas (`formulario.html` y `pedidos.html`) disponen de una cabecera de panel dedicada con botones de acción directa (`+ Nuevo Amigurumi` y `Gestión de Pedidos`).

### 2.1 Wireframe ASCII: Barra de Navegación Pública (Invitados / Clientes)
```text
+----------------------------------------------------------------------------------------------------+
| [Logo] Amigurumi Manager   [Catálogo]                                          [👤 Iniciar Sesión] |
+----------------------------------------------------------------------------------------------------+
| Móvil (<768px): [Logo] Amigurumi Manager   [ ☰ ] -> (Desplegado: Catálogo, Iniciar Sesión)          |
+----------------------------------------------------------------------------------------------------+
```

### 2.2 Wireframe ASCII: Barra de Navegación Autenticada (Artesano / Administrador)
```text
+----------------------------------------------------------------------------------------------------+
| [Logo] Amigurumi Manager   [Catálogo]  [🛠️ Panel del Artesano ▼]       [👤 @admin | 🚪 Cerrar Sesión] |
|                                        ├─ ➕ Nuevo Amigurumi                                       |
|                                        ├─ 📦 Gestión de Pedidos                                    |
|                                        └─ 👥 Gestión de Usuarios (Admin)                            |
+----------------------------------------------------------------------------------------------------+
```

### 2.3 Wireframe ASCII: Cabecera Interna del Panel del Artesano (En `formulario.html` y `pedidos.html`)
```text
+----------------------------------------------------------------------------------------------------+
| 🛠️ Panel de Administración del Artesano (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]         [📦 Gestión de Pedidos]                  [← Ver Catálogo Público]     |
+----------------------------------------------------------------------------------------------------+
```

### 2.4 Wireframe ASCII: Modal de Inicio de Sesión (Activado desde la Barra de Navegación)
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

## 3. `index.html`: Catálogo e Inventario

Página pública principal de vitrina con indicadores dinámicos de existencias y barra de filtrado.

### 3.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| BARRA DE NAVEGACIÓN (Cabecera Compartida)                                                          |
+----------------------------------------------------------------------------------------------------+
|                                                                                                    |
| SECCIÓN HERO                                                                                       |
| +------------------------------------------------------------------------------------------------+ |
| |  🧶 Catálogo de Amigurumis Hechos a Mano                                                       | |
| |  Explora creaciones artesanales únicas, revisa disponibilidad y encarga piezas personalizadas.  | |
| |  [ Ver Catálogo ↓ ]                     [ Encargar Amigurumi Especial ]                        | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| BARRA DE FILTRADO (col-12)                                                                         |
| +------------------------------------------------------------------------------------------------+ |
| | [ 🔍 Buscar por nombre o material... ]  [ Categoría: Todas ▼ ]  [ Stock: Todos ▼ ]  [ ↺ Limpiar ] | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| GRILLA DE TARJETAS (row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4)                                |
| +------------------------------+ +------------------------------+ +------------------------------+ |
| | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | | [col-12 col-md-6 col-lg-4]   | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | [CONTENEDOR IMAGEN 4:3]  | | | | [CONTENEDOR IMAGEN 4:3]  | | | | [CONTENEDOR IMAGEN 4:3]  | | |
| | | Badge: [ En Stock (4) ]  | | | | Badge: [ En Stock (12)]  | | | | Badge: [ Agotado ]       | | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| | | Dragón Ignis             | | | | Mini Suculenta Maceta    | | | | Ajolote Rosado Pastel    | | |
| | | Categ: Fantasía          | | | | Categ: Plantas / Botánica| | | | Categ: Animales / Fauna  | | |
| | | Mat: Algodón Mercerizado | | | | Mat: Algodón Rústico     | | | | Mat: Hilo Chenille       | | |
| | | Tam: 18.5 cm             | | | | Tam: 10.0 cm             | | | | Tam: 14.0 cm             | | |
| | |                          | | | |                          | | | |                          | | |
| | | $450.00 MXN              | | | | $180.00 MXN              | | | | $320.00 MXN              | | |
| | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Comprar ]| | | | [ Ver Detalle / Encargar]| | |
| | +--------------------------+ | | +--------------------------+ | | +--------------------------+ | |
| +------------------------------+ +------------------------------+ +------------------------------+ |
|                                                                                                    |
| PAGINACIÓN / BARRA DE ESTADO                                                                       |
| +------------------------------------------------------------------------------------------------+ |
| | Mostrando 3 de 3 piezas artesanales                     [ « ]  [ 1 ]  [ 2 ]  [ » ]             | |
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

---

## 4. `detalle.html`: Ficha Técnica y Checkout Público de Clientes

Vista de detalle profundo con especificaciones de mano de obra y disparador del Modal de Compra Pública.

### 4.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| BARRA DE NAVEGACIÓN                                                                                |
+----------------------------------------------------------------------------------------------------+
| MIGA DE PAN: Inicio / Catálogo / Dragón Ignis                                                      |
+----------------------------------------------------------------------------------------------------+
| DIVISIÓN DE 2 COLUMNAS (row g-5)                                                                   |
|                                                                                                    |
| [COLUMNA IZQUIERDA: col-12 col-lg-6]         | [COLUMNA DERECHA: col-12 col-lg-6]                  |
| +-----------------------------------------+ | +--------------------------------------------------+ |
| |                                         | | | Badge: [ Fantasía ]   Badge: [ En Stock: 4 u. ]  | |
| |  [ IMAGEN PRINCIPAL EN ALTA RESOLUCIÓN] | | |                                                  | |
| |  (Aspect ratio 1:1 o 4:3)               | | | Dragón Ignis                                     | |
| |                                         | | | $450.00 MXN (Precio final IVA incluido)          | |
| |                                         | | |                                                  | |
| +-----------------------------------------+ | | ESPECIFICACIONES TÉCNICAS:                       | |
| | MINIATURAS ADICIONALES:                 | | | • Material: 100% Algodón Mercerizado             | |
| | [Foto 1]   [Foto 2]   [Foto 3]          | | | • Altura/Largo: 18.5 cm                          | |
| |                                         | | | • Tiempo de Confección: 6.5 horas de tejido      | |
| | ATRIBUCIÓN ARTESANAL:                   | | | • Artesano Responsable: admin                    | |
| | Confeccionado a mano por @admin         | | |                                                  | |
| +-----------------------------------------+ | | DESCRIPCIÓN ARTESANAL:                           | |
|                                             | | Amigurumi de dragón fantástico tejido a crochet  | |
|                                             | | con escamas en relieve y relleno hipoalergénico. | |
|                                             | |                                                  | |
|                                             | | ACCIÓN DE COMPRA / ENCARGO:                      | |
|                                             | | +----------------------------------------------+ | |
|                                             | | | [🛒 Encargar / Comprar Ahora (Abre Modal)]   | | |
|                                             | | | [← Volver al Catálogo]                       | | |
|                                             | | +----------------------------------------------+ | |
|                                             | +--------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
```

### 4.2 Modal de Compra y Encargo Público (Trigger en `detalle.html`)
```text
+-------------------------------------------------------------+
| Solicitud de Pedido: Dragón Ignis                       [X] |
+-------------------------------------------------------------+
| Precio unitario: $450.00 MXN | Stock disponible: 4 unidad(es)
|                                                             |
| Nombre Completo del Cliente (*):                            |
| [ Mariana Gómez                                           ] |
|                                                             |
| Cantidad de Unidades (*):                                   |
| [ 1                                                       ] |
|                                                             |
| Fecha Deseada de Entrega:                                   |
| [ YYYY-MM-DD (Selector de fecha)                          ] |
|                                                             |
| Notas / Personalizaciones Especiales:                       |
| [ Empaque para regalo con listón verde bosque...          ] |
|                                                             |
| RESUMEN ECONÓMICO (Cálculo Dinámico en Tiempo Real):        |
| Total a Pagar: 1 x $450.00 = $450.00 MXN                    |
|                                                             |
| [ Cancelar ]                        [ Confirmar Pedido ]    |
+-------------------------------------------------------------+
```

---

## 5. `formulario.html`: Creación y Edición con Simulador de Márgenes

Formulario dual para registrar o actualizar amigurumis con carga real de archivos y tarjeta fija de rentabilidad.

### 5.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| BARRA DE NAVEGACIÓN (Cabecera Compartida con [Catálogo], [Panel del Artesano ▼], [@admin | Salir]) |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi (Activo)]         [📦 Gestión de Pedidos]                 [← Catálogo Público] |
+----------------------------------------------------------------------------------------------------+
| DIVISIÓN DE 2 COLUMNAS (row g-4)                                                                   |
|                                                                                                    |
| [IZQUIERDA: Formulario (col-12 col-lg-8)]     | [DERECHA: Simulador de Márgenes (col-12 col-lg-4)]   |
| +-------------------------------------------+ | +------------------------------------------------+ |
| | Nombre del Amigurumi (*):                 | | | 📊 RENDIMIENTO FINANCIERO (Sticky Card)        | |
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
| |                                           | | | [ Badge: Margen Saludable (> 60%) ]            | |
| | Horas Estimadas de Confección:            | | +------------------------------------------------+ |
| | [ 6.5                                   ] |                                                      |
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

## 6. `pedidos.html`: Panel de Gestión de Encargos

Panel de control para seguimiento y actualización de pedidos con cálculo de ingresos y filtros por estado.

### 6.1 Esquema ASCII
```text
+----------------------------------------------------------------------------------------------------+
| BARRA DE NAVEGACIÓN (Cabecera Compartida con [Catálogo], [Panel del Artesano ▼], [@admin | Salir]) |
+----------------------------------------------------------------------------------------------------+
| 🛠️ PANEL DE ADMINISTRACIÓN DEL ARTESANO (Sesión: @admin)                                          |
| [➕ Nuevo Amigurumi]                 [📦 Gestión de Pedidos (Activo)]        [← Catálogo Público]  |
+----------------------------------------------------------------------------------------------------+
| GESTIÓN DE PEDIDOS Y ENCARGOS                                                                      |
|                                                                                                    |
| TARJETAS DE MÉTRICAS (row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4)                         |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
| | Total Pedidos: 2   | | Pendientes: 1      | | En Proceso: 1      | | Ingresos: $1,090   |       |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
|                                                                                                    |
| FILTROS Y BÚSQUEDA (col-12 d-flex justify-content-between mb-3)                                    |
| +------------------------------------------------------------------------------------------------+ |
| | [ Todos (2) ]  [ Pendientes (1) ]  [ En Proceso (1) ]  [ Entregados (0) ]  [ Cancelados (0) ]    | |
| | [ 🔍 Filtrar cliente o ID... ]                                                                 | |
| +------------------------------------------------------------------------------------------------+ |
|                                                                                                    |
| TABLA RESPONSIVA DE PEDIDOS (table-responsive)                                                     |
| +------------------------------------------------------------------------------------------------+ |
| | ID | Cliente         | Producto            | Cant | Total    | Estado     | Entrega   | Acciones   |
| |----+-----------------+---------------------+------+----------+------------+-----------+------------|
| | #1 | Mariana Gómez   | Dragón Ignis        | 1    | $450.00  | [En Proceso| 2026-09-24| [⚙ Cambiar |
| |    |                 |                     |      |          |  (Azul)]   |           |    Estado ▼|
| |----+-----------------+---------------------+------+----------+------------+-----------+------------|
| | #2 | Carlos Mendoza  | Ajolote Rosado      | 2    | $640.00  | [Pendiente | 2026-09-30| [⚙ Cambiar |
| |    |                 |                     |      |          |  (Ámbar)]  |           |    Estado ▼|
| +------------------------------------------------------------------------------------------------+ |
| (Opciones de cambio: 'En Proceso', 'Entregado', 'Cancelar Pedido [Devuelve Stock]')                |
+----------------------------------------------------------------------------------------------------+
```

---

## 7. Matriz de Distribución Responsiva

| Vista | Móvil (`< 768px`) | Tableta (`768px - 991px`) | Escritorio (`≥ 992px`) |
| :--- | :--- | :--- | :--- |
| **Barra de Navegación** | Botón hamburguesa colapsable | Horizontal en línea | Horizontal en línea + estado de usuario |
| **Grilla de Catálogo** | 1 columna (`col-12`) | 2 columnas (`col-md-6`) | 3 columnas (`col-lg-4`) |
| **Ficha Técnica** | Apilado vertical: Imagen arriba, ficha abajo | Apilado con imagen destacada | División 50/50 (`col-lg-6` / `col-lg-6`) |
| **Formulario** | Apilado vertical: Formulario arriba, margen abajo | Formulario arriba, margen abajo | División 66/33: Formulario (`col-lg-8`), Tarjeta Sticky (`col-lg-4`) |
| **Panel de Pedidos** | Tarjetas KPI en fila simple, tabla scrollable | Tarjetas KPI 2x2, tabla scrollable | 4 tarjetas KPI (`col-lg-3`), tabla expandida |
