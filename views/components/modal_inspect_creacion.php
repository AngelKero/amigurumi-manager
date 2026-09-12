<?php
/**
 * Component: Modal Inspect Creación (Algodón Nórdico)
 * Detailed technical and financial inspection dialog for crochet creations.
 */
?>
<div class="modal fade" id="modalInspectCreacion" tabindex="-1" aria-labelledby="modalInspectCreacionTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-3" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex align-items-center gap-2">
          <span class="badge badge-textile-tag d-inline-flex align-items-center gap-1">
            <?= svg('branding/isologo-sello-taller', ['width' => 16, 'height' => 16]) ?>
            <span>Ficha Técnica de la Creación</span>
          </span>
          <span class="font-monospace text-muted small" id="inspectCreacionId">#1</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body p-4">
        <div class="row g-4">
          <!-- Columna Miniatura e Identidad -->
          <div class="col-12 col-md-5 text-center">
            <div class="p-3 bg-light rounded border mb-3 d-flex align-items-center justify-content-center" style="min-height: 220px; border-radius: var(--craft-radius-sm);">
              <div id="inspectCreacionImageContainer" class="w-100">
                <?= svg('creaciones/dragon-ignis', ['class' => 'img-fluid', 'style' => 'max-height: 180px;']) ?>
              </div>
            </div>
            
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
              <span class="badge badge-stock-in" id="inspectStockBadge">
                <i class="bi bi-check-circle-fill me-1"></i>En Stock: 4 u.
              </span>
              <span class="badge bg-light text-dark font-monospace border" id="inspectOnDemandBadge">
                Entrega Inmediata
              </span>
            </div>
            
            <small class="text-muted d-block font-monospace mb-2" id="inspectArtisanAuthor">
              Autoría: @admin (Artesano / Creador)
            </small>

            <div id="inspectPedidosInfo" class="mt-1">
              <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">
                <i class="bi bi-shield-lock-fill me-1"></i>1 encargo activo &bull; ON DELETE RESTRICT
              </span>
            </div>
          </div>

          <!-- Columna Especificaciones y Finanzas -->
          <div class="col-12 col-md-7">
            <h4 class="fw-bold font-theme-display text-dark mb-1" id="inspectCreacionTitle">Dragón Ignis</h4>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
              <span class="badge-textile-tag" id="inspectCategoryBadge">Amigurumis & Figuras</span>
              <span class="text-muted small">&bull;</span>
              <span class="card-product-dimension" id="inspectTamanoContainer">
                <i class="bi bi-rulers me-1"></i><span id="inspectTamano">18.5 cm (Alto)</span>
              </span>
            </div>

            <!-- Resumen Financiero del Creador -->
            <div class="p-3 rounded border mb-3" style="background-color: var(--craft-surface-muted);">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-cash-stack text-primary"></i>
                <span class="fw-bold small text-uppercase" style="letter-spacing: 0.04em;">Desglose Económico de Labor y Materiales</span>
              </div>
              
              <div class="row g-2 text-center">
                <div class="col-4">
                  <div class="bg-white p-2 rounded border">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Precio Venta</small>
                    <strong class="text-dark font-monospace" id="inspectPrecio">$450.00</strong>
                  </div>
                </div>
                <div class="col-4">
                  <div class="bg-white p-2 rounded border">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Costo Material</small>
                    <span class="text-danger font-monospace fw-bold" id="inspectCosto">$120.00</span>
                  </div>
                </div>
                <div class="col-4">
                  <div class="bg-white p-2 rounded border">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Margen Neto</small>
                    <span class="text-success font-monospace fw-bold" id="inspectMargen">$330.00</span>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top" style="font-size: 0.78rem;">
                <span class="text-muted">Labor Invertida: <strong id="inspectHoras" class="font-monospace text-dark">6.5 hrs</strong></span>
                <span class="badge-hourly-rate" id="inspectHourlyRate">
                  <i class="bi bi-clock-history"></i> Retorno: $50.77/hr
                </span>
              </div>
            </div>

            <!-- Materiales y Técnica -->
            <div class="mb-3">
              <span class="text-muted small fw-bold d-block mb-1">Composición Textil / Hilaza:</span>
              <div class="p-2 bg-white rounded border small" id="inspectMaterial">
                100% Algodón Mercerizado de calibre fino con relleno sintético siliconado hipoalergénico.
              </div>
            </div>

            <!-- Historia / Descripción de Confección -->
            <div>
              <span class="text-muted small fw-bold d-block mb-1">Descripción y Notas de Confección:</span>
              <p class="small text-muted mb-0" id="inspectDescripcion" style="line-height: 1.5;">
                Amigurumi de dragón fantástico tejido a crochet con escamas en relieve, alas articuladas y armazón no deformable.
              </p>
            </div>

          </div>
        </div>
      </div>

      <div class="modal-footer border-top py-2 d-flex justify-content-between">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cerrar Ficha
        </button>
        <div class="d-flex gap-2">
          <a href="#" class="btn btn-sm btn-craft-outline" id="inspectBtnViewDetail" target="_blank">
            <i class="bi bi-eye me-1"></i>Ver en Catálogo
          </a>
          <a href="#" class="btn btn-sm btn-craft-primary btn-craft-stitched" id="inspectBtnEdit">
            <i class="bi bi-pencil-square me-1"></i>Editar Creación
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
