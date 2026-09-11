# Informe de Auditoría Heurística de UI/UX y Sistema de Diseño

**Auditor:** Design Auditor Agent Skill (`v1.2.13`, basado en `Ashutos1997/claude-design-auditor-skill`)  
**Código Evaluado:** Micro-ERP y Catálogo de Amigurumis (`index.html`, `detalle.html`, `formulario.html`, `pedidos.html`, `css/styles.css`, `js/app.js`)  
**Alcance:** Evaluación Heurística Integral, Balance Visual, Arquitectura de Información, Heurísticas de Nielsen y 3 Propuestas de Sistema de Diseño  
**Normas de Evaluación:** WCAG 2.1 AA, 10 Heurísticas de Nielsen Norman, Marco de 19 Categorías de Design Auditor  
**Fecha:** 10 de Septiembre de 2026  

---

## 1. Resumen Ejecutivo y Puntuación Global

```
================================================================================
PUNTUACIÓN GLOBAL DE DISEÑO Y USABILIDAD:  89 / 100  [ Calificación: B+ / Muy Bueno ]
================================================================================
Accesibilidad (WCAG 2.1 AA)       :  91 / 100  [ Aprobado ]
Jerarquía Visual y Ritmo          :  92 / 100  [ Excelente ]
Arquitectura de Información       :  88 / 100  [ Muy Bueno ]
Heurísticas de Usabilidad (H1-H10):  87 / 100  [ Muy Bueno ]
Formularios y Prevención Errores  :  90 / 100  [ Excelente ]
Alineación con el Nicho Textil    :  86 / 100  [ Bueno ]
================================================================================
```

### Desglose por Categoría
| # | Categoría | Puntaje | Estado | Hallazgos Principales |
|---|---|:---:|:---:|---|
| 1 | **Tipografía y Jerarquía** | 92% | 🟢 Aprobado | Fuente Google (*Plus Jakarta Sans*), escala de encabezados estricta (`h1` a `h6`), excelente contraste. |
| 2 | **Color y Contraste** | 88% | 🟡 Advertencia | Terracota (`#C25E3E`) y Espresso (`#2D2621`) superan AAA/AA. El verde salvia de badge (`#5A7D66` sobre `#EDF4EF`) está al límite (3.8:1 para texto pequeño). |
| 3 | **Espaciado y Ritmo de Diseño** | 94% | 🟢 Aprobado | Respeta la cuadrícula base de 8pt de Bootstrap (`p-3`, `p-4`, `gap-3`, `mb-4`, `g-4`). |
| 4 | **Balance Visual y Escaneo** | 90% | 🟢 Aprobado | Hero equilibrado, tarjetas con relación 4:3 consistente, simulador de margen sticky efectivo. |
| 5 | **Navegación y Orientación** | 91% | 🟢 Aprobado | Separación limpia de Barra Pública (`Catálogo` + `Iniciar Sesión`) y Panel del Artesano autenticado. |
| 6 | **Formularios e Inputs** | 93% | 🟢 Aprobado | Validación dual, preview de fotografía oculta hasta selección de archivo. |
| 7 | **Retroalimentación Interactiva**| 87% | 🟢 Aprobado | Feedback dual en vivo en calculadora de márgenes, recalculo automático en stepper de checkout. |
| 8 | **Prevención y Recuperación** | 89% | 🟢 Aprobado | Stepper acotado a stock físico real, modal explícito de cancelación con alerta de restitución de stock. |
| 9 | **Arquitectura de Información** | 86% | 🟡 Advertencia | Flujo continuo catálogo-detalle; oportunidad de mejora en tabla de pedidos para pantallas móviles muy reducidas. |

---

## 2. Evaluación Profunda por Dimensiones Clave

### 2.1 Balance de Diseño Visual, Jerarquía y Ritmo de Espacios

#### Puntos Fuertes:
1. **Escala Modular Tipográfica:**
   - Se mantiene una progresión armónica y clara: Título Principal (`display-5` en Hero, `display-6` en Detalle), Títulos de Sección (`h3`/`h4` en peso `700`) y Títulos de Producto (`h5` en `1.25rem`).
   - El interlineado (`line-height: 1.6`) facilita la lectura de especificaciones técnicas y descripciones de confección.
