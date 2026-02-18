<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Talleres';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('talleres.php');
    }

    $cupoMax = filter_input(INPUT_POST, 'cupo_maximo', FILTER_VALIDATE_INT) ?: 0;
    $data = [
        'nombre_taller'   => sanitize(filter_input(INPUT_POST, 'nombre_taller', FILTER_SANITIZE_STRING) ?? ''),
        'descripcion'     => sanitize(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING) ?? ''),
        'fecha_taller'    => filter_input(INPUT_POST, 'fecha_taller', FILTER_SANITIZE_STRING) ?? '',
        'hora_inicio'     => filter_input(INPUT_POST, 'hora_inicio', FILTER_SANITIZE_STRING) ?: null,
        'hora_fin'        => filter_input(INPUT_POST, 'hora_fin', FILTER_SANITIZE_STRING) ?: null,
        'lugar'           => sanitize(filter_input(INPUT_POST, 'lugar', FILTER_SANITIZE_STRING) ?? ''),
        'cupo_maximo'     => $cupoMax,
        'cupo_disponible' => filter_input(INPUT_POST, 'cupo_disponible', FILTER_VALIDATE_INT) ?: $cupoMax,
        'instructor'      => sanitize(filter_input(INPUT_POST, 'instructor', FILTER_SANITIZE_STRING) ?? ''),
        'categoria'       => sanitize(filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_STRING) ?? ''),
        'estado'          => filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING) ?? 'Programado',
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sql = "UPDATE talleres SET 
                    nombre_taller = :nombre_taller,
                    descripcion = :descripcion,
                    fecha_taller = :fecha_taller,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    lugar = :lugar,
                    cupo_maximo = :cupo_maximo,
                    cupo_disponible = :cupo_disponible,
                    instructor = :instructor,
                    categoria = :categoria,
                    estado = :estado
                    WHERE idtalleres = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Taller actualizado.');
        } else {
            $sql = "INSERT INTO talleres 
                    (nombre_taller, descripcion, fecha_taller, hora_inicio, hora_fin, lugar, cupo_maximo, cupo_disponible, instructor, categoria, estado)
                    VALUES 
                    (:nombre_taller, :descripcion, :fecha_taller, :hora_inicio, :hora_fin, :lugar, :cupo_maximo, :cupo_disponible, :instructor, :categoria, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Taller creado.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
        error_log("Error en talleres.php: " . $e->getMessage());
    }
    redirect('talleres.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM talleres WHERE idtalleres = ?")->execute([$id]);
        setFlash('success', 'Taller eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en talleres.php (delete): " . $e->getMessage());
    }
    redirect('talleres.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM talleres WHERE idtalleres = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Taller no encontrado.');
        redirect('talleres.php');
    }
}

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$filtroEstado = sanitize(filter_input(INPUT_GET, 'estado', FILTER_SANITIZE_STRING) ?? '');
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$conditions = [];

if ($search) {
    $conditions[] = "(nombre_taller LIKE :search OR instructor LIKE :search OR lugar LIKE :search OR categoria LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($filtroEstado) {
    $conditions[] = "estado = :estado";
    $params[':estado'] = $filtroEstado;
}
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM talleres $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT * FROM talleres $where ORDER BY fecha_taller DESC LIMIT :limit OFFSET :offset";
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
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Taller' : 'Nuevo Taller' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idtalleres'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Nombre del Taller *</label>
                <input type="text" name="nombre_taller" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['nombre_taller'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Instructor</label>
                <input type="text" name="instructor" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['instructor'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Categoría</label>
                <input type="text" name="categoria" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['categoria'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <?php foreach (['Programado', 'En Curso', 'Finalizado', 'Cancelado'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($edit['estado'] ?? 'Programado') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha_taller" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['fecha_taller'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['hora_inicio'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['hora_fin'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Lugar</label>
                <input type="text" name="lugar" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['lugar'] ?? '') ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label">Cupo Máx.</label>
                <input type="number" name="cupo_maximo" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['cupo_maximo'] ?? '0') ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label">Disponible</label>
                <input type="number" name="cupo_disponible" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['cupo_disponible'] ?? '0') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($edit['descripcion'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="talleres.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Talleres <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <select name="estado" class="form-select form-select-sm" style="width:130px">
                    <option value="">Todos</option>
                    <?php foreach (['Programado', 'En Curso', 'Finalizado', 'Cancelado'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $filtroEstado === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="talleres.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Nombre</th><th>Instructor</th><th>Categoría</th><th>Fecha</th><th>Horario</th><th>Lugar</th><th>Cupo</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idtalleres'] ?></td>
            <td><?= htmlspecialchars($r['nombre_taller']) ?></td>
            <td><?= htmlspecialchars($r['instructor']) ?: '-' ?></td>
            <td><?= htmlspecialchars($r['categoria']) ?: '-' ?></td>
            <td><?= formatDate($r['fecha_taller']) ?></td>
            <td><?= $r['hora_inicio'] ? substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fin'], 0, 5) : '-' ?></td>
            <td><?= htmlspecialchars($r['lugar']) ?: '-' ?></td>
            <td><?= (int)$r['cupo_disponible'] ?>/<?= (int)$r['cupo_maximo'] ?></td>
            <td><?= badgeEstado($r['estado']) ?></td>
            <td>
                <a href="inscripciones_talleres.php?taller_id=<?= (int)$r['idtalleres'] ?>" class="btn btn-sm btn-outline-success btn-action" title="Inscripciones"><i class="bi bi-person-plus"></i></a>
                <a href="talleres.php?action=edit&id=<?= (int)$r['idtalleres'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="talleres.php?action=delete&id=<?= (int)$r['idtalleres'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="10" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'talleres.php?search='.urlencode($search).'&estado='.urlencode($filtroEstado)) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>