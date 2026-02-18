<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Emprendedores';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('emprendedores.php');
    }

    $data = [
        'personas_idpersonas' => filter_input(INPUT_POST, 'personas_idpersonas', FILTER_VALIDATE_INT) ?: 0,
        'tipo_negocio'        => sanitize(filter_input(INPUT_POST, 'tipo_negocio', FILTER_SANITIZE_STRING) ?? ''),
        'rubro'               => sanitize(filter_input(INPUT_POST, 'rubro', FILTER_SANITIZE_STRING) ?? ''),
        'producto_principal'  => sanitize(filter_input(INPUT_POST, 'producto_principal', FILTER_SANITIZE_STRING) ?? ''),
        'limite_credito'      => (float)str_replace(',', '.', filter_input(INPUT_POST, 'limite_credito', FILTER_SANITIZE_STRING) ?? 0),
        'fecha_registro'      => filter_input(INPUT_POST, 'fecha_registro', FILTER_SANITIZE_STRING) ?: null,
        'estado'              => filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sql = "UPDATE emprendedores SET 
                    personas_idpersonas = :personas_idpersonas,
                    tipo_negocio = :tipo_negocio,
                    rubro = :rubro,
                    producto_principal = :producto_principal,
                    limite_credito = :limite_credito,
                    fecha_registro = :fecha_registro,
                    estado = :estado
                    WHERE idemprendedores = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Emprendedor actualizado.');
        } else {
            $sql = "INSERT INTO emprendedores 
                    (personas_idpersonas, tipo_negocio, rubro, producto_principal, limite_credito, fecha_registro, estado)
                    VALUES 
                    (:personas_idpersonas, :tipo_negocio, :rubro, :producto_principal, :limite_credito, :fecha_registro, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Emprendedor registrado.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
        error_log("Error en emprendedores.php: " . $e->getMessage());
    }
    redirect('emprendedores.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM emprendedores WHERE idemprendedores = ?")->execute([$id]);
        setFlash('success', 'Emprendedor eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en emprendedores.php (delete): " . $e->getMessage());
    }
    redirect('emprendedores.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM emprendedores WHERE idemprendedores = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Emprendedor no encontrado.');
        redirect('emprendedores.php');
    }
}

$personas = $pdo->query("SELECT idpersonas, CONCAT(nombres,' ',apellidos,' - ',rut) AS label FROM personas WHERE estado = 1 ORDER BY nombres")->fetchAll();

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$page   = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$where = "";
if ($search) {
    $where = "WHERE p.nombres LIKE :search OR p.apellidos LIKE :search OR e.rubro LIKE :search OR p.rut LIKE :search";
    $params[':search'] = "%$search%";
}

$countSql = "SELECT COUNT(*) FROM emprendedores e JOIN personas p ON e.personas_idpersonas = p.idpersonas $where";
$totalStmt = $pdo->prepare($countSql);
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT e.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona, p.rut
        FROM emprendedores e 
        JOIN personas p ON e.personas_idpersonas = p.idpersonas
        $where 
        ORDER BY p.nombres ASC 
        LIMIT :limit OFFSET :offset";
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
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Emprendedor' : 'Nuevo Emprendedor' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idemprendedores'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Persona *</label>
                <select name="personas_idpersonas" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($personas as $p): ?>
                    <option value="<?= (int)$p['idpersonas'] ?>" <?= ($edit['personas_idpersonas'] ?? '') == $p['idpersonas'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipo de Negocio</label>
                <input type="text" name="tipo_negocio" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['tipo_negocio'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Rubro</label>
                <input type="text" name="rubro" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['rubro'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Producto Principal</label>
                <textarea name="producto_principal" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($edit['producto_principal'] ?? '') ?></textarea>
            </div>
            <div class="col-md-2">
                <label class="form-label">Límite de Crédito</label>
                <input type="number" step="0.01" name="limite_credito" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['limite_credito'] ?? '0') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Registro</label>
                <input type="date" name="fecha_registro" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['fecha_registro'] ?? date('Y-m-d')) ?>">
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
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="emprendedores.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>RUT</th><th>Nombre</th><th>Rubro</th><th>Tipo Negocio</th><th>Límite Crédito</th><th>Registro</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idemprendedores'] ?></td>
            <td><?= htmlspecialchars($r['rut']) ?></td>
            <td><?= htmlspecialchars($r['nombre_persona']) ?></td>
            <td><?= htmlspecialchars($r['rubro']) ?: '-' ?></td>
            <td><?= htmlspecialchars($r['tipo_negocio']) ?: '-' ?></td>
            <td><?= formatMoney($r['limite_credito']) ?></td>
            <td><?= formatDate($r['fecha_registro']) ?></td>
            <td><?= badgeEstado((string)$r['estado']) ?></td>
            <td>
                <a href="emprendedores.php?action=edit&id=<?= (int)$r['idemprendedores'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="emprendedores.php?action=delete&id=<?= (int)$r['idemprendedores'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'emprendedores.php?search='.urlencode($search)) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>