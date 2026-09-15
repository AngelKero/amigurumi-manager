<?php
/**
 * Seed Combinatorio del Catálogo (Subfase 4.2 · Feature 005) — CLI Only
 * Algodón Nórdico Design System
 *
 * Registra en la base de datos todas las variantes posibles del catálogo
 * iterando el producto cartesiano: categoría × material × estado de stock ×
 * banda de precio × artesano = 5 × 3 × 3 × 3 × 2 = **270 creaciones**.
 *
 * - Lista la matriz completa de combinaciones antes de insertar.
 * - Inserta vía CreacionService::createCreation (validación + SVG temático por
 *   categoría R-09). Cero SQL directo fuera de repositorios.
 * - Es idempotente: omite los nombres ya existentes (existsCreationByName).
 *
 * Uso:
 *   php setup.php
 *   php scripts/seed-catalogo-pruebas.php
 */

declare(strict_types=1);

// Guardia de seguridad: bloqueo estricto de ejecución vía HTTP (como setup.php)
if (php_sapi_name() !== 'cli') {
    die('Acceso Denegado: el seed del catálogo solo puede ejecutarse desde la terminal CLI.');
}

require dirname(__DIR__) . '/app/autoload.php';

use App\Services\CreacionService;

// ------------------------------------------------------------------------------
// Estilo CLI
// ------------------------------------------------------------------------------
function seedMessage(string $message, string $type = 'info'): void {
    $colors = [
        'success' => "\033[32m",
        'error'   => "\033[31m",
        'warning' => "\033[33m",
        'info'    => "\033[36m",
        'bold'    => "\033[1m",
        'reset'   => "\033[0m",
    ];
    $prefix = match ($type) {
        'success' => "[+] ",
        'error'   => "[!] ",
        'warning' => "[-] ",
        default   => "[i] ",
    };
    echo ($colors[$type] ?? $colors['info']) . $prefix . $message . $colors['reset'] . PHP_EOL;
}

// ------------------------------------------------------------------------------
// Ejes de variantes del producto cartesiano (5 × 3 × 3 × 3 × 2 = 270)
// ------------------------------------------------------------------------------
$categorias = [
    'Amigurumis & Figuras',
    'Prendas & Ropa',
    'Bolsos & Accesorios',
    'Hogar & Decoración',
    'Bebé & Infantil',
];

$materiales = [
    '100% Algodón Mercerizado',
    'Hilo Chenille Terciopelo',
    'Trapillo de Algodón Reciclado',
];

$estados = [
    'en_stock'     => ['label' => 'En Stock',     'stock' => 5, 'encargo' => 0],
    'bajo_encargo' => ['label' => 'Bajo Encargo', 'stock' => 0, 'encargo' => 1],
    'agotado'      => ['label' => 'Agotado',      'stock' => 0, 'encargo' => 0],
];

$bandas = [
    'economica' => ['label' => 'Económica', 'precio' => 19900, 'costo' => 7950],
    'media'     => ['label' => 'Media',     'precio' => 42000, 'costo' => 16800],
    'premium'   => ['label' => 'Premium',   'precio' => 85000, 'costo' => 34000],
];

$artesanos = [
    ['id' => 1, 'username' => 'admin'],
    ['id' => 2, 'username' => 'artesana_ana'],
];

// ------------------------------------------------------------------------------
// Matriz completa de combinaciones
// ------------------------------------------------------------------------------
$combinaciones = [];
$index = 1;
foreach ($categorias as $categoria) {
    foreach ($materiales as $material) {
        foreach ($estados as $estadoKey => $estado) {
            foreach ($bandas as $bandaKey => $banda) {
                foreach ($artesanos as $artesano) {
                    $combinaciones[] = [
                        'index'     => $index,
                        'categoria' => $categoria,
                        'material'  => $material,
                        'estado'    => $estadoKey,
                        'estado_label' => $estado['label'],
                        'banda'     => $bandaKey,
                        'banda_label' => $banda['label'],
                        'artesano'  => $artesano,
                        'precio'    => $banda['precio'],
                        'costo'     => $banda['costo'],
                        'stock'     => $estado['stock'],
                        'encargo'   => $estado['encargo'],
                    ];
                    $index++;
                }
            }
        }
    }
}

