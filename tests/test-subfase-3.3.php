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

// 1.12 Modificar nombre de usuario (updateUsername)
$userForUpdate = $service->createUser('usr_rename_' . time(), 'PasswordSeguro123', 'artesano');
$renameId = (int)$userForUpdate['id'];
$updatedUser = $service->updateUsername($renameId, 'usr_renamed_' . time());
TestHelper::assertSame('usr_renamed_' . time(), $updatedUser['username'], 'updateUsername() modifica el nombre de usuario');

// 1.13 Intento de duplicar nombre de usuario (HTTP 409)
$caughtRenameDuplicate = false;
try {
    $service->updateUsername($renameId, 'admin');
} catch (\RuntimeException $e) {
    $caughtRenameDuplicate = ($e->getCode() === 409);
}
TestHelper::assertTrue($caughtRenameDuplicate, 'updateUsername() rechaza nombre ya ocupado con HTTP 409');

// 1.14 Restablecer contraseña manual y autogenerada (resetPassword)
$resetManual = $service->resetPassword($renameId, 'NuevaClaveValida2026');
TestHelper::assertFalse($resetManual['es_autogenerada'], 'resetPassword con clave manual no marca es_autogenerada');
TestHelper::assertNull($resetManual['password_temporal'], 'resetPassword con clave manual no retorna password_temporal');

$resetAuto = $service->resetPassword($renameId);
TestHelper::assertTrue($resetAuto['es_autogenerada'], 'resetPassword sin clave autogenera clave temporal');
TestHelper::assertTrue(!empty($resetAuto['password_temporal']), 'resetPassword autogenerado entrega clave temporal en claro');
TestHelper::assertTrue(str_starts_with($resetAuto['password_temporal'], 'Crochet!'), 'Clave autogenerada sigue el patrón Crochet!...');

// 1.15 Intento de auto-eliminación con sesión activa (HTTP 403)
$caughtSelfDelete = false;
try {
    $service->deleteUser($renameId, $renameId);
} catch (\RuntimeException $e) {
    $caughtSelfDelete = ($e->getCode() === 403);
}
TestHelper::assertTrue($caughtSelfDelete, 'deleteUser() con currentUserId igual al target lanza HTTP 403 por auto-eliminación');

// 1.16 Intento de eliminar usuario con creaciones asociadas (HTTP 409)
$caughtCreationsConflict = false;
try {
    $service->deleteUser(2); // artesana_ana tiene creaciones
} catch (\RuntimeException $e) {
    $caughtCreationsConflict = ($e->getCode() === 409);
}
TestHelper::assertTrue($caughtCreationsConflict, 'deleteUser() de usuario con creaciones lanza HTTP 409 por integridad referencial');

// 1.17 Eliminación exitosa de usuario temporal sin creaciones
$deleteResult = $service->deleteUser($renameId);
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

$httpLogBuffer = "================================================================================\n";
$httpLogBuffer .= "  HTTP TRACE LOG: Subfase 3.3 - Gestión de Usuarios, Autoría & Roles RBAC\n";
$httpLogBuffer .= "  Fecha: " . date("r") . "\n";
$httpLogBuffer .= "  Host: http://localhost:8000\n";
$httpLogBuffer .= "================================================================================\n\n";

$logTrace = function(string $title, array $res) use (&$httpLogBuffer): void {
    $httpLogBuffer .= "--- {$title} ---\n";
    $httpLogBuffer .= "HTTP Status: {$res['status']}\n";
    foreach ($res['headers'] as $k => $v) {
        $httpLogBuffer .= "{$k}: {$v}\n";
    }
    $httpLogBuffer .= "\n" . $res['body'] . "\n\n";
};

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
$logTrace('1. GET /api/usuarios/index.php (Admin Token: HTTP 200 OK)', $listHttpRes);

TestHelper::assertSame(200, $listHttpRes['status'], 'HTTP GET /api/usuarios/index.php con token admin devuelve 200 OK');
TestHelper::assertTrue($listHttpRes['json']['exito'] ?? false, 'Respuesta contiene exito: true');
TestHelper::assertTrue(is_array($listHttpRes['json']['datos'] ?? null), 'Respuesta contiene array de datos');
TestHelper::assertTrue(isset($listHttpRes['json']['paginacion']), 'Respuesta contiene sobre de paginacion');

// 3.2 GET /api/usuarios/index.php con token de artesano (HTTP 403 Forbidden)
$artisanDeniedRes = TestHelper::curl('GET', 'http://localhost:8000/api/usuarios/index.php', [
    'Authorization: Bearer ' . $artisanToken
]);
$logTrace('2. GET /api/usuarios/index.php (Artesano Token: HTTP 403 Forbidden)', $artisanDeniedRes);

TestHelper::assertSame(403, $artisanDeniedRes['status'], 'HTTP GET /api/usuarios/index.php con token artesano devuelve 403 Forbidden');
TestHelper::assertFalse($artisanDeniedRes['json']['exito'] ?? true, 'Respuesta 403 contiene exito: false');

