<?php
include 'config.php';

$nombre = $_GET['nombre'] ?? '';
$id_ref = (int)($_GET['id'] ?? 0);
$origen = $_GET['origen'] ?? '';

// 1. Configuración de colores del sistema
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - <?php echo $nombre; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root { --bg: #f1f5f9; --text: #1e293b; --primary: #55b83e; --card: #ffffff; --border: #e2e8f0; }
        [data-theme="dark"] { --bg: #0f172a; --text: #f1f5f9; --primary: #2ecc71; --card: #1e293b; --border: #334155; }
        
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); padding: 20px; margin: 0; }
        .header { max-width: 800px; margin: 20px auto 40px; display: flex; align-items: center; gap: 20px; }
        .btn-back { text-decoration: none; color: var(--text); font-size: 1.2rem; background: var(--card); width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid var(--border); transition: 0.3s; }
        .btn-back:hover { background: var(--primary); color: white; }

        /* Botones de Exportación */
        .export-tools { max-width: 800px; margin: -20px auto 20px; display: flex; gap: 10px; justify-content: flex-end; }
        .btn-export { padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 700; display: flex; align-items: center; gap: 8px; transition: 0.2s; font-size: 0.9rem; }
        .btn-excel { background: #1d6f42; color: white; }
        .btn-pdf { background: #e74c3c; color: white; }
        .btn-export:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Estilo del Timeline */
        .timeline { max-width: 800px; margin: auto; position: relative; padding-bottom: 50px; }
        .timeline::before { content: ''; position: absolute; left: 31px; top: 0; bottom: 0; width: 2px; background: var(--border); }
        
        .timeline-item { position: relative; margin-bottom: 35px; padding-left: 80px; animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .timeline-icon { 
            position: absolute; left: 15px; top: 0; width: 35px; height: 35px; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center; 
            color: white; font-size: 0.9rem; z-index: 2; border: 4px solid var(--bg);
        }
        .icon-carrito { background: #3b82f6; box-shadow: 0 0 15px rgba(59, 130, 246, 0.4); }
        .icon-credito { background: #f59e0b; box-shadow: 0 0 15px rgba(245, 158, 11, 0.4); }

        .timeline-content { background: var(--card); padding: 20px; border-radius: 18px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .timeline-date { font-size: 0.75rem; opacity: 0.6; font-weight: 700; display: block; margin-bottom: 8px; text-transform: uppercase; }
        .timeline-title { font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--text); }
        .timeline-desc { font-size: 0.95rem; margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px; }
        
        .badge-info { padding: 5px 12px; border-radius: 8px; background: var(--bg); font-size: 0.8rem; font-weight: 700; border: 1px solid var(--border); }
    </style>
</head>
<body>

<div class="header">
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 style="margin:0; letter-spacing: -1px;"><?php echo htmlspecialchars($nombre); ?></h1>
        <p style="margin:0; opacity:0.6;">Línea de tiempo de actividades</p>
    </div>
</div>

<div class="export-tools">
    <button onclick="exportarExcel()" class="btn-export btn-excel">
        <i class="fas fa-file-excel"></i> Exportar Excel
    </button>
    <button onclick="exportarPDF()" class="btn-export btn-pdf">
        <i class="fas fa-file-pdf"></i> Exportar PDF
    </button>
</div>

<div class="timeline" id="contenido-historial">
    <?php
    $nom_sql = mysqli_real_escape_string($conexion, $nombre);
    
    $q_emp = mysqli_query($conexion, "SELECT idemprendedores FROM emprendedores WHERE personas_idpersonas = $id_ref LIMIT 1");
    $emp_data = mysqli_fetch_assoc($q_emp);
    $id_emp = $emp_data['idemprendedores'] ?? 0;

    $sql = "SELECT 'asistencia' as tipo, nombre_carrito as titulo, asistencia as detalle, created_at as fecha 
            FROM carritos WHERE nombre_responsable LIKE '%$nom_sql%'
            UNION
            SELECT 'credito' as tipo, 'Crédito Otorgado' as titulo, CONCAT('Monto inicial: $', monto_inicial) as detalle, created_at as fecha 
            FROM creditos WHERE emprendedores_idemprendedores = $id_emp AND $id_emp > 0
            ORDER BY fecha DESC";

    $res = mysqli_query($conexion, $sql);

    if(mysqli_num_rows($res) > 0):
        while($ev = mysqli_fetch_assoc($res)):
            $es_carrito = ($ev['tipo'] == 'asistencia');
            $fecha_formateada = date('d M, Y - H:i', strtotime($ev['fecha']));
    ?>
        <div class="timeline-item">
            <div class="timeline-icon <?php echo $es_carrito ? 'icon-carrito' : 'icon-credito'; ?>">
                <i class="fas <?php echo $es_carrito ? 'fa-shopping-cart' : 'fa-money-bill-wave'; ?>"></i>
            </div>
            <div class="timeline-content">
                <span class="timeline-date"><i class="far fa-calendar-alt"></i> <?php echo $fecha_formateada; ?></span>
                <h3 class="timeline-title"><?php echo htmlspecialchars($ev['titulo']); ?></h3>
                <div class="timeline-desc">
                    <span class="badge-info">
                        <?php echo $es_carrito ? "Estado: " : ""; ?>
                        <?php echo htmlspecialchars($ev['detalle']); ?>
                    </span>
                    <?php if(!$es_carrito): ?>
                        <span class="badge-info" style="color: #f59e0b;">Detalles del crédito</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php 
        endwhile;
    else:
        echo "<div style='text-align:center; padding: 40px; background: var(--card); border-radius: 20px; opacity:0.6;'>
                <i class='fas fa-history' style='font-size: 2rem; margin-bottom: 10px;'></i>
                <p>No se encontraron actividades registradas para este usuario.</p>
              </div>";
    endif; 
    ?>
</div>

<script>
// FUNCION EXCEL
function exportarExcel() {
    const items = document.querySelectorAll('.timeline-item');
    const data = [['Fecha y Hora', 'Tipo de Actividad', 'Título/Carrito', 'Detalle del Registro']];

    items.forEach(item => {
        const fecha = item.querySelector('.timeline-date').innerText.trim();
        const titulo = item.querySelector('.timeline-title').innerText.trim();
        const detalle = item.querySelector('.badge-info').innerText.trim();
        const tipo = item.querySelector('.timeline-icon').classList.contains('icon-carrito') ? 'Asistencia' : 'Crédito';
        data.push([fecha, tipo, titulo, detalle]);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);
    
    // Ajuste básico de ancho de columnas
    ws['!cols'] = [{wch:25}, {wch:15}, {wch:30}, {wch:40}];
    
    XLSX.utils.book_append_sheet(wb, ws, "Historial");
    XLSX.writeFile(wb, "Historial_<?php echo $nombre; ?>.xlsx");
}

// FUNCION PDF
function exportarPDF() {
    const elemento = document.body;
    const opciones = {
        margin: [0.5, 0.5],
        filename: 'Historial_<?php echo $nombre; ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, letterRendering: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    // Ocultar elementos que no queremos en el PDF
    const botones = document.querySelector('.export-tools');
    const backBtn = document.querySelector('.btn-back');
    
    botones.style.display = 'none';
    backBtn.style.visibility = 'hidden';

    html2pdf().set(opciones).from(elemento).save().then(() => {
        botones.style.display = 'flex';
        backBtn.style.visibility = 'visible';
    });
}
</script>

</body>
</html>