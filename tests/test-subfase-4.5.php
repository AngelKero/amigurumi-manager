<?php
/**
 * Test Suite: Subfase 4.5 - Directorio de Creadores & Roles RBAC
 * Feature 008 · Plan Maestro Fase 4 (009) · Algodón Nórdico Design System
 *
 * Valida:
 * 1. Auditoría estática: usuarios_content.php sin $usuariosList, tabla server-driven,
 *    filtro de estado, modal de restablecimiento de contraseña registrado.
 * 2. Backend: UsuarioRepository::getRoleCounts(), UsuarioService::listUsers() con resumen,
 *    api/usuarios/index.php con metadata resumen, salvaguarda ID #1 (R-05).
 * 3. RBAC estricto: denegación 401 sin token y 403 para rol no-admin en todos los endpoints.
 * 4. Ciclo de vida completo: alta (201), cambio de rol (200), reseteo de clave (200),
 *    baja lógica (200), salvaguarda ID #1 (403), integridad referencial (409) y reactivación (200).
 * 5. HTTP en vivo contra servidor local con registro de trazas en logs/subfase-4.5-http.log.
 *
 * Prerequisito: servidor local `php -S localhost:8000` en ejecución en la raíz.
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Services\UsuarioService;
use App\Services\AuthService;
use App\Repositories\UsuarioRepository;

$root = dirname(__DIR__);

TestHelper::init('Subfase 4.5: Directorio de Creadores & Roles RBAC');

// Inicializar archivo de logs HTTP
$httpLogFile = "$root/logs/subfase-4.5-http.log";
if (!is_dir("$root/logs")) {
    mkdir("$root/logs", 0777, true);
}
file_put_contents($httpLogFile, "=== LOG DE TRAZAS HTTP - SUBFASE 4.5 (" . date('Y-m-d H:i:s') . ") ===\n\n");

$logTrace = function (string $title, array $res) use ($httpLogFile): void {
    $entry = sprintf(
        "[%s] %s\nStatus: %s\nHeaders: %s\nBody: %s\n------------------------------------------------------------\n\n",
        date('Y-m-d H:i:s'),
        $title,
        $res['status'] ?? 'N/A',
        json_encode($res['headers'] ?? [], JSON_UNESCAPED_SLASHES),
        $res['body'] ?? ''
    );
    file_put_contents($httpLogFile, $entry, FILE_APPEND);
};

// =============================================================================
// 1. AUDITORÍA ESTÁTICA: Vistas, Componentes y Módulo JS
// =============================================================================
TestHelper::section('1. Auditoría Estática: Vistas, Modales y Módulo users.js');

$usuariosContent = (string)@file_get_contents("$root/views/pages/usuarios_content.php");
TestHelper::assertFalse(str_contains($usuariosContent, '$usuariosList ='), 'usuarios_content.php no contiene el array mock $usuariosList');
TestHelper::assertFalse(str_contains($usuariosContent, 'this.closest(\'tr\').remove()'), 'usuarios_content.php no contiene handlers onclick con remove() inline');
TestHelper::assertStringContains('id="tablaUsuarios"', $usuariosContent, 'La tabla conserva id="tablaUsuarios"');
TestHelper::assertStringContains('id="tablaUsuariosBody"', $usuariosContent, 'El cuerpo de la tabla tiene id="tablaUsuariosBody" para carga dinámica');
TestHelper::assertStringContains('id="filtroEstadoUsuarios"', $usuariosContent, 'Incluye estación de filtros #filtroEstadoUsuarios');
TestHelper::assertStringContains('data-estado="activos"', $usuariosContent, 'Filtro incluye opción "activos"');
TestHelper::assertStringContains('data-estado="inactivos"', $usuariosContent, 'Filtro incluye opción "inactivos"');
TestHelper::assertStringContains('data-estado="todos"', $usuariosContent, 'Filtro incluye opción "todos"');
TestHelper::assertStringContains('id="emptyStateUsuarios"', $usuariosContent, 'Incluye contenedor #emptyStateUsuarios');
TestHelper::assertStringContains('id="paginacionUsuarios"', $usuariosContent, 'Incluye contenedor #paginacionUsuarios');
TestHelper::assertStringContains('id="kpiTotalUsers"', $usuariosContent, 'Tarjeta KPI Total Usuarios tiene id="kpiTotalUsers"');
TestHelper::assertStringContains('id="kpiAdminUsers"', $usuariosContent, 'Tarjeta KPI Administradores tiene id="kpiAdminUsers"');
TestHelper::assertStringContains('id="kpiArtesanoUsers"', $usuariosContent, 'Tarjeta KPI Artesanos tiene id="kpiArtesanoUsers"');
TestHelper::assertStringContains('id="kpiAsistenteUsers"', $usuariosContent, 'Tarjeta KPI Asistentes tiene id="kpiAsistenteUsers"');
TestHelper::assertStringContains('id="usuariosGlobalAlert"', $usuariosContent, 'Incluye #usuariosGlobalAlert para feedback accesible');

// Modal crear usuario
$modalCrear = (string)@file_get_contents("$root/views/components/modal_crear_usuario.php");
TestHelper::assertStringContains('id="formCrearUsuario"', $modalCrear, 'modal_crear_usuario.php tiene id="formCrearUsuario"');
TestHelper::assertStringContains('novalidate', $modalCrear, 'modal_crear_usuario.php tiene atributo novalidate');
TestHelper::assertStringContains('id="usuarioAlert"', $modalCrear, 'modal_crear_usuario.php tiene alerta accesible #usuarioAlert');
TestHelper::assertStringContains('role="alert"', $modalCrear, 'Alerta de crear usuario tiene role="alert"');

// Modal editar rol
$modalRol = (string)@file_get_contents("$root/views/components/modal_editar_rol_usuario.php");
TestHelper::assertStringContains('id="formEditarRolUsuario"', $modalRol, 'modal_editar_rol_usuario.php tiene id="formEditarRolUsuario"');
TestHelper::assertStringContains('id="adminRootWarning"', $modalRol, 'modal_editar_rol_usuario.php tiene aviso de salvaguarda #adminRootWarning');
TestHelper::assertStringContains('id="selectEditarRol"', $modalRol, 'modal_editar_rol_usuario.php tiene selector #selectEditarRol');

// Modal restablecer password
$modalReset = (string)@file_get_contents("$root/views/components/modal_restablecer_password.php");
TestHelper::assertTrue(is_file("$root/views/components/modal_restablecer_password.php"), 'Componente modal_restablecer_password.php existe');
TestHelper::assertStringContains('id="formRestablecerPassword"', $modalReset, 'modal_restablecer_password.php tiene id="formRestablecerPassword"');
TestHelper::assertStringContains('id="nuevaPasswordInput"', $modalReset, 'modal_restablecer_password.php tiene input #nuevaPasswordInput');
TestHelper::assertStringContains('id="tempPasswordDisplay"', $modalReset, 'modal_restablecer_password.php tiene contenedor #tempPasswordDisplay');

// Registro de modales en usuarios.php
$usuariosPhp = (string)@file_get_contents("$root/usuarios.php");
TestHelper::assertStringContains('modal_restablecer_password', $usuariosPhp, 'usuarios.php registra modal_restablecer_password en $modals');

// Linting de users.js con node --check
$nodeCheckUsers = shell_exec('node --check ' . escapeshellarg("$root/src/js/modules/users.js") . ' 2>&1');
TestHelper::assertSame('', trim((string)$nodeCheckUsers), 'users.js pasa node --check sin errores de sintaxis');

$usersJs = (string)@file_get_contents("$root/src/js/modules/users.js");
TestHelper::assertStringContains('export function initUsers', $usersJs, 'users.js exporta initUsers()');
TestHelper::assertStringContains('fetchUsers', $usersJs, 'users.js implementa carga asíncrona fetchUsers()');
TestHelper::assertStringContains('ROL_URL', $usersJs, 'users.js define ROL_URL');
TestHelper::assertStringContains('RESET_PWD_URL', $usersJs, 'users.js define RESET_PWD_URL');
TestHelper::assertStringContains('ELIMINAR_URL', $usersJs, 'users.js define ELIMINAR_URL');
TestHelper::assertStringContains('REACTIVAR_URL', $usersJs, 'users.js define REACTIVAR_URL');
TestHelper::assertFalse(str_contains($usersJs, 'innerHTML = `') && str_contains($usersJs, '${escUsername}'), 'users.js no interpole datos dinámicos directamente en innerHTML de fila completa');

// =============================================================================
// 2. BACKEND: Repositorio, Servicio y Extensión de Resumen KPI
// =============================================================================
TestHelper::section('2. Backend: getRoleCounts() y listUsers() con Resumen');

$repo = new UsuarioRepository();
$service = new UsuarioService($repo);

// Conteo global de roles en activos
$countsActivos = $repo->getRoleCounts(true);
TestHelper::assertTrue(is_array($countsActivos), 'getRoleCounts(true) retorna array');
TestHelper::assertTrue(isset($countsActivos['total']), 'getRoleCounts() incluye clave total');
TestHelper::assertTrue(isset($countsActivos['admin']), 'getRoleCounts() incluye clave admin');
TestHelper::assertTrue(isset($countsActivos['artesano']), 'getRoleCounts() incluye clave artesano');
TestHelper::assertTrue(isset($countsActivos['asistente']), 'getRoleCounts() incluye clave asistente');
TestHelper::assertSame(
    $countsActivos['total'],
    $countsActivos['admin'] + $countsActivos['artesano'] + $countsActivos['asistente'],
    'La suma de roles coincide exactamente con el total de activos'
);
TestHelper::assertTrue($countsActivos['admin'] >= 1, 'Existe al menos 1 administrador activo (ID #1)');

// Conteo en inactivos y todos
$countsInactivos = $repo->getRoleCounts(false);
TestHelper::assertTrue(is_array($countsInactivos), 'getRoleCounts(false) retorna array para inactivos');
$countsTodos = $repo->getRoleCounts(null);
TestHelper::assertTrue(is_array($countsTodos), 'getRoleCounts(null) retorna array para todos');
TestHelper::assertSame(
    $countsTodos['total'],
    $countsActivos['total'] + $countsInactivos['total'],
    'Total de todas las cuentas = activos + inactivos'
);

// listUsers con resumen enriquecido
$listActivos = $service->listUsers(1, 10, true);
TestHelper::assertTrue(isset($listActivos['resumen']), 'listUsers() retorna clave resumen');
TestHelper::assertSame($countsActivos['total'], $listActivos['resumen']['total'], 'Resumen en listUsers coincide con getRoleCounts');
TestHelper::assertTrue(isset($listActivos['paginacion']), 'listUsers() conserva sobre paginacion');
TestHelper::assertTrue(isset($listActivos['usuarios']), 'listUsers() conserva lista usuarios');

// Salvaguarda ID #1 en capa de servicio (R-05)
try {
    $service->deleteUser(1);
    TestHelper::assert(false, 'deleteUser(1) debió lanzar RuntimeException 403');
} catch (RuntimeException $e) {
    TestHelper::assertSame(403, (int)$e->getCode(), 'deleteUser(1) arroja HTTP 403 por salvaguarda de ID #1 (R-05)');
}

try {
    $service->updateRole(1, 'artesano');
    TestHelper::assert(false, 'updateRole(1, "artesano") debió lanzar RuntimeException 403');
} catch (RuntimeException $e) {
    TestHelper::assertSame(403, (int)$e->getCode(), 'updateRole(1, "artesano") arroja HTTP 403 por salvaguarda de ID #1 (R-05)');
}

// =============================================================================
// 3. SEGURIDAD RBAC: Protección estricta de Endpoints de Usuarios
// =============================================================================
TestHelper::section('3. Seguridad RBAC: Denegación 401 y 403 en api/usuarios/*');

$baseUrl = 'http://localhost:8000';

// Obtener token de admin y de artesano para pruebas
$authService = new AuthService();
$adminLogin = $authService->authenticate('admin', 'admin123');
$adminToken = $adminLogin['token'] ?? '';
TestHelper::assertTrue(!empty($adminToken), 'Token de admin obtenido exitosamente');

$artisanLogin = $authService->authenticate('artesana_ana', 'admin123');
$artisanToken = $artisanLogin['token'] ?? '';
TestHelper::assertTrue(!empty($artisanToken), 'Token de artesano obtenido exitosamente');

$adminHeaders = ['Authorization: Bearer ' . $adminToken];
$artisanHeaders = ['Authorization: Bearer ' . $artisanToken];

$endpoints = [
    ['GET', '/api/usuarios/index.php'],
    ['POST', '/api/usuarios/crear.php'],
    ['POST', '/api/usuarios/cambiar-rol.php'],
    ['POST', '/api/usuarios/restablecer-password.php'],
    ['POST', '/api/usuarios/eliminar.php'],
    ['POST', '/api/usuarios/reactivar.php'],
];

foreach ($endpoints as [$method, $uri]) {
    // 1. Sin token -> 401
    $noAuthRes = TestHelper::curl($method, $baseUrl . $uri);
    TestHelper::assertSame(401, $noAuthRes['status'], "Acceso sin token a {$method} {$uri} responde 401 Unauthorized");

    // 2. Con rol artesano -> 403
    $artisanRes = TestHelper::curl($method, $baseUrl . $uri, $artisanHeaders);
    TestHelper::assertSame(403, $artisanRes['status'], "Acceso con rol artesano a {$method} {$uri} responde 403 Forbidden");
}

// =============================================================================
// 4. CICLO DE VIDA COMPLETO DE USUARIO (CRUD + Soft Delete + Reactivación)
// =============================================================================
TestHelper::section('4. Ciclo de Vida: Alta, Cambio de Rol, Reseteo Clave, Baja Lógica y Reactivación');

$suffix = time() . '_' . rand(100, 999);
$testUsername = "test_usr_{$suffix}";
$testPassword = "Password!{$suffix}";

// A. Alta exitosa (POST /api/usuarios/crear.php)
$createRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/crear.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'username' => $testUsername,
    'password' => $testPassword,
    'rol'      => 'artesano',
    'whatsapp' => '5599887766',
]));
$logTrace('Alta de nuevo creador (POST /api/usuarios/crear.php)', $createRes);

TestHelper::assertSame(201, $createRes['status'], 'Creación de usuario responde 201 Created');
TestHelper::assertTrue($createRes['json']['exito'] ?? false, 'Respuesta indica exito: true');
$createdUser = $createRes['json']['datos'] ?? [];
$testUserId = (int)($createdUser['id'] ?? 0);
TestHelper::assertTrue($testUserId > 0, 'ID de nuevo usuario es positivo');
TestHelper::assertSame($testUsername, $createdUser['username'] ?? '', 'Nombre de usuario coincide');
TestHelper::assertSame('artesano', $createdUser['rol'] ?? '', 'Rol asignado es artesano');
TestHelper::assertSame('5599887766', $createdUser['whatsapp'] ?? '', 'WhatsApp comercial asignado');

// A2. Validaciones de esquema en alta
$dupRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/crear.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'username' => $testUsername,
    'password' => '123456',
    'rol'      => 'artesano',
]));
TestHelper::assertSame(409, $dupRes['status'], 'Intento de crear usuario con username duplicado responde 409 Conflict');

$shortRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/crear.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'username' => "short_{$suffix}",
    'password' => '123',
    'rol'      => 'artesano',
]));
TestHelper::assertSame(422, $shortRes['status'], 'Intento de crear usuario con clave menor a 6 caracteres responde 422 Unprocessable Entity');

// B. Modificación de rol (POST /api/usuarios/cambiar-rol.php)
$roleRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/cambiar-rol.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id'  => $testUserId,
    'rol' => 'asistente',
]));
$logTrace('Modificación de rol a asistente', $roleRes);
TestHelper::assertSame(200, $roleRes['status'], 'Cambio de rol responde 200 OK');
TestHelper::assertSame('asistente', $roleRes['json']['datos']['rol'] ?? '', 'Rol actualizado a asistente');

// B2. Salvaguarda ID #1 contra degradación
$adminDegradeRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/cambiar-rol.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id'  => 1,
    'rol' => 'artesano',
]));
TestHelper::assertSame(403, $adminDegradeRes['status'], 'Intento de degradar rol de ID #1 responde 403 Forbidden (R-05)');

// C. Restablecimiento de contraseña manual y autogenerada
$resetManualRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/restablecer-password.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id'              => $testUserId,
    'nueva_password'  => "NuevaClave!{$suffix}",
]));
$logTrace('Restablecimiento de clave manual', $resetManualRes);
TestHelper::assertSame(200, $resetManualRes['status'], 'Restablecimiento de clave manual responde 200 OK');
TestHelper::assertFalse($resetManualRes['json']['datos']['es_autogenerada'] ?? true, 'Marca es_autogenerada: false con clave provista');

$resetAutoRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/restablecer-password.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => $testUserId,
]));
$logTrace('Restablecimiento de clave autogenerada', $resetAutoRes);
TestHelper::assertSame(200, $resetAutoRes['status'], 'Restablecimiento con clave autogenerada responde 200 OK');
TestHelper::assertTrue($resetAutoRes['json']['datos']['es_autogenerada'] ?? false, 'Marca es_autogenerada: true');
$tempPwd = $resetAutoRes['json']['datos']['password_temporal'] ?? '';
TestHelper::assertTrue(str_starts_with($tempPwd, 'Crochet!'), 'Clave temporal autogenerada inicia con prefijo Crochet!');

// D. Baja lógica (POST /api/usuarios/eliminar.php) (R-01)
// D1. Intento contra ID #1
$delAdminRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => 1,
]));
TestHelper::assertSame(403, $delAdminRes['status'], 'Intento de baja de ID #1 responde 403 Forbidden (R-05)');

// D2. Intento contra usuario con creaciones activas (artesana_ana = ID 2)
$delAnaRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => 2,
]));
TestHelper::assertSame(409, $delAnaRes['status'], 'Intento de baja de usuario con creaciones activas responde 409 Conflict (integridad referencial)');

// D3. Baja lógica exitosa de usuario sin creaciones
$delUserRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => $testUserId,
]));
$logTrace('Baja lógica de usuario de prueba', $delUserRes);
TestHelper::assertSame(200, $delUserRes['status'], 'Baja lógica de usuario responde 200 OK');
TestHelper::assertSame(0, $delUserRes['json']['datos']['activo'] ?? 1, 'Respuesta confirma activo: 0');

// D4. Segundo intento de baja sobre usuario ya inactivo -> 409
$delTwiceRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => $testUserId,
]));
TestHelper::assertSame(409, $delTwiceRes['status'], 'Segundo intento de baja sobre usuario inactivo responde 409 Conflict');

// E. Reactivación de cuenta (POST /api/usuarios/reactivar.php)
$reactivateRes = TestHelper::curl('POST', $baseUrl . '/api/usuarios/reactivar.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => $testUserId,
]));
$logTrace('Reactivación de cuenta dada de baja', $reactivateRes);
TestHelper::assertSame(200, $reactivateRes['status'], 'Reactivación de cuenta responde 200 OK');
TestHelper::assertSame(1, $reactivateRes['json']['datos']['activo'] ?? 0, 'Respuesta confirma activo: 1 tras reactivación');

// E2. Segundo intento de reactivación sobre usuario ya activo -> 409
$reactivateTwice = TestHelper::curl('POST', $baseUrl . '/api/usuarios/reactivar.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => $testUserId,
]));
TestHelper::assertSame(409, $reactivateTwice['status'], 'Intento de reactivar cuenta ya activa responde 409 Conflict');

// Limpieza final del usuario de prueba (baja lógica)
TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', array_merge($adminHeaders, ['Content-Type: application/json']), json_encode([
    'id' => $testUserId,
]));

// =============================================================================
// 5. PRUEBAS HTTP EN VIVO Y CABECERAS CSP
// =============================================================================
TestHelper::section('5. HTTP en Vivo: Listado con Filtros y CSP en usuarios.php');

// Listado de usuarios activos con resumen
$listHttpRes = TestHelper::curl('GET', $baseUrl . '/api/usuarios/index.php?estado=activos&pagina=1&limite=20', $adminHeaders);
$logTrace('GET /api/usuarios/index.php?estado=activos', $listHttpRes);
TestHelper::assertSame(200, $listHttpRes['status'], 'GET /api/usuarios/index.php?estado=activos responde 200 OK');
TestHelper::assertTrue(is_array($listHttpRes['json']['datos'] ?? null), 'Respuesta contiene datos en array');
TestHelper::assertTrue(isset($listHttpRes['json']['paginacion']), 'Respuesta contiene sobre de paginacion');
TestHelper::assertTrue(isset($listHttpRes['json']['meta']['resumen']) || isset($listHttpRes['json']['resumen']), 'Respuesta contiene resumen de KPIs');

// Listado de usuarios inactivos
$listInactivosHttp = TestHelper::curl('GET', $baseUrl . '/api/usuarios/index.php?estado=inactivos', $adminHeaders);
$logTrace('GET /api/usuarios/index.php?estado=inactivos', $listInactivosHttp);
TestHelper::assertSame(200, $listInactivosHttp['status'], 'GET /api/usuarios/index.php?estado=inactivos responde 200 OK');

// Listado de todos
$listTodosHttp = TestHelper::curl('GET', $baseUrl . '/api/usuarios/index.php?estado=todos', $adminHeaders);
$logTrace('GET /api/usuarios/index.php?estado=todos', $listTodosHttp);
TestHelper::assertSame(200, $listTodosHttp['status'], 'GET /api/usuarios/index.php?estado=todos responde 200 OK');

// Vista usuarios.php con CSP
$pageRes = TestHelper::curl('GET', $baseUrl . '/usuarios.php');
TestHelper::assertSame(200, $pageRes['status'], 'usuarios.php responde HTTP 200 OK');
$cspHeader = $pageRes['headers']['content-security-policy'] ?? '';
TestHelper::assertTrue(!empty($cspHeader), 'usuarios.php emite cabecera Content-Security-Policy estricta');
TestHelper::assertStringContains("script-src 'self'", $cspHeader, 'CSP permite script-src self');
TestHelper::assertStringContains("frame-ancestors 'none'", $cspHeader, 'CSP protege contra clickjacking (frame-ancestors none)');

// =============================================================================
// RESUMEN FINAL DE LA SUITE
// =============================================================================
TestHelper::summary();
