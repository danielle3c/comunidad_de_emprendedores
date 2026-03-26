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

<!-- 🔍 BUSCADOR ULTRA BONITO -->
<div class="card buscador-card mb-4">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center w-100">

      <div class="search-wrapper">
        <span class="search-icon">🔍</span>
        <input 
          type="text" 
          name="search" 
          class="search-input" 
          placeholder="Buscar personas..."
          value="<?= htmlspecialchars($search ?? '') ?>"
        >
      </div>

      <button class="btn btn-pro">
        Buscar
      </button>

      <?php if (!empty($search)): ?>
        <a href="personas.php" class="btn btn-clear">
          Limpiar
        </a>
      <?php endif; ?>

    </form>

  </div>
</div>

<!-- 📋 TABLA MODERNA -->
<div class="card tabla-card">
<div class="table-responsive">

<table class="table table-modern mb-0">
<thead>
<tr>
    <th>👤 Persona</th>
    <th>🪪 RUT</th>
    <th class="text-center">Acción</th>
</tr>
</thead>
<tbody>

<?php foreach ($rows as $r): ?>
<tr class="fila-hover">
    <td>
        <div class="persona-info">
            <div class="avatar">👤</div>
            <div>
                <div class="nombre"><?= htmlspecialchars($r['nombre']) ?></div>
                <small class="text-muted">Usuario registrado</small>
            </div>
        </div>
    </td>

    <td class="fw-semibold"><?= htmlspecialchars($r['rut']) ?></td>

    <td class="text-center">
        <button class="btn btn-action" onclick="toggleHistorial(<?= $r['idpersonas'] ?>)">
            Ver historial
        </button>
    </td>
</tr>

<tr id="historial-<?= $r['idpersonas'] ?>" class="historial-row">
<td colspan="3">
<div class="historial-box">
    <div class="historial-title">📋 Historial</div>
    <div class="historial-content">
        <i>Sin registros aún</i>
    </div>
</div>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
</div>

<!-- 🎨 ESTILO PRO -->
<style>

/* tarjeta buscador */
.buscador-card {
  border-radius: 16px;
  background: var(--surface);
  border: 1px solid var(--border);
}

/* buscador */
.search-wrapper {
  position: relative;
  width: 100%;
  max-width: 350px;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 35px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--input-bg);
  color: var(--text);
  transition: all .2s;
}

.search-input:focus {
  border-color: var(--brand-pick);
  box-shadow: 0 0 0 2px var(--brand-dim);
}

.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
}

/* botones */
.btn-pro {
  background: var(--brand-pick);
  color: #fff;
  border-radius: 10px;
  padding: 6px 14px;
  border: none;
}

.btn-pro:hover {
  transform: translateY(-1px);
}

.btn-clear {
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 6px 12px;
}

/* tabla */
.table-modern {
  border-collapse: separate;
  border-spacing: 0;
}

.table-modern thead {
  background: var(--surface2);
}

.table-modern th {
  font-size: .8rem;
  text-transform: uppercase;
  letter-spacing: .05em;
}

.fila-hover:hover {
  background: var(--table-row-hover);
}

/* persona */
.persona-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 35px;
  height: 35px;
  background: var(--brand-dim);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* botón acción */
.btn-action {
  border: 1px solid var(--brand-pick);
  color: var(--brand-pick);
  border-radius: 8px;
  padding: 5px 10px;
  background: transparent;
}

.btn-action:hover {
  background: var(--brand-pick);
  color: #fff;
}

/* historial */
.historial-row {
  display: none;
}

.historial-box {
  background: var(--surface2);
  padding: 12px;
  border-radius: 10px;
  border-left: 3px solid var(--brand-pick);
}

.historial-title {
  font-weight: bold;
  margin-bottom: 5px;
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