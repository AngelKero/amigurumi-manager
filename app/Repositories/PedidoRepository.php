<?php
/**
 * Order & Commission Persistence Repository (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Capa de persistencia para la entidad Pedido. Aísla el 100% del SQL parametrizado
 * sobre SQLite, implementando transacciones atómicas de reserva y restitución de stock,
 * aislamiento multi-artesano por autoría de creación, filtros avanzados, paginación y
 * borrado lógico universal.
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class PedidoRepository {
    private PDO $pdo;

    /**
     * Permite inyectar una conexión PDO o utilizar el Singleton Database por defecto.
     */
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    /**
     * Busca un pedido por su identificador primario con hidratación de creación y artesano.
     * 
     * @param int $id Identificador del pedido
     * @param bool $onlyActive Filtrar únicamente pedidos activos (default: true)
     * @return array|null Registro asociativo hidratado o null si no existe
     */
    public function findById(int $id, bool $onlyActive = true): ?array {
        $sql = '
            SELECT 
                p.id, p.cliente_nombre, p.cliente_contacto, p.creacion_id, p.cantidad,
                p.fecha_entrega, p.estado_pedido, p.estado_pago, p.precio_final,
                p.notas, p.activo, p.creado_en, p.actualizado_en, p.eliminado_en,
                c.nombre AS creacion_nombre,
                c.imagen_url AS creacion_imagen_url,
                c.precio AS creacion_precio,
                c.costo_materiales AS creacion_costo,
                c.cantidad_stock AS creacion_cantidad_stock,
                c.es_sobre_encargo AS creacion_es_sobre_encargo,
                c.artesano_id AS creacion_artesano_id,
                u.username AS creacion_artesano_username
            FROM pedidos p
            INNER JOIN creaciones c ON c.id = p.creacion_id
            INNER JOIN usuarios u ON u.id = c.artesano_id
            WHERE p.id = :id
        ';
        if ($onlyActive) {
            $sql .= ' AND p.activo = 1';
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrateOrder($row);
    }

    /**
     * Lista pedidos con filtrado multicriterio, paginación y aislamiento multi-artesano.
     * 
     * @param array $filters Filtros opcionales (estado_pedido, estado_pago, creacion_id, busqueda, activo, orden)
     * @param int $limit Número de registros por página
     * @param int $offset Desplazamiento para paginación
     * @param int|null $artesanoId Si se provee, restringe pedidos a creaciones de dicho artesano
     * @param bool $onlyActive Filtrar únicamente pedidos activos (default: true)
     * @return array Lista de pedidos hidratados
     */
    public function listAll(
        array $filters,
        int $limit,
        int $offset,
        ?int $artesanoId = null,
        bool $onlyActive = true
    ): array {
        $params = [];
        $where = [];

        // 1. Aislamiento por artesano (creador de la pieza)
        if ($artesanoId !== null) {
            $where[] = 'c.artesano_id = :artesano_id';
            $params[':artesano_id'] = $artesanoId;
        }

        // 2. Estado de actividad (baja lógica)
        if (isset($filters['activo'])) {
            $where[] = 'p.activo = :activo';
            $params[':activo'] = (int)$filters['activo'];
        } elseif ($onlyActive) {
            $where[] = 'p.activo = 1';
        }

        // 3. Filtro por estado del pedido
        if (!empty($filters['estado_pedido'])) {
            $where[] = 'p.estado_pedido = :estado_pedido';
            $params[':estado_pedido'] = trim((string)$filters['estado_pedido']);
        }

        // 4. Filtro por estado de pago
        if (!empty($filters['estado_pago'])) {
            $where[] = 'p.estado_pago = :estado_pago';
            $params[':estado_pago'] = trim((string)$filters['estado_pago']);
        }

        // 5. Filtro por creación vinculada
        if (!empty($filters['creacion_id'])) {
            $where[] = 'p.creacion_id = :creacion_id';
            $params[':creacion_id'] = (int)$filters['creacion_id'];
        }

        // 6. Búsqueda de texto (cliente, contacto, notas, nombre de creación)
        if (!empty($filters['busqueda'])) {
            $where[] = '(p.cliente_nombre LIKE :busqueda OR p.cliente_contacto LIKE :busqueda OR p.notas LIKE :busqueda OR c.nombre LIKE :busqueda)';
            $params[':busqueda'] = '%' . trim((string)$filters['busqueda']) . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // 7. Ordenación dinámica
        $orden = $filters['orden'] ?? 'recientes';
        switch ($orden) {
            case 'antiguos':
                $orderSql = 'ORDER BY p.id ASC';
                break;
            case 'entrega_asc':
                $orderSql = 'ORDER BY CASE WHEN p.fecha_entrega IS NULL THEN 1 ELSE 0 END, p.fecha_entrega ASC, p.id DESC';
                break;
            case 'precio_desc':
                $orderSql = 'ORDER BY p.precio_final DESC, p.id DESC';
                break;
            case 'precio_asc':
                $orderSql = 'ORDER BY p.precio_final ASC, p.id DESC';
                break;
            case 'recientes':
            default:
                $orderSql = 'ORDER BY p.id DESC';
                break;
        }

        $sql = "
            SELECT 
                p.id, p.cliente_nombre, p.cliente_contacto, p.creacion_id, p.cantidad,
                p.fecha_entrega, p.estado_pedido, p.estado_pago, p.precio_final,
                p.notas, p.activo, p.creado_en, p.actualizado_en, p.eliminado_en,
                c.nombre AS creacion_nombre,
                c.imagen_url AS creacion_imagen_url,
                c.precio AS creacion_precio,
                c.costo_materiales AS creacion_costo,
                c.cantidad_stock AS creacion_cantidad_stock,
                c.es_sobre_encargo AS creacion_es_sobre_encargo,
                c.artesano_id AS creacion_artesano_id,
                u.username AS creacion_artesano_username
            FROM pedidos p
            INNER JOIN creaciones c ON c.id = p.creacion_id
            INNER JOIN usuarios u ON u.id = c.artesano_id
            {$whereSql}
            {$orderSql}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->hydrateOrder($row);
        }

        return $result;
    }

    /**
     * Cuenta el total de pedidos que coinciden con los criterios de filtrado.
     * 
     * @param array $filters Mismos filtros que listAll
     * @param int|null $artesanoId Si se provee, restringe al artesano autor
     * @param bool $onlyActive Filtrar únicamente pedidos activos
     * @return int Cantidad total de registros
     */
    public function countAll(
        array $filters,
        ?int $artesanoId = null,
        bool $onlyActive = true
    ): int {
        $params = [];
        $where = [];

        if ($artesanoId !== null) {
            $where[] = 'c.artesano_id = :artesano_id';
            $params[':artesano_id'] = $artesanoId;
        }

        if (isset($filters['activo'])) {
            $where[] = 'p.activo = :activo';
            $params[':activo'] = (int)$filters['activo'];
        } elseif ($onlyActive) {
            $where[] = 'p.activo = 1';
        }

        if (!empty($filters['estado_pedido'])) {
            $where[] = 'p.estado_pedido = :estado_pedido';
            $params[':estado_pedido'] = trim((string)$filters['estado_pedido']);
        }

        if (!empty($filters['estado_pago'])) {
            $where[] = 'p.estado_pago = :estado_pago';
            $params[':estado_pago'] = trim((string)$filters['estado_pago']);
        }

        if (!empty($filters['creacion_id'])) {
            $where[] = 'p.creacion_id = :creacion_id';
            $params[':creacion_id'] = (int)$filters['creacion_id'];
        }

        if (!empty($filters['busqueda'])) {
            $where[] = '(p.cliente_nombre LIKE :busqueda OR p.cliente_contacto LIKE :busqueda OR p.notas LIKE :busqueda OR c.nombre LIKE :busqueda)';
            $params[':busqueda'] = '%' . trim((string)$filters['busqueda']) . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT COUNT(*) 
            FROM pedidos p
            INNER JOIN creaciones c ON c.id = p.creacion_id
            {$whereSql}
        ";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Registra un pedido dentro de una transacción atómica con validación y decremento de stock.
     * 
     * @param array $pedidoData Datos del pedido (cliente_nombre, cliente_contacto, creacion_id, cantidad, fecha_entrega, estado_pedido, estado_pago, notas)
     * @param bool $isCustomOrder Si true, no valida ni descuenta stock físico (creación bajo encargo)
     * @return int Identificador del nuevo pedido creado
     * @throws RuntimeException Si la creación no existe, está inactiva, o si el stock es insuficiente
     */
    public function createAtomic(array $pedidoData, bool $isCustomOrder = false): int {
        $creacionId = (int)($pedidoData['creacion_id'] ?? 0);
        $cantidad = (int)($pedidoData['cantidad'] ?? 1);

        if ($creacionId <= 0) {
            throw new InvalidArgumentException('El campo creacion_id debe ser un entero positivo.', 422);
        }
        if ($cantidad < 1 || $cantidad > 1000) {
            throw new InvalidArgumentException('La cantidad solicitada debe ser entre 1 y 1,000 unidades.', 422);
        }

        $startedTransaction = false;
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
            $startedTransaction = true;
        }

        try {
            // 1. Bloquear y leer la creación vinculada
            $stmtCreacion = $this->pdo->prepare('
                SELECT id, nombre, precio, cantidad_stock, es_sobre_encargo, activo 
                FROM creaciones 
                WHERE id = :id 
                LIMIT 1
            ');
            $stmtCreacion->execute([':id' => $creacionId]);
            $creacion = $stmtCreacion->fetch(PDO::FETCH_ASSOC);

            if (!is_array($creacion) || (int)$creacion['activo'] !== 1) {
                throw new RuntimeException('La creación especificada no existe o no se encuentra activa en el catálogo.', 404);
            }

            $esSobreEncargo = (int)$creacion['es_sobre_encargo'] === 1;
            $stockActual = (int)$creacion['cantidad_stock'];

            // 2. Validación y decremento de existencias
            // Solo se descuenta stock físico si NO es sobre encargo
            if (!$isCustomOrder && !$esSobreEncargo) {
                if ($stockActual < $cantidad) {
                    throw new RuntimeException(
                        "Stock insuficiente para entrega inmediata (disponibles: {$stockActual}, solicitadas: {$cantidad}).",
                        409
                    );
                }

                $stmtStock = $this->pdo->prepare('
                    UPDATE creaciones 
                    SET cantidad_stock = cantidad_stock - :cantidad,
                        actualizado_en = datetime("now", "localtime")
                    WHERE id = :id
                ');
                $stmtStock->execute([
                    ':cantidad' => $cantidad,
                    ':id' => $creacionId
                ]);
            }

            // 3. Cálculo de precio final congelado en servidor (precio_unitario * cantidad)
            $precioUnitario = (int)$creacion['precio'];
            $precioFinal = $precioUnitario * $cantidad;

            // 4. Inserción atómica del pedido
            $sqlInsert = '
                INSERT INTO pedidos (
                    cliente_nombre, cliente_contacto, creacion_id, cantidad,
                    fecha_entrega, estado_pedido, estado_pago, precio_final,
                    notas, activo, creado_en
                ) VALUES (
                    :cliente_nombre, :cliente_contacto, :creacion_id, :cantidad,
                    :fecha_entrega, :estado_pedido, :estado_pago, :precio_final,
                    :notas, 1, datetime("now", "localtime")
                )
            ';

            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                ':cliente_nombre'   => trim((string)$pedidoData['cliente_nombre']),
                ':cliente_contacto' => trim((string)($pedidoData['cliente_contacto'] ?? '')),
                ':creacion_id'      => $creacionId,
                ':cantidad'         => $cantidad,
                ':fecha_entrega'    => !empty($pedidoData['fecha_entrega']) ? trim((string)$pedidoData['fecha_entrega']) : null,
                ':estado_pedido'    => !empty($pedidoData['estado_pedido']) ? trim((string)$pedidoData['estado_pedido']) : 'Pendiente',
                ':estado_pago'      => !empty($pedidoData['estado_pago']) ? trim((string)$pedidoData['estado_pago']) : 'Pendiente',
                ':precio_final'     => $precioFinal,
                ':notas'            => !empty($pedidoData['notas']) ? trim((string)$pedidoData['notas']) : null,
            ]);

            $pedidoId = (int)$this->pdo->lastInsertId();

            if ($startedTransaction) {
                $this->pdo->commit();
            }

            return $pedidoId;
        } catch (\Throwable $e) {
            if ($startedTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Actualiza el estado de confección y/o estado de pago de un pedido.
     * 
     * @param int $id Identificador del pedido
     * @param string $estadoPedido Nuevo estado ('Pendiente', 'En Proceso', 'Entregado', 'Cancelado')
     * @param string|null $estadoPago Nuevo estado de pago opcional ('Pendiente', 'Anticipo 50%', 'Liquidado')
     * @return bool True si se actualizó el registro
     */
    public function updateStatus(int $id, string $estadoPedido, ?string $estadoPago = null): bool {
        $allowedEstados = ['Pendiente', 'En Proceso', 'Entregado', 'Cancelado'];
        if (!in_array($estadoPedido, $allowedEstados, true)) {
            throw new InvalidArgumentException("Estado de pedido no válido: '{$estadoPedido}'.", 422);
        }

        $params = [
            ':id' => $id,
            ':estado_pedido' => $estadoPedido
        ];

        $setPayment = '';
        if ($estadoPago !== null) {
            $allowedPagos = ['Pendiente', 'Anticipo 50%', 'Liquidado'];
            if (!in_array($estadoPago, $allowedPagos, true)) {
                throw new InvalidArgumentException("Estado de pago no válido: '{$estadoPago}'.", 422);
            }
            $setPayment = ', estado_pago = :estado_pago';
            $params[':estado_pago'] = $estadoPago;
        }

        $sql = "
            UPDATE pedidos 
            SET estado_pedido = :estado_pedido
                {$setPayment},
                actualizado_en = datetime('now', 'localtime')
            WHERE id = :id AND activo = 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Cancela un pedido de forma atómica e idempotente, restituyendo las unidades a inventario.
     * 
     * @param int $id Identificador del pedido
     * @return array Resumen de la cancelación y unidades restituidas
     * @throws RuntimeException Si el pedido no existe o ya se encuentra cancelado
     */
    public function cancelOrderAtomic(int $id): array {
        $startedTransaction = false;
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
            $startedTransaction = true;
        }

        try {
            // 1. Obtener pedido con datos de la creación
            $stmt = $this->pdo->prepare('
                SELECT 
                    p.id, p.creacion_id, p.cantidad, p.estado_pedido, p.activo,
                    c.nombre AS creacion_nombre, c.cantidad_stock, c.es_sobre_encargo
                FROM pedidos p
                INNER JOIN creaciones c ON c.id = p.creacion_id
                WHERE p.id = :id
                LIMIT 1
            ');
            $stmt->execute([':id' => $id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($pedido)) {
                throw new RuntimeException("El pedido #{$id} no existe.", 404);
            }

            // 2. Validación de Idempotencia: Si ya está cancelado, abortar con HTTP 409
            if ($pedido['estado_pedido'] === 'Cancelado') {
                throw new RuntimeException("El pedido #{$id} ya se encuentra cancelado.", 409);
            }

            $cantidad = (int)$pedido['cantidad'];
            $creacionId = (int)$pedido['creacion_id'];
            $stockActual = (int)$pedido['cantidad_stock'];
            $esSobreEncargo = (int)$pedido['es_sobre_encargo'] === 1;

            // 3. Restituir inventario físico (ADR-009)
            // Nota: Para piezas en catálogo físico, las unidades reservadas regresan a inventario
            $unidadesRestituidas = $cantidad;
            $stmtStock = $this->pdo->prepare('
                UPDATE creaciones 
                SET cantidad_stock = cantidad_stock + :cantidad,
                    actualizado_en = datetime("now", "localtime")
                WHERE id = :id
            ');
            $stmtStock->execute([
                ':cantidad' => $cantidad,
                ':id' => $creacionId
            ]);
            $nuevoStock = $stockActual + $cantidad;

            // 4. Marcar pedido como cancelado
            $stmtPedido = $this->pdo->prepare('
                UPDATE pedidos 
                SET estado_pedido = "Cancelado",
                    actualizado_en = datetime("now", "localtime")
                WHERE id = :id
            ');
            $stmtPedido->execute([':id' => $id]);

            if ($startedTransaction) {
                $this->pdo->commit();
            }

            return [
                'id'                   => $id,
                'estado_pedido'        => 'Cancelado',
                'unidades_restituidas' => $unidadesRestituidas,
                'creacion_id'          => $creacionId,
                'creacion_nombre'      => (string)$pedido['creacion_nombre'],
                'nuevo_stock'          => $nuevoStock,
                'actualizado_en'       => date('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            if ($startedTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Realiza borrado lógico de un pedido (activo = 0, eliminado_en = timestamp).
     * 
     * @param int $id Identificador del pedido
     * @return bool True si se inactivó el registro
     */
    public function softDelete(int $id): bool {
        $stmt = $this->pdo->prepare('
            UPDATE pedidos 
            SET activo = 0, 
                eliminado_en = datetime("now", "localtime"),
                actualizado_en = datetime("now", "localtime")
            WHERE id = :id AND activo = 1
        ');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Reactiva un pedido previamente dado de baja lógica.
     * 
     * @param int $id Identificador del pedido
     * @return bool True si se reactivó el registro
     */
    public function restore(int $id): bool {
        $stmt = $this->pdo->prepare('
            UPDATE pedidos 
            SET activo = 1, 
                eliminado_en = NULL,
                actualizado_en = datetime("now", "localtime")
            WHERE id = :id AND activo = 0
        ');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Hidrata una fila cruda de base de datos en una estructura rica y tipada.
     * 
     * @param array $row Fila cruda de base de datos
     * @return array Estructura normalizada
     */
    private function hydrateOrder(array $row): array {
        return [
            'id'               => (int)$row['id'],
            'cliente_nombre'   => (string)$row['cliente_nombre'],
            'cliente_contacto' => (string)$row['cliente_contacto'],
            'creacion_id'      => (int)$row['creacion_id'],
            'cantidad'         => (int)$row['cantidad'],
            'fecha_entrega'    => $row['fecha_entrega'] !== null ? (string)$row['fecha_entrega'] : null,
            'estado_pedido'    => (string)$row['estado_pedido'],
            'estado_pago'      => (string)$row['estado_pago'],
            'precio_final'     => (int)$row['precio_final'],
            'notas'            => $row['notas'] !== null ? (string)$row['notas'] : null,
            'activo'           => (int)$row['activo'],
            'creado_en'        => (string)$row['creado_en'],
            'actualizado_en'   => $row['actualizado_en'] !== null ? (string)$row['actualizado_en'] : null,
            'eliminado_en'     => $row['eliminado_en'] !== null ? (string)$row['eliminado_en'] : null,
            'creacion'         => [
                'id'                 => (int)$row['creacion_id'],
                'nombre'             => (string)$row['creacion_nombre'],
                'imagen_url'         => $row['creacion_imagen_url'] !== null ? (string)$row['creacion_imagen_url'] : null,
                'precio'             => (int)$row['creacion_precio'],
                'costo_materiales'   => (int)($row['creacion_costo'] ?? 0),
                'cantidad_stock'     => (int)($row['creacion_cantidad_stock'] ?? 0),
                'es_sobre_encargo'   => (int)($row['creacion_es_sobre_encargo'] ?? 0),
                'artesano_id'        => (int)$row['creacion_artesano_id'],
                'artesano_username'  => (string)$row['creacion_artesano_username'],
            ]
        ];
    }
}
