<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Auditoría';
$pdo = getConnection();

$stmt = $pdo->query("SELECT idauditoria, usuario_id, accion, tabla, registro_id, ip_address, fecha_hora FROM auditoria ORDER BY idauditoria DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Auditoría</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>Registro</th><th>IP</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idauditoria'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['usuario_id'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['accion'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['tabla'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['registro_id'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['ip_address'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_hora'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
        
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
