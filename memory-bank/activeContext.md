# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Order Security, Inventory Sync, and Lifecycle Controls Applied
- **Critical Business Logic & Security Flaws Resolved:**
  1. **Anti-Price Spoofing:** In `POST /api/pedidos.php`, `precio_final` was completely eliminated from the client payload. The PHP backend dynamically queries `amigurumis.precio` and calculates `precio_final = precio * cantidad`.
  2. **Atomic Inventory Synchronization (Stock Deduction):** Documented that creating an order runs inside a PDO database transaction (`BEGIN TRANSACTION`). It validates that `cantidad <= amigurumis.cantidad_stock` (returning HTTP 422 if insufficient) and automatically decrements physical stock (`UPDATE amigurumis SET cantidad_stock = cantidad_stock - :cantidad`).
  3. **Order Management & Restocking Endpoint:** Added protected endpoint `POST /api/actualizar_pedido.php` to manage status changes (`Pendiente`, `En Proceso`, `Entregado`, `Cancelado`). If an order transitions to `'Cancelado'`, the backend executes a transaction restoring the reserved units back to `amigurumis.cantidad_stock`.
- **Files Synchronized (Bilingual Suite):**
  - `docs/api-design.md` & `docs/api-design.es.md`
  - `docs/database-schema.md` & `docs/database-schema.es.md`
  - `memory-bank/techContext.md`
  - `memory-bank/progress.md`
- **Phase Gate Status:** Phase 0 architecture, security models, and business logic are fully addressed. Awaiting final user approval before starting Phase 1 (Layout & UI).

## Immediate Focus: Phase 1 (Layout & UI)
Upon user approval:
- Proceed to Phase 1: Build semantic HTML5 views (`index.html`, `formulario.html`, `detalle.html`, `pedidos.html`, `login.html`) with Bootstrap 5 CDN.
- Ensure cross-page navigation works seamlessly.
- Halt at Phase 1 completion for review.

## Next Steps Upon Sign-Off
1. Obtain final user approval on Phase 0 deliverables.
2. Advance to Phase 1 (Layout & UI) implementation without generating backend/database files.
3. Halt after Phase 1 completion for review.
