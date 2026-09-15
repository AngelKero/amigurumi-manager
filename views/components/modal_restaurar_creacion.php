<?php
/**
 * Component: Modal Restaurar Creación
 * Algodón Nórdico Design System
 * Subfase 4.3 · Feature 006: revierte la baja lógica vía
 * POST /api/creaciones/restaurar.php (cableado en creaciones.js).
 */
?>
<div class="modal fade" id="modalRestaurarCreacion" tabindex="-1" aria-labelledby="modalRestaurarTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3 bg-success-subtle text-success">
        <h5 class="modal-title fw-bold" id="modalRestaurarTitle">
          <i class="bi bi-arrow-counterclockwise me-2"></i>Restaurar Creación al Catálogo
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body p-4">
        <p class="text-dark mb-3">
          ¿Deseas devolver <strong id="restoreCreacionName" class="text-primary">esta pieza</strong> al catálogo activo?
        </p>

        <div class="p-3 mb-0 border rounded" style="background-color: var(--craft-surface-muted);">
          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill text-primary fs-5 mt-1"></i>
            <div>
              <strong class="d-block text-dark small">Reactivación inmediata:</strong>
              <span class="text-muted small">
                La pieza volverá a ser visible en el catálogo con su fotografía, existencias y modalidad intactas
                (<code>activo = 1</code>, <code>eliminado_en = NULL</code>).
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success btn-sm" id="btnConfirmRestoreCreacion" data-id="">
          <i class="bi bi-arrow-counterclockwise me-1"></i> Confirmar Restauración
        </button>
      </div>
    </div>
  </div>
</div>
