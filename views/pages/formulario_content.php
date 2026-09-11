<?php
/**
 * Page Content: Formulario de Alta y Edición de Creación
 * Algodón Nórdico Design System
 */
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEditing = $editId !== null && $editId > 0;

// Seed amigurumis data for pre-population in edit mode
$seedItems = [
  1 => [
    'nombre' => 'Dragón Ignis',
    'categoria' => 'Fantasía',
    'material' => '100% Algodón Mercerizado',
    'tamano_cm' => 18.5,
    'precio' => 450.00,
    'costo' => 120.00,
    'horas' => 6.5,
    'stock' => 4,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Amigurumi de dragón mítico tejido a mano con técnica crochet japonesa, escamas en relieve y fibra siliconada antialérgica.',
    'imagen_preview' => 'uploads/dragon.jpg'
  ],
  2 => [
    'nombre' => 'Mini Suculenta en Maceta',
    'categoria' => 'Plantas / Botánica',
    'material' => 'Algodón Rústico y Lana Acrílica',
    'tamano_cm' => 10.0,
    'precio' => 180.00,
    'costo' => 45.00,
    'horas' => 2.0,
    'stock' => 12,
    'es_sobre_encargo' => 0,
    'descripcion' => 'Pequeña maceta tejida con suculenta en relieve botánico. No requiere riego, ideal para escritorios.',
    'imagen_preview' => ''
  ],
  3 => [
    'nombre' => 'Ajolote Rosado Pastel',
    'categoria' => 'Animales / Fauna',
    'material' => 'Hilo Chenille Terciopelo',
    'tamano_cm' => 14.0,
    'precio' => 320.00,
    'costo' => 85.00,
    'horas' => 4.5,
    'stock' => 0,
    'es_sobre_encargo' => 1,
    'descripcion' => 'Tierno ajolote mexicano con textura aterciopelada ultra suave, branquias externas kawaii y ojos de seguridad.',
    'imagen_preview' => ''
  ]
];

$currentItem = ($isEditing && isset($seedItems[$editId])) ? $seedItems[$editId] : [
  'nombre' => $isEditing ? 'Creación #' . $editId : '',
  'categoria' => 'Fantasía',
  'material' => '',
  'tamano_cm' => '',
  'precio' => '',
  'costo' => '',
  'horas' => '',
  'stock' => '',
  'es_sobre_encargo' => 0,
  'descripcion' => '',
  'imagen_preview' => ''
];

// Sanitización de valores numéricos para prevenir TypeErrors en PHP 8
$valPrecio = (isset($currentItem['precio']) && is_numeric($currentItem['precio'])) ? (float)$currentItem['precio'] : null;
$valCosto = (isset($currentItem['costo']) && is_numeric($currentItem['costo'])) ? (float)$currentItem['costo'] : null;
$valHoras = (isset($currentItem['horas']) && is_numeric($currentItem['horas'])) ? (float)$currentItem['horas'] : null;
$valTamano = (isset($currentItem['tamano_cm']) && is_numeric($currentItem['tamano_cm'])) ? (float)$currentItem['tamano_cm'] : null;
$valStock = (isset($currentItem['stock']) && is_numeric($currentItem['stock'])) ? (int)$currentItem['stock'] : null;

