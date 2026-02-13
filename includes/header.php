<?php
// Asegura sesión antes de cualquier salida
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Comunidad de Emprendedores' ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f6f8fb;
      --card:#ffffff;
      --text:#0f172a;
      --muted:#64748b;
      --border:#e7eef7;
      --shadow:0 10px 30px rgba(15,23,42,.06);
      --shadow-sm:0 6px 18px rgba(15,23,42,.05);
      --radius:16px;
      --sidebar:#0f172a;
      --sidebar2:#111c33;
    }

    body{ background:var(--bg); font-size:.92rem; color:var(--text); }

    /* Sidebar */
    .sidebar{
      min-height:100vh; width:252px; position:fixed; top:0; left:0; z-index:100;
      background: linear-gradient(180deg, var(--sidebar), var(--sidebar2));
      border-right:1px solid rgba(255,255,255,.06);
      overflow-y:auto;
    }
    .sidebar .brand{
      padding:1.2rem 1rem;
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    .sidebar .brand .title{ color:#fff; font-weight:800; letter-spacing:-.02em; }
    .sidebar .brand .sub{ color:rgba(255,255,255,.55); font-size:.78rem; }

    .sidebar .nav-section{
      font-size:.72rem; text-transform:uppercase; letter-spacing:.12em;
      color:rgba(255,255,255,.35); padding:1rem 1rem .35rem;
    }
    .sidebar .nav-link{
      color:rgba(255,255,255,.70);
      padding:.55rem .9rem;
      border-radius:12px;
      margin:3px 10px;
      font-size:.9rem;
      display:flex; align-items:center; gap:.55rem;
    }
    .sidebar .nav-link:hover{ background:rgba(255,255,255,.08); color:#fff; }
    .sidebar .nav-link.active{ background:rgba(255,255,255,.14); color:#fff; }

    /* Main */
    .main-content{ margin-left:252px; min-height:100vh; }
    .topbar{
      position:sticky; top:0; z-index:90;
      background:rgba(255,255,255,.92);
      backdrop-filter: blur(8px);
      border-bottom:1px solid var(--border);
      padding:.75rem 1.25rem;
    }
    .content-area{ padding: 1.25rem; }

    /* Cards / tables */
    .card{ border:1px solid var(--border); border-radius:var(--radius); box-shadow:none; }
    .card-soft{ background:var(--card); box-shadow:var(--shadow-sm); }
    .card-hover{ transition: transform .15s ease, box-shadow .15s ease; }
    .card-hover:hover{ transform: translateY(-2px); box-shadow:var(--shadow); }

    .table thead th{
      font-size:.78rem; text-transform:uppercase; letter-spacing:.06em;
      color:#64748b; font-weight:800; background:#fff;
      position:sticky; top:0; z-index:1;
      border-bottom:1px solid var(--border) !important;
    }
    .table td{ border-top:1px solid var(--border); vertical-align:middle; }
    .table-wrap{ max-height:360px; overflow:auto; border-radius:var(--radius); }

    .btn, .form-control, .input-group-text{ border-radius:12px; }
    .form-control, .input-group-text{ border:1px solid var(--border); }
    .form-control:focus{
      border-color: rgba(13,110,253,.35);
      box-shadow: 0 0 0 .25rem rgba(13,110,253,.10);
    }

    .badge{ border-radius:999px; padding:.45em .75em; font-weight:800; }
    .kpi-icon{ width:52px; height:52px; }

    /* Responsive */
    @media(max-width: 992px){
      .sidebar{ position:relative; width:100%; min-height:auto; }
      .main-content{ margin-left:0; }
      .topbar{ position:relative; }
    }
  </style>
</head>

<body>
<div class="d-flex">

  <!-- SIDEBAR -->
  <div class="sidebar d-flex flex-column">
    <div class="brand">
      <div class="title"><i class="bi bi-people-fill me-2"></i>Emprendedores</div>
<div style="color:#fff; font-size:12px; opacity:.7; padding-top:6px;">
  HEADER OK - TARJETAS
</div>
    </div>

    <nav class="flex-grow-1 py-2">
      <div class="nav-section">Principal</div>
      <a href="index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='index.php')?'active':'' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>

      <div class="nav-section">Personas</div>
      <a href="personas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='personas.php')?'active':'' ?>">
        <i class="bi bi-person"></i> Personas
      </a>
      <a href="emprendedores.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='emprendedores.php')?'active':'' ?>">
        <i class="bi bi-briefcase"></i> Emprendedores
      </a>

      <div class="nav-section">Finanzas</div>
      <a href="contratos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='contratos.php')?'active':'' ?>">
        <i class="bi bi-file-earmark-text"></i> Contratos
      </a>
      <a href="creditos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='creditos.php')?'active':'' ?>">
        <i class="bi bi-credit-card"></i> Créditos
      </a>
      <a href="cobranzas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='cobranzas.php')?'active':'' ?>">
        <i class="bi bi-cash-coin"></i> Cobranzas
      </a>

      <div class="nav-section">Actividades</div>
      <a href="talleres.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='talleres.php')?'active':'' ?>">
        <i class="bi bi-book"></i> Talleres
      </a>
      <a href="inscripciones_talleres.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='inscripciones_talleres.php')?'active':'' ?>">
        <i class="bi bi-journal-check"></i> Inscripciones
      </a>
      <a href="jornadas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='jornadas.php')?'active':'' ?>">
        <i class="bi bi-calendar-event"></i> Jornadas
      </a>
      <a href="carritos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='carritos.php')?'active':'' ?>">
        <i class="bi bi-cart3"></i> Carritos
      </a>

      <div class="nav-section">Otros</div>
      <a href="encuestas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='encuestas.php')?'active':'' ?>">
        <i class="bi bi-clipboard-data"></i> Encuestas
      </a>
      <a href="documentos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='documentos.php')?'active':'' ?>">
        <i class="bi bi-folder"></i> Documentos
      </a>
      <a href="tarjetas_presentacion.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='tarjetas_presentacion.php')?'active':'' ?>">
  <i class="bi bi-person-vcard"></i> Tarjetas de Presentación
</a>



      <a href="usuarios.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='usuarios.php')?'active':'' ?>">
        <i class="bi bi-people"></i> Usuarios
      </a>
      <a href="auditoria.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='auditoria.php')?'active':'' ?>">
        <i class="bi bi-shield-check"></i> Auditoría
      </a>
      <a href="configuraciones.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='configuraciones.php')?'active':'' ?>">
        <i class="bi bi-gear"></i> Configuración
      </a>
    </nav>
  </div>

  <!-- MAIN -->
  <div class="main-content flex-grow-1">

    <!-- TOPBAR con buscador -->
    <div class="topbar">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <h6 class="mb-0 fw-bold"><?= $pageTitle ?? '' ?></h6>
          <span class="text-muted" style="font-size:.82rem"><?= date('d/m/Y H:i') ?></span>
        </div>

        <div class="position-relative" style="max-width:560px; width:100%;">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input id="smartSearch" type="text" class="form-control"
                   placeholder="Buscar persona por RUT o nombre..."
                   autocomplete="off">
          </div>

          <div id="smartResults" class="list-group position-absolute w-100 mt-2 shadow-sm"
               style="z-index:9999; display:none; max-height:340px; overflow:auto;"></div>
        </div>
      </div>
    </div>

    <div class="content-area">

      <?php if ($flash = getFlash()): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-4">
          <?= htmlspecialchars($flash['message']) ?>
        </div>
      <?php endif; ?>
