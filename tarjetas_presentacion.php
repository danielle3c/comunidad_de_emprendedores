<?php
require_once __DIR__ . '/includes/helpers.php';
$pageTitle = 'Tarjetas de Presentación';

$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// --- GUARDAR (CREAR/EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'nombre'          => sanitize($_POST['nombre'] ?? ''),
        'cantidad'        => (int)($_POST['cantidad'] ?? 0),
        'valor_monetario' => (float)($_POST['valor_monetario'] ?? 0),
    ];

    try {
        if (!empty($_POST['id'])) {
            $sql = "UPDATE tarjetas_presentacion
                    SET nombre=:nombre, cantidad=:cantidad, valor_monetario=:valor_monetario
                    WHERE idtarjeta=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':cantidad' => $data['cantidad'],
                ':valor_monetario' => $data['valor_monetario'],
                ':id' => (int)$_POST['id']
            ]);
            setFlash('success', 'Registro actualizado correctamente.');
        } else {
            $sql = "INSERT INTO tarjetas_presentacion (nombre, cantidad, valor_monetario)
                    VALUES (:nombre, :cantidad, :valor_monetario)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success', 'Registro creado correctamente.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }

    redirect('tarjetas_presentacion.php');
}

// --- ELIMINAR ---
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM tarjetas_presentacion WHERE idtarjeta=?")->execute([$id]);
        setFlash('success', 'Registro eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
    }
    redirect('tarjetas_presentacion.php');
}

// --- EDITAR: cargar datos ---
$edit = null;
if ($action === 'edit' && $id) {
    $st = $pdo->prepare("SELECT * FROM tarjetas_presentacion WHERE idtarjeta=?");
    $st->execute([$id]);
    $edit = $st->fetch();
    if (!$edit) {
        setFlash('error', 'Registro no encontrado.');
        redirect('tarjetas_presentacion.php');
    }
}

// --- CREAR: formulario vacío ---
if ($action === 'create') {
    $edit = null;
}

// --- LISTAR ---
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where  = $search ? "WHERE nombre LIKE :s" : "";
$params = $search ? [':s' => "%$search%"] : [];

$totalSt = $pdo->prepare("SELECT COUNT(*) FROM tarjetas_presentacion $where");
$totalSt->execute($params);
$total = (int)$totalSt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$stmt = $pdo->prepare("SELECT * FROM tarjetas_presentacion
                       $where
                       ORDER BY idtarjeta DESC
                       LIMIT :limit OFFSET :offset");
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
      <input type="hidden" name="id" value="<?= $edit['idtarjeta'] ?? '' ?>">

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
  
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 dt-export" data-title="Listado de Tarjetas de Presentación">
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
