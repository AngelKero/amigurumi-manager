# 🧪 Centro de Pruebas & Reportes de Subfases (Testing Hub)

[← Volver al Hub Principal de Documentación](../README.md)

---

## 📊 Estado de Reportes por Subfase

| Subfase | Nombre del Dominio | Script CLI | Aserciones | Estado | Reporte Ejecutivo |
| :---: | :--- | :--- | :--- | :---: | :--- |
| **3.1** | Infraestructura Nuclear & Core | `tests/test-subfase-3.1.php` | 93 / 93 (100%) | ✅ Aprobado | [Ver Reporte 3.1](./subfase-3.1-core.md) |
| **3.2** | Autenticación Stateless & Bearer | `tests/test-subfase-3.2.php` | 69 / 69 (100%) | ✅ Aprobado | [Ver Reporte 3.2](./subfase-3.2-auth.md) |
| **3.3** | Usuarios, Roles RBAC & Bajas Lógicas | `tests/test-subfase-3.3.php` | 105 / 105 (100%) | ✅ Aprobado | [Ver Reporte 3.3](./subfase-3.3-usuarios.md) |
| **3.4** | Catálogo, Creaciones & Ciclo de Imágenes | `tests/test-subfase-3.4.php` | 126 / 126 (100%) | ✅ Aprobado | [Ver Reporte 3.4](./subfase-3.4-creaciones.md) |
| **3.5** | Pedidos & Transacciones Atómicas | `tests/test-subfase-3.5.php` | 139 / 139 (100%) | ✅ Aprobado | [Ver Reporte 3.5](./subfase-3.5-pedidos.md) |
| **3.6.1** | Acceso, Autorización, IDOR & RBAC | `tests/test-subfase-3.6.1.php` | 165 / 165 (100%) | ✅ Aprobado | [Ver Reporte 3.6.1](./subfase-3.6.1-idor-access-control.md) |
| **3.6.2** | Criptografía, Auth & Datos Sensibles | `tests/test-subfase-3.6.2.php` | 141 / 141 (100%) | ✅ Aprobado | [Ver Reporte 3.6.2](./subfase-3.6.2-criptografia-autenticacion.md) |
| **3.6.3** | Inyección, Sanitización & Medios | `tests/test-subfase-3.6.3.php` | 157 / 157 (100%) | ✅ Aprobado | [Ver Reporte 3.6.3](./subfase-3.6.3-inyeccion-medios.md) |
| **3.6.4** | Lógica Negocio, Precios & Multibyte | `tests/test-subfase-3.6.4.php` | 151 / 151 (100%) | ✅ Aprobado | [Ver Reporte 3.6.4](./subfase-3.6.4-logica-precios.md) |
| **3.6.5** | Rendimiento SQLite & Regresión Global | `tests/test-subfase-3.6.5.php` | 141 / 141 (100%) | ✅ Aprobado | [Ver Reporte 3.6.5](./subfase-3.6.5-rendimiento-regresion.md) |
| **4.1** | Auth Bearer & Navbar Reactivo (Feature 004) | `tests/test-subfase-4.1.php` | 58 / 58 (100%) | ✅ Aprobado | [Ver Reporte 4.1](./subfase-4.1-auth-sesion.md) |
| **4.2** | Catálogo Dinámico & Filtros Textiles (Feature 005) | `tests/test-subfase-4.2.php` | 136 / 136 (100%) | ✅ Aprobado | [Ver Reporte 4.2](./subfase-4.2-catalogo.md) |
| **4.3** | Gestión de Creaciones & Subida Multipart (Feature 006) | `tests/test-subfase-4.3.php` | 212 / 212 (100%) | ✅ Aprobado | [Ver Reporte 4.3](./subfase-4.3-creaciones.md) |
| **4.3.1** | Scoping Servidor del Panel & Papelera (Feature 006) | `tests/test-subfase-4.3.1.php` | 62 / 62 (100%) | ✅ Aprobado | [Ver Reporte 4.3.1](./subfase-4.3.1-panel-scoping.md) |
| **4.3.2** | Contadores Correctos del Panel (Feature 006) | `tests/test-subfase-4.3.2.php` | 43 / 43 (100%) | ✅ Aprobado | [Ver Reporte 4.3.2](./subfase-4.3.2-contadores.md) |

**Total Acumulado Fase 3:** **1,287 / 1,287 Aserciones Aprobadas (100% OK en verde — Fase 3 Completa)** · verificado sobre semilla limpia con `php tests/cuenta-aserciones.php` (H-006).
**Total Acumulado Fase 4:** **511 / 511 Aserciones Aprobadas (100% OK en verde — Subfases 4.1, 4.2, 4.3, 4.3.1 y 4.3.2)** · verificado con `php tests/test-fase-4-acumulado.php`.

---

## 🔁 Suites de Regresión Acumulada por Fase (Gobernanza · Acción 3 · H-020)

Cada fase queda respaldada por una suite acumulada **obligatoria** al tocar sus features:

