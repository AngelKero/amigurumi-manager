/**
 * Module: Catalog (Búsqueda, Filtro por Categorías Textiles y Ordenamiento)
 * Single Responsibility: Filtrado dinámico del catálogo y actualización del contador de piezas.
 */

import { parseCurrency, centsToPesos } from './currency.js';

export function initCatalog() {
  const searchInput = document.getElementById('filterSearch') || document.getElementById('searchCatalog');
  const filterCategory = document.getElementById('filterCategory');
  const filterStock = document.getElementById('filterStock');
  const sortCatalog = document.getElementById('filterSort') || document.getElementById('sortCatalog');
  const filterPriceMin = document.getElementById('filterPriceMin');
  const filterPriceMax = document.getElementById('filterPriceMax');
  const filterArtisan = document.getElementById('filterArtisan');
  const btnClearFilters = document.getElementById('btnClearFilters');
  const productCards = document.querySelectorAll('.product-grid-item, .card-product-item');
  const filterResultsCount = document.getElementById('filterResultsCount');
  const textileChips = document.querySelectorAll('.btn-chip-textile');

  if (!productCards.length) return;

  function updateFilterCounter(visibleCount) {
    if (filterResultsCount) {
      filterResultsCount.innerHTML = `<i class="bi bi-grid-fill text-primary me-1"></i>${visibleCount} ${visibleCount === 1 ? 'pieza visible' : 'piezas visibles'}`;
    }
    const paginationShowing = document.getElementById('paginationShowingCount');
    if (paginationShowing) {
      paginationShowing.textContent = visibleCount;
    }
  }

  function filterAndSort() {
    const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const category = filterCategory ? filterCategory.value : 'all';
    const stock = filterStock ? filterStock.value : 'all';
    const minPrice = filterPriceMin && filterPriceMin.value !== '' ? parseCurrency(filterPriceMin.value) : 0;
    const maxPrice = filterPriceMax && filterPriceMax.value !== '' ? parseCurrency(filterPriceMax.value) : Infinity;
    const artisan = filterArtisan ? filterArtisan.value : 'all';

    let visibleCount = 0;

    productCards.forEach(item => {
      const name = (item.getAttribute('data-name') || '').toLowerCase();
      const material = (item.getAttribute('data-material') || '').toLowerCase();
      const cat = item.getAttribute('data-category') || '';
      const itemStock = parseInt(item.getAttribute('data-stock') || '0', 10);
      const itemPrice = parseFloat(item.getAttribute('data-price') || '0') || centsToPesos(item.getAttribute('data-price-cents') || 0);
      const itemArtisan = item.getAttribute('data-artisan') || 'admin';
      const isOnDemand = item.getAttribute('data-on-demand') === '1';

      const matchesSearch = !query || name.includes(query) || material.includes(query);
      const matchesCategory = category === 'all' || cat === category;
      const matchesPrice = itemPrice >= minPrice && itemPrice <= maxPrice;
      const matchesArtisan = artisan === 'all' || itemArtisan === artisan;

      let matchesStock = true;
      if (stock === 'in' || stock === 'in_stock') {
        matchesStock = itemStock > 0;
      } else if (stock === 'on-demand') {
        matchesStock = isOnDemand;
      } else if (stock === 'out' || stock === 'out_stock') {
        matchesStock = itemStock === 0 && !isOnDemand;
      }

      if (matchesSearch && matchesCategory && matchesStock && matchesPrice && matchesArtisan) {
        item.classList.remove('d-none');
        visibleCount++;
      } else {
        item.classList.add('d-none');
      }
    });

    const emptyState = document.getElementById('emptyCatalogState');
    if (emptyState) {
      if (visibleCount === 0) {
        emptyState.classList.remove('d-none');
      } else {
        emptyState.classList.add('d-none');
      }
    }

    updateFilterCounter(visibleCount);
  }

  // Restablecer filtros desde el Empty State
  const btnResetEmpty = document.getElementById('btnResetFiltersEmpty');
  if (btnResetEmpty && btnClearFilters) {
    btnResetEmpty.addEventListener('click', () => btnClearFilters.click());
  }

  // Activar acciones del artesano en tarjetas si hay sesión activa
  const isArtisanSession = localStorage.getItem('crochet_session_active') === 'true' || localStorage.getItem('amigurumi_session_active') === 'true';
  if (isArtisanSession) {
    document.querySelectorAll('.artisan-card-actions').forEach(el => {
      el.classList.remove('d-none');
    });
  }

  // Disparar modal de eliminación desde tarjeta
  document.querySelectorAll('.btn-card-delete').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.getAttribute('data-name') || 'esta pieza';
      const nameSpan = document.getElementById('deleteCreacionName') || document.getElementById('deleteAmigurumiName');
      if (nameSpan) nameSpan.textContent = name;
    });
  });

  // Sincronización bidireccional con los chips textiles
  if (textileChips.length) {
    textileChips.forEach(chip => {
      chip.addEventListener('click', (e) => {
        e.preventDefault();
        textileChips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');

        const cat = chip.getAttribute('data-category');
        if (filterCategory) {
          filterCategory.value = cat;
          filterAndSort();
        }
      });
    });
  }

  if (searchInput) searchInput.addEventListener('input', filterAndSort);
  if (filterCategory) {
    filterCategory.addEventListener('change', () => {
      const selected = filterCategory.value;
      textileChips.forEach(c => {
        if (c.getAttribute('data-category') === selected) {
          c.classList.add('active');
        } else {
          c.classList.remove('active');
        }
      });
      filterAndSort();
    });
  }
  if (filterStock) filterStock.addEventListener('change', filterAndSort);
  if (filterPriceMin) filterPriceMin.addEventListener('input', filterAndSort);
  if (filterPriceMax) filterPriceMax.addEventListener('input', filterAndSort);
  if (filterArtisan) filterArtisan.addEventListener('change', filterAndSort);

  // Ordenamiento
  if (sortCatalog) {
    sortCatalog.addEventListener('change', () => {
      const container = document.getElementById('productCardGrid') || document.getElementById('catalogProductsGrid');
      if (!container) return;

      const cardsArray = Array.from(productCards);
      const val = sortCatalog.value;

      cardsArray.sort((a, b) => {
        const priceA = parseFloat(a.getAttribute('data-price') || 0);
        const priceB = parseFloat(b.getAttribute('data-price') || 0);
        const nameA = a.getAttribute('data-name') || '';
        const nameB = b.getAttribute('data-name') || '';

        if (val === 'price-asc' || val === 'price_asc') return priceA - priceB;
        if (val === 'price-desc' || val === 'price_desc') return priceB - priceA;
        if (val === 'name-asc' || val === 'name_asc') return nameA.localeCompare(nameB);
        return 0;
      });

      cardsArray.forEach(card => container.appendChild(card));
    });
  }

  // Botón para limpiar filtros
  if (btnClearFilters) {
    btnClearFilters.addEventListener('click', () => {
      if (searchInput) searchInput.value = '';
      if (filterCategory) filterCategory.value = 'all';
      if (filterStock) filterStock.value = 'all';
      if (filterPriceMin) filterPriceMin.value = '';
      if (filterPriceMax) filterPriceMax.value = '';
      if (filterArtisan) filterArtisan.value = 'all';
      if (sortCatalog) sortCatalog.value = (sortCatalog.options[0]?.value || 'recent');

      textileChips.forEach(c => {
        if (c.getAttribute('data-category') === 'all') {
          c.classList.add('active');
        } else {
          c.classList.remove('active');
        }
      });

      filterAndSort();
    });
  }
}
