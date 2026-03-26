<?php 
include 'config.php'; 

// 1. OBTENER CONFIGURACIÓN PARA EL TEMA VISUAL
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

$mensaje = "";

// 2. PROCESAR EL REGISTRO
if(isset($_POST['btn_e'])){
    $idp   = mysqli_real_escape_string($conexion, $_POST['personas_idpersonas']);
    $tipo  = mysqli_real_escape_string($conexion, $_POST['tipo_negocio']);
    $rubro = mysqli_real_escape_string($conexion, $_POST['rubro']);
    $prod  = mysqli_real_escape_string($conexion, $_POST['producto_principal']);
    $lim   = mysqli_real_escape_string($conexion, $_POST['limite_credito']);

    // Verificar si ya es emprendedor para evitar duplicados
    $check = mysqli_query($conexion, "SELECT idemprendedores FROM emprendedores WHERE personas_idpersonas = '$idp'");
    
    if(mysqli_num_rows($check) > 0){
        $mensaje = "<div class='alert error'>Esta persona ya está registrada como emprendedor.</div>";
    } else {
        $sql = "INSERT INTO emprendedores (personas_idpersonas, tipo_negocio, rubro, producto_principal, limite_credito, fecha_registro, created_at) 
                VALUES ('$idp', '$tipo', '$rubro', '$prod', '$lim', NOW(), NOW())";

        if(mysqli_query($conexion, $sql)){
            $mensaje = "<div class='alert success'>Emprendedor vinculado exitosamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>Error: " . mysqli_error($conexion) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Emprendedor - <?php echo $cfg['nombre_sistema']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #f8fafc; --card: #ffffff; --text: #1e293b; --primary: #55b83e; --border: #e2e8f0; --input-bg: #ffffff;
        }
        [data-theme="dark"] { 
            --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; --primary: #55b83e; --border: #334155; --input-bg: #0f172a;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); padding: 20px; transition: 0.3s; }
        
        .container { 
            max-width: 550px; margin: 40px auto; background: var(--card); padding: 35px; 
            border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid var(--border);
        }
        
        h2 { text-align: center; color: var(--primary); margin-bottom: 25px; font-weight: 700; }
        
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 7px; font-weight: 600; font-size: 0.85rem; }
        
        input, select { 
            width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; 
            background: var(--input-bg); color: var(--text); font-size: 1rem; box-sizing: border-box; transition: 0.2s;
        }
        
        input:focus, select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1); }

        .btn-submit { 
            background: var(--primary); color: white; border: none; padding: 16px; 
            width: 100%; border-radius: 12px; cursor: pointer; font-weight: 700; 
            font-size: 1.1rem; margin-top: 20px; transition: 0.3s;
        }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Alertas */
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .success { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); }
        .error { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }

        .footer-nav { display: flex; gap: 10px; margin-top: 25px; }
        .btn-nav { 
            flex: 1; text-align: center; padding: 12px; text-decoration: none; border-radius: 10px; 
            font-size: 0.9rem; font-weight: 600; background: var(--border); color: var(--text) !important; 
        }
        .btn-nav:hover { background: var(--primary); color: white !important; }
    </style>
</head>
<body>

<div class="container">
    <h2>Vincular Negocio</h2>
    
    <?php echo $mensaje; ?>

    <form method="POST">
        <div class="form-group">
            <label>Seleccionar Persona:</label>
            <select name="personas_idpersonas" required>
                <option value="">-- Seleccione una persona --</option>
                <?php
                // EXPLICACIÓN: Se cambió 'HERE' por 'WHERE' y se optimizó la verificación de deleted_at
                $query = "SELECT idpersonas, nombres, apellidos, rut 
                        FROM personas 
                        WHERE (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00') 
                        ORDER BY nombres ASC";
                
                $res = mysqli_query($conexion, $query);

                if ($res && mysqli_num_rows($res) > 0) {
                    while($p = mysqli_fetch_assoc($res)){
                        echo "<option value='{$p['idpersonas']}'>{$p['nombres']} {$p['apellidos']} ({$p['rut']})</option>";
                    }
                } else {
                    echo "<option value=''>No se encontraron personas activas</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tipo de Negocio:</label>
            <input type="text" name="tipo_negocio" placeholder="Ej: Almacén, Taller, Bazar" required>
        </div>

        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label> Rubro:</label>
                <input type="text" name="rubro" placeholder="Ej: Alimentación" required>
            </div>

            <div class="form-group">
                <label> Límite Crédito ($):</label>
                <input type="number" name="limite_credito" placeholder="0" step="1" required>
            </div>
        </div>

        <div class="form-group">
            <label>Producto / Servicio Principal:</label>
            <input type="text" name="producto_principal" placeholder="¿Qué es lo que más vende?" required>
        </div>

        <button type="submit" name="btn_e" class="btn-submit">Vincular como Emprendedor</button>
    </form>

    <div class="footer-nav">
        <a href="index.php" class="btn-nav">Inicio</a>
        <a href="emprendedores.php" class="btn-nav">Ver Lista</a>
    </div>
</div>

</body>
</html>