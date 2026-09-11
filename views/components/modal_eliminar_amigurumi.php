<?php
/**
 * Component: Modal Eliminar Amigurumi
 * Algodón Nórdico Design System
 * Confirmación de eliminación con doble salvaguarda referencial y de archivos huérfanos.
 */
?>
<div class="modal fade" id="modalEliminarAmigurumi" tabindex="-1" aria-labelledby="modalEliminarTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3 bg-danger-subtle text-danger">
        <h5 class="modal-title fw-bold" id="modalEliminarTitle">
          <i class="bi bi-trash3-fill me-2"></i>Eliminar Creación del Catálogo
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body p-4">
        <p class="text-dark mb-3">
          ¿Estás seguro de que deseas eliminar permanentemente <strong id="deleteAmigurumiName" class="text-primary">Dragón Ignis</strong> del catálogo?
        </p>

        <!-- 1. Salvaguarda Referencial SQLite -->
        <div class="p-3 mb-3 border rounded" style="background-color: #fff8f8; border-color: rgba(220, 53, 69, 0.25) !important;">
          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-shield-lock-fill text-danger fs-5 mt-1"></i>
            <div>
              <strong class="d-block text-danger small">Protección Referencial (`ON DELETE RESTRICT`):</strong>
              <span class="text-muted small">
                Si esta pieza cuenta con encargos históricos registrados en la tabla <code>pedidos</code>, la base de datos SQLite bloqueará la eliminación para proteger el historial financiero y contable del taller.
              </span>
            </div>
          </div>
        </div>

        <!-- 2. Política de Archivos Huérfanos -->
        <div class="p-3 mb-3 border rounded" style="background-color: var(--craft-surface-muted);">
          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-file-earmark-x-fill text-primary fs-5 mt-1"></i>
            <div>
              <strong class="d-block text-dark small">Limpieza de Archivos en Servidor:</strong>
              <span class="text-muted small">
                Si no existen pedidos vinculados, el backend ejecutará <code>unlink()</code> en el servidor local eliminando el archivo fotográfico de <code>/uploads/</code> para mantener el almacenamiento limpio.
              </span>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Conservar Pieza</button>
        <button type="button" class="btn btn-danger btn-sm" id="btnConfirmDeleteAmigurumi" onclick="alert('En Fase 4 se conectará con el endpoint transaccional POST /api/eliminar.php verificando FK'); bootstrap.Modal.getInstance(document.getElementById('modalEliminarAmigurumi')).hide();">
          <i class="bi bi-trash me-1"></i> Confirmar Eliminación
        </button>
      </div>
    </div>
  </div>
</div>
