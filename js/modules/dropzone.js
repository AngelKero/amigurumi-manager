/**
 * Module: Dropzone (Carga y Vista Previa de Imágenes de Amigurumis)
 * Single Responsibility: Gestión del área de arrastre (drag and drop) y previsualización local.
 */

export function initDropzone() {
  const inputFile = document.getElementById('inputImagen');
  const previewContainer = document.getElementById('imagePreviewContainer');
  const previewImg = document.getElementById('imagePreview');
  const btnRemove = document.getElementById('btnRemoveImage');
  const dropzone = document.getElementById('uploadDropzone');

  if (!inputFile || !previewContainer || !previewImg) return;

  function showFile(file) {
    if (!file || !file.type.startsWith('image/')) return;
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
      previewImg.src = '';
      previewContainer.classList.add('d-none');
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
