<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Encuestas 2026';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('encuestas.php');
    }

    $data = [
        'emprendedores_idemprendedores' => filter_input(INPUT_POST, 'emprendedores_idemprendedores', FILTER_VALIDATE_INT) ?: null,
        'pregunta_1'   => sanitize(filter_input(INPUT_POST, 'pregunta_1', FILTER_SANITIZE_STRING) ?? ''),
        'respuesta_1'  => sanitize(filter_input(INPUT_POST, 'respuesta_1', FILTER_SANITIZE_STRING) ?? ''),
        'pregunta_2'   => sanitize(filter_input(INPUT_POST, 'pregunta_2', FILTER_SANITIZE_STRING) ?? ''),
        'respuesta_2'  => sanitize(filter_input(INPUT_POST, 'respuesta_2', FILTER_SANITIZE_STRING) ?? ''),
        'pregunta_3'   => sanitize(filter_input(INPUT_POST, 'pregunta_3', FILTER_SANITIZE_STRING) ?? ''),
        'respuesta_3'  => sanitize(filter_input(INPUT_POST, 'respuesta_3', FILTER_SANITIZE_STRING) ?? ''),
        'pregunta_4'   => sanitize(filter_input(INPUT_POST, 'pregunta_4', FILTER_SANITIZE_STRING) ?? ''),
        'respuesta_4'  => sanitize(filter_input(INPUT_POST, 'respuesta_4', FILTER_SANITIZE_STRING) ?? ''),
        'pregunta_5'   => sanitize(filter_input(INPUT_POST, 'pregunta_5', FILTER_SANITIZE_STRING) ?? ''),
        'respuesta_5'  => sanitize(filter_input(INPUT_POST, 'respuesta_5', FILTER_SANITIZE_STRING) ?? ''),
        'observaciones'=> sanitize(filter_input(INPUT_POST, 'observaciones', FILTER_SANITIZE_STRING) ?? ''),
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sets = [];
            foreach ($data as $col => $val) {
                $sets[] = "$col = :$col";
            }
            $sql = "UPDATE encuesta_2026 SET " . implode(', ', $sets) . " WHERE idencuesta = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Encuesta actualizada.');
        } else {
            $cols = implode(', ', array_keys($data));
            $vals = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO encuesta_2026 ($cols) VALUES ($vals)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Encuesta registrada.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
        error_log("Error en encuestas.php: " . $e->getMessage());
    }
    redirect('encuestas.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM encuesta_2026 WHERE idencuesta = ?")->execute([$id]);
        setFlash('success', 'Encuesta eliminada.');
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
        error_log("Error en encuestas.php (delete): " . $e->getMessage());
    }
    redirect('encuestas.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM encuesta_2026 WHERE idencuesta = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Encuesta no encontrada.');
        redirect('encuestas.php');
    }
}

$emprendedores = $pdo->query("SELECT e.idemprendedores, CONCAT(p.nombres,' ',p.apellidos,' - ',p.rut) AS label
    FROM emprendedores e 
    JOIN personas p ON e.personas_idpersonas = p.idpersonas 
    WHERE e.estado = 1 
    ORDER BY p.nombres")->fetchAll();

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$conditions = [];

if ($search) {
    $conditions[] = "(p.nombres LIKE :search OR p.apellidos LIKE :search OR enc.observaciones LIKE :search)";
    $params[':search'] = "%$search%";
}
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$base = "FROM encuesta_2026 enc 
         LEFT JOIN emprendedores e ON enc.emprendedores_idemprendedores = e.idemprendedores 
         LEFT JOIN personas p ON e.personas_idpersonas = p.idpersonas $where";

$totalStmt = $pdo->prepare("SELECT COUNT(*) $base");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT enc.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona $base ORDER BY enc.fecha_respuesta DESC LIMIT :limit OFFSET :offset";
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
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Encuesta' : 'Nueva Encuesta' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idencuesta'] ?? '') ?>">
        <div class="row g-3 mb-3">
            <div class="col-md-5">
                <label class="form-label">Emprendedor</label>
                <select name="emprendedores_idemprendedores" class="form-select form-select-sm">
                    <option value="">-- Opcional --</option>
                    <?php foreach ($emprendedores as $e): ?>
                    <option value="<?= (int)$e['idemprendedores'] ?>" <?= ($edit['emprendedores_idemprendedores'] ?? '') == $e['idemprendedores'] ? 'selected' : '' ?>><?= htmlspecialchars($e['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="row g-2 mb-2">
            <div class="col-md-5">
                <label class="form-label text-muted" style="font-size:.78rem">Pregunta <?= $i ?></label>
                <input type="text" name="pregunta_<?= $i ?>" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit["pregunta_$i"] ?? '') ?>" placeholder="Pregunta <?= $i ?>...">
            </div>
            <div class="col-md-7">
                <label class="form-label text-muted" style="font-size:.78rem">Respuesta <?= $i ?></label>
                <textarea name="respuesta_<?= $i ?>" class="form-control form-control-sm" rows="1" placeholder="Respuesta..."><?= htmlspecialchars($edit["respuesta_$i"] ?? '') ?></textarea>
            </div>
        </div>
        <?php endfor; ?>
        <div class="row g-3 mt-1">
            <div class="col-12">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($edit['observaciones'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="encuestas.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Encuestas 2026 <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="encuestas.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nueva</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Respuesta 1</th><th>Respuesta 2</th><th>Respuesta 3</th><th>Observaciones</th><th>Fecha</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idencuesta'] ?></td>
            <td><?= $r['nombre_persona'] ? htmlspecialchars($r['nombre_persona']) : '<span class="text-muted">-</span>' ?></td>
            <td><?= mb_strimwidth(htmlspecialchars($r['respuesta_1'] ?? ''), 0, 35, '...') ?></td>
            <td><?= mb_strimwidth(htmlspecialchars($r['respuesta_2'] ?? ''), 0, 35, '...') ?></td>
            <td><?= mb_strimwidth(htmlspecialchars($r['respuesta_3'] ?? ''), 0, 35, '...') ?></td>
            <td><?= mb_strimwidth(htmlspecialchars($r['observaciones'] ?? ''), 0, 35, '...') ?></td>
            <td><?= formatDateTime($r['fecha_respuesta']) ?></td>
            <td>
                <a href="encuestas.php?action=edit&id=<?= (int)$r['idencuesta'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="encuestas.php?action=delete&id=<?= (int)$r['idencuesta'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin encuestas</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'encuestas.php?search='.urlencode($search)) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>