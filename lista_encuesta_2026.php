<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Cargar configuración visual (Tema oscuro/claro)
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

// 2. Consulta mejorada: Trae el nombre del usuario y filtra las no eliminadas
// Usamos id_encuesta que es el nombre real de tu columna
$sql = "SELECT e.*, u.username 
        FROM encuesta_2026 e
        LEFT JOIN Usuarios u ON e.created_by = u.idUsuarios
        WHERE e.deleted_at = 0
        ORDER BY e.id_encuesta DESC";

$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Encuestas 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #f4f7fe; --card: #ffffff; --text: #2b3674; --primary: #4318FF; 
            --border: #e0e5f2; --secondary-text: #a3aed0; --success: #05CD99;
        }
        [data-theme="dark"] { 
            --bg: #0b1437; --card: #111c44; --text: #ffffff; --border: #1b254b; --secondary-text: #707eae;
        }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); padding: 40px; margin: 0; }
        .container { max-width: 1100px; margin: 0 auto; }
        
        /* Encabezado */
        .header-list { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .header-list h1 { font-size: 1.8rem; font-weight: 800; margin: 0; }
        
        .btn-new { 
            text-decoration: none; background: var(--primary); color: white; 
            padding: 12px 25px; border-radius: 14px; font-weight: bold; 
            display: flex; align-items: center; gap: 10px; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(67, 24, 255, 0.2);
        }
        .btn-new:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(67, 24, 255, 0.3); }

        /* Tabla Estética */
        .table-card { 
            background: var(--card); border-radius: 25px; padding: 25px; 
            border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0,0,0,0.03);
            overflow-x: auto;
        }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 18px; color: var(--secondary-text); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        td { padding: 18px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        tr:last-child td { border: none; }
        
        .user-badge { background: var(--bg); padding: 5px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: bold; color: var(--primary); }
        
        /* Botón de Acción */
        .btn-edit { 
            color: var(--primary); text-decoration: none; font-weight: 700; 
            padding: 10px 18px; border-radius: 12px; background: rgba(67, 24, 255, 0.08); 
            transition: 0.3s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-edit:hover { background: var(--primary); color: white; }

        .empty-state { text-align: center; padding: 50px; color: var(--secondary-text); }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" style="text-decoration: none; color: var(--secondary-text); font-weight: bold; font-size: 0.9rem; display: inline-block; margin-bottom: 20px;">
        <i class="fas fa-chevron-left"></i> VOLVER AL PANEL
    </a>

    <div class="header-list">
        <div>
            <h1>Historial Encuestas 2026</h1>
            <p style="color: var(--secondary-text); margin-top: 5px;">Visualización de registros y responsables de ingreso.</p>
        </div>
        <a href="encuesta_2026.php" class="btn-new">
            <i class="fas fa-plus"></i> NUEVA ENCUESTA
        </a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Local / Barrio</th>
                    <th>Representante</th>
                    <th>Ingresado por</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <td style="color: var(--secondary-text); font-weight: bold;">#<?php echo $row['id_encuesta']; ?></td>
                    <td><strong><?php echo date('d/m/Y', strtotime($row['fecha_encuesta'])); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['nombre_local']); ?></td>
                    <td><?php echo htmlspecialchars($row['representante']); ?></td>
                    <td><span class="user-badge"><i class="fas fa-user"></i> <?php echo htmlspecialchars($row['username'] ?? 'Admin'); ?></span></td>
                    <td>
                        <a href="editar_encuesta_2026.php?id=<?php echo $row['id_encuesta']; ?>" class="btn-edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if(mysqli_num_rows($resultado) == 0): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-folder-open fa-3x" style="margin-bottom: 15px; display: block;"></i>
                            No se han encontrado encuestas registradas para el año 2026.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>