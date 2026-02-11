<?php 
include 'config.php'; 

$mensaje = "";

if (isset($_POST['crear_usuario'])) {
    // Eliminamos 'persona_id' y capturamos solo los datos directos del usuario
    $username   = mysqli_real_escape_string($conexion, $_POST['username']);
    $email      = mysqli_real_escape_string($conexion, $_POST['email']);
    $password   = $_POST['password'];
    $estado     = $_POST['estado'];

    // 1. Verificar si el nombre de usuario ya existe
    $check_u = mysqli_query($conexion, "SELECT idUsuarios FROM Usuarios WHERE username = '$username'");
    
    // 2. Verificar si el email ya existe (opcional pero recomendado)
    $check_e = mysqli_query($conexion, "SELECT idUsuarios FROM Usuarios WHERE email = '$email'");

    if (mysqli_num_rows($check_u) > 0) {
        $mensaje = "<p style='color:red;'>Error: El nombre de usuario '$username' ya está en uso.</p>";
    } elseif (mysqli_num_rows($check_e) > 0) {
        $mensaje = "<p style='color:red;'>Error: El correo '$email' ya está registrado.</p>";
    } else {
        // Encriptar contraseña
        $pass_enc = password_hash($password, PASSWORD_DEFAULT);
        
        // El campo personas_idpersonas ahora lo enviamos como NULL o 0 si tu BD lo permite
        $sql = "INSERT INTO Usuarios (username, email, password, estado, created_at) 
                VALUES ('$username', '$email', '$pass_enc', '$estado', NOW())";

        if (mysqli_query($conexion, $sql)) {
            $mensaje = "<p style='color:green;'>✅ Usuario creado exitosamente.</p>";
            header("Refresh:2; url=usuarios_lista.php");
        } else {
            $mensaje = "<p style='color:red;'>❌ Error al insertar: " . mysqli_error($conexion) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Nuevo Usuario</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .form-box { max-width: 450px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .nav-bar { background: #55b83e; padding: 15px; text-align: center; border-radius: 8px; margin-bottom: 25px; }
        .nav-bar a { color: white; text-decoration: none; font-weight: bold; }
        h2 { color: #333; text-align: center; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.9em; }
        input, select { width: 100%; padding: 12px; margin: 5px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-save { background: #55b83e; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 25px; font-size: 1em; }
        .btn-save:hover { background: #55b83e; }
    </style>
</head>
<body>

<div class="nav-bar">
    <a href="usuarios_lista.php">Volver a Lista</a>
</div>

<div class="form-box">
    <h2>Registro de Usuario</h2>
    <?php echo $mensaje; ?>

    <form method="POST">
        <label>Nombre de Usuario (Login):</label>
        <input type="text" name="username" placeholder="Ej: admin_comunidad" required>

        <label>Correo Electrónico:</label>
        <input type="email" name="email" placeholder="correo@ejemplo.com" required>

        <label>Contraseña:</label>
        <input type="password" name="password" placeholder="********" required>

        <label>Estado inicial:</label>
        <select name="estado">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button type="submit" name="crear_usuario" class="btn-save">Registrar Usuario</button>
    </form>
</div>

</body> 
</html>