/**
 * Module: Dialog (Modal & SweetAlert2 Integration — Algodón Nórdico)
 * Sustituto accesible, estético y moderno para window.alert() y window.confirm()
 * respetando el sistema de diseño "Algodón Nórdico" y CSP estricto (H-004).
 */

import { escapeHtml } from './dom-safe.js';

/**
 * Verifica si SweetAlert2 está cargado y disponible en el entorno global.
 * @returns {boolean}
 */
export function hasSwal() {
  return typeof window !== 'undefined' && typeof window.Swal !== 'undefined';
}

/**
 * Diálogo modal de confirmación interactiva (reemplaza window.confirm)
 *
 * @param {Object} options
 * @param {string} options.title - Título principal del modal
 * @param {string} [options.text] - Mensaje en texto plano
 * @param {string} [options.html] - Contenido HTML pre-sanitizado
 * @param {string} [options.icon='warning'] - 'warning' | 'question' | 'info' | 'error'
 * @param {string} [options.confirmText='Confirmar']
 * @param {string} [options.cancelText='Cancelar']
 * @param {boolean} [options.danger=false] - Botón de confirmación en rojo destructivo
 * @returns {Promise<boolean>} Resuelve true si el usuario confirma, false si cancela
 */
export async function confirmModal({
  title,
  text,
  html,
  icon = 'warning',
  confirmText = 'Confirmar',
  cancelText = 'Cancelar',
  danger = false,
}) {
  if (hasSwal()) {
    const swalConfig = {
      title,
      icon,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
      buttonsStyling: false,
      customClass: {
        popup: 'craft-swal-popup',
        confirmButton: danger ? 'btn btn-danger px-4' : 'craft-swal-confirm-btn me-2',
        cancelButton: 'craft-swal-cancel-btn',
      },
      focusCancel: true,
      reverseButtons: true,
    };

    if (html) {
      swalConfig.html = html;
    } else if (text) {
      swalConfig.text = text;
    }

    const result = await window.Swal.fire(swalConfig);
    return !!result.isConfirmed;
  }

  // Fallback seguro si SweetAlert2 no está disponible
  if (typeof window !== 'undefined' && typeof window.confirm === 'function') {
    return window.confirm(`${title}\n\n${text || ''}`);
  }
  return false;
}

/**
 * Modal de éxito (informativo)
 *
 * @param {Object} options
 * @param {string} options.title
 * @param {string} [options.text]
 * @param {string} [options.html]
 * @param {string} [options.confirmText='Aceptar']
 * @returns {Promise<any>}
 */
export async function showSuccessModal({ title, text, html, confirmText = 'Aceptar' }) {
  if (hasSwal()) {
    const config = {
      icon: 'success',
      title,
      buttonsStyling: false,
      customClass: {
        popup: 'craft-swal-popup',
        confirmButton: 'craft-swal-confirm-btn',
      },
      confirmButtonText: confirmText,
    };
    if (html) config.html = html;
    else if (text) config.text = text;
    return await window.Swal.fire(config);
  }
}

/**
 * Modal de error o restricción (informativo)
 *
 * @param {Object} options
 * @param {string} options.title
 * @param {string} [options.text]
 * @param {string} [options.html]
 * @param {string} [options.confirmText='Entendido']
 * @returns {Promise<any>}
 */
export async function showErrorModal({ title, text, html, confirmText = 'Entendido' }) {
  if (hasSwal()) {
    const config = {
      icon: 'error',
      title,
      buttonsStyling: false,
      customClass: {
        popup: 'craft-swal-popup',
        confirmButton: 'craft-swal-confirm-btn',
      },
      confirmButtonText: confirmText,
    };
    if (html) config.html = html;
    else if (text) config.text = text;
    return await window.Swal.fire(config);
  }
}

/**
 * Toast de notificación flotante no intrusivo
 *
 * @param {string} message - Mensaje a mostrar
 * @param {'success'|'error'|'info'|'warning'} [type='success']
 */
export function showToastAlert(message, type = 'success') {
  if (hasSwal()) {
    const Toast = window.Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true,
      customClass: {
        popup: 'craft-swal-toast',
      },
      didOpen: (toast) => {
        toast.onmouseenter = window.Swal.stopTimer;
        toast.onmouseleave = window.Swal.resumeTimer;
      },
    });

    Toast.fire({
      icon: type,
      title: message,
    });
  }
}
