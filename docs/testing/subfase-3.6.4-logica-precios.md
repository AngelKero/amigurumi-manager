# Reporte de Pruebas: Subfase 3.6.4 — Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)

[← Volver al Índice de Testing](./README.md) • [Ver Especificación de Seguridad](../architecture/subfase-3.6-auditoria-seguridad.md) • [Ver Plan Maestro](../architecture/phase-3-plan.md)

- **Fecha de Ejecución:** 2026-09-12 18:00:07
- **Responsable:** Antigravity AI Engine (Clean Architecture & Algodón Nórdico Standards)
- **Entorno:** PHP 8.3.29 CLI + Servidor PHP Built-in (`http://localhost:8000`) + SQLite 3.x
- **Script de Pruebas:** [`tests/test-subfase-3.6.4.php`](../../tests/test-subfase-3.6.4.php)
- **Archivos de Logs:**
  - Consola CLI: [`logs/subfase-3.6.4-cli.log`](../../logs/subfase-3.6.4-cli.log)
  - Trazas HTTP: [`logs/subfase-3.6.4-http.log`](../../logs/subfase-3.6.4-http.log)
- **Resultado General:** **151 / 151 Aserciones Aprobadas (100% OK en 188.27 ms)** — ✅ **APROBADO SIN RESERVAS**

---

## 1. Resumen Ejecutivo de la Auditoría

La **Subfase 3.6.4** constituye la auditoría profunda de la lógica de negocio, diseño seguro (OWASP A04:2021 - Insecure Design), integridad transaccional de inventario, exactitud financiera en punto flotante y resiliencia UTF-8 multibyte para la plataforma **Crochet Manager**.

```
================================================================================
  SUITE DE PRUEBAS: Subfase 3.6.4: Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)
  Iniciada: 2026-09-12 18:00:07 | PHP 8.3.29 | OS: Darwin
================================================================================

► SECCIÓN: 1. Cálculo & Congelamiento de Precios en el Servidor (OWASP A04:2021)
  ✔ 24 aserciones aprobadas (precios autoritativos en servidor, CurrencyHelper, márgenes)

► SECCIÓN: 2. Aislamiento de Concurrencia, Stock Atómico & Restricciones CHECK
  ✔ 23 aserciones aprobadas (BEGIN IMMEDIATE, deducción atómica, CHECK stock >= 0, encargo)

► SECCIÓN: 3. Cancelación Idempotente & Restitución Transaccional (ADR-009)
  ✔ 22 aserciones aprobadas (restitución exacta, idempotencia 409, ciclo de vida de pedidos)

► SECCIÓN: 4. Resiliencia UTF-8 4-Byte & Caracteres Multibyte
  ✔ 22 aserciones aprobadas (emojis 🧶🧸, caracteres nórdicos ÅØ, katakana, mb_strlen)

► SECCIÓN: 5. Límites Numéricos y Casos Extremos (Boundary Testing)
  ✔ 30 aserciones aprobadas (precios, costos, stock, horas, longitudes de texto)

► SECCIÓN: 6. Pruebas de Integración HTTP en Vivo contra Servidor Local
  ✔ 30 aserciones aprobadas (solicitudes REST, 409 stock, 409 idempotencia, aislamiento)

================================================================================
  RESUMEN DE PRUEBAS: Subfase 3.6.4: Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)
--------------------------------------------------------------------------------
  Total Aserciones: 151
  Exitosas:         151
  Fallidas:         0
  Tiempo Total:     188.27 ms
================================================================================
  ✔ TODAS LAS PRUEBAS PASARON EXITOSAMENTE (100% OK)
```

---

## 2. Matriz Consolidada de Aserciones

