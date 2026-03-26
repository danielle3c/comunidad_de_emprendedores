<?php 
include 'config.php'; 

// 1. Obtener configuración para el tema visual
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

$mensaje = "";

if(isset($_POST['reg_c'])){
    $ide = mysqli_real_escape_string($conexion, $_POST['id_emp']); 
    $nom = mysqli_real_escape_string($conexion, $_POST['nom_c']); 
    $equ = mysqli_real_escape_string($conexion, $_POST['equipo']);

    $sql = "INSERT INTO carritos (nombre_carrito, equipamiento, emprendedores_idemprendedores, created_at) 
            VALUES ('$nom', '$equ', '$ide', NOW())";

    if(mysqli_query($conexion, $sql)){
        $mensaje = "<div class='alert success'>Carrito registrado y asignado con éxito.</div>";
    } else {
        $mensaje = "<div class='alert error'>Error al registrar: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Carritos - <?php echo $cfg['nombre_sistema']; ?></title>
    <style>
        :root { --bg: #f4f7f6; --text: #333; --card: #fff; --primary: #55b83e; }
        [data-theme="dark"] { --bg: #1a1a1a; --text: #f0f0f0; --card: #2d2d2d; --primary: #2ecc71; }
        
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding: 20px; }
        .box { 
            background: var(--card); padding: 35px; border-radius: 15px; 
            max-width: 550px; margin: 40px auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
        }
        
        h2 { text-align: center; color: var(--primary); margin-bottom: 25px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 0.9em; }
        
        input, select, textarea { 
            width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; 
            border-radius: 8px; box-sizing: border-box; background: var(--card); color: var(--text);
            font-size: 1em;
        }

        textarea { height: 100px; resize: vertical; }

        button { 
            background: var(--primary); color: white; border: none; padding: 15px; 
            width: 100%; border-radius: 10px; cursor: pointer; font-weight: bold; 
            font-size: 1.1em; transition: 0.3s;
        }
        button:hover { opacity: 0.9; transform: translateY(-2px); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .success { background: #dcfce7; color: #55b83e; border: 1px solid #86efac; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        
        .footer { text-align: center; margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px; }
        .footer a { color: var(--text); text-decoration: none; opacity: 0.7; font-size: 0.9em; font-weight: bold; }
    </style>
</head>
<body>

<div class="box">
    <h2>Asignación de Carritos</h2>
    
    <?php echo $mensaje; ?>

    <form method="POST">
        <label>Responsable (Emprendedor):</label>
        <select name="id_emp" required>
            <option value="">-- Seleccionar Emprendedor --</option>
            <?php
            $res = mysqli_query($conexion, "SELECT e.idemprendedores, p.nombres, p.apellidos 
                                        FROM emprendedores e 
                                        JOIN personas p ON e.personas_idpersonas = p.idpersonas");
            while($e = mysqli_fetch_assoc($res)) {
                echo "<option value='{$e['idemprendedores']}'>{$e['nombres']} {$e['apellidos']}</option>";
            }
            ?>
        </select>
        
        <label>Nombre o ID del Carrito:</label>
        <input type="text" name="nom_c" placeholder="Ej: Puesto Móvil #05 - Plaza Norte" required>
        
        <label>Inventario de Equipamiento:</label>
        <textarea name="equipo" placeholder="Ej: 1 Cocina industrial, 2 tanques de gas, 1 extintor..."></textarea>
        
        <button type="submit" name="reg_c">Registrar Carrito</button>
    </form>

    <div class="footer">
        <a href="index.php">Volver al Inicio</a>
    </div>
</div>

</body>
</html>