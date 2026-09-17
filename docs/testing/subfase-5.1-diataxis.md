# Reporte de Testing: Subfase 5.1 - Documentación Diátaxis & Manuales Operativos

> **Estado:** ✅ APROBADO (116 / 116 Aserciones CLI en Verde · 100% OK)  
> **Fecha:** 2026-09-17  
> **Ambiente:** PHP 8.3.29 · SQLite 3 · Darwin (macOS)  
> **Feature Activa:** `spec/features/012-plan-maestro-fase-5/`  
> **Suite de Prueba:** `tests/test-subfase-5.1.php`  
> **Evidencia Cruda Nivel 2:** `logs/subfase-5.1-cli.log` y `logs/subfase-5.1-http.log`  

---

## 1. Resumen Ejecutivo

La **Subfase 5.1** implementa la reestructuración y ampliación integral de la documentación del proyecto bajo el marco metodológico **Diátaxis**, organizando el conocimiento técnico y operativo en sus cuatro cuadrantes fundamentales:
1. **Tutoriales (Aprendizaje):** Guías paso a paso para la publicación de piezas textiles con costeo justo y la gestión del primer encargo coordinado por WhatsApp.
2. **Guías How-To (Resolución de Problemas):** Procedimientos de administración de roles RBAC, salvaguarda de administrador titular ID #1 (R-05), restitución atómica de stock en cancelaciones (R-07) y configuración internacional de WhatsApp E.164.
3. **Referencia Técnica (Información):** Catálogo consolidado de los 27 controladores REST de la API y diccionario exhaustivo de datos relacionales de SQLite (5 tablas, índices y pragmas).
4. **Explicación (Comprensión):** Justificación y diseño de Clean Architecture sin dependencias de Composer y fundamentación de los 10 Invariantes Canónicos de Seguridad (R-01 a R-10).

El 100% de los documentos cumple con el umbral técnico de contenido sustantivo ($\ge 1,200\text{ bytes}$ por archivo, sin placeholders `TODO`), se encuentra indexado en `docs/README.md` (cumpliendo la regla de cero huérfanos H-013) y ha sido verificado en runtime mediante comprobaciones HTTP en vivo.

---

## 2. Métricas de Ejecución del Gate en 3 Niveles

| Nivel de Calidad | Artefacto de Verificación | Resultado | Estado |
| :--- | :--- | :---: | :---: |
| **Nivel 1 (Suite CLI)** | `tests/test-subfase-5.1.php` | 116 / 116 aserciones | ✅ 100% PASS |
| **Nivel 2 (Log CLI)** | `logs/subfase-5.1-cli.log` | Cero fallos reportados | ✅ Conforme |
| **Nivel 2 (Log HTTP)** | `logs/subfase-5.1-http.log` | Endpoints 200, 401, 204 y CSP | ✅ Conforme |
| **Nivel 3 (Reporte)** | `docs/testing/subfase-5.1-diataxis.md` | Estándar Diátaxis formalizado | ✅ Aprobado |

---

## 3. Desglose de Cobertura por Secciones

### Sección 1: Estructura de los 4 Cuadrantes Diátaxis (18 Aserciones)
- Verificación física de existencia y tamaño de los 9 documentos:
  - `docs/tutorials/primera-creacion.md` (5,919 bytes)
  - `docs/tutorials/primer-encargo-whatsapp.md` (5,437 bytes)
  - `docs/how-to/gestion-roles-y-claves.md` (5,088 bytes)
  - `docs/how-to/cancelacion-y-restitucion-stock.md` (4,523 bytes)
  - `docs/how-to/configurar-whatsapp-artesano.md` (3,761 bytes)
  - `docs/api/catalogo-completo-endpoints.md` (9,786 bytes)
  - `docs/database/diccionario-datos.md` (7,919 bytes)
  - `docs/explanation/clean-architecture-sin-framework.md` (5,270 bytes)
  - `docs/explanation/invariantes-seguridad.md` (7,137 bytes)

### Sección 2: Cuadrante Tutoriales (11 Aserciones)
- Conceptos de diseño **Algodón Nórdico**, desglose de `costo_materiales`, labor en `horas_tejido`, `precio` en centavos, distinción de piezas sobre encargo (`es_sobre_encargo`), generación de enlaces `wa.me`, congelamiento de `precio_final` e interfaz **stepper** acotada.

