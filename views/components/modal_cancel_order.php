<?php
/**
 * Component: Modal Cancel Order (Algodón Nórdico)
 * Cancellation dialog with clear physical inventory unit restitution notice [QW-2].
 */
?>
<div class="modal fade" id="modalCancelarPedido" tabindex="-1" aria-labelledby="modalCancelarTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3" style="background-color: var(--craft-surface-muted);">
        <h5 class="modal-title fw-bold text-danger" id="modalCancelarTitle">
          <i class="bi bi-exclamation-octagon-fill me-2"></i>Cancelar Encargo
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body p-4">
        <p class="text-dark mb-3">
          ¿Estás seguro de que deseas cancelar el encargo <strong id="cancelModalOrderId" class="font-monospace text-primary">#1</strong>?
        </p>

        <!-- Stock Restitution Assurance Notice [QW-2] -->
        <div class="p-3 mb-3 border rounded" style="background-color: var(--craft-secondary-subtle); border-color: rgba(82, 133, 124, 0.35) !important;">
          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-arrow-repeat text-success fs-5 mt-1"></i>
            <div>
              <strong class="d-block text-dark small">Restitución de Inventario Físico:</strong>
              <span class="text-muted small">
                Se reintegrarán automáticamente <strong id="cancelStockRestitutionUnits" class="text-success">+1 unidad(es)</strong> al stock físico de <em id="cancelStockRestitutionProduct">Dragón Ignis</em>.
              </span>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label for="motivoCancelacion" class="form-label small fw-bold">Motivo de Cancelación (Opcional)</label>
          <textarea class="form-control" id="motivoCancelacion" rows="2" style="border-radius: var(--craft-radius-sm);" placeholder="Ej. Solicitado por el cliente, falta de material..."></textarea>
        </div>
      </div>

      <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Regresar</button>
        <button type="button" class="btn btn-danger btn-sm" id="btnConfirmarCancelacionPedido">
          <i class="bi bi-x-circle me-1"></i> Confirmar Cancelación y Restituir Stock
        </button>
      </div>
    </div>
  </div>
</div>
