<?php
/**
 * View Controller: Gestión de Artesanos & Equipo del Taller
 * Entry point for team and user administration (Admin only)
 */
$pageTitle = 'Gestión de Artesanos & Equipo | Amigurumi Manager';
$pageDescription = 'Control y administración de usuarios, asignación de roles y autoría de piezas del taller artesanal.';
$activePage = 'usuarios';
$contentView = __DIR__ . '/views/pages/usuarios_content.php';
$modals = ['modal_crear_usuario'];

require __DIR__ . '/views/layouts/main.php';
