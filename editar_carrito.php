<?php 
include 'config.php'; 

// 1. Cargar configuración y datos del carrito
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

$mensaje = "";

if(isset($_GET['id'])){
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $res_car = mysqli_query($conexion, "SELECT * FROM carritos WHERE id = '$id'");
    $car = mysqli_fetch_assoc($res_car);

    // Separar fecha y hora para los inputs
    $fecha_solo = date('Y-m-d', strtotime($car['created_at']));
    $hora_solo  = date('H:i', strtotime($car['created_at']));
}

// 2. Lógica para actualizar el registro
if(isset($_POST['update_car'])){
    $id             = $_POST['id'];
    $nombre_persona = mysqli_real_escape_string($conexion, $_POST['nombre_persona']); 
    $telefono       = mysqli_real_escape_string($conexion, $_POST['telefono']); 
    $nom_carrito    = mysqli_real_escape_string($conexion, $_POST['nombre_c']); 
    $des            = mysqli_real_escape_string($conexion, $_POST['desc']); 
    $equ            = mysqli_real_escape_string($conexion, $_POST['equip']);
    $ast            = mysqli_real_escape_string($conexion, $_POST['asistencia']); 
    
    $fecha_reg      = mysqli_real_escape_string($conexion, $_POST['fecha_reg']);
    $hora_ingreso   = mysqli_real_escape_string($conexion, $_POST['hora_ingreso']);
    $hora_salida    = mysqli_real_escape_string($conexion, $_POST['hora_salida']);
    
    $fecha_final    = $fecha_reg . " " . $hora_ingreso . ":00";

    $sql = "UPDATE carritos SET 
            nombre_responsable = '$nombre_persona', 
            telefono_responsable = '$telefono', 
            nombre_carrito = '$nom_carrito', 
            descripcion = '$des', 
            equipamiento = '$equ', 
            asistencia = '$ast', 
            created_at = '$fecha_final', 
            hora_salida = '$hora_salida' 
            WHERE id = '$id'";

    if(mysqli_query($conexion, $sql)){
        $mensaje = "<div class='alert success'><i class='fas fa-sync'></i> Registro actualizado correctamente</div>";
        // Recargar datos actualizados
        $res_car = mysqli_query($conexion, "SELECT * FROM carritos WHERE id = '$id'");
        $car = mysqli_fetch_assoc($res_car);
    } else {
        $mensaje = "<div class='alert error'>Error: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Registro | <?php echo $cfg['nombre_sistema']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #f8fafc; --card: #ffffff; --text: #1e293b; --primary: #55b83e; --border: #e2e8f0; }
        [data-theme="dark"] { --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; --primary: #2ecc71; --border: #334155; }
        
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); padding: 20px; }
        .box { background: var(--card); padding: 30px; border-radius: 20px; max-width: 650px; margin: auto; border: 1px solid var(--border); box-shadow: 0 10px 15px rgba(0,0,0,0.05); }
        
        h2 { text-align: center; margin-bottom: 25px; color: var(--text); }
        label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: var(--primary); }
        
        input, textarea { width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid var(--border); border-radius: 10px; background: transparent; color: var(--text); font-size: 1rem; box-sizing: border-box; transition: 0.3s; }
        input:focus { border-color: var(--primary); outline: none; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .grid-3 { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 15px; }

        .asistencia-container { display: flex; gap: 10px; margin-bottom: 20px; }
        .asistencia-btn { flex: 1; border: 2px solid var(--border); padding: 15px; border-radius: 12px; text-align: center; cursor: pointer; font-weight: 700; transition: 0.3s; font-size: 0.85rem; }
        .asistencia-btn input { display: none; }
        
        /* Colores dinámicos */
        .asistencia-btn:has(input[value="SÍ VINO"]:checked) { background: #55b83e; color: white; border-color: #55b83e; }
        .asistencia-btn:has(input[value="TAL VEZ"]:checked) { background: #f39c12; color: white; border-color: #f39c12; }
        .asistencia-btn:has(input[value="NO VINO"]:checked) { background: #e74c3c; color: white; border-color: #e74c3c; }

        .btn-update { background: #3498db; color: white; border: none; padding: 18px; width: 100%; border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 1.1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 10px; }
        .btn-update:hover { opacity: 0.9; transform: translateY(-1px); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .success { background: rgba(34, 197, 94, 0.1); color: #55b83e; border: 1px solid #55b83e; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
        
        .footer-links { display: flex; justify-content: center; margin-top: 25px; }
        .footer-links a { text-decoration: none; color: var(--text); font-weight: 600; opacity: 0.6; font-size: 0.9rem; }
        .footer-links a:hover { opacity: 1; color: var(--primary); }
    </style>
</head>
<body>

<div class="box">
    <h2><i class="fas fa-edit"></i> Editar Registro</h2>
    
    <?php echo $mensaje; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $car['id']; ?>">

        <div class="grid-3">
            <div>
                <label>Fecha:</label>
                <input type="date" name="fecha_reg" value="<?php echo $fecha_solo; ?>" required>
            </div>
            <div>
                <label>Ingreso:</label>
                <input type="time" name="hora_ingreso" value="<?php echo $hora_solo; ?>" required>
            </div>
            <div>
                <label>Salida:</label>
                <input type="time" name="hora_salida" value="<?php echo $car['hora_salida']; ?>">
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label>Responsable:</label>
                <input type="text" name="nombre_persona" value="<?php echo $car['nombre_responsable']; ?>" required>
            </div>
            <div>
                <label>Teléfono:</label>
                <input type="tel" name="telefono" value="<?php echo $car['telefono_responsable']; ?>">
            </div>
        </div>

        <label>Estado de Asistencia:</label>
        <div class="asistencia-container">
            <label class="asistencia-btn">
                <input type="radio" name="asistencia" value="SÍ VINO" <?php echo ($car['asistencia'] == 'SÍ VINO') ? 'checked' : ''; ?>>SÍ VINO
            </label>
            <label class="asistencia-btn">
                <input type="radio" name="asistencia" value="TAL VEZ" <?php echo ($car['asistencia'] == 'TAL VEZ') ? 'checked' : ''; ?>>TAL VEZ
            </label>
            <label class="asistencia-btn">
                <input type="radio" name="asistencia" value="NO VINO" <?php echo ($car['asistencia'] == 'NO VINO') ? 'checked' : ''; ?>>NO VINO
            </label>
        </div>

        <label>Nombre del Carrito:</label>
        <input type="text" name="nombre_c" value="<?php echo $car['nombre_carrito']; ?>" required>

        <div class="grid-2">
            <div>
                <label>Estado Estético:</label>
                <textarea name="desc" rows="3"><?php echo $car['descripcion']; ?></textarea>
            </div>
            <div>
                <label>Equipamiento:</label>
                <textarea name="equip" rows="3"><?php echo $car['equipamiento']; ?></textarea>
            </div>
        </div>

        <button type="submit" name="update_car" class="btn-update">
            <i class="fas fa-save"></i> Actualizar Cambios
        </button>
    </form>

    <div class="footer-links">
        <a href="lista_carritos.php"><i class="fas fa-arrow-left"></i> Volver al Historial</a>
    </div>
</div>

</body>
</html>