<?php 
// 1. Cargar la librería PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'config.php'; 

$mensaje = "";

if (isset($_POST['verificar_correo'])) {
    // Escapar el correo para evitar inyecciones básicas
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    
    // CORRECCIÓN: Se usa 'nombre_usuario' y 'activo' según tu estructura SQL
    $sql = "SELECT nombre_usuario FROM Usuarios WHERE email = '$correo' AND activo = 1";
    $res = mysqli_query($conexion, $sql);

    if ($datos = mysqli_fetch_assoc($res)) {
        $username = $datos['nombre_usuario'];
        $mail = new PHPMailer(true);

        try {
            // --- CONFIGURACIÓN DEL SERVIDOR SMTP ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sofidany.figueroa@gmail.com';      // Tu correo Gmail
            $mail->Password   = 'rgge ibmq dqjk kiub';              // Tu contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // --- PARCHE PARA ERRORES DE CERTIFICADO EN XAMPP ---
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // --- CONFIGURACIÓN DEL MENSAJE ---
            $mail->setFrom('sofidany.figueroa@gmail.com', 'Sistema Comunidad');
            $mail->addAddress($correo); 

            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de Contrasena - Comunidad';
            $mail->Body    = "
                <html>
                <body style='font-family: sans-serif;'>
                    <h2 style='color: #55b83e;'>Hola $username,</h2>
                    <p>Has solicitado restablecer tu contraseña en el sistema de la Comunidad.</p>
                    <p>Haz clic en el botón para crear una nueva clave:</p>
                    <a href='http://localhost/comunidad/reset_final.php?email=$correo' 
                    style='background: #55b83e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block;'>
                    Cambiar mi Contraseña
                    </a>
                    <p>Si no fuiste tú, puedes ignorar este correo de forma segura.</p>
                </body>
                </html>";

            $mail->send();
            $mensaje = "<div style='color:green; background:#dcfce7; padding:15px; border-radius:8px; margin-bottom:20px;'>
                        ¡Enviado! Revisa tu correo ($correo) para continuar.</div>";

        } catch (Exception $e) {
            $mensaje = "<div style='color:red; background:#fee2e2; padding:15px; border-radius:8px;'>
                        Error al enviar: {$mail->ErrorInfo}</div>";
        }
    } else {
        $mensaje = "<div style='color:red; background:#fee2e2; padding:15px; border-radius:8px;'>
                    El correo no está registrado o la cuenta está inactiva.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acceso</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn { background: #55b83e; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn:hover { background: #45a032; }
        a { color: #55b83e; text-decoration: none; font-size: 0.9em; display: block; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <h3>Recuperar Contraseña</h3>
        
        <?php echo $mensaje; ?>
        
        <?php if(!isset($_POST['verificar_correo']) || strpos($mensaje, 'Error') !== false): ?>
        <form method="POST">
            <p style="font-size: 0.9em; color: #666;">Ingresa tu correo para recibir un enlace de recuperación.</p>
            <input type="email" name="correo" placeholder="tu-correo@ejemplo.com" required>
            <button type="submit" name="verificar_correo" class="btn">Enviar enlace</button>
        </form>
        <?php endif; ?>
        
        <a href="login.php">⬅ Volver al Login</a>
    </div>
</body>
</html>