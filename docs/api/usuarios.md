# API REST: Gestión de Usuarios & Roles (`/api/usuarios/`)

[← Volver al Índice de API](./README.md)

Este módulo provee la administración completa del equipo de artesanos y colaboradores de la plataforma, gobernado por control de acceso basado en roles (**RBAC**), salvaguarda inviolable para el administrador titular (ID #1), ciclo de baja lógica universal y reactivación de cuentas.

---

## 1. Directorio de Creadores (`GET /api/usuarios/index.php`)

Retorna la lista paginada de usuarios con el conteo de creaciones activas asociadas. Permite filtrar por estado de cuenta.

- **Acceso:** Protegido (`RoleGuard::adminOnly()`).
- **Método HTTP:** `GET`
- **Cabeceras:** `Authorization: Bearer <token>`

### Parámetros de Consulta (Query String)
| Parámetro | Tipo | Opcional | Descripción |
| :--- | :---: | :---: | :--- |
| `estado` | `string` | Sí | `activos` (defecto: solo cuentas activas), `inactivos` (solo cuentas dadas de baja lógica), `todos` (directorio histórico completo). |
| `pagina` | `int` | Sí | Número de página (defecto: `1`). |
| `limite` | `int` | Sí | Usuarios por página (defecto: `10`, máx: `50`). |

### Ejemplo de Petición
```bash
curl -X GET "http://localhost:8000/api/usuarios/index.php?estado=activos&pagina=1" \
  -H "Authorization: Bearer <token>"
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Listado de creadores obtenido exitosamente.",
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "rol": "admin",
      "whatsapp": "5501112222",
      "activo": 1,
      "creado_en": "2026-09-11 13:30:52",
      "eliminado_en": null,
      "creaciones_asociadas": 3
    },
    {
      "id": 2,
      "username": "artesana_ana",
      "rol": "artesano",
      "whatsapp": "5512345678",
      "activo": 1,
      "creado_en": "2026-09-11 13:30:52",
      "eliminado_en": null,
      "creaciones_asociadas": 2
    }
  ],
  "paginacion": {
    "total_items": 2,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 10,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

---

## 2. Registrar Creador (`POST /api/usuarios/crear.php`)

Da de alta a un nuevo usuario en la plataforma con contraseña encriptada con bcrypt.

- **Acceso:** Protegido (`RoleGuard::adminOnly()`).
- **Método HTTP:** `POST`
- **Cabeceras:** `Authorization: Bearer <token>`, `Content-Type: application/json`

### Parámetros de Entrada (JSON Body)
| Campo | Tipo | Obligatorio | Descripción / Reglas |
| :--- | :---: | :---: | :--- |
| `username` | `string` | Sí | Nombre alfanumérico único (3 a 50 caracteres). |
| `password` | `string` | Sí | Contraseña inicial (mínimo 6 caracteres). |
| `rol` | `string` | No | `admin`, `artesano` o `asistente` (defecto: `artesano`). |
| `whatsapp` | `string` | No | WhatsApp comercial opcional (máx 20 caracteres, 8–15 dígitos; `null`/vacío = sin número). |

### Respuesta Exitosa (`HTTP 201 Created`)
```json
{
  "exito": true,
  "mensaje": "Creador registrado exitosamente.",
  "datos": {
    "id": 4,
    "username": "artesano_mateo",
    "rol": "artesano",
    "whatsapp": "5590001111"
  }
}
```

### Errores Posibles
- **`HTTP 409 Conflict`:** El nombre de usuario ya se encuentra ocupado.
- **`HTTP 422 Unprocessable Entity`:** Contraseña menor a 6 caracteres o rol inválido.

---

## 3. Modificar Rol de Usuario (`POST /api/usuarios/cambiar-rol.php`)

Actualiza los privilegios de acceso de un creador.

- **Acceso:** Protegido (`RoleGuard::adminOnly()`).
- **Salvaguarda Inviolable de ID #1:** Si se intenta modificar el rol del administrador titular (ID #1), la petición es rechazada con **`HTTP 403 Forbidden`**.

### Parámetros de Entrada (JSON Body)
```json
{
  "id": 2,
  "rol": "admin"
}
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Rol de usuario actualizado exitosamente.",
  "datos": {
    "id": 2,
    "username": "artesana_ana",
    "rol": "admin"
  }
}
```

---

## 4. Modificar Nombre de Usuario (`POST /api/usuarios/actualizar.php`)

Permite al administrador corregir el nombre de un creador, validando sintaxis y disponibilidad.

- **Acceso:** Protegido (`RoleGuard::adminOnly()`).
- **Payload:** `{"id": 2, "username": "ana_crochet_creaciones"}`

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Nombre de usuario actualizado exitosamente.",
  "datos": {
    "id": 2,
    "username": "ana_crochet_creaciones",
    "rol": "artesano"
  }
}
```

