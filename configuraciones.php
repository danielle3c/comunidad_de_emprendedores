<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/roles.php';

require_role(['admin']);

$pageTitle = 'Configuración del Sistema';
$pdo = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        setFlash('error', 'Error de validación. Intente de nuevo.');
        redirect('configuraciones.php');
    }

    $data = [
        'nombre_sistema'    => sanitize(filter_input(INPUT_POST, 'nombre_sistema', FILTER_SANITIZE_STRING) ?? ''),
        'tema_color'        => sanitize(filter_input(INPUT_POST, 'tema_color', FILTER_SANITIZE_STRING) ?? 'light'),
        'email_sistema'     => filter_input(INPUT_POST, 'email_sistema', FILTER_VALIDATE_EMAIL) ?: null,
        'telefono_sistema'  => sanitize(filter_input(INPUT_POST, 'telefono_sistema', FILTER_SANITIZE_STRING) ?? ''),
        'direccion_sistema' => sanitize(filter_input(INPUT_POST, 'direccion_sistema', FILTER_SANITIZE_STRING) ?? ''),
    ];

    // Logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'svg', 'gif'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/gif'];
        
        $fileMime = mime_content_type($_FILES['logo']['tmp_name']);
        
        if (in_array($ext, $allowedExts) && in_array($fileMime, $allowedMimes)) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $newFilename = 'logo_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destination = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                $data['logo'] = 'uploads/' . $newFilename;
            } else {
                setFlash('error', 'Error al guardar el archivo en el servidor.');
                error_log("Error al mover archivo de logo a: " . $destination);
            }
        } else {
            setFlash('error', 'Tipo de archivo no permitido para el logo.');
        }
    } else {
        $logoActual = filter_input(INPUT_POST, 'logo_actual', FILTER_SANITIZE_STRING) ?? '';
        if (strpos($logoActual, 'uploads/') === 0 && strpos($logoActual, '..') === false) {
            $data['logo'] = $logoActual;
        } else {
            $data['logo'] = '';
        }
    }

    try {
        $stmt = $pdo->query("SELECT id FROM configuraciones LIMIT 1");
        $existing = $stmt->fetch();
        
        if ($existing) {
            $sets = [];
            foreach ($data as $col => $val) {
                $sets[] = "$col = :$col";
            }
            $sql = "UPDATE configuraciones SET " . implode(', ', $sets) . " WHERE id = 1";
        } else {
            $cols = implode(', ', array_keys($data));
            $vals = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO configuraciones ($cols) VALUES ($vals)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        
        setFlash('success', 'Configuración guardada correctamente.');
    } catch (PDOException $e) {
        setFlash('error', 'Error al guardar la configuración.');
        error_log("Error en configuraciones.php: " . $e->getMessage());
    }
    redirect('configuraciones.php');
}

$config = $pdo->query("SELECT * FROM configuraciones WHERE id = 1")->fetch();

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header bg-white border-0 fw-semibold">Configuración General del Sistema</div>
    <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="logo_actual" value="<?= htmlspecialchars($config['logo'] ?? '') ?>">

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nombre del Sistema *</label>
                <input type="text" name="nombre_sistema" class="form-control" 
                       value="<?= htmlspecialchars($config['nombre_sistema'] ?? 'Sistema de Emprendedores') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tema de Color</label>
                <select name="tema_color" class="form-select">
                    <?php foreach (['light' => 'Claro', 'dark' => 'Oscuro', 'blue' => 'Azul', 'green' => 'Verde'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($config['tema_color'] ?? 'light') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email del Sistema</label>
                <input type="email" name="email_sistema" class="form-control" 
                       value="<?= htmlspecialchars($config['email_sistema'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono_sistema" class="form-control" 
                       value="<?= htmlspecialchars($config['telefono_sistema'] ?? '') ?>">
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
                <textarea name="direccion_sistema" class="form-control" rows="2"><?= htmlspecialchars($config['direccion_sistema'] ?? '') ?></textarea>
            </div>
        </div>

        <?php if ($config): ?>
        <div class="mt-4 p-3 bg-light rounded">
            <div class="row text-muted" style="font-size:.8rem">
                <div class="col-md-4"><strong>Creado:</strong> <?= formatDateTime($config['created_at'] ?? null) ?></div>
                <div class="col-md-4"><strong>Actualizado:</strong> <?= formatDateTime($config['updated_at'] ?? null) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar Configuración</button>
        </div>
    </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white border-0 fw-semibold">Información del Sistema</div>
    <div class="card-body">
        <div class="row g-3 text-sm">
            <?php
            $tablas = ['personas', 'emprendedores', 'Contratos', 'creditos', 'cobranzas', 'talleres', 'inscripciones_talleres', 'jornadas', 'carritos', 'encuesta_2026', 'documentos', 'Usuarios', 'auditoria'];
            foreach ($tablas as $tbl):
                try { 
                    $cnt = $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn(); 
                } catch (Exception $e) { 
                    $cnt = 'Error'; 
                    error_log("Error contando tabla $tbl: " . $e->getMessage());
                }
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

<?php include __DIR__ . '/includes/footer.php'; ?>