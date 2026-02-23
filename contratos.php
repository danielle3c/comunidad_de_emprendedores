<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Contratos';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'emprendedores_idemprendedores' => (int)$_POST['emprendedores_idemprendedores'],
        'fecha_inicio'   => $_POST['fecha_inicio'],
        'fecha_termino'  => $_POST['fecha_termino'] ?: null,
        'monto_total'    => (float)str_replace(',','.',$_POST['monto_total'] ?? 0),
        'descripcion'    => sanitize($_POST['descripcion'] ?? ''),
        'tipo_contrato'  => sanitize($_POST['tipo_contrato'] ?? ''),
        'estado'         => $_POST['estado'] ?? 'Activo',
    ];
    try {
        if ($_POST['id']) {
            $sql = "UPDATE Contratos SET emprendedores_idemprendedores=:emprendedores_idemprendedores,
                    fecha_inicio=:fecha_inicio,fecha_termino=:fecha_termino,monto_total=:monto_total,
                    descripcion=:descripcion,tipo_contrato=:tipo_contrato,estado=:estado WHERE idContratos=:id";
            $pdo->prepare($sql)->execute(array_merge($data, [':id' => (int)$_POST['id']]));
            setFlash('success', 'Contrato actualizado.');
        } else {
            $sql = "INSERT INTO Contratos (emprendedores_idemprendedores,fecha_inicio,fecha_termino,monto_total,descripcion,tipo_contrato,estado)
                    VALUES (:emprendedores_idemprendedores,:fecha_inicio,:fecha_termino,:monto_total,:descripcion,:tipo_contrato,:estado)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success', 'Contrato creado.');
        }
    } catch (PDOException $e) { setFlash('error', 'Error: ' . $e->getMessage()); }
    redirect('contratos.php');
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM Contratos WHERE idContratos=?")->execute([$id]); setFlash('success', 'Contrato eliminado.'); }
    catch (PDOException $e) { setFlash('error', 'No se puede eliminar: ' . $e->getMessage()); }
    redirect('contratos.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM Contratos WHERE idContratos=?"); $s->execute([$id]); $edit = $s->fetch();
}

$emprendedores = $pdo->query("SELECT e.idemprendedores, CONCAT(p.nombres,' ',p.apellidos,' - ',p.rut) AS label
    FROM emprendedores e JOIN personas p ON e.personas_idpersonas=p.idpersonas WHERE e.estado=1 ORDER BY p.nombres")->fetchAll();

$search  = sanitize($_GET['search'] ?? '');
$filtroEstado = sanitize($_GET['estado'] ?? '');
$page    = max(1,(int)($_GET['page'] ?? 1));
$perPage = 15;
$conditions = [];
$params = [];
if ($search) { $conditions[] = "(p.nombres LIKE :s OR p.apellidos LIKE :s OR c.tipo_contrato LIKE :s)"; $params[':s'] = "%$search%"; }
if ($filtroEstado) { $conditions[] = "c.estado=:estado"; $params[':estado'] = $filtroEstado; }
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';
$base = "FROM Contratos c JOIN emprendedores e ON c.emprendedores_idemprendedores=e.idemprendedores JOIN personas p ON e.personas_idpersonas=p.idpersonas $where";
$tc = $pdo->prepare("SELECT COUNT(*) $base"); $tc->execute($params); $total = (int)$tc->fetchColumn();
$pag = getPaginationData($total, $page, $perPage);
$stmt = $pdo->prepare("SELECT c.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona $base ORDER BY c.fecha_inicio DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute(); $rows = $stmt->fetchAll();

include 'includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Contrato' : 'Nuevo Contrato' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idContratos'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Emprendedor *</label>
                <select name="emprendedores_idemprendedores" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($emprendedores as $e): ?>
                    <option value="<?= $e['idemprendedores'] ?>" <?= ($edit['emprendedores_idemprendedores'] ?? '') == $e['idemprendedores'] ? 'selected' : '' ?>><?= sanitize($e['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Inicio *</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?= $edit['fecha_inicio'] ?? date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Término</label>
                <input type="date" name="fecha_termino" class="form-control form-control-sm" value="<?= $edit['fecha_termino'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Monto Total *</label>
                <input type="number" step="0.01" name="monto_total" class="form-control form-control-sm" value="<?= $edit['monto_total'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <?php foreach (['Activo','Finalizado','Cancelado'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($edit['estado'] ?? 'Activo') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de Contrato</label>
                <input type="text" name="tipo_contrato" class="form-control form-control-sm" value="<?= $edit['tipo_contrato'] ?? '' ?>">
            </div>
            <div class="col-md-9">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="2"><?= $edit['descripcion'] ?? '' ?></textarea>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="contratos.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Contratos <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <select name="estado" class="form-select form-select-sm" style="width:130px">
                    <option value="">Todos</option>
                    <?php foreach (['Activo','Finalizado','Cancelado'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $filtroEstado === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="contratos.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Tipo</th><th>Inicio</th><th>Término</th><th>Monto</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['idContratos'] ?></td>
            <td><?= sanitize($r['nombre_persona']) ?></td>
            <td><?= sanitize($r['tipo_contrato']) ?: '-' ?></td>
            <td><?= formatDate($r['fecha_inicio']) ?></td>
            <td><?= formatDate($r['fecha_termino']) ?></td>
            <td><?= formatMoney($r['monto_total']) ?></td>
            <td><?= badgeEstado($r['estado']) ?></td>
            <td>
                <a href="contratos.php?action=edit&id=<?= $r['idContratos'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="contratos.php?action=delete&id=<?= $r['idContratos'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'contratos.php?search='.urlencode($search).'&estado='.urlencode($filtroEstado)) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
