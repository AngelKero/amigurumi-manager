# Arquitectura de Autenticación y Seguridad de Sesiones

Este documento describe el ciclo de vida de autenticación de usuarios, la estrategia de cifrado de contraseñas, el control de acceso basado en sesiones de PHP y la vinculación de autoría para el sistema Micro-ERP.

---

## 1. Diagrama del Ciclo de Vida de Autenticación

```mermaid
sequenceDiagram
    autonumber
    actor Usuario as Artesano / Admin
    participant UI as Modal Navbar (Modal de Login)
    participant Auth as Backend (api/login.php)
    participant DB as SQLite (usuarios)
    participant Guard as Guardia de Sesión (auth_guard.php)
    participant API as Endpoints (api/crear.php, api/usuarios.php)

    Usuario->>UI: Clic en "Iniciar Sesión" en barra superior (abre modal)
    Usuario->>UI: Ingresa usuario y contraseña, envía formulario modal
    UI->>Auth: Envía credenciales vía POST (JSON por fetch)
    Auth->>DB: SELECT * FROM usuarios WHERE username = ?
    DB-->>Auth: Retorna registro con password_hash
    Auth->>Auth: password_verify(contrasenaPlana, password_hash)
    alt Credenciales Inválidas
        Auth-->>UI: HTTP 401 Unauthorized ("Credenciales inválidas")
        UI->>UI: Muestra alerta en el modal sin recargar página
    else Credenciales Válidas
        Auth->>Auth: session_start() y session_regenerate_id(true)
        Auth->>Auth: Almacena user_id, username, rol en $_SESSION
        Auth-->>UI: HTTP 200 OK (Cookie de sesión establecida)
        UI->>UI: Cierra modal y transforma el Navbar mostrando controles de Admin/Artesano
    end

    Note over UI,API: Operaciones Protegidas (Crear Amigurumi / Gestión de Usuarios)
    UI->>API: POST /api/crear.php (payload sin ID de usuario)
    API->>Guard: check_authenticated()
    alt Sesión No Iniciada / Expirada
        Guard-->>UI: HTTP 401 Unauthorized (Despliega modal de login)
    else Autenticado Exitosamente
        Guard-->>API: Permite ejecución
        API->>API: Extrae artesano_id desde $_SESSION['user_id']
        API->>DB: INSERT INTO amigurumis (artesano_id, ...) VALUES (?, ...)
        DB-->>API: ID del registro creado
        API-->>UI: Respuesta HTTP 201 Created Exitosa
    end
```

---

## 2. Estrategia de Cifrado de Contraseñas
- **Algoritmo:** Función nativa de PHP `password_hash($password, PASSWORD_DEFAULT)`, que implementa hashing seguro bcrypt/Argon2 con salting criptográfico automatizado.
- **Verificación:** Comparación resistente a ataques de temporización mediante `password_verify($password, $stored_hash)`.
- **Semilla Inicial (Seeder):** En `setup.php`, se insertará el usuario administrador por defecto con un hash generado dinámicamente:
  ```php
  <?php
  $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT OR IGNORE INTO usuarios (username, password_hash, rol) VALUES (?, ?, 'admin')");
  $stmt->execute(['admin', $adminHash]);
  ```

---

## 3. Configuración de Seguridad de Sesiones y Vinculación de Autoría
Todo script que valide sesiones debe inicializarlas aplicando directivas de seguridad para cookies:
```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 24 horas de vigencia
        'path' => '/',
        'domain' => '',
        'secure' => false,   // Cambiar a true al desplegar en HTTPS
        'httponly' => true,  // Previene robo de sesión mediante ataques XSS
        'samesite' => 'Lax'  // Protección contra solicitudes falsificadas (CSRF)
    ]);
    session_start();
}
```

### Regla de Atribución de Identidad:
- Al registrar una pieza mediante `POST /api/crear.php`, el backend toma `$_SESSION['user_id']` y lo asigna de forma obligatoria a `artesano_id`.
- El cliente no puede proporcionar `artesano_id` en el cuerpo de la solicitud JSON; cualquier valor enviado por el cliente es ignorado para evitar la suplantación de autoría.

---

## 4. Matriz de Endpoints Públicos vs. Protegidos

| Recurso / Endpoint | Nivel de Acceso | Requisito de Autenticación | Propósito |
| :--- | :--- | :--- | :--- |
| `index.html` (Vista de Catálogo) | **Público** | Ninguno | Permite a clientes y visitantes explorar creaciones. |
| `detalle.html` (Detalle de Pieza) | **Público** | Ninguno | Muestra especificaciones, autoría, stock y botón de compra directa. |
| `api/solicitar_pedido.php` | **Público** | Ninguno | Checkout de clientes; descuenta stock y fija precio atómicamente. |
| `api/leer.php` | **Público** | Ninguno | Retorna el catálogo o una pieza en formato JSON con nombre de artesano. |
| `api/login.php` | **Público** | Solo invitados (Modal en Navbar) | Valida credenciales e inicia la sesión del usuario. |
| `api/logout.php` | **Protegido** | Autenticado | Destruye la sesión activa y limpia las cookies. |
| `formulario.html` (Crear / Editar) | **Protegido** | Sesión requerida | Creación y edición con subida de imágenes a `/uploads/`. |
| `api/crear.php` | **Protegido** | Sesión requerida (`admin` o `artesano`) | Registra amigurumi, guarda imagen en `/uploads`, asigna `artesano_id`. |
| `api/actualizar.php` | **Protegido** | Sesión requerida (`admin` o autor) | Modifica catálogo, costos, inventario y actualiza archivo de imagen. |
| `api/eliminar.php` | **Protegido** | Sesión requerida (`admin`) | Elimina una pieza (sujeto a la restricción de pedidos). |
| `api/usuarios.php` | **Protegido** | Exclusivo rol `admin` | CRUD completo de gestión de usuarios (ver, crear, editar, eliminar). |
| `pedidos.html` y `api/pedidos.php`| **Protegido** | Sesión requerida (`admin`, `artesano`) | Gestión de pedidos, cambios de estado y encargos. |
| `api/actualizar_pedido.php` | **Protegido** | Sesión requerida (`admin`, `artesano`) | Actualiza estado; reintegra stock si se marca como `'Cancelado'`. |
