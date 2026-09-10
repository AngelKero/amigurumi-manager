# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Full Relational Linkage (Artisan Attribution Added)
- **Artisan Linkage Integrated:**
  1. Added `artesano_id INTEGER NOT NULL` with `FOREIGN KEY (artesano_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE` to `amigurumis`.
  2. Created index `idx_amigurumis_artesano ON amigurumis(artesano_id)`.
  3. Updated Mermaid ERDs across English and Spanish docs to illustrate `USUARIOS ||--o{ AMIGURUMIS` and `AMIGURUMIS ||--o{ PEDIDOS`.
  4. Updated API specifications: `POST /api/crear.php` automatically extracts `$_SESSION['user_id']` and maps it to `artesano_id`. Client cannot supply or spoof this value.
- **Files Synchronized:**
  - `docs/database-schema.md` & `docs/database-schema.es.md`
  - `docs/data-model.md` & `docs/data-model.es.md`
  - `docs/api-design.md` & `docs/api-design.es.md`
  - `docs/auth-flow.md` & `docs/auth-flow.es.md`
  - `memory-bank/techContext.md`
  - `memory-bank/progress.md`
- **Phase Gate Status:** Ready for final user sign-off on Phase 0 before starting Phase 1 (Layout & UI).

## Immediate Focus: Phase 1 (Layout & UI)
Upon user approval:
- Proceed to Phase 1: Build semantic HTML5 views (`index.html`, `formulario.html`, `detalle.html`, `pedidos.html`, `login.html`) with Bootstrap 5 CDN.
- Ensure cross-page navigation works seamlessly.
- Halt at Phase 1 completion for review.

## Next Steps Upon Sign-Off
1. Obtain final user approval on Phase 0 deliverables.
2. Advance to Phase 1 (Layout & UI) implementation without generating backend/database files.
3. Halt after Phase 1 completion for review.
