/**
 * Module: Margin Calculator (Simulador Dual de Rentabilidad y Retorno por Hora)
 * Single Responsibility: Cálculo en tiempo real del margen de utilidad (%) y retorno horario ($/hr).
 */

import { formatPesos } from './currency.js';

export function initMarginCalculator() {
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

  inputPrecio.addEventListener('input', calculateMargin);
  inputCosto.addEventListener('input', calculateMargin);
  inputHoras.addEventListener('input', calculateMargin);

  calculateMargin();
}
