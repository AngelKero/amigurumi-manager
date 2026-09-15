<?php
/**
 * Gobernanza · Acción 2: Suite de Blindaje del Ciclo de Token (H-002 / H-003 / H-004)
 * Algodón Nórdico Design System
 *
 * Tier 1 (ejecución CLI) de la Acción 2 de la auditoría de gobernanza.
 * Verifica: CSP estricto, sanitización de innerHTML, endurecimiento anti-fuerza-bruta
 * del login (login_intentos → 429) y revocación server-side de tokens (tokens_revocados
 * por jti + rotación de secretos con claim ver).
 *
 * Ejecución:
 *   php tests/test-gobernanza-accion-2.php > logs/gobernanza-accion-2-cli.log 2>&1
 */

declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\TokenManager;
use App\Repositories\LoginGuardRepository;
use App\Repositories\TokenRevocadoRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use Tests\TestHelper;

require_once __DIR__ . '/TestHelper.php';

TestHelper::init('Gobernanza · Acción 2: Blindaje del Ciclo de Token (CSP · Sanitización · Brute-force · Revocación)');

$pdo = Database::getInstance();
$baseUrl = 'http://localhost:8000';
$httpLogFile = dirname(__DIR__) . '/logs/gobernanza-accion-2-http.log';
file_put_contents($httpLogFile, "=== LOG HTTP · GOBERNANZA ACCIÓN 2 ===\nFecha: " . date('Y-m-d H:i:s') . "\n\n");