// Parámetros calculados iniciales para el simulador financiero
$hasFinancialData = ($valPrecio !== null && $valPrecio > 0);
$calcPrecio = $valPrecio ?? 0.0;
$calcCosto = $valCosto ?? 0.0;
$calcHoras = $valHoras ?? 0.0;
$calcGanancia = $calcPrecio - $calcCosto;
$calcMargen = $calcPrecio > 0 ? ($calcGanancia / $calcPrecio) * 100 : 0.0;
$calcRetorno = $calcHoras > 0 ? $calcGanancia / $calcHoras : 0.0;
?>
<!-- 2-COLUMN RESPONSIVE SPLIT: FORMULARIO (col-12 col-xl-7) + SIMULADOR (col-12 col-xl-5) -->
<div class="row g-4 mb-5">

  <!-- FORMULARIO DE ALTA / EDICIÓN -->
  <div class="col-12 col-xl-7">
    <div class="card border-0 shadow-sm p-4 bg-white card-stitched" style="border-radius: var(--craft-radius);">
      
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom pb-3 mb-4">
        <div>
          <div class="d-inline-flex align-items-center gap-2 badge badge-textile-tag mb-2">
            <i class="bi bi-pencil-fill text-primary"></i>
            <span><?= $isEditing ? 'Modificación de Pieza #' . $editId : 'Taller de Confección' ?></span>
          </div>
          <h3 class="fw-bold font-theme-display text-dark mb-1" id="formTitle">
            <?= $isEditing ? 'Modificar Creación Artesanal' : 'Registrar Nueva Creación Artesanal' ?>
          </h3>
          <p class="text-muted small mb-0">
            <?= $isEditing ? 'Actualiza los parámetros físicos, costos o inventario de la pieza.' : 'Completa las especificaciones físicas y económicas de la nueva pieza.' ?>
          </p>
        </div>
        <div>
          <span class="badge badge-artisan-seal" id="modeBadge" style="font-size: 0.8rem;">
            <?= $isEditing ? 'Modo: Edición #' . $editId : 'Modo: Nuevo Registro' ?>
          </span>
        </div>
      </div>

      <form id="amigurumiForm" enctype="multipart/form-data" onsubmit="event.preventDefault(); alert('<?= $isEditing ? '¡Creación actualizada con éxito! En Fase 4 se conectará con POST /api/actualizar.php' : '¡Creación registrada con éxito! En Fase 4 se conectará con POST /api/crear.php' ?>');">
        <!-- ID Oculto para Modo Edición -->
        <input type="hidden" id="amigurumiId" value="<?= $isEditing ? $editId : '' ?>">

        <!-- Nombre del Amigurumi -->
        <div class="mb-3">
          <label for="inputNombre" class="form-label fw-bold small">Nombre de la Creación (*)</label>
          <input type="text" class="form-control form-control-lg input-craft-pill" id="inputNombre" placeholder="Ej. Dragón Ignis, Mini Cactus, etc." value="<?= htmlspecialchars($currentItem['nombre']) ?>" required minlength="2" maxlength="100">
          <div class="form-text text-muted">Entre 2 y 100 caracteres. Será el título principal visible en el catálogo.</div>
        </div>

        <!-- Categoría y Material Textil -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6">
            <label for="inputCategoria" class="form-label fw-bold small">Categoría (*)</label>
            <select class="form-select select-craft-pill" id="inputCategoria" required>
              <option value="Fantasía" <?= $currentItem['categoria'] === 'Fantasía' ? 'selected' : '' ?>>Fantasía</option>
              <option value="Plantas / Botánica" <?= $currentItem['categoria'] === 'Plantas / Botánica' ? 'selected' : '' ?>>Plantas / Botánica</option>
              <option value="Animales / Fauna" <?= $currentItem['categoria'] === 'Animales / Fauna' ? 'selected' : '' ?>>Animales / Fauna</option>
              <option value="Navidad / Temporada" <?= $currentItem['categoria'] === 'Navidad / Temporada' ? 'selected' : '' ?>>Navidad / Temporada</option>
              <option value="Llaveros / Accesorios" <?= $currentItem['categoria'] === 'Llaveros / Accesorios' ? 'selected' : '' ?>>Llaveros / Accesorios</option>
            </select>
          </div>
          <div class="col-12 col-sm-6">
            <label for="inputMaterial" class="form-label fw-bold small">Material Textil Principal (*)</label>
            <input type="text" class="form-control input-craft-pill" id="inputMaterial" placeholder="Ej. 100% Algodón Mercerizado" value="<?= htmlspecialchars($currentItem['material']) ?>" required minlength="3" maxlength="80">
          </div>
        </div>

        <!-- Tamaño y Cantidad en Stock -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6">
            <label for="inputTamano" class="form-label fw-bold small">Tamaño / Altura (*)</label>
            <div class="input-group input-group-craft">
              <input type="number" step="0.1" min="0.1" max="250.0" class="form-control" id="inputTamano" placeholder="15.0" value="<?= $valTamano !== null ? number_format($valTamano, 1, '.', '') : '' ?>" required>
              <span class="input-group-text">cm</span>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <label for="inputStock" class="form-label fw-bold small">Stock Físico Inicial (*)</label>
            <div class="input-group input-group-craft">
              <input type="number" min="0" max="10000" class="form-control" id="inputStock" placeholder="0" value="<?= $valStock !== null ? $valStock : '' ?>" required>
              <span class="input-group-text">unidades</span>
            </div>
          </div>
        </div>

        <!-- Distintivo de Confección Sobre Encargo -->
        <div class="mb-3 p-3 bg-white border rounded card-stitched" style="border-radius: var(--craft-radius-sm);">
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="inputEsSobreEncargo" <?= !empty($currentItem['es_sobre_encargo']) ? 'checked' : '' ?> style="cursor: pointer;">
            <label class="form-check-label fw-bold small text-dark" for="inputEsSobreEncargo" style="cursor: pointer;">
              <i class="bi bi-magic text-primary me-1"></i> Creación Exclusiva Bajo Encargo (Confección a Pedido)
            </label>
          </div>
          <div class="form-text text-muted small mt-1 ps-4">
            Al activar esta opción, la pieza se exhibirá con distintivo morado artesanal <em>"Bajo Encargo (5-7 d)"</em> en lugar de marcarse como <em>"Agotada"</em> cuando el inventario sea 0.
          </div>
        </div>

        <!-- Parámetros Económicos (Disparan el cálculo dinámico) -->
        <div class="p-3 mb-4 rounded border card-stitched" style="background-color: var(--craft-surface-muted);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-cash-stack text-primary fs-5"></i>
            <div>
              <strong class="d-block text-dark small text-uppercase" style="letter-spacing: 0.04em;">Parámetros Económicos y Tiempo de Labor</strong>
              <span class="text-muted small" style="font-size: 0.72rem;">Los valores ingresados calculan el margen y retorno en el simulador en tiempo real.</span>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-4">
              <label for="inputPrecio" class="form-label fw-bold small text-dark mb-1">Precio Venta (*)</label>
              <div class="input-group input-group-craft">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" min="1" max="9999999" class="form-control fw-bold" id="inputPrecio" placeholder="0.00" value="<?= $valPrecio !== null ? number_format($valPrecio, 2, '.', '') : '' ?>" required>
              </div>
              <div class="form-text text-muted" style="font-size: 0.7rem;">En pesos MXN</div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
              <label for="inputCosto" class="form-label fw-bold small text-dark mb-1">Costo Materiales (*)</label>
              <div class="input-group input-group-craft">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" min="0" max="9999999" class="form-control" id="inputCosto" placeholder="0.00" value="<?= $valCosto !== null ? number_format($valCosto, 2, '.', '') : '' ?>" required>
              </div>
              <div class="form-text text-muted" style="font-size: 0.7rem;">Hilazas, ojos, relleno</div>
            </div>
            <div class="col-12 col-sm-12 col-md-4">
              <label for="inputHoras" class="form-label fw-bold small text-dark mb-1">Horas de Tejido</label>
              <div class="input-group input-group-craft">
                <input type="number" step="0.25" min="0" max="500.0" class="form-control" id="inputHoras" placeholder="0.0" value="<?= $valHoras !== null ? number_format($valHoras, 2, '.', '') : '' ?>">
                <span class="input-group-text">hrs</span>
              </div>
              <div class="form-text text-muted" style="font-size: 0.7rem;">Labor manual invertida</div>
            </div>
          </div>
        </div>

        <!-- Descripción -->
        <div class="mb-4">
          <label for="inputDescripcion" class="form-label fw-bold small">Descripción y Cuidados de la Pieza</label>
          <textarea class="form-control" id="inputDescripcion" rows="3" style="border-radius: var(--craft-radius-sm);" placeholder="Detalla la técnica, tipo de ojos de seguridad, recomendaciones de lavado..."><?= htmlspecialchars($currentItem['descripcion']) ?></textarea>
        </div>

        <!-- CARGA DE FOTOGRAFÍA CON PREVIEW OCULTO -->
        <div class="mb-4">
          <label class="form-label fw-bold small d-block">Fotografía del Amigurumi (Formatos: JPG, PNG, WEBP &bull; Máx 5MB)</label>
          
          <div class="upload-dropzone" id="uploadDropzone" onclick="document.getElementById('inputImagen').click()">
            <?= svg('decorations/nube-ovillo', ['width' => 64, 'height' => 48, 'class' => 'mx-auto mb-2 d-block']) ?>
            <strong class="d-block text-dark">
              <?= $isEditing ? 'Haz clic para reemplazar la fotografía existente' : 'Haz clic para buscar o arrastra una imagen aquí' ?>
            </strong>
            <span class="text-muted small">La fotografía se optimizará y guardará en /uploads con nombre único sanitizado.</span>
          </div>
          
          <input type="file" id="inputImagen" class="d-none" accept="image/jpeg,image/png,image/webp">

          <!-- Elemento Preview Oculto / Visible en Edición -->
          <div id="imagePreviewContainer" class="<?= !empty($currentItem['imagen_preview']) ? '' : 'd-none' ?> mt-3 p-3 border rounded text-center bg-light">
            <div class="small text-muted mb-2 fw-semibold"><i class="bi bi-image me-1"></i>Fotografía Actual de la Creación:</div>
            <img id="imagePreview" src="<?= htmlspecialchars($currentItem['imagen_preview']) ?>" alt="Vista previa" class="img-fluid rounded shadow-sm border" style="max-height: 240px; object-fit: contain;">
            <div class="mt-2">
              <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveImage">
                <i class="bi bi-trash me-1"></i> Cambiar / Quitar Imagen
              </button>
            </div>
          </div>
        </div>

        <!-- BOTONES DE ACCIÓN -->
        <hr class="divider-stitched my-3">
        <div class="d-flex justify-content-between align-items-center pt-2">
          <?php if ($isEditing): ?>
            <a href="detalle.php?id=<?= $editId ?>" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Cancelar y Volver al Detalle
            </a>
          <?php else: ?>
            <a href="pedidos.php" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-box-seam me-1"></i> Ver Pedidos
            </a>
          <?php endif; ?>

          <div class="d-flex gap-2">
            <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
            <button type="submit" class="btn btn-craft-primary btn-craft-stitched px-4">
              <i class="bi bi-check-circle me-1"></i> <?= $isEditing ? 'Actualizar Creación' : 'Guardar Creación' ?>
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>

  <!-- SIMULADOR FINANCIERO STICKY CON FEEDBACK DUAL (col-12 col-xl-5) -->
  <div class="col-12 col-xl-5" id="simuladorMargen">
    <div class="card sticky-margin-card p-3 p-sm-4 card-stitched">
      
      <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning-emphasis" style="width: 42px; height: 42px; flex-shrink: 0;">
          <i class="bi bi-calculator-fill fs-5"></i>
        </div>
        <div class="min-w-0">
          <h5 class="fw-bold mb-0 font-theme-display text-dark" style="font-size: 1.15rem; line-height: 1.2;">Simulador de Márgenes</h5>
          <span class="text-muted small" style="font-size: 0.75rem;">Cálculo de viabilidad en vivo</span>
        </div>
      </div>

      <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.4;">
        Monitorea en tiempo real la salud financiera de tu amigurumi conforme modificas precio, costo de estambre y horas dedicadas:
      </p>

      <!-- Desglose Financiero Directo -->
      <div class="p-3 rounded mb-3 border card-stitched" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size: 0.85rem;">
          <span class="text-muted">Precio Venta</span>
          <span class="fw-bold text-dark font-monospace text-nowrap" id="calcDisplayPrecio">$<?= number_format($calcPrecio, 2) ?> MXN</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size: 0.85rem;">
          <span class="text-muted">Costo Materiales</span>
          <span class="text-danger font-monospace text-nowrap" id="calcDisplayCosto">- $<?= number_format($calcCosto, 2) ?> MXN</span>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2 mt-1">
          <span class="fw-bold small text-dark text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.03em;">Ganancia Bruta</span>
          <span class="fw-bold font-monospace text-nowrap fs-5 <?= $calcGanancia >= 0 ? 'text-success' : 'text-danger' ?>" id="calcDisplayGanancia">$<?= number_format($calcGanancia, 2) ?> MXN</span>
        </div>
      </div>

      <!-- FEEDBACK DUAL: MÉTRICAS CLAVE -->
      <!-- Métrica 1: Margen Porcentual -->
      <div class="p-3 mb-3 rounded border card-stitched bg-white" style="border-radius: var(--craft-radius-sm);">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.03em;">
            <i class="bi bi-percent me-1 text-primary"></i> Margen de Utilidad
          </span>
          <strong class="fs-4 text-dark font-monospace text-nowrap" id="calcDisplayMargen">
            <?= number_format($calcMargen, 1) ?>%
          </strong>
        </div>
        <?php if ($hasFinancialData): ?>
          <?php if ($calcMargen >= 60): ?>
            <div id="badgeMargenStatus" class="margin-feedback-pill bg-success-subtle text-success border border-success-subtle">
              <i class="bi bi-shield-check"></i> Margen Saludable (>60%)
            </div>
          <?php elseif ($calcMargen >= 35): ?>
            <div id="badgeMargenStatus" class="margin-feedback-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">
              <i class="bi bi-exclamation-circle"></i> Margen Moderado (35-60%)
            </div>
          <?php else: ?>
            <div id="badgeMargenStatus" class="margin-feedback-pill bg-danger-subtle text-danger border border-danger-subtle">
              <i class="bi bi-slash-circle"></i> Margen Crítico (<35%)
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div id="badgeMargenStatus" class="margin-feedback-pill bg-light text-muted border">
            <i class="bi bi-dash-circle"></i> Esperando precio y costo
          </div>
        <?php endif; ?>
      </div>

      <!-- Métrica 2: Retorno por Hora de Trabajo -->
      <div class="p-3 mb-3 rounded border card-stitched bg-white" style="border-radius: var(--craft-radius-sm);">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <span class="small text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem; letter-spacing: 0.03em;">
              <i class="bi bi-clock-history me-1 text-primary"></i> Retorno por Hora
            </span>
            <span class="text-muted" style="font-size: 0.68rem;">Tiempo confeccionado</span>
          </div>
          <strong class="fs-4 text-primary font-monospace text-nowrap" id="calcDisplayRetorno">
            $<?= number_format($calcRetorno, 2) ?>/hr
          </strong>
        </div>
        <?php if ($hasFinancialData && $calcHoras > 0): ?>
          <?php if ($calcRetorno >= 50): ?>
            <div id="badgeRetornoStatus" class="margin-feedback-pill bg-success-subtle text-success border border-success-subtle">
              <i class="bi bi-star"></i> Remuneración Digna (> $50/hr)
            </div>
          <?php elseif ($calcRetorno >= 30): ?>
            <div id="badgeRetornoStatus" class="margin-feedback-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">
              <i class="bi bi-dash-circle"></i> Retorno Bajo ($30 - $50/hr)
            </div>
          <?php else: ?>
            <div id="badgeRetornoStatus" class="margin-feedback-pill bg-danger-subtle text-danger border border-danger-subtle">
              <i class="bi bi-arrow-down-circle"></i> Retorno Crítico (< $30/hr)
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div id="badgeRetornoStatus" class="margin-feedback-pill bg-light text-muted border">
            <i class="bi bi-clock"></i> Ingrese horas confeccionadas
          </div>
        <?php endif; ?>
      </div>

      <!-- Nota Metodológica -->
      <div class="p-3 bg-light rounded text-muted" style="font-size: 0.75rem; border: 1px dashed var(--craft-border);">
        <i class="bi bi-info-circle me-1 text-primary"></i>
        Fórmula: <code>(Precio - Materiales) / Horas</code>. Proporciona estimación objetiva de rentabilidad artesanal antes de publicar en catálogo.
      </div>

    </div>
  </div>

</div>
