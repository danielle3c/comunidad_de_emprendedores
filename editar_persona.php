<?php 
include 'config.php'; 

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    // Unimos Usuarios con Personas para obtener todos los datos
    $res = mysqli_query($conexion, "SELECT u.*, p.* FROM Usuarios u 
                                    JOIN personas p ON u.personas_idpersonas = p.idpersonas 
                                    WHERE u.idUsuarios = '$id'");
    $u = mysqli_fetch_assoc($res);
    $id_persona = $u['idpersonas']; // Guardamos el ID de la persona para el UPDATE
}

if (isset($_POST['update_all'])) {
    // Datos del Usuario
    $user = mysqli_real_escape_string($conexion, $_POST['username']);
    $estado = $_POST['estado'];
    $pass = $_POST['password'];

    // Datos de la Persona
    $rut = mysqli_real_escape_string($conexion, $_POST['rut']);
    $nom = mysqli_real_escape_string($conexion, $_POST['nombres']);
    $ape = mysqli_real_escape_string($conexion, $_POST['apellidos']);
    $fec = $_POST['fecha_nacimiento'];
    $ema = mysqli_real_escape_string($conexion, $_POST['email']);
    $tel = mysqli_real_escape_string($conexion, $_POST['telefono']);

    // 1. Actualizar Tabla Personas
    $sql_p = "UPDATE personas SET 
            rut='$rut', nombres='$nom', apellidos='$ape', 
            fecha_nacimiento='$fec', email='$ema', telefono='$tel', 
            updated_at=NOW() 
            WHERE idpersonas='$id_persona'";
    
    mysqli_query($conexion, $sql_p);

    // 2. Actualizar Tabla Usuarios
    if (!empty($pass)) {
        $pass_enc = password_hash($pass, PASSWORD_DEFAULT);
        $sql_u = "UPDATE Usuarios SET username='$user', password='$pass_enc', estado='$estado', updated_at=NOW() WHERE idUsuarios='$id'";
    } else {
        $sql_u = "UPDATE Usuarios SET username='$user', estado='$estado', updated_at=NOW() WHERE idUsuarios='$id'";
    }

    if (mysqli_query($conexion, $sql_u)) {
        echo "<p style='color:green; text-align:center;'>Usuario e información personal actualizados.</p>";
        header("Refresh:2; url=usuarios_lista.php");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil Completo</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .form-box { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .section-title { border-bottom: 2px solid #55b83e; color: #55b83e; margin-bottom: 15px; padding-bottom: 5px; }
        label { font-weight: bold; display: block; margin-top: 10px; font-size: 0.9em; }
        input, select { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-save { background: #55b83e; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 20px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>

<div class="form-box">
    <h2 style="text-align:center;">Editar Perfil de Usuario</h2>
    
    <form method="POST">
        <div class="section-title">Información Personal</div>
        <div class="grid">
            <div>
                <label>RUT:</label>
                <input type="text" name="rut" value="<?php echo $u['rut']; ?>">
            </div>
            <div>
                <label>Fecha Nacimiento:</label>
                <input type="date" name="fecha_nacimiento" value="<?php echo $u['fecha_nacimiento']; ?>">
            </div>
        </div>
        
        <div class="grid">
            <div>
                <label>Nombres:</label>
                <input type="text" name="nombres" value="<?php echo $u['nombres']; ?>" required>
            </div>
            <div>
                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="<?php echo $u['apellidos']; ?>" required>
            </div>
        </div>

        <div class="grid">
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo $u['email']; ?>">
            </div>
            <div>
                <label>Teléfono:</label>
                <input type="text" name="telefono" value="<?php echo $u['telefono']; ?>">
            </div>
        </div>

        <div class="section-title" style="margin-top:25px;">Credenciales de Sistema</div>
        <label>Nombre de Usuario (Login):</label>
        <input type="text" name="username" value="<?php echo $u['username']; ?>" required>

        <label>Contraseña (Dejar en blanco para no cambiar):</label>
        <input type="password" name="password" placeholder="********">

        <label>Estado de Acceso:</label>
        <select name="estado">
            <option value="1" <?php if($u['estado'] == 1) echo 'selected'; ?>>Activo (Tiene acceso)</option>
            <option value="0" <?php if($u['estado'] == 0) echo 'selected'; ?>>Inactivo (Bloqueado)</option>
        </select>

        <button type="submit" name="update_all" class="btn-save">Guardar Cambios Totales</button>
    </form>
    <p style="text-align:center;"><a href="usuarios_lista.php" style="color: #666; font-size: 0.9em;">Cancelar y volver</a></p>
</div>

</body>
</html>