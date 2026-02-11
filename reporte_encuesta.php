<?php
include 'config.php';

// Cargar configuración
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

function contarRespuesta($columna, $valor, $con) {
    $res = mysqli_query($con, "SELECT COUNT(*) as total FROM encuesta_2026 WHERE $columna = '$valor'");
    $data = mysqli_fetch_assoc($res);
    return $data['total'] ?? 0;
}

$preguntas = [
    'conoce_corporacion' => '¿Conoce la Corporación?',
    'contacto_municipalidad' => '¿Contacto con Municipalidad?',
    'interes_iniciativas' => '¿Interés en Iniciativas?',
    'participar_video' => '¿Participar en Video?'
];
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas Pro 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --bg: #f4f7fe; --card: #ffffff; --text: #2b3674; --primary: #4318FF; --success: #05CD99; --danger: #EE5D50; --border: #e0e5f2; }
        [data-theme="dark"] { --bg: #0b1437; --card: #111c44; --text: #ffffff; --border: #1b254b; }
        
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); padding: 40px; margin: 0; }
        .header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .btn-back { padding: 10px 20px; background: var(--card); border-radius: 12px; text-decoration: none; color: var(--text); font-weight: bold; border: 1px solid var(--border); transition: 0.3s; }
        .btn-back:hover { background: var(--primary); color: white; }

        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .stat-card { background: var(--card); padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--border); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        
        .chart-container { position: relative; height: 200px; margin-top: 20px; }
        .total-badge { background: var(--bg); padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; color: var(--primary); }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1>Dashboard de Encuestas 2026</h1>
            <p style="color: #a3aed0;">Análisis visual de participación y conocimiento.</p>
        </div>
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Panel Principal</a>
    </div>

    <div class="grid-stats">
        <?php foreach ($preguntas as $columna => $titulo): 
            $si = contarRespuesta($columna, 'SI', $conexion);
            $no = contarRespuesta($columna, 'NO', $conexion);
            $id_canvas = "chart_" . $columna;
        ?>
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <h3 style="margin: 0; font-size: 1.1rem; max-width: 70%;"><?php echo $titulo; ?></h3>
                    <span class="total-badge"><?php echo ($si + $no); ?> Total</span>
                </div>
                
                <div class="chart-container">
                    <canvas id="<?php echo $id_canvas; ?>"></canvas>
                </div>

                <script>
                    new Chart(document.getElementById('<?php echo $id_canvas; ?>'), {
                        type: 'doughnut',
                        data: {
                            labels: ['SÍ', 'NO'],
                            datasets: [{
                                data: [<?php echo $si; ?>, <?php echo $no; ?>],
                                backgroundColor: ['#05CD99', '#EE5D50'],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, color: '<?php echo ($cfg['tema_color'] == 'dark' ? '#fff' : '#2b3674'); ?>' } }
                            },
                            cutout: '70%'
                        }
                    });
                </script>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>