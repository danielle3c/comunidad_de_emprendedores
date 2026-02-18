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
<body>

<div class="bg-canvas">
  <svg class="hex-svg" viewBox="0 0 200 200" preserveAspectRatio="xMidYMid slice">
    <defs>
      <pattern id="hexPat" x="0" y="0" width="30" height="26" patternUnits="userSpaceOnUse">
        <polygon points="15,1 28,8 28,22 15,29 2,22 2,8" fill="none" stroke="rgb(67,176,42)" stroke-width=".7" id="hexStroke"/>
      </pattern>
    </defs>
    <rect width="100%" height="100%" fill="url(#hexPat)"/>
  </svg>
</div>

<div class="sys-controls">
  <button class="sys-btn" onclick="sysToggleTheme()" id="sysThemeBtn" title="Cambiar tema">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="5"/>
      <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
      <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
      <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
      <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <span id="sysThemeLabel">Claro</span>
  </button>

  <button class="sys-btn" onclick="document.getElementById('colorPickerGlobal').click()" title="Cambiar color">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/>
      <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/>
      <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
    </svg>
    Color
    <input type="color" id="colorPickerGlobal" value="#43b02a" oninput="sysApplyColor(this.value)"
           style="position:absolute;opacity:0;width:0;height:0;pointer-events:none">
  </button>

  <button class="sys-btn" onclick="sysCopyBg()" title="Copiar CSS del fondo">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="9" y="9" width="13" height="13" rx="2"/>
      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
    </svg>
    Fondo
  </button>

  <button class="sys-btn" onclick="sysExportPDF()" title="Exportar como PDF">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <polyline points="14 2 14 8 20 8"/>
      <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
    </svg>
    PDF
  </button>

  <select class="lang-select" onchange="sysSetLang(this.value)" id="sysLangSelect" title="Idioma">
    <option value="es">🇨🇱 ES</option>
    <option value="en">🇺🇸 EN</option>
    <option value="pt">🇧🇷 PT</option>
  </select>
</div>

<div class="brightness-rail" title="Ajustar brillo">
  <span class="rail-label">Brillo</span>
  <input type="range" id="sysBrightnessBar" min="40" max="140" value="100"
         oninput="document.body.style.filter='brightness('+this.value+'%)'">
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text2)">
    <circle cx="12" cy="12" r="5"/>
    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
  </svg>
</div>

<div class="sys-toast" id="sysToast"></div>

<div class="d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main-content flex-grow-1">
<?php endif; ?>

    <div class="topbar">
      <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <h6 class="mb-0 fw-bold" style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;letter-spacing:.03em">
          <?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h6>

        <div class="position-relative" style="max-width:520px;width:100%">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="smartSearch" type="text" class="form-control"
                   placeholder="Buscar persona por RUT o nombre..." autocomplete="off">
          </div>
          <div id="smartResults" class="list-group position-absolute w-100 mt-1 shadow"
               style="z-index:9999;display:none;max-height:320px;overflow:auto"></div>
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