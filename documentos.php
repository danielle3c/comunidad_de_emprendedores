<?php
require_once __DIR__ . '/includes/helpers.php';
$pageTitle = 'Documentos';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

define('DEFAULT_EMPRENDEDOR_ID', 1);

define('UPLOAD_DIR', __DIR__ . '/uploads/documentos/');
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rutaArchivo = '';
    $tamano_kb = 0;

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {

        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','txt','csv'];

        if (!in_array($ext, $allowed)) {
            setFlash('error','Tipo de archivo no permitido.');
            redirect('documentos.php');
        }

        $nombre_unico = uniqid('doc_', true) . '.' . $ext;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], UPLOAD_DIR . $nombre_unico)) {
            $rutaArchivo = 'uploads/documentos/' . $nombre_unico;
            $tamano_kb = ceil($_FILES['archivo']['size'] / 1024);
        }
    }

    $data = [
        'emprendedores_idemprendedores' => DEFAULT_EMPRENDEDOR_ID,
        'nombre_documento' => sanitize($_POST['nombre_documento'] ?? ''),
        'tipo_documento'   => sanitize($_POST['tipo_documento'] ?? ''),
        'descripcion'      => sanitize($_POST['descripcion'] ?? ''),
    ];

    try {

        if (!empty($_POST['id'])) {

            $data['ruta_archivo'] = $rutaArchivo ?: sanitize($_POST['ruta_actual'] ?? '');
            $data['tamano_kb']    = $rutaArchivo ? $tamano_kb : (int)($_POST['tamano_actual'] ?? 0);
            $data['id']           = (int)$_POST['id'];

            $sql = "UPDATE documentos
                    SET emprendedores_idemprendedores=:emprendedores_idemprendedores,
                        nombre_documento=:nombre_documento,
                        tipo_documento=:tipo_documento,
                        ruta_archivo=:ruta_archivo,
                        tamano_kb=:tamano_kb,
                        descripcion=:descripcion
                    WHERE iddocumentos=:id";

            $pdo->prepare($sql)->execute($data);

            setFlash('success','Documento actualizado.');

        } else {

            if (!$rutaArchivo) {
                setFlash('error','Seleccione un archivo.');
                redirect('documentos.php?action=create');
            }

            $data['ruta_archivo'] = $rutaArchivo;
            $data['tamano_kb']    = $tamano_kb;

            $sql = "INSERT INTO documentos
                    (emprendedores_idemprendedores,nombre_documento,tipo_documento,ruta_archivo,tamano_kb,descripcion)
                    VALUES
                    (:emprendedores_idemprendedores,:nombre_documento,:tipo_documento,:ruta_archivo,:tamano_kb,:descripcion)";

            $pdo->prepare($sql)->execute($data);

            setFlash('success','Documento subido.');
        }

    } catch (PDOException $e) {
        setFlash('error','Error: '.$e->getMessage());
    }

    redirect('documentos.php');
}

if ($action === 'delete' && $id) {
    try {

        $s = $pdo->prepare("SELECT ruta_archivo FROM documentos WHERE iddocumentos=?");
        $s->execute([$id]);
        $doc = $s->fetch();

        $pdo->prepare("DELETE FROM documentos WHERE iddocumentos=?")->execute([$id]);

        if ($doc && file_exists(__DIR__.'/'.$doc['ruta_archivo'])) {
            unlink(__DIR__.'/'.$doc['ruta_archivo']);
        }

        setFlash('success','Documento eliminado.');

    } catch (PDOException $e) {
        setFlash('error','Error: '.$e->getMessage());
    }

    redirect('documentos.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM documentos WHERE iddocumentos=?");
    $s->execute([$id]);
    $edit = $s->fetch();
}

$rows = $pdo->query("SELECT * FROM documentos ORDER BY fecha_subida DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-header bg-white border-0 fw-semibold">
        Documentos
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
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int)$r['iddocumentos'] ?></td>
                        <td><?= sanitize($r['nombre_documento']) ?></td>
                        <td><?= sanitize($r['tipo_documento']) ?></td>
                        <td><?= $r['tamano_kb'] ? $r['tamano_kb'].' KB' : '-' ?></td>
                        <td><?= formatDateTime($r['fecha_subida']) ?></td>
                        <td>
                            <a href="documentos.php?action=edit&id=<?= $r['iddocumentos'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="documentos.php?action=delete&id=<?= $r['iddocumentos'] ?>" 
                               onclick="return confirm('¿Eliminar este documento?')" 
                               class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Sin documentos</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
