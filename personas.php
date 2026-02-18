<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Personas';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('personas.php');
    }

    $data = [
        'rut'             => sanitize(filter_input(INPUT_POST, 'rut', FILTER_SANITIZE_STRING) ?? ''),
        'nombres'         => sanitize(filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING) ?? ''),
        'apellidos'       => sanitize(filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_STRING) ?? ''),
        'fecha_nacimiento'=> filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_STRING) ?: null,
        'genero'          => filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_STRING) ?? 'Otro',
        'telefono'        => sanitize(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING) ?? ''),
        'direccion'       => sanitize(filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING) ?? ''),
        'email'           => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null,
        'estado'          => filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sql = "UPDATE personas SET 
                    rut = :rut,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    fecha_nacimiento = :fecha_nacimiento,
                    genero = :genero,
                    telefono = :telefono,
                    direccion = :direccion,
                    email = :email,
                    estado = :estado
                    WHERE idpersonas = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Persona actualizada correctamente.');
        } else {
            $sql = "INSERT INTO personas 
                    (rut, nombres, apellidos, fecha_nacimiento, genero, telefono, direccion, email, estado)
                    VALUES 
                    (:rut, :nombres, :apellidos, :fecha_nacimiento, :genero, :telefono, :direccion, :email, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Persona registrada correctamente.');
        }
    } catch (PDOException $e) {
        $errorMsg = (strpos($e->getMessage(), 'Duplicate') !== false) 
            ? 'El RUT ya está registrado.' 
            : 'Error en la base de datos.';
        setFlash('error', $errorMsg);
        error_log("Error en personas.php: " . $e->getMessage());
    }
    redirect('personas.php');
}

if ($action === 'delete' && $id) {
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM emprendedores WHERE personas_idpersonas = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            setFlash('error', 'No se puede eliminar: la persona tiene emprendedores asociados.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM personas WHERE idpersonas = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Persona eliminada.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en personas.php (delete): " . $e->getMessage());
    }
    redirect('personas.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM personas WHERE idpersonas = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Persona no encontrada.');
        redirect('personas.php');
    }
}

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$page   = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$where = "";
if ($search) {
    $where = "WHERE nombres LIKE :search OR apellidos LIKE :search OR rut LIKE :search OR email LIKE :search";
    $params[':search'] = "%$search%";
}

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM personas $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT * FROM personas $where ORDER BY nombres ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold">
        <?= $edit ? 'Editar Persona' : 'Nueva Persona' ?>
    </div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idpersonas'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">RUT *</label>
                <input type="text" name="rut" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['rut'] ?? '') ?>" required maxlength="20">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombres *</label>
                <input type="text" name="nombres" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['nombres'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Apellidos *</label>
                <input type="text" name="apellidos" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['apellidos'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['fecha_nacimiento'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Género</label>
                <select name="genero" class="form-select form-select-sm">
                    <?php foreach (['Masculino', 'Femenino', 'Otro'] as $g): ?>
                    <option value="<?= $g ?>" <?= ($edit['genero'] ?? 'Otro') === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['email'] ?? '') ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['direccion'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="estado" class="form-check-input" id="estado" 
                           <?= ($edit['estado'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="estado">Activo</label>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="personas.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Listado de Personas <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="personas.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr><th>ID</th><th>RUT</th><th>Nombres</th><th>Apellidos</th><th>Teléfono</th><th>Email</th><th>Género</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idpersonas'] ?></td>
            <td><?= htmlspecialchars($r['rut']) ?></td>
            <td><?= htmlspecialchars($r['nombres']) ?></td>
            <td><?= htmlspecialchars($r['apellidos']) ?></td>
            <td><?= $r['telefono'] ?: '-' ?></td>
            <td><?= $r['email'] ?: '-' ?></td>
            <td><?= htmlspecialchars($r['genero'] ?? '') ?></td>
            <td><?= badgeEstado((string)$r['estado']) ?></td>
            <td>
                <a href="personas.php?action=edit&id=<?= (int)$r['idpersonas'] ?>" 
                   class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="personas.php?action=delete&id=<?= (int)$r['idpersonas'] ?>" 
                   class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0">
        <?= renderPagination($pag, 'personas.php?search='.urlencode($search)) ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>