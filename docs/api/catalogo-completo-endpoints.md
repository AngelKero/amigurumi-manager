# Referencia Técnica: Catálogo Completo de Endpoints REST

Este documento es la especificación canónica y exhaustiva de los 27 controladores REST que integran la API de **Crochet Manager**. Describe los contratos de solicitud, parámetros, códigos de respuesta HTTP y políticas de autorización para cada recurso.

---

## 1. Convenciones y Estándares HTTP

- **Transporte y Cabeceras:** Todo intercambio de datos se realiza bajo `Content-Type: application/json` (a excepción de endpoints de subida multipart en creaciones).
- **Autenticación:** Mediante cabecera `Authorization: Bearer <TOKEN_HMAC_SHA256>`.
- **Preflight CORS:** Toda ruta responde a peticiones HTTP `OPTIONS` con código de estado `204 No Content`.
- **Sobre Estándar de Respuesta (Envelope):**
  - Éxito estándar: `{"exito": true, "mensaje": "...", "datos": { ... }}`
  - Éxito paginado: `{"exito": true, "mensaje": "...", "datos": [ ... ], "paginacion": { ... }}`
  - Error estándar: `{"exito": false, "error": {"codigo": 4XX|5XX, "mensaje": "...", "detalles": [ ... ]}}`

### Códigos de Estado HTTP Utilizados
- **`200 OK`**: Petición procesada con éxito y retorno de datos.
- **`201 Created`**: Recurso persistido exitosamente en base de datos.
- **`204 No Content`**: Solicitud exitosa sin cuerpo de respuesta (ej. preflight CORS o borrado sin contenido).
- **`400 Bad Request`**: Parámetros obligatorios ausentes o formato JSON malformado.
- **`401 Unauthorized`**: Token de autenticación faltante, expirado o inválido.
- **`403 Forbidden`**: Autorización insuficiente, violación de pertenencia (IDOR) o salvaguarda de ID #1.
- **`404 Not Found`**: El recurso solicitado no existe o ha sido dado de baja lógica.
- **`409 Conflict`**: Conflicto de estado o duplicidad de identificadores únicos (ej. nombre de usuario).
- **`422 Unprocessable`**: Carga semánticamente inválida (reglas de negocio, stock insuficiente, teléfono no E.164).
- **`500 Internal`**: Error no controlado en el servidor capturado por el manejador global.

---

## 2. Módulo de Autenticación (`api/auth/`)

### 1. `/api/auth/login.php`
- **Método:** `POST`
- **Acceso:** Público (sujeto a rate-limiting: 5 fallos/usuario, 20 fallos/IP en ventana de 15 min).
- **Entrada:** `{"usuario": "artesana_maria", "password": "SecretPassword123"}`
- **Respuestas:** `200 OK` (token HMAC y datos de perfil), `400 Bad Request`, `401 Unauthorized`, `422 Unprocessable`, `500 Internal`.

### 2. `/api/auth/logout.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin`, `artesano`, `asistente`).
- **Comportamiento:** Registra el token Bearer en `tokens_revocados` mediante su reclamo `jti`.
- **Respuestas:** `200 OK`, `401 Unauthorized`.

### 3. `/api/auth/me.php`
- **Método:** `GET`
- **Acceso:** Autenticado.
- **Comportamiento:** Retorna la información de identidad del usuario activo decodificada del token.
- **Respuestas:** `200 OK`, `401 Unauthorized`.

### 4. `/api/auth/cambiar-password.php`
- **Método:** `POST`
- **Acceso:** Autenticado (autoservicio).
- **Entrada:** `{"password_actual": "...", "nueva_password": "..."}`
- **Respuestas:** `200 OK`, `400 Bad Request`, `401 Unauthorized`, `422 Unprocessable`.

---

## 3. Módulo de Creaciones y Catálogo (`api/creaciones/`)

### 5. `/api/creaciones/index.php`
- **Método:** `GET`
- **Acceso:** Público.
- **Parámetros Query:** `categoria`, `artesano_id`, `precio_min`, `precio_max`, `q` (búsqueda), `pagina`, `limite`.
- **Respuestas:** `200 OK` (listado paginado).

### 6. `/api/creaciones/detalle.php`
- **Método:** `GET`
- **Acceso:** Público.
- **Parámetros Query:** `id` (entero).
- **Respuestas:** `200 OK`, `400 Bad Request`, `404 Not Found`.

### 7. `/api/creaciones/crear.php`
- **Método:** `POST` (Soporta `multipart/form-data` para subida de imágenes o `application/json`).
- **Acceso:** Autenticado (`admin`, `artesano`).
- **Entrada:** `titulo`, `descripcion`, `categoria`, `precio` (centavos), `costo_materiales`, `horas_tejido`, `cantidad_stock`, `es_sobre_encargo`, `foto` (opcional).
- **Respuestas:** `201 Created`, `400 Bad Request`, `401 Unauthorized`, `422 Unprocessable`.

### 8. `/api/creaciones/actualizar.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin` o propietario de la pieza vía IDOR check).
- **Entrada:** `id`, campos modificables de la creación.
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable`.

### 9. `/api/creaciones/eliminar.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin` o propietario).
- **Comportamiento:** Borrado lógico universal (`activo = 0`). No borra archivos físicos (`unlink()` prohibido).
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`.

