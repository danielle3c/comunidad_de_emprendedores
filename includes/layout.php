<?php
// includes/layout.php — esqueleto HTML para index.php (usa ob_start + $content)
define('FROM_LAYOUT', true);

// helpers y flash
require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'Corporación de Fomento La Granja', ENT_QUOTES, 'UTF-8') ?></title>

  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- CSS del sistema (en raíz) -->
  <link href="app.css" rel="stylesheet">
</head>
<body class="hide-local-search">

<div class="sys-toast" id="sysToast"></div>

<div class="d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main-content flex-grow-1">
    <?php include __DIR__ . '/header.php'; ?>
    <?= $content ?? '' ?>
    <?php include __DIR__ . '/footer.php'; ?>
  </div>
</div>

</body>
</html>