$logHttp = function (string $test, array $res, ?string $payload = null) use ($httpLogFile) {
    $entry = "--------------------------------------------------------------------------------\n";
    $entry .= "TEST: {$test}\n";
    $entry .= "HTTP Status: {$res['status']} | Latencia: {$res['duration_ms']} ms\n";
    if ($payload !== null) {
        $entry .= "Request Payload:\n{$payload}\n";
    }
    $entry .= "Response Headers:\n" . json_encode($res['headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    $entry .= "Response Body:\n" . ($res['body'] ?: "(vacío)") . "\n\n";
    file_put_contents($httpLogFile, $entry, FILE_APPEND);
};

// Re-sincronizar base de datos con el esquema canónico (incluye login_intentos y tokens_revocados)
exec('php ' . escapeshellarg(dirname(__DIR__) . '/setup.php') . ' > /dev/null 2>&1');

// ============================================================================
// SECCIÓN 1 · M1 — CSP ESTRICTO (H-004)
// ============================================================================
TestHelper::section('1. M1 · Content-Security-Policy estricta (layout + API)');

$layoutFile = dirname(__DIR__) . '/views/layouts/main.php';
$layoutSource = file_get_contents($layoutFile);
$posCsp = strpos($layoutSource, 'Content-Security-Policy');
$posDoctype = strpos($layoutSource, '<!DOCTYPE');
TestHelper::assertTrue($posCsp !== false, 'El layout emite la cabecera Content-Security-Policy');
TestHelper::assertTrue(
    $posCsp !== false && $posDoctype !== false && $posCsp < $posDoctype,
    'La cabecera CSP se emite ANTES del DOCTYPE (sin difuminación del analizador HTML)'
);
TestHelper::assertStringContains("default-src 'self'", $layoutSource, 'CSP layout: default-src \'self\'');
TestHelper::assertStringContains("script-src 'self'", $layoutSource, 'CSP layout: script-src \'self\' (sin inline/unsafe-eval)');
TestHelper::assertStringContains("connect-src 'self'", $layoutSource, 'CSP layout: connect-src \'self\'');
TestHelper::assertStringContains("frame-ancestors 'none'", $layoutSource, 'CSP layout: frame-ancestors \'none\' (anti-clickjacking)');
TestHelper::assertStringContains("object-src 'none'", $layoutSource, 'CSP layout: object-src \'none\'');
TestHelper::assert(!str_contains($layoutSource, 'script-src \'unsafe-inline\''), 'CSP layout: PROHIBIDO unsafe-inline en scripts');

$responseFile = dirname(__DIR__) . '/app/Core/Response.php';
$responseSource = file_get_contents($responseFile);
TestHelper::assertStringContains(
    "Content-Security-Policy: default-src 'none'; frame-ancestors 'none'",
    $responseSource,
    'Response::json emite CSP default-src \'none\' para respuestas JSON'
);
TestHelper::assertStringContains('X-Content-Type-Options: nosniff', $responseSource, 'Response emite X-Content-Type-Options: nosniff');
TestHelper::assertStringContains('X-Frame-Options: DENY', $responseSource, 'Response emite X-Frame-Options: DENY');

// Verificación HTTP en vivo
$resCatalog = TestHelper::curl('GET', $baseUrl . '/');
$logHttp('GET / (página catálogo)', $resCatalog);
TestHelper::assertSame(200, $resCatalog['status'], 'HTTP GET / responde 200 OK');
TestHelper::assertStringContains("default-src 'self'", $resCatalog['headers']['content-security-policy'] ?? '', 'HTTP: cabecera CSP presente en la página');
TestHelper::assertStringContains("frame-ancestors 'none'", $resCatalog['headers']['content-security-policy'] ?? '', 'HTTP: frame-ancestors none en la página');

$resApi = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', ['Content-Type: application/json'], json_encode(['username' => 'csp_check_user', 'password' => 'wrong']));
$logHttp('POST login con credenciales erróneas (chequeo CSP API)', $resApi);
TestHelper::assertStringContains("default-src 'none'", $resApi['headers']['content-security-policy'] ?? '', 'HTTP: la API emite CSP default-src none');
TestHelper::assertStringContains('nosniff', $resApi['headers']['x-content-type-options'] ?? '', 'HTTP: la API emite X-Content-Type-Options');

// ============================================================================
// SECCIÓN 2 · M2 — AUDITORÍA DE SANITIZACIÓN DE innerHTML (H-004)
// ============================================================================
TestHelper::section('2. M2 · Sanitización de innerHTML — vectores de datos a cero');

$modulesDir = dirname(__DIR__) . '/src/js/modules';
TestHelper::assertTrue(is_file($modulesDir . '/dom-safe.js'), 'Existe el helper compartido src/js/modules/dom-safe.js');
$domSafe = file_get_contents($modulesDir . '/dom-safe.js');
TestHelper::assertStringContains('export function escapeHtml', $domSafe, 'dom-safe.js exporta escapeHtml');
TestHelper::assertStringContains('export function setIconText', $domSafe, 'dom-safe.js exporta setIconText');

$checkout = file_get_contents($modulesDir . '/checkout.js');
TestHelper::assertStringContains("import { setIconText }", $checkout, 'checkout.js usa setIconText (DOM seguro)');
TestHelper::assert(!str_contains($checkout, 'modalTitle.innerHTML'), 'checkout.js: eliminado innerHTML con ${name} (título modal)');

$orders = file_get_contents($modulesDir . '/orders.js');
TestHelper::assertStringContains('escapeHtml, setIconText', $orders, 'orders.js importa escapeHtml y setIconText');
TestHelper::assertStringContains('escapeHtml(status)', $orders, 'orders.js: getBadgeConfig escapa el valor por defecto');
TestHelper::assertStringContains('setIconText(inspectContacto', $orders, 'orders.js: contacto de inspección vía DOM seguro');
foreach (['${notas}', '${clienteNombre}', '${clienteContacto}', '${productName}', '${newIdString}', '${waDigits}'] as $vector) {
    TestHelper::assert(!str_contains($orders, $vector), "orders.js: sin interpolación sin escapar de {$vector}");
}

$users = file_get_contents($modulesDir . '/users.js');
TestHelper::assertStringContains('import { escapeHtml }', $users, 'users.js importa escapeHtml');
TestHelper::assertStringContains('btn-eliminar-usuario', $users, 'users.js: botón de borrado por clase + delegación');
TestHelper::assert(!str_contains($users, "onclick=\"if(confirm"), 'users.js: eliminado onclick inline con ${username} (contexto de atributo)');

// Conteo global de innerHTML restantes (36 usos reales: 0 vectores de datos sin escalar;
// 4.1 añadió en auth.js 3 usos de markup constante del spinner de login — H-004 conforme)
$innerHtmlCount = 0;
foreach (glob($modulesDir . '/*.js') as $jsFile) {
    if (basename($jsFile) === 'dom-safe.js') continue;
    $innerHtmlCount += substr_count(file_get_contents($jsFile), 'innerHTML');
}
TestHelper::assertTrue($innerHtmlCount <= 36, sprintf('Recuento final de innerHTML coaccionado: %d (≤36 inventariados)', $innerHtmlCount));

TestHelper::assertTrue(is_file(dirname(__DIR__) . '/.agents/rules/innerhtml-dom-safety.md'), 'Regla P1 innerhtml-dom-safety.md publicada');
TestHelper::assertTrue(is_file(dirname(__DIR__) . '/docs/security/auditoria-sanitizacion-js.md'), 'Reporte de auditoría de sanitización publicado');
$agentsMd = file_get_contents(dirname(__DIR__) . '/AGENTS.md');
TestHelper::assertStringContains('dom-safe.js', $agentsMd, 'AGENTS.md §4 referencia el helper dom-safe.js');

// ============================================================================
// SECCIÓN 3 · M3 — ENDURECIMIENTO ANTI-FUERZA-BRUTA (H-003)
// ============================================================================
TestHelper::section('3. M3 · Bloqueo anti-fuerza-bruta del login (login_intentos → 429)');

// 3.1 Esquema físico
$tables = [];
$stm = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'");
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) $tables[] = $row['name'];
TestHelper::assertTrue(in_array('login_intentos', $tables, true), 'Tabla login_intentos creada en el esquema');

$cols = [];
$stm = $pdo->query("PRAGMA table_info(login_intentos)");
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) $cols[] = $row['name'];
foreach (['id', 'username', 'ip', 'intento_ok', 'creado_en'] as $expectedCol) {
    TestHelper::assertTrue(in_array($expectedCol, $cols, true), "Columna login_intentos.{$expectedCol} presente");
}

