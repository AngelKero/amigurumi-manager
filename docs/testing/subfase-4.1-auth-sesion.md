# Reporte de Pruebas: Subfase 4.1 — Autenticación Bearer & Estado Reactivo del Navbar

- **Feature:** `004-auth-sesion-cliente` (subfase 4.1) · orquestada por `009-plan-maestro-fase-4`
- **Fecha de Ejecución:** 2026-09-14
- **Responsable:** Agente IA → validación humana final (HALT)
- **Entorno:** PHP 8.1+ CLI + Servidor Built-in (`localhost:8000`) + SQLite 3
- **Archivos de Log Crudo:** `logs/subfase-4.1-cli.log` · `logs/fase-4-acumulado.log`
- **Log HTTP:** `logs/subfase-4.1-http.log`
- **Script de Pruebas:** `tests/test-subfase-4.1.php`
- **Resultado General:** **58 / 58 Aprobados (100%)** — ✅ APTO PARA AVANZAR

---

## 1. Objetivo Verificado

Migrar la autenticación del frontend desde los **flags mock de `localStorage`
(`crochet_session_active` / `amigurumi_session_active`)** hacia el **ciclo real Bearer token**
contra `api/auth/*` (`login.php`, `me.php`, `logout.php`), con **navbar/sidebar/modal reactivos**,
CSP estricta intacta (H-004) y **logout con revocación server-side** (ADR-016 / H-002).

## 2. Cambios Implementados en la Subfase

| Archivo | Cambio |
| :--- | :--- |
| `src/js/modules/auth.js` | Reescrito: `TOKEN_KEY`/`USER_KEY`, `getToken`, `setSession`, `clearSession`, `isAuthenticated`, `login` (POST JSON, manejo 429), `checkSession` (`/me.php`), `logout` (revocación best-effort + descarte local), `renderAuthState` (RBAC admin), guarda de rutas privadas `creaciones.php`/`pedidos.php`/`usuarios.php` |
| `views/components/navbar.php` | Badge de artesano ahora con `<span id="navArtisanUsername">` reactivo |
| `views/components/panel_sidebar.php` | `id="panelProfileUsername"` + `id="sidebarLinkUsuarios"` (oculto salvo rol admin) |
| `views/components/modal_login.php` | `id="btnLoginSubmit"` (bloqueo + spinner + feedback 429/401) |
| `src/js/modules/catalog.js` / `detail.js` | Import de `isAuthenticated()` y eliminación de flags mock |

## 3. Matriz de Aserciones y Casos Evaluados (58 aserciones)

| # | Componente / Endpoint | Caso de Prueba | Código Esperado | Código Obtenido | Estado |
| - | :--- | :--- | :---: | :---: | :---: |
| 1–6 | `auth.js` (contrato síncrono) | `getToken`, `setSession`, `clearSession`, `isAuthenticated`, `initAuth`, `renderAuthState` exportadas | — | — | ✅ PASS |
| 7–9 | `auth.js` (contrato asíncrono) | `login`, `logout`, `checkSession` exportadas como `async` | — | — | ✅ PASS |
| 10–16 | `auth.js` (seguridad + accesibilidad) | Clave `crochet_auth_token`; consume `/login.php`, `/logout.php`, `/me.php`; **sin** flags `session_active`; **sin `alert()` nativo**; `#loginAlert` con `role="alert"` (AC-3) | — | — | ✅ PASS |
| 17–20 | `catalog.js` / `detail.js` | Importan `isAuthenticated`; sin flags mock | — | — | ✅ PASS |
| 21–24 | Vistas | `#navArtisanUsername`, `#panelProfileUsername`, `#sidebarLinkUsuarios`, `#btnLoginSubmit` | — | — | ✅ PASS |
| 25–28 | Layout CSP (H-004) | `script-src 'self'`, `connect-src 'self'`, `frame-ancestors 'none'`, **`font-src` incluye `https://cdn.jsdelivr.net` (iconos Bootstrap)** | — | — | ✅ PASS |
| 29–37 | `AuthService` | Login `admin/admin123` → token Bearer, `expira_en=86400`, rol `admin`; `validateToken` OK; `revokeToken` → **denylist jti**; token revocado → `null`; token inválido → revocación `false` | — | — | ✅ PASS |
| 38–39 | Fuerza bruta (H-003) | 5× credenciales erróneas → 401 (x5); 6.º intento → **429** (usuario dedicado, limpiado tras la prueba) | 401/429 | 401/429 | ✅ PASS |
| 40 | Preflight CORS | `OPTIONS /api/auth/login.php` | 200/204 | 204 | ✅ PASS |
| 41–45 | CSP página HTML | `GET /index.php` → 200 + CSP con `script-src 'self'`, `connect-src 'self'`, `frame-ancestors 'none'`, y **`font-src` con la fuente real de iconos** | — | — | ✅ PASS |
| 46–47 | CSP respuestas API | `GET /api/creaciones/index.php` → 200 + `default-src 'none'` | — | — | ✅ PASS |
| 48–51 | Login HTTP real | `POST /api/auth/login.php` admin → 200, `exito:true`, token Bearer, rol `admin` | 200 | 200 | ✅ PASS |
| 52–53 | `GET /api/auth/me.php` (token válido) | 200, username `admin` | 200 | 200 | ✅ PASS |
| 54–55 | Logout server-side | `POST /logout.php` con Bearer → 200, `revocado_en_servidor: true` | 200 | 200 | ✅ PASS |
| 56 | Token revocado | `GET /me.php` con token revocado → **401** | 401 | 401 | ✅ PASS |
| 57–58 | 429 por HTTP | 5× 401 (sin bloqueo prematuro) + 6.º intento → **429** (usuario dedicado, limpiado tras la prueba) | — | 429 | ✅ PASS |