// 3.3 GET /api/usuarios/index.php sin token (HTTP 401 Unauthorized)
$noTokenRes = TestHelper::curl('GET', 'http://localhost:8000/api/usuarios/index.php');
$logTrace('3. GET /api/usuarios/index.php (Sin Token: HTTP 401 Unauthorized)', $noTokenRes);
TestHelper::assertSame(401, $noTokenRes['status'], 'HTTP GET /api/usuarios/index.php sin token devuelve 401 Unauthorized');

// 3.4 POST en /api/usuarios/index.php (HTTP 405 Method Not Allowed)
$wrongMethodRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/index.php', [
    'Authorization: Bearer ' . $adminToken
]);
$logTrace('4. POST /api/usuarios/index.php (Método Inválido: HTTP 405)', $wrongMethodRes);
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
$logTrace('5. POST /api/usuarios/crear.php (Alta Exitosa: HTTP 201 Created)', $createHttpRes);

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
$logTrace('6. POST /api/usuarios/crear.php (Username Duplicado: HTTP 409 Conflict)', $duplicateHttpRes);

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
$logTrace('7. POST /api/usuarios/crear.php (Password Corto: HTTP 422 Unprocessable)', $shortPassHttpRes);

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
$logTrace('8. POST /api/usuarios/crear.php (Artesano No Autorizado: HTTP 403 Forbidden)', $artisanCreateRes);

TestHelper::assertSame(403, $artisanCreateRes['status'], 'HTTP POST /api/usuarios/crear.php con token artesano devuelve 403 Forbidden');

// 3.9 POST /api/usuarios/cambiar-rol.php en usuario regular (HTTP 200 OK)
$changeRoleRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/cambiar-rol.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id'  => $newUserId,
    'rol' => 'asistente'
]));
$logTrace('9. POST /api/usuarios/cambiar-rol.php (Actualización de Rol: HTTP 200 OK)', $changeRoleRes);

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
$logTrace('10. POST /api/usuarios/cambiar-rol.php (Salvaguarda ID #1: HTTP 403 Forbidden)', $demoteRootHttpRes);

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
$logTrace('11. POST /api/usuarios/cambiar-rol.php (Artesano No Autorizado: HTTP 403)', $artisanChangeRes);

TestHelper::assertSame(403, $artisanChangeRes['status'], 'HTTP POST /api/usuarios/cambiar-rol.php con token artesano devuelve 403 Forbidden');

// 3.12 Preflight CORS OPTIONS en /api/usuarios/index.php (HTTP 200 o 204)
$corsRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/usuarios/index.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: GET',
    'Access-Control-Request-Headers: Authorization, Content-Type',
]);
$logTrace('12. OPTIONS /api/usuarios/index.php (Preflight CORS: HTTP 204 No Content)', $corsRes);

TestHelper::assertTrue(
    in_array($corsRes['status'], [200, 204], true),
    'Preflight CORS OPTIONS en /api/usuarios/index.php responde con código válido (' . $corsRes['status'] . ')'
);

// 3.13 POST /api/usuarios/actualizar.php con token admin (HTTP 200 OK)
$renamedHttpTag = 'creador_renombrado_' . time();
$updateUserRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/actualizar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id'       => $newUserId,
    'username' => $renamedHttpTag
]));
$logTrace('13. POST /api/usuarios/actualizar.php (Modificar Nombre: HTTP 200 OK)', $updateUserRes);

TestHelper::assertSame(200, $updateUserRes['status'], 'HTTP POST /api/usuarios/actualizar.php con admin devuelve 200 OK');
TestHelper::assertTrue($updateUserRes['json']['exito'] ?? false, 'Respuesta contiene exito: true');
TestHelper::assertSame($renamedHttpTag, $updateUserRes['json']['datos']['username'] ?? '', 'Username devuelto coincide con el nuevo');

// 3.14 POST /api/usuarios/actualizar.php con username duplicado (HTTP 409 Conflict)
$dupUpdateRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/actualizar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id'       => $newUserId,
    'username' => 'admin'
]));
$logTrace('14. POST /api/usuarios/actualizar.php (Duplicado: HTTP 409 Conflict)', $dupUpdateRes);

TestHelper::assertSame(409, $dupUpdateRes['status'], 'HTTP POST /api/usuarios/actualizar.php con duplicado devuelve 409 Conflict');

// 3.15 POST /api/usuarios/actualizar.php con token de no-admin (HTTP 403 Forbidden)
$forbiddenUpdateRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/actualizar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id'       => $newUserId,
    'username' => 'hacked_name'
]));
$logTrace('15. POST /api/usuarios/actualizar.php (Artesano No Autorizado: HTTP 403)', $forbiddenUpdateRes);

