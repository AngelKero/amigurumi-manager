<?php
/**
 * Error Handler - Global Error & Exception Trap (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Captura todos los errores de PHP, excepciones no controladas y errores fatales,
 * purgando los buffers de salida con ob_end_clean() para garantizar CERO FUGAS
 * de HTML y emitiendo una respuesta estandarizada en formato JSON (HTTP 500).
 */

declare(strict_types=1);

namespace App\Core;

class ErrorHandler {
    /**
     * Estado del registro del ErrorHandler
     */
    private static bool $registered = false;

    /**
     * Registra los manejadores globales de PHP.
     */
    public static function register(): void {
        if (self::$registered) {
            return;
        }

        // Configurar directivas base de PHP
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL);

        // Iniciar buffer de salida si no hay ninguno activo
        if (ob_get_level() === 0) {
            ob_start();
        }

        // Manejador de errores estándar de PHP (E_WARNING, E_NOTICE, etc.)
        set_error_handler([self::class, 'handleError']);

        // Manejador de excepciones no capturadas
        set_exception_handler([self::class, 'handleException']);

        // Manejador de cierre para errores fatales (E_ERROR, E_PARSE, etc.)
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * Desregistra los manejadores de PHP (útil en entornos de prueba o CLI).
     */
    public static function unregister(): void {
        if (!self::$registered) {
            return;
        }

        restore_error_handler();
        restore_exception_handler();
        self::$registered = false;
    }

    /**
     * Convierte errores nativos de PHP en ErrorException.
     * 
     * @throws \ErrorException
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool {
        // Respetar el operador de supresión de errores (@)
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Procesa cualquier excepción no capturada.
     */
    public static function handleException(\Throwable $exception): void {
        self::renderJsonError($exception);
    }

    /**
     * Captura errores fatales durante el apagado del script.
     */
    public static function handleShutdown(): void {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            $exception = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );
            self::renderJsonError($exception);
        }
    }

    /**
     * Purga todos los buffers y emite la respuesta JSON 500 estándar.
     */
    public static function renderJsonError(\Throwable $exception): void {
        // 1. Purga total de cualquier buffer de salida para evitar fugas de HTML
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $isDebug = (bool)Config::get('app.debug', false);
        $isCli = (php_sapi_name() === 'cli');

        $statusCode = 500;
        $message = 'Error interno del servidor';

        // Si la excepción especifica un código HTTP válido (400 a 599), respetarlo
        $code = (int)$exception->getCode();
        if ($code >= 400 && $code < 600) {
            $statusCode = $code;
            $message = $exception->getMessage();
        }

        $responsePayload = [
            'exito' => false,
            'error' => [
                'codigo' => $statusCode,
                'mensaje' => $message,
            ]
        ];

        if ($isDebug) {
            $responsePayload['error']['detalles'] = [
                'excepcion' => get_class($exception),
                'mensaje'   => $exception->getMessage(),
                'archivo'   => $exception->getFile(),
                'linea'     => $exception->getLine(),
                'traza'     => explode("\n", $exception->getTraceAsString())
            ];
        }

        if ($isCli) {
            // En modo CLI, emitir el JSON estructurado para trazabilidad
            fwrite(STDERR, json_encode($responsePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
            exit(1);
        }

        // En modo Web: encabezados estrictos de seguridad y respuesta JSON
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }

        echo json_encode($responsePayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit();
    }
}
