<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Carritos / Puestos';
$pdo = getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('carritos.php');
    }

    $data = [
        'nombre_responsable'   => sanitize(filter_input(INPUT_POST, 'nombre_responsable', FILTER_SANITIZE_STRING) ?? ''),
        'telefono_responsable' => sanitize(filter_input(INPUT_POST, 'telefono_responsable', FILTER_SANITIZE_STRING) ?? ''),
        'nombre_carrito'       => sanitize(filter_input(INPUT_POST, 'nombre_carrito', FILTER_SANITIZE_STRING) ?? ''),
        'descripcion'          => sanitize(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING) ?? ''),
        'equipamiento'         => sanitize(filter_input(INPUT_POST, 'equipamiento', FILTER_SANITIZE_STRING) ?? ''),
        'asistencia'           => filter_input(INPUT_POST, 'asistencia', FILTER_SANITIZE_STRING) ?? 'Pendiente',
        'hora_salida'          => filter_input(INPUT_POST, 'hora_salida', FILTER_SANITIZE_STRING) ?: null,
        'fecha_registro'       => filter_input(INPUT_POST, 'fecha_registro', FILTER_SANITIZE_STRING) ?: null,
        'estado'               => filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
    ];
    
    $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($postId) {
            $sql = "UPDATE carritos SET 
                    nombre_responsable = :nombre_responsable,
                    telefono_responsable = :telefono_responsable,
                    nombre_carrito = :nombre_carrito,
                    descripcion = :descripcion,
                    equipamiento = :equipamiento,
                    asistencia = :asistencia,
                    hora_salida = :hora_salida,
                    fecha_registro = :fecha_registro,
                    estado = :estado
                    WHERE idcarritos = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $postId;
            $stmt->execute($data);
            setFlash('success', 'Carrito actualizado.');
        } else {
            $sql = "INSERT INTO carritos 
                    (nombre_responsable, telefono_responsable, nombre_carrito, descripcion, equipamiento, asistencia, hora_salida, fecha_registro, estado)
                    VALUES 
                    (:nombre_responsable, :telefono_responsable, :nombre_carrito, :descripcion, :equipamiento, :asistencia, :hora_salida, :fecha_registro, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Carrito registrado.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
        error_log("Error en carritos.php: " . $e->getMessage());
    }
    redirect('carritos.php');
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM carritos WHERE idcarritos = ?")->execute([$id]);
        setFlash('success', 'Carrito eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
        error_log("Error en carritos.php (delete): " . $e->getMessage());
    }
    redirect('carritos.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM carritos WHERE idcarritos = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        setFlash('error', 'Carrito no encontrado.');
        redirect('carritos.php');
    }
}

$search = sanitize(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '');
$filtroAsistencia = sanitize(filter_input(INPUT_GET, 'asistencia', FILTER_SANITIZE_STRING) ?? '');
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 15;

$params = [];
$conditions = [];

if ($search) {
    $conditions[] = "(nombre_responsable LIKE :search OR nombre_carrito LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($filtroAsistencia) {
    $conditions[] = "asistencia = :asistencia";
    $params[':asistencia'] = $filtroAsistencia;
}
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM carritos $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$sql = "SELECT * FROM carritos $where ORDER BY fecha_registro DESC, idcarritos DESC LIMIT :limit OFFSET :offset";
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
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Carrito' : 'Nuevo Carrito' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['idcarritos'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nombre Responsable *</label>
                <input type="text" name="nombre_responsable" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['nombre_responsable'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono Responsable</label>
                <input type="text" name="telefono_responsable" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['telefono_responsable'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombre del Carrito *</label>
                <input type="text" name="nombre_carrito" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['nombre_carrito'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Asistencia</label>
                <select name="asistencia" class="form-select form-select-sm">
                    <?php foreach (['Pendiente', 'Confirmada', 'Cancelada'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($edit['asistencia'] ?? 'Pendiente') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Hora Salida</label>
                <input type="time" name="hora_salida" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($edit['hora_salida'] ?? '') ?>">
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
            <div class="col-md-5">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($edit['descripcion'] ?? '') ?></textarea>
            </div>
            <div class="col-md-5">
                <label class="form-label">Equipamiento</label>
                <textarea name="equipamiento" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($edit['equipamiento'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="carritos.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Carritos / Puestos <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <select name="asistencia" class="form-select form-select-sm" style="width:130px">
                    <option value="">Todos</option>
                    <?php foreach (['Pendiente', 'Confirmada', 'Cancelada'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $filtroAsistencia === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="carritos.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Nuevo</a>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Nombre Carrito</th><th>Responsable</th><th>Teléfono</th><th>Asistencia</th><th>Hora Salida</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['idcarritos'] ?></td>
            <td><?= htmlspecialchars($r['nombre_carrito']) ?></td>
            <td><?= htmlspecialchars($r['nombre_responsable']) ?></td>
            <td><?= $r['telefono_responsable'] ?: '-' ?></td>
            <td><?= badgeEstado($r['asistencia']) ?></td>
            <td><?= $r['hora_salida'] ? substr($r['hora_salida'], 0, 5) : '-' ?></td>
            <td><?= formatDate($r['fecha_registro']) ?></td>
            <td><?= badgeEstado((string)$r['estado']) ?></td>
            <td>
                <a href="carritos.php?action=edit&id=<?= (int)$r['idcarritos'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="carritos.php?action=delete&id=<?= (int)$r['idcarritos'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag, 'carritos.php?search='.urlencode($search).'&asistencia='.urlencode($filtroAsistencia)) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>