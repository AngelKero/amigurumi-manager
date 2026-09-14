# Auditoría de Gobernanza — Crochet Manager

**Auditor:** Auditor senior de gobernanza (sesgo: adversarial, no confirmatorio)
**Fecha:** 14/09/2026 · **Método:** lectura de reglas + verificación física contra código y estructura real

## Resumen Ejecutivo

La gobernanza de **Crochet Manager** es, en su núcleo, notablemente sofisticada para un proyecto de este tamaño: tiene ADRs numerados y fechados, invariantes de seguridad bien diseñados (borrado lógico, IDOR multi-artesano, centavos enteros, transacciones atómicas, lockout ID #1), un protocolo de testing en 3 niveles con halting gate, y un sistema SDD con `spec/plan/tasks` por feature. La separación física `app/` / `api/` / `src/` es **real y verificada**: 0 archivos PHP en `src/`, 25 controladores, todos ≤60 líneas. Los tokens CSS declarados en las reglas existen en `variables.css` y los contrastes WCAG nominales se sostienen. Esto es un framework vivo y mayormente honesto con su propia realidad.

Sin embargo, el sistema muestra **fracturas de credibilidad en su documentación canónica**. La contradicción más grave es tripartita: AGENTS.md declara `spec/` como "the single, absolute source of truth", `.agents/rules/docs-source-of-truth.md` obliga a que `docs/` (y el código) manden, y `docs/architecture/proceso-desarrollo-fases.md` y ADR-011 todavía ordenan "sincronizar el Memory Bank" — una carpeta que **fue eliminada del repositorio**. Un agente instruido a seguir las tres fuentes no puede obedecer sin violar otra. Junto a esto, los invariantes de seguridad están numerados de forma divergente (9 en AGENTS.md, 6 en tech-stack.md), y las cifras de aserciones no cuadran (`tech-stack.md` dice 141+1.146; el resto del repositorio dice 1.307).

El tallón de Aquiles en seguridad es el ciclo de vida del token Bearer: no existe política de revocación (logout es un no-op server-side, el token sigue válido 24h), no hay rotación de secretos HMAC, no hay rate-limiting ni lockout ante fuerza bruta (confirmado: la matriz RBAC de `docs/architecture/security.md` indica "Ninguno" como mecanismo para `login.php`), y la feature 004 planea almacenar el token en `localStorage` mientras existen 36 usos de `innerHTML` en `src/js/` y **cero política CSP** en el código o las reglas. La fecha más urgente es que la feature activa (004) está a punto de materializar esta brecha.

El framework asume un modelo de **un solo agente serial**. No hay reglas para trabajo paralelo, resolución de merge-conflicts, rollback de features defectuosas, política de dependencias futuras, onbboarding de desarrolladores humanos, ni protocolo para la divergencia "CLI verde / HTTP rojo". Y mantiene duplicación masiva de reglas (dos `general.md` casi byte-idénticos, el invariante de soft-delete en 6 archivos, las heurísticas de diseño en 2+ fuentes) con numeración duplicada (`[CR-2]`, `[QW-2]` aparecen dos veces cada uno). El promedio de robustez es **5.1/10**: ejecución disciplinada, gobernanza de largo plazo frágil.

---

## Tabla de Hallazgos

| ID | Dim | Sev | Descripción | Archivos afectados | Recomendación |
|----|-----|-----|-------------|--------------------|----------------|
| H-001 | 1 | 🔴 | **Tri-fuente de verdad conflictiva.** `spec/` se declara fuente única absoluta (AGENTS.md:150), `docs/` + código mandan en docs-source-of-truth.md:30, y ADR-011 + proceso-desarrollo-fases ordenan sincronizar el "Memory Bank" que ya no existe (carpeta `memory-bank/` no existe en el repo). Ningún archivo define precedencia entre reglas vs. spec vs. docs | `AGENTS.md:150`, `.agents/rules/docs-source-of-truth.md:30`, `docs/architecture/decisiones/ADR-011…:22`, `docs/architecture/proceso-desarrollo-fases.md:201,283` | Definir precedencia explícita (p.ej. reglas > spec.md > ADR > docs > código, o la inversa para desincronizaciones) y purgar todas las referencias a Memory Bank |
| H-002 | 5 | 🔴 | **Token Bearer sin revocación ni rotación.** Logout es no-op (`Response::success` sin invalidar), el token vive 24h aunque el usuario "cierre sesión". No existe denylist, rotación de secreto HMAC, ni política documentada. ADR-002 vende "invalidación inmediata" pero solo aplica a cuentas desactivadas, no a logout | `api/auth/logout.php`, `app/Core/TokenManager.php`, `ADR-002…:30`, `docs/architecture/security.md` | Documentar la debilidad en ADR-002, evaluar short-TTL + refresh, o denylist por `jti`; añadir regla de rotación de secreto |
| H-003 | 5 | 🔴 | **Fuerza bruta sin mitigación ni regla.** `login.php` no cuenta con rate-limit/lockout (grep: 0 coincidencias en AuthService/UsuarioRepository) y la matriz RBAC declara mecanismo "Ninguno". Ningún invariante exige throttling | `docs/architecture/security.md` (matriz), `app/Services/AuthService.php`, `AGENTS.md §5` | Añadir invariante de login hardening (delay exponencial/lockout por username+IP con `uptime`) y regla OWASP para endpoints públicos no-autenticados |
| H-004 | 5 | 🔴 | **XSS vs. token en `localStorage` sin política CSP.** Feature 004 (en curso) guardará `crochet_auth_token` en localStorage; hay 36 usos de `innerHTML` en `src/js/`, solo 6/24 vistas usan `htmlspecialchars`, y no existe `Content-Security-Policy` ni en `views/layouts/main.php` ni en `.htaccess`. El plan de 004 "mitiga" el XSS por decreto ("escape riguroso en vistas") sin mecanismo verificable | `spec/features/004/spec.md:7,17`, `plan.md:24,29`, `src/js/**` (36×innerHTML), `views/layouts/main.php` | Requerir CSP (script-src self + nonce) y auditoría de sanitización como prerrequisito de 004, o migrar el plan a cookie httpOnly de doble envío |
| H-005 | 1 | 🟠 | **Numeración divergente de invariantes.** AGENTS.md §5 lista 9 invariantes (#3=garantías plataforma, #4=IDOR); tech-stack.md "Límites Duros" lista 6 con #3=IDOR y #4=raíz ID1. Referenciar "invariante #3" tiene dos significados distintos | `AGENTS.md:121-144`, `spec/constitution/tech-stack.md:61-68` | Unificar en una única tabla canónica numerada (R-01…R-09) citada por clave semántica, no por número posicional |
| H-006 | 1 | 🟠 | **Cifras de aserciones incompatibles.** tech-stack.md:29 afirma "141 directas, 1.146 acumuladas" (=1.287); AGENTS.md:69, rule/general.md:32, roadmap.md:7 y docs/testing/README.md:22 afirman 1.307 (93+69+105+126+139+165+161+157+151+141=1.307). La cifra "1.146" no cuadra con el cumulative 1.166 previo a 3.6.5 | `spec/constitution/tech-stack.md:29` vs `AGENTS.md:69`, `docs/testing/README.md:22` | Corregir tech-stack.md con la cifra verificada y convertir toda cifra en comando regenerable (contar asserts en runtime) |
| H-007 | 1 | 🟠 | **Identidad de producto inconsistente.** Las reglas y el código mezclan "Amigurumi Micro-ERP" (ui-ux-design-system.md:7, `.gitignore:2`, `src/js/main.js:1`, variables.css:44 `Fredoka`) con "Crochet Manager" (mission, AGENTS, docs). Un agente puede generar decisiones/marca para el nombre equivocado | `.agents/rules/ui-ux-design-system.md:7`, `.gitignore:2`, `src/js/main.js:1`, `spec/constitution/mission.md` | Decidir una identidad única y reemplazar en reglas, comentarios y gitignore |
| H-008 | 8 | 🟠 | **tasks.md de 004 ignora el gate de testing de 3 niveles.** El invariante (AGENTS.md:72-79, general.md:33-40) exige por subfase: suite CLI + curl HTTP + reporte `docs/testing/`. tasks.md de 004 solo lista "Validar flujo en navegador" y no menciona crear `tests/test-subfase-4.1.php`, `logs/`, ni reporte `docs/`. Fase 4 arranca sin blindaje | `spec/features/004/tasks.md:1-11`, `AGENTS.md:72-79` | Añadir tareas de suite CLI, logs HTTP y reporte ejecutivo a tasks.md antes de codificar 004 |
| H-009 | 1 | 🟠 | **AGENTS.md omite el paso HTTP del gate 3-tier.** general.md ordena "Perform HTTP curl checks" como paso 2; AGENTS.md §3 (pasos 1-6) no lo incluye. Un agente que siga solo AGENTS.md salta la comprobación HTTP | `AGENTS.md:72-79` vs `.agents/rules/general.md:36` | Sincronizar la lista de pasos del gate en ambos (o centralizar en un solo archivo) |
| H-010 | 6 | 🟠 | **Regla de "erradicación" de Bootstrap blue ambigua y en conflicto con la práctica.** ui-ux-design-system.md:113-114 prohíbe las **clases** `text-primary`/`border-primary`; AGENTS.md:104 prohíbe el **valor** `#0d6efd`. El código usa decenas de `text-primary`/`border-primary` (p.ej. `views/components/panel_sidebar.php:33,41`, `modal_cancel_order.php:19`, `creaciones_content.php:321`), neutralizados por override de `--bs-primary` en variables.css:48. Dos agentes auditores llegarían a veredictos opuestos | `.agents/rules/ui-ux-design-system.md:113-114`, `AGENTS.md:104`, `src/css/01-settings/variables.css:48`, `views/**/*.php` | Reescribir la regla como "valor resultante ≠ #0d6efd" (mecanismo: tokens) o prohibir literalmente las clases y refactorizar 25+ usos; eliminar el doble criterio |
| H-011 | 6 | 🟡 | **2 de las 21 reglas de craft detailing son fantasmas.** `.table-artisan-team` y `.avatar-artisan-initials` (regla §5.16) no existen en `src/css/` (0 archivos) ni se usan en `views/` (0 coincidencias). Regla irrealizable que un agente no puede ni cumplir ni verificar | `.agents/rules/ui-ux-design-system.md:123-124` | Implementarlas en usuarios.php o deprecar la regla; marcar reglas como "implantada"/"pendiente" |
| H-012 | 6 | 🟡 | **IDs de reglas duplicados.** `[CR-2]` aparece 2 veces (§4.2 y §4.3) y `[QW-2]` 2 veces (§4.5 y §4.6), replicado en `docs/design-system/audits.md`. Citar "regla CR-2" es ambiguo | `.agents/rules/ui-ux-design-system.md:66,68,73,75`, `docs/design-system/audits.md:3-5` | Renumerar (CR-1…CR-4, QW-1…QW-6) con lista de reglas en una tabla sumaria |
| H-013 | 7 | 🟡 | **`docs/README.md` desincronizado (violación commiteada del Cierre Documental).** El índice maestro no lista las subfases 3.6.2–3.6.5 ni las auditorías context7 en el Hub de Testing (líneas 17 y 42 solo llegan a 3.6.1), pese a que `docs-source-of-truth.md:70` lo exige. La regla de oro ya se incumplió una vez y quedó en Git (working tree limpio) | `docs/README.md:17,42`, `.agents/rules/docs-source-of-truth.md:70` | Regenerar el índice y añadir verificación automatizada de "cada ×.md en docs/ está enlazado en su README" |
| H-014 | 2 | 🟠 | **Sin política de rollback.** Si una subfase aprobada resulta defectuosa o introduce regresión, no hay regla de revert, hotfix o re-apertura de feature. El agente no sabe si es dueño de corregirla o debe abrir ADR | `AGENTS.md`, `.agents/workflows/sdd-feature.md` | Definir "ciclo de corrección post-aprobación": revert → diagnóstico → nueva subfase con gate, sin re-abrir el flujo completo |
| H-015 | 2 | 🟠 | **Sin protocolo de divergencia CLI vs. HTTP.** El gate exige ambas, pero no define qué hacer si CLI pasa y HTTP falla (o viceversa): ¿bloquea la subfase? ¿culpa al entorno (`php -S` vs Apache)? | `AGENTS.md:72-79`, `.agents/rules/general.md:36`, `docs/testing/README.md:76-88` | Definir triaje: reproducción HTTP en entorno Apache/FastCGI, decisión de "entorno vs. código", y criterio de halting |
| H-016 | 2 | 🟠 | **Modelo de agente único; paralelismo y merge sin cubrir.** Nada regula dos agentes en subfases paralelas, conflictos de merge, ni jerarquía de archivos "owned" por subfase. Es el riesgo #1 al migrar a multi-agente | `AGENTS.md`, `.agents/workflows/*.md` | Establecer regla de exclusión mutua por subfase y "un agente escribe, otro revisa"; git-add de archivos por subfase para acotar merge |
| H-017 | 2 | 🟡 | **Sin política de dependencias futuras.** Solo se dice "no Composer, no Node" (tech-stack:5, AGENTS.md:32). No hay criterio de decisión cuando el proyecto legitime una dependencia (¿qué umbral de dolor la justifica? ¿ADR obligatorio?) | `spec/constitution/tech-stack.md:5`, `AGENTS.md:32` | Añadir gate formal: "cualquier dependencia externa nueva exige ADR y aprobación del usuario" |
| H-018 | 2 | 🟡 | **Test irreparable en subfase sin protocolo de escalado.** El gate exige 100% verde o documentar el fallo en "Fallos Detectados", pero no cubre qué significa que una suite no pueda quedar verde en la subfase: ¿se bloquea, se escinde en sub-subfase, se pausa el roadmap? | `AGENTS.md:72-79`, `.agents/rules/general.md:38` | Definir bloqueo formal: la subfase no avanza sin fix o re-plan aprobada explícitamente por el usuario |
| H-019 | 4 | 🟡 | **Escapatoria de testing sin límite.** La cláusula "si el test falló o fue adaptado durante Red-Green-Refactor" (general.md:38) permite relajar aserciones como trayectoria de escape, sin límite de cuántas aserciones pueden debilitarse ni un check de "misma aserción, misma condición". El 100% verde no garantiza ausencia de regresión | `AGENTS.md:77`, `.agents/rules/general.md:38` | Requerir que toda adaptación de aserciones se audite como "cambio de contrato" y que el reporte binde diff de aserciones |
| H-020 | 4 | 🟡 | **Sin mandato de regresión cross-feature más allá de 3.6.5.** La suite acumulada existe solo para Fase 3 (test-subfase-3.6.5). No hay regla que obligue a re-ejecutar subfases anteriores al tocar features 004-008 | `AGENTS.md:68-69`, `docs/testing/README.md:20` | Crear suite de regresión acumulada por fase nueva (p.ej. `test-fase-4-acumulado.php`) y declararlo obligatorio |
| H-021 | 3 | 🟡 | **Umbral de "60 líneas" sin definir la métrica.** ¿Líneas físicas, incluidos comentarios y blanks, o lógicas? Ambiguo para dos agentes; hoy `api/creaciones/actualizar.php` está en el límite exacto (60) | `AGENTS.md:14,88`, `spec/constitution/tech-stack.md:18` | Definir "≤60 líneas físicas sin contar blanks" y automatizar el check en la suite (ya parcial en 3.6.5) |
| H-022 | 7 | 🟡 | **Sin política de deprecación/archivo de docs.** `docs/archive/` existe sin reglas; el workflow `ui-ux-audit.md` referencia rutas muertas (`index.html`, `css/styles.css`, `js/app.js`, `.docs/ui-ux-skill-report.md`) que no existen (la realidad: `index.php`, `src/css/styles.css`, `src/js/main.js`). Nada instruye a marcarlo obsoleto | `.agents/workflows/ui-ux-audit.md:10-11,22`, raíz del repo | Corregir o deprecar el workflow; crear política de "docs.md → archivo + enlace en histórico" |
| H-023 | 9 | 🟠 | **Duplicación literal de reglas con doble mantenimiento.** `.agents/rules/general.md` y `.agents/workflows/general.md` difieren solo en frontmatter/tiempos verbales (diff: cambios menores). El invariante de soft-delete vive en 6 fuentes (AGENTS.md §5.1, rules/general, workflows/general, tech-stack #1, docs/api/README §8, ADR-004) con redacción divergente | `.agents/rules/general.md`, `.agents/workflows/general.md`, `AGENTS.md`, `spec/constitution/tech-stack.md`, `docs/api/README.md:129-137`, `ADR-004` | Fusionar en un único `reglas/invariantes.md` canónico + referencias por ancla; convertir workflows en glue referencing reglas |
| H-024 | 9 | 🟡 | **Sobrecarga cognitiva alta.** Un agente debe operar: AGENTS.md (7 secciones), 3 archivos de rules, 3 workflows, 15 ADRs, ~10 docs de dominio, spec por feature, 6 skills. Riesgo de reglas contradictoras seguidas parcialmente por límites de contexto | todo el framework | Crear un índice de reglas "top-20" priorizado y fan-out por dominio (seguridad / UI / docs) para no saturar el contexto |
| H-025 | 10 | 🟠 | **Escalabilidad multi-rol/multi-artesano no planeada.** Las reglas asumen 3 tablas/25 endpoints/3 roles. Tech-stack ya documenta inventario "0-10000" y `roles` como lista fija; no hay guía para añadir tablas, roles o el arte de onbboarding de un nuevo desarrollador humano | `spec/constitution/tech-stack.md:33-37`, `AGENTS.md` | Añadir sección "Operar el framework: incorporar humano/agente nuevo" (checklist de lectura mínima + commits + gates) |
| H-026 | 8 | 🟢 | **`spec/README.md` quedó como plantilla genérica sin personalizar** (instrucciones "copia esta carpeta a tu proyecto", placeholders `<…>`) — contradice el status de fuente canónica | `spec/README.md:1-6,22` | Reescribirlo como documento de gobierno propio (índice de features + convenciones 004+), no plantilla |
| H-027 | 7 | 🟢 | **Árbol estructural incompleto** en AGENTS.md §1: omite `assets/` (referenciado en el invariante de uploads), `scripts/`, `skills-lock.json` y `piezas.php` | `AGENTS.md:12-23` | Complementar inventario del directorio raíz |

---

## Contradicciones Detectadas (explícitas, archivo:línea)

1. **Fuente de verdad tri-confilictiva:** `AGENTS.md:150` ("single, absolute source of truth = `spec/`") **vs.** `.agents/rules/docs-source-of-truth.md:30` ("el código fuente prevalece" sobre docs, y docs son obligatorias) **vs.** `docs/architecture/proceso-desarrollo-fases.md:201,283` y `ADR-011:22` ("sincronizar el Memory Bank", inexistente). → H-001
2. **Invariantes numerados distinto:** `AGENTS.md:121-144` (9 invariants, #3 = garantías de plataforma) **vs.** `spec/constitution/tech-stack.md:61-68` (6 límites, #3 = IDOR). → H-005
3. **Cifra de aserciones:** `spec/constitution/tech-stack.md:29` ("141 directas, 1.146 acumuladas") **vs.** `AGENTS.md:69`, rules/general.md:32, roadmap.md:7, docs/testing/README.md:22 ("1.307"). → H-006
4. **Gate de testing incompleto en AGENTS:** `AGENTS.md:72-79` (6 pasos, sin curl HTTP) **vs.** `.agents/rules/general.md:35-36` (paso 2 = HTTP curl checks). → H-009
5. **Prohibición Bootstrap blue como valor vs. como clases:** `AGENTS.md:104` (hex #0d6efd) **vs.** `.agents/rules/ui-ux-design-system.md:113-114` (clases `text-primary`/`bg-primary`/`border-primary`) — la práctica usa las clases remapeadas, por lo que se cumple un criterio y se viola el otro. → H-010
6. **Nombre del producto:** "Amigurumi Micro-ERP" (ui-ux-design-system.md:7, .gitignore:2, src/js/main.js:1) **vs.** "Crochet Manager" (mission.md, AGENTS.md, docs/). → H-007
7. **Decisión de almacenamiento del token:** `spec/features/004/plan.md:24` (localStorage) **vs.** roadmap.md:11 ("almacenamiento **seguro** del token" — sin definición de qué significa seguro; localStorage no es seguro ante XSS mientras no exista CSP). → H-004

(Antes de reportar estas contradicciones contrasté el Código real: 25 controladores ≤60 líneas ✔, 0 PHP en `src/` ✔, tokens CSS §1 del design system presentes en `variables.css` ✔, memoria de que `memory-bank/` no existe ✔.)

---

## Brechas sin Cobertura (escenarios reales sin regla)

1. **Rollback de una feature aprobada y defectuosa** (H-014).
2. **Suite CLI verde / HTTP rojo** (y viceversa): sin triaje (H-015).
3. **Test imposible de dejar en verde en la subfase**: sin protocolo de bloqueo/escindido (H-018).
4. **Conflicto entre dos reglas** con fuerza similar: no existe tie-breaker formal ni precedencia; se resuelve "como quiera el agente" (H-001).
5. **Regresión de features 004–008 al modificar una de ellas** (solo hay suite acumulada para Fase 3) (H-020).
6. **Dependencias externas futuras**: sin criterio de decisión ni ADR obligatorio (H-017).
7. **Múltiples agentes en paralelo → conflictos de merge sin política** (H-016).
8. **Onboarding de un artesano/nuevo desarrollador humano** al framework (H-025).
9. **Rotación de secreto HMAC y revocación de tokens** (H-002) — ausencia total.
10. **Deprecación de documentación obsoleta** (H-022).
11. **Nuevo dominio de docs sin sección previa**: el mapeo de docs-source-of-truth.md cubre cambios en dominios existentes, pero no define el spleen para crear un dominio nuevo (solo "actualizar docs/README si hay archivos nuevos", line 70).

---

## Reglas Redundantes (riesgo de desincronización)

| Regla | Fuentes donde aparece (N) | Archivos |
|---|---|---|
| Borrado lógico universal + preservación de assets | 6 | AGENTS.md §5.1-2, rules/general.md:53-54, workflows/general.md:53-54, tech-stack #1-2, docs/api/README §8, ADR-004 |
| 3-Tier testing + halting gate | 5 | AGENTS.md §3, rules/general.md:33-40, workflows/general.md:33-40, docs/testing/README.md:76-88, ADR-011 |
| Envelope JSON estandar | 4 | AGENTS.md §4, tech-stack:43-46, docs/api/README §3, (implicit) Response.php |
| Controladores ≤60 líneas | 3-4 | AGENTS.md:14,88 · tech-stack:18 · docs/architecture/README.md · (check 3.6.5) |
| Heurísticas de diseño CR/QW | 2+ | ui-ux-design-system.md §4 · docs/design-system/audits.md §2 (numeración divergente y duplicada) |
| **Flujo iterativo completo (phases 1-5)** | **2 (byte-idénticos)** | **`.agents/rules/general.md` vs `.agents/workflows/general.md`** |

---

## Puntuación de Robustez

| Dimensión | Nota | Fundamento |
|---|---|---|
| 1. Consistencia interna | **5** | Contradicciones tri-fuente, invariantes numerados distinto, identidad de producto, cifras dispares |
| 2. Cobertura de brechas | **4** | Muchos escenarios sin cubrir (rollback, multi-agente, CLI/HTTP, deps, blockers); bueno en docs-vs-código |
| 3. Claridad y no-ambigüedad | **6** | Tokens/hex precisos, umbral 60 líneas vago, criterio Bootstrap dual, IDs duplicados |
| 4. Protocolo de testing | **6** | Excelente en estructura (3 tiers + plantilla + gate), débil en divergencia, regresión y escapatoria acotada |
| 5. Seguridad | **5** | Invariantes de dominio fuertes y verificados; brechas en auth/bearer/XSS/CSP/brute force |
| 6. Design system | **6** | Tokens verificados reales; 2 reglas fantasma, IDs duplicados, doble criterio Bootstrap |
| 7. Ciclo de vida documental | **5** | Cierre documental robusto pero índices desincronizados (commiteado), sin deprecación, refs muertas |
| 8. SDD | **6** | Flujo spec→plan→tasks→código bien definido y 004 coherente; tasks sin gate de tests, spec/README plantilla |
| 9. Redundancia y mantenibilidad | **4** | Duplicación literal (general.md ×2), invariante en 6 fuentes, sobrecarga cognitiva alta |
| 10. Escalabilidad del framework | **4** | Modelo de agente único, sin merge/paralelismo/onboarding/multi-rol definidos |
| **Promedio** | **5.1** | |

---

## Top 5 Acciones Prioritarias

1. **Resolver la fuente de verdad única (urgente, 🔴).** Purgar el "Memory Bank" de proceso-desarrollo-fases.md y ADR-011, y publicar una jerarquía de precedencia explícita (Reglas → spec.md → ADR → docs → código) — con criterio de desincronización ya pactado (código manda, pero se reporta). → H-001, H-009
2. **Blindar la seguridad del ciclo de token ANTES de codificar 004 (urgente, 🔴).** Añadir CSP + auditoría de innerHTML como prerrequisito, decidir localStorage-vs-cookie httpOnly, añadir invariante anti-brute-force del login, y documentar en ADR-002 la no-revocación del logout + rotación de secreto. → H-002, H-003, H-004
3. **Completar el gate de testing para la Fase 4.** Enriquecer `tasks.md` de 004 con suite CLI + curl + reporte `docs/testing/`, definir triaje CLI-vs-HTTP, y crear suite de regresión acumulada de Fase 4. → H-008, H-015, H-020
4. **Consolidación de reglas (anti-rule-rot).** Fusionar rules/general.md y workflows/general.md en una sola fuente, canónizar la tabla de invariantes R-01…R-09 con referencia por clave semántica, y renumerar CR/QW únicos. → H-005, H-011, H-012, H-023
5. **Re-sincronizar la documentación commiteada.** Actualizar `docs/README.md` (3.6.2–3.6.5 + context7), corregir cifras de tech-stack.md, corregir/deprecar `ui-ux-audit.md` (rutas muertas) y añadir verificación automática de enlaces en índices. → H-006, H-013, H-022

---

**Notas de método:** no ejecuté las suites de tests en vivo (no hay servidor en `localhost:8000` y requiere levantar el built-in server); el conteo de 1.307 está verificado aritméticamente contra la tabla de docs/testing/README.md y no triangularizado por ejecución. Todos los demás hallazgos fueron verificados contra archivos y estructura reales del repositorio.