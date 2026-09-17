# 008 · Directorio de Creadores & Roles RBAC (Subfase 4.5)

**Estado:** propuesto (solo spec · sin código)

> 🧭 **Feature hija del plan maestro de la Fase 4 (`spec/features/009-plan-maestro-fase-4/`).**  
> Cubre la **subfase 4.5** con su gate 3-tier (suite CLI + logs CLI/HTTP + reporte) conforme a `009/tasks.md` y `spec/constitution/roadmap.md`.

---

## Qué hace

Conecta el panel administrativo de usuarios y creadores (`usuarios.php`) con la API REST del backend bajo autenticación Bearer y control de acceso estricto RBAC (exclusivo para el rol `admin`):

- **Carga Server-Driven & Directorio Reactivo:** sustituye los datos estáticos de presentación (`$usuariosList`) por una carga asíncrona real desde `GET /api/usuarios/index.php`. La tabla presenta a los artesanos y colaboradores con sus avatares artesanales (`.avatar-artisan-initials`), identificadores de autoría (`@username`), roles vigentes (`admin`, `artesano`, `asistente`), enlaces/números de WhatsApp comercial, conteo de piezas asociadas y fecha de registro.
- **Filtro por Estado de Cuenta:** permite alternar la vista entre creadores **Activos** (por defecto), **Inactivos / Suspendidos** y **Todos**, mostrando de manera clara badges de estado y opciones contextuales de gestión.
- **Métricas KPI en Tiempo Real:** las tarjetas superiores (Total Cuentas, Administradores, Artesanos, Asistentes) se recalculan dinámicamente según la respuesta del servidor o el estado filtrado.
- **Alta de Nuevos Creadores:** el modal `#modalCrearUsuario` envía `POST /api/usuarios/crear.php` con credenciales seguras (nombre de usuario validado contra `chk_usuarios_username`, contraseña hasheada con `password_hash`, rol inicial y WhatsApp comercial opcional), actualizando la lista y contadores sin recargar la página.
- **Actualización de Roles RBAC:** el modal `#modalEditarRolUsuario` procesa cambios de privilegios vía `POST /api/usuarios/cambiar-rol.php` conforme a la restricción `chk_usuarios_rol`, actualizando instantáneamente los badges y KPIs de la interfaz.
- **Salvaguarda Inviolable de la Cuenta Raíz (R-05):** el usuario administrador titular (`id: 1`, `@admin`) cuenta con bloqueos visuales y de comportamiento en la UI (selector deshabilitado, botón de baja inactivo y advertencia permanente) que impiden degradar su rol o eliminarlo, respaldado por la salvaguarda de seguridad HTTP 403 del backend.
- **Restablecimiento de Contraseñas:** permite al administrador generar o asignar una nueva contraseña segura para cualquier colaborador mediante `POST /api/usuarios/restablecer-password.php`, mostrando retroalimentación clara con la credencial actualizada.
- **Baja Lógica y Reactivación (R-01):**
  - **Eliminación Lógica:** da de baja cuentas mediante `POST /api/usuarios/eliminar.php` marcando `activo = 0` y registrando `eliminado_en` (sin sentencias `DELETE FROM` físicas y sin pérdida de autoría histórica). Si el usuario cuenta con creaciones protegidas por integridad referencial, el sistema notifica el bloqueo correspondiente.
  - **Reactivación Inmediata:** ante usuarios inactivos, ofrece la acción de reactivación directa mediante `POST /api/usuarios/reactivar.php`, devolviendo la cuenta al estado activo (`activo = 1`).
- **Blindaje DOM & Estética "Algodón Nórdico":** erradica `innerHTML` con datos dinámicos interpolados (H-004), empleando exclusivamente manipulación DOM segura (`textContent`, elementos DOM y `escapeHtml`), tarjetas con pespuntes artesanales (`.card-stitched`) y badges de rol de alto contraste.

---

## Por qué

En la Fase 3 se construyeron y blindaron los 7 controladores REST del módulo de usuarios (`index.php`, `crear.php`, `cambiar-rol.php`, `eliminar.php`, `reactivar.php`, `restablecer-password.php`, `actualizar.php`), junto con el servicio de dominio `UsuarioService` y la salvaguarda de cuenta raíz (R-05). En la Fase 4.6 se incorporó `actualizar-whatsapp.php`.

Sin embargo, en el frontend:
1. `usuarios_content.php` renderiza un array `$usuariosList` quemado en código PHP.
2. El formulario de alta en `users.js` agrega filas ficticias locales sin llamar al backend.
3. El modal de cambio de rol solo muta atributos HTML en memoria.
4. El botón de eliminar ejecuta un simple `row.remove()` en el navegador sin persistir la baja lógica.
5. No existe interfaz para restablecer contraseñas ni para ver o reactivar cuentas inactivas.
6. Cualquier usuario no autenticado o con rol artesano que ingrese a `usuarios.php` ve la estructura sin que la UI proteja la ruta ni consuma la sesión Bearer.

