<?php
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/helpers.php';

secure_session_start();

if (!csrf_verify()) {
    $_SESSION['flash_error'] = 'Error de validación de sesión. Intente de nuevo.';
    header('Location: login.php');
    exit;
}

$pdo = getConnection();

$username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? '');
$password = (string) filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING) ?? '';

if ($username === '' || $password === '') {
    $_SESSION['flash_error'] = 'Debe ingresar usuario y contraseña.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT idUsuarios, nombre_usuario, password, rol, estado
                       FROM usuarios
                       WHERE nombre_usuario = :u
                       LIMIT 1");
$stmt->execute([':u' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['flash_error'] = 'Credenciales incorrectas.';
    header('Location: login.php');
    exit;
}

if (($user['estado'] ?? 'activo') !== 'activo') {
    $_SESSION['flash_error'] = 'Su cuenta está inactiva. Contacte al administrador.';
    header('Location: login.php');
    exit;
}

if (!password_verify($password, $user['password'])) {
    $_SESSION['flash_error'] = 'Credenciales incorrectas.';
    header('Location: login.php');
    exit;
}

session_regenerate_id(true);

$_SESSION['user'] = [
    'id'   => (int)$user['idUsuarios'],
    'name' => $user['nombre_usuario'],
    'rol'  => $user['rol'] ?? 'usuario',
];

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE idUsuarios = ?");
    $stmt->execute([$user['idUsuarios']]);
} catch (Exception $e) {
    error_log("Error al actualizar ultimo_acceso: " . $e->getMessage());
}

header('Location: index.php');
exit;