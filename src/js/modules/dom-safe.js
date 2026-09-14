/**
 * Module: DOM Safe (H-004)
 * Algodón Nórdico Design System
 *
 * Utilidades de construcción de DOM seguras frente a XSS.
 * Regla de gobernanza: nunca interpolar datos de usuario/servidor en `innerHTML`.
 * Para markup dinámico usar textContent/createElement o escapeHtml() dentro de
 * plantillas constantes. Solo los contadores numéricos están exentos.
 */

/**
 * Escapa texto para interpolarlo de forma segura dentro de un HTML ya construido.
 * Protege contra la inyección de markup y atributos (contexto de elemento y de
 * atributo delimitado por comillas dobles).
 */
export function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/**
 * Reemplaza el contenido de un elemento con un icono Bootstrap + texto seguro
 * (sin intervención del analizador HTML en el texto dinámico).
 */
export function setIconText(element, iconClass, text) {
  if (!element) return;
  element.replaceChildren();
  const icon = document.createElement('i');
  icon.className = iconClass;
  element.appendChild(icon);
  element.appendChild(document.createTextNode(String(text ?? '')));
}