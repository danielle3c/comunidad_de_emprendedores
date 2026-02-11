<?php 
include 'config.php'; 

// 1. Cargar configuración del sistema
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

$mensaje = "";

// 2. Lógica para guardar el registro
if(isset($_POST['save_car'])){
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

    $sql = "INSERT INTO carritos (nombre_responsable, telefono_responsable, nombre_carrito, descripcion, equipamiento, asistencia, created_at, hora_salida) 
            VALUES ('$nombre_persona', '$telefono', '$nom_carrito', '$des', '$equ', '$ast', '$fecha_final', '$hora_salida')";

    if(mysqli_query($conexion, $sql)){
        $mensaje = "<div class='alert success'><i class='fas fa-check-circle'></i> Registro guardado con éxito</div>";
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
    <title>Registro de Carritos | <?php echo $cfg['nombre_sistema']; ?></title>
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

        /* Contenedor de Asistencia */
        .asistencia-container { display: flex; gap: 10px; margin-bottom: 20px; }
        .asistencia-btn { flex: 1; border: 2px solid var(--border); padding: 15px; border-radius: 12px; text-align: center; cursor: pointer; font-weight: 700; transition: 0.3s; font-size: 0.85rem; }
        .asistencia-btn input { display: none; }
        
        /* Colores dinámicos para los botones de opción */
        .asistencia-btn:has(input[value="SÍ VINO"]:checked) { background: #55b83e; color: white; border-color: #55b83e; }
        .asistencia-btn:has(input[value="TAL VEZ"]:checked) { background: #f39c12; color: white; border-color: #f39c12; }
        .asistencia-btn:has(input[value="NO VINO"]:checked) { background: #e74c3c; color: white; border-color: #e74c3c; }

        .btn-save { background: var(--primary); color: white; border: none; padding: 18px; width: 100%; border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 1.1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 10px; }
        .btn-save:hover { opacity: 0.9; transform: translateY(-1px); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .success { background: rgba(34, 197, 94, 0.1); color: #55b83e; border: 1px solid #55b83e; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
        
        .footer-links { display: flex; justify-content: space-between; margin-top: 25px; }
        .footer-links a { text-decoration: none; color: var(--text); font-weight: 600; opacity: 0.6; font-size: 0.9rem; }
        .footer-links a:hover { opacity: 1; color: var(--primary); }
    </style>
</head>
<body>

<div class="box">
    <h2><i class="fas fa-clipboard-list"></i> Registro de Carritos</h2>
    
    <?php echo $mensaje; ?>

    <form method="POST">
        <div class="grid-3">
            <div>
                <label><i class="fas fa-calendar-alt"></i> Fecha:</label>
                <input type="date" name="fecha_reg" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div>
                <label><i class="fas fa-clock"></i> Ingreso:</label>
                <input type="time" name="hora_ingreso" value="<?php echo date('H:i'); ?>" required>
            </div>
            <div>
                <label><i class="fas fa-sign-out-alt"></i> Salida:</label>
                <input type="time" name="hora_salida" value="<?php echo date('H:i', strtotime('+4 hours')); ?>">
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label><i class="fas fa-user"></i> Responsable:</label>
                <input type="text" name="nombre_persona" placeholder="Nombre completo" required>
            </div>
            <div>
                <label><i class="fas fa-phone"></i> Teléfono:</label>
                <input type="tel" name="telefono" placeholder="Ej: 91234567890">
            </div>
        </div>

        <label><i class="fas fa-question-circle"></i> Estado de Asistencia:</label>
        <div class="asistencia-container">
            <label class="asistencia-btn">
                <input type="radio" name="asistencia" value="SÍ VINO" checked>SÍ VINO
            </label>
            <label class="asistencia-btn">
                <input type="radio" name="asistencia" value="TAL VEZ">TAL VEZ
            </label>
            <label class="asistencia-btn">
                <input type="radio" name="asistencia" value="NO VINO">NO VINO
            </label>
        </div>

        <label><i class="fas fa-store"></i> Identificación del Carrito:</label>
        <input type="text" name="nombre_c" placeholder="Ej: Carrito de fomento" required>

        <div class="grid-2">
            <div>
                <label>Estado Estético:</label>
                <textarea name="desc" rows="3" placeholder="Rayones, limpieza, etc."></textarea>
            </div>
            <div>
                <label>Equipamiento:</label>
                <textarea name="equip" rows="3" placeholder="Stock inicial..."></textarea>
            </div>
        </div>

        <button type="submit" name="save_car" class="btn-save">
            <i class="fas fa-save"></i> Guardar Registro
        </button>
    </form>

    <div class="footer-links">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
        <a href="lista_carritos.php">Ver Historial <i class="fas fa-history"></i></a>
    </div>
</div>

</body>
</html>