| Fase | Suite Acumulada | Mandato | Comando |
| :---: | :--- | :--- | :--- |
| **3** (Backend) | `tests/test-subfase-3.6.5.php` | Regresión global de Fase 3 | `php tests/test-subfase-3.6.5.php > logs/subfase-3.6.5-cli.log 2>&1` |
| **4** (Fullstack 004–008) | `tests/test-fase-4-acumulado.php` | Obligatoria al tocar features 004–008 | `php tests/test-fase-4-acumulado.php > logs/fase-4-acumulado.log 2>&1` |

La suite de Fase 4 hace **despliegue dinámico** (`test-subfase-4.*.php`): con 0 subfases implementadas
reporta un estado vacío válido (exit 0) y detecta `test-subfase-4.1.php` automáticamente cuando nazca.
Cada suite descubierta debe tener fila en este índice y cada fila su script físico (verificación estructural
incluida en el propio runner).

> **Divergencia CLI vs. HTTP:** si al completar una subfase CLI y HTTP no coinciden, aplicar el
> [Protocolo de Divergencia CLI/HTTP](./protocolo-divergencia-cli-http.md) (triaje entorno vs. código)
> antes de aprobar — regla anclada en `AGENTS.md §3`.

---

## 🔍 Auditorías Normativas con Context7 (Estándares & Librerías Oficiales)

| Parte | Fase Auditada | Tecnología / Librería | Identificador Context7 | Estado | Reporte de Auditoría |
| :---: | :--- | :--- | :--- | :---: | :--- |
| **Parte 1** | **Fase 1** (Base de Datos) | SQLite 3 Engine & DDL | `/websites/sqlite_docs` | ✅ Aprobado | [Ver Auditoría Fase 1](./auditoria-context7-fase-1.md) |
| **Parte 2** | **Fase 2** (Layout & UI) | Bootstrap 5.3 & a11y | `/websites/getbootstrap_5_3` | ✅ Aprobado | [Ver Auditoría Fase 2](./auditoria-context7-fase-2.md) |
| **Parte 3** | **Fase 3** (Backend Core) | PHP 8.1+ & OWASP | `/websites/php_net_manual_en` | ✅ Aprobado | [Ver Auditoría Fase 3](./auditoria-context7-fase-3.md) |


## 🏛️ Arquitectura de Testing en 3 Niveles

Para garantizar que el código sea testeado exhaustivamente sin mezclar documentación con código ni volcar trazas crudas al control de versiones, el sistema opera en 3 niveles estrictamente desacoplados:

```
proyecto-web/
├── docs/testing/                      # 📑 NIVEL 1: REPORTES EJECUTIVOS (Markdown, versionados en Git)
│   ├── README.md                      # [Este archivo] Índice maestro y plantilla estándar
│   ├── subfase-3.1-core.md            # Reporte formal de infraestructura nuclear
│   ├── subfase-3.2-auth.md            # Reporte de autenticación y Bearer tokens
│   ├── subfase-3.3-usuarios.md        # Reporte de RBAC y salvaguarda ID #1
│   ├── subfase-3.4-creaciones.md      # Reporte de catálogo, imágenes y SVG fallback
│   ├── subfase-3.5-pedidos.md         # Reporte de transacciones atómicas y stock
│   ├── subfase-3.6.1-idor-access-control.md # Reporte de IDOR, RBAC y método 405
│   ├── subfase-3.6.2-criptografia-autenticacion.md # Reporte de tokens HMAC, bcrypt y data exposure
│   ├── subfase-3.6.3-inyeccion-medios.md # Reporte de SQLi, XSS y carga de medios
│   ├── subfase-3.6.4-logica-precios.md # Reporte de precios en servidor y stock atómico
│   ├── subfase-3.6.5-rendimiento-regresion.md # Reporte de EXPLAIN QUERY PLAN y regresión
│   ├── subfase-4.1-auth-sesion.md  # Reporte de Auth Bearer & Navbar reactivo (Fase 4)
│   ├── subfase-4.2-catalogo.md  # Reporte de Catálogo Dinámico & Filtros Textiles (Fase 4)
│   ├── subfase-4.3-creaciones.md  # Reporte de Gestión de Creaciones & Subida Multipart (Fase 4)
│   ├── subfase-4.3.1-panel-scoping.md  # Reporte de Scoping Servidor del Panel & Papelera (Fase 4)
│   ├── subfase-4.3.2-contadores.md  # Reporte de Contadores Correctos del Panel (Fase 4)
│   └── protocolo-divergencia-cli-http.md # Triaje CLI vs. HTTP (H-015)
│
├── tests/                             # 🧪 NIVEL 2: SCRIPTS DE PRUEBA CLI (PHP Nativo, versionados)
│   ├── TestHelper.php                 # Utilidades de aserción y llamadas HTTP curl
│   ├── test-subfase-3.1.php           # Suite ejecutable de la subfase 3.1
│   ├── test-subfase-3.2.php           # Suite ejecutable de la subfase 3.2
│   ├── test-subfase-3.3.php           # Suite ejecutable de la subfase 3.3
│   ├── test-subfase-3.4.php           # Suite ejecutable de la subfase 3.4
│   ├── test-subfase-3.5.php           # Suite ejecutable de la subfase 3.5
│   ├── test-subfase-3.6.1.php         # Suite de IDOR, RBAC y acceso
│   ├── test-subfase-3.6.2.php         # Suite de criptografía y datos sensibles
│   ├── test-subfase-3.6.3.php         # Suite de inyección y medios
│   ├── test-subfase-3.6.4.php         # Suite de lógica y multibyte
│   ├── test-subfase-3.6.5.php         # Suite de rendimiento y regresión total
│   ├── test-subfase-4.1.php           # Suite de Auth Bearer & Navbar reactivo (004)
│   ├── test-subfase-4.2.php           # Suite de Catálogo Dinámico & Filtros Textiles (005)
│   ├── test-subfase-4.3.php           # Suite de Gestión de Creaciones & Subida Multipart (006)
│   ├── test-subfase-4.3.1.php         # Suite de Scoping Servidor del Panel & Papelera (006)
│   ├── test-subfase-4.3.2.php         # Suite de Contadores Correctos del Panel (006)
│   └── test-fase-4-acumulado.php      # Regresión acumulada dinámica de Fase 4 (004-008)
│
└── logs/                              # 🪵 NIVEL 3: LOGS CRUDOS Y TRAZAS (Archivos temporales, fuera de Git)
    ├── .gitignore                     # Ignora *.log, preserva la carpeta
    ├── .htaccess                      # Bloqueo total HTTP (Require all denied)
    ├── subfase-3.X-cli.log            # Salida cruda de terminal de la prueba 3.X
    └── subfase-3.X-http.log           # Trazas de curl con headers de respuesta
```

