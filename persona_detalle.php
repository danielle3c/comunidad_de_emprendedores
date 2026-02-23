<?php
require_once 'includes/auth_guard.php';
require_once 'includes/helpers.php';

$pdo = getConnection();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) redirect('personas.php');

$pageTitle = 'Historial de Persona';

// ── PERSONA ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM personas WHERE idpersonas = ?");
$stmt->execute([$id]);
$persona = $stmt->fetch();
if (!$persona) redirect('personas.php');

// ── EMPRENDEDOR ──────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM emprendedores WHERE personas_idpersonas = ?");
$stmt->execute([$id]);
$emprendedor = $stmt->fetch();
$eid = $emprendedor ? (int)$emprendedor['idemprendedores'] : null;

// ── CONTRATOS (tabla: Contratos, PK: idContratos) ────────────
$contratos = [];
if ($eid) {
    $stmt = $pdo->prepare("SELECT * FROM Contratos WHERE emprendedores_idemprendedores = ? ORDER BY fecha_inicio DESC");
    $stmt->execute([$eid]);
    $contratos = $stmt->fetchAll();
}

// ── CRÉDITOS (tabla: creditos, PK: idcreditos) ───────────────
$creditos = [];
if ($eid) {
    $stmt = $pdo->prepare("SELECT * FROM creditos WHERE emprendedores_idemprendedores = ? ORDER BY fecha_inicio DESC");
    $stmt->execute([$eid]);
    $creditos = $stmt->fetchAll();
}

