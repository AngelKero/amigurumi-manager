<?php
/**
 * Component: Background Decorations (Algodón Nórdico)
 * Ambient floating clouds with running stitch and resting yarn balls.
 * Uses: assets/svg/decorations/nube-ovillo.svg & assets/svg/decorations/nube-pespunte.svg
 */
?>
<div class="cloud-yarn-bg-decorations" aria-hidden="true">
  <!-- Nube 1: Superior Izquierda (Pespunte Artesanal) -->
  <div class="bg-float-item bg-cloud-1" style="top: 2%; left: -25px;">
    <?= svg('decorations/nube-pespunte') ?>
  </div>

  <!-- Nube 2: Superior Derecha (Nube con Ovillo y Hebra) -->
  <div class="bg-float-item bg-cloud-2" style="top: 6%; right: -25px;">
    <?= svg('decorations/nube-ovillo') ?>
  </div>

  <!-- Nube 3: Centro-Izquierda (Nube con Ovillo y Destellos) -->
  <div class="bg-float-item bg-cloud-3" style="top: 42%; left: -35px;">
    <?= svg('decorations/nube-ovillo') ?>
  </div>

  <!-- Nube 4: Centro-Derecha (Pespunte Artesanal) -->
  <div class="bg-float-item bg-cloud-4" style="top: 64%; right: -30px;">
    <?= svg('decorations/nube-pespunte') ?>
  </div>

  <!-- Nube 5: Inferior Izquierda (Pespunte Artesanal) -->
  <div class="bg-float-item bg-cloud-5" style="top: 84%; left: 8%;">
    <?= svg('decorations/nube-pespunte') ?>
  </div>
</div>
