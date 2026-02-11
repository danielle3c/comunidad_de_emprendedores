<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
include 'config.php'; 

$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
$moneda = $cfg['simbolo_moneda'];

$mensaje = "";
if(isset($_POST['pay'])){
    $idc = mysqli_real_escape_string($conexion, $_POST['id_credito']); 
    $mon = mysqli_real_escape_string($conexion, $_POST['monto_pago']); 
    $tip = mysqli_real_escape_string($conexion, $_POST['tipo_pago']);
    
    $ins = "INSERT INTO cobranzas (creditos_idcreditos, monto, tipo_pago, fecha_hora, created_at) VALUES ('$idc', '$mon', '$tip', NOW(), NOW())";
    if(mysqli_query($conexion, $ins)){
        mysqli_query($conexion, "UPDATE creditos SET saldo_inicial = saldo_inicial - $mon WHERE idcreditos = '$idc'");
        $check = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT saldo_inicial FROM creditos WHERE idcreditos = '$idc'"));
        if($check['saldo_inicial'] <= 0){
            mysqli_query($conexion, "UPDATE creditos SET estado = 2 WHERE idcreditos = '$idc'");
        }
        $mensaje = "<div class='alert success'>Pago de $moneda".number_format($mon,2)." registrado.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Registrar Cobro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #f3f6f9; --white: #ffffff; --primary: #4e73df; --success: #55b83e; --text: #333; --border: #55b83e; }
        [data-theme="dark"] { --bg: #1a202c; --white: #2d3748; --text: #edf2f7; --border: #4a5568; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; max-width: 900px; margin: 0 auto 30px; }
        .btn-nav { background: var(--white); border: 1px solid var(--border); padding: 10px 20px; border-radius: 10px; text-decoration: none; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card { background: var(--white); padding: 30px; border-radius: 15px; max-width: 500px; margin: auto; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        input, select { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid var(--border); background: var(--white); color: var(--text); box-sizing: border-box; }
        .btn-submit { background: var(--success); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .alert { background: #d4edda; color: #55b83e; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="navbar">
    <a href="index.php" class="btn-nav"><i class="fas fa-home"></i> Inicio</a>
    <div style="font-weight:bold;"><i class="fas fa-cash-register"></i> Registrar Cobranza</div>
    <a href="historial_cobros.php" class="btn-nav"><i class="fas fa-history"></i> Historial</a>
</div>
<div class="card">
    <?php echo $mensaje; ?>
    <form method="POST">
        <label>Seleccionar Deudor:</label>
        <select name="id_credito" required>
            <option value="">-- Buscar Cliente --</option>
            <?php
            $sql_c = "SELECT c.idcreditos, p.nombres, c.saldo_inicial FROM creditos c JOIN emprendedores e ON c.emprendedores_idemprendedores = e.idemprendedores JOIN personas p ON e.personas_idpersonas = p.idpersonas WHERE c.estado = 1";
            $res_c = mysqli_query($conexion, $sql_c);
            while($c = mysqli_fetch_assoc($res_c)){
                echo "<option value='{$c['idcreditos']}'>{$c['nombres']} ({$moneda}{$c['saldo_inicial']})</option>";
            }
            ?>
        </select>
        <label>Monto:</label>
        <input type="number" step="0.01" name="monto_pago" required>
        <label>Método:</label>
        <select name="tipo_pago">
            <option value="Efectivo">Efectivo</option>
            <option value="Transferencia">Transferencia</option>
        </select>
        <button type="submit" name="pay" class="btn-submit">Confirmar Pago</button>
    </form>
</div>
</body>
</html>