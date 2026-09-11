<?php
/**
 * Component: Background Decorations (Algodón Nórdico)
 * Ambient floating clouds, yarn balls, and crochet hooks rendered via SvgHelper.
 */
?>
<div class="cloud-yarn-bg-decorations" aria-hidden="true">
  <!-- Nube Superior Izquierda (Pespunte Nórdico) -->
  <div class="bg-float-item bg-cloud-1" style="top: 6%; left: 2%;">
    <?= svg('decorations/nube-pespunte') ?>
  </div>

  <!-- Ovillo de Lana con Hebra Superior Derecha -->
  <div class="bg-float-item bg-yarn-1" style="top: 12%; right: 3%;">
    <?= svg('tools/ovillo-lana') ?>
  </div>

  <!-- Nube Grande con Ovillo Centro-Izquierda -->
  <div class="bg-float-item bg-cloud-2" style="top: 48%; left: 1%;">
    <?= svg('decorations/nube-ovillo') ?>
  </div>

  <!-- Madeja Textil Tradicional Centro-Derecha -->
  <div class="bg-float-item bg-yarn-2" style="top: 55%; right: 2%;">
    <?= svg('tools/madeja-textil') ?>
  </div>

  <!-- Nube Inferior Derecha con Pespunte -->
  <div class="bg-float-item bg-cloud-3" style="top: 80%; right: 4%;">
    <?= svg('decorations/nube-pespunte') ?>
  </div>

  <!-- Ganchillo de Crochet Artesanal con Mango Ergonómico -->
  <div class="bg-float-item bg-hook-1" style="top: 32%; right: 6%;">
    <?= svg('tools/ganchillo-crochet') ?>
  </div>
</div>
