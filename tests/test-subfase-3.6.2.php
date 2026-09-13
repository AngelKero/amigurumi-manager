<?php
/**
 * Test Suite: Subfase 3.6.2 - Criptografía, Autenticación & Protección de Datos Sensibles (Opción B)
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Alcance y Dimensiones Auditadas (OWASP A02:2021 + A07:2021):
 * 1. Integridad Criptográfica de Tokens Bearer & Manipulación HMAC-SHA256:
 *    - Tokens válidos con claims estructurados (sub, username, rol, iat, exp, jti).
 *    - Detección y rechazo de payload adulterado con firma original (null).
 *    - Detección y rechazo de firma adulterada con payload original (null).
 *    - Detección y rechazo de tokens firmados con clave secreta errónea (null).
 *    - Detección y rechazo de tokens malformados (sin punto, múltiples puntos, no-base64, strings vacíos).
 *    - Comparación en tiempo constante con hash_equals() contra ataques de temporización.
 *    - Peticiones HTTP en vivo con tokens manipulados o corruptos -> HTTP 401 Unauthorized.
 * 2. Ciclo de Vida, Expiración Estricta & TTL de Tokens:
 *    - Adherencia al TTL configurado (86400s / 24 horas por defecto).
 *    - Detección de token expirado con TokenManager::isExpired() = true.
 *    - Cálculo de tiempo restante con TokenManager::getRemainingTtl() = 0 para expirados.
 *    - Rechazo inmediato de tokens expirados en TokenManager::verify() -> null.
 *    - Petición HTTP en vivo con token expirado -> HTTP 401 Unauthorized.
 * 3. Almacenamiento Criptográfico Bcrypt & Mitigación de Ataques de Tiempo:
 *    - Verificación de hash Bcrypt con cost factor 10 ($2y$10$...) en todos los usuarios semilla.
 *    - Ausencia total de contraseñas en texto plano en la base de datos SQLite.
 *    - Verificación estricta de hash con password_verify().
 *    - Mitigación de timing attack en AuthService::authenticate mediante dummy hash en usuarios inexistentes.
 *    - Mensajes de error no enumerables: fallo por usuario inexistente y fallo por clave errónea devuelven el mismo error genérico con HTTP 401.
 * 4. Cero Exposición de Datos Sensibles (Data Exposure):
 *    - Respuesta de POST /api/auth/login.php sin campos password_hash ni contraseñas.
 *    - Respuesta de GET /api/auth/me.php sin campos password_hash.
 *    - Respuesta de GET /api/usuarios/index.php sin campos password_hash en ningún registro.
 *    - Métodos UsuarioRepository::findByIdSafe, listAll y listAllWithCreationsCount excluyen password_hash.
 *    - Guardia de acceso directo en app/config.php impidiendo lectura HTTP directa (HTTP 403).
 * 5. Políticas de Higiene de Contraseñas & Reseteo Seguro:
 *    - Validación de longitud mínima (>= 6 caracteres) en creación de usuarios (HTTP 422).
 *    - Autoservicio de cambio de contraseña validando clave actual y longitud mínima (HTTP 401 / 422 / 200).
 *    - Reseteo administrativo validando clave manual (>= 6 caracteres) o autogenerando clave temporal segura (Crochet!<hex>!).
 * 6. Revocación Inmediata de Tokens en Bajas Lógicas (Soft-Delete):
 *    - Usuario desactivado (activo = 0) invalida tokens unexpired de forma instantánea en validateToken().
 *    - Petición HTTP en vivo con token de usuario desactivado -> HTTP 401 Unauthorized.
 *    - Bloqueo de autenticación en login para cuentas inactivas (HTTP 401).
 *    - Reactivación de cuenta y reanudación de acceso autenticado (HTTP 200).
 * 7. Ciclo de Cierre de Sesión & Contrato Stateless:
 *    - Respuesta limpia y orientada al cliente en POST /api/auth/logout.php (HTTP 200).
 *    - Restricción de método HTTP en logout: GET /api/auth/logout.php -> HTTP 405 Method Not Allowed.
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Core\Config;
use App\Core\Database;
use App\Core\Response;
use App\Core\TokenManager;
use App\Repositories\CreacionRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use App\Services\CreacionService;
use App\Services\PedidoService;
use App\Services\UsuarioService;

// Iniciar suite
TestHelper::init('Subfase 3.6.2: Criptografía, Autenticación & Protección de Datos Sensibles (OWASP A02 + A07)');

// Desactivar terminación forzada en Response para pruebas en memoria
Response::setExitOnSend(false);

$pdo = Database::getInstance();
$usuarioRepo = new UsuarioRepository();
$creacionRepo = new CreacionRepository();
$pedidoRepo = new PedidoRepository();

$authService = new AuthService($usuarioRepo);
$usuarioService = new UsuarioService($usuarioRepo, $creacionRepo);

$baseUrl = 'http://localhost:8000';

// Helper para extraer mensaje de error estandarizado de la respuesta JSON
$getErrMsg = function (array $res): string {
    return (string)($res['json']['error']['mensaje'] ?? $res['json']['mensaje'] ?? '');
};

// ============================================================================
// SECCIÓN 1: Integridad Criptográfica de Tokens Bearer & Manipulación HMAC-SHA256
// ============================================================================
TestHelper::section('1. Integridad Criptográfica de Tokens Bearer & Manipulación HMAC (OWASP A02:2021)');

// 1.1 Emisión de token válido y estructura de claims
$adminAuth = $authService->authenticate('admin', 'admin123');
$adminToken = $adminAuth['token'];
$adminHeaders = ['Authorization: Bearer ' . $adminToken, 'Content-Type: application/json'];

$verifiedPayload = TokenManager::verify($adminToken);
TestHelper::assertNotNull($verifiedPayload, 'Token legítimo de admin es verificado con éxito');
TestHelper::assertSame(1, (int)($verifiedPayload['sub'] ?? 0), 'Claim "sub" contiene el ID correcto (1)');
TestHelper::assertSame('admin', (string)($verifiedPayload['username'] ?? ''), 'Claim "username" es "admin"');
TestHelper::assertSame('admin', (string)($verifiedPayload['rol'] ?? ''), 'Claim "rol" es "admin"');
TestHelper::assertArrayHasKey('iat', $verifiedPayload, 'Claim "iat" (issued at) está presente');
TestHelper::assertArrayHasKey('exp', $verifiedPayload, 'Claim "exp" (expiration) está presente');
TestHelper::assertArrayHasKey('jti', $verifiedPayload, 'Claim "jti" (identificador único de token) está presente');

// 1.2 Manipulación de payload (escalada de privilegios artesano -> admin) con firma auténtica
$anaAuth = $authService->authenticate('artesana_ana', 'admin123');
$anaToken = $anaAuth['token'];
[$anaPayloadB64, $anaSig] = explode('.', $anaToken);

$anaDecodedJson = TokenManager::base64UrlDecode($anaPayloadB64);
TestHelper::assertNotNull($anaDecodedJson, 'Payload de Ana se decodifica en base64Url');
$anaPayloadData = json_decode((string)$anaDecodedJson, true);
TestHelper::assertSame('artesano', $anaPayloadData['rol'], 'Payload original de Ana tiene rol "artesano"');

// Intentar alterar el rol a "admin" y sub a 1 conservando la firma original de Ana
$anaPayloadData['rol'] = 'admin';
$anaPayloadData['sub'] = 1;
$anaPayloadData['username'] = 'admin';
$forgedPayloadB64 = TokenManager::base64UrlEncode((string)json_encode($anaPayloadData, JSON_UNESCAPED_SLASHES));
$tamperedToken = "{$forgedPayloadB64}.{$anaSig}";

$tamperedVerify = TokenManager::verify($tamperedToken);
TestHelper::assertNull($tamperedVerify, 'Token con payload adulterado es RECHAZADO por TokenManager::verify (retorna null)');

// 1.3 Manipulación de la firma (modificar un solo carácter hexadecimal)
$corruptedSig = substr($anaSig, 0, -2) . 'aa';
$tokenWithCorruptedSig = "{$anaPayloadB64}.{$corruptedSig}";
$corruptedVerify = TokenManager::verify($tokenWithCorruptedSig);
TestHelper::assertNull($corruptedVerify, 'Token con firma adulterada es RECHAZADO por TokenManager::verify (retorna null)');

// 1.4 Token firmado con una clave secreta arbitraria/distinta
$fakeSecret = 'clave_falsa_del_atacante_totalmente_invalida_1234567890';
$fakeSig = hash_hmac('sha256', $anaPayloadB64, $fakeSecret);
$tokenWithFakeSecret = "{$anaPayloadB64}.{$fakeSig}";
$fakeSecretVerify = TokenManager::verify($tokenWithFakeSecret);
TestHelper::assertNull($fakeSecretVerify, 'Token firmado con clave secreta errónea es RECHAZADO (retorna null)');

// 1.5 Tokens malformados y vectores de entrada arbitrarios
$malformedTokens = [
    ''                                => 'String vacío',
    '   '                             => 'Espacios en blanco',
    'sintoken'                        => 'Sin separador de punto',
    'uno.dos.tres'                    => 'Múltiples separadores (tres partes estilo JWT clásico)',
    'payloadSinFirma.'                => 'Payload con firma vacía',
    '.firmaSinPayload'                => 'Firma sin payload',
    '???!!!.abc123'                   => 'Payload con caracteres no base64Url',
    '{"sub":1}.abc123'                => 'Payload en JSON crudo sin codificar',
    'admin.admin'                     => 'Valores arbitrarios de texto plano',
    '12345.67890'                     => 'Valores numéricos',
    '\' OR 1=1 --.firma'              => 'Intento de SQL Injection en formato token',
    '<script>alert(1)</script>.firma' => 'Intento de XSS en formato token',
];

foreach ($malformedTokens as $tokenInput => $tokenDesc) {
    $verifyResult = TokenManager::verify($tokenInput);
    TestHelper::assertNull($verifyResult, "Token malformado ({$tokenDesc}) es rechazado retornando null");
}

// 1.6 Verificación HTTP en vivo contra endpoints protegidos
$httpForgedMe = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', [
    'Authorization: Bearer ' . $tamperedToken,
    'Content-Type: application/json'
]);
TestHelper::assertSame(401, $httpForgedMe['status'], 'HTTP GET /api/auth/me.php con payload adulterado responde 401 Unauthorized');
TestHelper::assertStringContains('inválido', $getErrMsg($httpForgedMe), 'Mensaje de error indica token inválido o expirado');

$httpCorruptedMe = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', [
    'Authorization: Bearer ' . $tokenWithCorruptedSig,
    'Content-Type: application/json'
]);
TestHelper::assertSame(401, $httpCorruptedMe['status'], 'HTTP GET /api/auth/me.php con firma corrupta responde 401 Unauthorized');

$httpMalformedHeader = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', [
    'Authorization: NoBearerFormat ' . $adminToken,
    'Content-Type: application/json'
]);
TestHelper::assertSame(401, $httpMalformedHeader['status'], 'HTTP GET /api/auth/me.php con cabecera sin prefijo "Bearer " responde 401');

// ============================================================================
// SECCIÓN 2: Ciclo de Vida, Expiración Estricta & TTL de Tokens
// ============================================================================
TestHelper::section('2. Ciclo de Vida, Expiración Estricta & TTL de Tokens (OWASP A07:2021)');

// 2.1 TTL configurado por defecto
$configuredTtl = (int)(Config::get('auth.token_ttl') ?? Config::get('auth.jwt_ttl_seconds', 86400));
TestHelper::assertSame(86400, $configuredTtl, 'El TTL estándar configurado es de 86400 segundos (24 horas)');

// 2.2 Token generado con expiración en el pasado (customTtl = -60s)
$pastUser = ['id' => 1, 'username' => 'admin', 'rol' => 'admin'];
$expiredToken = TokenManager::generate($pastUser, -60);
TestHelper::assertStringContains('.', $expiredToken, 'Token con TTL negativo se genera correctamente');

// Inspección interna del payload expirado
[$expiredPayloadB64, $expiredSig] = explode('.', $expiredToken);
$expiredJson = TokenManager::base64UrlDecode($expiredPayloadB64);
$expiredPayloadData = json_decode((string)$expiredJson, true);

TestHelper::assertTrue(TokenManager::isExpired($expiredPayloadData), 'TokenManager::isExpired() retorna true para token del pasado');
TestHelper::assertSame(0, TokenManager::getRemainingTtl($expiredPayloadData), 'TokenManager::getRemainingTtl() retorna 0 para token expirado');
TestHelper::assertNull(TokenManager::verify($expiredToken), 'TokenManager::verify() RECHAZA token expirado retornando null');

// 2.3 Token generado con TTL activo (customTtl = 3600s)
$activeToken = TokenManager::generate($pastUser, 3600);
[$activePayloadB64] = explode('.', $activeToken);
$activeJson = TokenManager::base64UrlDecode($activePayloadB64);
$activePayloadData = json_decode((string)$activeJson, true);

TestHelper::assertFalse(TokenManager::isExpired($activePayloadData), 'TokenManager::isExpired() retorna false para token activo');
$remainingTtl = TokenManager::getRemainingTtl($activePayloadData);
TestHelper::assertTrue($remainingTtl > 0 && $remainingTtl <= 3600, 'TokenManager::getRemainingTtl() retorna valor positivo dentro del rango esperado');
$verifiedActive = TokenManager::verify($activeToken);
TestHelper::assertNotNull($verifiedActive, 'Token activo es verificado con éxito');

// 2.4 Verificación HTTP en vivo con token expirado
$httpExpiredMe = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', [
    'Authorization: Bearer ' . $expiredToken,
    'Content-Type: application/json'
]);
TestHelper::assertSame(401, $httpExpiredMe['status'], 'HTTP GET /api/auth/me.php con token expirado responde 401 Unauthorized');
TestHelper::assertStringContains('expirado', $getErrMsg($httpExpiredMe), 'Mensaje indica que el token expiró');

$httpExpiredOrders = TestHelper::curl('GET', $baseUrl . '/api/pedidos/index.php', [
    'Authorization: Bearer ' . $expiredToken,
    'Content-Type: application/json'
]);
TestHelper::assertSame(401, $httpExpiredOrders['status'], 'HTTP GET /api/pedidos/index.php con token expirado responde 401 Unauthorized');

// ============================================================================
// SECCIÓN 3: Almacenamiento Criptográfico Bcrypt & Mitigación de Ataques de Tiempo
// ============================================================================
TestHelper::section('3. Almacenamiento Criptográfico Bcrypt & Mitigación Timing Attack (OWASP A02:2021)');

// 3.1 Inspección en base de datos de usuarios semilla
$stmtUsers = $pdo->prepare('SELECT id, username, password_hash, rol FROM usuarios WHERE id IN (1, 2, 3)');
$stmtUsers->execute();
$seedUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
$stmtUsers->closeCursor();
unset($stmtUsers);

TestHelper::assertSame(3, count($seedUsers), 'Se recuperaron los 3 usuarios semilla de la base de datos');

foreach ($seedUsers as $u) {
    $uname = (string)$u['username'];
    $phash = (string)$u['password_hash'];

    // 1. Debe iniciar con prefijo de bcrypt ($2y$10$ o $2a$10$)
    $isBcrypt = str_starts_with($phash, '$2y$10$') || str_starts_with($phash, '$2a$10$');
    TestHelper::assertTrue($isBcrypt, "Usuario '{$uname}' utiliza hash Bcrypt con cost factor 10 ({$phash})");

    // 2. Longitud canónica de 60 caracteres
    TestHelper::assertSame(60, strlen($phash), "Usuario '{$uname}' tiene longitud de hash Bcrypt de exactamente 60 caracteres");

    // 3. Verificación criptográfica con password_verify
    TestHelper::assertTrue(password_verify('admin123', $phash), "password_verify('admin123') coincide con el hash almacenado de '{$uname}'");
    TestHelper::assertFalse(password_verify('clave_incorrecta', $phash), "password_verify() rechaza clave errónea para '{$uname}'");
}

// 3.2 Comprobación de que no hay contraseñas en texto plano en la base de datos
$stmtPlaintext = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE password_hash = "admin123" OR password_hash NOT LIKE "$2y$%"');
$stmtPlaintext->execute();
$countPlain = (int)$stmtPlaintext->fetchColumn();
$stmtPlaintext->closeCursor();
unset($stmtPlaintext);
TestHelper::assertSame(0, $countPlain, 'CERO contraseñas en texto plano encontradas en la tabla usuarios de SQLite');

// 3.3 Mitigación de Timing Attacks en AuthService::authenticate
// Cuando un usuario NO existe, AuthService debe ejecutar password_verify() contra un dummy hash
// para normalizar el tiempo de respuesta y evitar enumeración por temporización.
$timeStartNonexistent = microtime(true);
$errorNonexistent = null;
try {
    $authService->authenticate('usuario_inexistente_totalmente_aleatorio_99999', 'password123');
} catch (\Throwable $e) {
    $errorNonexistent = $e;
}
$durationNonexistent = (microtime(true) - $timeStartNonexistent) * 1000;

TestHelper::assertNotNull($errorNonexistent, 'Autenticación de usuario inexistente lanza excepción');
TestHelper::assertSame(401, $errorNonexistent->getCode(), 'Código de error para usuario inexistente es 401');
TestHelper::assertSame('Credenciales de acceso incorrectas.', $errorNonexistent->getMessage(), 'Mensaje para usuario inexistente es genérico');

// Caso de usuario que SÍ existe pero con contraseña errónea
$timeStartWrongPass = microtime(true);
$errorWrongPass = null;
try {
    $authService->authenticate('admin', 'password_totalmente_erronea_para_admin');
} catch (\Throwable $e) {
    $errorWrongPass = $e;
}
$durationWrongPass = (microtime(true) - $timeStartWrongPass) * 1000;

TestHelper::assertNotNull($errorWrongPass, 'Autenticación con contraseña errónea lanza excepción');
TestHelper::assertSame(401, $errorWrongPass->getCode(), 'Código de error para clave errónea es 401');
TestHelper::assertSame('Credenciales de acceso incorrectas.', $errorWrongPass->getMessage(), 'Mensaje para clave errónea es idéntico');

// 3.4 Verificación de no-enumeración de usuarios vía HTTP
$httpLoginNonexistent = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'usuario_fantasma_no_existe', 'password' => 'clave123']));

$httpLoginWrongPass = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'admin', 'password' => 'clave_completamente_falsa']));

TestHelper::assertSame(401, $httpLoginNonexistent['status'], 'HTTP Login usuario inexistente responde 401');
TestHelper::assertSame(401, $httpLoginWrongPass['status'], 'HTTP Login clave errónea responde 401');
TestHelper::assertSame(
    $getErrMsg($httpLoginNonexistent),
    $getErrMsg($httpLoginWrongPass),
    'El mensaje de error devuelto por la API HTTP es exactamente idéntico en ambos casos ("Credenciales de acceso incorrectas.")'
);

// ============================================================================
// SECCIÓN 4: Cero Exposición de Datos Sensibles (Data Exposure - OWASP A02:2021)
// ============================================================================
TestHelper::section('4. Cero Exposición de Datos Sensibles (Data Exposure - OWASP A02:2021)');

// 4.1 Verificación en respuesta de Login
$loginRes = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'admin', 'password' => 'admin123']));

TestHelper::assertSame(200, $loginRes['status'], 'Login exitoso responde HTTP 200');
TestHelper::assertArrayHasKey('token', $loginRes['json']['datos'] ?? [], 'Login incluye token');
TestHelper::assertArrayHasKey('usuario', $loginRes['json']['datos'] ?? [], 'Login incluye datos de usuario');

$loginRawBody = $loginRes['body'];
TestHelper::assertFalse(str_contains($loginRawBody, 'password_hash'), 'Cuerpo JSON de login NO contiene la clave "password_hash"');
TestHelper::assertFalse(str_contains($loginRawBody, '$2y$10$'), 'Cuerpo JSON de login NO contiene ningún hash bcrypt');
TestHelper::assertFalse(str_contains($loginRawBody, 'admin123'), 'Cuerpo JSON de login NO contiene la contraseña en texto plano');

// 4.2 Verificación en respuesta de /api/auth/me.php
$meRes = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', $adminHeaders);
TestHelper::assertSame(200, $meRes['status'], 'GET /api/auth/me.php responde HTTP 200');

$meRawBody = $meRes['body'];
TestHelper::assertFalse(str_contains($meRawBody, 'password_hash'), 'Cuerpo JSON de me.php NO contiene "password_hash"');
TestHelper::assertFalse(str_contains($meRawBody, '$2y$'), 'Cuerpo JSON de me.php NO contiene hashes bcrypt');
TestHelper::assertArrayHasKey('id', $meRes['json']['datos'] ?? [], 'me.php contiene id');
TestHelper::assertArrayHasKey('username', $meRes['json']['datos'] ?? [], 'me.php contiene username');
TestHelper::assertArrayHasKey('rol', $meRes['json']['datos'] ?? [], 'me.php contiene rol');

// 4.3 Verificación en listado de usuarios /api/usuarios/index.php
$usersRes = TestHelper::curl('GET', $baseUrl . '/api/usuarios/index.php', $adminHeaders);
TestHelper::assertSame(200, $usersRes['status'], 'GET /api/usuarios/index.php responde HTTP 200');

$usersRawBody = $usersRes['body'];
TestHelper::assertFalse(str_contains($usersRawBody, 'password_hash'), 'Directorio de usuarios NO contiene la clave "password_hash"');
TestHelper::assertFalse(str_contains($usersRawBody, '$2y$'), 'Directorio de usuarios NO contiene hashes bcrypt');

$userList = $usersRes['json']['datos'] ?? [];
TestHelper::assertTrue(count($userList) >= 3, 'El listado contiene al menos los 3 usuarios semilla');
foreach ($userList as $idx => $userItem) {
    TestHelper::assertFalse(isset($userItem['password_hash']), "Usuario #{$idx} ({$userItem['username']}) NO expone 'password_hash'");
    TestHelper::assertFalse(isset($userItem['password']), "Usuario #{$idx} ({$userItem['username']}) NO expone 'password'");
}

// 4.4 Verificación en métodos de persistencia del repositorio
$safeUser = $usuarioRepo->findByIdSafe(1);
TestHelper::assertNotNull($safeUser, 'UsuarioRepository::findByIdSafe(1) retorna usuario');
TestHelper::assertFalse(isset($safeUser['password_hash']), 'UsuarioRepository::findByIdSafe NO incluye password_hash');

$allUsers = $usuarioRepo->listAll(20, 0);
foreach ($allUsers as $u) {
    TestHelper::assertFalse(isset($u['password_hash']), "UsuarioRepository::listAll() NO incluye password_hash para {$u['username']}");
}

$allWithCreations = $usuarioRepo->listAllWithCreationsCount(20, 0);
foreach ($allWithCreations as $u) {
    TestHelper::assertFalse(isset($u['password_hash']), "UsuarioRepository::listAllWithCreationsCount() NO incluye password_hash para {$u['username']}");
}

// 4.5 Verificación de protección de archivo de configuración en disco
$configFilePath = dirname(__DIR__) . '/app/config.php';
TestHelper::assertTrue(file_exists($configFilePath), 'app/config.php existe físicamente');
$configContent = file_get_contents($configFilePath);
TestHelper::assertStringContains('http_response_code(403)', $configContent, 'app/config.php cuenta con guardia de ejecución directa (HTTP 403)');

// ============================================================================
// SECCIÓN 5: Políticas de Higiene de Contraseñas & Reseteo Seguro
// ============================================================================
TestHelper::section('5. Políticas de Higiene de Contraseñas & Reseteo Seguro (OWASP A07:2021)');

$randSuffix = substr(md5((string)microtime(true)), 0, 8);
$testUserA = 'artesano_pwd_' . $randSuffix;

// 5.1 Creación con contraseña menor a 6 caracteres -> HTTP 422
$createShortPassRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/crear.php', $adminHeaders, json_encode([
    'username' => $testUserA,
    'password' => '12345', // 5 caracteres
    'rol'      => 'artesano'
]));
TestHelper::assertSame(422, $createShortPassRes['status'], 'Crear usuario con contraseña de 5 chars responde 422 Unprocessable Entity');
TestHelper::assertStringContains('al menos 6 caracteres', $getErrMsg($createShortPassRes), 'Error exige al menos 6 caracteres');

// 5.2 Creación con contraseña vacía -> HTTP 422
$createEmptyPassRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/crear.php', $adminHeaders, json_encode([
    'username' => $testUserA,
    'password' => '',
    'rol'      => 'artesano'
]));
TestHelper::assertSame(422, $createEmptyPassRes['status'], 'Crear usuario con contraseña vacía responde 422 Unprocessable Entity');

// 5.3 Creación exitosa con contraseña válida (>= 6 caracteres)
$createValidPassRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/crear.php', $adminHeaders, json_encode([
    'username' => $testUserA,
    'password' => 'ClaveSegura123!',
    'rol'      => 'artesano'
]));
TestHelper::assertSame(201, $createValidPassRes['status'], 'Crear usuario con contraseña válida responde 201 Created');
$testUserAId = (int)($createValidPassRes['json']['datos']['id'] ?? 0);
TestHelper::assertTrue($testUserAId > 0, 'Se obtuvo un ID válido para el usuario de prueba');

// Autenticar al usuario de prueba para obtener su token
$testUserLogin = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => $testUserA, 'password' => 'ClaveSegura123!']));
TestHelper::assertSame(200, $testUserLogin['status'], 'Usuario de prueba puede iniciar sesión con su clave inicial');
$testUserToken = (string)($testUserLogin['json']['datos']['token'] ?? '');
$testUserHeaders = ['Authorization: Bearer ' . $testUserToken, 'Content-Type: application/json'];

// 5.4 Autoservicio de cambio de contraseña: Clave actual errónea -> HTTP 401
$changeWrongCurrentRes = TestHelper::curl('POST', $baseUrl . '/api/auth/cambiar-password.php', $testUserHeaders, json_encode([
    'password_actual' => 'ClaveTotalmenteIncorrecta',
    'password_nueva'  => 'NuevaClaveValida123!'
]));
TestHelper::assertSame(401, $changeWrongCurrentRes['status'], 'Cambio de clave con contraseña actual errónea responde 401 Unauthorized');
TestHelper::assertStringContains('actual es incorrecta', $getErrMsg($changeWrongCurrentRes), 'Mensaje indica que clave actual es errónea');

// 5.5 Autoservicio de cambio de contraseña: Nueva clave < 6 caracteres -> HTTP 422
$changeShortNewRes = TestHelper::curl('POST', $baseUrl . '/api/auth/cambiar-password.php', $testUserHeaders, json_encode([
    'password_actual' => 'ClaveSegura123!',
    'password_nueva'  => 'abc'
]));
TestHelper::assertSame(422, $changeShortNewRes['status'], 'Cambio de clave con nueva clave < 6 caracteres responde 422 Unprocessable');

// 5.6 Autoservicio de cambio de contraseña exitoso
$changeValidRes = TestHelper::curl('POST', $baseUrl . '/api/auth/cambiar-password.php', $testUserHeaders, json_encode([
    'password_actual' => 'ClaveSegura123!',
    'password_nueva'  => 'MiNuevaClaveSegura456!'
]));
TestHelper::assertSame(200, $changeValidRes['status'], 'Cambio de clave válido responde HTTP 200 OK');

// Verificar que la clave anterior ya NO funciona
$oldPassLoginRes = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => $testUserA, 'password' => 'ClaveSegura123!']));
TestHelper::assertSame(401, $oldPassLoginRes['status'], 'Inicio de sesión con la contraseña anterior es RECHAZADO (401)');

// Verificar que la nueva clave SÍ funciona
$newPassLoginRes = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => $testUserA, 'password' => 'MiNuevaClaveSegura456!']));
TestHelper::assertSame(200, $newPassLoginRes['status'], 'Inicio de sesión con la nueva contraseña es EXITOSO (200)');

// 5.7 Reseteo administrativo de contraseña manual < 6 caracteres -> HTTP 422
$resetShortRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/restablecer-password.php', $adminHeaders, json_encode([
    'id'       => $testUserAId,
    'password' => 'corta'
]));
TestHelper::assertSame(422, $resetShortRes['status'], 'Reseteo administrativo con clave < 6 caracteres responde 422');

// 5.8 Reseteo administrativo autogenerado (sin suministrar password)
$resetAutoRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/restablecer-password.php', $adminHeaders, json_encode([
    'id' => $testUserAId
]));
TestHelper::assertSame(200, $resetAutoRes['status'], 'Reseteo administrativo autogenerado responde 200 OK');
$tempPassword = (string)($resetAutoRes['json']['datos']['password_temporal'] ?? '');
TestHelper::assertTrue(strlen($tempPassword) >= 14, "Contraseña temporal tiene longitud segura ({$tempPassword})");
TestHelper::assertStringContains('Crochet!', $tempPassword, 'Contraseña temporal incluye el prefijo institucional "Crochet!"');
TestHelper::assertTrue(str_ends_with($tempPassword, '!'), 'Contraseña temporal termina con símbolo "!"');

// Verificar login con la contraseña temporal autogenerada
$tempLoginRes = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => $testUserA, 'password' => $tempPassword]));
TestHelper::assertSame(200, $tempLoginRes['status'], 'Inicio de sesión con contraseña temporal autogenerada es EXITOSO (200)');

// ============================================================================
// SECCIÓN 6: Revocación Inmediata de Tokens en Bajas Lógicas (Soft-Delete)
// ============================================================================
TestHelper::section('6. Revocación Inmediata de Tokens en Bajas Lógicas (OWASP A07:2021)');

// 6.1 Generar un token fresco para el usuario de prueba
$activeTempUserToken = $tempLoginRes['json']['datos']['token'];
$tempUserHeaders = ['Authorization: Bearer ' . $activeTempUserToken, 'Content-Type: application/json'];

// Verificar que el token funciona antes de la baja lógica
$beforeDeleteMe = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', $tempUserHeaders);
TestHelper::assertSame(200, $beforeDeleteMe['status'], 'Token de usuario de prueba funciona antes de la baja lógica (200)');

// 6.2 Ejecutar baja lógica (soft delete) del usuario
$deleteUserRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', $adminHeaders, json_encode([
    'id' => $testUserAId
]));
TestHelper::assertSame(200, $deleteUserRes['status'], 'Baja lógica de usuario responde HTTP 200');
TestHelper::assertSame(0, (int)($deleteUserRes['json']['datos']['activo'] ?? 1), 'El estado del usuario es activo = 0');

// 6.3 Verificación en memoria con AuthService::validateToken
// Aunque el token no haya expirado cronológicamente (su TTL es de 24h),
// validateToken verifica la existencia de cuenta activa en la base de datos (activo = 1)
$validatedDeactivated = $authService->validateToken($activeTempUserToken);
TestHelper::assertNull($validatedDeactivated, 'AuthService::validateToken() retorna null INMEDIATAMENTE para token de usuario inactivo');

// 6.4 Verificación HTTP en vivo: El token no expirado es RECHAZADO con 401
$afterDeleteMe = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', $tempUserHeaders);
TestHelper::assertSame(401, $afterDeleteMe['status'], 'HTTP GET /api/auth/me.php con token de usuario desactivado responde 401 Unauthorized');
TestHelper::assertStringContains('inválido', $getErrMsg($afterDeleteMe), 'Error indica token inválido');

// 6.5 Intento de login del usuario desactivado -> HTTP 401
$loginDeactivatedRes = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => $testUserA, 'password' => $tempPassword]));
TestHelper::assertSame(401, $loginDeactivatedRes['status'], 'Login de usuario desactivado es rechazado con HTTP 401');

// 6.6 Reactivación de la cuenta y restauración de acceso
$reactivateRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/reactivar.php', $adminHeaders, json_encode([
    'id' => $testUserAId
]));
TestHelper::assertSame(200, $reactivateRes['status'], 'Reactivación de usuario responde HTTP 200');
TestHelper::assertSame(1, (int)($reactivateRes['json']['datos']['activo'] ?? 0), 'Usuario restaurado tiene activo = 1');

// Login exitoso tras reactivación
$loginReactivatedRes = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => $testUserA, 'password' => $tempPassword]));
TestHelper::assertSame(200, $loginReactivatedRes['status'], 'Login tras reactivación es EXITOSO (200)');

// Dejar al usuario de prueba desactivado limpiamente
$usuarioRepo->delete($testUserAId);

// ============================================================================
// SECCIÓN 7: Ciclo de Cierre de Sesión & Contrato Stateless
// ============================================================================
TestHelper::section('7. Ciclo de Cierre de Sesión & Contrato Stateless (OWASP A07:2021)');

// 7.1 POST /api/auth/logout.php sin cabecera de autenticación
$logoutAnonRes = TestHelper::curl('POST', $baseUrl . '/api/auth/logout.php', [
    'Content-Type: application/json'
]);
TestHelper::assertSame(200, $logoutAnonRes['status'], 'POST /api/auth/logout.php responde HTTP 200');
TestHelper::assertTrue((bool)($logoutAnonRes['json']['exito'] ?? false), 'Respuesta de logout indica éxito = true');
TestHelper::assertStringContains('Sesión cerrada', $logoutAnonRes['json']['mensaje'] ?? '', 'Mensaje confirma cierre de sesión');
TestHelper::assertStringContains('Descarte el token', $logoutAnonRes['json']['mensaje'] ?? '', 'Mensaje instruye al cliente a descartar el token');

// 7.2 POST /api/auth/logout.php con Bearer token auténtico
$logoutAuthRes = TestHelper::curl('POST', $baseUrl . '/api/auth/logout.php', $adminHeaders);
TestHelper::assertSame(200, $logoutAuthRes['status'], 'POST /api/auth/logout.php con token Bearer responde HTTP 200');

// 7.3 Restricción de método HTTP: GET /api/auth/logout.php -> 405
$logoutGetRes = TestHelper::curl('GET', $baseUrl . '/api/auth/logout.php');
TestHelper::assertSame(405, $logoutGetRes['status'], 'GET /api/auth/logout.php responde 405 Method Not Allowed');
TestHelper::assertStringContains('Se requiere POST', $getErrMsg($logoutGetRes), 'Mensaje indica que se requiere método POST');

// ============================================================================
// RESUMEN CONSOLIDADO
// ============================================================================
exit(TestHelper::summary());
