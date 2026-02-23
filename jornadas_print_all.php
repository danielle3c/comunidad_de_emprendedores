<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Jornadas';
$pdo = getConnection();

$stmt = $pdo->query("SELECT idjornadas, nombre_jornada, fecha_jornada, hora_inicio, hora_fin, lugar, estado FROM jornadas ORDER BY fecha_jornada DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Jornadas</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Jornada</th><th>Fecha</th><th>Inicio</th><th>Fin</th><th>Lugar</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idjornadas'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['nombre_jornada'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_jornada'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['hora_inicio'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['hora_fin'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['lugar'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['estado'] ?? '')) ?></td></tr>
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
