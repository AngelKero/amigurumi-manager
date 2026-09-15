<?php
/**
 * Test Suite: Subfase 4.1 - Autenticación, Token Bearer & Estado Reactivo del Navbar
 * Feature 004 · Plan Maestro Fase 4 (009) · Clean Architecture - Algodón Nórdico Design System
 *
 * Valida de forma exhaustiva:
 * 1. Contrato Frontend: módulo auth.js (token Bearer real), IDs reactivos del navbar/sidebar/modal
 *    y migración completa fuera de los flags mock localStorage (H-004 / DOM safe).
 * 2. AuthService: emisión HMAC y revocación server-side mediante denylist por jti (ADR-016 / H-002).
 * 3. Endurecimiento anti-fuerza-bruta (H-003): bloqueo 429 tras 5 fallos por username.
 * 4. Pruebas HTTP en vivo contra localhost:8000:
 *    - Cabeceras Content-Security-Policy estrictas (página HTML y API JSON).
 *    - POST /api/auth/login.php → 200 Bearer; GET /api/auth/me.php → 200.
 *    - POST /api/auth/logout.php con Bearer → revocación server-side (revocado_en_servidor: true).
 *    - GET /api/auth/me.php con token revocado → 401.
 *    - Bloqueo 429 demostrado por HTTP y Preflight CORS OPTIONS.
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Core\Response;
use App\Repositories\LoginGuardRepository;
use App\Services\AuthService;

// Rutas relativas al proyecto (tests/ está anidado un nivel bajo la raíz)
$root = dirname(__DIR__);

TestHelper::init('Subfase 4.1: Autenticación Bearer & Estado Reactivo del Navbar');

// =============================================================================
// 1. CONTRATO FRONTEND (auth.js + IDs reactivos + migración fuera de mocks)
// =============================================================================
TestHelper::section('1. Contrato Frontend Reactivo (auth.js, navbar, sidebar, modal)');

$authJs   = (string)@file_get_contents("$root/src/js/modules/auth.js");
$catalogJs = (string)@file_get_contents("$root/src/js/modules/catalog.js");
$detailJs  = (string)@file_get_contents("$root/src/js/modules/detail.js");
$navbarPhp = (string)@file_get_contents("$root/views/components/navbar.php");
$sidebarPhp = (string)@file_get_contents("$root/views/components/panel_sidebar.php");
$modalPhp   = (string)@file_get_contents("$root/views/components/modal_login.php");
$layoutPhp  = (string)@file_get_contents("$root/views/layouts/main.php");

// 1.1 Módulo auth.js expone todo el contrato de sesión
foreach (['getToken', 'setSession', 'clearSession', 'isAuthenticated', 'initAuth', 'renderAuthState'] as $exportedFn) {
    TestHelper::assertTrue(
        str_contains($authJs, "export function $exportedFn"),
        "auth.js exporta '$exportedFn'"
    );
}
foreach (['login', 'logout', 'checkSession'] as $exportedAsyncFn) {
    TestHelper::assertTrue(
        str_contains($authJs, "export async function $exportedAsyncFn"),
        "auth.js exporta (async) '$exportedAsyncFn'"
    );
}

// 1.2 Unit test de caducidad/limpieza no aplica en Node; verificación estructural del storage
TestHelper::assertStringContains("'crochet_auth_token'", $authJs, "auth.js centraliza la clave del token ('crochet_auth_token') en TOKEN_KEY");
TestHelper::assertStringContains("/api/auth/login.php", $authJs, 'auth.js consume POST /api/auth/login.php (API real)');
TestHelper::assertStringContains("/api/auth/logout.php", $authJs, 'auth.js consume POST /api/auth/logout.php (revocación server-side)');
TestHelper::assertStringContains("/api/auth/me.php", $authJs, 'auth.js consume GET /api/auth/me.php (verificación de sesión)');
TestHelper::assertFalse(
    str_contains($authJs, 'session_active'),
    'auth.js ya no usa las flags localStorage mock (crochet/amigurumi_session_active)'
);
TestHelper::assertFalse(
    str_contains($authJs, 'alert('),
    'auth.js NO usa diálogos nativos alert() (AC-3: feedback accesible dentro del modal)'
);
TestHelper::assertStringContains(
    'role="alert"',
    $modalPhp,
    'modal_login.php: #loginAlert es accesible para lectores de pantalla (role="alert")'
);

// 1.3 New Syntax Check Client: catálogo y detalle consumen isAuthenticated() del módulo auth
TestHelper::assertStringContains("import { isAuthenticated } from './auth.js';", $catalogJs, 'catalog.js importa isAuthenticated desde ./auth.js');
TestHelper::assertFalse(str_contains($catalogJs, 'session_active'), 'catalog.js ya no lee flags mock de sesión');
TestHelper::assertStringContains("import { isAuthenticated } from './auth.js';", $detailJs, 'detail.js importa isAuthenticated desde ./auth.js');
TestHelper::assertFalse(str_contains($detailJs, 'session_active'), 'detail.js ya no lee flags mock de sesión');

// 1.4 IDs reactivos presentes en las vistas
TestHelper::assertStringContains('id="navArtisanUsername"', $navbarPhp, 'navbar.php expone #navArtisanUsername reactivo');
TestHelper::assertStringContains('id="panelProfileUsername"', $sidebarPhp, 'panel_sidebar.php expone #panelProfileUsername');
TestHelper::assertStringContains('id="sidebarLinkUsuarios"', $sidebarPhp, 'panel_sidebar.php expone #sidebarLinkUsuarios (RBAC admin)');
TestHelper::assertStringContains('id="btnLoginSubmit"', $modalPhp, 'modal_login.php expone #btnLoginSubmit (spinner/estados)');

// 1.5 CSP estricta del layout HTML (H-004)
TestHelper::assertStringContains("script-src 'self'", $layoutPhp, 'Layout HTML: CSP permite únicamente scripts propios (script-src \'self\')');
TestHelper::assertStringContains("connect-src 'self'", $layoutPhp, 'Layout HTML: CSP restringe conexiones al propio origen (connect-src \'self\')');
TestHelper::assertStringContains("frame-ancestors 'none'", $layoutPhp, 'Layout HTML: CSP bloquea anidación en iframes (frame-ancestors \'none\')');
TestHelper::assertStringContains(
    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
    $layoutPhp,
    'Layout HTML: CSP permite la fuente de iconos Bootstrap Icons (cdn.jsdelivr.net) en font-src'
);

// =============================================================================
// 2. AUTH SERVICE: EMISIÓN TOKEN BEARER & REVOCACIÓN SERVER-SIDE
// =============================================================================
TestHelper::section('2. AuthService: Token Bearer y Revocación Server-Side (ADR-016 / H-002)');

$authService = new AuthService();

// 2.1 Login exitoso y forma del contrato de sesión
$sesion = $authService->authenticate('admin', 'admin123');
TestHelper::assertSame('admin', $sesion['usuario']['username'] ?? '', 'authenticate("admin","admin123") autentica al administrador');
TestHelper::assertTrue(!empty($sesion['token']), 'Se emite un token Bearer no vacío');
TestHelper::assertSame('Bearer', $sesion['tipo_token'] ?? '', 'tipo_token es "Bearer"');
TestHelper::assertSame(86400, $sesion['expira_en'] ?? 0, 'expira_en es 86400s (24h)');
TestHelper::assertSame('admin', $sesion['usuario']['rol'] ?? '', 'El perfil emitido incluye el rol "admin" (selector RBAC del frontend)');

$token = $sesion['token'];
TestHelper::assertNotNull($authService->validateToken($token), 'validateToken() valida el token antes de revocar');

// 2.2 Revocación server-side por denylist de jti
TestHelper::assertTrue($authService->revokeToken($token), 'revokeToken() confirma la revocación en servidor (revocado_en_servidor)');

TestHelper::assertNull(
    $authService->validateToken($token),
    'validateToken() rechaza un token revocado (denylist por jti - H-002)'
);

// 2.3 Tokens inválidos no se pueden revocar
TestHelper::assertFalse($authService->revokeToken('token_falsificado_firma'), 'revokeToken() con token inválido devuelve false');

// =============================================================================
// 3. ENDURECIMIENTO ANTI-FUERZA-BRUTA (H-003): BLOQUEO HTTP 429
// =============================================================================
TestHelper::section('3. Endurecimiento Anti-Fuerza-Bruta (H-003): Bloqueo 429 tras 5 fallos');

$guard = new LoginGuardRepository();
$bfUser = 'bloqueo_' . substr(md5((string)microtime(true)), 0, 8);
$guard->clearFailuresByUsername($bfUser);

// 3.1 Cinco credenciales erróneas → HTTP 401 cada una
$failures401 = 0;
for ($i = 0; $i < 5; $i++) {
    try {
        $authService->authenticate($bfUser, 'clave_erronea');
    } catch (\RuntimeException $e) {
        if ($e->getCode() === 401) {
            $failures401++;
        }
    }
}
TestHelper::assertSame(5, $failures401, 'Cinco intentos con credenciales erróneas responden HTTP 401');

// 3.2 El sexto intento queda bloqueado → HTTP 429 Too Many Requests
$caught429 = false;
try {
    $authService->authenticate($bfUser, 'clave_erronea');
} catch (\RuntimeException $e) {
    $caught429 = ($e->getCode() === 429);
}
TestHelper::assertTrue($caught429, 'El sexto intento excede el umbral (5/username) y dispara HTTP 429');

// 3.3 Limpieza del usuario de bloqueo para no contaminar la ventana de 15 min
$guard->clearFailuresByUsername($bfUser);

// =============================================================================
// 4. PRUEBAS HTTP EN VIVO (CURL CONTRA LOCALHOST:8000)
// =============================================================================
TestHelper::section('4. Pruebas HTTP en Vivo contra Servidor (CSP + Flujo Login → Logout → Revocación)');

// 4.1 Preflight CORS OPTIONS
$corsRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/auth/login.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: Authorization, Content-Type',
]);
TestHelper::assertTrue(
    in_array($corsRes['status'], [200, 204], true),
    'Preflight CORS OPTIONS en /api/auth/login.php responde correctamente (' . $corsRes['status'] . ')'
);

// 4.2 Cabeceras CSP de la página principal (HTML)
$pageRes = TestHelper::curl('GET', 'http://localhost:8000/index.php');
TestHelper::assertSame(200, $pageRes['status'], 'HTTP GET /index.php devuelve 200 OK');
$cspPage = strtolower($pageRes['headers']['content-security-policy'] ?? '');
TestHelper::assertStringContains("script-src 'self'", $cspPage, 'CSP de la página HTML restringe scripts al propio origen');
TestHelper::assertStringContains("connect-src 'self'", $cspPage, 'CSP de la página HTML restringe conexiones al propio origen');
TestHelper::assertStringContains("frame-ancestors 'none'", $cspPage, 'CSP de la página HTML bloquea iframes externos');
TestHelper::assertStringContains(
    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
    $cspPage,
    'CSP de la página HTML permite la fuente de iconos Bootstrap Icons (regresión 404 - iconos desaparecidos)'
);

// 4.3 Cabeceras CSP de las respuestas API (JSON)
$apiRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php');
TestHelper::assertSame(200, $apiRes['status'], 'HTTP GET /api/creaciones/index.php devuelve 200 OK');
$cspApi = strtolower($apiRes['headers']['content-security-policy'] ?? '');
TestHelper::assertTrue(
    str_contains($cspApi, "default-src 'none'") || str_contains($cspApi, "default-src none"),
    'CSP de las respuestas API bloquea cualquier fuente por defecto (default-src \'none\')'
);

// 4.4 Login real por HTTP con credenciales de administrador
$loginRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'admin', 'password' => 'admin123']));
TestHelper::assertSame(200, $loginRes['status'], 'HTTP POST /api/auth/login.php con admin/admin123 devuelve 200 OK');
TestHelper::assertTrue($loginRes['json']['exito'] ?? false, 'Respuesta de login contiene exito: true');
TestHelper::assertTrue(!empty($loginRes['json']['datos']['token']), 'Respuesta de login contiene token Bearer');
TestHelper::assertSame('admin', $loginRes['json']['datos']['usuario']['rol'] ?? '', 'Perfil de login incluye rol admin');

$httpToken = (string)($loginRes['json']['datos']['token'] ?? '');

// 4.5 Verificación de sesión con el token obtenido
$meRes = TestHelper::curl('GET', 'http://localhost:8000/api/auth/me.php', [
    'Authorization: Bearer ' . $httpToken,
]);
TestHelper::assertSame(200, $meRes['status'], 'HTTP GET /api/auth/me.php con Bearer válido devuelve 200 OK');
TestHelper::assertSame('admin', $meRes['json']['datos']['username'] ?? '', 'me.php identifica al usuario admin');

// 4.6 Logout con revocación server-side del token presentado (ADR-016)
$logoutRes = TestHelper::curl('POST', 'http://localhost:8000/api/auth/logout.php', [
    'Authorization: Bearer ' . $httpToken,
]);
TestHelper::assertSame(200, $logoutRes['status'], 'HTTP POST /api/auth/logout.php con Bearer devuelve 200 OK');
TestHelper::assertTrue(
    ($logoutRes['json']['datos']['revocado_en_servidor'] ?? false) === true,
    'logout.php confirma revocación server-side del token (revocado_en_servidor: true)'
);

// 4.7 El token revocado ya no es válido → HTTP 401 en me.php
$meRevokedRes = TestHelper::curl('GET', 'http://localhost:8000/api/auth/me.php', [
    'Authorization: Bearer ' . $httpToken,
]);
TestHelper::assertSame(401, $meRevokedRes['status'], 'HTTP GET /api/auth/me.php con token revocado devuelve 401');

// 4.8 Demostración HTTP del bloqueo 429 por fuerza bruta con usuario dedicado
$bfHttp = 'bloqueo_http_' . substr(md5((string)microtime(true)), 0, 8);
$statuses429 = [];
for ($i = 0; $i < 5; $i++) {
    $attempt = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
        'Content-Type: application/json',
    ], json_encode(['username' => $bfHttp, 'password' => 'clave_erronea']));
    $statuses429[] = $attempt['status'];
}
TestHelper::assertFalse(in_array(429, $statuses429, true), 'Las primeras 5 credenciales erróneas por HTTP no están bloqueadas');

$blocked = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => $bfHttp, 'password' => 'clave_erronea']));
TestHelper::assertSame(429, $blocked['status'], 'El sexto intento por HTTP dispara 429 Too Many Requests');

// 4.9 Limpieza del usuario de bloqueo HTTP (misma BD SQLite compartida)
$guard->clearFailuresByUsername($bfHttp);

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);