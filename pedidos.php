<?php
/**
 * View Controller: Control de Pedidos y Encargos
 * Entry point for order fulfillment dashboard & stock restitution
 */
$pageTitle = 'Control de Pedidos | Panel del Artesano | Amigurumi Manager';
$pageDescription = 'Dashboard integral de seguimiento de pedidos, entregas programadas y cancelaciones con restitución de inventario.';
$activePage = 'pedidos';
$contentView = __DIR__ . '/views/pages/pedidos_content.php';
$modals = ['modal_cancel_order', 'modal_inspect_order', 'modal_nuevo_pedido'];

require __DIR__ . '/views/layouts/main.php';
