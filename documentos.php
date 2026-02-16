<?php
require_once __DIR__ . '/includes/helpers.php';
$pageTitle = 'Documentos';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ✅ Emprendedor fijo (cambiar si corresponde)
define('DEFAULT_EMPRENDEDOR_ID', 1);

// Directorio para uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/documentos/');
if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);

// ======================
// GUARDAR (CREAR/EDITAR)
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rutaArchivo = '';
    $tamano_kb = 0;

    // Manejar upload de archivo
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','txt','csv'];

        if (!in_array($ext, $allowed, true)) {
            setFlash('error','Tipo de archivo no permitido.');
            redirect('documentos.php');
        }

        $nombre_unico = uniqid('doc_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], UPLOAD_DIR . $nombre_unico)) {
            $rutaArchivo = 'uploads/documentos/' . $nombre_unico;
            $tamano_kb = (int)ceil($_FILES['archivo']['size'] / 1024);
        }
    }

    // ✅ Ya no pedimos emprendedor: usamos uno fijo
    $data = [
        'emprendedores_idemprendedores' => DEFAULT_EMPRENDEDOR_ID,
        'nombre_documento' => sanitize($_POST['nombre_documento'] ?? ''),
        'tipo_documento'   => sanitize($_POST['tipo_documento'] ?? ''),
        'descripcion'      => sanitize($_POST['descripcion'] ?? ''),
    ];

    try {
        if (!empty($_POST['id'])) {
            $data['ruta_archivo'] = $rutaArchivo ?: sanitize($_POST['ruta_actual'] ?? '');
            $data['tamaño_kb']    = $rutaArchivo ? $tamano_kb : (int)($_POST['tamano_actual'] ?? 0);
            $data['id']           = (int)$_POST['id'];

            $sql = "UPDATE documentos
                    SET emprendedores_idemprendedores=:emprendedores_idemprendedores,
                        nombre_documento=:nombre_documento,
                        tipo_documento=:tipo_documento,
                        ruta_archivo=:ruta_archivo,
                        `tamaño_kb`=:tamaño_kb,
                        descripcion=:descripcion
                    WHERE iddocumentos=:id";

            $pdo->prepare($sql)->execute($data);
            setFlash('success','Documento actualizado.');
        } else {
            if (!$rutaArchivo) {
                setFlash('error','Por favor seleccione un archivo.');
                redirect('documentos.php?action=create');
            }

            $data['ruta_archivo'] = $rutaArchivo;
            $data['tamaño_kb']    = $tamano_kb;

            $sql = "INSERT INTO documentos
                    (emprendedores_idemprendedores,nombre_documento,tipo_documento,ruta_archivo,`tamaño_kb`,descripcion)
                    VALUES
                    (:emprendedores_idemprendedores,:nombre_documento,:tipo_documento,:ruta_archivo,:tamaño_kb,:descripcion)";

            $pdo->prepare($sql)->execute($data);
            setFlash('success','Documento subido.');
        }
    } catch (PDOException $e) {
        setFlash('error','Error: '.$e->getMessage());
    }

    redirect('documentos.php');
}

// ==========
// ELIMINAR
// ==========
if ($action === 'delete' && $id) {
    try {
        $s = $pdo->prepare("SELECT ruta_archivo FROM documentos WHERE iddocumentos=?");
        $s->execute([$id]);
        $doc = $s->fetch();

        $pdo->prepare("DELETE FROM documentos WHERE iddocumentos=?")->execute([$id]);

        if ($doc && !empty($doc['ruta_archivo']) && file_exists(__DIR__.'/'.$doc['ruta_archivo'])) {
            @unlink(__DIR__.'/'.$doc['ruta_archivo']);
        }

        setFlash('success','Documento eliminado.');
    } catch (PDOException $e) {
        setFlash('error','Error: '.$e->getMessage());
    }
    redirect('documentos.php');
}

// ==========
// EDITAR
// ==========
$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM documentos WHERE iddocumentos=?");
    $s->execute([$id]);
    $edit = $s->fetch();
}

