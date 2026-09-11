<?php
/**
 * Component: Footer (Algodón Nórdico)
 * Master artisan textile footer with workshop seals, navigation & craft guarantee
 */
?>
<footer class="footer-craft mt-auto">
  <div class="container-xl">
    <div class="row g-4 mb-4">
      
      <!-- Columna 1: Identidad del Taller & Micro-ERP -->
      <div class="col-12 col-lg-4">
        <div class="mb-3">
          <?= svg('branding/imagotipo-horizontal', ['height' => 44, 'style' => 'max-width: 100%; width: auto;']) ?>
        </div>
        <p class="text-muted small mb-3" style="line-height: 1.6;">
          Micro-ERP y catálogo textil diseñado exclusivamente para artesanas y tejedores de amigurumis. Controla el rendimiento de tus horas de labor, tus inversiones en hilazas y lanas, tus pedidos a la medida y tus existencias en tiempo real.
        </p>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="badge badge-textile-tag d-inline-flex align-items-center gap-1">
            <?= svg('branding/isologo-sello-taller', ['width' => 16, 'height' => 16]) ?>
            <span>Taller Artesanal Oficial</span>
          </span>
          <span class="badge bg-white text-muted font-monospace border px-2 py-1" style="border-radius: var(--craft-radius-pill); font-size: 0.72rem;">
            Hecho a Mano con Amor
          </span>
        </div>
      </div>

      <!-- Columna 2: Navegación del Taller y Herramientas -->
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">
          <i class="bi bi-compass"></i> Explorar
        </h6>
        <ul class="footer-links-list">
          <li><a href="index.php" class="footer-link"><i class="bi bi-chevron-right"></i> Catálogo Textil</a></li>
          <li><a href="index.php#productCardGrid" class="footer-link"><i class="bi bi-chevron-right"></i> Stock en Vivo</a></li>
          <li><a href="detalle.php?id=1" class="footer-link"><i class="bi bi-chevron-right"></i> Pieza Destacada</a></li>
          <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#checkoutModal"><i class="bi bi-chevron-right"></i> Encargo a Medida</a></li>
        </ul>
      </div>

      <!-- Columna 3: Gestión y Panel del Artesano -->
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">
          <i class="bi bi-tools"></i> Taller ERP
        </h6>
        <ul class="footer-links-list">
          <li><a href="amigurumis.php" class="footer-link"><i class="bi bi-chevron-right"></i> Inventario Piezas</a></li>
          <li><a href="formulario.php" class="footer-link"><i class="bi bi-chevron-right"></i> Nueva Creación</a></li>
          <li><a href="pedidos.php" class="footer-link"><i class="bi bi-chevron-right"></i> Gestión Pedidos</a></li>
          <li><a href="usuarios.php" class="footer-link"><i class="bi bi-chevron-right"></i> Equipo Taller</a></li>
        </ul>
      </div>

      <!-- Columna 4: Compromiso Textil y Garantía Artesanal -->
      <div class="col-12 col-lg-4">
        <div class="footer-guarantee-box guarantee-stitched">
          <div class="d-flex align-items-center gap-2 mb-2">
            <?= svg('branding/isologo-medallon-garantia', ['width' => 38, 'height' => 38, 'class' => 'flex-shrink-0']) ?>
            <div>
              <h6 class="fw-bold mb-0 text-dark" style="font-family: var(--craft-font-theme); font-size: 0.98rem;">Compromiso de Calidad Artesanal</h6>
              <small class="text-muted" style="font-size: 0.74rem;">100% Confección Artesanal Certificada</small>
            </div>
          </div>
          <div class="footer-guarantee-item">
            <i class="bi bi-check-circle-fill"></i>
            <span><strong>Fibras Hipoalergénicas:</strong> 100% hilaza mercerizada y chenille velvet lavables a mano.</span>
          </div>
          <div class="footer-guarantee-item">
            <i class="bi bi-check-circle-fill"></i>
            <span><strong>Seguridad Infantil:</strong> Ojos de seguridad certificados con traba interna antiasfixia.</span>
          </div>
          <div class="footer-guarantee-item mb-3">
            <i class="bi bi-check-circle-fill"></i>
            <span><strong>Confección Justa:</strong> Cada precio garantiza una retribución horaria ética a las artesanas.</span>
          </div>
          <a href="https://wa.me/5215500000000?text=Hola,%20quisiera%20consultar%20sobre%20un%20encargo%20especial%20de%20amigurumi" 
             target="_blank" 
             rel="noopener noreferrer" 
             class="btn btn-craft-outline btn-craft-outline-stitched btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-whatsapp text-success fs-6"></i>
            <span>Contacto de Encargos por WhatsApp</span>
          </a>
        </div>
      </div>

    </div>

    <!-- Separador Pespunte Hilvanado -->
    <hr class="divider-stitched my-4">

    <!-- Barra Inferior de Copyright y Resguardo -->
    <div class="footer-bottom-bar">
      <div>
        <span>&copy; <?= date('Y') ?> <strong>Amigurumi Manager</strong> &bull; Micro-ERP & Catálogo Textil. Todos los derechos reservados.</span>
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
