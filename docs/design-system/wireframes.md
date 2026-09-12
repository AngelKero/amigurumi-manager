# Especificación de Vistas, Wireframes & Componentes

[← Volver al Índice de Diseño](./README.md)

Este documento define la estructura esquemática y la arquitectura de rejilla responsiva para todas las vistas de **Crochet Manager**, siguiendo la arquitectura modular de componentes PHP (`views/`) y el sistema de diseño "Algodón Nórdico".

---

## 1. Puntos de Interrupción Responsivos

El diseño sigue la rejilla móvil-primero de 12 columnas de Bootstrap 5:
- **Móvil (`xs`, `< 576px`):** Columnas apiladas al 100% de ancho (`col-12`), menú hamburguesa en navbar, tablas de pedidos transformadas en tarjetas táctiles apiladas (`.order-card-mobile`).
- **Tableta (`md`, `≥ 768px`):** Rejilla de 2 columnas para tarjetas (`col-md-6`), filtros horizontales y modales emparejados.
- **Escritorio (`lg` / `xl`, `≥ 992px`):** Catálogo en 3 columnas (`col-lg-4`), vista de inventario con menú lateral (`panel_sidebar.php`), vistas de detalle y formulario en división de 2 columnas (`col-lg-8` y `col-lg-4`).

---

## 2. Componentes de Navegación

### 2.1 Navbar Compartido (`views/components/navbar.php`)
- **Público:** Marca oficial `Crochet Manager`, enlace `[Catálogo]` y botón `[👤 Iniciar Sesión]`.
- **Autenticado:** Muestra el sello artesanal `@admin (Artesano Titular)` con acceso directo al panel de control y botón de cierre de sesión.

### 2.2 Menú Lateral del Panel (`views/components/panel_sidebar.php`)
En vistas administrativas (`creaciones.php`, `pedidos.php`, `usuarios.php`), despliega el menú lateral izquierdo:
- Perfil del creador activo.
- `[🧶 Inventario & Creaciones]`
- `[📦 Gestión de Pedidos]`
- `[👥 Comunidad de Artesanos]`
- `[➕ Nueva Creación]`

---

## 3. Vistas Principales del Sistema

### 3.1 Catálogo Público (`index.php` $\rightarrow$ `views/pages/catalogo_content.php`)
- **Hero Banner Quilted:** Titular en `Fraunces`, fotografía de estudio con marco pespunteado de 540px y chips de confianza de la plataforma.
- **Estación de Filtros Textiles:** Selector de categoría, búsqueda en vivo, selector de artesanos (`#filterArtisan`), filtros de precio mínimo/máximo y botón de reinicio.
- **Cuadrícula de Creaciones:** Componente reutilizable `product_card.php` con píldora anti-desbordamiento de dimensiones, badge de stock o modalidad bajo encargo, y botón de compra/inspección.
- **Paginación Textil:** Botones pill bordados con indicador de total de creaciones.

### 3.2 Ficha de Detalle de Producto (`detalle.php` $\rightarrow$ `views/pages/detalle_content.php`)
- Marco fotográfico acolchado con pespunte interior.
- Sello de creador verificado en la plataforma con enlace directo.
- Ficha técnica de especificaciones (dimensiones, fibra, horas de tejido, cuidados).
- Simulador de rentabilidad y labor del artesano.
- Modal de checkout para coordinación de compra directa con el artesano.

### 3.3 Inventario & Gestión de Creaciones (`creaciones.php` $\rightarrow$ `views/pages/creaciones_content.php`)
- Cabecera luminosa pespunteada con sello oficial.
- 4 Tarjetas KPI con tipografía `Outfit` de alto contraste.
- Cuadrícula responsiva de tarjetas de inventario con ajuste de existencias in-situ, toggle de modalidad bajo encargo, inspección técnica modal y eliminación lógica con salvaguarda referencial.

### 3.4 Formulario de Creación / Edición (`formulario.php` $\rightarrow$ `views/pages/formulario_content.php`)
- Formulario dual para alta y actualización de piezas con subida de fotografías.
- Simulador de margen bruto y retorno por hora en vivo, protegido por un escudo de porcelana con desenfoque (`filter: blur(24px)`) hasta completar los campos obligatorios.

### 3.5 Control de Pedidos & Encargos (`pedidos.php` $\rightarrow$ `views/pages/pedidos_content.php`)
- Tarjetas de pedidos pespunteadas responsivas 3x/2x con miniatura de la pieza.
- Enlace directo a WhatsApp (`wa.me`) con el cliente.
- Insignias tri-estado de pago (`Pendiente`, `Anticipo 50%`, `Liquidado`).
- Cancelación de pedidos con restitución atómica de existencias.

### 3.6 Directorio de Creadores (`usuarios.php` $\rightarrow$ `views/pages/usuarios_content.php`)
- Cabecera unificada `artisan-module-header` con fondo porcelana y tipografía `Fraunces`.
- Tabla y tarjetas de creadores con roles RBAC, métricas de piezas asociadas y salvaguarda ID #1.
