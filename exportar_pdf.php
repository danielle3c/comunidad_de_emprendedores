<?php
include 'config.php';

// 1. Obtener datos de configuración para el encabezado del PDF
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

// 2. Lógica de filtro (igual que en la lista principal)
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : "";
$where = $buscar != "" ? "WHERE nombre_responsable LIKE '%$buscar%' OR nombre_carrito LIKE '%$buscar%'" : "";

$sql = "SELECT * FROM carritos $where ORDER BY created_at DESC";
$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte_<?php echo date('d_m_Y'); ?></title>
    <style>
        /* Estilos para pantalla y PDF */
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #333; line-height: 1.6; padding: 20px; }
        .header-report { text-align: center; border-bottom: 2px solid #55b83e; margin-bottom: 20px; padding-bottom: 10px; }
        .header-report h1 { margin: 0; color: #1e293b; font-size: 24px; }
        .header-report p { margin: 5px 0; color: #64748b; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; padding: 10px; border: 1px solid #e2e8f0; }
        td { padding: 8px; border: 1px solid #e2e8f0; font-size: 12px; }
        tr:nth-child(even) { background-color: #fcfcfc; }

        .status { font-weight: bold; font-size: 10px; padding: 2px 5px; border-radius: 4px; }
        .status-si { color: #15803d; background: #dcfce7; }
        .status-no { color: #b91c1c; background: #fee2e2; }

        /* Botones de acción (no se imprimen) */
        .no-print { 
            display: flex; gap: 10px; justify-content: center; 
            background: #f1f5f9; padding: 15px; border-radius: 10px; margin-bottom: 30px; 
        }
        .btn { 
            padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; 
            font-weight: bold; text-decoration: none; font-size: 14px; transition: 0.3s;
        }
        .btn-print { background: #55b83e; color: white; }
        .btn-close { background: #64748b; color: white; }
        
        /* Reglas de Impresión */
        @media print {
            .no-print { display: none; } /* Ocultar botones al imprimir */
            body { padding: 0; margin: 0; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Confirmar Impresión / Guardar PDF
        </button>
        <button onclick="window.close()" class="btn btn-close">Cerrar Ventana</button>
    </div>

    <div class="header-report">
        <h1><?php echo strtoupper($cfg['nombre_sistema']); ?></h1>
        <h1>Reporte de Actividad de Carritos</h1>
        <p>Generado el: <?php echo date('d/m/Y - H:i:s'); ?></p>
        <?php if($buscar != ""): ?>
            <p><strong>Filtro aplicado:</strong> "<?php echo htmlspecialchars($buscar); ?>"</p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Responsable</th>
                <th>Carrito</th>
                <th>Asistencia</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(mysqli_num_rows($res) > 0):
                while($row = mysqli_fetch_assoc($res)): 
                    $f = new DateTime($row['created_at']);
                    $status_class = ($row['asistencia'] == 'SÍ VINO') ? 'status-si' : 'status-no';
            ?>
            <tr>
                <td style="font-weight: bold;"><?php echo $f->format('d/m/Y'); ?></td>
                <td><?php echo $f->format('H:i'); ?></td>
                <td><?php echo $row['hora_salida'] ?: '--:--'; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($row['nombre_responsable']); ?></strong><br>
                    <small style="color: #666;"><?php echo $row['telefono_responsable']; ?></small>
                </td>
                <td><?php echo htmlspecialchars($row['nombre_carrito']); ?></td>
                <td style="text-align: center;">
                    <span class="status <?php echo $status_class; ?>">
                        <?php echo $row['asistencia']; ?>
                    </span>
                </td>
            </tr>
            <?php 
                endwhile; 
            else:
                echo "<tr><td colspan='6' style='text-align:center;'>No hay datos para mostrar</td></tr>";
            endif;
            ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #94a3b8; text-align: center;">
        Fin del reporte - Página 1
    </div>

    <script>
        // window.onload = function() { window.print(); }
    </script>

</body>
</html>