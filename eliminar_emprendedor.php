<?php
include 'config.php';

if (isset($_GET['id'])) {
    // 1. Limpiar el ID para evitar inyecciones SQL
    $id_emprendedor = mysqli_real_escape_string($conexion, $_GET['id']);

    // 2. Ejecutar el borrado lógico (ponemos deleted_at en 1 o la fecha actual)
    // Asumiendo que tu tabla tiene la columna deleted_at
    $sql = "UPDATE emprendedores SET deleted_at = 1 WHERE idemprendedores = '$id_emprendedor'";

    if (mysqli_query($conexion, $sql)) {
        // 3. Redirigir al index con un mensaje de éxito
        header("Location: index.php?msg=emprendedor_eliminado");
        exit();
    } else {
        echo "Error al eliminar el negocio: " . mysqli_error($conexion);
    }
} else {
    header("Location: index.php");
    exit();
}
?>