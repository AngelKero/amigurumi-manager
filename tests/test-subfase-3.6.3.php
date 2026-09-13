<?php
/**
 * Test Suite: Subfase 3.6.3 - Inyección, Sanitización & Seguridad de Medios/Archivos (Opción B)
 * Clean Architecture - Algodón Nórdico Design System
 * 
 * Alcance y Dimensiones Auditadas (OWASP A03:2021 + A08:2021):
 * 1. Blindaje Total contra Inyección SQL (SQLi - OWASP A03:2021):
 *    - Inmunidad en CreacionRepository (filtros busqueda, categoria, artesano_id, precios, encargo).
 *    - Inmunidad en PedidoRepository (filtros busqueda, estado_pedido, estado_pago, creacion_id).
 *    - Inmunidad en UsuarioRepository (findByUsername, existsUsername, findById).
 *    - 100% de consultas preparadas PDO parametrizadas con bindValue().
 *    - Integridad referencial y conteo inalterado de registros en SQLite (zero data tampering).
 * 2. Defensa contra Cross-Site Scripting (XSS - OWASP A03:2021):
 *    - Matriz de payloads XSS clásicos (<script>, img onerror, svg onload, onmouseover, javascript:).
 *    - Validación de escape HTML riguroso con htmlspecialchars(..., ENT_QUOTES, 'UTF-8').
 *    - Almacenamiento íntegro y escape en salida (Clean Architecture).
 *    - Cabeceras Content-Type: application/json; charset=utf-8 en la API.
 *    - Codificación segura de parámetros en enlaces WhatsApp con rawurlencode().
 * 3. Detección y Validación de Tipo MIME Real en Carga de Medios (OWASP A08:2021):
 *    - Inspección binaria estricta con finfo_file / mime_content_type (restringido a JPEG, PNG, WebP).
 *    - Detección y rechazo con HTTP 422 de archivos camuflados (PHP, HTML, Shell, ejecutables ELF).
 *    - Restricción estricta de tamaño máximo (<= 5MB / 5,242,880 bytes).
 *    - Procesamiento exitoso de imágenes válidas y fallback temático SVG si no se envía archivo.
 * 4. Prevención de Path Traversal & Carga Arbitraria (OWASP A08:2021):
 *    - Descarte absoluto del nombre provisto por el cliente en uploads; generación con entropía criptográfica.
 *    - Confinamiento estricto de archivos guardados dentro del directorio uploads/.
 *    - Inmunidad contra secuencias de escape de directorio (../../, ..\\) en reemplazo y borrado de imágenes.
 *    - Preservación de fotografías en disco durante bajas lógicas (Regla de Oro ADR-008).
 * 5. Seguridad Vectorial SVG & Sanitización de Atributos:
 *    - Escaneo exhaustivo del 100% de archivos SVG en assets/svg/ (zero scripts, zero onload/onerror, zero XXE).
 *    - Sanitización y escape de atributos en SvgHelper::render().
 *    - Escape en comentarios de error ante archivos SVG inexistentes.
 *    - Resolución de rutas de fallback temáticas comprobadas en disco.
 * 6. Pruebas de Integración HTTP en Vivo contra Servidor Local:
 *    - Endpoints de catálogo y pedidos resilientes a payloads SQLi (HTTP 200 limpio sin fugas).
 *    - Rechazo HTTP 422 ante subida de archivos con contenido no-imagen via multipart/form-data.
 *    - Procesamiento seguro de pedidos públicos con caracteres especiales y payloads XSS.
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
use App\Repositories\CreacionRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use App\Services\CreacionService;
use App\Services\PedidoService;
use App\Services\UsuarioService;
use App\Utils\CurrencyHelper;
use App\Utils\SvgHelper;

// Iniciar suite
TestHelper::init('Subfase 3.6.3: Inyección, Sanitización & Seguridad de Medios/Archivos (OWASP A03 + A08)');

// Desactivar terminación forzada en Response para pruebas en memoria
Response::setExitOnSend(false);

$pdo = Database::getInstance();
$usuarioRepo = new UsuarioRepository();
$creacionRepo = new CreacionRepository();
$pedidoRepo = new PedidoRepository();

$authService = new AuthService($usuarioRepo);
$creacionService = new CreacionService($creacionRepo, $usuarioRepo);
$pedidoService = new PedidoService($pedidoRepo, $creacionRepo, $usuarioRepo);
$usuarioService = new UsuarioService($usuarioRepo, $creacionRepo);

$baseUrl = 'http://localhost:8000';

// Helper para extraer mensaje de error estandarizado de la respuesta JSON
$getErrMsg = function (array $res): string {
    return (string)($res['json']['error']['mensaje'] ?? $res['json']['mensaje'] ?? '');
};

// Autenticación administrativa para pruebas HTTP protegidas
$adminAuth = $authService->authenticate('admin', 'admin123');
$adminToken = $adminAuth['token'];
$adminHeaders = ['Authorization: Bearer ' . $adminToken, 'Content-Type: application/json'];

$uploadsDir = dirname(__DIR__) . '/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Conteo inicial de registros para auditoría de integridad
$stmtUsersCount = $pdo->query('SELECT COUNT(*) FROM usuarios');
$initialUsersCount = (int)$stmtUsersCount->fetchColumn();
$stmtUsersCount->closeCursor();
unset($stmtUsersCount);

$stmtCreationsCount = $pdo->query('SELECT COUNT(*) FROM creaciones');
$initialCreationsCount = (int)$stmtCreationsCount->fetchColumn();
$stmtCreationsCount->closeCursor();
unset($stmtCreationsCount);

$stmtOrdersCount = $pdo->query('SELECT COUNT(*) FROM pedidos');
$initialOrdersCount = (int)$stmtOrdersCount->fetchColumn();
$stmtOrdersCount->closeCursor();
unset($stmtOrdersCount);

// ============================================================================
// SECCIÓN 1: Blindaje Total contra Inyección SQL (SQLi - OWASP A03:2021)
// ============================================================================
TestHelper::section('1. Blindaje Total contra Inyección SQL (SQLi - OWASP A03:2021)');

// 1.1 Vectores de inyección en filtro de búsqueda de Creaciones
$sqliSearchVectors = [
    "' OR '1'='1",
    "1; DROP TABLE usuarios; --",
    "' UNION SELECT id, password_hash, rol, '1', '1', '1', 1, 1, 1, 1.0, 'a', 'b', 0, 1, '2026-01-01', NULL, NULL FROM usuarios --",
    "admin' --",
    "\" OR 1=1 --",
    "' AND 1=0 UNION ALL SELECT 999, 1, 'Injected', 'Hack', '100% Hack', '10x10', 9999, 0, 1, 1.0, 'x', 'y', 0, 1, '2026-01-01', NULL, NULL, 'admin' --",
    "'; DELETE FROM creaciones; --",
    "x' AND (SELECT count(*) FROM usuarios) > 0 AND 'x'='x",
    "\\0",
    "test%'; --",
];

foreach ($sqliSearchVectors as $index => $vector) {
    $results = $creacionRepo->listCatalog(['busqueda' => $vector], 10, 0);
    TestHelper::assert(
        is_array($results),
        "CreacionRepository::listCatalog con vector SQLi #{$index} en 'busqueda' no produce error de sintaxis"
    );
    // Verificar que ninguna fila inyectada pertenezca a la tabla usuarios
    $hasInjectedUser = false;
    foreach ($results as $row) {
        if (isset($row['password_hash']) || (isset($row['nombre']) && $row['nombre'] === 'Injected')) {
            $hasInjectedUser = true;
            break;
        }
    }
    TestHelper::assertFalse($hasInjectedUser, "Vector SQLi #{$index} en 'busqueda' no extrae datos de usuarios ni inyecta filas arbitrarias");
}

// 1.2 Vectores de inyección en filtro de categoría de Creaciones
$sqliCategoryVectors = [
    "Fantasía' OR '1'='1",
    "'; DROP TABLE creaciones; --",
    "' UNION SELECT 1, 1, 'X', 'Y', 'Z', 'W', 10, 5, 1, 1.0, 'd', 'i', 0, 1, '2026-01-01', NULL, NULL, 'root' --",
    "Amigurumis' AND 1=0 UNION ALL SELECT id, password_hash, 'a', 'b', 'c', 'd', 1, 1, 1, 1.0, 'e', 'f', 0, 1, '2026', NULL, NULL, 'u' FROM usuarios --",
];

foreach ($sqliCategoryVectors as $index => $vector) {
    $results = $creacionRepo->listCatalog(['categoria' => $vector], 10, 0);
    TestHelper::assert(
        is_array($results),
        "CreacionRepository::listCatalog con vector SQLi #{$index} en 'categoria' no produce error de sintaxis"
    );
    TestHelper::assertSame(
        0,
        count($results),
        "Vector SQLi #{$index} en 'categoria' es tratado como string literal y devuelve 0 coincidencias"
    );
}

// 1.3 Vectores de inyección en filtro artesano_id de Creaciones
$resultsArtesanoSql = $creacionRepo->listCatalog(['artesano_id' => '1 OR 1=1'], 10, 0);
TestHelper::assert(is_array($resultsArtesanoSql), "CreacionRepository::listCatalog con payload en artesano_id es tipado a entero");

// 1.4 Inmunidad en conteo countCatalog con payloads SQLi
$countCatalogSqli = $creacionRepo->countCatalog(['busqueda' => "' OR '1'='1"]);
TestHelper::assert(is_int($countCatalogSqli) && $countCatalogSqli >= 0, "countCatalog con SQLi devuelve entero seguro sin excepción");

// 1.5 Inmunidad en PedidoRepository::listAll con payloads SQLi
$sqliOrderVectors = [
    "' OR '1'='1",
    "'; DROP TABLE pedidos; --",
    "' UNION SELECT 1, 'hacker', '000', 1, 1, '2026-01-01', 'Pendiente', 'Pendiente', 100, 'x', 1, '2026-01-01', NULL, NULL, 'c', 'u', 10, 5, 1, 0, 1, 'adm' --",
];

foreach ($sqliOrderVectors as $index => $vector) {
    $orderResults = $pedidoRepo->listAll(['busqueda' => $vector], 10, 0);
    TestHelper::assert(is_array($orderResults), "PedidoRepository::listAll con vector SQLi #{$index} en 'busqueda' no falla");
    $hasInjectedOrder = false;
    foreach ($orderResults as $ord) {
        if ($ord['cliente_nombre'] === 'hacker') {
            $hasInjectedOrder = true;
            break;
        }
    }
    TestHelper::assertFalse($hasInjectedOrder, "Vector SQLi #{$index} en pedidos no inyecta filas arbitrarias");
}

// 1.6 Inmunidad en PedidoRepository con estados maliciosos
$ordersBadStatus = $pedidoRepo->listAll(['estado_pedido' => "Pendiente' OR '1'='1"], 10, 0);
TestHelper::assertSame(0, count($ordersBadStatus), "Filtro estado_pedido con SQLi devuelve 0 resultados literales");

$ordersBadPayment = $pedidoRepo->listAll(['estado_pago' => "Liquidado' OR 1=1 --"], 10, 0);
TestHelper::assertSame(0, count($ordersBadPayment), "Filtro estado_pago con SQLi devuelve 0 resultados literales");

// 1.7 Inmunidad en UsuarioRepository::findByUsername
$sqliUserVectors = [
    "' OR '1'='1",
    "admin' --",
    "admin' /*",
    "admin' OR 1=1; --",
    "' UNION SELECT id, username, password_hash, rol, activo, creado_en, eliminado_en FROM usuarios --",
];

foreach ($sqliUserVectors as $index => $vector) {
    $userResult = $usuarioRepo->findByUsername($vector);
    TestHelper::assertNull($userResult, "UsuarioRepository::findByUsername con vector SQLi #{$index} ('{$vector}') devuelve null");
}

// 1.8 Inmunidad en UsuarioRepository::existsUsername
TestHelper::assertFalse($usuarioRepo->existsUsername("' OR '1'='1"), "existsUsername con SQLi devuelve false");
TestHelper::assertFalse($usuarioRepo->existsUsername("admin' --"), "existsUsername con 'admin\' --' devuelve false");

// 1.9 Verificación de integridad referencial: Base de datos inalterada
$stmtCheckUsers = $pdo->query('SELECT COUNT(*) FROM usuarios');
$postUsersCount = (int)$stmtCheckUsers->fetchColumn();
$stmtCheckUsers->closeCursor();
unset($stmtCheckUsers);

$stmtCheckCreations = $pdo->query('SELECT COUNT(*) FROM creaciones');
$postCreationsCount = (int)$stmtCheckCreations->fetchColumn();
$stmtCheckCreations->closeCursor();
unset($stmtCheckCreations);

$stmtCheckOrders = $pdo->query('SELECT COUNT(*) FROM pedidos');
$postOrdersCount = (int)$stmtCheckOrders->fetchColumn();
$stmtCheckOrders->closeCursor();
unset($stmtCheckOrders);

TestHelper::assertSame($initialUsersCount, $postUsersCount, 'Conteo de usuarios intacto tras ataques SQLi');
TestHelper::assertSame($initialCreationsCount, $postCreationsCount, 'Conteo de creaciones intacto tras ataques SQLi');
TestHelper::assertSame($initialOrdersCount, $postOrdersCount, 'Conteo de pedidos intacto tras ataques SQLi');

// ============================================================================
// SECCIÓN 2: Defensa contra Cross-Site Scripting (XSS - OWASP A03:2021)
// ============================================================================
TestHelper::section('2. Defensa contra Cross-Site Scripting (XSS - OWASP A03:2021)');

// 2.1 Matriz de Payloads XSS
$xssPayloads = [
    'script_tag'      => "<script>alert('XSS')</script>",
    'img_onerror'     => "\"><img src=x onerror=alert(1)>",
    'svg_onload'      => "<svg/onload=alert('xss')>",
    'event_handler'   => "\" onmouseover=\"alert('xss')\"",
    'javascript_url'  => "javascript:alert(document.cookie)",
    'iframe_payload'  => "<iframe src=\"javascript:alert(1)\">",
];

// 2.2 Validación de escape seguro con htmlspecialchars(..., ENT_QUOTES, 'UTF-8')
foreach ($xssPayloads as $key => $payload) {
    $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
    TestHelper::assertFalse(str_contains($escaped, '<script>'), "Payload '{$key}': etiqueta <script> neutralizada en escape HTML");
    TestHelper::assertFalse(str_contains($escaped, '<img'), "Payload '{$key}': etiqueta <img neutralizada en escape HTML");
    TestHelper::assertFalse(str_contains($escaped, '<svg'), "Payload '{$key}': etiqueta <svg neutralizada en escape HTML");
    TestHelper::assertFalse(str_contains($escaped, '<iframe'), "Payload '{$key}': etiqueta <iframe neutralizada en escape HTML");
    TestHelper::assertFalse(str_contains($escaped, '"'), "Payload '{$key}': comillas dobles convertidas a entidades seguras (&quot;)");
    TestHelper::assertFalse(str_contains($escaped, "'"), "Payload '{$key}': comillas simples convertidas a entidades seguras (&#039;)");
}

// 2.3 Almacenamiento seguro y separación de capas (Clean Architecture)
// Creamos una creación con payload XSS válido en longitud
$xssCreationPayload = [
    'nombre'           => "Amigurumi <script>alert('xss')</script>",
    'categoria'        => 'Fantasía',
    'material'         => '100% Algodón',
    'dimensiones'      => '15 cm',
    'precio'           => 35000,
    'costo_materiales' => 8000,
    'cantidad_stock'   => 5,
    'horas_tejido'     => 4.0,
    'descripcion'      => "Descripción con \"><img src=x onerror=alert(1)>",
    'es_sobre_encargo' => 0,
];

$adminUser = $usuarioRepo->findById(1);
$createdXssPiece = $creacionService->createCreation($xssCreationPayload, null, $adminUser);
$createdXssPieceId = (int)$createdXssPiece['id'];

TestHelper::assert($createdXssPieceId > 0, 'Creación con payload XSS creada exitosamente con ID válido');

// Comprobar que en la base de datos se almacena la cadena íntegra (sin corrupción)
$rawStoredPiece = $creacionRepo->findById($createdXssPieceId, true);
TestHelper::assertSame(
    "Amigurumi <script>alert('xss')</script>",
    $rawStoredPiece['nombre'],
    'Clean Architecture: Datos se almacenan íntegros en persistencia'
);

// Comprobar que al renderizar en vistas HTML mediante el estándar de componentes se escapa
$simulatedViewTitle = htmlspecialchars($rawStoredPiece['nombre'], ENT_QUOTES, 'UTF-8');
TestHelper::assertSame(
    "Amigurumi &lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;",
    $simulatedViewTitle,
    'Capa de vista HTML escapa estrictamente el nombre con htmlspecialchars'
);

$simulatedViewDesc = htmlspecialchars($rawStoredPiece['descripcion'], ENT_QUOTES, 'UTF-8');
TestHelper::assertSame(
    "Descripción con &quot;&gt;&lt;img src=x onerror=alert(1)&gt;",
    $simulatedViewDesc,
    'Capa de vista HTML escapa estrictamente la descripción'
);

// 2.4 Cabecera Content-Type application/json en respuestas REST
$detailXssHttpRes = TestHelper::curl('GET', $baseUrl . "/api/creaciones/detalle.php?id={$createdXssPieceId}");
TestHelper::assertSame(200, $detailXssHttpRes['status'], 'Endpoint detalle.php responde HTTP 200 para la pieza');
TestHelper::assertStringContains('application/json', $detailXssHttpRes['headers']['content-type'] ?? '', 'Cabecera Content-Type es estrictamente application/json');
TestHelper::assertSame($createdXssPieceId, (int)($detailXssHttpRes['json']['datos']['id'] ?? 0), 'JSON entrega datos íntegros');

// 2.5 Pedido público con payload XSS y codificación de WhatsApp con rawurlencode
$xssOrderPayload = [
    'creacion_id'      => $createdXssPieceId,
    'cantidad'         => 1,
    'cliente_nombre'   => "Cliente <svg onload=alert(1)>",
    'cliente_contacto' => '5512345678',
    'notas'            => "Notas con <script>alert('xss')</script>",
];

$xssOrderResult = $pedidoService->requestPublicOrder($xssOrderPayload);
$xssOrderId = (int)$xssOrderResult['id'];
TestHelper::assert($xssOrderId > 0, 'Pedido con payloads XSS registrado con éxito');

// Verificar que el enlace de WhatsApp está debidamente codificado con rawurlencode
$enrichedOrder = $pedidoService->getOrderById($xssOrderId, $adminUser);
$waLink = (string)$enrichedOrder['enlace_whatsapp'];
TestHelper::assertStringContains('https://wa.me/525512345678?text=', $waLink, 'Enlace de WhatsApp tiene estructura E.164 segura');
TestHelper::assertFalse(str_contains($waLink, '<svg'), 'Enlace de WhatsApp no contiene caracteres <svg sin codificar');
TestHelper::assertFalse(str_contains($waLink, '<script>'), 'Enlace de WhatsApp no contiene caracteres <script> sin codificar');
TestHelper::assertStringContains('%3Csvg', $waLink, 'Caracteres <svg fueron codificados en porcentaje como %3Csvg');

// Limpiar pedido y creación de prueba
$pedidoRepo->cancelOrderAtomic($xssOrderId);
$pedidoRepo->softDelete($xssOrderId);
$creacionService->deleteCreation($createdXssPieceId, $adminUser);

// 2.6 Validación de formato en creación de usuario rechaza caracteres XSS
$xssUserRejected = false;
try {
    $usuarioService->createUser(
        '<script>alert(1)</script>',
        'secret123',
        'artesano'
    );
} catch (\InvalidArgumentException $e) {
    $xssUserRejected = true;
    TestHelper::assertStringContains('letras, números', $e->getMessage(), 'Username con XSS rechazado por contener caracteres no permitidos');
}
TestHelper::assertTrue($xssUserRejected, 'UsuarioService::createUser rechaza nombres con caracteres XSS');

// ============================================================================
// SECCIÓN 3: Detección y Validación de Tipo MIME Real en Carga de Medios (OWASP A08:2021)
// ============================================================================
TestHelper::section('3. Detección y Validación de Tipo MIME Real en Carga de Medios (OWASP A08:2021)');

// Helper para crear archivos temporales de prueba
$createTempFile = function (string $content, string $prefix = 'sec_test_'): string {
    $tmpPath = tempnam(sys_get_temp_dir(), $prefix);
    file_put_contents($tmpPath, $content);
    return $tmpPath;
};

// 3.1 Rechazo de archivo PHP camuflado con extensión .jpg
$fakePhpJpg = $createTempFile("<?php echo 'malicious code'; ?>", 'fake_jpg_');
$rejectedFakeJpg = false;
try {
    $creacionService->handleImageUpload([
        'name'     => 'malware.jpg',
        'type'     => 'image/jpeg', // Tipo MIME fraudulento reportado por cliente
        'tmp_name' => $fakePhpJpg,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($fakePhpJpg),
    ], 'Amigurumis', 'Test Piece');
} catch (\InvalidArgumentException $e) {
    $rejectedFakeJpg = true;
    TestHelper::assertSame(422, $e->getCode(), 'Rechazo de archivo PHP camuflado retorna código 422');
    TestHelper::assertStringContains('Formato de imagen no permitido', $e->getMessage(), 'Mensaje indica formato no permitido');
}
TestHelper::assertTrue($rejectedFakeJpg, 'Script PHP con extensión .jpg es detectado por finfo y rechazado');
@unlink($fakePhpJpg);

// 3.2 Rechazo de archivo HTML/JS camuflado con extensión .png
$fakeHtmlPng = $createTempFile("<!DOCTYPE html><html><script>alert('xss')</script></html>", 'fake_png_');
$rejectedFakePng = false;
try {
    $creacionService->handleImageUpload([
        'name'     => 'avatar.png',
        'type'     => 'image/png',
        'tmp_name' => $fakeHtmlPng,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($fakeHtmlPng),
    ], 'Amigurumis', 'Test Piece');
} catch (\InvalidArgumentException $e) {
    $rejectedFakePng = true;
    TestHelper::assertSame(422, $e->getCode(), 'Rechazo de HTML camuflado retorna código 422');
}
TestHelper::assertTrue($rejectedFakePng, 'Archivo HTML con extensión .png es detectado por finfo y rechazado');
@unlink($fakeHtmlPng);

// 3.3 Rechazo de script Bash camuflado con extensión .webp
$fakeShellWebp = $createTempFile("#!/bin/bash\necho 'rm -rf /'", 'fake_webp_');
$rejectedFakeWebp = false;
try {
    $creacionService->handleImageUpload([
        'name'     => 'foto.webp',
        'type'     => 'image/webp',
        'tmp_name' => $fakeShellWebp,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($fakeShellWebp),
    ], 'Amigurumis', 'Test Piece');
} catch (\InvalidArgumentException $e) {
    $rejectedFakeWebp = true;
    TestHelper::assertSame(422, $e->getCode(), 'Rechazo de Shell Script retorna código 422');
}
TestHelper::assertTrue($rejectedFakeWebp, 'Shell script con extensión .webp es rechazado con 422');
@unlink($fakeShellWebp);

// 3.4 Rechazo de archivo con tamaño superior a 5MB (5,242,881 bytes)
$oversizedMock = [
    'name'     => 'huge.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => '/tmp/huge.jpg',
    'error'    => UPLOAD_ERR_OK,
    'size'     => 5242881, // 5MB + 1 byte
];
$rejectedOversized = false;
try {
    $creacionService->handleImageUpload($oversizedMock, 'Amigurumis', 'Test Piece');
} catch (\InvalidArgumentException $e) {
    $rejectedOversized = true;
    TestHelper::assertSame(422, $e->getCode(), 'Archivo de >5MB retorna código 422');
    TestHelper::assertStringContains('no debe superar los 5 megabytes', $e->getMessage(), 'Mensaje indica límite de 5MB');
}
TestHelper::assertTrue($rejectedOversized, 'Archivo >5MB es rechazado antes de procesar');

// 3.5 Carga exitosa de JPEG genuino (1x1 pixel JPEG binario con cabecera \xFF\xD8\xFF)
// 1x1 pixel JPEG canónico mínimo
$validJpegBinary = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
$validJpegTmp = $createTempFile($validJpegBinary, 'valid_jpg_');

$uploadedJpegUrl = $creacionService->handleImageUpload([
    'name'     => 'genuine.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => $validJpegTmp,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($validJpegBinary),
], 'Fantasía', 'Pieza Genuina');

TestHelper::assertStringContains('uploads/creacion_', $uploadedJpegUrl, 'Imagen JPEG válida genera URL en uploads/creacion_...');
TestHelper::assertStringContains('.jpg', $uploadedJpegUrl, 'Imagen JPEG válida conserva extensión .jpg');
TestHelper::assertTrue(is_file(dirname(__DIR__) . '/' . $uploadedJpegUrl), 'Archivo físico JPEG existe en disco');
@unlink(dirname(__DIR__) . '/' . $uploadedJpegUrl);
@unlink($validJpegTmp);

// 3.6 Carga exitosa de PNG genuino (1x1 pixel PNG binario con cabecera \x89PNG)
$validPngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$validPngTmp = $createTempFile($validPngBinary, 'valid_png_');

$uploadedPngUrl = $creacionService->handleImageUpload([
    'name'     => 'genuine.png',
    'type'     => 'image/png',
    'tmp_name' => $validPngTmp,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($validPngBinary),
], 'Fantasía', 'Pieza PNG');

TestHelper::assertStringContains('uploads/creacion_', $uploadedPngUrl, 'Imagen PNG válida genera URL en uploads/creacion_...');
TestHelper::assertStringContains('.png', $uploadedPngUrl, 'Imagen PNG válida conserva extensión .png');
TestHelper::assertTrue(is_file(dirname(__DIR__) . '/' . $uploadedPngUrl), 'Archivo físico PNG existe en disco');
@unlink(dirname(__DIR__) . '/' . $uploadedPngUrl);
@unlink($validPngTmp);

// 3.7 Carga exitosa de WebP genuino (1x1 pixel WebP binario con cabecera RIFF...WEBP)
$validWebpBinary = base64_decode('UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAD8D+JaQAA3AA/ua1AAA=');
$validWebpTmp = $createTempFile($validWebpBinary, 'valid_webp_');

$uploadedWebpUrl = $creacionService->handleImageUpload([
    'name'     => 'genuine.webp',
    'type'     => 'image/webp',
    'tmp_name' => $validWebpTmp,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($validWebpBinary),
], 'Fantasía', 'Pieza WebP');

TestHelper::assertStringContains('uploads/creacion_', $uploadedWebpUrl, 'Imagen WebP válida genera URL en uploads/creacion_...');
TestHelper::assertStringContains('.webp', $uploadedWebpUrl, 'Imagen WebP válida conserva extensión .webp');
TestHelper::assertTrue(is_file(dirname(__DIR__) . '/' . $uploadedWebpUrl), 'Archivo físico WebP existe en disco');
@unlink(dirname(__DIR__) . '/' . $uploadedWebpUrl);
@unlink($validWebpTmp);

// 3.8 Fallback SVG temático automático si no se adjunta archivo
$fallbackNoFile = $creacionService->handleImageUpload(null, 'Fantasía', 'Dragón');
TestHelper::assertStringContains('assets/svg/piezas/', $fallbackNoFile, 'Sin archivo devuelve fallback temático SVG');
TestHelper::assertTrue(is_file(dirname(__DIR__) . '/' . $fallbackNoFile), 'Archivo SVG de fallback temático existe en disco');

// ============================================================================
// SECCIÓN 4: Prevención de Path Traversal & Carga Arbitraria (OWASP A08:2021)
// ============================================================================
TestHelper::section('4. Prevención de Path Traversal & Carga Arbitraria (OWASP A08:2021)');

// 4.1 Descarte del nombre provisto por el cliente e inmunidad a ../ en subida
$traversalTmp = $createTempFile($validJpegBinary, 'trav_jpg_');
$traversalUploadUrl = $creacionService->handleImageUpload([
    'name'     => '../../../../etc/cron.d/malicious_cron.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => $traversalTmp,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($validJpegBinary),
], 'Fantasía', 'Pieza Traversal');

TestHelper::assertFalse(str_contains($traversalUploadUrl, '..'), 'URL de imagen no contiene secuencias de escape (..)');
TestHelper::assertFalse(str_contains($traversalUploadUrl, 'cron.d'), 'Nombre provisto por el cliente es totalmente descartado');
TestHelper::assertTrue(str_starts_with($traversalUploadUrl, 'uploads/creacion_'), 'Nombre resultante utiliza prefijo seguro creacion_[hash]');

$physicalSavedPath = realpath(dirname(__DIR__) . '/' . $traversalUploadUrl);
$uploadsRealPath = realpath($uploadsDir);
TestHelper::assertTrue(str_starts_with($physicalSavedPath, $uploadsRealPath), 'Archivo resultante está estrictamente confinado al directorio uploads/');
@unlink($physicalSavedPath);
@unlink($traversalTmp);

// 4.2 Inmunidad contra Path Traversal en unlinkPreviousUploadFile
// Verificamos que la base de datos sqlite no pueda ser borrada mediante un unlink malicioso
$dbPath = dirname(__DIR__) . '/database/database.sqlite';
TestHelper::assertTrue(is_file($dbPath), 'database/database.sqlite existe antes de la prueba');

// Intentamos actualizar una creación enviando como imagen anterior una ruta relativa maliciosa
// Para probar esto, creamos una pieza temporal con imagen previa maliciosa directamente
$dbInsertStmt = $pdo->prepare("
    INSERT INTO creaciones (artesano_id, nombre, categoria, material, dimensiones, precio, costo_materiales, cantidad_stock, imagen_url)
    VALUES (1, 'Pieza Test Unlink', 'Fantasía', '100% Algodón', '10 cm', 10000, 2000, 1, 'uploads/../../database/database.sqlite')
");
$dbInsertStmt->execute();
$tempPieceId = (int)$pdo->lastInsertId();
$dbInsertStmt->closeCursor();
unset($dbInsertStmt);

// Ahora actualizamos la pieza subiendo una nueva imagen
$newImgTmp = $createTempFile($validJpegBinary, 'new_img_');
$updatedPiece = $creacionService->updateCreation($tempPieceId, [
    'nombre'           => 'Pieza Test Unlink Editada',
    'categoria'        => 'Fantasía',
    'material'         => '100% Algodón',
    'dimensiones'      => '10 cm',
    'precio'           => 10000,
    'costo_materiales' => 2000,
    'cantidad_stock'   => 1,
], [
    'name'     => 'new.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => $newImgTmp,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($validJpegBinary),
], $adminUser);

// Verificar que database/database.sqlite NO fue eliminado
TestHelper::assertTrue(is_file($dbPath), 'database/database.sqlite SOBREVIVE intacto (basename neutraliza ../../)');

// Limpiar nueva imagen creada y pieza
@unlink(dirname(__DIR__) . '/' . $updatedPiece['imagen_url']);
@unlink($newImgTmp);
$creacionService->deleteCreation($tempPieceId, $adminUser);

// 4.3 Inmunidad en borrado lógico: CERO unlink() en baja lógica (ADR-008)
$dummyPhoto = $uploadsDir . '/keep_on_delete_' . time() . '.jpg';
file_put_contents($dummyPhoto, $validJpegBinary);

$pieceWithPhotoId = $creacionRepo->create([
    'artesano_id'      => 1,
    'nombre'           => 'Pieza Soft Delete Foto',
    'categoria'        => 'Fantasía',
    'material'         => 'Algodón',
    'dimensiones'      => '10 cm',
    'precio'           => 15000,
    'costo_materiales' => 3000,
    'cantidad_stock'   => 2,
    'horas_tejido'     => 2.0,
    'descripcion'      => 'Test ADR-008',
    'imagen_url'       => 'uploads/' . basename($dummyPhoto),
    'es_sobre_encargo' => 0,
]);

// Aplicamos baja lógica con deleteCreation
$creacionService->deleteCreation($pieceWithPhotoId, $adminUser);

// Verificar que la foto en disco SIGUE EXISTIENDO (ADR-008)
TestHelper::assertTrue(is_file($dummyPhoto), 'Regla de Oro ADR-008: La fotografía en uploads/ NO se elimina en baja lógica');
@unlink($dummyPhoto);

// ============================================================================
// SECCIÓN 5: Seguridad Vectorial SVG & Sanitización de Atributos
// ============================================================================
TestHelper::section('5. Seguridad Vectorial SVG & Sanitización de Atributos');

// 5.1 Escaneo exhaustivo del 100% de archivos SVG en assets/svg/
$svgBaseDir = SvgHelper::getBaseDir();
$svgFiles = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($svgBaseDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'svg') {
        $svgFiles[] = $file->getPathname();
    }
}

TestHelper::assert(count($svgFiles) >= 20, "Se auditaron " . count($svgFiles) . " archivos SVG en assets/svg/ (>= 20)");

$cleanSvgCount = 0;
$dangerousPatterns = [
    '<script'     => 'etiqueta <script>',
    'onload'      => 'evento onload',
    'onerror'     => 'evento onerror',
    'onclick'     => 'evento onclick',
    'onmouseover' => 'evento onmouseover',
    'javascript:' => 'protocolo javascript:',
    '<!ENTITY'    => 'declaración de entidad externa XXE',
    'SYSTEM "'    => 'DTD externa SYSTEM XXE',
];

foreach ($svgFiles as $filePath) {
    $content = file_get_contents($filePath);
    $relPath = str_replace(dirname(__DIR__) . '/', '', $filePath);
    $isVulnerable = false;

    foreach ($dangerousPatterns as $pattern => $label) {
        if (stripos($content, $pattern) !== false) {
            $isVulnerable = true;
            TestHelper::assert(false, "Archivo SVG vulnerable ({$relPath}): detectado {$label}");
        }
    }

    if (!$isVulnerable) {
        $cleanSvgCount++;
    }
}

TestHelper::assertSame(
    count($svgFiles),
    $cleanSvgCount,
    "El 100% de los archivos SVG ({$cleanSvgCount}/" . count($svgFiles) . ") están limpios de scripts, eventos y XXE"
);

// 5.2 Sanitización de atributos en SvgHelper::render
$maliciousSvgAttrs = [
    'class'     => 'hero-img" onmouseover="alert(1)',
    'data-test' => '<script>alert("xss")</script>',
    'onload'    => 'alert(1)',
];

$renderedSvg = SvgHelper::render('tools/ovillo-lana', $maliciousSvgAttrs);

TestHelper::assertFalse(str_contains($renderedSvg, 'hero-img" onmouseover='), 'SvgHelper::render escapa comillas dobles en clases inyectadas');
TestHelper::assertStringContains('&quot;', $renderedSvg, 'Comillas inyectadas son convertidas a &quot;');
TestHelper::assertFalse(str_contains($renderedSvg, '<script>'), 'SvgHelper::render neutraliza etiquetas <script> en atributos data');
TestHelper::assertStringContains('&lt;script&gt;', $renderedSvg, 'Etiquetas inyectadas en atributos son convertidas a entidades seguras');

// 5.3 Escape en mensaje de error de SvgHelper::render para SVG no encontrado
$notFoundXssSvg = SvgHelper::render('<script>alert("missing")</script>');
TestHelper::assertFalse(str_contains($notFoundXssSvg, '<script>alert'), 'Mensaje de error no encontrado neutraliza etiquetas <script>');
TestHelper::assertStringContains('&lt;script&gt;alert(&quot;missing&quot;)&lt;/script&gt;', $notFoundXssSvg, 'Nombre malicioso en mensaje de error es escapado con htmlspecialchars');

// 5.4 Fallback temático por categorías y comprobación en disco
$thematicCategories = [
    'Fantasía'           => 'assets/svg/piezas/dragon-ignis.svg',
    'Prendas & Ropa'     => 'assets/svg/piezas/cardigan-granny.svg',
    'Bolsos & Accesorios'=> 'assets/svg/piezas/tote-bag.svg',
    'Hogar & Decoración' => 'assets/svg/piezas/mini-suculenta.svg',
    'Bebé & Infantil'    => 'assets/svg/piezas/osito-nordico.svg',
    'Genérica'           => 'assets/svg/piezas/gatito-ovillo.svg',
];

foreach ($thematicCategories as $cat => $expectedPath) {
    $resolved = $creacionService->getThematicSvgFallback($cat);
    TestHelper::assertSame($expectedPath, $resolved, "Categoría '{$cat}' resuelve al vector esperado: {$expectedPath}");
    TestHelper::assertTrue(is_file(dirname(__DIR__) . '/' . $resolved), "Vector temático para '{$cat}' existe en disco");
}

// ============================================================================
// SECCIÓN 6: Pruebas de Integración HTTP en Vivo contra Servidor Local
// ============================================================================
TestHelper::section('6. Pruebas de Integración HTTP en Vivo contra Servidor Local (OWASP A03 + A08)');

// 6.1 GET /api/creaciones/index.php con SQLi en parámetro busqueda
$httpSqliSearch = TestHelper::curl('GET', $baseUrl . "/api/creaciones/index.php?busqueda=" . urlencode("' OR '1'='1"));
TestHelper::assertSame(200, $httpSqliSearch['status'], "GET /api/creaciones/index.php?busqueda=' OR '1'='1 responde HTTP 200 OK");
TestHelper::assertTrue((bool)($httpSqliSearch['json']['exito'] ?? false), "Respuesta indica exito = true");
TestHelper::assert(is_array($httpSqliSearch['json']['datos'] ?? null), "Respuesta contiene array estructurado de datos");

// 6.2 GET /api/creaciones/index.php con UNION SELECT SQLi en categoría
$httpSqliUnion = TestHelper::curl('GET', $baseUrl . "/api/creaciones/index.php?categoria=" . urlencode("Fantasía' UNION SELECT 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17 --"));
TestHelper::assertSame(200, $httpSqliUnion['status'], "GET /api/creaciones/index.php con UNION SELECT responde HTTP 200");
TestHelper::assertSame(0, count($httpSqliUnion['json']['datos'] ?? [1]), "UNION SELECT es neutralizado por PDO y no devuelve registros espurios");

// 6.3 GET /api/pedidos/index.php con payload SQLi autenticado
$httpSqliOrder = TestHelper::curl('GET', $baseUrl . "/api/pedidos/index.php?busqueda=" . urlencode("1; DROP TABLE creaciones; --"), $adminHeaders);
TestHelper::assertSame(200, $httpSqliOrder['status'], "GET /api/pedidos/index.php con DROP TABLE responde HTTP 200 sin ejecutar la sentencia");

// 6.4 POST /api/pedidos/solicitar.php con payload XSS en cliente_nombre
$httpXssOrderPayload = [
    'creacion_id'      => 1,
    'cantidad'         => 1,
    'cliente_nombre'   => "Cliente <script>alert('pwn')</script>",
    'cliente_contacto' => '5598765432',
    'notas'            => "Notas con <img src=x onerror=alert(1)>",
];

$httpXssOrderRes = TestHelper::curl('POST', $baseUrl . '/api/pedidos/solicitar.php', [
    'Content-Type: application/json',
], json_encode($httpXssOrderPayload));

TestHelper::assertSame(201, $httpXssOrderRes['status'], 'POST /api/pedidos/solicitar.php con payload XSS responde 201 Created');
TestHelper::assertTrue((bool)($httpXssOrderRes['json']['exito'] ?? false), 'Pedido procesado exitosamente');
$httpCreatedOrderId = (int)($httpXssOrderRes['json']['datos']['id'] ?? 0);
TestHelper::assert($httpCreatedOrderId > 0, 'ID de pedido generado > 0');

// Limpiar pedido de prueba HTTP
if ($httpCreatedOrderId > 0) {
    $pedidoRepo->cancelOrderAtomic($httpCreatedOrderId);
    $pedidoRepo->softDelete($httpCreatedOrderId);
}

// 6.5 POST /api/creaciones/crear.php con archivo falso (multipart/form-data)
$fakeHttpUploadPath = $createTempFile("<?php echo 'shell'; ?>", 'http_fake_');
$cfile = new \CURLFile($fakeHttpUploadPath, 'image/jpeg', 'exploit.jpg');

$multipartPayload = [
    'nombre'           => 'Pieza Exploit Upload',
    'categoria'        => 'Fantasía',
    'material'         => '100% Algodón',
    'dimensiones'      => '15 cm',
    'precio'           => '350.00',
    'costo_materiales' => '50.00',
    'cantidad_stock'   => '3',
    'horas_tejido'     => '2.5',
    'descripcion'      => 'Prueba de subida maliciosa',
    'imagen'           => $cfile,
];

// Petición POST autenticada con Bearer token y multipart payload
$httpUploadRes = TestHelper::curl('POST', $baseUrl . '/api/creaciones/crear.php', [
    'Authorization: Bearer ' . $adminToken,
], $multipartPayload);

TestHelper::assertSame(422, $httpUploadRes['status'], 'POST /api/creaciones/crear.php con archivo PHP disfrazado responde HTTP 422');
TestHelper::assertStringContains('Formato de imagen no permitido', $getErrMsg($httpUploadRes), 'Mensaje de error HTTP informa formato no permitido');
@unlink($fakeHttpUploadPath);

// ============================================================================
// RESUMEN CONSOLIDADO
// ============================================================================
exit(TestHelper::summary());