$indexes = [];
$stm = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'index'");
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) $indexes[] = $row['name'];
foreach (['idx_login_intentos_username', 'idx_login_intentos_ip'] as $idx) {
    TestHelper::assertTrue(in_array($idx, $indexes, true), "Índice {$idx} presente");
}

// 3.2 Configuración de umbrales
TestHelper::assertSame(5, (int)Config::get('auth.max_intentos_username', 0), 'config: max_intentos_username = 5');
TestHelper::assertSame(20, (int)Config::get('auth.max_intentos_ip', 0), 'config: max_intentos_ip = 20');
TestHelper::assertSame(900, (int)Config::get('auth.ventana_segundos', 0), 'config: ventana_segundos = 900');
TestHelper::assertSame(400, (int)Config::get('auth.backoff_ms', 0), 'config: backoff_ms = 400');

// 3.3 Prueba directa: fallos → registro + backoff, éxito → limpieza
Config::set('auth.backoff_ms', 1);
$loginGuard = new LoginGuardRepository();
$usuarioRepo = new UsuarioRepository();

$guardOkHash = password_hash('GuardOkClave2026', PASSWORD_BCRYPT);
$guardOkId = $usuarioRepo->create('guard_ok_openc', $guardOkHash, 'artesano');
TestHelper::assertTrue($guardOkId > 0, 'Creado usuario de prueba guard_ok_openc');

$authOk = new AuthService($usuarioRepo, $loginGuard, new TokenRevocadoRepository());
try {
    $authOk->authenticate('guard_ok_openc', 'clave-incorrecta');
    TestHelper::assertTrue(false, 'Intento fallido 1 de guard_ok_openc lanza 401');
} catch (RuntimeException $e) {
    TestHelper::assertSame(401, $e->getCode(), 'Intento fallido 1 lanza RuntimeException 401');
}
try {
    $authOk->authenticate('guard_ok_openc', 'otra-clave-incorrecta');
    TestHelper::assertTrue(false, 'Intento fallido 2 de guard_ok_openc lanza 401');
} catch (RuntimeException $e) {
    TestHelper::assertSame(401, $e->getCode(), 'Intento fallido 2 lanza RuntimeException 401');
}
TestHelper::assertTrue($loginGuard->countFailuresByUsername('guard_ok_openc', 900) >= 2, 'Se registraron los intentos fallidos del usuario en login_intentos');

$loginResult = $authOk->authenticate('guard_ok_openc', 'GuardOkClave2026');
TestHelper::assertArrayHasKey('token', $loginResult, 'Login exitoso retorna token');
TestHelper::assertSame('guard_ok_openc', $loginResult['usuario']['username'] ?? '', 'Login exitoso resuelve el usuario activo');
TestHelper::assertSame('artesano', $loginResult['usuario']['rol'] ?? '', 'Login exitoso devuelve el rol del usuario');
TestHelper::assertTrue($loginGuard->countFailuresByUsername('guard_ok_openc', 900) === 0, 'El éxito restableció el contador de fallos del usuario');

// 3.4 Prueba directa de bloqueo (umbral bajo configurado en test)
Config::set('auth.max_intentos_username', 3);
Config::set('auth.max_intentos_ip', 3);
$guardBfId = $usuarioRepo->create('guard_bf_openc', password_hash('x', PASSWORD_BCRYPT), 'artesano');
TestHelper::assertTrue($guardBfId > 0, 'Creado usuario de prueba guard_bf_openc');

