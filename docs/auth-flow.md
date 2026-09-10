# Authentication & Session Security Architecture

This document details the user authentication lifecycle, password hashing strategy, session access control, and identity attribution for the Micro-ERP system.

---

## 1. Authentication Lifecycle Diagram

```mermaid
sequenceDiagram
    autonumber
    actor User as Artisan / Admin
    participant UI as Browser (login.html)
    participant Auth as Backend (api/login.php)
    participant DB as SQLite (usuarios)
    participant Guard as Session Guard (auth_guard.php)
    participant API as CRUD Endpoints (api/crear.php, etc.)

    User->>UI: Enters username & password
    UI->>Auth: POST credentials (JSON / form-data)
    Auth->>DB: SELECT * FROM usuarios WHERE username = ?
    DB-->>Auth: Returns user record with password_hash
    Auth->>Auth: password_verify(rawPassword, password_hash)
    alt Invalid Credentials
        Auth-->>UI: HTTP 401 Unauthorized ("Credenciales inválidas")
    else Valid Credentials
        Auth->>Auth: session_start() & session_regenerate_id(true)
        Auth->>Auth: Store user_id, username, rol in $_SESSION
        Auth-->>UI: HTTP 200 OK (Auth token/cookie established)
        UI->>UI: Redirect to catalog / admin dashboard
    end

    Note over UI,API: Creation Request (Catalog Amigurumi Item)
    UI->>API: POST /api/crear.php (payload without user ID)
    API->>Guard: check_authenticated()
    alt Session Not Set / Expired
        Guard-->>UI: HTTP 401 Unauthorized (Redirect to login)
    else Authenticated
        Guard-->>API: Allow execution
        API->>API: Extract artesano_id from $_SESSION['user_id']
        API->>DB: INSERT INTO amigurumis (artesano_id, ...) VALUES (?, ...)
        DB-->>API: Created record ID
        API-->>UI: HTTP 201 Created
    end
```

---

## 2. Password Hashing Strategy
- **Algorithm:** Native PHP `password_hash($password, PASSWORD_DEFAULT)` utilizing strong bcrypt/Argon2 hashing with random automated salting.
- **Verification:** Secure timing-attack resistant `password_verify($password, $stored_hash)`.
- **Default Seeding:** In `setup.php`, default administrative credentials will be automatically generated with a secure hash:
  ```php
  <?php
  $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT OR IGNORE INTO usuarios (username, password_hash, rol) VALUES (?, ?, 'admin')");
  $stmt->execute(['admin', $adminHash]);
  ```

---

## 3. Session Security & Identity Binding
Every script requiring session verification must initialize sessions with strict security flags:
```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => false,   // Set to true in HTTPS production
        'httponly' => true,  // Protect against XSS session theft
        'samesite' => 'Lax'  // CSRF protection
    ]);
    session_start();
}
```

### Identity Attribution Rule:
- When a user creates a new creation via `POST /api/crear.php`, the backend reads `$_SESSION['user_id']` and directly sets `artesano_id`.
- The client cannot supply `artesano_id` in the request body; any client-provided ID is discarded to prevent privilege escalation.

---

## 4. Protected vs. Public Endpoints Matrix

| Resource / Endpoint | Access Tier | Authentication Requirement | Purpose |
| :--- | :--- | :--- | :--- |
| `index.html` (Catalog View) | **Public** | None | Allows potential customers to browse amigurumi creations. |
| `detalle.html` (Item Details) | **Public** | None | Displays detailed specifications and availability. |
| `api/leer.php` | **Public** | None | Returns JSON catalog list or single item with artisan attribution. |
| `api/login.php` | **Public** | Guest only | Authenticates credentials and starts user session. |
| `api/logout.php` | **Protected** | Authenticated | Destroys current session and clears cookies. |
| `formulario.html` (Add / Edit) | **Protected** | Session required | Prevents unauthorized visitors from modifying catalog. |
| `api/crear.php` | **Protected** | Session required (`admin` or `artesano`) | Inserts new amigurumi, binding `artesano_id = $_SESSION['user_id']`. |
| `api/actualizar.php` | **Protected** | Session required (`admin` or creator) | Modifies catalog details, costs, and stock. |
| `api/eliminar.php` | **Protected** | Session required (`admin`) | Deletes an amigurumi (subject to order constraint). |
| `pedidos.html` & `api/pedidos.php`| **Protected** | Session required | Full order management, status updates, and commissions. |
