<?php
require_once 'includes/helpers.php';
$pageTitle = 'Dashboard';
$pdo = getConnection();

$stats = [
    'personas'       => $pdo->query("SELECT COUNT(*) FROM personas WHERE estado=1")->fetchColumn(),
    'emprendedores'  => $pdo->query("SELECT COUNT(*) FROM emprendedores WHERE estado=1")->fetchColumn(),
    'contratos'      => $pdo->query("SELECT COUNT(*) FROM Contratos WHERE estado='Activo'")->fetchColumn(),
    'creditos'       => $pdo->query("SELECT COUNT(*) FROM creditos WHERE estado='Activo'")->fetchColumn(),
    'cobranzas_mes'  => $pdo->query("SELECT COALESCE(SUM(monto),0) FROM cobranzas WHERE MONTH(fecha_hora)=MONTH(NOW()) AND YEAR(fecha_hora)=YEAR(NOW())")->fetchColumn(),
    'talleres'       => $pdo->query("SELECT COUNT(*) FROM talleres WHERE estado='Programado'")->fetchColumn(),
    'usuarios'       => $pdo->query("SELECT COUNT(*) FROM Usuarios WHERE activo=1")->fetchColumn(),
];

include 'includes/header.php';
?>

<style>
/* Pequeños “toques” sin romper Bootstrap */
.card-hover { transition: transform .12s ease, box-shadow .12s ease; }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 .4rem 1.2rem rgba(0,0,0,.08); }
.kpi-icon { width: 52px; height: 52px; }
.table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
</style>

<!-- Encabezado + Barra de búsqueda -->
<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="mb-1">Panel de control</h4>
        <div class="text-muted" style="font-size:.9rem">
            Acceso rápido a indicadores y movimientos recientes.
        </div>
    </div>

    <div class="w-100 w-lg-50" style="max-width: 540px;">
        <form method="GET" action="personas_buscar.php" class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control"
                   placeholder="Buscar persona por RUT o nombre..."
                   autocomplete="off">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </form>
        <div class="text-muted mt-1" style="font-size:.8rem">
            Selecciona una persona para ver: contratos, créditos, pagos y talleres.
        </div>
    </div>
</div>

<!-- KPI Cards -->
<?php
$cards = [
    ['label'=>'Personas','value'=>$stats['personas'],'icon'=>'person','color'=>'primary','url'=>'personas.php'],
    ['label'=>'Emprendedores','value'=>$stats['emprendedores'],'icon'=>'briefcase','color'=>'success','url'=>'emprendedores.php'],
    ['label'=>'Contratos Activos','value'=>$stats['contratos'],'icon'=>'file-earmark-text','color'=>'info','url'=>'contratos.php'],
    ['label'=>'Créditos Activos','value'=>$stats['creditos'],'icon'=>'credit-card','color'=>'warning','url'=>'creditos.php'],
    ['label'=>'Recaudado este mes','value'=>formatMoney((float)$stats['cobranzas_mes']),'icon'=>'cash-coin','color'=>'success','url'=>'cobranzas.php'],
    ['label'=>'Talleres Programados','value'=>$stats['talleres'],'icon'=>'book','color'=>'secondary','url'=>'talleres.php'],
    ['label'=>'Usuarios Activos','value'=>$stats['usuarios'],'icon'=>'people','color'=>'dark','url'=>'usuarios.php'],
];
?>

<div class="row g-3 mb-4">
<?php foreach ($cards as $c): ?>
    <div class="col-sm-6 col-md-4 col-lg-3">
        <a href="<?= $c['url'] ?>" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-<?= $c['color'] ?> bg-opacity-10 d-flex align-items-center justify-content-center kpi-icon">
                        <i class="bi bi-<?= $c['icon'] ?> text-<?= $c['color'] ?> fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-4 text-dark lh-1"><?= $c['value'] ?></div>
                        <div class="text-muted" style="font-size:.85rem"><?= $c['label'] ?></div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </div>
        </a>
    </div>
<?php endforeach; ?>
</div>

<!-- Tablas -->
<div class="row g-3">
    <!-- Últimas cobranzas -->
    <div class="col-lg-6">
        <div class="card card-hover">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between">
                <div class="fw-semibold">Últimas cobranzas</div>
                <a href="cobranzas.php" class="btn btn-sm btn-outline-secondary">
                    Ver todo
                </a>
            </div>
            <div class="card-body p-0" style="max-height: 360px; overflow:auto;">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Crédito</th>
                            <th class="text-end">Monto</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rows = $pdo->query("SELECT c.idcobranzas, c.fecha_hora, c.monto, c.tipo_pago, c.creditos_idcreditos
                                         FROM cobranzas c
                                         ORDER BY c.fecha_hora DESC
                                         LIMIT 8")->fetchAll();
                    foreach ($rows as $r): ?>
                        <tr>
                            <td><?= formatDateTime($r['fecha_hora']) ?></td>
                            <td><a href="creditos.php?id=<?= (int)$r['creditos_idcreditos'] ?>">#<?= (int)$r['creditos_idcreditos'] ?></a></td>
                            <td class="text-end"><?= formatMoney((float)$r['monto']) ?></td>
                            <td><span class="badge bg-light text-dark"><?= sanitize($r['tipo_pago']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin registros</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Próximos talleres -->
    <div class="col-lg-6">
        <div class="card card-hover">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between">
                <div class="fw-semibold">Próximos talleres</div>
                <a href="talleres.php" class="btn btn-sm btn-outline-secondary">
                    Ver todo
                </a>
            </div>
            <div class="card-body p-0" style="max-height: 360px; overflow:auto;">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fecha</th>
                            <th class="text-end">Cupo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $talleres = $pdo->query("SELECT * FROM talleres
                                             WHERE fecha_taller >= CURDATE()
                                             ORDER BY fecha_taller ASC
                                             LIMIT 8")->fetchAll();
                    foreach ($talleres as $t): ?>
                        <tr>
                            <td><?= sanitize($t['nombre_taller']) ?></td>
                            <td><?= formatDate($t['fecha_taller']) ?></td>
                            <td class="text-end"><?= (int)$t['cupo_disponible'] ?>/<?= (int)$t['cupo_maximo'] ?></td>
                            <td><?= badgeEstado($t['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$talleres): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin talleres próximos</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
