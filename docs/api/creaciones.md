# API REST: Creaciones & Catálogo (`/api/creaciones/`)

[← Volver al Índice de API](./README.md)

Este módulo gestiona el catálogo público de piezas en crochet, la lista de artesanos con creaciones activas para filtrado, y las operaciones de inventario, stock, modalidad de encargo y fotografías para artesanos y administradores.

---

## 1. Catálogo Público Paginado (`GET /api/creaciones/index.php`)

Recupera piezas artesanales activas (`activo = 1`) con filtros combinables, ordenación parametrizada y sobre estándar de paginación.

- **Acceso:** Público (Unauthenticated)
- **Método HTTP:** `GET`
- **Paginación por Defecto:** 12 ítems por página.

### Parámetros de Consulta (Query String)
| Parámetro | Tipo | Opcional | Descripción |
| :--- | :---: | :---: | :--- |
| `categoria` | `string` | Sí | Filtrar por categoría exacta (ej. `Prendas & Ropa`, `Fantasía`, `Bolsos & Accesorios`). |
| `artesano_id` | `int` | Sí | Filtrar por el ID del artesano creador. |
| `precio_min` | `int` | Sí | Precio mínimo en centavos (ej. `10000` = $100.00 MXN). |
| `precio_max` | `int` | Sí | Precio máximo en centavos (ej. `50000` = $500.00 MXN). |
| `busqueda` | `string` | Sí | Búsqueda parcial por texto en nombre, material o descripción. |
| `es_sobre_encargo` | `int` | Sí | `1` para piezas bajo encargo exclusivo; `0` para catálogo regular. |
| `solo_en_stock` | `bool` | Sí | `1` o `true` para listar únicamente piezas con `cantidad_stock > 0`. |
| `orden` | `string` | Sí | Criterio de orden: `recientes` (defecto), `precio_asc`, `precio_desc`, `nombre_asc`. |
| `pagina` | `int` | Sí | Número de página (defecto: `1`). |
| `limite` | `int` | Sí | Elementos por página (defecto: `12`, máx: `50`). |

### Ejemplo de Petición
```bash
curl -X GET "http://localhost:8000/api/creaciones/index.php?categoria=Fantas%C3%ADa&pagina=1&limite=12"
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Catálogo de creaciones obtenido exitosamente.",
  "datos": [
    {
      "id": 1,
      "nombre": "Dragón Ignis",
      "categoria": "Fantasía",
      "material": "Hilaza 100% Algodón Mercerizado",
      "dimensiones": "22 cm de alto",
      "precio": 45000,
      "precio_formateado": "$450.00 MXN",
      "costo_materiales": 12000,
      "costo_formateado": "$120.00 MXN",
      "cantidad_stock": 3,
      "horas_tejido": 8.5,
      "descripcion": "Dragón tejido en punto bajo con detalles escamados en relieve.",
      "imagen_url": "uploads/dragon_ignis.jpg",
      "es_sobre_encargo": 0,
      "artesano": {
        "id": 1,
        "username": "admin"
      },
      "metricas": {
        "margen_bruto_porcentaje": 73.33,
        "retorno_por_hora": 3882,
        "retorno_por_hora_formateado": "$38.82 MXN"
      }
    }
  ],
  "paginacion": {
    "total_items": 1,
    "pagina_actual": 1,
    "total_paginas": 1,
    "limite": 12,
    "tiene_siguiente": false,
    "tiene_anterior": false
  }
}
```

---

## 2. Listado Público de Artesanos para Filtro (`GET /api/creaciones/artesanos.php`)

Retorna la lista de creadores activos que poseen al menos una creación activa en el catálogo. Se utiliza para alimentar dinámicamente el selector `#filterArtisan` en la interfaz pública sin requerir permisos de administrador.

- **Acceso:** Público (Unauthenticated)
- **Método HTTP:** `GET`

### Ejemplo de Petición
```bash
curl -X GET http://localhost:8000/api/creaciones/artesanos.php
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
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

---

## 3. Detalle de una Creación (`GET /api/creaciones/detalle.php?id=X`)

Recupera la ficha técnica completa de una pieza activa por su ID.

- **Acceso:** Público (Unauthenticated)
- **Método HTTP:** `GET`

### Ejemplo de Petición
```bash
curl -X GET "http://localhost:8000/api/creaciones/detalle.php?id=1"
```

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Detalle de creación recuperado exitosamente.",
  "datos": {
    "id": 1,
    "nombre": "Dragón Ignis",
    "categoria": "Fantasía",
    "material": "Hilaza 100% Algodón Mercerizado",
    "dimensiones": "22 cm de alto",
    "precio": 45000,
    "precio_formateado": "$450.00 MXN",
    "costo_materiales": 12000,
    "costo_formateado": "$120.00 MXN",
    "cantidad_stock": 3,
    "horas_tejido": 8.5,
    "descripcion": "Dragón tejido en punto bajo con detalles escamados en relieve.",
    "imagen_url": "uploads/dragon_ignis.jpg",
    "es_sobre_encargo": 0,
    "artesano": {
      "id": 1,
      "username": "admin"
    }
  }
}
```