| Dimensión Auditada | Estándar / Dominio | Casos Evaluados | Aserciones | Estado |
| :--- | :--- | :--- | :---: | :---: |
| **1. Cálculo y Congelamiento de Precios** | OWASP A04:2021 (Insecure Design) | Inmunidad contra manipulación de precios en cliente, cálculo autoritativo en servidor, exactitud matemática en centavos enteros sin errores flotantes, márgenes de utilidad y retornos por hora. | 24 | ✅ Aprobado |
| **2. Stock Atómico & Concurrencia** | Transacciones ACID & DDL CHECK | `BEGIN IMMEDIATE TRANSACTION`, deducción exacta, bloqueo HTTP 409 ante stock físico agotado, inmunidad relacional contra stock negativo (`chk_creaciones_cantidad_stock`), piezas bajo encargo sin decremento físico. | 23 | ✅ Aprobado |
| **3. Cancelación Idempotente & Restitución** | ADR-009 (Idempotencia en Pedidos) | Restitución exacta de inventario a `creaciones.cantidad_stock`, prevención estricta de inventario fantasma (segunda cancelación responde HTTP 409), delegación automática al cambiar estado a `'Cancelado'`. | 22 | ✅ Aprobado |
| **4. Resiliencia UTF-8 Multibyte** | Unicode 4-Byte (RFC 3629) | Persistencia, lectura y búsqueda reactiva con emojis 4-byte (`🧶`, `🧸`, `✨`, `🌸`, `🐉`, `🐱`), caracteres nórdicos (`Å`, `Ø`, `ä`, `ö`), katakana japonés (`アミグルミ`), y codificación segura en WhatsApp. | 22 | ✅ Aprobado |
| **5. Límites Numéricos & Casos Extremos** | Boundary Value Analysis (BVA) | Pruebas de valores frontera en pedidos (cantidad 1 a 1000, nombres 2 a 100, notas 1000) y creaciones (precio 1 a 9,999,999 centavos, horas 0.0 a 500.0, dimensiones 2 a 100, materiales 3 a 80). | 30 | ✅ Aprobado |
| **6. Integración HTTP contra Servidor Local** | REST Endpoints en Vivo (`localhost:8000`) | Pruebas curl en vivo para inyección de precio en checkout (ignorado), stock insuficiente (HTTP 409), validaciones (HTTP 422), cancelación idempotente (HTTP 409), búsqueda por emoji y aislamiento multi-artesano. | 30 | ✅ Aprobado |
| **TOTAL SUBFASE 3.6.4** | **Auditoría Integral de Lógica & Negocio** | **151 Aserciones de Seguridad y Resiliencia** | **151 / 151** | ✅ **100% OK** |

---

## 3. Desglose Detallado por Dimensión de Seguridad

### 3.1 Cálculo y Congelamiento de Precios en el Servidor (OWASP A04:2021)
- **Vulnerabilidad Evaluada:** Insecure Design / Client-Side Price Tampering. En arquitecturas vulnerables, el cliente envía el precio total o el subtotal del pedido, permitiendo a un atacante pagar $1.00 por un artículo de $500.00.
- **Mecanismo de Defensa:**
  1. En `PedidoService::requestPublicOrder()` y `PedidoRepository::createAtomic()`, el servidor **ignora por completo** cualquier campo `precio`, `precio_unitario` o `precio_final` recibido en el payload JSON.
  2. El servidor consulta el precio oficial de la pieza directamente en la base de datos relacional SQLite bajo bloqueo transaccional (`SELECT precio FROM creaciones WHERE id = :id`).
  3. El servidor calcula autoritativamente `precio_final = creacion.precio * cantidad` y congela dicho valor en la columna `pedidos.precio_final`.
- **Exactitud Financiera sin Errores Flotantes:**
  - Todos los montos se gestionan y almacenan como enteros en **centavos** (`45000 = $450.00 MXN`).
  - Se probó la clásica trampa binaria de IEEE 754 ($0.10 + 0.20 = 0.30000000000000004$); en centavos enteros, `10 + 20 === 30` ($0.30 MXN) de forma exacta y determinista.
  - Se verificó la robustez de `CurrencyHelper::enrichCreation()` ante casos límite: margen cero cuando precio = costo, margen negativo cuando costo > precio, y división por cero prevenida cuando horas de tejido = 0.0 o precio = 0.

