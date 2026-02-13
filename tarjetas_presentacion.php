<?php
require_once 'includes/helpers.php';
$pageTitle = 'Tarjetas de Presentación';
$pdo = getConnection();

/* GUARDAR */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = sanitize($_POST['nombre'] ?? '');
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $valor    = (float)($_POST['valor'] ?? 0);

    $sql = "INSERT INTO tarjetas_presentacion (nombre, cantidad, valor)
            VALUES (:nombre, :cantidad, :valor)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':cantidad' => $cantidad,
        ':valor' => $valor
    ]);

    setFlash('success', 'Tarjeta registrada correctamente.');
    redirect('tarjetas_presentacion.php');
}

/* LISTAR */
$tarjetas = $pdo->query("SELECT * FROM tarjetas_presentacion ORDER BY idtarjeta DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="card card-soft p-4 mb-4">
  <h5 class="mb-3">Nueva Tarjeta</h5>

  <form method="POST">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Cantidad</label>
        <input type="number" name="cantidad" class="form-control" required min="1">
      </div>

      <div class="col-md-4">
        <label class="form-label">Valor</label>
        <input type="number" name="valor" step="0.01" class="form-control" required min="0">
      </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">
      <i class="bi bi-save"></i> Guardar
    </button>
  </form>
</div>


<div class="card card-soft p-4">
  <h5 class="mb-3">Listado de Tarjetas</h5>

  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Cantidad</th>
          <th>Valor</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tarjetas as $t): ?>
          <tr>
            <td><?= $t['idtarjeta'] ?></td>
            <td><?= htmlspecialchars($t['nombre']) ?></td>
            <td><?= $t['cantidad'] ?></td>
            <td><?= formatMoney($t['valor']) ?></td>
            <td><?= formatDateTime($t['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
