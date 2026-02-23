<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Encuestas';
$pdo = getConnection();

$stmt = $pdo->query("SELECT idencuesta, emprendedores_idemprendedores, fecha_respuesta FROM encuesta_2026 ORDER BY idencuesta DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Encuestas</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idencuesta'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['emprendedores_idemprendedores'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_respuesta'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="3" class="text-center text-muted py-4">Sin registros</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
