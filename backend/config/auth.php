<?php
/**
 * ZONA TURBO - Sistema de Autenticación
 * JWT manual (header.payload.signature) + bcrypt para contraseñas
 */

class Auth {
    private static string $secret;
    private static int    $expireSeconds = 28800; // 8 horas

    public static function init(): void {
        self::$secret = $_ENV['JWT_SECRET'] ?? 'CAMBIAR_EN_PRODUCCION_CLAVE_SECRETA_MINIMO_32_CHARS';
    }

    // ----------------------------------------------------------
    // CONTRASEÑAS
    // ----------------------------------------------------------

    /** Hashea una contraseña con bcrypt */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /** Verifica contraseña contra hash */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    // ----------------------------------------------------------
    // JWT
    // ----------------------------------------------------------

    /** Genera un token JWT firmado */
    public static function generateToken(array $payload): string {
        self::init();

        $header = self::base64url(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));

        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expireSeconds;

        $payloadEncoded = self::base64url(json_encode($payload));
        $signature      = self::base64url(
            hash_hmac('sha256', "$header.$payloadEncoded", self::$secret, true)
        );

        return "$header.$payloadEncoded.$signature";
    }

    /** Valida y decodifica un token JWT */
    public static function validateToken(string $token): ?array {
        self::init();

        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        $expectedSig = self::base64url(
            hash_hmac('sha256', "$header.$payload", self::$secret, true)
        );

        if (!hash_equals($expectedSig, $signature)) return null;

        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        if (!$data || $data['exp'] < time()) return null;

        return $data;
    }

    /** Extrae el token del header Authorization: Bearer <token> */
    public static function getBearerToken(): ?string {
        $headers = apache_request_headers();
        $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(\S+)/i', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Middleware: valida token y verifica rol
     * Uso: Auth::require(['admin', 'mecanico'])
     */
    public static function require(array $rolesPermitidos = []): array {
        $token = self::getBearerToken();

        if (!$token) {
            http_response_code(401);
            die(json_encode(['success' => false, 'message' => 'Token requerido.']));
        }

        $data = self::validateToken($token);

        if (!$data) {
            http_response_code(401);
            die(json_encode(['success' => false, 'message' => 'Token inválido o expirado.']));
        }

        if (!empty($rolesPermitidos) && !in_array($data['rol'], $rolesPermitidos)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Acceso denegado.']));
        }

        return $data;
    }

    // ----------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------

    private static function base64url(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
