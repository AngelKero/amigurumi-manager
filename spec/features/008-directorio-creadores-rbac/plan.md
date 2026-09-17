# 008 · Directorio de Creadores & Roles RBAC — Plan

**Estado:** propuesto (sin código) · léase con `spec.md`

> 🧭 **Feature hija del plan maestro de la Fase 4 (`spec/features/009-plan-maestro-fase-4/`).**  
> Define la estrategia técnica y los cambios por capa para la **subfase 4.5**, implementando el panel server-driven de administración de usuarios en `usuarios.php` con Bearer token, salvaguarda ID #1 (R-05) y baja lógica (R-01).

---

## Enfoque

Implementar un panel administrativo reactivo y server-driven para `usuarios.php` anclado al ciclo de vida Bearer token (`auth.js`), con control de acceso exclusivo para administradores (`getUser()?.rol === 'admin'` en cliente y `RoleGuard::adminOnly()` en todos los endpoints REST):

1. **Protección de Ruta & Inicialización Segura:** al cargar `usuarios.php`, el módulo `users.js` verifica la sesión activa. Si no existe token o el rol del usuario no es `admin`, redirige a `index.php` o solicita login, evitando exponer controles administrativos a visitantes o artesanos.
2. **Carga Server-Driven & Paginación Reactiva:** se elimina el array estático `$usuariosList` en `views/pages/usuarios_content.php`. La tabla se puebla asíncronamente desde `GET /api/usuarios/index.php?estado={activos|inactivos|todos}&pagina={p}&limite=20`.
3. **Resumen de Métricas KPI desde el Servidor:** se extiende `UsuarioRepository` y `UsuarioService::listUsers()` para retornar un objeto `resumen` (`total`, `admin`, `artesano`, `asistente`) junto con la paginación. Esto garantiza que las tarjetas KPI muestren cifras exactas de toda la base de datos y no solo de la página visible.
4. **Modales de Gestión:**
   - `#modalCrearUsuario`: conecta `#formCrearUsuario` a `POST /api/usuarios/crear.php` (`username`, `password`, `rol`, `whatsapp`). Valida longitudes, maneja errores 422/409 con alertas accesibles (`role="alert"`) y actualiza la tabla tras un 201.
   - `#modalEditarRolUsuario`: conecta `#formEditarRolUsuario` a `POST /api/usuarios/cambiar-rol.php` (`id`, `rol`). Protege a ID #1 desactivando la edición de su rol en la UI.
   - `#modalRestablecerPassword` (**nuevo componente**): permite al administrador ingresar una nueva contraseña o dejar el campo vacío para que el backend autogenere una clave temporal de 8 caracteres con entropía criptográfica (`Crochet!xxxxxx!`). Muestra feedback claro para compartir con el artesano.
5. **Ciclo de Vida de Cuenta (R-01 & R-05):**
   - **Baja Lógica:** botón de eliminar conectado a `POST /api/usuarios/eliminar.php` (`id`). Si el usuario posee creaciones activas, el backend responde `409 Conflict` (integridad referencial) y la UI lo reporta sin alterar el estado. Para ID #1, el botón está bloqueado y deshabilitado.
   - **Reactivación:** para cuentas inactivas (`activo === 0`), botón reactivar conectado a `POST /api/usuarios/reactivar.php` (`id`) que restaura `activo = 1` y `eliminado_en = null`.
6. **Seguridad DOM (H-004):** renderizado DOM seguro mediante `textContent`, manipulación de nodos o `escapeHtml` para atributos. Cero inyección de datos de usuario en cadenas `innerHTML`.

---

## Implementación

| Capa | Archivo(s) | Cambio |
| :--- | :--- | :--- |
| `app/Repositories/` | `UsuarioRepository.php` | Agregar método `getRoleCounts(?bool $onlyActive = true): array` (`total`, `admin`, `artesano`, `asistente`) mediante `SELECT rol, COUNT(*) FROM usuarios ... GROUP BY rol`. |
| `app/Services/` | `UsuarioService.php` | Enriquecer `listUsers()` incluyendo `'resumen' => $this->usuarioRepo->getRoleCounts($onlyActive)` en el array de retorno. |
| `api/usuarios/` | `index.php` | Pasar `'resumen' => $result['resumen']` en la metadata de `Response::success()`. Conservar `paginacion` intacta. |
| `views/pages/` | `usuarios_content.php` | Retirar `$usuariosList` hardcodeado y PHP loops; añadir contenedor `#tablaUsuariosBody` con skeleton/spinner de carga; añadir selector/tabs de estado `#filtroEstadoUsuarios` (`activos`, `inactivos`, `todos`); contenedor de paginación `#paginacionUsuarios` y estado vacío `#emptyStateUsuarios`. |
| `views/components/` | `modal_crear_usuario.php` | Agregar `novalidate`, atributos de accesibilidad, spinner en submit `#btnSubmitCrearUsuario`. |
| `views/components/` | `modal_editar_rol_usuario.php` | Añadir control para deshabilitar selección cuando sea ID #1; feedback accesible. |
| `views/components/` | `modal_restablecer_password.php` (**nuevo**) | Componente modal para restablecimiento de contraseña (campo nueva contraseña opcional, botón generar/asignar, display de clave temporal). |
| `usuarios.php` | `usuarios.php` | Registrar `'modal_restablecer_password'` en `$modals`. |
| `src/js/modules/` | `users.js` (reescritura) | Redirección RBAC si no es admin; `fetchUsers(estado, pagina)`; renderizado seguro DOM de filas; actualización de KPIs desde `resumen`; alta con `POST crear.php`; cambio de rol con `POST cambiar-rol.php`; restablecer clave con `POST restablecer-password.php`; baja lógica con `POST eliminar.php`; reactivación con `POST reactivar.php`; preservación del botón WhatsApp existente. |
| `tests/` | `test-subfase-4.5.php` (**nuevo**) | Suite de pruebas CLI de 5 secciones (estática, endpoints backend, salvaguardas RBAC/IDOR, módulo JS, HTTP curl en vivo). |
| `tests/` | `test-fase-4-acumulado.php` | Detección automática de `test-subfase-4.5.php` y validación de regresión acumulada. |
| `docs/testing/` | `subfase-4.5-usuarios.md` + `README.md` | Reporte ejecutivo Tier 3 con trazabilidad de ACs y registro en tabla del README. |