## 4. Criterios de Aceptación (spec.md de 004 · traceabilidad AC-1…AC-8)

Todos los criterios de `spec/features/004-auth-sesion-cliente/spec.md` están marcados `[x]` tras la verificación.

| AC | Criterio (resumen) | Evidencia de implementación | Evidencia de prueba | Estado |
| :-: | :--- | :--- | :--- | :-: |
| AC-1 | Login asíncrono `POST /api/auth/login.php` desde el modal | `auth.js` → `login()` con `fetch` POST JSON; listener del `submit` de `#loginForm` con `preventDefault()` | Suite 10–16; HTTP 48–51 (200 con token) | ✅ |
| AC-2 | Feedback diferenciado ante HTTP 429 sin recarga | `login()` lanza `Error` con `status=429`; el catch muestra mensaje de bloqueo (cuenta 15 min) en `#loginAlert`; sin recarga | Suite 38–39 (CLI) y 57–58 (HTTP: 5×401 + 429) | ✅ |
| AC-3 | Feedback accesible ante 401, sin `alert()` nativo | `#loginAlert` con `role="alert"` en `modal_login.php`; catch general escribe por `textContent`; **suite bloquea `alert(` en `auth.js`** | Suite 10–16 (sin `alert()`, `role="alert"`); HTTP 401 | ✅ |
| AC-4 | 200 → token + perfil en `localStorage` y cierre de modal | `setSession(datos.token, datos.usuario)`; `bootstrap.Modal.getInstance(...).hide()` | Suite 10–16 (TOKEN_KEY); HTTP 48–51 | ✅ |
| AC-5 | Navbar reactivo: "Iniciar Sesión" ↔ sello con nombre + logout | `renderAuthState()`: toggle `#btnNavLogin` (d-none) ↔ `#navUserBadge`, `#navArtisanUsername.textContent = '@usuario'` | Suite 21–24 (IDs); contrato visual verificado | ✅ |
| AC-6 | RBAC: artesano → Creaciones/Pedidos; admin → + Usuarios | `renderAuthState()` oculta `#sidebarLinkUsuarios` salvo `rol === 'admin'`; Creaciones/Pedidos visibles para todo autenticado; backend valida con `AuthGuard`/`RoleGuard` (Fase 3) | Suite 21–24 (`#sidebarLinkUsuarios`) | ✅ |
| AC-7 | Verificación de sesión al cargar (`GET /me.php`); 401 → estado público | `initAuth()` → `checkSession()`: `GET /me.php`; 200 re-sincroniza perfil; 401/403 → `clearSession()` + `renderAuthState(null)` + redirección si vista privada | Suite 10–16 (consume `/me.php`); HTTP 52–53 (200) y 56 (401 tras revocación) | ✅ |
| AC-8 | Logout → `POST /logout.php`; 200 → descartar local + redirigir vistas privadas | `logout()` revoca best-effort + `clearSession()` siempre; redirección a `index.php` si `isPrivatePage()` | Suite 54–55 (200, `revocado_en_servidor: true`); HTTP 56 (token inválido) | ✅ |

> 📌 **Observación de nomenclatura (regla de discrepancia P6):** la spec 004 menciona `#modalLogin`, pero el
> componente real `modal_login.php` usa el id `#loginModal` (target de `#btnNavLogin`, y de `loginModal` en `auth.js`).
> Es una correspondencia funcional exacta del mismo modal; **no** se modificó ni spec ni código. Queda registrado
> para re-anclaje humano si procede.

## 5. Evidencia de Respuestas JSON y Cabeceras (extractos de `logs/subfase-4.1-http.log`)

