<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Cobranzas';
$pdo = getConnection();

$action    = $_GET['action'] ?? 'list';
$id        = (int)($_GET['id'] ?? 0);
$creditoId = (int)($_GET['credito_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'creditos_idcreditos' => (int)$_POST['creditos_idcreditos'],
        'monto'               => (float)str_replace(',','.',$_POST['monto']),
        'tipo_pago'           => $_POST['tipo_pago'] ?? 'Efectivo',
        'fecha_hora'          => $_POST['fecha_hora'],
        'observaciones'       => sanitize($_POST['observaciones'] ?? ''),
        'usuario_registro'    => sanitize($_POST['usuario_registro'] ?? ''),
    ];
    try {
        if ($_POST['id']) {
            $sql = "UPDATE cobranzas SET creditos_idcreditos=:creditos_idcreditos,monto=:monto,
                    tipo_pago=:tipo_pago,fecha_hora=:fecha_hora,observaciones=:observaciones,
                    usuario_registro=:usuario_registro WHERE idcobranzas=:id";
            $pdo->prepare($sql)->execute(array_merge($data,[':id'=>(int)$_POST['id']]));
            setFlash('success','Cobranza actualizada.');
        } else {
            $sql = "INSERT INTO cobranzas (creditos_idcreditos,monto,tipo_pago,fecha_hora,observaciones,usuario_registro)
                    VALUES (:creditos_idcreditos,:monto,:tipo_pago,:fecha_hora,:observaciones,:usuario_registro)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success','Pago registrado.');
        }
    } catch (PDOException $e) { setFlash('error','Error: '.$e->getMessage()); }
    redirect('cobranzas.php' . ($creditoId ? "?credito_id=$creditoId" : ''));
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM cobranzas WHERE idcobranzas=?")->execute([$id]); setFlash('success','Cobranza eliminada.'); }
    catch (PDOException $e) { setFlash('error','No se puede eliminar: '.$e->getMessage()); }
    redirect('cobranzas.php' . ($creditoId ? "?credito_id=$creditoId" : ''));
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM cobranzas WHERE idcobranzas=?"); $s->execute([$id]); $edit = $s->fetch();
}

