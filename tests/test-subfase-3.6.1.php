<?php
/**
 * Test Suite: Subfase 3.6.1 - Acceso, Autorización, IDOR & Blindaje RBAC (Opción B)
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Alcance y Dimensiones Auditadas:
 * 1. Control de Acceso Vertical (RBAC) & Rutas Protegidas (OWASP A01:2021):
 *    - Peticiones anónimas a endpoints protegidos (401 Unauthorized).
 *    - Peticiones con token malformado o adulterado (401 Unauthorized).
 *    - Rol 'asistente' bloqueado en mutaciones y gestión de usuarios (403 Forbidden).
 *    - Rol 'artesano' bloqueado en gestión de usuarios (403 Forbidden).
 * 2. Prevención IDOR Horizontal en Creaciones (OWASP A01:2021):
 *    - Artesana Ana (id: 2) intentando editar, eliminar, restaurar, ajustar stock o togglear encargo en piezas de Admin (id: 1) -> 403 Forbidden.
 *    - Admin con privilegios globales para administrar cualquier creación.
 * 3. Prevención IDOR Horizontal en Pedidos (OWASP A01:2021):
 *    - Artesana Ana intentando consultar, cambiar estado o cancelar pedidos de piezas de Admin -> 403 Forbidden.
 *    - Artesana Ana intentando crear encargo manual referenciando piezas ajenas -> 403 Forbidden.
 *    - Aislamiento estricto de listado de pedidos: Ana solo ve pedidos de sus creaciones.
 * 4. Salvaguardas Inmutables de Cuenta Raíz (ID #1) y Auto-eliminación:
 *    - Imposibilidad de alterar rol de ID #1 (@admin) -> 403 Forbidden.
 *    - Imposibilidad de eliminar a ID #1 -> 403 Forbidden.
 *    - Prevención de auto-eliminación de la cuenta en sesión activa -> 403 Forbidden.
 * 5. Aislamiento de Creaciones Inactivas y Usuarios Desactivados:
 *    - Consulta pública de detalle por ID de pieza con activo = 0 -> 404 Not Found.
 *    - Catálogo público excluye piezas inactivas.
 *    - Usuario desactivado bloqueado de autenticación y tokens revocados de inmediato.
 * 6. Restricción Estricta de Métodos HTTP (405 Method Not Allowed):
 *    - Verificación de 405 ante método erróneo en los 25 controladores de la API.
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
use App\Core\Database;
use App\Core\TokenManager;
use App\Repositories\CreacionRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use App\Services\CreacionService;
use App\Services\PedidoService;
use App\Services\UsuarioService;

// Iniciar suite
TestHelper::init('Subfase 3.6.1: Acceso, Autorización, IDOR & Blindaje RBAC (OWASP A01)');

// Desactivar terminación forzada en Response para pruebas en memoria
Response::setExitOnSend(false);

$pdo = Database::getInstance();
$usuarioRepo = new UsuarioRepository();
$creacionRepo = new CreacionRepository();
$pedidoRepo = new PedidoRepository();

$authService = new AuthService($usuarioRepo);
$usuarioService = new UsuarioService($usuarioRepo, $creacionRepo);
$creacionService = new CreacionService($creacionRepo);
$pedidoService = new PedidoService($pedidoRepo, $creacionRepo);

$baseUrl = 'http://localhost:8000';

// Helper para extraer mensaje de error estandarizado de la respuesta JSON
$getErrMsg = function (array $res): string {
    return (string)($res['json']['error']['mensaje'] ?? $res['json']['mensaje'] ?? '');
};

// ============================================================================
// SECCIÓN 1: Emisión de Tokens y Preparación de Roles
// ============================================================================
TestHelper::section('1. Preparación de Credenciales y Emisión de Tokens por Rol');

// Login Admin
$adminAuth = $authService->authenticate('admin', 'admin123');
TestHelper::assertSame(1, $adminAuth['usuario']['id'], 'Admin ID es 1');
TestHelper::assertSame('admin', $adminAuth['usuario']['rol'], 'Admin Rol es admin');
$adminToken = $adminAuth['token'];
$adminHeaders = ['Authorization: Bearer ' . $adminToken, 'Content-Type: application/json'];

// Login Artesana Ana
$anaAuth = $authService->authenticate('artesana_ana', 'admin123');
TestHelper::assertSame(2, $anaAuth['usuario']['id'], 'Artesana Ana ID es 2');
TestHelper::assertSame('artesano', $anaAuth['usuario']['rol'], 'Artesana Ana Rol es artesano');
$anaToken = $anaAuth['token'];
$anaHeaders = ['Authorization: Bearer ' . $anaToken, 'Content-Type: application/json'];

// Login Asistente Leo
$leoAuth = $authService->authenticate('asistente_leo', 'admin123');
TestHelper::assertSame(3, $leoAuth['usuario']['id'], 'Asistente Leo ID es 3');
TestHelper::assertSame('asistente', $leoAuth['usuario']['rol'], 'Asistente Leo Rol es asistente');
$leoToken = $leoAuth['token'];
$leoHeaders = ['Authorization: Bearer ' . $leoToken, 'Content-Type: application/json'];

// ============================================================================
// SECCIÓN 2: Control de Acceso Vertical (RBAC) & Rutas Protegidas
// ============================================================================
TestHelper::section('2. Control de Acceso Vertical (RBAC) & Rutas Protegidas (OWASP A01)');

// 2.1 Peticiones anónimas a endpoints protegidos (401 Unauthorized)
$protectedEndpoints = [
    ['GET', '/api/auth/me.php'],
    ['POST', '/api/auth/cambiar-password.php'],
    ['GET', '/api/usuarios/index.php'],
    ['POST', '/api/usuarios/crear.php'],
    ['POST', '/api/usuarios/cambiar-rol.php'],
    ['POST', '/api/usuarios/eliminar.php'],
    ['POST', '/api/creaciones/crear.php'],
    ['POST', '/api/creaciones/actualizar.php'],
    ['POST', '/api/creaciones/eliminar.php'],
    ['GET', '/api/pedidos/index.php'],
    ['POST', '/api/pedidos/crear.php'],
    ['POST', '/api/pedidos/cambiar-estado.php'],
    ['POST', '/api/pedidos/cancelar.php'],
];

foreach ($protectedEndpoints as [$method, $endpoint]) {
    $res = TestHelper::curl($method, $baseUrl . $endpoint, ['Content-Type: application/json'], json_encode(['id' => 1]));
    TestHelper::assertSame(401, $res['status'], "Acceso anónimo a {$endpoint} retorna 401 Unauthorized");
    TestHelper::assertFalse($res['json']['exito'] ?? true, "Respuesta indica exito=false para {$endpoint}");
}

// 2.2 Token malformado o adulterado (401 Unauthorized)
$forgedHeaders = ['Authorization: Bearer token_falso_adulterado_hmac_invalido', 'Content-Type: application/json'];
$resForged = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', $forgedHeaders);
TestHelper::assertSame(401, $resForged['status'], 'Token manipulado/falso es rechazado con 401 Unauthorized');
TestHelper::assertStringContains('inválido', $getErrMsg($resForged), 'Mensaje explica token inválido');

// 2.3 Rol 'asistente' bloqueado en gestión de usuarios (403 Forbidden)
$adminOnlyUserEndpoints = [
    ['GET', '/api/usuarios/index.php', null],
    ['POST', '/api/usuarios/crear.php', ['username' => 'test_hacker', 'password' => 'clave123', 'rol' => 'artesano']],
    ['POST', '/api/usuarios/cambiar-rol.php', ['id' => 2, 'rol' => 'asistente']],
    ['POST', '/api/usuarios/actualizar.php', ['id' => 2, 'username' => 'ana_hackeada']],
    ['POST', '/api/usuarios/restablecer-password.php', ['id' => 2, 'password' => 'nueva123']],
    ['POST', '/api/usuarios/eliminar.php', ['id' => 2]],
    ['POST', '/api/usuarios/reactivar.php', ['id' => 2]],
];

foreach ($adminOnlyUserEndpoints as [$method, $endpoint, $bodyData]) {
    $body = $bodyData !== null ? json_encode($bodyData) : null;
    $res = TestHelper::curl($method, $baseUrl . $endpoint, $leoHeaders, $body);
    TestHelper::assertSame(403, $res['status'], "Asistente Leo bloqueado en {$endpoint} con 403 Forbidden");
    TestHelper::assertFalse($res['json']['exito'] ?? true, "Respuesta indica exito=false para Asistente en {$endpoint}");
}

// 2.4 Rol 'asistente' bloqueado en mutaciones de catálogo e inventario (403 Forbidden)
$artisanCreacionEndpoints = [
    ['POST', '/api/creaciones/crear.php', ['nombre' => 'Test Hacker', 'categoria' => 'Prendas', 'material' => 'Lana', 'dimensiones' => '20cm', 'precio' => 25000]],
    ['POST', '/api/creaciones/actualizar.php', ['id' => 1, 'nombre' => 'Modificado', 'categoria' => 'Prendas', 'material' => 'Lana', 'dimensiones' => '20cm', 'precio' => 25000]],
    ['POST', '/api/creaciones/eliminar.php', ['id' => 1]],
    ['POST', '/api/creaciones/restaurar.php', ['id' => 1]],
    ['POST', '/api/creaciones/ajustar-stock.php', ['id' => 1, 'cantidad_stock' => 10]],
    ['POST', '/api/creaciones/toggle-encargo.php', ['id' => 1, 'es_sobre_encargo' => 1]],
];

foreach ($artisanCreacionEndpoints as [$method, $endpoint, $bodyData]) {
    $body = json_encode($bodyData);
    $res = TestHelper::curl($method, $baseUrl . $endpoint, $leoHeaders, $body);
    TestHelper::assertSame(403, $res['status'], "Asistente Leo bloqueado en {$endpoint} con 403 Forbidden");
    TestHelper::assertFalse($res['json']['exito'] ?? true, "Respuesta exito=false en {$endpoint}");
}

// 2.5 Rol 'asistente' bloqueado en gestión y consulta de pedidos (403 Forbidden)
$orderEndpoints = [
    ['GET', '/api/pedidos/index.php', null],
    ['POST', '/api/pedidos/crear.php', ['cliente_nombre' => 'Cliente Test', 'creacion_id' => 1, 'cantidad' => 1]],
    ['POST', '/api/pedidos/cambiar-estado.php', ['id' => 1, 'estado_pedido' => 'En Proceso']],
    ['POST', '/api/pedidos/cancelar.php', ['id' => 1]],
];

foreach ($orderEndpoints as [$method, $endpoint, $bodyData]) {
    $body = $bodyData !== null ? json_encode($bodyData) : null;
    $res = TestHelper::curl($method, $baseUrl . $endpoint, $leoHeaders, $body);
    TestHelper::assertSame(403, $res['status'], "Asistente Leo bloqueado en {$endpoint} con 403 Forbidden");
}

// 2.6 Rol 'artesano' bloqueado en gestión de usuarios (403 Forbidden)
foreach ($adminOnlyUserEndpoints as [$method, $endpoint, $bodyData]) {
    $body = $bodyData !== null ? json_encode($bodyData) : null;
    $res = TestHelper::curl($method, $baseUrl . $endpoint, $anaHeaders, $body);
    TestHelper::assertSame(403, $res['status'], "Artesana Ana bloqueada en {$endpoint} con 403 Forbidden");
}

// ============================================================================
// SECCIÓN 3: Prevención IDOR Horizontal en Creaciones (OWASP A01)
// ============================================================================
TestHelper::section('3. Prevención IDOR Horizontal en Catálogo / Creaciones');

// Creación #1 ("Dragón Ignis") pertenece a artesano_id = 1 (admin)
$creacion1 = $creacionRepo->findById(1, true);
TestHelper::assertSame(1, (int)$creacion1['artesano_id'], 'Creación #1 pertenece al artesano_id 1 (admin)');

// Creación #3 ("Ajolote Rosado Pastel") pertenece a artesano_id = 2 (artesana_ana)
$creacion3 = $creacionRepo->findById(3, true);
TestHelper::assertSame(2, (int)$creacion3['artesano_id'], 'Creación #3 pertenece al artesano_id 2 (artesana_ana)');

// Creación #5 ("Tote Bag Boho Trapillo") pertenece a artesano_id = 2 (artesana_ana)
$creacion5 = $creacionRepo->findById(5, true);
TestHelper::assertSame(2, (int)$creacion5['artesano_id'], 'Creación #5 pertenece al artesano_id 2 (artesana_ana)');

// 3.1 Verificación en memoria de CreacionService::ensureArtisanOwnership
try {
    $creacionService->ensureArtisanOwnership($creacion1, ['id' => 2, 'rol' => 'artesano']);
    TestHelper::assert(false, 'ensureArtisanOwnership debió lanzar excepción 403');
} catch (\RuntimeException $e) {
    TestHelper::assertSame(403, (int)$e->getCode(), 'ensureArtisanOwnership lanza código 403');
    TestHelper::assertStringContains('No tienes permisos', $e->getMessage(), 'Mensaje explicativo de IDOR en creaciones');
}

// 3.2 Artesana Ana intentando actualizar Pieza #1 (HTTP 403)
$resUpdateIdor = TestHelper::curl('POST', $baseUrl . '/api/creaciones/actualizar.php', $anaHeaders, json_encode([
    'id'          => 1,
    'nombre'      => 'Oso Hackeado por Ana',
    'categoria'   => 'Amigurumis & Figuras',
    'material'    => 'Algodón Premium',
    'dimensiones' => '22 cm',
    'precio'      => 49000,
]));
TestHelper::assertSame(403, $resUpdateIdor['status'], 'Artesana Ana no puede actualizar pieza de Admin (403 IDOR)');
TestHelper::assertStringContains('No tienes permisos', $getErrMsg($resUpdateIdor), 'Feedback IDOR en actualización');

// 3.3 Artesana Ana intentando ajustar stock en Pieza #1 (HTTP 403)
$resStockIdor = TestHelper::curl('POST', $baseUrl . '/api/creaciones/ajustar-stock.php', $anaHeaders, json_encode([
    'id'             => 1,
    'cantidad_stock' => 99,
]));
TestHelper::assertSame(403, $resStockIdor['status'], 'Artesana Ana no puede ajustar stock de pieza de Admin (403 IDOR)');

// 3.4 Artesana Ana intentando toggle de encargo en Pieza #1 (HTTP 403)
$resToggleIdor = TestHelper::curl('POST', $baseUrl . '/api/creaciones/toggle-encargo.php', $anaHeaders, json_encode([
    'id'               => 1,
    'es_sobre_encargo' => 1,
]));
TestHelper::assertSame(403, $resToggleIdor['status'], 'Artesana Ana no puede alternar modo de encargo en pieza de Admin (403 IDOR)');

// 3.5 Artesana Ana intentando eliminar lógicamente Pieza #1 (HTTP 403)
$resDeleteIdor = TestHelper::curl('POST', $baseUrl . '/api/creaciones/eliminar.php', $anaHeaders, json_encode([
    'id' => 1,
]));
TestHelper::assertSame(403, $resDeleteIdor['status'], 'Artesana Ana no puede dar de baja pieza de Admin (403 IDOR)');

// 3.6 Artesana Ana intentando restaurar Pieza #1 (HTTP 403)
$resRestoreIdor = TestHelper::curl('POST', $baseUrl . '/api/creaciones/restaurar.php', $anaHeaders, json_encode([
    'id' => 1,
]));
TestHelper::assertSame(403, $resRestoreIdor['status'], 'Artesana Ana no puede restaurar pieza de Admin (403 IDOR)');

// 3.7 Admin tiene privilegios omnímodos: puede actualizar stock de Pieza #5 (de Ana)
$resAdminStock = TestHelper::curl('POST', $baseUrl . '/api/creaciones/ajustar-stock.php', $adminHeaders, json_encode([
    'id'             => 5,
    'cantidad_stock' => 10,
]));
TestHelper::assertSame(200, $resAdminStock['status'], 'Admin tiene privilegios globales para modificar stock de pieza de Ana (200 OK)');
TestHelper::assertTrue($resAdminStock['json']['exito'], 'Operación de admin fue exitosa');

// Restaurar stock original de Pieza #5 (6 unidades)
TestHelper::curl('POST', $baseUrl . '/api/creaciones/ajustar-stock.php', $adminHeaders, json_encode([
    'id'             => 5,
    'cantidad_stock' => 6,
]));

// ============================================================================
// SECCIÓN 4: Prevención IDOR Horizontal en Pedidos (OWASP A01)
// ============================================================================
TestHelper::section('4. Prevención IDOR Horizontal en Pedidos / Encargos');

// Pedido #1 está asociado a Creación #1 (Admin)
$pedido1 = $pedidoRepo->findById(1);
TestHelper::assertSame(1, (int)($pedido1['creacion']['artesano_id'] ?? 0), 'Pedido #1 pertenece a una creación de Admin');

// Pedido #2 está asociado a Creación #3 (Artesana Ana)
$pedido2 = $pedidoRepo->findById(2);
TestHelper::assertSame(2, (int)($pedido2['creacion']['artesano_id'] ?? 0), 'Pedido #2 pertenece a una creación de Artesana Ana');

// 4.1 Verificación en memoria de PedidoService::ensureArtisanOwnership
try {
    $pedidoService->ensureArtisanOwnership($pedido1, ['id' => 2, 'rol' => 'artesano']);
    TestHelper::assert(false, 'PedidoService::ensureArtisanOwnership debió lanzar excepción 403');
} catch (\RuntimeException $e) {
    TestHelper::assertSame(403, (int)$e->getCode(), 'ensureArtisanOwnership en pedidos lanza 403');
    TestHelper::assertStringContains('otro artesano', $e->getMessage(), 'Mensaje explicativo de IDOR en pedidos');
}

// 4.2 Artesana Ana intentando cambiar estado a Pedido #1 (HTTP 403)
$resOrderStatusIdor = TestHelper::curl('POST', $baseUrl . '/api/pedidos/cambiar-estado.php', $anaHeaders, json_encode([
    'id'            => 1,
    'estado_pedido' => 'En Proceso',
]));
TestHelper::assertSame(403, $resOrderStatusIdor['status'], 'Artesana Ana no puede cambiar estado de pedido de Admin (403 IDOR)');

// 4.3 Artesana Ana intentando cancelar Pedido #1 (HTTP 403)
$resOrderCancelIdor = TestHelper::curl('POST', $baseUrl . '/api/pedidos/cancelar.php', $anaHeaders, json_encode([
    'id' => 1,
]));
TestHelper::assertSame(403, $resOrderCancelIdor['status'], 'Artesana Ana no puede cancelar pedido de Admin (403 IDOR)');

// 4.4 Artesana Ana intentando crear pedido manual para Creación #1 (de Admin) (HTTP 403)
$resManualOrderIdor = TestHelper::curl('POST', $baseUrl . '/api/pedidos/crear.php', $anaHeaders, json_encode([
    'cliente_nombre'   => 'Cliente Ficticio',
    'cliente_contacto' => '5512345678',
    'creacion_id'      => 1,
    'cantidad'         => 1,
    'estado_pago'      => 'Anticipo 50%',
]));
TestHelper::assertSame(403, $resManualOrderIdor['status'], 'Artesana Ana no puede registrar pedido manual para creación de Admin (403 IDOR)');
TestHelper::assertStringContains('otros artesanos', $getErrMsg($resManualOrderIdor), 'Feedback IDOR en creación manual');

// 4.5 Aislamiento de listado de pedidos: Ana solo ve pedidos de sus piezas
$resAnaOrders = TestHelper::curl('GET', $baseUrl . '/api/pedidos/index.php', $anaHeaders);
TestHelper::assertSame(200, $resAnaOrders['status'], 'Artesana Ana puede listar pedidos');
$anaItems = $resAnaOrders['json']['datos'] ?? [];
$allBelongToAna = true;
foreach ($anaItems as $item) {
    if ((int)($item['creacion']['artesano_id'] ?? 0) !== 2) {
        $allBelongToAna = false;
        break;
    }
}
TestHelper::assertTrue($allBelongToAna, 'Todos los pedidos devueltos a Ana pertenecen exclusivamente a sus creaciones');

// 4.6 Admin ve pedidos de todos los artesanos
$resAdminOrders = TestHelper::curl('GET', $baseUrl . '/api/pedidos/index.php', $adminHeaders);
TestHelper::assertSame(200, $resAdminOrders['status'], 'Admin lista pedidos globalmente');
$adminItems = $resAdminOrders['json']['datos'] ?? [];
$hasAna = false;
$hasAdmin = false;
foreach ($adminItems as $item) {
    $artId = (int)($item['creacion']['artesano_id'] ?? 0);
    if ($artId === 1) $hasAdmin = true;
    if ($artId === 2) $hasAna = true;
}
TestHelper::assertTrue($hasAdmin && $hasAna, 'Listado de pedidos para Admin contiene creaciones de múltiples artesanos');

// ============================================================================
// SECCIÓN 5: Salvaguardas Inmutables de la Cuenta Raíz (ID #1) y Auto-eliminación
// ============================================================================
TestHelper::section('5. Salvaguardas Inmutables de Cuenta Raíz (ID #1) y Auto-eliminación');

// 5.1 En memoria: Actualizar rol de ID #1
try {
    $usuarioService->updateRole(1, 'artesano');
    TestHelper::assert(false, 'updateRole(1) debió lanzar excepción');
} catch (\RuntimeException $e) {
    TestHelper::assertSame(403, (int)$e->getCode(), 'updateRole en ID #1 lanza código 403');
    TestHelper::assertStringContains('administrador titular (ID #1)', $e->getMessage(), 'Mensaje explicativo de protección ID #1');
}

// 5.2 HTTP: Intento de cambiar rol de ID #1 por la API
$resChangeRoleRoot = TestHelper::curl('POST', $baseUrl . '/api/usuarios/cambiar-rol.php', $adminHeaders, json_encode([
    'id'  => 1,
    'rol' => 'artesano',
]));
TestHelper::assertSame(403, $resChangeRoleRoot['status'], 'API rechaza cambiar rol de Administrador Raíz (403 Forbidden)');

// 5.3 En memoria: Eliminar ID #1
try {
    $usuarioService->deleteUser(1, 1);
    TestHelper::assert(false, 'deleteUser(1) debió lanzar excepción');
} catch (\RuntimeException $e) {
    TestHelper::assertSame(403, (int)$e->getCode(), 'deleteUser en ID #1 lanza código 403');
    TestHelper::assertStringContains('administrador titular (ID #1)', $e->getMessage(), 'Mensaje explicativo de eliminación de ID #1');
}

// 5.4 HTTP: Intento de eliminar ID #1 por la API
$resDeleteRoot = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', $adminHeaders, json_encode([
    'id' => 1,
]));
TestHelper::assertSame(403, $resDeleteRoot['status'], 'API rechaza eliminar a Administrador Raíz (403 Forbidden)');

// 5.5 Comprobación de integridad en base de datos para ID #1
$rootUser = $usuarioRepo->findById(1, false);
TestHelper::assertSame('admin', $rootUser['rol'], 'Rol de ID #1 sigue siendo estrictamente admin en SQLite');
TestHelper::assertSame(1, (int)$rootUser['activo'], 'Estado activo de ID #1 sigue siendo 1 en SQLite');

// 5.6 Auto-eliminación: Crear admin secundario y probar auto-eliminación
$tmpAdminSuffix = substr(md5((string)microtime(true)), 0, 8);
$tmpAdminUsername = 'admin_temp_' . $tmpAdminSuffix;
$tmpAdminUser = $usuarioService->createUser($tmpAdminUsername, 'segura123', 'admin');
$tmpAdminId = (int)$tmpAdminUser['id'];
$tmpAdminAuth = $authService->authenticate($tmpAdminUsername, 'segura123');
$tmpAdminHeaders = ['Authorization: Bearer ' . $tmpAdminAuth['token'], 'Content-Type: application/json'];

// Intento de auto-eliminación con su propio token
$resSelfDelete = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', $tmpAdminHeaders, json_encode([
    'id' => $tmpAdminId,
]));
TestHelper::assertSame(403, $resSelfDelete['status'], 'Auto-eliminación bloqueada con 403 Forbidden');
TestHelper::assertStringContains('propia cuenta', $getErrMsg($resSelfDelete), 'Feedback de bloqueo de auto-eliminación');

// Ahora el admin original (ID 1) elimina al admin temporal correctamente
$resCleanTmp = TestHelper::curl('POST', $baseUrl . '/api/usuarios/eliminar.php', $adminHeaders, json_encode([
    'id' => $tmpAdminId,
]));
TestHelper::assertSame(200, $resCleanTmp['status'], 'Admin ID #1 puede dar de baja al admin secundario');

// ============================================================================
// SECCIÓN 6: Aislamiento de Creaciones Inactivas y Usuarios Desactivados
// ============================================================================
TestHelper::section('6. Aislamiento de Creaciones Inactivas y Usuarios Desactivados');

// 6.1 Crear una creación temporal y desactivarla
$tmpCreationName = 'Pieza Temp ' . $tmpAdminSuffix;
$tmpCreation = $creacionService->createCreation([
    'nombre'           => $tmpCreationName,
    'categoria'        => 'Decoración',
    'material'         => 'Trapillo',
    'dimensiones'      => '15 cm',
    'precio'           => 18000,
    'costo_materiales' => 5000,
    'cantidad_stock'   => 2,
    'horas_tejido'     => 1.5,
    'es_sobre_encargo' => 0,
], null, ['id' => 1, 'rol' => 'admin']);
$tmpCreationId = (int)$tmpCreation['id'];

// Dar de baja lógica
$creacionService->deleteCreation($tmpCreationId, ['id' => 1, 'rol' => 'admin']);

// 6.2 Consulta pública de detalle por ID para la pieza inactiva (404 Not Found)
$resDetailInactive = TestHelper::curl('GET', $baseUrl . '/api/creaciones/detalle.php?id=' . $tmpCreationId);
TestHelper::assertSame(404, $resDetailInactive['status'], 'Pieza inactiva no es accesible en endpoint de detalle público (404 Not Found)');
TestHelper::assertFalse($resDetailInactive['json']['exito'], 'Exito es false para pieza inactiva');

// 6.3 Consulta pública de catálogo excluye la pieza inactiva
$resCatalogInactive = TestHelper::curl('GET', $baseUrl . '/api/creaciones/index.php?busqueda=' . urlencode($tmpCreationName));
TestHelper::assertSame(200, $resCatalogInactive['status'], 'Catálogo responde 200');
TestHelper::assertSame(0, count($resCatalogInactive['json']['datos']), 'Pieza inactiva no aparece en resultados de catálogo público');

// 6.4 Usuario desactivado bloqueado de autenticación
$inactiveUser = $usuarioRepo->findById($tmpAdminId, false);
TestHelper::assertSame(0, (int)$inactiveUser['activo'], 'Usuario temporal está inactivo (activo = 0)');

try {
    $authService->authenticate($tmpAdminUsername, 'segura123');
    TestHelper::assert(false, 'Login con usuario inactivo debió fallar');
} catch (\RuntimeException $e) {
    TestHelper::assertSame(401, (int)$e->getCode(), 'Login con usuario inactivo retorna código 401');
    TestHelper::assertStringContains('Credenciales de acceso incorrectas', $e->getMessage(), 'Mensaje explicativo de cuenta desactivada');
}

// 6.5 Token previo de usuario desactivado es invalidado inmediatamente
$tokenCheck = $authService->validateToken($tmpAdminAuth['token']);
TestHelper::assertNull($tokenCheck, 'validateToken retorna null para usuario desactivado (invalidación inmediata)');

// ============================================================================
// SECCIÓN 7: Restricción Estricta de Métodos HTTP (405 Method Not Allowed)
// ============================================================================
TestHelper::section('7. Restricción Estricta de Métodos HTTP (405 Method Not Allowed)');

// 7.1 Métodos erróneos en controladores POST-only (llamados con GET)
$postOnlyControllers = [
    '/api/auth/login.php',
    '/api/auth/logout.php',
    '/api/auth/cambiar-password.php',
    '/api/creaciones/crear.php',
    '/api/creaciones/actualizar.php',
    '/api/creaciones/eliminar.php',
    '/api/creaciones/restaurar.php',
    '/api/creaciones/ajustar-stock.php',
    '/api/creaciones/toggle-encargo.php',
    '/api/pedidos/solicitar.php',
    '/api/pedidos/crear.php',
    '/api/pedidos/cambiar-estado.php',
    '/api/pedidos/cancelar.php',
    '/api/usuarios/crear.php',
    '/api/usuarios/cambiar-rol.php',
    '/api/usuarios/actualizar.php',
    '/api/usuarios/restablecer-password.php',
    '/api/usuarios/eliminar.php',
    '/api/usuarios/reactivar.php',
];

foreach ($postOnlyControllers as $endpoint) {
    $res = TestHelper::curl('GET', $baseUrl . $endpoint);
    TestHelper::assertSame(405, $res['status'], "GET a endpoint POST {$endpoint} retorna 405 Method Not Allowed");
    TestHelper::assertStringContains('POST', $getErrMsg($res), "Mensaje de 405 indica método requerido para {$endpoint}");
}

// 7.2 Métodos erróneos en controladores GET-only (llamados con POST)
$getOnlyControllers = [
    '/api/auth/me.php',
    '/api/creaciones/index.php',
    '/api/creaciones/artesanos.php',
    '/api/creaciones/detalle.php?id=1',
    '/api/pedidos/index.php',
    '/api/usuarios/index.php',
];

foreach ($getOnlyControllers as $endpoint) {
    $res = TestHelper::curl('POST', $baseUrl . $endpoint, ['Content-Type: application/json'], json_encode(['foo' => 'bar']));
    TestHelper::assertSame(405, $res['status'], "POST a endpoint GET {$endpoint} retorna 405 Method Not Allowed");
    TestHelper::assertStringContains('GET', $getErrMsg($res), "Mensaje de 405 indica método requerido para {$endpoint}");
}

// Finalizar suite y emitir resumen consolidado
exit(TestHelper::summary());
