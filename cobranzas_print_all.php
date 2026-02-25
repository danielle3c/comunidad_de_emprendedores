<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Cobranzas';
$pdo = getConnection();

$stmt = $pdo->query("SELECT idcobranzas, creditos_idcreditos, monto, tipo_pago, fecha_hora FROM cobranzas ORDER BY idcobranzas DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Cobranzas</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Crédito</th><th>Monto</th><th>Tipo pago</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idcobranzas'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['creditos_idcreditos'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['monto'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['tipo_pago'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_hora'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
        
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
