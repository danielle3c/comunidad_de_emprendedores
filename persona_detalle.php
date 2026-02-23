<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';

$pdo = getConnection();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) redirect('personas.php');

$pageTitle = 'Historial de Persona';

/* ── PERSONA ─────────────────────────────────── */
$stmt = $pdo->prepare("SELECT * FROM personas WHERE idpersonas = ?");
$stmt->execute([$id]);
$persona = $stmt->fetch();
if (!$persona) redirect('personas.php');

/* ── EMPRENDEDOR ─────────────────────────────── */
$stmt = $pdo->prepare("SELECT * FROM emprendedores WHERE personas_idpersonas = ?");
$stmt->execute([$id]);
$emprendedor = $stmt->fetch();
$eid = $emprendedor ? (int)$emprendedor['idemprendedores'] : null;

/* ── CONTRATOS ───────────────────────────────── */
$contratos = [];
if ($eid) {
    $stmt = $pdo->prepare("
        SELECT * FROM Contratos
        WHERE emprendedores_idemprendedores = ?
        ORDER BY fecha_inicio DESC
    ");
    $stmt->execute([$eid]);
    $contratos = $stmt->fetchAll();
}

/* ── CRÉDITOS ────────────────────────────────── */
$creditos = [];
if ($eid) {
    $stmt = $pdo->prepare("
        SELECT * FROM creditos
        WHERE emprendedores_idemprendedores = ?
        ORDER BY fecha_inicio DESC
    ");
    $stmt->execute([$eid]);
    $creditos = $stmt->fetchAll();
}

/* ── COBRANZAS ───────────────────────────────── */
$cobranzas = [];
if ($eid) {
    $stmt = $pdo->prepare("
        SELECT cb.*
        FROM cobranzas cb
        JOIN creditos cr ON cb.creditos_idcreditos = cr.idcreditos
        WHERE cr.emprendedores_idemprendedores = ?
        ORDER BY cb.fecha_hora DESC
    ");
    $stmt->execute([$eid]);
    $cobranzas = $stmt->fetchAll();
}

/* ── TALLERES ────────────────────────────────── */
$talleres = [];
if ($eid) {
    $stmt = $pdo->prepare("
        SELECT t.nombre_taller, t.fecha_taller, t.lugar,
               i.asistio, i.calificacion, i.comentarios
        FROM inscripciones_talleres i
        JOIN talleres t ON i.talleres_idtalleres = t.idtalleres
        WHERE i.emprendedores_idemprendedores = ?
        ORDER BY t.fecha_taller DESC
    ");
    $stmt->execute([$eid]);
    $talleres = $stmt->fetchAll();
}

/* ── CARRITOS ────────────────────────────────── */
$carritos = [];
if ($eid) {
    // carritos no tiene FK directa a emprendedores en el esquema detectado
    // intentar por si acaso existe la columna
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM carritos
            WHERE emprendedores_idemprendedores = ?
            ORDER BY fecha_registro DESC
        ");
        $stmt->execute([$eid]);
        $carritos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $carritos = []; // columna no existe en esta BD
    }
}

/* ── TARJETAS ────────────────────────────────── */
$tarjetas = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM tarjetas_presentacion
        WHERE personas_idpersonas = ?
        ORDER BY idtarjeta DESC
    ");
    $stmt->execute([$id]);
    $tarjetas = $stmt->fetchAll();
} catch (PDOException $e) {
    // FK puede no existir o tener otro nombre
    $tarjetas = [];
}

include 'includes/header.php';
?>

<!-- Nombre y datos básicos -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
  <div class="kpi-icon d-flex align-items-center justify-content-center"
       style="width:56px;height:56px;border-radius:16px;background:var(--brand-dim);border:1.5px solid var(--brand-pick)">
    <i class="bi bi-person-circle" style="font-size:1.7rem;color:var(--brand-pick)"></i>
  </div>
  <div>
    <h4 class="mb-0 fw-bold" style="font-family:'Barlow Condensed',sans-serif;font-size:1.5rem">
      <?= htmlspecialchars($persona['nombres'] . ' ' . $persona['apellidos'], ENT_QUOTES) ?>
    </h4>
    <div class="d-flex gap-3 flex-wrap mt-1" style="font-size:.83rem;color:var(--text2)">
      <span><i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($persona['rut'] ?? '') ?></span>
      <?php if ($persona['telefono']): ?>
        <span><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($persona['telefono']) ?></span>
      <?php endif; ?>
      <?php if ($persona['email']): ?>
        <span><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($persona['email']) ?></span>
      <?php endif; ?>
      <?php if ($persona['genero'] ?? ''): ?>
        <span><i class="bi bi-gender-ambiguous me-1"></i><?= htmlspecialchars($persona['genero']) ?></span>
      <?php endif; ?>
      <span><?= badgeEstado((string)($persona['estado'] ?? '0')) ?></span>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2 flex-wrap">
    <a href="personas.php" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
    <a href="personas.php?edit=<?= $id ?>" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-pencil me-1"></i>Editar
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-printer me-1"></i>Imprimir
    </button>
  </div>
</div>

<?php if ($emprendedor): ?>
<!-- Datos del emprendimiento -->
<div class="card mb-4" style="border-left:3px solid var(--brand-pick)">
  <div class="card-body py-2 px-3">
    <div class="d-flex flex-wrap gap-4" style="font-size:.84rem">
      <span><i class="bi bi-shop me-1" style="color:var(--brand-pick)"></i>
        <strong>Rubro:</strong> <?= htmlspecialchars($emprendedor['rubro'] ?? '—') ?></span>
      <span><i class="bi bi-box me-1" style="color:var(--brand-pick)"></i>
        <strong>Producto:</strong> <?= htmlspecialchars($emprendedor['producto_principal'] ?? '—') ?></span>
      <span><i class="bi bi-currency-dollar me-1" style="color:var(--brand-pick)"></i>
        <strong>Límite crédito:</strong> <?= formatMoney($emprendedor['limite_credito'] ?? 0) ?></span>
      <span><i class="bi bi-calendar me-1" style="color:var(--brand-pick)"></i>
        <strong>Desde:</strong> <?= formatDate($emprendedor['fecha_registro'] ?? '') ?></span>
      <span><?= badgeEstado((string)($emprendedor['estado'] ?? '')) ?></span>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Tabs de historial -->
<ul class="nav nav-tabs mb-3" id="histTabs" role="tablist" style="border-color:var(--border)">
  <?php
  $tabs = [
    ['contratos',  'Contratos',   'file-earmark-text', count($contratos)],
    ['creditos',   'Créditos',    'cash-coin',          count($creditos)],
    ['cobranzas',  'Pagos',       'cash-stack',         count($cobranzas)],
    ['talleres',   'Talleres',    'mortarboard',        count($talleres)],
    ['carritos',   'Carritos',    'cart',               count($carritos)],
    ['tarjetas',   'Tarjetas',    'person-vcard',       count($tarjetas)],
  ];
  $first = true;
  foreach ($tabs as [$id2, $label, $icon, $count]):
  ?>
  <li class="nav-item" role="presentation">
    <button class="nav-link <?= $first ? 'active' : '' ?>"
            id="tab-<?= $id2 ?>"
            data-bs-toggle="tab"
            data-bs-target="#panel-<?= $id2 ?>"
            type="button" role="tab"
            style="font-size:.85rem;<?= $first ? 'color:var(--brand-pick);border-bottom:2px solid var(--brand-pick)' : '' ?>">
      <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?>
      <span class="badge ms-1" style="background:var(--brand-dim);color:var(--brand-pick);font-size:.7rem"><?= $count ?></span>
    </button>
  </li>
  <?php $first = false; endforeach; ?>
</ul>

<div class="tab-content" id="histTabsContent">

  <!-- ── CONTRATOS ─────────────────────────── -->
  <div class="tab-pane fade show active" id="panel-contratos" role="tabpanel">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr>
            <th>#</th><th>Inicio</th><th>Término</th>
            <th>Tipo</th><th>Monto</th><th>Estado</th>
          </tr></thead>
          <tbody>
          <?php foreach ($contratos as $c): ?>
          <tr>
            <td class="fw-bold">#<?= $c['idContratos'] ?></td>
            <td><?= formatDate($c['fecha_inicio']) ?></td>
            <td><?= formatDate($c['fecha_termino'] ?? '') ?></td>
            <td><?= htmlspecialchars($c['tipo_contrato'] ?? '—') ?></td>
            <td><?= formatMoney($c['monto_total'] ?? 0) ?></td>
            <td><?= badgeEstado($c['estado'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$contratos): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">
            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4"></i>
            Sin contratos registrados
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── CRÉDITOS ──────────────────────────── -->
  <div class="tab-pane fade" id="panel-creditos" role="tabpanel">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr>
            <th>#</th><th>Inicio</th><th>Monto inicial</th>
            <th>Saldo</th><th>Cuota/mes</th><th>Estado</th>
          </tr></thead>
          <tbody>
          <?php foreach ($creditos as $cr): ?>
          <tr>
            <td class="fw-bold">#<?= $cr['idcreditos'] ?></td>
            <td><?= formatDate($cr['fecha_inicio']) ?></td>
            <td><?= formatMoney($cr['monto_inicial'] ?? 0) ?></td>
            <td><?= formatMoney($cr['saldo_inicial']  ?? 0) ?></td>
            <td><?= formatMoney($cr['cuota_mensual']  ?? 0) ?></td>
            <td><?= badgeEstado($cr['estado'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$creditos): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">
            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4"></i>
            Sin créditos registrados
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── COBRANZAS / PAGOS ─────────────────── -->
  <div class="tab-pane fade" id="panel-cobranzas" role="tabpanel">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr>
            <th>Fecha y hora</th><th>Monto</th>
            <th>Tipo pago</th><th>Observaciones</th>
          </tr></thead>
          <tbody>
          <?php foreach ($cobranzas as $cb): ?>
          <tr>
            <td><?= formatDateTime($cb['fecha_hora'] ?? '') ?></td>
            <td class="fw-bold"><?= formatMoney($cb['monto'] ?? 0) ?></td>
            <td><?= htmlspecialchars($cb['tipo_pago'] ?? '—') ?></td>
            <td style="font-size:.8rem;color:var(--text2)"><?= htmlspecialchars($cb['observaciones'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$cobranzas): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">
            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4"></i>
            Sin pagos registrados
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── TALLERES ───────────────────────────── -->
  <div class="tab-pane fade" id="panel-talleres" role="tabpanel">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr>
            <th>Taller</th><th>Fecha</th><th>Lugar</th>
            <th>Asistió</th><th>Calificación</th>
          </tr></thead>
          <tbody>
          <?php foreach ($talleres as $t): ?>
          <tr>
            <td class="fw-bold"><?= htmlspecialchars($t['nombre_taller'] ?? '') ?></td>
            <td><?= formatDate($t['fecha_taller'] ?? '') ?></td>
            <td><?= htmlspecialchars($t['lugar'] ?? '—') ?></td>
            <td><?= $t['asistio'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
            <td>
              <?php if ($t['calificacion']): ?>
                <span style="color:var(--brand-pick);font-weight:700"><?= (int)$t['calificacion'] ?>/10</span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$talleres): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">
            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4"></i>
            Sin inscripciones a talleres
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── CARRITOS ───────────────────────────── -->
  <div class="tab-pane fade" id="panel-carritos" role="tabpanel">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr>
            <th>#</th><th>Nombre carrito</th><th>Responsable</th>
            <th>Teléfono</th><th>Estado</th><th>Fecha</th>
          </tr></thead>
          <tbody>
          <?php foreach ($carritos as $ca): ?>
          <tr>
            <td>#<?= $ca['idcarritos'] ?></td>
            <td><?= htmlspecialchars($ca['nombre_carrito'] ?? '') ?></td>
            <td><?= htmlspecialchars($ca['nombre_responsable'] ?? '') ?></td>
            <td><?= htmlspecialchars($ca['telefono_responsable'] ?? '—') ?></td>
            <td><?= badgeEstado($ca['estado'] ?? '') ?></td>
            <td><?= formatDate($ca['fecha_registro'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$carritos): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">
            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4"></i>
            Sin carritos asignados
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── TARJETAS ───────────────────────────── -->
  <div class="tab-pane fade" id="panel-tarjetas" role="tabpanel">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr>
            <th>#</th><th>Nombre</th><th>Cantidad</th><th>Valor</th>
          </tr></thead>
          <tbody>
          <?php foreach ($tarjetas as $tj): ?>
          <tr>
            <td>#<?= $tj['idtarjeta'] ?></td>
            <td><?= htmlspecialchars($tj['nombre'] ?? '') ?></td>
            <td><?= (int)($tj['cantidad'] ?? 0) ?></td>
            <td><?= formatMoney($tj['valor_monetario'] ?? 0) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$tarjetas): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">
            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4"></i>
            Sin tarjetas de presentación
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div><!-- /.tab-content -->

<?php include 'includes/footer.php'; ?>
