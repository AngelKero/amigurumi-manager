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
      <div class="modal-header border-bottom py-3 align-items-center" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex align-items-center gap-2">
          <?= svg('branding/isologo-medallon-garantia', ['width' => 28, 'height' => 28]) ?>
          <h5 class="modal-title fw-bold m-0" id="modalNuevoPedidoTitle" style="font-size: 1.05rem;">
            Registrar Encargo Manual del Taller
          </h5>
        </div>
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
              <option value="1" data-price="450" data-name="Dragón Ignis" selected>Dragón Ignis — $450.00 MXN (Amigurumi, Stock: 4)</option>
              <option value="2" data-price="180" data-name="Mini Suculenta en Maceta">Mini Suculenta en Maceta — $180.00 MXN (Hogar, Stock: 12)</option>
              <option value="3" data-price="320" data-name="Ajolote Rosado Pastel">Ajolote Rosado Pastel — $320.00 MXN (Bajo encargo)</option>
              <option value="4" data-price="980" data-name="Cardigan Granny Squares">Cardigan Granny Squares — $980.00 MXN (Prenda, Stock: 2)</option>
              <option value="5" data-price="380" data-name="Tote Bag Boho Trapillo">Tote Bag Boho Trapillo — $380.00 MXN (Bolso, Stock: 6)</option>
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

          <!-- Estado Financiero / Cobro del Encargo -->
          <div class="mb-3">
            <label for="manualEstadoPago" class="form-label fw-bold small">Estado de Cobro / Anticipo (*)</label>
            <select class="form-select select-craft-pill" id="manualEstadoPago" required>
              <option value="Pendiente">Pendiente (Sin cobro previo)</option>
              <option value="Anticipo 50%" selected>Anticipo 50% (Anticipo para compra de hilazas recibido)</option>
              <option value="Liquidado">Liquidado (100% pagado por el cliente)</option>
            </select>
          </div>

          <!-- Notas y Especificaciones del Encargo -->
          <div class="mb-3">
            <label for="manualNotas" class="form-label fw-bold small">Notas / Especificaciones Especiales</label>
            <textarea class="form-control" id="manualNotas" rows="2" placeholder="Ej. Hilo color esmeralda, bordar iniciales 'VM', empaque para regalo..." maxlength="1000" style="border-radius: var(--craft-radius-sm);"></textarea>
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
