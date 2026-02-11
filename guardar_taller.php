<?php
// 1. Datos de conexión
$host = "localhost";
$user = "root";    
$pass = "";        
$db   = "bd_comunidad"; 

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fecha          = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora           = mysqli_real_escape_string($conexion, $_POST['hora']);
    $nombre_taller  = mysqli_real_escape_string($conexion, $_POST['nombre_taller']);
    $relator        = mysqli_real_escape_string($conexion, $_POST['relator']);
    $id_emprendedor = mysqli_real_escape_string($conexion, $_POST['id_emprendedor']);
    $notas          = mysqli_real_escape_string($conexion, $_POST['notas']);

    $sql = "INSERT INTO talleres (fecha, hora, nombre_taller, relator, id_emprendedor, notas) 
            VALUES ('$fecha', '$hora', '$nombre_taller', '$relator', '$id_emprendedor', '$notas')";

    if (mysqli_query($conexion, $sql)) {
        // REDIRECCIÓN QUE NO DEJA RASTRO EN EL HISTORIAL
        echo "<script>
            window.location.replace('https://nonpolarizable-neil-wondrously.ngrok-free.dev/comunidad/talleres.php');
        </script>";
        exit();
    } else {
        echo "Error al guardar: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);
?>