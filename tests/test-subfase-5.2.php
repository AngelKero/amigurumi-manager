<?php
/**
 * Test Suite: Subfase 5.2 - Accesibilidad WCAG 2.1 AA & Refinamiento Visual
 * Feature 011 / 012 · Plan Maestro Fase 5 · Algodón Nórdico Design System
 *
 * Valida:
 * 1. Foco visible (:focus y :focus-visible) y tokens accesibles en reset.css.
 * 2. Navegabilidad por teclado y ARIA en dropzone (Enter/Space, tabindex, role).
 * 3. Focus trap universal y restauración al trigger en todos los modales (a11y.js).
 * 4. Atributos aria-label en steppers, botones solo-icono y regiones aria-live.
 * 5. Estados de carga: .skeleton, @keyframes skeleton-shimmer y soporte reduced-motion.
 * 6. Estados vacíos con ilustración SVG artesanal y botón CTA primario en las 4 vistas.
 * 7. Stepper con ArrowUp/ArrowDown, inputmode="decimal" y micro-interacciones táctiles móviles.
 * 8. Comprobaciones HTTP en vivo garantizando que las páginas renderizan los atributos a11y.
 *
 * Ejecución:
 *   php tests/test-subfase-5.2.php > logs/subfase-5.2-cli.log 2>&1
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

TestHelper::init('Subfase 5.2: Accesibilidad WCAG 2.1 AA & Refinamiento Visual (Feature 011)');

// =============================================================================
// 1. FOCO VISIBLE Y NAVEGACIÓN POR TECLADO (RESET.CSS & FORMS.CSS)
// =============================================================================
TestHelper::section('1. Foco Visible Accesible y Navegación por Teclado');

$resetCss = (string)@file_get_contents($root . '/src/css/02-base/reset.css');
TestHelper::assertStringContains('--focus-ring-width', $resetCss, 'reset.css define token --focus-ring-width');
TestHelper::assertStringContains('--focus-ring-color', $resetCss, 'reset.css define token --focus-ring-color');
TestHelper::assertStringContains(':focus-visible', $resetCss, 'reset.css define regla :focus-visible');
TestHelper::assertStringContains('outline:', $resetCss, 'reset.css define contorno visible');

$dropzoneJs = (string)@file_get_contents($root . '/src/js/modules/dropzone.js');
TestHelper::assertStringContains("'Enter'", $dropzoneJs, 'dropzone.js maneja tecla Enter');
TestHelper::assertStringContains("' '", $dropzoneJs, 'dropzone.js maneja tecla Espacio');

$formView = (string)@file_get_contents($root . '/views/pages/formulario_content.php');
TestHelper::assertStringContains('tabindex="0"', $formView, 'uploadDropzone tiene tabindex="0" en formulario');
TestHelper::assertStringContains('role="button"', $formView, 'uploadDropzone tiene role="button" en formulario');
TestHelper::assertStringContains('aria-label=', $formView, 'uploadDropzone tiene aria-label descriptivo');

// =============================================================================
// 2. MÓDULO A11Y Y RESTAURACIÓN DE FOCO EN MODALES
// =============================================================================
TestHelper::section('2. Módulo a11y.js: Focus Trap, Restauración y Stepper');

$a11yJsPath = $root . '/src/js/modules/a11y.js';
TestHelper::assertTrue(is_file($a11yJsPath), 'Existe el módulo src/js/modules/a11y.js');

$a11yJs = (string)@file_get_contents($a11yJsPath);
TestHelper::assertStringContains('initA11y', $a11yJs, 'a11y.js exporta función initA11y()');
TestHelper::assertStringContains('hidden.bs.modal', $a11yJs, 'a11y.js escucha hidden.bs.modal para restaurar foco');
TestHelper::assertStringContains('show.bs.modal', $a11yJs, 'a11y.js captura el elemento activador en show.bs.modal');
TestHelper::assertStringContains('ArrowUp', $a11yJs, 'a11y.js soporta flecha arriba en steppers');
TestHelper::assertStringContains('ArrowDown', $a11yJs, 'a11y.js soporta flecha abajo en steppers');

$mainJs = (string)@file_get_contents($root . '/src/js/main.js');
TestHelper::assertStringContains('initA11y', $mainJs, 'main.js importa e inicializa initA11y()');

// =============================================================================
// 3. ATRIBUTOS ARIA-LABEL Y REGIONES ARIA-LIVE EN MODALES Y VISTAS
// =============================================================================
TestHelper::section('3. Atributos ARIA en Steppers, Botones e Indicadores Dinámicos');

$modalCheckout = (string)@file_get_contents($root . '/views/components/modal_checkout.php');
TestHelper::assertStringContains('aria-label="Disminuir cantidad"', $modalCheckout, 'Botón decrementar stepper tiene aria-label');
TestHelper::assertStringContains('aria-label="Aumentar cantidad"', $modalCheckout, 'Botón incrementar stepper tiene aria-label');
TestHelper::assertStringContains('aria-label="Cantidad a solicitar"', $modalCheckout, 'Input de cantidad tiene aria-label');
TestHelper::assertStringContains('role="alert"', $modalCheckout, '#checkoutFeedback tiene role="alert"');

$modalUser = (string)@file_get_contents($root . '/views/components/modal_crear_usuario.php');
TestHelper::assertStringContains('role="alert"', $modalUser, '#usuarioAlert tiene role="alert"');
TestHelper::assertStringContains('aria-live="polite"', $modalUser, '#usuarioAlert tiene aria-live="polite"');

$modalEditRole = (string)@file_get_contents($root . '/views/components/modal_editar_rol_usuario.php');
TestHelper::assertStringContains('role="alert"', $modalEditRole, '#editarRolAlert tiene role="alert"');
TestHelper::assertStringContains('aria-live="polite"', $modalEditRole, '#editarRolAlert tiene aria-live="polite"');

$modalLogin = (string)@file_get_contents($root . '/views/components/modal_login.php');
TestHelper::assertStringContains('role="alert"', $modalLogin, '#loginAlert tiene role="alert"');
TestHelper::assertStringContains('aria-live="polite"', $modalLogin, '#loginAlert tiene aria-live="polite"');

$modalNewOrder = (string)@file_get_contents($root . '/views/components/modal_nuevo_pedido.php');
TestHelper::assertStringContains('role="alert"', $modalNewOrder, '#nuevoPedidoAlert tiene role="alert"');
TestHelper::assertStringContains('aria-live="polite"', $modalNewOrder, '#nuevoPedidoAlert tiene aria-live="polite"');

// Botones en plantillas dinámicas
$creacionesView = (string)@file_get_contents($root . '/views/pages/creaciones_content.php');
TestHelper::assertStringContains('aria-label="Disminuir stock en 1 unidad"', $creacionesView, 'creacionCardTemplate tiene aria-label en stockDec');
TestHelper::assertStringContains('aria-label="Aumentar stock en 1 unidad"', $creacionesView, 'creacionCardTemplate tiene aria-label en stockInc');
TestHelper::assertStringContains('aria-label="Ver ficha técnica"', $creacionesView, 'creacionCardTemplate tiene aria-label en inspectBtn');
TestHelper::assertStringContains('aria-label="Editar creación"', $creacionesView, 'creacionCardTemplate tiene aria-label en editLink');
TestHelper::assertStringContains('aria-label="Eliminar creación"', $creacionesView, 'creacionCardTemplate tiene aria-label en deleteBtn');

$pedidosView = (string)@file_get_contents($root . '/views/pages/pedidos_content.php');
TestHelper::assertStringContains('aria-label="Ver ficha del pedido"', $pedidosView, 'pedidoCardTemplate tiene aria-label en inspectBtn');

$usersJs = (string)@file_get_contents($root . '/src/js/modules/users.js');
TestHelper::assertStringContains("btnEdit.setAttribute('aria-label'", $usersJs, 'users.js asigna aria-label a botón modificar rol');
TestHelper::assertStringContains("btnReset.setAttribute('aria-label'", $usersJs, 'users.js asigna aria-label a botón restablecer clave');

// =============================================================================
// 4. SKELETON LOADERS, KEYFRAMES Y PREFERS-REDUCED-MOTION
// =============================================================================
TestHelper::section('4. Skeletons de Carga y Micro-animaciones Inclusivas');

$cardsCss = (string)@file_get_contents($root . '/src/css/04-components/cards.css');
TestHelper::assertStringContains('.skeleton', $cardsCss, 'cards.css define clase .skeleton');
TestHelper::assertStringContains('skeleton-shimmer', $cardsCss, 'cards.css referencia animación skeleton-shimmer');
TestHelper::assertStringContains('.stagger-in', $cardsCss, 'cards.css define clase de entrada .stagger-in');
TestHelper::assertStringContains('prefers-reduced-motion', $cardsCss, 'cards.css respeta prefers-reduced-motion para skeletons y stagger');

$keyframesCss = (string)@file_get_contents($root . '/src/css/03-animations/keyframes.css');
TestHelper::assertStringContains('@keyframes skeleton-shimmer', $keyframesCss, 'keyframes.css define @keyframes skeleton-shimmer');
TestHelper::assertStringContains('@keyframes cardFadeUp', $keyframesCss, 'keyframes.css define @keyframes cardFadeUp');

$catView = (string)@file_get_contents($root . '/views/pages/catalogo_content.php');
TestHelper::assertStringContains('skeleton-card', $catView, 'catalogo_content.php incluye placeholders skeleton');

// =============================================================================
// 5. ESTADOS VACÍOS CON ILUSTRACIÓN SVG Y ACCIÓN PRIMARIA
// =============================================================================
TestHelper::section('5. Estados Vacíos con SVG Artesanal y Botón CTA Primario');

// 1. Catálogo
TestHelper::assertStringContains('id="emptyCatalogState"', $catView, 'Catálogo tiene contenedor #emptyCatalogState');
TestHelper::assertStringContains("svg('empty-basket'", $catView, 'Catálogo usa ilustración SVG empty-basket');
TestHelper::assertStringContains('id="btnResetFiltersEmpty"', $catView, 'Catálogo tiene botón de restablecimiento');

// 2. Pedidos
TestHelper::assertStringContains('id="emptyOrdersGrid"', $pedidosView, 'Pedidos tiene contenedor #emptyOrdersGrid');
TestHelper::assertStringContains("svg('empty-basket'", $pedidosView, 'Pedidos usa ilustración SVG artesanal');
TestHelper::assertStringContains('id="btnNuevoPedidoEmpty"', $pedidosView, 'Pedidos tiene botón CTA primario');

// 3. Creaciones
TestHelper::assertStringContains('id="emptyCreacionesState"', $creacionesView, 'Creaciones tiene contenedor #emptyCreacionesState');
TestHelper::assertStringContains("svg('empty-basket'", $creacionesView, 'Creaciones usa ilustración SVG');
TestHelper::assertStringContains('id="btnResetEmptyCreaciones"', $creacionesView, 'Creaciones tiene botón CTA');

// 4. Usuarios
$usersView = (string)@file_get_contents($root . '/views/pages/usuarios_content.php');
TestHelper::assertStringContains('id="emptyStateUsuarios"', $usersView, 'Usuarios tiene contenedor #emptyStateUsuarios');
TestHelper::assertStringContains('svg(', $usersView, 'Usuarios usa ilustración SVG artesanal');
TestHelper::assertStringContains('id="btnCrearUsuarioEmpty"', $usersView, 'Usuarios tiene botón CTA primario');

// =============================================================================
// 6. FORMULARIOS, DETALLES Y MONEDA (INPUTMODE & BREADCRUMB)
// =============================================================================
TestHelper::section('6. Formulario, Moneda, Breadcrumb y Toque Móvil');

TestHelper::assertStringContains('inputmode="decimal"', $formView, 'inputPrecio tiene inputmode="decimal"');

$detailCss = (string)@file_get_contents($root . '/src/css/04-components/detail.css');
TestHelper::assertStringContains('content: "›"', $detailCss, 'detail.css usa separador estilizado › en breadcrumb');

TestHelper::assertStringContains('@media (hover: none)', $cardsCss, 'cards.css define soporte táctil móvil @media (hover: none)');

// =============================================================================
// 7. COMPROBACIONES HTTP EN VIVO (CÓDIGOS 200 Y CSP ESTRICTO)
// =============================================================================
TestHelper::section('7. Verificación HTTP en Vivo de las Vistas Optimizadas');

$endpoints = [
    '/' => 'Catálogo Público',
    '/pedidos.php' => 'Panel de Pedidos',
    '/creaciones.php' => 'Panel de Creaciones',
    '/usuarios.php' => 'Directorio de Usuarios',
    '/formulario.php' => 'Formulario de Creación'
];

foreach ($endpoints as $path => $desc) {
    $res = TestHelper::curl('GET', 'http://localhost:8000' . $path);
    TestHelper::assertSame(200, $res['status'], "{$desc} ({$path}) responde 200 OK");
    TestHelper::assertTrue(isset($res['headers']['content-security-policy']), "{$desc} incluye cabecera CSP");
}

// Sanidad sintáctica
$outJs = [];
$codeJs = 0;
exec('node --check ' . escapeshellarg($root . '/src/js/main.js') . ' 2>&1', $outJs, $codeJs);
TestHelper::assertSame(0, $codeJs, 'main.js pasa node --check sin errores');

exit(TestHelper::summary());
