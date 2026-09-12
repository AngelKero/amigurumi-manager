# Reporte de Pruebas: Subfase 3.1 — Base del Backend & Infraestructura Nuclear

- **Fecha de Ejecución:** 2026-09-11 22:28:15 CST
- **Responsable:** Antigravity Agent (Pair Programming con Ingeniero Titular)
- **Entorno:** PHP 8.3.29 CLI + Servidor Built-in (`localhost:8000`) + SQLite 3 (macOS Darwin)
- **Archivos de Log Crudos:**
  - CLI: [`logs/subfase-3.1-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.1-cli.log)
  - HTTP: [`logs/subfase-3.1-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.1-http.log)
- **Script de Pruebas:** [`tests/test-subfase-3.1.php`](file:///Users/angelzaragoza/Desktop/proyecto-web/tests/test-subfase-3.1.php)
- **Resultado General:** **92 / 92 Aserciones Aprobadas (100% OK) en 11.74 ms** — ✅ **APTO PARA AVANZAR**

---

## 1. Matriz de Aserciones y Casos Evaluados

| # | Componente / Capa | Caso de Prueba Evaluado | Entrada / Condición | Comportamiento Esperado | Resultado | Estado |
| :-: | :--- | :--- | :--- | :--- | :---: | :---: |
| 1 | `app/autoload.php` | Carga de clases bajo `App\` | `class_exists(Config::class)` | Autoloading dinámico sin Composer | `true` | ✅ PASS |
| 2 | `App\Core\Config` | Acceso con notación por puntos | `Config::get('app.name')` | Resuelve `'Crochet Manager'` | `'Crochet Manager'` | ✅ PASS |
| 3 | `App\Core\Config` | Zona horaria del sistema | `Config::get('app.timezone')` | Resuelve `'America/Mexico_City'` | Coincide con `date_default_timezone_get()` | ✅ PASS |
| 4 | `App\Core\Config` | Fallbacks y escritura en memoria | `Config::set('test.subfase', '3.1')` | Almacena y recupera clave anidada | `'3.1'` | ✅ PASS |
| 5 | `App\Core\Database` | Patrón Singleton estricto | Instanciación consecutiva | `Database::getInstance() === Database::getInstance()` | Misma referencia PDO | ✅ PASS |
| 6 | `App\Core\Database` | Claves foráneas obligatorias | Consulta `PRAGMA foreign_keys;` | Valor entero `1` (ON) | `1` | ✅ PASS |
| 7 | `App\Core\Database` | Verificación de integridad SQLite | Consulta `PRAGMA integrity_check;` | Cadena `'ok'` | `'ok'` | ✅ PASS |
| 8 | `App\Core\Database` | Bloqueo de violación referencial | Inserción con `artesano_id: 99999` | Lanza `PDOException` (FOREIGN KEY constraint failed) | Excepción capturada | ✅ PASS |
| 9 | `App\Core\Request` | Parámetros GET / POST / JSON | Inyección de query, form y JSON | Recuperación tipada y priorización JSON | Valores recuperados | ✅ PASS |
| 10 | `App\Core\Request` | Extracción de Bearer Token | `Authorization: Bearer <token>` | Extrae token sin prefijo `'Bearer '` | Token limpio | ✅ PASS |
| 11 | `App\Core\Request` | Retención de usuario autenticado | `Request::setUser($user)` | `Request::user()` devuelve array del usuario | Array idéntico | ✅ PASS |
| 12 | `App\Core\Response` | Envolvente estándar de éxito | `Response::success($data, 'Pieza registrada', 201)` | JSON con `exito: true`, `codigo: 201`, `datos` | Formato canónico | ✅ PASS |
| 13 | `App\Core\Response` | Envolvente estándar de error | `Response::error('Mensaje', 422, $detalles)` | JSON con `exito: false`, `codigo: 422`, `detalles` | Formato canónico | ✅ PASS |
| 14 | `App\Core\TokenManager` | Formato de token firmado | `TokenManager::generate($user)` | Estructura compacta `"payloadB64.firmaHex"` | 2 partes delimitadas por punto | ✅ PASS |
| 15 | `App\Core\TokenManager` | Verificación de token legítimo | `TokenManager::verify($token)` | Retorna claims `sub`, `username`, `rol`, `exp` | Payload verificado | ✅ PASS |
| 16 | `App\Core\TokenManager` | Ataque por manipulación (Tampering) | Firma o payload alterado (+1 char) | Retorna `null` mediante `hash_equals()` | `null` | ✅ PASS |
| 17 | `App\Core\TokenManager` | Rechazo de tokens expirados | Token con `exp` vencido (-3600s) | Retorna `null` | `null` | ✅ PASS |
| 18 | `App\Utils\CurrencyHelper`| Conversión centavos a pesos | `formatCents(45000)` / `centsToMxn(45000)` | `"$450.00 MXN"` y `450.0` float | Formatos válidos | ✅ PASS |
| 19 | `App\Utils\CurrencyHelper`| Conversión pesos a centavos | `mxnToCents('$450.00 MXN')` | `45000` entero en SQLite | `45000` | ✅ PASS |
| 20 | `App\Utils\CurrencyHelper`| Enriquecimiento dual y márgenes | `enrichCreation($creacion)` | Calcula margen bruto % y retorno por hora | Métricas exactas | ✅ PASS |
| 21 | `App\Utils\PaginationHelper`| Cálculo de páginas y límites | `build(total: 100, page: 1, limit: 12)` | `total_paginas: 9`, `tiene_siguiente: true` | Sobre estructurado | ✅ PASS |
| 22 | `App\Utils\SvgHelper` | Renderizado y atributos inline | `svg('dragon-ignis', ['width' => 120])` | Inyección de atributos en `<svg ...>` | Marcado seguro | ✅ PASS |
| 23 | Servidor Local HTTP | Acceso a catálogo público | `GET http://localhost:8000/index.php` | HTTP 200 OK | `200` | ✅ PASS |
| 24 | Servidor Local HTTP | Blindaje directo a `app/config.php` | `GET http://localhost:8000/app/config.php` | HTTP 403 Forbidden (JSON `exito: false`) | `403` | ✅ PASS |
| 25 | Servidor Local HTTP | Verificación de reglas `.htaccess` | Análisis de directivas `.htaccess` | Bloqueo a `app/`, `database/`, `.sqlite` y FastCGI | Reglas presentes | ✅ PASS |
| 26 | Servidor Local HTTP | Preflight CORS OPTIONS | `OPTIONS http://localhost:8000/index.php` | Código de preflight válido (200/204) | `200` | ✅ PASS |

---

## 2. Evidencia de Respuestas JSON y Cabeceras

### 2.1 Emisión Exitosa (`Response::success` — HTTP 201)
```json
{
  "exito": true,
  "mensaje": "Pieza registrada",
  "datos": {
    "id": 10,
    "nombre": "Gorro Alpaca"
  },
  "paginacion": {
    "total": 1
  }
}
```

### 2.2 Error Controlado (`Response::error` — HTTP 422)
```json
{
  "exito": false,
  "error": {
    "codigo": 422,
    "mensaje": "Stock insuficiente para completar el pedido",
    "detalles": {
      "disponible": 0,
      "solicitado": 2
    }
  }
}
```

### 2.3 Captura Global del ErrorHandler (HTTP 500 — Purga de Buffer)
Cuando se produce una excepción no controlada en el backend, el `ErrorHandler` purga cualquier contenido preliminar con `while (ob_get_level() > 0) ob_end_clean();` y emite exclusivamente JSON:
```json
{
  "exito": false,
  "error": {
    "codigo": 500,
    "mensaje": "Error interno del servidor"
  }
}
```

### 2.4 Bloqueo de Acceso Directo a `app/config.php` (HTTP 403)
```http
HTTP/1.1 403 Forbidden
Host: localhost:8000
Content-Type: application/json; charset=utf-8

{
  "exito": false,
  "error": {
    "codigo": 403,
    "mensaje": "Acceso denegado: este archivo de configuración no es accesible públicamente."
  }
}
```

---

## 3. Verificación de Integridad en SQLite

Ejecución directa en SQLite 3 sobre [`database/database.sqlite`](file:///Users/angelzaragoza/Desktop/proyecto-web/database/database.sqlite):
- **Estado de Claves Foráneas:**
  ```sql
  PRAGMA foreign_keys;
  -- Resultado: 1 (Habilitado estrictamente en cada conexión PDO)
  ```
- **Integridad Física de la Base de Datos:**
  ```sql
  PRAGMA integrity_check;
  -- Resultado: ok (Cero páginas corruptas o anomalías de árbol B)
  ```
- **Prueba de Restricción Referencial:**
  - Intento de inserción de creación con artesano huérfano (`artesano_id = 99999`) rechazado de inmediato con `FOREIGN KEY constraint failed`.

---

## 4. Veredicto y Siguientes Pasos

- [x] Autocargador PSR-4 nativo (`app/autoload.php`) funcional sin Composer.
- [x] Configuración centralizada con notación de puntos (`App\Core\Config`).
- [x] Conexión Singleton PDO SQLite con `PRAGMA foreign_keys = ON;` (`App\Core\Database`).
- [x] Manejador global `ErrorHandler` garantizando cero fugas HTML y salida JSON 500.
- [x] Abstracción de peticiones `Request` con extracción de Bearer Token.
- [x] Emisor de respuestas `Response` con resolución de preflight CORS OPTIONS.
- [x] Gestor criptográfico `TokenManager` con firma HMAC-SHA256, detección de manipulación y caducidad de 24h.
- [x] Asistente de paginación (`PaginationHelper`) con envolvente estándar para la API REST.
- [x] Utilidad monetaria (`CurrencyHelper`) con almacenamiento en centavos enteros y enriquecimiento dual.
- [x] Asistente vectorial (`SvgHelper`) con funciones globales `svg()` y `svg_url()`.
- [x] Erradicación total de backend en `src/`: la carpeta `src/` contiene **0 archivos PHP** (reservada 100% a frontend: `src/css/` y `src/js/`), y todas las utilidades de servidor residen limpiamente en [`app/Utils/`](file:///Users/angelzaragoza/Desktop/proyecto-web/app/Utils/).
- [x] Retrocompatibilidad absoluta: Vistas SSR existentes (`index.php`, `creaciones.php`, `detalle.php`, `formulario.php`, `pedidos.php`, `usuarios.php`) cargan desde `app/autoload.php` y responden HTTP 200 sin regresiones.
- [x] 92 de 92 aserciones aprobadas en el script automatizado CLI.
- [x] Logs respaldados en [`logs/subfase-3.1-cli.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.1-cli.log) y [`logs/subfase-3.1-http.log`](file:///Users/angelzaragoza/Desktop/proyecto-web/logs/subfase-3.1-http.log).

**ESTADO ACTUAL:** **COMPLETA Y VERIFICADA AL 100%.**
**ACCIONES SIGUIENTES:** En cumplimiento estricto del protocolo de subfases y la compuerta de testing, se detiene completamente la ejecución y se solicita la autorización explícita del usuario para iniciar la **Subfase 3.2: Autenticación Stateless & Middleware de Seguridad (AuthGuard & RoleGuard)**.