### 3.2 Aislamiento de Concurrencia & Stock Atómico
- **Vulnerabilidad Evaluada:** Race Conditions / Overselling / Inventario Negativo.
- **Mecanismo de Defensa:**
  1. `PedidoRepository::createAtomic()` opera dentro de una transacción `BEGIN IMMEDIATE TRANSACTION`, adquiriendo un bloqueo de escritura reservado en SQLite que impide lecturas sucias y condiciones de carrera concurrentes.
  2. Si `cantidad_stock < cantidad`, se aborta inmediatamente arrojando `RuntimeException` con código HTTP 409 Conflict y mensaje orientador, evitando la inserción del pedido.
  3. **Salvaguarda de Base de Datos Relacional:** La restricción DDL `chk_creaciones_cantidad_stock CHECK(cantidad_stock >= 0 AND cantidad_stock <= 10000)` garantiza a nivel del motor de base de datos que jamás pueda existir stock físico negativo ni superior a 10,000 unidades.
  4. **Creaciones Bajo Encargo (`es_sobre_encargo = 1`):** Se validó que las piezas etiquetadas como comisión exclusiva permiten pedidos con stock físico en 0 sin decrementar existencias ni arrojar errores de stock insuficiente.

### 3.3 Cancelación Idempotente & Restitución Transaccional (ADR-009)
- **Vulnerabilidad Evaluada:** Double-Spend / Phantom Inventory Duplication. Si un artesano o atacante invoca el endpoint de cancelación múltiples veces, un sistema defectuoso podría restituir unidades repetidamente, inflando artificialmente el inventario.
- **Mecanismo de Defensa (ADR-009):**
  1. `PedidoRepository::cancelOrderAtomic()` inspecciona el estado actual del pedido. Si ya se encuentra en `estado_pedido = 'Cancelado'`, la transacción aborta inmediatamente con HTTP 409 Conflict (`"El pedido #X ya se encuentra cancelado."`).
  2. Se demostró empíricamente que tras cancelar un pedido de 4 unidades sobre un stock de 6, el stock regresó exactamente a 10; un segundo intento consecutivo de cancelación fue rechazado con HTTP 409 y el stock permaneció inalterado en 10 (sin duplicarse a 14).
  3. Se probó la delegación automática en `updateOrderStatus()`: cambiar el estado de un pedido a `'Cancelado'` delega internamente a `cancelOrder()` para restituir las existencias automáticamente.

### 3.4 Resiliencia UTF-8 4-Byte & Caracteres Multibyte
- **Vulnerabilidad Evaluada:** Truncamiento de cadenas, errores de codificación JSON (`Malformed UTF-8 characters`), y rechazos prematuros de validación de longitud.
- **Mecanismo de Defensa:**
  1. Se probó la inserción y recuperación exitosa de emojis que ocupan 4 bytes por punto de código (`🧸`, `🧶`, `✨`, `🌸`, `🐉`, `🐱`).
  2. Se validó la convivencia de alfabetos mixtos en la misma ficha técnica: español con acentos y diéresis, caracteres nórdicos (`Åse Øyvindson • Häkeln Taller 🧵`) y katakana japonés (`アミグルミ`).
  3. Se verificó que todas las validaciones de longitud emplean `mb_strlen($str, 'UTF-8')`. Para el emoji `🧸`, `strlen()` reporta 4 bytes, mientras que `mb_strlen()` reporta exactamente 1 carácter, evitando el rechazo prematuro de descripciones o nombres con iconografía textil.
  4. Los enlaces directos de WhatsApp generados con `rawurlencode()` escapan apropiadamente los caracteres multibyte y emojis en los parámetros de consulta `?text=...`.

### 3.5 Límites Numéricos & Boundary Value Analysis (BVA)
Se sometieron los repositorios y servicios a una batería exhaustiva de valores frontera:
- **Pedidos:**
  - `cantidad = 0`: Rechazado con HTTP 422.
  - `cantidad = -5`: Rechazado con HTTP 422.
  - `cantidad = 1`: Válido (límite inferior).
  - `cantidad = 1000`: Válido (límite superior).
  - `cantidad = 1001`: Rechazado con HTTP 422.
  - `cliente_nombre`: 1 carácter rechazado (mínimo 2), 100 caracteres aceptado, 101 caracteres rechazado.
  - `notas`: 1000 caracteres aceptado, 1001 caracteres rechazado.
