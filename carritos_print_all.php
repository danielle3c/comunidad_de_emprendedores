<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Carritos';
$pdo = getConnection();

$stmt = $pdo->query("SELECT idcarritos, nombre_carrito, nombre_responsable, telefono_responsable, asistencia, fecha_registro, estado FROM carritos ORDER BY idcarritos DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Carritos</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Carrito</th><th>Responsable</th><th>Teléfono</th><th>Asistencia</th><th>Registro</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['idcarritos'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['nombre_carrito'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['nombre_responsable'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['telefono_responsable'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['asistencia'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_registro'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['estado'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
        
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
