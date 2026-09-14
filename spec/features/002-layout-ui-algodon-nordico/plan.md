# 002 · Layout, Componentes UI & Sistema Algodón Nórdico — Plan

## Enfoque

Adoptar el principio de separación física estricta: `src/` queda 100% reservado a recursos estáticos de frontend (`css/` y `js/`), mientras que `views/` organiza las plantillas modulares en PHP divididas en `layouts/`, `components/` y `pages/`. Estilizar mediante CSS moderno con variables personalizadas y componentes reutilizables sin atarse a frameworks CSS pesados.

## Implementación

1. **Estructura ITCSS en `src/css/`**:
   - `01-settings/`: variables CSS, tokens cromáticos y tipografías (`variables.css`).
   - `02-base/`: reset, estilos globales del cuerpo y fuentes (`base.css`).
   - `03-layout/`: rejillas, contenedores y layouts maestros.
   - `04-components/`: botones, tarjetas, modales, tablas, badges y micro-detalles pespunteados (`buttons.css`, `cards.css`, `badges.css`, `orders.css`, `creaciones.css`).
2. **Sistema de Componentes en `views/`**:
   - `views/layouts/main.php`: layout maestro HTML5 con inyección de CSS, fuentes y modales comunes.
   - `views/components/`: navbar, sidebar de administración, footer y modales interactivos.
   - `views/pages/`: contenidos específicos por pantalla (`catalogo_content.php`, `detalle_content.php`, etc.).
3. **Puntos de Entrada Raíz**:
   - `index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`, `creaciones.php`.

## Decisiones

- **Bootstrap 5.3 CDN solo como utility baseline:** Se utiliza para el grid responsivo y modales básicos, pero se redefinen sus variables (`--bs-primary: var(--craft-primary)`) para erradicar su estética genérica.
- **Micro-animaciones suaves:** Se añaden efectos de flotación sutiles (`@keyframes floatSoft`) y desenfoque glassmorphic (`backdrop-filter: blur(12px)`).

## Riesgos

- **Contraste insuficiente en fondos claros:** Mitigado mediante la creación de `--craft-secondary-text: #235048` para garantizar > 6.2:1 en badges y píldoras.
- **Desbordamiento de texto en tarjetas:** Mitigado con envolventes flexibles (`.card-product-meta`) y truncado elíptico protector.
