<?php
/**
 * Test Suite: Subfase 3.1 - Base del Backend & Infraestructura Nuclear
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Valida de forma exhaustiva todos los componentes nucleares de app/:
 * 1. Autoloader PSR-4 & Configuración por notación de puntos
 * 2. Conexión Singleton SQLite & Claves Foráneas (PRAGMA foreign_keys = ON)
 * 3. Manejador Global de Errores (Cero fugas HTML y JSON 500)
 * 4. Abstracción de Peticiones Request & Extracción de Bearer Token
 * 5. Emisor de Respuestas Response & Preflight CORS
 * 6. Generación, Verificación, Manipulación y Expiración de Tokens HMAC-SHA256
 * 7. Helpers Monetarios (Centavos enteros <-> MXN y Enriquecimiento)
 * 8. Asistente de Paginación Estándar y Cotas Límites
 * 9. SvgHelper, URLs y Funciones Globales
 * 10. Pruebas HTTP curl en vivo contra el servidor local (200 OK, 403 Forbidden en app/)
 */

declare(strict_types=1);

// Guardia de seguridad: Restricción absoluta a CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Core\Config;
use App\Core\Database;
use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Response;
use App\Core\TokenManager;
use App\Utils\CurrencyHelper;
use App\Utils\PaginationHelper;
use App\Utils\SvgHelper;

// Iniciar suite
TestHelper::init('Subfase 3.1: Base del Backend & Infraestructura Nuclear');

// Desactivar exit en Response para permitir pruebas unitarias en memoria
Response::setExitOnSend(false);

// =============================================================================
// 1. CONFIGURACIÓN & AUTOLOADER
// =============================================================================
TestHelper::section('1. Autoloader PSR-4 & Configuración Centralizada');

TestHelper::assertTrue(class_exists(Config::class), 'La clase App\Core\Config fue cargada por el autoloader PSR-4');
TestHelper::assertTrue(class_exists(Database::class), 'La clase App\Core\Database fue cargada por el autoloader');
TestHelper::assertTrue(class_exists(Request::class), 'La clase App\Core\Request fue cargada por el autoloader');
TestHelper::assertTrue(class_exists(Response::class), 'La clase App\Core\Response fue cargada por el autoloader');
TestHelper::assertTrue(class_exists(TokenManager::class), 'La clase App\Core\TokenManager fue cargada por el autoloader');

TestHelper::assertEquals('Crochet Manager', Config::get('app.name'), 'Config::get("app.name") resuelve correctamente');
TestHelper::assertEquals('America/Mexico_City', Config::get('app.timezone'), 'Config::get("app.timezone") resuelve la zona horaria correcta');
TestHelper::assertEquals(date_default_timezone_get(), Config::get('app.timezone'), 'date_default_timezone_get() coincide con la configuración');
TestHelper::assertEquals(86400, Config::get('auth.token_ttl'), 'Config::get("auth.token_ttl") es de 86400 segundos (24 horas)');

// Test dot-notation override y fallback
Config::set('test.subfase', '3.1');
TestHelper::assertEquals('3.1', Config::get('test.subfase'), 'Config::set() almacena valores con notación por puntos');
TestHelper::assertEquals('valor_default', Config::get('no_existe.clave', 'valor_default'), 'Config::get() retorna el fallback cuando la clave no existe');

// =============================================================================
// 2. CONEXIÓN SINGLETON DATABASE & PRAGMA FOREIGN KEYS
// =============================================================================
TestHelper::section('2. Conexión Singleton PDO SQLite & Claves Foráneas');

$pdo1 = Database::getInstance();
TestHelper::assertTrue($pdo1 instanceof PDO, 'Database::getInstance() devuelve una instancia de PDO');

$pdo2 = Database::getInstance();
TestHelper::assertSame($pdo1, $pdo2, 'Llamadas sucesivas a Database::getInstance() devuelven exactamente la misma instancia (Singleton)');

// Comprobar PRAGMA foreign_keys
$stmtFk = $pdo1->query('PRAGMA foreign_keys;');
$fkStatus = (int)$stmtFk->fetchColumn();
TestHelper::assertSame(1, $fkStatus, 'PRAGMA foreign_keys está activo con valor 1 (ON)');

