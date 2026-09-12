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
use App\Core\TokenManager;
use App\Repositories\UsuarioRepository;
use InvalidArgumentException;
use RuntimeException;

class AuthService {
    private UsuarioRepository $usuarioRepo;

    public function __construct(?UsuarioRepository $usuarioRepo = null) {
        $this->usuarioRepo = $usuarioRepo ?? new UsuarioRepository();
    }

    /**
     * Autentica un usuario mediante su nombre y contraseña en texto plano.
     * 
     * @param string $username Nombre de usuario
     * @param string $password Contraseña en texto plano
     * @return array{token: string, tipo_token: string, expira_en: int, usuario: array{id: int, username: string, rol: string}}
     * @throws InvalidArgumentException Si las entradas son inválidas o vacías (HTTP 422)
     * @throws RuntimeException Si las credenciales no coinciden (HTTP 401)
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

        $user = $this->usuarioRepo->findByUsername($username);

        // Mitigación de timing attack con hash dummy si el usuario no existe
        $dummyHash = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUUabcdefghijk';
        $hashToVerify = $user !== null ? (string)$user['password_hash'] : $dummyHash;

        $isValid = password_verify($password, $hashToVerify);

        if ($user === null || !$isValid) {
            throw new RuntimeException('Credenciales de acceso incorrectas.', 401);
        }

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

        $user = $this->usuarioRepo->findByIdSafe($userId);
        return is_array($user) ? $user : null;
    }

    /**
     * Obtiene el perfil seguro del usuario por su identificador.
     */
    public function getProfile(int $userId): ?array {
        return $this->usuarioRepo->findByIdSafe($userId);
    }
}
