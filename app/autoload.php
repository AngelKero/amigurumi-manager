<?php
/**
 * Master Native PSR-4 Autoloader & Bootstrap (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Carga automáticamente todas las clases bajo el namespace App\ desde el
 * directorio app/ sin dependencias de Composer. Inicializa la configuración
 * centralizada, zona horaria y el manejador global de excepciones ErrorHandler.
 */

declare(strict_types=1);

// 1. Registro del Autoloader PSR-4 nativo
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

// 2. Cargar configuración maestra centralizada
\App\Core\Config::load(__DIR__ . '/config.php');

// 3. Configurar zona horaria oficial del sistema
$timezone = \App\Core\Config::get('app.timezone', 'America/Mexico_City');
if (is_string($timezone) && $timezone !== '') {
    date_default_timezone_set($timezone);
}

// 4. Registrar manejador global de errores (cero fugas de HTML)
\App\Core\ErrorHandler::register();

// 5. Cargar utilidades con funciones globales si existen (ej. SvgHelper con svg() y svg_url())
if (is_file(__DIR__ . '/Utils/SvgHelper.php')) {
    require_once __DIR__ . '/Utils/SvgHelper.php';
}