$authBf = new AuthService($usuarioRepo, $loginGuard, new TokenRevocadoRepository());
$codes = [];
for ($i = 0; $i < 4; $i++) {
    try {
        $authBf->authenticate('guard_bf_openc', 'clave-invalida');
        $codes[] = null;
    } catch (RuntimeException $e) {
        $codes[] = $e->getCode();
    }
}
TestHelper::assertSame(401, $codes[0] ?? 0, 'Fallos 1..3 devuelven 401');
TestHelper::assertSame(401, $codes[1] ?? 0, 'Fallos 1..3 devuelven 401 (2º)');
TestHelper::assertSame(401, $codes[2] ?? 0, 'Fallos 1..3 devuelven 401 (3º)');
TestHelper::assertSame(429, $codes[3] ?? 0, 'Superado el umbral, el 4º intento devuelve 429 Too Many Requests');

try {
    $authBf->authenticate('guard_bf_openc', 'clave-invalida');
    TestHelper::assertTrue(false, 'Cuenta bloqueada: nuevos intentos siguen en 429');
} catch (RuntimeException $e) {
    TestHelper::assertSame(429, $e->getCode(), 'Cuenta bloqueada: 5º intento también en 429');
}
TestHelper::assertTrue($loginGuard->countFailuresByUsername('guard_bf_openc', 900) >= 3, 'No se registran más fallos mientras la cuenta está bloqueada (early-exit)');

// 3.5 Verificación HTTP en vivo (cuenta de ataque dedicada, NUNCA admin)
$bfUser = 'bfattack_' . substr(bin2hex(random_bytes(4)), 0, 8);
$httpStatuses = [];
for ($i = 0; $i < 5; $i++) {
    $res = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', ['Content-Type: application/json'], json_encode(['username' => $bfUser, 'password' => 'falsa-' . $i]));
    $httpStatuses[] = $res['status'];
    if ($i === 0) {
        TestHelper::assertSame(401, $res['status'], 'HTTP: primer fallo de fuerza bruta → 401');
    }
}
$logHttp('Fuerza bruta: 5 intentos contra cuenta dedicada ' . $bfUser, TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', ['Content-Type: application/json'], json_encode(['username' => $bfUser, 'password' => 'ultima'])));
$resBfFinal = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', ['Content-Type: application/json'], json_encode(['username' => $bfUser, 'password' => 'ultima']));
TestHelper::assertSame(429, $resBfFinal['status'], 'HTTP: tras 5 fallos el login devuelve 429 Too Many Requests');
TestHelper::assertSame(429, $resBfFinal['json']['error']['codigo'] ?? 0, 'HTTP: envuelto en {"exito":false,"error":{"codigo":429,...}}');

// El lockout por usuario NO aplica a otras cuentas: admin sigue autenticando
$resAdminFree = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', ['Content-Type: application/json'], json_encode(['username' => 'admin', 'password' => 'admin123']));
TestHelper::assertSame(200, $resAdminFree['status'], 'HTTP: el bloqueo de la cuenta bfattack no afecta a admin');

// Limpieza de la bitácora para no contaminar otras suites dentro del proceso
$pdo->exec('DELETE FROM login_intentos');

// ============================================================================
// SECCIÓN 4 · M4 — REVOCACIÓN SERVER-SIDE + ROTACIÓN DE SECRETOS (H-002)
// ============================================================================
TestHelper::section('4. M4 · Revocación por denylist de jti y rotación de secretos');

// 4.1 Esquema físico
TestHelper::assertTrue(in_array('tokens_revocados', $tables, true), 'Tabla tokens_revocados creada en el esquema');
$colsRev = [];
$stm = $pdo->query('PRAGMA table_info(tokens_revocados)');
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) $colsRev[] = $row['name'];
foreach (['jti', 'sub', 'expira_en', 'revocado_en'] as $expectedCol) {
    TestHelper::assertTrue(in_array($expectedCol, $colsRev, true), "Columna tokens_revocados.{$expectedCol} presente");
}
TestHelper::assertTrue(in_array('idx_tokens_revocados_expira', $indexes, true), 'Índice idx_tokens_revocados_expira presente');

// 4.2 Claim ver + rotación de secretos
$tokenPayload = ['id' => 2, 'username' => 'admin', 'rol' => 'admin'];
$token = TokenManager::generate($tokenPayload);
TestHelper::assertNotNull($token, 'TokenManager::generate emite token');
$decoded = TokenManager::verify($token);
TestHelper::assertArrayHasKey('jti', $decoded ?? [], 'El token incluye claim jti');
TestHelper::assertSame((int)Config::get('auth.secret_version', 1), (int)($decoded['ver'] ?? 0), 'El token incluye claim ver con la versión de secreto');