// ==================
// LISTAR + FILTROS
// ==================
$search = sanitize($_GET['search'] ?? '');
$filtroTipo = sanitize($_GET['tipo_documento'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(d.nombre_documento LIKE :s1 OR d.descripcion LIKE :s2 OR d.tipo_documento LIKE :s3)";
    $like = "%$search%";
    $params[':s1'] = $like;
    $params[':s2'] = $like;
    $params[':s3'] = $like;
}

if ($filtroTipo) {
    $conditions[] = "d.tipo_documento = :tipo";
    $params[':tipo'] = $filtroTipo;
}

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

$base = "FROM documentos d $where";

$tc = $pdo->prepare("SELECT COUNT(*) $base");
$tc->execute($params);
$total = (int)$tc->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$stmt = $pdo->prepare("SELECT d.* $base
                       ORDER BY d.fecha_subida DESC
                       LIMIT :limit OFFSET :offset");

foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll();

$tipos = $pdo->query("SELECT DISTINCT tipo_documento FROM documentos WHERE tipo_documento IS NOT NULL AND tipo_documento != '' ORDER BY tipo_documento")
            ->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="card mb-4">
    <div class="card-header bg-white border-0 fw-semibold"><?= $edit ? 'Editar Documento' : 'Subir Documento' ?></div>
    <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['iddocumentos'] ?? '' ?>">
        <input type="hidden" name="ruta_actual" value="<?= $edit['ruta_archivo'] ?? '' ?>">
        <input type="hidden" name="tamano_actual" value="<?= $edit['tamaño_kb'] ?? 0 ?>">

        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Nombre del Documento *</label>
                <input type="text" name="nombre_documento" class="form-control form-control-sm" value="<?= $edit['nombre_documento'] ?? '' ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Tipo de Documento</label>
                <input type="text" name="tipo_documento" class="form-control form-control-sm" value="<?= $edit['tipo_documento'] ?? '' ?>" list="tipos_doc" placeholder="Contrato, Foto, etc.">
                <datalist id="tipos_doc">
                    <?php foreach ($tipos as $t): ?><option value="<?= htmlspecialchars($t) ?>"><?php endforeach; ?>
                </datalist>
            </div>

            <div class="col-md-6">
                <label class="form-label">Archivo <?= $edit ? '(dejar vacío para mantener)' : '*' ?></label>
                <input type="file" name="archivo" class="form-control form-control-sm"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt,.csv" <?= !$edit ? 'required' : '' ?>>
                <?php if ($edit && $edit['ruta_archivo']): ?>
                <small class="text-muted">Actual: <?= basename($edit['ruta_archivo']) ?> (<?= $edit['tamaño_kb'] ?> KB)</small>
                <?php endif; ?>
            </div>

            <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="2"><?= $edit['descripcion'] ?? '' ?></textarea>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            <a href="documentos.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Documentos <span class="badge bg-secondary"><?= $total ?></span></span>
        <div class="d-flex gap-2 flex-wrap">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>">
                <select name="tipo_documento" class="form-select form-select-sm" style="width:140px">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($tipos as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= $filtroTipo===$t?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="documentos.php?action=create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Subir</a>
        </div>
    </div>

    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Tamaño</th>
            <th>Fecha Subida</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['iddocumentos'] ?></td>
            <td><?= sanitize($r['nombre_documento']) ?></td>
            <td><?= $r['tipo_documento'] ? '<span class="badge bg-light text-dark">'.sanitize($r['tipo_documento']).'</span>' : '-' ?></td>
            <td><?= $r['tamaño_kb'] ? (int)$r['tamaño_kb'].' KB' : '-' ?></td>
            <td><?= formatDateTime($r['fecha_subida']) ?></td>
            <td>
                <?php if (!empty($r['ruta_archivo']) && file_exists(__DIR__.'/'.$r['ruta_archivo'])): ?>
                <a href="<?= htmlspecialchars($r['ruta_archivo']) ?>" target="_blank" class="btn btn-sm btn-outline-info btn-action" title="Ver/Descargar">
                  <i class="bi bi-download"></i>
                </a>
                <?php endif; ?>
                <a href="documentos.php?action=edit&id=<?= (int)$r['iddocumentos'] ?>" class="btn btn-sm btn-outline-primary btn-action">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="documentos.php?action=delete&id=<?= (int)$r['iddocumentos'] ?>" class="btn btn-sm btn-outline-danger btn-action btn-delete"
                   onclick="return confirm('¿Eliminar este documento?');">
                  <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">Sin documentos</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    </div>

    <?php if ($pag['totalPages'] > 1): ?>
    <div class="card-footer bg-white border-0">
      <?= renderPagination($pag,'documentos.php?search='.urlencode($search).'&tipo_documento='.urlencode($filtroTipo)) ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