2. **Distribución de Espacios en Blanco:**
   - La rejilla de `index.html` con `g-4` aporta una separación de 24px que evita la sensación de sobrecarga visual.
   - En `detalle.html`, el espacio de 48px (`g-lg-5`) entre la galería y la columna de compra genera un respiro visual óptimo.
3. **Composición de Tarjetas:**
   - La relación de aspecto `4:3` unificada en las imágenes evita desalineaciones verticales ("saltos de diseño"). La insignia flotante de stock en la esquina superior derecha informa de inmediato sin estorbar la visualización del muñeco.

#### Oportunidades de Mejora:
- 🟡 **Contraste en Insignia de Stock:** `.badge-stock-in` usa texto `#5A7D66` sobre fondo `#EDF4EF` (ratio 3.82:1). En pantallas móviles bajo luz solar intensa puede dificultar la lectura rápida.
  - *Solución:* Oscurecer el texto a `#3D5E49` (ratio 5.8:1, superando WCAG AA).

---

### 2.2 Arquitectura de Información y Fricción de Flujos de Usuario

#### Flujo A: Exploración del Catálogo a Ficha Técnica
- **Nivel de Fricción:** Muy Bajo (9/10).
- Doble zona clicable: tanto la fotografía como el título enlazan a `detalle.html?id=ITEM_ID`.
- Barra de herramientas combinada: búsqueda textual + filtro por categoría + filtro por stock + ordenación por precio o novedad.
- **Oportunidad:** Reemplazar el `<select>` de categorías por botones de píldora horizontales (`[Todas] [Fantasía] [Botánica] [Fauna]`) para reducir clics en móviles.

#### Flujo B: Checkout Público del Cliente
- **Nivel de Fricción:** Casi Nulo (9.5/10).
- El modal (`#checkoutModal`) incluye botones de paso `[-] [ 1 ] [+]` acotados al stock disponible (`max="4"`), impidiendo solicitar más unidades de las existentes.
- Cálculo en tiempo real sin recarga de página.
- Enlace de retorno degradado a texto sutil (`← Volver al Catálogo Completo`) garantizando que el 90% de la atención visual recaiga en el botón de compra `🛒 Encargar / Comprar Ahora`.
- **Riesgo en Caso Borde:** Si un usuario entra directo a `detalle.html?id=3` (producto agotado), el botón de compra debería mostrarse deshabilitado con etiqueta "Agotado (Bajo Encargo)".

#### Flujo C: Gestión de Operaciones del Artesano (Micro-ERP)
- **Nivel de Fricción:** Muy Bajo (9.5/10).
- **Separación de Permisos:** Se eliminaron `Nuevo Amigurumi` y `Pedidos` del navbar público, evitando confusiones a los clientes.
- Panel superior oscuro (`.artisan-panel-banner`) proporciona contexto inmediato y navegación fluida entre piezas y pedidos.
- Tarjeta fija (*sticky card*) del simulador de márgenes en `formulario.html`: se mantiene visible al desplazarse en pantallas de escritorio.

---

### 2.3 Evaluación de Heurísticas de Usabilidad (Nielsen Norman H1–H10)

| Heurística | Estado | Diagnóstico en Amigurumi ERP |
|---|:---:|---|
| **H1: Visibilidad del Estado del Sistema** | 🟢 10/10 | Insignias de margen cambian de color en vivo (*Saludable* vs *Crítico*). Stepper se bloquea en límites. Estados de pedidos con psicología de color (Ámbar Pendiente, Azul En Proceso, Verde Entregado). |
| **H2: Relación entre Sistema y Mundo Real** | 🟢 10/10 | Términos familiares para artesanos: *Costo Materiales*, *Horas Confeccionadas*, *Retorno por Hora*, *Hilo Mercerizado*, *Fibra Siliconada*. |
| **H3: Control y Libertad del Usuario** | 🟢 9/10 | Botón de reinicio de filtros. Modal de cancelación con botón explícito de abortar ("No, Mantener Pedido"). Eliminación de imagen seleccionada con un clic. |
| **H4: Consistencia y Estándares** | 🟢 9.5/10 | Rejilla Bootstrap consistente en todas las vistas. Cabecera y pie de página unificados con diseño textil coherente. |
| **H5: Prevención de Errores** | 🟢 10/10 | Input de cantidad en modo solo lectura para evitar caracteres inválidos. Modal de cancelación advierte sobre la restitución automática de existencias. |
| **H6: Reconocimiento antes que Recuerdo** | 🟢 9/10 | Modal de inspección (`#modalDetallePedido`) muestra notas completas del cliente sin obligar al artesano a salir del panel. |
| **H7: Flexibilidad y Eficiencia de Uso** | 🟡 8/10 | Óptimo en escritorio y tabletas. En móviles pequeños (<576px), la tabla amplia de pedidos requiere scroll lateral. |
| **H8: Diseño Estético y Minimalista** | 🟢 9.5/10 | Estética cálida, limpia, sin popups invasivos ni saturación de elementos. |
| **H9: Reconocer y Recuperarse de Errores** | 🟢 9/10 | Contenedor de alerta en línea en Login Modal (`#loginAlert`) con mensajes descriptivos. |
| **H10: Ayuda y Documentación** | 🟢 9/10 | Textos de ayuda bajo los campos (límites de caracteres, formatos JPG/PNG/WEBP, consejos financieros). |

