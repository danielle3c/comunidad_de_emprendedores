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

<!-- 🔍 BUSCADOR PRO -->
<div class="card mb-3 shadow-sm">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">

    <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">

      <div class="input-group input-group-sm search-box">
        <span class="input-group-text">🔍</span>
        <input 
          type="text" 
          name="search" 
          class="form-control" 
          placeholder="Buscar por nombre, apellido o RUT..."
          value="<?= htmlspecialchars($search ?? '') ?>"
        >
      </div>

      <button class="btn btn-sm btn-success">
        Buscar
      </button>

      <?php if (!empty($search)): ?>
        <a href="personas.php" class="btn btn-sm btn-outline-light">
          Limpiar
        </a>
      <?php endif; ?>

    </form>

  </div>
</div>

<!-- 📋 TABLA PRO -->
<div class="card shadow-sm">
<div class="table-responsive">

<table class="table table-hover align-middle mb-0">
<thead class="table-dark">
<tr>
    <th>👤 Nombre</th>
    <th>🪪 RUT</th>
    <th>📋 Acciones</th>
</tr>
</thead>
<tbody>

<?php foreach ($rows as $r): ?>
<tr>
    <td>
        <div class="fw-semibold"><?= htmlspecialchars($r['nombre']) ?></div>
    </td>

    <td><?= htmlspecialchars($r['rut']) ?></td>

    <td>
        <button class="btn btn-sm btn-outline-success" onclick="toggleHistorial(<?= $r['idpersonas'] ?>)">
            👁 Ver historial
        </button>
    </td>
</tr>

<tr id="historial-<?= $r['idpersonas'] ?>" class="historial-row">
<td colspan="3">
<div class="historial-box">
    <i>Sin historial aún</i>
</div>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
</div>

<!-- 🎨 ESTILOS -->
<style>
.search-box {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--border);
}

.historial-row {
  display: none;
  background: rgba(67, 176, 42, 0.05);
}

.historial-box {
  padding: 10px;
  border-left: 3px solid var(--brand-pick);
  background: rgba(67, 176, 42, 0.08);
  border-radius: 8px;
}

.btn-success {
  background: var(--brand-pick);
  border: none;
}

.btn-success:hover {
  filter: brightness(1.1);
}
</style>

<!-- ⚡ SCRIPT -->
<script>
function toggleHistorial(id){
  const fila = document.getElementById("historial-"+id);

  if (fila.style.display === "none" || fila.style.display === "") {
    fila.style.display = "table-row";
  } else {
    fila.style.display = "none";
  }
}
</script>