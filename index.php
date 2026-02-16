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
    // Evita que el sistema se rompa
}

$ultimosPagos = [];
$proximosTalleres = [];

try {
    $ultimosPagos = $pdo->query("
        SELECT monto, fecha_pago 
        FROM cobranzas 
        ORDER BY fecha_pago DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $proximosTalleres = $pdo->query("
        SELECT nombre, fecha 
        FROM talleres 
        WHERE fecha >= CURDATE()
        ORDER BY fecha ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {}

ob_start();
?>

<div class="row g-3 mb-4">
<?php
$cards = [
    ['label'=>'Personas','value'=>$stats['personas'], 'icon'=>'bi-people'],
    ['label'=>'Emprendedores','value'=>$stats['emprendedores'], 'icon'=>'bi-briefcase'],
    ['label'=>'Contratos','value'=>$stats['contratos'], 'icon'=>'bi-file-earmark-text'],
    ['label'=>'Créditos','value'=>$stats['creditos'], 'icon'=>'bi-credit-card'],
    ['label'=>'Carritos','value'=>$stats['carritos'], 'icon'=>'bi-cart3'],
    ['label'=>'Recaudación','value'=>'$'.number_format($stats['recaudacion'],0,',','.'), 'icon'=>'bi-cash-coin'],
];

foreach ($cards as $c):
?>
    <div class="col-md-4 col-lg-2">
        <div class="card card-soft card-hover h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon rounded-4 d-flex align-items-center justify-content-center bg-white border">
                    <i class="bi <?= $c['icon'] ?> fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small"><?= $c['label'] ?></div>
                    <div class="fw-bold fs-5"><?= $c['value'] ?></div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
