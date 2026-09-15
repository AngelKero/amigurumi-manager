/**
 * Module: Dropzone (Carga y Vista Previa de Imágenes de Creaciones)
 * Single Responsibility: Gestión del área de arrastre (drag and drop), validación
 * espejo del servidor (MIME real JPEG/PNG/WebP + ≤5MB, R-09) y previsualización local.
 *
 * El servidor sigue siendo la autoridad (422 ante MIME/tamaño inválidos); esta
 * validación solo adelanta el feedback accesible sin roundtrip.
 */

const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_BYTES = 5 * 1024 * 1024;

function describeFileError(file) {
  if (!file) return '';
  if (file.type && !ALLOWED_MIME.includes(file.type)) {
    return `Formato no admitido (${file.type || 'desconocido'}). Usa JPG, PNG o WEBP.`;
  }
  const maxBytes = MAX_BYTES;
  if (typeof file.size === 'number' && file.size > maxBytes) {
    return `La imagen supera el máximo de 5MB (${(file.size / 1048576).toFixed(1)}MB).`;
  }
  return '';
}

export function isValidImageFile(file) {
  if (!file) return false;
  if (file.type && !file.type.startsWith('image/')) return false;
  return describeFileError(file) === '';
}

export function initDropzone() {
  const inputFile = document.getElementById('inputImagen');
  const previewContainer = document.getElementById('imagePreviewContainer');
  const previewImg = document.getElementById('imagePreview');
  const btnRemove = document.getElementById('btnRemoveImage');
  const dropzone = document.getElementById('uploadDropzone');
  const errorEl = document.getElementById('dropzoneError');

  if (!inputFile || !previewContainer || !previewImg) return;

  const maxBytes = Number(inputFile.getAttribute('data-max-bytes')) || MAX_BYTES;

  function showError(message) {
    if (!errorEl) return;
    if (!message) {
      errorEl.classList.add('d-none');
      errorEl.textContent = '';
      return;
    }
    errorEl.textContent = message;
    errorEl.classList.remove('d-none');
  }

  function showFile(file) {
    if (!file) return;
    if (file.type && !file.type.startsWith('image/')) {
      showError('El archivo elegido no es una imagen.');
      return;
    }
    const problem = (typeof file.size === 'number' && file.size > maxBytes)
      || (file.type && !ALLOWED_MIME.includes(file.type))
      ? describeFileError(file)
      : '';
    if (problem) {
      showError(problem);
      return;
    }
    showError('');
    const reader = new FileReader();
    reader.onload = (e) => {
      previewImg.src = e.target.result;
      previewContainer.classList.remove('d-none');
      if (dropzone) dropzone.classList.add('d-none');
    };
    reader.readAsDataURL(file);
  }

  inputFile.addEventListener('change', (e) => {
    if (e.target.files && e.target.files[0]) {
      showFile(e.target.files[0]);
    }
  });

  if (btnRemove) {
    btnRemove.addEventListener('click', () => {
      inputFile.value = '';
      previewImg.removeAttribute('src');
      previewContainer.classList.add('d-none');
      showError('');
      if (dropzone) dropzone.classList.remove('d-none');
    });
  }

  if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
      }, false);
    });

    dropzone.addEventListener('drop', (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files && files[0]) {
        inputFile.files = files;
        showFile(files[0]);
      }
    });
  }
}