### 10. `/api/creaciones/restaurar.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin`).
- **Comportamiento:** Reactiva una pieza previamente dada de baja (`activo = 1`).
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`.

### 11. `/api/creaciones/mias.php`
- **Método:** `GET`
- **Acceso:** Autenticado (`admin`, `artesano`).
- **Comportamiento:** Devuelve el inventario propio del artesano conectado, incluyendo piezas sin stock.
- **Respuestas:** `200 OK`, `401 Unauthorized`.

### 12. `/api/creaciones/artesanos.php`
- **Método:** `GET`
- **Acceso:** Público.
- **Comportamiento:** Lista todos los creadores activos que tienen creaciones en el catálogo público.
- **Respuestas:** `200 OK`.

### 13. `/api/creaciones/ajustar-stock.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin` o propietario).
- **Entrada:** `{"creacion_id": 10, "cantidad": 5, "operacion": "sumar|restar|fijar"}`
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `422 Unprocessable`.

### 14. `/api/creaciones/toggle-encargo.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin` o propietario).
- **Entrada:** `{"creacion_id": 10, "es_sobre_encargo": 1}`
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`.

---

## 4. Módulo de Pedidos y Encargos (`api/pedidos/`)

### 15. `/api/pedidos/index.php`
- **Método:** `GET`
- **Acceso:** Autenticado (`admin` ve todos; `artesano` ve solo los correspondientes a sus creaciones).
- **Respuestas:** `200 OK`, `401 Unauthorized`.

### 16. `/api/pedidos/crear.php`
- **Método:** `POST`
- **Acceso:** Autenticado o administrativo.
- **Entrada:** `creacion_id`, `cantidad`, `cliente_nombre`, `cliente_contacto`, `notas`.
- **Respuestas:** `201 Created`, `400 Bad Request`, `422 Unprocessable`.

### 17. `/api/pedidos/solicitar.php`
- **Método:** `POST`
- **Acceso:** Público (Checkout desde vitrina).
- **Entrada:** `{"creacion_id": 5, "cantidad": 1, "cliente_nombre": "Carlos Gómez", "cliente_telefono": "5512345678", "notas": "..."}`
- **Comportamiento:** Transacción atómica inmediata que reserva stock y congela `precio_final`.
- **Respuestas:** `201 Created`, `400 Bad Request`, `409 Conflict`, `422 Unprocessable`.

### 18. `/api/pedidos/cambiar-estado.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin` o artesano titular).
- **Entrada:** `{"pedido_id": 8, "nuevo_estado": "En Proceso|Completado"}`
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `422 Unprocessable`.

### 19. `/api/pedidos/cancelar.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin` o artesano titular).
- **Comportamiento:** Transacción atómica idempotente que actualiza estado a `Cancelado` y reintegra stock físico.
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`.

---

## 5. Módulo de Usuarios y Directorio (`api/usuarios/`)

### 20. `/api/usuarios/index.php`
- **Método:** `GET`
- **Acceso:** Exclusivo `admin`.
- **Parámetros:** `incluir_inactivos=1` (opcional).
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`.

### 21. `/api/usuarios/crear.php`
- **Método:** `POST`
- **Acceso:** Exclusivo `admin`.
- **Entrada:** `nombre`, `usuario`, `password`, `rol` (`admin|artesano|asistente`), `whatsapp` (opcional).
- **Respuestas:** `201 Created`, `400 Bad Request`, `403 Forbidden`, `409 Conflict`, `422 Unprocessable`.

### 22. `/api/usuarios/actualizar.php`
- **Método:** `POST`
- **Acceso:** Exclusivo `admin` o propio usuario para campos de perfil.
- **Entrada:** `id`, `nombre`, `whatsapp`.
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`.

### 23. `/api/usuarios/cambiar-rol.php`
- **Método:** `POST`
- **Acceso:** Exclusivo `admin`. Protegido contra alteración de **ID #1** (R-05).
- **Entrada:** `{"usuario_id": 3, "nuevo_rol": "artesano"}`
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden` (si usuario es ID #1), `422 Unprocessable`.

### 24. `/api/usuarios/restablecer-password.php`
- **Método:** `POST`
- **Acceso:** Exclusivo `admin`.
- **Entrada:** `{"usuario_id": 3, "nueva_password": "..."}`
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`.

### 25. `/api/usuarios/eliminar.php`
- **Método:** `POST`
- **Acceso:** Exclusivo `admin`. Protegido contra eliminación de **ID #1** (R-05).
- **Comportamiento:** Borrado lógico (`activo = 0`).
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`.

### 26. `/api/usuarios/reactivar.php`
- **Método:** `POST`
- **Acceso:** Exclusivo `admin`.
- **Comportamiento:** Reactiva cuenta inactiva (`activo = 1`).
- **Respuestas:** `200 OK`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`.

### 27. `/api/usuarios/actualizar-whatsapp.php`
- **Método:** `POST`
- **Acceso:** Autenticado (`admin`, `artesano`, `asistente`).
- **Entrada:** `{"whatsapp": "525512345678"}` (validación E.164).
- **Respuestas:** `200 OK`, `400 Bad Request`, `401 Unauthorized`, `422 Unprocessable`.
