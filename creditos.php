<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Créditos';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('creditos.php');
    }

    $data = [
        'emprendedores_idemprendedores' => filter_input(INPUT_POST, 'emprendedores_idemprendedores', FILTER_VALIDATE_INT) ?: 0,
        'Contratos_idContratos'         => filter_input(INPUT_POST, 'Contratos_idContratos', FILTER_VALIDATE_INT) ?: null,
        'monto_inicial'  => (float)str_replace(',', '.', filter_input(INPUT_POST, 'monto_inicial', FILTER_SANITIZE_STRING) ?? 0),
        'saldo_inicial'  => (float)str_replace(',', '.', filter_input(INPUT_POST, 'saldo_inicial', FILTER_SANITIZE_STRING) ?? 0),
        'fecha_inicio'   => filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING) ?? '',
        'dia_de_pago'    => filter_input(INPUT_POST, 'dia_de_pago', FILTER_VALIDATE_INT) ?: 1,
        'cuota_mensual'  => (float)str_replace(',', '.', filter_input(INPUT_POST, 'cuota_mensual', FILTER_SANITIZE_STRING) ?? 0),
        'estado'         => filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING) ?? 'Activo',
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sql = "UPDATE creditos SET 
                    emprendedores_idemprendedores = :emprendedores_idemprendedores,
                    Contratos_idContratos = :Contratos_idContratos,
                    monto_inicial = :monto_inicial,
                    saldo_inicial = :saldo_inicial,
                    fecha_inicio = :fecha_inicio,
                    dia_de_pago = :dia_de_pago,
                    cuota_mensual = :cuota_mensual,
                    estado = :estado
                    WHERE idcreditos = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Crédito actualizado.');
        } else {
            $sql = "INSERT INTO creditos 
                    (emprendedores_idemprendedores, Contratos_idContratos, monto_inicial, saldo_inicial, fecha_inicio, dia_de_pago, cuota_mensual, estado)
                    VALUES 
                    (:emprendedores_idemprendedores, :Contratos_idContratos, :monto_inicial, :saldo_inicial, :fecha_inicio, :dia_de_pago, :cuota_mensual, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Crédito registrado.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
        error_log("Error en creditos.php: " . $e->getMessage());
    }
    redirect('creditos.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM creditos WHERE idcreditos = ?")->execute([$id]);
        setFlash('success', 'Crédito eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en creditos.php (delete): " . $e->getMessage());
    }
    redirect('creditos.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM creditos WHERE idcreditos = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Crédito no encontrado.');
        redirect('creditos.php');
    }
}

$emprendedores = $pdo->query("SELECT e.idemprendedores, CONCAT(p.nombres,' ',p.apellidos,' - ',p.rut) AS label
    FROM emprendedores e 
    JOIN personas p ON e.personas_idpersonas = p.idpersonas 
    WHERE e.estado = 1 
    ORDER BY p.nombres")->fetchAll();

$contratos = $pdo->query("SELECT c.idContratos, CONCAT('#',c.idContratos,' - ',p.nombres,' ',p.apellidos) AS label
    FROM Contratos c 
    JOIN emprendedores e ON c.emprendedores_idemprendedores = e.idemprendedores 
    JOIN personas p ON e.personas_idpersonas = p.idpersonas
    WHERE c.estado = 'Activo' 
    ORDER BY c.fecha_inicio DESC")->fetchAll();

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$filtroEstado = sanitize(filter_input(INPUT_GET, 'estado', FILTER_SANITIZE_STRING) ?? '');
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$conditions = [];

if ($search) {
    $conditions[] = "(p.nombres LIKE :search OR p.apellidos LIKE :search OR p.rut LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($filtroEstado) {
    $conditions[] = "cr.estado = :estado";
    $params[':estado'] = $filtroEstado;
}
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$base = "FROM creditos cr 
         JOIN emprendedores e ON cr.emprendedores_idemprendedores = e.idemprendedores 
         JOIN personas p ON e.personas_idpersonas = p.idpersonas $where";

$totalStmt = $pdo->prepare("SELECT COUNT(*) $base");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT cr.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona $base ORDER BY cr.fecha_inicio DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Crédito' : 'Nuevo Crédito' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idcreditos'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Emprendedor *</label>
                <select name="emprendedores_idemprendedores" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($emprendedores as $e): ?>
                    <option value="<?= (int)$e['idemprendedores'] ?>" <?= ($edit['emprendedores_idemprendedores'] ?? '') == $e['idemprendedores'] ? 'selected' : '' ?>><?= htmlspecialchars($e['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contrato (opcional)</label>
                <select name="Contratos_idContratos" class="form-select form-select-sm">
                    <option value="">-- Ninguno --</option>
                    <?php foreach ($contratos as $c): ?>
                    <option value="<?= (int)$c['idContratos'] ?>" <?= ($edit['Contratos_idContratos'] ?? '') == $c['idContratos'] ? 'selected' : '' ?>><?= htmlspecialchars($c['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <?php foreach (['Activo', 'Pagado', 'Vencido', 'Cancelado'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($edit['estado'] ?? 'Activo') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Monto Inicial *</label>
                <input type="number" step="0.01" name="monto_inicial" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['monto_inicial'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Saldo Inicial *</label>
                <input type="number" step="0.01" name="saldo_inicial" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['saldo_inicial'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cuota Mensual</label>
                <input type="number" step="0.01" name="cuota_mensual" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['cuota_mensual'] ?? '0') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Inicio *</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['fecha_inicio'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Día de Pago</label>
                <input type="number" min="1" max="31" name="dia_de_pago" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['dia_de_pago'] ?? '1') ?>">
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="creditos.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Créditos <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <select name="estado" class="form-select form-select-sm" style="width:120px">
                    <option value="">Todos</option>
                    <?php foreach (['Activo', 'Pagado', 'Vencido', 'Cancelado'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $filtroEstado === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="creditos.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Monto Inicial</th><th>Saldo Inicial</th><th>Cuota</th><th>Inicio</th><th>Día Pago</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idcreditos'] ?></td>
            <td><?= htmlspecialchars($r['nombre_persona']) ?></td>
            <td><?= formatMoney($r['monto_inicial']) ?></td>
            <td><?= formatMoney($r['saldo_inicial']) ?></td>
            <td><?= formatMoney($r['cuota_mensual']) ?></td>
            <td><?= formatDate($r['fecha_inicio']) ?></td>
            <td><?= (int)$r['dia_de_pago'] ?></td>
            <td><?= badgeEstado($r['estado']) ?></td>
            <td>
                <a href="cobranzas.php?credito_id=<?= (int)$r['idcreditos'] ?>" class="btn btn-sm btn-outline-success btn-action" title="Ver Pagos"><i class="bi bi-cash"></i></a>
                <a href="creditos.php?action=edit&id=<?= (int)$r['idcreditos'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="creditos.php?action=delete&id=<?= (int)$r['idcreditos'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'creditos.php?search='.urlencode($search).'&estado='.urlencode($filtroEstado)) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>