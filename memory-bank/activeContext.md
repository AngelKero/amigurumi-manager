# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: User Management CRUD, Public Checkout, Image Uploads, Navbar Modal & Orphaned Files Management
- **New Functional Requirements Integrated:**
  1. **User Management CRUD (`/api/usuarios.php`):** Added endpoints (`GET`, `POST`, `PUT`, `DELETE`) for administrative user management, strictly restricted to authenticated sessions with the `admin` role.
  2. **Public Client Checkout (`/api/solicitar_pedido.php`):** Created a public-facing order creation endpoint for customers browsing `detalle.html`. Operates without requiring an account/session, while enforcing the identical atomic database transaction (`cantidad_stock` validation and deduction, server-calculated `precio_final`, and default `'Pendiente'` status).
  3. **Real Image File Uploads (`/uploads`):** Upgraded `POST /api/crear.php` and `POST /api/actualizar.php` to handle `multipart/form-data` image uploads, verifying MIME types and size limits, generating unique filenames, storing files locally in `/uploads/`, and persisting relative paths in `imagen_url`.
  4. **UI Navigation Refinement (Navbar Login Modal):** Converted the login interface from a standalone `login.html` page into a dynamic Bootstrap Modal component shared across the navbar of all views (`index.html`, `formulario.html`, `detalle.html`, `pedidos.html`).
  5. **Orphaned File Deletion Policy (`POST /api/eliminar.php`):** Mandated that before removing an amigurumi record from SQLite, the backend must retrieve `imagen_url` and delete the associated physical file from `/uploads/` using PHP's `unlink()`, preventing orphaned files on disk while respecting `ON DELETE RESTRICT` constraints.
- **Files Synchronized (Bilingual Suite):**
  - `docs/api-design.md` & `docs/api-design.es.md`
  - `docs/auth-flow.md` & `docs/auth-flow.es.md`
  - `docs/database-schema.md` & `docs/database-schema.es.md`
  - `memory-bank/techContext.md`
  - `memory-bank/activeContext.md`
  - `memory-bank/progress.md`
- **Phase Gate Status:** Phase 0 concluded and signed off. Roadmap restructured to strict Database-First approach. Now executing **Phase 1: Database Implementation, Seeding & Testing**.

## Immediate Focus: Phase 1 (Database Implementation, Seeding & Testing)
Executing Phase 1 deliverables:
1. `database/seed.sql`: Complete executable SQLite schema DDL (3 tables with constraints, indexes) and rich initial mock seed data (1 admin user with verified hash for `admin123`, 3 crafted amigurumis, 2 commission orders).
2. `setup.php`: Automated initialization script creating root `database.sqlite`, enforcing `PRAGMA foreign_keys = ON;`, and executing `seed.sql`.
3. `.docs/database-testing.md` & `database-testing.es.md`: CLI verification manual with reproducible `sqlite3` terminal commands testing foreign key violations, price calculations, and stock limits.

## Next Steps
1. Execute `setup.php` to generate and populate `database.sqlite`.
2. Verify all tables, constraints, and mock data using `sqlite3`.
3. Halt upon Phase 1 completion and present deliverables for user review before proceeding to Phase 2 (Layout & UI).
