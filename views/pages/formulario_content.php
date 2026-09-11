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
    'descripcion' => 'Tierno ajolote mexicano con textura aterciopelada ultra suave, branquias externas kawaii y ojos de seguridad.',
    'imagen_preview' => ''
  ]
];

$currentItem = ($isEditing && isset($seedItems[$editId])) ? $seedItems[$editId] : [
  'nombre' => $isEditing ? 'Creación #' . $editId : '',
  'categoria' => 'Fantasía',
  'material' => '',
  'tamano_cm' => 15.0,
  'precio' => 350.00,
  'costo' => 100.00,
  'horas' => 5.0,
  'stock' => 1,
  'descripcion' => '',
  'imagen_preview' => ''
];
?>
<!-- 2-COLUMN SPLIT: FORMULARIO (col-lg-8) + SIMULADOR FINANCIERO (col-lg-4) -->
<div class="row g-4 mb-5">

  <!-- FORMULARIO DE ALTA / EDICIÓN -->
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm p-4 bg-white card-stitched" style="border-radius: var(--craft-radius);">
      
      <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <div>
          <h3 class="fw-bold mb-1" id="formTitle">
            <?= $isEditing ? 'Modificar Creación Artesanal' : 'Registrar Nueva Creación Artesanal' ?>
          </h3>
          <p class="text-muted small mb-0">
            <?= $isEditing ? 'Actualiza los parámetros físicos, costos o inventario de la pieza.' : 'Completa las especificaciones físicas y económicas de la nueva pieza.' ?>
          </p>
        </div>
        <span class="badge badge-textile-tag fs-6" id="modeBadge">
          <?= $isEditing ? 'Modo: Edición #' . $editId : 'Modo: Nuevo Registro' ?>
        </span>
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
          <div class="col-12 col-md-6">
            <label for="inputCategoria" class="form-label fw-bold small">Categoría (*)</label>
            <select class="form-select select-craft-pill" id="inputCategoria" required>
              <option value="Fantasía" <?= $currentItem['categoria'] === 'Fantasía' ? 'selected' : '' ?>>Fantasía</option>
              <option value="Plantas / Botánica" <?= $currentItem['categoria'] === 'Plantas / Botánica' ? 'selected' : '' ?>>Plantas / Botánica</option>
              <option value="Animales / Fauna" <?= $currentItem['categoria'] === 'Animales / Fauna' ? 'selected' : '' ?>>Animales / Fauna</option>
              <option value="Navidad / Temporada" <?= $currentItem['categoria'] === 'Navidad / Temporada' ? 'selected' : '' ?>>Navidad / Temporada</option>
              <option value="Llaveros / Accesorios" <?= $currentItem['categoria'] === 'Llaveros / Accesorios' ? 'selected' : '' ?>>Llaveros / Accesorios</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label for="inputMaterial" class="form-label fw-bold small">Material Textil Principal (*)</label>
            <input type="text" class="form-control input-craft-pill" id="inputMaterial" placeholder="Ej. 100% Algodón Mercerizado" value="<?= htmlspecialchars($currentItem['material']) ?>" required minlength="3" maxlength="80">
          </div>
        </div>

        <!-- Tamaño y Cantidad en Stock -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-md-6">
            <label for="inputTamano" class="form-label fw-bold small">Tamaño / Altura en cm (*)</label>
            <div class="input-group">
              <input type="number" step="0.1" min="0.1" max="250.0" class="form-control input-craft-pill" id="inputTamano" value="<?= $currentItem['tamano_cm'] ?>" required>
              <span class="input-group-text bg-light text-muted" style="border-top-right-radius: var(--craft-radius-pill); border-bottom-right-radius: var(--craft-radius-pill);">cm</span>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <label for="inputStock" class="form-label fw-bold small">Cantidad en Stock Físico Inicial (*)</label>
            <div class="input-group">
              <input type="number" min="0" max="10000" class="form-control input-craft-pill" id="inputStock" value="<?= $currentItem['stock'] ?>" required>
              <span class="input-group-text bg-light text-muted" style="border-top-right-radius: var(--craft-radius-pill); border-bottom-right-radius: var(--craft-radius-pill);">unidades</span>
            </div>
          </div>
        </div>

        <!-- Parámetros Económicos (Disparan el cálculo dinámico) -->
        <div class="p-3 mb-4 rounded border" style="background-color: var(--craft-surface-muted);">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-cash-stack text-primary"></i>
            <span class="fw-bold small text-uppercase">Parámetros Económicos y Tiempo de Labor</span>
          </div>
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <label for="inputPrecio" class="form-label fw-bold small">Precio Venta (MXN) (*)</label>
              <div class="input-group">
                <span class="input-group-text bg-white" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">$</span>
                <input type="number" step="0.01" min="1" max="9999999" class="form-control fw-bold input-craft-pill" id="inputPrecio" value="<?= number_format($currentItem['precio'], 2, '.', '') ?>" required style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
              </div>
            </div>
            <div class="col-12 col-md-4">
              <label for="inputCosto" class="form-label fw-bold small">Costo Materiales (MXN) (*)</label>
              <div class="input-group">
                <span class="input-group-text bg-white" style="border-top-left-radius: var(--craft-radius-pill); border-bottom-left-radius: var(--craft-radius-pill);">$</span>
                <input type="number" step="0.01" min="0" max="9999999" class="form-control input-craft-pill" id="inputCosto" value="<?= number_format($currentItem['costo'], 2, '.', '') ?>" required style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
              </div>
            </div>
            <div class="col-12 col-md-4">
              <label for="inputHoras" class="form-label fw-bold small">Horas Confeccionadas</label>
              <div class="input-group">
                <input type="number" step="0.25" min="0" max="500.0" class="form-control input-craft-pill" id="inputHoras" value="<?= $currentItem['horas'] ?>">
                <span class="input-group-text bg-white text-muted" style="border-top-right-radius: var(--craft-radius-pill); border-bottom-right-radius: var(--craft-radius-pill);">hrs</span>
              </div>
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
          
          <!-- Dropzone de Selección -->
          <div class="upload-dropzone" id="uploadDropzone" onclick="document.getElementById('inputImagen').click()">
            <i class="bi bi-cloud-arrow-up fs-1 text-primary d-block mb-2"></i>
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

  <!-- SIMULADOR FINANCIERO STICKY CON FEEDBACK DUAL (col-lg-4) -->
  <div class="col-12 col-lg-4">
    <div class="card sticky-margin-card p-4 card-stitched">
      
      <div class="d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
        <div class="bg-warning-subtle text-warning-emphasis p-2 rounded">
          <i class="bi bi-calculator-fill fs-5"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0">Simulador de Márgenes</h5>
          <small class="text-muted">Cálculo de viabilidad en vivo</small>
        </div>
      </div>

      <p class="text-muted small mb-3">
        Monitorea en tiempo real la salud financiera de tu amigurumi conforme modificas precio, costo de estambre y horas dedicadas:
      </p>

      <!-- Desglose de Números -->
      <div class="p-3 rounded mb-3 border" style="background-color: var(--craft-surface-muted);">
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted small">Precio Venta:</span>
          <span class="fw-bold text-dark font-monospace" id="calcDisplayPrecio">$<?= number_format($currentItem['precio'], 2) ?> MXN</span>
        </div>
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted small">Costo Materiales:</span>
          <span class="text-danger font-monospace" id="calcDisplayCosto">- $<?= number_format($currentItem['costo'], 2) ?> MXN</span>
        </div>
        <div class="d-flex justify-content-between py-2 mt-2 bg-white px-2 rounded border">
          <span class="fw-bold small">Ganancia Bruta:</span>
          <span class="fw-bold text-success font-monospace" id="calcDisplayGanancia">$<?= number_format($currentItem['precio'] - $currentItem['costo'], 2) ?> MXN</span>
        </div>
      </div>

      <!-- FEEDBACK DUAL -->
      <!-- Métrica 1: Margen Porcentual -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="small text-muted fw-bold">1. Margen de Ganancia:</span>
          <strong class="fs-5 text-dark font-monospace" id="calcDisplayMargen">
            <?= number_format((($currentItem['precio'] - $currentItem['costo']) / ($currentItem['precio'] ?: 1)) * 100, 1) ?>%
          </strong>
        </div>
        <div id="badgeMargenStatus" class="margin-feedback-pill bg-success-subtle text-success border border-success-subtle">
          <i class="bi bi-shield-check"></i> Margen Saludable (>60%)
        </div>
      </div>

      <!-- Métrica 2: Retorno por Hora de Trabajo -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="small text-muted fw-bold">2. Retorno Efectivo por Hora:</span>
          <strong class="fs-5 text-primary font-monospace" id="calcDisplayRetorno">
            $<?= number_format(($currentItem['precio'] - $currentItem['costo']) / ($currentItem['horas'] ?: 1), 2) ?> MXN/hr
          </strong>
        </div>
        <div id="badgeRetornoStatus" class="margin-feedback-pill bg-success-subtle text-success border border-success-subtle">
          <i class="bi bi-star"></i> Remuneración Digna (> $50/hr)
        </div>
      </div>

      <!-- Nota Metodológica -->
      <div class="p-3 bg-light rounded text-muted" style="font-size: 0.75rem;">
        <i class="bi bi-info-circle me-1 text-primary"></i>
        Fórmula: <code>(Precio - Materiales) / Horas</code>. Proporciona estimación objetiva de rentabilidad artesanal antes de publicar en catálogo.
      </div>

    </div>
  </div>

</div>
