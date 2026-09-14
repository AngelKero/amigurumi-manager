# Protocolo de Divergencia CLI vs. HTTP (Gate 3-Tier)

[← Testing Hub](./README.md)

> **Gobernanza · Acción 3 (H-015)** · Referencia canónica anclada en `AGENTS.md §3` y en el gate de `.agents/rules/general.md`.
> Aplica a cualquier subfase (3.X, 4.X, gobernanza) cuando las verificaciones del Tier 1 (suite CLI) y Tier 2 (curl HTTP en vivo) **discrepan**.

---

## 1. Contexto

El 3-Tier Testing Gate exige que **ambas** verificaciones estén verdes. Cuando CLI y HTTP divergen, el resultado **no bloquea automáticamente**, pero tampoco se ignora:
debe completarse un triaje documentado que distinga **defecto de código** (bloquea la subfase = HALT) de **defecto de entorno de pruebas** (no bloquea, pero se registra y se re-verifica).

## 2. Matriz de Escenarios

| Escenario | CLI | HTTP | Acción |
| :--- | :---: | :---: | :--- |
| **A · Coherente** | ✅ | ✅ | Avanzar al reporte (flujo normal). |
| **B · Divergencia networking** | ✅ | ❌ | Triaje → posible defecto de entorno. Ver §4. |
| **C · Divergencia lógica** | ❌ | ✅ | Triaje → probable defecto de suite(entorno de aserciones) o de la propia verificación CLI. Ver §4. |
| **D · Doble rojo** | ❌ | ❌ | Defecto de código salvo prueba en contra. Bloquea (HALT) + re-plan aprobado. |

## 3. Definiciones

- **Defecto de código:** la subfase introduce comportamiento incorrecto frente al contrato de la API / espec (p.ej. un endpoint devuelve 500, envelope roto, CSP ausente, 429 no emitido).
- **Defecto de entorno de pruebas:** el código es correcto pero la verificación falla por el entorno de ejecución (p.ej. `php -S` no reenvía `Authorization` como Apache/FastCGI, puerto ocupado, BD regenerada entre CLI y HTTP, datos de seed divergentes, proxy/vPN).

## 4. Proceso de Triaje (6 pasos)

1. **Reproducir 2 veces idénticas.** Lanzar el mismo curl 2× consecutivas contra `localhost:8000`. Si el resultado es intermitente → sospecha de entorno (estado, timers, SQLite `busy`).
2. **Aislar la variable.** Ejecutar la suite HTTP contra un segundo proceso servidor (mismo `php -S` en otro puerto o Apache/FastCGI si está disponible). Registrar en el log HTTP del intento (marcar `ENV:`).
3. **Clasificar entorno vs código.**
   - Revisar cabeceras del servidor (`Server:`), errores de `curl_error`/`CURLOPT_` y logs del server.
   - Si la petición falla solo al atravesar un shim de entorno (burden CGI, proxy, encabezados no reenviados) y funciona en el otro entorno → **defecto de entorno / infra**.
   - Si el fallo persiste en todos los entornos → **defecto de código**.
4. **Registrar la traza.** En el reporte ejecutivo, sección "Fallos Detectados & Correcciones Quirúrgicas", con columnas: escenario (B/C), petición, canal esperado ↔ obtenido, decisión (entorno/código), evidencia.
5. **Decidir.**
   - **Enviroment-only:** la subfase puede aprobarse; quedan UN commit de evidencia en el log HTTP (`ENV:` marcado) y nota en el reporte. La regresión posterior re-ejecuta el check en el entorno canónico (Apache/FastCGI en producción).
   - **Defecto de código:** la subfase **se bloquea**. No se avanza el roadmap; se fija el defecto con una nueva subfase/commit y se re-ejecuta el gate completo antes de pedir aprobación.
6. **Halting.** Cualquier decisión C-2 (defecto de código) o escenario D activa el HALT estándar: reportar al usuario, esperar aprobación explícita. Nunca silenciar una divergencia.

## 5. Plantilla de Registro (pégala en el reporte de la subfase)

```markdown
### Triaje CLI/HTTP (protocolo `docs/testing/protocolo-divergencia-cli-http.md`)
- **Escenario:** B | C | D
- **Petición:** `METHOD /ruta` (payload/headers relevantes)
- **Esperado:** `HTTP X` · **Obtenido (CLI):** `HTTP Y` · **Obtenido (HTTP):** `HTTP Z`
- **Reproducción 2×:** idéntico / intermitente
- **Segundo entorno (puerto/pila):** resultado
- **Decisión:** defecto de código | defecto de entorno | no reproducible
- **Evidencia:** `logs/...-http.log` · **Consecuencia:** aprobada / bloqueada (HALT)
```

## 6. Mandato

- Ningún reporte puede declarar "Apto para avanzar" con una divergencia sin triaje documentado (§4 p.4).
- La decisión de "entorno" exige re-verificación en el entorno canónico en la propia regresión (sección 6 de `test-subfase-3.6.5.php` o `test-fase-4-acumulado.php`).