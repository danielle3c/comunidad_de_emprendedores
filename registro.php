<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Comunidad</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reg-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 350px; }
        .btn-save { width: 100%; padding: 12px; background: #43b02a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        
        /* 2. Estilos para el contenedor de la contraseña */
        .password-container { position: relative; }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="reg-card">
        <h2 style="text-align:center; color: #55b83e;">Crear Cuenta</h2>
        <?php if(isset($error)) echo "<p style='color:red; font-size:0.8em;'>$error</p>"; ?>
        
        <form method="POST">
            <label>ID de Persona:</label>
            <input type="number" name="id_persona" placeholder="Ej: 15" required>
            
            <label>Usuario:</label>
            <input type="text" name="username" placeholder="juan.perez" required>
            
            <label>Contraseña Nueva:</label>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="********" required>
                <i class="fa-solid fa-eye toggle-password" id="toggleEye"></i>
            </div>
            
            <button type="submit" name="registrar" class="btn-save">Registrar Trabajador</button>
        </form>
        <p style="text-align:center;"><a href="login.php" style="color:#666; font-size:0.8em;">Volver al login</a></p>
    </div>

    <script>
        const toggleEye = document.querySelector('#toggleEye');
        const password = document.querySelector('#password');

        toggleEye.addEventListener('click', function () {
            // Cambiar el tipo de atributo
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Cambiar el icono
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>