### Errores Posibles
- **`HTTP 404 Not Found`:** Si la pieza no existe o se encuentra dada de baja lógica (`activo = 0`).

---

## 4. Crear Creación (`POST /api/creaciones/crear.php`)

Registra una nueva pieza en el catálogo. Soporta subida de archivos de imagen vía `multipart/form-data` o asignación automática de un vector SVG representativo de `assets/svg/piezas/` si no se envía fotografía.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()`)
- **Método HTTP:** `POST`
- **Cabeceras:** `Authorization: Bearer <token>`, `Content-Type: multipart/form-data` o `application/json`

### Parámetros de Entrada
| Campo | Tipo | Obligatorio | Descripción / Reglas |
| :--- | :---: | :---: | :--- |
| `nombre` | `string` | Sí | Nombre de la pieza (2 a 100 caracteres). |
| `categoria` | `string` | Sí | Categoría de la pieza (2 a 50 caracteres). |
| `material` | `string` | Sí | Composición de la fibra (3 a 80 caracteres). |
| `dimensiones` | `string` | Sí | Medidas físicas (2 a 100 caracteres). |
| `precio` | `int` | Sí | Precio de venta en centavos (1 a 9,999,999). |
| `costo_materiales`| `int` | No | Costo de materiales en centavos (defecto: `0`). |
| `cantidad_stock` | `int` | No | Unidades en inventario físico (defecto: `0`). |
| `horas_tejido` | `float` | No | Horas de labor manual (0.0 a 500.0). |
| `descripcion` | `string` | No | Descripción artesanal (máx 2000 caracteres). |
| `es_sobre_encargo`| `int` | No | `1` para pieza bajo encargo; `0` regular (defecto: `0`). |
| `imagen` | `file` | No | Fotografía en formato JPEG/PNG/WebP ($\le 5\text{MB}$). |

### Respuesta Exitosa (`HTTP 201 Created`)
```json
{
  "exito": true,
  "mensaje": "Creación registrada exitosamente en el catálogo.",
  "datos": {
    "id": 6,
    "nombre": "Gorro Nórdico Alpaca",
    "categoria": "Prendas & Ropa",
    "precio_formateado": "$380.00 MXN",
    "imagen_url": "uploads/c7f8a9...b2.jpg"
  }
}
```

---

## 5. Actualizar Creación (`POST /api/creaciones/actualizar.php`)

Actualiza los datos técnicos, económicos o la fotografía de una pieza existente.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()` + Salvaguarda IDOR).
- **Protección IDOR:** Solo el autor de la pieza (`artesano_id === current_user.id`) o un `admin` pueden modificarla. Violaciones emiten `HTTP 403 Forbidden`.
- **Ciclo de Vida de Fotos:** Si se sube una nueva imagen y la anterior era un archivo local en `uploads/`, el archivo previo es eliminado físicamente del disco con `unlink()`.

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Creación actualizada exitosamente.",
  "datos": {
    "id": 6,
    "nombre": "Gorro Nórdico Alpaca Premium",
    "actualizado_en": "2026-09-12 14:10:00"
  }
}
```

---

## 6. Eliminar Creación — Baja Lógica (`POST /api/creaciones/eliminar.php`)

Aplica una baja lógica a la creación (`activo = 0`, `eliminado_en = datetime(...)`).

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()` + Salvaguarda IDOR).
- **Regla Universal Inviolable:** **CERO `unlink()` en baja lógica.** La imagen en disco NO se destruye, garantizando que los pedidos históricos que compraron esta pieza sigan mostrando su miniatura fotográfica intacta.

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Creación retirada exitosamente del catálogo (baja lógica).",
  "datos": {
    "id": 6,
    "activo": 0
  }
}
```

---

## 7. Restaurar Creación (`POST /api/creaciones/restaurar.php`)

Revierte la baja lógica de una creación previamente inactivada (`activo = 1`, `eliminado_en = NULL`), reintegrándola al catálogo público.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()` + Salvaguarda IDOR).

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Creación restaurada exitosamente en el catálogo.",
  "datos": {
    "id": 6,
    "activo": 1
  }
}
```

---

## 8. Ajustar Stock In-Situ (`POST /api/creaciones/ajustar-stock.php`)

Actualiza rápidamente la cantidad de existencias físicas disponibles.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()` + Salvaguarda IDOR).
- **Payload:** `{"id": 1, "cantidad_stock": 5}`

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Stock de la creación actualizado exitosamente.",
  "datos": {
    "id": 1,
    "cantidad_stock": 5
  }
}
```

---

## 9. Conmutar Modalidad Bajo Encargo (`POST /api/creaciones/toggle-encargo.php`)

Alterna el flag `es_sobre_encargo` entre `1` y `0`.

- **Acceso:** Protegido (`RoleGuard::artisanOrAdmin()` + Salvaguarda IDOR).
- **Payload:** `{"id": 1, "es_sobre_encargo": 1}`

### Respuesta Exitosa (`HTTP 200 OK`)
```json
{
  "exito": true,
  "mensaje": "Modalidad de encargo conmutada exitosamente.",
  "datos": {
    "id": 1,
    "es_sobre_encargo": 1
  }
}
```
