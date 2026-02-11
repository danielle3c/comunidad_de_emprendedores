<?php
// config.php
$conexion = mysqli_connect("localhost", "root", "", "comunidad_de_emprendedores");


if (!$conexion) {
    die("Fallo de conexión: " . mysqli_connect_error());
}

// Soporte para tildes y eñes
mysqli_set_charset($conexion, "utf8mb4");

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$pagina_actual = basename($_SERVER['PHP_SELF']);
$paginas_permitidas = ['login.php', 'usuarios_agregar.php', 'cambiar_clave.php'];

if (!isset($_SESSION['usuario_id']) && !in_array($pagina_actual, $paginas_permitidas)) {
    header("Location: login.php");
    exit();
}
?>