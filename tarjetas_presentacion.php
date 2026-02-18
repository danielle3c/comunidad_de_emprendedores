<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Tarjetas de Presentación';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('tarjetas_presentacion.php');
    }

    $data = [
        'nombre'          => sanitize(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING) ?? ''),
        'cantidad'        => filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT) ?: 0,
        'valor_monetario' => (float)str_replace(',', '.', filter_input(INPUT_POST, 'valor_monetario', FILTER_SANITIZE_STRING) ?? 0),
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sql = "UPDATE tarjetas_presentacion
                    SET nombre = :nombre, cantidad = :cantidad, valor_monetario = :valor_monetario
                    WHERE idtarjeta = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Registro actualizado correctamente.');
        } else {
            $sql = "INSERT INTO tarjetas_presentacion (nombre, cantidad, valor_monetario)
                    VALUES (:nombre, :cantidad, :valor_monetario)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Registro creado correctamente.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
        error_log("Error en tarjetas_presentacion.php: " . $e->getMessage());
    }
    redirect('tarjetas_presentacion.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM tarjetas_presentacion WHERE idtarjeta = ?")->execute([$id]);
        setFlash('success', 'Registro eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en tarjetas_presentacion.php (delete): " . $e->getMessage());
    }
    redirect('tarjetas_presentacion.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM tarjetas_presentacion WHERE idtarjeta = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Registro no encontrado.');
        redirect('tarjetas_presentacion.php');
    }
}

$search  = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$page    = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$where = "";
if ($search) {
    $where = "WHERE nombre LIKE :search";
    $params[':search'] = "%$search%";
}

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM tarjetas_presentacion $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT * FROM tarjetas_presentacion $where ORDER BY idtarjeta DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card card-soft mb-4">
  <div class="card-header bg-white border-0 fw-semibold">
    <?= $edit ? 'Editar Tarjeta de Presentación' : 'Nueva Tarjeta de Presentación' ?>
  </div>

  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idtarjeta'] ?? '') ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control form-control-sm" required
                 value="<?= htmlspecialchars($edit['nombre'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Cantidad</label>
          <input type="number" name="cantidad" class="form-control form-control-sm" required min="1"
                 value="<?= (int)($edit['cantidad'] ?? 1) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Valor Monetario</label>
          <input type="number" step="0.01" name="valor_monetario" class="form-control form-control-sm" required min="0"
                 value="<?= (float)($edit['valor_monetario'] ?? 0) ?>">
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-save"></i> Guardar
        </button>
        <a href="tarjetas_presentacion.php" class="btn btn-secondary btn-sm">Volver</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card card-soft">
  <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
    <span class="fw-semibold">
      Listado <span class="badge bg-secondary"><?= $total ?></span>
    </span>

    <div class="d-flex gap-2">
      <form class="d-flex gap-2" method="GET">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Buscar por nombre..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-sm btn-outline-secondary">Buscar</button>
      </form>

      <a href="tarjetas_presentacion.php?action=create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus"></i> Nuevo
      </a>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Cantidad</th>
            <th>Valor</th>
            <th>Fecha</th>
            <th style="width:120px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int)$r['idtarjeta'] ?></td>
              <td><?= htmlspecialchars($r['nombre']) ?></td>
              <td><?= (int)$r['cantidad'] ?></td>
              <td><?= formatMoney($r['valor_monetario']) ?></td>
              <td><?= formatDateTime($r['created_at'] ?? null) ?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary"
                   href="tarjetas_presentacion.php?action=edit&id=<?= (int)$r['idtarjeta'] ?>">
                  <i class="bi bi-pencil"></i>
                </a>
                <a class="btn btn-sm btn-outline-danger"
                   href="tarjetas_presentacion.php?action=delete&id=<?= (int)$r['idtarjeta'] ?>"
                   onclick="return confirm('¿Eliminar este registro?');">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (!$rows): ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Sin registros</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0">
      <?= renderPagination($pag, 'tarjetas_presentacion.php?search=' . urlencode($search)) ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>