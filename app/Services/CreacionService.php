<?php
/**
 * Creation & Catalog Business Service (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Orquesta la lógica de negocio para el catálogo colectivo de piezas en crochet:
 * validaciones estrictas de dominio, enriquecimiento monetario dual, gestión segura de
 * subida de imágenes a uploads/, asignación automática de SVG temático de respaldo,
 * ciclo de vida de archivos físicos (reemplazo en edición, preservación en baja lógica ADR-008),
 * autorización IDOR (ADR-007), ajuste rápido de inventario y conmutación de encargo.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\CreacionRepository;
use App\Repositories\UsuarioRepository;
use App\Utils\CurrencyHelper;
use App\Utils\PaginationHelper;
use InvalidArgumentException;
use RuntimeException;

class CreacionService {
    private CreacionRepository $creacionRepo;
    private UsuarioRepository $usuarioRepo;
    private string $uploadsDir;
    private string $baseDir;

    public function __construct(
        ?CreacionRepository $creacionRepo = null,
        ?UsuarioRepository $usuarioRepo = null,
        ?string $uploadsDir = null
    ) {
        $this->creacionRepo = $creacionRepo ?? new CreacionRepository();
        $this->usuarioRepo = $usuarioRepo ?? new UsuarioRepository();
        $this->baseDir = realpath(dirname(__DIR__, 2)) ?: dirname(__DIR__, 2);
        $this->uploadsDir = $uploadsDir ?? (string)Config::get('uploads.dir', $this->baseDir . '/uploads');
    }

    /**
     * Consulta el catálogo público o administrativo de creaciones con filtros, orden y paginación.
     * 
     * @param array $query Parámetros de consulta (categoria, artesano_id, precio_min, precio_max, busqueda, es_sobre_encargo, solo_en_stock, orden, pagina, limite)
     * @return array{datos: array, paginacion: array}
     */
    public function getCatalog(array $query = []): array {
        $page = max(1, (int)($query['pagina'] ?? 1));
        $limitDefault = (int)Config::get('pagination.creaciones_default', 12);
        $limitMax = (int)Config::get('pagination.creaciones_max', 48);
        $limit = max(1, min($limitMax, (int)($query['limite'] ?? $limitDefault)));
        $offset = ($page - 1) * $limit;

        $filters = [];

        // 1. Categoría
        if (!empty($query['categoria'])) {
            $filters['categoria'] = trim((string)$query['categoria']);
        }

        // 2. Artesano
        if (!empty($query['artesano_id']) && (int)$query['artesano_id'] > 0) {
            $filters['artesano_id'] = (int)$query['artesano_id'];
        }

        // 3. Precios (si se envían en centavos o formato numérico)
        if (isset($query['precio_min']) && $query['precio_min'] !== '') {
            $val = $query['precio_min'];
            $filters['precio_min'] = is_numeric($val) && (int)$val > 1000 ? (int)$val : CurrencyHelper::mxnToCents($val);
        }

        if (isset($query['precio_max']) && $query['precio_max'] !== '') {
            $val = $query['precio_max'];
            $filters['precio_max'] = is_numeric($val) && (int)$val > 1000 ? (int)$val : CurrencyHelper::mxnToCents($val);
        }

        // 4. Búsqueda por texto
        if (!empty($query['busqueda'])) {
            $filters['busqueda'] = trim((string)$query['busqueda']);
        }

        // 5. Modalidad bajo encargo
        if (isset($query['es_sobre_encargo']) && $query['es_sobre_encargo'] !== '') {
            $filters['es_sobre_encargo'] = !empty($query['es_sobre_encargo']) ? 1 : 0;
        }

        // 6. Solo piezas con existencias físicas
        if (!empty($query['solo_en_stock']) && ($query['solo_en_stock'] === '1' || $query['solo_en_stock'] === 'true' || $query['solo_en_stock'] === true)) {
            $filters['solo_en_stock'] = true;
        }

        // 7. Filtro de estado activo
        if (isset($query['activo'])) {
            if ($query['activo'] === 'todos' || $query['activo'] === null) {
                $filters['activo'] = null;
            } elseif ($query['activo'] === '0' || $query['activo'] === 'false' || $query['activo'] === false) {
                $filters['activo'] = false;
            } else {
                $filters['activo'] = true;
            }
        }

        // Criterio de ordenación
        $orden = (string)($query['orden'] ?? 'recientes');

        $totalItems = $this->creacionRepo->countCatalog($filters);
        $items = $this->creacionRepo->listCatalog($filters, $limit, $offset, $orden);

        $enrichedItems = array_map([$this, 'enrichCreation'], $items);
        $paginationEnvelope = PaginationHelper::build($totalItems, $page, $limit);
        $pagination = $paginationEnvelope['paginacion'] ?? $paginationEnvelope;

        return [
            'datos'      => $enrichedItems,
            'paginacion' => $pagination,
        ];
    }

    /**
     * Obtiene la lista pública de artesanos que tienen creaciones activas a la venta (ADR-014).
     * 
     * @return array Array de items [{ id, username, total_creaciones }]
     */
    public function getActiveArtisans(): array {
        return $this->creacionRepo->findActiveArtisansWithCreations();
    }

    /**
     * Obtiene la ficha técnica completa y enriquecida de una creación por su ID.
     * 
     * @param int $id Identificador de la pieza
     * @param bool $onlyActive Filtrar únicamente creaciones activas (default: true)
     * @return array Ficha técnica completa de la pieza
     * @throws InvalidArgumentException Si el ID es inválido (HTTP 422)
     * @throws RuntimeException Si la pieza no existe o está dada de baja (HTTP 404)
     */
    public function getCreationById(int $id, bool $onlyActive = true): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El identificador de la creación debe ser un número entero positivo.', 422);
        }

        $creacion = $this->creacionRepo->findById($id, $onlyActive);
        if ($creacion === null) {
            throw new RuntimeException("La creación con ID #{$id} no existe o no se encuentra disponible en el catálogo.", 404);
        }

        return $this->enrichCreation($creacion);
    }

    /**
     * Registra una nueva pieza en el catálogo con validaciones rigurosas y gestión de imagen.
     * 
     * @param array $data Datos de la creación
     * @param array|null $fileData Archivo opcional subido vía $_FILES['imagen']
     * @param array $currentUser Usuario autenticado que realiza la petición
     * @return array Datos de la creación registrada
     * @throws InvalidArgumentException Si algún campo no cumple las reglas de validación (HTTP 422)
     */
    public function createCreation(array $data, ?array $fileData, array $currentUser): array {
        // 1. Determinar el artesano_id de la pieza
        $artesanoId = (int)($currentUser['id'] ?? 0);
        if (($currentUser['rol'] ?? '') === 'admin' && !empty($data['artesano_id'])) {
            $targetArtisanId = (int)$data['artesano_id'];
            $artisan = $this->usuarioRepo->findByIdSafe($targetArtisanId, true);
            if ($artisan !== null) {
                $artesanoId = $targetArtisanId;
            }
        }

        if ($artesanoId <= 0) {
            throw new InvalidArgumentException('El artesano creador de la pieza no es válido.', 422);
        }

        // 2. Validaciones de dominio
        $validatedData = $this->validateCreationData($data);
        $validatedData['artesano_id'] = $artesanoId;

        // 3. Gestión de la fotografía o vector temático SVG de respaldo
        $imagenUrl = $this->handleImageUpload($fileData, $validatedData['categoria'], $validatedData['nombre']);
        $validatedData['imagen_url'] = $imagenUrl;

        // 4. Inserción en la base de datos
        $newId = $this->creacionRepo->create($validatedData);

        return $this->getCreationById($newId, true);
    }

    /**
     * Actualiza una creación existente con salvaguarda IDOR y reemplazo higiénico de foto en disco.
     * 
     * @param int $id Identificador de la creación a modificar
     * @param array $data Datos actualizados
     * @param array|null $fileData Nueva fotografía opcional
     * @param array $currentUser Usuario en sesión activa
     * @return array Ficha técnica actualizada
     * @throws RuntimeException Si no existe (404) o si no es propietario IDOR (403)
     */
    public function updateCreation(int $id, array $data, ?array $fileData, array $currentUser): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de la creación no es válido.', 422);
        }

        // 1. Verificar existencia de la pieza
        $existing = $this->creacionRepo->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException("La creación con ID #{$id} no existe en el sistema.", 404);
        }

        // 2. Salvaguarda IDOR (ADR-007)
        $this->ensureArtisanOwnership($existing, $currentUser);

        // 3. Validar datos
        $validatedData = $this->validateCreationData($data);

        // 4. Gestión del reemplazo de fotografía (ADR-008)
        $oldImageUrl = (string)($existing['imagen_url'] ?? '');
        $hasNewUpload = $fileData !== null && isset($fileData['error']) && $fileData['error'] === UPLOAD_ERR_OK && ($fileData['size'] ?? 0) > 0;

        if ($hasNewUpload) {
            $newImageUrl = $this->handleImageUpload($fileData, $validatedData['categoria'], $validatedData['nombre']);
            $validatedData['imagen_url'] = $newImageUrl;

            // Eliminar archivo físico anterior solo si residía en uploads/ (nunca SVGs de assets/)
            $this->unlinkPreviousUploadFile($oldImageUrl);
        } else {
            // Mantener imagen existente si no se subió una nueva
            $validatedData['imagen_url'] = $oldImageUrl !== '' ? $oldImageUrl : $this->getThematicSvgFallback($validatedData['categoria']);
        }

        // 5. Persistir en la base de datos
        $this->creacionRepo->update($id, $validatedData);

        return $this->getCreationById($id, true);
    }

    /**
     * Aplica baja lógica a una creación sin borrar físicamente la foto en disco (ADR-008).
     * 
     * @param int $id Identificador de la creación
     * @param array $currentUser Usuario en sesión activa
     * @return array{id: int, activo: int}
     * @throws RuntimeException Si no existe (404), violación IDOR (403) o ya está inactiva (409)
     */
    public function deleteCreation(int $id, array $currentUser): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de la creación no es válido.', 422);
        }

        $existing = $this->creacionRepo->findById($id, false);
        if ($existing === null) {
            throw new RuntimeException("La creación con ID #{$id} no existe.", 404);
        }

        // Salvaguarda IDOR (ADR-007)
        $this->ensureArtisanOwnership($existing, $currentUser);

        if ((int)$existing['activo'] === 0) {
            throw new RuntimeException("La creación con ID #{$id} ya se encuentra inactiva/retirada del catálogo.", 409);
        }

        // Baja lógica: CERO unlink() en disco para proteger miniaturas en pedidos históricos (ADR-008)
        $this->creacionRepo->softDelete($id);

        return [
            'id'     => $id,
            'activo' => 0,
        ];
    }

    /**
     * Restaura una creación dada de baja lógica previa (ADR-015).
     * 
     * @param int $id Identificador de la pieza a restaurar
     * @param array $currentUser Usuario en sesión activa
     * @return array{id: int, activo: int}
     * @throws RuntimeException Si no existe (404), violación IDOR (403) o ya está activa (409)
     */
    public function restoreCreation(int $id, array $currentUser): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de la creación no es válido.', 422);
        }

        $existing = $this->creacionRepo->findById($id, false);
        if ($existing === null) {
            throw new RuntimeException("La creación con ID #{$id} no existe.", 404);
        }

        // Salvaguarda IDOR (ADR-007)
        $this->ensureArtisanOwnership($existing, $currentUser);

        if ((int)$existing['activo'] === 1) {
            throw new RuntimeException("La creación con ID #{$id} ya se encuentra activa en el catálogo.", 409);
        }

        $this->creacionRepo->restore($id);

        return [
            'id'     => $id,
            'activo' => 1,
        ];
    }

    /**
     * Ajusta rápidamente las existencias de inventario físico disponible.
     * 
     * @param int $id Identificador de la pieza
     * @param int $newStock Nueva cantidad de stock (0-10000)
     * @param array $currentUser Usuario en sesión activa
     * @return array{id: int, cantidad_stock: int}
     */
    public function adjustStock(int $id, int $newStock, array $currentUser): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de la creación no es válido.', 422);
        }

        if ($newStock < 0 || $newStock > 10000) {
            throw new InvalidArgumentException('La cantidad de existencias debe encontrarse entre 0 y 10,000 unidades.', 422);
        }

        $existing = $this->creacionRepo->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException("La creación con ID #{$id} no existe o se encuentra inactiva.", 404);
        }

        // Salvaguarda IDOR (ADR-007)
        $this->ensureArtisanOwnership($existing, $currentUser);

        $this->creacionRepo->adjustStock($id, $newStock);

        return [
            'id'             => $id,
            'cantidad_stock' => $newStock,
        ];
    }

    /**
     * Conmuta la bandera de modalidad bajo encargo exclusivo.
     * 
     * @param int $id Identificador de la pieza
     * @param int $state 1 para encargo exclusivo, 0 para regular
     * @param array $currentUser Usuario en sesión activa
     * @return array{id: int, es_sobre_encargo: int}
     */
    public function toggleCommission(int $id, int $state, array $currentUser): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de la creación no es válido.', 422);
        }

        $stateVal = $state ? 1 : 0;

        $existing = $this->creacionRepo->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException("La creación con ID #{$id} no existe o se encuentra inactiva.", 404);
        }

        // Salvaguarda IDOR (ADR-007)
        $this->ensureArtisanOwnership($existing, $currentUser);

        $this->creacionRepo->toggleCommission($id, $stateVal);

        return [
            'id'               => $id,
            'es_sobre_encargo' => $stateVal,
        ];
    }

    /**
     * Valida que el usuario actual tenga autorización sobre la creación especificada (ADR-007).
     * 
     * @param array $creacion Datos de la creación (con 'artesano_id')
     * @param array $currentUser Usuario autenticado (con 'id' y 'rol')
     * @throws RuntimeException Si el usuario no es el artesano autor ni administrador (HTTP 403)
     */
    public function ensureArtisanOwnership(array $creacion, array $currentUser): void {
        $artesanoId = (int)($creacion['artesano_id'] ?? 0);
        $userId = (int)($currentUser['id'] ?? 0);
        $userRol = (string)($currentUser['rol'] ?? '');

        if ($artesanoId !== $userId && $userRol !== 'admin') {
            throw new RuntimeException('Operación denegada: No tienes permisos para modificar o eliminar esta creación.', 403);
        }
    }

    /**
     * Procesa la subida física de una fotografía en uploads/ o asigna un vector temático SVG de respaldo.
     * 
     * @param array|null $fileData Array con datos de $_FILES['imagen']
     * @param string $categoria Categoría de la pieza
     * @param string|null $nombre Nombre de la pieza
     * @return string Ruta relativa (ej. 'uploads/creacion_abc123.jpg' o 'assets/svg/piezas/dragon-ignis.svg')
     */
    public function handleImageUpload(?array $fileData, string $categoria, ?string $nombre = null): string {
        $hasUpload = $fileData !== null 
            && isset($fileData['error']) 
            && $fileData['error'] === UPLOAD_ERR_OK 
            && ($fileData['size'] ?? 0) > 0;

        if ($hasUpload) {
            $maxBytes = (int)Config::get('uploads.max_bytes', 5242880); // 5MB
            if ((int)$fileData['size'] > $maxBytes) {
                throw new InvalidArgumentException('El archivo de imagen no debe superar los 5 megabytes (5MB).', 422);
            }

            $tmpPath = (string)$fileData['tmp_name'];
            if (!is_file($tmpPath)) {
                throw new RuntimeException('El archivo temporal de imagen no es accesible en el servidor.', 500);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_file($finfo, $tmpPath) : mime_content_type($tmpPath);
            if ($finfo) {
                finfo_close($finfo);
            }

            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($allowedMimes[$mime])) {
                throw new InvalidArgumentException("Formato de imagen no permitido ({$mime}). Formatos válidos: JPEG, PNG y WebP.", 422);
            }

            $ext = $allowedMimes[$mime];
            $uniqueName = 'creacion_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;

            if (!is_dir($this->uploadsDir)) {
                mkdir($this->uploadsDir, 0755, true);
            }

            $destPath = $this->uploadsDir . '/' . $uniqueName;

            // move_uploaded_file o copy como fallback para pruebas
            if (!@move_uploaded_file($tmpPath, $destPath)) {
                if (!@copy($tmpPath, $destPath)) {
                    throw new RuntimeException('No fue posible guardar la imagen en el directorio de almacenamiento.', 500);
                }
            }

            return 'uploads/' . $uniqueName;
        }

        // Si no se adjuntó archivo, retornar SVG temático
        return $this->getThematicSvgFallback($categoria, $nombre);
    }

    /**
     * Resuelve un vector temático de alta fidelidad desde assets/svg/piezas/ según la categoría o nombre.
     * 
     * @param string $categoria
     * @param string|null $nombre
     * @return string
     */
    public function getThematicSvgFallback(string $categoria, ?string $nombre = null): string {
        $cat = mb_strtolower(trim($categoria));
        $name = mb_strtolower(trim((string)$nombre));

        // Prioridad por palabras clave en el nombre
        if (str_contains($name, 'dragón') || str_contains($name, 'dragon')) {
            return 'assets/svg/piezas/dragon-ignis.svg';
        }
        if (str_contains($name, 'ajolote')) {
            return 'assets/svg/piezas/ajolote-pastel.svg';
        }
        if (str_contains($name, 'oso') || str_contains($name, 'osito')) {
            return 'assets/svg/piezas/osito-nordico.svg';
        }
        if (str_contains($name, 'gatito') || str_contains($name, 'gato')) {
            return 'assets/svg/piezas/gatito-ovillo.svg';
        }
        if (str_contains($name, 'pingüino') || str_contains($name, 'pinguino')) {
            return 'assets/svg/piezas/pinguino-bufanda.svg';
        }
        if (str_contains($name, 'medusa')) {
            return 'assets/svg/piezas/medusa-magica.svg';
        }
        if (str_contains($name, 'hongo')) {
            return 'assets/svg/piezas/hongo-bosque.svg';
        }
        if (str_contains($name, 'cardigan') || str_contains($name, 'suéter') || str_contains($name, 'sueter')) {
            return 'assets/svg/piezas/cardigan-granny.svg';
        }
        if (str_contains($name, 'tote') || str_contains($name, 'bolso') || str_contains($name, 'bolsa')) {
            return 'assets/svg/piezas/tote-bag.svg';
        }

        // Fallback por categoría oficial
        if (str_contains($cat, 'amigurumi') || str_contains($cat, 'figura') || str_contains($cat, 'fantasía') || str_contains($cat, 'fantasia')) {
            return 'assets/svg/piezas/dragon-ignis.svg';
        }
        if (str_contains($cat, 'prenda') || str_contains($cat, 'ropa')) {
            return 'assets/svg/piezas/cardigan-granny.svg';
        }
        if (str_contains($cat, 'bolso') || str_contains($cat, 'accesorio')) {
            return 'assets/svg/piezas/tote-bag.svg';
        }
        if (str_contains($cat, 'hogar') || str_contains($cat, 'decoraci')) {
            return 'assets/svg/piezas/mini-suculenta.svg';
        }
        if (str_contains($cat, 'bebé') || str_contains($cat, 'bebe') || str_contains($cat, 'infantil')) {
            return 'assets/svg/piezas/osito-nordico.svg';
        }

        return 'assets/svg/piezas/gatito-ovillo.svg';
    }

    /**
     * Valida de manera estricta los datos suministrados para una creación.
     * 
     * @param array $data
     * @return array Datos normalizados y validados
     * @throws InvalidArgumentException Si alguna regla falla
     */
    private function validateCreationData(array $data): array {
        $nombre = trim((string)($data['nombre'] ?? ''));
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100) {
            throw new InvalidArgumentException('El nombre de la creación debe tener entre 2 y 100 caracteres.', 422);
        }

        $categoria = trim((string)($data['categoria'] ?? ''));
        if (mb_strlen($categoria) < 2 || mb_strlen($categoria) > 50) {
            throw new InvalidArgumentException('La categoría de la creación debe tener entre 2 y 50 caracteres.', 422);
        }

        $material = trim((string)($data['material'] ?? ''));
        if (mb_strlen($material) < 3 || mb_strlen($material) > 80) {
            throw new InvalidArgumentException('La fibra o material debe tener entre 3 y 80 caracteres.', 422);
        }

        $dimensiones = trim((string)($data['dimensiones'] ?? ''));
        if (mb_strlen($dimensiones) < 2 || mb_strlen($dimensiones) > 100) {
            throw new InvalidArgumentException('Las dimensiones deben tener entre 2 y 100 caracteres.', 422);
        }

        // Precio: puede llegar en centavos (int) o formato monetario/decimal (ej. "450.00" o 45000)
        $rawPrecio = $data['precio'] ?? null;
        if ($rawPrecio === null || $rawPrecio === '') {
            throw new InvalidArgumentException('El precio de venta es obligatorio.', 422);
        }

        $precio = is_int($rawPrecio) ? $rawPrecio : CurrencyHelper::mxnToCents($rawPrecio);
        if ($precio < 1 || $precio > 9999999) {
            throw new InvalidArgumentException('El precio de venta debe ser positivo y no exceder $99,999.99 MXN.', 422);
        }

        // Costo de materiales
        $costo = 0;
        if (isset($data['costo_materiales']) && $data['costo_materiales'] !== '') {
            $rawCosto = $data['costo_materiales'];
            $costo = is_int($rawCosto) ? $rawCosto : CurrencyHelper::mxnToCents($rawCosto);
        }
        if ($costo < 0 || $costo > 9999999) {
            throw new InvalidArgumentException('El costo de materiales no puede ser negativo ni exceder $99,999.99 MXN.', 422);
        }

        // Cantidad stock
        $stock = isset($data['cantidad_stock']) && $data['cantidad_stock'] !== '' ? (int)$data['cantidad_stock'] : 0;
        if ($stock < 0 || $stock > 10000) {
            throw new InvalidArgumentException('La cantidad en inventario debe encontrarse entre 0 y 10,000 unidades.', 422);
        }

        // Horas de tejido
        $horas = 0.0;
        if (isset($data['horas_tejido']) && $data['horas_tejido'] !== '' && $data['horas_tejido'] !== null) {
            $horas = (float)$data['horas_tejido'];
        }
        if ($horas < 0.0 || $horas > 500.0) {
            throw new InvalidArgumentException('Las horas de labor manual deben encontrarse entre 0.0 y 500.0 horas.', 422);
        }

        // Descripción
        $descripcion = isset($data['descripcion']) && trim((string)$data['descripcion']) !== '' ? trim((string)$data['descripcion']) : null;
        if ($descripcion !== null && mb_strlen($descripcion) > 2000) {
            throw new InvalidArgumentException('La descripción artesanal no puede exceder los 2,000 caracteres.', 422);
        }

        // Modalidad bajo encargo
        $esSobreEncargo = !empty($data['es_sobre_encargo']) ? 1 : 0;

        return [
            'nombre'           => $nombre,
            'categoria'        => $categoria,
            'material'         => $material,
            'dimensiones'      => $dimensiones,
            'precio'           => $precio,
            'costo_materiales' => $costo,
            'cantidad_stock'   => $stock,
            'horas_tejido'     => $horas,
            'descripcion'      => $descripcion,
            'es_sobre_encargo' => $esSobreEncargo,
        ];
    }

    /**
     * Enriquece la representación de una creación con métricas y campos duales de moneda.
     * 
     * @param array $creacion
     * @return array
     */
    private function enrichCreation(array $creacion): array {
        $enriched = CurrencyHelper::enrichCreation($creacion);

        $precio = (int)$enriched['precio'];
        $costo = (int)$enriched['costo_materiales'];
        $horas = (float)$enriched['horas_tejido'];
        $gananciaCents = $precio - $costo;

        $retornoPorHoraCents = $horas > 0.0 ? (int)round($gananciaCents / $horas) : 0;

        $enriched['costo_formateado'] = $enriched['costo_materiales_formateado'];

        $enriched['metricas'] = [
            'margen_bruto_porcentaje'     => $enriched['margen_bruto_porcentaje'],
            'retorno_por_hora'            => $retornoPorHoraCents,
            'retorno_por_hora_formateado' => CurrencyHelper::formatCents($retornoPorHoraCents),
        ];

        return $enriched;
    }

    /**
     * Elimina higiénicamente un archivo subido previo si se encontraba en uploads/ (ADR-008).
     * 
     * @param string $imageUrl
     */
    private function unlinkPreviousUploadFile(string $imageUrl): void {
        if ($imageUrl === '') {
            return;
        }

        // Solo eliminar si está dentro de uploads/ (nunca assets/svg/ o URLs absolutas externas)
        $normalized = str_replace('\\', '/', $imageUrl);
        if (str_starts_with($normalized, 'uploads/')) {
            $filename = basename($normalized);
            $fullPath = $this->uploadsDir . '/' . $filename;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }
}
