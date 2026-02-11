<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
include 'config.php'; 

// Obtener configuración
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
$moneda = $cfg['simbolo_moneda']; 
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Historial de Créditos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #f3f6f9; --white: #ffffff; --border: #e3e6f0; --text: #333; --blue: #448aff; --green: #4caf50; }
        [data-theme="dark"] { --bg: #1a202c; --white: #2d3748; --text: #edf2f7; --border: #4a5568; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        .container { max-width: 1150px; margin: auto; }
        
        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-nav-white { background: var(--white); border: 1px solid var(--border); padding: 10px 20px; border-radius: 10px; text-decoration: none; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-new-blue { background: var(--blue); color: white; border-radius: 10px; padding: 10px 25px; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-new-blue:hover { opacity: 0.9; transform: translateY(-1px); }

        /* Buscador */
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-input { flex-grow: 1; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--white); color: var(--text); font-size: 1rem; }
        .btn-search-green { background: var(--green); color: white; border: none; padding: 0 20px; border-radius: 10px; cursor: pointer; font-size: 1.2rem; }

        /* Tabla */
        .table-card { background: var(--white); border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fc; padding: 15px; text-align: left; border-bottom: 2px solid var(--border); color: #4e73df; font-size: 0.9rem; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid var(--border); }
        
        /* Estados y Botones */
        .status { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .active { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .paid { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        
        .actions { display: flex; gap: 15px; align-items: center; }
        .btn-pdf { color: #e67e22; text-decoration: none; font-size: 1.2rem; }
        .btn-del { color: #e74c3c; text-decoration: none; font-size: 1.1rem; }
        .btn-pdf:hover, .btn-del:hover { transform: scale(1.1); }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="index.php" class="btn-nav-white"><i class="fas fa-home"></i> Inicio</a>
        <div style="font-weight:bold; font-size:1.3rem;"><i class="fas fa-hand-holding-usd"></i> Historial de Créditos</div>
        <a href="creditos.php" class="btn-new-blue"><i class="fas fa-plus"></i> Nuevo Crédito</a>
    </div>

    <form method="GET" class="search-bar">
        <input type="text" name="buscar" class="search-input" placeholder="Buscar por cliente o contrato..." value="<?php echo htmlspecialchars($buscar); ?>">
        <button type="submit" class="btn-search-green"><i class="fas fa-search"></i></button>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Inicio</th>
                    <th>Emprendedor</th>
                    <th>Monto Inicial</th>
                    <th>Saldo Pendiente</th>
                    <th>Día Pago</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where = $buscar ? "AND (p.nombres LIKE '%$buscar%' OR cr.Contratos_idContratos LIKE '%$buscar%')" : "";
                $sql = "SELECT cr.*, p.nombres FROM creditos cr 
                        JOIN emprendedores e ON cr.emprendedores_idemprendedores = e.idemprendedores 
                        JOIN personas p ON e.personas_idpersonas = p.idpersonas 
                        WHERE 1=1 $where ORDER BY cr.fecha_inicio DESC";
                
                $res = mysqli_query($conexion, $sql);
                if(mysqli_num_rows($res) > 0){
                    while($row = mysqli_fetch_assoc($res)){
                        $estado_label = ($row['estado'] == 1) ? "<span class='status active'>Activo</span>" : "<span class='status paid'>Pagado</span>";
                        echo "<tr>
                                <td>".date("d/m/Y", strtotime($row['fecha_inicio']))."</td>
                                <td><b>{$row['nombres']}</b></td>
                                <td>{$moneda}".number_format($row['monto_inicial'], 2)."</td>
                                <td style='color:#e74c3c; font-weight:bold;'>{$moneda}".number_format($row['saldo_inicial'], 2)."</td>
                                <td>Día {$row['dia_de_pago']}</td>
                                <td>$estado_label</td>
                                <td>
                                    <div class='actions'>
                                        <a href='pdf_cronograma.php?id={$row['idcreditos']}' target='_blank' class='btn-pdf' title='Descargar Cronograma'>
                                            <i class='fas fa-file-pdf'></i>
                                        </a>
                                        <a href='eliminar_credito.php?id={$row['idcreditos']}' class='btn-del' onclick=\"return confirm('¿Estás seguro de eliminar este crédito? Esta acción no se puede deshacer.')\" title='Eliminar'>
                                            <i class='fas fa-trash-alt'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding:50px; color:#999;'>No se encontraron créditos registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>