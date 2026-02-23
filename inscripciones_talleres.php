<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Inscripciones en Talleres';
$pdo = getConnection();

$action   = $_GET['action'] ?? 'list';
$id       = (int)($_GET['id'] ?? 0);
$tallerId = (int)($_GET['taller_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'emprendedores_idemprendedores' => (int)$_POST['emprendedores_idemprendedores'],
        'talleres_idtalleres'            => (int)$_POST['talleres_idtalleres'],
        'asistio'                        => isset($_POST['asistio']) ? 1 : 0,
        'calificacion'                   => $_POST['calificacion'] ? (int)$_POST['calificacion'] : null,
        'comentarios'                    => sanitize($_POST['comentarios'] ?? ''),
    ];
    try {
        if ($_POST['id']) {
            $sql = "UPDATE inscripciones_talleres SET emprendedores_idemprendedores=:emprendedores_idemprendedores,
                    talleres_idtalleres=:talleres_idtalleres,asistio=:asistio,calificacion=:calificacion,
                    comentarios=:comentarios WHERE idinscripcion=:id";
            $pdo->prepare($sql)->execute(array_merge($data,[':id'=>(int)$_POST['id']]));
            setFlash('success','Inscripción actualizada.');
        } else {
            $sql = "INSERT INTO inscripciones_talleres (emprendedores_idemprendedores,talleres_idtalleres,asistio,calificacion,comentarios)
                    VALUES (:emprendedores_idemprendedores,:talleres_idtalleres,:asistio,:calificacion,:comentarios)";
            $pdo->prepare($sql)->execute($data);
            // Reducir cupo disponible
            $pdo->prepare("UPDATE talleres SET cupo_disponible=GREATEST(0,cupo_disponible-1) WHERE idtalleres=?")->execute([$data['talleres_idtalleres']]);
            setFlash('success','Inscripción registrada.');
        }
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(),'Duplicate') ? 'Este emprendedor ya está inscrito en ese taller.' : 'Error: '.$e->getMessage();
        setFlash('error',$msg);
    }
    redirect('inscripciones_talleres.php' . ($tallerId ? "?taller_id=$tallerId" : ''));
}

if ($action === 'delete' && $id) {
    try {
        $s = $pdo->prepare("SELECT talleres_idtalleres FROM inscripciones_talleres WHERE idinscripcion=?"); $s->execute([$id]); $ins = $s->fetch();
        $pdo->prepare("DELETE FROM inscripciones_talleres WHERE idinscripcion=?")->execute([$id]);
        if ($ins) $pdo->prepare("UPDATE talleres SET cupo_disponible=cupo_disponible+1 WHERE idtalleres=?")->execute([$ins['talleres_idtalleres']]);
        setFlash('success','Inscripción eliminada.');
    } catch (PDOException $e) { setFlash('error','No se puede eliminar: '.$e->getMessage()); }
    redirect('inscripciones_talleres.php' . ($tallerId ? "?taller_id=$tallerId" : ''));
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM inscripciones_talleres WHERE idinscripcion=?"); $s->execute([$id]); $edit = $s->fetch();
}

