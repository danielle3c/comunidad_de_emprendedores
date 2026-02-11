<?php
include 'config.php';
// Limpiamos la variable de búsqueda
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - Escuela de Verano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f4f7fe; padding: 20px; color: #2b3674; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background: #111c44; color: white; padding: 12px; text-align: left; }
        .table td { padding: 12px; border-bottom: 1px solid #f4f7fe; }
        .btn-view { background: #f1c40f; color: black; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1><i class="fas fa-history"></i> Historial de Evaluaciones</h1>
    
    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="buscar" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($buscar); ?>" style="padding:10px; border-radius:10px; border:1px solid #ddd; width:300px;">
        <button type="submit" style="padding:10px 20px; background:#422afb; color:white; border:none; border-radius:10px; cursor:pointer;">Buscar</button>
        <a href="digitalizar_escuela.php" style="margin-left:10px; text-decoration:none; color:#55b83e;">+ Nueva Encuesta</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID Excel</th>
                <th>Nombre Emprendedor</th>
                <th>Emprendimiento</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // La consulta ahora usa los nombres exactos de la tabla que creamos arriba
            $sql = "SELECT id_emprendedor, nombre, emprendimiento FROM emprendedores WHERE nombre LIKE '%$buscar%' OR id_emprendedor LIKE '%$buscar%' ORDER BY id_emprendedor ASC";
            $res = mysqli_query($conexion, $sql);

            if ($res && mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    echo "<tr>
                            <td>#{$row['id_emprendedor']}</td>
                            <td>{$row['nombre']}</td>
                            <td>{$row['emprendimiento']}</td>
                            <td><a href='ver_historial_escuela.php?id={$row['id_emprendedor']}' class='btn-view'><i class='fas fa-eye'></i> VER FICHA</a></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; padding:20px;'>No hay registros que coincidan.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>