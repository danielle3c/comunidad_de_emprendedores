<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Emprendedores';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'personas_idpersonas' => (int)$_POST['personas_idpersonas'],
        'tipo_negocio'        => sanitize($_POST['tipo_negocio'] ?? ''),
        'rubro'               => sanitize($_POST['rubro'] ?? ''),
        'producto_principal'  => sanitize($_POST['producto_principal'] ?? ''),
        'limite_credito'      => (float)str_replace(',', '.', $_POST['limite_credito'] ?? 0),
        'fecha_registro'      => $_POST['fecha_registro'] ?: null,
        'estado'              => isset($_POST['estado']) ? 1 : 0,
    ];
    try {
        if ($_POST['id']) {
            $sql = "UPDATE emprendedores SET personas_idpersonas=:personas_idpersonas,tipo_negocio=:tipo_negocio,
                    rubro=:rubro,producto_principal=:producto_principal,limite_credito=:limite_credito,
                    fecha_registro=:fecha_registro,estado=:estado WHERE idemprendedores=:id";
            $pdo->prepare($sql)->execute(array_merge($data, [':id' => (int)$_POST['id']]));
            setFlash('success', 'Emprendedor actualizado.');
        } else {
            $sql = "INSERT INTO emprendedores (personas_idpersonas,tipo_negocio,rubro,producto_principal,limite_credito,fecha_registro,estado)
                    VALUES (:personas_idpersonas,:tipo_negocio,:rubro,:producto_principal,:limite_credito,:fecha_registro,:estado)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success', 'Emprendedor registrado.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
    redirect('emprendedores.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM emprendedores WHERE idemprendedores=?")->execute([$id]);
        setFlash('success', 'Emprendedor eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
    }
    redirect('emprendedores.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM emprendedores WHERE idemprendedores=?");
    $s->execute([$id]); $edit = $s->fetch();
}

$personas = $pdo->query("SELECT idpersonas, CONCAT(nombres,' ',apellidos,' - ',rut) AS label FROM personas WHERE estado=1 ORDER BY nombres")->fetchAll();

$search = sanitize($_GET['search'] ?? '');
$page   = max(1,(int)($_GET['page'] ?? 1));
$perPage = 15;
$where = $search ? "WHERE p.nombres LIKE :s OR p.apellidos LIKE :s OR e.rubro LIKE :s OR p.rut LIKE :s" : "";
$params = $search ? [':s' => "%$search%"] : [];
$countSql = "SELECT COUNT(*) FROM emprendedores e JOIN personas p ON e.personas_idpersonas=p.idpersonas $where";
$tc = $pdo->prepare($countSql);
$tc->execute($params);
$total = (int)$tc->fetchColumn();
$pag = getPaginationData($total, $page, $perPage);
$sql = "SELECT e.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona, p.rut
        FROM emprendedores e JOIN personas p ON e.personas_idpersonas=p.idpersonas
        $where ORDER BY p.nombres ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

include 'includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Emprendedor' : 'Nuevo Emprendedor' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idemprendedores'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Persona *</label>
                <select name="personas_idpersonas" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($personas as $p): ?>
                    <option value="<?= $p['idpersonas'] ?>" <?= ($edit['personas_idpersonas'] ?? '') == $p['idpersonas'] ? 'selected' : '' ?>>
                        <?= sanitize($p['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipo de Negocio</label>
                <input type="text" name="tipo_negocio" class="form-control form-control-sm" value="<?= $edit['tipo_negocio'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Rubro</label>
                <input type="text" name="rubro" class="form-control form-control-sm" value="<?= $edit['rubro'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Producto Principal</label>
                <textarea name="producto_principal" class="form-control form-control-sm" rows="2"><?= $edit['producto_principal'] ?? '' ?></textarea>
            </div>
            <div class="col-md-2">
                <label class="form-label">Límite de Crédito</label>
                <input type="number" step="0.01" name="limite_credito" class="form-control form-control-sm" value="<?= $edit['limite_credito'] ?? '0' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Registro</label>
                <input type="date" name="fecha_registro" class="form-control form-control-sm" value="<?= $edit['fecha_registro'] ?? date('Y-m-d') ?>">
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
            <a href="emprendedores.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Emprendedores <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2">
            <div class="local-search-block">
<form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
</div>
            <a href="emprendedores.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    
    <?php include __DIR__ . '/includes/print_button.php'; ?>
</div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0 dt-export" data-title="Listado de Emprendedores">
        <thead><tr><th>ID</th><th>RUT</th><th>Nombre</th><th>Rubro</th><th>Tipo Negocio</th><th>Límite Crédito</th><th>Registro</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['idemprendedores'] ?></td>
            <td><?= $r['rut'] ?></td>
            <td><?= sanitize($r['nombre_persona']) ?></td>
            <td><?= sanitize($r['rubro']) ?: '-' ?></td>
            <td><?= sanitize($r['tipo_negocio']) ?: '-' ?></td>
            <td><?= formatMoney($r['limite_credito']) ?></td>
            <td><?= formatDate($r['fecha_registro']) ?></td>
            <td><?= badgeEstado((string)$r['estado']) ?></td>
            <td>
                <a href="emprendedores.php?action=edit&id=<?= $r['idemprendedores'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="emprendedores.php?action=delete&id=<?= $r['idemprendedores'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'emprendedores.php?search='.urlencode($search)) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
