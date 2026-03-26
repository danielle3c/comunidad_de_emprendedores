<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
include 'config.php'; 

$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
$moneda = $cfg['simbolo_moneda'] ?? "$"; 

$mensaje = "";
$nuevo_id = null;

if(isset($_POST['activar'])){
    $id_contrato = mysqli_real_escape_string($conexion, $_POST['id_contrato']);
    $cuota_mensual = mysqli_real_escape_string($conexion, $_POST['cuota']);
    $dia_pago = mysqli_real_escape_string($conexion, $_POST['dia']);

    $res_info = mysqli_query($conexion, "SELECT monto_total, emprendedores_idemprendedores FROM Contratos WHERE idContratos = '$id_contrato'");
    
    if($info = mysqli_fetch_assoc($res_info)) {
        $monto = $info['monto_total'];
        $emp_id = $info['emprendedores_idemprendedores'];

        $chequeo = mysqli_query($conexion, "SELECT idcreditos FROM creditos WHERE emprendedores_idemprendedores = '$emp_id' AND saldo_inicial > 0 AND estado = 1");

        if(mysqli_num_rows($chequeo) > 0) {
            $mensaje = "<div class='alert error'><i class='fas fa-exclamation-triangle'></i> El emprendedor ya tiene una deuda pendiente.</div>";
        } else {
            $sql = "INSERT INTO creditos (monto_inicial, saldo_inicial, fecha_inicio, estado, dia_de_pago, cuota_mensual, Contratos_idContratos, emprendedores_idemprendedores, created_at) 
                    VALUES ('$monto', '$monto', NOW(), 1, '$dia_pago', '$cuota_mensual', '$id_contrato', '$emp_id', NOW())";
            
            if(mysqli_query($conexion, $sql)) {
                $nuevo_id = mysqli_insert_id($conexion);
                $mensaje = "<div class='alert success'>
                                <i class='fas fa-check-circle'></i> Crédito activado.<br><br>
                                <a href='pdf_cronograma.php?id=$nuevo_id' target='_blank' class='btn-pdf'><i class='fas fa-file-pdf'></i> Descargar Cronograma</a>
                            </div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Activar Créditos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #f3f6f9; --white: #ffffff; --primary: #55b83e; --success: #2ecc71; --text: #333; --border: #e3e6f0; }
        [data-theme="dark"] { --bg: #1a202c; --white: #55b83e; --text: #edf2f7; --border: #4a5568; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; max-width: 900px; margin: 0 auto 30px; }
        .btn-nav { background: var(--white); border: 1px solid var(--border); padding: 10px 20px; border-radius: 10px; text-decoration: none; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card { background: var(--white); padding: 30px; border-radius: 15px; max-width: 500px; margin: auto; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        label { font-weight: 600; font-size: 0.85rem; color: var(--primary); display: block; margin-bottom: 5px; text-transform: uppercase; }
        input, select { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid var(--border); background: var(--white); color: var(--text); box-sizing: border-box; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1rem; }
        .alert { padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 20px; }
        .success { background: #dcfce7; color: #55b83e; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .btn-pdf { display: inline-block; margin-top: 10px; background: #e74c3c; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php" class="btn-nav"><i class="fas fa-home"></i> Inicio</a>
    <div style="font-weight:bold; font-size:1.2rem;"><i class="fas fa-plus-circle"></i> Nuevo Crédito</div>
    <a href="historial_creditos.php" class="btn-nav"><i class="fas fa-history"></i> Historial</a>
</div>

<div class="card">
    <?php echo $mensaje; ?>
    <form method="POST">
        <label>Contrato Firmado:</label>
        <select name="id_contrato" required>
            <option value="">-- Seleccione un Contrato --</option>
            <?php
            $sql_pendientes = "SELECT c.idContratos, p.nombres, c.monto_total 
                               FROM Contratos c 
                               JOIN emprendedores e ON c.emprendedores_idemprendedores = e.idemprendedores 
                               JOIN personas p ON e.personas_idpersonas = p.idpersonas 
                               LEFT JOIN creditos cr ON c.idContratos = cr.Contratos_idContratos 
                               WHERE cr.idcreditos IS NULL";
            $res = mysqli_query($conexion, $sql_pendientes);
            while($c = mysqli_fetch_assoc($res)) {
                echo "<option value='{$c['idContratos']}'>#{$c['idContratos']} - {$c['nombres']} ({$moneda}{$c['monto_total']})</option>";
            }
            ?>
        </select>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label>Cuota Mensual:</label>
                <input type="number" step="0.01" name="cuota" placeholder="0.00" required>
            </div>
            <div>
                <label>Día de Pago:</label>
                <input type="number" name="dia" min="1" max="30" placeholder="Ej: 10" required>
            </div>
        </div>

        <button type="submit" name="activar" class="btn-submit">
            <i class="fas fa-save"></i> Guardar y Activar
        </button>
    </form>
</div>
</body>
</html> 