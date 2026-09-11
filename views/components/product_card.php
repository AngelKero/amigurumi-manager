<?php
/**
 * Component: Product Card (Algodón Nórdico)
 * Renders an artisan amigurumi catalog card with stitched borders and stock guards.
 * 
 * Expected $item:
 * [
 *   'id' => 1,
 *   'nombre' => 'Dragón Ignis',
 *   'categoria' => 'Fantasía',
 *   'material' => 'Algodón Mercerizado',
 *   'tamano_cm' => 18.5,
 *   'precio' => 450.00,
 *   'cantidad_stock' => 4,
 *   'descripcion' => '...',
 *   'imagen_url' => 'uploads/dragon.jpg', // or null
 *   'svg_illustration' => '...' // optional custom SVG
 * ]
 */
$isOutOfStock = ($item['cantidad_stock'] ?? 0) === 0;
$stockCount = (int)($item['cantidad_stock'] ?? 0);
$detailUrl = 'detalle.php?id=' . urlencode($item['id']);
?>
<article class="col product-grid-item" 
         data-name="<?= htmlspecialchars($item['nombre']) ?>" 
         data-material="<?= htmlspecialchars($item['material']) ?>" 
         data-category="<?= htmlspecialchars($item['categoria']) ?>" 
         data-stock="<?= $stockCount ?>" 
         data-price="<?= number_format($item['precio'], 2, '.', '') ?>">
  <div class="card card-product card-stitched h-100 <?= $isOutOfStock ? 'opacity-90' : '' ?>">
    <!-- Clickable Image / Preview -->
    <a href="<?= $detailUrl ?>" class="card-product-img-wrapper text-decoration-none">
      <div class="card-product-badge-float">
        <?php if ($isOutOfStock): ?>
          <span class="badge badge-stock-out shadow-sm">
            <i class="bi bi-dash-circle me-1"></i>Agotado (0 disp.)
          </span>
        <?php else: ?>
          <span class="badge badge-stock-in shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i>En Stock (<?= $stockCount ?> u.)
          </span>
        <?php endif; ?>
      </div>

      <?php if (!empty($item['imagen_url'])): ?>
        <img src="<?= htmlspecialchars($item['imagen_url']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" class="card-product-img" style="object-fit: cover;">
      <?php elseif (!empty($item['svg_illustration'])): ?>
        <?= $item['svg_illustration'] ?>
      <?php else: ?>
        <!-- Default Craft Fallback Illustration -->
        <svg viewBox="0 0 400 300" class="card-product-img" xmlns="http://www.w3.org/2000/svg">
          <rect width="400" height="300" fill="#f7eff3"/>
          <circle cx="200" cy="150" r="75" fill="#8e5b74" opacity="0.8"/>
          <circle cx="175" cy="140" r="8" fill="#ffffff"/>
          <circle cx="225" cy="140" r="8" fill="#ffffff"/>
          <path d="M185 165 Q200 175 215 165" stroke="#ffffff" stroke-width="4" fill="none" stroke-linecap="round"/>
          <text x="200" y="260" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif" font-weight="700" fill="#75475e" font-size="14">🧶 Tejido a Mano</text>
        </svg>
      <?php endif; ?>
    </a>

    <div class="card-body d-flex flex-column p-4" style="position: relative; z-index: 2;">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="badge-textile-tag">
          <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($item['categoria']) ?>
        </span>
        <small class="text-muted"><i class="bi bi-rulers me-1"></i><?= number_format($item['tamano_cm'], 1) ?> cm</small>
      </div>
      
      <!-- Clickable Title -->
      <h5 class="card-title fw-bold mb-2">
        <a href="<?= $detailUrl ?>" class="text-decoration-none text-dark hover-primary">
          <?= htmlspecialchars($item['nombre']) ?>
        </a>
      </h5>

      <p class="card-text text-muted small flex-grow-1 mb-2">
        <?= htmlspecialchars($item['descripcion']) ?>
      </p>

      <!-- Running-Stitch Seam Divider -->
      <div class="divider-stitched mb-3"></div>

      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="fs-4 fw-bold <?= $isOutOfStock ? 'text-muted' : 'text-dark' ?>">$<?= number_format($item['precio'], 2) ?></span>
          <small class="text-muted d-block" style="font-size: 0.75rem;">MXN / Unidad</small>
        </div>

        <?php if ($isOutOfStock): ?>
          <button class="btn btn-outline-secondary btn-sm" disabled title="Sin stock físico inmediato disponible">
            <i class="bi bi-slash-circle me-1"></i> Agotado
          </button>
        <?php else: ?>
          <button class="btn btn-craft-primary btn-craft-stitched btn-sm btn-buy-product" data-bs-toggle="modal" data-bs-target="#checkoutModal">
            <i class="bi bi-cart-plus me-1"></i> Comprar
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</article>
