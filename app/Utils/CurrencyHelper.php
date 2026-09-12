<?php
/**
 * Currency Helper Utility (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Regla de Oro Contable: La base de datos SQLite almacena montos monetarios en
 * centavos enteros (ej. 45000 = $450.00 MXN) para erradicar imprecisiones de coma flotante.
 * Esta clase proporciona conversión bidireccional y enriquecimiento dual para la API REST.
 */

declare(strict_types=1);

namespace App\Utils;

class CurrencyHelper {
    /**
     * Convierte centavos enteros a cadena formateada en pesos mexicanos.
     * Ejemplo: 45000 -> "$450.00 MXN" o "$450.00"
     */
    public static function formatCents(int $cents, bool $includeCurrency = true): string {
        $pesos = $cents / 100.0;
        $formatted = '$' . number_format($pesos, 2, '.', ',');
        return $includeCurrency ? $formatted . ' MXN' : $formatted;
    }

    /**
     * Convierte centavos enteros a número flotante en pesos.
     * Ejemplo: 45000 -> 450.0
     */
    public static function centsToMxn(int $cents): float {
        return round($cents / 100.0, 2);
    }

    /**
     * Convierte cualquier representación de pesos a centavos enteros para almacenar en SQLite.
     * Ejemplo: 450.00 -> 45000, "$450.00 MXN" -> 45000, "180" -> 18000
     */
    public static function mxnToCents(float|int|string $amount): int {
        if (is_string($amount)) {
            // Eliminar signos de moneda, comas y sufijo MXN
            $sanitized = preg_replace('/[^0-9.]/', '', $amount);
            $floatVal = (float)$sanitized;
        } else {
            $floatVal = (float)$amount;
        }
        return (int)round($floatVal * 100);
    }

    /**
     * Formatea un valor en pesos decimales a cadena con formato de moneda.
     * Ejemplo: 450.0 -> "$450.00 MXN"
     */
    public static function formatMxn(float|int $amount, bool $includeCurrency = true): string {
        $formatted = '$' . number_format((float)$amount, 2, '.', ',');
        return $includeCurrency ? $formatted . ' MXN' : $formatted;
    }

    /**
     * Alias de conveniencia para centavos a pesos.
     */
    public static function centsToPesos(int $cents): float {
        return self::centsToMxn($cents);
    }

    /**
     * Alias de conveniencia para pesos a centavos.
     */
    public static function pesosToCents(float|int|string $pesos): int {
        return self::mxnToCents($pesos);
    }

    /**
     * Enriquece un registro de creación con campos monetarios formateados y métricas de margen.
     * 
     * @param array $creacion Registro de creación de la base de datos
     * @return array Registro enriquecido con representación dual y márgenes
     */
    public static function enrichCreation(array $creacion): array {
        $precio = (int)($creacion['precio'] ?? 0);
        $costo  = (int)($creacion['costo_materiales'] ?? 0);
        $horas  = (float)($creacion['horas_tejido'] ?? 0.0);

        $creacion['precio_centavos']   = $precio;
        $creacion['precio_mxn']        = self::centsToMxn($precio);
        $creacion['precio_formateado'] = self::formatCents($precio);

        $creacion['costo_materiales_centavos']   = $costo;
        $creacion['costo_materiales_mxn']        = self::centsToMxn($costo);
        $creacion['costo_materiales_formateado'] = self::formatCents($costo);

        $gananciaCents = $precio - $costo;
        $creacion['ganancia_bruta_centavos']   = $gananciaCents;
        $creacion['ganancia_bruta_mxn']        = self::centsToMxn($gananciaCents);
        $creacion['ganancia_bruta_formateada'] = self::formatCents($gananciaCents);

        $margenPorcentaje = $precio > 0 ? round(($gananciaCents / $precio) * 100, 1) : 0.0;
        $creacion['margen_bruto_porcentaje'] = $margenPorcentaje;

        if ($horas > 0.0) {
            $retornoHora = round(($gananciaCents / 100.0) / $horas, 2);
            $creacion['retorno_por_hora_mxn']        = $retornoHora;
            $creacion['retorno_por_hora_formateado'] = self::formatMxn($retornoHora) . '/h';
        } else {
            $creacion['retorno_por_hora_mxn']        = 0.0;
            $creacion['retorno_por_hora_formateado'] = '$0.00 MXN/h';
        }

        return $creacion;
    }

    /**
     * Enriquece un registro de pedido con campos monetarios formateados.
     * 
     * @param array $pedido Registro de pedido de la base de datos
     * @return array Registro enriquecido con representación dual
     */
    public static function enrichOrder(array $pedido): array {
        $precioFinal = (int)($pedido['precio_final'] ?? 0);

        $pedido['precio_final_centavos']   = $precioFinal;
        $pedido['precio_final_mxn']        = self::centsToMxn($precioFinal);
        $pedido['precio_final_formateado'] = self::formatCents($precioFinal);

        return $pedido;
    }
}
