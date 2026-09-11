<?php
/**
 * View Controller: Inventario y Gestión de Creaciones
 * Entry point for artisan creations administrative CRUD table & stock control
 */
$pageTitle = 'Inventario & Gestión de Creaciones | Panel del Artesano | Crochet Manager';
$pageDescription = 'Panel de control de creaciones en crochet, inventario físico, costos de insumos, horas de labor y operaciones CRUD.';
$activePage = 'creaciones';
$contentView = __DIR__ . '/views/pages/creaciones_content.php';
$modals = ['modal_eliminar_creacion', 'modal_inspect_creacion'];

require __DIR__ . '/views/layouts/main.php';
