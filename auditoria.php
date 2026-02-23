<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Auditoría';
$pdo = getConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM auditoria WHERE idauditoria=?")->execute([$id]); setFlash('success','Registro de auditoría eliminado.'); }
    catch (PDOException $e) { setFlash('error','Error: '.$e->getMessage()); }
    redirect('auditoria.php');
}

if ($action === 'clear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try { $pdo->exec("DELETE FROM auditoria"); setFlash('success','Auditoría limpiada.'); }
    catch (PDOException $e) { setFlash('error','Error: '.$e->getMessage()); }
    redirect('auditoria.php');
}

$search = sanitize($_GET['search'] ?? '');
$filtroTabla = sanitize($_GET['tabla'] ?? '');
$filtroUsuario = sanitize($_GET['usuario'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1)); $perPage = 20;
$conditions = []; $params = [];
if ($search) { $conditions[] = "(a.accion LIKE :s OR a.descripcion LIKE :s OR a.ip_address LIKE :s)"; $params[':s'] = "%$search%"; }
if ($filtroTabla) { $conditions[] = "a.tabla=:tabla"; $params[':tabla'] = $filtroTabla; }
if ($filtroUsuario) { $conditions[] = "u.nombre_usuario LIKE :usr"; $params[':usr'] = "%$filtroUsuario%"; }
$where = $conditions ? "WHERE ".implode(' AND ',$conditions) : '';
$base = "FROM auditoria a LEFT JOIN Usuarios u ON a.usuario_id=u.idUsuarios $where";
$tc = $pdo->prepare("SELECT COUNT(*) $base"); $tc->execute($params); $total = (int)$tc->fetchColumn();
$pag = getPaginationData($total,$page,$perPage);
$stmt = $pdo->prepare("SELECT a.*, u.nombre_usuario $base ORDER BY a.fecha_hora DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$pag['perPage'],PDO::PARAM_INT);
$stmt->bindValue(':offset',$pag['offset'],PDO::PARAM_INT);
$stmt->execute(); $rows = $stmt->fetchAll();

$tablas = $pdo->query("SELECT DISTINCT tabla FROM auditoria WHERE tabla IS NOT NULL ORDER BY tabla")->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Log de Auditoría <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <div class="local-search-block">
<form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar acción..." value="<?= $search ?>">
                <input type="text" name="usuario" class="form-control form-control-sm" placeholder="Usuario..." value="<?= $filtroUsuario ?>" style="width:120px">
                <select name="tabla" class="form-select form-select-sm" style="width:130px">
                    <option value="">Todas las tablas</option>
                    <?php foreach ($tablas as $t): ?>
                    <option value="<?= $t ?>" <?= $filtroTabla===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
</div>
            <form method="POST" action="auditoria.php" class="d-inline" onsubmit="return confirm('¿Limpiar toda la auditoría? Esta acción no se puede deshacer.')">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Limpiar todo</button>
            </form>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover table-sm mb-0 dt-export" data-title="Listado de Auditoría">
        <thead><tr><th>ID</th><th>Fecha/Hora</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>Registro ID</th><th>Descripción</th><th>IP</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['idauditoria'] ?></td>
            <td style="white-space:nowrap"><?= formatDateTime($r['fecha_hora']) ?></td>
            <td><?= $r['nombre_usuario'] ? sanitize($r['nombre_usuario']) : '<span class="text-muted">Sistema</span>' ?></td>
            <td><span class="badge bg-info text-dark"><?= sanitize($r['accion']) ?></span></td>
            <td><?= $r['tabla'] ? '<code>'.sanitize($r['tabla']).'</code>' : '-' ?></td>
            <td><?= $r['registro_id'] ?: '-' ?></td>
            <td><?= mb_strimwidth(sanitize($r['descripcion'] ?? ''),0,50,'...') ?></td>
            <td><small><?= $r['ip_address'] ?: '-' ?></small></td>
            <td>
                <a href="auditoria.php?action=delete&id=<?= $r['idauditoria'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Sin registros de auditoría</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag,'auditoria.php?search='.urlencode($search).'&tabla='.urlencode($filtroTabla).'&usuario='.urlencode($filtroUsuario)) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
