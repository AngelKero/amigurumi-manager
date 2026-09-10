# Active Context: Amigurumi Micro-ERP & Catalog

## Current State & Relational Micro-ERP Expansion
- **Architectural Scope Expanded:** Successfully transitioned from a single-table catalog to a full **Relational Micro-ERP**.
- **Modular Documentation Created in `.docs/`:**
  - [.docs/data-model.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/data-model.md): Master architecture index and consolidated ERD.
  - [.docs/database-schema.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/database-schema.md): Complete 3-table DDL (`usuarios`, `amigurumis`, `pedidos`), data dictionaries, and foreign key integrity rules.
  - [.docs/auth-flow.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/auth-flow.md): Sequence diagram, password hashing standards, session security, and access control matrix.
  - [.docs/api-design.md](file:///Users/angelzaragoza/Desktop/proyecto-web/.docs/api-design.md): REST-like endpoint specifications, status codes, and error response contracts.
- **Phase Gate Status:** Multi-table relational schema and modular docs complete. Awaiting final user approval before starting Phase 1 (Layout & UI).

## Relational Data Model Features
1. `usuarios`: Authentication with `password_hash`, roles (`admin`, `artesano`, `asistente`), and session enforcement.
2. `amigurumis`: Core catalog with cents pricing (`precio`, `costo_materiales`), physical inventory count (`cantidad_stock`), crafting labor (`horas_tejido`), and audit timestamps.
3. `pedidos`: Custom client order tracking with foreign key `REFERENCES amigurumis(id) ON DELETE RESTRICT`, locked agreed price (`precio_final`), and workflow states.

## Immediate Focus: Phase 1 (Layout & UI)
Upon user authorization:
- Build the presentation layer using semantic HTML5 and Bootstrap 5 CDN:
  - `index.html`: Public catalog with dynamic stock badges and navigation to admin tools.
  - `formulario.html`: Dual-purpose create/edit form with real-time profit margin and hourly return calculator.
  - `detalle.html`: Comprehensive creation view with economic metrics card and direct order triggers.
  - `pedidos.html`: Orders and custom commissions dashboard.
  - `login.html`: Administrative login interface.
- Ensure cross-page navigation links function seamlessly across all views.

## Next Steps Upon Sign-Off
1. Obtain user approval on the 3-table relational schema and `.docs/` architecture.
2. Begin Phase 1 (Layout & UI) implementation without generating backend/database files.
3. Halt after Phase 1 completion for phase review.