$totalCombinaciones = count($combinaciones);

seedMessage('Calculando matriz de variantes del catálogo...', 'bold');
seedMessage(sprintf('Ejes: %d categorías × %d materiales × %d estados × %d bandas de precio × %d artesanos', count($categorias), count($materiales), count($estados), count($bandas), count($artesanos)), 'info');
seedMessage(sprintf('TOTAL DE COMBINACIONES POSIBLES: %d', $totalCombinaciones), 'bold');

seedMessage('Matriz de combinaciones (variante → categoría | material | estado | precio | artesano):', 'info');
foreach ($combinaciones as $comb) {
    $peso = number_format($comb['precio'] / 100, 2);
    printf(
        "  [%3d] %-24s | %-30s | %-12s | %-9s | $%s | @%s\n",
        $comb['index'],
        $comb['categoria'],
        $comb['material'],
        $comb['estado_label'],
        $comb['banda_label'],
        $peso,
        $comb['artesano']['username']
    );
}

// ------------------------------------------------------------------------------
// Siembra idempotente
// ------------------------------------------------------------------------------
seedMessage('Sembrando variantes vía CreacionService::createCreation (SVG temático por categoría, R-09)...', 'bold');

$service = new CreacionService();
$created = 0;
$skipped = 0;
$errors = 0;

$currentUser = ['id' => 1, 'rol' => 'admin'];

foreach ($combinaciones as $comb) {
    $nombre = sprintf(
        '%s — %s · %s · %s · @%s',
        $comb['categoria'],
        $comb['material'],
        $comb['estado_label'],
        $comb['banda_label'],
        $comb['artesano']['username']
    );

    if ($service->existsCreationByName($nombre)) {
        $skipped++;
        printf("  [%3d] Omitida (ya existe): %s\n", $comb['index'], $nombre);
        continue;
    }

    $data = [
        'nombre'           => $nombre,
        'categoria'        => $comb['categoria'],
        'material'         => $comb['material'],
        'dimensiones'      => '12.0 x 9.0 cm (variante de pruebas)',
        'precio'           => $comb['precio'],
        'costo_materiales' => $comb['costo'],
        'cantidad_stock'   => $comb['stock'],
        'horas_tejido'     => 3.5,
        'descripcion'      => sprintf(
            'Pieza de catálogo generada por seed-catalogo-pruebas.php para ejercitar filtros y paginación. Categoría %s, banda %s (%s).',
            $comb['categoria'],
            $comb['banda_label'],
            $comb['estado_label']
        ),
        'es_sobre_encargo'   => $comb['encargo'],
        'artesano_id'        => $comb['artesano']['id'],
    ];

    try {
        $service->createCreation($data, null, $currentUser);
        $created++;
        printf("  [%3d] Creada: %s\n", $comb['index'], $nombre);
    } catch (Throwable $e) {
        $errors++;
        seedMessage(sprintf('Error al insertar [%3d] %s: %s', $comb['index'], $nombre, $e->getMessage()), 'error');
    }
}

// ------------------------------------------------------------------------------
// Resumen final
// ------------------------------------------------------------------------------
$repo = new \App\Repositories\CreacionRepository();
$totalActivas = $repo->countCatalog(['activo' => true]);

seedMessage('Resumen de siembra:', 'bold');
seedMessage(sprintf('Combinaciones analizadas: %d', $totalCombinaciones), 'info');
seedMessage(sprintf('Creadas:        %d', $created), 'success');
seedMessage(sprintf('Omitidas (ya existían): %d', $skipped), 'warning');
if ($errors > 0) {
    seedMessage(sprintf('Errores:        %d', $errors), 'error');
}
seedMessage(sprintf('Total de creaciones activas en BD: %d', $totalActivas), 'success');
seedMessage('¡Siembra completada! Prueba filtros y paginación contra el catálogo reactivo.', 'success');