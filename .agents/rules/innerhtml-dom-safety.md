# Regla de Gobernanza: Seguridad de `innerHTML` y Sanitización (H-004)

> **Clasificación:** P1 · Regla operativa permanente.
> **Origen:** Hallazgo H-004 de la auditoría de gobernanza (Acción 2).

## Prohibición

Queda **prohibida** la interpolación de datos de usuario o servidor en `innerHTML`.

```js
// PROHIBIDO
el.innerHTML = `<strong>@${username}</strong>`;

// CORRECTO (textContent / DOM APIs)
const strong = document.createElement('strong');
strong.textContent = `@${username}`;
el.replaceChildren(strong);

// CORRECTO (plantilla constante + escapeHtml del dato)
el.innerHTML = `<strong class="fw-bold">@${escapeHtml(username)}</strong>`;
```

## Exenciones (únicas)

1. **Markup craft constante** — el literal no contiene interpolaciones.
2. **Contadores numéricos** — la única interpolación es un valor numérico puro
   (`${count}`, `toFixed()`, operaciones aritméticas sobre `parseInt/Float`).

## Contexto de atributo

Ningún evento debe declararse con `on*="..."` inline que interpole datos.
Usar `class`/`data-*` (con `escapeHtml`) + `addEventListener` con delegación.

## Helper canónico

`src/js/modules/dom-safe.js` exporta `escapeHtml` y `setIconText`.
Todo nuevo módulo ES importará de aquí antes que duplicar escape manual.

## Referencia

- `docs/security/auditoria-sanitizacion-js.md` (inventario y correcciones aplicadas).
- `AGENTS.md §4 · Frontend Guidelines`.