// ── COBRANZAS (FK: creditos_idcreditos → creditos.idcreditos) ─
$cobranzas = [];
if ($eid) {
    $stmt = $pdo->prepare("
        SELECT cb.*
        FROM   cobranzas cb
        JOIN   creditos cr ON cb.creditos_idcreditos = cr.idcreditos
        WHERE  cr.emprendedores_idemprendedores = ?
        ORDER  BY cb.fecha_hora DESC
    ");
    $stmt->execute([$eid]);
    $cobranzas = $stmt->fetchAll();
}

// ── TALLERES (inscripciones_talleres → talleres) ─────────────
$talleres = [];
if ($eid) {
    $stmt = $pdo->prepare("
        SELECT t.nombre_taller, t.fecha_taller, t.lugar,
               i.asistio, i.calificacion, i.comentarios
        FROM   inscripciones_talleres i
        JOIN   talleres t ON i.talleres_idtalleres = t.idtalleres
        WHERE  i.emprendedores_idemprendedores = ?
        ORDER  BY t.fecha_taller DESC
    ");
    $stmt->execute([$eid]);
    $talleres = $stmt->fetchAll();
}

// ── ENCUESTAS (tabla: encuesta_2026) ─────────────────────────
$encuestas = [];
if ($eid) {
    $stmt = $pdo->prepare("SELECT * FROM encuesta_2026 WHERE emprendedores_idemprendedores = ? ORDER BY fecha_respuesta DESC");
    $stmt->execute([$eid]);
    $encuestas = $stmt->fetchAll();
}

// ── DOCUMENTOS (tabla: documentos, FK: emprendedores_idemprendedores) ─
$documentos = [];
if ($eid) {
    $stmt = $pdo->prepare("SELECT * FROM documentos WHERE emprendedores_idemprendedores = ? ORDER BY fecha_subida DESC");
    $stmt->execute([$eid]);
    $documentos = $stmt->fetchAll();
}

// ── TARJETAS (tabla: tarjetas_presentacion — sin FK a persona) ─
// No tiene relación directa con personas en el esquema actual
$tarjetas = [];

include 'includes/header.php';
?>

<!-- Cabecera persona -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
  <div class="kpi-icon d-flex align-items-center justify-content-center"
       style="width:56px;height:56px;border-radius:16px;background:var(--brand-dim);border:1.5px solid var(--brand-pick)">
    <i class="bi bi-person-circle" style="font-size:1.7rem;color:var(--brand-pick)"></i>
  </div>
  <div>
    <h4 class="mb-0 fw-bold" style="font-family:'Barlow Condensed',sans-serif;font-size:1.5rem">
      <?= htmlspecialchars($persona['nombres'] . ' ' . $persona['apellidos']) ?>
    </h4>
    <div class="d-flex gap-3 flex-wrap mt-1" style="font-size:.83rem;color:var(--text2)">
      <span><i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($persona['rut']) ?></span>
      <?php if ($persona['telefono']): ?>
        <span><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($persona['telefono']) ?></span>
      <?php endif; ?>
      <?php if ($persona['email']): ?>
        <span><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($persona['email']) ?></span>
      <?php endif; ?>
      <?php if ($persona['genero'] ?? ''): ?>
        <span><i class="bi bi-gender-ambiguous me-1"></i><?= htmlspecialchars($persona['genero']) ?></span>
      <?php endif; ?>
      <span><?= $persona['estado'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></span>
    </div>
  </div>
  <div class="ms-auto d-flex gap-2 flex-wrap">
    <a href="personas.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Imprimir</button>
  </div>
</div>

<?php if ($emprendedor): ?>
<!-- Datos emprendimiento -->
<div class="card mb-4" style="border-left:3px solid var(--brand-pick)">
  <div class="card-body py-2 px-3">
    <div class="d-flex flex-wrap gap-4" style="font-size:.84rem">
      <?php if ($emprendedor['rubro']): ?>
        <span><i class="bi bi-shop me-1" style="color:var(--brand-pick)"></i><strong>Rubro:</strong> <?= htmlspecialchars($emprendedor['rubro']) ?></span>
      <?php endif; ?>
      <?php if ($emprendedor['producto_principal']): ?>
        <span><i class="bi bi-box me-1" style="color:var(--brand-pick)"></i><strong>Producto:</strong> <?= htmlspecialchars($emprendedor['producto_principal']) ?></span>
      <?php endif; ?>
      <span><i class="bi bi-currency-dollar me-1" style="color:var(--brand-pick)"></i><strong>Límite crédito:</strong> <?= formatMoney($emprendedor['limite_credito'] ?? 0) ?></span>
      <?php if ($emprendedor['fecha_registro']): ?>
        <span><i class="bi bi-calendar me-1" style="color:var(--brand-pick)"></i><strong>Desde:</strong> <?= formatDate($emprendedor['fecha_registro']) ?></span>
      <?php endif; ?>
      <span><?= $emprendedor['estado'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></span>
    </div>
  </div>
</div>
<?php else: ?>
<div class="alert alert-warning mb-4">
  <i class="bi bi-info-circle me-2"></i>Esta persona no está registrada como emprendedor.
</div>
<?php endif; ?>

<!-- Tabs -->
<?php
$tabs = [
  ['contratos',  'Contratos',   'file-earmark-text', count($contratos)],
  ['creditos',   'Créditos',    'cash-coin',          count($creditos)],
  ['cobranzas',  'Pagos',       'cash-stack',         count($cobranzas)],
  ['talleres',   'Talleres',    'mortarboard',        count($talleres)],
  ['encuestas',  'Encuestas',   'clipboard-check',    count($encuestas)],
  ['documentos', 'Documentos',  'file-earmark',       count($documentos)],
];
?>
<ul class="nav nav-tabs mb-3" id="histTabs" style="border-color:var(--border)">
  <?php foreach ($tabs as $i => [$tid, $label, $icon, $count]): ?>
  <li class="nav-item">
    <button class="nav-link <?= $i === 0 ? 'active' : '' ?>"
            data-bs-toggle="tab" data-bs-target="#panel-<?= $tid ?>"
            type="button" style="font-size:.85rem">
      <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?>
      <span class="badge ms-1" style="background:var(--brand-dim);color:var(--brand-pick);font-size:.7rem"><?= $count ?></span>
    </button>
  </li>
  <?php endforeach; ?>
</ul>

<div class="tab-content">

  <!-- CONTRATOS -->
  <div class="tab-pane fade show active" id="panel-contratos">
    <div class="card"><div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>#</th><th>Inicio</th><th>Término</th><th>Tipo</th><th>Monto</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($contratos as $c): ?>
          <tr>
            <td><strong>#<?= $c['idContratos'] ?></strong></td>
            <td><?= formatDate($c['fecha_inicio']) ?></td>
            <td><?= formatDate($c['fecha_termino'] ?? '') ?></td>
            <td><?= htmlspecialchars($c['tipo_contrato'] ?? '—') ?></td>
            <td><?= formatMoney($c['monto_total']) ?></td>
            <td><?= badgeEstado($c['estado']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$contratos): ?><tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox d-block mb-1" style="font-size:1.4rem;opacity:.4"></i>Sin contratos</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <!-- CRÉDITOS -->
  <div class="tab-pane fade" id="panel-creditos">
    <div class="card"><div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>#</th><th>Inicio</th><th>Monto inicial</th><th>Saldo</th><th>Cuota/mes</th><th>Día pago</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($creditos as $cr): ?>
          <tr>
            <td><strong>#<?= $cr['idcreditos'] ?></strong></td>
            <td><?= formatDate($cr['fecha_inicio']) ?></td>
            <td><?= formatMoney($cr['monto_inicial']) ?></td>
            <td><?= formatMoney($cr['saldo_inicial']) ?></td>
            <td><?= formatMoney($cr['cuota_mensual']) ?></td>
            <td>Día <?= (int)$cr['dia_de_pago'] ?></td>
            <td><?= badgeEstado($cr['estado']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$creditos): ?><tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-inbox d-block mb-1" style="font-size:1.4rem;opacity:.4"></i>Sin créditos</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <!-- PAGOS -->
  <div class="tab-pane fade" id="panel-cobranzas">
    <div class="card"><div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>Fecha y hora</th><th>Monto</th><th>Tipo pago</th><th>Observaciones</th><th>Registrado por</th></tr></thead>
        <tbody>
        <?php foreach ($cobranzas as $cb): ?>
          <tr>
            <td><?= formatDateTime($cb['fecha_hora']) ?></td>
            <td><strong><?= formatMoney($cb['monto']) ?></strong></td>
            <td><?= htmlspecialchars($cb['tipo_pago'] ?? '—') ?></td>
            <td style="font-size:.8rem;color:var(--text2)"><?= htmlspecialchars($cb['observaciones'] ?? '') ?></td>
            <td style="font-size:.8rem;color:var(--text2)"><?= htmlspecialchars($cb['usuario_registro'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$cobranzas): ?><tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox d-block mb-1" style="font-size:1.4rem;opacity:.4"></i>Sin pagos</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <!-- TALLERES -->
  <div class="tab-pane fade" id="panel-talleres">
    <div class="card"><div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>Taller</th><th>Fecha</th><th>Lugar</th><th>Asistió</th><th>Calificación</th><th>Comentarios</th></tr></thead>
        <tbody>
        <?php foreach ($talleres as $t): ?>
          <tr>
            <td><strong><?= htmlspecialchars($t['nombre_taller']) ?></strong></td>
            <td><?= formatDate($t['fecha_taller']) ?></td>
            <td><?= htmlspecialchars($t['lugar'] ?? '—') ?></td>
            <td><?= $t['asistio'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
            <td><?= $t['calificacion'] ? '<strong style="color:var(--brand-pick)">'.(int)$t['calificacion'].'/5</strong>' : '—' ?></td>
            <td style="font-size:.8rem;color:var(--text2)"><?= htmlspecialchars($t['comentarios'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$talleres): ?><tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox d-block mb-1" style="font-size:1.4rem;opacity:.4"></i>Sin talleres</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <!-- ENCUESTAS -->
  <div class="tab-pane fade" id="panel-encuestas">
    <?php if (!$encuestas): ?>
      <div class="card"><div class="card-body text-center text-muted py-4">
        <i class="bi bi-inbox d-block mb-1" style="font-size:1.4rem;opacity:.4"></i>Sin encuestas
      </div></div>
    <?php endif; ?>
    <?php foreach ($encuestas as $enc): ?>
    <div class="card mb-3">
      <div class="card-header" style="font-size:.85rem">
        <i class="bi bi-clipboard-check me-1" style="color:var(--brand-pick)"></i>
        Encuesta — <?= formatDateTime($enc['fecha_respuesta'] ?? '') ?>
      </div>
      <div class="card-body" style="font-size:.85rem">
        <?php for ($n = 1; $n <= 5; $n++): ?>
          <?php if ($enc["pregunta_$n"] ?? ''): ?>
          <div class="mb-2">
            <div style="color:var(--text2);font-size:.78rem;text-transform:uppercase;letter-spacing:.06em"><?= htmlspecialchars($enc["pregunta_$n"]) ?></div>
            <div><?= htmlspecialchars($enc["respuesta_$n"] ?? '—') ?></div>
          </div>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($enc['observaciones'] ?? ''): ?>
          <div class="mt-2 pt-2" style="border-top:1px solid var(--border)">
            <div style="color:var(--text2);font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Observaciones</div>
            <div><?= htmlspecialchars($enc['observaciones']) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- DOCUMENTOS -->
  <div class="tab-pane fade" id="panel-documentos">
    <div class="card"><div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Fecha subida</th><th>Acción</th></tr></thead>
        <tbody>
        <?php foreach ($documentos as $doc): ?>
          <tr>
            <td><i class="bi bi-file-earmark me-1" style="color:var(--brand-pick)"></i><?= htmlspecialchars($doc['nombre_documento']) ?></td>
            <td><?= htmlspecialchars($doc['tipo_documento'] ?? '—') ?></td>
            <td><?= $doc['tamano_kb'] ? number_format($doc['tamano_kb']).' KB' : '—' ?></td>
            <td><?= formatDateTime($doc['fecha_subida'] ?? '') ?></td>
            <td><a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>" target="_blank" class="btn btn-outline-primary btn-sm btn-action"><i class="bi bi-download"></i></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$documentos): ?><tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox d-block mb-1" style="font-size:1.4rem;opacity:.4"></i>Sin documentos</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>

</div><!-- /.tab-content -->

<?php include 'includes/footer.php'; ?>