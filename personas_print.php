<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

secure_session_start();
require_once __DIR__ . '/auth_guard.php';

$pdo = getConnection();
$rows = $pdo->query("
  SELECT idpersonas, rut, nombres, apellidos, telefono, email, direccion
  FROM personas
  WHERE estado = 1
  ORDER BY apellidos, nombres
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>

<div class="content-area">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Listado de Personas</h5>
        <small class="text-muted">Para imprimir o guardar como PDF</small>
      </div>
      <button class="btn btn-primary" onclick="window.print()">Imprimir / PDF</button>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>RUT</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Dirección</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $p): ?>
            <tr>
              <td><?= (int)$p['idpersonas'] ?></td>
              <td><?= htmlspecialchars($p['rut']) ?></td>
              <td><?= htmlspecialchars($p['nombres'].' '.$p['apellidos']) ?></td>
              <td><?= htmlspecialchars($p['telefono'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['direccion'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>