<?php
session_start();

require_once __DIR__ . '/includes/helpers.php'; // <-- si existe en tu proyecto
$pdo = getConnection();

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
  $_SESSION['flash_error'] = 'Debe ingresar usuario y contraseña.';
  header('Location: login.php');
  exit;
}

/*
  EJEMPLO DE TABLA:
  usuarios: idusuarios, nombre_usuario, password, rol, estado

  - password debe estar con password_hash()
*/
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

// Regenerar ID de sesión (seguridad)
session_regenerate_id(true);

$_SESSION['user'] = [
  'id'   => (int)$user['idUsuarios'],
  'name' => $user['nombre_usuario'],
  'rol'  => $user['rol'] ?? 'usuario'
];

header('Location: index.php');
exit;
