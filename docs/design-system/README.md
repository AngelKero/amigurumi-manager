# Sistema de Diseño "Algodón Nórdico" & UI/UX

[← Volver al Hub Principal de Documentación](../README.md)

Este directorio documenta el sistema de diseño visual, los tokens de estilo, la galería de recursos vectoriales y las auditorías heurísticas de accesibilidad para la aplicación **Crochet Manager**.

---

## 1. Documentos del Módulo

| Documento | Descripción |
| :--- | :--- |
| **[Identidad Visual & Branding](./brand-identity.md)** | Manual maestro de marca v2.0.0, imagotipos, logotipos, isologos y sellos del taller artesanal. |
| **[Recursos Vectoriales & SvgHelper](./svg-assets.md)** | Catálogo de SVGs en `assets/svg/`, renderizado inline de alto rendimiento y helper PHP. |
| **[Wireframes & Especificación de Vistas](./wireframes.md)** | Arquitectura de componentes PHP, vistas públicas y paneles administrativos responsivos. |
| **[Auditorías Heurísticas & Accesibilidad](./audits.md)** | Reporte consolidado de cumplimiento WCAG 2.1 AA, evaluación de contrastes y resolución de brechas. |

---

## 2. Tokens de Color ("Algodón Nórdico")

Definidos canónicamente en `src/css/01-settings/variables.css`:

| Token CSS | Hex Code | Rol Semántico |
| :--- | :---: | :--- |
| `--craft-primary` | `#8E5B74` | Color de marca primario, CTAs principales, branding navbar. |
| `--craft-primary-hover` | `#75475E` | Estados de hover y foco interactivo. |
| `--craft-primary-subtle` | `#F7EFF3` | Fondo lavanda suave para chips activos y filas seleccionadas. |
| `--craft-secondary` | `#52857C` | Abeto Nórdico (Nordic Spruce), acento artesanal, garantías. |
| `--craft-secondary-text` | `#235048` | **Texto de alto contraste** (>6.2:1) para badges sobre fondo spruce. |
| `--craft-secondary-subtle` | `#EBF4F2` | Niebla Glaciar (Glacier Mist), fondo para badges de stock positivo. |
| `--craft-accent-gold` | `#D99C52` | Miel Nórdica (Nordic Honey), pedidos pendientes, badges de alerta. |
| `--craft-bg` | `#F8F9FB` | Alabastro nórdico de fondo de página (reemplaza blancos crudos). |
| `--craft-surface` | `#FFFFFF` | Fondo blanco limpio para tarjetas y modales con elevación suave. |
| `--craft-surface-muted` | `#F1F4F8` | Fondos de cabecera de tabla, dropzone y divisores. |
| `--craft-border` | `#E4E8ED` | Bordes frost sutiles y divisores. |
| `--craft-text-main` | `#1E252D` | Pizarra Medianoche para títulos, precios y textos destacados. |
| `--craft-text-muted` | `#505963` | Texto secundario, microcopy y especificaciones técnicas. |

---

## 3. Jerarquía Tipográfica

- **Titulares Artesanales de Alto Impacto:** Google Font **`Fraunces`** (`weights: 600, 700, 800`) para titulares del Hero, marcas de agua, nombres de producto y títulos de modal.
- **Display Geométrico Moderno & Cifras:** Google Font **`Outfit`** (`weights: 600, 700, 800`) para subtítulos administrativos, valores en tarjetas KPI y badges numéricos.
- **Cuerpo y Controles de Formulario:** Google Font **`Plus Jakarta Sans`** (`weights: 400, 500, 600`) para microcopy, tablas, inputs y descripciones.

---

## 4. Patrones de Craft Detailing Textil

1. **Pespuntes Interiores (`.card-stitched`):** Borde pespunteado mediante pseudo-elemento `dashed` sutil al 22% de opacidad.
2. **Botones Bordados (`.btn-craft-stitched`):** Contorno interior discontinuo blanco para CTAs principales.
3. **Etiquetas Textiles de Cuidado (`.badge-textile-tag`):** Insignias con borde izquierdo sólido de 3.5px y simulación de costura `•••`.
4. **Marcos Acolchados (`.product-photo-stitched-frame`):** Marco paspartú blanco con pespunte interior alrededor de fotografías.
5. **Divisores de Costura (`.divider-stitched`):** Separadores horizontales con gradiente de hilo discontinuo.
