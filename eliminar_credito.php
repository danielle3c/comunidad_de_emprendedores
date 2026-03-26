<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { exit("Acceso denegado"); }
include 'config.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    
    // Primero eliminamos las cobranzas asociadas a ese crédito para no dejar datos huérfanos
    mysqli_query($conexion, "DELETE FROM cobranzas WHERE creditos_idcreditos = '$id'");
    
    // Luego eliminamos el crédito
    mysqli_query($conexion, "DELETE FROM creditos WHERE idcreditos = '$id'");
}

// Volvemos automáticamente al historial
header("Location: historial_creditos.php");
exit();