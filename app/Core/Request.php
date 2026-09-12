<?php
/**
 * HTTP Request Abstraction (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Encapsula el acceso seguro a parámetros de entrada (GET, POST, JSON en php://input),
 * archivos adjuntos, cabeceras HTTP normalizadas y extracción de Bearer Tokens
 * con compatibilidad para entornos FastCGI / Apache.
 */

declare(strict_types=1);

namespace App\Core;

class Request {
    /**
     * Caché en memoria del cuerpo JSON parseado
     */
    private static ?array $jsonCache = null;

    /**
     * Usuario autenticado en el ciclo de la petición
     */
    private static ?array $authenticatedUser = null;

    /**
     * Cabeceras mock para pruebas unitarias
     */
    private static array $mockHeaders = [];

    /**
     * Obtiene un parámetro de la cadena de consulta ($_GET) o todo el array.
     */
    public static function get(?string $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Obtiene un parámetro enviado por formulario ($_POST) o todo el array.
     */
    public static function post(?string $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtiene un parámetro del cuerpo JSON (php://input) o todo el payload asociativo.
     */
    public static function json(?string $key = null, mixed $default = null): mixed {
        if (self::$jsonCache === null) {
            $rawInput = (string)file_get_contents('php://input');
            if ($rawInput !== '') {
                $decoded = json_decode($rawInput, true);
                self::$jsonCache = is_array($decoded) ? $decoded : [];
            } else {
                self::$jsonCache = [];
            }
        }

        if ($key === null) {
            return self::$jsonCache;
        }

        return self::$jsonCache[$key] ?? $default;
    }

    /**
     * Obtiene un parámetro buscando secuencialmente en JSON, POST y GET.
     */
    public static function input(?string $key = null, mixed $default = null): mixed {
        if ($key === null) {
            $json = self::json() ?? [];
            return array_merge($_GET, $_POST, $json);
        }

        $jsonVal = self::json($key);
        if ($jsonVal !== null) {
            return $jsonVal;
        }

        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }

    /**
     * Obtiene información de un archivo subido ($_FILES).
     */
    public static function file(string $key): ?array {
        return $_FILES[$key] ?? null;
    }

    /**
     * Obtiene una cabecera HTTP normalizada insensible a mayúsculas/minúsculas.
     */
    public static function header(string $name, ?string $default = null): ?string {
        $normalizedName = strtolower(trim($name));

        // 1. Revisar cabeceras mock para tests
        if (isset(self::$mockHeaders[$normalizedName])) {
            return self::$mockHeaders[$normalizedName];
        }

        // 2. Soporte especial para Authorization (FastCGI / Apache / PHP CLI)
        if ($normalizedName === 'authorization') {
            if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
                return $_SERVER['HTTP_AUTHORIZATION'];
            }
            if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                foreach ($headers as $k => $v) {
                    if (strtolower($k) === 'authorization') {
                        return $v;
                    }
                }
            }
        }

        // 3. Búsqueda en $_SERVER
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($_SERVER[$serverKey])) {
            return (string)$_SERVER[$serverKey];
        }

        // Casos especiales directos sin prefijo HTTP_
        $directKeys = [
            'content-type'   => 'CONTENT_TYPE',
            'content-length' => 'CONTENT_LENGTH',
        ];
        if (isset($directKeys[$normalizedName], $_SERVER[$directKeys[$normalizedName]])) {
            return (string)$_SERVER[$directKeys[$normalizedName]];
        }

        return $default;
    }

    /**
     * Extrae el Bearer token de la cabecera Authorization.
     * Ejemplo: "Bearer eyJhbGciOi..." -> "eyJhbGciOi..."
     */
    public static function bearerToken(): ?string {
        $header = self::header('Authorization');
        if ($header !== null && preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Obtiene el método HTTP en mayúsculas (GET, POST, PUT, DELETE, OPTIONS).
     */
    public static function method(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Obtiene la URI de la petición.
     */
    public static function uri(): string {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Determina si la petición declara Content-Type application/json.
     */
    public static function isJson(): bool {
        $contentType = self::header('Content-Type', '');
        return stripos($contentType ?? '', 'application/json') !== false;
    }

    /**
     * Almacena el usuario autenticado para la petición activa.
     */
    public static function setUser(array $user): void {
        self::$authenticatedUser = $user;
    }

    /**
     * Recupera el usuario autenticado asociado a la petición.
     */
    public static function user(): ?array {
        return self::$authenticatedUser;
    }

    /**
     * Reinicia el estado estático de la petición (útil para pruebas unitarias).
     */
    public static function reset(): void {
        self::$jsonCache = null;
        self::$authenticatedUser = null;
        self::$mockHeaders = [];
    }

    /**
     * Permite inyectar un payload JSON mock (para testing en CLI).
     */
    public static function setJsonPayload(array $payload): void {
        self::$jsonCache = $payload;
    }

    /**
     * Permite inyectar cabeceras mock (para testing en CLI).
     */
    public static function setMockHeader(string $name, string $value): void {
        self::$mockHeaders[strtolower(trim($name))] = $value;
    }
}
