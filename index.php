<?php 
include 'config.php'; 

// 1. Cargar configuración (Nombre del sistema y Tema)
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Auditoría | <?php echo $cfg['nombre_sistema']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #f4f7fe; --card: #ffffff; --text: #2b3674; --primary: #55b83e; 
            --sidebar: #111c44; --border: #e0e5f2; --secondary-text: #a3aed0;
        }
        [data-theme="dark"] { 
            --bg: #0b1437; --card: #111c44; --text: #ffffff; --primary: #2ecc71; --border: #1b254b; --secondary-text: #707eae;
        }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; height: 100vh; overflow: hidden; }

        /* SIDEBAR */
        .sidebar { 
            width: 280px; background: var(--sidebar); color: white; 
            display: flex; flex-direction: column; padding: 30px 20px; 
            box-sizing: border-box; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }
        .sidebar-brand { font-size: 1.2rem; font-weight: 800; margin-bottom: 50px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; color: var(--primary); }
        
        .nav-link { 
            display: flex; align-items: center; gap: 15px; padding: 16px 20px; 
            color: #707eae; text-decoration: none; border-radius: 15px; 
            margin-bottom: 8px; transition: 0.3s; font-weight: 700;
        }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.05); color: white; }
        .nav-link.active { border-right: 4px solid var(--primary); color: white; }

        /* BOTÓN HAMBURGUESA */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: var(--sidebar);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            align-items: center;
            justify-content: center;
        }

        /* CONTENIDO PRINCIPAL */
        .main-content { flex: 1; overflow-y: auto; padding: 40px; position: relative; }

        /* HEADER Y BUSCADOR */
        .header-section { margin-bottom: 40px; }
        .header-section h1 { font-size: 2.2rem; font-weight: 800; margin: 0; letter-spacing: -1px; }
        
        .search-container { 
            background: var(--card); border-radius: 20px; padding: 10px; 
            display: flex; align-items: center; margin-top: 25px; 
            box-shadow: 14px 17px 40px 4px rgba(112, 144, 176, 0.08);
            border: 1px solid var(--border);
        }
        .search-container input { flex: 1; border: none; padding: 15px 25px; font-size: 1.1rem; outline: none; background: transparent; color: var(--text); }
        .btn-search { background: var(--primary); color: white; border: none; padding: 12px 35px; border-radius: 15px; font-weight: 800; cursor: pointer; transition: 0.3s; }

        /* RESULTADOS */
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; margin-top: 20px; }
        .person-card { background: var(--card); border-radius: 20px; border: 1px solid var(--border); padding: 25px; transition: 0.3s; }

        /* --- MÓVIL --- */
        @media (max-width: 992px) {
            .mobile-toggle { display: flex; }
            .sidebar {
                position: fixed;
                transform: translateX(-100%);
                height: 100vh;
            }
            .sidebar.active { transform: translateX(0); }
            .main-content { padding: 80px 20px 20px 20px; }
            .results-grid { grid-template-columns: 1fr; }
        }

        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(11, 20, 55, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
        }
        .sidebar-overlay.active { display: block; }

        /* Detalles Estéticos */
        .card-top { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .avatar { width: 60px; height: 60px; border-radius: 15px; object-fit: cover; }
        .badge { font-size: 0.65rem; padding: 4px 10px; border-radius: 8px; font-weight: 800; text-transform: uppercase; }
        .badge-base { background: #e2e8f0; color: #475569; }
        .badge-carrito { background: #fef9c3; color: #854d0e; }
        .stats-row { display: flex; justify-content: space-between; padding-top: 15px; border-top: 1px solid var(--border); margin-bottom: 15px; }
        .stat-num { display: block; font-weight: 800; font-size: 1.2rem; }
        .stat-label { font-size: 0.7rem; color: var(--secondary-text); font-weight: 700; }
        .btn-action { display: block; width: 100%; text-align: center; margin-top: 10px; padding: 12px; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 0.85rem; box-sizing: border-box; }
        .btn-trayectoria { background: var(--sidebar); color: white; }
        .btn-formalizar { background: var(--primary); color: white; border: none; }
    </style>
</head>
<body>

<button class="mobile-toggle" id="btnToggle">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar-overlay" id="overlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">Corp. La Granja</div>
    <nav>
        <a href="index.php" class="nav-link active"><i class="fas fa-search"></i> Buscador</a>
        <a href="personas.php" class="nav-link"><i class="fas fa-users"></i> Personas</a>
        <a href="talleres.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i> Talleres</a>
        <a href="lista_carritos.php" class="nav-link"><i class="fas fa-shopping-basket"></i> Carritos</a>
        <a href="creditos.php" class="nav-link"><i class="fas fa-hand-holding-dollar"></i> Créditos</a>
        <a href="cobranzas.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Cobranzas</a>
        <a href="configuraciones.php" class="nav-link"><i class="fas fa-tools"></i> Ajustes</a>
    </nav>
</aside>

<main class="main-content">
    <div class="header-section">
        <h1>Centro de Auditoría</h1>
        <p style="color: var(--secondary-text);">Gestión unificada de clientes y registros.</p>
        
        <form method="GET" class="search-container">
            <i class="fas fa-search" style="margin-left: 20px; color: var(--secondary-text);"></i>
            <input type="text" name="buscar" placeholder="Nombre, RUT o Apellidos..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>" autofocus autocomplete="off">
            <button type="submit" class="btn-search">BUSCAR</button>
        </form>
    </div>

    <div class="results-grid">
        <?php 
        if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))): 
            $busqueda = mysqli_real_escape_string($conexion, $_GET['buscar']);
            $sql = "SELECT idpersonas as id, nombres, apellidos, rut, 'persona' as origen FROM personas 
                    WHERE nombres LIKE '%$busqueda%' OR apellidos LIKE '%$busqueda%' OR rut LIKE '%$busqueda%'
                    UNION
                    SELECT id as id, nombre_responsable as nombres, '' as apellidos, '' as rut, 'carrito' as origen FROM carritos 
                    WHERE nombre_responsable LIKE '%$busqueda%' GROUP BY nombre_responsable";
            
            $res = mysqli_query($conexion, $sql);

            if(mysqli_num_rows($res) > 0):
                while ($p = mysqli_fetch_assoc($res)): 
                    $nombre_p = mysqli_real_escape_string($conexion, $p['nombres']);
                    
                    // Conteo de Visitas (Lógica original)
                    $q_visitas = mysqli_query($conexion, "SELECT COUNT(*) as t FROM carritos WHERE nombre_responsable LIKE '%$nombre_p%' AND asistencia = 'SÍ VINO'");
                    $visitas = mysqli_fetch_assoc($q_visitas)['t'];

                    // Conteo de Talleres (Nueva lógica integrada)
                    $talleres_total = 0;
                    if ($p['origen'] == 'persona') {
                        $q_tallas = mysqli_query($conexion, "SELECT COUNT(*) as t FROM asistencia_talleres WHERE emprendedores_id IN (SELECT idemprendedores FROM emprendedores WHERE personas_idpersonas = {$p['id']})");
                        $talleres_total = mysqli_fetch_assoc($q_tallas)['t'] ?? 0;
                    }
            ?>
                <div class="person-card">
                    <div class="card-top">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($p['nombres']); ?>&background=random&bold=true" class="avatar">
                        <div>
                            <span class="badge <?php echo ($p['origen'] == 'persona') ? 'badge-base' : 'badge-carrito'; ?>">
                                <?php echo ($p['origen'] == 'persona') ? 'Formal' : 'Informal'; ?>
                            </span>
                            <h3 style="margin: 5px 0; font-size: 1.1rem;"><?php echo htmlspecialchars($p['nombres']." ".$p['apellidos']); ?></h3>
                            <small style="color: var(--secondary-text); font-weight: 600;"><i class="far fa-id-card"></i> <?php echo $p['rut'] ?: 'Sin ID'; ?></small>
                        </div>
                    </div>
                    
                    <div class="stats-row">
                        <div class="stat-box">
                            <span class="stat-num"><?php echo $visitas; ?></span>
                            <span class="stat-label">Visitas</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-num" style="color: #3b82f6;"><?php echo $talleres_total; ?></span>
                            <span class="stat-label">Talleres</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-num"><?php echo ($visitas > 4) ? 'Frecuente' : 'Nuevo'; ?></span>
                            <span class="stat-label">Rango</span>
                        </div>
                    </div>

                    <a href="ver_historial.php?nombre=<?php echo urlencode($p['nombres']); ?>&id=<?php echo $p['id']; ?>" class="btn-action btn-trayectoria">TRAYECTORIA</a>

                    <?php if($p['origen'] == 'carrito'): ?>
                        <a href="personas.php?formalizar_nombre=<?php echo urlencode($p['nombres']); ?>" class="btn-action btn-formalizar">REGISTRAR</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; 
            else: ?>
                <div style="grid-column: 1 / -1; text-align:center; padding: 60px; opacity:0.3;">
                    <i class="fas fa-search fa-3x"></i>
                    <p>No hay resultados.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<script>
    const btnToggle = document.getElementById('btnToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    function toggleMenu() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        const icon = btnToggle.querySelector('i');
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
    }

    btnToggle.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);
</script>

</body>
</html>