# Guía How-To: Gestión de Roles (RBAC), Reseteo de Claves y Salvaguarda de Administrador

Esta guía operativa detalla los procedimientos para administrar el directorio de usuarios, asignar roles de acceso basado en roles (RBAC), restablecer contraseñas de forma segura y garantizar el principio de continuidad del sistema mediante la protección contra bloqueo del administrador principal.

---

## 1. Modelo de Roles y Permisos (RBAC)

**Crochet Manager** implementa un modelo de control de acceso jerárquico dividido en tres roles operativos:

| Rol | Privilegios Principales | Restricciones Operativas |
| :--- | :--- | :--- |
| **`admin`** | Acceso total al catálogo, pedidos globales, administración de usuarios, cambio de roles y reactivación de cuentas. | Sujeto a la salvaguarda de **ID #1** (no puede degradar ni eliminar al admin titular). |
| **`artesano`** | Publicación y edición de creaciones propias, gestión de stock y seguimiento de pedidos sobre sus piezas. | No puede editar creaciones de otros artesanos (protección IDOR) ni acceder al panel de usuarios. |
| **`asistente`** | Consulta de inventario, asistencia operativa en pedidos y marcaje de estados. | No puede eliminar creaciones ni alterar configuraciones críticas de usuario. |

---

## 2. Cambio de Rol de Usuario

Para actualizar el nivel de acceso de un miembro del taller:

### Mediante la Interfaz Gráfica (`/usuarios.php`)
1. Inicie sesión con una cuenta de rol `admin`.
2. Diríjase a la sección **Directorio del Equipo** en la barra de navegación.
3. Localice la fila del usuario correspondiente en la tabla `.table-artisan-team`.
4. En la columna de rol, seleccione el nuevo valor en el selector (`admin`, `artesano`, o `asistente`).
5. El sistema enviará una petición asíncrona inmediata y confirmará la actualización mediante un mensaje en pantalla.

### Mediante la API REST
Envíe una petición `POST` al endpoint `/api/usuarios/cambiar-rol.php` con el token Bearer del administrador:
```http
POST /api/usuarios/cambiar-rol.php HTTP/1.1
Host: localhost:8000
Authorization: Bearer <TOKEN_ADMIN>
Content-Type: application/json

{
  "usuario_id": 4,
  "nuevo_rol": "artesano"
}
```

Respuesta esperada (HTTP 200 OK):
```json
{
  "exito": true,
  "mensaje": "Rol actualizado correctamente a artesano.",
  "datos": {
    "usuario_id": 4,
    "rol": "artesano"
  }
}
```

---

## 3. Salvaguarda Incondicional del Administrador Titular (ID #1 / Invariante R-05)

Para prevenir incidentes en los que la plataforma quede sin administración técnica (bloqueo accidental o malintencionado):

- El usuario con identificador primario **ID #1** (`usuario: admin`) tiene una inmunidad inviolable en el backend.
- Si cualquier administrador intenta cambiar el rol del usuario **ID #1** o ejecutar su baja lógica (`eliminar.php`), el servicio `UsuarioService` y el controlador abortan inmediatamente la operación.
- El servidor emite un código de respuesta HTTP **403 Forbidden** con el siguiente sobre de error JSON:
```json
{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "Operación denegada: El usuario administrador principal (ID #1) no puede ser degradado ni desactivado."
  }
}
```
- En la interfaz web, el selector de rol para el usuario con **ID #1** aparece bloqueado y con un icono de candado que documenta esta política de seguridad.

---

## 4. Restablecimiento de Contraseñas

Existen dos vías para el cambio de credenciales:

### Procedimiento A: Autoservicio por el Propio Usuario
Si un usuario conoce su contraseña actual y desea actualizarla:
1. Diríjase a su perfil de usuario o envíe `POST /api/auth/cambiar-password.php`.
2. Proporcione la contraseña actual y la nueva contraseña (mínimo 8 caracteres, combinando mayúsculas, minúsculas y números).
3. El backend verifica el hash con `password_verify()` antes de almacenar el nuevo hash generado con `PASSWORD_BCRYPT`.

### Procedimiento B: Reseteo Administrativo por Olvido
Si un colaborador olvidó sus credenciales de acceso:
1. El `admin` ingresa al Directorio del Equipo (`/usuarios.php`).
2. Hace clic en el botón de restablecer clave junto al usuario afectado.
3. Se despliega el modal donde el administrador asigna una contraseña temporal segura.
4. La petición se envía a `/api/usuarios/restablecer-password.php`:
```http
POST /api/usuarios/restablecer-password.php HTTP/1.1
Authorization: Bearer <TOKEN_ADMIN>
Content-Type: application/json

{
  "usuario_id": 3,
  "nueva_password": "Temporal#Crochet2026"
}
```
5. Todas las sesiones activas del usuario son revocadas en la tabla `tokens_revocados` obligándolo a iniciar sesión con la nueva clave asignada.

---

## Resumen de Respuestas HTTP

- **200 OK:** Rol o contraseña modificados exitosamente.
- **400 Bad Request:** Formato de clave inválido o parámetros faltantes.
- **401 Unauthorized:** Token no suministrado o expirado.
- **403 Forbidden:** Intento de modificar al usuario con **ID #1** o usuario sin privilegios `admin`.
- **404 Not Found:** Identificador de usuario no existente o marcado como inactivo.
