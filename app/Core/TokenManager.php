<?php
/**
 * Stateless HMAC-SHA256 Token Manager (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Emite y valida tokens de autenticación Bearer de forma compacta y sin estado.
 * Implementa firma criptográfica HMAC-SHA256 con protección contra ataques de tiempo
 * mediante hash_equals() y caducidad estricta (24 horas por defecto).
 */

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class TokenManager {
    /**
     * Algoritmo de hash criptográfico utilizado
     */
    private const ALGO = 'sha256';

    /**
     * Genera un nuevo Bearer token para un usuario autenticado.
     * 
     * @param array $user Datos del usuario (id, username, rol)
     * @param int|null $customTtl TTL en segundos (opcional, por defecto Config::get('auth.token_ttl'))
     * @return string Token firmado en formato "payloadB64Url.firmaHex"
     * @throws RuntimeException si no hay clave secreta configurada
     */
    public static function generate(array $user, ?int $customTtl = null): string {
        $secret = (string)(Config::get('auth.token_secret') ?? Config::get('auth.jwt_secret', ''));
        if (trim($secret) === '') {
            throw new RuntimeException('No se ha configurado la clave secreta "auth.token_secret" en la configuración.');
        }

        $now = time();
        $ttl = $customTtl ?? (int)(Config::get('auth.token_ttl') ?? Config::get('auth.jwt_ttl_seconds', 86400));

        $payload = [
            'sub'      => (int)($user['id'] ?? 0),
            'username' => (string)($user['username'] ?? ''),
            'rol'      => (string)($user['rol'] ?? 'artesano'),
            'iat'      => $now,
            'exp'      => $now + $ttl,
            'jti'      => bin2hex(random_bytes(16)),
        ];

        $encodedPayload = self::base64UrlEncode((string)json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac(self::ALGO, $encodedPayload, $secret);

        return "{$encodedPayload}.{$signature}";
    }

    /**
     * Valida la autenticidad y vigencia de un token Bearer.
     * 
     * @param string $token Token en formato "payloadB64Url.firmaHex"
     * @return array|null Devuelve el payload del token si es válido y está vigente, o null si fue alterado o expiró
     */
    public static function verify(string $token): ?array {
        $secret = (string)(Config::get('auth.token_secret') ?? Config::get('auth.jwt_secret', ''));
        if (trim($secret) === '') {
            return null;
        }

        $parts = explode('.', trim($token));
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $providedSignature] = $parts;

        // 1. Recalcular la firma esperada
        $expectedSignature = hash_hmac(self::ALGO, $encodedPayload, $secret);

        // 2. Comparación en tiempo constante contra ataques de temporización
        if (!hash_equals($expectedSignature, $providedSignature)) {
            return null;
        }

        // 3. Decodificar el payload
        $json = self::base64UrlDecode($encodedPayload);
        if ($json === null) {
            return null;
        }

        $payload = json_decode($json, true);
        if (!is_array($payload) || !isset($payload['sub'], $payload['exp'], $payload['rol'])) {
            return null;
        }

        // 4. Verificar expiración estricta
        if (self::isExpired($payload)) {
            return null;
        }

        return $payload;
    }

    /**
     * Comprueba si el payload de un token ya ha expirado.
     */
    public static function isExpired(array $payload): bool {
        $exp = (int)($payload['exp'] ?? 0);
        return time() >= $exp;
    }

    /**
     * Calcula los segundos restantes de vida de un token.
     */
    public static function getRemainingTtl(array $payload): int {
        $exp = (int)($payload['exp'] ?? 0);
        $diff = $exp - time();
        return max(0, $diff);
    }

    /**
     * Codifica datos en Base64 URL-Safe sin relleno ('=').
     */
    public static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodifica datos en Base64 URL-Safe.
     */
    public static function base64UrlDecode(string $data): ?string {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        return $decoded !== false ? $decoded : null;
    }
}
