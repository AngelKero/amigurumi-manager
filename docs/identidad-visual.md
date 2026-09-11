# Identidad Visual y Sistema de Marca: "Algodón Nórdico"
**Proyecto:** Crochet Manager (Micro-ERP & Catálogo Textil)  
**Metodología:** Framework `brand-identity` y estándares vectoriales `logo-generator`  
**Versión:** 2.0.0

---

## 01 — Declaración Estratégica de Identidad (Identity Strategy Statement)

> **Crochet Manager** es la convergencia armónica entre el calor táctil del tejido artesanal a mano (prendas, amigurumis, bolsos, mantas y accesorios) y la claridad serena de un Micro-ERP contemporáneo. La identidad visual materializa el confort de la hilaza de algodón mercerizado, la dedicación de cada puntada y la solvencia de un taller artesanal organizado. Cada elemento gráfico respira calidez, empleando líneas de pespunte textil (*running stitch*), arcos orgánicos suaves y una paleta inspirada en tintes botánicos nórdicos, erradicando cualquier frialdad corporativa o estridencia visual.

---

## 02 — Los 4 Arquetipos de Identidad de Marca

Para garantizar versatilidad en todas las superficies (favicons, cabeceras web, marcas de agua, sellos de embalaje, membretes y aplicaciones móviles), la identidad se desglosa en cuatro tipologías fundamentales:

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        ARQUITECTURA DE MARCA                            │
├────────────────────┬────────────────────┬───────────────────────────────┤
│ 1. ISOTIPO         │ 2. LOGOTIPO        │ 3. IMAGOTIPO                  │
│ [Símbolo Puro]     │ [Wordmark / Texto] │ [Símbolo + Texto Separables]  │
│ Solo icono         │ Solo tipografía    │ Coexisten en armonía          │
├────────────────────┴────────────────────┴───────────────────────────────┤
│ 4. ISOLOGO                                                              │
│ [Emblema / Sello Indivisible]                                           │
│ Símbolo y texto fusionados en una unidad estructural                    │
└─────────────────────────────────────────────────────────────────────────┘
```

### 1. Isotipo (Símbolo / Icono puro sin texto)
- **Definición:** La síntesis gráfica mínima que condensa la esencia de la marca sin necesidad de caracteres tipográficos. Funciona como favicon de navegador, avatar en redes sociales, sello de lacre y app icon.
- **Variantes Desarrolladas:**
  1. `isotipo-ovillo-corazon.svg`: Un ovillo de estambre texturizado con una hebra continua que dibuja un corazón y un gancho de crochet en diagonal. Símbolo oficial universal de la marca.
  2. `isotipo-hebra-nordica.svg`: Nube algodonada entrelazada con una lazada de estambre en espiral dorada.
  3. `isotipo-osito-amigurumi.svg`: Rostro geométrico y tierno de osito tejido con pespunte perimetral y orejitas tejidas (representativo de la disciplina de amigurumis).

### 2. Logotipo (Wordmark / Tipografía estilizada sin símbolo)
- **Definición:** La representación verbal exclusiva del nombre "Crochet Manager" mediante una construcción tipográfica personalizada basada en las curvas y terminales esféricas (*ball terminals*) de la fuente `Fraunces`, complementada por un pespunte inferior.
- **Variantes Desarrolladas:**
  1. `logotipo-crochet-manager.svg`: Wordmark principal en Ciruela Artesanal (`#8E5B74`) con hebra subyacente y remates textiles artesanales.
  2. `logotipo-taller-artesanal.svg`: Wordmark institucional con tipografía condensada "CROCHET TALLER & ERP" y marco de pespunte suave.

### 3. Imagotipo (Símbolo + Texto combinados y separables)
- **Definición:** La convivencia coordinada del isotipo y el logotipo. Ambos elementos guardan proporciones áureas precisas y pueden desacoplarse según la necesidad del soporte.
- **Variantes Desarrolladas:**
  1. `imagotipo-horizontal.svg`: Isotipo de ovillo/gancho a la izquierda + Logotipo "Crochet Manager" con bajada "Micro-ERP • Control de Costos, Stock & Pedidos" a la derecha. Utilizado en el `navbar` principal y encabezados.
  2. `imagotipo-vertical.svg`: Isotipo centrado en la parte superior + Logotipo "Crochet MANAGER" y submarca centrados debajo. Ideal para tarjetas de presentación, pantalla de login, modales y portadas de reportes.

