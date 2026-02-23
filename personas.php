<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Personas';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// --- GUARDAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'rut'             => sanitize($_POST['rut'] ?? ''),
        'nombres'         => sanitize($_POST['nombres'] ?? ''),
        'apellidos'       => sanitize($_POST['apellidos'] ?? ''),
        'fecha_nacimiento'=> $_POST['fecha_nacimiento'] ?: null,
        'genero'          => $_POST['genero'] ?? 'Otro',
        'telefono'        => sanitize($_POST['telefono'] ?? ''),
        'direccion'       => sanitize($_POST['direccion'] ?? ''),
        'email'           => sanitize($_POST['email'] ?? ''),
        'estado'          => isset($_POST['estado']) ? 1 : 0,
    ];
    try {
        if ($_POST['id']) {
            $sql = "UPDATE personas SET rut=:rut,nombres=:nombres,apellidos=:apellidos,
                    fecha_nacimiento=:fecha_nacimiento,genero=:genero,telefono=:telefono,
                    direccion=:direccion,email=:email,estado=:estado WHERE idpersonas=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($data, [':id' => (int)$_POST['id']]));
            setFlash('success', 'Persona actualizada correctamente.');
        } else {
            $sql = "INSERT INTO personas (rut,nombres,apellidos,fecha_nacimiento,genero,telefono,direccion,email,estado)
                    VALUES (:rut,:nombres,:apellidos,:fecha_nacimiento,:genero,:telefono,:direccion,:email,:estado)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success', 'Persona registrada correctamente.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
    redirect('personas.php');
}

// --- ELIMINAR ---
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM personas WHERE idpersonas=?")->execute([$id]);
        setFlash('success', 'Persona eliminada.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
    }
    redirect('personas.php');
}

// --- EDITAR: cargar datos ---
$edit = null;
if ($action === 'edit' && $id) {
    $edit = $pdo->prepare("SELECT * FROM personas WHERE idpersonas=?");
    $edit->execute([$id]);
    $edit = $edit->fetch();
}

// --- LISTAR ---
$search = sanitize($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$where = $search ? "WHERE nombres LIKE :s OR apellidos LIKE :s OR rut LIKE :s OR email LIKE :s" : "";
$params = $search ? [':s' => "%$search%"] : [];
$total = $pdo->prepare("SELECT COUNT(*) FROM personas $where");
$total->execute($params);
$total = (int)$total->fetchColumn();
$pag = getPaginationData($total, $page, $perPage);
$stmt = $pdo->prepare("SELECT * FROM personas $where ORDER BY nombres ASC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

include 'includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<!-- FORMULARIO -->
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold">
        <?= $edit ? 'Editar Persona' : 'Nueva Persona' ?>
    </div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idpersonas'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">RUT *</label>
                <input type="text" name="rut" class="form-control form-control-sm" value="<?= $edit['rut'] ?? '' ?>" required maxlength="20">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombres *</label>
                <input type="text" name="nombres" class="form-control form-control-sm" value="<?= $edit['nombres'] ?? '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Apellidos *</label>
                <input type="text" name="apellidos" class="form-control form-control-sm" value="<?= $edit['apellidos'] ?? '' ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control form-control-sm" value="<?= $edit['fecha_nacimiento'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Género</label>
                <select name="genero" class="form-select form-select-sm">
                    <?php foreach (['Masculino','Femenino','Otro'] as $g): ?>
                    <option value="<?= $g ?>" <?= ($edit['genero'] ?? 'Otro') === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control form-control-sm" value="<?= $edit['telefono'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" value="<?= $edit['email'] ?? '' ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control form-control-sm" value="<?= $edit['direccion'] ?? '' ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="estado" class="form-check-input" id="estado" <?= ($edit['estado'] ?? 1) ? 'checked' : '' ?>>
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

<!-- TABLA -->
<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Listado de Personas <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="personas.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
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
            <td><?= $r['idpersonas'] ?></td>
            <td><?= $r['rut'] ?></td>
            <td><?= sanitize($r['nombres']) ?></td>
            <td><?= sanitize($r['apellidos']) ?></td>
            <td><?= $r['telefono'] ?: '-' ?></td>
            <td><?= $r['email'] ?: '-' ?></td>
            <td><?= $r['genero'] ?></td>
            <td><?= badgeEstado((string)$r['estado']) ?></td>
            <td>
                <a href="personas.php?action=edit&id=<?= $r['idpersonas'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="personas.php?action=delete&id=<?= $r['idpersonas'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
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

<?php include 'includes/footer.php'; ?>
