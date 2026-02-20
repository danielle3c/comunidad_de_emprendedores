<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pageTitle = 'Carritos / Puestos';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre_responsable'   => sanitize($_POST['nombre_responsable']),
        'telefono_responsable' => sanitize($_POST['telefono_responsable'] ?? ''),
        'nombre_carrito'       => sanitize($_POST['nombre_carrito']),
        'descripcion'          => sanitize($_POST['descripcion'] ?? ''),
        'equipamiento'         => sanitize($_POST['equipamiento'] ?? ''),
        'asistencia'           => $_POST['asistencia'] ?? 'Pendiente',
        'hora_salida'          => $_POST['hora_salida'] ?: null,
        'fecha_registro'       => $_POST['fecha_registro'] ?: null,
        'estado'               => isset($_POST['estado']) ? 1 : 0,
    ];
    try {
        if ($_POST['id']) {
            $sql = "UPDATE carritos SET nombre_responsable=:nombre_responsable,
                    nombre_carrito=:nombre_carrito,descripcion=:descripcion,
                    asistencia=:asistencia,hora_salida=:hora_salida,fecha_registro=:fecha_registro,estado=:estado WHERE idcarritos=:id";
            $pdo->prepare($sql)->execute(array_merge($data,[':id'=>(int)$_POST['id']]));
            setFlash('success','Carrito actualizado.');
        } else {
            $sql = "INSERT INTO carritos (nombre_responsable,telefono_responsable,nombre_carrito,descripcion,equipamiento,asistencia,hora_salida,fecha_registro,estado)
                    VALUES (:nombre_responsable,:telefono_responsable,:nombre_carrito,:descripcion,:equipamiento,:asistencia,:hora_salida,:fecha_registro,:estado)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success','Carrito registrado.');
        }
    } catch (PDOException $e) { setFlash('error','Error: '.$e->getMessage()); }
    redirect('carritos.php');
}

if ($action === 'delete' && $id) {
    try { $pdo->prepare("DELETE FROM carritos WHERE idcarritos=?")->execute([$id]); setFlash('success','Carrito eliminado.'); }
    catch (PDOException $e) { setFlash('error','No se puede eliminar: '.$e->getMessage()); }
    redirect('carritos.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM carritos WHERE idcarritos=?"); $s->execute([$id]); $edit = $s->fetch();
}

$search = sanitize($_GET['search'] ?? '');
$filtroAsistencia = sanitize($_GET['asistencia'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1)); $perPage = 15;
$conditions = []; $params = [];
if ($search) { $conditions[] = "(nombre_responsable LIKE :s OR nombre_carrito LIKE :s)"; $params[':s'] = "%$search%"; }
if ($filtroAsistencia) { $conditions[] = "asistencia=:asistencia"; $params[':asistencia'] = $filtroAsistencia; }
$where = $conditions ? "WHERE ".implode(' AND ',$conditions) : '';
$tc = $pdo->prepare("SELECT COUNT(*) FROM carritos $where"); $tc->execute($params); $total = (int)$tc->fetchColumn();
$pag = getPaginationData($total,$page,$perPage);
$stmt = $pdo->prepare("SELECT * FROM carritos $where ORDER BY fecha_registro DESC, idcarritos DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$pag['perPage'],PDO::PARAM_INT);
$stmt->bindValue(':offset',$pag['offset'],PDO::PARAM_INT);
$stmt->execute(); $rows = $stmt->fetchAll();

include 'includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Carrito' : 'Nuevo Carrito' ?></div>
    <div class="card-body">
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['idcarritos'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nombre Responsable *</label>
                <input type="text" name="nombre_responsable" class="form-control form-control-sm" value="<?= $edit['nombre_responsable'] ?? '' ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono Responsable</label>
                <input type="text" name="telefono_responsable" class="form-control form-control-sm" value="<?= $edit['telefono_responsable'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombre del Carrito *</label>
                <input type="text" name="nombre_carrito" class="form-control form-control-sm" value="<?= $edit['nombre_carrito'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Asistencia</label>
                <select name="asistencia" class="form-select form-select-sm">
                    <?php foreach (['Pendiente','Confirmada','Cancelada'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($edit['asistencia'] ?? 'Pendiente') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Hora Salida</label>
                <input type="time" name="hora_salida" class="form-control form-control-sm" value="<?= $edit['hora_salida'] ?? '' ?>">
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
            <div class="col-md-5">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="2"><?= $edit['descripcion'] ?? '' ?></textarea>
            </div>
            <div class="col-md-5">
                <label class="form-label">Equipamiento</label>
                <textarea name="equipamiento" class="form-control form-control-sm" rows="2"><?= $edit['equipamiento'] ?? '' ?></textarea>
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
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= $search ?>">
                <select name="asistencia" class="form-select form-select-sm" style="width:130px">
                    <option value="">Todos</option>
                    <?php foreach (['Pendiente','Confirmada','Cancelada'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $filtroAsistencia===$opt?'selected':'' ?>><?= $opt ?></option>
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
            <td><?= $r['idcarritos'] ?></td>
            <td><?= sanitize($r['nombre_carrito']) ?></td>
            <td><?= sanitize($r['nombre_responsable']) ?></td>
            <td><?= $r['telefono_responsable'] ?: '-' ?></td>
            <td><?= badgeEstado($r['asistencia']) ?></td>
            <td><?= $r['hora_salida'] ? substr($r['hora_salida'],0,5) : '-' ?></td>
            <td><?= formatDate($r['fecha_registro']) ?></td>
            <td><?= badgeEstado((string)$r['estado']) ?></td>
            <td>
                <a href="carritos.php?action=edit&id=<?= $r['idcarritos'] ?>" class="btn btn-sm btn-outline-primary btn-action"><i class="bi bi-pencil"></i></a>
                <a href="carritos.php?action=delete&id=<?= $r['idcarritos'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Sin registros</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>
    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0"><?= renderPagination($pag,'carritos.php?search='.urlencode($search).'&asistencia='.urlencode($filtroAsistencia)) ?></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
