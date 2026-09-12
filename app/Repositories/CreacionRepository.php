<?php
/**
 * Creation / Catalog Persistence Repository (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Capa de persistencia para la entidad Creación. Aísla el 100% de las sentencias SQL
 * mediante prepared statements parametrizados sobre SQLite, implementando filtrado multicriterio,
 * ordenamiento dinámico, paginación, borrado lógico universal y agregaciones de artesanos activos.
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use InvalidArgumentException;
use PDO;

class CreacionRepository {
    private PDO $pdo;

    /**
     * Permite inyectar una conexión PDO o utilizar el Singleton Database por defecto.
     */
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    /**
     * Busca una creación por su identificador primario.
     * 
     * @param int $id Identificador de la creación
     * @param bool $onlyActive Filtrar únicamente creaciones activas (default: true)
     * @return array|null Registro completo asociativo o null si no existe
     */
    public function findById(int $id, bool $onlyActive = true): ?array {
        $sql = '
            SELECT 
                c.id, c.artesano_id, c.nombre, c.categoria, c.material, c.dimensiones,
                c.precio, c.costo_materiales, c.cantidad_stock, c.horas_tejido,
                c.descripcion, c.imagen_url, c.es_sobre_encargo, c.activo,
                c.creado_en, c.actualizado_en, c.eliminado_en,
                u.username AS artesano_username
            FROM creaciones c
            INNER JOIN usuarios u ON u.id = c.artesano_id
            WHERE c.id = :id
        ';
        if ($onlyActive) {
            $sql .= ' AND c.activo = 1';
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrateRow($row);
    }

    /**
     * Lista piezas del catálogo con filtros combinables, ordenación y paginación.
     * 
     * @param array $filters Filtros opcionales (categoria, artesano_id, precio_min, precio_max, busqueda, es_sobre_encargo, solo_en_stock, activo)
     * @param int $limit Límite de elementos por página
     * @param int $offset Desplazamiento
     * @param string $orden Criterio de ordenación ('recientes', 'precio_asc', 'precio_desc', 'nombre_asc', 'stock_desc')
     * @return array Lista de creaciones tipadas
     */
    public function listCatalog(array $filters = [], int $limit = 12, int $offset = 0, string $orden = 'recientes'): array {
        [$whereClause, $params] = $this->buildWhereClause($filters);

        $orderSql = match ($orden) {
            'precio_asc'  => 'ORDER BY c.precio ASC, c.id DESC',
            'precio_desc' => 'ORDER BY c.precio DESC, c.id DESC',
            'nombre_asc'  => 'ORDER BY c.nombre COLLATE NOCASE ASC, c.id DESC',
            'stock_desc'  => 'ORDER BY c.cantidad_stock DESC, c.id DESC',
            default       => 'ORDER BY c.id DESC',
        };

        $sql = "
            SELECT 
                c.id, c.artesano_id, c.nombre, c.categoria, c.material, c.dimensiones,
                c.precio, c.costo_materiales, c.cantidad_stock, c.horas_tejido,
                c.descripcion, c.imagen_url, c.es_sobre_encargo, c.activo,
                c.creado_en, c.actualizado_en, c.eliminado_en,
                u.username AS artesano_username
            FROM creaciones c
            INNER JOIN usuarios u ON u.id = c.artesano_id
            {$whereClause}
            {$orderSql}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);

        // Vincular parámetros de filtrado
        foreach ($params as $key => $val) {
            if (is_int($val)) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }

        // Vincular paginación tipada
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrateRow'], $rows);
    }

    /**
     * Cuenta el total de creaciones que satisfacen los filtros de catálogo.
     * 
     * @param array $filters Filtros opcionales
     * @return int Total de coincidencias
     */
    public function countCatalog(array $filters = []): int {
        [$whereClause, $params] = $this->buildWhereClause($filters);

        $sql = "
            SELECT COUNT(*) 
            FROM creaciones c
            INNER JOIN usuarios u ON u.id = c.artesano_id
            {$whereClause}
        ";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            if (is_int($val)) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lista creaciones pertenecientes a un artesano específico.
     * 
     * @param int $artesanoId Identificador del artesano
     * @param int $limit Límite de elementos
     * @param int $offset Desplazamiento
     * @param bool|null $onlyActive true para activas, false para inactivas, null para todas
     * @return array
     */
    public function listByArtisan(int $artesanoId, int $limit = 20, int $offset = 0, ?bool $onlyActive = true): array {
        $filters = [
            'artesano_id' => $artesanoId,
            'activo'      => $onlyActive,
        ];
        return $this->listCatalog($filters, $limit, $offset, 'recientes');
    }

    /**
     * Cuenta creaciones pertenecientes a un artesano específico.
     * 
     * @param int $artesanoId Identificador del artesano
     * @param bool|null $onlyActive true para activas, false para inactivas, null para todas
     * @return int
     */
    public function countByArtisan(int $artesanoId, ?bool $onlyActive = true): int {
        $filters = [
            'artesano_id' => $artesanoId,
            'activo'      => $onlyActive,
        ];
        return $this->countCatalog($filters);
    }

    /**
     * Obtiene la lista de artesanos activos que cuentan con al menos una creación activa en el catálogo.
     * Implementa la especificación de ADR-014 para alimentar el dropdown público del catálogo.
     * 
     * @return array Array de items ['id' => int, 'username' => string, 'total_creaciones' => int]
     */
    public function findActiveArtisansWithCreations(): array {
        $sql = '
            SELECT DISTINCT u.id, u.username, COUNT(c.id) AS total_creaciones
            FROM usuarios u
            INNER JOIN creaciones c ON c.artesano_id = u.id
            WHERE u.activo = 1 AND c.activo = 1
            GROUP BY u.id, u.username
            ORDER BY u.username COLLATE NOCASE ASC
        ';

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();

        return array_map(function ($r) {
            return [
                'id'               => (int)$r['id'],
                'username'         => (string)$r['username'],
                'total_creaciones' => (int)$r['total_creaciones'],
            ];
        }, $rows);
    }

    /**
     * Registra una nueva pieza artesanal en el catálogo.
     * 
     * @param array $data Datos de la creación validados
     * @return int Identificador asignado por SQLite
     */
    public function create(array $data): int {
        $sql = '
            INSERT INTO creaciones (
                artesano_id, nombre, categoria, material, dimensiones,
                precio, costo_materiales, cantidad_stock, horas_tejido,
                descripcion, imagen_url, es_sobre_encargo, activo, creado_en
            ) VALUES (
                :artesano_id, :nombre, :categoria, :material, :dimensiones,
                :precio, :costo_materiales, :cantidad_stock, :horas_tejido,
                :descripcion, :imagen_url, :es_sobre_encargo, 1, datetime("now", "localtime")
            )
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':artesano_id'        => (int)$data['artesano_id'],
            ':nombre'             => trim((string)$data['nombre']),
            ':categoria'          => trim((string)$data['categoria']),
            ':material'           => trim((string)$data['material']),
            ':dimensiones'        => trim((string)$data['dimensiones']),
            ':precio'             => (int)$data['precio'],
            ':costo_materiales'   => (int)($data['costo_materiales'] ?? 0),
            ':cantidad_stock'     => (int)($data['cantidad_stock'] ?? 0),
            ':horas_tejido'       => isset($data['horas_tejido']) && $data['horas_tejido'] !== null ? (float)$data['horas_tejido'] : 0.0,
            ':descripcion'        => isset($data['descripcion']) && trim((string)$data['descripcion']) !== '' ? trim((string)$data['descripcion']) : null,
            ':imagen_url'         => isset($data['imagen_url']) && trim((string)$data['imagen_url']) !== '' ? trim((string)$data['imagen_url']) : null,
            ':es_sobre_encargo'   => !empty($data['es_sobre_encargo']) ? 1 : 0,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Actualiza los datos de una creación existente en el catálogo.
     * 
     * @param int $id Identificador de la creación
     * @param array $data Datos actualizados
     * @return bool True si se actualizó correctamente
     */
    public function update(int $id, array $data): bool {
        $sql = '
            UPDATE creaciones SET
                nombre = :nombre,
                categoria = :categoria,
                material = :material,
                dimensiones = :dimensiones,
                precio = :precio,
                costo_materiales = :costo_materiales,
                cantidad_stock = :cantidad_stock,
                horas_tejido = :horas_tejido,
                descripcion = :descripcion,
                imagen_url = :imagen_url,
                es_sobre_encargo = :es_sobre_encargo,
                actualizado_en = datetime("now", "localtime")
            WHERE id = :id AND activo = 1
        ';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id'                 => $id,
            ':nombre'             => trim((string)$data['nombre']),
            ':categoria'          => trim((string)$data['categoria']),
            ':material'           => trim((string)$data['material']),
            ':dimensiones'        => trim((string)$data['dimensiones']),
            ':precio'             => (int)$data['precio'],
            ':costo_materiales'   => (int)($data['costo_materiales'] ?? 0),
            ':cantidad_stock'     => (int)($data['cantidad_stock'] ?? 0),
            ':horas_tejido'       => isset($data['horas_tejido']) && $data['horas_tejido'] !== null ? (float)$data['horas_tejido'] : 0.0,
            ':descripcion'        => isset($data['descripcion']) && trim((string)$data['descripcion']) !== '' ? trim((string)$data['descripcion']) : null,
            ':imagen_url'         => isset($data['imagen_url']) && trim((string)$data['imagen_url']) !== '' ? trim((string)$data['imagen_url']) : null,
            ':es_sobre_encargo'   => !empty($data['es_sobre_encargo']) ? 1 : 0,
        ]);
    }

    /**
     * Realiza la baja lógica (soft delete) de una creación en el catálogo.
     * Marca activo = 0 y establece la marca temporal de baja en eliminado_en.
     * 
     * @param int $id Identificador de la creación
     * @return bool True si se dio de baja lógicamente
     */
    public function softDelete(int $id): bool {
        $stmt = $this->pdo->prepare('
            UPDATE creaciones 
            SET activo = 0, 
                eliminado_en = datetime("now", "localtime") 
            WHERE id = :id AND activo = 1
        ');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Revierte la baja lógica de una creación previamente retirada del catálogo.
     * Marca activo = 1 y limpia eliminado_en (ADR-015).
     * 
     * @param int $id Identificador de la creación a restaurar
     * @return bool True si se restauró con éxito
     */
    public function restore(int $id): bool {
        $stmt = $this->pdo->prepare('
            UPDATE creaciones 
            SET activo = 1, 
                eliminado_en = NULL, 
                actualizado_en = datetime("now", "localtime") 
            WHERE id = :id AND activo = 0
        ');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Actualiza rápidamente las existencias de inventario físico disponible.
     * 
     * @param int $id Identificador de la creación
     * @param int $newStock Nueva cantidad de stock (0-10000)
     * @return bool True si se actualizó
     */
    public function adjustStock(int $id, int $newStock): bool {
        $stmt = $this->pdo->prepare('
            UPDATE creaciones 
            SET cantidad_stock = :stock, 
                actualizado_en = datetime("now", "localtime") 
            WHERE id = :id AND activo = 1
        ');
        return $stmt->execute([
            ':stock' => max(0, min(10000, $newStock)),
            ':id'    => $id,
        ]);
    }

    /**
     * Conmuta la bandera de modalidad bajo encargo exclusivo.
     * 
     * @param int $id Identificador de la creación
     * @param int $state 1 para bajo encargo, 0 para regular
     * @return bool True si se actualizó
     */
    public function toggleCommission(int $id, int $state): bool {
        $stmt = $this->pdo->prepare('
            UPDATE creaciones 
            SET es_sobre_encargo = :state, 
                actualizado_en = datetime("now", "localtime") 
            WHERE id = :id AND activo = 1
        ');
        return $stmt->execute([
            ':state' => $state ? 1 : 0,
            ':id'    => $id,
        ]);
    }

    /**
     * Construye dinámicamente la cláusula WHERE y el mapa de parámetros a partir del array de filtros.
     * 
     * @param array $filters
     * @return array{0: string, 1: array} Cláusula WHERE y parámetros
     */
    private function buildWhereClause(array $filters): array {
        $clauses = [];
        $params = [];

        // 1. Filtro de estado activo (default: solo piezas activas)
        if (!array_key_exists('activo', $filters) || $filters['activo'] === true || $filters['activo'] === 1 || $filters['activo'] === '1') {
            $clauses[] = 'c.activo = 1';
        } elseif ($filters['activo'] === false || $filters['activo'] === 0 || $filters['activo'] === '0') {
            $clauses[] = 'c.activo = 0';
        } // Si es null o 'todos', no se agrega condición de activo

        // 2. Filtro por categoría exacta
        if (!empty($filters['categoria'])) {
            $clauses[] = 'c.categoria = :categoria';
            $params[':categoria'] = trim((string)$filters['categoria']);
        }

        // 3. Filtro por artesano creador
        if (!empty($filters['artesano_id']) && (int)$filters['artesano_id'] > 0) {
            $clauses[] = 'c.artesano_id = :artesano_id';
            $params[':artesano_id'] = (int)$filters['artesano_id'];
        }

        // 4. Filtro por rango de precio (en centavos)
        if (isset($filters['precio_min']) && $filters['precio_min'] !== '' && (int)$filters['precio_min'] >= 0) {
            $clauses[] = 'c.precio >= :precio_min';
            $params[':precio_min'] = (int)$filters['precio_min'];
        }

        if (isset($filters['precio_max']) && $filters['precio_max'] !== '' && (int)$filters['precio_max'] > 0) {
            $clauses[] = 'c.precio <= :precio_max';
            $params[':precio_max'] = (int)$filters['precio_max'];
        }

        // 5. Búsqueda por texto (nombre, material, descripción)
        if (!empty($filters['busqueda'])) {
            $clauses[] = '(c.nombre LIKE :busqueda OR c.material LIKE :busqueda OR c.descripcion LIKE :busqueda)';
            $params[':busqueda'] = '%' . trim((string)$filters['busqueda']) . '%';
        }

        // 6. Filtro de modalidad bajo encargo
        if (isset($filters['es_sobre_encargo']) && $filters['es_sobre_encargo'] !== '') {
            $clauses[] = 'c.es_sobre_encargo = :es_sobre_encargo';
            $params[':es_sobre_encargo'] = !empty($filters['es_sobre_encargo']) ? 1 : 0;
        }

        // 7. Filtro de existencias físicas disponibles
        if (!empty($filters['solo_en_stock'])) {
            $clauses[] = 'c.cantidad_stock > 0';
        }

        $whereClause = !empty($clauses) ? 'WHERE ' . implode(' AND ', $clauses) : '';
        return [$whereClause, $params];
    }

    /**
     * Normaliza los tipos de datos de una fila asociativa y anida los datos del artesano.
     * 
     * @param array $row Fila cruda de SQLite
     * @return array Fila con casting tipado estricto
     */
    private function hydrateRow(array $row): array {
        $artesanoId = (int)$row['artesano_id'];
        $artesanoUsername = (string)($row['artesano_username'] ?? '');

        unset($row['artesano_username']);

        $row['id']               = (int)$row['id'];
        $row['artesano_id']      = $artesanoId;
        $row['precio']           = (int)$row['precio'];
        $row['costo_materiales'] = (int)$row['costo_materiales'];
        $row['cantidad_stock']   = (int)$row['cantidad_stock'];
        $row['horas_tejido']     = $row['horas_tejido'] !== null ? (float)$row['horas_tejido'] : 0.0;
        $row['es_sobre_encargo'] = (int)$row['es_sobre_encargo'];
        $row['activo']           = (int)$row['activo'];

        $row['artesano'] = [
            'id'       => $artesanoId,
            'username' => $artesanoUsername,
        ];

        return $row;
    }
}
