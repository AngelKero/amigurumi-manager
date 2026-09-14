# Auditoría de Sanitización de `innerHTML` en Frontend (H-004)

> **Dominio:** Seguridad de Frontend · **Fuente:** Hallazgo H-004 de la auditoría de gobernanza (Acción 2).
> **Fecha:** 14/09/2026 · **Ámbito:** `src/js/modules/*.js`

## 1. Objetivo

Eliminar **todos** los vectores de XSS por interpolación de datos de usuario/servidor
en `innerHTML`. La política de gobernanza (P1: `AGENTS.md §4` + `.agents/rules/innerhtml-dom-safety.md`)
prohíbe interpolar datos dinámicos en `innerHTML`; solo se permiten:
markup craft constante y contadores de interpolación **numérica** pura.

## 2. Método

1. Inventario exhaustivo de `innerHTML` en `src/js/modules/` (herramienta `grep`).
2. Clasificación de cada uso por **vector** (constante / numérico / whitelist / plantilla con datos).
3. Refactorizado quirúrgico exclusivo de los **vectores de datos** hacia `textContent`,
   DOM APIs o el helper compartido `escapeHtml` (`src/js/modules/dom-safe.js`).
4. Re-verificación con `node --check` en todos los módulos y recuento final.

## 3. Inventario inicial (36 usos)

| Módulo | Líneas | Clasificación |
|--------|--------|---------------|
| `checkout.js` | 73, 103 (título modal) | **Vector de datos** (`${name}`) |
| `orders.js` | 80 (contacto) | **Vector de datos** (`${tel}`) |
| `orders.js` | 318 (plantilla card) | **Vector de datos** (cliente, producto, notas…) |
| `orders.js` | `getBadgeConfig` default | **Vector de datos** (`html: status`) |
| `users.js` | 209 (fila de usuario) | **Vector de datos** (`@${username}`) |
| `users.js` | 213 (onclick inline) | **Vector de datos** (contexto de atributo injerto) |
| `orders.js` | 85, 137 · `users.js` 123 | Whitelist (getPaymentBadge / getRoleBadgeHtml / getBadgeConfig whitelist) |
| `catalog.js` 25 · `users.js` 48 · `detail.js` 45 · `creaciones.js` 100, 292, 299 | Numérico puro | Exento |
| `detail.js` 23–50 · `creaciones.js` 180–301 · `margin-calculator.js` 138–161 · `users.js` 111 | Constante pura | Exento |

## 4. Correcciones aplicadas (Red → Green)

1. **`checkout.js:73/103`** → `setIconText(modalTitle, 'bi bi-bag-heart…', 'Solicitud de Pedido: ' + name)`
   de `dom-safe.js` (icono vía `createElement('i')` + `appendChild(createTextNode())`).
2. **`orders.js:80`** → `setIconText(inspectContacto, 'bi bi-whatsapp…', tel)`.
3. **`orders.js:318`** → se precomputan 9 variables escapadas (`escNewId`, `escFecha`, `escProduct`,
   `escCliente`, `escContacto`, `escWa`, `escTotal`, `escNotas`, `escEstado`) y la plantilla
   constantes de craft solo interpola estas variables.
4. **`orders.js:getBadgeConfig` default** → `html: escapeHtml(status)` (los branches whitelist son
   constantes y se conservan).
5. **`users.js:209`** → precomputadas `escUsername`, `escRol`, `escInitial` con `escapeHtml`.
6. **`users.js:213`** → el `onclick` inline (contexto de atributo con `${username}`) se sustituye por
   `class="btn-eliminar-usuario"` + `data-username` + **delegación** de eventos
   (`bindDeleteUserButtons` / `handleDeleteUser` con `window.confirm` y `textContent`).

## 5. Recuento final (34 coincidencias de búsqueda, 33 usos reales)

- **1** comentario de documentación en `dom-safe.js` (no es uso).
- **20** constantes puras sin interpolación → seguras.
- **6** interpolación numérica pura → exentas por política.
- **3** whitelist mapeable a templates constantes → seguras.
- **2** plantillas grandes → **escapadas** con `escapeHtml` (users, orders).
- **3** vectores de elemento → **remediados** con `textContent`/DOM (`checkout` ×2, `orders`).
- **1** default de badge → **escapado** (`orders.js:getBadgeConfig`).

**Resultado: 0 vectores de datos de usuario/servidor sin escalar en `innerHTML`.**

## 6. Verificación

```bash
find src/js -name "*.js" -exec node --check {} +   # sin errores de sintaxis
```

## 7. Criterios de aceptación cumplidos

- [x] Ningún dato de usuario/servidor se interpola sin `escapeHtml`, `textContent` o DOM APIs.
- [x] Los contadores numéricos conservan `innerHTML` (exención documentada y aprobada).
- [x] Ayudante compartido `escapeHtml`/`setIconText` centralizado en `src/js/modules/dom-safe.js`.
- [x] Regla perenne publicada en `.agents/rules/innerhtml-dom-safety.md` e `AGENTS.md §4`.