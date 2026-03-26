<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Jornadas de Trabajo</title></head>
<body style="font-family: sans-serif; padding: 20px; background: #fff7ed;">
    <div style="background: white; padding: 20px; border-radius: 10px; max-width: 500px; margin: auto; border-top: 5px solid #f97316;">
        <h2>Registro de Jornada</h2>
        <form method="POST">
            <label>Seleccionar Carrito:</label>
            <select name="id_car" required style="width:100%; padding:10px; margin:10px 0;">
                <?php
                $res = mysqli_query($conexion, "SELECT idcarritos, nombre_carrito FROM carritos WHERE deleted_at = 0");
                while($c = mysqli_fetch_assoc($res)) echo "<option value='{$c['idcarritos']}'>{$c['nombre_carrito']}</option>";
                ?>
            </select>
            <label>Hora Apertura:</label>
            <input type="time" name="abre" required style="width:100%; padding:10px; margin:10px 0;">
            <label>Hora Cierre:</label>
            <input type="time" name="cierra" required style="width:100%; padding:10px; margin:10px 0;">
            
            <button type="submit" name="save_j" style="width:100%; background:#f97316; color:white; border:none; padding:12px; cursor:pointer;">Registrar Jornada</button>
        </form>

        <?php
        if(isset($_POST['save_j'])){
            $idc = $_POST['id_car']; $abre = $_POST['abre']; $cierre = $_POST['cierre'];
            $sql = "INSERT INTO jornadas_carritos (fecha, hora_apertura, hora_cierre, estado, carritos_idcarritos, created_at) 
                    VALUES (CURDATE(), '$abre', '$cierre', 1, '$idc', NOW())";
            if(mysqli_query($conexion, $sql)) echo "Jornada guardada.";
        }
        ?>
    </div>
</body>
</html>