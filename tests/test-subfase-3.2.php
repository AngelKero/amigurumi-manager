<?php
/**
 * Test Suite: Subfase 3.2 - Autenticación Stateless, Repositorio de Usuarios & Bearer Middleware
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Valida de forma exhaustiva:
 * 1. UsuarioRepository: consultas parametrizadas, persistencia, salvaguardas ID #1
 * 2. AuthService: verificación bcrypt, emisión HMAC, mitigación de timing attack
 * 3. AuthGuard: protección Bearer token, inyección en Request, error 401
 * 4. RoleGuard: RBAC ('admin', 'artesano', 'asistente') y restricción 403
 * 5. Endpoints REST en vivo contra localhost:8000:
 *    - POST /api/auth/login.php (200 éxito, 401 credenciales malas, 422 vacío, 405 método)
 *    - POST /api/auth/logout.php (200 OK)
 *    - GET /api/auth/me.php (401 sin token, 401 token inválido, 200 con token verificado)
 *    - OPTIONS Preflight CORS
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use App\Middleware\AuthGuard;
use App\Middleware\RoleGuard;

// Iniciar suite
TestHelper::init('Subfase 3.2: Autenticación Stateless & Middleware de Seguridad');

// Desactivar terminación forzada en Response para pruebas en memoria
Response::setExitOnSend(false);

// =============================================================================
// 1. USUARIO REPOSITORY
// =============================================================================
TestHelper::section('1. UsuarioRepository (Capa de Persistencia SQL)');

$repo = new UsuarioRepository();

// Búsqueda por username existente
$adminUser = $repo->findByUsername('admin');
TestHelper::assertNotNull($adminUser, 'findByUsername("admin") encuentra al usuario administrador');
TestHelper::assertSame(1, (int)($adminUser['id'] ?? 0), 'El ID del admin es 1');
TestHelper::assertSame('admin', $adminUser['rol'] ?? '', 'El rol del admin es "admin"');
TestHelper::assertTrue(str_starts_with($adminUser['password_hash'] ?? '', '$2y$'), 'El hash de contraseña es un hash bcrypt válido ($2y$)');

// Búsqueda de usuario inexistente
$nonExistent = $repo->findByUsername('usuario_no_existente_999');
TestHelper::assertNull($nonExistent, 'findByUsername() devuelve null para usuarios inexistentes');

// Búsqueda por ID
$userById = $repo->findById(1);
TestHelper::assertNotNull($userById, 'findById(1) encuentra al usuario');
TestHelper::assertSame('admin', $userById['username'] ?? '', 'El username coincide con "admin"');

// Búsqueda segura sin hash de contraseña
$safeUser = $repo->findByIdSafe(1);
TestHelper::assertNotNull($safeUser, 'findByIdSafe(1) devuelve registro');
TestHelper::assertFalse(isset($safeUser['password_hash']), 'findByIdSafe() no expone la columna password_hash');
TestHelper::assertSame('admin', $safeUser['username'] ?? '', 'findByIdSafe() incluye username');

// Comprobación de existencia de username
TestHelper::assertTrue($repo->existsUsername('admin'), 'existsUsername("admin") devuelve true');
TestHelper::assertFalse($repo->existsUsername('admin', 1), 'existsUsername("admin", 1) excluye el ID 1 y devuelve false');
TestHelper::assertFalse($repo->existsUsername('usuario_fantasma_xyz'), 'existsUsername() devuelve false para nombre disponible');

// Salvaguarda del Administrador Raíz (ID #1): No puede degradarse ni eliminarse
TestHelper::assertFalse($repo->updateRole(1, 'artesano'), 'Salvaguarda ID #1: updateRole(1, "artesano") es rechazado y devuelve false');
TestHelper::assertFalse($repo->updateRole(1, 'asistente'), 'Salvaguarda ID #1: updateRole(1, "asistente") es rechazado y devuelve false');
TestHelper::assertFalse($repo->delete(1), 'Salvaguarda ID #1: delete(1) es rechazado y devuelve false');

// Creación, actualización y eliminación de usuario de prueba
$tempUsername = 'test_artesano_' . time();
$tempHash = password_hash('temporal123', PASSWORD_BCRYPT);
$createdId = $repo->create($tempUsername, $tempHash, 'artesano');
TestHelper::assertTrue($createdId > 0, 'create() inserta un nuevo usuario y devuelve ID > 0');

$createdUser = $repo->findById($createdId);
TestHelper::assertNotNull($createdUser, 'Usuario recién creado es recuperable por ID');
TestHelper::assertSame('artesano', $createdUser['rol'] ?? '', 'El rol asignado es "artesano"');

// Actualizar rol del usuario temporal (no es ID #1)
TestHelper::assertTrue($repo->updateRole($createdId, 'asistente'), 'updateRole() permite cambiar rol a usuarios regulares');
$updatedUser = $repo->findById($createdId);
TestHelper::assertSame('asistente', $updatedUser['rol'] ?? '', 'El rol se actualizó a "asistente"');

// Eliminar usuario temporal
TestHelper::assertTrue($repo->delete($createdId), 'delete() permite eliminar usuarios temporales regulares');
TestHelper::assertNull($repo->findById($createdId), 'Usuario eliminado ya no existe en la base de datos');

// Conteo y listado paginado
$totalUsers = $repo->countAll();
TestHelper::assertTrue($totalUsers >= 3, 'countAll() contabiliza al menos los 3 usuarios semilla iniciales');
$userList = $repo->listAll(10, 0);
TestHelper::assertTrue(is_array($userList) && count($userList) >= 3, 'listAll() retorna lista paginada de usuarios');

// =============================================================================
// 2. AUTH SERVICE
// =============================================================================
TestHelper::section('2. AuthService (Reglas de Negocio de Autenticación)');

$authService = new AuthService($repo);

// Autenticación exitosa
$authResult = $authService->authenticate('admin', 'admin123');
TestHelper::assertTrue(is_array($authResult), 'authenticate("admin", "admin123") retorna array de sesión');
TestHelper::assertTrue(!empty($authResult['token']), 'authenticate() emite un token no vacío');
TestHelper::assertSame('Bearer', $authResult['tipo_token'] ?? '', 'tipo_token es "Bearer"');
TestHelper::assertSame(86400, $authResult['expira_en'] ?? 0, 'expira_en es de 86400 segundos (24h)');
TestHelper::assertSame(1, $authResult['usuario']['id'] ?? 0, 'usuario.id es 1');
TestHelper::assertSame('admin', $authResult['usuario']['username'] ?? '', 'usuario.username es "admin"');
TestHelper::assertSame('admin', $authResult['usuario']['rol'] ?? '', 'usuario.rol es "admin"');
TestHelper::assertFalse(isset($authResult['usuario']['password_hash']), 'usuario no expone password_hash');

$validToken = $authResult['token'];

// Validación del token emitido
$validatedUser = $authService->validateToken($validToken);
TestHelper::assertNotNull($validatedUser, 'validateToken() valida exitosamente el token emitido');
TestHelper::assertSame('admin', $validatedUser['username'] ?? '', 'validateToken() devuelve los datos del usuario');

// Autenticación con contraseña incorrecta (HTTP 401)
$caughtWrongPass = false;
try {
    $authService->authenticate('admin', 'password_erronea_xyz');
} catch (\RuntimeException $e) {
    $caughtWrongPass = ($e->getCode() === 401);
}
TestHelper::assertTrue($caughtWrongPass, 'authenticate() lanza RuntimeException 401 ante contraseña incorrecta');

// Autenticación con usuario inexistente (HTTP 401)
$caughtNonExistent = false;
try {
    $authService->authenticate('no_existe_este_artesano', 'admin123');
} catch (\RuntimeException $e) {
    $caughtNonExistent = ($e->getCode() === 401);
}
TestHelper::assertTrue($caughtNonExistent, 'authenticate() lanza RuntimeException 401 ante usuario inexistente');

// Autenticación con campos vacíos (HTTP 422)
$caughtEmpty = false;
try {
    $authService->authenticate('', 'admin123');
} catch (\InvalidArgumentException $e) {
    $caughtEmpty = ($e->getCode() === 422);
}
TestHelper::assertTrue($caughtEmpty, 'authenticate() lanza InvalidArgumentException 422 ante campos vacíos');

// Cambio de contraseña propio: clave actual errónea (HTTP 401)
$caughtChangeWrong = false;
try {
    $authService->changePassword(1, 'clave_incorrecta', 'nuevaClave123');
} catch (\RuntimeException $e) {
    $caughtChangeWrong = ($e->getCode() === 401);
}
TestHelper::assertTrue($caughtChangeWrong, 'changePassword() lanza RuntimeException 401 ante clave actual errónea');

// Cambio de contraseña propio: clave nueva demasiado corta (HTTP 422)
$caughtChangeShort = false;
try {
    $authService->changePassword(1, 'admin123', '123');
} catch (\InvalidArgumentException $e) {
    $caughtChangeShort = ($e->getCode() === 422);
}
TestHelper::assertTrue($caughtChangeShort, 'changePassword() lanza InvalidArgumentException 422 ante clave menor a 6 caracteres');

// =============================================================================
// 3. MIDDLEWARE AUTHGUARD & ROLEGUARD
// =============================================================================
TestHelper::section('3. Middleware AuthGuard & RoleGuard');

Request::reset();

// 3.1 AuthGuard sin token en cabecera
ob_start();
$guardUserNoToken = AuthGuard::handle($authService);
$outputNoToken = ob_get_clean();

TestHelper::assertSame(401, Response::getLastStatusCode(), 'AuthGuard emite 401 si no hay token en la petición');
$lastPayload = Response::getLastPayload();
TestHelper::assertFalse($lastPayload['exito'] ?? true, 'AuthGuard emite exito: false');

// 3.2 AuthGuard con token válido
Request::reset();
Request::setMockHeader('Authorization', 'Bearer ' . $validToken);

ob_start();
$guardUserValid = AuthGuard::handle($authService);
$outputValid = ob_get_clean();

TestHelper::assertSame('admin', $guardUserValid['username'] ?? '', 'AuthGuard valida el token y retorna el usuario');
TestHelper::assertSame('admin', Request::user()['username'] ?? '', 'AuthGuard inyecta el usuario en Request::user()');

// 3.3 RoleGuard con rol suficiente (adminOnly con usuario admin)
$roleUserAdmin = RoleGuard::adminOnly();
TestHelper::assertSame('admin', $roleUserAdmin['username'] ?? '', 'RoleGuard::adminOnly() concede acceso a usuarios admin');

// 3.4 RoleGuard con rol insuficiente
Request::setUser(['id' => 3, 'username' => 'asistente_leo', 'rol' => 'asistente']);

ob_start();
$roleForbidden = RoleGuard::adminOnly();
$outputForbidden = ob_get_clean();

TestHelper::assertSame(403, Response::getLastStatusCode(), 'RoleGuard emite 403 si el rol es insuficiente (asistente en adminOnly)');

ob_start();
$artisanForbidden = RoleGuard::artisanOrAdmin();
$outputArtisanForbidden = ob_get_clean();
TestHelper::assertSame(403, Response::getLastStatusCode(), 'RoleGuard emite 403 para asistente en artisanOrAdmin');

// 3.5 RoleGuard con rol artesano en artisanOrAdmin
Request::setUser(['id' => 2, 'username' => 'artesana_ana', 'rol' => 'artesano']);
$artisanAllowed = RoleGuard::artisanOrAdmin();
TestHelper::assertSame('artesana_ana', $artisanAllowed['username'] ?? '', 'RoleGuard::artisanOrAdmin() concede acceso a usuarios artesanos');

Request::reset();

// =============================================================================
// 4. PRUEBAS HTTP EN VIVO (CURL CONTRA LOCALHOST:8000)
// =============================================================================
TestHelper::section('4. Pruebas HTTP en Vivo contra Servidor (api/auth/)');

// 4.1 Login exitoso vía POST JSON
$loginSuccessRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'admin', 'password' => 'admin123']));

TestHelper::assertSame(200, $loginSuccessRes['status'], 'HTTP POST /api/auth/login.php devuelve 200 OK');
TestHelper::assertTrue($loginSuccessRes['json']['exito'] ?? false, 'Respuesta de login contiene exito: true');
TestHelper::assertTrue(!empty($loginSuccessRes['json']['datos']['token']), 'Respuesta contiene token');
TestHelper::assertSame('admin', $loginSuccessRes['json']['datos']['usuario']['username'] ?? '', 'Respuesta contiene usuario admin');

$httpToken = (string)($loginSuccessRes['json']['datos']['token'] ?? '');

// 4.2 Login con contraseña incorrecta (HTTP 401)
$loginFailRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'admin', 'password' => 'incorrecta_123']));

TestHelper::assertSame(401, $loginFailRes['status'], 'HTTP POST /api/auth/login.php con clave errónea devuelve 401');
TestHelper::assertFalse($loginFailRes['json']['exito'] ?? true, 'Respuesta contiene exito: false');

// 4.3 Login con campos vacíos (HTTP 422)
$loginEmptyRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => '', 'password' => '']));

TestHelper::assertSame(422, $loginEmptyRes['status'], 'HTTP POST /api/auth/login.php con campos vacíos devuelve 422');

// 4.4 Intento de GET en /api/auth/login.php (HTTP 405)
$loginGetRes = TestHelper::curl('GET', 'http://localhost:8000/api/auth/login.php');
TestHelper::assertSame(405, $loginGetRes['status'], 'HTTP GET en /api/auth/login.php devuelve 405 Method Not Allowed');

// 4.5 Acceso a /api/auth/me.php sin token (HTTP 401)
$meNoTokenRes = TestHelper::curl('GET', 'http://localhost:8000/api/auth/me.php');
TestHelper::assertSame(401, $meNoTokenRes['status'], 'HTTP GET /api/auth/me.php sin token devuelve 401');

// 4.6 Acceso a /api/auth/me.php con token alterado (HTTP 401)
$meTamperedRes = TestHelper::curl('GET', 'http://localhost:8000/api/auth/me.php', [
    'Authorization: Bearer ' . $httpToken . '_alterado'
]);
TestHelper::assertSame(401, $meTamperedRes['status'], 'HTTP GET /api/auth/me.php con token manipulado devuelve 401');

// 4.7 Acceso a /api/auth/me.php con token legítimo (HTTP 200)
$meSuccessRes = TestHelper::curl('GET', 'http://localhost:8000/api/auth/me.php', [
    'Authorization: Bearer ' . $httpToken
]);
TestHelper::assertSame(200, $meSuccessRes['status'], 'HTTP GET /api/auth/me.php con Bearer token legítimo devuelve 200 OK');
TestHelper::assertTrue($meSuccessRes['json']['exito'] ?? false, 'Respuesta de me.php contiene exito: true');
TestHelper::assertSame('admin', $meSuccessRes['json']['datos']['username'] ?? '', 'me.php devuelve el usuario "admin"');
TestHelper::assertSame('admin', $meSuccessRes['json']['datos']['rol'] ?? '', 'me.php devuelve el rol "admin"');

// 4.8 Cierre de sesión POST /api/auth/logout.php (HTTP 200)
$logoutRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/logout.php');
TestHelper::assertSame(200, $logoutRes['status'], 'HTTP POST /api/auth/logout.php devuelve 200 OK');
TestHelper::assertTrue($logoutRes['json']['exito'] ?? false, 'Respuesta de logout contiene exito: true');

// 4.9 Preflight CORS OPTIONS en /api/auth/login.php (HTTP 200 o 204)
$corsRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/auth/login.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: Authorization, Content-Type',
]);
TestHelper::assertTrue(
    in_array($corsRes['status'], [200, 204], true),
    'Preflight CORS OPTIONS en /api/auth/login.php responde con código válido (' . $corsRes['status'] . ')'
);

// 4.10 Cambio de contraseña propio vía HTTP POST /api/auth/cambiar-password.php
$loginAnaRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'artesana_ana', 'password' => 'admin123']));
$anaToken = (string)($loginAnaRes['json']['datos']['token'] ?? '');

// Intento con clave actual errónea
$changeWrongRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/cambiar-password.php', [
    'Authorization: Bearer ' . $anaToken,
    'Content-Type: application/json',
], json_encode(['password_actual' => 'clave_falsa_999', 'nueva_password' => 'miNuevaClave2026']));
TestHelper::assertSame(401, $changeWrongRes['status'], 'HTTP POST /api/auth/cambiar-password.php con clave errónea devuelve 401');

// Cambio exitoso
$changeSuccessRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/cambiar-password.php', [
    'Authorization: Bearer ' . $anaToken,
    'Content-Type: application/json',
], json_encode(['password_actual' => 'admin123', 'nueva_password' => 'miNuevaClave2026']));
TestHelper::assertSame(200, $changeSuccessRes['status'], 'HTTP POST /api/auth/cambiar-password.php con datos válidos devuelve 200 OK');
TestHelper::assertTrue($changeSuccessRes['json']['exito'] ?? false, 'Respuesta de cambio de clave contiene exito: true');

// Verificar login con la nueva contraseña
$loginNewPassRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'artesana_ana', 'password' => 'miNuevaClave2026']));
TestHelper::assertSame(200, $loginNewPassRes['status'], 'Login exitoso con la nueva clave cambiada por el usuario');

// Restaurar clave original de artesana_ana para mantener idempotencia
$newAnaToken = (string)($loginNewPassRes['json']['datos']['token'] ?? '');
TestHelper::curl('POST', 'http://localhost:8000/api/auth/cambiar-password.php', [
    'Authorization: Bearer ' . $newAnaToken,
    'Content-Type: application/json',
], json_encode(['password_actual' => 'miNuevaClave2026', 'nueva_password' => 'admin123']));

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
