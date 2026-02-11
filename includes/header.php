<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Comunidad de Emprendedores' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; font-size: 0.9rem; }
        .sidebar { min-height: 100vh; background: #1e293b; width: 240px; position: fixed; top: 0; left: 0; z-index: 100; overflow-y: auto; }
        .sidebar .brand { padding: 1.2rem 1rem; background: #0f172a; border-bottom: 1px solid #334155; }
        .sidebar .nav-link { color: #94a3b8; padding: .45rem 1rem; border-radius: 6px; margin: 2px 6px; font-size:.85rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .sidebar .nav-section { font-size:.7rem; text-transform:uppercase; letter-spacing:.1em; color:#475569; padding:.8rem 1rem .3rem; }
        .main-content { margin-left: 240px; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: .65rem 1.5rem; }
        .content-area { padding: 1.5rem; }
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.07); border-radius: 10px; }
        .table th { font-size:.78rem; text-transform:uppercase; letter-spacing:.05em; color:#64748b; font-weight:600; background:#f8fafc; }
        .table td { vertical-align: middle; }
        .btn-action { padding: .2rem .5rem; font-size: .78rem; }
        .form-label { font-weight: 500; font-size: .85rem; }
        @media(max-width:768px){ .sidebar{width:100%;position:relative;min-height:auto} .main-content{margin-left:0} }
    </style>
</head>
<body>
<div class="d-flex">
<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">
    <div class="brand">
        <div class="text-white fw-bold fs-6"><i class="bi bi-people-fill me-2"></i>Emprendedores</div>
        <div class="text-secondary" style="font-size:.72rem">Sistema de Gestión</div>
    </div>
    <nav class="flex-grow-1 py-2">
        <div class="nav-section">Principal</div>
        <a href="index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='index.php')?'active':'' ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>

        <div class="nav-section">Personas</div>
        <a href="personas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='personas.php')?'active':'' ?>"><i class="bi bi-person me-2"></i>Personas</a>
        <a href="emprendedores.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='emprendedores.php')?'active':'' ?>"><i class="bi bi-briefcase me-2"></i>Emprendedores</a>

        <div class="nav-section">Finanzas</div>
        <a href="contratos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='contratos.php')?'active':'' ?>"><i class="bi bi-file-earmark-text me-2"></i>Contratos</a>
        <a href="creditos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='creditos.php')?'active':'' ?>"><i class="bi bi-credit-card me-2"></i>Créditos</a>
        <a href="cobranzas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='cobranzas.php')?'active':'' ?>"><i class="bi bi-cash-coin me-2"></i>Cobranzas</a>

        <div class="nav-section">Actividades</div>
        <a href="talleres.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='talleres.php')?'active':'' ?>"><i class="bi bi-book me-2"></i>Talleres</a>
        <a href="inscripciones_talleres.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='inscripciones_talleres.php')?'active':'' ?>"><i class="bi bi-journal-check me-2"></i>Inscripciones</a>
        <a href="jornadas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='jornadas.php')?'active':'' ?>"><i class="bi bi-calendar-event me-2"></i>Jornadas</a>
        <a href="carritos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='carritos.php')?'active':'' ?>"><i class="bi bi-cart3 me-2"></i>Carritos</a>

        <div class="nav-section">Otros</div>
        <a href="encuestas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='encuestas.php')?'active':'' ?>"><i class="bi bi-clipboard-data me-2"></i>Encuestas</a>
        <a href="documentos.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='documentos.php')?'active':'' ?>"><i class="bi bi-folder me-2"></i>Documentos</a>
        <a href="usuarios.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='usuarios.php')?'active':'' ?>"><i class="bi bi-people me-2"></i>Usuarios</a>
        <a href="auditoria.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='auditoria.php')?'active':'' ?>"><i class="bi bi-shield-check me-2"></i>Auditoría</a>
        <a href="configuraciones.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='configuraciones.php')?'active':'' ?>"><i class="bi bi-gear me-2"></i>Configuración</a>
    </nav>
</div>
<!-- MAIN -->
<div class="main-content flex-grow-1">
    <div class="topbar d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><?= $pageTitle ?? '' ?></h6>
        <span class="text-muted" style="font-size:.8rem"><?= date('d/m/Y H:i') ?></span>
    </div>
    <div class="content-area">
<?php
$flash = getFlash();
if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
