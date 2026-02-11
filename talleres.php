<?php 
include 'config.php'; 
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Control de Talleres | <?php echo $cfg['nombre_sistema']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos base del sistema */
        :root { --bg: #f4f7fe; --card: #ffffff; --text: #2b3674; --primary: #55b83e; --border: #e0e5f2; --secondary-text: #a3aed0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        
        .header-historial { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-inicio { background: white; border: 1px solid var(--border); padding: 10px 20px; border-radius: 12px; text-decoration: none; color: var(--text); font-weight: 700; }
        .btn-nuevo { background: #4361ee; color: white; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 700; }

        .search-bar { background: white; border-radius: 15px; padding: 10px; display: flex; gap: 10px; border: 1px solid var(--border); margin-bottom: 20px; }
        .search-bar input { flex: 1; border: none; outline: none; padding-left: 10px; }
        .btn-search { background: var(--primary); color: white; border: none; padding: 10px; border-radius: 10px; cursor: pointer; }

        .table-container { background: white; border-radius: 20px; overflow: hidden; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fc; padding: 15px; text-align: left; color: var(--secondary-text); font-size: 0.8rem; text-transform: uppercase; }
        td { padding: 15px; border-top: 1px solid var(--border); font-size: 0.9rem; }

        .date-box { font-weight: 800; }
        .time-tag { display: inline-block; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; margin-top: 5px; }
        .tag-green { background: #e6fffa; color: #047857; }
        
        .status-badge { background: #dcfce7; color: #55b83e; padding: 5px 15px; border-radius: 10px; font-weight: 800; font-size: 0.75rem; }
        .actions i { margin: 0 5px; cursor: pointer; }
        .fa-edit { color: #4361ee; }
        .fa-trash { color: #ef4444; }
    </style>
</head>
<body>

    <div class="header-historial">
        <a href="index.php" class="btn-inicio"><i class="fas fa-home"></i> Inicio</a>
        <h2 style="display: flex; align-items: center; gap: 10px;"><i class="fas fa-chalkboard-user"></i> Historial de Talleres</h2>
        <a href="nuevo_taller.php" class="btn-nuevo"><i class="fas fa-plus"></i> Nuevo</a>
    </div>

    <div class="search-bar">
        <input type="text" placeholder="Buscar taller o emprendedor...">
        <button class="btn-search"><i class="fas fa-search"></i></button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha y Horario</th>
                    <th>Emprendedor</th>
                    <th>Nombre del Taller</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Esta consulta une la asistencia con los datos del taller y la persona
                $sql = "SELECT t.nombre as taller, t.fecha, p.nombres, p.apellidos, p.rut 
                        FROM asistencia_talleres at
                        JOIN talleres t ON at.talleres_id = t.idtalleres
                        JOIN emprendedores e ON at.emprendedores_id = e.idemprendedores
                        JOIN personas p ON e.personas_idpersonas = p.idpersonas
                        ORDER BY t.fecha DESC";
                $res = mysqli_query($conexion, $sql);
                while($row = mysqli_fetch_assoc($res)):
                ?>
                <tr>
                    <td>
                        <div class="date-box"><?php echo date("d/m/Y", strtotime($row['fecha'])); ?></div>
                        <div class="time-tag tag-green"><i class="far fa-clock"></i> Realizado</div>
                    </td>
                    <td>
                        <strong><?php echo $row['nombres']." ".$row['apellidos']; ?></strong><br>
                        <small style="color: var(--secondary-text);"><?php echo $row['rut']; ?></small>
                    </td>
                    <td><?php echo $row['taller']; ?></td>
                    <td><span class="status-badge">ASISTIÓ</span></td>
                    <td class="actions">
                        <i class="fas fa-edit"></i>
                        <i class="fas fa-trash"></i>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>