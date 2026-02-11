<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Usuarios</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f4f7f6; }
        .user-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .user-table th, .user-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .user-table th { background: #55b83e; color: white; }
        .btn-edit { background: #f97316; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .btn-delete { background: #dc2626; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; border: none; cursor: pointer; }
        .status-msg { padding: 10px; background: #dcfce7; color: #166534; margin-bottom: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>👥 Usuarios del Sistema</h2>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="status-msg">Usuario eliminado correctamente (Borrado lógico).</div>
    <?php endif; ?>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Persona Asociada</th>
                <th>Estado</th>
                <th colspan="2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT u.idUsuarios, u.username, u.estado, p.nombres, p.apellidos 
                    FROM Usuarios u 
                    LEFT JOIN personas p ON u.personas_idpersonas = p.idpersonas 
                    WHERE u.deleted_at IS NULL OR u.deleted_at = '0000-00-00 00:00:00'";
            
            $res = mysqli_query($conexion, $sql);
            
            while($u = mysqli_fetch_assoc($res)){
                $est = ($u['estado'] == 1) ? "Activo" : "Bloqueado";
                $nombre_p = ($u['nombres']) ? $u['nombres']." ".$u['apellidos'] : "<em>Sin asignar</em>";
                $id = $u['idUsuarios'];
                ?>
                <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $u['username']; ?></td>
                    <td><?php echo $nombre_p; ?></td>
                    <td><?php echo $est; ?></td>
                    <td>
                        <a href="editar_usuario.php?id=<?php echo $id; ?>" class="btn-edit">Modificar</a>
                    </td>
                    <td>
                        <a href="eliminar_usuario.php?id=<?php echo $id; ?>" 
                        class="btn-delete" 
                        onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">
                        Eliminar
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>