### 4. Isologo (Emblema / Sello integrado e indivisible)
- **Definición:** Fusión indisoluble donde la tipografía y el símbolo gráfico forman un solo cuerpo coherente (como una insignia, parche bordado o sello de cera). Si se retira el texto, el diseño pierde su integridad.
- **Variantes Desarrolladas:**
  1. `isologo-sello-taller.svg`: Sello circular con pespunte exterior bordado, texto arqueado sobre trayectoria SVG ("• CROCHET MANAGER • TALLER DE CONFECCIÓN •"), anillo interior dorado y silueta de ovillo/gancho en el centro.
  2. `isologo-medallon-garantia.svg`: Medallón festoneado con listón de garantía artesanal "CROCHET MANAGER - 100% HECHO A MANO", ideal para etiquetas de producto físico y certificados de autenticidad.

---

## 03 — Paleta Cromática y Tokens Semánticos ("Algodón Nórdico")

| Rol Semántico | Código Hex | Variable CSS | Significado y Aplicación |
| :--- | :---: | :--- | :--- |
| **Ciruela Artesanal** | `#8E5B74` | `--craft-primary` | Tono identitario maestro. Evoca hilazas teñidas con frutos silvestres. |
| **Ciruela Profunda** | `#75475E` | `--craft-primary-hover` | Sombras, trazos principales de contraste y estados activos. |
| **Leche de Lavanda** | `#F7EFF3` | `--craft-primary-subtle` | Relleno suave de fondos de insignia y etiquetas de cuidado. |
| **Abeto Nórdico** | `#52857C` | `--craft-secondary` | Acento botánico, garantía de materiales 100% algodón y agujas. |
| **Abeto Profundo** | `#235048` | `--craft-secondary-text` | Contraste de alto impacto accesible (>6.2:1 WCAG AA). |
| **Miel Cálida** | `#D99C52` | `--craft-accent-gold` | Detalles de ganchillos, hilo dorado de pespunte y sellos oficiales. |
| **Niebla Glaciar** | `#EBF4F2` | `--craft-secondary-subtle` | Fondos luminosos para sellos de stock e insignias. |
| **Lienzo Alabastro** | `#F8F9FB` | `--craft-bg` | Fondo natural de porcelana para aplicaciones claras. |
| **Borde Escarcha** | `#E4E8ED` | `--craft-border` | Líneas de división y costuras perimetrales. |
| **Pizarra Nórdica** | `#1E252D` | `--craft-text-main` | Tipografía de títulos principales y marcas de palabra. |

---

## 04 — Jerarquía Tipográfica de Marca

1. **Titular Display Artesanal:** Google Font **`Fraunces`** (`weights: 600, 700, 800`).
   - *Carácter:* Curvas sinuosas, serifa suave y remates redondeados (*ball terminals*) que recuerdan el punto bajo y las hebras de lana.
2. **Display Secundario y Subtítulos:** Google Font **`Outfit`** (`weights: 600, 700`).
   - *Carácter:* Geometría moderna, limpia y altamente legible para subtítulos institucionales y números de serie.
3. **Cuerpo y Microcopy:** Google Font **`Plus Jakarta Sans`** (`weights: 400, 500, 600`).
   - *Carácter:* Neutralidad equilibrada, excelente legibilidad en pantallas digitales de cualquier tamaño.

---

## 05 — Estándares Técnicos de Construcción Vectorial (SVG)

1. **viewBox Semántico y Escalable:**
   - Isotipos e Isologos: `viewBox="0 0 120 120"` o `viewBox="0 0 100 100"`.
   - Imagotipos Horizontales y Logotipos: `viewBox="0 0 340 90"`.
   - Imagotipos Verticales: `viewBox="0 0 240 200"`.
2. **Detallado Textil Artesanal:**
   - Inclusión de costuras en pespunte mediante `stroke-dasharray="3,3"` o `"4,3"` con `stroke-linecap="round"`.
   - Trazos geométricos cerrados para facilitar renderizado nítido en cualquier densidad de píxeles (Retina / 4K).
   - Uso de gradientes lineales suaves con IDs únicos para evitar colisiones en renderizado inline.
   - Accesibilidad: Etiquetas `<title>` y descripciones descriptivas dentro de cada archivo SVG.
