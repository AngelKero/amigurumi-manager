/**
 * Amigurumi Micro-ERP & Catalog - Frontend UI Engine
 * Handles interactive controls, margin calculations, modal lifecycles, and form previews.
 */

document.addEventListener('DOMContentLoaded', () => {
  initNavbarSessionState();
  initMarginCalculator();
  initImagePreview();
  initCheckoutModalStepper();
  initCatalogSortAndFilter();
  initOrderModals();
});

/**
 * 1. Navbar Session State Simulation (Phase 2 Preview & Phase 4 Preparation)
 */
function initNavbarSessionState() {
  const navArtisanDropdown = document.getElementById('navArtisanDropdown');
  const btnNavLogin = document.getElementById('btnNavLogin');
  const navUserBadge = document.getElementById('navUserBadge');

  // Check if simulated artisan session is active in localStorage or query param
  const isArtisanSession = localStorage.getItem('amigurumi_session_active') === 'true';

  if (isArtisanSession && navArtisanDropdown && btnNavLogin && navUserBadge) {
    navArtisanDropdown.classList.remove('d-none');
    btnNavLogin.classList.add('d-none');
    navUserBadge.classList.remove('d-none');
    navUserBadge.classList.add('d-flex');
  }

  // Handle logout buttons
  const logoutButtons = document.querySelectorAll('.btn-nav-logout');
  logoutButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      localStorage.removeItem('amigurumi_session_active');
      window.location.reload();
    });
  });

  // Handle simulated login inside login modal
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const usernameInput = document.getElementById('loginUsername');
      const passwordInput = document.getElementById('loginPassword');
      const alertPlaceholder = document.getElementById('loginAlert');

      if (!usernameInput || !passwordInput) return;

      const user = usernameInput.value.trim();
      const pass = passwordInput.value.trim();

      if (user === 'admin' && pass === 'admin123') {
        localStorage.setItem('amigurumi_session_active', 'true');
        if (alertPlaceholder) alertPlaceholder.classList.add('d-none');
        
        // Hide modal and transform UI
        const loginModalEl = document.getElementById('loginModal');
        if (loginModalEl) {
          const modalInstance = bootstrap.Modal.getInstance(loginModalEl);
          if (modalInstance) modalInstance.hide();
        }
        window.location.reload();
      } else {
        if (alertPlaceholder) {
          alertPlaceholder.textContent = 'Credenciales inválidas. Usuario o contraseña incorrectos.';
          alertPlaceholder.classList.remove('d-none');
        }
      }
    });
  }
}

/**
 * 2. Real-time Margin & Hourly Return Calculator (formulario.html)
 * Provides visual dual feedback on both gross margin % and effective hourly rate.
 */
function initMarginCalculator() {
  const inputPrecio = document.getElementById('inputPrecio');
  const inputCosto = document.getElementById('inputCosto');
  const inputHoras = document.getElementById('inputHoras');

  if (!inputPrecio || !inputCosto || !inputHoras) return;

  const displayPrecio = document.getElementById('calcDisplayPrecio');
  const displayCosto = document.getElementById('calcDisplayCosto');
  const displayGanancia = document.getElementById('calcDisplayGanancia');
  const displayMargen = document.getElementById('calcDisplayMargen');
  const displayRetorno = document.getElementById('calcDisplayRetorno');
  const badgeMargenStatus = document.getElementById('badgeMargenStatus');
  const badgeRetornoStatus = document.getElementById('badgeRetornoStatus');

  function calculateMargin() {
    const precio = parseFloat(inputPrecio.value) || 0;
    const costo = parseFloat(inputCosto.value) || 0;
    const horas = parseFloat(inputHoras.value) || 0;

    const ganancia = precio - costo;
    const margenPorcentaje = precio > 0 ? ((ganancia / precio) * 100) : 0;
    const retornoHora = horas > 0 ? (ganancia / horas) : 0;

    // Update text
    if (displayPrecio) displayPrecio.textContent = `$${precio.toFixed(2)} MXN`;
    if (displayCosto) displayCosto.textContent = `- $${costo.toFixed(2)} MXN`;
    if (displayGanancia) {
      displayGanancia.textContent = `$${ganancia.toFixed(2)} MXN`;
      displayGanancia.className = ganancia >= 0 ? 'fw-bold fs-5 text-success' : 'fw-bold fs-5 text-danger';
    }
    if (displayMargen) displayMargen.textContent = `${margenPorcentaje.toFixed(1)}%`;
    if (displayRetorno) displayRetorno.textContent = `$${retornoHora.toFixed(2)} MXN/hr`;

    // Dual Visual Feedback: 1. Margen de Utilidad
    if (badgeMargenStatus) {
      if (margenPorcentaje >= 60) {
        badgeMargenStatus.className = 'margin-feedback-pill bg-success-subtle text-success border border-success-subtle';
        badgeMargenStatus.innerHTML = '<i class="bi bi-shield-check"></i> Margen Saludable (>60%)';
      } else if (margenPorcentaje >= 35) {
        badgeMargenStatus.className = 'margin-feedback-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        badgeMargenStatus.innerHTML = '<i class="bi bi-exclamation-circle"></i> Margen Moderado (35-60%)';
      } else {
        badgeMargenStatus.className = 'margin-feedback-pill bg-danger-subtle text-danger border border-danger-subtle';
        badgeMargenStatus.innerHTML = '<i class="bi bi-slash-circle"></i> Margen Crítico (<35%)';
      }
    }

    // Dual Visual Feedback: 2. Retorno Efectivo por Hora
    if (badgeRetornoStatus) {
      if (horas === 0) {
        badgeRetornoStatus.className = 'margin-feedback-pill bg-light text-muted border';
        badgeRetornoStatus.innerHTML = '<i class="bi bi-clock"></i> Ingrese horas confeccionadas';
      } else if (retornoHora >= 50) {
        badgeRetornoStatus.className = 'margin-feedback-pill bg-success-subtle text-success border border-success-subtle';
        badgeRetornoStatus.innerHTML = '<i class="bi bi-star"></i> Remuneración Digna (> $50/hr)';
      } else if (retornoHora >= 30) {
        badgeRetornoStatus.className = 'margin-feedback-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        badgeRetornoStatus.innerHTML = '<i class="bi bi-dash-circle"></i> Retorno Bajo ($30 - $50/hr)';
      } else {
        badgeRetornoStatus.className = 'margin-feedback-pill bg-danger-subtle text-danger border border-danger-subtle';
        badgeRetornoStatus.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Retorno Crítico (< $30/hr)';
      }
    }
  }

  inputPrecio.addEventListener('input', calculateMargin);
  inputCosto.addEventListener('input', calculateMargin);
  inputHoras.addEventListener('input', calculateMargin);

  // Initial call
  calculateMargin();
}

