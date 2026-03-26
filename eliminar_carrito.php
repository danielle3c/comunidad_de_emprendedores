<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $sql = "DELETE FROM carritos WHERE id = '$id'";

    if (mysqli_query($conexion, $sql)) {
        header("Location: carritos.php?status=deleted"); 
        exit(); 
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
} else {
    header("Location: carritos.php");
    exit();
}
?>