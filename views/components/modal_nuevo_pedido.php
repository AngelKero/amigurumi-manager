<?php
/**
 * Component: Modal Nuevo Encargo Manual
 * Algodón Nórdico Design System
 * Formulario para que el artesano registre pedidos directos recibidos por WhatsApp, feria o en persona.
 */
?>
<div class="modal fade" id="modalNuevoPedido" tabindex="-1" aria-labelledby="modalNuevoPedidoTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3" style="background-color: var(--craft-surface-muted);">
        <h5 class="modal-title fw-bold" id="modalNuevoPedidoTitle">
          <i class="bi bi-journal-plus me-2 text-primary"></i>Registrar Encargo Manual
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formNuevoPedido">
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            Registra un pedido recibido directamente fuera de la tienda web para reservar inventario y programar la confección:
          </p>

          <div id="nuevoPedidoAlert" class="alert alert-danger d-none py-2 small" role="alert"></div>

          <!-- Selección de Amigurumi -->
          <div class="mb-3">
            <label for="manualAmigurumiSelect" class="form-label fw-bold small">Pieza del Catálogo (*)</label>
            <select class="form-select select-craft-pill" id="manualAmigurumiSelect" required>
              <option value="1" data-price="450" data-name="Dragón Ignis" selected>Dragón Ignis — $450.00 MXN (Stock: 4)</option>
              <option value="2" data-price="180" data-name="Mini Suculenta Maceta">Mini Suculenta Maceta — $180.00 MXN (Stock: 12)</option>
              <option value="3" data-price="320" data-name="Ajolote Rosado Pastel">Ajolote Rosado Pastel — $320.00 MXN (Bajo encargo)</option>
              <option value="custom" data-price="500" data-name="Diseño Personalizado a Medida">✨ Encargo Especial / Personalizado — $500.00 MXN</option>
            </select>
          </div>

          <!-- Nombre y Contacto del Cliente -->
          <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
              <label for="manualClienteNombre" class="form-label fw-bold small">Nombre del Cliente (*)</label>
              <input type="text" class="form-control input-craft-pill" id="manualClienteNombre" placeholder="Ej. Sofía Morales" required minlength="2" maxlength="100">
            </div>
            <div class="col-12 col-md-6">
              <label for="manualClienteContacto" class="form-label fw-bold small">WhatsApp / Teléfono (*)</label>
              <div class="input-group">
                <span class="input-group-text bg-white" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);"><i class="bi bi-whatsapp text-success"></i></span>
                <input type="text" class="form-control input-craft-pill" id="manualClienteContacto" placeholder="+52 55 1234 5678" required style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
              </div>
            </div>
          </div>

          <!-- Cantidad y Fecha de Entrega -->
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label for="manualCantidad" class="form-label fw-bold small">Cantidad de Piezas (*)</label>
              <input type="number" min="1" max="50" class="form-control input-craft-pill" id="manualCantidad" value="1" required>
            </div>
            <div class="col-6">
              <label for="manualFechaEntrega" class="form-label fw-bold small">Fecha de Entrega (*)</label>
              <input type="date" class="form-control input-craft-pill" id="manualFechaEntrega" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
            </div>
          </div>

          <!-- Notas y Personalizaciones -->
          <div class="mb-3">
            <label for="manualNotas" class="form-label fw-bold small">Detalles y Especificaciones de Confección</label>
            <textarea class="form-control" id="manualNotas" rows="2" style="border-radius: var(--craft-radius-sm);" placeholder="Ej. Colores específicos, tarjeta de regalo, empaque especial..."></textarea>
          </div>

          <!-- Resumen Financiero -->
          <div class="p-3 border rounded bg-light" style="border-radius: var(--craft-radius-sm);">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted fw-bold small">Monto Total del Encargo:</span>
              <span class="fs-4 fw-bold text-dark font-monospace" id="manualTotalDisplay">$450.00 MXN</span>
            </div>
            <div class="small text-muted mt-1">
              <i class="bi bi-info-circle text-primary me-1"></i>Se registrará con estado inicial <strong>Pendiente</strong> en la base de datos.
            </div>
          </div>
        </div>

        <div class="modal-footer border-top py-3" style="background-color: var(--craft-surface-muted);">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-craft-primary btn-craft-stitched btn-sm">
            <i class="bi bi-check-circle me-1"></i> Confirmar y Agendar Encargo
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