---

## 📋 Protocolo Obligatorio al Finalizar Cada Subfase (Regla de Oro)

Cualquier ingeniero o asistente de IA que trabaje en esta plataforma **debe seguir rigurosamente este flujo** al concluir la implementación de una subfase:

1. **Ejecución de Pruebas Automatizadas:**
   Ejecutar el script correspondiente enviando la salida cruda a `logs/`:
   ```bash
   php tests/test-subfase-X.X.php > logs/subfase-X.X-cli.log 2>&1
   ```
2. **Generación del Reporte Formal:**
   Crear o actualizar `docs/testing/subfase-X.X-[nombre].md` documentando casos evaluados, aserciones aprobadas, capturas de payload y tiempo de respuesta.
3. **Pausa Obligatoria & Compás de Espera:**
   Detener completamente la ejecución de código. Presentar el reporte ejecutivo al usuario y **solicitar su autorización explícita antes de tocar cualquier archivo de la siguiente subfase**.

---

## 📝 Plantilla Estándar de Reporte de Subfase

Cada reporte en `docs/testing/` debe seguir esta estructura homogénea:

```markdown
# Reporte de Pruebas: Subfase X.X — [Nombre de la Subfase]

- **Fecha de Ejecución:** [Fecha ISO 8601 o legible]
- **Responsable:** [Ingeniero / Agente IA]
- **Entorno:** PHP 8.x CLI + Servidor Built-in (`localhost:8000`) + SQLite 3
- **Archivo de Log Crudo:** `logs/subfase-X.X-cli.log`
- **Script de Pruebas:** `tests/test-subfase-X.X.php`
- **Resultado General:** [X / X Aprobados (100%)] — ✅ APTO PARA AVANZAR

---

## 1. Matriz de Aserciones y Casos Evaluados

| # | Componente / Endpoint | Caso de Prueba | Entrada / Payload | Código Esperado | Código Obtenido | Estado |
| - | :--- | :--- | :--- | :---: | :---: | :---: |
| 1 | `App\Core\...` | Caso exitoso | `{ ... }` | 200 OK | 200 OK | ✅ PASS |
| 2 | `api/...` | Validación fallida | `{ ... }` | 422 Unprocessable | 422 Unprocessable | ✅ PASS |
| 3 | `api/...` | Sin autorización | (Sin token) | 401 Unauthorized | 401 Unauthorized | ✅ PASS |

---

## 2. Evidencia de Respuestas JSON y Cabeceras

### Caso Exitoso (200 / 201)
```json
{ ... }
```

### Caso de Error Controlado (401 / 403 / 422 / 500)
```json
{ ... }
```

---

## 3. Verificación de Integridad en SQLite
- `PRAGMA integrity_check;` -> `ok`
- `PRAGMA foreign_key_check;` -> `0 violaciones encontradas`

---

## 4. Veredicto y Siguientes Pasos
- [x] Todos los componentes unitarios instanciados sin errores.
- [x] Endpoints probados con curl devolviendo JSON puro sin fugas HTML.
- [x] Logs respaldados en `logs/subfase-X.X-cli.log`.
- **Estado:** Esperando autorización del usuario para iniciar Subfase X.Y.
```