La Feature 008 (Subfase 4.5) es el cierre del cableado fullstack esencial de la Fase 4, garantizando que la administración de artesanos y colaboradores sea completamente funcional, segura y respaldada por SQLite.

---

## Criterios de aceptación

- [ ] **Acceso y Protección RBAC en Cliente:** si no hay sesión activa o el usuario autenticado no posee el rol `admin`, la página `usuarios.php` redirige a `index.php` (o login) impidiendo el acceso a la interfaz de administración; la API rechaza peticiones no admin con `HTTP 401/403`.
- [ ] **Carga Asíncrona Server-Driven:** la tabla de creadores y los 4 KPIs superiores se cargan desde `GET /api/usuarios/index.php` enviando el Bearer token activo. Se descarta `$usuariosList` hardcodeado en la vista PHP.
- [ ] **Filtro de Estado de Cuentas:** la interfaz provee control para alternar entre creadores `activos` (por defecto), `inactivos` y `todos`, consultando el endpoint con el parámetro `?estado=` correspondiente y reflejando visualmente el estado del registro.
- [ ] **Alta Exitosa de Creador:** el formulario `#formCrearUsuario` envía `POST /api/usuarios/crear.php` (`username`, `password`, `rol`, `whatsapp`); ante respuesta exitosa `201`, cierra el modal, limpia los campos, inserta la nueva fila en la tabla y actualiza los KPIs; ante error (`422/409`), muestra el mensaje del servidor en `#usuarioAlert` con `role="alert"` sin recargar.
- [ ] **Modificación Reactiva de Rol:** `#formEditarRolUsuario` envía `POST /api/usuarios/cambiar-rol.php` (`id`, `rol`); ante `200`, actualiza el badge y atributos de la fila, recalcula los KPIs de roles y cierra el modal; ante fallo (`422/403`), presenta la alerta correspondiente.
- [ ] **Salvaguarda Inviolable de ID #1 (R-05):** en la interfaz, la fila correspondiente al usuario con `id === 1` o `@admin` muestra controles de edición de rol deshabilitados o restringidos (con tooltip/alerta explicativa) y botón de baja desactivado; cualquier intento forzado vía API es rechazado con `HTTP 403`.
- [ ] **Restablecimiento Seguro de Contraseña:** se implementa la acción/modal para restablecer la contraseña mediante `POST /api/usuarios/restablecer-password.php` (`id`, `nueva_password` opcional); ante `200`, presenta feedback accesible con la contraseña establecida o generada.
- [ ] **Baja Lógica Persistente (R-01):** la acción de dar de baja ejecuta `POST /api/usuarios/eliminar.php` (`id`); ante `200`, actualiza la UI mostrando el usuario como inactivo o retirándolo de la vista de activos (sin ejecutar sentencias `DELETE` físicas en SQLite). Ante error por creaciones asociadas (`HTTP 409/422`), muestra el motivo al usuario.
- [ ] **Reactivación de Creador:** para usuarios en estado inactivo, se expone la acción de reactivación vía `POST /api/usuarios/reactivar.php` (`id`); ante `200`, restaura la cuenta como activa (`activo = 1`) y actualiza la lista y contadores.
- [ ] **Seguridad DOM (H-004):** todas las inserciones y mutaciones en el DOM de `users.js` emplean `escapeHtml`, `textContent` o nodos seguros; cero concatenación de datos no confiables en `innerHTML`.
- [ ] **Suite de Pruebas Automatizada (Tier 1 & 2):** se crea y ejecuta la suite CLI `tests/test-subfase-4.5.php` con el 100% de aserciones en verde (`logs/subfase-4.5-cli.log`), y se realizan las validaciones HTTP curl documentadas en `logs/subfase-4.5-http.log` sin divergencia.
- [ ] **Regresión Acumulada y Reporte (Tier 3):** se genera el reporte `docs/testing/subfase-4.5-usuarios.md`, la suite de regresión de fase `php tests/test-fase-4-acumulado.php` pasa al 100% integrando las subfases 4.1 a 4.5, y la regresión de Fase 3 se mantiene en **1,287** aserciones (H-006).

---

## Fuera de alcance

- **Edición y Carga de Creaciones Textiles:** corresponde a la Feature 006 (Subfase 4.3).
- **Gestión de Pedidos y Enlaces de Compra:** corresponde a la Feature 007 (Subfase 4.4).
- **Recuperación Pública de Contraseña por Correo/SMS:** la plataforma es un Micro-ERP colaborativo local sin servidor SMTP externo; la gestión de claves es potestad del administrador del taller.
- **Auditorías de Sesión Avanzadas o MFA:** fuera del alcance de la Fase 4; el esquema opera con tokens HMAC-SHA256 con revocación por denylist (H-002/H-003).
