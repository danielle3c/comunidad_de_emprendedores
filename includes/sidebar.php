<?php
$current = basename($_SERVER['PHP_SELF']);
function active($file, $current){ return $file === $current ? 'active' : ''; }
?>
<div class="sidebar d-flex flex-column">
  <div class="brand">
    <div class="title"><i class="bi bi-people-fill me-2"></i>Emprendedores</div>
    <div class="sub">Panel de administración</div>
  </div>

  <nav class="flex-grow-1 py-2">
    <div class="nav-section">Principal</div>
    <a href="index.php" class="nav-link <?= active('index.php',$current) ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>

    <div class="nav-section">Personas</div>
    <a href="personas.php" class="nav-link <?= active('personas.php',$current) ?>"><i class="bi bi-person"></i> Personas</a>
    <a href="emprendedores.php" class="nav-link <?= active('emprendedores.php',$current) ?>"><i class="bi bi-briefcase"></i> Emprendedores</a>

    <div class="nav-section">Finanzas</div>
    <a href="contratos.php" class="nav-link <?= active('contratos.php',$current) ?>"><i class="bi bi-file-earmark-text"></i> Contratos</a>
    <a href="creditos.php" class="nav-link <?= active('creditos.php',$current) ?>"><i class="bi bi-credit-card"></i> Créditos</a>
    <a href="cobranzas.php" class="nav-link <?= active('cobranzas.php',$current) ?>"><i class="bi bi-cash-coin"></i> Cobranzas</a>

    <div class="nav-section">Actividades</div>
    <a href="talleres.php" class="nav-link <?= active('talleres.php',$current) ?>"><i class="bi bi-book"></i> Talleres</a>
    <a href="inscripciones_talleres.php" class="nav-link <?= active('inscripciones_talleres.php',$current) ?>"><i class="bi bi-journal-check"></i> Inscripciones</a>
    <a href="jornadas.php" class="nav-link <?= active('jornadas.php',$current) ?>"><i class="bi bi-calendar-event"></i> Jornadas</a>
    <a href="carritos.php" class="nav-link <?= active('carritos.php',$current) ?>"><i class="bi bi-cart3"></i> Carritos</a>

    <div class="nav-section">Otros</div>
    <a href="encuestas.php" class="nav-link <?= active('encuestas.php',$current) ?>"><i class="bi bi-clipboard-data"></i> Encuestas</a>
    <a href="documentos.php" class="nav-link <?= active('documentos.php',$current) ?>"><i class="bi bi-folder"></i> Documentos</a>
    <a href="tarjetas_presentacion.php" class="nav-link <?= active('tarjetas_presentacion.php',$current) ?>"><i class="bi bi-person-vcard"></i> Tarjetas</a>
    <a href="usuarios.php" class="nav-link <?= active('usuarios.php',$current) ?>"><i class="bi bi-people"></i> Usuarios</a>
    <a href="auditoria.php" class="nav-link <?= active('auditoria.php',$current) ?>"><i class="bi bi-shield-check"></i> Auditoría</a>
    <a href="configuraciones.php" class="nav-link <?= active('configuraciones.php',$current) ?>"><i class="bi bi-gear"></i> Configuración</a>
  </nav>
</div>
