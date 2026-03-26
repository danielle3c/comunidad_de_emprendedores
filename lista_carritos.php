<?php 
include 'config.php'; 

// 1. Cargar configuración (Tema y Nombre del Sistema)
$res_conf = mysqli_query($conexion, "SELECT * FROM configuraciones WHERE id = 1");
$cfg = mysqli_fetch_assoc($res_conf);

// 2. Lógica de Búsqueda
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : "";

// --- LÓGICA DE PAGINACIÓN ---
$resultados_por_pagina = 10; 
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $resultados_por_pagina;

// Filtro WHERE reutilizable para conteo y consulta
$where = $buscar != "" ? "WHERE nombre_responsable LIKE '%$buscar%' OR nombre_carrito LIKE '%$buscar%'" : "";

// Contar total de registros
$total_res = mysqli_query($conexion, "SELECT COUNT(*) as total FROM carritos $where");
$total_filas = mysqli_fetch_assoc($total_res)['total'];
$total_paginas = ceil($total_filas / $resultados_por_pagina);
// ----------------------------

$hoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $cfg['tema_color']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Carritos | <?php echo $cfg['nombre_sistema']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --bg: #f1f5f9; --card: #ffffff; --text: #1e293b; 
            --primary: #55b83e; --secondary: #3b82f6; --border: #e2e8f0; 
            --excel: #1D6F42; --pdf: #E44D26; 
        }
        [data-theme="dark"] { 
            --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; 
            --primary: #2ecc71; --secondary: #60a5fa; --border: #334155; 
        }
        
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding: 15px; margin: 0; }
        .container { max-width: 1000px; margin: auto; }
        
        /* Encabezado y Navegación */
        .header-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 10px; flex-wrap: wrap; }
        .nav-group { display: flex; gap: 8px; align-items: center; }
        
        .btn-nav { 
            text-decoration: none; color: var(--text); background: var(--card); 
            padding: 10px 15px; border-radius: 12px; border: 1px solid var(--border); 
            font-size: 0.9rem; font-weight: bold; transition: 0.3s; 
            display: flex; align-items: center; gap: 8px; 
        }
        .btn-nav:hover { background: var(--bg); border-color: var(--primary); transform: translateY(-1px); }
        
        /* Botones Especiales */
        .btn-primary { background: var(--primary); color: white; border: none; }
        .btn-excel { background: var(--excel); color: white; border: none; }
        .btn-pdf { background: var(--pdf); color: white; border: none; }

        /* Tabla Estilizada */
        .card-table { background: var(--card); border-radius: 15px; border: 1px solid var(--border); overflow-x: auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th { background: rgba(0,0,0,0.02); padding: 12px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b; border-bottom: 1px solid var(--border); }
        td { padding: 12px; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }

        .tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 6px; font-weight: bold; font-size: 0.75rem; }
        .tag-entrada { background: rgba(67, 176, 42, 0.1); color: var(--primary); }
        .tag-salida { background: rgba(59, 130, 246, 0.1); color: var(--secondary); }

        .badge { padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: bold; }
        .si { background: #dcfce7; color: #55b83e; }
        .no { background: #fee2e2; color: #b91c1c; }

        /* Paginación */
        .pagination { display: flex; justify-content: center; align-items: center; gap: 5px; margin: 20px 0 40px; }
        .page-link { text-decoration: none; padding: 8px 14px; background: var(--card); border: 1px solid var(--border); color: var(--text); border-radius: 8px; font-weight: bold; transition: 0.3s; font-size: 0.9rem; }
        .page-link:hover { border-color: var(--primary); color: var(--primary); }
        .page-link.active { background: var(--primary); color: white; border-color: var(--primary); }
        .page-info { font-size: 0.8rem; color: #64748b; margin-bottom: 10px; text-align: center; }

        .search-container { margin-bottom: 20px; display: flex; gap: 8px; }
        .search-input { flex: 1; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--card); color: var(--text); font-size: 1rem; }
        
        @media (max-width: 600px) { .hide-mobile { display: none; } .header-nav { justify-content: center; } }
    </style>
</head>
<body>

<div class="container">
    <div class="header-nav">
        <div class="nav-group">
            <a href="index.php" class="btn-nav"><i class="fas fa-home"></i> <span class="hide-mobile">Inicio</span></a>
            
            <a href="exportar_excel.php?buscar=<?php echo urlencode($buscar); ?>" class="btn-nav btn-excel" title="Exportar a Excel">
                <i class="fas fa-file-excel"></i> <span class="hide-mobile">Excel</span>
            </a>
            <a href="exportar_pdf.php?buscar=<?php echo urlencode($buscar); ?>" class="btn-nav btn-pdf" title="Exportar a PDF" target="_blank">
                <i class="fas fa-file-pdf"></i> <span class="hide-mobile">PDF</span>
            </a>
        </div>

        <h2 style="margin: 0; font-size: 1.2rem;"><i class="fas fa-list-check"></i> Historial</h2>

        <a href="carritos.php" class="btn-nav" style="background: var(--secondary); color: white; border: none;">
            <i class="fas fa-plus"></i> <span class="hide-mobile">Nuevo</span>
        </a>
    </div>

    <form method="GET" class="search-container">
        <input type="text" name="buscar" class="search-input" placeholder="Buscar responsable o carrito..." value="<?php echo htmlspecialchars($buscar); ?>">
        <button type="submit" class="btn-nav btn-primary"><i class="fas fa-search"></i></button>
        <?php if($buscar != ""): ?>
            <a href="lista_carritos.php" class="btn-nav" title="Limpiar búsqueda"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </form>

    <div class="card-table">
        <table>
            <thead>
                <tr>
                    <th>Fecha y Horarios</th>
                    <th>Responsable</th>
                    <th class="hide-mobile">Carrito</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta paginada
                $sql = "SELECT * FROM carritos $where ORDER BY created_at DESC LIMIT $inicio, $resultados_por_pagina";
                $res = mysqli_query($conexion, $sql);

                if (mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)) {
                        $fecha_dt = new DateTime($row['created_at']);
                        $clase_ast = ($row['asistencia'] == 'SÍ VINO') ? 'si' : 'no';
                    ?>
                    <tr>
                        <td>
                            <div style="display:grid; gap:4px">
                                <strong><?php echo $fecha_dt->format('d/m/Y'); ?></strong>
                                <span class="tag tag-entrada"><i class="fas fa-clock"></i> <?php echo $fecha_dt->format('H:i'); ?></span>
                                <?php if(!empty($row['hora_salida'])): ?>
                                    <span class="tag tag-salida"><i class="fas fa-sign-out-alt"></i> <?php echo $row['hora_salida']; ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['nombre_responsable']); ?></strong>
                            <div style="font-size: 0.75rem; opacity: 0.6;"><?php echo htmlspecialchars($row['telefono_responsable']); ?></div>
                        </td>
                        <td class="hide-mobile"><?php echo htmlspecialchars($row['nombre_carrito']); ?></td>
                        <td><span class="badge <?php echo $clase_ast; ?>"><?php echo $row['asistencia']; ?></span></td>
                        <td>
                            <div style="display: flex; gap: 15px;">
                                <a href="editar_carrito.php?id=<?php echo $row['id']; ?>" style="color: var(--secondary);" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="eliminar.php?id=<?php echo $row['id']; ?>" style="color: #ef4444;" onclick="return confirm('¿Seguro que deseas eliminar este registro?')" title="Eliminar"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php } 
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding:40px; color: #64748b;'>No se encontraron registros para tu búsqueda.</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>

    <?php if($total_paginas > 1): ?>
        <div class="page-info">Mostrando página <?php echo $pagina; ?> de <?php echo $total_paginas; ?></div>
        <div class="pagination">
            <?php if($pagina > 1): ?>
                <a href="?p=<?php echo $pagina-1; ?>&buscar=<?php echo urlencode($buscar); ?>" class="page-link">&laquo;</a>
            <?php endif; ?>

            <?php 
            // Lógica para no mostrar demasiados números de página si hay cientos
            $rango = 2;
            for($i = 1; $i <= $total_paginas; $i++): 
                if($i == 1 || $i == $total_paginas || ($i >= $pagina - $rango && $i <= $pagina + $rango)) {
                    if($i == $pagina) {
                        echo "<span class='page-link active'>$i</span>";
                    } else {
                        echo "<a href='?p=$i&buscar=".urlencode($buscar)."' class='page-link'>$i</a>";
                    }
                }
            endfor; 
            ?>

            <?php if($pagina < $total_paginas): ?>
                <a href="?p=<?php echo $pagina+1; ?>&buscar=<?php echo urlencode($buscar); ?>" class="page-link">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>