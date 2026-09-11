<?php
/**
 * Layout: Master Main Layout (Algodón Nórdico)
 * Clean Architecture View Layout
 * Parameters:
 *  - $pageTitle (string)
 *  - $pageDescription (string)
 *  - $activePage (string)
 *  - $contentView (string) - path to page content template
 *  - $modals (array) - list of modal components to include
 */
$pageTitle = $pageTitle ?? 'Amigurumi Manager | Micro-ERP & Catálogo Textil';
$pageDescription = $pageDescription ?? 'Sistema de gestión de catálogo, inventario físico, costos y pedidos para artesanos de amigurumi.';
$activePage = $activePage ?? 'catalogo';
$modals = $modals ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  
  <!-- Google Fonts: Outfit (Display) & Plus Jakarta Sans (Body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5.3 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- Master Stylesheet (Modular ITCSS Architecture) -->
  <link href="css/styles.css" rel="stylesheet">
</head>
<body>

  <!-- Atmospheric Vector Background Decorations -->
  <?php require __DIR__ . '/../components/background_decorations.php'; ?>

  <!-- Unified Artisan Navigation Bar -->
  <?php require __DIR__ . '/../components/navbar.php'; ?>

  <!-- Main View Container Slot -->
  <main class="py-4">
    <div class="container-xl">
      <?php 
      if (isset($contentView) && file_exists($contentView)) {
        require $contentView;
      }
      ?>
    </div>
  </main>

  <!-- Global Modal: Artisan Login -->
  <?php require __DIR__ . '/../components/modal_login.php'; ?>

  <!-- Dynamic Page-Specific Modals -->
  <?php 
  foreach ($modals as $modalComponent) {
    $modalPath = __DIR__ . '/../components/' . $modalComponent . '.php';
    if (file_exists($modalPath)) {
      require $modalPath;
    }
  }
  ?>

  <!-- Unified Footer -->
  <?php require __DIR__ . '/../components/footer.php'; ?>

  <!-- Bootstrap 5.3 Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Frontend Scripting: ES Modules Native Orchestrator -->
  <script type="module" src="js/main.js"></script>
</body>
</html>
