# 008 · Directorio de Creadores & Roles RBAC — Tareas

**Estado:** completado · gate 3-tier superado · 100% OK

> 🧭 **Feature hija del plan maestro `009` (subfase 4.5).** Su `tasks.md` vive aquí; el avance
> global de la fase se registra en `009/tasks.md`. Gate 3-tier obligatorio (H-008/H-015/H-020).

---

## 1. Especificación (antes de código)

- [x] `spec.md` validado y aprobado por el usuario (HALT cumplido).
- [x] `plan.md` validado y aprobado por el usuario (HALT cumplido).

---

## 2. Backend: Extensión no destructiva de KPIs de roles (sin gate propio, verificado en §5)

- [x] `app/Repositories/UsuarioRepository.php`: implementar método `getRoleCounts(?bool $onlyActive = true): array` que computa `total`, `admin`, `artesano`, `asistente` usando `GROUP BY rol`. Verificación: `php -l`.
- [x] `app/Services/UsuarioService.php`: extender `listUsers()` incluyendo `'resumen' => $this->usuarioRepo->getRoleCounts($onlyActive)` en el retorno. Verificación: `php -l`.
- [x] `api/usuarios/index.php`: propagar `'resumen' => $result['resumen']` en la metadata de `Response::success()`. Verificación: `php -l` + curl con token de admin.

---

## 3. Vistas y Componentes UI (sin gate propio, verificado en §5)

- [x] `views/pages/usuarios_content.php`: retirar array mock `$usuariosList` y loops PHP; añadir `#tablaUsuariosBody` con skeleton/loading; selector de filtro de estado `#filtroEstadoUsuarios` (`activos`, `inactivos`, `todos`); contenedor de paginación `#paginacionUsuarios`; contenedor de estado vacío `#emptyStateUsuarios`; IDs consistentes para KPIs (`#kpiTotalUsers`, `#kpiAdminUsers`, `#kpiArtesanoUsers`, `#kpiAsistenteUsers`). Verificación: `php -l` + `rg '\$usuariosList' views/pages/usuarios_content.php` vacío.
- [x] `views/components/modal_crear_usuario.php`: `novalidate`, feedback `#usuarioAlert` accesible con `role="alert"`, spinner de envío en submit. Verificación: `php -l`.
- [x] `views/components/modal_editar_rol_usuario.php`: control para deshabilitar selección cuando sea ID #1 (`@admin`), feedback `#editarRolAlert`. Verificación: `php -l`.
- [x] `views/components/modal_restablecer_password.php` (**nuevo**): modal para restablecimiento de contraseña (campo `#nuevaPasswordInput` opcional, `#btnSubmitResetPassword`, `#resetPasswordResult` accesible con clave temporal). Verificación: `php -l`.
- [x] `usuarios.php`: registrar `'modal_restablecer_password'` en `$modals`. Verificación: `php -l`.

---

## 4. Frontend: Módulo `users.js` server-driven (sin gate propio, verificado en §5)

- [x] `src/js/modules/users.js` (reescritura modular):
  - Guarda de ruta RBAC: comprobar sesión activa al inicio (`getUser()?.rol === 'admin'`); si no es admin o no hay token, redirigir a `index.php`.
  - Carga asíncrona de usuarios: función `fetchUsers(estado, pagina)` consumiendo `GET /api/usuarios/index.php`.
  - Renderizado DOM seguro (H-004): construir filas con `textContent`, `escapeHtml` para atributos data, avatares `.avatar-artisan-initials`, badges de rol (`badge-role-admin`, `badge-role-artesano`, `badge-role-asistente`), badge de estado y celda WhatsApp.
  - Salvaguarda ID #1 (R-05): para ID #1, deshabilitar botón de baja lógica con candado y bloquear selector de rol en modal.
  - Actualización de KPIs: sincronizar los 4 contadores superiores usando los datos de `resumen` provistos por el servidor.
  - Formulario de alta (`#formCrearUsuario`): envío a `POST /api/usuarios/crear.php` con Bearer token; actualización reactiva de tabla y KPIs ante 201; feedback ante 422/409.
  - Formulario de cambio de rol (`#formEditarRolUsuario`): envío a `POST /api/usuarios/cambiar-rol.php` con Bearer token; actualización de badge y KPIs ante 200.
  - Modal de restablecer contraseña (`#formRestablecerPassword`): envío a `POST /api/usuarios/restablecer-password.php` con Bearer token; visualización de la clave generada o asignada.
  - Acción de baja lógica: confirmación modal/diálogo y envío a `POST /api/usuarios/eliminar.php` con Bearer token; manejo de error 409 por creaciones asociadas (integridad referencial).
  - Acción de reactivación: para cuentas inactivas, botón para enviar `POST /api/usuarios/reactivar.php` con Bearer token y restaurar a `activo = 1`.
  - Filtros y paginación: cambio dinámico de estado (`activos`, `inactivos`, `todos`) y navegación de páginas.
  - Preservación de WhatsApp: conservar funcionalidad de guardado rápido (`POST /api/usuarios/actualizar-whatsapp.php`).
  - Verificación: `node --check src/js/modules/users.js` + `rg 'innerHTML ='` sin interpolación no confiable.

---

## 5. Gate de testing 3-tier (obligatorio, subfase 4.5)

- [x] Suite CLI `tests/test-subfase-4.5.php` (§§1-5: auditoría estática, endpoints REST con admin token, salvaguardas RBAC/IDOR, módulo JS, HTTP en vivo). Ejecutar: `php tests/test-subfase-4.5.php > logs/subfase-4.5-cli.log 2>&1` → 100% verde (99/99).
- [x] Trazas HTTP curl registradas en `logs/subfase-4.5-http.log` (`GET index.php` con paginación y resumen, `POST crear.php` 201, `POST cambiar-rol.php` 200, 403 degradar ID #1, `POST restablecer-password.php` 200, `POST eliminar.php` 200, 403 eliminar ID #1, 409 con creaciones, `POST reactivar.php` 200, 401 sin auth, 403 artesano); divergencias según `docs/testing/protocolo-divergencia-cli-http.md` (H-015).
- [x] Reporte ejecutivo `docs/testing/subfase-4.5-usuarios.md` (plantilla `docs/testing/README.md`) con matriz + tabla AC→evidencia + "Fallos Detectados & Correcciones Quirúrgicas".
- [x] Regresión acumulada Fase 4: `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` (H-020, auto-descubre 4.5 y valida coherencia con `docs/testing/README.md`) → 841/841 en verde.
- [x] Regresión Fase 3 y aserciones regenerables: `php tests/test-subfase-3.6.5.php` y `php tests/cuenta-aserciones.php` (**1,287**, H-006) en verde.
- [x] **HALT:** registrar avance en `009/tasks.md` y esperar aprobación explícita antes de cerrar la feature.

---

## 6. Verificación & Cierre

- [x] Criterios de aceptación de `spec.md` al 100% (`- [x]` con tabla AC→evidencia en el reporte).
- [x] `spec/features/009-plan-maestro-fase-4/tasks.md`: subfase 4.5 marcada como completada.
- [x] `spec/constitution/roadmap.md`: subfase 4.5 y feature 008 movidas a **Hecho ✅**.
- [x] `docs/testing/README.md`: fila 4.5 añadida y total acumulado actualizado.
- [x] **HALT:** aprobación explícita del usuario antes de cualquier otra feature.
