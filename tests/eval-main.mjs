/* Chequeo permanente (Subfase 4.4): evalúa el grafo ES de main.js con un DOM
 * simulado mínimo. Detecta errores que `node --check` no ve: identificadores
 * duplicados entre ediciones, top-level throws y fallos en los init().
 * Uso: node tests/eval-main.mjs (exit 0 = grafo sano). */
function makeEl(id) {
  const el = {
    tag: 'div',
    id,
    value: '',
    files: null,
    dataset: {},
    style: {},
    classList: {
      add() {}, remove() {}, toggle() {}, contains() { return false; },
    },
    textContent: '',
    disabled: false,
    href: '',
    src: '',
    alt: '',
    title: '',
    tabIndex: 0,
    childElementCount: 0,
    className: '',
    setAttribute() {},
    getAttribute() { return null; },
    removeAttribute() {},
    addEventListener() {},
    appendChild(c) { return c; },
    append() {},
    replaceChildren() {},
    cloneNode() { return makeEl(id); },
    querySelector() { return makeEl(id + '-q'); },
    querySelectorAll() { return []; },
    closest() { return null; },
    remove() {},
    click() {},
    reset() {},
    show() {},
    hide() {},
    focus() {},
    dispatchEvent() { return true; },
  };
  return el;
}

globalThis.window = {
  location: { pathname: '/index.php', search: '', href: 'http://localhost:8000/index.php', replace() {} },
  bootstrap: undefined,
};
globalThis.document = {
  getElementById: (id) => makeEl(id),
  querySelectorAll: () => [],
  querySelector: () => makeEl('q'),
  createElement: () => makeEl('c'),
  createTextNode: (t) => ({ text: t }),
  addEventListener: (ev, fn) => {
    if (ev === 'DOMContentLoaded') {
      Promise.resolve().then(() => fn()).catch((e) => {
        console.error('DOMContentLoaded ERROR:', e);
        process.exit(2);
      });
    }
  },
};
const store = {};
globalThis.localStorage = {
  getItem: (k) => (k in store ? store[k] : null),
  setItem: (k, v) => { store[k] = String(v); },
  removeItem: (k) => { delete store[k]; },
};
globalThis.fetch = async () => ({
  ok: true,
  status: 200,
  headers: { get: () => 'application/json' },
  json: async () => ({ exito: true, datos: [], paginacion: { total_items: 0, total_paginas: 1, pagina_actual: 1, limite: 12, tiene_anterior: false, tiene_siguiente: false } }),
});
globalThis.FileReader = class {
  readAsDataURL() {}
};

await import('../src/js/main.js');
setTimeout(() => { console.log('DIAG OK: main.js evaluado e inits ejecutados sin excepciones'); }, 500);
