<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';
$pdo = getConnection();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('personas.php');

$pageTitle = 'Historial de Persona';

/* PERSONA */
$stmt = $pdo->prepare("SELECT * FROM personas WHERE idpersonas=?");
$stmt->execute([$id]);
$persona = $stmt->fetch();
if (!$persona) redirect('personas.php');

/* EMPRENDEDOR (si existe) */
$stmt = $pdo->prepare("SELECT * FROM emprendedores WHERE personas_idpersonas=?");
$stmt->execute([$id]);
$emprendedor = $stmt->fetch();

/* CONTRATOS */
$contratos = [];
if ($emprendedor) {
    $stmt = $pdo->prepare("
        SELECT * FROM Contratos
        WHERE emprendedores_idemprendedores=?
        ORDER BY fecha_inicio DESC
    ");
    $stmt->execute([$emprendedor['idemprendedores']]);
    $contratos = $stmt->fetchAll();
}

/* CREDITOS */
$creditos = [];
if ($emprendedor) {
    $stmt = $pdo->prepare("
        SELECT * FROM creditos
        WHERE emprendedores_idemprendedores=?
        ORDER BY fecha_inicio DESC
    ");
    $stmt->execute([$emprendedor['idemprendedores']]);
    $creditos = $stmt->fetchAll();
}

/* COBRANZAS */
$cobranzas = [];
if ($emprendedor) {
    $stmt = $pdo->prepare("
        SELECT c.*
        FROM cobranzas c
        JOIN creditos cr ON c.creditos_idcreditos=cr.idcreditos
        WHERE cr.emprendedores_idemprendedores=?
        ORDER BY c.fecha_hora DESC
    ");
    $stmt->execute([$emprendedor['idemprendedores']]);
    $cobranzas = $stmt->fetchAll();
}

/* TALLERES */
$talleres = [];
if ($emprendedor) {
    $stmt = $pdo->prepare("
        SELECT t.nombre_taller, t.fecha_taller,
               i.asistio, i.calificacion
        FROM inscripciones_talleres i
        JOIN talleres t ON i.talleres_idtalleres=t.idtalleres
        WHERE i.emprendedores_idemprendedores=?
        ORDER BY t.fecha_taller DESC
    ");
    $stmt->execute([$emprendedor['idemprendedores']]);
    $talleres = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<h4 class="mb-3">
    <?= sanitize($persona['nombres'].' '.$persona['apellidos']) ?>
</h4>

<div class="row g-3">

<!-- CONTRATOS -->
<div class="col-lg-6">
<div class="card">
<div class="card-header fw-semibold">Contratos</div>
<div class="card-body p-0">
<table class="table table-sm mb-0">
<thead><tr><th>ID</th><th>Inicio</th><th>Estado</th></tr></thead>
<tbody>
<?php foreach ($contratos as $c): ?>
<tr>
<td>#<?= $c['idContratos'] ?></td>
<td><?= formatDate($c['fecha_inicio']) ?></td>
<td><?= badgeEstado($c['estado']) ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$contratos): ?>
<tr><td colspan="3" class="text-center text-muted py-3">Sin contratos</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- CREDITOS -->
<div class="col-lg-6">
<div class="card">
<div class="card-header fw-semibold">Créditos</div>
<div class="card-body p-0">
<table class="table table-sm mb-0">
<thead><tr><th>ID</th><th>Monto</th><th>Estado</th></tr></thead>
<tbody>
<?php foreach ($creditos as $cr): ?>
<tr>
<td>#<?= $cr['idcreditos'] ?></td>
<td><?= formatMoney($cr['monto_inicial']) ?></td>
<td><?= badgeEstado($cr['estado']) ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$creditos): ?>
<tr><td colspan="3" class="text-center text-muted py-3">Sin créditos</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- COBRANZAS -->
<div class="col-lg-6">
<div class="card">
<div class="card-header fw-semibold">Historial de Pagos</div>
<div class="card-body p-0">
<table class="table table-sm mb-0">
<thead><tr><th>Fecha</th><th>Monto</th><th>Tipo</th></tr></thead>
<tbody>
<?php foreach ($cobranzas as $cb): ?>
<tr>
<td><?= formatDateTime($cb['fecha_hora']) ?></td>
<td><?= formatMoney($cb['monto']) ?></td>
<td><?= sanitize($cb['tipo_pago']) ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$cobranzas): ?>
<tr><td colspan="3" class="text-center text-muted py-3">Sin pagos</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- TALLERES -->
<div class="col-lg-6">
<div class="card">
<div class="card-header fw-semibold">Talleres</div>
<div class="card-body p-0">
<table class="table table-sm mb-0">
<thead><tr><th>Taller</th><th>Fecha</th><th>Asistió</th></tr></thead>
<tbody>
<?php foreach ($talleres as $t): ?>
<tr>
<td><?= sanitize($t['nombre_taller']) ?></td>
<td><?= formatDate($t['fecha_taller']) ?></td>
<td><?= $t['asistio'] ? badgeEstado('1') : badgeEstado('0') ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$talleres): ?>
<tr><td colspan="3" class="text-center text-muted py-3">Sin talleres</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

</div>

<?php include 'includes/footer.php'; ?>
