<?php
/**
 * Order & Commission Business Service (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Orquesta la lógica de negocio para pedidos y encargos en crochet:
 * validaciones estrictas de entrada, transacciones atómicas de stock, aislamiento
 * multi-artesano por autoría, enlaces dinámicos de WhatsApp para contacto directo,
 * cálculo seguro de precio final en servidor y cancelación idempotente con restitución
 * automática de inventario físico.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\CreacionRepository;
use App\Repositories\PedidoRepository;
use App\Utils\CurrencyHelper;
use App\Utils\PaginationHelper;
use InvalidArgumentException;
use RuntimeException;

class PedidoService {
    private PedidoRepository $pedidoRepo;
    private CreacionRepository $creacionRepo;

    public function __construct(
        ?PedidoRepository $pedidoRepo = null,
        ?CreacionRepository $creacionRepo = null
    ) {
        $this->pedidoRepo = $pedidoRepo ?? new PedidoRepository();
        $this->creacionRepo = $creacionRepo ?? new CreacionRepository();
    }

    /**
     * Consulta el listado paginado de pedidos con aislamiento por artesano y enriquecimiento.
     * 
     * @param array $query Parámetros de filtrado (estado_pedido, estado_pago, creacion_id, busqueda, orden, pagina, limite)
     * @param array $currentUser Usuario autenticado en sesión
     * @return array{datos: array, paginacion: array}
     */
    public function listOrders(array $query, array $currentUser): array {
        $page = max(1, (int)($query['pagina'] ?? 1));
        $limitDefault = (int)Config::get('pagination.pedidos_default', 20);
        $limitMax = (int)Config::get('pagination.pedidos_max', 50);
        $limit = max(1, min($limitMax, (int)($query['limite'] ?? $limitDefault)));
        $offset = ($page - 1) * $limit;

        // Aislamiento Multi-Artesano:
        // - Si el usuario es artesano, solo ve pedidos de sus propias piezas
        // - Si es admin o asistente, puede ver todo o filtrar por artesano específico
        $userRole = (string)($currentUser['rol'] ?? 'artesano');
        $artesanoId = null;

        if ($userRole === 'artesano') {
            $artesanoId = (int)$currentUser['id'];
        } elseif (!empty($query['artesano_id'])) {
            $artesanoId = (int)$query['artesano_id'];
        }

        $filters = [];
        if (!empty($query['estado_pedido'])) {
            $filters['estado_pedido'] = trim((string)$query['estado_pedido']);
        }
        if (!empty($query['estado_pago'])) {
            $filters['estado_pago'] = trim((string)$query['estado_pago']);
        }
        if (!empty($query['creacion_id'])) {
            $filters['creacion_id'] = (int)$query['creacion_id'];
        }
        if (!empty($query['busqueda'])) {
            $filters['busqueda'] = trim((string)$query['busqueda']);
        }
        if (!empty($query['orden'])) {
            $filters['orden'] = trim((string)$query['orden']);
        }

        $rawOrders = $this->pedidoRepo->listAll($filters, $limit, $offset, $artesanoId);
        $totalItems = $this->pedidoRepo->countAll($filters, $artesanoId);

        $enrichedOrders = [];
        foreach ($rawOrders as $order) {
            $enrichedOrders[] = $this->enrichOrder($order);
        }

        $paginationEnvelope = PaginationHelper::build($totalItems, $page, $limit);
        $pagination = $paginationEnvelope['paginacion'] ?? $paginationEnvelope;

        return [
            'datos'      => $enrichedOrders,
            'paginacion' => $pagination,
        ];
    }

    /**
     * Obtiene el detalle de un pedido validando autorización de autoría (IDOR).
     * 
     * @param int $id Identificador del pedido
     * @param array $currentUser Usuario autenticado
     * @return array Detalle enriquecido del pedido
     * @throws RuntimeException Si el pedido no existe o si no pertenece al artesano
     */
    public function getOrderById(int $id, array $currentUser): array {
        $order = $this->pedidoRepo->findById($id);

        if ($order === null) {
            throw new RuntimeException("El pedido #{$id} no existe o ha sido desactivado.", 404);
        }

        $this->ensureArtisanOwnership($order, $currentUser);

        return $this->enrichOrder($order);
    }

    /**
     * Procesa una solicitud pública de compra o encargo desde el modal de checkout.
     * 
     * @param array $input Datos de la solicitud (creacion_id, cliente_nombre, cliente_contacto, cantidad, notas)
     * @return array Resumen del pedido registrado
     */
    public function requestPublicOrder(array $input): array {
        $creacionId = (int)($input['creacion_id'] ?? 0);
        $clienteNombre = trim((string)($input['cliente_nombre'] ?? ''));
        $clienteContacto = trim((string)($input['cliente_contacto'] ?? ''));
        $cantidad = (int)($input['cantidad'] ?? 1);
        $notas = isset($input['notas']) ? trim((string)$input['notas']) : null;

        // Validaciones de dominio
        if ($creacionId <= 0) {
            throw new InvalidArgumentException('El campo creacion_id es obligatorio y debe ser positivo.', 422);
        }
        if (mb_strlen($clienteNombre) < 2 || mb_strlen($clienteNombre) > 100) {
            throw new InvalidArgumentException('El nombre del cliente debe tener entre 2 y 100 caracteres.', 422);
        }
        if (mb_strlen($clienteContacto) < 3 || mb_strlen($clienteContacto) > 50) {
            throw new InvalidArgumentException('El medio de contacto (teléfono/WhatsApp) debe tener entre 3 y 50 caracteres.', 422);
        }
        if ($cantidad < 1 || $cantidad > 1000) {
            throw new InvalidArgumentException('La cantidad debe ser entre 1 y 1,000 unidades.', 422);
        }
        if ($notas !== null && mb_strlen($notas) > 1000) {
            throw new InvalidArgumentException('Las notas no pueden exceder 1,000 caracteres.', 422);
        }

        // Consultar creación para determinar si es sobre encargo
        $creacion = $this->creacionRepo->findById($creacionId, true);
        if ($creacion === null) {
            throw new RuntimeException('La creación solicitada no existe o no se encuentra activa en el catálogo.', 404);
        }

        $isCustomOrder = (int)$creacion['es_sobre_encargo'] === 1;

        // Ejecutar transacción atómica de stock y pedido
        $pedidoData = [
            'cliente_nombre'   => $clienteNombre,
            'cliente_contacto' => $clienteContacto,
            'creacion_id'      => $creacionId,
            'cantidad'         => $cantidad,
            'fecha_entrega'    => null,
            'estado_pedido'    => 'Pendiente',
            'estado_pago'      => 'Pendiente',
            'notas'            => $notas,
        ];

        $pedidoId = $this->pedidoRepo->createAtomic($pedidoData, $isCustomOrder);
        $pedidoCreado = $this->pedidoRepo->findById($pedidoId);

        return [
            'id'                      => $pedidoId,
            'creacion_id'             => $creacionId,
            'creacion_nombre'         => $creacion['nombre'],
            'cantidad'                => $cantidad,
            'precio_final'            => $pedidoCreado ? (int)$pedidoCreado['precio_final'] : ((int)$creacion['precio'] * $cantidad),
            'precio_final_formateado' => CurrencyHelper::formatCents($pedidoCreado ? (int)$pedidoCreado['precio_final'] : ((int)$creacion['precio'] * $cantidad)),
            'estado_pedido'           => 'Pendiente',
            'estado_pago'             => 'Pendiente',
            'es_sobre_encargo'        => $isCustomOrder ? 1 : 0,
            'mensaje'                 => $isCustomOrder 
                ? 'Encargo registrado exitosamente. El artesano se pondrá en contacto contigo para coordinar detalles y tiempos de confección.'
                : 'Pedido registrado exitosamente. El artesano se pondrá en contacto contigo para acordar la entrega.',
        ];
    }

    /**
     * Registra un encargo manual por parte del artesano o administrador.
     * 
     * @param array $input Datos del encargo (creacion_id, cliente_nombre, cliente_contacto, cantidad, fecha_entrega, estado_pedido, estado_pago, notas)
     * @param array $currentUser Usuario autenticado
     * @return array Resumen del encargo registrado
     */
    public function createManualOrder(array $input, array $currentUser): array {
        $creacionId = (int)($input['creacion_id'] ?? 0);
        $clienteNombre = trim((string)($input['cliente_nombre'] ?? ''));
        $clienteContacto = trim((string)($input['cliente_contacto'] ?? ''));
        $cantidad = (int)($input['cantidad'] ?? 1);
        $fechaEntrega = !empty($input['fecha_entrega']) ? trim((string)$input['fecha_entrega']) : null;
        $estadoPedido = !empty($input['estado_pedido']) ? trim((string)$input['estado_pedido']) : 'Pendiente';
        $estadoPago = !empty($input['estado_pago']) ? trim((string)$input['estado_pago']) : 'Pendiente';
        $notas = isset($input['notas']) ? trim((string)$input['notas']) : null;

        if ($creacionId <= 0) {
            throw new InvalidArgumentException('El campo creacion_id es obligatorio y debe ser positivo.', 422);
        }
        if (mb_strlen($clienteNombre) < 2 || mb_strlen($clienteNombre) > 100) {
            throw new InvalidArgumentException('El nombre del cliente debe tener entre 2 y 100 caracteres.', 422);
        }
        if (mb_strlen($clienteContacto) > 50) {
            throw new InvalidArgumentException('El contacto no puede exceder 50 caracteres.', 422);
        }
        if ($cantidad < 1 || $cantidad > 1000) {
            throw new InvalidArgumentException('La cantidad debe ser entre 1 y 1,000 unidades.', 422);
        }
        if ($fechaEntrega !== null) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEntrega)) {
                throw new InvalidArgumentException('El formato de fecha_entrega debe ser YYYY-MM-DD.', 422);
            }
        }
        $allowedEstados = ['Pendiente', 'En Proceso', 'Entregado', 'Cancelado'];
        if (!in_array($estadoPedido, $allowedEstados, true)) {
            throw new InvalidArgumentException("Estado de pedido no válido: '{$estadoPedido}'.", 422);
        }
        $allowedPagos = ['Pendiente', 'Anticipo 50%', 'Liquidado'];
        if (!in_array($estadoPago, $allowedPagos, true)) {
            throw new InvalidArgumentException("Estado de pago no válido: '{$estadoPago}'.", 422);
        }
        if ($notas !== null && mb_strlen($notas) > 1000) {
            throw new InvalidArgumentException('Las notas no pueden exceder 1,000 caracteres.', 422);
        }

        // Consultar creación y verificar autorización IDOR
        $creacion = $this->creacionRepo->findById($creacionId, true);
        if ($creacion === null) {
            throw new RuntimeException('La creación solicitada no existe o no se encuentra activa en el catálogo.', 404);
        }

        // Salvaguarda IDOR: Un artesano solo puede registrar pedidos para sus propias piezas
        $userRole = (string)($currentUser['rol'] ?? 'artesano');
        if ($userRole === 'artesano' && (int)$creacion['artesano_id'] !== (int)$currentUser['id']) {
            throw new RuntimeException('No tienes autorización para registrar pedidos sobre creaciones de otros artesanos.', 403);
        }

        $isCustomOrder = (int)$creacion['es_sobre_encargo'] === 1;

        $pedidoData = [
            'cliente_nombre'   => $clienteNombre,
            'cliente_contacto' => $clienteContacto,
            'creacion_id'      => $creacionId,
            'cantidad'         => $cantidad,
            'fecha_entrega'    => $fechaEntrega,
            'estado_pedido'    => $estadoPedido,
            'estado_pago'      => $estadoPago,
            'notas'            => $notas,
        ];

        $pedidoId = $this->pedidoRepo->createAtomic($pedidoData, $isCustomOrder);
        $pedidoCreado = $this->pedidoRepo->findById($pedidoId);

        return $this->enrichOrder($pedidoCreado);
    }

    /**
     * Actualiza el estado del pedido y/o estado de pago con protección IDOR.
     * Si el nuevo estado es 'Cancelado', delega a cancelOrder para restituir inventario.
     * 
     * @param int $id Identificador del pedido
     * @param string $estadoPedido Nuevo estado de confección
     * @param string|null $estadoPago Nuevo estado de cobro
     * @param array $currentUser Usuario autenticado
     * @return array Detalle del pedido actualizado
     */
    public function updateOrderStatus(int $id, string $estadoPedido, ?string $estadoPago, array $currentUser): array {
        $order = $this->pedidoRepo->findById($id);
        if ($order === null) {
            throw new RuntimeException("El pedido #{$id} no existe.", 404);
        }

        $this->ensureArtisanOwnership($order, $currentUser);

        // Si se cambia a cancelado, delegar en la transacción de cancelación para restituir stock
        if ($estadoPedido === 'Cancelado') {
            return $this->cancelOrder($id, $currentUser);
        }

        $allowedEstados = ['Pendiente', 'En Proceso', 'Entregado', 'Cancelado'];
        if (!in_array($estadoPedido, $allowedEstados, true)) {
            throw new InvalidArgumentException("Estado de pedido no válido: '{$estadoPedido}'.", 422);
        }

        if ($estadoPago !== null) {
            $allowedPagos = ['Pendiente', 'Anticipo 50%', 'Liquidado'];
            if (!in_array($estadoPago, $allowedPagos, true)) {
                throw new InvalidArgumentException("Estado de pago no válido: '{$estadoPago}'.", 422);
            }
        }

        $this->pedidoRepo->updateStatus($id, $estadoPedido, $estadoPago);
        $updatedOrder = $this->pedidoRepo->findById($id);

        return $this->enrichOrder($updatedOrder);
    }

    /**
     * Cancela un pedido con restitución atómica e idempotente de existencias al inventario.
     * 
     * @param int $id Identificador del pedido
     * @param array $currentUser Usuario autenticado
     * @return array Resumen de la cancelación y unidades restituidas
     */
    public function cancelOrder(int $id, array $currentUser): array {
        $order = $this->pedidoRepo->findById($id);
        if ($order === null) {
            throw new RuntimeException("El pedido #{$id} no existe.", 404);
        }

        $this->ensureArtisanOwnership($order, $currentUser);

        $result = $this->pedidoRepo->cancelOrderAtomic($id);

        return [
            'id'                   => $result['id'],
            'estado_pedido'        => 'Cancelado',
            'unidades_restituidas' => $result['unidades_restituidas'],
            'creacion_id'          => $result['creacion_id'],
            'creacion_nombre'      => $result['creacion_nombre'],
            'nuevo_stock'          => $result['nuevo_stock'],
            'actualizado_en'       => $result['actualizado_en'],
            'mensaje'              => "Pedido cancelado exitosamente. Se restituyeron {$result['unidades_restituidas']} unidad(es) al stock de '{$result['creacion_nombre']}'.",
        ];
    }

    /**
     * Realiza borrado lógico de un pedido (activo = 0).
     * 
     * @param int $id Identificador del pedido
     * @param array $currentUser Usuario autenticado
     * @return array Identificador y estado de actividad
     */
    public function deleteOrder(int $id, array $currentUser): array {
        $order = $this->pedidoRepo->findById($id, false);
        if ($order === null) {
            throw new RuntimeException("El pedido #{$id} no existe.", 404);
        }

        $this->ensureArtisanOwnership($order, $currentUser);

        if ((int)$order['activo'] === 0) {
            throw new RuntimeException("El pedido #{$id} ya se encuentra inactivo.", 409);
        }

        $this->pedidoRepo->softDelete($id);

        return [
            'id'      => $id,
            'activo'  => 0,
            'mensaje' => "El pedido #{$id} ha sido dado de baja lógicamente.",
        ];
    }

    /**
     * Reactiva un pedido previamente dado de baja lógica.
     * 
     * @param int $id Identificador del pedido
     * @param array $currentUser Usuario autenticado
     * @return array Identificador y estado de actividad
     */
    public function restoreOrder(int $id, array $currentUser): array {
        $order = $this->pedidoRepo->findById($id, false);
        if ($order === null) {
            throw new RuntimeException("El pedido #{$id} no existe.", 404);
        }

        $this->ensureArtisanOwnership($order, $currentUser);

        if ((int)$order['activo'] === 1) {
            throw new RuntimeException("El pedido #{$id} ya se encuentra activo.", 409);
        }

        $this->pedidoRepo->restore($id);

        return [
            'id'      => $id,
            'activo'  => 1,
            'mensaje' => "El pedido #{$id} ha sido reactivado exitosamente.",
        ];
    }

    /**
     * Garantiza que el artesano autenticado solo pueda gestionar pedidos sobre sus propias piezas (IDOR).
     * El administrador global tiene permiso omnímodo sobre cualquier pedido.
     * 
     * @param array $order Registro del pedido con datos de creación
     * @param array $currentUser Usuario autenticado
     * @throws RuntimeException Si se detecta un intento de acceso no autorizado
     */
    public function ensureArtisanOwnership(array $order, array $currentUser): void {
        $userRole = (string)($currentUser['rol'] ?? 'artesano');
        $userId = (int)($currentUser['id'] ?? 0);

        if ($userRole === 'admin') {
            return;
        }

        $artesanoId = (int)($order['creacion']['artesano_id'] ?? 0);

        if ($artesanoId !== $userId) {
            throw new RuntimeException('Acceso denegado: Este pedido pertenece a creaciones de otro artesano.', 403);
        }
    }

    /**
     * Construye un enlace directo a WhatsApp (https://wa.me/...) con mensaje pre-redactado.
     * 
     * @param string $phone Teléfono o contacto del cliente
     * @param string $clientName Nombre del cliente
     * @param int $orderId Número de pedido
     * @param string $creationName Nombre de la pieza en crochet
     * @return string|null URL de WhatsApp o null si el teléfono no es válido
     */
    public function buildWhatsAppLink(
        string $phone,
        string $clientName,
        int $orderId,
        string $creationName
    ): ?string {
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);

        if (empty($cleanPhone) || strlen($cleanPhone) < 8) {
            return null;
        }

        // Si son 10 dígitos nacionales (formato mexicano habitual sin código de país), anteponer 52
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '52' . $cleanPhone;
        }

        $message = "¡Hola {$clientName}! Te escribo de Crochet Manager con respecto a tu pedido #{$orderId} de '{$creationName}'.";

        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
    }

    /**
     * Enriquece un pedido con formato monetario dual y enlace directo a WhatsApp.
     * 
     * @param array $order Registro crudo hidratado
     * @return array Registro enriquecido
     */
    private function enrichOrder(array $order): array {
        $order['precio_final_formateado'] = CurrencyHelper::formatCents((int)$order['precio_final']);

        if (isset($order['creacion']['precio'])) {
            $order['creacion']['precio_unitario_formateado'] = CurrencyHelper::formatCents((int)$order['creacion']['precio']);
        }

        $order['enlace_whatsapp'] = $this->buildWhatsAppLink(
            (string)$order['cliente_contacto'],
            (string)$order['cliente_nombre'],
            (int)$order['id'],
            (string)($order['creacion']['nombre'] ?? 'pieza en crochet')
        );

        return $order;
    }
}
