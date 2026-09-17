<?php
/**
 * Test Suite: Subfase 5.1 - Documentación Diátaxis & Manuales Operativos
 * Feature 012 · Plan Maestro Fase 5 · Algodón Nórdico Design System
 *
 * Valida:
 * 1. Estructura de los 4 Cuadrantes Diátaxis (Tutorials, How-To, Reference, Explanation).
 * 2. Existencia y completitud técnica de manuales de taller y onboarding.
 * 3. Cobertura del catálogo consolidado de los 25 endpoints REST de la API.
 * 4. Diccionario de datos exhaustivo de las 5 tablas SQLite y restricciones.
 * 5. Documentación profunda de los 10 Invariantes Canónicos (R-01 a R-10).
 * 6. Integridad del índice maestro docs/README.md (cero huérfanos, H-013).
 * 7. Pruebas HTTP en vivo verificando fidelidad de los contratos documentados.
 *
 * Ejecución:
 *   php tests/test-subfase-5.1.php > logs/subfase-5.1-cli.log 2>&1
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ACCESO DENEGADO: Este script solo puede ejecutarse desde la terminal CLI.\n";
    exit(1);
}

require_once __DIR__ . '/TestHelper.php';

use Tests\TestHelper;

$root = dirname(__DIR__);

TestHelper::init('Subfase 5.1: Documentación Diátaxis & Manuales Operativos');

// =============================================================================
// 1. ESTRUCTURA DE LOS 4 CUADRANTES DIÁTAXIS
// =============================================================================
TestHelper::section('1. Estructura de los 4 Cuadrantes Diátaxis');

$diataxisFiles = [
    // Cuadrante 1: Tutoriales (Aprendizaje guiado)
    'tutorials/primera-creacion.md'               => 'Tutorial 1: Publicar primera creación',
    'tutorials/primer-encargo-whatsapp.md'        => 'Tutorial 2: Gestión de encargo y WhatsApp',
    
    // Cuadrante 2: Guías How-To (Resolución de problemas)
    'how-to/gestion-roles-y-claves.md'            => 'How-To 1: Roles RBAC y reseteo de claves',
    'how-to/cancelacion-y-restitucion-stock.md'   => 'How-To 2: Cancelación de pedidos y stock',
    'how-to/configurar-whatsapp-artesano.md'      => 'How-To 3: Configurar WhatsApp comercial E.164',
    
    // Cuadrante 3: Referencia Técnica
    'api/catalogo-completo-endpoints.md'          => 'Referencia: Catálogo de 25 endpoints REST',
    'database/diccionario-datos.md'               => 'Referencia: Diccionario de datos SQLite',
    
    // Cuadrante 4: Explicación y Arquitectura
    'explanation/clean-architecture-sin-framework.md' => 'Explicación: Clean Architecture y PSR-4',
    'explanation/invariantes-seguridad.md'            => 'Explicación: 10 Invariantes Canónicos R-01 a R-10',
];

foreach ($diataxisFiles as $relPath => $desc) {
    $fullPath = $root . '/docs/' . $relPath;
    TestHelper::assertTrue(is_file($fullPath), "Existe el archivo {$desc} ({$relPath})");
    if (is_file($fullPath)) {
        $size = filesize($fullPath);
        TestHelper::assert($size >= 1200, "{$relPath} contiene contenido técnico sustantivo ({$size} bytes >= 1200)");
    }
}

// =============================================================================
// 2. TUTORIALES: ORIENTACIÓN A LA EXPERIENCIA DEL ARTESANO Y COMPRADOR
// =============================================================================
TestHelper::section('2. Cuadrante Tutoriales: Calidad y Pasos Prácticos');

$tut1 = (string)@file_get_contents($root . '/docs/tutorials/primera-creacion.md');
TestHelper::assertStringContains('Algodón Nórdico', $tut1, 'Tutorial 1 menciona el sistema de diseño');
TestHelper::assertStringContains('costo_materiales', $tut1, 'Tutorial 1 explica el registro de costos');
TestHelper::assertStringContains('precio', $tut1, 'Tutorial 1 explica fijación de precio en centavos');
TestHelper::assertStringContains('horas_tejido', $tut1, 'Tutorial 1 guía sobre cálculo de horas de labor');
TestHelper::assertStringContains('es_sobre_encargo', $tut1, 'Tutorial 1 aborda piezas sobre encargo vs stock inmediato');
TestHelper::assert(!str_contains($tut1, 'TODO'), 'Tutorial 1 no contiene placeholders TODO');

$tut2 = (string)@file_get_contents($root . '/docs/tutorials/primer-encargo-whatsapp.md');
TestHelper::assertStringContains('WhatsApp', $tut2, 'Tutorial 2 incluye canal de WhatsApp');
TestHelper::assertStringContains('wa.me', $tut2, 'Tutorial 2 documenta generación de enlaces wa.me');
TestHelper::assertStringContains('precio_final', $tut2, 'Tutorial 2 explica el precio congelado del pedido');
TestHelper::assertStringContains('stepper', $tut2, 'Tutorial 2 documenta control de cantidad con stepper');
TestHelper::assert(!str_contains($tut2, 'TODO'), 'Tutorial 2 no contiene placeholders TODO');

// =============================================================================
// 3. GUÍAS HOW-TO: RESOLUCIÓN DE TAREAS CRÍTICAS DEL TALLER
// =============================================================================
TestHelper::section('3. Cuadrante How-To: Procedimientos Operativos');

$ht1 = (string)@file_get_contents($root . '/docs/how-to/gestion-roles-y-claves.md');
TestHelper::assertStringContains('admin', $ht1, 'How-To 1 cubre rol admin');
TestHelper::assertStringContains('artesano', $ht1, 'How-To 1 cubre rol artesano');
TestHelper::assertStringContains('asistente', $ht1, 'How-To 1 cubre rol asistente');
TestHelper::assertStringContains('ID #1', $ht1, 'How-To 1 enfatiza la salvaguarda del admin titular (R-05)');
TestHelper::assertStringContains('403', $ht1, 'How-To 1 documenta respuesta 403 ante bloqueo de ID #1');

$ht2 = (string)@file_get_contents($root . '/docs/how-to/cancelacion-y-restitucion-stock.md');
TestHelper::assertStringContains('Cancelado', $ht2, 'How-To 2 cubre estado Cancelado');
TestHelper::assertStringContains('BEGIN IMMEDIATE', $ht2, 'How-To 2 detalla transacción atómica (R-07)');
TestHelper::assertStringContains('cantidad_stock', $ht2, 'How-To 2 explica restitución a inventario físico');
TestHelper::assertStringContains('idempotente', $ht2, 'How-To 2 destaca la garantía de idempotencia');

$ht3 = (string)@file_get_contents($root . '/docs/how-to/configurar-whatsapp-artesano.md');
TestHelper::assertStringContains('E.164', $ht3, 'How-To 3 explica el estándar internacional E.164');
TestHelper::assertStringContains('+52', $ht3, 'How-To 3 documenta código de país México (+52)');
TestHelper::assertStringContains('10 dígitos', $ht3, 'How-To 3 valida longitud de números nacionales');

// =============================================================================
// 4. REFERENCIA TÉCNICA: CONTRATOS REST Y DICCIONARIO DDL
// =============================================================================
TestHelper::section('4. Cuadrante Referencia: Cobertura de 25 Endpoints y Esquema');

$refApi = (string)@file_get_contents($root . '/docs/api/catalogo-completo-endpoints.md');

// Validar que documenta los 25 controladores en api/
$expectedEndpoints = [
    '/api/auth/login.php',
    '/api/auth/logout.php',
    '/api/auth/me.php',
    '/api/auth/cambiar-password.php',
    '/api/creaciones/index.php',
    '/api/creaciones/detalle.php',
    '/api/creaciones/crear.php',
    '/api/creaciones/actualizar.php',
    '/api/creaciones/eliminar.php',
    '/api/creaciones/restaurar.php',
    '/api/creaciones/mias.php',
    '/api/creaciones/artesanos.php',
    '/api/creaciones/ajustar-stock.php',
    '/api/creaciones/toggle-encargo.php',
    '/api/pedidos/index.php',
    '/api/pedidos/crear.php',
    '/api/pedidos/solicitar.php',
    '/api/pedidos/cambiar-estado.php',
    '/api/pedidos/cancelar.php',
    '/api/usuarios/index.php',
    '/api/usuarios/crear.php',
    '/api/usuarios/actualizar.php',
    '/api/usuarios/cambiar-rol.php',
    '/api/usuarios/restablecer-password.php',
    '/api/usuarios/eliminar.php',
    '/api/usuarios/reactivar.php',
    '/api/usuarios/actualizar-whatsapp.php',
];

foreach ($expectedEndpoints as $ep) {
    TestHelper::assertStringContains($ep, $refApi, "Catálogo de referencia incluye endpoint {$ep}");
}

// Códigos de estado HTTP documentados
foreach (['200 OK', '201 Created', '204 No Content', '400 Bad Request', '401 Unauthorized', '403 Forbidden', '404 Not Found', '409 Conflict', '422 Unprocessable', '500 Internal'] as $httpCode) {
    TestHelper::assertStringContains($httpCode, $refApi, "Referencia documenta código {$httpCode}");
}

$refDb = (string)@file_get_contents($root . '/docs/database/diccionario-datos.md');
foreach (['usuarios', 'creaciones', 'pedidos', 'tokens_revocados', 'login_intentos'] as $table) {
    TestHelper::assertStringContains("Tabla: `{$table}`", $refDb, "Diccionario de datos detalla tabla {$table}");
}
TestHelper::assertStringContains('PRAGMA foreign_keys = ON', $refDb, 'Diccionario cita PRAGMA foreign_keys');
TestHelper::assertStringContains('PRAGMA busy_timeout = 5000', $refDb, 'Diccionario cita PRAGMA busy_timeout');
TestHelper::assertStringContains('INTEGER (Centavos)', $refDb, 'Diccionario estipula moneda en centavos (R-06)');

// =============================================================================
// 5. EXPLICACIÓN ARQUITECTÓNICA & 10 INVARIANTES
// =============================================================================
TestHelper::section('5. Cuadrante Explicación: Fundamentos & 10 Invariantes');

$expArch = (string)@file_get_contents($root . '/docs/explanation/clean-architecture-sin-framework.md');
TestHelper::assertStringContains('Clean Architecture', $expArch, 'Explicación documenta Clean Architecture');
TestHelper::assertStringContains('PSR-4', $expArch, 'Explicación documenta autoloader PSR-4 nativo');
TestHelper::assertStringContains('Zero Composer', $expArch, 'Explicación justifica ausencia de Composer en producción');
TestHelper::assertStringContains('Dependency Rule', $expArch, 'Explicación describe la regla de dependencias inward');

$expSec = (string)@file_get_contents($root . '/docs/explanation/invariantes-seguridad.md');
for ($i = 1; $i <= 10; $i++) {
    $code = sprintf('R-%02d', $i);
    TestHelper::assertStringContains($code, $expSec, "Explicación de seguridad detalla Invariante {$code}");
}

// =============================================================================
// 6. INTEGRIDAD DEL ÍNDICE MAESTRO (H-013)
// =============================================================================
TestHelper::section('6. Integridad del Índice Maestro docs/README.md');

$indexMaster = (string)@file_get_contents($root . '/docs/README.md');

foreach (array_keys($diataxisFiles) as $relPath) {
    TestHelper::assertStringContains(
        './' . $relPath,
        $indexMaster,
        "Índice maestro docs/README.md enlaza archivo Diátaxis './{$relPath}'"
    );
}

// =============================================================================
// 7. COMPROBACIÓN HTTP EN VIVO (FIDELIDAD DE CONTRATOS DOCUMENTADOS)
// =============================================================================
TestHelper::section('7. Verificación HTTP en Vivo de Contratos');

// 1. Catálogo público responde 200 OK
$catRes = TestHelper::curl('GET', 'http://localhost:8000/api/creaciones/index.php');
TestHelper::assertSame(200, $catRes['status'], 'GET /api/creaciones/index.php responde 200 OK');
TestHelper::assertTrue($catRes['json']['exito'] ?? false, 'Catálogo público devuelve sobre JSON con exito: true');

// 2. Ruta privada sin token responde 401 Unauthorized
$privRes = TestHelper::curl('GET', 'http://localhost:8000/api/auth/me.php');
TestHelper::assertSame(401, $privRes['status'], 'GET /api/auth/me.php sin token responde 401 Unauthorized');
TestHelper::assertFalse($privRes['json']['exito'] ?? true, 'Ruta privada denegada devuelve exito: false');

// 3. Preflight OPTIONS responde 204 No Content
$corsRes = TestHelper::curl('OPTIONS', 'http://localhost:8000/api/creaciones/crear.php');
TestHelper::assertSame(204, $corsRes['status'], 'OPTIONS preflight responde 204 No Content según contrato');

// 4. Página principal con CSP estricto
$homeRes = TestHelper::curl('GET', 'http://localhost:8000/index.php');
TestHelper::assertSame(200, $homeRes['status'], 'GET /index.php responde 200 OK');
TestHelper::assertStringContains('script-src', $homeRes['headers']['content-security-policy'] ?? '', 'CSP presente en HTML');

exit(TestHelper::summary());
