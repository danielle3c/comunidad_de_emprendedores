<?php
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

secure_session_start();
csrf_verify();

$pdo = getConnection();

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['flash_error'] = 'Debe ingresar usuario y contraseña.';
    header('Location: login.php');
    exit;
}

/* ==========================
   PROTECCIÓN BÁSICA ANTI-BRUTEFORCE
========================== */

if (!isset($_SESSION['_login_attempts'])) {
    $_SESSION['_login_attempts'] = 0;
}

if ($_SESSION['_login_attempts'] >= 5) {
    $_SESSION['flash_error'] = 'Demasiados intentos. Espere unos minutos.';
    header('Location: login.php');
    exit;
}

/* ==========================
   CONSULTA SEGURA
========================== */

$stmt = $pdo->prepare("
    SELECT idUsuarios, nombre_usuario, password, rol, estado
    FROM usuarios
    WHERE nombre_usuario = :user
    LIMIT 1
");

$stmt->execute([':user' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || ($user['estado'] ?? 'activo') !== 'activo') {
    $_SESSION['_login_attempts']++;
    $_SESSION['flash_error'] = 'Credenciales incorrectas.';
    header('Location: login.php');
    exit;
}

if (!password_verify($password, $user['password'])) {
    $_SESSION['_login_attempts']++;
    $_SESSION['flash_error'] = 'Credenciales incorrectas.';
    header('Location: login.php');
    exit;
}

/* ==========================
   LOGIN CORRECTO
========================== */

session_regenerate_id(true);
$_SESSION['_login_attempts'] = 0;

$_SESSION['user'] = [
    'id'   => (int)$user['idUsuarios'],
    'name' => $user['nombre_usuario'],
    'rol'  => $user['rol'] ?? 'usuario'
];

header('Location: index.php');
exit;
