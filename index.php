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
    // columna correcta es fecha_hora (no fecha_pago)
    $ultimosPagos = $pdo->query("
        SELECT c.monto, c.fecha_hora,
            CONCAT(p.nombres,' ',p.apellidos) AS nombre_persona
        FROM cobranzas c
        JOIN creditos cr ON c.creditos_idcreditos = cr.idcreditos
        JOIN emprendedores e ON cr.emprendedores_idemprendedores = e.idemprendedores
        JOIN personas p ON e.personas_idpersonas = p.idpersonas
        ORDER BY c.fecha_hora DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // columnas correctas son nombre_taller y fecha_taller
    $proximosTalleres = $pdo->query("
        SELECT nombre_taller, fecha_taller, lugar
        FROM talleres
        WHERE fecha_taller >= CURDATE()
        AND estado NOT IN ('Finalizado','Cancelado')
        ORDER BY fecha_taller ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {}

ob_start();
?>

<div class="row g-3 mb-4">
<?php
$cards = [
    ['label'=>'Personas','value'=>$stats['personas'], 'icon'=>'bi-people', 'href'=>'personas.php'],
    ['label'=>'Emprendedores','value'=>$stats['emprendedores'], 'icon'=>'bi-briefcase', 'href'=>'emprendedores.php'],
    ['label'=>'Contratos','value'=>$stats['contratos'], 'icon'=>'bi-file-earmark-text', 'href'=>'contratos.php'],
    ['label'=>'Créditos','value'=>$stats['creditos'], 'icon'=>'bi-credit-card', 'href'=>'creditos.php'],
    ['label'=>'Carritos','value'=>$stats['carritos'], 'icon'=>'bi-cart3', 'href'=>'carritos.php'],
    ['label'=>'Recaudación','value'=>'$'.number_format($stats['recaudacion'],0,',','.'), 'icon'=>'bi-cash-coin', 'href'=>'cobranzas.php'],
];

foreach ($cards as $c):
?>
    <div class="col-md-4 col-lg-2">
        <a href="<?= $c['href'] ?>" class="text-decoration-none">
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
        </a>
    </div>
<?php endforeach; ?>
</div>

<div class="row g-3">
    <!-- Últimos Pagos -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-cash-stack me-1"></i> Últimos Pagos</span>
                <a href="cobranzas.php" class="btn btn-sm btn-outline-secondary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Persona</th><th>Monto</th><th>Fecha</th></tr></thead>
                    <tbody>
                    <?php if ($ultimosPagos): ?>
                        <?php foreach ($ultimosPagos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nombre_persona'] ?? '-') ?></td>
                            <td class="fw-semibold text-success">$<?= number_format($p['monto'],0,',','.') ?></td>
                            <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($p['fecha_hora'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin pagos registrados</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Próximos Talleres -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-calendar-event me-1"></i> Próximos Talleres</span>
                <a href="talleres.php" class="btn btn-sm btn-outline-secondary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Taller</th><th>Fecha</th><th>Lugar</th></tr></thead>
                    <tbody>
                    <?php if ($proximosTalleres): ?>
                        <?php foreach ($proximosTalleres as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['nombre_taller']) ?></td>
                            <td class="text-muted small"><?= date('d/m/Y', strtotime($t['fecha_taller'])) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($t['lugar'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin talleres próximos</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
