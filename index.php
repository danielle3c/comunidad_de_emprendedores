<!-- TARJETAS -->
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
            <div class="text-muted small"><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="fw-bold fs-5"><?= htmlspecialchars((string)$c['value'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">

  <!-- ÚLTIMOS PAGOS -->
  <div class="col-lg-6">
    <div class="card card-soft">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-bold">Últimos pagos registrados</h6>
          <span class="text-muted small">Top 5</span>
        </div>

        <?php if (empty($ultimosPagos)): ?>
          <div class="text-muted">No hay pagos recientes.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Monto</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ultimosPagos as $p): ?>
                  <tr>
                    <td>$<?= number_format((float)$p['monto'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($p['fecha_pago'], ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <!-- PRÓXIMOS TALLERES -->
  <div class="col-lg-6">
    <div class="card card-soft">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-bold">Próximos talleres</h6>
          <span class="text-muted small">Top 5</span>
        </div>

        <?php if (empty($proximosTalleres)): ?>
          <div class="text-muted">No hay talleres programados.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($proximosTalleres as $t): ?>
                  <tr>
                    <td><?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($t['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
