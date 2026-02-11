<?php 
include 'config.php'; 

// 1. Cargar configuración
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Registro de Taller | <?php echo $cfg['nombre_sistema']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #f4f7fe; --card: #ffffff; --text: #2b3674; --primary: #55b83e; --border: #e0e5f2; --secondary-text: #a3aed0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        
        /* Navegación Superior */
        .nav-top { width: 100%; max-width: 700px; display: flex; justify-content: space-between; margin-bottom: 20px; }
        .btn-nav { background: white; border: 1px solid var(--border); padding: 10px 20px; border-radius: 12px; text-decoration: none; color: var(--text); font-weight: 700; font-size: 0.9rem; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .btn-nav:hover { background: #f8f9fc; transform: translateY(-2px); }

        .form-card { background: white; padding: 40px; border-radius: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 700px; box-sizing: border-box; }
        .form-title { text-align: center; font-size: 1.5rem; font-weight: 800; margin-bottom: 30px; display: flex; align-items: center; justify-content: center; gap: 10px; color: var(--text); }
        
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: 1 / -1; }

        label { display: block; font-weight: 800; font-size: 0.75rem; color: var(--primary); margin-bottom: 8px; text-transform: uppercase; }
        input, select, textarea { 
            width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--border); 
            background: #f8f9fc; outline: none; box-sizing: border-box; font-family: inherit; font-size: 0.95rem; color: var(--text);
        }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); background: white; }

        .btn-submit { 
            grid-column: 1 / -1; background: var(--primary); color: white; border: none; 
            padding: 18px; border-radius: 15px; font-weight: 800; cursor: pointer; margin-top: 10px; font-size: 1rem; transition: 0.3s;
        }
        .btn-submit:hover { filter: brightness(1.1); box-shadow: 0 4px 15px rgba(67, 176, 42, 0.3); }

        @media (max-width: 600px) {
            .grid-form { grid-template-columns: 1fr; }
            .form-card { padding: 25px; }
        }
    </style>
</head>
<body>

    <div class="nav-top">
        <a href="index.php" class="btn-nav"><i class="fas fa-home"></i> Ir a Inicio</a>
        <a href="talleres.php" class="btn-nav"><i class="fas fa-list-ul"></i> Ver Historial</a>
    </div>

    <div class="form-card">
        <div class="form-title">
            <i class="fas fa-clipboard-list"></i> Registro de Talleres
        </div>

        <form action="guardar_taller.php" method="POST" class="grid-form">
            <div>
                <label><i class="far fa-calendar-alt"></i> Fecha:</label>
                <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div>
                <label><i class="far fa-clock"></i> Hora Inicio:</label>
                <input type="time" name="hora" required>
            </div>

            <div class="full-width">
                <label><i class="fas fa-chalkboard"></i> Nombre del Taller:</label>
                <input type="text" name="nombre_taller" placeholder="Ej: Marketing para emprendedores" required>
            </div>

            <div class="full-width">
                <label><i class="fas fa-user-tie"></i> Relator / Encargado:</label>
                <input type="text" name="relator" placeholder="Nombre de quien dicta el taller">
            </div>

            <div class="full-width">
                <label><i class="fas fa-users"></i> Emprendedor Asistente:</label>
                <select name="id_emprendedor" required>
                    <option value="">Seleccione al emprendedor...</option>
                    <?php
                    $emp = mysqli_query($conexion, "SELECT e.idemprendedores, p.nombres, p.apellidos FROM emprendedores e JOIN personas p ON e.personas_idpersonas = p.idpersonas ORDER BY p.nombres ASC");
                    while($e = mysqli_fetch_assoc($emp)){
                        echo "<option value='{$e['idemprendedores']}'>{$e['nombres']} {$e['apellidos']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="full-width">
                <label><i class="fas fa-align-left"></i> Observaciones del Taller:</label>
                <textarea name="notas" rows="3" placeholder="Contenidos vistos, comportamiento, etc."></textarea>
            </div>

            <button type="submit" class="btn-submit">REGISTRAR ASISTENCIA AL TALLER</button>
        </form>
    </div>

</body>
</html>