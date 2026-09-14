# Roadmap

## Hecho ✅

1. **001 · Base de Datos, DDL & Concurrencia SQLite (Fase 1)** — Esquema relacional con 3 tablas (`usuarios`, `creaciones`, `pedidos`), restricciones CHECK de dominio, foreign keys, índices de rendimiento y script de inicialización seguro CLI-only `setup.php`.
2. **002 · Layout, Componentes UI & Sistema Algodón Nórdico (Fase 2)** — Arquitectura de presentación modular en PHP (`views/`), estilos ITCSS en `src/css/`, módulos ES6 en `src/js/`, y diseño artesanal pespunteado "Algodón Nórdico" (Fraunces, running stitch, etiquetas textiles).
3. **003 · Backend Clean Architecture, API REST & Blindaje (Fase 3)** — Núcleo PHP en `app/` (PSR-4 nativo, repositorios con 100% prepared statements, servicios de negocio, middleware RBAC, 25 controladores REST delgados en `api/`, auditoría OWASP e integración de 1,307 aserciones en verde en `tests/`).

## Gobernanza ✅

- **Fuente de Verdad Única (Acción 1 · audit 14/09/2026)** — Jerarquía de precedencia de fuentes canónica (P1–P6) publicada en `AGENTS.md §6` y encadenada desde `.agents/rules/docs-source-of-truth.md`; gate de testing 3-tier sincronizado con paso HTTP curl (`AGENTS.md §3`); purga completa del mecanismo obsoleto "Memory Bank" en `docs/`, `README.md`, `.htaccess` y aserciones; suites 3.1 y 3.6.5 en verde.
- **Blindaje del Ciclo de Token (Acción 2 · audit 14/09/2026)** — CSP estricto en layout y API (H-004); auditoría de sanitización `innerHTML` (36 → 0 vectores sin escalar, `dom-safe.js` + `AGENTS.md §4` + regla P1); `login_intentos` con lockout 5/cuenta + 20/IP → HTTP 429 + backoff (H-003); revocación server-side por denylist de `jti` en `logout.php` (H-002) + rotación de secretos con claim `ver`; ADR-016 + enmienda ADR-002; prerequisito de seguridad de la feature 004.

## Siguiente 🔜

4. **004 · Autenticación, Token Bearer & Estado Reactivo del Navbar (Fase 4.1)** — Cableado AJAX de login/logout, almacenamiento seguro de token Bearer en cliente, detección de sesión expirada y reactividad visual inmediata del Navbar y sidebar.

## Backlog / Ideas 💡

- **005 · Catálogo Dinámico & Filtros Textiles (Fase 4.2)** — Conexión asíncrona de la vitrina con `/api/creaciones/`, sincronización bidireccional de chips textiles, rangos de precio y paginación reactiva.
- **006 · Gestión de Creaciones & Subida Multipart (Fase 4.3)** — Cableado completo de `formulario.php` (creación y edición con subida real de fotos), ajuste de stock in-situ, toggle bajo encargo y modal de baja lógica.
- **007 · Checkout Público, Pedidos Atómicos & WhatsApp (Fase 4.4)** — Modal de compra rápida en ficha de detalle, reserva atómica de existencias, cálculo seguro de precio en servidor y enlaces directos pre-formateados a WhatsApp.
- **008 · Directorio de Creadores & Roles RBAC (Fase 4.5)** — Panel de administración de usuarios en `usuarios.php`, actualización reactiva de roles con salvaguarda ID #1, reseteo de claves y reactivación de cuentas.

> Cada feature nueva se crea en `spec/features/NNN-nombre-feature/` con `spec.md`, `plan.md` y `tasks.md` antes de tocar código.
