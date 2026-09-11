/**
 * Module: Currency Helpers (Estandarización Universal de Moneda)
 * Algodón Nórdico Design System
 * 
 * Responsabilidad:
 * 1. Conversión bidireccional exacta: céntimos (entero SQLite) <-> pesos (decimal MXN).
 * 2. Formateo consistente de moneda mexicana ($X,XXX.XX MXN) con prevención de errores de punto flotante.
 * 3. Sanitización y parseo de entradas de texto monetarias.
 */

/**
 * Convierte un monto en céntimos enteros (almacenamiento SQLite) a pesos flotantes.
 * @param {number|string} cents - Entero en céntimos (ej. 45000)
 * @returns {number} Monto decimal en pesos (ej. 450.00)
 */
export function centsToPesos(cents) {
  const num = parseInt(cents, 10);
  if (isNaN(num)) return 0;
  return num / 100;
}

/**
 * Convierte un monto en pesos decimales a céntimos enteros para almacenamiento seguro en SQLite.
 * Utiliza Math.round para blindar contra imprecisiones de punto flotante de IEEE 754.
 * @param {number|string} pesos - Número decimal o cadena (ej. 450.50)
 * @returns {number} Entero en céntimos (ej. 45050)
 */
export function pesosToCents(pesos) {
  if (typeof pesos === 'string') {
    pesos = parseCurrency(pesos);
  }
  const num = parseFloat(pesos);
  if (isNaN(num)) return 0;
  return Math.round(num * 100);
}

/**
 * Formatea un monto en céntimos enteros directamente a cadena legible ($450.00 MXN).
 * @param {number|string} cents - Céntimos enteros (ej. 45000)
 * @param {boolean} includeCode - Si incluye el sufijo ' MXN'
 * @returns {string} Cadena formateada
 */
export function formatCents(cents, includeCode = true) {
  return formatCurrency(centsToPesos(cents), { isCents: false, includeCode });
}

/**
 * Formatea un monto en pesos a cadena legible ($450.00 MXN).
 * @param {number|string} pesos - Pesos (ej. 450)
 * @param {boolean} includeCode - Si incluye el sufijo ' MXN'
 * @returns {string} Cadena formateada
 */
export function formatPesos(pesos, includeCode = true) {
  return formatCurrency(pesos, { isCents: false, includeCode });
}

/**
 * Formateador universal para cualquier valor monetario con opciones flexibles.
 * @param {number|string} amount - Monto numérico o texto
 * @param {Object} options - Configuración de formateo
 * @param {boolean} options.isCents - Indica si amount viene en céntimos
 * @param {boolean} options.includeCode - Agrega sufijo ' MXN' (default: true)
 * @param {boolean} options.includeSymbol - Agrega prefijo '$' (default: true)
 * @returns {string} Cadena formateada
 */
export function formatCurrency(amount, { isCents = false, includeCode = true, includeSymbol = true } = {}) {
  let val = isCents ? centsToPesos(amount) : (parseFloat(amount) || 0);
  if (isNaN(val)) val = 0;

  const formatted = val.toLocaleString('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  const prefix = includeSymbol ? '$' : '';
  const suffix = includeCode ? ' MXN' : '';
  return `${prefix}${formatted}${suffix}`;
}

/**
 * Parsea cualquier cadena de texto con símbolos ($450.00 MXN, 1,200.50, etc.) a un número flotante en pesos.
 * @param {string|number} str - Cadena a parsear
 * @returns {number} Valor numérico en pesos
 */
export function parseCurrency(str) {
  if (typeof str === 'number') return str;
  if (!str) return 0;
  const clean = String(str).replace(/[^0-9.-]+/g, '');
  const val = parseFloat(clean);
  return isNaN(val) ? 0 : val;
}
