<?php
/**
 * Test Suite: Subfase 3.3 - Gestión de Usuarios, Autoría de Creadores & Roles RBAC
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Valida de forma exhaustiva:
 * 1. UsuarioService: listado paginado con creaciones_asociadas, alta de creadores,
 *    validaciones de formato/unicidad/contraseñas, salvaguarda de cuenta raíz ID #1
 * 2. RoleGuard & AuthGuard aplicados a rutas administrativas exclusivas de admin
 * 3. Endpoints REST en vivo contra localhost:8000:
 *    - GET /api/usuarios/index.php (200 con admin, 403 con artesano, 401 sin token, 405 en POST)
 *    - POST /api/usuarios/crear.php (201 éxito, 409 username duplicado, 422 inválido, 403 no-admin)
 *    - POST /api/usuarios/cambiar-rol.php (200 éxito en usuario regular, 403 al intentar degradar ID #1)
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
use App\Services\UsuarioService;
use App\Services\AuthService;
use App\Middleware\RoleGuard;

// Iniciar suite
TestHelper::init('Subfase 3.3: Gestión de Usuarios, Autoría de Creadores & Roles RBAC');

// Desactivar terminación forzada en Response para pruebas en memoria
Response::setExitOnSend(false);

$repo = new UsuarioRepository();
$service = new UsuarioService($repo);

// =============================================================================
// 1. USUARIOSERVICE: LÓGICA DE NEGOCIO Y REGLAS RBAC
// =============================================================================
TestHelper::section('1. UsuarioService: Lógica de Negocio y Reglas RBAC');

// 1.1 Listar usuarios con paginación y creaciones asociadas
$listResult = $service->listUsers(1, 10);
TestHelper::assertTrue(is_array($listResult), 'listUsers() retorna un array con datos');
TestHelper::assertTrue(isset($listResult['usuarios']), 'listUsers() contiene clave "usuarios"');
TestHelper::assertTrue(isset($listResult['paginacion']), 'listUsers() contiene sobre "paginacion"');
TestHelper::assertTrue(count($listResult['usuarios']) >= 3, 'Se listan al menos los 3 usuarios semilla iniciales');

$firstUser = $listResult['usuarios'][0];
TestHelper::assertSame(1, (int)$firstUser['id'], 'El primer usuario es el ID 1');
TestHelper::assertSame('admin', $firstUser['username'], 'El primer usuario es admin');
TestHelper::assertTrue(isset($firstUser['creaciones_asociadas']), 'El usuario incluye la clave creaciones_asociadas');
TestHelper::assertTrue((int)$firstUser['creaciones_asociadas'] >= 0, 'creaciones_asociadas es un entero no negativo');
TestHelper::assertFalse(isset($firstUser['password_hash']), 'El listado no expone hashes de contraseñas');

// 1.2 Paginación estandarizada
$pagination = $listResult['paginacion'];
TestHelper::assertSame(1, $pagination['pagina_actual'], 'Página actual es 1');
TestHelper::assertSame(10, $pagination['limite'], 'Límite por página es 10');
TestHelper::assertTrue($pagination['total_items'] >= 3, 'Total items es >= 3');

// 1.3 Obtener usuario por ID
$userAdmin = $service->getUserById(1);
TestHelper::assertSame('admin', $userAdmin['username'], 'getUserById(1) devuelve al usuario admin');
TestHelper::assertFalse(isset($userAdmin['password_hash']), 'getUserById() no expone password_hash');

// 1.4 Obtener usuario inexistente (404)
$caughtNotFound = false;
try {
    $service->getUserById(99999);
} catch (\RuntimeException $e) {
    $caughtNotFound = ($e->getCode() === 404);
}
TestHelper::assertTrue($caughtNotFound, 'getUserById(99999) lanza RuntimeException 404');

// 1.5 Crear usuario con datos válidos
$testUserTag = 'usr_' . time();
$createdUser = $service->createUser($testUserTag, 'PasswordSeguro123', 'artesano');
TestHelper::assertTrue((int)$createdUser['id'] > 0, 'createUser() inserta un nuevo usuario y retorna ID > 0');
TestHelper::assertSame($testUserTag, $createdUser['username'], 'El username coincide con el solicitado');
TestHelper::assertSame('artesano', $createdUser['rol'], 'El rol asignado es artesano');

$createdId = (int)$createdUser['id'];

// 1.6 Validación de unicidad de username (HTTP 409)
$caughtDuplicate = false;
try {
    $service->createUser($testUserTag, 'OtraClave123', 'artesano');
} catch (\RuntimeException $e) {
    $caughtDuplicate = ($e->getCode() === 409);
}
TestHelper::assertTrue($caughtDuplicate, 'createUser() rechaza username duplicado con código 409');

// 1.7 Validación de contraseña corta (HTTP 422)
$caughtShortPass = false;
try {
    $service->createUser('usr_corto_' . time(), '12345', 'artesano');
} catch (\InvalidArgumentException $e) {
    $caughtShortPass = ($e->getCode() === 422);
}
TestHelper::assertTrue($caughtShortPass, 'createUser() rechaza contraseña menor a 6 caracteres con 422');

// 1.8 Validación de rol inválido (HTTP 422)
$caughtInvalidRole = false;
try {
    $service->createUser('usr_rol_' . time(), 'PasswordValido123', 'superhacker');
} catch (\InvalidArgumentException $e) {
    $caughtInvalidRole = ($e->getCode() === 422);
}
TestHelper::assertTrue($caughtInvalidRole, 'createUser() rechaza rol inexistente con 422');

// 1.9 Modificar rol de usuario regular
$roleUpdateResult = $service->updateRole($createdId, 'asistente');
TestHelper::assertSame('asistente', $roleUpdateResult['rol'], 'updateRole() actualiza rol de usuario regular');

// 1.10 Salvaguarda Inviolable del Administrador Raíz (ID #1): Intento de degradación (HTTP 403)
$caughtRootDemote = false;
try {
    $service->updateRole(1, 'artesano');
} catch (\RuntimeException $e) {
    $caughtRootDemote = ($e->getCode() === 403);
}
TestHelper::assertTrue($caughtRootDemote, 'updateRole(1, "artesano") lanza RuntimeException 403 por salvaguarda de ID #1');

// 1.11 Salvaguarda Inviolable del Administrador Raíz (ID #1): Intento de eliminación (HTTP 403)
$caughtRootDelete = false;
try {
    $service->deleteUser(1);
} catch (\RuntimeException $e) {
    $caughtRootDelete = ($e->getCode() === 403);
}
TestHelper::assertTrue($caughtRootDelete, 'deleteUser(1) lanza RuntimeException 403 por salvaguarda de ID #1');

// 1.12 Eliminación de usuario temporal sin creaciones
$deleteResult = $service->deleteUser($createdId);
TestHelper::assertTrue($deleteResult, 'deleteUser() permite eliminar usuario regular sin creaciones');

// =============================================================================
// 2. MIDDLEWARE DE ACCESO: ROLEGUARD (ADMINONLY)
// =============================================================================
TestHelper::section('2. RoleGuard: Protección Exclusiva para Administradores');

Request::reset();

// 2.1 RoleGuard concede acceso al rol admin
Request::setUser(['id' => 1, 'username' => 'admin', 'rol' => 'admin']);
$adminAccess = RoleGuard::adminOnly();
TestHelper::assertSame('admin', $adminAccess['rol'], 'RoleGuard::adminOnly() permite el paso a admin');

// 2.2 RoleGuard bloquea el acceso al rol artesano con HTTP 403
Request::setUser(['id' => 2, 'username' => 'artesana_ana', 'rol' => 'artesano']);
ob_start();
$artisanBlocked = RoleGuard::adminOnly();
ob_get_clean();

TestHelper::assertSame(403, Response::getLastStatusCode(), 'RoleGuard::adminOnly() emite HTTP 403 para artesano');
$lastPayload = Response::getLastPayload();
TestHelper::assertFalse($lastPayload['exito'] ?? true, 'Respuesta 403 contiene exito: false');

// 2.3 RoleGuard bloquea el acceso al rol asistente con HTTP 403
Request::setUser(['id' => 3, 'username' => 'asistente_leo', 'rol' => 'asistente']);
ob_start();
$assistantBlocked = RoleGuard::adminOnly();
ob_get_clean();

TestHelper::assertSame(403, Response::getLastStatusCode(), 'RoleGuard::adminOnly() emite HTTP 403 para asistente');

Request::reset();

// =============================================================================
// 3. PRUEBAS HTTP EN VIVO (CURL CONTRA LOCALHOST:8000)
// =============================================================================
TestHelper::section('3. Pruebas HTTP en Vivo contra Servidor (api/usuarios/)');

// 3.0 Obtener tokens para admin y para artesano
$adminLoginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'admin', 'password' => 'admin123']));

$adminToken = (string)($adminLoginRes['json']['datos']['token'] ?? '');
TestHelper::assertTrue(!empty($adminToken), 'Login de admin exitoso para obtener token de prueba');

$artisanLoginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json'
], json_encode(['username' => 'artesana_ana', 'password' => 'admin123']));

$artisanToken = (string)($artisanLoginRes['json']['datos']['token'] ?? '');
TestHelper::assertTrue(!empty($artisanToken), 'Login de artesana_ana exitoso para obtener token de no-admin');

// 3.1 GET /api/usuarios/index.php con token de admin (HTTP 200 OK)
$listHttpRes = TestHelper::curl('GET', 'http://localhost:8000/api/usuarios/index.php?pagina=1&limite=10', [
    'Authorization: Bearer ' . $adminToken
]);

TestHelper::assertSame(200, $listHttpRes['status'], 'HTTP GET /api/usuarios/index.php con token admin devuelve 200 OK');
TestHelper::assertTrue($listHttpRes['json']['exito'] ?? false, 'Respuesta contiene exito: true');
TestHelper::assertTrue(is_array($listHttpRes['json']['datos'] ?? null), 'Respuesta contiene array de datos');
TestHelper::assertTrue(isset($listHttpRes['json']['paginacion']), 'Respuesta contiene sobre de paginacion');

// 3.2 GET /api/usuarios/index.php con token de artesano (HTTP 403 Forbidden)
$artisanDeniedRes = TestHelper::curl('GET', 'http://localhost:8000/api/usuarios/index.php', [
    'Authorization: Bearer ' . $artisanToken
]);

TestHelper::assertSame(403, $artisanDeniedRes['status'], 'HTTP GET /api/usuarios/index.php con token artesano devuelve 403 Forbidden');
TestHelper::assertFalse($artisanDeniedRes['json']['exito'] ?? true, 'Respuesta 403 contiene exito: false');

// 3.3 GET /api/usuarios/index.php sin token (HTTP 401 Unauthorized)
$noTokenRes = TestHelper::curl('GET', 'http://localhost:8000/api/usuarios/index.php');
TestHelper::assertSame(401, $noTokenRes['status'], 'HTTP GET /api/usuarios/index.php sin token devuelve 401 Unauthorized');

// 3.4 POST en /api/usuarios/index.php (HTTP 405 Method Not Allowed)
$wrongMethodRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/index.php', [
    'Authorization: Bearer ' . $adminToken
]);
TestHelper::assertSame(405, $wrongMethodRes['status'], 'HTTP POST en /api/usuarios/index.php devuelve 405 Method Not Allowed');

// 3.5 POST /api/usuarios/crear.php con token de admin (HTTP 201 Created)
$httpNewUsername = 'creador_' . time();
$createHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/crear.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'username' => $httpNewUsername,
    'password' => 'Secreta2026',
    'rol'      => 'artesano'
]));

TestHelper::assertSame(201, $createHttpRes['status'], 'HTTP POST /api/usuarios/crear.php devuelve 201 Created');
TestHelper::assertTrue($createHttpRes['json']['exito'] ?? false, 'Respuesta contiene exito: true');
TestHelper::assertSame($httpNewUsername, $createHttpRes['json']['datos']['username'] ?? '', 'Username devuelto coincide');
$newUserId = (int)($createHttpRes['json']['datos']['id'] ?? 0);
TestHelper::assertTrue($newUserId > 0, 'ID de usuario devuelto es > 0');

// 3.6 POST /api/usuarios/crear.php con username repetido (HTTP 409 Conflict)
$duplicateHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/crear.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'username' => $httpNewUsername,
    'password' => 'Secreta2026',
    'rol'      => 'artesano'
]));

TestHelper::assertSame(409, $duplicateHttpRes['status'], 'HTTP POST /api/usuarios/crear.php con username repetido devuelve 409 Conflict');
TestHelper::assertFalse($duplicateHttpRes['json']['exito'] ?? true, 'Respuesta 409 contiene exito: false');

// 3.7 POST /api/usuarios/crear.php con password corto (HTTP 422 Unprocessable)
$shortPassHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/crear.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'username' => 'usuario_corto_' . time(),
    'password' => '123',
    'rol'      => 'artesano'
]));

TestHelper::assertSame(422, $shortPassHttpRes['status'], 'HTTP POST /api/usuarios/crear.php con clave corta devuelve 422');

// 3.8 POST /api/usuarios/crear.php con token de artesano (HTTP 403 Forbidden)
$artisanCreateRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/crear.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'username' => 'hacker_' . time(),
    'password' => 'Secreta2026',
    'rol'      => 'admin'
]));

TestHelper::assertSame(403, $artisanCreateRes['status'], 'HTTP POST /api/usuarios/crear.php con token artesano devuelve 403 Forbidden');

// 3.9 POST /api/usuarios/cambiar-rol.php en usuario regular (HTTP 200 OK)
$changeRoleRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/cambiar-rol.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id'  => $newUserId,
    'rol' => 'asistente'
]));

TestHelper::assertSame(200, $changeRoleRes['status'], 'HTTP POST /api/usuarios/cambiar-rol.php devuelve 200 OK');
TestHelper::assertTrue($changeRoleRes['json']['exito'] ?? false, 'Respuesta contiene exito: true');
TestHelper::assertSame('asistente', $changeRoleRes['json']['datos']['rol'] ?? '', 'Rol actualizado a asistente');

// 3.10 SALVAGUARDA HTTP ID #1: Intento de degradar al administrador titular (HTTP 403 Forbidden)
$demoteRootHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/cambiar-rol.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id'  => 1,
    'rol' => 'artesano'
]));

TestHelper::assertSame(403, $demoteRootHttpRes['status'], 'HTTP POST /api/usuarios/cambiar-rol.php para ID #1 devuelve 403 Forbidden');
TestHelper::assertFalse($demoteRootHttpRes['json']['exito'] ?? true, 'Respuesta 403 contiene exito: false');

// 3.11 POST /api/usuarios/cambiar-rol.php con token de artesano (HTTP 403 Forbidden)
$artisanChangeRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/cambiar-rol.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'  => 2,
    'rol' => 'admin'
]));

TestHelper::assertSame(403, $artisanChangeRes['status'], 'HTTP POST /api/usuarios/cambiar-rol.php con token artesano devuelve 403 Forbidden');

// 3.12 Preflight CORS OPTIONS en /api/usuarios/index.php (HTTP 200 o 204)
$corsRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/usuarios/index.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: GET',
    'Access-Control-Request-Headers: Authorization, Content-Type',
]);
TestHelper::assertTrue(
    in_array($corsRes['status'], [200, 204], true),
    'Preflight CORS OPTIONS en /api/usuarios/index.php responde con código válido (' . $corsRes['status'] . ')'
);

// Limpieza de usuario de prueba en base de datos
$repo->delete($newUserId);

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
