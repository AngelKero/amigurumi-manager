# 📚 Índice Maestro de Documentación Técnica

Bienvenido al centro de documentación técnica y arquitectura del sistema **Crochet Manager (Micro-ERP & Catálogo Textil)**.

Este repositorio de documentación está estructurado bajo los principios de **Clean Architecture & Clean Code**, garantizando que cada documento posea una única responsabilidad bien definida, libre de referencias obsoletas y perfectamente alineada con la base de datos SQLite y las interfaces de usuario.

---

## 🗺️ Mapa de Navegación de la Documentación

```
docs/
├── README.md                      # [Este archivo] Guía y mapa maestro de navegación
├── 1. Base de Datos & Modelos Relacionales
│   ├── database-schema.md         # Esquema DDL SQLite y Diccionario de Datos (EN)
│   ├── database-schema.es.md      # Esquema DDL SQLite y Diccionario de Datos (ES)
│   ├── data-model.md              # Diagrama Entidad-Relación y Estructura Física (EN)
│   ├── data-model.es.md           # Diagrama Entidad-Relación y Estructura Física (ES)
│   ├── database-testing.md        # Guía de Pruebas en Terminal SQLite y Constraints (EN)
│   └── database-testing.es.md     # Guía de Pruebas en Terminal SQLite y Constraints (ES)
├── 2. Seguridad & Control de Acceso
│   ├── auth-flow.md               # Ciclo de Sesión, RBAC y Salvaguardas Administrativas (EN)
│   └── auth-flow.es.md            # Ciclo de Sesión, RBAC y Salvaguardas Administrativas (ES)
├── 3. Contratos de API & Backend
│   ├── api-design.md              # Especificación de Endpoints REST/JSON (EN)
│   └── api-design.es.md           # Especificación de Endpoints REST/JSON (ES)
├── 4. Arquitectura de Software & Planes
│   ├── architecture-refactor-plan.md # Plan Integral de Arquitectura Modular (Clean Code)
│   └── ui-ux-database-gap-analysis.md # Auditoría de Brechas y Matriz de Cierre (100% Verificado)
├── 5. Diseño UI/UX & Heurística
│   ├── identidad-visual.md        # Manual de Identidad Visual, Arquetipos y Branding SVG
│   ├── ui-ux-skill-report.md      # Auditoría Heurística y Accesibilidad WCAG (EN)
│   ├── ui-ux-skill-report.es.md   # Auditoría Heurística y Accesibilidad WCAG (ES)
│   ├── wireframes.md              # Especificaciones de Wireframes y Flujos (EN)
│   ├── wireframes.es.md           # Especificaciones de Wireframes y Flujos (ES)
│   └── svg-assets-and-helper.md   # Catálogo de 31 SVGs y Utilidad SvgHelper (PHP)
└── archive/                       # Archivo histórico de maquetas estáticas (.html)
```

---

## 📖 Resumen Temático de Documentos

### 1. Base de Datos e Integridad Relacional
- **[database-schema.es.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/database-schema.es.md)** / **[database-schema.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/database-schema.md):**  
  Especificación canónica de las tablas `usuarios`, `creaciones` y `pedidos`, detallando todas las restricciones CHECK de SQLite (`chk_creaciones_es_sobre_encargo`, `chk_pedidos_cliente_contacto`, `chk_pedidos_estado_pago`, etc.) y la política de claves foráneas con `ON DELETE RESTRICT`.
- **[data-model.es.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/data-model.es.md)** / **[data-model.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/data-model.md):**  
  Diagrama relacional Mermaid, ciclo de vida de entidades y correspondencia con los directorios de la aplicación.
- **[database-testing.es.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/database-testing.es.md)** / **[database-testing.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/database-testing.md):**  
  Batería de comandos CLI para SQLite para verificar restricciones de longitud, valores permitidos y protección referencial en `creaciones`.

