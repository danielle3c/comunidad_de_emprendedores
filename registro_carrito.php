<?php 
include 'config.php'; 
if(isset($_POST['save_car'])){
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_persona']); 
    $telef = mysqli_real_escape_string($conexion, $_POST['telefono']); 
    $carro = mysqli_real_escape_string($conexion, $_POST['nombre_c']); 
    $asist = $_POST['asistencia'];
    $fecha_final = $_POST['fecha_reg'] . " " . $_POST['hora_ingreso'] . ":00";
    $salida = $_POST['hora_salida'];

    $sql = "INSERT INTO carritos (nombre_responsable, telefono_responsable, nombre_carrito, asistencia, created_at, hora_salida) 
            VALUES ('$nombre', '$telef', '$carro', '$asist', '$fecha_final', '$salida')";

    if(mysqli_query($conexion, $sql)) header("Location: carritos.php");
}
?>