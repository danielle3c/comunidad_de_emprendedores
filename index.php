<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Dashboard';
$pdo = getConnection();

/* ===============================
   ESTADÍSTICAS PRINCIPALES
================================ */

$stats = [
    'personas'       => 0,
    'emprendedores'  => 0,
    'contratos'      => 0,
    'creditos'       => 0,
    'carritos'       => 0,
    'recaudacion'    => 0,
];

try {
    $stats['personas']      = (int)$pdo->query("SELECT COUNT(*) FROM personas")->fetchColumn();
    $stats['emprendedores'] = (int)$pdo->query("SELECT COUNT(*) FROM emprendedores")->fetchColumn();
    $stats['contratos']     = (int)$pdo->query("SELECT COUNT(*) FROM contratos")->fetchColumn();
    $stats['creditos']      = (int)$pdo->query("SELECT COUNT(*) FROM creditos")->fetchColumn();
    $stats['carritos']      = (int)$pdo->query("SELECT COUNT(*) FROM carritos")->fetchColumn();
    $stats['recaudacion']   = (int)$pdo->query("SELECT IFNULL(SUM(monto),0) FROM cobranzas")->fetchColumn();
} catch (Exception $e) {
    // Evita que el sistema se rompa si alguna tabla falla
}

/* ===============================
   ÚLTIMOS PAGOS
================================ */

$ultimosPagos = [];

try {
    $stmt = $pdo->query("
        SELECT monto, fecha_pago 
        FROM cobranzas 
        ORDER BY fecha_pago DESC 
        LIMIT 5
    ");
    $ultimosPagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

/* ===============================
   PRÓXIMOS TALLERES
================================ */

$proximosTalleres = [];

try {
    $stmt = $pdo->query("
        SELECT nombre, fecha 
        FROM talleres 
        WHERE fecha >= CURDATE()
        ORDER BY fecha ASC
        LIMIT 5
    ");
    $proximosTalleres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="m-0">Panel Principal</h4>
    <span class="badge-soft">
        Bienvenido, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>
    </span>
</div>

<!-- TARJETAS -->
<div class="row g-3 mb-4">

    <?php
    $cards = [
        ['label'=>'Personas','value'=>$stats['personas']],
        ['label'=>'Emprendedores','value'=>$stats['emprendedores']],
        ['label'=>'Contratos','value'=>$stats['contratos']],
        ['label'=>'Créditos','value'=>$stats['creditos']],
        ['label'=>'Carritos','value'=>$stats['carritos']],
        ['label'=>'Recaudación','value'=>'$'.number_format($stats['recaudacion'],0,',','.')]
    ];

    foreach ($cards as $c):
    ?>

    <div class="col-md-4 col-lg-2">
        <div class="card-modern text-center">
            <div class="card-modern-body">
                <div class="text-muted small"><?= $c['label'] ?></div>
                <h5 class="fw-bold mt-1"><?= $c['value'] ?></h5>
            </div>
        </div>
    </div>

    <?php endforeach; ?>

</div>

<div class="row g-3">

    <!-- ÚLTIMOS PAGOS -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-modern-header">
                Últimos pagos registrados
            </div>
            <div class="card-modern-body">

                <?php if (empty($ultimosPagos)): ?>
                    <div class="text-muted">No hay pagos recientes.</div>
                <?php else: ?>

                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Monto</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimosPagos as $p): ?>
                        <tr>
                            <td>$<?= number_format($p['monto'],0,',','.') ?></td>
                            <td><?= htmlspecialchars($p['fecha_pago']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- PRÓXIMOS TALLERES -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-modern-header">
                Próximos talleres
            </div>
            <div class="card-modern-body">

                <?php if (empty($proximosTalleres)): ?>
                    <div class="text-muted">No hay talleres programados.</div>
                <?php else: ?>

                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proximosTalleres as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['nombre']) ?></td>
                            <td><?= htmlspecialchars($t['fecha']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php endif; ?>

            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
