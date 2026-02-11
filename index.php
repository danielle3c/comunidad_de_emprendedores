<?php 
include 'config.php'; 

// 1. Cargar configuración del sistema
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
            --escuela: #f1c40f; /* Color especial para Escuela de Verano */
        }
        [data-theme="dark"] { 
            --bg: #0b1437; --card: #111c44; --text: #ffffff; --primary: #2ecc71; --border: #1b254b; --secondary-text: #707eae;
        }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; height: 100vh; overflow: hidden; }

        /* SIDEBAR */
        .sidebar { width: 280px; background: var(--sidebar); color: white; display: flex; flex-direction: column; padding: 30px 20px; box-sizing: border-box; z-index: 1000; }
        .sidebar-brand { font-size: 1.2rem; font-weight: 800; margin-bottom: 50px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; color: var(--primary); }
        .nav-link { display: flex; align-items: center; gap: 15px; padding: 16px 20px; color: #707eae; text-decoration: none; border-radius: 15px; margin-bottom: 8px; transition: 0.3s; font-weight: 700; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.05); color: white; }
        .nav-link.active { border-right: 4px solid var(--primary); color: white; }

        /* BUSCADOR */
        .main-content { flex: 1; overflow-y: auto; padding: 40px; }
        .search-container { background: var(--card); border-radius: 20px; padding: 10px; display: flex; align-items: center; margin-top: 25px; box-shadow: 14px 17px 40px 4px rgba(112, 144, 176, 0.08); border: 1px solid var(--border); }
        .search-container input { flex: 1; border: none; padding: 15px 25px; font-size: 1.1rem; outline: none; background: transparent; color: var(--text); }
        .btn-search { background: var(--primary); color: white; border: none; padding: 12px 35px; border-radius: 15px; font-weight: 800; cursor: pointer; }

        /* CARDS DE RESULTADOS */
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; margin-top: 20px; }
        .person-card { background: var(--card); border-radius: 20px; border: 1px solid var(--border); padding: 25px; position: relative; }
        .card-escuela { border-top: 5px solid var(--escuela); }
        
        .badge { font-size: 0.65rem; padding: 4px 10px; border-radius: 8px; font-weight: 800; text-transform: uppercase; }
        .badge-base { background: #e2e8f0; color: #475569; }
        .badge-escuela { background: #fef9c3; color: #92400e; }

        .btn-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
        .btn-action { text-align: center; padding: 12px; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 0.85rem; }
        .btn-trayectoria { background: var(--sidebar); color: white; }
        .btn-escuela { background: var(--escuela); color: #000; }
        .btn-editar { background: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">Corp. La Granja</div>
    <nav>
        <a href="index.php" class="nav-link active"><i class="fas fa-search"></i> Buscador</a>
        <a href="digitalizar_escuela.php" class="nav-link" style="color: var(--escuela);">
            <i class="fas fa-edit"></i> Digitalizar Escuela
        </a>
        <a href="personas.php" class="nav-link"><i class="fas fa-users"></i> Personas</a>
        <a href="talleres.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i> Talleres</a>
        <a href="lista_carritos.php" class="nav-link"><i class="fas fa-shopping-basket"></i> Carritos</a>
        <a href="creditos.php" class="nav-link"><i class="fas fa-hand-holding-dollar"></i> Créditos</a>
        <a href="cobranzas.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Cobranzas</a>
    </nav>
</aside>

<main class="main-content">
    <div class="header-section">
        <h1>Centro de Auditoría</h1>
        <form method="GET" class="search-container">
            <input type="text" name="buscar" placeholder="Buscar por Nombre, RUT o Escuela..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>" autofocus>
            <button type="submit" class="btn-search">BUSCAR</button>
        </form>
    </div>

    <div class="results-grid">
        <?php 
        if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))): 
            $busqueda = mysqli_real_escape_string($conexion, $_GET['buscar']);
            
            // Query unificada incluyendo la nueva tabla
            $sql = "SELECT idpersonas as id, nombres, apellidos, rut, 'persona' as origen FROM personas 
                    WHERE nombres LIKE '%$busqueda%' OR apellidos LIKE '%$busqueda%' OR rut LIKE '%$busqueda%'
                    UNION
                    SELECT id as id, nombre_responsable as nombres, '' as apellidos, '' as rut, 'carrito' as origen FROM carritos 
                    WHERE nombre_responsable LIKE '%$busqueda%'
                    UNION
                    SELECT id_escuela as id, nombre_emprendedor as nombres, '' as apellidos, '' as rut, 'escuela' as origen FROM escuela_verano 
                    WHERE nombre_emprendedor LIKE '%$busqueda%'";
            
            $res = mysqli_query($conexion, $sql);

            while ($p = mysqli_fetch_assoc($res)): 
                $id_ref = $p['id'];
                $origen = $p['origen'];
        ?>
            <div class="person-card <?php echo ($origen == 'escuela') ? 'card-escuela' : ''; ?>">
                <span class="badge <?php echo ($origen == 'escuela') ? 'badge-escuela' : 'badge-base'; ?>">
                    <?php echo strtoupper($origen); ?>
                </span>
                <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($p['nombres']." ".$p['apellidos']); ?></h3>
                
                <div class="btn-group">
                    <?php if($origen == 'escuela'): ?>
                        <a href="ver_historial_escuela.php?id=<?php echo $id_ref; ?>" class="btn-action btn-escuela">REPORTE PDF</a>
                        <a href="digitalizar_escuela.php?id=<?php echo $id_ref; ?>" class="btn-action btn-editar">EDITAR</a>
                    <?php else: ?>
                        <a href="ver_historial.php?id=<?php echo $id_ref; ?>" class="btn-action btn-trayectoria">TRAYECTORIA</a>
                        <a href="editar.php?id=<?php echo $id_ref; ?>" class="btn-action btn-editar">EDITAR</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; endif; ?>
    </div>
</main>
</body>
</html>