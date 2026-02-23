<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Emprendedores';
$pdo = getConnection();

$stmt = $pdo->query("SELECT e.idemprendedores, p.rut, p.nombres, p.apellidos, e.rubro, e.tipo_negocio, e.estado FROM emprendedores e JOIN personas p ON p.idpersonas=e.personas_idpersonas ORDER BY e.idemprendedores DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Emprendedores</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>RUT</th><th>Nombres</th><th>Apellidos</th><th>Rubro</th><th>Tipo negocio</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idemprendedores'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['rut'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['nombres'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['apellidos'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['rubro'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['tipo_negocio'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['estado'] ?? '')) ?></td></tr>
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
