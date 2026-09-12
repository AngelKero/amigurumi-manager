<?php
/**
 * HTTP Response Emitter & CORS Manager (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Estandariza la emisión de respuestas en formato JSON bajo una envolvente
 * unificada de éxito o error, gestiona los códigos de estado HTTP y resuelve
 * de forma centralizada las peticiones preflight CORS (OPTIONS) con código 204.
 */

declare(strict_types=1);

namespace App\Core;

class Response {
    /**
     * Determina si Response::json debe invocar exit(). Útil para tests unitarios.
     */
    private static bool $exitOnSend = true;

    /**
     * Almacén de la última respuesta enviada para aserciones de testing
     */
    private static ?array $lastPayload = null;
    private static int $lastStatusCode = 200;

    /**
     * Configura si la clase debe terminar la ejecución del script tras enviar la respuesta.
     */
    public static function setExitOnSend(bool $exit): void {
        self::$exitOnSend = $exit;
    }

    /**
     * Recupera el último payload emitido (para pruebas).
     */
    public static function getLastPayload(): ?array {
        return self::$lastPayload;
    }

    /**
     * Recupera el último código HTTP emitido (para pruebas).
     */
    public static function getLastStatusCode(): int {
        return self::$lastStatusCode;
    }

    /**
     * Gestiona las cabeceras CORS y resuelve peticiones preflight OPTIONS.
     */
    public static function handleCors(): void {
        $corsConfig = Config::get('cors', []);
        $allowedOrigins = $corsConfig['allowed_origins'] ?? ['*'];
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        if (in_array('*', $allowedOrigins, true) || in_array($origin, $allowedOrigins, true)) {
            $allowOrigin = $origin === '*' ? '*' : $origin;
        } else {
            $allowOrigin = $allowedOrigins[0] ?? '*';
        }

        $methods = implode(', ', $corsConfig['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
        $headers = implode(', ', $corsConfig['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With']);
        $maxAge  = (string)($corsConfig['max_age'] ?? 86400);

        if (!headers_sent()) {
            header("Access-Control-Allow-Origin: {$allowOrigin}");
            header("Access-Control-Allow-Methods: {$methods}");
            header("Access-Control-Allow-Headers: {$headers}");
            header("Access-Control-Max-Age: {$maxAge}");
        }

        // Si la petición es preflight OPTIONS, terminar inmediatamente con 204 No Content
        if (Request::method() === 'OPTIONS') {
            http_response_code(204);
            if (self::$exitOnSend) {
                exit();
            }
        }
    }

    /**
     * Emite una respuesta JSON estandarizada con código de estado y encabezados.
     */
    public static function json(mixed $payload, int $statusCode = 200, array $headers = []): void {
        self::$lastStatusCode = $statusCode;
        self::$lastPayload = is_array($payload) ? $payload : ['data' => $payload];

        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');

            foreach ($headers as $key => $val) {
                header("{$key}: {$val}");
            }
        }

        $jsonOutput = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo $jsonOutput;

        if (self::$exitOnSend) {
            exit();
        }
    }

    /**
     * Emite una respuesta exitosa con envolvente estándar.
     * 
     * @param mixed $data Datos principales devueltos (objeto, array o valor escalar)
     * @param string $message Mensaje descriptivo de la operación
     * @param int $statusCode Código HTTP (predeterminado 200)
     * @param array $extra Campos adicionales opcionales a incluir en el nivel raíz (ej. paginacion)
     */
    public static function success(
        mixed $data = null,
        string $message = 'Operación exitosa',
        int $statusCode = 200,
        array $extra = []
    ): void {
        $payload = array_merge([
            'exito'   => true,
            'mensaje' => $message,
            'datos'   => $data,
        ], $extra);

        self::json($payload, $statusCode);
    }

    /**
     * Emite una respuesta de error con envolvente estándar.
     * 
     * @param string $message Mensaje descriptivo del error
     * @param int $statusCode Código de estado HTTP de error (400, 401, 403, 404, 422, etc.)
     * @param mixed $details Detalles específicos de validación u origen del error
     */
    public static function error(
        string $message = 'Error en la solicitud',
        int $statusCode = 400,
        mixed $details = null
    ): void {
        $errorBlock = [
            'codigo'  => $statusCode,
            'mensaje' => $message,
        ];

        if ($details !== null) {
            $errorBlock['detalles'] = $details;
        }

        $payload = [
            'exito' => false,
            'error' => $errorBlock,
        ];

        self::json($payload, $statusCode);
    }
}
