# API REST: Autenticación & Sesión (`/api/auth/`)

[← Volver al Índice de API](./README.md)

Este módulo gestiona la emisión y validación de tokens de acceso Bearer (HMAC-SHA256) sin estado, la consulta del perfil activo y el autoservicio de cambio de contraseñas.

---

## 1. Iniciar Sesión (`POST /api/auth/login.php`)

Autentica a un usuario (`admin`, `artesano`, `asistente`), valida su contraseña con `password_verify()` y emite un Bearer Token con 24 horas de vigencia (86,400s).

- **Acceso:** Público (Unauthenticated)
- **Método HTTP:** `POST`
- **Cabeceras:** `Content-Type: application/json`

### Parámetros de Entrada (JSON Body)
| Campo | Tipo | Obligatorio | Descripción / Reglas |
| :--- | :---: | :---: | :--- |
| `username` | `string` | Sí | Nombre de usuario registrado (3 a 50 caracteres). |
| `password` | `string` | Sí | Contraseña en texto plano. |

### Ejemplo de Petición
```bash
curl -X POST http://localhost:8000/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin123"}'
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Autenticación exitosa. Token emitido.",
  "datos": {
    "token": "eyJzdWIiOjEsInVzZXJuYW1lIjoiYWRtaW4iLCJyb2wiOiJhZG1pbiIsImlhdCI6MTc4OTIzNTMwOCwiZXhwIjoxNzg5MzIxNzA4LCJqdGkiOiJiMWE3MDMxNTlmOWRkZTY2ZTA1ODI2MDZmNmVjNjQyZCJ9.5bb046f117afb512766d72e5ba3ec04c38c3df381b04749e80bae83d1ca67371",
    "tipo_token": "Bearer",
    "expira_en": 86400,
    "usuario": {
      "id": 1,
      "username": "admin",
      "rol": "admin",
      "creado_en": "2026-09-11 13:30:52"
    }
  }
}
```

### Errores Posibles
- **`HTTP 401 Unauthorized`:** Credenciales incorrectas o cuenta inactivada (`activo = 0`).
  *(Mitigación de timing attack activa: compara contra dummy hash si el usuario no existe).*
- **`HTTP 422 Unprocessable Entity`:** Campos `username` o `password` vacíos o ausentes.
- **`HTTP 405 Method Not Allowed`:** Método HTTP distinto a `POST`.

---

## 2. Cerrar Sesión (`POST /api/auth/logout.php`)

Instruye al cliente a descartar el Bearer Token almacenado localmente. Dado que la arquitectura es sin estado (stateless), la invalidación inmediata reside en el cliente.

- **Acceso:** Público / Autenticado
- **Método HTTP:** `POST`

### Ejemplo de Petición
```bash
curl -X POST http://localhost:8000/api/auth/logout.php
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Sesión cerrada exitosamente. Descarte el token del cliente.",
  "datos": null
}
```

---

## 3. Consultar Perfil Activo (`GET /api/auth/me.php`)

Recupera los datos del usuario autenticado a partir del Bearer Token provisto en la cabecera.

- **Acceso:** Protegido (`AuthGuard`)
- **Método HTTP:** `GET`
- **Cabeceras:** `Authorization: Bearer <token>`

### Ejemplo de Petición
```bash
curl -X GET http://localhost:8000/api/auth/me.php \
  -H "Authorization: Bearer <token>"
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Perfil de usuario recuperado exitosamente.",
  "datos": {
    "id": 1,
    "username": "admin",
    "rol": "admin",
    "creado_en": "2026-09-11 13:30:52"
  }
}
```

### Errores Posibles
- **`HTTP 401 Unauthorized`:** Token no provisto, firma HMAC alterada, token expirado o cuenta desactivada (`activo = 0`).

---

## 4. Autoservicio: Cambiar Contraseña Propia (`POST /api/auth/cambiar-password.php`)

Permite a cualquier usuario autenticado actualizar su contraseña de forma autónoma. Valida que la contraseña actual sea correcta y que la nueva tenga al menos 6 caracteres.

- **Acceso:** Protegido (`AuthGuard`)
- **Método HTTP:** `POST`
- **Cabeceras:** `Authorization: Bearer <token>`, `Content-Type: application/json`

### Parámetros de Entrada (JSON Body)
| Campo | Tipo | Obligatorio | Descripción / Reglas |
| :--- | :---: | :---: | :--- |
| `password_actual` | `string` | Sí | Contraseña actual del usuario para verificar identidad. |
| `nueva_password` | `string` | Sí | Nueva contraseña (mínimo 6 caracteres). |

### Ejemplo de Petición
```bash
curl -X POST http://localhost:8000/api/auth/cambiar-password.php \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"password_actual": "admin123", "nueva_password": "MiNuevaClaveSegura2026"}'
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Contraseña actualizada exitosamente.",
  "datos": {
    "id": 1,
    "username": "admin"
  }
}
```

### Errores Posibles
- **`HTTP 401 Unauthorized`:** La contraseña actual es incorrecta o falta el token Bearer.
- **`HTTP 422 Unprocessable Entity`:** Campos vacíos o nueva contraseña menor a 6 caracteres.
