# 002 · Layout, Componentes UI & Sistema Algodón Nórdico

**Estado:** implementado ✅

## Qué hace

Construye la arquitectura de presentación modular de la aplicación bajo el sistema de diseño visual **"Algodón Nórdico"**. Desacopla las vistas en componentes PHP reutilizables (`views/components/`), define un layout maestro (`views/layouts/main.php`), estructura los estilos en capas ITCSS (`src/css/`) y los módulos cliente en JavaScript ES6 (`src/js/`). Transmite una estética textil acogedora con pespuntes discontinuos, etiquetas textiles y marcos fotográficos acolchados.

## Por qué

Una aplicación para artesanos textiles debe reflejar la calidez, dedicación y cuidado visual del trabajo hecho a mano. El sistema erradica las tablas grises y botones genéricos de fábrica, garantizando accesibilidad visual (WCAG 2.1 AA con ratios de contraste superiores a 6.2:1) y una navegación fluida en dispositivos móviles y de escritorio.

## Criterios de aceptación

- [x] Arquitectura de presentación modular basada en PHP nativo sin dependencias de motores de plantillas externos.
- [x] Estilos organizados bajo la metodología ITCSS en `src/css/` (01-settings a 04-components).
- [x] Paleta cromática oficial implementada en variables CSS (`--craft-primary: #8E5B74`, `--craft-secondary: #52857C`, etc.).
- [x] Tipografía artesanal Google Font `Fraunces` para títulos y branding, `Outfit` para KPIs y `Plus Jakarta Sans` para textos.
- [x] Micro-detalles textiles implementados: pespuntes `.card-stitched`, `.btn-craft-stitched`, etiquetas de cuidado `.badge-textile-tag` y marcos acolchados `.product-photo-stitched-frame`.
- [x] Erradicación total del azul eléctrico por defecto de Bootstrap (`#0d6efd`).
- [x] 6 vistas principales completamente operativas a nivel de maqueta: catálogo (`index.php`), detalle de pieza (`detalle.php`), formulario de creación (`formulario.php`), pedidos (`pedidos.php`), gestión de creaciones (`creaciones.php`) y directorio de usuarios (`usuarios.php`).

## Fuera de alcance

- Conexión asíncrona AJAX con APIs de servidor (reservada para la Fase 4).