---

## 5. Restablecer Contraseña Administrativa (`POST /api/usuarios/restablecer-password.php`)

Permite al administrador generar una contraseña temporal autogenerada (formato `Crochet!<hex>!`) o asignar una clave manual para entregar al artesano que olvidó su acceso.

- **Acceso:** Protegido (`RoleGuard::adminOnly()`).
- **Payload:** `{"id": 2}` *(autogenerada)* o `{"id": 2, "nueva_password": "NuevaClaveManual2026"}`.

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Contraseña restablecida exitosamente para el usuario 'artesana_ana'. Entregue la clave temporal al artesano: Crochet!4d8ec3!",
  "datos": {
    "id": 2,
    "username": "artesana_ana",
    "password_temporal": "Crochet!4d8ec3!",
    "es_autogenerada": true
  }
}
```

---

## 6. Eliminar Usuario — Baja Lógica (`POST /api/usuarios/eliminar.php`)

Aplica una baja lógica estricta a la cuenta (`activo = 0`, `eliminado_en = datetime(...)`). Invalida inmediatamente cualquier Bearer Token activo para este usuario.

- **Acceso:** Protegido (`RoleGuard::adminOnly()`).
- **Salvaguardas Críticas:**
  1. **ID #1 Inviolable:** Intento de eliminar al administrador raíz retorna **`HTTP 403 Forbidden`**.
  2. **Auto-Eliminación:** Intento de eliminar la propia cuenta en sesión retorna **`HTTP 403 Forbidden`**.
  3. **Integridad Referencial:** Si el usuario tiene creaciones activas asociadas en catálogo, la baja es denegada con **`HTTP 409 Conflict`** (*"Reasigne o elimine sus piezas antes de continuar"*).
  4. **Doble Baja:** Si la cuenta ya está inactiva, retorna **`HTTP 409 Conflict`**.

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Usuario eliminado lógicamente de la plataforma.",
  "datos": {
    "id": 4,
    "activo": 0
  }
}
```

---

## 7. Reactivar Cuenta Inactiva (`POST /api/usuarios/reactivar.php`)

Restaura el acceso de un usuario previamente dado de baja lógica (`activo = 1`, `eliminado_en = NULL`), permitiéndole volver a iniciar sesión.

