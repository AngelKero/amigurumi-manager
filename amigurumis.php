<?php
/**
 * View Controller: Inventario y Gestión de Amigurumis
 * Entry point for artisan amigurumis administrative CRUD table & stock control
 */
$pageTitle = 'Inventario & Gestión de Amigurumis | Panel del Artesano | Amigurumi Manager';
$pageDescription = 'Panel de control de amigurumis, inventario físico, costos de insumos, horas de labor y operaciones CRUD.';
$activePage = 'amigurumis';
$contentView = __DIR__ . '/views/pages/amigurumis_content.php';
$modals = ['modal_eliminar_amigurumi', 'modal_inspect_amigurumi'];

require __DIR__ . '/views/layouts/main.php';
