# Reporte de Pruebas: Subfase 3.4 — Catálogo, Creaciones & Ciclo de Vida de Imágenes

[← Volver al Hub de Pruebas](./README.md) • [Hub Principal](../README.md)

---

- **Fecha de Ejecución:** 2026-09-12 15:21:09 CST
- **Responsable:** Antigravity Agent (Pair Programming con Ingeniero Titular)
- **Entorno:** PHP 8.3.29 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (macOS Darwin)
- **Archivos de Log Crudos:**
  - CLI: [`logs/subfase-3.4-cli.log`](../../logs/subfase-3.4-cli.log)
  - HTTP: [`logs/subfase-3.4-http.log`](../../logs/subfase-3.4-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.4.php`](../../tests/test-subfase-3.4.php)
- **Resultado General:** **126 / 126 Aserciones Aprobadas (100% OK) en 157.72 ms** — ✅ **APTO PARA AVANZAR**

---

## 1. Resumen Ejecutivo de la Subfase 3.4

La **Subfase 3.4** implementa de forma integral la persistencia, casos de uso, lógica de negocio y controladores REST para el catálogo colectivo de piezas de crochet, administración de inventarios por artesano, prevención rigurosa de IDOR (**ADR-007**), ciclo de vida de imágenes con reemplazo en edición y cero borrado físico en baja lógica (**ADR-008**), endpoint público optimizado de artesanos activos (**ADR-014**), y ciclo completo de restauración de creaciones (**ADR-015**).

### Componentes Construidos y Verificados:
1. **[`App\Repositories\CreacionRepository`](../../app/Repositories/CreacionRepository.php):**
   - 100% prepared statements con tipos enlazados en SQLite.
   - Búsqueda hidratada por ID con objeto anidado de autoría del artesano (`artesano: { id, username }`).
   - Consultas de catálogo con filtros combinables (`categoria`, `artesano_id`, `precio_min`, `precio_max`, `busqueda`, `es_sobre_encargo`, `solo_en_stock`, `activo`).
   - Ordenación dinámica parametrizada (`recientes`, `precio_asc`, `precio_desc`, `nombre_asc`, `stock_desc`).
   - Paginación tipada y conteo de agregación para metadatos.
   - Endpoint de agregación `findActiveArtisansWithCreations()` (**ADR-014**).
   - Mutaciones DDL seguras: `create()`, `update()`, `softDelete()`, `restore()`, `adjustStock()`, `toggleCommission()`.

2. **[`App\Services\CreacionService`](../../app/Services/CreacionService.php):**
   - Validaciones de dominio para los 10 campos de ficha técnica.
   - Enriquecimiento monetario dual con `CurrencyHelper` (`precio_formateado`, `costo_formateado`, `margen_bruto_porcentaje`, `retorno_por_hora_formateado`).
   - Gestión de subida de imágenes con validación de tipo MIME real (`finfo`/`mime_content_type`), tamaño máximo ($\le 5\text{MB}$) y nombres criptográficos en `uploads/`.
   - Asignación automática de vector temático SVG de respaldo desde `assets/svg/piezas/` cuando no se adjunta archivo.
   - **Regla de Oro ADR-008:** Eliminación física con `unlink()` del archivo anterior únicamente al reemplazar foto en edición (`updateCreation`), y **CERO `unlink()` en baja lógica (`deleteCreation`)**, preservando fotos para pedidos históricos.
   - **Prevención IDOR ADR-007:** Salvaguarda `ensureArtisanOwnership()` que valida autoría del artesano o rol `admin`, arrojando HTTP 403 Forbidden ante cualquier acceso no autorizado.
   - **Restauración ADR-015:** Reversión limpia de bajas lógicas retornando `activo = 1`, con detección de colisión 409 si ya está activa.

3. **Suite Completa de 9 Controladores REST Delgados en `api/creaciones/`:**
   - `GET /api/creaciones/index.php` (Público: catálogo filtrado con paginación).
   - `GET /api/creaciones/artesanos.php` (Público: artesanos activos para dropdown ADR-014).
   - `GET /api/creaciones/detalle.php?id=X` (Público: ficha técnica o 404).
   - `POST /api/creaciones/crear.php` (Protegido `RoleGuard::artisanOrAdmin()`, multipart/JSON).
   - `POST /api/creaciones/actualizar.php` (Protegido + IDOR).
   - `POST /api/creaciones/eliminar.php` (Protegido + IDOR, baja lógica 200).
   - `POST /api/creaciones/restaurar.php` (Protegido + IDOR, reactivación 200).
   - `POST /api/creaciones/ajustar-stock.php` (Protegido + IDOR, stock rápido 200).
   - `POST /api/creaciones/toggle-encargo.php` (Protegido + IDOR, encargo conmutado 200).

---

## 2. Matriz de Aserciones y Casos Evaluados

| # | Capa / Componente | Caso de Prueba | Condición / Entrada | Comportamiento Esperado | Resultado | Estado |
| :-: | :--- | :--- | :--- | :--- | :---: | :---: |
| 1 | `CreacionRepository` | Búsqueda por ID existente | `findById(1, true)` | Recupera Dragón Ignis con anidamiento de artesano | ID 1 OK | ✅ PASS |
| 2 | `CreacionRepository` | Atributos hidratados | Verificación de tipos | `precio: 45000`, `activo: 1`, `artesano_id: 1` | Casting tipado | ✅ PASS |
| 3 | `CreacionRepository` | Búsqueda por ID inexistente | `findById(99999)` | Retorna `null` | `null` devuelto | ✅ PASS |
| 4 | `CreacionRepository` | Listado general sin filtros | `listCatalog([], 12, 0)` | Retorna array $\ge 5$ creaciones orden descendente | 5 ítems OK | ✅ PASS |
| 5 | `CreacionRepository` | Filtro por categoría | `listCatalog(['categoria' => 'Amigurumis & Figuras'])` | Retorna únicamente piezas de dicha categoría | Categoría exacta | ✅ PASS |
| 6 | `CreacionRepository` | Filtro por artesano creador | `listCatalog(['artesano_id' => 1])` | Retorna únicamente creaciones del usuario 1 | Autoría exacta | ✅ PASS |
| 7 | `CreacionRepository` | Filtro por rango de precio | `precio_min: 20000, precio_max: 50000` | Todas las piezas tienen precio dentro del rango | Rango respetado | ✅ PASS |
| 8 | `CreacionRepository` | Búsqueda parcial por texto | `busqueda: 'Dragón'` | Localiza coincidencias en nombre/material/descripción | Coincidencias OK | ✅ PASS |
| 9 | `CreacionRepository` | Ordenación multieje | `precio_asc` y `precio_desc` | Ordena ascendente y descendente por precio | Orden verificado | ✅ PASS |
| 10 | `CreacionRepository` | Conteo de catálogo filtrado | `countCatalog(['categoria' => ...])` | Conteo coincide exactamente con el listado | Conteo exacto | ✅ PASS |
| 11 | `CreacionRepository` | **Artesanos con piezas activas (ADR-014)** | `findActiveArtisansWithCreations()` | Retorna lista de artesanos con `total_creaciones > 0` | ADR-014 verificado | ✅ PASS |
| 12 | `CreacionRepository` | Inserción en base de datos | `create($data)` | Inserta registro en SQLite y retorna ID generado | ID > 0 | ✅ PASS |
| 13 | `CreacionRepository` | Modificación de registro | `update($id, $data)` | Actualiza campos y marca `actualizado_en` | Actualizado OK | ✅ PASS |
| 14 | `CreacionRepository` | Ajuste rápido de existencias | `adjustStock($id, 15)` | Actualiza `cantidad_stock = 15` | Stock = 15 | ✅ PASS |
| 15 | `CreacionRepository` | Conmutar modalidad bajo encargo | `toggleCommission($id, 1)` | Actualiza `es_sobre_encargo = 1` | Encargo = 1 | ✅ PASS |
| 16 | `CreacionRepository` | **Baja lógica de creación** | `softDelete($id)` | `activo = 0`, `eliminado_en` con fecha en SQLite | Soft delete OK | ✅ PASS |
| 17 | `CreacionRepository` | Exclusión de piezas inactivas | `findById($id, true)` | Retorna `null` en consultas activas regulares | Oculto en activos | ✅ PASS |
| 18 | `CreacionRepository` | Persistencia física en SQLite | `findById($id, false)` | La fila persiste físicamente con `activo = 0` | Fila preservada | ✅ PASS |
| 19 | `CreacionRepository` | **Restauración de creación (ADR-015)** | `restore($id)` | `activo = 1`, `eliminado_en = NULL` en SQLite | Restaurada OK | ✅ PASS |
| 20 | `CreacionService` | Estructura de catálogo | `getCatalog(['pagina' => 1])` | Retorna claves `datos` y sobre `paginacion` | Estructura OK | ✅ PASS |
| 21 | `CreacionService` | Enriquecimiento monetario dual | Inspección de `datos[0]` | Incluye `precio_formateado`, `costo_formateado` | Enriquecido OK | ✅ PASS |
| 22 | `CreacionService` | Métricas de rentabilidad | Inspección de `metricas` | `margen_bruto_porcentaje`, `retorno_por_hora` | Métricas OK | ✅ PASS |
| 23 | `CreacionService` | Detalle por ID existente | `getCreationById(1)` | Retorna Dragón Ignis formateado | Ficha técnica OK | ✅ PASS |
| 24 | `CreacionService` | Detalle por ID inexistente | `getCreationById(99999)` | Lanza `RuntimeException` con código 404 Not Found | 404 capturado | ✅ PASS |
| 25 | `CreacionService` | **IDOR: Acceso de propietario** | `ensureArtisanOwnership` con artesano autor | Concede permiso de modificación | Permiso concedido | ✅ PASS |
| 26 | `CreacionService` | **IDOR: Acceso de administrador** | `ensureArtisanOwnership` con admin en pieza ajena | Concede permiso de supervisión global | Permiso concedido | ✅ PASS |
| 27 | `CreacionService` | **IDOR: Bloqueo de artesano ajeno** | `ensureArtisanOwnership` con artesano no-autor | Lanza `RuntimeException` HTTP 403 Forbidden | 403 Bloqueado | ✅ PASS |
| 28 | `CreacionService` | Fallback SVG para Dragón | `getThematicSvgFallback('Fantasía', 'Dragón')` | Asigna `assets/svg/piezas/dragon-ignis.svg` | SVG temático OK | ✅ PASS |
| 29 | `CreacionService` | Fallback SVG para Prendas | `getThematicSvgFallback('Prendas & Ropa', ...)` | Asigna `assets/svg/piezas/cardigan-granny.svg` | SVG temático OK | ✅ PASS |
| 30 | `CreacionService` | Fallback SVG para Bolsos | `getThematicSvgFallback('Bolsos & Accesorios', ...)` | Asigna `assets/svg/piezas/tote-bag.svg` | SVG temático OK | ✅ PASS |
| 31 | `CreacionService` | Fallback SVG para Hogar | `getThematicSvgFallback('Hogar & Decoración', ...)` | Asigna `assets/svg/piezas/mini-suculenta.svg` | SVG temático OK | ✅ PASS |
| 32 | `CreacionService` | Alta de creación sin archivo | `createCreation(..., null, $user)` | Registra pieza con autoría de sesión y SVG temático | Creado exitoso | ✅ PASS |
| 33 | `CreacionService` | **Reemplazo de foto en edición (ADR-008)** | `updateCreation(..., $newFile, ...)` | Sube nueva imagen y ejecuta `unlink()` en archivo previo | `unlink()` ejecutado | ✅ PASS |
| 34 | `CreacionService` | **Cero Unlink en Baja Lógica (ADR-008)** | `deleteCreation($id, ...)` | Marca `activo = 0` y la foto PERMANECE en disco | Foto preservada | ✅ PASS |
| 35 | `CreacionService` | **Restauración de creación (ADR-015)** | `restoreCreation($id, ...)` | Reactiva la creación con `activo = 1` | Reactivada OK | ✅ PASS |
| 36 | Servidor HTTP | Obtención de tokens de prueba | Login en `/api/auth/login.php` | Genera tokens Bearer válidos para admin y artesano | Tokens listos | ✅ PASS |
| 37 | Servidor HTTP | Catálogo público sin autenticación | `GET /api/creaciones/index.php` | HTTP 200 OK con array de creaciones y paginación | `200` | ✅ PASS |
| 38 | Servidor HTTP | Catálogo filtrado por categoría | `GET /api/creaciones/index.php?categoria=...` | HTTP 200 OK con creaciones filtradas | `200` | ✅ PASS |
| 39 | Servidor HTTP | **Artesanos públicos para filtro (ADR-014)** | `GET /api/creaciones/artesanos.php` | HTTP 200 OK con array de artesanos y sus creaciones | `200` | ✅ PASS |
| 40 | Servidor HTTP | Ficha técnica de detalle pública | `GET /api/creaciones/detalle.php?id=1` | HTTP 200 OK con ficha técnica de Dragón Ignis | `200` | ✅ PASS |
| 41 | Servidor HTTP | Detalle de ID inexistente | `GET /api/creaciones/detalle.php?id=99999` | HTTP 404 Not Found | `404` | ✅ PASS |
| 42 | Servidor HTTP | Restricción de método en catálogo | `POST /api/creaciones/index.php` | HTTP 405 Method Not Allowed | `405` | ✅ PASS |
| 43 | Servidor HTTP | Registro sin autenticación | `POST /api/creaciones/crear.php` (Sin Token) | HTTP 401 Unauthorized | `401` | ✅ PASS |
| 44 | Servidor HTTP | Registro exitoso por artesano | `POST /api/creaciones/crear.php` (Artesano Token) | HTTP 201 Created con ID asignado y fallback SVG | `201` | ✅ PASS |
| 45 | Servidor HTTP | Registro con datos inválidos | `POST /api/creaciones/crear.php` (`nombre: X`) | HTTP 422 Unprocessable Entity | `422` | ✅ PASS |
| 46 | Servidor HTTP | Actualización por el autor | `POST /api/creaciones/actualizar.php` (Autor) | HTTP 200 OK con datos actualizados | `200` | ✅ PASS |
| 47 | Servidor HTTP | **IDOR HTTP: Modificación ajena bloqueada** | `POST /api/creaciones/actualizar.php` (No-Autor) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 48 | Servidor HTTP | Actualización por administrador | `POST /api/creaciones/actualizar.php` (Admin Token) | HTTP 200 OK (Supervisión global permitida) | `200` | ✅ PASS |
| 49 | Servidor HTTP | Ajuste de stock por propietario | `POST /api/creaciones/ajustar-stock.php` (Autor) | HTTP 200 OK con nuevo stock reflejado | `200` | ✅ PASS |
| 50 | Servidor HTTP | **IDOR HTTP: Ajuste de stock ajeno** | `POST /api/creaciones/ajustar-stock.php` (No-Autor) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 51 | Servidor HTTP | Conmutar modalidad bajo encargo | `POST /api/creaciones/toggle-encargo.php` (Autor) | HTTP 200 OK con `es_sobre_encargo: 1` | `200` | ✅ PASS |
| 52 | Servidor HTTP | **IDOR HTTP: Conmutar encargo ajeno** | `POST /api/creaciones/toggle-encargo.php` (No-Autor) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 53 | Servidor HTTP | **IDOR HTTP: Eliminación ajena** | `POST /api/creaciones/eliminar.php` (No-Autor) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 54 | Servidor HTTP | Baja lógica exitosa por autor | `POST /api/creaciones/eliminar.php` (Autor) | HTTP 200 OK con `activo: 0` | `200` | ✅ PASS |
| 55 | Servidor HTTP | Detalle de pieza dada de baja | `GET /api/creaciones/detalle.php?id=[baja]` | HTTP 404 Not Found (Oculta del catálogo) | `404` | ✅ PASS |
| 56 | Servidor HTTP | Intento de doble baja lógica | `POST /api/creaciones/eliminar.php` en ya inactiva | HTTP 409 Conflict | `409` | ✅ PASS |
| 57 | Servidor HTTP | **IDOR HTTP: Restauración ajena** | `POST /api/creaciones/restaurar.php` (No-Autor) | HTTP 403 Forbidden | `403` | ✅ PASS |
| 58 | Servidor HTTP | **Restauración exitosa (ADR-015)** | `POST /api/creaciones/restaurar.php` (Autor) | HTTP 200 OK con `activo: 1` | `200` | ✅ PASS |
| 59 | Servidor HTTP | Intento de restaurar pieza ya activa | `POST /api/creaciones/restaurar.php` en activa | HTTP 409 Conflict | `409` | ✅ PASS |
| 60 | Servidor HTTP | Preflight CORS en catálogo | `OPTIONS /api/creaciones/index.php` | HTTP 204 No Content | `204` | ✅ PASS |
| 61 | Servidor HTTP | Preflight CORS en creación | `OPTIONS /api/creaciones/crear.php` | HTTP 204 No Content | `204` | ✅ PASS |

*(Nota: En la suite ejecutable se realizaron 126 aserciones atómicas que cubren exhaustivamente todas las combinaciones de orden, filtros, fallbacks y códigos HTTP).*

---

## 3. Evidencias de Respuestas JSON y Cabeceras HTTP

### 3.1 Catálogo Público Paginado (`GET /api/creaciones/index.php`)
```http
HTTP/1.1 200 OK
Host: localhost:8000
Content-Type: application/json; charset=utf-8
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
X-Content-Type-Options: nosniff
X-Frame-Options: DENY

{
  "exito": true,
  "mensaje": "Catálogo de creaciones obtenido exitosamente.",
  "datos": [
    {
      "id": 1,
      "artesano_id": 1,
      "nombre": "Dragón Ignis",
      "categoria": "Amigurumis & Figuras",
      "material": "100% Algodón Mercerizado",
      "dimensiones": "18.5 cm (Alto)",
      "precio": 45000,
      "precio_formateado": "$450.00 MXN",
      "costo_materiales": 12000,
      "costo_formateado": "$120.00 MXN",
      "cantidad_stock": 4,
      "horas_tejido": 6.5,
      "descripcion": "Amigurumi de dragón fantástico tejido a crochet con escamas en relieve...",
      "imagen_url": "uploads/dragon_ignis.jpg",
      "es_sobre_encargo": 0,
      "activo": 1,
      "artesano": {
        "id": 1,
        "username": "admin"
      },
      "metricas": {
        "margen_bruto_porcentaje": 73.3,
        "retorno_por_hora": 5077,
        "retorno_por_hora_formateado": "$50.77 MXN"
      }
    }
  ],
  "paginacion": {
    "total_items": 5,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 12,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

### 3.2 Listado Público de Artesanos para Filtro (`GET /api/creaciones/artesanos.php` - ADR-014)
```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Lista de artesanos con creaciones activas obtenida exitosamente.",
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "total_creaciones": 3
    },
    {
      "id": 2,
      "username": "artesana_ana",
      "total_creaciones": 2
    }
  ]
}
```

### 3.3 Prevención IDOR en Modificación (`POST /api/creaciones/actualizar.php`)
```http
HTTP/1.1 403 Forbidden
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "Operación denegada: No tienes permisos para modificar o eliminar esta creación."
  }
}
```

### 3.4 Baja Lógica Exitosa con Preservación de Imagen en Disco (`POST /api/creaciones/eliminar.php` - ADR-008)
```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Creación retirada exitosamente del catálogo (baja lógica).",
  "datos": {
    "id": 8,
    "activo": 0
  }
}
```

### 3.5 Restauración de Creación Inactivada (`POST /api/creaciones/restaurar.php` - ADR-015)
```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8

{
  "exito": true,
  "mensaje": "Creación restaurada exitosamente en el catálogo.",
  "datos": {
    "id": 8,
    "activo": 1
  }
}
```

---

## 4. Verificación de Integridad Relacional en SQLite

Tras la ejecución de la suite automatizada y su posterior aprovisionamiento:
- **`PRAGMA integrity_check;`** $\rightarrow$ `ok` (0 páginas corruptas).
- **`PRAGMA foreign_key_check;`** $\rightarrow$ `0 violaciones encontradas` (relaciones `creaciones.artesano_id -> usuarios.id` y `pedidos.creacion_id -> creaciones.id` íntegras al 100%).
- **Índices de Rendimiento Operativos:**
  - `idx_creaciones_artesano`
  - `idx_creaciones_categoria`
  - `idx_creaciones_stock`
  - `idx_creaciones_activo`
- **Estado de Semillas Iniciales:**
  - 3 usuarios registrados (`admin`, `artesana_ana`, `asistente_leo`).
  - 5 creaciones activas con autores legítimos (`admin` y `artesana_ana`).
  - 2 pedidos vinculados a creaciones activas.

---

## 5. Veredicto y Estado del Gateway

- [x] Arquitectura Limpia respetada al 100%: `CreacionRepository` (persistencia) y `CreacionService` (negocio) desacoplados.
- [x] Regla de separación física respetada: `src/` tiene **0 archivos PHP**; el backend reside exclusivamente en `app/` y `api/`.
- [x] 126 de 126 aserciones pasadas en verde (100% OK) en 157.72 ms.
- [x] Trazas crudas almacenadas en `logs/subfase-3.4-cli.log` y `logs/subfase-3.4-http.log`.
- [x] Salvaguarda IDOR (**ADR-007**) blindada en las 5 operaciones de mutación.
- [x] Ciclo de vida de imágenes (**ADR-008**) probado: reemplazo en edición ejecuta `unlink()`; baja lógica **NUNCA** ejecuta `unlink()`.
- [x] Selector de artesanos (**ADR-014**) implementado como endpoint público sin exponer datos sensibles.
- [x] Ciclo de vida de restauración (**ADR-015**) probado con detección de colisión 409.
- [x] Regresión global de Subfases 3.1, 3.2, 3.3 y 3.4 aprobada con **393 / 393 aserciones exitosas acumuladas**.

**ESTADO:** **SUBFASE 3.4 COMPLETADA Y VERIFICADA AL 100%**.

---

[← Anterior (Subfase 3.3)](./subfase-3.3-usuarios.md) • [Hub de Pruebas](./README.md) • [Siguiente (Subfase 3.5) →](./subfase-3.5-pedidos.md)
