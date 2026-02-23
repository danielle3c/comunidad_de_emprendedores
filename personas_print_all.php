<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Listado completo de Personas (PDF)';
$pdo = getConnection();

// Traer TODAS las personas activas (sin paginación)
$stmt = $pdo->query("
  SELECT idpersonas, rut, nombres, apellidos, telefono, email, direccion
  FROM personas
  WHERE estado = 1
  ORDER BY apellidos, nombres
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Listado completo de Personas</h4>
      <small class="text-muted">Impresión / PDF (todas las personas)</small>
    </div>

    <?php include __DIR__ . '/includes/print_button.php'; ?>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
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
            <td><?= htmlspecialchars($p['rut'] ?? '') ?></td>
            <td><?= htmlspecialchars(($p['nombres'] ?? '') . ' ' . ($p['apellidos'] ?? '')) ?></td>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';