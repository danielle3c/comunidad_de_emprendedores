<?php
include 'config.php';

// Verificamos que el ID venga por la URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);

    // Marcamos el usuario como eliminado (borrado lógico)
    // Ponemos NOW() para saber CUÁNDO se eliminó
    $sql = "UPDATE Usuarios SET deleted_at = NOW(), estado = 0 WHERE idUsuarios = '$id'";

    if (mysqli_query($conexion, $sql)) {
        // Redirigimos a la lista con un aviso
        header("Location: usuarios_lista.php?status=deleted");
    } else {
        echo "Error al eliminar: " . mysqli_error($conexion);
    }
} else {
    // Si no hay ID, volvemos a la lista
    header("Location: usuarios_lista.php");
}
exit();
