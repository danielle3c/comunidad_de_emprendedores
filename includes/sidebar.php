<?php
// includes/sidebar.php
$current = basename($_SERVER['PHP_SELF']);
function navActive(string $file, string $current): string {
    return $file === $current ? 'active' : '';
}
?>
<div class="sidebar d-flex flex-column">
  <!-- Logo más compacto -->
  <div class="brand-compact">
    <div class="logo-wrapper">
      <i class="bi bi-people-fill"></i>
    </div>
    <div class="brand-text">
      <span class="title">Emprendedores</span>
      <span class="sub">v2.0</span>
    </div>
  </div>

  <nav class="flex-grow-1 py-2">
    <div class="nav-section">Principal</div>
    <a href="index.php" class="nav-link <?= navActive('index.php', $current) ?>" title="Dashboard">
      <i class="bi bi-speedometer2"></i>
      <span class="nav-text">Dashboard</span>
    </a>

    <div class="nav-section">Personas</div>
    <a href="personas.php" class="nav-link <?= navActive('personas.php', $current) ?>" title="Personas">
      <i class="bi bi-person"></i>
      <span class="nav-text">Personas</span>
    </a>
    <a href="emprendedores.php" class="nav-link <?= navActive('emprendedores.php', $current) ?>" title="Emprendedores">
      <i class="bi bi-briefcase"></i>
      <span class="nav-text">Emprendedores</span>
    </a>

    <div class="nav-section">Finanzas</div>
    <a href="contratos.php" class="nav-link <?= navActive('contratos.php', $current) ?>" title="Contratos">
      <i class="bi bi-file-earmark-text"></i>
      <span class="nav-text">Contratos</span>
    </a>
    <a href="creditos.php" class="nav-link <?= navActive('creditos.php', $current) ?>" title="Créditos">
      <i class="bi bi-credit-card"></i>
      <span class="nav-text">Créditos</span>
    </a>
    <a href="cobranzas.php" class="nav-link <?= navActive('cobranzas.php', $current) ?>" title="Cobranzas">
      <i class="bi bi-cash-coin"></i>
      <span class="nav-text">Cobranzas</span>
    </a>

    <div class="nav-section">Actividades</div>
    <a href="talleres.php" class="nav-link <?= navActive('talleres.php', $current) ?>" title="Talleres">
      <i class="bi bi-book"></i>
      <span class="nav-text">Talleres</span>
    </a>
    <a href="inscripciones_talleres.php" class="nav-link <?= navActive('inscripciones_talleres.php', $current) ?>" title="Inscripciones">
      <i class="bi bi-journal-check"></i>
      <span class="nav-text">Inscripciones</span>
    </a>
    <a href="jornadas.php" class="nav-link <?= navActive('jornadas.php', $current) ?>" title="Jornadas">
      <i class="bi bi-calendar-event"></i>
      <span class="nav-text">Jornadas</span>
    </a>
    <a href="carritos.php" class="nav-link <?= navActive('carritos.php', $current) ?>" title="Carritos">
      <i class="bi bi-cart3"></i>
      <span class="nav-text">Carritos</span>
    </a>

    <div class="nav-section">Otros</div>
    <a href="encuestas.php" class="nav-link <?= navActive('encuestas.php', $current) ?>" title="Encuestas">
      <i class="bi bi-clipboard-data"></i>
      <span class="nav-text">Encuestas</span>
    </a>
    <a href="documentos.php" class="nav-link <?= navActive('documentos.php', $current) ?>" title="Documentos">
      <i class="bi bi-folder"></i>
      <span class="nav-text">Documentos</span>
    </a>
    <a href="tarjetas_presentacion.php" class="nav-link <?= navActive('tarjetas_presentacion.php', $current) ?>" title="Tarjetas">
      <i class="bi bi-person-vcard"></i>
      <span class="nav-text">Tarjetas</span>
    </a>
    <a href="usuarios.php" class="nav-link <?= navActive('usuarios.php', $current) ?>" title="Usuarios">
      <i class="bi bi-people"></i>
      <span class="nav-text">Usuarios</span>
    </a>
    <a href="auditoria.php" class="nav-link <?= navActive('auditoria.php', $current) ?>" title="Auditoría">
      <i class="bi bi-shield-check"></i>
      <span class="nav-text">Auditoría</span>
    </a>
    <a href="configuraciones.php" class="nav-link <?= navActive('configuraciones.php', $current) ?>" title="Configuración">
      <i class="bi bi-gear"></i>
      <span class="nav-text">Configuración</span>
    </a>
  </nav>

  <!-- Usuario al pie del sidebar más compacto -->
  <div class="user-compact">
    <div class="avatar">
      <i class="bi bi-person-fill"></i>
    </div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></div>
      <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['user']['rol'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a href="logout.php" class="logout-btn" title="Cerrar sesión">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</div>