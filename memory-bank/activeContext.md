# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: User Management CRUD, Public Checkout, Image Uploads, and Navbar Modal
- **New Functional Requirements Integrated:**
  1. **User Management CRUD (`/api/usuarios.php`):** Added endpoints (`GET`, `POST`, `PUT`, `DELETE`) for administrative user management, strictly restricted to authenticated sessions with the `admin` role.
  2. **Public Client Checkout (`/api/solicitar_pedido.php`):** Created a public-facing order creation endpoint for customers browsing `detalle.html`. Operates without requiring an account/session, while enforcing the identical atomic database transaction (`cantidad_stock` validation and deduction, server-calculated `precio_final`, and default `'Pendiente'` status).
  3. **Real Image File Uploads (`/uploads`):** Upgraded `POST /api/crear.php` and `POST /api/actualizar.php` to handle `multipart/form-data` image uploads, verifying MIME types and size limits, generating unique filenames, storing files locally in `/uploads/`, and persisting relative paths in `imagen_url`.
  4. **UI Navigation Refinement (Navbar Login Modal):** Converted the login interface from a standalone `login.html` page into a dynamic Bootstrap Modal component shared across the navbar of all views (`index.html`, `formulario.html`, `detalle.html`, `pedidos.html`).
- **Files Synchronized (Bilingual Suite):**
  - `docs/api-design.md` & `docs/api-design.es.md`
  - `docs/auth-flow.md` & `docs/auth-flow.es.md`
  - `docs/database-schema.md` & `docs/database-schema.es.md`
  - `memory-bank/techContext.md`
  - `memory-bank/progress.md`
- **Phase Gate Status:** Phase 0 architecture, API contracts, and security specifications are fully updated and validated. Awaiting final user approval before starting Phase 1 (Layout & UI).

## Immediate Focus: Phase 1 (Layout & UI)
Upon user approval:
- Proceed to Phase 1: Build semantic HTML5 views (`index.html`, `formulario.html`, `detalle.html`, `pedidos.html`) with Bootstrap 5 CDN.
- Integrate the dynamic Login Modal into the shared header/navbar.
- Integrate the Public Checkout Modal into `detalle.html`.
- Incorporate `enctype="multipart/form-data"` and file inputs into `formulario.html`.
- Halt at Phase 1 completion for review.

## Next Steps Upon Sign-Off
1. Obtain final user approval on Phase 0 deliverables.
2. Advance to Phase 1 (Layout & UI) implementation without generating backend/database files.
3. Halt after Phase 1 completion for review.