TestHelper::assertSame(403, $forbiddenUpdateRes['status'], 'HTTP POST /api/usuarios/actualizar.php con artesano devuelve 403 Forbidden');

// 3.16 POST /api/usuarios/restablecer-password.php autogenerada (HTTP 200 OK)
$resetAutoHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/restablecer-password.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $newUserId
]));
$logTrace('16. POST /api/usuarios/restablecer-password.php (Autogenerada: HTTP 200 OK)', $resetAutoHttpRes);

TestHelper::assertSame(200, $resetAutoHttpRes['status'], 'HTTP POST /api/usuarios/restablecer-password.php autogenerada devuelve 200 OK');
TestHelper::assertTrue($resetAutoHttpRes['json']['datos']['es_autogenerada'] ?? false, 'Respuesta indica es_autogenerada: true');
TestHelper::assertTrue(!empty($resetAutoHttpRes['json']['datos']['password_temporal']), 'Respuesta incluye password_temporal');

// 3.17 POST /api/usuarios/restablecer-password.php manual (HTTP 200 OK)
$resetManualHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/restablecer-password.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id'              => $newUserId,
    'nueva_password'  => 'NuevaClaveManual2026'
]));
$logTrace('17. POST /api/usuarios/restablecer-password.php (Manual: HTTP 200 OK)', $resetManualHttpRes);

TestHelper::assertSame(200, $resetManualHttpRes['status'], 'HTTP POST /api/usuarios/restablecer-password.php manual devuelve 200 OK');
TestHelper::assertFalse($resetManualHttpRes['json']['datos']['es_autogenerada'] ?? true, 'Respuesta indica es_autogenerada: false');

// 3.18 POST /api/usuarios/restablecer-password.php con no-admin (HTTP 403 Forbidden)
$forbiddenResetRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/restablecer-password.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => 1
]));
$logTrace('18. POST /api/usuarios/restablecer-password.php (Artesano No Autorizado: HTTP 403)', $forbiddenResetRes);

TestHelper::assertSame(403, $forbiddenResetRes['status'], 'HTTP POST /api/usuarios/restablecer-password.php con artesano devuelve 403 Forbidden');

// 3.19 POST /api/usuarios/eliminar.php intentando borrar ID #1 (HTTP 403 Forbidden)
$deleteRootHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/eliminar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id' => 1
]));
$logTrace('19. POST /api/usuarios/eliminar.php (Salvaguarda ID #1: HTTP 403 Forbidden)', $deleteRootHttpRes);

TestHelper::assertSame(403, $deleteRootHttpRes['status'], 'HTTP POST /api/usuarios/eliminar.php contra ID #1 devuelve 403 Forbidden');

// 3.20 POST /api/usuarios/eliminar.php intentando borrar usuario con creaciones (HTTP 409 Conflict)
$deleteWithCreationsRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/eliminar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id' => 2 // artesana_ana tiene piezas en el catálogo
]));
$logTrace('20. POST /api/usuarios/eliminar.php (Conflicto Creaciones: HTTP 409 Conflict)', $deleteWithCreationsRes);

TestHelper::assertSame(409, $deleteWithCreationsRes['status'], 'HTTP POST /api/usuarios/eliminar.php contra usuario con creaciones devuelve 409 Conflict');

// 3.21 POST /api/usuarios/eliminar.php con no-admin (HTTP 403 Forbidden)
$forbiddenDeleteRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/eliminar.php', [
    'Authorization: Bearer ' . $artisanToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $newUserId
]));
$logTrace('21. POST /api/usuarios/eliminar.php (Artesano No Autorizado: HTTP 403)', $forbiddenDeleteRes);

TestHelper::assertSame(403, $forbiddenDeleteRes['status'], 'HTTP POST /api/usuarios/eliminar.php con artesano devuelve 403 Forbidden');

// 3.22 POST /api/usuarios/eliminar.php borrando usuario temporal sin creaciones (HTTP 200 OK)
$deleteHttpRes = TestHelper::curl('POST', 'http://localhost:8000/api/usuarios/eliminar.php', [
    'Authorization: Bearer ' . $adminToken,
    'Content-Type: application/json'
], json_encode([
    'id' => $newUserId
]));
$logTrace('22. POST /api/usuarios/eliminar.php (Eliminación Exitosa sin Creaciones: HTTP 200 OK)', $deleteHttpRes);

TestHelper::assertSame(200, $deleteHttpRes['status'], 'HTTP POST /api/usuarios/eliminar.php en usuario sin creaciones devuelve 200 OK');
TestHelper::assertTrue($deleteHttpRes['json']['exito'] ?? false, 'Respuesta de eliminación contiene exito: true');

// Guardar log de trazas HTTP
file_put_contents(dirname(__DIR__) . '/logs/subfase-3.3-http.log', $httpLogBuffer);

// Limpieza final de seguridad por si no se hubiera eliminado
$repo->delete($newUserId);

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
