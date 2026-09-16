<?php
/**
 * WhatsApp Helper Utility (Clean Architecture)
 * Algodón Nórdico Design System
 *
 * Normalización E.164 MX y construcción server-side de enlaces wa.me.
 * Todo enlace WhatsApp de la plataforma nace aquí (cero `wa.me` en frontend).
 */

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;

class WhatsAppHelper {
    /**
     * Longitud máxima del valor crudo (refleja chk_usuarios_whatsapp).
     */
    public const MAX_RAW_LENGTH = 20;

    /**
     * Normaliza un teléfono a dígitos E.164 MX.
     *
     * @param string|null $phone Valor crudo (admite `+`, espacios, guiones)
     * @return string|null Dígitos normalizados o null si no es utilizable
     */
    public static function normalize(?string $phone): ?string {
        if ($phone === null) {
            return null;
        }
        $clean = (string)preg_replace('/[^\d]/', '', trim($phone));
        if ($clean === '' || strlen($clean) < 8 || strlen($clean) > 15) {
            return null;
        }
        // 10 dígitos nacionales (formato mexicano sin código de país) → prefijo 52
        if (strlen($clean) === 10) {
            $clean = '52' . $clean;
        }
        return $clean;
    }

    /**
     * Valida el valor crudo de WhatsApp de un artesano (opcional).
     *
     * @param string|null $phone Valor crudo o null/vacío (sin número)
     * @return string|null Valor saneado o null si no hay número
     * @throws InvalidArgumentException Si el formato no es válido (HTTP 422)
     */
    public static function sanitizeOptional(?string $phone): ?string {
        if ($phone === null) {
            return null;
        }
        $raw = trim($phone);
        if ($raw === '') {
            return null;
        }
        if (mb_strlen($raw) > self::MAX_RAW_LENGTH) {
            throw new InvalidArgumentException('El WhatsApp no puede exceder los 20 caracteres.', 422);
        }
        if (self::normalize($raw) === null) {
            throw new InvalidArgumentException('El WhatsApp debe contener entre 8 y 15 dígitos válidos.', 422);
        }
        return $raw;
    }

    /**
     * Construye un enlace wa.me con mensaje pre-redactado codificado.
     *
     * @param string|null $phone Teléfono destino (crudo o normalizado)
     * @param string $message Mensaje en claro (se codifica con rawurlencode)
     * @return string|null URL o null si el teléfono no es utilizable
     */
    public static function link(?string $phone, string $message): ?string {
        $normalized = self::normalize($phone);
        if ($normalized === null) {
            return null;
        }
        return 'https://wa.me/' . $normalized . '?text=' . rawurlencode($message);
    }
}
