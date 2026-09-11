<?php
/**
 * View Controller: Registro y Edición de Amigurumis
 * Entry point for artisan creation management & margin simulator
 */
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEditing = $editId !== null && $editId > 0;

$pageTitle = $isEditing 
  ? 'Modificar Creación #' . $editId . ' | Panel del Artesano | Amigurumi Manager' 
  : 'Registrar Nueva Creación | Panel del Artesano | Amigurumi Manager';
$pageDescription = 'Alta y modificación de amigurumis, carga de fotografías y simulación de rentabilidad en tiempo real.';
$activePage = 'formulario';
$contentView = __DIR__ . '/views/pages/formulario_content.php';
$modals = [];

require __DIR__ . '/views/layouts/main.php';