### Ciclo completo Login → Logout → Revocación
```json
// POST /api/auth/login.php  (admin/admin123)
{"exito":true,"mensaje":"Autenticación exitosa. Token emitido.","datos":{"token":"eyJzdWIiOjEsInVzZXJuYW1lIjoiYWRtaW4iLCJyb2wiOiJhZG1pbiIsImlhdCI6MTc4OTQzNTQ5NywiZXhwIjoxNzg5NTIxODk3LCJqdGkiOiJiMmViMWQ2NTY1ZDYzOWViZWQ3MjRjNjEzZmE4ZmMzMSIsInZlciI6MX0.0afe44e1938585f0ef9557af7144d52ba0bf5e54bd8d4082966ac361175cad12","tipo_token":"Bearer","expira_en":86400,"usuario":{"id":1,"username":"admin","rol":...}}

// POST /api/auth/logout.php  (Authorization: Bearer <token>)
{"exito":true,"mensaje":"Sesión cerrada exitosamente. Descarte el token del cliente.","datos":{"revocado_en_servidor":true}}

// GET /api/auth/me.php  (con el mismo token, ahora revocado)
{"exito":false,"error":{"codigo":401,"mensaje":"Token de autenticación inválido, manipulado o expirado."}}
```

### Cabeceras de seguridad
```
GET /index.php                          → Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; ... font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; connect-src 'self'; ... frame-ancestors 'none'
GET /api/creaciones/index.php           → Content-Security-Policy: default-src 'none'; frame-ancestors 'none'
OPTIONS /api/auth/login.php             → 204 No Content + Access-Control-Allow-* (CORS)
Fuerza bruta (6.º intento, usuario dedicado) → HTTP 429
```

> 🔧 **Hotfix de iconos (post-auditoría visual):** la CSP emitida tras la corrección incluye
> `font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net`, desbloqueando los
> glifos de **Bootstrap Icons** en todo el sitio (Regla de Oro: regresión positiva verificada
> por HTTP en la suite 58/58).

## 6. Fallos Detectados & Correcciones Quirúrgicas

| # | Hallazgo | Clasificación | Corrección | Resultado |
| - | :--- | :--- | :--- | :--- |
| 1 | La aserción de la suite buscaba solo `export function login/logout/checkSession`, pero estos se exportan como `export async function` → 3 FAIL en la 1.ª corrida. | **Defecto de aserción (suite), no de código.** El módulo `auth.js` es correcto (las funciones asíncronas deben ser `async`). | Se dividió el bucle de contratos: síncronos vs. `export async function`. | 58/58 ✅ |
| 2 | **Todos los iconos de Bootstrap Icons se rompieron tras la CSP estricta (H-004)** — se mostraba el glifo de codepoint en lugar del icono, en todo el sitio. **Causa raíz:** `@font-face` de `bootstrap-icons.css` carga el `.woff2` desde `cdn.jsdelivr.net`, pero `font-src` solo permitía `'self' https://fonts.gstatic.com` → el navegador bloqueaba la fuente. | **Defecto de producto (CSP).** `script-src`/`style-src` ya permitían el CDN; faltaba `font-src`. | Añadido `https://cdn.jsdelivr.net` a `font-src` en `views/layouts/main.php` + 2 aserciones anti-regresión (estática y HTTP) en la suite. | 58/58 ✅ · CSP emitida verifica `font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net` |

**Conclusión Red-Green-Refactor:**
1. El hallazgo #1 fue un **defecto de aserción de la suite** (firma `async` de `login`/`logout`/`checkSession`), no un defecto del entregable; se corrigió la expectativa del test, ningún archivo de aplicación se tocó por ello.
2. El hallazgo #2 fue un **defecto de producto real** (CSP bloqueando la fuente de iconos), detectado por auditoría visual del usuario y corregido quirúrgicamente en `views/layouts/main.php` con **2 aserciones anti-regresión** añadidas a la suite (estática + HTTP).

## 7. Verificación de Integridad & Regresiones

| Prueba | Resultado |
| :--- | :--- |
| `php tests/test-subfase-4.1.php` | 58 / 58 (100%) ✅ |
| `php tests/test-fase-4-acumulado.php` | Exit 0 · subfase 4.1 en verde (10 aserciones runner + 58 subproceso) ✅ |
| `php tests/test-subfase-3.6.5.php` (regresión global Fase 3) | 141 / 141 (100%) ✅ |
| `php tests/cuenta-aserciones.php` (H-006, semilla limpia) | **1,287 aserciones Fase 3** en verde ✅ |
| Divergencia CLI vs. HTTP | **Sin divergencia** (protocolo H-015 no requerido) ✅ |

## 8. Veredicto y Siguientes Pasos

- [x] Módulo `auth.js` reescrito con ciclo Bearer real y sin flags mock.
- [x] Navbar, sidebar y modal del login reactivos ante sesión/rol.
- [x] Revocación server-side de tokens (ADR-016) verificada CLI y HTTP.
- [x] Endurecimiento 429 (H-003) verificado CLI y HTTP.
- [x] CSP estricta intacta en HTML y API (H-004), incluida la fuente de Bootstrap Icons (`font-src` con `cdn.jsdelivr.net`) tras el hotfix #2.
- [x] **Criterios de aceptación de `spec.md` (004) verificados AC-1…AC-8** y marcados `[x]` (§4 de este reporte).
- [x] Logs respaldados y reporte generado.
- **Estado:** ⏸ **HALT — esperando autorización humana** para iniciar la siguiente subfase (4.2 · `005-catalogos-filtros`, o la que el plan maestro 009 indique).