- **Creaciones:**
  - `precio`: 0 centavos rechazado, 1 centavo aceptado ($0.01 MXN), 9,999,999 centavos aceptado ($99,999.99 MXN), 10,000,000 centavos rechazado.
  - `costo_materiales`: -1 rechazado, 0 aceptado, 9,999,999 aceptado, 10,000,000 rechazado.
  - `horas_tejido`: -0.5 rechazado, 0.0 aceptado, 500.0 aceptado, 500.1 rechazado.
  - `cantidad_stock`: -1 rechazado, 0 aceptado, 10,000 aceptado, 10,001 rechazado.
  - `dimensiones`: 100 caracteres aceptado, 101 caracteres rechazado.
  - `material`: 80 caracteres aceptado, 81 caracteres rechazado.
  - `categoria`: 50 caracteres aceptado, 51 caracteres rechazado.
  - `descripcion`: 2,000 caracteres aceptado, 2,001 caracteres rechazado.

### 3.6 Integración HTTP en Vivo contra Servidor Local
Se ejecutaron 11 trazas HTTP curl en vivo contra `http://localhost:8000`:
1. `POST /api/pedidos/solicitar.php` con manipulación de precio: responde HTTP 201 Created y retorna `precio_final: 64000` (`$640.00 MXN`), ignorando los campos fraudulentos del cliente.
2. `POST /api/pedidos/solicitar.php` solicitando 20 unidades de una pieza con stock 12: responde HTTP 409 Conflict con mensaje `"Stock insuficiente para entrega inmediata"`.
3. `POST /api/pedidos/solicitar.php` con cantidad 0: responde HTTP 422 Unprocessable Entity.
4. `POST /api/pedidos/solicitar.php` con cliente y notas conteniendo emojis 4-byte: responde HTTP 201 Created con cuerpo JSON limpio sin entidades corruptas.
5. `POST /api/pedidos/cambiar-estado.php` cambiando a `'En Proceso'`: responde HTTP 200 OK.
6. `POST /api/pedidos/cambiar-estado.php` con estado inválido: responde HTTP 422.
7. `POST /api/pedidos/cancelar.php`: responde HTTP 200 OK y confirma la restitución de existencias al inventario.
8. `POST /api/pedidos/cancelar.php` (re-intento sobre el mismo pedido): responde HTTP 409 Conflict demostrando **idempotencia estricta**.
9. `POST /api/creaciones/crear.php` con título `"Gatito Nórdico 🐱🧶"`: responde HTTP 201 Created.
10. `GET /api/creaciones/index.php?busqueda=🐱`: responde HTTP 200 OK y localiza la pieza en el catálogo mediante búsqueda por emoji.
11. `GET /api/pedidos/index.php` con token Bearer de `artesana_ana`: responde HTTP 200 OK y demuestra que el 100% de los pedidos retornados corresponden exclusivamente a piezas de su autoría (aislamiento multi-artesano).

---

## 4. Trazas Crudas de Ejecución

