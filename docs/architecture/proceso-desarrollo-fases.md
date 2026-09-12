# Proceso de Desarrollo: Ciclo de Vida Integral del Proyecto (Fases 1 a 5)

[← Volver al Índice de Arquitectura](./README.md) • [Ver Plan Detallado de la Fase 3](./phase-3-plan.md) • [Ver Registro de ADRs](./decisiones/README.md)

Este documento describe la **hoja de ruta global, metodología de ingeniería, fases secuenciales y compuertas de calidad** para el desarrollo completo de **Crochet Manager** (Plataforma Colaborativa & Micro-ERP de Creaciones en Crochet).

---

## 1. Resumen Ejecutivo del Ciclo de Vida

El proyecto sigue un enfoque **Database-First y Clean Architecture**, estructurado en **5 fases secuenciales estrictas** precedidas por una fase de descubrimiento y diseño conceptual. Cada fase cuenta con objetivos específicos, entregables auditables y una compuerta de verificación (*Quality Gate*) que debe superarse antes de avanzar.

```
┌────────────────────────────────────────────────────────────────────────┐
│ Fase 0: Arquitectura Relacional, Descubrimiento & Modelo Colaborativo   │ [COMPLETADA]
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ Fase 1: Base de Datos Relacional SQLite & Seguridad CLI                │ [COMPLETADA]
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ Fase 2: Layout, UI, Componentes PHP & Sistema "Algodón Nórdico"        │ [COMPLETADA]
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ Fase 3: Backend & Clean Architecture en app/ (6 Subfases Secuenciales) │ [EN CURSO: 3.1-3.4 OK]
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ Fase 4: Cableado Fullstack & Operaciones Asíncronas (AJAX ↔ REST)      │ [PENDIENTE]
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ Fase 5: Documentación Diátaxis, Rendimiento & Entrega Final            │ [CONTINUA / FINAL]
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Matriz Comparativa de Fases

| Fase | Denominación del Hito | Estado | Enfoque Principal | Entregables Clave | Compuerta de Calidad |
| :---: | :--- | :---: | :--- | :--- | :--- |
| **0** | **Descubrimiento & Modelo Conceptual** | ✅ **Completado** | Definición de dominio colaborativo multi-artesano | Requerimientos, reglas heurísticas, modelo de datos tripartito | Aprobación de alcance y modelo de negocio |
| **1** | **Base de Datos & Seguridad CLI** | ✅ **Completado** | Persistencia SQLite 3 e integridad referencial | `database/seed.sql`, `setup.php`, `database.sqlite`, `.htaccess` | `PRAGMA integrity_check` (ok), 0 violaciones FK |
| **2** | **Layout, UI & Sistema de Componentes** | ✅ **Completado** | Frontend modular, ITCSS y "Algodón Nórdico" | `views/layouts/`, `views/components/`, `src/css/`, `src/js/` | Sintaxis PHP/JS limpia, WCAG 2.1 AA, Score 98.5/100 |
| **3** | **Backend & Clean Architecture (`app/`)** | 🔄 **En Curso** (3.1-3.4 ✅) | Lógica de negocio, Repositorios, Servicios y REST | `app/Core/`, `app/Repositories/`, `app/Services/`, `api/` | Testing 3 niveles (CLI + HTTP + QA Report), Sign-off |
| **4** | **Cableado Fullstack Asíncrono** | ⏳ **Pendiente** | Integración reactiva AJAX/fetch $\leftrightarrow$ API REST | Conexión de `src/js/modules/` con `api/`, feedback UI | Browser Testing E2E, 0 errores 500, toasts reactivos |
| **5** | **Documentación, Rendimiento & Entrega** | 🔄 **En Curso** | Clean Documentation (Zero Monoliths) y despliegue | `docs/` (5 dominios), manual CLI `php -S`, auditoría final | Navegación 100% funcional, entrega formal |

---

## 3. Detalle Exhaustivo por Fase

---

### Fase 0: Arquitectura Relacional, Descubrimiento & Modelo Colaborativo

**Estado:** ✅ **Completado & Aprobado**

#### Objetivo:
Definir con precisión el modelo de negocio, los actores del sistema y los principios rectores de la aplicación, transitando de una noción inicial de taller centralizado hacia una **Plataforma Colaborativa y Micro-ERP** abierta para creadores textiles independientes de crochet integral.

#### Decisiones y Principios Rectores:
1. **Autonomía Total de los Creadores:** La plataforma no controla qué, cómo ni cuándo tejen los artesanos. Cada creador gestiona sus piezas, tiempos de confección y stock de forma soberana.
2. **Erradicación de Promesas de Fábrica:** Se eliminan compromisos de taller centralizado (como plazos rígidos universales de 5 a 7 días). Los acuerdos de personalización y tiempos de entrega se coordinan directamente entre cliente y creador.
3. **Calidad Centrada en la Plataforma:** Las garantías del sistema se focalizan en lo que la plataforma provee y audita:
   - **Fichas Técnicas Transparentes:** Especificación rigurosa de dimensiones, fibras textiles y cuidados.
   - **Contacto Directo:** Enlace ágil vía WhatsApp directo con el autor de cada creación.
   - **Comercio Ético:** Herramientas de costeo y simuladores de rentabilidad para garantizar precios justos que valoran la labor manual.
   - **Autoría Verificada:** Perfiles de creadores transparentes con integridad referencial en base de datos.
4. **Modelo Relacional Tripartito:** Usuarios (`usuarios`), Catálogo e Inventario (`creaciones`) y Encargos/Ventas (`pedidos`).

#### Entregables:
- Especificación de requerimientos y reglas heurísticas de negocio.
- Documentos de contexto inicial en `memory-bank/projectbrief.md` y `memory-bank/productContext.md`.

---

### Fase 1: Implementación, Siembra & Pruebas de Base de Datos Relacional SQLite

**Estado:** ✅ **Completado & Verificado**

#### Objetivo:
Construir una base de datos relacional robusta, portátil y con integridad referencial estricta en SQLite 3, blindada contra accesos web directos y ejecuciones indebidas.

#### Componentes & Arquitectura:
- **`database/seed.sql`:** Definición DDL estricta de las 3 tablas del sistema:
  - `usuarios`: Autenticación, roles RBAC (`admin`, `artesano`, `asistente`), control de baja lógica (`activo`, `eliminado_en`) y restricción CHECK de longitud de username.
  - `creaciones`: Catálogo e inventario, clave foránea `CONSTRAINT fk_creaciones_artesano FOREIGN KEY (artesano_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE`, restricciones CHECK en dimensiones, precio, costo y horas de tejido, y control de baja lógica.
  - `pedidos`: Encargos y compras, clave foránea `CONSTRAINT fk_pedidos_creacion FOREIGN KEY (creacion_id) REFERENCES creaciones(id) ON DELETE RESTRICT ON UPDATE CASCADE`, restricciones CHECK de cantidad, estados de pedido (`Pendiente`, `En Proceso`, `Entregado`, `Cancelado`), estados de pago (`Pendiente`, `Anticipo 50%`, `Liquidado`), notas y baja lógica.
  - **Índices de Rendimiento:** Índices específicos para optimizar consultas de catálogo, filtros por artesano, categoría, stock y estado activo (`idx_*`).
- **`setup.php`:** Inicializador seguro de base de datos restringido exclusivamente al entorno de línea de comandos (`php_sapi_name() === 'cli'`). Impide de forma absoluta cualquier reinicio accidental o malicioso de la base de datos vía navegador web.
- **`database/database.sqlite`:** Archivo físico de base de datos alojado en directorio aislado.
- **`.htaccess`:** Reglas de seguridad Apache que bloquean con HTTP 403 Forbidden cualquier acceso directo a archivos `.sqlite`, `.sql`, `.md`, carpetas `database/`, `app/`, etc.

#### Compuerta de Calidad:
- Ejecución limpia de `php setup.php` con código de salida 0.
- Verificación con `PRAGMA integrity_check;` $\rightarrow$ `ok`.
- Verificación con `PRAGMA foreign_key_check;` $\rightarrow$ 0 violaciones.
- Documentación completa en [`docs/database/`](../database/README.md) ([`schema.md`](../database/schema.md) y [`testing.md`](../database/testing.md)).

---

### Fase 2: Layout, UI, Sistema de Componentes PHP & Design System "Algodón Nórdico"

**Estado:** ✅ **Completado & Verificado** (Incluye Refactors 2.5, 2.6 y 2.7)

#### Objetivo:
Diseñar e implementar una interfaz gráfica web responsiva, estéticamente sobresaliente, accesible (WCAG 2.1 AA) y profundamente inspirada en la calidez táctil del tejido artesanal.

#### Evolución por Sub-Hitos:
1. **Hito 2.1 – 2.4 (Maquetación Base & Identidad):**
   - Sistema de diseño **"Algodón Nórdico"** con paleta oficial: Ciruela Nórdico (`#8E5B74`), Abeto Glaciar (`#52857C` con texto de alto contraste `#235048`), Miel Nórdica (`#D99C52`) y Lienzo Porcelana (`#F8F9FB`).
   - Tipografía canónica: **Fraunces** para display y títulos de impacto artesanal, **Outfit** para métricas y subtítulos geométricos, y **Plus Jakarta Sans** para lectura y formularios.
   - Pespuntes textiles: Bordes hilvanados (`.card-stitched`), botones bordados (`.btn-craft-stitched`), etiquetas de cuidado tejidas (`.badge-textile-tag`) y cintas de navegación (`.breadcrumb-craft-ribbon`).
2. **Subfase 2.5 (Componentización PHP & Modularidad ITCSS):**
   - Desacoplamiento en sistema de vistas modular: `views/layouts/main.php`, componentes reutilizables en `views/components/` (navbar, sidebar, cards, modales, footer) y controladores de página en `views/pages/`.
   - Arquitectura CSS por capas ITCSS en `src/css/` (01-settings, 02-tools, 03-generic, 04-components).
   - Modularidad cliente en Vanilla JavaScript mediante ES Modules en `src/js/modules/`.
3. **Subfase 2.6 (Completitud UI/UX & Gestión Administrativa):**
   - Pantalla de gestión de creadores y roles (`usuarios.php`, `users.js`).
   - Pre-poblado inteligente en edición de piezas (`formulario.php?id=X`).
   - Modal de eliminación con salvaguarda explicativa de integridad referencial `ON DELETE RESTRICT`.
   - Estado vacío (*empty state*) en catálogo e inventario.
   - Pedidos manuales con filtros reactivos y cálculo de KPIs en tiempo real.
4. **Subfase 2.7 (Cierre de Brechas UI/UX y Base de Datos):**
   - Filtros avanzados de catálogo: Rango de precios dinámico (Min/Max) y filtro por autor artesano con sincronización bidireccional.
   - Helper universal de moneda en frontend (`currency.js`) garantizando cero discrepancias por punto flotante.
   - Sincronización total de DDL (`cliente_contacto`, `estado_pago`, `es_sobre_encargo`).
   - Modal interactivo de modificación de roles RBAC con salvaguarda inviolable para el administrador raíz (ID #1).
5. **Rediseño de Pedidos:** Sustitución de tablas rígidas por cuadrícula de tarjetas artesanales responsivas 3x/2x (`.card-admin-pedido`), cabecera luminosa con sello oficial, badges tri-estado de pago (`Pendiente`, `Anticipo 50%`, `Liquidado`) y botón directo de contacto por WhatsApp.
6. **Generalización a Crochet Integral & Renombrado Global:**
   - Expansión de amigurumis a 5 disciplinas de crochet (Prendas de vestir, Bolsos/Accesorios, Hogar/Decoración, Amigurumis, Bebé/Infantil).
   - Migración de tamaño numérico rígido a especificación flexible de dimensiones en texto (`chk_creaciones_dimensiones`).
   - Renombrado global en base de datos (`amigurumis` $\rightarrow$ `creaciones`), vistas (`creaciones.php`, `piezas.php`), estilos y scripts. Erradicación del 100% de archivos con nombre obsoleto.
7. **Escudo Protector en Simulador:** Desenfoque profundo (`blur(24px)`) y escudo protector en `formulario.php` que oculta el cálculo de margen y rentabilidad hasta completar los campos obligatorios de costo, precio y horas.
8. **Auditoría Integral de Calidad:** Evaluación exhaustiva en 6 dimensiones con calificación **98.5 / 100** documentada en [`docs/testing/qa-audit-report.md`](../testing/qa-audit-report.md).

#### Compuerta de Calidad:
- Verificación de sintaxis PHP: 100% de archivos con `php -l` (0 errores).
- Verificación de sintaxis JS: 100% de módulos con `node --check` (0 errores).
- Verificación visual end-to-end con browser subagent y contrastes de color WCAG 2.1 AA ($>4.5:1$ en todos los elementos interactivos).

---

### Fase 3: Backend & Clean Architecture en `app/` (6 Subfases Secuenciales)

**Estado:** 🔄 **En Curso (Subfases 3.1, 3.2, 3.3 y 3.4 Completadas; 3.5 y 3.6 Pendientes)**

#### Objetivo:
Implementar una capa de backend modular, desacoplada, segura y tipada en `app/` (`src/` reservado exclusivamente a frontend), organizada bajo principios de Clean Architecture y SOLID, gobernada por un protocolo estricto de pruebas en 3 niveles y compuertas de detención y firma explícita (*Sign-off*).

#### Separación Física Estricta de Capas:
- `app/Core/`: Infraestructura nuclear (Base de datos Singleton, Manejo de Configuración, Token Manager, Error Handler, Request y Response).
- `app/Repositories/`: Capa de persistencia. **El 100% de las consultas SQL parametrizadas reside exclusivamente aquí**.
- `app/Services/`: Reglas de negocio y casos de uso. Totalmente desacopladas de formatos HTTP.
- `app/Middleware/`: Guardianes de seguridad (`AuthGuard` para Bearer tokens, `RoleGuard` para RBAC).
- `app/Utils/`: Utilidades de servidor (`CurrencyHelper`, `PaginationHelper`, `SvgHelper`).
- `api/`: Controladores REST delgados organizados en subcarpetas temáticas (`api/auth/`, `api/creaciones/`, `api/pedidos/`, `api/usuarios/`).

#### Las 6 Subfases Secuenciales:
1. **Subfase 3.1: Infraestructura Nuclear & Base** (✅ **Completado: 93/93 Aserciones OK**)
   - Autocargador PSR-4 nativo sin Composer (`autoload.php`).
   - Configuración estática centralizada (`config.php` y `Config`).
   - Conexión PDO Singleton SQLite con `PRAGMA foreign_keys = ON;` y `PRAGMA busy_timeout = 5000;` para prevenir bloqueos por concurrencia.
   - `ErrorHandler` con captura global y purga de buffers (`ob_end_clean()`) garantizando CERO fugas de HTML y respuesta estándar JSON 500.
   - Abstracciones `Request` (compatible con FastCGI Bearer tokens) y `Response` con resolución preflight CORS `OPTIONS` (204 No Content).
   - `TokenManager` con tokens Bearer HMAC-SHA256 (24h TTL) y comparación en tiempo constante con `hash_equals()`.
   - Utilidades de servidor: `PaginationHelper`, `CurrencyHelper` (centavos enteros $\leftrightarrow$ MXN) y `SvgHelper`.
2. **Subfase 3.2: Autenticación Stateless & Bearer Middleware** (✅ **Completado: 69/69 Aserciones OK**)
   - `UsuarioRepository` con consultas preparadas y salvaguarda para el usuario raíz ID #1.
   - `AuthService` con validación de credenciales bcrypt, mitigación de ataques de tiempo (*timing attacks*) mediante dummy hash, emisión de tokens y autoservicio de cambio de contraseña (`changePassword()`).
   - `AuthGuard` y `RoleGuard` (`admin`, `artesano`, `asistente`).
   - Controladores en `api/auth/`: `login.php`, `logout.php`, `me.php`, `cambiar-password.php`.
3. **Subfase 3.3: Gestión de Usuarios, Roles RBAC & Bajas Lógicas** (✅ **Completado: 105/105 Aserciones OK**)
   - Regla universal de borrado lógico en base de datos: `UPDATE usuarios SET activo = 0, eliminado_en = datetime(...)`. Cero eliminaciones físicas.
   - `UsuarioService` con 7 operaciones: listado paginado con filtro de estado (`?estado=activos|inactivos|todos`), alta de creador, modificación de rol con salvaguarda ID #1, actualización de username, restablecimiento administrativo de contraseña (manual o temporal autogenerada), baja lógica con salvaguardas (auto-eliminación, creaciones activas 409, cuenta ya inactiva 409) y reactivación formal de cuentas.
   - Suite completa de controladores en `api/usuarios/`.
4. **Subfase 3.4: Catálogo, Creaciones & Ciclo de Vida de Imágenes** (✅ **Completado: 126/126 Aserciones OK**)
   - `CreacionRepository`: Consultas de catálogo paginado, filtros combinables (categoría, precio min/max, búsqueda, encargo, stock), orden dinámico, y agregación de artesanos activos para `#filterArtisan` (**ADR-014**).
   - `CreacionService`: Validaciones de dominio de 10 campos de la ficha técnica, enriquecimiento monetario dual, fallback automático a vector SVG temático de `assets/svg/piezas/`, ciclo de vida de imágenes (reemplazo elimina foto anterior con `unlink()`; **baja lógica preserva la foto físicamente sin `unlink()`** para no corromper pedidos históricos - **ADR-008**), protección IDOR multi-artesano (**ADR-007**) y reactivación (**ADR-015**).
   - 9 controladores REST delgados en `api/creaciones/`.
5. **Subfase 3.5: Pedidos, Transacciones Atómicas & Notificaciones WhatsApp** (⏳ *Siguiente Subfase*)
   - `PedidoRepository`: Transacciones atómicas de stock con `BEGIN IMMEDIATE TRANSACTION`, consulta de pedidos aislada por artesano creador, actualización de estados de pedido y pago, y cancelación con restitución de inventario.
   - `PedidoService`: Validación de existencias en tiempo real, cálculo de precio final congelado en servidor (`precio * cantidad`), generación de enlaces dinámicos a WhatsApp con mensajes pre-redactados, y cancelación idempotente (HTTP 409 si ya estaba cancelado).
   - Controladores REST en `api/pedidos/` (`index.php`, `solicitar.php`, `crear.php`, `cambiar-estado.php`, `cancelar.php`).
6. **Subfase 3.6: Auditoría Integral de Seguridad & Regresión Global** (⏳ *Pendiente*)
   - Verificación OWASP Top Ten: inyección SQL (100% prepared statements), mitigación XSS (`htmlspecialchars` en frontend / sanitización de entrada), protección contra ataques de fuerza bruta y CSRF/CORS.
   - Pruebas de estrés y concurrencia SQLite bajo múltiples peticiones simultáneas con `busy_timeout`.
   - Ejecución consecutiva y encadenada de las suites de prueba 3.1 a 3.5 asegurando cero regresiones.

#### Protocolo de Testing en 3 Niveles (Inviolable):
Al concluir cada subfase, se ejecutan obligatoriamente los 3 niveles antes de detenerse:
1. **Nivel 1 (Reporte Ejecutivo de QA):** Redactado en `docs/testing/subfase-3.X-[nombre].md` con matriz de aserciones, evidencias JSON y verificación SQLite.
2. **Nivel 2 (Suite CLI Nativa):** Ejecución de `php tests/test-subfase-3.X.php > logs/subfase-3.X-cli.log 2>&1`.
3. **Nivel 3 (Trazas HTTP Reales):** Peticiones curl contra el servidor local registradas en `logs/subfase-3.X-http.log`.
4. **Compuerta de Detención Absoluta (*Halt*):** Detener toda ejecución, sincronizar Memory Bank y **aguardar la aprobación explícita por escrito del usuario** antes de escribir código para la siguiente subfase.

---

### Fase 4: Cableado Fullstack & Operaciones Asíncronas (AJAX / ES Modules $\leftrightarrow$ API REST)

**Estado:** ⏳ **Pendiente (Inicia tras concluir la Fase 3)**

#### Objetivo:
Conectar de forma fluida, reactiva y asíncrona la interfaz de usuario desarrollada en la Fase 2 con los servicios y endpoints REST desarrollados en la Fase 3, sustituyendo cualquier almacenamiento temporal en memoria por peticiones asíncronas `fetch()` con autenticación Bearer token.

#### Módulos de Integración:
- **`src/js/modules/auth.js`:**
  - Envío de credenciales a `POST /api/auth/login.php`.
  - Almacenamiento seguro del token Bearer en almacenamiento del navegador (`sessionStorage` o `localStorage`).
  - Interceptor centralizado para adjuntar la cabecera `Authorization: Bearer <token>` en todas las peticiones protegidas.
  - Sincronización reactiva del estado visual de la sesión (Navbar, Sidebar del Artesano y visibilidad de rutas protegidas).
  - Manejo de expiración de token (HTTP 401 $\rightarrow$ apertura automática del modal de inicio de sesión).
- **`src/js/modules/catalog.js`:**
  - Consumo de `GET /api/creaciones/index.php` con serialización automática de filtros (categoría, precio min/max, artesano creador, búsqueda de texto y ordenación).
  - Paginación dinámica sin recargar la página (*seamless pagination*).
  - Poblado dinámico del selector `#filterArtisan` consumiendo `GET /api/creaciones/artesanos.php`.
- **`src/js/modules/creaciones.js`:**
  - Envío de nuevas piezas con imagen real vía `multipart/form-data` a `POST /api/creaciones/crear.php`.
  - Edición asíncrona mediante `POST /api/creaciones/actualizar.php`.
  - Bajas lógicas instantáneas mediante `POST /api/creaciones/eliminar.php`.
  - Ajustes in-situ de stock y alternancia de modalidad por encargo sin recarga de página.
- **`src/js/modules/detail.js`:**
  - Carga hidratada de la pieza por ID desde `GET /api/creaciones/detalle.php?id=X`.
  - Integración del modal de compra pública hacia `POST /api/pedidos/solicitar.php`.
- **`src/js/modules/orders.js`:**
  - Consumo de `GET /api/pedidos/index.php` con filtros textiles reactivos.
  - Actualización de estados de entrega y cobro mediante `POST /api/pedidos/cambiar-estado.php`.
  - Cancelación de pedidos con feedback visual de las unidades restituidas a inventario mediante `POST /api/pedidos/cancelar.php`.
- **`src/js/modules/users.js`:**
  - Consumo de `GET /api/usuarios/index.php?estado=activos|inactivos|todos`.
  - Altas, cambios de rol RBAC, actualización de nombre, reactivación y reseteo de claves con notificación en pantalla.
- **Experiencia de Usuario Asíncrona (Feedback & Microinteracciones):**
  - Estados de carga (*loading spinners* y *skeletons* de porcelana nórdica).
  - Notificaciones toast estilizadas bajo el sistema "Algodón Nórdico".
  - Tratamiento estandarizado de errores HTTP (400, 401, 403, 404, 409, 422, 500) con alertas amigables al usuario.
  - Prevención de doble clic (*debouncing* y bloqueo de botones durante envíos).

#### Compuerta de Calidad:
- Pruebas funcionales E2E con browser subagent cubriendo el flujo completo: Login $\rightarrow$ Catálogo $\rightarrow$ Ficha de Detalle $\rightarrow$ Solicitud de Pedido $\rightarrow$ Gestión de Inventario $\rightarrow$ Cambio de Estados de Pedido.
- Verificación en consola y pestaña de Red (Network): Cero errores 500 no controlados y transiciones reactivas instantáneas.

---

### Fase 5: Documentación Modular Diátaxis, Rendimiento & Entrega Final

**Estado:** 🔄 **En Curso / Fase de Cierre**

#### Objetivo:
Mantener, consolidar y culminar la documentación del proyecto bajo la arquitectura modular Diátaxis y el principio **Zero Monoliths**, ejecutar pruebas de estrés y rendimiento, auditar la accesibilidad final y preparar el paquete de entrega final listo para despliegue.

#### Ejes de Acción:
1. **Arquitectura de Documentación Modular (Diátaxis Framework):**
   - **Tutoriales & Guías:** Puesta en marcha rápida con `php -S localhost:8000` e inicialización con `php setup.php`.
   - **Explicación & Arquitectura:** Principios de Clean Architecture, separación física `app/` vs `src/`, y catálogo de Decisiones de Arquitectura ([`ADR-001` a `ADR-015`](./decisiones/README.md)).
   - **Referencia Técnica:** Contratos de clases ([`contracts.md`](./contracts.md)), esquema de base de datos ([`database/schema.md`](../database/schema.md)) y especificación REST de la API ([`docs/api/`](../api/README.md)).
2. **Auditoría de Rendimiento & Optimización:**
   - Verificación de tiempos de respuesta de endpoints de la API ($< 50\text{ ms}$ en entorno local).
   - Optimización de activos vectoriales SVG y caché en memoria con `SvgHelper`.
   - Comprobación de consultas SQLite para garantizar uso eficiente de índices (`idx_*`).
3. **Auditoría de Accesibilidad Final (WCAG 2.1 AA):**
   - Revisión completa de navegación por teclado en modales y menús desplegables.
   - Atributos ARIA y etiquetado descriptivo para lectores de pantalla.
   - Verificación de soporte para usuarios con preferencias de movimiento reducido (`prefers-reduced-motion`).
4. **Entrega & Traspaso:**
   - Verificación de que el repositorio se encuentre limpio, sin dependencias innecesarias, con `.gitignore` y `.htaccess` blindados.
   - Aprobación formal y entrega al usuario.

---

## 4. Gobernanza del Flujo y Reglas Inviolables

1. **Secuencialidad Estricta:** Queda estrictamente prohibido iniciar código de una fase o subfase posterior sin haber completado, testeado y recibido aprobación formal de la fase precedente.
2. **Aislamiento Físico:** El directorio `src/` es 100% frontend. Todo el backend reside exclusivamente en `app/`.
3. **Baja Lógica Universal:** No existen eliminaciones físicas (`DELETE FROM`) en ninguna tabla de negocio. Todas las bajas son lógicas (`activo = 0`).
4. **Algodón Nórdico:** Ningún componente o vista puede revertir a estilos por defecto de Bootstrap o colores eléctricos ajenos a la paleta oficial.
5. **Cero Monolitos en Documentación:** Toda nueva documentación debe ser modular, residir en el dominio correspondiente y mantener enlaces relativos bidireccionales.
6. **Sincronización del Memory Bank:** Al concluir cualquier hito o cambio arquitectónico, los 5 archivos de `/memory-bank/` deben actualizarse de inmediato.
