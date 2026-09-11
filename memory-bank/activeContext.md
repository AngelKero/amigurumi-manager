# Active Context: Amigurumi Micro-ERP & Catalog

## Current Task: Disociación de "Nórdico" (Exclusividad para el Tema CSS / Sistema de Diseño) (Completado y Verificado)

- **User Request:**
  - *"Debes quitar cualquie referencia a que es nordico, lo nordico solo es el tema de css no los productos u otra cosa"*

- **Principio Establecido:**
  - El término "Algodón Nórdico" pertenece estricta y únicamente al **tema y sistema de diseño CSS** (paleta de colores, tokens, tipografías y pespuntes).
  - Los **productos (amigurumis), descripciones, materiales, colecciones, talleres artesanos y garantías de confección** son 100% artesanales y NO deben presentarse como "nórdicos".

- **Ajustes Realizados:**
  - **Catálogo (`views/pages/catalogo_content.php`):**
    - `Admin (Taller Nórdico)` &rarr; `Admin (Taller Principal)`.
    - `Colección Textil Algodón Nórdico` &rarr; `Colección Textil Artesanal`.
    - `lanas nórdicas y algodón mercerizado` &rarr; `hilazas suaves y algodón mercerizado`.
    - `Colección Artesanal Amigurumi Algodón Nórdico` &rarr; `Colección Artesanal de Amigurumis`.
    - `Favorito del Taller • Colección Nórdica` &rarr; `Favorito del Taller • Colección Artesanal`.
    - `Colección Nórdica 2026` &rarr; `Colección Textil 2026`.
    - Dropdown de filtro `@admin (Taller Nórdico)` &rarr; `@admin (Taller Principal)`.
  - **Detalle (`views/pages/detalle_content.php`):**
    - Código de taller `#TT-001-NORDIC` &rarr; `#TT-001-ARTISAN`.
    - `Inspirado en leyendas nórdicas...` &rarr; `Inspirado en criaturas fantásticas de fuego sereno...`.
  - **Footer (`views/components/footer.php`):**
    - `Compromiso de Calidad Nórdica` &rarr; `Compromiso de Calidad Artesanal`.
    - `Tejido punto a punto con lana nórdica` &rarr; `Tejido punto a punto con amor artesanal`.
    - `Algodón Nórdico v2.7` &rarr; `Micro-ERP Artesanal v2.7`.
  - **Gestión de Usuarios (`views/pages/usuarios_content.php` & `src/js/modules/users.js`):**
    - Subtítulo `Taller Textil Nórdico` &rarr; `Taller Textil Principal`.
  - **Formulario (`views/pages/formulario_content.php`):**
    - `distintivo morado nórdico` &rarr; `distintivo morado artesanal`.
  - **SVGs (`assets/svg/`):**
    - `osito-nordico.svg`: Cinta inferior cambiada a `🐻 Osito Artesanal • Lana Cardada`.
    - `madeja-textil.svg`: Faja de papel rotulada cambiada de `Nórdico` a `Artesanal`.
  - **Documentación (`docs/svg-assets-and-helper.md`):**
    - Ajustadas descripciones de recursos vectoriales para eliminar referencias a lanas y maderas nórdicas en productos.

- **Verificaciones Ejecutadas:**
  - `php -l` limpio en todas las vistas y componentes modificados (0 errores).
  - `node --check` limpio en `src/js/modules/users.js` (0 errores).
  - `git diff` verificado exhaustivamente: 0 referencias espurias a "nórdico" en productos o textos de negocio.

