<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

secure_session_start();

if (empty($_SESSION['user']['id'])) {
    die('No autorizado');
}

$usuario_id = (int)($_GET['id'] ?? 0);
$pdo = getConnection();

$stmt = $pdo->prepare("
    SELECT accion, tabla, registro_id, descripcion, ip_address, fecha_hora
    FROM auditoria
    WHERE usuario_id = ?
    ORDER BY fecha_hora DESC
    LIMIT 100
");
$stmt->execute([$usuario_id]);

$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Historial del Usuario</h3>

<table border="1" width="100%">
<tr>
    <th>Fecha</th>
    <th>Acción</th>
    <th>Tabla</th>
    <th>Registro</th>
    <th>IP</th>
</tr>

<?php foreach($historial as $h): ?>
<tr>
    <td><?= $h['fecha_hora'] ?></td>
    <td><?= $h['accion'] ?></td>
    <td><?= $h['tabla'] ?></td>
    <td><?= $h['registro_id'] ?></td>
    <td><?= $h['ip_address'] ?></td>
</tr>
<?php endforeach; ?>
</table>