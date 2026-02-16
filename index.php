<?php
require_once __DIR__ . '/includes/helpers.php';


if (!function_exists('getConnection')) {
    die("No se cargó getConnection(). Revisa includes/helpers.php y config/database.php");
}

$pdo = getConnection();



$stats = [
    'personas'       => $pdo->query("SELECT COUNT(*) FROM personas WHERE estado=1")->fetchColumn(),
    'emprendedores'  => $pdo->query("SELECT COUNT(*) FROM emprendedores WHERE estado=1")->fetchColumn(),
    'contratos'      => $pdo->query("SELECT COUNT(*) FROM Contratos WHERE estado='Activo'")->fetchColumn(),
    'creditos'       => $pdo->query("SELECT COUNT(*) FROM creditos WHERE estado='Activo'")->fetchColumn(),
    'cobranzas_mes'  => $pdo->query("SELECT COALESCE(SUM(monto),0) FROM cobranzas WHERE MONTH(fecha_hora)=MONTH(NOW()) AND YEAR(fecha_hora)=YEAR(NOW())")->fetchColumn(),
    'talleres'       => $pdo->query("SELECT COUNT(*) FROM talleres WHERE estado='Programado'")->fetchColumn(),
    'usuarios'       => $pdo->query("SELECT COUNT(*) FROM Usuarios WHERE activo=1")->fetchColumn(),
    'tarjetas'      => $pdo->query("SELECT COUNT(*) FROM tarjetas_presentacion")->fetchColumn(),
'tarjetas_monto'=> $pdo->query("SELECT COALESCE(SUM(valor_monetario),0) FROM tarjetas_presentacion")->fetchColumn(),

];

include 'includes/header.php';
?>
<div class="row g-3 mb-4">
<?php
$cards = [
    ['label'=>'Personas','value'=>$stats['personas'],'icon'=>'person','color'=>'primary','url'=>'personas.php'],
    ['label'=>'Emprendedores','value'=>$stats['emprendedores'],'icon'=>'briefcase','color'=>'success','url'=>'emprendedores.php'],
    ['label'=>'Contratos Activos','value'=>$stats['contratos'],'icon'=>'file-earmark-text','color'=>'info','url'=>'contratos.php'],
    ['label'=>'Créditos Activos','value'=>$stats['creditos'],'icon'=>'credit-card','color'=>'warning','url'=>'creditos.php'],
    ['label'=>'Recaudado este mes','value'=>formatMoney($stats['cobranzas_mes']),'icon'=>'cash-coin','color'=>'success','url'=>'cobranzas.php'],
    ['label'=>'Talleres Programados','value'=>$stats['talleres'],'icon'=>'book','color'=>'secondary','url'=>'talleres.php'],
    ['label'=>'Usuarios Activos','value'=>$stats['usuarios'],'icon'=>'people','color'=>'dark','url'=>'usuarios.php'],
     ['label'=>'Tarjetas de Presentación','value'=>$stats['tarjetas'],'icon'=>'person-vcard','color'=>'primary','url'=>'tarjetas_presentacion.php'],
];
foreach ($cards as $c): ?>
<div class="col-sm-6 col-md-4 col-lg-3">
    <a href="<?= $c['url'] ?>" class="text-decoration-none">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-<?= $c['color'] ?> bg-opacity-15 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-<?= $c['icon'] ?> text-<?= $c['color'] ?> fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5 text-dark"><?= $c['value'] ?></div>
                    <div class="text-muted" style="font-size:.8rem"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
    </a>
</div>
<?php endforeach; ?>
</div>

<!-- Últimas cobranzas -->
<div class="row g-3">
<div class="col-lg-6">
    <div class="card">
        <div class="card-header bg-white border-0 fw-semibold">Últimas Cobranzas</div>
        <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Fecha</th><th>Crédito</th><th>Monto</th><th>Tipo</th></tr></thead>
            <tbody>
            <?php $rows = $pdo->query("SELECT c.idcobranzas, c.fecha_hora, c.monto, c.tipo_pago, c.creditos_idcreditos FROM cobranzas c ORDER BY c.fecha_hora DESC LIMIT 8")->fetchAll();
            foreach ($rows as $r): ?>
            <tr>
                <td><?= formatDateTime($r['fecha_hora']) ?></td>
                <td><a href="creditos.php?id=<?= $r['creditos_idcreditos'] ?>">#<?= $r['creditos_idcreditos'] ?></a></td>
                <td><?= formatMoney($r['monto']) ?></td>
                <td><span class="badge bg-light text-dark"><?= $r['tipo_pago'] ?></span></td>
            </tr>
            <?php endforeach; if (!$rows): ?><tr><td colspan="4" class="text-center text-muted py-3">Sin registros</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="col-lg-6">
    <div class="card">
        <div class="card-header bg-white border-0 fw-semibold">Próximos Talleres</div>
        <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Nombre</th><th>Fecha</th><th>Cupo</th><th>Estado</th></tr></thead>
            <tbody>
            <?php $talleres = $pdo->query("SELECT * FROM talleres WHERE fecha_taller >= CURDATE() ORDER BY fecha_taller ASC LIMIT 8")->fetchAll();
            foreach ($talleres as $t): ?>
            <tr>
                <td><?= sanitize($t['nombre_taller']) ?></td>
                <td><?= formatDate($t['fecha_taller']) ?></td>
                <td><?= $t['cupo_disponible'] ?>/<?= $t['cupo_maximo'] ?></td>
                <td><?= badgeEstado($t['estado']) ?></td>
            </tr>
            <?php endforeach; if (!$talleres): ?><tr><td colspan="4" class="text-center text-muted py-3">Sin talleres próximos</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</div>
<?php include 'includes/footer.php'; ?>
