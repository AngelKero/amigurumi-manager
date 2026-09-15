# 006 · Gestión de Creaciones & Subida Multipart (Subfase 4.3)

**Estado:** implementada y validada (AC 14/14 · suites 212/212 + 62/62 · gate 3-tier verde)

> 🧭 **Feature hija del plan maestro de la Fase 4 (`spec/features/009-plan-maestro-fase-4/`).**
> Cubre la **subfase 4.3** con su gate 3-tier (suite CLI + logs CLI/HTTP + reporte) conforme a
> `009/tasks.md`.

## Qué hace

Conecta el panel del artesano (`creaciones.php` → `views/pages/creaciones_content.php`) y el
formulario de pieza (`formulario.php` → `views/pages/formulario_content.php`) con la API real
de creaciones, de forma asíncrona (`fetch`), sin recargas bruscas de página. El artesano o
administrador autenticado (Bearer en `localStorage`, Feature 004) puede:

- **Crear una pieza** desde `formulario.php` (modo nuevo) con foto real (multipart) o sin foto
  (SVG temático de respaldo por categoría, R-09). Ante `201` muestra confirmación y redirige
  al panel; ante `422` muestra errores de campo accesibles dentro del propio formulario.
- **Editar una pieza propia** desde `formulario.php?id=` (modo edición, precarga vía
  `GET /api/creaciones/detalle.php?id=`). Si no adjunta foto nueva conserva la actual; si
  adjunta reemplazo, el anterior de `uploads/` se elimina y los SVG de `assets/` jamás se
  tocan (R-02).
- **Gestionar el inventario sin salir del panel**: ajuste de stock in-situ (`+/-`),
  alternador de modalidad bajo encargo y baja lógica con modal de confirmación, más
  **restauración** de piezas con baja lógica (botón dedicado, `POST restaurar.php`).
- **Explorar su inventario como rejilla reactiva server-driven** (mismo patrón que el catálogo
  005): buscador, filtros (categoría, estado de stock, artesano), orden y paginación consumen
  `GET /api/creaciones/index.php` (`datos` + `paginacion`); los KPIs (modelos, stock, valor,
  costos) reflejan el total real del servidor y el estado vacío permite restablecer filtros.
- **Ver feedback diferenciado** ante `401` (sesión expirada → limpiar `localStorage` y estado
  público), `403` (pieza ajena → IDOR, R-04), `404` (pieza inactiva), `409` (baja/restauración
  idempotente) y `429` de login heredado, sin diálogos nativos `alert()`.

Toda la validación de cliente **espeja** los límites del servidor (longitudes, rangos,
MIME/tamaño) para feedback inmediato; el servidor sigue siendo la autoridad (422).
Todo render de datos usa DOM APIs/`textContent` o `escapeHtml` (`src/js/modules/dom-safe.js`):
cero interpolación de datos en `innerHTML` (H-004). Precios en **centavos enteros** vía
`currency.js` (R-06).

## Por qué

El backend de creaciones está completo y blindado en Fase 3 (`CreacionService` con
crear/actualizar/baja/restaurar/ajustar-stock/toggle-encargo, 9 controladores delgados en
`api/creaciones/`, RBAC + IDOR + upload seguro + 1.287 aserciones en verde), pero el frontend
sigue en mock: `formulario_content.php` usa `$seedItems` + `onsubmit="alert(...)"` con rutas
inexistentes (`/api/crear.php`), `creaciones_content.php` usa `$creacionesList` hardcodeada con
valores de filtro/orden (`in-stock`, `name-asc`, usernames) que no existen en el contrato, y
`creaciones.js`/`dropzone.js` solo mutan el DOM sin ningún `fetch`. Sin este cableado ningún
artesano puede registrar, editar, stockear ni dar de baja una pieza real desde la UI. Es el
segundo entregable funcional de la Fase 4 visible para el artesano y valida end-to-end el ciclo
multipart → servicio → repositorio → render del sistema Algodón Nórdico.

## Criterios de aceptación

