<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Jornadas';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre_jornada' => sanitize($_POST['nombre_jornada']),
        'descripcion'    => sanitize($_POST['descripcion'] ?? ''),
        'fecha_jornada'  => $_POST['fecha_jornada'],
        'hora_inicio'    => $_POST['hora_inicio'] ?: null,
        'hora_fin'       => $_POST['hora_fin'] ?: null,
        'lugar'          => sanitize($_POST['lugar'] ?? ''),
        'tipo_jornada'   => sanitize($_POST['tipo_jornada'] ?? ''),
        'estado'         => $_POST['estado'] ?? 'Planificada',
    ];
    try {
        if ($_POST['id']) {
            $sql = "UPDATE jornadas SET nombre_jornada=:nombre_jornada,descripcion=:descripcion,fecha_jornada=:fecha_jornada,
                    hora_inicio=:hora_inicio,hora_fin=:hora_fin,lugar=:lugar,tipo_jornada=:tipo_jornada,estado=:estado WHERE idjornadas=:id";
            $pdo->prepare($sql)->execute(array_merge($data,[':id'=>(int)$_POST['id']]));
            setFlash('success','Jornada actualizada.');
        } else {
            $sql = "INSERT INTO jornadas (nombre_jornada,descripcion,fecha_jornada,hora_inicio,hora_fin,lugar,tipo_jornada,estado)
                    VALUES (:nombre_jornada,:descripcion,:fecha_jornada,:hora_inicio,:hora_fin,:lugar,:tipo_jornada,:estado)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success','Jornada creada.');
        }
    } catch (PDOException $e) { setFlash('error','Error: '.$e->getMessage()); }
    redirect('jornadas.php');
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM jornadas WHERE idjornadas=?")->execute([$id]); setFlash('success','Jornada eliminada.'); }
    catch (PDOException $e) { setFlash('error','No se puede eliminar: '.$e->getMessage()); }
    redirect('jornadas.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM jornadas WHERE idjornadas=?"); $s->execute([$id]); $edit = $s->fetch();
}

$search = sanitize($_GET['search'] ?? '');
$filtroEstado = sanitize($_GET['estado'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1)); $perPage = 15;
$conditions = []; $params = [];
if ($search) { $conditions[] = "(nombre_jornada LIKE :s OR tipo_jornada LIKE :s OR lugar LIKE :s)"; $params[':s'] = "%$search%"; }
if ($filtroEstado) { $conditions[] = "estado=:estado"; $params[':estado'] = $filtroEstado; }
$where = $conditions ? "WHERE ".implode(' AND ',$conditions) : '';
$tc = $pdo->prepare("SELECT COUNT(*) FROM jornadas $where"); $tc->execute($params); $total = (int)$tc->fetchColumn();
$pag = getPaginationData($total,$page,$perPage);
$stmt = $pdo->prepare("SELECT * FROM jornadas $where ORDER BY fecha_jornada DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$pag['perPage'],PDO::PARAM_INT);
$stmt->bindValue(':offset',$pag['offset'],PDO::PARAM_INT);
$stmt->execute(); $rows = $stmt->fetchAll();

include 'includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Jornada' : 'Nueva Jornada' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idjornadas'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Nombre de la Jornada *</label>
                <input type="text" name="nombre_jornada" class="form-control form-control-sm" value="<?= $edit['nombre_jornada'] ?? '' ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de Jornada</label>
                <input type="text" name="tipo_jornada" class="form-control form-control-sm" value="<?= $edit['tipo_jornada'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <?php foreach (['Planificada','En Ejecución','Finalizada','Cancelada'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($edit['estado'] ?? 'Planificada') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha_jornada" class="form-control form-control-sm" value="<?= $edit['fecha_jornada'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control form-control-sm" value="<?= $edit['hora_inicio'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control form-control-sm" value="<?= $edit['hora_fin'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Lugar</label>
                <input type="text" name="lugar" class="form-control form-control-sm" value="<?= $edit['lugar'] ?? '' ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="2"><?= $edit['descripcion'] ?? '' ?></textarea>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="jornadas.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Jornadas <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <div class="local-search-block">
<form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <select name="estado" class="form-select form-select-sm" style="width:140px">
                    <option value="">Todos</option>
                    <?php foreach (['Planificada','En Ejecución','Finalizada','Cancelada'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $filtroEstado===$opt?'selected':'' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
</div>
            <a href="jornadas.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nueva</a>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0 dt-export" data-title="Listado de Jornadas">
        <thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Fecha</th><th>Horario</th><th>Lugar</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['idjornadas'] ?></td>
            <td><?= sanitize($r['nombre_jornada']) ?></td>
            <td><?= sanitize($r['tipo_jornada']) ?: '-' ?></td>
            <td><?= formatDate($r['fecha_jornada']) ?></td>
            <td><?= $r['hora_inicio'] ? substr($r['hora_inicio'],0,5).' - '.substr($r['hora_fin'],0,5) : '-' ?></td>
            <td><?= sanitize($r['lugar']) ?: '-' ?></td>
            <td><?= badgeEstado($r['estado']) ?></td>
            <td>
                <a href="jornadas.php?action=edit&id=<?= $r['idjornadas'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="jornadas.php?action=delete&id=<?= $r['idjornadas'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag,'jornadas.php?search='.urlencode($search).'&estado='.urlencode($filtroEstado)) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