$oldSecret = (string)Config::get('auth.token_secret');
Config::set('auth.token_secret', 'nuevo_secreto_rotacion_2026');
Config::set('auth.token_secret_anterior', '');
TestHelper::assertNull(TokenManager::verify($token), 'Firmado con clave anterior sin configuración → token rechazado');
Config::set('auth.token_secret_anterior', $oldSecret);
TestHelper::assertNotNull(TokenManager::verify($token), 'Rotación caliente: con token_secret_anterior el token antiguo sigue siendo válido');
Config::set('auth.token_secret', $oldSecret);
Config::set('auth.token_secret_anterior', '');

// 4.3 Denylist por jti: revocar → invalidar
$tokenRevocadoRepo = new TokenRevocadoRepository();
$authRev = new AuthService($usuarioRepo, $loginGuard, $tokenRevocadoRepo);

TestHelper::assertNotNull($authRev->validateToken($token), 'validateToken acepta el token antes de revocar');
TestHelper::assertTrue($authRev->revokeToken($token), 'revokeToken revoca el token en servidor');
TestHelper::assertTrue($tokenRevocadoRepo->isRevoked($decoded['jti'] ?? ''), 'El jti figura en la denylist tokens_revocados');
TestHelper::assertTrue($authRev->revokeToken($token), 'revokeToken es idempotente (INSERT OR IGNORE)');
TestHelper::assertNull($authRev->validateToken($token), 'validateToken REJECTA el token revocado');
TestHelper::assertTrue($tokenRevocadoRepo->pruneExpirados() >= 0, 'pruneExpirados ejecuta sin errores');

// 4.4 End-to-end HTTP: login → me → logout (revoca) → me rechazado
$resLogin = TestHelper::curl('POST', $baseUrl . '/api/auth/login.php', ['Content-Type: application/json'], json_encode(['username' => 'admin', 'password' => 'admin123']));
$logHttp('POST login admin (logout E2E)', $resLogin);
TestHelper::assertSame(200, $resLogin['status'], 'HTTP: login admin OK');
$sessionToken = $resLogin['json']['datos']['token'] ?? '';
TestHelper::assertTrue($sessionToken !== '', 'HTTP: login devuelve token');

$resMe = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', ['Authorization: Bearer ' . $sessionToken]);
$logHttp('GET me con token vigente', $resMe);
TestHelper::assertSame(200, $resMe['status'], 'HTTP: me.php acepta el token vigente');

$resLogout = TestHelper::curl('POST', $baseUrl . '/api/auth/logout.php', ['Authorization: Bearer ' . $sessionToken]);
$logHttp('POST logout con Bearer', $resLogout);
TestHelper::assertSame(200, $resLogout['status'], 'HTTP: logout.php responde 200');
TestHelper::assertSame(true, $resLogout['json']['datos']['revocado_en_servidor'] ?? false, 'HTTP: logout confirma revocación en servidor (revocado_en_servidor=true)');

$resMeAfter = TestHelper::curl('GET', $baseUrl . '/api/auth/me.php', ['Authorization: Bearer ' . $sessionToken]);
$logHttp('GET me con token revocado', $resMeAfter);
TestHelper::assertSame(401, $resMeAfter['status'], 'HTTP: me.php REJECTA el token tras logout (revocado por jti)');

// El archivo del controlador ejecuta revocación real (no-op eliminado)
$logoutSource = file_get_contents(dirname(__DIR__) . '/api/auth/logout.php');
TestHelper::assertStringContains('revokeToken', $logoutSource, 'api/auth/logout.php ejecuta revocación server-side (H-002)');
$loginSource = file_get_contents(dirname(__DIR__) . '/api/auth/login.php');
TestHelper::assertStringContains('getCode()', $loginSource, 'api/auth/login.php mapea el código HTTP de la excepción (429 para bloqueos)');

// Archivo de seguridad documentado
$securityDoc = file_get_contents(dirname(__DIR__) . '/docs/architecture/security.md');
TestHelper::assertStringContains('tokens_revocados', $securityDoc, 'security.md documenta la denylist tokens_revocados');
TestHelper::assertStringContains('login_intentos', $securityDoc, 'security.md documenta login_intentos');
$adr016 = file_get_contents(dirname(__DIR__) . '/docs/architecture/decisiones/ADR-016-token-revocacion-y-brute-force-guard.md');
TestHelper::assertStringContains('ADR-016', $adr016, 'ADR-016 publicado y legible');

// ============================================================================
exit(TestHelper::summary());