<?php 
// 1. Cargar la librería PHPMailer (Asegúrate que la carpeta PHPMailer existe)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'config.php'; 

$mensaje = "";

if (isset($_POST['verificar_correo'])) {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    
    // Verificar si el email existe en la BD
    $sql = "SELECT username FROM Usuarios WHERE email = '$correo' AND estado = 1";
    $res = mysqli_query($conexion, $sql);

    if ($datos = mysqli_fetch_assoc($res)) {
        $username = $datos['username'];
        $mail = new PHPMailer(true);

        try {
            // --- CONFIGURACIÓN DEL SERVIDOR SMTP ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sofidany.figueroa@gmail.com';      // CAMBIA POR TU GMAIL
            $mail->Password   = 'rgge ibmq dqjk kiub';      // CAMBIA POR TUS 16 LETRAS DE GOOGLE
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
            $mail->setFrom('tu-correo@gmail.com', 'Sistema Comunidad');
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
                        ¡Enviado! Revisa tu correo institucional ($correo).</div>";

        } catch (Exception $e) {
            $mensaje = "<div style='color:red; background:#fee2e2; padding:15px; border-radius:8px;'>
                        Error al enviar: {$mail->ErrorInfo}</div>";
        }
    } else {
        $mensaje = "<p style='color:red;'> El correo no está registrado en el sistema.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Acceso</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn { background: #55b83e; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #55b83e; }
        a { color: #55b83e; text-decoration: none; font-size: 0.9em; display: block; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <h3>Recuperar Contraseña</h3>
        
        <?php echo $mensaje; ?>
        
        <?php if(!isset($_POST['verificar_correo']) || strpos($mensaje, 'no exsite') !== false): ?>
        <form method="POST">
            <p style="font-size: 0.9em; color: #666;">Ingresa tu correo para recibir un enlace de recuperación.</p>
            <input type="email" name="correo" placeholder="tu-correo@empresa.cl" required>
            <button type="submit" name="verificar_correo" class="btn">Enviar enlace</button>
        </form>
        <?php endif; ?>
        
        <a href="login.php">⬅ Volver al Login</a>
    </div>
</body>
</html>