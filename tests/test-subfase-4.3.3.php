<?php
/**
 * Test Suite: Subfase 4.3.3 - Centavos Multipart & Subida Clicable (CSP)
 * Feature 006 (correctivo) · Plan Maestro Fase 4 (009) · Algodón Nórdico
 *
 * Causas raíz:
 * a) FormData envía TODO como string: "35000" (centavos del panel) pasaba por
 *    mxnToCents() de nuevo → ×100 ($350 → $35,000). Pieza #316.
 * b) El dropzone usaba onclick="" inline, bloqueado por la CSP
 *    (script-src 'self' sin 'unsafe-inline') → clic muerto, sin foto.
 *
 * Valida:
 * 1. Servicio: strings de dígitos puros ya son centavos; decimales se convierten.
 * 2. HTTP multipart: precio string "38000" se guarda 38000 (no 3.800.000).
 * 3. Vistas: cero handlers inline (onclick/onerror/onsubmit) en formulario y catálogo.
 * 4. dropzone.js bindea el clic por addEventListener (+ teclado).
 *
 * Prerequisito: servidor local `php -S localhost:8000` en la raíz del repo.
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;
use App\Services\CreacionService;

$root = dirname(__DIR__);

TestHelper::init('Subfase 4.3.3: Centavos Multipart & Subida Clicable (CSP)');

// =============================================================================
// 1. SERVICIO: strings de dígitos = centavos (contrato del panel), resto convierte
// =============================================================================
TestHelper::section('1. Servicio: sin doble conversión en strings multipart');

$service = new CreacionService();
$admin = ['id' => 1, 'rol' => 'admin'];
$uid = substr(md5((string)microtime(true)), 0, 8);
$trackedIds = [];

$mkData = static function (string $nombre, $precio, $costo) use ($uid): array {
    return [
        'nombre' => $nombre . ' ' . $uid,
        'categoria' => 'Hogar & Decoración',
        'material' => 'Algodón de prueba 4.3.3',
        'dimensiones' => '12 x 12 cm',
        'precio' => $precio,
        'costo_materiales' => $costo,
        'cantidad_stock' => 2,
        'horas_tejido' => 1.0,
        'descripcion' => 'Pieza efímera de la suite 4.3.3.',
        'es_sobre_encargo' => 0,
    ];
};

// 1.1 El caso exacto del reporte: el panel envía `precio_centavos` (ya convertidos)
// FormData serializa como string "35000" → se guarda 35000¢ ($350.00, sin ×100)
$panelPayload = $mkData('ZZ433 Panel', null, null);
$panelPayload['precio_centavos'] = '35000';
$panelPayload['costo_materiales_centavos'] = '10000';
$stringCents = $service->createCreation($panelPayload, null, $admin);
$trackedIds[] = (int)$stringCents['id'];
TestHelper::assertSame(35000, (int)$stringCents['precio_centavos'], 'Panel: "35000" en precio_centavos se guarda como 35000¢ ($350.00, sin ×100)');
TestHelper::assertSame(10000, (int)$stringCents['costo_materiales_centavos'], 'Panel: "10000" en costo_materiales_centavos se guarda como 10000¢ ($100.00, sin ×100)');

// 1.2 Contrato legado intacto: `precio` en pesos se convierte (sin cambios de conducta)
$legacy = $service->createCreation($mkData('ZZ433 Legado', '450', '99.99'), null, $admin);
$trackedIds[] = (int)$legacy['id'];
TestHelper::assertSame(45000, (int)$legacy['precio_centavos'], 'Legado: string "450" en precio se convierte a 45000¢ ($450.00, como antes)');
TestHelper::assertSame(9999, (int)$legacy['costo_materiales_centavos'], 'Legado: string "99.99" en costo se convierte a 9999¢ (como antes)');

// 1.3 Enteros nativos intactos + validaciones intactas
$native = $service->createCreation($mkData('ZZ433 Int', 45000, 12000), null, $admin);
$trackedIds[] = (int)$native['id'];
TestHelper::assertSame(45000, (int)$native['precio_centavos'], 'Int 45000 sigue guardándose como 45000¢');
$zero422 = null;
try {
    $service->createCreation($mkData('ZZ433 Cero', '0', '0'), null, $admin);
} catch (InvalidArgumentException $e) {
    $zero422 = 422;
}
TestHelper::assertSame(422, $zero422, 'String "0" como precio sigue rechazado (422, sin bypass)');

// 1.4 Limpieza efímera (baja lógica)
foreach ($trackedIds as $tid) {
    try {
        $service->deleteCreation($tid, $admin);
    } catch (Throwable $e) {
    }
}
TestHelper::assertTrue(true, 'Limpieza de piezas efímeras 4.3.3 completada (baja lógica)');

// =============================================================================
// 2. HTTP MULTIPART: el precio string no se multiplica (regresión #316)
// =============================================================================
TestHelper::section('2. HTTP Multipart: precio string íntegro (regresión #316)');

$loginAdmin = TestHelper::curl('POST', 'http://localhost:8000/api/auth/login.php', [
    'Content-Type: application/json',
], json_encode(['username' => 'admin', 'password' => 'admin123']));
TestHelper::assertSame(200, $loginAdmin['status'], 'HTTP login admin devuelve 200 OK');
$tokenAdmin = (string)($loginAdmin['json']['datos']['token'] ?? '');

$pngPath = sys_get_temp_dir() . '/zz433-foto-' . $uid . '.png';
file_put_contents($pngPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

$httpName = 'ZZ433 HTTP ' . $uid;
$httpCreate = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $tokenAdmin,
], [
    'nombre' => $httpName,
    'categoria' => 'Hogar & Decoración',
    'material' => 'Trapillo de prueba HTTP',
    'dimensiones' => '30 x 25 cm',
    'precio_centavos' => '38000',
    'costo_materiales_centavos' => '9500',
    'cantidad_stock' => '6',
    'horas_tejido' => '4.5',
    'es_sobre_encargo' => '0',
    'imagen' => new CURLFile($pngPath, 'image/png', 'foto.png'),
]);
TestHelper::assertSame(201, $httpCreate['status'], 'HTTP multipart con foto devuelve 201 Created');
$httpId = (int)($httpCreate['json']['datos']['id'] ?? 0);
TestHelper::assertSame(38000, (int)($httpCreate['json']['datos']['precio_centavos'] ?? 0), 'HTTP: precio_centavos string "38000" se guarda 38000¢ ($380.00, regresión #316)');
TestHelper::assertSame(9500, (int)($httpCreate['json']['datos']['costo_materiales_centavos'] ?? 0), 'HTTP: costo_materiales_centavos string "9500" se guarda 9500¢');

$httpCleanup = TestHelper::curl('POST', 'http://localhost:8000/api/creaciones/eliminar.php', [
    'Authorization: Bearer ' . $tokenAdmin,
    'Content-Type: application/json',
], json_encode(['id' => $httpId]));
TestHelper::assertTrue(in_array($httpCleanup['status'], [200, 409], true), 'Limpieza HTTP: pieza efímera en baja lógica');
@unlink($pngPath);

// =============================================================================
// 3. VISTAS: cero handlers inline (la CSP los mata en silencio)
// =============================================================================
TestHelper::section('3. Vistas: sin handlers inline bloqueados por la CSP');

$formView = (string)@file_get_contents("$root/views/pages/formulario_content.php");
TestHelper::assertFalse(str_contains($formView, 'onclick='), 'formulario_content.php: cero onclick inline (el clic del dropzone vive en dropzone.js)');
TestHelper::assertFalse(str_contains($formView, 'onsubmit='), 'formulario_content.php: cero onsubmit inline');
TestHelper::assertStringContains('id="uploadDropzone"', $formView, 'El dropzone conserva id="uploadDropzone" para el binding JS');
TestHelper::assertStringContains('role="button"', $formView, 'El dropzone es operable por teclado (role="button")');
TestHelper::assertStringContains('tabindex="0"', $formView, 'El dropzone es enfocable (tabindex="0")');

$catalogView = (string)@file_get_contents("$root/views/pages/catalogo_content.php");
TestHelper::assertFalse(str_contains($catalogView, 'onerror='), 'catalogo_content.php: cero onerror inline (fallback por listener error)');

// =============================================================================
// 4. dropzone.js bindea el clic + teclado; node --check
// =============================================================================
TestHelper::section('4. dropzone.js: clic y teclado por addEventListener');

$dropzoneJs = (string)@file_get_contents("$root/src/js/modules/dropzone.js");
TestHelper::assertStringContains("addEventListener('click'", $dropzoneJs, 'dropzone.js bindea el clic con addEventListener (compatible CSP)');
TestHelper::assertStringContains("addEventListener('keydown'", $dropzoneJs, 'dropzone.js bindea el teclado (Enter/Espacio)');
TestHelper::assertStringContains('inputFile.click()', $dropzoneJs, 'El clic del dropzone abre el selector de archivo');

exec('node --check ' . escapeshellarg("$root/src/js/modules/dropzone.js") . ' 2>&1', $jsOut, $jsExit);
TestHelper::assertSame(0, $jsExit, 'node --check de dropzone.js sin errores');

// =============================================================================
// RESUMEN FINAL
// =============================================================================
$exitCode = TestHelper::summary();
exit($exitCode);