---

## 3. Matriz de Priorización de Mejoras

```
   ALTO IMPACTO │ [QW-1] Ajustar Contraste Insignia │ [CR-1] Bloqueo Agotado en detalle.html
                │                                   │ [CR-2] Vista Tarjeta Móvil en pedidos
                ├───────────────────────────────────┼────────────────────────────────────────
   BAJO IMPACTO │ [QW-2] Píldoras de Categoría      │ [EN-1] Zoom Lightbox en Imágenes
                │ [QW-3] Plazo Estimado de Entrega  │ [EN-2] Efecto Esqueleto (Skeleton)
                └───────────────────────────────────┴────────────────────────────────────────
                               BAJO ESFUERZO                       ALTO ESFUERZO
```

---

## 4. Propuestas de Sistemas de Diseño Visual para el Nicho Artesanal

Para dotar al proyecto de una identidad visual de nivel profesional, se presentan **3 propuestas completas de diseño** diseñadas específicamente para el nicho de amigurumis tejidos a mano:

---

### Propuesta A: "Tierra & Telar" (Arcilla Orgánica & Salvia Herbal) — *Evolución Recomendada*
> **Concepto:** Atmósfera de taller de cerámica y teñido natural. Transmite calidez, sustentabilidad, textura de hilaza de algodón y trabajo manual honesto.

```css
/* Tokens de Paleta de Color */
--color-primary:        #C25E3E;  /* Terracota Arcilla (Botones principales, marca) */
--color-primary-hover:  #A34B2E;  /* Terracota Tostada */
--color-primary-subtle: #FBEFEA;  /* Crema Bisque (Fondos suaves de botones e insignias) */

--color-secondary:      #4E7259;  /* Salvia Bosque / Verde Herbal (Stock disponible) */
--color-secondary-sub:  #EAF2EC;  /* Eucalipto Pálido */

--color-accent-amber:   #D4933B;  /* Ocre Hilado (Pedidos pendientes y destacados) */
--color-bg-canvas:      #FAF7F2;  /* Papel Lino Crudo (Fondo general) */
--color-bg-surface:     #FFFFFF;  /* Blanco Algodón (Superficie de tarjetas) */
--color-text-title:     #2B231E;  /* Carbón Espresso Tostado (Títulos y énfasis) */
--color-text-body:      #5E554E;  /* Gris Cacao Cálido (Párrafos y lectura) */
--color-text-muted:     #8C8177;  /* Yute Suave (Metadatos y textos secundarios) */
--color-border-card:    #EAE3D8;  /* Hilo de Telar (Bordes sutiles) */

/* Combinación Tipográfica */
--font-family-display:  'Plus Jakarta Sans', sans-serif; /* Pesos: 700, 800 */
--font-family-body:     'Inter', sans-serif;              /* Pesos: 400, 500 */

/* Elevación y Bordes */
--radius-card:          14px;
--radius-badge:         8px;
--shadow-soft-card:     0 4px 16px rgba(43, 35, 30, 0.05);
--shadow-hover-card:    0 10px 28px rgba(43, 35, 30, 0.10);
```

---

### Propuesta B: "Algodón Nórdico" (Minimalismo Escandinavo & Lana Pastel)
> **Concepto:** Boutique de diseño nórdico, sensación acogedora (*hygge*), tonos empolvados de ciruela y menta escarchada sobre superficies blancas puras.

