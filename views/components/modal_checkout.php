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
            Solicitud de Pedido &amp; Encargo Especial
          </h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="publicCheckoutForm" onsubmit="event.preventDefault(); alert('¡Pedido solicitado exitosamente! En Fase 4 se conectará con el backend /api/pedidos/solicitar.php'); bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();">
        <input type="hidden" id="checkoutUnitPrice" value="450.00">
        <input type="hidden" id="checkoutStockMax" value="4">

        <div class="modal-body p-4">
          
          <div class="p-3 mb-3 border rounded d-flex justify-content-between align-items-center" style="background-color: var(--craft-surface-muted);">
            <div>
              <strong class="d-block text-dark" id="modalProductName">Dragón Ignis</strong>
              <span class="text-muted small" id="modalUnitPriceDisplay">Precio Unitario: $450.00 MXN</span>
            </div>
            <span class="badge badge-stock-in" id="modalStockBadge">
              Stock: 4 disp.
            </span>
          </div>

          <!-- Lead Time Notice [QW-2] -->
          <div class="lead-time-notice mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="flex-shrink-0">
                <?= svg('branding/isotipo-ovillo-corazon', ['width' => 28, 'height' => 28]) ?>
              </div>
              <div class="small">
                <strong>Tiempo de Confección Artesanal:</strong> Las piezas bajo encargo o sin stock inmediato requieren de 5 a 7 días hábiles de tejido punto a punto.
              </div>
            </div>
          </div>

          <!-- Nombre del Cliente -->
          <div class="mb-3">
            <label for="clienteNombre" class="form-label fw-bold small">Nombre Completo (*)</label>
            <input type="text" class="form-control input-craft-pill" id="clienteNombre" placeholder="Ej. Mariana Gómez" value="Mariana Gómez" required>
          </div>

          <!-- Stepper Bounded by Available Stock [-] [ 1 ] [+] (CR-2) -->
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-bold small d-block">Cantidad (*)</label>
              <div class="qty-stepper">
                <button type="button" id="btnCheckoutDec" aria-label="Disminuir cantidad">-</button>
                <input type="text" id="inputCheckoutQty" value="1" readonly>
                <button type="button" id="btnCheckoutInc" aria-label="Aumentar cantidad">+</button>
              </div>
              <small class="text-muted d-block mt-1" id="checkoutStockNote" style="font-size: 0.72rem;">Máx: 4 unidades en stock</small>
            </div>

            <div class="col-6">
              <label for="fechaEntrega" class="form-label fw-bold small">Fecha Deseada</label>
              <input type="date" class="form-control input-craft-pill" id="fechaEntrega" value="2026-09-25">
            </div>
          </div>

          <!-- Notas de Personalización -->
          <div class="mb-3">
            <label for="notasPedido" class="form-label fw-bold small">Notas o Especificaciones Especiales</label>
            <textarea class="form-control" id="notasPedido" rows="2" style="border-radius: var(--craft-radius-sm);" placeholder="Ej. Empaque para regalo, combinación de colores...">Empaque especial para regalo con tarjeta personalizada.</textarea>
          </div>

          <!-- Resumen Total -->
          <div class="p-3 border rounded bg-white shadow-sm" style="border-radius: var(--craft-radius-sm);">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted fw-bold">Total a Pagar:</span>
              <span class="fs-4 fw-bold text-dark font-monospace" id="checkoutTotalDisplay">$450.00 MXN</span>
            </div>
            <div class="small text-muted mt-1">
              <i class="bi bi-shield-check text-success me-1"></i>El backend computa el precio oficial para prevenir manipulación.
            </div>
          </div>

        </div>

        <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-craft-primary btn-craft-stitched btn-sm">
            <i class="bi bi-check2-circle me-1"></i> Confirmar Pedido
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
