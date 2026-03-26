<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Personas';
$pdo = getConnection();

$search = sanitize($_GET['search'] ?? '');

if ($search) {
    $stmt = $pdo->prepare("
        SELECT 
            p.idpersonas,
            CONCAT(p.nombres,' ',p.apellidos) AS nombre,
            p.rut
        FROM personas p
        WHERE p.nombres LIKE :s 
           OR p.apellidos LIKE :s 
           OR p.rut LIKE :s
        ORDER BY p.nombres ASC
    ");
    $stmt->execute([':s' => "%$search%"]);
} else {
    $stmt = $pdo->prepare("
        SELECT 
            p.idpersonas,
            CONCAT(p.nombres,' ',p.apellidos) AS nombre,
            p.rut
        FROM personas p
        ORDER BY p.nombres ASC
    ");
    $stmt->execute();
}

$rows = $stmt->fetchAll();

/* 🔥 AGRUPAR */
$personasAgrupadas = [];

foreach ($rows as $r) {
    $id = $r['idpersonas'];

    $personasAgrupadas[$id] = [
        'nombre' => $r['nombre'],
        'rut' => $r['rut'],
        'historial' => [] // vacío por ahora
    ];
}
?>

<!-- 🔍 BUSCADOR -->
<div class="card mb-3">
  <div class="card-body d-flex flex-wrap gap-2">

    <form method="GET" class="d-flex gap-2">

      <div class="input-group input-group-sm">
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
<i>Sin historial (aún no conectado)</i>
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