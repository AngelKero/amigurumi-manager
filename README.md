# 🧶 Crochet Manager | Micro-ERP & Catálogo Textil Artesanal

Sistema web integral de comercio artesanal y gestión de taller (**Micro-ERP**) diseñado especialmente para creadores y artesanos de crochet integral (amigurumis, prendas de vestir, bolsos/accesorios, artículos del hogar y línea bebé). Permite controlar inventarios físicos en tiempo real, calcular costos de material y rentabilidad por hora, gestionar encargos de clientes y administrar el catálogo público bajo la identidad visual de **"Algodón Nórdico"**.

---

## 🚀 Inicio Rápido con un Solo Comando

Para levantar el servidor web local de desarrollo, abre una terminal en la raíz del proyecto y ejecuta:

```bash
php -S localhost:8000
```

Luego, abre tu navegador web favorito en:
👉 **[http://localhost:8000](http://localhost:8000)**

*(Opcionalmente, si utilizas otra dirección local como `127.0.0.1:8000`, también es totalmente compatible).*

---

## 🛠️ Inicialización de Base de Datos (SQLite)

La base de datos SQLite preconfigurada y protegida se encuentra en `database/database.sqlite`.

Si en algún momento deseas reconstruir la base de datos o restablecer los datos iniciales de prueba con sus restricciones de integridad relacional, ejecuta el instalador CLI seguro:

```bash
php setup.php
```

> **Nota de Seguridad:** `setup.php` está blindado para ejecutarse **únicamente desde la terminal (CLI)**. Los intentos de ejecución remota o vía navegador web son rechazados automáticamente para proteger la integridad de los datos.

---

## 🔑 Credenciales de Acceso (Panel del Artesano)

Para acceder a las herramientas administrativas del taller textil (Nueva Creación, Gestión de Pedidos, Inventario y Márgenes):

- **Usuario:** `admin`
- **Contraseña:** `admin123`
- **Rol:** `admin` (Artesano Principal Titular)

*El inicio de sesión se realiza cómodamente desde el botón "Iniciar Sesión" en la barra de navegación superior o haciendo clic en el badge del artesano autenticado.*

---

## 🏛️ Arquitectura del Sistema (Clean Code & Modular)

El proyecto sigue una arquitectura desacoplada basada en la habilidad `clean-code-architect` y los principios SOLID, sin dependencias de frameworks externos:

```
proyecto-web/
├── views/                          # Sistema de plantillas y componentes modulares PHP
│   ├── layouts/main.php            # Layout maestro (<head>, scripts, decoraciones y modals)
│   ├── components/                 # Barra de navegación, footer, tarjetas y modales
│   └── pages/                      # Contenido específico de cada vista (catalogo, creaciones, etc.)
├── src/                            # Código fuente modular (Backend y Activos Frontend)
│   ├── css/                        # Estilos modulares organizados por capas ITCSS (Algodón Nórdico)
│   ├── js/                         # Scripts cliente desacoplados en ES Modules
│   ├── Utils/                      # Helpers universales (CurrencyHelper, SvgHelper, etc.)
│   ├── Core/                       # Autoloader y Formateador de Respuestas JSON
│   ├── Database/                   # Conexión Singleton PDO SQLite con Foreign Keys
│   ├── Middleware/                 # Guardián de sesión y autorización por rol
│   ├── Repositories/               # Capa DAO (100% del SQL aislado)
│   └── Services/                   # Reglas de negocio (anti-spoofing, subida de fotos y unlink)
├── api/                            # Fachada de Controladores HTTP livianos (JSON APIs)
│   ├── auth/                       # login.php, logout.php, me.php
│   ├── creaciones/                 # leer.php, crear.php, actualizar.php, eliminar.php
│   ├── pedidos/                    # solicitar.php, listar.php, actualizar_estado.php
│   └── usuarios/                   # index.php (CRUD de administradores)
├── database/                       # Almacenamiento físico SQLite protegido (seed.sql, database.sqlite)
├── uploads/                        # Directorio para fotografías reales de creaciones
└── docs/                           # Documentación técnica de arquitectura y pruebas
```

---

## 🎨 Sistema de Diseño: "Algodón Nórdico"

El frontend implementa una estética textil escandinava suave y acogedora:
- **Paleta de Color:** Ciruela Nórdico (`#8E5B74`), Abeto Glaciar (`#52857C` con alto contraste `#235048`), Miel Nórdica (`#D99C52`) y Lienzo Porcelana (`#F8F9FB`).
- **Detallado Textil:** Bordes pespunteados (`.card-stitched`), cintas hilvanadas para migas de pan (`.breadcrumb-craft-ribbon`), etiquetas de cuidado tejidas (`.badge-textile-tag`), marcos paspartú acolchados para fotografías y sello oficial del taller artesanal.
- **Tipografía:** *Fraunces* (títulos de display y cabeceras de impacto), *Outfit* (subtítulos y cifras) y *Plus Jakarta Sans* (lectura, tablas y formularios).
- **Accesibilidad:** Contraste superior a 6.2:1 (superando WCAG 2.1 AA) y navegación mobile-first.

---

## 📋 Requisitos del Sistema

- **PHP 8.0** o superior con extensiones estándar `pdo_sqlite` y `mbstring`.
- **Navegador web moderno** (Chrome, Firefox, Safari o Edge) con soporte para JavaScript ES6+.
- Cero dependencias externas de Composer o Node.js requeridas en tiempo de ejecución.

