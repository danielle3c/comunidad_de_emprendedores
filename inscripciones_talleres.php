<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Inscripciones en Talleres';
$pdo = getConnection();

$action   = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id       = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$tallerId = filter_input(INPUT_GET, 'taller_id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('inscripciones_talleres.php' . ($tallerId ? "?taller_id=$tallerId" : ''));
    }

    $data = [
        'emprendedores_idemprendedores' => filter_input(INPUT_POST, 'emprendedores_idemprendedores', FILTER_VALIDATE_INT) ?: 0,
        'talleres_idtalleres'            => filter_input(INPUT_POST, 'talleres_idtalleres', FILTER_VALIDATE_INT) ?: 0,
        'asistio'                        => filter_input(INPUT_POST, 'asistio', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        'calificacion'                   => filter_input(INPUT_POST, 'calificacion', FILTER_VALIDATE_INT) ?: null,
        'comentarios'                    => sanitize(filter_input(INPUT_POST, 'comentarios', FILTER_SANITIZE_STRING) ?? ''),
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sql = "UPDATE inscripciones_talleres SET 
                    emprendedores_idemprendedores = :emprendedores_idemprendedores,
                    talleres_idtalleres = :talleres_idtalleres,
                    asistio = :asistio,
                    calificacion = :calificacion,
                    comentarios = :comentarios
                    WHERE idinscripcion = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Inscripción actualizada.');
        } else {
            $sql = "INSERT INTO inscripciones_talleres 
                    (emprendedores_idemprendedores, talleres_idtalleres, asistio, calificacion, comentarios)
                    VALUES 
                    (:emprendedores_idemprendedores, :talleres_idtalleres, :asistio, :calificacion, :comentarios)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            
            // Reducir cupo disponible
            $pdo->prepare("UPDATE talleres SET cupo_disponible = GREATEST(0, cupo_disponible - 1) WHERE idtalleres = ?")->execute([$data['talleres_idtalleres']]);
            setFlash('success', 'Inscripción registrada.');
        }
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Este emprendedor ya está inscrito en ese taller.' : 'Error: ' . $e->getMessage();
        setFlash('error', $msg);
        error_log("Error en inscripciones_talleres.php: " . $e->getMessage());
    }
    redirect('inscripciones_talleres.php' . ($tallerId ? "?taller_id=$tallerId" : ''));
}

if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT talleres_idtalleres FROM inscripciones_talleres WHERE idinscripcion = ?");
        $stmt->execute([$id]);
        $ins = $stmt->fetch();
        
        $pdo->prepare("DELETE FROM inscripciones_talleres WHERE idinscripcion = ?")->execute([$id]);
        
        if ($ins) {
            $pdo->prepare("UPDATE talleres SET cupo_disponible = cupo_disponible + 1 WHERE idtalleres = ?")->execute([$ins['talleres_idtalleres']]);
        }
        setFlash('success', 'Inscripción eliminada.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en inscripciones_talleres.php (delete): " . $e->getMessage());
    }
    redirect('inscripciones_talleres.php' . ($tallerId ? "?taller_id=$tallerId" : ''));
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM inscripciones_talleres WHERE idinscripcion = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Inscripción no encontrada.');
        redirect('inscripciones_talleres.php' . ($tallerId ? "?taller_id=$tallerId" : ''));
    }
}

