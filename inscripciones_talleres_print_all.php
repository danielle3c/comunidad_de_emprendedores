<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Inscripciones a Talleres';
$pdo = getConnection();

$stmt = $pdo->query("SELECT idinscripcion, emprendedores_idemprendedores, talleres_idtalleres, fecha_inscripcion, asistio FROM inscripciones_talleres ORDER BY idinscripcion DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Inscripciones a Talleres</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Taller</th><th>Fecha</th><th>Asistió</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idinscripcion'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['emprendedores_idemprendedores'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['talleres_idtalleres'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_inscripcion'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['asistio'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
        
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
