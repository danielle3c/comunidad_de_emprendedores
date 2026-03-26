<?php
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

<!-- 🔍 BUSCADOR BONITO -->
<div class="card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">

    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">

      <div class="input-group input-group-sm" style="max-width: 320px;">
        <span class="input-group-text">🔍</span>
        <input 
          type="text" 
          name="search" 
          class="form-control" 
          placeholder="Buscar persona..." 
          value="<?= htmlspecialchars($search ?? '') ?>"
        >
      </div>

      <button class="btn btn-sm btn-outline-primary">
        Buscar
      </button>

      <?php if (!empty($search)): ?>
        <a href="personas.php" class="btn btn-sm btn-outline-secondary">
          Limpiar
        </a>
      <?php endif; ?>

    </form>

  </div>
</div>

<!-- 📋 TABLA -->
<table class="table table-bordered">
<thead>
<tr>
    <th>Nombre</th>
    <th>RUT</th>
    <th>Historial</th>
</tr>
</thead>
<tbody>

<?php foreach ($personasAgrupadas as $id => $p): ?>
<tr>
    <td><?= $p['nombre'] ?></td>
    <td><?= $p['rut'] ?></td>
    <td>
        <button class="btn btn-sm btn-outline-primary" onclick="toggleHistorial(<?= $id ?>)">
            Ver historial
        </button>
    </td>
</tr>

<tr id="historial-<?= $id ?>" style="display:none;">
<td colspan="3">
<?php if ($p['historial']): ?>
<ul>
<?php foreach ($p['historial'] as $h): ?>
<li>
<b><?= $h['accion'] ?></b> - 
<?= $h['descripcion'] ?> 
<small>(<?= $h['fecha'] ?>)</small>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<i>Sin historial</i>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<script>
function toggleHistorial(id){
  const fila = document.getElementById("historial-"+id);
  fila.style.display = (fila.style.display === "none") ? "table-row" : "none";
}
</script>