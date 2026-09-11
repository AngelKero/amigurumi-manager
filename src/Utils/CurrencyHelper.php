<?php
/**
 * Currency Helper Utility (Amigurumi Micro-ERP)
 * Algodón Nórdico Design System
 * 
 * Regla de Oro: La base de datos almacena números enteros en centavos (ej. 45000 = $450.00 MXN)
 * para evitar imprecisiones de redondeo de punto flotante en cálculos contables.
 */

declare(strict_types=1);

namespace App\Utils;

class CurrencyHelper {
    /**
     * Convierte centavos enteros a cadena formateada en moneda mexicana.
     * Ejemplo: 45000 -> "$450.00 MXN"
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
}
