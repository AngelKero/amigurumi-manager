<?php
/**
 * Native CLI Test Suite Helper & Runner (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Marco de pruebas unitarias e integración en PHP nativo sin dependencias de Composer.
 * Proporciona aserciones tipadas, salida formateada con colores ANSI, cálculo de métricas
 * y cliente HTTP curl para pruebas en vivo contra el servidor local.
 */

declare(strict_types=1);

namespace Tests;

// Guardia de seguridad: Restricción absoluta a CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Las suites de prueba solo pueden ejecutarse desde la terminal CLI.\n";
    exit(1);
}

// Cargar el entorno de la aplicación
require_once dirname(__DIR__) . '/app/autoload.php';

class TestHelper {
    private static int $passed = 0;
    private static int $failed = 0;
    private static float $startTime = 0.0;
    private static string $suiteTitle = '';

    // Colores ANSI para terminal
    public const RESET   = "\033[0m";
    public const BOLD    = "\033[1m";
    public const GREEN   = "\033[32m";
    public const RED     = "\033[31m";
    public const YELLOW  = "\033[33m";
    public const BLUE    = "\033[34m";
    public const MAGENTA = "\033[35m";
    public const CYAN    = "\033[36m";
    public const GRAY    = "\033[90m";

    /**
     * Inicia una suite de pruebas con título y registro de tiempo.
     */
    public static function init(string $title): void {
        self::$passed = 0;
        self::$failed = 0;
        self::$startTime = microtime(true);
        self::$suiteTitle = $title;

        echo self::BOLD . self::CYAN . "================================================================================" . self::RESET . PHP_EOL;
        echo self::BOLD . self::CYAN . "  SUITE DE PRUEBAS: " . self::$suiteTitle . self::RESET . PHP_EOL;
        echo self::GRAY . "  Iniciada: " . date('Y-m-d H:i:s') . " | PHP " . PHP_VERSION . " | OS: " . PHP_OS . self::RESET . PHP_EOL;
        echo self::BOLD . self::CYAN . "================================================================================" . self::RESET . PHP_EOL . PHP_EOL;
    }

    /**
     * Imprime un encabezado de sección lógica.
     */
    public static function section(string $name): void {
        echo PHP_EOL . self::BOLD . self::YELLOW . "► SECCIÓN: " . $name . self::RESET . PHP_EOL;
        echo self::GRAY . str_repeat('─', 70) . self::RESET . PHP_EOL;
    }

    /**
     * Aserción general con evaluación booleana.
     */
    public static function assert(bool $condition, string $description, ?string $failureDetail = null): void {
        if ($condition) {
            self::$passed++;
            echo "  " . self::GREEN . "✔ PASS:" . self::RESET . " " . $description . PHP_EOL;
        } else {
            self::$failed++;
            echo "  " . self::RED . self::BOLD . "✖ FAIL:" . self::RESET . " " . $description . PHP_EOL;
            if ($failureDetail !== null) {
                echo "         " . self::RED . "→ " . $failureDetail . self::RESET . PHP_EOL;
            }
        }
    }

    /**
     * Aserción de igualdad no estricta (==).
     */
    public static function assertEquals(mixed $expected, mixed $actual, string $description): void {
        $ok = ($expected == $actual);
        $detail = $ok ? null : sprintf("Esperado: %s, Obtenido: %s", var_export($expected, true), var_export($actual, true));
        self::assert($ok, $description, $detail);
    }

    /**
     * Aserción de identidad estricta (===).
     */
    public static function assertSame(mixed $expected, mixed $actual, string $description): void {
        $ok = ($expected === $actual);
        $detail = $ok ? null : sprintf("Esperado exactamente: %s (%s), Obtenido: %s (%s)", var_export($expected, true), gettype($expected), var_export($actual, true), gettype($actual));
        self::assert($ok, $description, $detail);
    }

    /**
     * Aserción de verdadero.
     */
    public static function assertTrue(mixed $actual, string $description): void {
        self::assertSame(true, $actual, $description);
    }

    /**
     * Aserción de falso.
     */
    public static function assertFalse(mixed $actual, string $description): void {
        self::assertSame(false, $actual, $description);
    }

