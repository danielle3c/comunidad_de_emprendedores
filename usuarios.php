<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/roles.php';

// Solo admin puede gestionar usuarios
require_role(['admin']);

$pageTitle = 'Usuarios';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('usuarios.php');
    }

    $pass = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING) ?? '');
    $data = [
        'nombre_usuario' => sanitize(filter_input(INPUT_POST, 'nombre_usuario', FILTER_SANITIZE_STRING) ?? ''),
        'email'          => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null,
        'rol'            => filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING) ?? 'usuario',
        'nombres'        => sanitize(filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING) ?? ''),
        'apellidos'      => sanitize(filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_STRING) ?? ''),
        'telefono'       => sanitize(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING) ?? ''),
        'activo'         => filter_input(INPUT_POST, 'activo', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        'estado'         => filter_input(INPUT_POST, 'activo', FILTER_VALIDATE_BOOLEAN) ? 'activo' : 'inactivo',
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            if ($pass) {
                $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
            }
            $sets = [];
            foreach ($data as $col => $val) {
                $sets[] = "$col = :$col";
            }
            $sql = "UPDATE Usuarios SET " . implode(', ', $sets) . " WHERE idUsuarios = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Usuario actualizado.');
        } else {
            if (!$pass) {
                setFlash('error', 'La contraseña es obligatoria.');
                redirect('usuarios.php?action=create');
            }
            $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
            $cols = implode(', ', array_keys($data));
            $vals = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO Usuarios ($cols) VALUES ($vals)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Usuario creado.');
        }
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(), 'Duplicate') ? 'El nombre de usuario o email ya existe.' : 'Error: ' . $e->getMessage();
        setFlash('error', $msg);
        error_log("Error en usuarios.php: " . $e->getMessage());
    }
    redirect('usuarios.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM Usuarios WHERE idUsuarios = ?")->execute([$id]);
        setFlash('success', 'Usuario eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en usuarios.php (delete): " . $e->getMessage());
    }
    redirect('usuarios.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE idUsuarios = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Usuario no encontrado.');
        redirect('usuarios.php');
    }
}

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$filtroRol = sanitize(filter_input(INPUT_GET, 'rol', FILTER_SANITIZE_STRING) ?? '');
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$conditions = [];

if ($search) {
    $conditions[] = "(nombre_usuario LIKE :search OR email LIKE :search OR nombres LIKE :search OR apellidos LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($filtroRol) {
    $conditions[] = "rol = :rol";
    $params[':rol'] = $filtroRol;
}
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM Usuarios $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT idUsuarios, nombre_usuario, email, rol, nombres, apellidos, telefono, activo, estado, ultimo_acceso, created_at 
        FROM Usuarios $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
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
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Usuario' : 'Nuevo Usuario' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idUsuarios'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Usuario *</label>
                <input type="text" name="nombre_usuario" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['nombre_usuario'] ?? '') ?>" required maxlength="100">
            </div>
            <div class="col-md-3">
                <label class="form-label">Contraseña <?= $edit ? '(dejar vacío para mantener)' : '*' ?></label>
                <input type="password" name="password" class="form-control form-control-sm" 
                       <?= !$edit ? 'required' : '' ?> autocomplete="new-password">
            </div>
            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['email'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-select form-select-sm">
                    <?php foreach (['usuario', 'moderador', 'admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= ($edit['rol'] ?? 'usuario') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nombres</label>
                <input type="text" name="nombres" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['nombres'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Apellidos</label>
                <input type="text" name="apellidos" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['apellidos'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['telefono'] ?? '') ?>">
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
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <select name="rol" class="form-select form-select-sm" style="width:120px">
                    <option value="">Todos</option>
                    <?php foreach (['usuario', 'moderador', 'admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= $filtroRol === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="usuarios.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Último Acceso</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idUsuarios'] ?></td>
            <td><strong><?= htmlspecialchars($r['nombre_usuario']) ?></strong></td>
            <td><?= htmlspecialchars(trim($r['nombres'] . ' ' . $r['apellidos'])) ?: '-' ?></td>
            <td><?= $r['email'] ?: '-' ?></td>
            <td>
                <?php 
                $rolColor = ['admin' => 'danger', 'moderador' => 'warning', 'usuario' => 'secondary'];
                $color = $rolColor[$r['rol']] ?? 'secondary';
                echo '<span class="badge bg-' . $color . '">' . ucfirst($r['rol']) . '</span>';
                ?>
            </td>
            <td><?= $r['ultimo_acceso'] ? formatDateTime($r['ultimo_acceso']) : '-' ?></td>
            <td><?= badgeEstado((string)$r['activo']) ?></td>
            <td>
                <a href="usuarios.php?action=edit&id=<?= (int)$r['idUsuarios'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="usuarios.php?action=delete&id=<?= (int)$r['idUsuarios'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin usuarios</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'usuarios.php?search='.urlencode($search).'&rol='.urlencode($filtroRol)) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>