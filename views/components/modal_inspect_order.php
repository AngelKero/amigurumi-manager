<?php
/**
 * Component: Modal Inspect Order (Algodón Nórdico)
 * Technical order inspection modal for artisans.
 */
?>
<div class="modal fade" id="modalInspeccionarPedido" tabindex="-1" aria-labelledby="inspectModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3" style="background-color: var(--craft-surface-muted);">
        <h5 class="modal-title fw-bold" id="inspectModalTitle">
          <i class="bi bi-file-earmark-text text-primary me-2"></i>Detalle de Encargo: <span id="inspectOrderId" class="font-monospace text-primary">#1</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body p-4">
        <div class="table-responsive mb-3">
          <table class="table table-sm table-borderless align-middle mb-0">
            <tbody>
              <tr class="border-bottom">
                <td class="text-muted small py-2" style="width: 40%;">Cliente:</td>
                <td class="fw-bold py-2" id="inspectCliente">Mariana Gómez</td>
              </tr>
              <tr class="border-bottom">
                <td class="text-muted small py-2">Pieza Confeccionada:</td>
                <td class="fw-bold py-2" id="inspectProducto">Dragón Ignis</td>
              </tr>
              <tr class="border-bottom">
                <td class="text-muted small py-2">Cantidad:</td>
                <td class="fw-bold py-2" id="inspectCantidad">1 u.</td>
              </tr>
              <tr class="border-bottom">
                <td class="text-muted small py-2">Precio Final Acordado:</td>
                <td class="fw-bold py-2 font-monospace text-primary" id="inspectTotal">$450.00 MXN</td>
              </tr>
              <tr>
                <td class="text-muted small py-2">Fecha Estimada de Entrega:</td>
                <td class="fw-bold py-2 font-monospace" id="inspectFecha">2026-09-24</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-3 border rounded bg-white shadow-sm" style="border-radius: var(--craft-radius-sm);">
          <strong class="d-block small text-muted mb-1">
            <i class="bi bi-chat-quote text-primary me-1"></i>Notas y Personalizaciones del Cliente:
          </strong>
          <p class="mb-0 small text-dark" id="inspectNotas">
            Empaque para regalo con dedicatoria especial. Escamas en tono carmesí más oscuro.
          </p>
        </div>
      </div>

      <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
