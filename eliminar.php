<?php
include 'config.php';

// Verificamos que recibimos el ID por la URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);

    // 1. Ejecutamos la eliminación en la tabla carritos
    $sql = "DELETE FROM carritos WHERE id = '$id'";

    if (mysqli_query($conexion, $sql)) {
        // 2. Redirigir a la lista de carritos con un mensaje de éxito
        // Cambia 'carritos.php' por el nombre exacto de tu archivo de lista
        header("Location: carritos.php?msg=eliminado");
        exit(); 
    } else {
        echo "Error al eliminar: " . mysqli_error($conexion);
    }
} else {
    // Si alguien entra a eliminar.php sin un ID, lo mandamos al inicio
    header("Location: index.php");
    exit();
}
?>