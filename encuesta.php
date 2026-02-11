<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$mensaje = "";
if (isset($_POST['guardar'])) {
    $datos = array_map(function($val) use ($conexion) {
        return mysqli_real_escape_string($conexion, $val);
    }, $_POST);

    $usuario_id  = $_SESSION['usuario_id'] ?? 1;

    $sql = "INSERT INTO encuesta_2026 (
        fecha_encuesta, nombre_local, direccion, representante, cargo, telefono,
        necesidades_productivas, participa_programa_beneficios, beneficio_ofrecido,
        observaciones, conoce_corporacion, contacto_municipalidad, interes_iniciativas, participar_video,
        created_at, created_by
    ) VALUES (
        '{$datos['fecha']}', '{$datos['local']}', '{$datos['direccion']}', '{$datos['rep']}', '{$datos['cargo']}', '{$datos['tel']}',
        '{$datos['necesidades']}', '{$datos['participa']}', '{$datos['beneficio']}',
        '{$datos['obs']}', '{$datos['conoce_corp']}', '{$datos['cont_muni']}', '{$datos['interes']}', '{$datos['video']}',
        NOW(), '$usuario_id'
    )";

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "<div class='alert-success'><i class='fas fa-check-circle'></i> ¡Registro guardado con éxito en la base de datos!</div>";
    }
}

$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta 2026 | Digitalización</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #f4f7fe; --card: #ffffff; --text: #2b3674; --primary: #4318FF; 
            --border: #e0e5f2; --secondary-text: #a3aed0;
            --blue-icon: #4318FF; --green-icon: #05CD99; --orange-icon: #FFB547;
            --grey-btn: #707eae;
        }
        [data-theme="dark"] { 
            --bg: #0b1437; --card: #111c44; --text: #ffffff; --border: #1b254b; --secondary-text: #707eae;
        }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); padding: 40px; margin: 0; }
        .form-container { max-width: 850px; margin: 0 auto; background: var(--card); padding: 40px; border-radius: 30px; border: 1px solid var(--border); box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
        .header-title { margin-bottom: 30px; text-align: center; }
        .header-title h2 { font-size: 2rem; font-weight: 800; margin: 0; color: var(--text); }
        .section-header { grid-column: 1 / -1; display: flex; align-items: center; gap: 15px; margin-top: 35px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--border); }
        .section-header i { color: white; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1rem; }
        .section-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .section-subtitle { grid-column: 1 / -1; color: var(--secondary-text); font-size: 0.85rem; margin-bottom: 25px; }
        .sc-azul i { background: var(--blue-icon); box-shadow: 0 4px 12px rgba(67, 24, 255, 0.3); }
        .sc-verde i { background: var(--green-icon); box-shadow: 0 4px 12px rgba(5, 205, 153, 0.3); }
        .sc-naranja i { background: var(--orange-icon); box-shadow: 0 4px 12px rgba(255, 181, 71, 0.3); }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 0.85rem; color: var(--secondary-text); }
        input, textarea, select { width: 100%; padding: 14px 18px; border-radius: 15px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-family: inherit; font-size: 0.95rem; box-sizing: border-box; transition: 0.3s; }
        .full-width { grid-column: 1 / -1; }

        /* Botones */
        .button-group { grid-column: 1 / -1; display: flex; gap: 15px; margin-top: 40px; }
        .btn-save { flex: 2; background: var(--primary); color: white; border: none; padding: 18px; border-radius: 18px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(67, 24, 255, 0.2); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-history { flex: 1; background: var(--grey-btn); color: white; border: none; padding: 18px; border-radius: 18px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: 0.3s; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-save:hover, .btn-history:hover { transform: translateY(-3px); filter: brightness(1.1); }

        .alert-success { background: #def7ec; color: #03543f; padding: 20px; border-radius: 15px; margin-bottom: 30px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <a href="index.php" style="text-decoration: none; color: var(--secondary-text); font-weight: 700; font-size: 0.9rem;">
        <i class="fas fa-chevron-left"></i> VOLVER AL BUSCADOR
    </a>

    <div class="header-title">
        <h2>Ingreso de Encuesta 2026</h2>
        <p style="color: var(--secondary-text);">Digitalización de planillas físicas del programa.</p>
    </div>

    <?php echo $mensaje; ?>

    <form method="POST" class="grid-form">
        
        <div class="section-header sc-azul">
            <i class="fas fa-id-card"></i>
            <h3>Identificación del Local</h3>
        </div>
        <p class="section-subtitle">Complete los datos de contacto y ubicación detallados en la Foto 2.</p>

        <div><label>Fecha</label><input type="date" name="fecha" value="2026-01-19" required></div>
        <div><label>Barrio / Local</label><input type="text" name="local" placeholder="Nombre del local" required></div>
        <div class="full-width"><label>Dirección</label><input type="text" name="direccion" placeholder="Calle y número" required></div>
        <div><label>Nombre Representante</label><input type="text" name="rep" required></div>
        <div><label>Cargo</label><input type="text" name="cargo"></div>
        <div class="full-width"><label>Teléfono</label><input type="text" name="tel"></div>

        <div class="section-header sc-verde">
            <i class="fas fa-briefcase"></i>
            <h3>Necesidades y Beneficios</h3>
        </div>
        <p class="section-subtitle">Información extraída de la Foto 1.</p>

        <div class="full-width"><label>Necesidades</label><textarea name="necesidades" rows="3"></textarea></div>
        <div><label>¿Participaría?</label><select name="participa"><option value="SI">SÍ</option><option value="NO">NO</option><option value="EVALUACION">EN EVALUACIÓN</option></select></div>
        <div><label>Beneficio</label><input type="text" name="beneficio"></div>
        <div class="full-width"><label>Observaciones</label><textarea name="obs" rows="3"></textarea></div>

        <div class="section-header sc-naranja">
            <i class="fas fa-check-double"></i>
            <h3>Indicadores de Percepción</h3>
        </div>
        <p class="section-subtitle">Respuestas Sí/No de la Foto 3.</p>

        <div><label>¿Conoce Corp.?</label><select name="conoce_corp"><option value="SI">SÍ</option><option value="NO">NO</option></select></div>
        <div><label>¿Contacto Muni?</label><select name="cont_muni"><option value="SI">SÍ</option><option value="NO">NO</option></select></div>
        <div><label>¿Interés?</label><select name="interes"><option value="SI">SÍ</option><option value="NO">NO</option></select></div>
        <div><label>¿Video?</label><select name="video"><option value="SI">SÍ</option><option value="NO">NO</option></select></div>

        <div class="button-group">
            <button name="guardar" class="btn-save">
                <i class="fas fa-save"></i> GUARDAR PLANILLA
            </button>
            <a href="lista_encuestas_2026.php" class="btn-history">
                <i class="fas fa-history"></i> VER HISTORIAL
            </a>
        </div>

    </form>
</div>
</body>
</html>