### Sección 3: Cuadrante How-To (12 Aserciones)
- Cobertura de roles `admin`, `artesano` y `asistente`, salvaguarda inmutable de **ID #1** y respuesta HTTP 403, cancelación atómica con `BEGIN IMMEDIATE`, incremento de `cantidad_stock`, garantía de idempotencia y validación E.164 (+52 y 10 dígitos).

### Sección 4: Cuadrante Referencia Técnica (44 Aserciones)
- Documentación exhaustiva de los 27 endpoints REST en `api/auth/`, `api/creaciones/`, `api/pedidos/` y `api/usuarios/`.
- Cobertura de los 10 códigos de estado HTTP estándar: `200 OK`, `201 Created`, `204 No Content`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `409 Conflict`, `422 Unprocessable`, `500 Internal`.
- Diccionario de las 5 tablas SQLite (`usuarios`, `creaciones`, `pedidos`, `tokens_revocados`, `login_intentos`), directivas `PRAGMA foreign_keys = ON`, `PRAGMA busy_timeout = 5000` y moneda en `INTEGER (Centavos)`.

### Sección 5: Cuadrante Explicación & 10 Invariantes (14 Aserciones)
- Documentación conceptual de Clean Architecture nativa, autoloader PSR-4 sin dependencias, justificación de **Zero Composer** y la regla de dependencias inward (**Dependency Rule**).
- Análisis profundo de los 10 Invariantes Canónicos: R-01 a R-10.

### Sección 6: Integridad del Índice Maestro H-013 (9 Aserciones)
- Enlace explícito y bidireccional de cada archivo nuevo en `docs/README.md`.

### Sección 7: Verificación HTTP en Vivo (8 Aserciones)
- `GET /api/creaciones/index.php`: `200 OK` con sobre JSON estándar.
- `GET /api/auth/me.php`: `401 Unauthorized` ante petición anónima.
- `OPTIONS /api/creaciones/crear.php`: `204 No Content` para preflight CORS.
- `GET /index.php`: `200 OK` con cabecera `Content-Security-Policy` restrictiva.

---

## 4. Fallos Detectados & Correcciones Quirúrgicas

Durante el ciclo Red-Green-Refactor se registraron las siguientes adaptaciones:
1. **Detección de Huérfanos H-013 en `test-gobernanza-accion-5.php`:**
   - *Hallazgo:* Al crearse los 9 archivos en subcarpetas `tutorials/`, `how-to/` y `explanation/`, la suite de gobernanza exigió su indexación exhaustiva en `docs/README.md` como `./path/archivo.md`.
   - *Corrección Quirúrgica:* Se actualizó `docs/README.md` incorporando las filas temáticas en la tabla de navegación por dominios y las ramas correspondientes en el árbol estructural de documentación. La suite de gobernanza pasó inmediatamente a 41/41 (100% OK).
2. **Normalización del Catálogo REST a 27 Endpoints:**
   - *Hallazgo:* El test verificaba la cobertura de los 27 controladores existentes en `api/`, requiriendo documentar tanto rutas públicas como endpoints administrativos de reactivación y restablecimiento de contraseña.
   - *Corrección Quirúrgica:* Se redactó `docs/api/catalogo-completo-endpoints.md` con las especificaciones de entrada, salida y códigos HTTP para la totalidad de los 27 controladores.

---

## 5. Trazabilidad de Criterios de Aceptación (`spec.md`)

| Criterio de Aceptación | Descripción | Evidencia de Validación | Estado |
| :--- | :--- | :--- | :---: |
| **AC-1** | Cuadrantes Diátaxis y Manuales Operativos | 9 archivos en 4 cuadrantes, 116 aserciones CLI en verde, cero TODO, `test-subfase-5.1.php` exit 0. | ✅ Cumplido |
| **H-013** | Cero Archivos Huérfanos en `docs/` | `test-gobernanza-accion-5.php` 41/41 aserciones en verde. | ✅ Cumplido |

---

## 6. Conclusión y Gate de Validación

La **Subfase 5.1** ha completado rigurosamente los tres niveles del Quality Gate. La documentación técnica y operativa del proyecto queda estandarizada bajo Diátaxis, garantizando onboarding fluido, trazabilidad arquitectónica y cero discrepancias con la base de código.

**Estado:** APTO PARA REVISIÓN Y SIGN-OFF DEL USUARIO.
