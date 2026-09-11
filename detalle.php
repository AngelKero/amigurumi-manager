<?php
/**
 * View Controller: Detalle de Creación
 * Entry point for product specification & public checkout
 */
$pageTitle = 'Dragón Ignis | Ficha Técnica y Encargo | Amigurumi Manager';
$pageDescription = 'Especificaciones técnicas de confección, materiales, dimensiones y solicitud de encargo del Dragón Ignis.';
$activePage = 'detalle';
$contentView = __DIR__ . '/views/pages/detalle_content.php';
$modals = ['modal_checkout', 'modal_eliminar_amigurumi'];

require __DIR__ . '/views/layouts/main.php';