- **Acceso:** Protegido (`RoleGuard::adminOnly()`).
- **Payload:** `{"id": 4}`

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Usuario reactivado exitosamente en la plataforma.",
  "datos": {
    "id": 4,
    "username": "artesano_mateo",
    "rol": "artesano",
    "activo": 1
  }
}
```

### Errores Posibles
- **`HTTP 409 Conflict`:** La cuenta ya se encuentra activa.
- **`HTTP 404 Not Found`:** El usuario no existe en la base de datos.

---

## 8. Actualizar WhatsApp Comercial (`POST /api/usuarios/actualizar-whatsapp.php`)

Registra o retira el número de WhatsApp con el que los compradores coordinan con el artesano (Feature 010).

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()` + IDOR en servicio: `admin` edita cualquiera, el resto solo el propio → **`HTTP 403 Forbidden`** en otro caso).
- **Payload:** `{"id": 2, "whatsapp": "+52 55 1234 5678"}` (`null`/vacío retira el número; el checkout oculta entonces el botón).

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "WhatsApp de contacto actualizado exitosamente.",
  "datos": {
    "id": 2,
    "username": "artesana_ana",
    "whatsapp": "+52 55 1234 5678"
  }
}
```

### Errores Posibles
- **`HTTP 422 Unprocessable Entity`:** Más de 20 caracteres o menos de 8 / más de 15 dígitos.
- **`HTTP 403 Forbidden`:** Intentar modificar el número de otro usuario sin ser admin.
- **`HTTP 404 Not Found`:** El usuario no existe.

---

## 9. Especificación Futura / Backlog: Baneo Total de Usuario & Cascada de Bajas Lógicas

> **Nota de Diseño & Backlog:** Esta funcionalidad queda documentada para implementación futura como extensión de la administración de usuarios y mitigación de infractores o desvinculaciones totales.

### 9.1 Problema Operativo Actual
En el flujo actual de `POST /api/usuarios/eliminar.php`, si un artesano posee creaciones activas en el catálogo, el backend bloquea la eliminación con `HTTP 409 Conflict`:
`No se puede eliminar al usuario 'X' porque tiene N creación(es) asociada(s) en el catálogo. Reasigne o elimine sus piezas antes de continuar.`

Esto obliga al administrador a retirar manualmente cada creación una por una antes de poder suspender la cuenta.

### 9.2 Propuesta Funcional: Baneo Total (`POST /api/usuarios/banear.php` o flag `cascada: true`)
La funcionalidad de **Baneo Total** permitirá al administrador suspender a un usuario en una sola operación atómica, aplicando una cascada de bajas lógicas:

1. **Inactivación de la Cuenta:**
   - La cuenta del usuario se marca como inactiva (`activo = 0`, `eliminado_en = datetime('now', 'localtime')`).
   - Se revoca inmediatamente cualquier token Bearer activo en la denylist (`tokens_revocados`), cerrando sus sesiones activas de inmediato (H-002).
2. **Cascada de Baja Lógica sobre Creaciones (Invariante R-01):**
   - Dentro de una transacción atómica SQLite (`BEGIN IMMEDIATE TRANSACTION`), se da de baja lógica a todas las creaciones asociadas al artesano:
     ```sql
     UPDATE creaciones 
     SET activo = 0, eliminado_en = datetime('now', 'localtime') 
     WHERE artesano_id = :id AND activo = 1;
     ```
   - Las piezas desaparecen de forma inmediata de la vitrina pública del catálogo (`index.php`), protegiendo a los clientes.
3. **Preservación Estricta de Assets Multimedia (Invariante R-02 / ADR-008):**
   - **CERO `unlink()`:** Las fotos de los productos en `uploads/` se conservan intactas en disco para mantener la validez visual de los pedidos históricos ya facturados.
4. **Tratamiento de Pedidos Existentes:**
   - Ningún registro de la tabla `pedidos` se elimina físicamente (prohibido `DELETE FROM`).
   - Los pedidos previos permanecen con su `creacion_id` original apuntando a la fila inactiva de la creación, resguardando la auditoría contable (precio en centavos, comprobantes, pagos).
   - Opcionalmente, pedidos en estado `Pendiente` o `En Proceso` podrán alertar al administrador para su reasignación o cancelación manual con restitución de inventario.
5. **Salvaguarda Inviolable del Administrador Raíz (Invariante R-05):**
   - El administrador raíz (`id: 1`, `@admin`) **nunca podrá ser baneado** bajo ninguna circunstancia (`HTTP 403 Forbidden`).