/**
 * 3. File Upload Dropzone & Image Preview (formulario.html)
 */
function initImagePreview() {
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

  // Drag & drop highlight
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

/**
 * 4. Checkout Stepper Quantity Control bounded by Stock (detalle.html & index.html)
 */
function initCheckoutModalStepper() {
  const checkoutModal = document.getElementById('checkoutModal');
  if (!checkoutModal) return;

  const btnDec = document.getElementById('btnCheckoutDec');
  const btnInc = document.getElementById('btnCheckoutInc');
  const inputQty = document.getElementById('inputCheckoutQty');
  const displayTotal = document.getElementById('checkoutTotalDisplay');
  const unitPriceHidden = document.getElementById('checkoutUnitPrice');
  const availableStockHidden = document.getElementById('checkoutStockMax');

  function updateTotal() {
    if (!inputQty || !unitPriceHidden || !displayTotal) return;
    const qty = parseInt(inputQty.value) || 1;
    const unitPrice = parseFloat(unitPriceHidden.value) || 450;
    const maxStock = parseInt(availableStockHidden ? availableStockHidden.value : 4) || 4;

    // Enable/disable stepper buttons according to boundary
    if (btnDec) btnDec.disabled = qty <= 1;
    if (btnInc) btnInc.disabled = qty >= maxStock;

    const total = qty * unitPrice;
    displayTotal.textContent = `$${total.toFixed(2)} MXN`;
  }

  if (btnDec && btnInc && inputQty) {
    btnDec.addEventListener('click', () => {
      let currentVal = parseInt(inputQty.value) || 1;
      if (currentVal > 1) {
        inputQty.value = currentVal - 1;
        updateTotal();
      }
    });

    btnInc.addEventListener('click', () => {
      let currentVal = parseInt(inputQty.value) || 1;
      const maxStock = parseInt(availableStockHidden ? availableStockHidden.value : 4) || 4;
      if (currentVal < maxStock) {
        inputQty.value = currentVal + 1;
        updateTotal();
      }
    });

    inputQty.addEventListener('change', () => {
      let currentVal = parseInt(inputQty.value) || 1;
      const maxStock = parseInt(availableStockHidden ? availableStockHidden.value : 4) || 4;
      if (currentVal < 1) inputQty.value = 1;
      if (currentVal > maxStock) inputQty.value = maxStock;
      updateTotal();
    });

    updateTotal();
  }
}

/**
 * 5. Catalog Search, Filter, and Sort Toolbar (index.html)
 */
function initCatalogSortAndFilter() {
  const filterSearch = document.getElementById('filterSearch');
  const filterCategory = document.getElementById('filterCategory');
  const filterStock = document.getElementById('filterStock');
  const sortDropdown = document.getElementById('filterSort');
  const btnClearFilters = document.getElementById('btnClearFilters');
  const productCards = document.querySelectorAll('.product-grid-item');

  if (!productCards.length) return;

  function filterAndSortProducts() {
    const query = filterSearch ? filterSearch.value.trim().toLowerCase() : '';
    const selectedCategory = filterCategory ? filterCategory.value : 'all';
    const selectedStock = filterStock ? filterStock.value : 'all';
    const selectedSort = sortDropdown ? sortDropdown.value : 'recent';

    let matchedCards = [];

    productCards.forEach(card => {
      const name = card.getAttribute('data-name')?.toLowerCase() || '';
      const material = card.getAttribute('data-material')?.toLowerCase() || '';
      const category = card.getAttribute('data-category') || '';
      const stock = parseInt(card.getAttribute('data-stock')) || 0;
      const price = parseFloat(card.getAttribute('data-price')) || 0;

      const matchesSearch = !query || name.includes(query) || material.includes(query);
      const matchesCategory = selectedCategory === 'all' || category === selectedCategory;
      const matchesStock = selectedStock === 'all' || (selectedStock === 'in' && stock > 0) || (selectedStock === 'out' && stock === 0);

      if (matchesSearch && matchesCategory && matchesStock) {
        card.classList.remove('d-none');
        matchedCards.push({ element: card, price: price, stock: stock });
      } else {
        card.classList.add('d-none');
      }
    });

    // Handle sort ordering
    const gridContainer = document.getElementById('productCardGrid');
    if (gridContainer && matchedCards.length > 0) {
      if (selectedSort === 'price-asc') {
        matchedCards.sort((a, b) => a.price - b.price);
      } else if (selectedSort === 'price-desc') {
        matchedCards.sort((a, b) => b.price - a.price);
      }
      // Re-append in order
      matchedCards.forEach(item => gridContainer.appendChild(item.element));
    }
  }

  if (filterSearch) filterSearch.addEventListener('input', filterAndSortProducts);
  if (filterCategory) filterCategory.addEventListener('change', filterAndSortProducts);
  if (filterStock) filterStock.addEventListener('change', filterAndSortProducts);
  if (sortDropdown) sortDropdown.addEventListener('change', filterAndSortProducts);

  if (btnClearFilters) {
    btnClearFilters.addEventListener('click', () => {
      if (filterSearch) filterSearch.value = '';
      if (filterCategory) filterCategory.value = 'all';
      if (filterStock) filterStock.value = 'all';
      if (sortDropdown) sortDropdown.value = 'recent';
      filterAndSortProducts();
    });
  }
}

/**
 * 6. Order Cancellation & Inspection Modals (pedidos.html)
 */
function initOrderModals() {
  // Cancellation Modal Setup
  const cancelButtons = document.querySelectorAll('.btn-trigger-cancel-order');
  const cancelModalIdSpan = document.getElementById('cancelOrderIdSpan');
  const cancelStockUnitsSpan = document.getElementById('cancelStockUnitsSpan');
  const cancelProductNameSpan = document.getElementById('cancelProductNameSpan');

  cancelButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const orderId = btn.getAttribute('data-order-id') || '#1';
      const qty = btn.getAttribute('data-qty') || '1';
      const product = btn.getAttribute('data-product') || 'Dragón Ignis';

      if (cancelModalIdSpan) cancelModalIdSpan.textContent = orderId;
      if (cancelStockUnitsSpan) cancelStockUnitsSpan.textContent = `${qty} unidad(es)`;
      if (cancelProductNameSpan) cancelProductNameSpan.textContent = product;
    });
  });

  // Inspection Modal Setup
  const inspectButtons = document.querySelectorAll('.btn-inspect-order');
  const inspectId = document.getElementById('inspectOrderId');
  const inspectCliente = document.getElementById('inspectCliente');
  const inspectProducto = document.getElementById('inspectProducto');
  const inspectCantidad = document.getElementById('inspectCantidad');
  const inspectTotal = document.getElementById('inspectTotal');
  const inspectFecha = document.getElementById('inspectFecha');
  const inspectNotas = document.getElementById('inspectNotas');

  inspectButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      if (inspectId) inspectId.textContent = btn.getAttribute('data-order-id') || '#1';
      if (inspectCliente) inspectCliente.textContent = btn.getAttribute('data-cliente') || 'Mariana Gómez';
      if (inspectProducto) inspectProducto.textContent = btn.getAttribute('data-product') || 'Dragón Ignis';
      if (inspectCantidad) inspectCantidad.textContent = `${btn.getAttribute('data-qty') || 1} u.`;
      if (inspectTotal) inspectTotal.textContent = btn.getAttribute('data-total') || '$450.00 MXN';
      if (inspectFecha) inspectFecha.textContent = btn.getAttribute('data-fecha') || '2026-09-24';
      if (inspectNotas) inspectNotas.textContent = btn.getAttribute('data-notes') || 'Sin notas especiales.';
    });
  });
}
