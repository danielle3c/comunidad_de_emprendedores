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
        WHERE p.nombres LIKE ?
           OR p.apellidos LIKE ?
           OR p.rut LIKE ?
        ORDER BY p.nombres ASC
    ");

    $stmt->execute([
        "%$search%",
        "%$search%",
        "%$search%"
    ]);

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

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<?php foreach ($rows as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['nombre']) ?></td>
    <td><?= htmlspecialchars($r['rut']) ?></td>
    <td>
        <button class="btn btn-sm btn-outline-primary" onclick="toggleHistorial(<?= $r['idpersonas'] ?>)">
            Ver historial
        </button>
    </td>
</tr>

<tr id="historial-<?= $r['idpersonas'] ?>" style="display:none;">
<td colspan="3">
<i>Sin historial aún</i>
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