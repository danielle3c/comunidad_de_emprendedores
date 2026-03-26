<?php
include 'config.php';

$mensaje = "";
$error = false;

// 1. Validamos que el correo venga en la URL
if (!isset($_GET['email']) || empty($_GET['email'])) {
    die("Acceso no autorizado. Falta el parámetro de correo.");
}

$email_recibido = mysqli_real_escape_string($conexion, $_GET['email']);

// 2. Procesar el cambio de contraseña
if (isset($_POST['actualizar'])) {
    $pass1 = $_POST['nueva_pass'];
    $pass2 = $_POST['confirma_pass'];

    if ($pass1 !== $pass2) {
        $mensaje = "Las contraseñas no coinciden.";
        $error = true;
    } elseif (strlen($pass1) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $error = true;
    } else {
        // Encriptamos la nueva clave
        $nueva_pass_encriptada = password_hash($pass1, PASSWORD_DEFAULT);
        
        $sql = "UPDATE Usuarios SET password = '$nueva_pass_encriptada' WHERE correo_institucional = '$email_recibido'";
        
        if (mysqli_query($conexion, $sql)) {
            $mensaje = "Contraseña actualizada con éxito. Redirigiendo al login...";
            $error = false;
            header("Refresh:3; url=login.php");
        } else {
            $mensaje = "Error al actualizar en la base de datos.";
            $error = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Establecer Nueva Contraseña</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 380px; text-align: center; border-top: 5px solid #43b02a; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        .btn { background: #43b02a; color: white; border: none; padding: 14px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 10px; }
        .msg { padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9em; font-weight: bold; }
        .msg-error { background: #fee2e2; color: #b91c1c; }
        .msg-success { background: #dcfce7; color: #166534; }
        label { display: block; text-align: left; font-size: 0.85em; color: #555; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h3>Nueva Contraseña</h3>
        <p style="font-size: 0.85em; color: #666;">Estás cambiando la clave para:<br><strong><?php echo htmlspecialchars($email_recibido); ?></strong></p>

        <?php if($mensaje): ?>
            <div class="msg <?php echo $error ? 'msg-error' : 'msg-success'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php if(!$mensaje || $error): ?>
        <form method="POST">
            <label>Nueva Contraseña:</label>
            <input type="password" name="nueva_pass" placeholder="Mínimo 6 caracteres" required>
            
            <label>Confirmar Contraseña:</label>
            <input type="password" name="confirma_pass" placeholder="Repite la contraseña" required>
            
            <button type="submit" name="actualizar" class="btn">Guardar y Entrar</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>