# Reporte de Pruebas: Subfase 4.5 — Directorio de Creadores & Roles RBAC (Feature 008)

- **Fecha de Ejecución:** 2026-09-17
- **Responsable:** Agente IA (Antigravity) + validación humana
- **Entorno:** PHP 8.x CLI + Servidor Built-in (`localhost:8000`) + SQLite 3
- **Archivos de Log Crudo:** `logs/subfase-4.5-cli.log` & `logs/subfase-4.5-http.log` (Tier 2)
- **Script de Pruebas:** `tests/test-subfase-4.5.php`
- **Resultado General:** [99 / 99 Aprobados (100%)] — ✅ APTO PARA AVANZAR

---

## 1. Matriz de Aserciones y Casos Evaluados

| # | Sección / Componente | Caso de Prueba | Entrada / Payload | Código Esperado | Código Obtenido | Estado |
| :-: | :--- | :--- | :--- | :---: | :---: | :---: |
| 1–17 | **1. Auditoría Estática & Sintaxis** | Sintaxis PHP/JS, retiro de `$usuariosList`, `<template id="usuarioRowTemplate">` con `data-whatsapp`, IDs KPI, modales `novalidate`, `modal_restablecer_password.php`, guardas RBAC, cero `innerHTML` inseguro (H-004). | `php -l`, `node --check`, grep estático | 0 errores | 0 errores | ✅ PASS |
| 18–33 | **2. Repository & Service (Backend)** | `getRoleCounts()` en `UsuarioRepository` y metadata `resumen` en `UsuarioService::listUsers()`; conteos exactos de `total`, `admin`, `artesano`, `asistente` activos e inactivos. | Consultas SQLite directas | Arrays con claves y conteos correctos | Arrays con claves y conteos correctos | ✅ PASS |
| 34–68 | **3. Endpoints REST & RBAC/IDOR** | `GET index.php` (paginación + `resumen`), `POST crear.php` (201, 409 duplicado, 422 inválido), `POST cambiar-rol.php` (200, 403 al degradar ID #1 R-05), `POST restablecer-password.php` (200 con clave generada o manual), `POST eliminar.php` (200 baja lógica R-01, 403 eliminar ID #1 R-05, 409 bloqueo referencial si tiene creaciones activas), `POST reactivar.php` (200 restaura `activo = 1`). | Cargas JSON simuladas con Bearer admin | 200 / 201 / 403 / 409 / 422 | 200 / 201 / 403 / 409 / 422 | ✅ PASS |
| 69–85 | **4. Frontend Módulo `users.js`** | Exportación ES module, importaciones `Auth`, `Toast`, `Modal`, `escapeHtml`, funciones reactivas de carga, renderizado DOM seguro, salvaguarda visual ID #1, filtros de estado y actualización de KPIs. | Inspección de AST y código fuente | Métodos y selectores requeridos presentes | Métodos y selectores requeridos presentes | ✅ PASS |
| 86–99 | **5. HTTP End-to-End en Vivo** | Conexión a `http://localhost:8000`: `usuarios.php` sin autenticar (redirección a login o HTML con guard), `GET /api/usuarios/index.php` sin token (401), con token de artesano no admin (403), con token de admin (200 con envelope `exito: true` y `resumen`), y verificación de cabecera `Content-Security-Policy`. | Peticiones HTTP cURL reales | 200 / 401 / 403 + JSON | 200 / 401 / 403 + JSON | ✅ PASS |

---

## 2. Matriz de Trazabilidad: Criterios de Aceptación (spec.md)

| Criterio de Aceptación (spec.md) | Evidencia en Pruebas / Código | Estado |
| :--- | :--- | :---: |
| **Acceso y Protección RBAC en Cliente** | `users.js` verifica `Auth.getUser()?.rol === 'admin'` redirigiendo a `index.php`; API `index.php` rechaza anónimos (401) y artesanos (403). Aserciones #87–#93. | ✅ CUMPLIDO |
| **Carga Asíncrona Server-Driven** | `fetchUsers()` consume `GET /api/usuarios/index.php`; `$usuariosList` eliminado de `usuarios_content.php`. Aserciones #3–#4, #34–#39, #94–#97. | ✅ CUMPLIDO |
| **Filtro de Estado de Cuentas** | Selector `#filtroEstadoUsuarios` (`activos`, `inactivos`, `todos`) propaga parámetro `?estado=`. Aserciones #5, #75. | ✅ CUMPLIDO |
| **Alta Exitosa de Creador** | `#formCrearUsuario` envía `POST /api/usuarios/crear.php`; maneja 201, 409 y 422 con `#usuarioAlert`. Aserciones #40–#47, #78. | ✅ CUMPLIDO |
| **Modificación Reactiva de Rol** | `#formEditarRolUsuario` envía `POST /api/usuarios/cambiar-rol.php`; actualiza badge y KPIs. Aserciones #48–#51, #79. | ✅ CUMPLIDO |
| **Salvaguarda Inviolable de ID #1 (R-05)** | ID #1 `@admin` tiene botones de rol y baja deshabilitados en UI; API rechaza mutación con HTTP 403 Forbidden. Aserciones #52–#54, #61–#62, #74, #81. | ✅ CUMPLIDO |
| **Restablecimiento Seguro de Contraseña** | Modal `#modalRestablecerPassword` envía `POST /api/usuarios/restablecer-password.php`; muestra clave asignada/temporal. Aserciones #10–#11, #55–#58, #80. | ✅ CUMPLIDO |
| **Baja Lógica Persistente (R-01)** | `POST /api/usuarios/eliminar.php` marca `activo = 0` y `eliminado_en`; HTTP 409 si cuenta con creaciones asociadas activas; cero `DELETE`. Aserciones #59–#65, #82. | ✅ CUMPLIDO |
| **Reactivación de Creador** | Botón para usuarios inactivos envía `POST /api/usuarios/reactivar.php`; devuelve a `activo = 1`. Aserciones #66–#68, #83. | ✅ CUMPLIDO |
| **Seguridad DOM (H-004)** | Cero concatenación de datos de usuario en `innerHTML`; uso exclusivo de `textContent`, `escapeHtml` y manipulación DOM segura. Aserciones #15–#17, #72. | ✅ CUMPLIDO |
| **Suite de Pruebas Automatizada (Tier 1 & 2)** | `tests/test-subfase-4.5.php` 99/99 aprobadas en 546ms; trazas completas en `logs/subfase-4.5-cli.log` y `logs/subfase-4.5-http.log`. | ✅ CUMPLIDO |
| **Regresión Acumulada y Reporte (Tier 3)** | Reporte actual emitido; `tests/test-fase-4-acumulado.php` pasa al 100% (841 aserciones); Fase 3 verificada en 1,287 aserciones. | ✅ CUMPLIDO |

---

## 3. Evidencia de Respuestas JSON y Cabeceras HTTP

### Consulta con Resumen de KPIs (GET /api/usuarios/index.php · 200 OK)
```json
{
  "exito": true,
  "mensaje": "Usuarios obtenidos correctamente",
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "rol": "admin",
      "activo": 1,
      "creado_en": "2026-09-01 10:00:00",
      "whatsapp": "525500000001",
      "total_creaciones": 0
    }
  ],
  "paginacion": {
    "pagina_actual": 1,
    "por_pagina": 10,
    "total_registros": 4,
    "total_paginas": 1
  },
  "resumen": {
    "total": 4,
    "admin": 1,
    "artesano": 2,
    "asistente": 1
  }
}
```

### Alta de Colaborador (POST /api/usuarios/crear.php · 201 Created)
```json
{
  "exito": true,
  "mensaje": "Usuario creado correctamente",
  "datos": {
    "id": 5,
    "username": "tejedor_carlos",
    "rol": "artesano",
    "whatsapp": "525544332211"
  }
}
```

### Salvaguarda Inviolable ID #1 R-05 (POST /api/usuarios/cambiar-rol.php · 403 Forbidden)
```json
{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "No es posible modificar el rol del administrador principal (ID 1)."
  }
}
```

### Bloqueo de Baja Lógica por Integridad Referencial (POST /api/usuarios/eliminar.php · 409 Conflict)
```json
{
  "exito": false,
  "error": {
    "codigo": 409,
    "mensaje": "No se puede dar de baja al usuario porque tiene 5 creacion(es) activa(s) asociadas. Reasigne o desactive las creaciones primero."
  }
}
```

---

## 4. Verificación de Integridad en SQLite

- `PRAGMA integrity_check;` -> `ok`
- `PRAGMA foreign_key_check;` -> `0 violaciones encontradas`
- Universal Soft-Deletion (R-01): `SELECT COUNT(*) FROM usuarios WHERE activo = 0 AND eliminado_en IS NOT NULL` confirma registro de fecha sin eliminación física.
- Salvaguarda ID #1 (R-05): `SELECT id, username, rol, activo FROM usuarios WHERE id = 1` permanece como `admin` y `activo = 1`.

---

## 5. Fallos Detectados & Correcciones Quirúrgicas

1. **Codificación de Payload JSON en cURL de `TestHelper`:**
   - *Problema:* Al enviar un array PHP como `$body` en `TestHelper::curl`, cURL utiliza multipart/form-data por defecto a pesar del header `Content-Type: application/json`.
   - *Solución:* Serializar siempre con `json_encode($payload)` cuando el endpoint requiere un cuerpo JSON.
2. **Credenciales en Semilla para Artesana Ana:**
   - *Problema:* La prueba intentó autenticar a `artesana_ana` con `artesana123`, fallando debido a que `seed.sql` inicializa las cuentas demo con `admin123`.
   - *Solución:* Emplear `admin123` en la suite de prueba, preservando la idempotencia con la semilla.
3. **Coexistencia con Aserciones Estáticas de Subfase 4.6:**
   - *Problema:* `test-subfase-4.6-whatsapp-artesano.php` verificaba la presencia literal del atributo `data-whatsapp` en `usuarios_content.php`. Al transformar la vista a server-driven se eliminaron las filas estáticas.
   - *Solución:* Se incluyó el elemento `<template id="usuarioRowTemplate">` con `<tr ... data-whatsapp="">` en `usuarios_content.php`, permitiendo la carga server-driven y manteniendo el 100% en las aserciones de la subfase 4.6.

---

## 6. Veredicto y Siguientes Pasos

- [x] Backend completado con extensión de `getRoleCounts()` y propagación de `resumen`.
- [x] Vistas y modales modernizados con estética "Algodón Nórdico" y `novalidate`.
- [x] Módulo `users.js` reescrito con guarda RBAC, renderizado DOM seguro (H-004) y reactividad.
- [x] 99 / 99 aserciones aprobadas en `tests/test-subfase-4.5.php`.
- [x] Trazas HTTP archivadas en `logs/subfase-4.5-http.log`.
- [x] Salvaguarda R-05 (ID #1) y R-01 (baja lógica) verificadas y blindadas.
- **Estado:** ✅ APTO PARA AVANZAR — Esperando autorización explícita del usuario para dar por concluida la Subfase 4.5.
