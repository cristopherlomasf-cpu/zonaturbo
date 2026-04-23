<?php
/**
 * ZONA TURBO - Endpoint de Autenticación
 * POST /api/auth/login
 * POST /api/auth/logout
 * GET  /api/auth/verify
 */

require_once dirname(__DIR__) . '/config/app.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$action = explode('/', $uri)[2] ?? '';

match ("$method:$action") {
    'POST:login'  => handleLogin(),
    'POST:logout' => handleLogout(),
    'GET:verify'  => handleVerify(),
    default       => jsonResponse(false, 'Ruta no encontrada.', null, 404)
};

// ──────────────────────────────────────────────────────────────
// LOGIN
// ──────────────────────────────────────────────────────────────
function handleLogin(): never {
    $body  = getJsonBody();
    $email = sanitize($body['email'] ?? '');
    $pass  = $body['password'] ?? '';

    if (!$email || !$pass) {
        jsonResponse(false, 'Email y contraseña son requeridos.', null, 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Email inválido.', null, 422);
    }

    $pdo = Database::getInstance()->getConnection();

    $stmt = $pdo->prepare(
        'SELECT id, nombre, email, password, rol, telefono, activo
         FROM usuarios WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !Auth::verifyPassword($pass, $user['password'])) {
        // Mismo mensaje para no revelar si el email existe
        jsonResponse(false, 'Credenciales incorrectas.', null, 401);
    }

    if (!$user['activo']) {
        jsonResponse(false, 'Cuenta desactivada. Contacte al administrador.', null, 403);
    }

    $token = Auth::generateToken([
        'id'     => $user['id'],
        'nombre' => $user['nombre'],
        'email'  => $user['email'],
        'rol'    => $user['rol'],
    ]);

    jsonResponse(true, 'Sesión iniciada correctamente.', [
        'token' => $token,
        'user'  => [
            'id'       => $user['id'],
            'nombre'   => $user['nombre'],
            'email'    => $user['email'],
            'rol'      => $user['rol'],
            'telefono' => $user['telefono'],
        ]
    ]);
}

// ──────────────────────────────────────────────────────────────
// LOGOUT (el cliente solo elimina el token localmente)
// ──────────────────────────────────────────────────────────────
function handleLogout(): never {
    Auth::require(); // Valida que tenga token activo
    jsonResponse(true, 'Sesión cerrada. Elimine el token en el cliente.');
}

// ──────────────────────────────────────────────────────────────
// VERIFY - Valida si el token sigue vigente
// ──────────────────────────────────────────────────────────────
function handleVerify(): never {
    $data = Auth::require();
    jsonResponse(true, 'Token válido.', [
        'id'     => $data['id'],
        'nombre' => $data['nombre'],
        'rol'    => $data['rol'],
        'exp'    => $data['exp'],
    ]);
}
