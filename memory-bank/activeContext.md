# Active Context: Amigurumi Micro-ERP & Catalog

## Current State: Multi-Layered Security Hardening & Database-First Phase 1 Execution
- **Multi-Layered Security Architecture Implemented:**
  1. **Data Loss Prevention (CLI-Only Setup):** Hardened `setup.php` to immediately abort with `Access Denied` if requested via HTTP/browser (`php_sapi_name() !== 'cli'`). Removed all HTML rendering to enforce command-line execution only.
  2. **Data Leak Mitigation (Relocated Database):** Moved physical database file to `database/database.sqlite`, out of the web server document root.
  3. **Apache Direct Access Blocking (.htaccess):** Generated root `.htaccess` and `database/.htaccess` strictly denying direct HTTP requests to `.sqlite`, `.sql`, and `.md` files, and blocking web access to `database/` and `memory-bank/`.
  4. **Orphaned File Deletion Policy:** Fully specified in `docs/api-design.md` and `docs/api-design.es.md` (`POST /api/eliminar.php`), requiring `unlink()` of local image files before database record deletion.
- **Files Synchronized (Bilingual Suite):**
  - `docs/api-design.md` & `docs/api-design.es.md`
  - `docs/database-schema.md` & `docs/database-schema.es.md`
  - `docs/database-testing.md` & `docs/database-testing.es.md`
  - `memory-bank/projectbrief.md`
  - `memory-bank/techContext.md`
  - `memory-bank/activeContext.md`
  - `memory-bank/progress.md`
- **Phase Gate Status:** Phase 0 concluded. Executed **Phase 1: Database Implementation, Seeding & Testing** with hardened security infrastructure.

## Immediate Focus: Phase 1 (Database Implementation, Seeding & Testing)
Phase 1 deliverables generated and verified:
1. `database/seed.sql`: DDL schema with separated, documented table-level constraints (`CONSTRAINT ... UNIQUE/CHECK/FOREIGN KEY`) and seed data (1 admin user with verified hash for `admin123`, 3 distinct amigurumis, 2 orders).
2. `setup.php`: Hardened CLI-only script creating `database/database.sqlite` with `PRAGMA foreign_keys = ON;` and executing `seed.sql`.
3. `.htaccess` (Root & `database/`): Web protection denying access to `.sqlite`, `.sql`, `.md`, `database/`, and `memory-bank/`.
4. `.docs/database-testing.md` & `database-testing.es.md`: Updated CLI verification manual targeting `database/database.sqlite`.

## Next Steps
1. Await user review and explicit approval of Phase 1 deliverables.
2. Advance to Phase 2 (Layout & UI) upon approval without generating PHP endpoint logic yet.
