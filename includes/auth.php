<?php
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/helpers.php';

secure_session_start();

// Verificar token CSRF
csrf_verify();

$pdo = getConnection();

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

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

if (!$user || ($user['estado'] ?? 'activo') !== 'activo') {
    $_SESSION['flash_error'] = 'Usuario no válido o inactivo.';
    header('Location: login.php');
    exit;
}

if (!password_verify($password, $user['password'])) {
    $_SESSION['flash_error'] = 'Contraseña incorrecta.';
    header('Location: login.php');
    exit;
}

// Regenerar ID de sesión (previene session fixation)
session_regenerate_id(true);

$_SESSION['user'] = [
    'id'   => (int)$user['idUsuarios'],
    'name' => $user['nombre_usuario'],
    'rol'  => $user['rol'] ?? 'usuario',
];

// Actualizar último acceso (no crítico si la columna no existe)
try {
    $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE idUsuarios = ?")
        ->execute([$user['idUsuarios']]);
} catch (Exception $e) {}

header('Location: index.php');
exit;