### Extracto de Log CLI (`logs/subfase-3.6.4-cli.log`)
```
================================================================================
  SUITE DE PRUEBAS: Subfase 3.6.4: Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)
  Iniciada: 2026-09-12 18:00:07 | PHP 8.3.29 | OS: Darwin
================================================================================

► SECCIÓN: 1. Cálculo & Congelamiento de Precios en el Servidor (OWASP A04:2021)
──────────────────────────────────────────────────────────────────────
  ✔ PASS: Pedido público registrado con éxito
  ✔ PASS: precio_final fue calculado por el servidor (45000 * 2 = 90000 centavos)
  ✔ PASS: precio_final_formateado refleja cálculo real del servidor
  ✔ PASS: El pedido existe en la base de datos
  ✔ PASS: El precio_final almacenado en SQLite es exactamente 90000 centavos
  ...
► SECCIÓN: 6. Pruebas de Integración HTTP en Vivo contra Servidor Local
──────────────────────────────────────────────────────────────────────
  ✔ PASS: POST /api/pedidos/solicitar.php responde 201 Created
  ✔ PASS: Respuesta indica exito = true
  ✔ PASS: ID de pedido generado > 0
  ✔ PASS: HTTP: Servidor ignora manipulación y calcula 32000 * 2 = 64000 centavos
  ✔ PASS: HTTP: precio_final_formateado es $640.00 MXN
  ✔ PASS: POST /api/pedidos/solicitar.php con exceso de stock responde HTTP 409 Conflict
  ✔ PASS: Mensaje HTTP informa stock insuficiente
  ✔ PASS: POST /api/pedidos/solicitar.php con cantidad 0 responde HTTP 422
  ✔ PASS: POST /api/pedidos/solicitar.php con emojis 4-byte responde HTTP 201 Created
  ✔ PASS: Pedido con emojis registrado con ID válido
  ✔ PASS: POST /api/pedidos/cambiar-estado.php responde HTTP 200 OK
  ✔ PASS: Estado de pedido actualizado a En Proceso
  ✔ PASS: POST /api/pedidos/cambiar-estado.php con estado inválido responde HTTP 422
  ✔ PASS: POST /api/pedidos/cancelar.php responde HTTP 200 OK
  ✔ PASS: HTTP: Se restituyeron exactamente 2 unidades al stock
  ✔ PASS: HTTP: Re-intento de cancelación responde HTTP 409 Conflict (idempotencia)
  ✔ PASS: Mensaje confirma que el pedido ya estaba cancelado
  ✔ PASS: POST /api/creaciones/crear.php con emojis responde HTTP 201 Created
  ✔ PASS: Nueva creación con emojis creada
  ✔ PASS: GET /api/creaciones/index.php con emoji responde HTTP 200 OK
  ✔ PASS: Búsqueda HTTP por emoji 🐱 retorna al menos 1 resultado
  ✔ PASS: GET /api/pedidos/index.php con token artesana responde HTTP 200 OK
  ✔ PASS: Aislamiento estricto: el 100% de los pedidos listados pertenecen a piezas de la artesana autenticada

================================================================================
  RESUMEN DE PRUEBAS: Subfase 3.6.4: Lógica de Negocio, Precios, Stock Atómico & Casos Límite Multibyte (OWASP A04:2021)
--------------------------------------------------------------------------------
  Total Aserciones: 151
  Exitosas:         151
  Fallidas:         0
  Tiempo Total:     188.27 ms
================================================================================
  ✔ TODAS LAS PRUEBAS PASARON EXITOSAMENTE (100% OK)
```

---

## 5. Estado Acumulado Global de la Fase 3

| Subfase | Nombre del Dominio | Aserciones | Estado |
| :---: | :--- | :---: | :---: |
| **3.1** | Infraestructura Nuclear & Core | 93 / 93 | ✅ Aprobado |
| **3.2** | Autenticación Stateless & Bearer | 69 / 69 | ✅ Aprobado |
| **3.3** | Usuarios, Roles RBAC & Bajas Lógicas | 105 / 105 | ✅ Aprobado |
| **3.4** | Catálogo, Creaciones & Ciclo de Imágenes | 126 / 126 | ✅ Aprobado |
| **3.5** | Pedidos & Transacciones Atómicas | 139 / 139 | ✅ Aprobado |
| **3.6.1** | Acceso, Autorización, IDOR & RBAC (OWASP A01) | 165 / 165 | ✅ Aprobado |
| **3.6.2** | Criptografía, Auth & Datos Sensibles (OWASP A02+A07) | 161 / 161 | ✅ Aprobado |
| **3.6.3** | Inyección, Sanitización & Medios (OWASP A03+A08) | 157 / 157 | ✅ Aprobado |
| **3.6.4** | Lógica de Negocio, Precios & Multibyte (OWASP A04) | 151 / 151 | ✅ Aprobado |
| **TOTAL ACUMULADO** | **Backend Clean Architecture & Seguridad** | **1,166 / 1,166 (100% OK)** | 🟢 **IMPECABLE** |

---

## 6. Conclusión y Compás de Espera Inviolable (Gate Sign-Off)

La **Subfase 3.6.4** ha cumplido con el 100% de sus objetivos de diseño seguro (OWASP A04:2021), exactitud financiera en punto flotante, transaccionalidad atómica y resiliencia UTF-8 multibyte.

En apego estricto a las directrices de `.agents/rules/general.md` y el protocolo de desarrollo iterativo:
- **Se detiene completamente la ejecución de herramientas.**
- **Queda prohibido escribir código o adelantar tareas para la Subfase 3.6.5** (Rendimiento SQLite con `EXPLAIN QUERY PLAN`, Erradicación N+1, `busy_timeout` y Suite de Regresión Global Acumulada) hasta recibir la instrucción explícita y por escrito del usuario.
