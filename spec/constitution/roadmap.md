# Roadmap

## Hecho ✅

1. **001 · Base de Datos, DDL & Concurrencia SQLite (Fase 1)** — Esquema relacional con 3 tablas (`usuarios`, `creaciones`, `pedidos`), restricciones CHECK de dominio, foreign keys, índices de rendimiento y script de inicialización seguro CLI-only `setup.php`.
2. **002 · Layout, Componentes UI & Sistema Algodón Nórdico (Fase 2)** — Arquitectura de presentación modular en PHP (`views/`), estilos ITCSS en `src/css/`, módulos ES6 en `src/js/`, y diseño artesanal pespunteado "Algodón Nórdico" (Fraunces, running stitch, etiquetas textiles).
3. **003 · Backend Clean Architecture, API REST & Blindaje (Fase 3)** — Núcleo PHP en `app/` (PSR-4 nativo, repositorios con 100% prepared statements, servicios de negocio, middleware RBAC, 25 controladores REST delgados en `api/`, auditoría OWASP e integración de 1,287 aserciones en verde en `tests/` — cifra regenerable con `php tests/cuenta-aserciones.php`, H-006).

## Gobernanza ✅

- **Fuente de Verdad Única (Acción 1 · audit 14/09/2026)** — Jerarquía de precedencia de fuentes canónica (P1–P6) publicada en `AGENTS.md §6` y encadenada desde `.agents/rules/docs-source-of-truth.md`; gate de testing 3-tier sincronizado con paso HTTP curl (`AGENTS.md §3`); purga completa del mecanismo obsoleto "Memory Bank" en `docs/`, `README.md`, `.htaccess` y aserciones; suites 3.1 y 3.6.5 en verde.
- **Blindaje del Ciclo de Token (Acción 2 · audit 14/09/2026)** — CSP estricto en layout y API (H-004); auditoría de sanitización `innerHTML` (36 → 0 vectores sin escalar, `dom-safe.js` + `AGENTS.md §4` + regla P1); `login_intentos` con lockout 5/cuenta + 20/IP → HTTP 429 + backoff (H-003); revocación server-side por denylist de `jti` en `logout.php` (H-002) + rotación de secretos con claim `ver`; ADR-016 + enmienda ADR-002; prerequisito de seguridad de la feature 004.
- **Gate de Testing de la Fase 4 (Acción 3 · audit 14/09/2026)** — SDD de la feature 004 con gate 3-tier por subfase 4.1–4.5 (H-008); protocolo de divergencia CLI/HTTP anclado en `AGENTS.md §3`, `.agents/rules/general.md` y `docs/testing/README.md` (H-015); suite de regresión acumulada `tests/test-fase-4-acumulado.php` con despliegue dinámico y estado vacío exitoso, obligatoria al tocar features 004–008 (H-020); suite de la acción 33/33 + regresión 3.1/3.6.5/gobernanza-2 en verde.
- **Consolidación de Reglas (Acción 4 · audit 14/09/2026)** — Anti-rule-rot: invariantes canónicos `R-01…R-10` en `AGENTS.md §5` citados por clave semántica (tabla-ancla en `tech-stack.md`, `docs/api/README §8`, ADR-004) (H-005); reglas de directorio de creadores implantadas `.table-artisan-team` + `.avatar-artisan-initials`, eliminada `.user-avatar-circle` (H-011); heurísticas CR/QW renumeradas a IDs únicos CR-1…CR-4 / QW-1…QW-3 en regla, audits, código vivo e históricos `qa-audit-report` + `docs/archive` (H-012); single-source: `.agents/rules/general.md` canónico y `.agents/workflows/general.md` como glue de enrutamiento (H-023); suite de la acción 56/56 + regresión 3.1/3.6.5/gobernanza-2/3 + runner fase-4 en verde.
- **Re-sincronización Documental (Acción 5 · audit 14/09/2026)** — Cifras de aserciones verificables y regenerables: runner `tests/cuenta-aserciones.php` (resetea BD a semilla + parse ANSI + 10 suites Fase 3) con **ground truth 1,287** en verde; totales canónicos corregidos (1,307→1,287, 3.6.2=141, 3.6.x=755) en P1/P2/P4/P5 (H-006); índice maestro `docs/README.md` exhaustivo que enlaza **todo** `docs/**/*.md` y desentiérra el dominio huérfano `docs/security/` (H-013); workflow `ui-ux-audit` reescrito como glue sin rutas muertas + política "Deprecación y Archivado" en `docs-source-of-truth.md` (P1) + `docs/archive/README.md` como histórico no autoritativo (H-022); suite de la acción 42/42 (+10 asserts detector del propio H-013) + regresión gobernanza-2/3/4 + 3.1 + 3.6.5 + fase-4 en verde.

## Siguiente 🔜

4. **004 · Autenticación, Token Bearer & Estado Reactivo del Navbar (Fase 4.1)** — Cableado AJAX de login/logout, almacenamiento seguro de token Bearer en cliente, detección de sesión expirada y reactividad visual inmediata del Navbar y sidebar. *(Gate 3-tier por subfase: Gobernanza · Acción 3, H-008/H-015/H-020.)*

## Backlog / Ideas 💡

- **005 · Catálogo Dinámico & Filtros Textiles (Fase 4.2)** — Conexión asíncrona de la vitrina con `/api/creaciones/`, sincronización bidireccional de chips textiles, rangos de precio y paginación reactiva.
- **006 · Gestión de Creaciones & Subida Multipart (Fase 4.3)** — Cableado completo de `formulario.php` (creación y edición con subida real de fotos), ajuste de stock in-situ, toggle bajo encargo y modal de baja lógica.
- **007 · Checkout Público, Pedidos Atómicos & WhatsApp (Fase 4.4)** — Modal de compra rápida en ficha de detalle, reserva atómica de existencias, cálculo seguro de precio en servidor y enlaces directos pre-formateados a WhatsApp.
- **008 · Directorio de Creadores & Roles RBAC (Fase 4.5)** — Panel de administración de usuarios en `usuarios.php`, actualización reactiva de roles con salvaguarda ID #1, reseteo de claves y reactivación de cuentas.

> Cada feature nueva se crea en `spec/features/NNN-nombre-feature/` con `spec.md`, `plan.md` y `tasks.md` antes de tocar código.
