<?php
/**
 * Component: Modal Checkout (Algodón Nórdico)
 * Public customer checkout modal with inventory-bounded quantity stepper.
 */
?>
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3 align-items-center" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex align-items-center gap-2">
          <?= svg('branding/isologo-medallon-garantia', ['width' => 32, 'height' => 32]) ?>
          <h5 class="modal-title fw-bold m-0" id="checkoutModalTitle" style="font-size: 1.05rem;">
            Solicitud de Pedido &amp; Encargo al Artesano
          </h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="publicCheckoutForm" novalidate data-creacion-id="">
        <input type="hidden" id="checkoutCreacionId" value="">
        <input type="hidden" id="checkoutUnitPrice" value="">
        <input type="hidden" id="checkoutStockMax" value="">
        <input type="hidden" id="checkoutEsSobreEncargo" value="0">

        <div id="checkoutFormFields">
        <div class="modal-body p-4">

          <div id="checkoutFeedback" class="alert alert-danger d-none py-2 small" role="alert" aria-live="assertive"></div>
          
          <div class="p-3 mb-3 border rounded d-flex justify-content-between align-items-center" style="background-color: var(--craft-surface-muted);">
            <div>
              <strong class="d-block text-dark" id="modalProductName">Pieza artesanal</strong>
              <span class="text-muted small" id="modalUnitPriceDisplay">Precio Unitario: —</span>
            </div>
            <span class="badge badge-stock-in" id="modalStockBadge">
              Stock: — disp.
            </span>
          </div>

          <!-- Coordinación con el Artesano -->
          <div class="lead-time-notice mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="flex-shrink-0">
                <?= svg('branding/isotipo-ovillo-corazon', ['width' => 28, 'height' => 28]) ?>
              </div>
              <div class="small">
                <strong>Coordinación con el Artesano:</strong> Los tiempos de confección, detalles de personalización y entrega son acordados directamente con el artesano creador mediante la plataforma.
              </div>
            </div>
          </div>

          <!-- Nombre del Cliente -->
          <div class="mb-3">
            <label for="clienteNombre" class="form-label fw-bold small">Nombre Completo (*)</label>
            <input type="text" class="form-control input-craft-pill" id="clienteNombre" placeholder="Ej. Mariana Gómez" value="" required minlength="2" maxlength="100">
          </div>

          <!-- Contacto del Cliente (WhatsApp / teléfono para coordinar) -->
          <div class="mb-3">
            <label for="clienteContacto" class="form-label fw-bold small">WhatsApp / Teléfono (*)</label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);"><i class="bi bi-whatsapp text-success"></i></span>
              <input type="text" class="form-control input-craft-pill" id="clienteContacto" placeholder="+52 55 1234 5678" value="" required minlength="3" maxlength="50" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
            </div>
            <div class="form-text text-muted" style="font-size: 0.7rem;">El artesano te contactará por este medio para acordar la entrega.</div>
          </div>

          <!-- Stepper Bounded by Available Stock [-] [ 1 ] [+] (CR-2) -->
          <div class="row g-3 mb-3">
            <div class="col-12">
              <label class="form-label fw-bold small d-block">Cantidad (*)</label>
              <div class="qty-stepper">
                <button type="button" id="btnCheckoutDec" aria-label="Disminuir cantidad">-</button>
                <input type="text" id="inputCheckoutQty" value="1" readonly>
                <button type="button" id="btnCheckoutInc" aria-label="Aumentar cantidad">+</button>
              </div>
              <small class="text-muted d-block mt-1" id="checkoutStockNote" style="font-size: 0.72rem;"></small>
            </div>
          </div>

          <!-- Notas de Personalización -->
          <div class="mb-3">
            <label for="notasPedido" class="form-label fw-bold small">Notas o Especificaciones Especiales</label>
            <textarea class="form-control" id="notasPedido" rows="2" style="border-radius: var(--craft-radius-sm);" placeholder="Ej. Empaque para regalo, combinación de colores..." maxlength="1000"></textarea>
          </div>

          <!-- Resumen Total (estimado: el precio oficial lo congela el servidor) -->
          <div class="p-3 border rounded bg-white shadow-sm" style="border-radius: var(--craft-radius-sm);">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted fw-bold">Total Estimado:</span>
              <span class="fs-4 fw-bold text-dark font-monospace" id="checkoutTotalDisplay">—</span>
            </div>
            <div class="small text-muted mt-1">
              <i class="bi bi-shield-check text-success me-1"></i>El backend computa el precio oficial para prevenir manipulación.
            </div>
          </div>

        </div>

        <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-craft-primary btn-craft-stitched btn-sm" id="btnConfirmCheckout">
            <i class="bi bi-check2-circle me-1"></i> Confirmar Pedido
          </button>
        </div>
        </div><!-- /#checkoutFormFields -->

        <!-- Vista de éxito (folio + precio congelado + WhatsApp servidor) -->
        <div id="checkoutSuccess" class="d-none">
          <div class="modal-body p-4 text-center">
            <div class="mb-3">
              <?= svg('branding/isologo-medallon-garantia', ['width' => 56, 'height' => 56]) ?>
            </div>
            <h5 class="fw-bold font-theme-display text-dark mb-1">¡Pedido registrado!</h5>
            <p class="text-muted small mb-1">Folio <strong class="font-monospace" id="checkoutSuccessFolio">#0</strong></p>
            <p class="text-muted small mb-3" id="checkoutSuccessMessage"></p>
            <div class="p-3 rounded border mb-3" style="background-color: var(--craft-surface-muted);">
              <span class="text-muted small d-block">Total acordado</span>
              <strong class="fs-4 font-monospace text-dark" id="checkoutSuccessTotal">—</strong>
            </div>
            <a href="#" id="checkoutWhatsAppBtn" target="_blank" rel="noopener" class="btn btn-success btn-craft-stitched d-none align-items-center justify-content-center gap-2 px-4">
              <i class="bi bi-whatsapp"></i><span>Coordinar por WhatsApp</span>
            </a>
            <p class="text-muted mt-2 mb-0" id="checkoutWhatsAppHint" style="font-size: 0.75rem;">El artesano te contactará para acordar la entrega.</p>
          </div>
          <div class="modal-footer border-top py-3 justify-content-center" style="background-color: var(--craft-surface-muted);">
            <button type="button" class="btn btn-craft-outline btn-sm px-4" data-bs-dismiss="modal">Seguir Explorando</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
