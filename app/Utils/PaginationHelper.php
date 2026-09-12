<?php
/**
 * Standardized Pagination Helper (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Gestiona el cálculo unificado de límites, páginas, offsets y la construcción
 * del sobre estándar de metadatos de paginación para la API REST.
 */

declare(strict_types=1);

namespace App\Utils;

use App\Core\Request;

class PaginationHelper {
    /**
     * Extrae y normaliza los parámetros de paginación desde la petición HTTP activa.
     * 
     * @param int $defaultLimit Límite de elementos por página por defecto (ej. 12 para catálogo, 20 para pedidos)
     * @param int $maxLimit Límite máximo permitido para prevenir abusos de memoria
     * @return array{pagina: int, limite: int, offset: int}
     */
    public static function getParams(int $defaultLimit = 12, int $maxLimit = 100): array {
        $rawPage = Request::get('pagina', Request::get('page', 1));
        $rawLimit = Request::get('limite', Request::get('limit', $defaultLimit));

        $page = is_numeric($rawPage) ? (int)$rawPage : 1;
        $limit = is_numeric($rawLimit) ? (int)$rawLimit : $defaultLimit;

        // Validar cotas mínimas y máximas
        if ($page < 1) {
            $page = 1;
        }

        if ($limit < 1) {
            $limit = $defaultLimit;
        } elseif ($limit > $maxLimit) {
            $limit = $maxLimit;
        }

        $offset = ($page - 1) * $limit;

        return [
            'pagina' => $page,
            'limite' => $limit,
            'offset' => $offset,
        ];
    }

    /**
     * Construye el sobre estructurado de metadatos de paginación para las respuestas JSON.
     * 
     * @param int $totalItems Conteo total de elementos que coinciden con los filtros
     * @param int $currentPage Página actual consultada
     * @param int $limit Elementos por página solicitados
     * @return array{paginacion: array{total_items: int, pagina_actual: int, total_paginas: int, limite: int, tiene_siguiente: bool, tiene_anterior: bool}}
     */
    public static function build(int $totalItems, int $currentPage, int $limit): array {
        $totalItems = max(0, $totalItems);
        $limit = max(1, $limit);
        $totalPages = (int)ceil($totalItems / $limit);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        $hasNext = $currentPage < $totalPages;
        $hasPrev = $currentPage > 1;

        return [
            'paginacion' => [
                'total_items'     => $totalItems,
                'pagina_actual'   => $currentPage,
                'total_paginas'   => $totalPages,
                'limite'          => $limit,
                'tiene_siguiente' => $hasNext,
                'tiene_anterior'  => $hasPrev,
            ]
        ];
    }
}
