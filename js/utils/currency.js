/**
 * Currency Utility Module (Amigurumi Micro-ERP)
 * Algodón Nórdico Design System
 * 
 * Regla de Oro: La base de datos almacena montos en centavos enteros (ej. 45000 = $450.00 MXN).
 * Este módulo unifica las conversiones deterministas en el cliente frontend.
 */

/**
 * Convierte centavos enteros a formato de moneda con separador de miles.
 * @param {number} cents - Entero en centavos (ej. 45000)
 * @param {boolean} includeCurrency - Si debe incluir sufijo 'MXN'
 * @returns {string} - Ej: "$450.00 MXN" o "$450.00"
 */
export function formatCents(cents, includeCurrency = true) {
  const numericCents = parseInt(cents, 10) || 0;
  const pesos = numericCents / 100;
  const formatted = '$' + pesos.toLocaleString('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
  return includeCurrency ? `${formatted} MXN` : formatted;
}

/**
 * Convierte centavos enteros a número flotante en pesos.
 * @param {number} cents - Entero en centavos (ej. 45000)
 * @returns {number} - Ej: 450.0
 */
export function centsToMxn(cents) {
  const numericCents = parseInt(cents, 10) || 0;
  return Math.round(numericCents) / 100;
}

/**
 * Convierte pesos (número o cadena) a centavos enteros para la base de datos.
 * @param {number|string} amount - Cantidad en pesos (ej. 450, 450.00, "$450.00 MXN")
 * @returns {number} - Entero en centavos (ej. 45000)
 */
export function mxnToCents(amount) {
  if (typeof amount === 'string') {
    const cleaned = amount.replace(/[^0-9.]/g, '');
    const floatVal = parseFloat(cleaned) || 0;
    return Math.round(floatVal * 100);
  }
  return Math.round((Number(amount) || 0) * 100);
}

/**
 * Formatea una cantidad decimal de pesos a cadena visual.
 * @param {number|string} amount - Cantidad en pesos (ej. 450)
 * @param {boolean} includeCurrency - Si debe incluir sufijo 'MXN'
 * @returns {string} - Ej: "$450.00 MXN"
 */
export function formatMxn(amount, includeCurrency = true) {
  const floatVal = typeof amount === 'string' ? parseFloat(amount.replace(/[^0-9.]/g, '')) || 0 : Number(amount) || 0;
  const formatted = '$' + floatVal.toLocaleString('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
  return includeCurrency ? `${formatted} MXN` : formatted;
}