$creditos = $pdo->query("SELECT cr.idcreditos, CONCAT('#',cr.idcreditos,' - ',p.nombres,' ',p.apellidos) AS label
    FROM creditos cr JOIN emprendedores e ON cr.emprendedores_idemprendedores=e.idemprendedores
    JOIN personas p ON e.personas_idpersonas=p.idpersonas WHERE cr.estado='Activo' ORDER BY p.nombres")->fetchAll();

// Filtros
$search = sanitize($_GET['search'] ?? '');
$filtroTipo = sanitize($_GET['tipo_pago'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1)); $perPage = 15;
$conditions = []; $params = [];
if ($creditoId) { $conditions[] = "c.creditos_idcreditos=:cid"; $params[':cid'] = $creditoId; }
if ($search) { $conditions[] = "(p.nombres LIKE :s OR p.apellidos LIKE :s OR c.observaciones LIKE :s)"; $params[':s'] = "%$search%"; }
if ($filtroTipo) { $conditions[] = "c.tipo_pago=:tp"; $params[':tp'] = $filtroTipo; }
$where = $conditions ? "WHERE ".implode(' AND ',$conditions) : '';
$base = "FROM cobranzas c JOIN creditos cr ON c.creditos_idcreditos=cr.idcreditos JOIN emprendedores e ON cr.emprendedores_idemprendedores=e.idemprendedores JOIN personas p ON e.personas_idpersonas=p.idpersonas $where";
$tc = $pdo->prepare("SELECT COUNT(*) $base"); $tc->execute($params); $total = (int)$tc->fetchColumn();
$stmtTotal = $pdo->prepare("SELECT COALESCE(SUM(c.monto),0) $base"); $stmtTotal->execute($params); $totalMonto = (float)$stmtTotal->fetchColumn();
$pag = getPaginationData($total,$page,$perPage);
$stmt = $pdo->prepare("SELECT c.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona $base ORDER BY c.fecha_hora DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$pag['perPage'],PDO::PARAM_INT);
$stmt->bindValue(':offset',$pag['offset'],PDO::PARAM_INT);
$stmt->execute(); $rows = $stmt->fetchAll();

// Info del crédito si se filtra por uno
$creditoInfo = null;
if ($creditoId) {
    $s = $pdo->prepare("SELECT cr.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona FROM creditos cr JOIN emprendedores e ON cr.emprendedores_idemprendedores=e.idemprendedores JOIN personas p ON e.personas_idpersonas=p.idpersonas WHERE cr.idcreditos=?");
    $s->execute([$creditoId]); $creditoInfo = $s->fetch();
}

include 'includes/header.php';
?>

<?php if ($creditoInfo): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center py-2">
    <span><strong>Crédito #<?= $creditoId ?></strong> — <?= sanitize($creditoInfo['nombre_persona']) ?> | Monto: <?= formatMoney($creditoInfo['monto_inicial']) ?> | Cuota: <?= formatMoney($creditoInfo['cuota_mensual']) ?> | <?= badgeEstado($creditoInfo['estado']) ?></span>
    <a href="cobranzas.php" class="btn btn-sm btn-outline-secondary">Ver todos</a>
</div>
<?php endif; ?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Cobranza' : 'Registrar Pago' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idcobranzas'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Crédito *</label>
                <select name="creditos_idcreditos" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($creditos as $cr): ?>
                    <option value="<?= $cr['idcreditos'] ?>" <?= ($edit['creditos_idcreditos'] ?? $creditoId) == $cr['idcreditos'] ? 'selected' : '' ?>><?= sanitize($cr['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Monto *</label>
                <input type="number" step="0.01" name="monto" class="form-control form-control-sm" value="<?= $edit['monto'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo de Pago</label>
                <select name="tipo_pago" class="form-select form-select-sm">
                    <?php foreach (['Efectivo','Transferencia','Cheque','Tarjeta','Otro'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($edit['tipo_pago'] ?? 'Efectivo') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha y Hora *</label>
                <input type="datetime-local" name="fecha_hora" class="form-control form-control-sm" value="<?= isset($edit['fecha_hora']) ? date('Y-m-d\TH:i', strtotime($edit['fecha_hora'])) : date('Y-m-d\TH:i') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Usuario Registra</label>
                <input type="text" name="usuario_registro" class="form-control form-control-sm" value="<?= $edit['usuario_registro'] ?? '' ?>">
            </div>
            <div class="col-md-9">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control form-control-sm" rows="2"><?= $edit['observaciones'] ?? '' ?></textarea>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="cobranzas.php<?= $creditoId ? "?credito_id=$creditoId" : '' ?>" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Cobranzas <span class="badge bg-secondary"><?= $total ?></span> <small class="text-success ms-2"><?= formatMoney($totalMonto) ?></small></span>
        <div class="d-flex gap-2 flex-wrap">
            <form class="d-flex gap-2" method="GET">
                <?php if ($creditoId): ?><input type="hidden" name="credito_id" value="<?= $creditoId ?>"><?php endif; ?>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <select name="tipo_pago" class="form-select form-select-sm" style="width:130px">
                    <option value="">Todos</option>
                    <?php foreach (['Efectivo','Transferencia','Cheque','Tarjeta','Otro'] as $t): ?>
                    <option value="<?= $t ?>" <?= $filtroTipo===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="cobranzas.php?action=create<?= $creditoId ? "&credito_id=$creditoId" : '' ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Crédito</th><th>Monto</th><th>Tipo Pago</th><th>Fecha</th><th>Observaciones</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['idcobranzas'] ?></td>
            <td><?= sanitize($r['nombre_persona']) ?></td>
            <td><a href="creditos.php?action=edit&id=<?= $r['creditos_idcreditos'] ?>">#<?= $r['creditos_idcreditos'] ?></a></td>
            <td class="fw-semibold"><?= formatMoney($r['monto']) ?></td>
            <td><span class="badge bg-light text-dark"><?= $r['tipo_pago'] ?></span></td>
            <td><?= formatDateTime($r['fecha_hora']) ?></td>
            <td><?= mb_strimwidth(sanitize($r['observaciones'] ?? ''), 0, 40, '...') ?></td>
            <td>
                <a href="cobranzas.php?action=edit&id=<?= $r['idcobranzas'] ?><?= $creditoId ? "&credito_id=$creditoId" : '' ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="cobranzas.php?action=delete&id=<?= $r['idcobranzas'] ?><?= $creditoId ? "&credito_id=$creditoId" : '' ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag,'cobranzas.php?search='.urlencode($search).'&tipo_pago='.urlencode($filtroTipo).($creditoId?"&credito_id=$creditoId":'')) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
