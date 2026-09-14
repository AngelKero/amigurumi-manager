<?php
/**
 * Authentication Business Service (Clean Architecture)
 * Algodón Nórdico Design System
 * 
 * Orquesta los flujos de autenticación de usuarios, validación de credenciales bcrypt,
 * emisión de tokens Bearer HMAC-SHA256 y resolución de identidades en tiempo constante.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Request;
use App\Core\TokenManager;
use App\Repositories\LoginGuardRepository;
use App\Repositories\TokenRevocadoRepository;
use App\Repositories\UsuarioRepository;
use InvalidArgumentException;
use RuntimeException;

class AuthService {
    private UsuarioRepository $usuarioRepo;
    private LoginGuardRepository $loginGuard;
    private TokenRevocadoRepository $tokenRevocadoRepo;

    public function __construct(
        ?UsuarioRepository $usuarioRepo = null,
        ?LoginGuardRepository $loginGuard = null,
        ?TokenRevocadoRepository $tokenRevocadoRepo = null
    ) {
        $this->usuarioRepo = $usuarioRepo ?? new UsuarioRepository();
        $this->loginGuard = $loginGuard ?? new LoginGuardRepository();
        $this->tokenRevocadoRepo = $tokenRevocadoRepo ?? new TokenRevocadoRepository();
    }

    /**
     * Autentica un usuario mediante su nombre y contraseña en texto plano.
     * 
     * @param string $username Nombre de usuario
     * @param string $password Contraseña en texto plano
     * @return array{token: string, tipo_token: string, expira_en: int, usuario: array{id: int, username: string, rol: string}}
     * @throws InvalidArgumentException Si las entradas son inválidas o vacías (HTTP 422)
     * @throws RuntimeException Si las credenciales no coinciden (HTTP 401) o hay bloqueo por fuerza bruta (HTTP 429)
     */
    public function authenticate(string $username, string $password): array {
        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            throw new InvalidArgumentException('El nombre de usuario y la contraseña son obligatorios.', 422);
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new InvalidArgumentException('El nombre de usuario debe tener entre 3 y 50 caracteres.', 422);
        }

        // 1. Endurecimiento anti-fuerza-bruta (H-003): bloqueo por username e IP
        $this->verificarBloqueo($username, Request::clientIp());

        $user = $this->usuarioRepo->findByUsername($username);

        // Mitigación de timing attack con hash dummy si el usuario no existe
        $dummyHash = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUUabcdefghijk';
        $hashToVerify = $user !== null ? (string)$user['password_hash'] : $dummyHash;

        $isValid = password_verify($password, $hashToVerify);

        if ($user === null || !$isValid) {
            // 2. Registrar intento fallido con backoff progresivo
            $this->loginGuard->registerIntent($username, Request::clientIp(), false);
            $this->aplicarBackoff();
            throw new RuntimeException('Credenciales de acceso incorrectas.', 401);
        }

        // 3. Éxito: restablecer contador de fallos del usuario y registrar acierto
        $this->loginGuard->clearFailuresByUsername($username);
        $this->loginGuard->registerIntent($username, Request::clientIp(), true);

        $ttl = (int)(Config::get('auth.token_ttl') ?? Config::get('auth.jwt_ttl_seconds', 86400));
        $token = TokenManager::generate($user, $ttl);

        return [
            'token'      => $token,
            'tipo_token' => 'Bearer',
            'expira_en'  => $ttl,
            'usuario'    => [
                'id'        => (int)$user['id'],
                'username'  => (string)$user['username'],
                'rol'       => (string)$user['rol'],
                'creado_en' => (string)$user['creado_en'],
            ]
        ];
    }

    /**
     * Valida un Bearer token y recupera los datos actuales del usuario en la base de datos.
     * 
     * @param string $token Token en formato "payloadB64.firma"
     * @return array|null Datos seguros del usuario o null si el token es inválido/expirado
     */
    public function validateToken(string $token): ?array {
        $payload = TokenManager::verify($token);
        if ($payload === null) {
            return null;
        }

        $userId = (int)($payload['sub'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        // 2. Denylist por jti (H-002): tokens revocados en logout se rechazan
        $jti = (string)($payload['jti'] ?? '');
        if ($jti !== '' && $this->tokenRevocadoRepo->isRevoked($jti)) {
            return null;
        }

        // 3. Purga oportunista de la denylist (1% de las validaciones)
        if ($jti !== '' && random_int(1, 100) === 1) {
            $this->tokenRevocadoRepo->pruneExpirados();
        }

        $user = $this->usuarioRepo->findByIdSafe($userId);
        return is_array($user) ? $user : null;
    }

    /**
     * Revoca un Bearer token en servidor añadiendo su jti a la denylist (H-002).
     *
     * @param string $token Token en formato "payloadB64.firma"
     * @return bool True si el token era válido y se revocó; false si era inválido/expirado
     */
    public function revokeToken(string $token): bool {
        $payload = TokenManager::verify($token);
        if ($payload === null) {
            return false;
        }

        $jti = (string)($payload['jti'] ?? '');
        if ($jti === '') {
            return false;
        }

        $this->tokenRevocadoRepo->revoke(
            $jti,
            (int)($payload['sub'] ?? 0),
            (int)($payload['exp'] ?? 0)
        );

        return true;
    }

    /**
     * Verifica si una combinación username + IP ha excedido los umbrales anti-fuerza-bruta.
     *
     * @throws RuntimeException HTTP 429 Too Many Requests si hay bloqueo activo
     */
    private function verificarBloqueo(string $username, string $ip): void {
        $ventana  = (int)(Config::get('auth.ventana_segundos', 900));
        $maxU     = (int)(Config::get('auth.max_intentos_username', 5));
        $maxIp    = (int)(Config::get('auth.max_intentos_ip', 20));

        $fallosU  = $this->loginGuard->countFailuresByUsername($username, $ventana);
        $fallosIp = $this->loginGuard->countFailuresByIp($ip, $ventana);

        if ($fallosU >= $maxU || $fallosIp >= $maxIp) {
            throw new RuntimeException(
                'Demasiados intentos de acceso fallidos. Cuenta temporalmente bloqueada.', 429
            );
        }
    }

    /**
     * Aplica una pausa de backoff configurable para frenar ataques de fuerza bruta.
     */
    private function aplicarBackoff(): void {
        $ms = (int)(Config::get('auth.backoff_ms', 400));
        if ($ms > 0) {
            usleep(min(2000, $ms) * 1000);
        }
    }

    /**
     * Obtiene el perfil seguro del usuario por su identificador.
     */
    public function getProfile(int $userId): ?array {
        return $this->usuarioRepo->findByIdSafe($userId);
    }

    /**
     * Permite a un usuario autenticado cambiar su propia contraseña verificando la clave actual.
     * 
     * @param int $userId ID del usuario en sesión
     * @param string $currentPassword Contraseña actual en texto plano
     * @param string $newPassword Nueva contraseña en texto plano (mínimo 6 caracteres)
     * @return array{id: int, username: string}
     * @throws InvalidArgumentException Si los datos son inválidos (HTTP 422)
     * @throws RuntimeException Si la clave actual es errónea (HTTP 401) o el usuario no existe (HTTP 404)
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array {
        $currentPassword = trim($currentPassword);
        $newPassword = trim($newPassword);

        if ($currentPassword === '' || $newPassword === '') {
            throw new InvalidArgumentException('La contraseña actual y la nueva contraseña son obligatorias.', 422);
        }

        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException('La nueva contraseña debe tener al menos 6 caracteres.', 422);
        }

        $user = $this->usuarioRepo->findById($userId, true);
        if ($user === null || (int)($user['activo'] ?? 1) !== 1) {
            throw new RuntimeException('Usuario no encontrado o cuenta inactiva.', 404);
        }

        if (!password_verify($currentPassword, (string)$user['password_hash'])) {
            throw new RuntimeException('La contraseña actual es incorrecta.', 401);
        }

        $cost = (int)(Config::get('auth.bcrypt_cost', 10));
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => $cost]);

        $updated = $this->usuarioRepo->updatePassword($userId, $newHash);
        if (!$updated) {
            throw new RuntimeException('Error interno al actualizar la contraseña en el repositorio.', 500);
        }

        return [
            'id'       => $userId,
            'username' => (string)$user['username'],
        ];
    }
}

