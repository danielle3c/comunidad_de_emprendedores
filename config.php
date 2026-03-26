<?php
// config.php
$conexion = mysqli_connect("localhost", "root", "", "comunidad_de_emprendedores");

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$pagina_actual = basename($_SERVER['PHP_SELF']);

// Agregamos 'cambiar_clave.php' a la lista de permitidos sin estar logueado
$paginas_permitidas = ['login.php', 'usuarios_agregar.php', 'cambiar_clave.php'];

if (!isset($_SESSION['usuario_id']) && !in_array($pagina_actual, $paginas_permitidas)) {
    header("Location: login.php");
    exit();
}
?>