<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Créditos';
$pdo = getConnection();

$stmt = $pdo->query("SELECT idcreditos, emprendedores_idemprendedores, monto_inicial, saldo_inicial, fecha_inicio, cuota_mensual, estado FROM creditos ORDER BY idcreditos DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Créditos</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Monto</th><th>Saldo</th><th>Inicio</th><th>Cuota</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idcreditos'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['emprendedores_idemprendedores'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['monto_inicial'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['saldo_inicial'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_inicio'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['cuota_mensual'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['estado'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Sin registros</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
