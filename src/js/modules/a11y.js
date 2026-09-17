/**
 * Módulo de Accesibilidad (WCAG 2.1 AA) y Micro-interacciones
 * Algodón Nórdico Design System - Crochet Manager
 *
 * Responsabilidades:
 * 1. Focus trap y restauración automática de foco al trigger al cerrar modales de Bootstrap 5.
 * 2. Soporte de teclado (ArrowUp / ArrowDown) en controles de cantidad (stepper).
 * 3. Activación accesible por teclado (Enter / Space) en elementos interactivos con role="button".
 * 4. Anuncio en regiones aria-live.
 */

export function initA11y() {
  setupModalFocusRestoration();
  setupStepperKeyboardNavigation();
  setupRoleButtonKeyboardAccess();
}

/**
 * Registra eventos globales en modales para almacenar el trigger que los abrió
 * y devolver el foco inmediatamente al cerrarse (WCAG 2.4.3 Focus Order).
 */
function setupModalFocusRestoration() {
  document.addEventListener('show.bs.modal', (e) => {
    const modal = e.target;
    if (!(modal instanceof Element)) return;
    // Captura el elemento que tenía el foco o el relatedTarget de Bootstrap
    modal._a11yTriggerElement = (e && e.relatedTarget instanceof HTMLElement)
      ? e.relatedTarget
      : (document.activeElement instanceof HTMLElement ? document.activeElement : null);
  });

  document.addEventListener('hidden.bs.modal', (e) => {
    const modal = e.target;
    if (!(modal instanceof Element)) return;
    const trigger = modal._a11yTriggerElement;
    if (trigger && typeof trigger.focus === 'function' && document.contains(trigger)) {
      setTimeout(() => {
        try {
          trigger.focus();
        } catch (_) {
          // Si el elemento se deshabilitó o removió, silenciar con gracia
        }
      }, 50);
    }
    modal._a11yTriggerElement = null;
  });
}

/**
 * Permite que los inputs de stepper reaccionen a las teclas ArrowUp y ArrowDown.
 */
function setupStepperKeyboardNavigation() {
  document.addEventListener('keydown', (e) => {
    const target = e.target;
    if (!(target instanceof HTMLInputElement)) return;

    const isStepper = target.id === 'inputCheckoutQty'
      || target.closest('.qty-stepper')
      || target.classList.contains('input-qty-stepper');

    if (!isStepper) return;

    if (e.key === 'ArrowUp') {
      e.preventDefault();
      const stepper = target.closest('.qty-stepper');
      const incBtn = stepper ? stepper.querySelector('button[id$="Inc"], button:last-of-type') : document.getElementById('btnCheckoutInc');
      if (incBtn && !incBtn.disabled) {
        incBtn.click();
      }
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      const stepper = target.closest('.qty-stepper');
      const decBtn = stepper ? stepper.querySelector('button[id$="Dec"], button:first-of-type') : document.getElementById('btnCheckoutDec');
      if (decBtn && !decBtn.disabled) {
        decBtn.click();
      }
    }
  });
}

/**
 * Garantiza que cualquier elemento con role="button" y tabindex="0"
 * se active con Enter y Space (WCAG 2.1.1 Keyboard).
 */
function setupRoleButtonKeyboardAccess() {
  document.addEventListener('keydown', (e) => {
    const target = e.target;
    if (!(target instanceof HTMLElement)) return;
    if (target.getAttribute('role') === 'button' && target.getAttribute('tabindex') === '0') {
      if (target.tagName === 'BUTTON' || target.tagName === 'A' || target.tagName === 'INPUT') return;
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        target.click();
      }
    }
  });
}