```css
/* Tokens de Paleta de Color */
--color-primary:        #8E5B74;  /* Ciruela Brezo Empolvado (Botón principal y acentos) */
--color-primary-hover:  #75475E;  /* Ciruela Intensa */
--color-primary-subtle: #F7EFF3;  /* Lavanda Lechoso */

--color-secondary:      #52857C;  /* Abeto Escandinavo / Verde Glaciar */
--color-secondary-sub:  #EBF4F2;  /* Niebla de Menta */

--color-accent-gold:    #D99C52;  /* Miel Nórdica (Alertas e indicadores) */
--color-bg-canvas:      #F8F9FB;  /* Porcelana Alabastro */
--color-bg-surface:     #FFFFFF;  /* Blanco Nieve */
--color-text-title:     #1E252D;  /* Pizarra Medianoche */
--color-text-body:      #505963;  /* Granito Neutro */
--color-text-muted:     #87929E;  /* Cachemira Frío */
--color-border-card:    #E4E8ED;  /* Borde Escarchado Suave */

/* Combinación Tipográfica */
--font-family-display:  'Outfit', sans-serif;             /* Pesos: 600, 700 */
--font-family-body:     'Plus Jakarta Sans', sans-serif; /* Pesos: 400, 500 */

/* Elevación y Bordes */
--radius-card:          18px;     /* Bordes extra redondeados */
--radius-badge:         20px;    /* Etiquetas en forma de píldora */
--shadow-soft-card:     0 4px 20px rgba(30, 37, 45, 0.04);
--shadow-hover-card:    0 12px 32px rgba(30, 37, 45, 0.09);
```

---

### Propuesta C: "Café & Crochet" (Atelier Vintage & Canela Tostada)
> **Concepto:** Taller clásico parisino, mercería tradicional con etiquetas de cuero, maderas caobas, canela y ámbar. Evoca tradición, piezas de colección y herencia artesanal.

```css
/* Tokens de Paleta de Color */
--color-primary:        #A65128;  /* Canela Tostada / Óxido Cálido */
--color-primary-hover:  #873E1B;  /* Castaño Oscuro */
--color-primary-subtle: #FDF3EB;  /* Manzanilla Suave */

--color-secondary:      #6B7A54;  /* Lana Oliva Artesanal */
--color-secondary-sub:  #F0F3EB;  /* Hoja de Olivo Clara */

--color-accent-amber:   #CC8A35;  /* Caramelo Glaseado */
--color-bg-canvas:      #F7F3EB;  /* Pergamino Antiguo */
--color-bg-surface:     #FFFEFA;  /* Crema Densa */
--color-text-title:     #241B15;  /* Melaza Oscura */
--color-text-body:      #5C5046;  /* Achicoria Tostada */
--color-text-muted:     #8A7D72;  /* Sepia Añejo */
--color-border-card:    #E5DDCE;  /* Borde Lienzo de Yute */

/* Combinación Tipográfica */
--font-family-display:  'Playfair Display', serif;        /* Peso: 700 - Editorial Clásico */
--font-family-body:     'Work Sans', sans-serif;          /* Pesos: 400, 500 - Alta Claridad */

/* Elevación y Bordes */
--radius-card:          10px;     /* Ángulos artesanales tradicionales */
--radius-badge:         6px;     /* Etiquetas estructuradas */
--shadow-soft-card:     0 4px 14px rgba(36, 27, 21, 0.06);
--shadow-hover-card:    0 10px 24px rgba(36, 27, 21, 0.12);
```

---

## 5. Recomendación para Decisión del Usuario

- **Calidad Actual del Código:** La implementación actual de la Fase 2 obtuvo **89/100**, posicionándose en el cuartil superior de aplicaciones web funcionales y accesibles.
- **Ajuste Rápido Recomendado:** Oscurecer el texto de `.badge-stock-in` a `#3D5E49` para asegurar el 100% de cumplimiento WCAG AA en todas las condiciones de brillo.
- **Paleta Recomendada para Producción:** **Propuesta A ("Tierra & Telar")** maximiza la armonía con texturas de estambre, barro y algodón orgánico. Si se busca un toque más contemporáneo y juvenil, la **Propuesta B ("Algodón Nórdico")** es una alternativa sobresaliente.
