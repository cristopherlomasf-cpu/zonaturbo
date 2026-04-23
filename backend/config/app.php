<?php
/**
 * ZONA TURBO - Bootstrap de la aplicación
 * Carga variables de entorno, headers CORS y helpers globales
 */

// ── Cargar .env si existe ──────────────────────────────────────
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '='))         continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

// ── Headers globales ──────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    $_ENV['FRONTEND_URL'] ?? ''
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Autoload simple ───────────────────────────────────────────
spl_autoload_register(function (string $class) {
    $base = dirname(__DIR__);
    $paths = [
        "$base/config/$class.php",
        "$base/models/$class.php",
        "$base/controllers/$class.php",
        "$base/middleware/$class.php",
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Helpers globales ──────────────────────────────────────────

/** Respuesta JSON estandarizada */
function jsonResponse(bool $success, string $message, mixed $data = null, int $code = 200): never {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Lee y valida el body JSON del request */
function getJsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Sanitiza un string de entrada */
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}
