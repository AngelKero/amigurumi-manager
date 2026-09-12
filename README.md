# 🧶 Crochet Manager | Micro-ERP & Catálogo Textil Artesanal

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Design System](https://img.shields.io/badge/Design%20System-Algodón%20Nórdico-8E5B74)](docs/design-system/README.md)
[![Tests](https://img.shields.io/badge/Tests-393%20%2F%20393%20Passing%20(100%25)-52857C)](docs/testing/README.md)
[![Architecture](https://img.shields.io/badge/Clean%20Architecture-SOLID-235048)](docs/architecture/README.md)
[![License](https://img.shields.io/badge/Dependencies-Zero%20External-D99C52)]()

Sistema web integral de comercio artesanal y gestión de taller (**Micro-ERP**) diseñado especialmente para artesanas y creadores de **crochet integral** (amigurumis, prendas de vestir, bolsos y accesorios, artículos de decoración para el hogar y línea infantil/bebé).

La plataforma opera bajo un **modelo colaborativo multi-artesano**, donde múltiples creadores independientes pueden registrarse, publicar su catálogo, controlar existencias físicas en tiempo real, calcular costos exactos de hilaza y rentabilidad por hora con herramientas de comercio ético, coordinar encargos personalizados mediante contacto directo por **WhatsApp** y administrar el catálogo colectivo bajo la identidad visual de **"Algodón Nórdico"**.

---

## 🚀 Inicio Rápido con un Solo Comando

Para levantar el servidor web local de desarrollo, abre una terminal en la raíz del proyecto y ejecuta:

```bash
php -S localhost:8000
```

Luego, abre tu navegador web en:
👉 **[http://localhost:8000](http://localhost:8000)**

*(Opcionalmente, si utilizas otra dirección local como `127.0.0.1:8000`, también es totalmente compatible).*

---

## 🛠️ Inicialización de Base de Datos (SQLite)

La base de datos SQLite preconfigurada y protegida se encuentra en `database/database.sqlite`.

Si en algún momento deseas reconstruir la base de datos o restablecer los datos semilla de prueba con sus restricciones de integridad relacional, ejecuta el instalador CLI seguro:

```bash
php setup.php
```

> [!NOTE]
> **Seguridad de Inicialización:** `setup.php` está blindado para ejecutarse **únicamente desde la terminal (CLI)** (`php_sapi_name() === 'cli'`). Los intentos de ejecución remota o vía navegador web son bloqueados automáticamente para proteger la integridad de los datos.

---

## 🔑 Credenciales de Acceso para Pruebas (Matriz RBAC)

La base de datos incluye 3 usuarios semilla para probar los diferentes niveles de privilegios:

| Usuario | Contraseña | Rol del Sistema | Alcance y Privilegios |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | `admin` | **Administrador Global & Artesano Titular**: Control total de plataforma, directorio de usuarios, cambio de roles RBAC, restablecimiento de claves, y gestión de piezas propias o de terceros. *(Protegido contra bloqueo en ID #1)*. |
| **`artesana_ana`** | `artesana123` | `artesano` | **Creadora Textil Independiente**: Registro y edición de creaciones propias, gestión de stock e inventario, ajuste de precios/costos, y control de encargos de clientes. *(Protegido contra IDOR)*. |
| **`asistente_leo`** | `asistente123` | `asistente` | **Asistente de Plataforma**: Consulta de inventarios, apoyo operativo en logística y seguimiento de entregas sin facultades de alteración de catálogo ni privilegios RBAC. |

*El inicio de sesión se realiza cómodamente desde el botón **"Iniciar Sesión"** en la barra de navegación superior o haciendo clic en el sello del creador autenticado.*

---

## 📱 Módulos & Vistas del Sistema

| Vista / Archivo | Tipo de Acceso | Descripción Funcional |
| :--- | :---: | :--- |
| **[`index.php`](index.php)** | 🌐 Público | **Catálogo Colectivo**: Cuadrícula responsiva de creaciones con filtros textiles, selector de categoría, rango dinámico de precios (Min/Max), filtro por autor artesano, badges de disponibilidad inmediata y etiquetas de *"Bajo Encargo"*. |
| **[`detalle.php`](detalle.php?id=1)** | 🌐 Público | **Ficha Técnica & Solicitud de Pedido**: Especificación detallada de medidas, fibras textiles, cuidados, sello de creador verificado y modal interactivo de compra/encargo con stepper acotado. |
| **[`creaciones.php`](creaciones.php)** | 🔒 Artesano / Admin | **Taller de Inventario & Creaciones**: Panel con métricas KPI en vivo, ajuste in-situ de existencias, toggle interactivo de encargo, ficha de inspección técnica modal y baja lógica con salvaguarda referencial. |
| **[`formulario.php`](formulario.php)** | 🔒 Artesano / Admin | **Alta & Edición de Creaciones**: Formulario de 10 campos con subida de fotografías reales, fallback vectorial SVG y simulador de margen de beneficio con **escudo protector y desenfoque** hasta completar campos obligatorios. |
| **[`pedidos.php`](pedidos.php)** | 🔒 Artesano / Admin | **Control de Pedidos & Encargos**: Cuadrícula de tarjetas responsivas 3x/2x pespunteadas, badges de cobro tri-estado (*Pendiente*, *Anticipo 50%*, *Liquidado*), enlace directo a WhatsApp y cancelación con restitución de inventario. |
| **[`usuarios.php`](usuarios.php)** | 🔒 Solo Admin | **Directorio de Creadores & Roles**: Gestión del equipo con filtros de estado (*activos*, *inactivos*, *todos*), modificación de roles con salvaguarda en ID #1, recuperación de contraseñas y reactivación de cuentas. |

---

## 🏛️ Arquitectura del Sistema (Clean Architecture & Modular)

El backend reside exclusivamente en el directorio `app/`. El directorio `src/` queda 100% reservado al frontend (`src/css/`, `src/js/`) con **0 archivos PHP**, garantizando separación estricta de responsabilidades:

```
proyecto-web/
├── app/                            # 🟢 CAPA BACKEND PHP EXCLUSIVA (Protegida por .htaccess)
│   ├── autoload.php                # Autocargador PSR-4 nativo sin Composer & ErrorHandler global
│   ├── config.php                  # Configuración centralizada (claves secretas, TTL tokens, límites)
│   ├── Core/                       # Infraestructura: Database (Singleton PDO), Config, TokenManager,
│   │                               # ErrorHandler (cero fugas HTML, JSON 500), Request, Response
│   ├── Repositories/               # Capa de Persistencia: 100% de consultas preparadas PDO SQLite
│   │                               # (CreacionRepository, PedidoRepository, UsuarioRepository)
│   ├── Services/                   # Lógica de Negocio: validaciones de dominio, formato monetario dual,
│   │                               # transacciones atómicas, ciclo de fotos y salvaguardas IDOR
│   ├── Middleware/                 # Guardianes: AuthGuard (Bearer token) y RoleGuard (RBAC)
│   └── Utils/                      # Helpers de Servidor: CurrencyHelper, PaginationHelper, SvgHelper
├── views/                          # Sistema de plantillas y componentes modulares PHP
│   ├── layouts/main.php            # Layout maestro (<head>, scripts, decoraciones y modals)
│   ├── components/                 # Barra de navegación, footer, tarjetas y modales
│   └── pages/                      # Contenido específico de cada vista (catalogo, creaciones, etc.)
├── src/                            # 🔵 EXCLUSIVO FRONTEND (0 archivos PHP)
│   ├── css/                        # Estilos modulares organizados por capas ITCSS (Algodón Nórdico)
│   └── js/                         # Scripts cliente desacoplados en ES Modules (main.js + modules/)
├── api/                            # 🌐 Controladores REST Delgados (JSON APIs por recurso)
│   ├── auth/                       # login.php, logout.php, me.php, cambiar-password.php
│   ├── creaciones/                 # index.php, artesanos.php, detalle.php, crear.php, actualizar.php...
│   ├── pedidos/                    # index.php, solicitar.php, crear.php, cambiar-estado.php, cancelar.php
│   └── usuarios/                   # index.php, crear.php, cambiar-rol.php, actualizar.php, eliminar.php...
├── database/                       # Persistencia física SQLite protegida (seed.sql, database.sqlite)
├── uploads/                        # Directorio para fotografías reales de creaciones (subidas protegidas)
├── tests/                          # 🧪 Suites automatizadas de pruebas CLI nativas
├── logs/                           # 🪵 Trazas de ejecución CLI y volcados HTTP (protegido por .htaccess)
└── docs/                           # 📚 Documentación técnica modular Diátaxis (Zero Monoliths)
    ├── README.md                   # Hub maestro de navegación técnica
    ├── api/                        # Especificación de endpoints REST (auth, creaciones, pedidos, usuarios)
    ├── architecture/               # Ciclo de vida (Fases 1 a 5), contratos, seguridad y 15 ADRs
    ├── database/                   # Modelo físico relacional, diagrama ERD Mermaid y pruebas DDL
    ├── design-system/              # Tokens "Algodón Nórdico", identidad visual y auditorías WCAG
    └── testing/                    # Protocolo en 3 niveles y reportes de QA por subfase
```

---

## 🗺️ Ciclo de Vida del Proyecto: Hoja de Ruta de Fases

El desarrollo se rige por **5 fases secuenciales estrictas** bajo el protocolo de compuertas de calidad (*Quality Gates*):

👉 **[Consulta la Documentación Completa de Todas las Fases](docs/architecture/proceso-desarrollo-fases.md)**

| Fase | Denominación | Estado | Enfoque Principal |
| :---: | :--- | :---: | :--- |
| **Fase 0** | **Descubrimiento & Modelo Conceptual** | ✅ **Completada** | Plataforma colaborativa multi-artesano, autonomía operativa y modelo relacional tripartito. |
| **Fase 1** | **Base de Datos & Seguridad CLI** | ✅ **Completada** | DDL SQLite con restricciones CHECK y FKs estrictas, script CLI `setup.php` y blindaje `.htaccess`. |
| **Fase 2** | **Layout, UI & Sistema de Componentes** | ✅ **Completada** | Sistema de diseño "Algodón Nórdico", componentes PHP desacoplados, ITCSS, ES Modules, rediseño de pedidos con WhatsApp y generalización a crochet integral. Score QA: 98.5/100. |
| **Fase 3** | **Backend & Clean Architecture (`app/`)** | 🔄 **En Curso** | Aislamiento en `app/`, 6 subfases secuenciales con compuertas de testing en 3 niveles: <br>• **3.1 Infraestructura Nuclear:** ✅ Aprobado (93/93 aserciones) <br>• **3.2 Autenticación Stateless:** ✅ Aprobado (69/69 aserciones) <br>• **3.3 Gestión de Usuarios & RBAC:** ✅ Aprobado (105/105 aserciones) <br>• **3.4 Catálogo & Creaciones:** ✅ Aprobado (126/126 aserciones) <br>• **3.5 Pedidos & Transacciones Atómicas:** ⏳ *En desarrollo* <br>• **3.6 Seguridad & Regresión:** ⏳ *Pendiente* |
| **Fase 4** | **Cableado Fullstack Asíncrono** | ⏳ **Pendiente** | Integración reactiva AJAX `fetch()` entre `src/js/modules/` y la API REST, con Bearer tokens y feedback visual. |
| **Fase 5** | **Documentación, Rendimiento & Entrega** | 🔄 **En Curso** | Mantenimiento de Clean Documentation (Zero Monoliths), catálogo de 15 ADRs, auditoría WCAG 2.1 AA y empaquetado final. |

---

## 🧪 Pruebas Automatizadas & Protocolo en 3 Niveles

El backend incluye una batería completa de pruebas nativas CLI y HTTP sin dependencias de Composer, acumulando **393 aserciones aprobadas al 100%**:

```bash
# Ejecutar suite individual por subfase:
php tests/test-subfase-3.1.php   # Core, Base de Datos, Config, Tokens y Helpers (93 OK)
php tests/test-subfase-3.2.php   # Autenticación, Repositorio de Usuarios y Middleware (69 OK)
php tests/test-subfase-3.3.php   # Ciclo de Usuarios, Bajas Lógicas y Roles RBAC (105 OK)
php tests/test-subfase-3.4.php   # Catálogo, Creaciones, Fotos y Protección IDOR (126 OK)

# Ejecutar batería completa acumulada:
php tests/test-subfase-3.1.php && php tests/test-subfase-3.2.php && php tests/test-subfase-3.3.php && php tests/test-subfase-3.4.php
```

Los reportes ejecutivos formales con evidencias JSON y estado de SQLite se encuentran en:
- [`docs/testing/subfase-3.1-core.md`](docs/testing/subfase-3.1-core.md)
- [`docs/testing/subfase-3.2-auth.md`](docs/testing/subfase-3.2-auth.md)
- [`docs/testing/subfase-3.3-usuarios.md`](docs/testing/subfase-3.3-usuarios.md)
- [`docs/testing/subfase-3.4-creaciones.md`](docs/testing/subfase-3.4-creaciones.md)
- [`docs/testing/qa-audit-report.md`](docs/testing/qa-audit-report.md)

---

## 🎨 Sistema de Diseño: "Algodón Nórdico"

El frontend implementa una estética textil escandinava cálida y de alta gama:
- **Paleta Cromática Oficial:**
  - **Ciruela Nórdico** (`#8E5B74` / `--craft-primary`): Botones primarios, enlaces activos y acentos de marca.
  - **Abeto Glaciar** (`#52857C` / `--craft-secondary`) con texto de alto contraste (`#235048` / `--craft-secondary-text`): Badges de stock disponible con ratio $>6.2:1$.
  - **Miel Nórdica** (`#D99C52` / `--craft-accent-gold`): Pedidos pendientes, estados intermedios y alertas artesanales.
  - **Lienzo Porcelana** (`#F8F9FB` / `--craft-bg`): Fondo suave que sustituye blancos deslumbrantes o grises toscos.
- **Detallado Textil Artesanal:**
  - Inset Running Seams (`.card-stitched`): Bordes hilvanados pespunteados interiores en tarjetas y módulos.
  - Botones Bordados (`.btn-craft-stitched`): Contorno pespunteado interior con hilo suave.
  - Etiquetas de Cuidado Tejidas (`.badge-textile-tag`): Píldoras con ojal y costura lateral.
  - Cintas Hilvanadas (`.breadcrumb-craft-ribbon`): Migas de pan con aspecto de cinta textil.
  - Sello Oficial del Taller (`.badge-artisan-seal`): Distintivo en pergamino cálido con bordado dorado.
- **Jerarquía Tipográfica:**
  - **Fraunces** (`600`, `700`, `800`): Display artesanal con serifa cálida para títulos principales y cabeceras de marca.
  - **Outfit** (`600`, `700`, `800`): Subtítulos geométricos, métricas KPI y cifras destacadas.
  - **Plus Jakarta Sans** (`400`, `500`, `600`): Lectura fluida para párrafos, fichas técnicas, tablas y formularios.
- **Accesibilidad:** Ratio de contraste verificado superior a 4.5:1 (alcanzando hasta 14.2:1), navegación responsiva *mobile-first* con tarjetas adaptativas en pantallas pequeñas.

---

## 🔒 Seguridad & Salvaguardas de Integridad

1. **Aislamiento de Persistencia (.htaccess):** Peticiones web directas a archivos `.sqlite`, `.sql`, `.md`, carpetas `app/`, `database/`, `memory-bank/`, `logs/` o `tests/` son rechazadas con HTTP 403 Forbidden.
2. **Cero Fugas HTML:** `ErrorHandler` captura errores y excepciones en producción, purga el búfer con `ob_end_clean()` y emite respuestas homogéneas JSON 500.
3. **Stateless Bearer Tokens:** Autenticación sin estado con tokens HMAC-SHA256 (24h TTL) validados en tiempo constante con `hash_equals()`.
4. **Regla Universal de Baja Lógica (Zero Physical Deletions):** Quedan prohibidas las sentencias `DELETE FROM` en tablas de negocio. Todas las bajas son lógicas (`activo = 0`, `eliminado_en = timestamp`).
5. **Preservación de Fotografías en Bajas Lógicas (ADR-008):** El archivo físico de una imagen solo se borra con `unlink()` al reemplazarla en edición; **en bajas lógicas la foto se conserva** para preservar los pedidos históricos.
6. **Protección IDOR Multi-Artesano (ADR-007):** Ningún artesano puede modificar ni eliminar creaciones de otro autor; el sistema valida la propiedad del recurso (`artesano_id === user.id`) retornando HTTP 403 Forbidden.
7. **Salvaguarda de Administrador Raíz (ID #1):** La cuenta `@admin` (ID 1) está blindada contra cambio de rol o baja lógica.
8. **Prevención de Concurrencia SQLite:** `PRAGMA busy_timeout = 5000;` en cada conexión PDO evita bloqueos inmediatos de base de datos bajo peticiones simultáneas.

---

## 📚 Centro de Documentación Técnica (Documentation Hub)

La documentación se organiza bajo el principio **Zero Monoliths** y el estándar Diátaxis:

- 🏛️ **[Arquitectura & Decisiones](docs/architecture/README.md):** Principios Clean Architecture, contratos de capas ([`contracts.md`](docs/architecture/contracts.md)), seguridad ([`security.md`](docs/architecture/security.md)), ciclo de vida de fases ([`proceso-desarrollo-fases.md`](docs/architecture/proceso-desarrollo-fases.md)) y el registro de 15 Decisiones de Arquitectura ([ADR-001 a ADR-015](docs/architecture/decisiones/README.md)).
- 🌐 **[Especificación API REST](docs/api/README.md):** Estándares HTTP, envelope JSON y contratos para [Auth](docs/api/auth.md), [Creaciones](docs/api/creaciones.md), [Pedidos](docs/api/pedidos.md) y [Usuarios](docs/api/usuarios.md).
- 🗄️ **[Base de Datos & DDL](docs/database/README.md):** Diagrama ERD físico en Mermaid, especificación de columnas y restricciones ([`schema.md`](docs/database/schema.md)) y suite de pruebas de integridad ([`testing.md`](docs/database/testing.md)).
- 🎨 **[Sistema de Diseño](docs/design-system/README.md):** Manual de identidad visual ([`brand-identity.md`](docs/design-system/brand-identity.md)), catálogo de activos vectoriales ([`svg-assets.md`](docs/design-system/svg-assets.md)) y auditorías de accesibilidad ([`audits.md`](docs/design-system/audits.md)).
- 🧪 **[Reportes de Pruebas](docs/testing/README.md):** Protocolo de testing en 3 niveles y reportes de QA por subfase.

---

## 📋 Requisitos del Sistema

- **PHP 8.0** o superior con extensiones estándar `pdo_sqlite` y `mbstring`.
- **Navegador web moderno** (Chrome, Firefox, Safari o Edge) con soporte para JavaScript ES6+.
- **Servidor Web:** Compatible con el servidor integrado de PHP (`php -S`) y servidores de producción Apache/Nginx con módulo de reescritura activado.
- **Cero dependencias:** No requiere Node.js, npm ni Composer en tiempo de ejecución.
