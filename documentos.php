<?php
require_once __DIR__ . '/includes/helpers.php';
$pageTitle = 'Documentos';
$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Emprendedor fijo (debe existir en la tabla emprendedores)
$DEFAULT_EMPRENDEDOR_ID = (int)$pdo->query("SELECT idemprendedores FROM emprendedores ORDER BY idemprendedores ASC LIMIT 1")->fetchColumn();

if ($DEFAULT_EMPRENDEDOR_ID <= 0) {
    setFlash('error', 'No hay emprendedores creados. Cree un emprendedor primero para poder subir documentos.');
    redirect('emprendedores.php?action=create');
}


// Carpeta de uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/documentos/');
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}

// ======================
// GUARDAR (CREAR/EDITAR)
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rutaArchivo = '';
    $tamano_kb = 0;

    // subir archivo (si viene)
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
        } else {
            setFlash('error','No se pudo guardar el archivo en el servidor.');
            redirect('documentos.php');
        }
    }

    $data = [
        'emprendedores_idemprendedores' => DEFAULT_EMPRENDEDOR_ID,
        'nombre_documento' => sanitize($_POST['nombre_documento'] ?? ''),
        'tipo_documento'   => sanitize($_POST['tipo_documento'] ?? ''),
        'descripcion'      => sanitize($_POST['descripcion'] ?? ''),
    ];

    try {

        // EDITAR
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
            // CREAR
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
    if (!$edit) {
        setFlash('error','Documento no encontrado.');
        redirect('documentos.php');
    }
}

// =========
// LISTAR
// =========
$rows = $pdo->query("SELECT * FROM documentos ORDER BY fecha_subida DESC")->fetchAll();

// ✅ IMPORTANTE: incluir header SOLO UNA VEZ
include __DIR__ . '/includes/header.php';
?>

<?php if ($action === 'create' || $action === 'edit'): ?>
<div class="card mb-4">
  <div class="card-header bg-white border-0 fw-semibold">
    <?= $action === 'edit' ? 'Editar Documento' : 'Subir Documento' ?>
  </div>

  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $edit['iddocumentos'] ?? '' ?>">
      <input type="hidden" name="ruta_actual" value="<?= htmlspecialchars($edit['ruta_archivo'] ?? '') ?>">
      <input type="hidden" name="tamano_actual" value="<?= (int)($edit['tamano_kb'] ?? 0) ?>">

      <div class="row g-3">
        <div class="col-md-5">
          <label class="form-label">Nombre del Documento *</label>
          <input type="text" name="nombre_documento" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($edit['nombre_documento'] ?? '') ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Tipo</label>
          <input type="text" name="tipo_documento" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($edit['tipo_documento'] ?? '') ?>" placeholder="Contrato, Foto, etc.">
        </div>

        <div class="col-md-6">
          <label class="form-label">Archivo <?= $action === 'edit' ? '(opcional)' : '*' ?></label>
          <input type="file" name="archivo" class="form-control form-control-sm"
                 accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt,.csv" <?= $action === 'create' ? 'required' : '' ?>>

          <?php if ($action === 'edit' && !empty($edit['ruta_archivo'])): ?>
            <small class="text-muted">
              Actual: <?= htmlspecialchars(basename($edit['ruta_archivo'])) ?> (<?= (int)$edit['tamano_kb'] ?> KB)
            </small>
          <?php endif; ?>
        </div>

        <div class="col-md-12">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($edit['descripcion'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary btn-sm" type="submit">Guardar</button>
        <a href="documentos.php" class="btn btn-secondary btn-sm">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
    <span class="fw-semibold">Documentos</span>
    <a href="documentos.php?action=create" class="btn btn-primary btn-sm">
      <i class="bi bi-plus"></i> Subir
    </a>
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
        <?php if ($rows): ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int)$r['iddocumentos'] ?></td>
              <td><?= sanitize($r['nombre_documento']) ?></td>
              <td><?= $r['tipo_documento'] ? '<span class="badge bg-light text-dark">'.sanitize($r['tipo_documento']).'</span>' : '-' ?></td>
              <td><?= $r['tamano_kb'] ? (int)$r['tamano_kb'].' KB' : '-' ?></td>
              <td><?= formatDateTime($r['fecha_subida']) ?></td>
              <td>
                <?php if (!empty($r['ruta_archivo']) && file_exists(__DIR__.'/'.$r['ruta_archivo'])): ?>
                  <a href="<?= htmlspecialchars($r['ruta_archivo']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-download"></i>
                  </a>
                <?php endif; ?>

                <a href="documentos.php?action=edit&id=<?= (int)$r['iddocumentos'] ?>" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil"></i>
                </a>

                <a href="documentos.php?action=delete&id=<?= (int)$r['iddocumentos'] ?>"
                   onclick="return confirm('¿Eliminar este documento?');"
                   class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Sin documentos</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
