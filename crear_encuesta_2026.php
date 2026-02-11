<?php
include 'config.php';

$mensaje = "";

if (isset($_POST['guardar'])) {

    $fecha       = $_POST['fecha'];
    $local       = mysqli_real_escape_string($conexion, $_POST['local']);
    $direccion   = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $rep         = mysqli_real_escape_string($conexion, $_POST['representante']);
    $telefono    = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $necesidades = mysqli_real_escape_string($conexion, $_POST['necesidades']);
    $participa   = $_POST['participa'];
    $beneficio   = mysqli_real_escape_string($conexion, $_POST['beneficio']);
    $obs         = mysqli_real_escape_string($conexion, $_POST['observaciones']);
    $usuario_id  = $_SESSION['usuario_id'];

    $sql = "INSERT INTO encuesta_2026 (
        fecha_encuesta, nombre_local, direccion, representante, telefono,
        necesidades_productivas, participa_programa_beneficios, beneficio_ofrecido,
        observaciones, created_at, created_by
    ) VALUES (
        '$fecha','$local','$direccion','$rep','$telefono',
        '$necesidades','$participa','$beneficio',
        '$obs',NOW(),'$usuario_id'
    )";

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "✅ Encuesta guardada correctamente";
    } else {
        $mensaje = "❌ Error: ".mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Crear Encuesta 2026</h2>
<?= $mensaje ?>
<form method="POST">
<input type="date" name="fecha" required>
<input type="text" name="local" placeholder="Nombre local" required>
<input type="text" name="direccion" placeholder="Dirección" required>
<input type="text" name="representante" placeholder="Representante" required>
<input type="text" name="telefono" placeholder="Teléfono">
<textarea name="necesidades" placeholder="Necesidades"></textarea>
<select name="participa">
<option value="1">Sí</option>
<option value="0">No</option>
</select>
<textarea name="beneficio" placeholder="Beneficio"></textarea>
<textarea name="observaciones" placeholder="Observaciones"></textarea>
<button name="guardar">Guardar</button>
</form>
</body>
</html>