---

## Decisiones

- **Extensión mínima de `index.php` con `resumen`:**
  - El backend de usuarios ya cuenta con paginación (`limite=20`), pero los 4 KPIs superiores (Total, Admin, Artesano, Asistente) deben reflejar el universo filtrado completo, no solo la página actual. Agregar `getRoleCounts()` en el repositorio y exponerlo en `resumen` resuelve el cálculo exacto sin alterar los contratos existentes de `datos` o `paginacion`.
  - *Alternativa descartada:* calcular KPIs sumando los rows del DOM (produce datos erróneos si hay más de 20 usuarios).
  - *Alternativa descartada:* hacer 4 peticiones `COUNT(*)` separadas por HTTP desde el cliente (overhead de red innecesario).
- **Protección RBAC en Frontend + Backend:**
  - `usuarios.php` es una vista exclusiva de administración. Si un visitante o un artesano intenta cargar la vista en el navegador, `users.js` detecta `getUser()?.rol !== 'admin'` y redirige a `index.php`. En caso de que se intente saltar el frontend, todos los endpoints en `api/usuarios/` ejecutan `RoleGuard::adminOnly()` retornando 401/403.
  - *Alternativa descartada:* confiar únicamente en el backend (mala UX, el usuario vería una tabla vacía sin entender por qué).
- **Modal dedicado para Restablecimiento de Contraseña:**
  - Separar el cambio de rol del reseteo de contraseñas mantiene el principio de responsabilidad única en los componentes UI y evita envíos accidentales de nuevas credenciales.
  - *Alternativa descartada:* incluir el reseteo de clave dentro del mismo modal de edición de rol (sobrecarga visual y riesgo de reseteos no intencionados).
- **Manejo explícito de Integridad Referencial en Eliminación:**
  - La base de datos SQLite tiene claves foráneas activas (`PRAGMA foreign_keys = ON`). Un usuario con creaciones activas no puede ser eliminado (`UsuarioService::deleteUser` valida `countCreationsByUser > 0` y responde 409). La interfaz debe capturar este 409 y explicar amablemente al administrador que primero debe transferir o eliminar las piezas del artesano.
  - *Alternativa descartada:* borrar en cascada las creaciones (violaría R-01 y R-02 de preservación de creaciones).

---

## Riesgos

- **Riesgo:** Bloqueo accidental del Administrador Raíz (ID #1) → **Mitigación:** La UI deshabilita el selector de rol y el botón de baja para ID #1; el backend `UsuarioService` valida `id === 1` arrojando `HTTP 403` inmediato (R-05). Suite `test-subfase-4.5.php` incluye pruebas negativas específicas.
- **Riesgo:** Desincronización de aserciones en suites de Fase 3 (`test-subfase-3.3.php`, `3.6.1`, `3.6.5`) al añadir `resumen` a `index.php` → **Mitigación:** La adición es un campo extra en metadata; no modifica `datos` ni `paginacion`. Se ejecutará `php tests/cuenta-aserciones.php` (1,287) y `php tests/test-subfase-3.6.5.php` para validar 100% de compatibilidad.
- **Riesgo:** Inyección XSS mediante nombres de usuario manipulados en la tabla → **Mitigación:** Cumplimiento estricto de H-004; `users.js` utiliza `textContent` para cadenas de texto de usuario y `escapeHtml` para atributos data. Cero interpolación directa en `innerHTML`.
- **Riesgo:** Divergencia CLI vs HTTP en cookies/tokens → **Mitigación:** Se utiliza el flujo Bearer estándar documentado en ADR-002/ADR-016 y verificado en 4.1. Divergencias se triajan con `docs/testing/protocolo-divergencia-cli-http.md`.
