/**
 * Module: Catalog (Búsqueda, Filtro por Categorías Textiles y Ordenamiento)
 * Single Responsibility: Filtrado dinámico del catálogo y actualización del contador de piezas.
 */

export function initCatalog() {
  const searchInput = document.getElementById('searchCatalog');
  const filterCategory = document.getElementById('filterCategory');
  const filterStock = document.getElementById('filterStock');
  const sortCatalog = document.getElementById('sortCatalog');
  const btnClearFilters = document.getElementById('btnClearFilters');
  const productCards = document.querySelectorAll('.card-product-item');
  const filterResultsCount = document.getElementById('filterResultsCount');
  const textileChips = document.querySelectorAll('.btn-chip-textile');

  if (!productCards.length) return;

  function updateFilterCounter(visibleCount) {
    if (filterResultsCount) {
      filterResultsCount.innerHTML = `<i class="bi bi-grid-fill text-primary me-1"></i>${visibleCount} ${visibleCount === 1 ? 'pieza visible' : 'piezas visibles'}`;
    }
  }

  function filterAndSort() {
    const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const category = filterCategory ? filterCategory.value : 'all';
    const stock = filterStock ? filterStock.value : 'all';
    let visibleCount = 0;

    productCards.forEach(item => {
      const name = item.getAttribute('data-name') || '';
      const cat = item.getAttribute('data-category') || '';
      const itemStock = parseInt(item.getAttribute('data-stock') || '0');

      const matchesSearch = !query || name.includes(query);
      const matchesCategory = category === 'all' || cat === category;
      let matchesStock = true;

      if (stock === 'in_stock') {
        matchesStock = itemStock > 0;
      } else if (stock === 'out_stock') {
        matchesStock = itemStock === 0;
      }

      if (matchesSearch && matchesCategory && matchesStock) {
        item.classList.remove('d-none');
        visibleCount++;
      } else {
        item.classList.add('d-none');
      }
    });

    updateFilterCounter(visibleCount);
  }

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

  // Ordenamiento
  if (sortCatalog) {
    sortCatalog.addEventListener('change', () => {
      const container = document.getElementById('catalogProductsGrid');
      if (!container) return;

      const cardsArray = Array.from(productCards);
      const val = sortCatalog.value;

      cardsArray.sort((a, b) => {
        const priceA = parseFloat(a.getAttribute('data-price') || 0);
        const priceB = parseFloat(b.getAttribute('data-price') || 0);
        const nameA = a.getAttribute('data-name') || '';
        const nameB = b.getAttribute('data-name') || '';

        if (val === 'price_asc') return priceA - priceB;
        if (val === 'price_desc') return priceB - priceA;
        if (val === 'name_asc') return nameA.localeCompare(nameB);
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
      if (sortCatalog) sortCatalog.value = 'newest';

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
