<?php
require_once 'includes/helpers.php';
$pageTitle = 'Configuración del Sistema';
$pdo = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre_sistema'    => sanitize($_POST['nombre_sistema']),
        'tema_color'        => sanitize($_POST['tema_color'] ?? 'light'),
        'email_sistema'     => sanitize($_POST['email_sistema'] ?? ''),
        'telefono_sistema'  => sanitize($_POST['telefono_sistema'] ?? ''),
        'direccion_sistema' => sanitize($_POST['direccion_sistema'] ?? ''),
    ];

    // Logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','svg','gif'])) {
            $dir = __DIR__ . '/uploads/';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $nombre = 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dir . $nombre)) {
                $data['logo'] = 'uploads/' . $nombre;
            }
        }
    } else {
        $data['logo'] = sanitize($_POST['logo_actual'] ?? '');
    }

    try {
        $s = $pdo->query("SELECT id FROM configuraciones LIMIT 1");
        $existing = $s->fetch();
        if ($existing) {
            $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($data)));
            $pdo->prepare("UPDATE configuraciones SET $sets WHERE id=1")->execute($data);
        } else {
            $cols = implode(',',array_keys($data));
            $vals = ':'.implode(',:', array_keys($data));
            $pdo->prepare("INSERT INTO configuraciones ($cols) VALUES ($vals)")->execute($data);
        }
        setFlash('success','Configuración guardada correctamente.');
    } catch (PDOException $e) { setFlash('error','Error: '.$e->getMessage()); }
    redirect('configuraciones.php');
}

$config = $pdo->query("SELECT * FROM configuraciones WHERE id=1")->fetch();

include 'includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header bg-white border-0 fw-semibold">Configuración General del Sistema</div>
    <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="logo_actual" value="<?= $config['logo'] ?? '' ?>">

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nombre del Sistema *</label>
                <input type="text" name="nombre_sistema" class="form-control" value="<?= $config['nombre_sistema'] ?? 'Sistema de Emprendedores' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tema de Color</label>
                <select name="tema_color" class="form-select">
                    <?php foreach (['light'=>'Claro','dark'=>'Oscuro','blue'=>'Azul','green'=>'Verde'] as $val=>$label): ?>
                    <option value="<?= $val ?>" <?= ($config['tema_color'] ?? 'light') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email del Sistema</label>
                <input type="email" name="email_sistema" class="form-control" value="<?= $config['email_sistema'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono_sistema" class="form-control" value="<?= $config['telefono_sistema'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.svg,.gif">
                <?php if ($config['logo'] ?? ''): ?>
                <div class="mt-2"><img src="<?= htmlspecialchars($config['logo']) ?>" alt="Logo actual" style="max-height:60px; max-width:200px; object-fit:contain;"></div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label">Dirección</label>
                <textarea name="direccion_sistema" class="form-control" rows="2"><?= $config['direccion_sistema'] ?? '' ?></textarea>
            </div>
        </div>

        <?php if ($config): ?>
        <div class="mt-4 p-3 bg-light rounded">
            <div class="row text-muted" style="font-size:.8rem">
                <div class="col-md-4"><strong>Creado:</strong> <?= formatDateTime($config['created_at']) ?></div>
                <div class="col-md-4"><strong>Actualizado:</strong> <?= formatDateTime($config['updated_at']) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar Configuración</button>
        </div>
    </form>
    </div>
</div>

<!-- Info del sistema -->
<div class="card mt-3">
    <div class="card-header bg-white border-0 fw-semibold">Información del Sistema</div>
    <div class="card-body">
        <div class="row g-3 text-sm">
            <?php
            $tablas = ['personas','emprendedores','Contratos','creditos','cobranzas','talleres','inscripciones_talleres','jornadas','carritos','encuesta_2026','documentos','Usuarios','auditoria'];
            foreach ($tablas as $tbl):
                try { $cnt = $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn(); } catch (Exception $e) { $cnt = 'Error'; }
            ?>
            <div class="col-md-4 col-6">
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span class="text-muted" style="font-size:.82rem"><?= $tbl ?></span>
                    <span class="badge bg-light text-dark"><?= $cnt ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-3 text-muted" style="font-size:.8rem">
            <strong>PHP:</strong> <?= PHP_VERSION ?> &nbsp; <strong>MySQL:</strong> <?= $pdo->query("SELECT VERSION()")->fetchColumn() ?>
        </div>
    </div>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
