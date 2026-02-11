<?php
include 'config.php';

// Variable para manejar el ID recién guardado y mostrar el botón de ver reporte individual
$ultimo_id = isset($_GET['ultimo_id']) ? $_GET['ultimo_id'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos el ID desde el formulario
    $id = mysqli_real_escape_string($conexion, $_POST['id_emprendedor']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $negocio = mysqli_real_escape_string($conexion, $_POST['negocio']);
    
    mysqli_begin_transaction($conexion);

    try {
        // 1. Guardar o actualizar datos del emprendedor (Hoja 1)
        // Usamos id_emprendedor que es el nombre correcto de la columna
        mysqli_query($conexion, "INSERT INTO emprendedores (id_emprendedor, nombre, emprendimiento, fecha_registro) 
            VALUES ('$id', '$nombre', '$negocio', NOW()) 
            ON DUPLICATE KEY UPDATE nombre='$nombre', emprendimiento='$negocio'");

        // 2. Guardar notas (Hoja 2)
        mysqli_query($conexion, "INSERT INTO evaluaciones_notas (id_emprendedor, evaluacion_general, evaluacion_modulos, evaluacion_funcionarios, evaluacion_espacio) 
            VALUES ('$id', '{$_POST['n1']}', '{$_POST['n2']}', '{$_POST['n3']}', '{$_POST['n4']}')");

        // 3. Guardar opiniones (Hoja 3 y 4)
        mysqli_query($conexion, "INSERT INTO opiniones (id_emprendedor, opinion_general, mejoras, capacitacion_deseada, critica_adicional) 
            VALUES ('$id', '{$_POST['opinion']}', '{$_POST['mejoras']}', '{$_POST['interes']}', '{$_POST['critica']}')");

        mysqli_commit($conexion);
        
        // Redireccionamos para limpiar el POST y mostrar el botón de éxito
        echo "<script>alert('Datos guardados con éxito'); window.location='digitalizar_escuela.php?ultimo_id=$id';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        echo "<div style='color:red; background:white; padding:10px;'>Error al guardar: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Digitalización - Escuela de Verano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f4f7fe; padding: 20px; color: #2b3674; }
        .form-container { background: white; max-width: 800px; margin: auto; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .section-title { color: #55b83e; border-bottom: 2px solid #f1c40f; padding-bottom: 10px; margin-top: 30px; font-weight: bold; }
        .input-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; }
        input[type="text"], textarea, select { width: 100%; padding: 12px; border: 1px solid #e0e5f2; border-radius: 10px; box-sizing: border-box; }
        
        /* Navegación superior */
        .nav-bar { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .btn-nav { text-decoration: none; padding: 10px 20px; border-radius: 10px; font-weight: bold; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; }
        .btn-blue { background: #422afb; color: white; }
        .btn-gray { background: #707eae; color: white; }
        .btn-yellow { background: #f1c40f; color: black; }
        
        .btn-save { background: #55b83e; color: white; border: none; padding: 18px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: bold; font-size: 1.1rem; margin-top: 20px; }
        .btn-save:hover { background: #45a032; }

        .success-box { background: #e6fffa; border: 1px solid #b2f5ea; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="form-container">
    <div class="nav-bar">
        <div style="display: flex; gap: 10px;">
            <a href="index.php" class="btn-nav btn-gray"><i class="fas fa-home"></i> INICIO</a>
            <a href="consultar_historial.php" class="btn-nav btn-blue"><i class="fas fa-list"></i> VER TODO EL HISTORIAL</a>
        </div>
        <?php if($ultimo_id): ?>
            <a href="ver_historial_escuela.php?id=<?php echo $ultimo_id; ?>" class="btn-nav btn-yellow"><i class="fas fa-eye"></i> VER REPORTE RECIENTE</a>
        <?php endif; ?>
    </div>

    <?php if($ultimo_id): ?>
        <div class="success-box">
            <strong style="color: #2c7a7b;"><i class="fas fa-check-circle"></i> ¡Registro #<?php echo $ultimo_id; ?> guardado!</strong>
        </div>
    <?php endif; ?>

    <h1 style="margin: 0;"><i class="fas fa-file-signature" style="color: #55b83e;"></i> Nueva Encuesta</h1>

    <form method="POST">
        <div class="section-title">1. Identificación</div>
        <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 15px; margin-top: 15px;">
            <div class="input-group">
                <label>ID Excel</label>
                <input type="text" name="id_emprendedor" required placeholder="Ej: 1">
            </div>
            <div class="input-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" required placeholder="Nombre como aparece en la hoja">
            </div>
        </div>
        <div class="input-group">
            <label>Emprendimiento / Negocio</label>
            <input type="text" name="negocio" placeholder="Rubro o nombre del negocio">
        </div>

        <div class="section-title">2. Notas (1 al 5)</div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 15px;">
            <div class="input-group">
                <label>Gral. Escuela</label>
                <select name="n1"><?php for($i=5;$i>=1;$i--) echo "<option value='$i'>$i</option>"; ?></select>
            </div>
            <div class="input-group">
                <label>Módulos</label>
                <select name="n2"><?php for($i=5;$i>=1;$i--) echo "<option value='$i'>$i</option>"; ?></select>
            </div>
            <div class="input-group">
                <label>Funcionarios</label>
                <select name="n3"><?php for($i=5;$i>=1;$i--) echo "<option value='$i'>$i</option>"; ?></select>
            </div>
            <div class="input-group">
                <label>Espacio/Atención</label>
                <select name="n4"><?php for($i=5;$i>=1;$i--) echo "<option value='$i'>$i</option>"; ?></select>
            </div>
        </div>

        <div class="section-title">3. Comentarios Libres</div>
        <div class="input-group">
            <label>Opinión General</label>
            <textarea name="opinion" rows="2"></textarea>
        </div>
        <div class="input-group">
            <label>Mejoras Sugeridas</label>
            <textarea name="mejoras" rows="2"></textarea>
        </div>
        <div class="input-group">
            <label>Interés en Capacitación</label>
            <input type="text" name="interes">
        </div>
        <div class="input-group">
            <label>Crítica/Comentario Extra</label>
            <textarea name="critica" rows="2"></textarea>
        </div>

        <button type="submit" class="btn-save"><i class="fas fa-save"></i> GUARDAR DATOS</button>
    </form>
</div>

</body>
</html>