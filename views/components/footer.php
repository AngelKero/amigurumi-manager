<?php
/**
 * Component: Footer (Algodón Nórdico)
 * Master artisan textile footer with workshop seals, navigation & craft guarantee
 */
?>
<footer class="footer-craft mt-auto">
  <div class="container-xl">
    <div class="row g-4 mb-4">
      
      <!-- Columna 1: Identidad de la Plataforma & Micro-ERP -->
      <div class="col-12 col-lg-4">
        <div class="mb-3">
          <?= svg('branding/imagotipo-horizontal', ['height' => 44, 'style' => 'max-width: 100%; width: auto;']) ?>
        </div>
        <p class="text-muted small mb-3" style="line-height: 1.6;">
          Plataforma colaborativa y Micro-ERP textil para artesanas y creadores independientes en crochet. Conecta con clientes, exhibe tus creaciones, gestiona encargos personalizados y transparenta tus horas de labor con total autonomía.
        </p>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="badge badge-textile-tag d-inline-flex align-items-center gap-1">
            <?= svg('branding/isologo-sello-taller', ['width' => 16, 'height' => 16]) ?>
            <span>Plataforma Textil Colaborativa</span>
          </span>
          <span class="badge bg-white text-muted font-monospace border px-2 py-1" style="border-radius: var(--craft-radius-pill); font-size: 0.72rem;">
            Creadores Independientes
          </span>
        </div>
      </div>

      <!-- Columna 2: Navegación del Catálogo y Exploración -->
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">
          <i class="bi bi-compass"></i> Explorar
        </h6>
        <ul class="footer-links-list">
          <li><a href="index.php" class="footer-link"><i class="bi bi-chevron-right"></i> Catálogo Colectivo</a></li>
          <li><a href="index.php#productCardGrid" class="footer-link"><i class="bi bi-chevron-right"></i> Piezas en Stock</a></li>
          <li><a href="detalle.php?id=1" class="footer-link"><i class="bi bi-chevron-right"></i> Pieza Destacada</a></li>
          <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#checkoutModal"><i class="bi bi-chevron-right"></i> Encargo a Medida</a></li>
        </ul>
      </div>

      <!-- Columna 3: Gestión y Panel del Creador -->
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">
          <i class="bi bi-tools"></i> Gestión &amp; ERP
        </h6>
        <ul class="footer-links-list">
          <li><a href="creaciones.php" class="footer-link"><i class="bi bi-chevron-right"></i> Inventario de Piezas</a></li>
          <li><a href="formulario.php" class="footer-link"><i class="bi bi-chevron-right"></i> Publicar Creación</a></li>
          <li><a href="pedidos.php" class="footer-link"><i class="bi bi-chevron-right"></i> Control de Pedidos</a></li>
          <li><a href="usuarios.php" class="footer-link"><i class="bi bi-chevron-right"></i> Comunidad Creadores</a></li>
        </ul>
      </div>

      <!-- Columna 4: Compromiso y Calidad de la Plataforma -->
      <div class="col-12 col-lg-4">
        <div class="footer-guarantee-box guarantee-stitched">
          <div class="d-flex align-items-center gap-2 mb-2">
            <?= svg('branding/isologo-medallon-garantia', ['width' => 38, 'height' => 38, 'class' => 'flex-shrink-0']) ?>
            <div>
              <h6 class="fw-bold mb-0 text-dark" style="font-family: var(--craft-font-theme); font-size: 0.98rem;">Compromiso de la Plataforma</h6>
              <small class="text-muted" style="font-size: 0.74rem;">Transparencia y Respaldo para Creadores y Clientes</small>
            </div>
          </div>
          <div class="footer-guarantee-item">
            <i class="bi bi-check-circle-fill"></i>
            <span><strong>Fichas Transparentes:</strong> Cada artesano especifica con claridad dimensiones reales, fibras empleadas y cuidados de su pieza.</span>
          </div>
          <div class="footer-guarantee-item">
            <i class="bi bi-check-circle-fill"></i>
            <span><strong>Contacto Directo:</strong> Acuerdos personalizados y comunicación ágil directamente con cada creador independiente para coordinar pedidos y tiempos.</span>
          </div>
          <div class="footer-guarantee-item mb-3">
            <i class="bi bi-check-circle-fill"></i>
            <span><strong>Comercio Ético:</strong> Herramientas de cálculo para que los artesanos coticen de manera justa valorando sus horas de labor manual.</span>
          </div>
          <a href="https://wa.me/5215500000000?text=Hola,%20quisiera%20consultar%20sobre%20la%20plataforma%20de%20crochet" 
             target="_blank" 
             rel="noopener noreferrer" 
             class="btn btn-craft-outline btn-craft-outline-stitched btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-whatsapp text-success fs-6"></i>
            <span>Contacto y Soporte de la Plataforma</span>
          </a>
        </div>
      </div>

    </div>

    <!-- Separador Pespunte Hilvanado -->
    <hr class="divider-stitched my-4">

    <!-- Barra Inferior de Copyright y Resguardo -->
    <div class="footer-bottom-bar">
      <div>
        <span>&copy; <?= date('Y') ?> <strong>Crochet Manager</strong> &bull; Micro-ERP & Catálogo Textil. Todos los derechos reservados.</span>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="footer-trust-tag d-inline-flex align-items-center gap-1">
          <?= svg('branding/isotipo-ovillo-corazon', ['width' => 16, 'height' => 16]) ?>
          <span>Tejido punto a punto con amor artesanal</span>
        </span>
        <span class="badge bg-white text-muted font-monospace border px-2 py-1" style="border-radius: var(--craft-radius-pill); font-size: 0.72rem;">
          Micro-ERP Artesanal v2.7
        </span>
      </div>
    </div>

  </div>
</footer>
