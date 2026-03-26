<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
include 'config.php'; 

$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
$moneda = $cfg['simbolo_moneda'];
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Historial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #f3f6f9; --white: #ffffff; --border: #e3e6f0; --text: #333; --blue: #55b83e; --green: #4caf50; }
        [data-theme="dark"] { --bg: #1a202c; --white: #2d3748; --text: #edf2f7; --border: #4a5568; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-nav-white { background: var(--white); border: 1px solid var(--border); padding: 10px 20px; border-radius: 10px; text-decoration: none; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px; }
        .btn-new-blue { background: var(--blue); color: white; border-radius: 10px; padding: 10px 25px; text-decoration: none; font-weight: bold; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-input { flex-grow: 1; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--white); color: var(--text); }
        .btn-search-green { background: var(--green); color: white; border: none; padding: 0 20px; border-radius: 10px; cursor: pointer; }
        .table-card { background: var(--white); border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        .btn-delete { color: #e74c3c; text-decoration: none; font-size: 1.1rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="navbar">
        <a href="index.php" class="btn-nav-white"><i class="fas fa-home"></i> Inicio</a>
        <div style="font-weight:bold;"><i class="fas fa-list-ul"></i> Historial</div>
        <a href="cobranzas.php" class="btn-new-blue"><i class="fas fa-plus"></i> Nuevo</a>
    </div>
    <form method="GET" class="search-bar">
        <input type="text" name="buscar" class="search-input" placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($buscar); ?>">
        <button type="submit" class="btn-search-green"><i class="fas fa-search"></i></button>
    </form>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Monto</th>
                    <th>Saldo Restante</th>
                    <th>Método</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where = $buscar ? "AND (p.nombres LIKE '%$buscar%')" : "";
                $sql = "SELECT cob.idcobranzas, cob.monto, cob.tipo_pago, cob.fecha_hora, p.nombres, cr.saldo_inicial 
                        FROM cobranzas cob JOIN creditos cr ON cob.creditos_idcreditos = cr.idcreditos
                        JOIN emprendedores e ON cr.emprendedores_idemprendedores = e.idemprendedores
                        JOIN personas p ON e.personas_idpersonas = p.idpersonas
                        WHERE 1=1 $where ORDER BY cob.fecha_hora DESC";
                $res = mysqli_query($conexion, $sql);
                while($h = mysqli_fetch_assoc($res)){
                    echo "<tr>
                            <td>".date("d/m H:i", strtotime($h['fecha_hora']))."</td>
                            <td><b>{$h['nombres']}</b></td>
                            <td style='color:green;'>+{$moneda}{$h['monto']}</td>
                            <td style='color:red;'>{$moneda}{$h['saldo_inicial']}</td>
                            <td><small>{$h['tipo_pago']}</small></td>
                            <td><a href='eliminar_cobro.php?id={$h['idcobranzas']}' class='btn-delete' onclick=\"return confirm('¿Eliminar cobro y devolver saldo?')\"><i class='fas fa-trash'></i></a></td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>