$emprendedores = $pdo->query("SELECT e.idemprendedores, CONCAT(p.nombres,' ',p.apellidos,' - ',p.rut) AS label
    FROM emprendedores e 
    JOIN personas p ON e.personas_idpersonas = p.idpersonas 
    WHERE e.estado = 1 
    ORDER BY p.nombres")->fetchAll();

$talleres = $pdo->query("SELECT idtalleres, CONCAT(nombre_taller,' (',fecha_taller,')') AS label 
    FROM talleres 
    WHERE estado IN ('Programado', 'En Curso') 
    ORDER BY fecha_taller")->fetchAll();

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$conditions = [];

if ($tallerId) {
    $conditions[] = "i.talleres_idtalleres = :tid";
    $params[':tid'] = $tallerId;
}
if ($search) {
    $conditions[] = "(p.nombres LIKE :search OR p.apellidos LIKE :search OR t.nombre_taller LIKE :search)";
    $params[':search'] = "%$search%";
}
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$base = "FROM inscripciones_talleres i 
         JOIN emprendedores e ON i.emprendedores_idemprendedores = e.idemprendedores 
         JOIN personas p ON e.personas_idpersonas = p.idpersonas 
         JOIN talleres t ON i.talleres_idtalleres = t.idtalleres $where";

$totalStmt = $pdo->prepare("SELECT COUNT(*) $base");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT i.*, CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona, t.nombre_taller, t.fecha_taller 
        $base ORDER BY i.fecha_inscripcion DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$tallerInfo = null;
if ($tallerId) {
    $stmt = $pdo->prepare("SELECT * FROM talleres WHERE idtalleres = ?");
    $stmt->execute([$tallerId]);
    $tallerInfo = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($tallerInfo): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center py-2">
    <span><strong><?= htmlspecialchars($tallerInfo['nombre_taller']) ?></strong> | <?= formatDate($tallerInfo['fecha_taller']) ?> | Cupo: <?= (int)$tallerInfo['cupo_disponible'] ?>/<?= (int)$tallerInfo['cupo_maximo'] ?> | <?= badgeEstado($tallerInfo['estado']) ?></span>
    <a href="inscripciones_talleres.php" class="btn btn-sm btn-outline-secondary">Ver todos</a>
</div>
<?php endif; ?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Inscripción' : 'Nueva Inscripción' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idinscripcion'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Emprendedor *</label>
                <select name="emprendedores_idemprendedores" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($emprendedores as $e): ?>
                    <option value="<?= (int)$e['idemprendedores'] ?>" <?= ($edit['emprendedores_idemprendedores'] ?? '') == $e['idemprendedores'] ? 'selected' : '' ?>><?= htmlspecialchars($e['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Taller *</label>
                <select name="talleres_idtalleres" class="form-select form-select-sm" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($talleres as $t): ?>
                    <option value="<?= (int)$t['idtalleres'] ?>" <?= ($edit['talleres_idtalleres'] ?? $tallerId) == $t['idtalleres'] ? 'selected' : '' ?>><?= htmlspecialchars($t['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Calificación (1-5)</label>
                <input type="number" min="1" max="5" name="calificacion" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['calificacion'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="asistio" class="form-check-input" id="asistio" <?= ($edit['asistio'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="asistio">Asistió</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Comentarios</label>
                <textarea name="comentarios" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($edit['comentarios'] ?? '') ?></textarea>
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
            <form class="d-flex gap-2" method="GET">
                <?php if ($tallerId): ?><input type="hidden" name="taller_id" value="<?= (int)$tallerId ?>"><?php endif; ?>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="inscripciones_talleres.php?action=create<?= $tallerId ? "&taller_id=$tallerId" : '' ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nueva</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Emprendedor</th><th>Taller</th><th>Fecha Taller</th><th>Inscripción</th><th>Asistió</th><th>Calificación</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idinscripcion'] ?></td>
            <td><?= htmlspecialchars($r['nombre_persona']) ?></td>
            <td><?= htmlspecialchars($r['nombre_taller']) ?></td>
            <td><?= formatDate($r['fecha_taller']) ?></td>
            <td><?= formatDateTime($r['fecha_inscripcion']) ?></td>
            <td><?= $r['asistio'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
            <td><?= $r['calificacion'] ? '⭐ ' . (int)$r['calificacion'] : '-' ?></td>
            <td>
                <a href="inscripciones_talleres.php?action=edit&id=<?= (int)$r['idinscripcion'] ?><?= $tallerId ? "&taller_id=$tallerId" : '' ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="inscripciones_talleres.php?action=delete&id=<?= (int)$r['idinscripcion'] ?><?= $tallerId ? "&taller_id=$tallerId" : '' ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin inscripciones</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'inscripciones_talleres.php?search='.urlencode($search).($tallerId ? "&taller_id=$tallerId" : '')) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>