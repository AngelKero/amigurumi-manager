<?php
/**
 * View Controller: Catálogo Textil
 * Entry point for catalog & showcase
 */
$pageTitle = 'Catálogo Textil & Inventario | Crochet Manager';
$pageDescription = 'Explora creaciones en crochet artesanales tejidas a mano con fibras naturales. Monitorea inventarios reales o encarga piezas exclusivas.';
$activePage = 'catalogo';
$contentView = __DIR__ . '/views/pages/catalogo_content.php';
$modals = ['modal_checkout', 'modal_eliminar_creacion'];

require __DIR__ . '/views/layouts/main.php';
