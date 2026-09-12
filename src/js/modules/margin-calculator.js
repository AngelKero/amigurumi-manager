/**
 * Module: Margin Calculator (Simulador Dual de Rentabilidad y Retorno por Hora)
 * Single Responsibility: Cálculo en tiempo real del margen de utilidad (%) y retorno horario ($/hr),
 * con sistema de desenfoque total y escudo protector hasta completar campos requeridos.
 */

import { formatPesos } from './currency.js';

export function initMarginCalculator() {
  // Campos del Simulador Financiero
  const inputPrecio = document.getElementById('inputPrecio');
  const inputCosto = document.getElementById('inputCosto');
  const inputHoras = document.getElementById('inputHoras');

  if (!inputPrecio || !inputCosto || !inputHoras) return;

  // Campos Requeridos del Formulario de Creación
  const inputNombre = document.getElementById('inputNombre');
  const inputCategoria = document.getElementById('inputCategoria');
  const inputMaterial = document.getElementById('inputMaterial');
  const inputDimensiones = document.getElementById('inputDimensiones');
  const inputStock = document.getElementById('inputStock');

  // Elementos del Escudo Protector y Desenfoque Minimalista (Tarjeta Completa)
  const simuladorCardInner = document.getElementById('simuladorCardInner') || document.getElementById('simuladorBody');
  const simuladorBlurShield = document.getElementById('simuladorBlurShield');

  // Elementos de Presentación de Cifras
  const displayPrecio = document.getElementById('calcDisplayPrecio');
  const displayCosto = document.getElementById('calcDisplayCosto');
  const displayGanancia = document.getElementById('calcDisplayGanancia');
  const displayMargen = document.getElementById('calcDisplayMargen');
  const displayRetorno = document.getElementById('calcDisplayRetorno');
  const badgeMargenStatus = document.getElementById('badgeMargenStatus');
  const badgeRetornoStatus = document.getElementById('badgeRetornoStatus');

  /**
   * Valida si los datos físicos de la creación están completos
   * (Nombre >= 2, Categoría no vacía, Material >= 3, Dimensiones >= 2, Stock >= 0)
   */
  function checkSpecsValid() {
    if (!inputNombre || !inputCategoria || !inputMaterial || !inputDimensiones || !inputStock) {
      return true;
    }
    const nombreVal = inputNombre.value.trim();
    const catVal = inputCategoria.value.trim();
    const matVal = inputMaterial.value.trim();
    const dimVal = inputDimensiones.value.trim();
    const stockVal = inputStock.value.trim();

    const isNombreValid = nombreVal.length >= 2;
    const isCatValid = catVal.length > 0;
    const isMatValid = matVal.length >= 3;
    const isDimValid = dimVal.length >= 2;
    const isStockValid = stockVal !== '' && !isNaN(stockVal) && parseInt(stockVal, 10) >= 0;

    return isNombreValid && isCatValid && isMatValid && isDimValid && isStockValid;
  }

  /**
   * Valida si los parámetros económicos están completos (Precio > 0 y Costo >= 0)
   */
  function checkParamsValid() {
    const precioVal = inputPrecio.value.trim();
    const costoVal = inputCosto.value.trim();

    const isPrecioValid = precioVal !== '' && !isNaN(precioVal) && parseFloat(precioVal) > 0;
    const isCostoValid = costoVal !== '' && !isNaN(costoVal) && parseFloat(costoVal) >= 0;

    return isPrecioValid && isCostoValid;
  }

  /**
   * Valida si el tiempo de labor está completo (Horas > 0)
   */
  function checkLaborValid() {
    const horasVal = inputHoras.value.trim();
    return horasVal !== '' && !isNaN(horasVal) && parseFloat(horasVal) > 0;
  }

  /**
   * Actualiza el estado visual del desenfoque del simulador
   */
  function updateLockState() {
    const isSpecsValid = checkSpecsValid();
    const isParamsValid = checkParamsValid();
    const isLaborValid = checkLaborValid();

    const isFormComplete = isSpecsValid && isParamsValid && isLaborValid;

    // Desbloquear o desenfocar tarjeta completa del simulador
    if (isFormComplete) {
      if (simuladorCardInner) {
        simuladorCardInner.classList.remove('is-locked');
      }
      if (simuladorBlurShield) {
        simuladorBlurShield.classList.add('is-unlocked');
        simuladorBlurShield.setAttribute('aria-hidden', 'true');
      }
    } else {
      if (simuladorCardInner) {
        simuladorCardInner.classList.add('is-locked');
      }
      if (simuladorBlurShield) {
        simuladorBlurShield.classList.remove('is-unlocked');
        simuladorBlurShield.setAttribute('aria-hidden', 'false');
      }
    }

    return isFormComplete;
  }

  function calculateMargin() {
    const isUnlocked = updateLockState();

    const precio = parseFloat(inputPrecio.value) || 0;
    const costo = parseFloat(inputCosto.value) || 0;
    const horas = parseFloat(inputHoras.value) || 0;

    const ganancia = precio - costo;
    const margenPorcentaje = precio > 0 ? ((ganancia / precio) * 100) : 0;
    const retornoHora = horas > 0 ? (ganancia / horas) : 0;

    // Actualizar cifras con helpers monetarios estandarizados
    if (displayPrecio) displayPrecio.textContent = formatPesos(precio);
    if (displayCosto) displayCosto.textContent = `- ${formatPesos(costo)}`;
    if (displayGanancia) {
      displayGanancia.textContent = formatPesos(ganancia);
      displayGanancia.className = ganancia >= 0 ? 'fw-bold font-monospace text-nowrap fs-5 text-success' : 'fw-bold font-monospace text-nowrap fs-5 text-danger';
    }
    if (displayMargen) displayMargen.textContent = `${margenPorcentaje.toFixed(1)}%`;
    if (displayRetorno) displayRetorno.textContent = `${formatPesos(retornoHora, false)}/hr`;

    // 1. Feedback Visual de Margen de Utilidad
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

    // 2. Feedback Visual de Retorno Efectivo por Hora
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

  // Escuchar cambios en todos los campos requeridos para reactividad instantánea
  const watchedInputs = [
    inputNombre,
    inputCategoria,
    inputMaterial,
    inputDimensiones,
    inputStock,
    inputPrecio,
    inputCosto,
    inputHoras
  ].filter(Boolean);

  watchedInputs.forEach(input => {
    input.addEventListener('input', calculateMargin);
    input.addEventListener('change', calculateMargin);
  });

  // Re-evaluar en evento de reset del formulario
  const creacionForm = document.getElementById('creacionForm');
  if (creacionForm) {
    creacionForm.addEventListener('reset', () => {
      setTimeout(calculateMargin, 15);
    });
  }

  calculateMargin();
}

