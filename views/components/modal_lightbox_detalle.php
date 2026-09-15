<?php
/**
 * Component: Modal Lightbox Detalle (Algodón Nórdico)
 * Vista ampliada de la fotografía de la creación con la misma cadena de
 * fallback R-09 que el catálogo (foto real -> SVG temático -> genérico).
 *
 * Se carga vía $modals en detalle.php para renderizar como hijo directo de
 * <body> (fuera del stacking context de <main>{position:relative;z-index:1}),
 * de modo que el backdrop de Bootstrap (z-index 1050) nunca lo cubra.
 */
?>
<div class="modal fade" id="detailLightboxModal" tabindex="-1" aria-labelledby="detailLightboxTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg modal-content-stitched" style="border-radius: var(--craft-radius);">
      <div class="modal-header border-bottom py-2 px-3" style="background-color: var(--craft-surface-muted);">
        <strong class="small text-dark d-inline-flex align-items-center gap-2" id="detailLightboxTitle">
          <i class="bi bi-arrows-fullscreen text-primary"></i><span id="detailLightboxTitleText">Fotografía de la creación</span>
        </strong>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar vista ampliada"></button>
      </div>
      <div class="modal-body p-3 text-center" style="background: #fff;">
        <img id="detailLightboxImage" src="assets/svg/piezas/ovillo-generico.svg" alt="Vista ampliada de la creación artesanal" class="detail-lightbox-img">
        <p id="detailLightboxCaption" class="text-muted small mb-0 mt-2"></p>
      </div>
      <div class="modal-footer border-top py-2 d-flex justify-content-between align-items-center">
        <span class="badge badge-textile-tag" id="detailLightboxSourceBadge"><i class="bi bi-card-image me-1"></i><span id="detailLightboxSourceLabel">Ilustración temática</span></span>
        <button type="button" class="btn btn-sm btn-craft-outline" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cerrar
        </button>
      </div>
    </div>
  </div>
</div>
