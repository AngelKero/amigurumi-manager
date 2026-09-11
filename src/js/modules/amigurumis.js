/**
 * Module: Amigurumis Management (Panel del Artesano)
 * Single Responsibility: Filtrado reactivo, ordenación, ajuste rápido de stock in-situ,
 * cálculo de KPIs de almacén y ficha técnica modal.
 */

export function initAmigurumis() {
  const table = document.getElementById('amigurumisTable');
  if (!table) return;

  const tbody = document.getElementById('amigurumisTableBody');
  const searchInput = document.getElementById('searchAmigurumiInput');
  const filterCategory = document.getElementById('filterCategorySelect');
  const filterStockStatus = document.getElementById('filterStockStatusSelect');
  const filterArtisan = document.getElementById('filterArtisanSelect');
  const sortSelect = document.getElementById('sortAmigurumisSelect');
  const btnReset = document.getElementById('btnResetAmigurumiFilters');
  const btnResetEmpty = document.getElementById('btnResetEmptyAmigurumis');
  const emptyState = document.getElementById('emptyAmigurumisState');

  // KPI elements
  const kpiStockEl = document.getElementById('kpiAmigurumiStock');
  const kpiValorEl = document.getElementById('kpiAmigurumiValor');
  const kpiCostosEl = document.getElementById('kpiAmigurumiCostos');

  // Recalcular métricas de almacén
  function updateKPIs() {
    const rows = tbody.querySelectorAll('tr');
    let totalStock = 0;
    let totalValor = 0;
    let totalCostos = 0;

    rows.forEach(tr => {
      const stock = parseInt(tr.getAttribute('data-stock') || '0', 10);
      const precio = parseFloat(tr.getAttribute('data-precio') || '0');
      const costo = parseFloat(tr.getAttribute('data-costo') || '0');

      totalStock += stock;
      totalValor += (stock * precio);
      totalCostos += (stock * costo);
    });

    if (kpiStockEl) kpiStockEl.textContent = totalStock;
    if (kpiValorEl) kpiValorEl.textContent = `$${totalValor.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    if (kpiCostosEl) kpiCostosEl.textContent = `$${totalCostos.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }

  // Filtrar y ordenar la tabla de amigurumis
  function filterAndSort() {
    const term = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const category = filterCategory ? filterCategory.value : 'all';
    const stockStatus = filterStockStatus ? filterStockStatus.value : 'all';
    const artisan = filterArtisan ? filterArtisan.value : 'all';
    const sortVal = sortSelect ? sortSelect.value : 'name-asc';

    const rows = Array.from(tbody.querySelectorAll('tr'));
    let visibleCount = 0;

    rows.forEach(row => {
      const nombre = (row.getAttribute('data-nombre') || '').toLowerCase();
      const material = (row.getAttribute('data-material') || '').toLowerCase();
      const descripcion = (row.getAttribute('data-descripcion') || '').toLowerCase();
      const rowCategory = row.getAttribute('data-categoria') || '';
      const rowArtisan = row.getAttribute('data-artisan') || '';
      const rowStock = parseInt(row.getAttribute('data-stock') || '0', 10);
      const isOnDemand = row.getAttribute('data-on-demand') === '1';

      // Coincidencia de texto
      const matchSearch = !term || nombre.includes(term) || material.includes(term) || descripcion.includes(term);

      // Coincidencia de categoría
      const matchCategory = (category === 'all') || (rowCategory === category);

      // Coincidencia de artesano
      const matchArtisan = (artisan === 'all') || (rowArtisan === artisan);

      // Coincidencia de estado de stock
      let matchStock = true;
      if (stockStatus === 'in-stock') {
        matchStock = (rowStock > 0);
      } else if (stockStatus === 'low-stock') {
        matchStock = (rowStock > 0 && rowStock <= 3);
      } else if (stockStatus === 'out-of-stock') {
        matchStock = (rowStock === 0 && !isOnDemand);
      } else if (stockStatus === 'on-demand') {
        matchStock = isOnDemand;
      }

      if (matchSearch && matchCategory && matchArtisan && matchStock) {
        row.classList.remove('d-none');
        visibleCount++;
      } else {
        row.classList.add('d-none');
      }
    });

    // Ordenar filas visibles
    const sortedRows = rows.slice().sort((a, b) => {
      const nameA = (a.getAttribute('data-nombre') || '').toLowerCase();
      const nameB = (b.getAttribute('data-nombre') || '').toLowerCase();
      const priceA = parseFloat(a.getAttribute('data-precio') || '0');
      const priceB = parseFloat(b.getAttribute('data-precio') || '0');
      const stockA = parseInt(a.getAttribute('data-stock') || '0', 10);
      const stockB = parseInt(b.getAttribute('data-stock') || '0', 10);
      
      const costA = parseFloat(a.getAttribute('data-costo') || '0');
      const costB = parseFloat(b.getAttribute('data-costo') || '0');
      const hoursA = parseFloat(a.getAttribute('data-horas') || '1') || 1;
      const hoursB = parseFloat(b.getAttribute('data-horas') || '1') || 1;
      const rateA = (priceA - costA) / hoursA;
      const rateB = (priceB - costB) / hoursB;

      switch (sortVal) {
        case 'name-asc':
          return nameA.localeCompare(nameB);
        case 'price-desc':
          return priceB - priceA;
        case 'price-asc':
          return priceA - priceB;
        case 'stock-desc':
          return stockB - stockA;
        case 'stock-asc':
          return stockA - stockB;
        case 'rate-desc':
          return rateB - rateA;
        default:
          return 0;
      }
    });

    sortedRows.forEach(r => tbody.appendChild(r));

    // Controlar estado vacío
    if (emptyState) {
      if (visibleCount === 0) {
        emptyState.classList.remove('d-none');
        table.classList.add('d-none');
      } else {
        emptyState.classList.add('d-none');
        table.classList.remove('d-none');
      }
    }
  }

  // Ajuste rápido de stock in-situ
  tbody.addEventListener('click', (e) => {
    const incBtn = e.target.closest('.btn-stock-inc');
    const decBtn = e.target.closest('.btn-stock-dec');

    if (incBtn || decBtn) {
      const btn = incBtn || decBtn;
      const row = btn.closest('tr');
      if (!row) return;

      const id = row.getAttribute('data-id');
      let currentStock = parseInt(row.getAttribute('data-stock') || '0', 10);
      const isOnDemand = row.getAttribute('data-on-demand') === '1';

      if (incBtn) {
        currentStock++;
      } else if (decBtn && currentStock > 0) {
        currentStock--;
      }

      // Actualizar atributo y valor visual
      row.setAttribute('data-stock', currentStock);
      const valEl = document.getElementById(`stockVal_${id}`);
      if (valEl) valEl.textContent = currentStock;

      // Actualizar insignia de estado de stock
      const badgeContainer = document.getElementById(`stockBadgeContainer_${id}`);
      if (badgeContainer) {
        if (isOnDemand) {
          badgeContainer.innerHTML = '<span class="badge bg-light text-muted font-monospace border" style="font-size: 0.7rem;">Bajo Encargo</span>';
        } else if (currentStock === 0) {
          badgeContainer.innerHTML = '<span class="badge-stock-alert">Agotado</span>';
        } else if (currentStock <= 3) {
          badgeContainer.innerHTML = '<span class="badge-stock-critical">Stock Crítico</span>';
        } else {
          badgeContainer.innerHTML = '<span class="badge badge-stock-in" style="font-size: 0.72rem;">En Existencia</span>';
        }
      }

      updateKPIs();
    }

    // Alternar modalidad de confección in-situ (es_sobre_encargo)
    const toggleEncargoBtn = e.target.closest('.btn-toggle-encargo');
    if (toggleEncargoBtn) {
      const row = toggleEncargoBtn.closest('tr');
      if (!row) return;

      const id = row.getAttribute('data-id');
      const currentOnDemand = row.getAttribute('data-on-demand') === '1';
      const newOnDemand = !currentOnDemand;
      row.setAttribute('data-on-demand', newOnDemand ? '1' : '0');

      if (newOnDemand) {
        toggleEncargoBtn.className = 'btn-toggle-encargo badge badge-textile-tag text-primary border-primary border-0 bg-transparent p-1';
        toggleEncargoBtn.innerHTML = '<i class="bi bi-magic me-1"></i>Bajo Encargo (5-7 d)';
        toggleEncargoBtn.title = 'Click para cambiar a Entrega Inmediata (Con stock)';
      } else {
        toggleEncargoBtn.className = 'btn-toggle-encargo badge bg-light text-dark border font-monospace border-0 p-1';
        toggleEncargoBtn.innerHTML = '<i class="bi bi-lightning-charge-fill text-warning me-1"></i>Inmediata';
        toggleEncargoBtn.title = 'Click para cambiar a Bajo Encargo Exclusivo';
      }

      // Actualizar insignia de estado de stock en la fila
      const currentStock = parseInt(row.getAttribute('data-stock') || '0', 10);
      const badgeContainer = document.getElementById(`stockBadgeContainer_${id}`);
      if (badgeContainer) {
        if (newOnDemand) {
          badgeContainer.innerHTML = '<span class="badge bg-light text-muted font-monospace border" style="font-size: 0.7rem;">Bajo Encargo</span>';
        } else if (currentStock === 0) {
          badgeContainer.innerHTML = '<span class="badge-stock-alert">Agotado</span>';
        } else if (currentStock <= 3) {
          badgeContainer.innerHTML = '<span class="badge-stock-critical">Stock Crítico</span>';
        } else {
          badgeContainer.innerHTML = '<span class="badge badge-stock-in" style="font-size: 0.72rem;">En Existencia</span>';
        }
      }
    }

    // Modal de Eliminación: poblar nombre del amigurumi
    const deleteBtn = e.target.closest('.btn-card-delete');
    if (deleteBtn) {
      const name = deleteBtn.getAttribute('data-name');
      const nameSpan = document.getElementById('deleteAmigurumiName');
      if (nameSpan && name) nameSpan.textContent = name;
    }

    // Modal de Inspección Técnica
    const inspectBtn = e.target.closest('.btn-inspect-amigurumi');
    if (inspectBtn) {
      const row = inspectBtn.closest('tr');
      if (!row) return;

      const id = row.getAttribute('data-id');
      const nombre = row.getAttribute('data-nombre');
      const categoria = row.getAttribute('data-categoria');
      const material = row.getAttribute('data-material');
      const tamano = row.getAttribute('data-tamano');
      const precio = parseFloat(row.getAttribute('data-precio') || '0');
      const costo = parseFloat(row.getAttribute('data-costo') || '0');
      const stock = parseInt(row.getAttribute('data-stock') || '0', 10);
      const horas = parseFloat(row.getAttribute('data-horas') || '0');
      const artisan = row.getAttribute('data-artisan');
      const isOnDemand = row.getAttribute('data-on-demand') === '1';
      const descripcion = row.getAttribute('data-descripcion');
      const pedidos = parseInt(row.getAttribute('data-pedidos') || '0', 10);

      const margen = precio - costo;
      const retornoHora = horas > 0 ? (margen / horas) : 0;

      // Inyectar datos en el modal
      const idEl = document.getElementById('inspectAmigurumiId');
      const titleEl = document.getElementById('inspectAmigurumiTitle');
      const catEl = document.getElementById('inspectCategoryBadge');
      const tamEl = document.getElementById('inspectTamano');
      const precioEl = document.getElementById('inspectPrecio');
      const costoEl = document.getElementById('inspectCosto');
      const margenEl = document.getElementById('inspectMargen');
      const horasEl = document.getElementById('inspectHoras');
      const hourlyEl = document.getElementById('inspectHourlyRate');
      const matEl = document.getElementById('inspectMaterial');
      const descEl = document.getElementById('inspectDescripcion');
      const stockBadgeEl = document.getElementById('inspectStockBadge');
      const onDemandBadgeEl = document.getElementById('inspectOnDemandBadge');
      const authorEl = document.getElementById('inspectArtisanAuthor');
      const pedidosInfoEl = document.getElementById('inspectPedidosInfo');
      const btnEdit = document.getElementById('inspectBtnEdit');
      const btnDetail = document.getElementById('inspectBtnViewDetail');

      if (idEl) idEl.textContent = `#${id}`;
      if (titleEl) titleEl.textContent = nombre;
      if (catEl) catEl.textContent = categoria;
      if (tamEl) tamEl.textContent = `${tamano} cm`;
      if (precioEl) precioEl.textContent = `$${precio.toFixed(2)}`;
      if (costoEl) costoEl.textContent = `$${costo.toFixed(2)}`;
      if (margenEl) margenEl.textContent = `$${margen.toFixed(2)}`;
      if (horasEl) horasEl.textContent = `${horas} hrs`;
      if (hourlyEl) hourlyEl.innerHTML = `<i class="bi bi-clock-history"></i> Retorno: $${retornoHora.toFixed(2)}/hr`;
      if (matEl) matEl.textContent = material;
      if (descEl) descEl.textContent = descripcion;
      if (authorEl) authorEl.textContent = `Autoría: @${artisan} (Taller Principal)`;

      if (pedidosInfoEl) {
        if (pedidos > 0) {
          pedidosInfoEl.innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="bi bi-shield-lock-fill me-1"></i>${pedidos} encargo(s) activo(s) &bull; ON DELETE RESTRICT</span>`;
        } else {
          pedidosInfoEl.innerHTML = `<span class="badge bg-light text-muted border"><i class="bi bi-check2-circle me-1"></i>Sin encargos vinculados</span>`;
        }
      }

      if (stockBadgeEl) {
        if (stock === 0 && !isOnDemand) {
          stockBadgeEl.className = 'badge badge-stock-alert';
          stockBadgeEl.textContent = 'Agotado (0 disp.)';
        } else {
          stockBadgeEl.className = 'badge badge-stock-in';
          stockBadgeEl.textContent = `En Stock: ${stock} u.`;
        }
      }

      if (onDemandBadgeEl) {
        if (isOnDemand) {
          onDemandBadgeEl.className = 'badge badge-textile-tag';
          onDemandBadgeEl.textContent = 'Bajo Encargo (5-7 d)';
        } else {
          onDemandBadgeEl.className = 'badge bg-light text-dark font-monospace border';
          onDemandBadgeEl.textContent = 'Entrega Inmediata';
        }
      }

      if (btnEdit) btnEdit.href = `formulario.php?id=${id}`;
      if (btnDetail) btnDetail.href = `detalle.php?id=${id}`;

      // Abrir modal con Bootstrap
      const modalEl = document.getElementById('modalInspectAmigurumi');
      if (modalEl && window.bootstrap) {
        const modal = new window.bootstrap.Modal(modalEl);
        modal.show();
      }
    }
  });

  // Eventos de filtrado
  if (searchInput) searchInput.addEventListener('input', filterAndSort);
  if (filterCategory) filterCategory.addEventListener('change', filterAndSort);
  if (filterStockStatus) filterStockStatus.addEventListener('change', filterAndSort);
  if (filterArtisan) filterArtisan.addEventListener('change', filterAndSort);
  if (sortSelect) sortSelect.addEventListener('change', filterAndSort);

  // Botones de reinicio
  function resetFilters() {
    if (searchInput) searchInput.value = '';
    if (filterCategory) filterCategory.value = 'all';
    if (filterStockStatus) filterStockStatus.value = 'all';
    if (filterArtisan) filterArtisan.value = 'all';
    if (sortSelect) sortSelect.value = 'name-asc';
    filterAndSort();
  }

  if (btnReset) btnReset.addEventListener('click', resetFilters);
  if (btnResetEmpty) btnResetEmpty.addEventListener('click', resetFilters);

  // Inicializar cálculo de KPIs
  updateKPIs();
}
