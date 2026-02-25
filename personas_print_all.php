<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Listado completo de Personas (PDF)';
$pdo = getConnection();

$search = sanitize($_GET['search'] ?? '');
$where  = $search ? "WHERE nombres LIKE :s OR apellidos LIKE :s OR rut LIKE :s OR email LIKE :s" : "";
$params = $search ? [':s' => "%$search%"] : [];

$stmt = $pdo->prepare("
  SELECT idpersonas, rut, nombres, apellidos, telefono, email, genero, estado, direccion
  FROM personas
  $where
  ORDER BY nombres ASC
");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Listado completo de Personas</h4>
      <small class="text-muted">
        Total: <?= count($rows) ?><?= $search ? ' | Filtro: '.htmlspecialchars($search) : '' ?>
      </small>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="bi bi-printer"></i> PDF / Imprimir
      </button>
      <a href="personas.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead>
          <tr>
            <th>ID</th><th>RUT</th><th>Nombres</th><th>Apellidos</th>
            <th>Teléfono</th><th>Email</th><th>Género</th><th>Estado</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['idpersonas'] ?></td>
            <td><?= htmlspecialchars($r['rut'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['nombres'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['apellidos'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['telefono'] ?: '-') ?></td>
            <td><?= htmlspecialchars($r['email'] ?: '-') ?></td>
            <td><?= htmlspecialchars($r['genero'] ?? '') ?></td>
            <td><?= badgeEstado((string)($r['estado'] ?? 0)) ?></td>
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
