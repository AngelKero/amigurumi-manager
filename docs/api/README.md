# API REST: Estándares y Convenciones Globales

[← Volver al Hub Principal de Documentación](../README.md)

Este documento define el estándar arquitectónico universal para todos los endpoints del backend en `api/`. Todo controlador del sistema implementa de forma estricta las convenciones detalladas a continuación.

---

## 1. Módulos de la API

La API se organiza en 4 dominios REST modulares:

| Módulo | Ruta Base | Descripción | Documentación |
| :--- | :--- | :--- | :--- |
| **Autenticación** | `/api/auth/` | Login stateless, logout, perfil `/me` y autoservicio de contraseña. | [Ver auth.md](./auth.md) |
| **Creaciones & Catálogo** | `/api/creaciones/` | Catálogo público, lista de artesanos, CRUD, inventario, stock y fotos. | [Ver creaciones.md](./creaciones.md) |
| **Pedidos & Encargos** | `/api/pedidos/` | Solicitudes públicas, gestión de pedidos, WhatsApp y restitución de stock. | [Ver pedidos.md](./pedidos.md) |
| **Usuarios & Creadores** | `/api/usuarios/` | Directorio, altas, roles RBAC, salvaguarda ID #1, baja lógica y reactivación. | [Ver usuarios.md](./usuarios.md) |

---

## 2. Protocolo y Servidor Base

- **Servidor Local de Desarrollo:** `http://localhost:8000`
- **Prefijo Global de Endpoints:** `/api/<modulo>/<accion>.php`
- **Formato de Petición:** `application/json` (o `multipart/form-data` para subidas de archivos).
- **Formato de Respuesta:** `application/json; charset=utf-8` emitido exclusivamente por `App\Core\Response`.
- **Cero Fugas HTML:** `App\Core\ErrorHandler` intercepta cualquier advertencia, error o excepción fatal, limpiando buffers con `ob_end_clean()` y emitiendo JSON 500 estandarizado.

---

## 3. Formato Estándar de Respuesta (JSON Envelope)

### 3.1 Respuesta Exitosa (2xx)
```json
{
  "exito": true,
  "mensaje": "Descripción legible de la operación realizada.",
  "datos": { ... }
}
```
*Si la operación no produce datos (ej. logout o preflight), `"datos"` se retorna como `null`.*

### 3.2 Respuesta con Paginación
```json
{
  "exito": true,
  "mensaje": "Listado obtenido exitosamente.",
  "datos": [ ... ],
  "paginacion": {
    "total_items": 45,
    "pagina_actual": 1,
    "total_paginas": 4,
    "limite": 12,
    "tiene_siguiente": true,
    "tiene_anterior": false
  }
}
```

### 3.3 Respuesta de Error (4xx / 5xx)
```json
{
  "exito": false,
  "error": {
    "codigo": 422,
    "mensaje": "Descripción del error en español.",
    "detalles": [ ... ]
  }
}
```

---

## 4. Códigos de Estado HTTP

| Código | Significado Canónico | Uso en el Sistema |
| :---: | :--- | :--- |
| **200** | `OK` | Consulta o actualización exitosa con contenido. |
| **201** | `Created` | Creación exitosa de un recurso (usuario, creación, pedido). |
| **204** | `No Content` | Resolución de preflight CORS `OPTIONS`. |
| **400** | `Bad Request` | JSON malformado o payload de entrada sintácticamente inválido. |
| **401** | `Unauthorized` | Token Bearer faltante, expirado, inválido o cuenta desactivada. |
| **403** | `Forbidden` | Rol insuficiente (RoleGuard), violación IDOR o salvaguarda de ID #1. |
| **404** | `Not Found` | Recurso no encontrado o dado de baja lógica (`activo = 0`). |
| **405** | `Method Not Allowed` | Método HTTP no soportado (ej. POST en endpoint exclusivo GET). |
| **409** | `Conflict` | Conflicto de estado (username duplicado, doble baja, creaciones asociadas). |
| **422** | `Unprocessable Entity` | Violación de validaciones semánticas o restricciones CHECK. |
| **500** | `Internal Server Error` | Excepción no capturada en el servidor (capturada por ErrorHandler). |

---

## 5. Autenticación Stateless y Cabeceras

Los endpoints protegidos requieren la cabecera HTTP `Authorization`:
```http
Authorization: Bearer <token_hmac_sha256>
```
El token es emitido por `POST /api/auth/login.php`, tiene una vigencia de 24 horas (86,400 segundos) y es validado en cada petición por `App\Middleware\AuthGuard`.

### Compatibilidad con Apache FastCGI
El archivo `.htaccess` en la raíz reenvía automáticamente la cabecera al entorno de PHP:
```apache
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

---

## 6. Preflight CORS (Cross-Origin Resource Sharing)

Todos los endpoints invocan `Response::handleCors()` antes de procesar la lógica de negocio. Ante peticiones HTTP con método `OPTIONS`, el servidor responde inmediatamente con código **204 No Content** y las siguientes cabeceras:
```http
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Max-Age: 86400
```

---

## 7. Estándar Universal de Moneda

1. **Persistencia:** Todos los valores monetarios se almacenan como **enteros en centavos** en SQLite (`precio`, `costo_materiales`, `precio_final`). Queda estrictamente prohibido el uso de flotantes (`REAL`) para dinero.
2. **Enriquecimiento:** `App\Utils\CurrencyHelper` provee en las respuestas JSON tanto el entero en centavos como la cadena formateada en pesos mexicanos (ej. `"$240.00 MXN"`).

---

## 8. Estándar Universal de Baja Lógica (Zero Physical Delete)

1. Queda estrictamente prohibido ejecutar sentencias SQL `DELETE FROM` en las tablas `usuarios`, `creaciones` y `pedidos`.
2. Las eliminaciones se aplican lógicamente:
   ```sql
   UPDATE <tabla> SET activo = 0, eliminado_en = datetime('now', 'localtime') WHERE id = :id AND activo = 1;
   ```
3. Las consultas de lectura filtran por defecto `WHERE activo = 1`.
4. **Preservación de Archivos:** Las imágenes de creaciones dadas de baja lógica **NUNCA se borran del disco con `unlink()`** para preservar las miniaturas en pedidos históricos.
