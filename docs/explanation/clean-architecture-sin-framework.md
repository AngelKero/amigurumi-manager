# Explicación Arquitectónica: Clean Architecture sin Framework en PHP 8.1+

Este documento profundiza en la fundamentación técnica y filosófica que sustenta la arquitectura de **Crochet Manager**: la adopción de los principios de **Clean Architecture** (Arquitectura Limpia) implementados de forma nativa en PHP moderno, prescindiendo completamente de frameworks pesados o gestores de dependencias en el entorno de ejecución (**Zero Composer**).

---

## 1. Motivación y Principio Rector: Cero Dependencias Externas

La gran mayoría de los proyectos PHP modernos delegan su ciclo de vida a microframeworks o stacks monolíticos (Laravel, Symfony, Slim) gestionados mediante Composer. Aunque estos ecosistemas ofrecen velocidad inicial, introducen:
1. Cientos de dependencias transitivas en `vendor/` con riesgos de seguridad en la cadena de suministro.
2. Frecuente rotura de compatibilidad (*breaking changes*) en actualizaciones menores de librerías.
3. Costos de arranque y sobrecarga de memoria por la carga de contenedores de inyección y componentes innecesarios.

**Crochet Manager** demuestra que los estándares nativos de PHP 8.1+ (tipado estricto `declare(strict_types=1)`, propiedades tipadas de solo lectura, enumeraciones y extensiones PDO optimizadas en C) permiten construir un Micro-ERP robusto, seguro y de altísimo rendimiento con **Zero Composer** en producción y cero dependencias de terceros.

---

## 2. La Regla de Dependencias (Dependency Rule)

El núcleo de la arquitectura se rige por la **Dependency Rule**: *las dependencias del código fuente solo pueden apuntar hacia adentro, hacia las capas de mayor nivel de abstracción y lógica de negocio*.

```
       [ HTTP / Web / Controladores REST (api/) ]
                           │
                           ▼
          [ Servicios de Aplicación (app/Services/) ]
                           │
                           ▼
     [ Modelos de Dominio / Entidades (app/Models/) ]
                           ▲
                           │
          [ Repositorios SQL (app/Repositories/) ]
```

### Capas del Sistema:
1. **Controladores REST Delgados (`api/`):**
   - Son adaptadores de entrada extremadamente compactos (máximo 60 líneas de código por controlador).
   - Su única responsabilidad es extraer la petición mediante `App\Core\Request`, delegar la ejecución a la capa de servicios y retornar la respuesta JSON a través de `App\Core\Response`.
   - No contienen lógica de negocio ni sentencias SQL.
2. **Servicios de Aplicación (`app/Services/`):**
   - Orquestan los casos de uso (ej. `CreacionService`, `PedidoService`, `AuthService`).
   - Aplican validaciones de reglas de negocio, gestionan transacciones, calculan precios justos y aplican políticas de autorización (control de acceso IDOR).
   - Son completamente independientes del protocolo de entrega: no interactúan directamente con `$_GET`, `$_POST` o cabeceras HTTP.
3. **Repositorios de Datos (`app/Repositories/`):**
   - Concentran el **100% de las sentencias SQL**. Ninguna otra capa tiene permitido escribir consultas `SELECT`, `INSERT` o `UPDATE`.
   - Interactúan directamente con la base de datos a través de sentencias preparadas de PDO, aislando los detalles de persistencia.
4. **Núcleo y Utilidades (`app/Core/`, `app/Utils/`):**
   - Proveen las piezas fundamentales: conexión singleton a base de datos (`Database`), enrutamiento de errores (`ErrorHandler`), gestión de tokens (`TokenManager`) y utilidades monetarias (`CurrencyHelper`).

---

## 3. Autoloader Nativo PSR-4

Para lograr la carga automática de clases sin depender del autoloader generado por Composer:
- El archivo `app/bootstrap.php` registra un autoloader nativo con `spl_autoload_register()`.
- Implementa la especificación **PSR-4**, asociando el prefijo de espacio de nombres `App\` directamente al directorio físico `app/`:

```php
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
```

Este enfoque garantiza que cualquier nueva clase o servicio añadido al directorio `app/` esté inmediatamente disponible sin necesidad de ejecutar comandos de regeneración como `composer dump-autoload`.

---

## 4. Beneficios Operativos y de Mantenimiento

1. **Despliegue Instantáneo:** El proyecto se ejecuta en cualquier servidor Linux/macOS/Windows con solo tener instalado PHP y SQLite, sin procesos de compilación o instalación previa de paquetes.
2. **Auditoría Transparente:** La totalidad del código fuente que se ejecuta en el servidor es propiedad del proyecto y se encuentra versionada en el repositorio.
3. **Mínimo Consumo de Recursos:** Tiempos de respuesta de sub-milisegundo en peticiones a la API REST gracias a la ausencia de capas intermedias complejas.
4. **Mantenibilidad a Largo Plazo:** Al apoyarse exclusivamente en la biblioteca estándar de PHP, el código no sufrirá obsolescencia por abandono de frameworks externos.
