<?php
// includes/header.php
if (!defined('FROM_LAYOUT')):
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Corporación de Fomento La Granja', ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="hide-local-search">

<!-- Fondo animado -->
<!-- ===== CONTROLES FLOTANTES ===== -->
<!-- ===== BARRA DE BRILLO ===== -->
<div class="sys-toast" id="sysToast"></div>

<div class="d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main-content flex-grow-1">
<?php endif; // FROM_LAYOUT ?>

<?php
// Calcular la ruta absoluta al API de búsqueda una sola vez
$_searchApiUrl = rtrim(
    str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])),
    '/'
) . '/includes/search_global_api.php';
?>
    <!-- TOPBAR -->
    <div class="topbar">
      <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <h6 class="mb-0 fw-bold" style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;letter-spacing:.03em">
          <?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h6>

        <!-- Búsqueda inteligente -->
        <div class="position-relative" style="max-width:520px;width:100%">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="smartSearch" type="text" class="form-control"
                   placeholder="Buscar persona por RUT o nombre..." autocomplete="off"
                   data-api="<?= htmlspecialchars($_searchApiUrl, ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div id="smartResults" class="list-group position-absolute w-100 mt-1 shadow"
               style="z-index:9999;display:none;max-height:320px;overflow:auto;border-radius:12px"></div>
        </div>

        <span class="text-muted" style="font-size:.78rem;white-space:nowrap">
          <?= date('d/m/Y H:i') ?>
        </span>
      </div>
    </div>

    <div class="content-area">
      <?php if (function_exists('getFlash') && ($flash = getFlash())): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> d-flex align-items-center gap-2 mb-3">
          <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?>"></i>
          <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
