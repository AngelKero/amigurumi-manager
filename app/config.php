<?php
/**
 * Master Application Configuration
 * Crochet Manager Micro-ERP & Collaborative Catalog
 */

declare(strict_types=1);

// Guardia de seguridad: Bloquear acceso HTTP directo como script de entrada
if (php_sapi_name() !== 'cli' && basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'config.php') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'exito' => false,
        'error' => [
            'codigo' => 403,
            'mensaje' => 'Acceso denegado: este archivo de configuración no es accesible públicamente.'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

return [
    'app' => [
        'name' => 'Crochet Manager',
        'env' => 'development', // 'development' | 'production'
        'debug' => true,
        'timezone' => 'America/Mexico_City',
        'url' => 'http://localhost:8000',
    ],
    'auth' => [
        // Clave secreta para firma criptográfica HMAC-SHA256 de Bearer Tokens
        'token_secret' => 'crochet_artisan_secret_key_change_in_prod_2026_nordic_cotton',
        'jwt_secret' => 'crochet_artisan_secret_key_change_in_prod_2026_nordic_cotton',
        'token_ttl' => 86400, // 24 horas en segundos
        'jwt_ttl_seconds' => 86400,
        'algo' => 'sha256',
    ],
    'database' => [
        'path' => realpath(__DIR__ . '/../database/database.sqlite') ?: (__DIR__ . '/../database/database.sqlite'),
        'seed_file' => realpath(__DIR__ . '/../database/seed.sql') ?: (__DIR__ . '/../database/seed.sql'),
    ],
    'uploads' => [
        'dir' => realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads'),
        'max_bytes' => 5 * 1024 * 1024, // 5MB
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
    'pagination' => [
        'creaciones_default' => 12,
        'creaciones_max' => 48,
        'pedidos_default' => 20,
        'pedidos_max' => 100,
        'usuarios_default' => 20,
        'usuarios_max' => 100,
    ],
    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 86400,
    ],
];