$emprendedores = $pdo->query("SELECT e.idemprendedores, CONCAT(p.nombres,' ',p.apellidos,' - ',p.rut) AS label
    FROM emprendedores e JOIN personas p ON e.personas_idpersonas=p.idpersonas WHERE e.estado=1 ORDER BY p.nombres")->fetchAll();
$talleres = $pdo->query("SELECT idtalleres, CONCAT(nombre_taller,' (',fecha_taller,')') AS label FROM talleres WHERE estado IN ('Programado','En Curso') ORDER BY fecha_taller")->fetchAll();

$search = sanitize($_GET['search'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1)); $perPage = 15;
$conditions = []; $params = [];
if ($tallerId) { $conditions[] = "i.talleres_idtalleres=:tid"; $params[':tid'] = $tallerId; }
if ($search) { $conditions[] = "(p.nombres LIKE :s OR p.apellidos LIKE :s OR t.nombre_taller LIKE :s)"; $params[':s'] = "%$search%"; }
$where = $conditions ? "WHERE ".implode(' AND ',$conditions) : '';
$base = "FROM inscripciones_talleres i JOIN emprendedores e ON i.emprendedores_idemprendedores=e.idemprendedores JOIN personas p ON e.personas_idpersonas=p.idpersonas JOIN talleres t ON i.talleres_idtalleres=t.idtalleres $where";
$tc = $pdo->prepare("SELECT COUNT(*) $base"); $tc->execute($params); $total = (int)$tc->fetchColumn();
$pag = getPaginationData($total,$page,$perPage);
$stmt = $pdo->prepare("SELECT i.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona, t.nombre_taller, t.fecha_taller $base ORDER BY i.fecha_inscripcion DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$pag['perPage'],PDO::PARAM_INT);
$stmt->bindValue(':offset',$pag['offset'],PDO::PARAM_INT);
$stmt->execute(); $rows = $stmt->fetchAll();

$tallerInfo = null;
if ($tallerId) {
    $s = $pdo->prepare("SELECT * FROM talleres WHERE idtalleres=?"); $s->execute([$tallerId]); $tallerInfo = $s->fetch();
}

include 'includes/header.php';
?>

<?php if ($tallerInfo): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center py-2">
    <span><strong><?= sanitize($tallerInfo['nombre_taller']) ?></strong> | <?= formatDate($tallerInfo['fecha_taller']) ?> | Cupo: <?= $tallerInfo['cupo_disponible'] ?>/<?= $tallerInfo['cupo_maximo'] ?> | <?= badgeEstado($tallerInfo['estado']) ?></span>
    <a href="inscripciones_talleres.php" class="btn btn-sm btn-outline-secondary">Ver todos</a>
</div>
<?php endif; ?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Inscripción' : 'Nueva Inscripción' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idinscripcion'] ?? '' ?>">
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
            <div class="col-md-4">
                <label class="form-label">Taller *</label>
                <select name="talleres_idtalleres" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($talleres as $t): ?>
                    <option value="<?= $t['idtalleres'] ?>" <?= ($edit['talleres_idtalleres'] ?? $tallerId) == $t['idtalleres'] ? 'selected' : '' ?>><?= sanitize($t['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Calificación (1-5)</label>
                <input type="number" min="1" max="5" name="calificacion" class="form-control form-control-sm" value="<?= $edit['calificacion'] ?? '' ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="asistio" class="form-check-input" id="asistio" <?= ($edit['asistio'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="asistio">Asistió</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Comentarios</label>
                <textarea name="comentarios" class="form-control form-control-sm" rows="2"><?= $edit['comentarios'] ?? '' ?></textarea>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="inscripciones_talleres.php<?= $tallerId ? "?taller_id=$tallerId" : '' ?>" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Inscripciones <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2">
            <div class="local-search-block">
<form class="d-flex gap-2" method="GET">
                <?php if ($tallerId): ?><input type="hidden" name="taller_id" value="<?= $tallerId ?>"><?php endif; ?>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
</div>
            <a href="inscripciones_talleres.php?action=create<?= $tallerId ? "&taller_id=$tallerId" : '' ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nueva</a>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0 dt-export" data-title="Listado de Inscripciones a Talleres">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Taller</th><th>Fecha Taller</th><th>Inscripción</th><th>Asistió</th><th>Calificación</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['idinscripcion'] ?></td>
            <td><?= sanitize($r['nombre_persona']) ?></td>
            <td><?= sanitize($r['nombre_taller']) ?></td>
            <td><?= formatDate($r['fecha_taller']) ?></td>
            <td><?= formatDateTime($r['fecha_inscripcion']) ?></td>
            <td><?= $r['asistio'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
            <td><?= $r['calificacion'] ? '⭐ '.$r['calificacion'] : '-' ?></td>
            <td>
                <a href="inscripciones_talleres.php?action=edit&id=<?= $r['idinscripcion'] ?><?= $tallerId ? "&taller_id=$tallerId" : '' ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="inscripciones_talleres.php?action=delete&id=<?= $r['idinscripcion'] ?><?= $tallerId ? "&taller_id=$tallerId" : '' ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin inscripciones</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag,'inscripciones_talleres.php?search='.urlencode($search).($tallerId?"&taller_id=$tallerId":'')) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
