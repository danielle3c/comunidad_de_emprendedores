<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$mensaje = "";
// 1. Obtener el ID de la encuesta desde la URL
$id_encuesta = $_GET['id'] ?? null;

if (!$id_encuesta) {
    die("Error: No se proporcionó un ID válido.");
}

// 2. Lógica para ACTUALIZAR los datos
if (isset($_POST['actualizar'])) {
    $datos = array_map(function($val) use ($conexion) {
        return mysqli_real_escape_string($conexion, $val);
    }, $_POST);

    $sql_update = "UPDATE encuesta_2026 SET 
        fecha_encuesta = '{$datos['fecha']}',
        nombre_local = '{$datos['local']}',
        direccion = '{$datos['direccion']}',
        representante = '{$datos['rep']}',
        cargo = '{$datos['cargo']}',
        telefono = '{$datos['tel']}',
        necesidades_productivas = '{$datos['necesidades']}',
        participa_programa_beneficios = '{$datos['participa']}',
        beneficio_ofrecido = '{$datos['beneficio']}',
        observaciones = '{$datos['obs']}',
        conoce_corporacion = '{$datos['conoce_corp']}',
        contacto_municipalidad = '{$datos['cont_muni']}',
        interes_iniciativas = '{$datos['interes']}',
        participar_video = '{$datos['video']}'
        WHERE id = $id_encuesta"; // Asegúrate que tu columna se llame 'id'

    if (mysqli_query($conexion, $sql_update)) {
        $mensaje = "<div class='alert-success'><i class='fas fa-sync'></i> ¡Registro actualizado correctamente!</div>";
    } else {
        $mensaje = "<div style='color:red'>Error: " . mysqli_error($conexion) . "</div>";
    }
}

// 3. Cargar los datos actuales de la encuesta
$resultado = mysqli_query($conexion, "SELECT * FROM encuesta_2026 WHERE id = $id_encuesta");
$data = mysqli_fetch_assoc($resultado);

if (!$data) { die("La encuesta no existe."); }

$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Editar Encuesta 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reutilizamos los mismos estilos estéticos de encuesta_2026.php */
        :root { --bg: #f4f7fe; --card: #ffffff; --text: #2b3674; --primary: #4318FF; --border: #e0e5f2; --secondary-text: #a3aed0; --blue-icon: #4318FF; --green-icon: #05CD99; --orange-icon: #FFB547; }
        [data-theme="dark"] { --bg: #0b1437; --card: #111c44; --text: #ffffff; --border: #1b254b; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); padding: 40px; }
        .form-container { max-width: 850px; margin: 0 auto; background: var(--card); padding: 40px; border-radius: 30px; border: 1px solid var(--border); box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .section-header { grid-column: 1 / -1; display: flex; align-items: center; gap: 15px; margin-top: 30px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        .section-header i { color: white; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px; }
        .sc-azul i { background: var(--blue-icon); } .sc-verde i { background: var(--green-icon); } .sc-naranja i { background: var(--orange-icon); }
        input, textarea, select { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg); color: var(--text); box-sizing: border-box; }
        .btn-update { grid-column: 1 / -1; background: var(--success, #05CD99); color: white; border: none; padding: 18px; border-radius: 15px; font-weight: 800; cursor: pointer; margin-top: 20px; }
        .alert-success { background: #def7ec; color: #03543f; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: bold; }
        .full-width { grid-column: 1 / -1; }
    </style>
</head>
<body>

<div class="form-container">
    <a href="lista_encuestas_2026.php" style="text-decoration: none; color: var(--secondary-text); font-weight: bold;">
        <i class="fas fa-arrow-left"></i> VOLVER AL HISTORIAL
    </a>

    <h2 style="text-align: center; margin: 20px 0;">Modificar Encuesta #<?php echo $id_encuesta; ?></h2>

    <?php echo $mensaje; ?>

    <form method="POST" class="grid-form">
        <div class="section-header sc-azul"><i class="fas fa-id-card"></i> <h3>Datos de Identificación</h3></div>
        <div><label>Fecha</label><input type="date" name="fecha" value="<?php echo $data['fecha_encuesta']; ?>"></div>
        <div><label>Local</label><input type="text" name="local" value="<?php echo htmlspecialchars($data['nombre_local']); ?>"></div>
        <div class="full-width"><label>Dirección</label><input type="text" name="direccion" value="<?php echo htmlspecialchars($data['direccion']); ?>"></div>
        <div><label>Representante</label><input type="text" name="rep" value="<?php echo htmlspecialchars($data['representante']); ?>"></div>
        <div><label>Cargo</label><input type="text" name="cargo" value="<?php echo htmlspecialchars($data['cargo']); ?>"></div>

        <div class="section-header sc-verde"><i class="fas fa-briefcase"></i> <h3>Necesidades</h3></div>
        <div class="full-width"><label>Necesidades</label><textarea name="necesidades"><?php echo htmlspecialchars($data['necesidades_productivas']); ?></textarea></div>
        <div>
            <label>¿Participa?</label>
            <select name="participa">
                <option value="SI" <?php if($data['participa_programa_beneficios'] == 'SI') echo 'selected'; ?>>SÍ</option>
                <option value="NO" <?php if($data['participa_programa_beneficios'] == 'NO') echo 'selected'; ?>>NO</option>
                <option value="EVALUACION" <?php if($data['participa_programa_beneficios'] == 'EVALUACION') echo 'selected'; ?>>EN EVALUACIÓN</option>
            </select>
        </div>
        <div><label>Beneficio</label><input type="text" name="beneficio" value="<?php echo htmlspecialchars($data['beneficio_ofrecido']); ?>"></div>

        <div class="section-header sc-naranja"><i class="fas fa-check-double"></i> <h3>Indicadores</h3></div>
        <div>
            <label>¿Conoce Corp.?</label>
            <select name="conoce_corp">
                <option value="SI" <?php if($data['conoce_corporacion'] == 'SI') echo 'selected'; ?>>SÍ</option>
                <option value="NO" <?php if($data['conoce_corporacion'] == 'NO') echo 'selected'; ?>>NO</option>
            </select>
        </div>
        <div>
            <label>¿Interés?</label>
            <select name="interes">
                <option value="SI" <?php if($data['interes_iniciativas'] == 'SI') echo 'selected'; ?>>SÍ</option>
                <option value="NO" <?php if($data['interes_iniciativas'] == 'NO') echo 'selected'; ?>>NO</option>
            </select>
        </div>

        <button name="actualizar" class="btn-update">
            <i class="fas fa-save"></i> GUARDAR CAMBIOS
        </button>
    </form>
</div>

</body>
</html>