### 2. Autenticación y Control de Acceso (RBAC)
- **[auth-flow.es.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/auth-flow.es.md)** / **[auth-flow.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/auth-flow.md):**  
  Ciclo de vida de la sesión PHP (`$_SESSION['user_id']`, `rol`), integración del modal en el navbar, roles del sistema (`admin`, `artesano`, `asistente`), pantalla administrativa `usuarios.php` y regla de seguridad inviolable para la cuenta raíz.

### 3. Contratos de Endpoints (Backend & APIs)
- **[api-design.es.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/api-design.es.md)** / **[api-design.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/api-design.md):**  
  Contratos JSON estrictos para autenticación, gestión del catálogo de creaciones, pedidos públicos y manuales (con WhatsApp y anticipos), y modificación de roles de usuario.

### 4. Arquitectura de Software & Auditorías
- **[architecture-refactor-plan.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/architecture-refactor-plan.md):**  
  Documento maestro de la arquitectura modular: vistas PHP desacopladas en `views/`, estilos ITCSS en `src/css/`, módulos nativos ES6 en `src/js/` y diseño de capas del backend en `src/`.
- **[ui-ux-database-gap-analysis.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/ui-ux-database-gap-analysis.md):**  
  Informe de correspondencia entre la base de datos y la interfaz, registrando el 100% de cumplimiento de las brechas detectadas.
- **[qa-audit-report.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/qa-audit-report.md):**  
  Reporte exhaustivo de Aseguramiento de Calidad (QA), verificación de las 6 dimensiones del sistema, ratios de contraste WCAG 2.1 AA y checklist de producción.

### 5. UI/UX & Sistema de Diseño
- **[identidad-visual.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/identidad-visual.md):**  
  Manual de Identidad Visual Corporativa, definición formal de los 4 arquetipos marcarios (isotipo, logotipo, imagotipo, isologo), paleta semántica Algodón Nórdico, tipografías maestras y catálogo de assets de branding SVG en `assets/svg/branding/`.
- **[ui-ux-skill-report.es.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/ui-ux-skill-report.es.md)** / **[ui-ux-skill-report.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/ui-ux-skill-report.md):**  
  Evaluación contra 19 reglas de diseño UI/UX y estándares de contraste WCAG 2.1 AA (> 4.5:1).
- **[wireframes.es.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/wireframes.es.md)** / **[wireframes.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/wireframes.md):**  
  Representación textual de alta fidelidad de las vistas y componentes del sistema.
- **[svg-assets-and-helper.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/svg-assets-and-helper.md):**  
  Catálogo de gráficos vectoriales SVG temáticos organizados en `assets/svg/` (incluyendo `branding/` y `creaciones/`) y guía de uso del helper `SvgHelper::render()` / funciones globales `svg()` y `svg_url()`.

---

## ⚡ Convenciones de Código y Ubicación de Archivos

| Capa / Dominio | Ubicación Canónica | Convención & Tecnologías |
| :--- | :--- | :--- |
| **Vistas y Layouts** | `views/layouts/`, `views/pages/` | PHP 8.x semántico, componentes desacoplados. |
| **Componentes UI** | `views/components/` | Modales, navbar, footer, tarjetas reutilizables. |
| **Estilos CSS** | `src/css/` | Arquitectura ITCSS por capas (`01-settings` a `04-components`). |
| **Lógica JavaScript** | `src/js/`, `src/js/modules/` | Vanilla JS nativo en ES Modules (`main.js` orquestador). |
| **Helpers de Utilidad** | `src/Utils/` (PHP), `src/js/modules/currency.js` (JS) | `CurrencyHelper.php` (moneda) y `SvgHelper.php` (vector assets). |
| **Recursos Vectoriales** | `assets/svg/` | SVGs artesanales divididos en `branding`, `creaciones`, `tools`, `badges` y `decorations`. |
| **Puntos de Entrada** | `index.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`, `creaciones.php` | Controladores de vista en raíz. |
| **Base de Datos** | `database/database.sqlite`, `database/seed.sql` | SQLite 3 con `PRAGMA foreign_keys = ON;`. |
