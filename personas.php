<?php 
include 'config.php'; 
// Consulta de configuración para aplicar el tema guardado
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Personas | <?php echo $cfg['nombre_sistema']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #f8fafc; --card: #ffffff; --text: #1e293b; --text-light: #64748b;
            --primary: #55b83e; --primary-hover: #55b83e; --border: #e2e8f0; --input-bg: #ffffff;
        }

        /* Variables específicas para Modo Oscuro */
        [data-theme="dark"] { 
            --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; --text-light: #94a3b8;
            --primary: #2ecc71; --primary-hover: #27ae60; --border: #334155; --input-bg: #0f172a;
        }

        body { 
            font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); 
            margin: 0; padding: 20px; transition: background 0.3s ease, color 0.3s ease; 
        }
        
        .form-container { 
            background: var(--card); padding: 40px; border-radius: 24px; 
            max-width: 600px; margin: 40px auto; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); 
            border: 1px solid var(--border);
        }

        h2 { margin-top: 0; font-weight: 700; color: var(--primary); text-align: center; font-size: 1.8rem; }
        p.subtitle { text-align: center; font-size: 0.95rem; color: var(--text-light); margin-bottom: 30px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.85rem; color: var(--text); }
        
        input, select, textarea { 
            width: 100%; padding: 14px; border: 2px solid var(--border); 
            border-radius: 12px; background: var(--input-bg); color: var(--text);
            font-size: 1rem; transition: all 0.2s ease; box-sizing: border-box;
        }

        input:focus, select:focus, textarea:focus { 
            border-color: var(--primary); outline: none; 
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15); 
            transform: translateY(-1px);
        }

        button { 
            background: var(--primary); color: white; border: none; padding: 16px; 
            width: 100%; border-radius: 14px; cursor: pointer; font-weight: 700; 
            font-size: 1.1rem; margin-top: 25px; transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(46, 204, 113, 0.3);
        }
        button:hover { 
            background: var(--primary-hover); transform: translateY(-2px); 
            box-shadow: 0 20px 25px -5px rgba(46, 204, 113, 0.4); 
        }

        /* Alertas mejoradas */
        .alert { padding: 16px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; text-align: center; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .alert-success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        
        .back-link { 
            display: inline-block; width: 100%; text-align: center; margin-top: 25px; 
            text-decoration: none; color: var(--text-light); font-size: 0.9rem; font-weight: 500;
        }
        .back-link:hover { color: var(--primary); }

        /* Estilo para los iconos dentro de los inputs (opcional) */
        input::placeholder { color: var(--text-light); opacity: 0.5; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Registro de Persona</h2>
    <p class="subtitle">Complete los campos para ingresar al nuevo integrante.</p>

    <?php
    if(isset($_POST['guardar'])){
        // Limpieza de datos
        $rut = mysqli_real_escape_string($conexion, $_POST['rut']);
        $nom = mysqli_real_escape_string($conexion, $_POST['nombres']);
        $ape = mysqli_real_escape_string($conexion, $_POST['apellidos']);
        $gen = $_POST['genero'];
        $fec = $_POST['fecha_nacimiento'];
        $tel = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $ema = mysqli_real_escape_string($conexion, $_POST['email']);
        $dir = mysqli_real_escape_string($conexion, $_POST['direccion']);

        $sql = "INSERT INTO personas (rut, nombres, apellidos, fecha_nacimiento, genero, telefono, direccion, email, created_at, estado) 
                VALUES ('$rut', '$nom', '$ape', '$fec', '$gen', '$tel', '$dir', '$ema', NOW(), 1)";

        if(mysqli_query($conexion, $sql)){
            echo "<div class='alert alert-success'>¡Éxito! $nom ha sido registrado correctamente.</div>";
        } else {
            if(mysqli_errno($conexion) == 1062) {
                echo "<div class='alert alert-error'>El RUT <b>$rut</b> ya se encuentra en nuestra base de datos.</div>";
            } else {
                echo "<div class='alert alert-error'> Error crítico: " . mysqli_error($conexion) . "</div>";
            }
        }
    }
    ?>

    <form method="POST" autocomplete="off">
        <div class="form-grid">
            <div class="full-width">
                <label>RUT / Documento Identidad</label>
                <input type="text" name="rut" id="rut" placeholder="Ej: 12.345.678-9" required maxlength="12">
            </div>

            <div>
                <label>Nombres</label>
                <input type="text" name="nombres" placeholder="Ej: Juan Pedro" required>
            </div>
            
            <div>
                <label>Apellidos</label>
                <input type="text" name="apellidos" placeholder="Ej: Pérez García" required>
            </div>
            
            <div>
                <label>Género</label>
                <select name="genero">
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <div>
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div>
                <label>Teléfono de Contacto</label>
                <input type="tel" name="telefono" placeholder="+56 9 ...">
            </div>
            
            <div>
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="nombre@correo.com">
            </div>

            <div class="full-width">
                <label>Dirección Particular</label>
                <textarea name="direccion" rows="3" placeholder="Calle, número, departamento y comuna..."></textarea>
            </div>
        </div>

        <button type="submit" name="guardar">Finalizar Registro</button>
    </form>

    <a href="index.php" class="back-link">⬅ Volver al Panel de Control</a>
</div>

<script>
    // Script mejorado para formateo de RUT Chileno
    const inputRut = document.getElementById('rut');
    inputRut.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\./g, '').replace('-', '');
        
        if (value.length > 1) {
            let body = value.slice(0, -1);
            let dv = value.slice(-1).toUpperCase();
            
            // Formateo con puntos opcional (aquí solo guion para simplicidad)
            value = body + '-' + dv;
        }
        e.target.value = value;
    });
</script>

</body>
</html>