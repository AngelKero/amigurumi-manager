<?php
/**
 * Test Suite: Subfase 5.3 - Rendimiento Web, Core Web Vitals & Optimización de Carga
 * Feature 012 · Plan Maestro Fase 5 · Algodón Nórdico Design System
 *
 * Valida:
 * 1. Prevención de Cumulative Layout Shift (CLS = 0) mediante aspect-ratio y dimensiones explícitas.
 * 2. Carga diferida nativa (loading="lazy", decoding="async") y priorización LCP (fetchpriority="high").
 * 3. Asignación simétrica en renderizado dinámico DOM (catalog.js, creaciones.js, orders.js).
 * 4. Directivas de compresión (mod_deflate) y caché de navegador (mod_expires / mod_headers) en .htaccess.
 * 5. Presupuesto de peso ligero: cero dependencias externas o bundles pesados no declarados.
 * 6. Sanidad y latencia en canal HTTP en vivo (index, detalle, creaciones, pedidos y API JSON).
 *
 * Ejecución:
 *   php tests/test-subfase-5.3.php > logs/subfase-5.3-cli.log 2>&1
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

TestHelper::init('Subfase 5.3: Rendimiento Web, Core Web Vitals & Optimización de Carga (Feature 012)');

// =============================================================================
// 1. PREVENCIÓN DE CUMULATIVE LAYOUT SHIFT (CLS = 0) & ASPECT-RATIO
// =============================================================================
TestHelper::section('1. Prevención de CLS: Contenedores con Aspect-Ratio y Reglas CSS');

$cardsCss = (string)@file_get_contents($root . '/src/css/04-components/cards.css');
TestHelper::assertStringContains('aspect-ratio: 4 / 3;', $cardsCss, 'cards.css define aspect-ratio 4:3 en card-product-img-wrapper');
TestHelper::assertStringContains('.card-product-img-wrapper', $cardsCss, 'cards.css contiene selector .card-product-img-wrapper');
TestHelper::assertStringContains('.skeleton-img', $cardsCss, 'cards.css define selector .skeleton-img');
TestHelper::assertStringContains('aspect-ratio: 4 / 3;', $cardsCss, 'skeleton-img mantiene aspect-ratio 4:3 para evitar layout shift durante carga');

$detailCss = (string)@file_get_contents($root . '/src/css/04-components/detail.css');
TestHelper::assertStringContains('.product-photo-stitched-frame', $detailCss, 'detail.css define marco acolchado .product-photo-stitched-frame');
TestHelper::assertStringContains('.detail-photo-zoom-trigger', $detailCss, 'detail.css define trigger de zoom');
TestHelper::assertStringContains('aspect-ratio: 1 / 1;', $detailCss, 'detail.css define aspect-ratio 1:1 en zoom trigger');

$creacionesCss = (string)@file_get_contents($root . '/src/css/04-components/creaciones.css');
TestHelper::assertStringContains('.admin-card-photo-frame', $creacionesCss, 'creaciones.css define marco fotográfico administrativo');
TestHelper::assertStringContains('height: 160px;', $creacionesCss, 'creaciones.css define altura fija contenedora en admin-card-photo-frame');

$ordersCss = (string)@file_get_contents($root . '/src/css/04-components/orders.css');
TestHelper::assertStringContains('.order-card-photo-frame', $ordersCss, 'orders.css define marco fotográfico de pedidos');
TestHelper::assertStringContains('height: 135px;', $ordersCss, 'orders.css define altura fija contenedora en order-card-photo-frame');

// =============================================================================
// 2. DIMENSIONES EXPLÍCITAS EN PLANTILLAS Y VISTAS HTML/PHP
// =============================================================================
TestHelper::section('2. Dimensiones Explícitas (width / height) en Marcado Estático y Templates');

$productCardPhp = (string)@file_get_contents($root . '/views/components/product_card.php');
TestHelper::assertStringContains('width="400"', $productCardPhp, 'product_card.php define width="400" en img');
TestHelper::assertStringContains('height="300"', $productCardPhp, 'product_card.php define height="300" en img');
TestHelper::assertStringContains('loading="lazy"', $productCardPhp, 'product_card.php define loading="lazy"');
TestHelper::assertStringContains('decoding="async"', $productCardPhp, 'product_card.php define decoding="async"');

$catalogoContent = (string)@file_get_contents($root . '/views/pages/catalogo_content.php');
TestHelper::assertStringContains('width="560"', $catalogoContent, 'catalogo_content.php define width="560" en heroCraftedImg');
TestHelper::assertStringContains('height="380"', $catalogoContent, 'catalogo_content.php define height="380" en heroCraftedImg');
TestHelper::assertStringContains('fetchpriority="high"', $catalogoContent, 'catalogo_content.php prioriza imagen Hero con fetchpriority="high"');
TestHelper::assertStringContains('decoding="async"', $catalogoContent, 'catalogo_content.php define decoding="async" en heroCraftedImg');
TestHelper::assertStringContains('width="400"', $catalogoContent, 'catalogo_content.php define width="400" en template');
TestHelper::assertStringContains('height="300"', $catalogoContent, 'catalogo_content.php define height="300" en template');
TestHelper::assertStringContains('loading="lazy"', $catalogoContent, 'catalogo_content.php define loading="lazy" en template');

$creacionesContent = (string)@file_get_contents($root . '/views/pages/creaciones_content.php');
TestHelper::assertStringContains('width="300"', $creacionesContent, 'creaciones_content.php define width="300" en template');
TestHelper::assertStringContains('height="225"', $creacionesContent, 'creaciones_content.php define height="225" en template');
TestHelper::assertStringContains('loading="lazy"', $creacionesContent, 'creaciones_content.php define loading="lazy" en template');
TestHelper::assertStringContains('decoding="async"', $creacionesContent, 'creaciones_content.php define decoding="async" en template');

$pedidosContent = (string)@file_get_contents($root . '/views/pages/pedidos_content.php');
TestHelper::assertStringContains('width="120"', $pedidosContent, 'pedidos_content.php define width="120" en template');
TestHelper::assertStringContains('height="90"', $pedidosContent, 'pedidos_content.php define height="90" en template');
TestHelper::assertStringContains('loading="lazy"', $pedidosContent, 'pedidos_content.php define loading="lazy" en template');
TestHelper::assertStringContains('decoding="async"', $pedidosContent, 'pedidos_content.php define decoding="async" en template');

$detalleContent = (string)@file_get_contents($root . '/views/pages/detalle_content.php');
TestHelper::assertStringContains('width="600"', $detalleContent, 'detalle_content.php define width="600" en imagen principal');
TestHelper::assertStringContains('height="600"', $detalleContent, 'detalle_content.php define height="600" en imagen principal');
TestHelper::assertStringContains('fetchpriority="high"', $detalleContent, 'detalle_content.php define fetchpriority="high" en imagen LCP');
TestHelper::assertStringContains('decoding="async"', $detalleContent, 'detalle_content.php define decoding="async" en imagen principal');

// =============================================================================
// 3. OPTIMIZACIÓN EN MÓDULOS JAVASCRIPT CLIENTE (DOM APIS)
// =============================================================================
TestHelper::section('3. Asignación Dinámica de Dimensiones y Lazy/Async en Módulos ES');

$catalogJs = (string)@file_get_contents($root . '/src/js/modules/catalog.js');
TestHelper::assertStringContains("img.setAttribute('loading', 'lazy')", $catalogJs, 'catalog.js asigna loading="lazy" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('decoding', 'async')", $catalogJs, 'catalog.js asigna decoding="async" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('width', '400')", $catalogJs, 'catalog.js asigna width="400" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('height', '300')", $catalogJs, 'catalog.js asigna height="300" dinámicamente');

$creacionesJs = (string)@file_get_contents($root . '/src/js/modules/creaciones.js');
TestHelper::assertStringContains("img.setAttribute('loading', 'lazy')", $creacionesJs, 'creaciones.js asigna loading="lazy" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('decoding', 'async')", $creacionesJs, 'creaciones.js asigna decoding="async" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('width', '300')", $creacionesJs, 'creaciones.js asigna width="300" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('height', '225')", $creacionesJs, 'creaciones.js asigna height="225" dinámicamente');

$ordersJs = (string)@file_get_contents($root . '/src/js/modules/orders.js');
TestHelper::assertStringContains("img.setAttribute('loading', 'lazy')", $ordersJs, 'orders.js asigna loading="lazy" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('decoding', 'async')", $ordersJs, 'orders.js asigna decoding="async" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('width', '120')", $ordersJs, 'orders.js asigna width="120" dinámicamente');
TestHelper::assertStringContains("img.setAttribute('height', '90')", $ordersJs, 'orders.js asigna height="90" dinámicamente');

// =============================================================================
// 4. CONFIGURACIÓN DE CACHÉ Y COMPRESIÓN EN .HTACCESS
// =============================================================================
TestHelper::section('4. Directivas de Caché HTTP y Compresión Gzip/Deflate en .htaccess');

$htaccess = (string)@file_get_contents($root . '/.htaccess');

// mod_deflate
TestHelper::assertStringContains('<IfModule mod_deflate.c>', $htaccess, '.htaccess incluye bloque mod_deflate.c');
TestHelper::assertStringContains('text/html', $htaccess, 'mod_deflate comprime text/html');
TestHelper::assertStringContains('text/css', $htaccess, 'mod_deflate comprime text/css');
TestHelper::assertStringContains('application/javascript', $htaccess, 'mod_deflate comprime application/javascript');
TestHelper::assertStringContains('application/json', $htaccess, 'mod_deflate comprime application/json');
TestHelper::assertStringContains('image/svg+xml', $htaccess, 'mod_deflate comprime image/svg+xml');

// mod_expires
TestHelper::assertStringContains('<IfModule mod_expires.c>', $htaccess, '.htaccess incluye bloque mod_expires.c');
TestHelper::assertStringContains('ExpiresActive On', $htaccess, '.htaccess activa ExpiresActive On');
TestHelper::assertStringContains('ExpiresDefault', $htaccess, '.htaccess define ExpiresDefault');
TestHelper::assertStringContains('access plus 7 days', $htaccess, '.htaccess expira CSS y JS en 7 días');
TestHelper::assertStringContains('access plus 30 days', $htaccess, '.htaccess expira imágenes y SVG en 30 días');
TestHelper::assertStringContains('access plus 1 year', $htaccess, '.htaccess expira tipografías en 1 año');

// mod_headers
TestHelper::assertStringContains('<IfModule mod_headers.c>', $htaccess, '.htaccess incluye bloque mod_headers.c');
TestHelper::assertStringContains('Cache-Control', $htaccess, '.htaccess define cabecera Cache-Control');
TestHelper::assertStringContains('max-age=2592000', $htaccess, 'Cache-Control asigna 30 días (2592000s) para imágenes');
TestHelper::assertStringContains('max-age=604800', $htaccess, 'Cache-Control asigna 7 días (604800s) para CSS/JS');
TestHelper::assertStringContains('max-age=31536000', $htaccess, 'Cache-Control asigna 1 año (31536000s) para fuentes');

// =============================================================================
// 5. PRESUPUESTO DE PESO LIGERO Y DEPENDENCIAS
// =============================================================================
TestHelper::section('5. Presupuesto de Peso Ligero y Autonomía de Dependencias');

TestHelper::assertFalse(is_file($root . '/composer.json'), 'No existe composer.json (cero dependencias externas en backend)');
TestHelper::assertFalse(is_dir($root . '/vendor'), 'No existe directorio vendor/ (arquitectura limpia y ligera)');
TestHelper::assertFalse(is_dir($root . '/node_modules'), 'No existe directorio node_modules/ (Vanilla JS puro)');

// =============================================================================
// 6. VERIFICACIÓN HTTP EN VIVO Y LATENCIA DE RESPUESTA
// =============================================================================
TestHelper::section('6. Verificación HTTP en Vivo y Latencia de Carga');

$endpoints = [
    '/' => 'Catálogo público (index.php)',
    '/detalle.php?id=1' => 'Detalle de Creación (detalle.php)',
    '/creaciones.php' => 'Gestión de Creaciones (creaciones.php)',
    '/pedidos.php' => 'Gestión de Pedidos (pedidos.php)',
    '/usuarios.php' => 'Directorio de Usuarios (usuarios.php)',
    '/formulario.php' => 'Formulario de Alta/Edición (formulario.php)',
    '/api/creaciones/index.php' => 'API Catálogo Público JSON',
];

foreach ($endpoints as $uri => $label) {
    $response = TestHelper::curl('GET', 'http://localhost:8000' . $uri);
    $durationMs = $response['duration_ms'] ?? 0;

    TestHelper::assertTrue(
        $response['status'] === 200,
        "Endpoint HTTP {$uri} ({$label}) responde 200 OK (Status real: {$response['status']})"
    );

    TestHelper::assertTrue(
        $durationMs < 600,
        "Endpoint HTTP {$uri} responde en tiempo óptimo ({$durationMs} ms < 600 ms)"
    );

    if (str_ends_with($uri, '.php') && str_starts_with($uri, '/api/')) {
        TestHelper::assertStringContains('application/json', $response['headers']['content-type'] ?? '', "API {$uri} entrega Content-Type application/json");
    } else {
        TestHelper::assertStringContains('text/html', $response['headers']['content-type'] ?? '', "Página {$uri} entrega Content-Type text/html");
        TestHelper::assertTrue(
            isset($response['headers']['content-security-policy']),
            "Página {$uri} emite cabecera Content-Security-Policy estricta"
        );
    }
}

// Verificación de contenido HTML con atributos optimizados
$catalogRes = TestHelper::curl('GET', 'http://localhost:8000/');
$catalogHtml = $catalogRes['body'] ?? '';
TestHelper::assertStringContains('id="heroCraftedImg"', $catalogHtml, 'HTML de catálogo renderiza #heroCraftedImg');
TestHelper::assertStringContains('fetchpriority="high"', $catalogHtml, 'HTML de catálogo incluye fetchpriority="high" en hero');
TestHelper::assertStringContains('width="560"', $catalogHtml, 'HTML de catálogo incluye width="560" en hero');
TestHelper::assertStringContains('height="380"', $catalogHtml, 'HTML de catálogo incluye height="380" en hero');

$detailRes = TestHelper::curl('GET', 'http://localhost:8000/detalle.php?id=1');
$detailHtml = $detailRes['body'] ?? '';
TestHelper::assertStringContains('id="detailMainImage"', $detailHtml, 'HTML de detalle renderiza #detailMainImage');
TestHelper::assertStringContains('width="600"', $detailHtml, 'HTML de detalle incluye width="600"');
TestHelper::assertStringContains('height="600"', $detailHtml, 'HTML de detalle incluye height="600"');
TestHelper::assertStringContains('decoding="async"', $detailHtml, 'HTML de detalle incluye decoding="async"');

exit(TestHelper::summary());
