/**
 * Amigurumi Micro-ERP & Catalog - Master Frontend Orchestrator (ES Modules)
 * Clean Architecture Refactor: Encapsulates domain logic into cohesive modules.
 */

import { initAuth } from './modules/auth.js';
import { initCatalog } from './modules/catalog.js';
import { initDetail } from './modules/detail.js';
import { initCheckout } from './modules/checkout.js';
import { initMarginCalculator } from './modules/margin-calculator.js';
import { initDropzone } from './modules/dropzone.js';
import { initOrders } from './modules/orders.js';

document.addEventListener('DOMContentLoaded', () => {
  // Inicialización global de sesión y autenticación
  initAuth();

  // Inicialización condicional basada en los elementos presentes en el DOM
  initCatalog();
  initDetail();
  initCheckout();
  initMarginCalculator();
  initDropzone();
  initOrders();
});
