<?php 
include 'config.php'; 

// Obtener configuración para el tema visual
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

$mensaje = "";

if(isset($_POST['save_con'])){
    $ide = mysqli_real_escape_string($conexion, $_POST['id_emprendedor']);
    $monto = mysqli_real_escape_string($conexion, $_POST['monto_total']);
    $plazo = mysqli_real_escape_string($conexion, $_POST['plazo']);
    
    // Insertamos con estado 1 (Activo)
    $sql = "INSERT INTO Contratos (fecha_firma, monto_total, plazo_meses, total_pagado, emprendedores_idemprendedores, created_at, estado) 
            VALUES (CURDATE(), '$monto', '$plazo', 0, '$ide', NOW(), 1)";
    
    if(mysqli_query($conexion, $sql)){
        $mensaje = "<div class='alert success'>Contrato N° ".mysqli_insert_id($conexion)." generado y firmado con éxito.</div>";
    } else {
        $mensaje = "<div class='alert error'>Error al registrar: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Generar Contrato - <?php echo $cfg['nombre_sistema']; ?></title>
    <style>
        :root { --bg: #f4f7f6; --text: #333; --card: #fff; --primary: #55b83e; --accent: #2c3e50; }
        [data-theme="dark"] { --bg: #1a1a1a; --text: #f0f0f0; --card: #2d2d2d; --primary: #2ecc71; --accent: #ecf0f1; }
        
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding: 20px; }
        .container { max-width: 550px; margin: auto; background: var(--card); padding: 35px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        h2 { text-align: center; color: var(--accent); margin-bottom: 5px; }
        p.subtitle { text-align: center; color: var(--primary); font-weight: bold; margin-bottom: 25px; font-size: 0.9em; }
        
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 7px; font-size: 0.85em; text-transform: uppercase; letter-spacing: 0.5px; }
        
        input, select { 
            width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; 
            box-sizing: border-box; background: var(--card); color: var(--text); font-size: 1em;
            transition: border-color 0.3s;
        }
        input:focus { border-color: var(--primary); outline: none; }

        .calc-info { 
            background: rgba(67, 176, 42, 0.1); padding: 15px; border-radius: 10px; 
            margin: 20px 0; font-size: 0.9em; border: 1px dashed var(--primary);
        }

        .btn-save { 
            background: var(--primary); color: white; border: none; padding: 16px; 
            width: 100%; border-radius: 10px; cursor: pointer; font-weight: bold; 
            font-size: 1.1em; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-save:hover { filter: brightness(1.1); transform: translateY(-2px); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .success { background: #dcfce7; color: #55b83e; border: 1px solid #86efac; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        
        .footer { margin-top: 25px; text-align: center; display: flex; justify-content: center; gap: 20px; }
        .footer a { color: var(--primary); text-decoration: none; font-size: 0.9em; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <h2>Contrato de Financiamiento</h2>
    <p class="subtitle">SISTEMA DE GESTIÓN DE CRÉDITOS</p>
    
    <?php echo $mensaje; ?>

    <form method="POST">
        <div class="form-group">
            <label>Socio / Emprendedor:</label>
            <select name="id_emprendedor" required>
                <option value="">Seleccione al titular...</option>
                <?php
                $res = mysqli_query($conexion, "SELECT e.idemprendedores, p.nombres, p.apellidos, e.limite_credito 
                                            FROM emprendedores e 
                                            JOIN personas p ON e.personas_idpersonas = p.idpersonas 
                                            WHERE p.deleted_at IS NULL");
                while($e = mysqli_fetch_assoc($res)){
                    echo "<option value='{$e['idemprendedores']}'>{$e['nombres']} {$e['apellidos']} (Máx: \${$e['limite_credito']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Monto a Financiar ($):</label>
            <input type="number" step="0.01" name="monto_total" id="monto" placeholder="0.00" required>
        </div>

        <div class="form-group">
            <label>Plazo de Devolución (Meses):</label>
            <input type="number" name="plazo" id="plazo" placeholder="Ej: 12" required>
        </div>

        <div class="calc-info" id="simulador">
            Cuota estimada: <b>$0.00</b> al mes.
        </div>

        <button type="submit" name="save_con" class="btn-save">
            Firmar y Registrar Contrato
        </button>
    </form>

    <div class="footer">
        <a href="index.php">Inicio</a>
        <a href="contratos_lista.php">Ver todos</a>
    </div>
</div>

<script>
    // Pequeño script para calcular cuota en tiempo real
    const montoInput = document.getElementById('monto');
    const plazoInput = document.getElementById('plazo');
    const simulador = document.getElementById('simulador');

    function actualizarCuota() {
        const monto = parseFloat(montoInput.value) || 0;
        const plazo = parseInt(plazoInput.value) || 0;
        if(monto > 0 && plazo > 0) {
            const cuota = (monto / plazo).toFixed(2);
            simulador.innerHTML = `Cuota estimada: <b>$${cuota}</b> al mes por ${plazo} meses.`;
        }
    }

    montoInput.addEventListener('input', actualizarCuota);
    plazoInput.addEventListener('input', actualizarCuota);
</script>

</body>
</html>