- [x] El submit de `formulario.php` (modo nuevo) envía `FormData` multipart con `Authorization: Bearer` a `POST /api/creaciones/crear.php`; ante `201` confirma y redirige al panel; ante `422` muestra errores por campo accesibles (`role="alert"`) sin recargar ni usar `alert()`.
- [x] `formulario.php?id=` precarga la pieza vía `GET /api/creaciones/detalle.php?id=` (no `$seedItems`) y su submit envía `POST /api/creaciones/actualizar.php`; sin foto nueva conserva `imagen_url`; con reemplazo elimina el previo solo si era `uploads/` (R-02).
- [x] La validación de cliente espeja al servidor (`nombre 2-100`, `categoria 2-50`, `material 3-80`, `dimensiones 2-100`, `precio 1-9.999.999¢`, `costo 0-9.999.999¢`, `stock 0-10000`, `horas 0-500`, `descripcion ≤2000`, `imagen jpeg/png/webp ≤5MB`) y convierte pesos→centavos con `currency.js` (R-06).
- [x] Sin foto se asigna SVG temático por categoría con fallback `ovillo-generico.svg` en disco (R-09); con foto el nombre es `creacion_[16-hex]_[timestamp].[ext]`, MIME verificado por `finfo` y confinado con `basename()`.
- [x] Los controles `+/-` de stock invocan `POST /api/creaciones/ajustar-stock.php` y refrescan badge/contador desde la respuesta (clamp `0-10000`).
- [x] El alternador bajo encargo invoca `POST /api/creaciones/toggle-encargo.php` y refresca el badge `on-demand` desde la respuesta.
- [x] El modal de baja invoca `POST /api/creaciones/eliminar.php` (200 → `activo=0` + `eliminado_en`); el fichero de imagen **sigue en disco** (R-02); el catálogo la filtra y `detalle.php` responde `404`; reintento responde `409` idempotente.
- [x] El control de restauración invoca `POST /api/creaciones/restaurar.php` (200 → `activo=1`, `eliminado_en=NULL`); `detalle.php` vuelve a `200`; reintento responde `409`.
- [x] Mutar pieza ajena como artesano (`actualizar/eliminar/ajustar-stock/toggle-encargo`) responde `403` (R-04); como `admin` responde `200/201`.
- [x] El panel es server-driven: filtros/orden/paginación consumen el bloque `paginacion` real, los valores enviados existen en el contrato (`en_stock/bajo_encargo/agotados`, `artesano_id`, `recientes/precio_asc/...`), el chip "Mostrando X de Y" refleja el total y el estado vacío restablece TODOS los filtros.
- [x] Cero datos del servidor interpolados en `innerHTML` (auditoría `innerHTML =` en `creaciones.js`/`dropzone.js`/`margin-calculator.js`, H-004); suite `tests/test-subfase-4.3.php` en verde, trazas HTTP en `logs/subfase-4.3-http.log` y reporte `docs/testing/subfase-4.3-creaciones.md` (H-008/H-015).
- [x] Regresión acumulada `php tests/test-fase-4-acumulado.php` en verde (H-020); regresión Fase 3 y cifra regenerable `php tests/cuenta-aserciones.php` (**1.287**) en verde si se tocó backend compartido (H-006).
- [x] El panel lee desde `GET /api/creaciones/mias.php` con Bearer: como artesano solo devuelve piezas propias aunque se falsee `artesano_id` (anti-spoof, 401 sin token); como `admin`, visión global con filtro `artesano_id` opcional (4.3.1, ADR-017).
- [x] La papelera es usable: `estado=inactivas` lista solo `activo=0` propios, `estado=todas` ambas, y el botón Restaurar aparece solo en inactivas (4.3.1).

## Fuera de alcance

- **Checkout, pedidos atómicos y WhatsApp**: Feature 007, subfase 4.4.
- **Directorio de creadores y roles RBAC** (`usuarios.php`, reseteo de claves, salvaguarda ID #1): Feature 008, subfase 4.5.
- **Catálogo público y filtros de vitrina** (`index.php`, `catalog.js`): Feature 005, subfase 4.2 (solo se reutiliza su patrón server-driven).
- **Autenticación y sesión** (login/logout/navbar reactivo, revocación `jti`, lockout 429): Feature 004, subfase 4.1 (solo se consume `getToken()`/Bearer).
- **Nuevos endpoints o cambios de contrato backend**: el contrato de `api/creaciones/` ya está completo; cualquier extensión quirúrgica se justifica en `plan.md` con su regresión.
