<?php
// PERSONAS CON HISTORIAL (VERSIÓN MEJORADA)
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Personas';
$pdo = getConnection();

$search = sanitize($_GET['search'] ?? '');
$where = $search ? "WHERE p.nombres LIKE :s OR p.apellidos LIKE :s OR p.rut LIKE :s" : "";
$params = $search ? [':s' => "%$search%"] : [];

$stmt = $pdo->prepare("
SELECT 
    p.*,
    CONCAT(p.nombres,' ',p.apellidos) AS nombre_completo,
    a.accion,
    a.descripcion,
    a.fecha_hora
FROM personas p
LEFT JOIN auditoria a 
    ON a.registro_id = p.idpersonas 
    AND a.tabla = 'personas'
$where
ORDER BY p.idpersonas, a.fecha_hora DESC
");

foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$rows = $stmt->fetchAll();

$personasAgrupadas = [];
foreach ($rows as $r) {
    $id = $r['idpersonas'];

    if (!isset($personasAgrupadas[$id])) {
        $personasAgrupadas[$id] = [
            'nombre' => $r['nombre_completo'],
            'rut' => $r['rut'],
            'historial' => []
        ];
    }

    if ($r['accion']) {
        $personasAgrupadas[$id]['historial'][] = [
            'accion' => $r['accion'],
            'descripcion' => $r['descripcion'],
            'fecha' => $r['fecha_hora']
        ];
    }
}
?>

<table border="1" cellpadding="8">
<tr><th>Nombre</th><th>RUT</th><th>Historial</th></tr>

<?php foreach ($personasAgrupadas as $id => $p): ?>
<tr>
<td><?= $p['nombre'] ?></td>
<td><?= $p['rut'] ?></td>
<td><button onclick="toggleHistorial(<?= $id ?>)">Ver historial</button></td>
</tr>

<tr id="historial-<?= $id ?>" style="display:none;">
<td colspan="3">
<?php if ($p['historial']): ?>
<ul>
<?php foreach ($p['historial'] as $h): ?>
<li><b><?= $h['accion'] ?></b> - <?= $h['descripcion'] ?> (<?= $h['fecha'] ?>)</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
Sin historial
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>

<script>
function toggleHistorial(id){
  const fila = document.getElementById("historial-"+id);
  fila.style.display = (fila.style.display === "none") ? "table-row" : "none";
}
</script>
