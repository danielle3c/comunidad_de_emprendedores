<?php
include 'config.php';

$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : "";
$where = $buscar != "" ? "WHERE nombre_responsable LIKE '%$buscar%' OR nombre_carrito LIKE '%$buscar%'" : "";

// Configuración de cabeceras para descarga
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Carritos_" . date('Y-m-d') . ".xls");

$sql = "SELECT * FROM carritos $where ORDER BY created_at DESC";
$res = mysqli_query($conexion, $sql);
?>

<table border="1">
    <thead>
        <tr style="background-color: #55b83e; color: white;">
            <th>Fecha</th>
            <th>Hora Entrada</th>
            <th>Hora Salida</th>
            <th>Responsable</th>
            <th>Teléfono</th>
            <th>Carrito</th>
            <th>Asistencia</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($res)): 
            $fecha = new DateTime($row['created_at']);
        ?>
        <tr>
            <td><?php echo $fecha->format('d/m/Y'); ?></td>
            <td><?php echo $fecha->format('H:i'); ?></td>
            <td><?php echo $row['hora_salida']; ?></td>
            <td><?php echo utf8_decode($row['nombre_responsable']); ?></td>
            <td><?php echo $row['telefono_responsable']; ?></td>
            <td><?php echo utf8_decode($row['nombre_carrito']); ?></td>
            <td><?php echo $row['asistencia']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>