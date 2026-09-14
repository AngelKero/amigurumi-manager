# Tech Stack y Convenciones

## Tecnologías

- **Lenguaje Backend:** PHP 8.1+ nativo (Strict Typing, PSR-4 nativo sin Composer, `app/autoload.php`).
- **Base de Datos:** SQLite 3 (`database/database.sqlite`) vía PDO Singleton (`App\Core\Database`).
  - `PRAGMA foreign_keys = ON;`
  - `PRAGMA busy_timeout = 5000;`
- **Frontend:** Semantic HTML5, Bootstrap 5.3 (CDN), Bootstrap Icons (CDN), Google Fonts (*Fraunces*, *Outfit*, *Plus Jakarta Sans*).
- **Estilos CSS:** Vanilla CSS modular bajo arquitectura ITCSS (`src/css/01-settings` a `04-components`) con sistema "Algodón Nórdico".
- **Scripts Frontend:** JavaScript Vanilla nativo en ES Modules (`src/js/main.js` + `src/js/modules/*.js`). Sin bundlers ni Node en runtime.
- **Autenticación:** Stateless Bearer Tokens HMAC-SHA256 (`App\Core\TokenManager`) con TTL de 24 horas y `hash_equals()`. Hashing Bcrypt con cost 10 para contraseñas.
- **Testing:** Suite CLI nativa en PHP (`tests/TestHelper.php`, `tests/test-subfase-3.X.php`) con arquitectura en 3 niveles (CLI, raw logs en `logs/`, reportes en `docs/testing/`).

## Archivos y Módulos Clave

- `app/` — 100% de la lógica de backend (Clean Architecture: Core, Repositories, Services, Middleware, Utils). Protegida por `.htaccess` (HTTP 403).
- `api/` — Controladores REST delgados ($\le 60$ líneas) organizados en subcarpetas temáticas (`auth/`, `creaciones/`, `pedidos/`, `usuarios/`).
- `src/` — 100% recursos de frontend (`src/css/`, `src/js/`). Cero archivos PHP en `src/`.
- `views/` — Capa de presentación modular en PHP (`views/layouts/main.php`, `views/components/`, `views/pages/`).
- `database/` — Esquema relacional DDL (`seed.sql`) y base SQLite (`database.sqlite`).
- `docs/` — Biblioteca de referencia técnica modular (ADRs en `architecture/decisiones/`, contratos en `api/`, ERD en `database/`, manual de marca en `design-system/`, QA en `testing/`).
- `spec/` — Motor activo de especificación y ejecución SDD (`constitution/` y `features/`).

## Comandos

- `php -S localhost:8000` — Arranca el servidor local de desarrollo.
- `php setup.php` — Inicializa o restablece la base de datos física SQLite desde `seed.sql` (estrictamente CLI-only).
- `php tests/test-subfase-3.6.5.php` — Ejecuta la suite de regresión acumulada del backend (141 aserciones directas en la propia subfase).
- `php tests/cuenta-aserciones.php` — **Contador regenerable de aserciones (H-006)**: ejecuta las 10 suites de Fase 3 sobre semilla limpia y verifica el total en runtime (valor actual verificado: **1,287** acumuladas). Toda cifra de aserciones se obtiene con este comando; jamás se copia a mano.
- `find app api views *.php -name "*.php" -exec php -l {} +` — Linter sintáctico de todo el backend y vistas PHP.
- `find src/js -name "*.js" -exec node --check {} +` — Linter sintáctico de todos los módulos JavaScript ES6.

## Modelo de Dominio & Datos

- **`usuarios`:** Cuentas con roles (`admin`, `artesano`, `asistente`), autenticación Bcrypt, borrado lógico (`activo = 0`).
- **`creaciones`:** Ficha técnica de piezas artesanales (`artesano_id`, `dimensiones`, `precio`, `costo_materiales`, `cantidad_stock`, `horas_tejido`, `imagen_url`, `es_sobre_encargo`, `activo`).
- **`pedidos`:** Encargos y compras relationally linked to `creaciones` (`cliente_nombre`, `cliente_contacto`, `cantidad`, `precio_final`, `estado_pedido`, `estado_pago`, `activo`).

## Convenciones de Código

- **Aislamiento Estricto de SQL:** El 100% de sentencias SQL reside en `app/Repositories/`. Cero consultas directas en controladores, servicios ni vistas.
- **Controladores REST Delgados:** Máximo 60 líneas por archivo en `api/`. Solo extraen entrada con `App\Core\Request`, delegan a un servicio en `App\Services\`, y emiten salida con `App\Core\Response`.
- **Envolvente JSON Estándar:**
  - Éxito: `{"exito": true, "mensaje": "...", "datos": { ... }}`
  - Paginado: `{"exito": true, "mensaje": "...", "datos": [ ... ], "paginacion": { ... }}`
  - Error: `{"exito": false, "error": {"codigo": 4XX, "mensaje": "...", "detalles": [ ... ]}}`
- **Manejo de Errores Sin Fugas:** `App\Core\ErrorHandler` limpia buffers con `ob_end_clean()` y emite JSON 500 estándar ante excepciones o fatals.
- **Preflight CORS Centralizado:** Toda llamada invoca `Response::handleCors()` al inicio respondiendo `OPTIONS` con HTTP 204.

## Estilo Visual ("Algodón Nórdico")

- **Paleta Oficial:**
  - `--craft-primary: #8E5B74` (Ciruela Nórdico)
  - `--craft-secondary: #52857C` (Pícea Nórdica, contraste `--craft-secondary-text: #235048` > 6.2:1)
  - `--craft-accent-gold: #D99C52` (Miel Nórdica para pendientes y anticipos)
  - `--craft-bg: #F8F9FB` (Porcelana Alabastro para fondos)
  - Prohibición absoluta del azul eléctrico default de Bootstrap (`#0d6efd`).
- **Tipografías:** `Fraunces` (títulos y marcas), `Outfit` (subtítulos y KPIs), `Plus Jakarta Sans` (cuerpo e inputs).
- **Detalles Artesanales:** Pespuntes discontinuos (`.card-stitched`, `.btn-craft-stitched`), etiquetas textiles (`.badge-textile-tag`), marco fotográfico acolchado (`.product-photo-stitched-frame`).

## Límites Duros (Inviolables)

> **Fuente canónica:** los 10 invariantes operativos viven en `AGENTS.md §5` y se citan por **clave semántica `R-0X`**, nunca por número posicional (H-005).

| Clave | Invariante | ADR / Ref. |
| :--- | :--- | :--- |
| **R-01** | Borrado lógico universal (cero `DELETE FROM`) | ADR-004 |
| **R-02** | Preservación de fotos en bajas lógicas (nunca `unlink()` de assets) | ADR-008 |
| **R-03** | Autonomía multi-artesano y garantías de plataforma | — |
| **R-04** | Prevención de IDOR multi-artesano (403 a recursos ajenos) | ADR-007 |
| **R-05** | Protección del administrador raíz ID #1 (403 irrestricto) | ADR-010 |
| **R-06** | Estándar monetario en centavos enteros (nunca `REAL`) | ADR-005 |
| **R-07** | Transacciones atómicas de inventario y cancelación idempotente | ADR-009 |
| **R-08** | Concurrencia SQLite: `foreign_keys = ON` + `busy_timeout = 5000` | `App\Core\Database` |
| **R-09** | Carga segura de archivos: MIME binario real, ≤5 MB, nombres criptográficos | ADR-008 |
| **R-10** | Hardening de login (429/backoff) y revocación server-side de tokens | H-002/H-003 |
