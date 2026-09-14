# 004 · Autenticación, Token Bearer & Estado Reactivo del Navbar

**Estado:** en curso

## Qué hace

Conecta la interfaz de usuario con la API de autenticación del backend. Permite a los artesanos y administradores iniciar sesión desde el modal oficial de la barra de navegación (`#modalLogin`) y cerrar sesión desde `.btn-craft-logout` de forma asíncrona (`fetch`), sin recargas bruscas de página. Gestiona el token Bearer en el cliente (`localStorage`), valida la sesión activa contra `GET /api/auth/me.php` y actualiza reactivamente los accesos del Navbar y del menú lateral según el rol (`admin` vs `artesano`).

## Por qué

Actualmente, las vistas del frontend y los controladores de `api/auth/` operan de forma independiente. Este cableado permite que el usuario inicie sesión real, almacene su token de forma persistente y acceda de inmediato a las funcionalidades de administración protegidas por `AuthGuard` y `RoleGuard`, manteniendo la navegación fluida y la experiencia visual "Algodón Nórdico".

## Criterios de aceptación

- [ ] El envío de credenciales en el modal `#modalLogin` realiza una petición asíncrona `POST /api/auth/login.php`.
- [ ] Ante credenciales erróneas o cuenta inactiva (401), muestra feedback visual de error accesible dentro del propio modal, sin recargar la página ni usar diálogos nativos `alert()`.
- [ ] Ante login exitoso (200), almacena el token Bearer y la información de perfil en `localStorage` y cierra el modal automáticamente.
- [ ] El Navbar reacciona de inmediato: sustituye el botón "Iniciar Sesión" por el sello artesanal pespunteado (`.badge-artisan-seal`) con el nombre del usuario y el botón de cierre de sesión (`.btn-craft-logout`).
- [ ] Si el rol es `artesano`, despliega los accesos a Creaciones y Pedidos; si es `admin`, habilita adicionalmente el acceso a Usuarios.
- [ ] Al cargar cualquier página, el cliente verifica el token almacenado invocando `GET /api/auth/me.php`. Si el token expiró o fue revocado (401), limpia `localStorage` y restaura el estado público sin errores en consola.
- [ ] El clic en el botón de logout invoca `POST /api/auth/logout.php`, purga las credenciales locales y, si el usuario se encuentra en una vista administrativa privada (`creaciones.php`, `pedidos.php`, `usuarios.php`), lo redirige al catálogo público (`index.php`).

## Fuera de alcance

- Peticiones del catálogo público y filtros reactivos (Feature 005).
- Mutaciones de creaciones o subida multipart de fotos (Feature 006).
- Creación de nuevos usuarios o reseteo de claves desde el panel (Feature 008).
