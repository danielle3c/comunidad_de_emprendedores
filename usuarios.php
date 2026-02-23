<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Usuarios';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = trim($_POST['password'] ?? '');
    $data = [
        'nombre_usuario' => sanitize($_POST['nombre_usuario']),
        'email'          => sanitize($_POST['email'] ?? ''),
        'rol'            => $_POST['rol'] ?? 'usuario',
        'nombres'        => sanitize($_POST['nombres'] ?? ''),
        'apellidos'      => sanitize($_POST['apellidos'] ?? ''),
        'telefono'       => sanitize($_POST['telefono'] ?? ''),
        'activo'         => isset($_POST['activo']) ? 1 : 0,
        'estado'         => isset($_POST['activo']) ? 'activo' : 'inactivo',
    ];
    try {
        if ($_POST['id']) {
            if ($pass) $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
            $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($data)));
            $pdo->prepare("UPDATE Usuarios SET $sets WHERE idUsuarios=:id")->execute(array_merge($data,[':id'=>(int)$_POST['id']]));
            setFlash('success','Usuario actualizado.');
        } else {
            if (!$pass) { setFlash('error','La contraseña es obligatoria.'); redirect('usuarios.php?action=create'); }
            $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
            $cols = implode(',',array_keys($data));
            $vals = ':'.implode(',:', array_keys($data));
            $pdo->prepare("INSERT INTO Usuarios ($cols) VALUES ($vals)")->execute($data);
            setFlash('success','Usuario creado.');
        }
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(),'Duplicate') ? 'El nombre de usuario o email ya existe.' : 'Error: '.$e->getMessage();
        setFlash('error',$msg);
    }
    redirect('usuarios.php');
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM Usuarios WHERE idUsuarios=?")->execute([$id]); setFlash('success','Usuario eliminado.'); }
    catch (PDOException $e) { setFlash('error','No se puede eliminar: '.$e->getMessage()); }
    redirect('usuarios.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM Usuarios WHERE idUsuarios=?"); $s->execute([$id]); $edit = $s->fetch();
}

$search = sanitize($_GET['search'] ?? '');
$filtroRol = sanitize($_GET['rol'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1)); $perPage = 15;
$conditions = []; $params = [];
if ($search) { $conditions[] = "(nombre_usuario LIKE :s OR email LIKE :s OR nombres LIKE :s OR apellidos LIKE :s)"; $params[':s'] = "%$search%"; }
if ($filtroRol) { $conditions[] = "rol=:rol"; $params[':rol'] = $filtroRol; }
$where = $conditions ? "WHERE ".implode(' AND ',$conditions) : '';
$tc = $pdo->prepare("SELECT COUNT(*) FROM Usuarios $where"); $tc->execute($params); $total = (int)$tc->fetchColumn();
$pag = getPaginationData($total,$page,$perPage);
$stmt = $pdo->prepare("SELECT idUsuarios,nombre_usuario,email,rol,nombres,apellidos,telefono,activo,estado,ultimo_acceso,created_at FROM Usuarios $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$pag['perPage'],PDO::PARAM_INT);
$stmt->bindValue(':offset',$pag['offset'],PDO::PARAM_INT);
$stmt->execute(); $rows = $stmt->fetchAll();

include 'includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Usuario' : 'Nuevo Usuario' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idUsuarios'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Usuario *</label>
                <input type="text" name="nombre_usuario" class="form-control form-control-sm" value="<?= $edit['nombre_usuario'] ?? '' ?>" required maxlength="100">
            </div>
            <div class="col-md-3">
                <label class="form-label">Contraseña <?= $edit ? '(dejar vacío para mantener)' : '*' ?></label>
                <input type="password" name="password" class="form-control form-control-sm" <?= !$edit ? 'required' : '' ?> autocomplete="new-password">
            </div>
            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" value="<?= $edit['email'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-select form-select-sm">
                    <?php foreach (['usuario','moderador','admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= ($edit['rol'] ?? 'usuario') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nombres</label>
                <input type="text" name="nombres" class="form-control form-control-sm" value="<?= $edit['nombres'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Apellidos</label>
                <input type="text" name="apellidos" class="form-control form-control-sm" value="<?= $edit['apellidos'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control form-control-sm" value="<?= $edit['telefono'] ?? '' ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="activo" class="form-check-input" id="activo" <?= ($edit['activo'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="usuarios.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Usuarios <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <select name="rol" class="form-select form-select-sm" style="width:120px">
                    <option value="">Todos</option>
                    <?php foreach (['usuario','moderador','admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= $filtroRol===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="usuarios.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0 dt-export" data-title="Listado de Usuarios">
        <thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Último Acceso</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['idUsuarios'] ?></td>
            <td><strong><?= sanitize($r['nombre_usuario']) ?></strong></td>
            <td><?= sanitize(trim($r['nombres'].' '.$r['apellidos'])) ?: '-' ?></td>
            <td><?= $r['email'] ?: '-' ?></td>
            <td>
                <?php $rolColor = ['admin'=>'danger','moderador'=>'warning','usuario'=>'secondary'];
                echo '<span class="badge bg-'.($rolColor[$r['rol']] ?? 'secondary').'">'.ucfirst($r['rol']).'</span>'; ?>
            </td>
            <td><?= $r['ultimo_acceso'] ? formatDateTime($r['ultimo_acceso']) : '-' ?></td>
            <td><?= badgeEstado((string)$r['activo']) ?></td>
            <td>
                <a href="usuarios.php?action=edit&id=<?= $r['idUsuarios'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="usuarios.php?action=delete&id=<?= $r['idUsuarios'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin usuarios</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag,'usuarios.php?search='.urlencode($search).'&rol='.urlencode($filtroRol)) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
