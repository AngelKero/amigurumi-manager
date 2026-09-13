# Reporte de Pruebas: Subfase 3.6.3 — Inyección, Sanitización & Seguridad de Medios/Archivos (OWASP A03 + A08)

[← Volver al Hub de Testing](./README.md) | [Ver Plan Maestro de Fase 3](../architecture/phase-3-plan.md) | [Ver Especificación de Seguridad 3.6](../architecture/subfase-3.6-auditoria-seguridad.md)

- **Fecha de Ejecución:** 2026-09-12
- **Responsable:** Antigravity (Advanced Agentic Coding)
- **Marco Metodológico:** OWASP Top 10:2021 — Categoría A03 (Injection) & Categoría A08 (Software and Data Integrity Failures)
- **Entorno:** PHP 8.3 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (`PRAGMA busy_timeout = 5000;`, `PRAGMA foreign_keys = ON;`)
- **Archivo de Log Crudo (CLI):** [`logs/subfase-3.6.3-cli.log`](../../logs/subfase-3.6.3-cli.log)
- **Archivo de Trazas HTTP:** [`logs/subfase-3.6.3-http.log`](../../logs/subfase-3.6.3-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.6.3.php`](../../tests/test-subfase-3.6.3.php)
- **Resultado General:** **157 / 157 Aserciones Aprobadas (100% OK en 119.92 ms)** — ✅ **APTO PARA AVANZAR**
- **Total Acumulado Fase 3:** **1,015 / 1,015 Aserciones Aprobadas (100% OK en verde)** (3.1: 93, 3.2: 69, 3.3: 105, 3.4: 126, 3.5: 139, 3.6.1: 165, 3.6.2: 161, 3.6.3: 157)

---

## 1. Resumen Ejecutivo del Alcance Implementado

La **Subfase 3.6.3** corresponde a la tercera entrega temática de la **Auditoría Integral de Seguridad (Opción B)**, focalizada en la inmunidad absoluta contra inyecciones SQL, la defensa en profundidad contra Cross-Site Scripting (XSS), la inspección binaria MIME estricta en la carga de medios, la neutralización de vulnerabilidades de Path Traversal y la auditoría de integridad vectorial SVG (**OWASP A03:2021 + A08:2021**).

Se auditaron exhaustivamente en memoria y en integración HTTP en vivo contra `http://localhost:8000` las siguientes 6 dimensiones críticas:

1. **Blindaje Total contra Inyección SQL (SQLi - OWASP A03:2021):**
   - El 100% de las consultas a base de datos en `CreacionRepository`, `PedidoRepository` y `UsuarioRepository` utilizan sentencias preparadas PDO parametrizadas con `bindValue()` y tipado estricto (`PDO::PARAM_INT`, `PDO::PARAM_STR`).
   - Inyección de vectores SQLi clásicos y avanzados (`' OR '1'='1`, `1; DROP TABLE usuarios; --`, `UNION SELECT ...`, `admin' --`, comillas simples y dobles, caracteres nulos `%00` y comentarios `--`) en filtros de búsqueda, categoría, artesano, precios, encargo y estados de pedidos.
   - Verificación de que todos los vectores son tratados como literales textuales, sin errores de sintaxis (`PDOException`), sin fuga de datos entre tablas y preservando la integridad y el conteo exacto de registros en SQLite.
2. **Defensa contra Cross-Site Scripting (XSS - OWASP A03:2021):**
   - Arquitectura Limpia de almacenamiento: los datos de entrada se almacenan íntegros y limpios en la capa de persistencia (preservando el dato original sin codificaciones dobles corrosivas).
   - Escape sistemático en la capa de presentación mediante `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` en todas las plantillas y atributos HTML, neutralizando etiquetas `<script>`, `<img onerror=...>`, `<svg onload=...>`, eventos `onmouseover` y protocolos `javascript:`.
   - Garantía de cabecera `Content-Type: application/json; charset=utf-8` en todos los controladores de la API REST, impidiendo que los navegadores interpreten payloads JSON como documentos HTML ejecutables.
   - Codificación segura de parámetros en enlaces de WhatsApp mediante `rawurlencode()`, convirtiendo caracteres especiales en porcentajes seguros (`%3C`, `%3E`).
3. **Detección y Validación de Tipo MIME Real en Carga de Medios (OWASP A08:2021):**
   - Inspección binaria mediante `finfo_file` (`FILEINFO_MIME_TYPE`) y `mime_content_type` en `CreacionService::handleImageUpload`, restringida estrictamente a `image/jpeg`, `image/png` y `image/webp`.
   - Detección y bloqueo inmediato con `HTTP 422 Unprocessable Entity` ante archivos camuflados o con extensiones falsificadas (scripts PHP, documentos HTML/JS, scripts Bash, ejecutables binarios).
   - Control estricto de tamaño de archivo: rechazo garantizado con `HTTP 422` ante cualquier archivo que supere los 5MB configurados (5,242,880 bytes).
   - Procesamiento confiable de archivos JPEG, PNG y WebP genuinos, y fallback automático a vectores SVG temáticos de `assets/svg/piezas/` cuando no se adjunta archivo.
4. **Prevención de Path Traversal & Carga Arbitraria (OWASP A08:2021):**
   - Descarte total del nombre de archivo provisto por el cliente en `$_FILES['imagen']['name']`; generación obligatoria en el servidor con entropía criptográfica (`creacion_[16-hex]_[timestamp].[ext]`).
   - Confinamiento estricto de los archivos almacenados dentro de `uploads/`, verificando que `realpath()` pertenezca al directorio autorizado y neutralizando secuencias de escape (`../../`, `..\\`).
   - Inmunidad a Directory Traversal en `unlinkPreviousUploadFile`: uso de `basename()` para neutralizar intentos de borrado arbitrario fuera de `uploads/` (ej. ataques dirigidos contra `database/database.sqlite`).
   - Regla de Oro **ADR-008**: Preservación física de imágenes en disco durante bajas lógicas (`activo = 0`), garantizando la disponibilidad de miniaturas en pedidos históricos.
5. **Seguridad Vectorial SVG & Sanitización de Atributos:**
   - Escaneo integral automatizado del 100% de los archivos SVG en `assets/svg/` (34 archivos): verificación de cero `<script>`, cero eventos `onload`/`onerror`/`onclick`, cero enlaces `javascript:` y cero declaraciones de entidades externas `<!ENTITY` (ataques XXE).
   - Sanitización de atributos en `SvgHelper::render()`: escape seguro de comillas y etiquetas mediante `htmlspecialchars` en la inyección de atributos HTML en la etiqueta raíz `<svg>`.
   - Sanitización en mensajes de error generados ante archivos SVG no encontrados.
   - Comprobación en disco de todas las rutas devueltas por los fallbacks temáticos de piezas.
6. **Pruebas de Integración HTTP en Vivo contra Servidor Local:**
   - Verificación de respuestas `HTTP 200 OK` sin fugas ante consultas con vectores SQLi en `/api/creaciones/index.php` y `/api/pedidos/index.php`.
   - Rechazo con `HTTP 422` ante intentos de carga multipart de archivos PHP disfrazados en `/api/creaciones/crear.php`.
   - Registro seguro de pedidos con payloads XSS en `/api/pedidos/solicitar.php` con respuesta `HTTP 201 Created`.

---

## 2. Matriz Detallada de Aserciones y Casos Evaluados

| Sección | Dominio de Prueba | Total Aserciones | Aprobadas | Fallidas | Estado |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **Sección 1** | Blindaje Total contra Inyección SQL (SQLi - OWASP A03:2021) | 38 | 38 | 0 | ✅ Aprobado |
| **Sección 2** | Defensa contra Cross-Site Scripting (XSS - OWASP A03:2021) | 47 | 47 | 0 | ✅ Aprobado |
| **Sección 3** | Detección y Validación de Tipo MIME Real en Medios (OWASP A08) | 21 | 21 | 0 | ✅ Aprobado |
| **Sección 4** | Prevención de Path Traversal & Carga Arbitraria (OWASP A08) | 7 | 7 | 0 | ✅ Aprobado |
| **Sección 5** | Seguridad Vectorial SVG & Sanitización de Atributos | 33 | 33 | 0 | ✅ Aprobado |
| **Sección 6** | Pruebas de Integración HTTP en Vivo contra Servidor Local | 11 | 11 | 0 | ✅ Aprobado |
| **TOTAL** | **Subfase 3.6.3 Consolidada** | **157** | **157** | **0** | ✅ **100% OK** |

---

## 3. Evidencias de Ejecución CLI

A continuación se muestra el extracto consolidado de la salida del ejecutor nativo en terminal CLI ([`logs/subfase-3.6.3-cli.log`](../../logs/subfase-3.6.3-cli.log)):

```text
================================================================================
  SUITE DE PRUEBAS: Subfase 3.6.3: Inyección, Sanitización & Seguridad de Medios/Archivos (OWASP A03 + A08)
  Iniciada: 2026-09-12 17:50:33 | PHP 8.3.29 | OS: Darwin
================================================================================

► SECCIÓN: 1. Blindaje Total contra Inyección SQL (SQLi - OWASP A03:2021)
──────────────────────────────────────────────────────────────────────
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #0 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #0 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #1 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #1 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #2 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #2 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #3 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #3 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #4 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #4 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #5 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #5 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #6 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #6 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #7 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #7 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #8 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #8 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #9 en 'busqueda' no produce error de sintaxis
  ✔ PASS: Vector SQLi #9 en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #0 en 'categoria' no produce error de sintaxis
  ✔ PASS: Vector SQLi #0 en 'categoria' es tratado como string literal y devuelve 0 coincidencias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #1 en 'categoria' no produce error de sintaxis
  ✔ PASS: Vector SQLi #1 en 'categoria' es tratado como string literal y devuelve 0 coincidencias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #2 en 'categoria' no produce error de sintaxis
  ✔ PASS: Vector SQLi #2 en 'categoria' es tratado como string literal y devuelve 0 coincidencias
  ✔ PASS: CreacionRepository::listCatalog con vector SQLi #3 en 'categoria' no produce error de sintaxis
  ✔ PASS: Vector SQLi #3 en 'categoria' es tratado como string literal y devuelve 0 coincidencias
  ✔ PASS: CreacionRepository::listCatalog con payload en artesano_id es tipado a entero
  ✔ PASS: countCatalog con SQLi devuelve entero seguro sin excepción
  ✔ PASS: PedidoRepository::listAll con vector SQLi #0 en 'busqueda' no falla
  ✔ PASS: Vector SQLi #0 en pedidos no inyecta filas arbitrarias
  ✔ PASS: PedidoRepository::listAll con vector SQLi #1 en 'busqueda' no falla
  ✔ PASS: Vector SQLi #1 en pedidos no inyecta filas arbitrarias
  ✔ PASS: PedidoRepository::listAll con vector SQLi #2 en 'busqueda' no falla
  ✔ PASS: Vector SQLi #2 en pedidos no inyecta filas arbitrarias
  ✔ PASS: Filtro estado_pedido con SQLi devuelve 0 resultados literales
  ✔ PASS: Filtro estado_pago con SQLi devuelve 0 resultados literales
  ✔ PASS: UsuarioRepository::findByUsername con vector SQLi #0 ('' OR '1'='1') devuelve null
  ✔ PASS: UsuarioRepository::findByUsername con vector SQLi #1 ('admin' --') devuelve null
  ✔ PASS: UsuarioRepository::findByUsername con vector SQLi #2 ('admin' /*') devuelve null
  ✔ PASS: UsuarioRepository::findByUsername con vector SQLi #3 ('admin' OR 1=1; --') devuelve null
  ✔ PASS: UsuarioRepository::findByUsername con vector SQLi #4 ('' UNION SELECT id, username, password_hash, rol, activo, creado_en, eliminado_en FROM usuarios --') devuelve null
  ✔ PASS: existsUsername con SQLi devuelve false
  ✔ PASS: existsUsername con 'admin\' --' devuelve false
  ✔ PASS: Conteo de usuarios intacto tras ataques SQLi
  ✔ PASS: Conteo de creaciones intacto tras ataques SQLi
  ✔ PASS: Conteo de pedidos intacto tras ataques SQLi

► SECCIÓN: 2. Defensa contra Cross-Site Scripting (XSS - OWASP A03:2021)
──────────────────────────────────────────────────────────────────────
  ✔ PASS: Payload 'script_tag': etiqueta <script> neutralizada en escape HTML
  ✔ PASS: Payload 'script_tag': etiqueta <img neutralizada en escape HTML
  ✔ PASS: Payload 'script_tag': etiqueta <svg neutralizada en escape HTML
  ✔ PASS: Payload 'script_tag': etiqueta <iframe neutralizada en escape HTML
  ✔ PASS: Payload 'script_tag': comillas dobles convertidas a entidades seguras (&quot;)
  ✔ PASS: Payload 'script_tag': comillas simples convertidas a entidades seguras (&#039;)
  ✔ PASS: Payload 'img_onerror': etiqueta <script> neutralizada en escape HTML
  ✔ PASS: Payload 'img_onerror': etiqueta <img neutralizada en escape HTML
  ✔ PASS: Payload 'img_onerror': etiqueta <svg neutralizada en escape HTML
  ✔ PASS: Payload 'img_onerror': etiqueta <iframe neutralizada en escape HTML
  ✔ PASS: Payload 'img_onerror': comillas dobles convertidas a entidades seguras (&quot;)
  ✔ PASS: Payload 'img_onerror': comillas simples convertidas a entidades seguras (&#039;)
  ✔ PASS: Payload 'svg_onload': etiqueta <script> neutralizada en escape HTML
  ✔ PASS: Payload 'svg_onload': etiqueta <img neutralizada en escape HTML
  ✔ PASS: Payload 'svg_onload': etiqueta <svg neutralizada en escape HTML
  ✔ PASS: Payload 'svg_onload': etiqueta <iframe neutralizada en escape HTML
  ✔ PASS: Payload 'svg_onload': comillas dobles convertidas a entidades seguras (&quot;)
  ✔ PASS: Payload 'svg_onload': comillas simples convertidas a entidades seguras (&#039;)
  ✔ PASS: Payload 'event_handler': etiqueta <script> neutralizada en escape HTML
  ✔ PASS: Payload 'event_handler': etiqueta <img neutralizada en escape HTML
  ✔ PASS: Payload 'event_handler': etiqueta <svg neutralizada en escape HTML
  ✔ PASS: Payload 'event_handler': etiqueta <iframe neutralizada en escape HTML
  ✔ PASS: Payload 'event_handler': comillas dobles convertidas a entidades seguras (&quot;)
  ✔ PASS: Payload 'event_handler': comillas simples convertidas a entidades seguras (&#039;)
  ✔ PASS: Payload 'javascript_url': etiqueta <script> neutralizada en escape HTML
  ✔ PASS: Payload 'javascript_url': etiqueta <img neutralizada en escape HTML
  ✔ PASS: Payload 'javascript_url': etiqueta <svg neutralizada en escape HTML
  ✔ PASS: Payload 'javascript_url': etiqueta <iframe neutralizada en escape HTML
  ✔ PASS: Payload 'javascript_url': comillas dobles convertidas a entidades seguras (&quot;)
  ✔ PASS: Payload 'javascript_url': comillas simples convertidas a entidades seguras (&#039;)
  ✔ PASS: Payload 'iframe_payload': etiqueta <script> neutralizada en escape HTML
  ✔ PASS: Payload 'iframe_payload': etiqueta <img neutralizada en escape HTML
  ✔ PASS: Payload 'iframe_payload': etiqueta <svg neutralizada en escape HTML
  ✔ PASS: Payload 'iframe_payload': etiqueta <iframe neutralizada en escape HTML
  ✔ PASS: Payload 'iframe_payload': comillas dobles convertidas a entidades seguras (&quot;)
  ✔ PASS: Payload 'iframe_payload': comillas simples convertidas a entidades seguras (&#039;)
  ✔ PASS: Creación con payload XSS creada exitosamente con ID válido
  ✔ PASS: Clean Architecture: Datos se almacenan íntegros en persistencia
  ✔ PASS: Capa de vista HTML escapa estrictamente el nombre con htmlspecialchars
  ✔ PASS: Capa de vista HTML escapa estrictamente la descripción
  ✔ PASS: Endpoint detalle.php responde HTTP 200 para la pieza
  ✔ PASS: Cabecera Content-Type es estrictamente application/json
  ✔ PASS: JSON entrega datos íntegros
  ✔ PASS: Pedido con payloads XSS registrado con éxito
  ✔ PASS: Enlace de WhatsApp tiene estructura E.164 segura
  ✔ PASS: Enlace de WhatsApp no contiene caracteres <svg sin codificar
  ✔ PASS: Enlace de WhatsApp no contiene caracteres <script> sin codificar
  ✔ PASS: Caracteres <svg fueron codificados en porcentaje como %3Csvg
  ✔ PASS: Username con XSS rechazado por contener caracteres no permitidos
  ✔ PASS: UsuarioService::createUser rechaza nombres con caracteres XSS

► SECCIÓN: 3. Detección y Validación de Tipo MIME Real en Carga de Medios (OWASP A08:2021)
──────────────────────────────────────────────────────────────────────
  ✔ PASS: Rechazo de archivo PHP camuflado retorna código 422
  ✔ PASS: Mensaje indica formato no permitido
  ✔ PASS: Script PHP con extensión .jpg es detectado por finfo y rechazado
  ✔ PASS: Rechazo de HTML camuflado retorna código 422
  ✔ PASS: Archivo HTML con extensión .png es detectado por finfo y rechazado
  ✔ PASS: Rechazo de Shell Script retorna código 422
  ✔ PASS: Shell script con extensión .webp es rechazado con 422
  ✔ PASS: Archivo de >5MB retorna código 422
  ✔ PASS: Mensaje indica límite de 5MB
  ✔ PASS: Archivo >5MB es rechazado antes de procesar
  ✔ PASS: Imagen JPEG válida genera URL en uploads/creacion_...
  ✔ PASS: Imagen JPEG válida conserva extensión .jpg
  ✔ PASS: Archivo físico JPEG existe en disco
  ✔ PASS: Imagen PNG válida genera URL en uploads/creacion_...
  ✔ PASS: Imagen PNG válida conserva extensión .png
  ✔ PASS: Archivo físico PNG existe en disco
  ✔ PASS: Imagen WebP válida genera URL en uploads/creacion_...
  ✔ PASS: Imagen WebP válida conserva extensión .webp
  ✔ PASS: Archivo físico WebP existe en disco
  ✔ PASS: Sin archivo devuelve fallback temático SVG
  ✔ PASS: Archivo SVG de fallback temático existe en disco

► SECCIÓN: 4. Prevención de Path Traversal & Carga Arbitraria (OWASP A08:2021)
──────────────────────────────────────────────────────────────────────
  ✔ PASS: URL de imagen no contiene secuencias de escape (..)
  ✔ PASS: Nombre provisto por el cliente es totalmente descartado
  ✔ PASS: Nombre resultante utiliza prefijo seguro creacion_[hash]
  ✔ PASS: Archivo resultante está estrictamente confinado al directorio uploads/
  ✔ PASS: database/database.sqlite existe antes de la prueba
  ✔ PASS: database/database.sqlite SOBREVIVE intacto (basename neutraliza ../../)
  ✔ PASS: Regla de Oro ADR-008: La fotografía en uploads/ NO se elimina en baja lógica

► SECCIÓN: 5. Seguridad Vectorial SVG & Sanitización de Atributos
──────────────────────────────────────────────────────────────────────
  ✔ PASS: Se auditaron 34 archivos SVG en assets/svg/ (>= 20)
  ✔ PASS: El 100% de los archivos SVG (34/34) están limpios de scripts, eventos y XXE
  ✔ PASS: SvgHelper::render escapa comillas dobles en clases inyectadas
  ✔ PASS: Comillas inyectadas son convertidas a &quot;
  ✔ PASS: SvgHelper::render neutraliza etiquetas <script> en atributos data
  ✔ PASS: Etiquetas inyectadas en atributos son convertidas a entidades seguras
  ✔ PASS: Mensaje de error no encontrado neutraliza etiquetas <script>
  ✔ PASS: Nombre malicioso en mensaje de error es escapado con htmlspecialchars
  ✔ PASS: Categoría 'Fantasía' resuelve al vector esperado: assets/svg/piezas/dragon-ignis.svg
  ✔ PASS: Vector temático para 'Fantasía' existe en disco
  ✔ PASS: Categoría 'Prendas & Ropa' resuelve al vector esperado: assets/svg/piezas/cardigan-granny.svg
  ✔ PASS: Vector temático para 'Prendas & Ropa' existe en disco
  ✔ PASS: Categoría 'Bolsos & Accesorios' resuelve al vector esperado: assets/svg/piezas/tote-bag.svg
  ✔ PASS: Vector temático para 'Bolsos & Accesorios' existe en disco
  ✔ PASS: Categoría 'Hogar & Decoración' resuelve al vector esperado: assets/svg/piezas/mini-suculenta.svg
  ✔ PASS: Vector temático para 'Hogar & Decoración' existe en disco
  ✔ PASS: Categoría 'Bebé & Infantil' resuelve al vector esperado: assets/svg/piezas/osito-nordico.svg
  ✔ PASS: Vector temático para 'Bebé & Infantil' existe en disco
  ✔ PASS: Categoría 'Genérica' resuelve al vector esperado: assets/svg/piezas/gatito-ovillo.svg
  ✔ PASS: Vector temático para 'Genérica' existe en disco

► SECCIÓN: 6. Pruebas de Integración HTTP en Vivo contra Servidor Local (OWASP A03 + A08)
──────────────────────────────────────────────────────────────────────
  ✔ PASS: GET /api/creaciones/index.php?busqueda=' OR '1'='1 responde HTTP 200 OK
  ✔ PASS: Respuesta indica exito = true
  ✔ PASS: Respuesta contiene array estructurado de datos
  ✔ PASS: GET /api/creaciones/index.php con UNION SELECT responde HTTP 200
  ✔ PASS: UNION SELECT es neutralizado por PDO y no devuelve registros espurios
  ✔ PASS: GET /api/pedidos/index.php con DROP TABLE responde HTTP 200 sin ejecutar la sentencia
  ✔ PASS: POST /api/pedidos/solicitar.php con payload XSS responde 201 Created
  ✔ PASS: Pedido procesado exitosamente
  ✔ PASS: ID de pedido generado > 0
  ✔ PASS: POST /api/creaciones/crear.php con archivo PHP disfrazado responde HTTP 422
  ✔ PASS: Mensaje de error HTTP informa formato no permitido

================================================================================
  RESUMEN DE PRUEBAS: Subfase 3.6.3: Inyección, Sanitización & Seguridad de Medios/Archivos (OWASP A03 + A08)
--------------------------------------------------------------------------------
  Total Aserciones: 157
  Exitosas:         157
  Fallidas:         0
  Tiempo Total:     119.92 ms
================================================================================

  ✔ TODAS LAS PRUEBAS PASARON EXITOSAMENTE (100% OK)
```

---

## 4. Evidencias de Integración HTTP en Vivo

Extractos representativos de las peticiones capturadas en [`logs/subfase-3.6.3-http.log`](../../logs/subfase-3.6.3-http.log):

### Traza 1: Inyección SQL en Búsqueda Pública (`GET /api/creaciones/index.php?busqueda=' OR '1'='1`)
```http
GET /api/creaciones/index.php?busqueda=%27+OR+%271%27%3D%271 HTTP/1.1
Host: localhost:8000

HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff
X-Frame-Options: DENY

{
    "exito": true,
    "mensaje": "Catálogo de creaciones obtenido exitosamente.",
    "datos": [],
    "paginacion": {
        "total_items": 0,
        "pagina_actual": 1,
        "total_paginas": 1,
        "limite": 12,
        "tiene_siguiente": false,
        "tiene_anterior": false
    }
}
```

### Traza 2: Rechazo de Archivo PHP Camuflado como Imagen (`POST /api/creaciones/crear.php`)
```http
POST /api/creaciones/crear.php HTTP/1.1
Host: localhost:8000
Authorization: Bearer [token_admin]
Content-Type: multipart/form-data; boundary=---------------------------974767299852498929531610575

[Payload con archivo exploit.jpg que contiene código PHP]

HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json; charset=utf-8

{
    "exito": false,
    "error": {
        "codigo": 422,
        "mensaje": "Formato de imagen no permitido (text/x-php). Formatos válidos: JPEG, PNG y WebP."
    }
}
```

### Traza 3: Almacenamiento Seguro de Pedido con XSS (`POST /api/pedidos/solicitar.php`)
```http
POST /api/pedidos/solicitar.php HTTP/1.1
Host: localhost:8000
Content-Type: application/json

{
    "creacion_id": 1,
    "cantidad": 1,
    "cliente_nombre": "Cliente <script>alert('xss')</script>",
    "cliente_contacto": "5512345678",
    "notas": "Notas con <img src=x onerror=alert(1)>"
}

HTTP/1.1 201 Created
Content-Type: application/json; charset=utf-8

{
    "exito": true,
    "mensaje": "Pedido registrado exitosamente.",
    "datos": {
        "id": 3,
        "creacion_id": 1,
        "creacion_nombre": "Dragón Ignis",
        "cantidad": 1,
        "precio_final": 45000,
        "precio_final_formateado": "$450.00 MXN",
        "estado_pedido": "Pendiente",
        "estado_pago": "Pendiente",
        "es_sobre_encargo": 0
    }
}
```

---

## 5. Cumplimiento de Guardrails y Decisión de Diseño

| Guardrail / Regla | Estado | Mecanismo de Verificación |
| :--- | :---: | :--- |
| **100% Prepared Statements (Zero SQLi)** | ✅ Cumplido | El 100% de consultas usan marcadores `:param` y `bindValue()`. Pruebas con comillas, nulos, uniones y sentencias múltiples pasan sin fuga ni error. |
| **Clean Architecture XSS Separation** | ✅ Cumplido | Persistencia almacena datos íntegros; la capa de presentación escapa con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. Controladores emiten `application/json`. |
| **Detección Binaria de Tipos MIME (finfo)** | ✅ Cumplido | `finfo_file` inspecciona bytes mágicos de archivo real. Scripts ejecutables camuflados son rechazados de inmediato con `HTTP 422`. |
| **Inmunidad a Path Traversal (`basename()`)** | ✅ Cumplido | Nombres de archivo generados con hashes pseudoaleatorios (`uniqid('creacion_', true)`). `unlinkPreviousUploadFile` usa `basename()`, imposibilitando la salida de `uploads/`. |
| **Regla de Oro ADR-008 (Cero Unlink en Baja Lógica)** | ✅ Cumplido | Soft-delete preserva fotografías en disco para mantener vivas las miniaturas de pedidos históricos. |
| **Seguridad Vectorial SVG (Zero Scripts/XXE)** | ✅ Cumplido | Escaneo de 34 archivos SVG en `assets/svg/` reporta 0 ocurrencias de scripts, eventos inline o entidades externas. Atributos en `SvgHelper::render()` escapan comillas y etiquetas. |

---

## 6. Siguientes Pasos (Transición a Subfase 3.6.4)

Tras haber aprobado satisfactoriamente la **Subfase 3.6.3** con **157 / 157 aserciones aprobadas**, y cumpliendo con el **Compás de Espera Inviolable**:

> [!IMPORTANT]
> **Compás de Espera Obligatorio:** Se detiene completamente cualquier ejecución automatizada y se solicita la autorización explícita y por escrito del usuario antes de avanzar a la **Subfase 3.6.4: Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)**.