    /**
     * Aserción de nulidad.
     */
    public static function assertNull(mixed $actual, string $description): void {
        self::assertSame(null, $actual, $description);
    }

    /**
     * Aserción de no nulidad.
     */
    public static function assertNotNull(mixed $actual, string $description): void {
        $ok = ($actual !== null);
        $detail = $ok ? null : "El valor obtenido es estrictamente null";
        self::assert($ok, $description, $detail);
    }

    /**
     * Aserción de que un string contiene un substring.
     */
    public static function assertStringContains(string $needle, string $haystack, string $description): void {
        $ok = str_contains($haystack, $needle);
        $detail = $ok ? null : sprintf("No se encontró '%s' dentro del texto analizado", $needle);
        self::assert($ok, $description, $detail);
    }

    /**
     * Aserción de presencia de clave en un array.
     */
    public static function assertArrayHasKey(string|int $key, array $array, string $description): void {
        $ok = array_key_exists($key, $array);
        $detail = $ok ? null : sprintf("La clave '%s' no existe en el array proporcionado", (string)$key);
        self::assert($ok, $description, $detail);
    }

    /**
     * Cliente HTTP curl nativo para pruebas de integración contra el servidor local.
     * 
     * @param string $method Método HTTP (GET, POST, PUT, DELETE, OPTIONS)
     * @param string $url URL destino (ej. "http://localhost:8000/api/auth/login.php")
     * @param array $headers Encabezados en formato ['Authorization: Bearer ...', 'Content-Type: application/json']
     * @param string|null $body Cuerpo de la petición (JSON o raw)
     * @return array{status: int, headers: array<string, string>, body: string, json: ?array, duration_ms: float}
     */
    public static function curl(string $method, string $url, array $headers = [], ?string $body = null): array {
        $ch = curl_init();
        $responseHeaders = [];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Captura de cabeceras de respuesta
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
            return $len;
        });

        $startTime = microtime(true);
        $responseBody = curl_exec($ch);
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $rawBody = is_string($responseBody) ? $responseBody : '';
        $decodedJson = json_decode($rawBody, true);

        return [
            'status'      => $httpCode,
            'headers'     => $responseHeaders,
            'body'        => $rawBody,
            'json'        => is_array($decodedJson) ? $decodedJson : null,
            'duration_ms' => $durationMs,
            'error'       => $curlError,
        ];
    }

    /**
     * Finaliza la suite de pruebas, emite el resumen consolidado y retorna el código de salida.
     * 
     * @return int 0 si todas las pruebas pasaron, 1 si hubo al menos un fallo.
     */
    public static function summary(): int {
        $duration = round((microtime(true) - self::$startTime) * 1000, 2);
        $total = self::$passed + self::$failed;

        echo PHP_EOL . self::BOLD . self::CYAN . "================================================================================" . self::RESET . PHP_EOL;
        echo self::BOLD . "  RESUMEN DE PRUEBAS: " . self::$suiteTitle . self::RESET . PHP_EOL;
        echo self::GRAY . "--------------------------------------------------------------------------------" . self::RESET . PHP_EOL;
        echo "  Total Aserciones: " . self::BOLD . $total . self::RESET . PHP_EOL;
        echo "  Exitosas:         " . self::GREEN . self::BOLD . self::$passed . self::RESET . PHP_EOL;
        echo "  Fallidas:         " . (self::$failed > 0 ? (self::RED . self::BOLD . self::$failed) : "0") . self::RESET . PHP_EOL;
        echo "  Tiempo Total:     " . self::CYAN . $duration . " ms" . self::RESET . PHP_EOL;
        echo self::BOLD . self::CYAN . "================================================================================" . self::RESET . PHP_EOL;

        if (self::$failed === 0) {
            echo PHP_EOL . self::GREEN . self::BOLD . "  ✔ TODAS LAS PRUEBAS PASARON EXITOSAMENTE (100% OK)" . self::RESET . PHP_EOL . PHP_EOL;
            return 0;
        }

        echo PHP_EOL . self::RED . self::BOLD . "  ✖ HUBO FALLOS EN LA SUITE DE PRUEBAS (" . self::$failed . " FALLOS)" . self::RESET . PHP_EOL . PHP_EOL;
        return 1;
    }
}
