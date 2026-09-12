# Product Context: Multi-Artisan Crochet Platform & Collaborative Micro-ERP

## Why This Project Exists
Independent crochet artisans and textile creators need more than a static personal portfolio; they require a lightweight, collaborative **Micro-ERP and Collective Platform** where multiple independent artisans can register, showcase their pieces, track physical stock, manage costs and labor hours, and coordinate custom commissions directly with customers.

Because creators operate autonomously with total independence, the platform **does not and cannot control what, how, or when artisans craft**. Instead of centralized manufacturing promises or fixed factory lead times, the platform centers its quality assurances on **platform transparency, verified creator profiles, accurate technical specification sheets, fair-trade pricing tools, and direct customer-to-artisan communication (WhatsApp)**.

## Target User Personas
- **Registered Artisan / Independent Creator (`rol: artesano` / `rol: admin`):**
  - Registers and showcases creations across diverse crochet disciplines (Amigurumis, Garments, Bags & Accessories, Home Decor, Baby & Kids).
  - Calculates production costs, labor investment, and ethical pricing with real-time margin simulators.
  - Controls their own stock availability (`cantidad_stock`) or toggles on-demand commission status (`es_sobre_encargo`).
  - Receives and manages client commission requests (`pedidos`), agreeing on personalized details and delivery times directly with the client.
- **Platform Administrator (`rol: admin`):**
  - Manages platform governance, creator directory, and role privileges without interfering with creator artistic autonomy.
- **Client / Public Visitor (Unauthenticated):**
  - Explores the open collective catalog, filters creations by category, price, and artisan author.
  - Inspects transparent craftsmanship specifications (dimensions, fiber composition, care instructions).
  - Coordinates directly with the creator for immediate purchases or custom commissions via platform tools and WhatsApp.

## Key User Journeys
1. **Collective Catalog Exploration (`index.php`):**
   - Visitor browses creations by category or creator author, checking real-time stock availability or on-demand status.
2. **Creations & Stock Administration (`creaciones.php` - Authenticated):**
   - Artisan manages their pieces with live KPIs, quick stock adjustments, on-demand toggles, technical inspection modals, and deletion with referential integrity protection.
3. **Creation Management (`formulario.php` - Authenticated):**
   - Artisan registers or updates a piece with flexible dimensions and real-time margin simulation (protected by privacy shield until required fields are completed).
4. **Product Inspection & Order Coordination (`detalle.php`):**
   - High-fidelity visual showcase with verified creator profile seal, transparent specifications, and public order modal that coordinates directly with the artisan.
5. **Order & Commission Tracking (`pedidos.php` - Authenticated):**
   - Artisan manages incoming orders, delivery states, payment badges, customer contact via WhatsApp, and automatic inventory restitution upon cancellation.
6. **Community & Creator Management (`usuarios.php` - Authenticated Admin):**
   - Directory of registered platform creators and role management governed by RBAC rules with root admin lockout protection.

## Design & Guarantee Principles
- **Warm, Crafted Aesthetic:** "Algodón Nórdico" palette, Fraunces display typography, running-stitch seams, and quilted frames.
- **Platform-Centered Guarantees:**
  - *Transparent Technical Sheets:* Rigorous documentation of dimensions, fibers, and care.
  - *Direct Communication:* Direct customer-creator agreements for customizations and delivery timeframes.
  - *Ethical Commerce:* Tools that ensure fair pricing respecting manual labor.
- **Data Integrity & Relational Safety:** Financial amounts stored in integer cents, order prices frozen at creation, and relational constraints (`ON DELETE RESTRICT`).
