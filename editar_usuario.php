<?php 
include 'config.php'; 

$mensaje = "";

// 1. Obtener datos del usuario a editar
if (isset($_GET['id'])) {
    $id_usuario = mysqli_real_escape_string($conexion, $_GET['id']);
    $res = mysqli_query($conexion, "SELECT u.*, p.nombres, p.apellidos 
                                    FROM Usuarios u 
                                    JOIN personas p ON u.personas_idpersonas = p.idpersonas 
                                    WHERE u.idUsuarios = '$id_usuario'");
    $u = mysqli_fetch_assoc($res);
    
    if (!$u) { die("Usuario no encontrado."); }
}

// 2. Procesar la actualización
if (isset($_POST['actualizar_usuario'])) {
    $nuevo_user = mysqli_real_escape_string($conexion, $_POST['username']);
    $nuevo_estado = $_POST['estado'];
    $pass_nueva = $_POST['password'];

    // Si la contraseña no está vacía, la actualizamos encriptada
    if (!empty($pass_nueva)) {
        $pass_enc = password_hash($pass_nueva, PASSWORD_DEFAULT);
        $sql = "UPDATE Usuarios SET username = '$nuevo_user', password = '$pass_enc', estado = '$nuevo_estado' WHERE idUsuarios = '$id_usuario'";
    } else {
        // Si está vacía, solo actualizamos nombre y estado
        $sql = "UPDATE Usuarios SET username = '$nuevo_user', estado = '$nuevo_estado' WHERE idUsuarios = '$id_usuario'";
    }

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "<p style='color:green; font-weight:bold;'>✅ Usuario actualizado correctamente.</p>";
        // Recargar datos actualizados
        header("Refresh:1; url=usuarios_lista.php");
    } else {
        $mensaje = "<p style='color:red;'>Error: " . mysqli_error($conexion) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .form-box { max-width: 450px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .nav-bar { background: #43b02a; padding: 15px; text-align: center; border-radius: 8px; margin-bottom: 25px; }
        .nav-bar a { color: white; text-decoration: none; font-weight: bold; }
        h2 { color: #333; text-align: center; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.9em; }
        input, select { width: 100%; padding: 12px; margin: 5px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-save { background: #43b02a; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 25px; }
        .info-p { background: #e0f2fe; padding: 10px; border-radius: 6px; font-size: 0.9em; color: #0369a1; }
    </style>
</head>
<body>

<div class="nav-bar">
    <a href="usuarios_lista.php">Volver a Usuarios</a>
</div>

<div class="form-box">
    <h2>Editar Acceso de Usuario</h2>
    <?php echo $mensaje; ?>

    <div class="info-p">
        Persona vinculada: <strong><?php echo $u['nombres'] . " " . $u['apellidos']; ?></strong>
    </div>

    <form method="POST">
        <label>Nombre de Usuario:</label>
        <input type="text" name="username" value="<?php echo $u['username']; ?>" required>

        <label>Nueva Contraseña (Dejar en blanco para no cambiar):</label>
        <input type="password" name="password" placeholder="Escriba aquí solo si desea cambiarla">

        <label>Estado de la Cuenta:</label>
        <select name="estado">
            <option value="1" <?php if($u['estado'] == 1) echo 'selected'; ?>>Activo (Puede entrar)</option>
            <option value="0" <?php if($u['estado'] == 0) echo 'selected'; ?>>Inactivo (Acceso bloqueado)</option>
        </select>

        <button type="submit" name="actualizar_usuario" class="btn-save">Guardar Cambios</button>
    </form>
</div>

</body>
</html>