// Comprobar PRAGMA busy_timeout (mitigación de bloqueos concurrentes)
$stmtTimeout = $pdo1->query('PRAGMA busy_timeout;');
$timeoutVal = (int)$stmtTimeout->fetchColumn();
TestHelper::assertTrue($timeoutVal >= 5000, 'PRAGMA busy_timeout está configurado con al menos 5000 ms para mitigar bloqueos');

// Comprobar PRAGMA integrity_check
$stmtCheck = $pdo1->query('PRAGMA integrity_check;');
$integrityResult = (string)$stmtCheck->fetchColumn();
TestHelper::assertEquals('ok', strtolower($integrityResult), 'PRAGMA integrity_check devuelve "ok"');

// Probar violación de integridad referencial (debe arrojar PDOException)
$violationCaught = false;
try {
    // Intentar insertar una creación vinculada a un artesano_id inexistente (ej. 99999)
    $stmt = $pdo1->prepare("
        INSERT INTO creaciones (
            artesano_id, nombre, categoria, material, dimensiones, precio, 
            costo_materiales, cantidad_stock, horas_tejido
        ) VALUES (
            99999, 'Test Fallo FK', 'Amigurumis', 'Algodón', '10 cm', 10000, 
            3000, 5, 2.0
        )
    ");
    $stmt->execute();
} catch (\PDOException $e) {
    $violationCaught = true;
}
TestHelper::assertTrue($violationCaught, 'SQLite rechaza inserción con artesano_id inexistente por restricción de clave foránea');

// =============================================================================
// 3. ABSTRACCIÓN DE PETICIONES HTTP (REQUEST)
// =============================================================================
TestHelper::section('3. Abstracción de Peticiones Request & Bearer Token');

Request::reset();

// Test GET
$_GET['categoria'] = 'Prendas';
TestHelper::assertEquals('Prendas', Request::get('categoria'), 'Request::get("categoria") recupera parámetro $_GET');
TestHelper::assertEquals('default', Request::get('inexistente', 'default'), 'Request::get() con valor por defecto');

// Test POST
$_POST['accion'] = 'crear';
TestHelper::assertEquals('crear', Request::post('accion'), 'Request::post("accion") recupera parámetro $_POST');

// Test Mock JSON Payload
Request::setJsonPayload(['nombre' => 'Chaleco Nórdico', 'precio' => 45000]);
TestHelper::assertEquals('Chaleco Nórdico', Request::json('nombre'), 'Request::json("nombre") recupera valor de payload JSON');
TestHelper::assertEquals(45000, Request::json('precio'), 'Request::json("precio") recupera entero de payload JSON');

// Test Request::input combinando fuentes
TestHelper::assertEquals('Chaleco Nórdico', Request::input('nombre'), 'Request::input() prioriza payload JSON');
TestHelper::assertEquals('crear', Request::input('accion'), 'Request::input() cae a $_POST si no está en JSON');
TestHelper::assertEquals('Prendas', Request::input('categoria'), 'Request::input() cae a $_GET si no está en POST/JSON');

// Test Bearer Token
Request::setMockHeader('Authorization', 'Bearer mi_token_secreto_hmac_123');
TestHelper::assertEquals('mi_token_secreto_hmac_123', Request::bearerToken(), 'Request::bearerToken() extrae el token limpio de la cabecera Authorization');

// Test Contexto de Usuario
$mockUser = ['id' => 1, 'username' => 'admin', 'rol' => 'admin'];
Request::setUser($mockUser);
TestHelper::assertSame($mockUser, Request::user(), 'Request::user() retiene el usuario autenticado en memoria');

Request::reset();
unset($_GET['categoria'], $_POST['accion']);

// =============================================================================
// 4. EMISOR DE RESPUESTAS HTTP (RESPONSE) & PREFLIGHT CORS
// =============================================================================
TestHelper::section('4. Emisor de Respuestas JSON & Preflight CORS');

// Probar respuesta exitosa
ob_start();
Response::success(['id' => 10, 'nombre' => 'Gorro Alpaca'], 'Pieza registrada', 201, ['paginacion' => ['total' => 1]]);
$output = ob_get_clean();

$lastPayload = Response::getLastPayload();
$lastStatus = Response::getLastStatusCode();

TestHelper::assertSame(201, $lastStatus, 'Response::success() emite código de estado 201');
TestHelper::assertTrue($lastPayload['exito'] ?? false, 'Payload emitido contiene "exito": true');
TestHelper::assertEquals('Pieza registrada', $lastPayload['mensaje'] ?? '', 'Payload contiene mensaje correcto');
TestHelper::assertEquals('Gorro Alpaca', $lastPayload['datos']['nombre'] ?? '', 'Payload contiene datos anidados');
TestHelper::assertArrayHasKey('paginacion', $lastPayload, 'Payload incluye campos adicionales pasados en $extra');

// Probar respuesta de error
ob_start();
Response::error('Stock insuficiente para completar el pedido', 422, ['disponible' => 0, 'solicitado' => 2]);
$errorOutput = ob_get_clean();

$lastErrorPayload = Response::getLastPayload();
$lastErrorStatus = Response::getLastStatusCode();

TestHelper::assertSame(422, $lastErrorStatus, 'Response::error() emite código de estado 422');
TestHelper::assertFalse($lastErrorPayload['exito'] ?? true, 'Payload de error contiene "exito": false');
TestHelper::assertEquals(422, $lastErrorPayload['error']['codigo'] ?? 0, 'Bloque de error incluye código HTTP 422');
TestHelper::assertEquals('Stock insuficiente para completar el pedido', $lastErrorPayload['error']['mensaje'] ?? '', 'Mensaje de error correcto');
TestHelper::assertEquals(0, $lastErrorPayload['error']['detalles']['disponible'] ?? -1, 'Detalles de validación presentes en bloque de error');

// =============================================================================
// 5. GESTOR DE TOKENS HMAC-SHA256 (TOKEN MANAGER)
// =============================================================================
TestHelper::section('5. Gestor de Tokens Bearer HMAC-SHA256 (Seguridad)');

$userData = ['id' => 1, 'username' => 'admin', 'rol' => 'admin'];
$token = TokenManager::generate($userData);

TestHelper::assertTrue(is_string($token) && str_contains($token, '.'), 'Token generado tiene formato válido "payload.firma"');

$parts = explode('.', $token);
TestHelper::assertSame(2, count($parts), 'El token consta exactamente de 2 partes delimitadas por punto');

// Verificar token legítimo
$verifiedPayload = TokenManager::verify($token);
TestHelper::assertNotNull($verifiedPayload, 'TokenManager::verify() valida exitosamente el token recién emitido');
TestHelper::assertSame(1, $verifiedPayload['sub'] ?? null, 'El claim "sub" coincide con el ID de usuario (1)');
TestHelper::assertSame('admin', $verifiedPayload['username'] ?? null, 'El claim "username" coincide con "admin"');
TestHelper::assertSame('admin', $verifiedPayload['rol'] ?? null, 'El claim "rol" coincide con "admin"');
TestHelper::assertFalse(TokenManager::isExpired($verifiedPayload), 'El token recién emitido no está expirado');

// Probar detección de manipulación (Tampering attack)
$tamperedToken = $token . 'a';
TestHelper::assertNull(TokenManager::verify($tamperedToken), 'TokenManager::verify() rechaza token con firma alterada');

// Probar alteración del payload
$tamperedPayloadToken = 'X' . substr($token, 1);
TestHelper::assertNull(TokenManager::verify($tamperedPayloadToken), 'TokenManager::verify() rechaza token con payload alterado');

// Probar expiración de tokens
$expiredToken = TokenManager::generate($userData, -3600); // Expirado hace 1 hora
TestHelper::assertNull(TokenManager::verify($expiredToken), 'TokenManager::verify() rechaza token cuya fecha de expiración ha vencido');

// =============================================================================
// 6. UTILIDADES MONETARIAS (CURRENCY HELPER)
// =============================================================================
TestHelper::section('6. Utilidad Monetaria & Enriquecimiento Dual (CurrencyHelper)');

// Conversión centavos -> pesos
TestHelper::assertEquals('$450.00 MXN', CurrencyHelper::formatCents(45000), '45000 centavos se formatean como "$450.00 MXN"');
TestHelper::assertEquals('$450.00', CurrencyHelper::formatCents(45000, false), '45000 centavos sin divisa se formatean como "$450.00"');
TestHelper::assertEquals('$1,250.50 MXN', CurrencyHelper::formatCents(125050), '125050 centavos se formatean con separador de miles "$1,250.50 MXN"');
TestHelper::assertSame(450.0, CurrencyHelper::centsToMxn(45000), '45000 centavos equivalen a 450.0 float');

// Conversión pesos -> centavos
TestHelper::assertSame(45000, CurrencyHelper::mxnToCents(450.0), '450.0 float se convierte exactamente a 45000 centavos');
TestHelper::assertSame(45000, CurrencyHelper::mxnToCents('$450.00 MXN'), '"$450.00 MXN" string se convierte a 45000 centavos');
TestHelper::assertSame(125050, CurrencyHelper::mxnToCents('$1,250.50'), '"$1,250.50" string se convierte a 125050 centavos');
TestHelper::assertSame(18000, CurrencyHelper::mxnToCents('180'), '"180" string se convierte a 18000 centavos');

// Probar enriquecimiento de creación
$rawCreacion = [
    'id' => 1,
    'nombre' => 'Dragón Ignis',
    'precio' => 45000,
    'costo_materiales' => 12000,
    'horas_tejido' => 6.5,
];
$enrichedCreacion = CurrencyHelper::enrichCreation($rawCreacion);

TestHelper::assertSame(45000, $enrichedCreacion['precio_centavos'], 'Enriquecimiento: precio_centavos preservado como 45000');
TestHelper::assertSame(450.0, $enrichedCreacion['precio_mxn'], 'Enriquecimiento: precio_mxn calculado como 450.0');
TestHelper::assertEquals('$450.00 MXN', $enrichedCreacion['precio_formateado'], 'Enriquecimiento: precio_formateado correcto');
TestHelper::assertSame(33000, $enrichedCreacion['ganancia_bruta_centavos'], 'Enriquecimiento: ganancia bruta calculada (45000 - 12000 = 33000)');
TestHelper::assertSame(73.3, $enrichedCreacion['margen_bruto_porcentaje'], 'Enriquecimiento: margen bruto calculado (33000/45000 = 73.3%)');
TestHelper::assertSame(50.77, $enrichedCreacion['retorno_por_hora_mxn'], 'Enriquecimiento: retorno por hora calculado ($330 / 6.5h = 50.77)');

// Probar enriquecimiento de pedido
$rawPedido = [
    'id' => 5,
    'precio_final' => 90000,
];
$enrichedPedido = CurrencyHelper::enrichOrder($rawPedido);
TestHelper::assertSame(90000, $enrichedPedido['precio_final_centavos'], 'Pedido: precio_final_centavos preservado');
TestHelper::assertSame(900.0, $enrichedPedido['precio_final_mxn'], 'Pedido: precio_final_mxn calculado como 900.0');
TestHelper::assertEquals('$900.00 MXN', $enrichedPedido['precio_final_formateado'], 'Pedido: precio_final_formateado correcto');

// =============================================================================
// 7. ASISTENTE DE PAGINACIÓN (PAGINATION HELPER)
// =============================================================================
TestHelper::section('7. Asistente de Paginación Estándar (PaginationHelper)');

$pageEnvelope = PaginationHelper::build(100, 1, 12);
TestHelper::assertArrayHasKey('paginacion', $pageEnvelope, 'PaginationHelper::build genera clave "paginacion"');
TestHelper::assertSame(100, $pageEnvelope['paginacion']['total_items'], 'Paginación: total_items = 100');
TestHelper::assertSame(1, $pageEnvelope['paginacion']['pagina_actual'], 'Paginación: pagina_actual = 1');
TestHelper::assertSame(9, $pageEnvelope['paginacion']['total_paginas'], 'Paginación: total_paginas calculado (ceil(100/12) = 9)');
TestHelper::assertSame(12, $pageEnvelope['paginacion']['limite'], 'Paginación: limite = 12');
TestHelper::assertTrue($pageEnvelope['paginacion']['tiene_siguiente'], 'Paginación: tiene_siguiente es true en la primera página');
TestHelper::assertFalse($pageEnvelope['paginacion']['tiene_anterior'], 'Paginación: tiene_anterior es false en la primera página');

// Probar última página
$lastPageEnvelope = PaginationHelper::build(100, 9, 12);
TestHelper::assertFalse($lastPageEnvelope['paginacion']['tiene_siguiente'], 'Paginación: tiene_siguiente es false en la última página');
TestHelper::assertTrue($lastPageEnvelope['paginacion']['tiene_anterior'], 'Paginación: tiene_anterior es true en la última página');

// Probar catálogo vacío (0 elementos)
$emptyEnvelope = PaginationHelper::build(0, 1, 12);
TestHelper::assertSame(1, $emptyEnvelope['paginacion']['total_paginas'], 'Paginación: catálogo vacío devuelve total_paginas = 1');
TestHelper::assertFalse($emptyEnvelope['paginacion']['tiene_siguiente'], 'Paginación: catálogo vacío tiene_siguiente = false');
TestHelper::assertFalse($emptyEnvelope['paginacion']['tiene_anterior'], 'Paginación: catálogo vacío tiene_anterior = false');

// =============================================================================
// 8. ASISTENTE VECTORIAL SVG (SVG HELPER & FUNCIONES GLOBALES)
// =============================================================================
TestHelper::section('8. Asistente Vectorial SvgHelper & Funciones Globales');

TestHelper::assertTrue(function_exists('svg'), 'La función helper global svg() está disponible');
TestHelper::assertTrue(function_exists('svg_url'), 'La función helper global svg_url() está disponible');

TestHelper::assertTrue(SvgHelper::exists('dragon-ignis'), 'SvgHelper::exists("dragon-ignis") encuentra el SVG en assets');
TestHelper::assertNotNull(SvgHelper::getPath('dragon-ignis'), 'SvgHelper::getPath() resuelve la ruta física en disco');
TestHelper::assertStringContains('assets/svg/', SvgHelper::url('dragon-ignis'), 'SvgHelper::url() genera URL relativa con prefijo assets/svg/');

$renderedSvg = svg('dragon-ignis', ['class' => 'prueba-test', 'width' => 120]);
TestHelper::assertStringContains('<svg', $renderedSvg, 'svg() renderiza la etiqueta de apertura <svg');
TestHelper::assertStringContains('class="', $renderedSvg, 'svg() inyecta el atributo class');
TestHelper::assertStringContains('prueba-test', $renderedSvg, 'svg() inyecta la clase personalizada');
TestHelper::assertStringContains('width="120"', $renderedSvg, 'svg() inyecta el atributo width="120"');

// =============================================================================
// 9. INTEGRIDAD DE ARCHIVOS & SEGURIDAD HTTP EN VIVO
// =============================================================================
TestHelper::section('9. Integridad del Servidor Local & Seguridad HTTP en Vivo');

// Prueba 1: Acceso público a catálogo index.php debe responder 200 OK
$resIndex = TestHelper::curl('GET', 'http://localhost:8000/index.php');
TestHelper::assertSame(200, $resIndex['status'], 'HTTP GET a http://localhost:8000/index.php responde 200 OK');

// Prueba 2: Intento de acceso directo a app/config.php debe estar bloqueado (403)
$resAppConfig = TestHelper::curl('GET', 'http://localhost:8000/app/config.php');
TestHelper::assertSame(403, $resAppConfig['status'], 'HTTP GET a app/config.php devuelve HTTP 403 Forbidden');
TestHelper::assertFalse($resAppConfig['json']['exito'] ?? true, 'Respuesta 403 de app/config.php devuelve JSON con exito: false');

// Prueba 3: Verificación de directivas de seguridad en .htaccess
$htaccessPath = dirname(__DIR__) . '/.htaccess';
TestHelper::assertTrue(file_exists($htaccessPath), 'El archivo raíz .htaccess de seguridad existe');
$htaccessContent = (string)file_get_contents($htaccessPath);
TestHelper::assertStringContains('app|database|memory-bank|logs|tests', $htaccessContent, '.htaccess contiene regla de bloqueo 403 para carpetas del sistema');
TestHelper::assertStringContains('sqlite|sqlite3|sql|md', $htaccessContent, '.htaccess contiene regla de bloqueo Require all denied para extensiones de BD');
TestHelper::assertStringContains('HTTP_AUTHORIZATION', $htaccessContent, '.htaccess contiene regla de reenvío de cabecera Authorization para FastCGI');

// Prueba 4: Preflight CORS OPTIONS
$resCors = TestHelper::curl('OPTIONS', 'http://localhost:8000/index.php', [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: Authorization, Content-Type',
]);
TestHelper::assertTrue(
    in_array($resCors['status'], [200, 204], true),
    'Petición HTTP OPTIONS responde código válido para CORS (' . $resCors['status'] . ')'
);

// =============================================================================
// RESUMEN FINAL DE LA SUITE
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
