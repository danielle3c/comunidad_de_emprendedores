<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Administrar Talleres</title></head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2> Registro de Nuevos Talleres</h2>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre del Taller" required><br><br>
        <input type="date" name="fecha" required><br><br>
        <input type="text" name="lugar" placeholder="Lugar"><br><br>
        <textarea name="desc" placeholder="Descripción"></textarea><br><br>
        <button type="submit" name="new_t">Crear Taller</button>
    </form>

    <?php
    if(isset($_POST['new_t'])){
        $nom = $_POST['nombre']; $fec = $_POST['fecha']; $lug = $_POST['lugar']; $des = $_POST['desc'];
        $sql = "INSERT INTO talleres (nombre, fecha, lugar, descripcion, created_at) VALUES ('$nom', '$fec', '$lug', '$des', NOW())";
        if(mysqli_query($conexion, $sql)) echo " Taller creado.";
    }
    ?>
</body>
</html>