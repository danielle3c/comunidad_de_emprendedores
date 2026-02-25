<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Imprimir TODO - Documentos';
$pdo = getConnection();

$stmt = $pdo->query("SELECT iddocumentos, emprendedores_idemprendedores, nombre_documento, tipo_documento, fecha_subida FROM documentos ORDER BY iddocumentos DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Documentos</h4>
      <small class="text-muted">Total: <?= count($rows) ?></small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="bi bi-printer"></i> PDF / Imprimir
    </button>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Documento</th><th>Tipo</th><th>Fecha subida</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= htmlspecialchars((string)($r['iddocumentos'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['emprendedores_idemprendedores'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['nombre_documento'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['tipo_documento'] ?? '')) ?></td><td><?= htmlspecialchars((string)($r['fecha_subida'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
        
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
